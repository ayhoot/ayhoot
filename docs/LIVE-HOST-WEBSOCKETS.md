# Live Host real-time (WebSockets) — production setup & troubleshooting

The "live host" feature (Kahoot-style live quiz) is **entirely driven by real-time
broadcasts**. If WebSockets don't work in production, *all* of these break at once:

- teacher dashboard never shows participants / charts / rankings,
- students stay stuck on the waiting page when the teacher starts,
- submitted answers don't register on the host,
- the report page opens empty.

The application code is correct (verified end-to-end locally). Breakage in
production is **always a transport/config problem**, described below.

---

## Architecture (how it must be wired)

```
                 publish (loopback, plain http)
  Laravel app  ───────────────────────────────►  websockets:serve
 (php-fpm)         http://127.0.0.1:6001            (127.0.0.1:6001, plain ws)
                                                        ▲
                                                        │ proxied by nginx
                 subscribe (same-origin, TLS)           │ (TLS terminates here)
   Browser  ──────────────────────────────────────────►┘
              wss://ayhoot.com/app/<APP_KEY>   →  nginx  →  127.0.0.1:6001
```

Two independent paths reach the **same** `websockets:serve` process:

1. **Server → server (publish).** Laravel's `pusher` broadcast driver POSTs each
   event to the websocket server over the **loopback in plain http**
   (`PUSHER_HOST=127.0.0.1`, `PUSHER_SCHEME=http`, `PUSHER_PORT=6001`).
2. **Browser → server (subscribe).** `pusher-js` opens `wss://ayhoot.com/app/<key>`
   on **port 443**; **nginx** terminates TLS and proxies the `/app` path to
   `127.0.0.1:6001` (which speaks plain `ws`).

### Why the old config was broken
- `PUSHER_HOST=socket.ayhoot.com` + `PUSHER_SCHEME=https` on **:6001** made Laravel
  publish over `https://socket.ayhoot.com:6001`. But `websockets:serve` has **no TLS
  cert** (`LARAVEL_WEBSOCKETS_SSL_*` empty), so it only speaks plain `ws`/`http`.
  The TLS handshake to a plain port fails → **every `broadcast()` silently dies**.
- The production JS bundle is built in CI **without** `VITE_PUSHER_HOST`
  (`.github/workflows/deploy.yml`), so the browser connects **same-origin**
  (`wss://ayhoot.com/app/...`). That requires the nginx `/app` proxy below. If it
  isn't present, the browser never connects.

Fixing **both** = publish over loopback http + nginx same-origin proxy. No public
`:6001` port, no extra TLS cert, no `socket.ayhoot.com` dependency.

---

## 1) Server `.env`  (`/var/www/ayhoot/.env`)

```dotenv
BROADCAST_DRIVER=pusher
QUEUE_CONNECTION=sync          # events use ShouldBroadcastNow — no queue worker needed

PUSHER_APP_ID=2120092
PUSHER_APP_KEY=5c8a81ec227bf87005fe
PUSHER_APP_SECRET=d5ce19d23c076bf98cca
PUSHER_HOST=127.0.0.1          # ← was socket.ayhoot.com  (publish over loopback)
PUSHER_PORT=6001
PUSHER_SCHEME=http             # ← was https              (no TLS on :6001)
PUSHER_APP_CLUSTER=mt1
PUSHER_APP_PATH=
```

Config is cached on deploy, so after editing `.env`:

```bash
cd /var/www/ayhoot
php artisan config:clear && php artisan config:cache
```

> The browser side needs **no** server env: the bundle is built in CI and already
> connects same-origin. Do not set `VITE_PUSHER_HOST` on the server.

---

## 2) systemd unit  (`/etc/systemd/system/ayhoot-websockets.service`)

The deploy already runs `systemctl restart ayhoot-websockets`. Ensure the unit
exists and binds to **loopback** (not `0.0.0.0` — nginx is the only client):

