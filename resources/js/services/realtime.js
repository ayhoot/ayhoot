import Echo from 'laravel-echo'
import { getWebSocketConfig } from '@/bootstrap'

/**
 * Centralized real-time (self-hosted Pusher / laravel-websockets) service.
 *
 * A single shared Echo connection is multiplexed across every feature
 * (live host sessions, notifications, …) instead of opening a new WebSocket
 * per feature. Channels are reference-counted so leaving one feature's channel
 * never tears down a channel another feature still needs.
 *
 *   import { realtime } from '@/services/realtime'
 *   const ch = realtime.channel('notifications.42')
 *   ch.listen('.notification.created', (e) => { ... })
 *   // later
 *   realtime.leave('notifications.42')
 */

let echoInstance = null
const refCounts = Object.create(null)

function getEcho() {
    if (echoInstance) return echoInstance

    // Reuse the global Echo created in bootstrap.js (imported by app.js before any
    // component mounts) so the ENTIRE app multiplexes ONE WebSocket connection —
    // live sessions here + exam-attempt monitoring (which uses window.Echo). Without
    // this we opened a second socket with identical config, doubling reconnects and
    // making the two halves disagree about connection state.
    if (typeof window !== 'undefined' && window.Echo) {
        echoInstance = window.Echo
        return echoInstance
    }

    echoInstance = new Echo(getWebSocketConfig())
    if (typeof window !== 'undefined') window.Echo = echoInstance
    return echoInstance
}

/** Subscribe to (or reuse) a public channel and bump its ref count. */
function channel(name) {
    const echo = getEcho()
    refCounts[name] = (refCounts[name] || 0) + 1
    return echo.channel(name)
}

/** Subscribe to (or reuse) a private channel and bump its ref count. */
function privateChannel(name) {
    const echo = getEcho()
    refCounts[name] = (refCounts[name] || 0) + 1
    return echo.private(name)
}

/** Subscribe to a presence channel and bump its ref count. */
function presence(name) {
    const echo = getEcho()
    refCounts[name] = (refCounts[name] || 0) + 1
    return echo.join(name)
}

/** Leave a channel; only actually unsubscribes once the last consumer leaves. */
function leave(name) {
    if (!echoInstance) return
    refCounts[name] = Math.max(0, (refCounts[name] || 0) - 1)
    if (refCounts[name] === 0) {
        try {
            echoInstance.leave(name)
        } catch (e) {
            console.warn('[realtime] Error leaving channel', name, e)
        }
        delete refCounts[name]
    }
}

/** Bind to the underlying pusher connection lifecycle (connected/error/…). */
function onConnection(cbs = {}) {
    try {
        const connection = getEcho().connector.pusher.connection
        if (cbs.onConnected) {
            connection.bind('connected', cbs.onConnected)
            // The shared Echo connects once and is reused, so the socket can already
            // be 'connected' by the time a feature binds — 'connected' has then
            // already fired and the UI would be stuck showing "Connecting…".
            if (connection.state === 'connected') cbs.onConnected()
        }
        if (cbs.onDisconnected) connection.bind('disconnected', cbs.onDisconnected)
        if (cbs.onError) connection.bind('error', cbs.onError)
        if (cbs.onStateChange) connection.bind('state_change', cbs.onStateChange)
        if (cbs.onUnavailable) connection.bind('unavailable', cbs.onUnavailable)
    } catch (e) {
        console.error('[realtime] Failed to bind connection handlers:', e)
    }
}

/** Fully tear down the shared connection (e.g. on logout). */
function disconnect() {
    if (echoInstance) {
        try {
            echoInstance.disconnect()
        } catch (e) {
            /* ignore */
        }
        echoInstance = null
        if (typeof window !== 'undefined' && window.Echo) window.Echo = null
    }
    for (const k in refCounts) delete refCounts[k]
}

export const realtime = {
    getEcho,
    channel,
    privateChannel,
    presence,
    leave,
    onConnection,
    disconnect,
}

export default realtime
