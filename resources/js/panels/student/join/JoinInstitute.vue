<template>
    <div
        class="fixed inset-0 flex flex-col items-center justify-center bg-gray-100 overflow-y-auto p-4"
        :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'"
    >
        <div class="absolute w-96 h-96 bg-indigo-200 rounded-full opacity-30 -top-10 -left-20 -z-10"></div>
        <div class="absolute w-96 h-96 bg-purple-200 rounded-lg opacity-20 -bottom-20 -right-10 -z-10"></div>
        <LoginNavBar class="fixed top-0 left-0 right-0 z-50" />

        <div class="w-full max-w-lg pt-20 sm:pt-0">
            <!-- Loading State Skeleton -->
            <div v-if="isLoading" class="bg-white p-8 rounded-xl shadow-md w-full text-center animate-pulse">
                <div class="h-8 bg-gray-200 rounded w-3/4 mx-auto mb-3"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto mb-8"></div>
                <div class="h-4 bg-gray-200 rounded w-full mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-2/3 mx-auto mb-10"></div>
                <div class="h-12 bg-gray-300 rounded-lg w-full"></div>
            </div>

            <!-- Success State -->
            <div v-else-if="joinSuccess && institute" class="bg-white p-8 rounded-xl shadow-md w-full text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $t('joinInstitute.success.title') }}</h1>
                <p class="text-gray-600 mb-6">
                    {{ $t('joinInstitute.success.description', { instituteName: institute.name }) }}
                </p>
                <div class="border-t border-gray-200 pt-4 mt-4 flex items-center gap-3 justify-center">
                    <img
                        :src="getAvatarUrl(institute.logo)"
                        :alt="institute.name"
                        class="w-12 h-12 rounded-full object-cover border-2 border-blue-200"
                    />
                    <div>
                        <p class="font-semibold text-sm text-gray-700">{{ institute.name }}</p>
                        <p class="text-xs text-gray-500">@{{ institute.username }}</p>
                    </div>
                </div>
                <ResponsiveButton @click="goToDashboard" type="light-green" class="w-full mt-8 py-3 font-bold">
                    {{ $t('joinInstitute.success.actionButton') }}
                </ResponsiveButton>
            </div>

            <!-- Error State -->
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
                <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $t('joinInstitute.error.title') }}</h1>
                <p class="text-gray-600 mb-8">{{ errorMessage }}</p>
                <ResponsiveButton @click="goHome" type="dash-blue" class="w-full py-3 font-bold">
                    {{ $t('joinInstitute.error.actionButton') }}
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
    name: 'JoinInstitutePage',
    components: { ResponsiveButton, LoginNavBar },
    setup() {
        const toast = useToast()
        return { toast }
    },
    data() {
        return {
            isLoading: true,
            joinSuccess: false,
            errorMessage: '',
            institute: null,
        }
    },
    async mounted() {
        const token = this.$route.params.token
        if (!token) {
            this.errorMessage = this.$t('joinInstitute.notifications.invalidToken')
            this.isLoading = false
            return
        }
        await this.joinInstitute(token)
    },
    methods: {
        async joinInstitute(token) {
            this.isLoading = true
            try {
                // API endpoint for a TEACHER to join an INSTITUTE
                const response = await apiCall('POST', `/api/instructor/institute/join/${token}`)
                this.institute = response.institute
                this.joinSuccess = true
                this.toast.success(this.$t('joinInstitute.notifications.success'))
            } catch (error) {
                const errorData = error.response?.data
                this.errorMessage = errorData?.message || this.$t('joinInstitute.notifications.genericError')
                this.toast.error(this.errorMessage)
            } finally {
                this.isLoading = false
            }
        },
        getAvatarUrl(avatarPath) {
            const fallback = '/imgHolders/profile.svg'
            if (!avatarPath) return fallback
            if (avatarPath.startsWith('http')) return avatarPath
            return `/uploads/${avatarPath.replace(/\\/g, '')}`
        },
        goToDashboard() {
            // Redirects to the teacher's dashboard
            this.$router.push('/teacher/dashboard')
        },
        goHome() {
            this.$router.push('/')
        },
    },
}
</script>
