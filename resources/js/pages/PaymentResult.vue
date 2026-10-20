<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <!-- Success Icon -->
                <div v-if="isSuccess"
                    class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <!-- Error Icon -->
                <div v-else class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </div>

                <h2 class="text-3xl font-extrabold text-gray-900 mb-2">
                    {{ isSuccess ? t('payment.success.title') : t('payment.error.title') }}
                </h2>

                <p class="text-gray-600 mb-6">
                    {{ message }}
                </p>

                <!-- Success Details -->
                <div v-if="isSuccess && refId" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="text-sm text-green-800">
                        <div class="font-semibold mb-2">{{ t('payment.success.details') }}</div>
                        <div class="space-y-1">
                            <div>{{ t('payment.refId') }}: {{ refId }}</div>
                            <div v-if="contentType">{{ t('payment.contentType') }}: {{ t(`content.${contentType}`) }}
                            </div>
                            <div v-if="contentId">{{ t('payment.contentId') }}: {{ contentId }}</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <button v-if="isSuccess && contentType && contentId" @click="goToContent"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ t('payment.success.goToContent') }}
                    </button>

                    <button v-if="isSuccess" @click="goToPurchasedContent"
                        class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ t('payment.success.viewPurchased') }}
                    </button>

                    <button @click="goToDashboard"
                        class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ t('payment.backToDashboard') }}
                    </button>
                </div>

                <!-- Support Info -->
                <div class="mt-8 text-xs text-gray-500">
                    <p>{{ t('payment.support.text') }}</p>
                    <p>{{ t('payment.support.email') }}: support@ayhoot.com</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'PaymentResult',
    data() {
        return {
            isSuccess: false,
            message: '',
            refId: null,
            contentType: null,
            contentId: null
        }
    },
    computed: {
        userRole() {
            return localStorage.getItem('role') || 'student'
        }
    },
    methods: {
        parseQueryParams() {
            const params = new URLSearchParams(window.location.search)

            this.isSuccess = params.get('success') === 'true'

            // Get the raw message and decode it properly
            let rawMessage = params.get('message') || (this.isSuccess ? 'Payment completed successfully' : 'Payment failed')

            // Handle multiple levels of encoding that might exist
            try {
                // First, replace + with spaces (URL encoding standard)
                let decodedMessage = rawMessage.replace(/\+/g, ' ')

                // Try to decode URL encoding - handle potential double encoding
                try {
                    decodedMessage = decodeURIComponent(decodedMessage)
                    // Check if it's still encoded (contains %XX patterns)
                    if (decodedMessage.includes('%')) {
                        decodedMessage = decodeURIComponent(decodedMessage)
                    }
                } catch (e) {
                    // If decoding fails, use the message as is
                    console.warn('Failed to decode message:', e)
                }

                this.message = decodedMessage
            } catch (e) {
                console.error('Error parsing message:', e)
                this.message = rawMessage
            }

            this.refId = params.get('ref_id')
            this.contentType = params.get('content_type')
            this.contentId = params.get('content_id')

            // Store user type from URL params if available
            const userType = params.get('user_type')
            if (userType) {
                localStorage.setItem('role', userType)
            }

            // Log the parsed parameters for debugging
            console.log('Payment result parameters:', {
                success: this.isSuccess,
                message: this.message,
                rawMessage: rawMessage,
                refId: this.refId,
                contentType: this.contentType,
                contentId: this.contentId,
                userType: userType
            })
        },

        goToContent() {
            if (this.contentType === 'exam') {
                // Redirect to exam page for all roles
                if (this.userRole === 'student') {
                    this.$router.push(`/${this.userRole}/exam/${this.contentId}/detail`)
                } else {
                    // For teachers/institutes, redirect to main exam preview page
                    this.$router.push(`/${this.userRole}/ayhoot/${this.contentId}`)
                }
            } else if (this.contentType === 'course') {
                // For courses, redirect to course detail page
                this.$router.push(`/${this.userRole}/course/${this.contentId}`)
            }
        },

        goToPurchasedContent() {
            // Use the correct purchased content route based on user role
            if (this.userRole === 'student') {
                this.$router.push(`/${this.userRole}/exam/purchased`)
            } else {
                this.$router.push(`/${this.userRole}/purchased`)
            }
        },

        goToDashboard() {
            this.$router.push(`/${this.userRole}/dashboard`)
        },

        t(key) {
            // Simple translation function - replace with your i18n implementation
            const translations = {
                'payment.success.title': 'Payment Successful!',
                'payment.error.title': 'Payment Failed',
                'payment.success.details': 'Payment Details:',
                'payment.refId': 'Reference ID',
                'payment.contentType': 'Content Type',
                'payment.contentId': 'Content ID',
                'payment.success.goToContent': 'Go to Content',
                'payment.success.viewPurchased': 'View My Purchases',
                'payment.backToDashboard': 'Back to Dashboard',
                'payment.support.text': 'Need help? Contact our support team:',
                'payment.support.email': 'Email',
                'content.exam': 'Exam',
                'content.course': 'Course'
            }

            return translations[key] || key
        }
    },

    mounted() {
        this.parseQueryParams()

        // Auto-redirect to dashboard after 30 seconds for successful payments
        if (this.isSuccess) {
            setTimeout(() => {
                this.goToDashboard()
            }, 30000)
        }
    }
}
</script>