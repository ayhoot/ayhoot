<template>
    <PublicLayout nav="login">
        <div
            class="relative flex flex-col items-center justify-center min-h-[calc(100vh-4rem)] bg-gray-100 overflow-hidden"
        >
            <div
                class="absolute w-100 h-100 bg-gray-300 rounded-full opacity-30"
                style="top: 10%; left: -10%; transform: rotate(25deg)"
            ></div>
            <div
                class="absolute w-100 h-100 bg-gray-300 rounded-lg opacity-20"
                style="top: 40%; right: -10%; transform: rotate(-15deg)"
            ></div>
            <div class="bg-white p-8 rounded-2xl shadow-sm w-full max-w-sm z-50">
                <h1 class="text-2xl font-bold text-center mb-6">
                    {{ t('verify.title') }}
                </h1>
                <p class="text-gray-600 text-sm text-center mb-6">
                    {{ t('verify.description') }}<br />
                    <span class="font-semibold">{{ email }}</span>
                </p>

                <form @submit.prevent="handleSubmit">
                    <div v-if="generalError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                        <p class="text-red-600 text-sm">{{ generalError }}</p>
                    </div>

                    <div class="py-1">
                        <label for="code" class="block text-xs font-bold">{{ t('verify.codeLabel') }}</label>
                        <input
                            type="text"
                            id="code"
                            v-model="code"
                            maxlength="5"
                            :placeholder="t('verify.codePlaceholder')"
                            :class="[
                                'mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 sm:text-sm',
                                errors.code || validationErrors.code
                                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                    : 'border-black',
                            ]"
                            @input="handleInput"
                        />

                        <div class="mt-1">
                            <p v-if="validationErrors.code" class="text-red-500 text-xs">
                                {{ validationErrors.code[0] }}
                            </p>
                            <p v-if="validationErrors.email" class="text-red-500 text-xs">
                                {{ validationErrors.email[0] }}
                            </p>
                            <p v-if="errors.code" class="text-red-500 text-xs">
                                {{ errors.code }}
                            </p>
                        </div>
                    </div>

                    <AyButton
                        class="py-2 w-full mt-4"
                        type="dark-blue"
                        :handleClick="handleSubmit"
                        :disabled="isSubmitting || !isFormValid"
                    >
                        {{ isSubmitting ? t('verify.verifying') : t('verify.submitButton') }}
                    </AyButton>
                </form>

                <p class="text-center text-sm text-gray-500 mt-4">
                    {{ t('verify.noCode') }}
                    <button
                        @click="resendCode"
                        :disabled="isResending"
                        class="text-indigo-600 hover:text-indigo-500 font-medium disabled:opacity-50"
                    >
                        {{ isResending ? t('verify.resending') : t('verify.resendButton') }}
                    </button>
                </p>
            </div>
        </div>
    </PublicLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import AyButton from '@/components/atoms/button/AyButton.vue'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toast-notification'

export default {
    name: 'Verify',
    components: { AyButton, PublicLayout },
    setup() {
        const { t } = useI18n()
        const $toast = useToast()
        return { t, $toast }
    },
    data() {
        return {
            code: '',
            email: '',
            errors: {},
            validationErrors: {},
            generalError: '',
            isSubmitting: false,
            isResending: false,
        }
    },
    computed: {
        isFormValid() {
            return this.code.length === 5 && this.email && !this.hasErrors
        },
        hasErrors() {
            return Object.keys(this.errors).length > 0 || Object.keys(this.validationErrors).length > 0
        },
    },
    created() {
        this.email = this.$route.query.email
        if (!this.email) {
            this.$router.push('/signup')
        }
    },
    methods: {
        clearErrors() {
            this.errors = {}
            this.validationErrors = {}
            this.generalError = ''
        },
        handleInput(e) {
            this.code = e.target.value.replace(/[^0-9]/g, '').slice(0, 5)
            this.clearErrors()
        },
        async handleSubmit() {
            if (!this.isFormValid) return

            try {
                this.isSubmitting = true
                this.clearErrors()
                const response = await apiCall('POST', '/api/verify', {
                    email: this.email,
                    code: this.code,
                })
                if (response.success) {
                    this.$toast.success('Email verified successfully!')
                    localStorage.setItem('token', response.token)
                    localStorage.setItem('user', JSON.stringify(response.user))
                    localStorage.setItem('role', response.role)
                    if (!response.user.first_name || response.user.first_name === 'null') {
                        this.$router.push('/update-user')
                        return
                    }
                    this.$router.push(`/${response.role}/dashboard`)
                }
            } catch (error) {
                if (error.response?.errors) {
                    this.validationErrors = error.response.errors
                } else if (error.response?.message) {
                    this.generalError = error.response.message
                } else {
                    this.$toast.error('An unexpected error occurred')
                }
            } finally {
                this.isSubmitting = false
            }
        },
        async resendCode() {
            try {
                this.isResending = true
                this.clearErrors()

                const response = await apiCall('POST', '/api/resend-verification', {
                    email: this.email,
                })

                if (response.success) {
                    this.$toast.success('Verification code resent!')
                    this.code = ''
                }
            } catch (error) {
                if (error.response?.message) {
                    this.generalError = error.response.data.message
                    this.$toast.error(error.response.data.message)
                } else {
                    this.generalError = 'Failed to resend verification code'
                    this.$toast.error('Failed to resend verification code')
                }
            } finally {
                this.isResending = false
            }
        },
    },
}
</script>
