import { realtime } from '@/services/realtime'
import { useToast } from 'vue-toast-notification'

/**
 * Global real-time notification subscription.
 *
 * Subscribes the logged-in user to their private notification channel over the
 * self-hosted websocket. On a new notification it shows a toast (so every panel
 * reacts) and dispatches a window `ayhoot:notification` event so any open
 * notification list can refresh itself. Idempotent — call connect() freely.
 */

let currentChannel = null

function getUser() {
    try {
        return JSON.parse(localStorage.getItem('user') || 'null')
    } catch (e) {
        return null
    }
}

function connect() {
    const role = localStorage.getItem('role')
    const token = localStorage.getItem('token')
    const user = getUser()

    // Logged out (or incomplete session) → ensure we are disconnected.
    if (!role || !token || !user || !user.id) {
        disconnect()
        return
    }

    const name = `notifications.${role}.${user.id}`
    if (currentChannel === name) return // already subscribed for this user

    disconnect()
    currentChannel = name

    try {
        const channel = realtime.privateChannel(name)
        channel.listen('.notification.created', (e) => {
            const n = e && e.notification ? e.notification : e
            try {
                useToast().info(n && n.message ? n.message : 'You have a new notification', {
                    position: 'top-right',
                    duration: 5000,
                })
            } catch (err) {
                /* toast not ready — non-fatal */
            }
            window.dispatchEvent(new CustomEvent('ayhoot:notification', { detail: n }))
        })
    } catch (e) {
        console.warn('[notifications] subscribe failed:', e)
        currentChannel = null
    }
}

function disconnect() {
    if (currentChannel) {
        realtime.leave(currentChannel)
        currentChannel = null
    }
}

export const notificationsRealtime = { connect, disconnect }
export default notificationsRealtime
