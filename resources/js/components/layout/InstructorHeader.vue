<template>
    <div class="fixed top-0 inset-x-0" :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'">
        <div class="flex justify-between items-center py-3 px-6 border-b-1 border-gray-300 z-50 bg-white">
            <div class="w-full flex justify-between">
                <!-- mobile sidebar -->
                <template v-if="searchMode">
                    <div class="flex items-center justify-between w-full">
                        <SearchWithCategory />
                        <button @click="toggleSearchMode" class="pl-4">
                            <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M18 6L6 18"
                                    stroke="#0F1D42"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M6 6L18 18"
                                    stroke="#0F1D42"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                    </div>
                </template>
                <template v-else>
                    <div class="flex items-center gap-4 sm:gap-6">
                        <button
                            @click="$emit('toggle-sidebar')"
                            class="block cursor-pointer xl:hidden text-gray-600 focus:outline-none"
                        >
                            <span class="sr-only">{{ t('header.openMenu') }}</span>
                            <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path d="M5 7H19" stroke="#33363F" stroke-width="2" stroke-linecap="round" />
                                <path d="M5 12H19" stroke="#33363F" stroke-width="2" stroke-linecap="round" />
                                <path d="M5 17H19" stroke="#33363F" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </button>
                        <RouterLink to="/">
                            <DashAyhootTutorLogo />
                        </RouterLink>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-4">
                        <UpgradeButton class="hidden sm:block" :to="`/${userRole}/plans`">{{
                            t('header.upgrade')
                        }}</UpgradeButton>

                        <!-- Create Button with Dropdown -->
                        <div class="relative" ref="createDropdownWrapper">
                            <ResponsiveButton
                                @click="toggleCreateDropdown"
                                class="block sm:hidden p-2"
                                type="dash-blue"
                            >
                                <Create />
                            </ResponsiveButton>
                            <ResponsiveButton
                                @click="toggleCreateDropdown"
                                class="hidden sm:block px-4 py-2"
                                type="dash-blue"
                                >{{ t('header.create') }}</ResponsiveButton
                            >
                            <div
                                v-if="createDropdownVisible"
                                class="absolute mt-5 w-72 bg-white rounded-lg shadow-xl border border-gray-200 z-20"
                                :class="$i18n.locale === 'fa' ? '-left-20' : '-right-20'"
                            >
                                <div class="flex flex-col space-y-1 p-2">
                                    <RouterLink
                                        :to="`/${userRole}/exam/create`"
                                        @click="createDropdownVisible = false"
                                        class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-100"
                                    >
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="flex-shrink-0 h-12 w-12 bg-blue-100 rounded-xl flex items-center justify-center"
                                            >
                                                <svg
                                                    class="w-6 h-6 text-blue-600"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                    />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-sm text-gray-800">
                                                    {{ t('header.createDropdown.ayhoot.title') }}
                                                </h4>
                                                <p class="text-xs text-gray-500">
                                                    {{ t('header.createDropdown.ayhoot.description') }}
                                                </p>
                                            </div>
                                        </div>
                                        <svg
                                            class="w-5 h-5 text-gray-400"
                                            :class="{ 'rotate-180': $i18n.locale === 'fa' }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 5l7 7-7 7"
                                            ></path>
                                        </svg>
                                    </RouterLink>
                                    <RouterLink
                                        :to="`/${userRole}/course/create`"
                                        @click="createDropdownVisible = false"
                                        class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-100"
                                    >
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="flex-shrink-0 text-purple-700 h-12 w-12 bg-purple-100 rounded-xl flex items-center justify-center"
                                            >
                                                <svg
                                                    width="24"
                                                    height="24"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                >
                                                    <path
                                                        d="M19 11V8.2C19 7.0799 19 6.51984 18.782 6.09202C18.5903 5.71569 18.2843 5.40973 17.908 5.21799C17.4802 5 16.9201 5 15.8 5H7.2C6.0799 5 5.51984 5 5.09202 5.21799C4.71569 5.40973 4.40973 5.71569 4.21799 6.09202C4 6.51984 4 7.07989 4 8.2V13.8C4 14.9201 4 15.4802 4.21799 15.908C4.40973 16.2843 4.71569 16.5903 5.09202 16.782C5.51984 17 6.07989 17 7.2 17H14"
                                                        class="stroke-current"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                    />
                                                    <path
                                                        d="M8 13H12"
                                                        class="stroke-current"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                    />
                                                    <path
                                                        d="M8 9H15"
                                                        class="stroke-current"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                    />
                                                    <circle
                                                        cx="18"
                                                        cy="15"
                                                        r="1"
                                                        class="stroke-current"
                                                        stroke-width="2"
                                                    />
                                                    <path
                                                        d="M20 20C20 20 19.5 19 18 19C16.5 19 16 20 16 20"
                                                        class="stroke-current"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                    />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-sm text-gray-800">
                                                    {{ t('header.createDropdown.course.title') }}
                                                </h4>
                                                <p class="text-xs text-gray-500">
                                                    {{ t('header.createDropdown.course.description') }}
                                                </p>
                                            </div>
                                        </div>
                                        <svg
                                            class="w-5 h-5 text-gray-400"
                                            :class="{ 'rotate-180': $i18n.locale === 'fa' }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 5l7 7-7 7"
                                            ></path>
                                        </svg>
                                    </RouterLink>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="relative" ref="pdropdownwrapper">
                            <ResponsiveButton
                                type="light-gray"
                                @click="togglePdropdown"
                                class="flex items-center gap-2 p-0"
                            >
                                <img
                                    v-if="institute.avatar"
                                    :src="instituteAvatarUrl"
                                    :alt="t('header.profileDropdown.avatarAlt')"
                                    class="w-9 h-9 rounded-md object-cover"
                                />
                                <div
                                    v-else
                                    class="w-9 h-9 rounded-md bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-sm"
                                >
                                    <svg
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M3.23779 19.5C4.5632 17.2892 7.46807 15.7762 12.0001 15.7762C16.5321 15.7762 19.4369 17.2892 20.7623 19.5M15.6001 8.1C15.6001 10.0882 13.9883 11.7 12.0001 11.7C10.0118 11.7 8.40007 10.0882 8.40007 8.1C8.40007 6.11177 10.0118 4.5 12.0001 4.5C13.9883 4.5 15.6001 6.11177 15.6001 8.1Z"
                                            class="stroke-current"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                </div>
                            </ResponsiveButton>
                            <div
                                v-if="pdropdown"
                                class="absolute mt-5 w-56 bg-white rounded-lg shadow-xl border border-gray-200"
                                :class="$i18n.locale === 'fa' ? 'left-0' : 'right-0'"
                            >
                                <RouterLink
                                    :to="`/${userRole}/my-profile`"
                                    class="flex items-center px-4 py-3 font-semibold text-sm rounded-t-md bg-gray-100 hover:bg-gray-200"
                                >
                                    <img
                                        v-if="institute.avatar"
                                        :src="instituteAvatarUrl"
                                        :alt="t('header.profileDropdown.avatarAlt')"
                                        class="w-8 h-8 rounded-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-xs"
                                    >
                                        {{ instituteInitials }}
                                    </div>
                                    <div :class="$i18n.locale === 'fa' ? 'mr-2' : 'pl-2'">
                                        <div class="truncate max-w-[15ch]">{{ institute.name }}</div>
                                        <div class="text-xs text-gray-500 font-light truncate max-w-[20ch]">
                                            @{{ institute.username }}
                                        </div>
                                    </div>
                                </RouterLink>

                                <RouterLink
                                    :to="`/${userRole}/wallet`"
                                    class="block px-4 py-2 font-medium text-sm hover:bg-gray-100"
                                    >{{ t('header.profileDropdown.wallet') }}</RouterLink
                                >
                                <RouterLink
                                    :to="`/${userRole}/plans`"
                                    class="block px-4 py-2 font-medium text-sm hover:bg-gray-100"
                                    >{{ t('header.upgrade') }}</RouterLink
                                >
                                <div
                                    @click="logout"
                                    class="items-center cursor-pointer flex gap-2 px-4 py-3 rounded-b-md hover:bg-red-100 text-red-500 border-t-1 border-gray-100"
                                >
                                    <Signout />
                                    <p class="font-medium text-sm">{{ t('header.profileDropdown.signOut') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notification Button and Dropdown -->
                        <div class="relative" ref="notificationDropdownWrapper">
                            <ResponsiveButton
                                @click="toggleNotificationDropdown"
                                class="p-2"
                                type="dark-gray"
                                :hasAlert="unreadNotificationsCount > 0"
                            >
                                <Notification />
                            </ResponsiveButton>
                            <div
                                v-if="notificationDropdownVisible"
                                class="absolute mt-5 w-80 sm:w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-20"
                                :class="$i18n.locale === 'fa' ? '-left-5' : '-right-5'"
                            >
                                <div class="flex justify-between items-center p-3 border-b border-gray-200">
                                    <h3 class="font-semibold text-gray-800">
                                        {{ t('header.notificationDropdown.title') }}
                                    </h3>
                                    <ResponsiveButton
                                        type="light-gray"
                                        class="px-3 py-1 text-xs"
                                        @click="markAllAsRead"
                                        v-if="unreadNotificationsCount > 0"
                                        >{{ t('header.notificationDropdown.markAllRead') }}</ResponsiveButton
                                    >
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    <div
                                        v-for="notification in notifications"
                                        :key="notification.id"
                                        class="flex items-start justify-between p-3 border-b border-gray-100 last:border-b-0 hover:bg-gray-50"
                                        :class="{ 'bg-blue-50/50': !notification.read_at }"
                                    >
                                        <div class="pr-2">
                                            <p class="text-sm text-gray-700">{{ notification.message }}</p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ formatNotificationTime(notification.created_at) }}
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <ResponsiveButton
                                                type="light-gray"
                                                class="px-3 py-1 text-xs"
                                                @click="markAsRead(notification.id)"
                                                v-if="!notification.read_at"
                                                >{{ t('header.notificationDropdown.markRead') }}</ResponsiveButton
                                            >
                                        </div>
                                    </div>
                                    <div v-if="!notifications.length" class="p-8 text-center text-sm text-gray-500">
                                        {{ t('header.notificationDropdown.noNotifications') }}
                                    </div>
                                </div>
                                <div class="p-2 border-t border-gray-200 text-center">
                                    <RouterLink
                                        :to="`/${userRole}/notification`"
                                        class="text-sm font-medium text-blue-600 hover:underline"
                                        >{{ t('header.notificationDropdown.seeAll') }}</RouterLink
                                    >
                                </div>
                            </div>
                        </div>
                        <!-- NEW Language Toggle Button -->
                        <LanguageSwitcher />
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
import { useI18n } from 'vue-i18n'
import { getRole } from '@/utils/role'
import DashAyhootTutorLogo from '@/components/atoms/icons/DashAyhootTutorLogo.vue'
import UpgradeButton from '@/components/atoms/button/UpgradeButton.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import Notification from '@/components/atoms/icons/Notification.vue'
import Signout from '@/components/atoms/icons/Signout.vue'
import Create from '@/components/atoms/icons/Create.vue'
import Search from '@/components/atoms/icons/Search.vue'
import SearchWithCategory from '@/components/molecules/Search/SearchWithCategory.vue'
import { apiCall } from "@/services/apiService"
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'Header',
    components: {
        DashAyhootTutorLogo,
        UpgradeButton,
        AyButton,
        ResponsiveButton,
        Notification,
        Signout,
        Create,
        Search,
        SearchWithCategory,
        LanguageSwitcher,
    },
    props: {
        instituteName: { type: String, default: '' },
        instituteUsername: { type: String, default: '' },
    },
    setup() {
        const { t } = useI18n()
        return { t }
    },
    data() {
        return {
            pdropdown: false,
            createDropdownVisible: false,
            notificationDropdownVisible: false,
            searchMode: false,
            institute: {},
            notifications: [],
        }
    },
    computed: {
        languageToggleText() {
            return this.$i18n.locale === 'en' ? 'فا' : 'En'
        },
        instituteAvatarUrl() {
            const avatar = this.institute?.avatar
            if (!avatar) return null
            return avatar.replace(/^https?:\/\/[^\/]+/, '')
        },
        instituteInitials() {
            // Changed from teacherInitials
            if (!this.institute || !this.institute.name) return '?'
            const nameParts = this.institute.name.trim().split(' ').filter(Boolean)
            if (nameParts.length === 0) return '?'
            if (nameParts.length === 1) return nameParts[0].charAt(0).toUpperCase()
            return (nameParts[0].charAt(0) + nameParts[nameParts.length - 1].charAt(0)).toUpperCase()
        },
        unreadNotificationsCount() {
            return this.notifications.filter((n) => !n.read_at).length
        },
        userRole() {
            return getRole()
        },
    },
    async mounted() {
        try {
            const response = await apiCall('GET', `/api/instructor/dashboard`)
            if (response.teacher) {
                this.institute = response.teacher
                localStorage.setItem('user', JSON.stringify(response.teacher))
            }
        } catch (error) {
            console.error('Failed to fetch dashboard data, falling back to localStorage.', error)
            const user = JSON.parse(localStorage.getItem('user'))
            if (user) {
                this.institute = user
            } else {
                this.institute = { name: this.instituteName, username: this.instituteUsername }
                this.institute = { name: this.instituteName, username: this.instituteUsername } // Data property and props changed
            }
        }
        await this.fetchNotifications()
        document.addEventListener('click', this.handleClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside)
    },
    methods: {
        async fetchNotifications() {
            try {
                const response = await apiCall('GET', `/api/instructor/notifications`)
                this.notifications = response.notifications
            } catch (error) {
                console.error('Failed to fetch notifications:', error)
            }
        },
        async markAsRead(notificationId) {
            try {
                await apiCall('GET', `/api/instructor/notifications/${notificationId}/read`)
                await this.fetchNotifications()
            } catch (error) {
                console.error('Failed to mark notification as read:', error)
            }
        },
        async markAllAsRead() {
            try {
                await apiCall('GET', `/api/instructor/notifications/read`)
                await this.fetchNotifications()
            } catch (error) {
                console.error('Failed to mark all notifications as read:', error)
            }
        },
        formatNotificationTime(timestamp) {
            const date = new Date(timestamp)
            const now = new Date()
            const diffSeconds = Math.floor((now - date) / 1000)
            const diffMinutes = Math.floor(diffSeconds / 60)
            const diffHours = Math.floor(diffMinutes / 60)
            const diffDays = Math.floor(diffHours / 24)

            if (diffSeconds < 60) {
                return this.t('header.notifications.timestamps.secondsAgo', { count: diffSeconds })
            } else if (diffMinutes < 60) {
                return this.t('header.notifications.timestamps.minutesAgo', { count: diffMinutes })
            } else if (diffHours < 24) {
                return this.t('header.notifications.timestamps.hoursAgo', { count: diffHours })
            } else if (diffDays < 7) {
                return this.t('header.notifications.timestamps.daysAgo', { count: diffDays })
            } else {
                return date.toLocaleDateString(this.$i18n.locale, { month: 'short', day: 'numeric' })
            }
        },
        togglePdropdown() {
            this.pdropdown = !this.pdropdown
        },
        toggleCreateDropdown() {
            this.createDropdownVisible = !this.createDropdownVisible
        },
        toggleNotificationDropdown() {
            this.notificationDropdownVisible = !this.notificationDropdownVisible
        },
        toggleLanguage() {
            const newLocale = this.$i18n.locale === 'en' ? 'fa' : 'en'
            this.$i18n.locale = newLocale
            const d = new Date()
            d.setTime(d.getTime() + 365 * 24 * 60 * 60 * 1000)
            let expires = 'expires=' + d.toUTCString()
            document.cookie = `lang=${this.$i18n.locale};${expires};path=/`
        },
        handleClickOutside(event) {
            if (this.pdropdown && this.$refs.pdropdownwrapper && !this.$refs.pdropdownwrapper.contains(event.target)) {
                this.pdropdown = false
            }
            if (
                this.createDropdownVisible &&
                this.$refs.createDropdownWrapper &&
                !this.$refs.createDropdownWrapper.contains(event.target)
            ) {
                this.createDropdownVisible = false
            }
            if (
                this.notificationDropdownVisible &&
                this.$refs.notificationDropdownWrapper &&
                !this.$refs.notificationDropdownWrapper.contains(event.target)
            ) {
                this.notificationDropdownVisible = false
            }
        },
        async logout() {
            if (await confirmDialog({ message: this.t('header.logoutConfirm'), variant: 'danger' })) {
                localStorage.removeItem('token')
                localStorage.removeItem('user')
                localStorage.removeItem('role')
                this.$router.push({ name: 'Login' })
            }
        },
        toggleSearchMode() {
            this.searchMode = !this.searchMode
        },
    },
}
</script>
