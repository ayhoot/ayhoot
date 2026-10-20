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
                <h1 class="text-2xl font-bold text-center mb-6">{{ t('resetPassword.title') }}</h1>
                <p class="text-gray-600 text-sm text-center mb-6">
                    {{ t('resetPassword.description') }}
                </p>

                <form @submit.prevent="handleSubmit" class="text-start">
                    <div v-if="generalError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                        <p class="text-red-600 text-sm text-start">{{ generalError }}</p>
                    </div>

                    <div class="py-1">
                        <label for="email" class="block text-xs font-bold">{{ t('resetPassword.emailLabel') }}</label>
                        <input
                            type="email"
                            id="email"
                            v-model="email"
                            :placeholder="t('resetPassword.emailPlaceholder')"
                            :class="[
                                'mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm',
                                errors.email ? 'border-red-500' : 'border-black',
                            ]"
                            @input="clearErrors"
                        />
                        <span v-if="errors.email" class="text-red-500 text-sm">{{ errors.email[0] }}</span>
                    </div>

                    <AyButton
                        class="py-2 w-full mt-4"
                        type="dark-blue"
                        :handleClick="handleSubmit"
                        :disabled="isSubmitting || !email"
                    >
                        {{ isSubmitting ? t('resetPassword.sending') : t('resetPassword.sendCode') }}
                    </AyButton>
                </form>

                <div class="mt-6 text-center">
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
    name: 'ResetPassword',
    components: { NavDropdown, AyButton, PublicLayout },
    setup() {
        const { t } = useI18n()
        const $toast = useToast()
        return { t, $toast }
    },
    data() {
        return {
            email: '',
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
            if (!this.email) return

            try {
                this.isSubmitting = true
                this.clearErrors()

                const response = await apiCall('POST', '/api/request-password-reset', {
                    email: this.email,
                })

                if (response.success) {
                    this.$toast.success(this.t('resetPassword.codeSentSuccess'))
                    this.$router.push({
                        path: '/reset-password/verify',
                        query: { email: this.email },
                    })
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
    },
}
</script>
