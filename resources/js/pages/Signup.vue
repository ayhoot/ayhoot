<template>
    <PublicLayout>
        <!-- Fixed Background Blobs -->
        <div
            class="fixed w-96 h-96 bg-gray-300 rounded-full opacity-30 pointer-events-none"
            style="top: 10%; inset-inline-start: -10%; transform: rotate(25deg)"
        ></div>
        <div
            class="fixed w-96 h-96 bg-gray-300 rounded-lg opacity-20 pointer-events-none"
            style="top: 40%; inset-inline-end: -10%; transform: rotate(-15deg)"
        ></div>

        <!-- Content Wrapper -->
        <div class="flex flex-col items-center justify-center w-full min-h-[calc(100vh-4rem)] z-10 pb-12 px-4">
            <div class="bg-white p-8 rounded-2xl shadow-sm w-full max-w-sm">
                <h1 class="text-2xl font-bold text-center mb-6">{{ t('signup.title') }}</h1>

                <!-- Role Selection -->
                <div v-if="!role" class="mb-6">
                    <label class="block text-xs font-bold mb-4 text-start">{{ t('signup.role.label') }}</label>
                    <div class="grid grid-cols-3 gap-4">
                        <div
                            @click="role = 'student'"
                            :class="[
                                'cursor-pointer p-4 rounded-lg border-2 transition-all duration-200 hover:border-blue-500',
                                role === 'student' ? 'border-blue-500 bg-blue-50' : 'border-gray-200',
                            ]"
                        >
                            <div class="flex flex-col items-center text-center">
                                <svg
                                    class="w-8 h-8 mb-2"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"
                                    />
                                </svg>
                                <span class="text-xs font-medium">{{ t('signup.role.student') }}</span>
                            </div>
                        </div>
                        <div
                            @click="role = 'teacher'"
                            :class="[
                                'cursor-pointer p-4 rounded-lg border-2 transition-all duration-200 hover:border-blue-500',
                                role === 'teacher' ? 'border-blue-500 bg-blue-50' : 'border-gray-200',
                            ]"
                        >
                            <div class="flex flex-col items-center text-center">
                                <svg
                                    class="w-8 h-8 mb-2"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"
                                    />
                                </svg>
                                <span class="text-xs font-medium">{{ t('signup.role.teacher') }}</span>
                            </div>
                        </div>
                        <div
                            @click="role = 'institute'"
                            :class="[
                                'cursor-pointer p-4 rounded-lg border-2 transition-all duration-200 hover:border-blue-500',
                                role === 'institute' ? 'border-blue-500 bg-blue-50' : 'border-gray-200',
                            ]"
                        >
                            <div class="flex flex-col items-center text-center">
                                <svg
                                    class="w-8 h-8 mb-2"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"
                                    />
                                </svg>
                                <span class="text-xs font-medium">{{ t('signup.role.institute') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Section -->
                <form v-if="role" @submit.prevent="handleSubmit" class="text-start space-y-3">
                    <div>
                        <label for="identifier" class="block text-xs font-bold">{{ t('signup.emailLabel') }}</label>
                        <input
                            type="text"
                            id="identifier"
                            v-model="identifier"
                            :placeholder="t('signup.emailPlaceholder')"
                            class="mt-1 block w-full px-3 py-2 border border-black rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        />
                        <span v-if="errors.email" class="text-red-500 text-xs">{{ errors.email[0] }}</span>
                        <span v-if="errors.phone" class="text-red-500 text-xs">{{ errors.phone[0] }}</span>
                        <span v-if="errors.identifier" class="text-red-500 text-xs">{{ errors.identifier[0] }}</span>
                    </div>

                    <div>
                        <label for="username" class="block text-xs font-bold">{{ t('signup.usernameLabel') }}</label>
                        <input
                            type="text"
                            id="username"
                            v-model="username"
                            @input="checkUsername"
                            :placeholder="t('signup.usernamePlaceholder')"
                            :class="[
                                'mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm',
                                usernameAvailable === false ? 'border-red-500' : 'border-black',
                            ]"
                        />
                        <span v-if="errors.username" class="text-red-500 text-xs">{{ errors.username[0] }}</span>
                        <p v-if="usernameAvailable === false" class="mt-1 text-xs text-red-500">
                            {{ t('signup.usernameTaken') }}
                        </p>
                    </div>

                    <div class="relative">
                        <label for="password" class="block text-xs font-bold">{{ t('signup.passwordLabel') }}</label>
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            v-model="password"
                            :placeholder="t('signup.passwordPlaceholder')"
                            :class="[
                                'mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm',
                                errors.password ? 'border-red-500' : 'border-black',
                            ]"
                        />
                        <!-- Password Toggle Icon -->
                        <button
                            type="button"
                            @click="togglePassword"
                            class="absolute end-3 top-8 transform text-gray-500"
                        >
                            <svg
                                v-if="showPassword"
                                width="20"
                                height="20"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M15.1643 12.0523C15.1643 13.7983 13.7483 15.2143 12.0023 15.2143C10.2563 15.2143 8.84033 13.7983 8.84033 12.0523C8.84033 10.3053 10.2563 8.89026 12.0023 8.89026C13.7483 8.89026 15.1643 10.3053 15.1643 12.0523Z"
                                    stroke="black"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M2.75024 12.0522C2.75024 15.3322 6.89224 19.3542 12.0022 19.3542C17.1112 19.3542 21.2542 15.3352 21.2542 12.0522C21.2542 8.76924 17.1112 4.75024 12.0022 4.75024C6.89224 4.75024 2.75024 8.77224 2.75024 12.0522Z"
                                    stroke="black"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <svg
                                v-else
                                width="20"
                                height="20"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M6.42 17.7299C4.19 16.2699 2.75 14.0699 2.75 12.1399C2.75 8.85991 6.89 4.8399 12 4.8399C14.09 4.8399 16.03 5.5099 17.59 6.5499"
                                    stroke="black"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M19.8496 8.61029C20.7406 9.74029 21.2596 10.9903 21.2596 12.1403C21.2596 15.4203 17.1096 19.4403 11.9996 19.4403C11.0896 19.4403 10.2006 19.3103 9.36963 19.0803"
                                    stroke="black"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M9.7656 14.3671C9.1706 13.7781 8.8376 12.9751 8.8406 12.1381C8.8366 10.3931 10.2486 8.97512 11.9946 8.97212C12.8346 8.97012 13.6406 9.30312 14.2346 9.89712"
                                    stroke="black"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M15.1095 12.6992C14.8755 13.9912 13.8645 15.0042 12.5725 15.2412"
                                    stroke="black"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M19.8917 4.25L4.11768 20.024"
                                    stroke="black"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                        <span v-if="errors.password" class="text-red-500 text-xs">{{ errors.password[0] }}</span>
                    </div>

                    <div>
                        <label for="city" class="block text-xs font-bold">{{ t('signup.cityLabel') }}</label>
                        <select
                            id="city"
                            v-model="cityId"
                            class="mt-1 block w-full px-3 py-2 border border-black rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                            <option value="">{{ t('signup.cityPlaceholder') }}</option>
                            <option v-for="city in cities" :key="city.id" :value="city.id">
                                {{ currentLocale === 'fa' ? city.name_fa : city.name_en }}
                            </option>
                        </select>
                        <span v-if="errors.city_id" class="text-red-500 text-xs">{{ errors.city_id[0] }}</span>
                    </div>

                    <AyButton
                        class="py-2 w-full mt-4"
                        type="dark-blue"
                        :handleClick="handleSubmit"
                        :disabled="isSubmitting"
                    >
                        {{ isSubmitting ? t('signup.submitting') : t('signup.submitButton') }}
                    </AyButton>
                </form>

                <div class="flex items-center py-4">
                    <div class="flex-1 border-t border-gray-300"></div>
                    <span class="px-3 text-gray-500 text-xs">{{ t('signup.orSeparator') }}</span>
                    <div class="flex-1 border-t border-gray-300"></div>
                </div>

                <!-- Google Button (Disabled with Hover Tooltip) -->
                <div class="relative group w-full">
                    <button
                        disabled
                        type="button"
                        class="w-full py-3 px-4 border border-gray-300 text-gray-400 text-sm font-medium rounded-md flex items-center justify-around cursor-not-allowed opacity-60 grayscale"
                    >
                        <GoogleAccount />
                        {{ t('signup.continueWithGoogle') }}
                    </button>
                    <!-- Tooltip -->
                    <div
                        class="absolute invisible group-hover:visible bg-gray-800 text-white text-xs py-1 px-2 rounded -top-10 left-1/2 transform -translate-x-1/2 whitespace-nowrap z-[60] after:content-[''] after:absolute after:top-full after:left-1/2 after:-translate-x-1/2 after:border-8 after:border-x-transparent after:border-b-transparent after:border-t-gray-800"
                    >
                        {{ locale === 'fa' ? 'هنوز در دسترس نیست' : 'Not available yet' }}
                    </div>
                </div>

                <p class="text-center flex items-center justify-center text-sm text-gray-500 py-4">
                    {{ t('signup.hasAccount') }}
                    <NavDropdown class="ms-1" to="/login">{{ t('signup.loginLink') }}</NavDropdown>
                </p>
            </div>

            <!-- Footer links inside main to scroll with content -->
            <div class="mt-6 text-center w-full max-w-sm text-sm text-gray-600 px-4">
                <p>
                    {{ t('signup.terms.prefix') }}
                    <RouterLink class="font-semibold underline hover:text-black" to="/terms-of-service">{{
                        t('signup.terms.link1')
                    }}</RouterLink>
                    {{ t('signup.terms.middle') }}
                    <RouterLink class="font-semibold underline hover:text-black" to="/privacy-policy">{{
                        t('signup.terms.link2')
                    }}</RouterLink>
                    {{ t('signup.terms.and') }}
                    <RouterLink class="font-semibold underline hover:text-black" to="/privacy-policy">{{
                        t('signup.terms.link3')
                    }}</RouterLink
                    >.
                </p>
            </div>
        </div>
    </PublicLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import GoogleAccount from '@/components/atoms/icons/GoogleAccount.vue'
