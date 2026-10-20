<template>
    <PageWrapper>
                <div class="mx-auto">
                    <!-- Page Header -->
                    <div class="pb-6">
                        <h2 class="text-3xl font-bold mb-2">{{ t('notifications.title') }}</h2>
                        <p class="text-gray-600">{{ t('notifications.description') }}</p>
                    </div>

                    <!-- START: Loading Skeleton -->
                    <div v-if="isLoading" class="space-y-4">
                        <div v-for="n in 5" :key="n" class="p-4 flex items-start gap-4 animate-pulse">
                            <div class="h-10 w-10 rounded-full bg-gray-200 flex-shrink-0"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                            </div>
                            <div class="h-8 w-24 bg-gray-200 rounded-md self-center"></div>
                        </div>
                    </div>

                    <!-- START: Notifications List -->
                    <div v-else-if="notifications.length > 0" class="space-y-8">
                        <!-- Loop through notification groups -->
                        <div v-for="group in groupedNotifications" :key="group.title">
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                    <div class="w-full border-t border-gray-200"></div>
                                </div>
                                <div
                                    class="relative flex"
                                    :class="$i18n.locale === 'fa' ? 'justify-end' : 'justify-start'"
                                >
                                    <span
                                        class="bg-gray-50 text-sm font-medium text-gray-500"
                                        :class="$i18n.locale === 'fa' ? 'pl-3' : 'pr-3'"
                                        >{{ group.title }}</span
                                    >
                                </div>
                            </div>

                            <div class="mt-4 flow-root">
                                <ul role="list" class="-mb-4">
                                    <li
                                        v-for="notification in group.items"
                                        :key="notification.id"
                                        class="hover:bg-gray-100 rounded-lg transition-colors duration-200"
                                        :class="{ 'opacity-60': notification.read_at }"
                                    >
                                        <div class="relative p-4">
                                            <div class="flex items-start gap-4">
                                                <div
                                                    class="relative flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center ring-8 ring-white"
                                                >
                                                    <svg
                                                        class="h-5 w-5 text-blue-600"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke-width="1.5"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"
                                                        />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm text-gray-800">{{ notification.message }}</p>
                                                    <p class="mt-1 text-xs text-gray-500">
                                                        {{ formatTimestamp(notification.created_at) }}
                                                    </p>
                                                </div>
                                                <div class="flex-shrink-0 self-center">
                                                    <ResponsiveButton
                                                        v-if="!notification.read_at"
                                                        @click="markAsRead(notification)"
                                                        type="light-gray"
                                                        class="px-3 py-1 text-sm"
                                                    >
                                                        {{ t('notifications.markAsRead') }}
                                                    </ResponsiveButton>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-12">
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"
                            />
                        </svg>

                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ t('notifications.empty.title') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ t('notifications.empty.description') }}</p>
                    </div>
                </div>
    </PageWrapper>
</template>

<script>
import PageWrapper from '@/components/shared/PageWrapper.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import { apiCall } from '@/services/apiService'
import { useToast } from 'vue-toast-notification'
import { useI18n } from 'vue-i18n'

export default {
    name: 'NotificationIndex',
    components: {
        PageWrapper,
        ResponsiveButton,
    },
    setup() {
        const { t, locale } = useI18n()
        const $toast = useToast()
        return { t, locale, $toast }
    },
    data() {
        return {
            isLoading: true,
            notifications: [],
        }
    },
    computed: {
        groupedNotifications() {
            const groups = {
                today: { title: this.t('notifications.timeGroups.today'), items: [] },
                yesterday: { title: this.t('notifications.timeGroups.yesterday'), items: [] },
                thisWeek: { title: this.t('notifications.timeGroups.thisWeek'), items: [] },
                older: { title: this.t('notifications.timeGroups.older'), items: [] },
            }

            const now = new Date()
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
            const yesterday = new Date(today)
            yesterday.setDate(yesterday.getDate() - 1)
            const startOfWeek = new Date(today)
            startOfWeek.setDate(startOfWeek.getDate() - now.getDay())

            this.notifications.forEach((notification) => {
                const notificationDate = new Date(notification.created_at)
                const notificationDay = new Date(
                    notificationDate.getFullYear(),
                    notificationDate.getMonth(),
                    notificationDate.getDate()
                )

                if (notificationDay.getTime() === today.getTime()) {
                    groups.today.items.push(notification)
                } else if (notificationDay.getTime() === yesterday.getTime()) {
                    groups.yesterday.items.push(notification)
                } else if (notificationDay >= startOfWeek) {
                    groups.thisWeek.items.push(notification)
                } else {
                    groups.older.items.push(notification)
                }
            })

            return Object.values(groups).filter((group) => group.items.length > 0)
        },
    },
    methods: {
        async fetchNotifications() {
            this.isLoading = true
            try {
                const response = await apiCall('GET', '/api/student/notifications')
                this.notifications = response.notifications || []
            } catch (error) {
                console.error('Failed to fetch notifications:', error)
                this.$toast.error(this.t('notifications.notifications.loadFailed'))
            } finally {
                this.isLoading = false
            }
        },
        async markAsRead(notification) {
            try {
                const index = this.notifications.findIndex((n) => n.id === notification.id)
                if (index !== -1) {
                    this.notifications[index].read_at = new Date().toISOString()
                }

                await apiCall('GET', `/api/student/notifications/${notification.id}/read`)
            } catch (error) {
                console.error('Failed to mark notification as read:', error)
                const index = this.notifications.findIndex((n) => n.id === notification.id)
                if (index !== -1) {
                    this.notifications[index].read_at = null
                }
                this.$toast.error(this.t('notifications.notifications.updateFailed'))
            }
        },
        formatTimestamp(timestamp) {
            const now = new Date()
            const past = new Date(timestamp)
            const seconds = Math.floor((now - past) / 1000)

            const dateLocale = this.locale === 'fa' ? 'fa-IR' : 'en-US'

            const rtf = new Intl.RelativeTimeFormat(dateLocale, { numeric: 'auto' })

            const minute = 60
            const hour = minute * 60
            const day = hour * 24
            const week = day * 7
            const month = day * 30
            const year = day * 365

            if (seconds < minute) {
                return rtf.format(-seconds, 'second')
            } else if (seconds < hour) {
                return rtf.format(-Math.floor(seconds / minute), 'minute')
            } else if (seconds < day) {
                return rtf.format(-Math.floor(seconds / hour), 'hour')
            } else if (seconds < week) {
                return rtf.format(-Math.floor(seconds / day), 'day')
            } else if (seconds < month) {
                return rtf.format(-Math.floor(seconds / week), 'week')
            } else if (seconds < year) {
                return rtf.format(-Math.floor(seconds / month), 'month')
            } else {
                return rtf.format(-Math.floor(seconds / year), 'year')
            }
        },
    },
    async mounted() {
        await this.fetchNotifications()
        // Refresh the list when a notification arrives in real time.
        this._onRealtimeNotif = () => this.fetchNotifications()
        window.addEventListener('ayhoot:notification', this._onRealtimeNotif)
    },
    beforeUnmount() {
        if (this._onRealtimeNotif) {
            window.removeEventListener('ayhoot:notification', this._onRealtimeNotif)
        }
    },
}
</script>
