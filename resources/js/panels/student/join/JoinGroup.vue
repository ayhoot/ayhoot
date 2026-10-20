<template>
    <div
        class="fixed inset-0 flex flex-col items-center justify-center bg-gray-100 overflow-y-auto p-4"
        :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'"
    >
        <!-- Background Decorations -->
        <div class="absolute w-96 h-96 bg-indigo-200 rounded-full opacity-30 -top-10 -left-20 -z-10"></div>
        <div class="absolute w-96 h-96 bg-purple-200 rounded-lg opacity-20 -bottom-20 -right-10 -z-10"></div>

        <LoginNavBar class="fixed top-0 left-0 right-0 z-50" />

        <div class="w-full max-w-lg pt-20 sm:pt-0">
            <!-- 1. Loading State Skeleton -->
            <div v-if="isPageLoading" class="bg-white p-8 rounded-xl shadow-md w-full text-center animate-pulse">
                <div class="h-8 bg-gray-200 rounded w-3/4 mx-auto mb-3"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto mb-8"></div>
                <div class="h-4 bg-gray-200 rounded w-full mb-2"></div>
                <div class="h-12 bg-gray-300 rounded-lg w-full"></div>
            </div>

            <!-- 2. Confirmation State (Token is valid, user needs to click Join) -->
            <div
                v-else-if="isTokenValid && group && !joinSuccess"
                class="bg-white p-8 rounded-xl shadow-md w-full text-center"
            >
                <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $t('joinGroup.confirm.title') }}</h1>
                <p class="text-gray-600 mb-6">
                    <!-- Changed group.name to group.title based on your API -->
                    {{ $t('joinGroup.confirm.description', { groupName: group.title }) }}
                </p>

                <!-- Teacher Info Section (Added v-if check because your API response is missing the teacher object) -->
                <div
                    v-if="group.teacher"
                    class="border-t border-gray-200 pt-4 mt-4 flex items-center gap-3 justify-center"
                >
                    <img
                        :src="getAvatarUrl(group.teacher.avatar)"
                        :alt="group.teacher.name"
                        class="w-12 h-12 rounded-full object-cover border-2 border-blue-200"
                    />
                    <div>
                        <p class="font-semibold text-sm text-gray-700">{{ group.teacher.name }}</p>
                        <p class="text-xs text-gray-500">@{{ group.teacher.username }}</p>
                    </div>
                </div>

                <!-- <ResponsiveButton
                    @click="joinGroup"
                    :disabled="isJoining"
                    type="dash-blue"
                    class="w-full mt-8 py-3 font-bold"
                >
                    <span v-if="isJoining">{{ $t('joinGroup.confirm.joiningButton') }}</span>
                    <span v-else>{{ $t('joinGroup.confirm.actionButton') }}</span>
                </ResponsiveButton> -->
            </div>

            <!-- 3. Final Success State -->
            <div v-else-if="joinSuccess && group" class="bg-white p-8 rounded-xl shadow-md w-full text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $t('joinGroup.success.title') }}</h1>
                <p class="text-gray-600 mb-6">
                    {{ $t('joinGroup.success.description', { groupName: group.title }) }}
                </p>
                <!-- <ResponsiveButton @click="goToDashboard" type="light-green" class="w-full mt-8 py-3 font-bold">
                    {{ $t('joinGroup.success.actionButton') }}
                </ResponsiveButton> -->
            </div>

            <!-- 4. Error State -->
            <div v-else class="bg-white p-8 rounded-xl shadow-md w-full text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $t('joinGroup.error.title') }}</h1>
                <p class="text-gray-600 mb-8">{{ errorMessage || $t('joinGroup.notifications.genericError') }}</p>
                <ResponsiveButton @click="goHome" type="dash-blue" class="w-full py-3 font-bold">
                    {{ $t('joinGroup.error.actionButton') }}
                </ResponsiveButton>
            </div>
        </div>
    </div>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useToast } from 'vue-toast-notification'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import LoginNavBar from '@/components/organisms/nav/LoginNavBar.vue'

export default {
    name: 'JoinGroupPage',
    components: { ResponsiveButton, LoginNavBar },
    setup() {
        const toast = useToast()
        return { toast }
    },
    data() {
        return {
            isPageLoading: true,
            isJoining: false,
            isTokenValid: false,
            joinSuccess: false,
            errorMessage: '',
            group: null,
        }
    },
    async mounted() {
        const token = this.$route.params.token
        if (!token) {
            this.errorMessage = this.$t('joinGroup.notifications.invalidToken')
            this.isPageLoading = false
            return
        }
        await this.validateToken(token)
    },
    methods: {
        async validateToken(token) {
            this.isPageLoading = true
            try {
                // Step 1: Validate the token
                const response = await apiCall('POST', `/api/validate-token`, {
                    token: token,
                    role: 'student',
                })

                if (response && response.group) {
                    this.group = response.group
                    this.isTokenValid = true
                } else {
                    throw new Error('Invalid response format')
                }
            } catch (error) {
                console.error('Validation Error:', error)
                const errorData = error.response?.data
                this.errorMessage = errorData?.message || this.$t('joinGroup.notifications.genericError')
                this.isTokenValid = false
            } finally {
                this.isPageLoading = false
            }
        },
        async joinGroup() {
            const token = this.$route.params.token
            this.isJoining = true
            try {
                // Step 2: Join the group
                const response = await apiCall('POST', `/api/student/group/join/${token}`)

                // If the response is successful
                this.joinSuccess = true
                this.isTokenValid = false
                this.toast.success(this.$t('joinGroup.notifications.success'))
            } catch (error) {
                console.error('Join Error:', error)
                const errorData = error.response?.data
                this.errorMessage = errorData?.message || this.$t('joinGroup.notifications.genericError')
                this.toast.error(this.errorMessage)
                this.isTokenValid = false
            } finally {
                this.isJoining = false
            }
        },
        getAvatarUrl(avatarPath) {
            const fallback = '/imgHolders/profile.svg'
            if (!avatarPath) return fallback
            if (avatarPath.startsWith('http')) return avatarPath
            return `/uploads/${avatarPath.replace(/\\/g, '/')}`
        },
        goToDashboard() {
            this.$router.push('/student/dashboard')
        },
        goHome() {
            this.$router.push('/')
        },
    },
}
</script>
