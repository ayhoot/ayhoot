<template>
    <PublicLayout>
        <div
            class="relative flex flex-col items-center justify-center min-h-[calc(100vh-4rem)] bg-gray-100 overflow-hidden"
        >
            <div
                class="absolute w-100 h-100 bg-gray-300 rounded-full opacity-30"
                style="top: 10%; inset-inline-start: -10%; transform: rotate(25deg)"
            ></div>
            <div
                class="absolute w-100 h-100 bg-gray-300 rounded-lg opacity-20"
                style="top: 40%; inset-inline-end: -10%; transform: rotate(-15deg)"
            ></div>
            <div class="bg-white p-8 rounded-2xl shadow-sm w-full max-w-sm z-50">
                <h1 class="text-2xl font-bold text-center mb-6">{{ t('resetPassword.verifyTitle') }}</h1>
                <p class="text-gray-600 text-sm text-center mb-6">
                    {{ t('resetPassword.verifyDescription') }}<br />
                    <span class="font-semibold">{{ email }}</span>
                </p>

                <form @submit.prevent="handleSubmit" class="text-start">
                    <div v-if="generalError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                        <p class="text-red-600 text-sm text-start">{{ generalError }}</p>
                    </div>

                    <div class="py-1">
                        <label for="code" class="block text-xs font-bold">{{ t('resetPassword.codeLabel') }}</label>
                        <input
                            type="text"
                            id="code"
                            v-model="code"
                            maxlength="5"
                            :placeholder="t('resetPassword.codePlaceholder')"
                            :class="[
                                'mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm',
                                errors.code ? 'border-red-500' : 'border-black',
                            ]"
                            @input="handleCodeInput"
                        />
                        <span v-if="errors.code" class="text-red-500 text-sm">{{ errors.code[0] }}</span>
                    </div>

                    <div class="py-1">
                        <label for="password" class="block text-xs font-bold">{{
                            t('resetPassword.newPasswordLabel')
                        }}</label>
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            v-model="password"
                            :placeholder="t('resetPassword.newPasswordPlaceholder')"
                            :class="[
                                'mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm',
                                errors.password ? 'border-red-500' : 'border-black',
                            ]"
                            @input="clearErrors"
                        />
                        <span v-if="errors.password" class="text-red-500 text-sm">{{ errors.password[0] }}</span>
                    </div>

                    <div class="py-1">
                        <label for="password_confirmation" class="block text-xs font-bold">{{
                            t('resetPassword.confirmPasswordLabel')
                        }}</label>
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password_confirmation"
                            v-model="passwordConfirmation"
                            :placeholder="t('resetPassword.confirmPasswordPlaceholder')"
                            :class="[
                                'mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm',
                                errors.password_confirmation ? 'border-red-500' : 'border-black',
                            ]"
                            @input="clearErrors"
                        />
                        <span v-if="errors.password_confirmation" class="text-red-500 text-sm">{{
                            errors.password_confirmation[0]
                        }}</span>
                    </div>

                    <div class="py-1 flex items-center">
                        <input type="checkbox" id="showPassword" v-model="showPassword" class="mr-2" />
                        <label for="showPassword" class="text-xs text-gray-600">{{
                            t('resetPassword.showPassword')
                        }}</label>
                    </div>

                    <AyButton
                        class="py-2 w-full mt-4"
                        type="dark-blue"
                        :handleClick="handleSubmit"
                        :disabled="isSubmitting || !isFormValid"
                    >
                        {{ isSubmitting ? t('resetPassword.resetting') : t('resetPassword.resetButton') }}
                    </AyButton>
                </form>

                <div class="mt-6 text-center">
                    <button
                        @click="resendCode"
                        :disabled="isResending"
                        class="text-sm text-blue-600 hover:text-blue-500 disabled:opacity-50"
                    >
                        {{ isResending ? t('resetPassword.resending') : t('resetPassword.resendCode') }}
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <NavDropdown class="text-sm text-gray-500" to="/login">
                        {{ t('resetPassword.backToLogin') }}
                    </NavDropdown>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import NavDropdown from '@/components/molecules/dropdown/NavDropdown.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toast-notification'

export default {
    name: 'ResetPasswordVerify',
    components: { NavDropdown, AyButton, PublicLayout },
    setup() {
        const { t } = useI18n()
        const $toast = useToast()
        return { t, $toast }
    },
    data() {
        return {
            email: '',
            code: '',
            password: '',
            passwordConfirmation: '',
            showPassword: false,
            errors: {},
            generalError: '',
            isSubmitting: false,
            isResending: false,
        }
    },
    computed: {
        isFormValid() {
            return (
                this.code.length === 5 &&
                this.password.length >= 6 &&
                this.password === this.passwordConfirmation &&
                this.email
            )
        },
    },
    created() {
        this.email = this.$route.query.email
        if (!this.email) {
            this.$router.push('/reset-password')
        }
    },
    methods: {
        clearErrors() {
            this.errors = {}
            this.generalError = ''
        },
        handleCodeInput(e) {
            this.code = e.target.value.replace(/[^0-9]/g, '').slice(0, 5)
            this.clearErrors()
        },
        async handleSubmit() {
            if (!this.isFormValid) return

            try {
                this.isSubmitting = true
                this.clearErrors()

                const response = await apiCall('POST', '/api/reset-password', {
                    email: this.email,
                    code: this.code,
                    password: this.password,
                    password_confirmation: this.passwordConfirmation,
                })

                if (response.success) {
                    this.$toast.success(this.t('resetPassword.resetSuccess'))
                    this.$router.push('/login')
                }
            } catch (error) {
                if (error.response?.errors) {
                    this.errors = error.response.errors
                } else if (error.response?.message) {
                    this.generalError = error.response.message
                } else {
                    this.$toast.error(this.t('resetPassword.error'))
                }
            } finally {
                this.isSubmitting = false
            }
        },
        async resendCode() {
            try {
                this.isResending = true
                this.clearErrors()

                const response = await apiCall('POST', '/api/request-password-reset', {
                    email: this.email,
                })

                if (response.success) {
                    this.$toast.success(this.t('resetPassword.codeResent'))
                }
            } catch (error) {
                if (error.response?.message) {
                    this.generalError = error.response.message
                    this.$toast.error(error.response.message)
                } else {
                    this.$toast.error(this.t('resetPassword.error'))
                }
            } finally {
                this.isResending = false
            }
        },
    },
}
</script>
