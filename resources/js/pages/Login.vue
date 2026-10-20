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

        <!-- Main Content Wrapper -->
        <div class="flex flex-col items-center justify-center w-full min-h-[calc(100vh-4rem)] z-10 pb-12 px-4">
            <div class="bg-white p-8 rounded-2xl shadow-sm w-full max-w-sm">
                <h1 class="text-2xl font-bold text-center mb-6">{{ t('login.title') }}</h1>

                <!-- General Error Message -->
                <div v-if="generalError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-red-600 text-sm text-start">{{ generalError }}</p>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="handleSubmit" class="text-start space-y-4">
                    <div>
                        <label for="username" class="block text-xs font-bold">{{ t('login.usernameLabel') }}</label>
                        <input
                            type="text"
                            id="username"
                            v-model="username"
                            :placeholder="t('login.usernamePlaceholder')"
                            :class="[
                                'mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 sm:text-sm',
                                errors.username ? 'border-red-500' : 'border-black',
                            ]"
                            @input="clearErrors"
                        />
                        <span v-if="errors.username" class="text-red-500 text-xs mt-1">
                            {{ errors.username[0] }}
                        </span>
                    </div>

                    <div class="relative">
                        <label for="password" class="block text-xs font-bold">{{ t('login.passwordLabel') }}</label>
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            v-model="password"
                            :placeholder="t('login.passwordPlaceholder')"
                            :class="[
                                'mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 sm:text-sm',
                                errors.password ? 'border-red-500' : 'border-black',
                            ]"
                            @input="clearErrors"
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
                        <span v-if="errors.password" class="text-red-500 text-xs mt-1">
                            {{ errors.password[0] }}
                        </span>
                    </div>

                    <div class="text-sm py-2 text-gray-500 flex items-center">
                        {{ t('login.forgotPassword') }}
                        <NavDropdown class="ms-1" to="/reset-password">{{ t('login.resetPassword') }}</NavDropdown>
                    </div>

                    <AyButton class="py-2 w-full mt-2" type="dark-blue" :disabled="isSubmitting">
                        {{ isSubmitting ? t('login.loggingIn') : t('login.loginButton') }}
                    </AyButton>
                </form>

                <!-- Separator -->
                <div class="flex items-center py-6">
                    <div class="flex-1 border-t border-gray-300"></div>
                    <span class="px-3 text-gray-500 text-xs">{{ t('login.orSeparator') }}</span>
                    <div class="flex-1 border-t border-gray-300"></div>
                </div>

                <!-- Google Button (Disabled) -->
                <div class="relative group w-full">
                    <button
                        disabled
                        type="button"
                        class="w-full py-3 px-4 border border-gray-300 text-gray-400 text-sm font-medium rounded-md flex items-center justify-around cursor-not-allowed opacity-60 grayscale"
                    >
                        <GoogleAccount />
                        {{ t('login.continueWithGoogle') }}
                    </button>
                    <!-- Tooltip -->
                    <div
                        class="absolute invisible group-hover:visible bg-gray-800 text-white text-xs py-1 px-2 rounded -top-10 left-1/2 transform -translate-x-1/2 whitespace-nowrap z-[60] after:content-[''] after:absolute after:top-full after:left-1/2 after:-translate-x-1/2 after:border-8 after:border-x-transparent after:border-b-transparent after:border-t-gray-800"
                    >
                        {{ locale === 'fa' ? 'هنوز در دسترس نیست' : 'Not available yet' }}
                    </div>
                </div>

                <!-- Redirect to Signup -->
                <p class="text-center flex items-center justify-center text-sm text-gray-500 py-6">
                    {{ t('login.noAccount') }}
                    <NavDropdown class="ms-1" to="/signup">{{ t('login.signUpLink') }}</NavDropdown>
                </p>
            </div>

            <!-- Footer Links -->
            <div class="mt-8 text-center w-full max-w-sm text-sm text-gray-600 px-4">
                <div>
                    {{ t('login.terms.prefix') }}
                    <RouterLink class="font-semibold underline hover:text-black" to="/terms-of-service">
                        {{ t('login.terms.link1') }}</RouterLink
                    >
                    {{ t('login.terms.middle') }}
                    <RouterLink class="font-semibold underline hover:text-black" to="/privacy-policy">{{
                        t('login.terms.link2')
                    }}</RouterLink>
                    {{ t('login.terms.and') }}
                    <RouterLink class="font-semibold underline hover:text-black" to="/privacy-policy">{{
                        t('login.terms.link3')
                    }}</RouterLink>
                    {{ t('login.terms.suffix') }}
                </div>
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

export default {
    name: 'Login',
    components: { GoogleAccount, NavDropdown, AyButton, PublicLayout },
    setup() {
        const { t, locale } = useI18n()
        return { t, locale }
    },
    data() {
        return {
            username: '',
            password: '',
            showPassword: false,
            errors: {},
            generalError: '',
            isSubmitting: false,
        }
    },
    methods: {
        clearErrors() {
            this.errors = {}
            this.generalError = ''
        },
        async handleSubmit() {
            try {
                this.isSubmitting = true
                this.clearErrors()

                const response = await apiCall('POST', '/api/login', {
                    username: this.username,
                    password: this.password,
                })

                if (response.success) {
                    localStorage.setItem('token', response.token)
                    localStorage.setItem('user', JSON.stringify(response.user))
                    localStorage.setItem('role', response.role)

                    if (response.role == 'institute') {
                        if (!response.user.name || response.user.name === 'null') {
                            this.$router.push('/update-user')
                            return
                        }
                    } else {
                        if (!response.user.first_name || response.user.first_name === 'null') {
                            this.$router.push('/update-user')
                            return
                        }
                    }

                    const redirectUrl = this.$route.query.redirect
                    if (redirectUrl) {
                        this.$router.push(decodeURIComponent(redirectUrl))
                    } else if (response.role == 'teacher' && response.user.institutes.length > 0) {
                        this.$router.push('/teacher/select-institute')
                    } else {
                        this.$router.push(`/${response.role}/dashboard`)
                    }
                } else {
                    this.generalError = response.message || 'An error occurred during login'
                }
            } catch (error) {
                const data = error.response?.data || error.response
                if (data?.errors) {
                    this.errors = data.errors
                } else if (data?.message) {
                    this.generalError = data.message
                } else if (error.status === 401 || error.statusCode === 401) {
                    this.generalError = this.t('login.invalidCredentials')
                } else {
                    this.generalError = this.t('login.unexpectedError') || 'An unexpected error occurred'
                }
            } finally {
                this.isSubmitting = false
            }
        },
        togglePassword() {
            this.showPassword = !this.showPassword
        },
    },
}
</script>

<style scoped>
.pointer-events-none {
    pointer-events: none;
}
</style>