import NavDropdown from '@/components/molecules/dropdown/NavDropdown.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toast-notification'
import i18n from '../utils/i18n'

export default {
    name: 'Signup',
    components: { GoogleAccount, NavDropdown, AyButton, PublicLayout },
    setup() {
        const { t, locale } = useI18n()
        const $toast = useToast()
        return { t, locale, $toast }
    },
    data() {
        return {
            identifier: '',
            password: '',
            showPassword: false,
            username: '',
            role: '',
            cityId: '',
            cities: [],
            usernameAvailable: null,
            usernameCheckTimeout: null,
            errors: {},
            isSubmitting: false,
        }
    },
    computed: {
        currentLocale() {
            return i18n.global.locale.value
        },
    },
    mounted() {
        this.loadCities()
    },
    methods: {
        togglePassword() {
            this.showPassword = !this.showPassword
        },
        async loadCities() {
            try {
                const response = await apiCall('GET', '/api/cities')
                this.cities = response.cities || []
            } catch (error) {}
        },
        checkUsername() {
            clearTimeout(this.usernameCheckTimeout)
            this.usernameCheckTimeout = setTimeout(async () => {
                if (this.username.length < 1 || !this.role) {
                    this.usernameAvailable = null
                    return
                }
                const response = await apiCall('POST', '/api/check-username', {
                    username: this.username,
                    role: this.role,
                })
                this.usernameAvailable = response.available
            }, 500)
        },
        async handleSubmit() {
            try {
                this.isSubmitting = true
                this.errors = {}

                if (!this.identifier.trim()) {
                    this.errors = { identifier: ['Email or phone number is required'] }
                    return
                }

                const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.identifier)
                const payload = {
                    password: this.password,
                    username: this.username,
                    role: this.role,
                    city_id: this.cityId || null,
                }

                if (isEmail) {
                    payload.email = this.identifier
                } else {
                    payload.phone = this.identifier
                }

                const response = await apiCall('POST', '/api/signup', payload)
                if (response.success) {
                    this.$toast.success(this.t('signup.alerts.emailSent'))
                    this.$router.push({
                        path: '/verify',
                        query: { email: this.identifier },
                    })
                }
            } catch (error) {
                const data = error.response?.data || error.response
                if (data?.errors) {
                    this.errors = data.errors
                }
            } finally {
                this.isSubmitting = false
            }
        },
        loginWithGoogle() {
            // Button is disabled, but keeping the method structure
        },
    },
}
</script>

<style scoped>
/* Ensure the background blobs don't interfere with interaction */
.pointer-events-none {
    pointer-events: none;
}

/* Custom transition for role cards */
.cursor-pointer:hover {
    transform: translateY(-2px);
}
</style>
