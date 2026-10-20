<template>
    <PublicLayout nav="login">
        <div
            class="relative flex flex-col items-center justify-center min-h-[calc(100vh-4rem)] bg-gray-100 overflow-y-auto p-4"
        >
            <div class="absolute w-96 h-96 bg-indigo-200 rounded-full opacity-30 -top-10 -left-20 -z-10"></div>
            <div class="absolute w-96 h-96 bg-purple-200 rounded-lg opacity-20 -bottom-20 -right-10 -z-10"></div>

            <!-- Main Content Wrapper -->
            <div class="w-full max-w-2xl">
                <!-- Loading State -->
                <div v-if="isLoading" class="bg-white p-8 rounded-xl shadow-md text-center animate-pulse">
                    <div class="h-8 bg-gray-200 rounded w-3/4 mx-auto mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto mb-8"></div>
                    <div class="space-y-4">
                        <div class="h-12 bg-gray-200 rounded-lg w-full"></div>
                        <div class="h-12 bg-gray-200 rounded-lg w-full"></div>
                        <div class="h-12 bg-gray-200 rounded-lg w-full"></div>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="bg-white p-8 rounded-xl shadow-md text-center">
                    <div class="text-red-500 text-lg font-semibold mb-4">{{ error }}</div>
                    <button
                        @click="$router.push('/discover')"
                        class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600"
                    >
                        Go to Discover
                    </button>
                </div>

                <!-- Universal Checkout Form -->
                <div v-else class="bg-white p-8 rounded-xl shadow-md">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Universal Checkout</h1>
                        <p class="text-gray-600">Purchase any content with a direct link or ID</p>
                    </div>

                    <!-- Content Selection Form -->
                    <CheckoutSelection
                        v-if="!selectedContent"
                        v-model:contentType="contentType"
                        v-model:contentId="contentId"
                        :isLoadingContent="isLoadingContent"
                        @load-content="loadContent"
                    />

                    <!-- Content Preview and Purchase -->
                    <div v-else class="space-y-6">
                        <CheckoutPreview
                            :content="selectedContent"
                            :contentType="contentType"
                            :ownerName="getOwnerName()"
                            :ownerAvatar="getOwnerAvatar()"
                        />

                        <CheckoutSummary
                            :basePrice="basePrice"
                            v-model:voucherCode="voucherCode"
                            :voucherDiscount="voucherDiscount"
                            :walletDeduction="walletDeduction"
                            :finalAmount="finalAmount"
                            :walletBalance="walletBalance"
                            v-model:useWalletBalance="useWalletBalance"
                            :isApplyingVoucher="isApplyingVoucher"
                            :isSubmitting="isSubmitting"
                            @apply-voucher="applyVoucher"
                            @reset="resetSelection"
                            @pay="proceedToPayment"
                        />
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useToast } from 'vue-toast-notification'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import CheckoutSelection from '../components/payment/CheckoutSelection.vue'
import CheckoutPreview from '../components/payment/CheckoutPreview.vue'
import CheckoutSummary from '../components/payment/CheckoutSummary.vue'