```ini
[Unit]
Description=Ayhoot Laravel WebSockets
After=network.target mysql.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/ayhoot
ExecStart=/usr/bin/php artisan websockets:serve --host=127.0.0.1 --port=6001
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

```bash
systemctl daemon-reload
systemctl enable --now ayhoot-websockets
systemctl status ayhoot-websockets        # active (running)
ss -ltnp | grep 6001                      # php listening on 127.0.0.1:6001
```

---

## 3) nginx — proxy `/app` to the websocket server

Inside the existing `server { listen 443 ssl; server_name ayhoot.com; ... }` block,
**above** the SPA catch-all `location /`, add:

```nginx
# laravel-websockets: browser connects to wss://ayhoot.com/app/<APP_KEY>
location ^~ /app/ {
    proxy_pass http://127.0.0.1:6001;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 3600s;   # keep idle sockets open through long quizzes
    proxy_send_timeout 3600s;
}
```

```bash
nginx -t && systemctl reload nginx
```

> `^~ /app/` only matches paths starting with `/app/`. The SPA has no routes under
> `/app/` (routes are `/teacher/*`, `/student/*`, `/institute/*`, `/hostlive/:pin`,
> `/login`), so there is no collision — but verify if you add routes later.
> You can now retire `socket.ayhoot.com` (DNS + any `:6001` vhost) entirely.

---

## 4) Verification (run on the server, top to bottom)

```bash
# a) process up and listening on loopback
systemctl status ayhoot-websockets
ss -ltnp | grep 6001

# b) Laravel can publish over loopback (expect connection, not TLS error)
curl -s -o /dev/null -w "loopback http %{http_code}\n" http://127.0.0.1:6001

# c) browser WS handshake THROUGH nginx — expect "HTTP/1.1 101 Switching Protocols"
curl -s -i -N \
  -H "Connection: Upgrade" -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" -H "Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==" \
  "https://ayhoot.com/app/5c8a81ec227bf87005fe?protocol=7&client=js&version=8.4.0" \
  | head -n 1

# d) end-to-end broadcast from Laravel (uses the first session+participant in DB)
#    GET /api/live-session/{sessionId}/test-broadcast  (auth required), then:
tail -n 50 storage/logs/laravel.log | grep -i BROADCAST   # should log, with no error
```

In the browser (teacher screen, DevTools):
- **Console:** `[WebSocket] Configured: { host: "ayhoot.com", forceTLS: true, transports: ["wss"] }` then Echo connects.
- **Network → WS:** a `wss://ayhoot.com/app/5c8a81ec227bf87005fe...` request with status **101**, frames flowing.

---

## 5) Troubleshooting matrix

| Symptom | Likely cause | Fix |
|---|---|---|
| Browser WS request is `404`/`502`, never `101` | nginx `/app` proxy missing/wrong, or `websockets:serve` down | add §3 block; `systemctl status ayhoot-websockets` |
| Browser connects (101) but nothing ever arrives | publish failing (Laravel→ws) | set `PUSHER_HOST=127.0.0.1` + `PUSHER_SCHEME=http` (§1), then `config:cache` |
| `laravel.log` shows cURL/TLS error on broadcast | publishing to `https://...:6001` | §1 (loopback http) |
| Works right after deploy, breaks later | server crashed, no auto-restart | `Restart=always` in unit (§2) |
| Local works, prod doesn't | config cache stale | `php artisan config:clear && php artisan config:cache` |
| Browser console shows `wsHost: 127.0.0.1` in prod | a server-side build picked up `VITE_PUSHER_HOST` | keep `VITE_PUSHER_HOST` empty (§1 note); rebuild in CI |

---

## Local development

```bash
php artisan websockets:serve --host=127.0.0.1 --port=6001   # terminal 1
php artisan serve                                           # terminal 2
npm run dev                                                 # terminal 3
```

Local `.env` already publishes and subscribes to `ws://127.0.0.1:6001` directly
(no nginx). Open `http://127.0.0.1:6001/laravel-websockets` for the live dashboard.
