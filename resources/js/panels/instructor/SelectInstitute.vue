<template>
    <div class="fixed inset-0 flex flex-col items-center justify-center bg-gray-100 p-4">
        <div
            class="absolute w-96 h-96 bg-indigo-200 rounded-full opacity-30 -top-10 -left-20 transform rotate-45"
        ></div>
        <div
            class="absolute w-96 h-96 bg-purple-200 rounded-lg opacity-20 -bottom-20 -right-10 transform -rotate-12"
        ></div>

        <LoginNavBar class="absolute top-0 left-0 right-0" />

        <!-- Added 'mt-24' for top margin -->
        <div class="flex flex-col items-center justify-center overflow-y-auto">
            <div
                class="bg-white/80 backdrop-blur-sm p-6 sm:p-8 rounded-2xl shadow-lg w-full max-w-3xl z-10 sm:mt-0 mt-24"
            >
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $t('selectInstitute.title') }}</h1>
                    <p class="text-gray-600">{{ $t('selectInstitute.subtitle') }}</p>
                </div>

                <div v-if="isLoading" class="py-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6 animate-pulse">
                        <div
                            v-for="n in 2"
                            :key="n"
                            class="relative flex flex-col items-center text-center p-6 bg-white border-2 border-gray-200 rounded-xl"
                        >
                            <div class="w-16 h-16 rounded-full bg-gray-200 mb-4"></div>
                            <div class="h-6 bg-gray-300 rounded w-3/4 mb-2"></div>
                            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                    <button
                        v-for="institute in institutes"
                        :key="institute.id"
                        @click="selectInstitute(institute.id)"
                        class="group relative flex flex-col items-center text-center p-6 bg-white border-2 border-gray-200 rounded-xl transition-all duration-300 hover:border-blue-500 hover:shadow-xl hover:-translate-y-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 cursor-pointer"
                    >
                        <div
                            class="w-16 h-16 rounded-full flex items-center justify-center mb-4 bg-gradient-to-br from-blue-50 to-blue-200 group-hover:from-blue-500 group-hover:to-blue-700 transition-all duration-300"
                        >
                            <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-8 h-8 text-blue-500 group-hover:text-white transition-colors duration-300"
                            >
                                <path
                                    d="M3.9999 14.9999V12.3312M9.33323 14.9999V12.3312M14.6666 14.9999V12.3312M19.9999 14.9999V12.3312M2.3999 18.3999H21.5999V21.5999H2.3999V18.3999ZM2.3999 8.7999V6.66657L11.6054 2.3999L21.5999 6.66657V8.7999H2.3999Z"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                        <h3 class="font-bold text-lg text-gray-800">{{ institute.name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $t('selectInstitute.selectButton') }}</p>
                    </button>

                    <button
                        @click="selectInstitute(null)"
                        class="group relative flex flex-col items-center text-center p-6 bg-white border-2 border-dashed border-gray-300 rounded-xl transition-all duration-300 hover:border-gray-400 hover:bg-gray-50 hover:shadow-lg hover:-translate-y-1.5 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50"
                    >
                        <div
                            class="w-16 h-16 rounded-full flex items-center justify-center mb-4 bg-gray-100 group-hover:bg-gray-200 transition-colors duration-300"
                        >
                            <svg
                                class="w-8 h-8 text-gray-500 transition-colors duration-300"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                ></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-lg text-gray-700">{{ $t('selectInstitute.freelancerTitle') }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $t('selectInstitute.freelancerSubtitle') }}</p>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { apiCall } from "@/services/apiService"
import { getRole } from '@/utils/role'
import LoginNavBar from '@/components/organisms/nav/LoginNavBar.vue'

export default {
    name: 'SelectInstitute',
    components: {
        LoginNavBar,
    },
    data() {
        return {
            institutes: [],
            isLoading: true,
        }
    },
    created() {
        const user = JSON.parse(localStorage.getItem('user'))
        if (user && user.institutes) {
            this.institutes = user.institutes
        }
        setTimeout(() => {
            this.isLoading = false
        }, 500)
    },
    methods: {
        async selectInstitute(instituteId) {
            this.isLoading = true
            try {
                const response = await apiCall('POST', '/api/select-institute', {
                    institute_id: instituteId,
                })
                localStorage.setItem('token', response.token)
                if (instituteId) {
                    localStorage.setItem('selectedInstitute', instituteId)
                } else {
                    localStorage.removeItem('selectedInstitute')
                }
                this.$router.push(`/${getRole()}/dashboard`)
            } catch (error) {
                console.error('Failed to select institute:', error)
                this.isLoading = false
            }
        },
    },
}
</script>

<style scoped>
@keyframes pulse {
    50% {
        opacity: 0.5;
    }
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