export default {
    name: 'UniversalCheckoutPage',
    components: { PublicLayout, CheckoutSelection, CheckoutPreview, CheckoutSummary },
    setup() {
        const toast = useToast()
        return { toast }
    },
    data() {
        return {
            isLoading: false,
            error: null,
            contentType: '',
            contentId: '',
            selectedContent: null,
            isLoadingContent: false,
            isSubmitting: false,
            voucherCode: '',
            voucherDiscount: 0,
            isApplyingVoucher: false,
            useWalletBalance: false,
            walletBalance: 0,
        }
    },
    computed: {
        userRole() {
            return localStorage.getItem('role') || 'student'
        },
        basePrice() {
            return this.selectedContent ? parseFloat(this.selectedContent.price) : 0
        },
        priceAfterDiscounts() {
            return this.basePrice - this.voucherDiscount
        },
        walletDeduction() {
            if (!this.useWalletBalance) return 0
            return Math.min(this.priceAfterDiscounts, this.walletBalance)
        },
        finalAmount() {
            const final = this.priceAfterDiscounts - this.walletDeduction
            return Math.max(0, final)
        },
    },
    async mounted() {
        await this.initializeWallet()

        const { type, id } = this.$route.query
        if (type && id) {
            this.contentType = type
            this.contentId = id
            await this.loadContent()
        }
    },
    methods: {
        async initializeWallet() {
            try {
                const user = JSON.parse(localStorage.getItem('user') || '{}')
                this.walletBalance = user.wallet ? parseFloat(user.wallet) : 0
            } catch (error) {
                console.error('Failed to load wallet balance:', error)
            }
        },

        async loadContent() {
            if (!this.contentType || !this.contentId) {
                this.toast.error('Please select content type and enter content ID')
                return
            }

            this.isLoadingContent = true
            try {
                const endpoint =
                    this.contentType === 'exam'
                        ? `/api/${this.userRole}/exam/${this.contentId}`
                        : `/api/${this.userRole}/course/${this.contentId}`

                const response = await apiCall('GET', endpoint)
                this.selectedContent = response[this.contentType] || response.data

                if (this.selectedContent.purchased === 'yes' || this.selectedContent.purchased === true) {
                    this.toast.info(`You have already purchased this ${this.contentType}.`)
                    this.goToContent()
                    return
                }

                this.toast.success('Content loaded successfully!')
            } catch (error) {
                console.error('Failed to load content:', error)
                this.toast.error(`Could not load ${this.contentType} details.`)
            } finally {
                this.isLoadingContent = false
            }
        },

        resetSelection() {
            this.selectedContent = null
            this.contentType = ''
            this.contentId = ''
            this.voucherCode = ''
            this.voucherDiscount = 0
            this.useWalletBalance = false
        },

        getOwnerName() {
            if (!this.selectedContent) return 'Unknown'
            if (this.contentType === 'exam') {
                return this.selectedContent.teacher?.name || this.selectedContent.institute?.name || 'Unknown'
            }
            return this.selectedContent.teacher?.name || 'Unknown'
        },

        getOwnerAvatar() {
            if (!this.selectedContent) return null
            if (this.contentType === 'exam') {
                return this.selectedContent.teacher?.avatar || this.selectedContent.institute?.avatar
            }
            return this.selectedContent.teacher?.avatar
        },

        async applyVoucher() {
            if (!this.voucherCode) return
            this.isApplyingVoucher = true
            this.toast.info('Applying voucher...')
            try {
                // Simulate voucher validation
                await new Promise((resolve) => setTimeout(resolve, 1000))
                if (this.voucherCode.toUpperCase() === 'SAVE20') {
                    this.voucherDiscount = Math.min(20000, this.basePrice)
                    this.toast.success('Voucher applied successfully!')
                } else {
                    this.toast.error('Invalid voucher code.')
                    this.voucherCode = ''
                }
            } catch (error) {
                this.toast.error('Could not validate voucher code.')
            } finally {
                this.isApplyingVoucher = false
            }
        },

        async proceedToPayment() {
            if (!this.selectedContent) return

            this.isSubmitting = true
            try {
                const paymentData = {
                    content_type: this.contentType,
                    content_id: this.contentId,
                    amount: this.finalAmount,
                    voucher_code: this.voucherDiscount > 0 ? this.voucherCode : null,
                }

                const response = await apiCall('POST', `/api/${this.userRole}/purchase/initiate`, paymentData)

                if (response.success && response.payment_url) {
                    window.location.href = response.payment_url
                } else if (response.success && response.final_amount === 0) {
                    this.toast.success('Purchase completed successfully!')
                    this.goToContent()
                } else {
                    throw new Error(response.message || 'Payment initiation failed')
                }
            } catch (error) {
                console.error('Payment error:', error)
                this.toast.error(error.response?.data?.message || error.message || 'Payment failed')
            } finally {
                this.isSubmitting = false
            }
        },

        goToContent() {
            if (!this.selectedContent) return

            if (this.contentType === 'exam') {
                // Students view exams at /exam/:id/detail; instructors at /ayhoot/:id.
                if (this.userRole === 'student') {
                    this.$router.push(`/student/exam/${this.contentId}/detail`)
                } else {
                    this.$router.push(`/${this.userRole}/ayhoot/${this.contentId}`)
                }
            } else if (this.contentType === 'course') {
                this.$router.push(`/${this.userRole}/course/${this.contentId}`)
            }
        },
    },
}
</script>
