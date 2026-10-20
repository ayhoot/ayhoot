<template>
    <div class="fixed inset-0 flex items-center justify-center bg-gray-100 overflow-hidden p-4">
        <!-- Background decorative shapes -->
        <div class="absolute w-96 h-96 bg-indigo-200 rounded-full opacity-30 -top-10 -left-20 -z-10"></div>
        <div class="absolute w-96 h-96 bg-purple-200 rounded-lg opacity-20 -bottom-20 -right-10 -z-10"></div>

        <!-- Status Card -->
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md text-center transform transition-all duration-500"
            :class="animationClass">
            <!-- Success State -->
            <div v-if="status === 'success'" class="text-center">
                <!-- Animated Icon -->
                <div class="mx-auto mb-6">
                    <div class="w-24 h-24 rounded-full bg-green-100 flex items-center justify-center mx-auto">
                        <svg class="w-16 h-16 text-green-500" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path class="checkmark" d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-3xl font-extrabold text-gray-800 mb-3">{{ $t('paymentStatus.success.title') }}</h3>
                <p class="text-sm text-gray-600 mb-4 px-4">
                    {{ examId ? $t('paymentStatus.success.examMessage') : $t('paymentStatus.success.message') }}
                </p>
                <div v-if="examId && (role === 'teacher' || role === 'institute')"
                    class="bg-[#DCE9FF] rounded-md p-3 mb-4">
                    <p class="text-sm text-[#3450AE] font-semibold">{{ $t('paymentStatus.success.examPurchased') }}</p>
                </div>
                <div v-if="refId" class="bg-gray-50 rounded-md p-3 mb-4">
                    <p class="text-xs text-gray-500">{{ $t('paymentStatus.referenceId') }}</p>
                    <p class="font-mono text-sm">{{ refId }}</p>
                </div>
                <button @click="redirectToDashboard"
                    class="w-full bg-[#3450AE] hover:bg-[#4C8BF5] text-white font-bold py-3 px-6 rounded-lg transition-colors duration-300 disabled:opacity-50">
                    {{ getRedirectButtonText() }}
                </button>
            </div>

            <!-- Failed State -->
            <div v-else-if="status === 'failed'" class="text-center">
                <!-- Animated Icon -->
                <div class="mx-auto mb-6">
                    <div class="w-24 h-24 rounded-full bg-red-100 flex items-center justify-center mx-auto">
                        <svg class="w-16 h-16 text-red-500" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g class="cross">
                                <path d="M6 18L18 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M18 18L6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </g>
                        </svg>
                    </div>
                </div>
                <h3 class="text-3xl font-extrabold text-red-600 mb-3">{{ $t('paymentStatus.failed.title') }}</h3>
                <p class="text-sm text-gray-600 mb-8 px-4">{{ getFailedMessage() }}</p>
                <div class="space-y-2">
                    <button @click="retryPayment"
                        class="w-full bg-[#3450AE] hover:bg-[#4C8BF5] text-white font-bold py-3 px-6 rounded-lg transition-colors duration-300">
                        {{ $t('paymentStatus.tryAgain') }}
                    </button>
                    <button @click="redirectToDashboard"
                        class="w-full bg-gray-200 hover:bg-[#DCE9FF] hover:text-[#4C8BF5] text-gray-700 font-bold py-3 px-6 rounded-lg transition-colors duration-300">
                        {{ $t('paymentStatus.goToDashboard') }}
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-else class="text-center">
                <div class="mx-auto mb-6">
                    <div class="w-24 h-24 rounded-full bg-blue-100 flex items-center justify-center mx-auto">
                        <svg class="animate-spin w-16 h-16 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-3xl font-extrabold text-gray-800 mb-3">{{ $t('paymentStatus.processing.title') }}</h3>
                <p class="text-sm text-gray-600 px-4">{{ $t('paymentStatus.processing.message') }}</p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'PaymentStatus',
    data() {
        return {
            status: null,
            refId: null,
            reason: null,
            role: null,
            examId: null,
            courseId: null,
            planId: null,
            animationClass: 'opacity-0 scale-95'
        }
    },
    mounted() {
        this.parseUrlParams()

        // Trigger the entrance animation
        this.$nextTick(() => {
            setTimeout(() => {
                this.animationClass = 'opacity-100 scale-100'
            }, 100)
        })
    },
    methods: {
        parseUrlParams() {
            const urlParams = new URLSearchParams(window.location.search)

            this.status = urlParams.get('status')
            this.refId = urlParams.get('ref_id')
            this.reason = urlParams.get('reason')
            this.role = urlParams.get('role')
            this.examId = urlParams.get('exam_id')
            this.courseId = urlParams.get('course_id')
            this.planId = urlParams.get('plan_id')

            // If no status is provided, assume we're still processing
            if (!this.status) {
                this.status = 'processing'
            }
        },

        getFailedMessage() {
            const reasonMessages = {
                'missing_parameters': this.$t('paymentStatus.failed.reasons.missingParameters'),
                'transaction_not_found': this.$t('paymentStatus.failed.reasons.transactionNotFound'),
                'invoice_not_found': this.$t('paymentStatus.failed.reasons.invoiceNotFound'),
                'payment_canceled': this.$t('paymentStatus.failed.reasons.paymentCanceled')
            }

            return reasonMessages[this.reason] || this.$t('paymentStatus.failed.message')
        },

        retryPayment() {
            // Redirect back to the item page to retry payment
            const role = this.role || localStorage.getItem('role') || 'student'
            if (this.examId) {
                // Use role-specific routes that match the application structure
                if (role === 'teacher' || role === 'institute') {
                    this.$router.push(`/${role}/ayhoot/${this.examId}`)
                } else {
                    this.$router.push(`/student/exam/${this.examId}/detail`)
                }
            } else if (this.courseId) {
                if (role === 'teacher' || role === 'institute') {
                    this.$router.push(`/${role}/course/${this.courseId}`)
                } else {
                    this.$router.push(`/student/course/${this.courseId}`)
                }
            } else {
                this.redirectToDashboard()
            }
        },

        getRedirectButtonText() {
            const role = this.role || localStorage.getItem('role') || 'student'

            if ((role === 'teacher' || role === 'institute') && this.examId) {
                return this.$t('paymentStatus.goToPurchasedExams')
            } else if ((role === 'teacher' || role === 'institute') && this.courseId) {
                return this.$t('paymentStatus.goToPurchasedCourses')
            } else {
                return this.$t('paymentStatus.goToDashboard')
            }
        },

        redirectToDashboard() {
            const role = this.role || localStorage.getItem('role') || 'student'

            // If exam was purchased, redirect to exam page (not questions page)
            if (this.examId) {
                if (role === 'student') {
                    this.$router.push(`/${role}/exam/${this.examId}/detail`)
                } else {
                    // For teachers/institutes, redirect to main exam preview page
                    this.$router.push(`/${role}/ayhoot/${this.examId}`)
                }
            } else if (this.courseId) {
                // For courses, redirect to course detail page
                this.$router.push(`/${role}/course/${this.courseId}`)
            } else if ((role === 'teacher' || role === 'institute') && (this.examId || this.courseId)) {
                // Fallback: redirect to purchased content list
                this.$router.push(`/${role}/exam/purchased`)
            } else {
                this.$router.push(`/${role}/dashboard`)
            }
        }
    }
}
</script>

<style scoped>
/* Cool animations for the icons */
.checkmark {
    stroke-dasharray: 50;
    stroke-dashoffset: 50;
    animation: draw 0.5s ease-out 0.3s forwards;
}

.cross path {
    stroke-dasharray: 20;
    stroke-dashoffset: 20;
    animation: draw 0.4s ease-out 0.3s forwards;
}

@keyframes draw {
    to {
        stroke-dashoffset: 0;
    }
}
</style>