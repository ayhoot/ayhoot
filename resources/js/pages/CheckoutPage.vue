<template>
    <PublicLayout nav="login">
        <div
            class="relative flex flex-col items-center justify-center min-h-[calc(100vh-4rem)] bg-gray-100 overflow-y-auto p-4"
        >
            <div class="absolute w-96 h-96 bg-indigo-200 rounded-full opacity-30 -top-10 -left-20 -z-10"></div>
            <div class="absolute w-96 h-96 bg-purple-200 rounded-lg opacity-20 -bottom-20 -right-10 -z-10"></div>

            <!-- Main Content Wrapper -->
            <div class="w-full max-w-4xl">
                <!-- Loading State -->
                <div v-if="isLoading" class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div class="bg-white p-6 rounded-xl shadow-md w-full animate-pulse hidden lg:block">
                        <div class="h-48 bg-gray-200 rounded-lg mb-4"></div>
                        <div class="h-6 bg-gray-200 rounded w-3/4 mb-4"></div>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-8 rounded-xl shadow-md w-full text-center">
                        <div class="h-8 bg-gray-200 rounded w-3/4 mx-auto mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto mb-10"></div>
                        <div class="space-y-4">
                            <div class="border-2 border-gray-200 rounded-lg p-6 space-y-3">
                                <div class="h-6 bg-gray-200 rounded w-1/2 mx-auto"></div>
                                <div class="h-4 bg-gray-200 rounded w-1/3 mx-auto"></div>
                            </div>
                            <div class="h-10 bg-gray-200 rounded-md w-full"></div>
                            <div class="h-12 bg-gray-300 rounded-lg w-full"></div>
                        </div>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="text-center">
                    <div class="bg-white p-8 rounded-xl shadow-md">
                        <div class="text-red-500 text-lg font-semibold mb-4">{{ error }}</div>
                        <button
                            @click="$router.go(-1)"
                            class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600"
                        >
                            Go Back
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div v-else-if="content" class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-center">
                    <!-- Content Preview Card -->
                    <div class="bg-white p-3 rounded-xl shadow-md w-full space-y-2">
                        <img
                            :src="getCoverUrl(content.cover)"
                            :alt="content.title"
                            class="w-full h-40 object-cover rounded-lg shadow-md mb-4 bg-gray-200"
                        />
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase"
                                >{{ contentType }} Purchase</span
                            >
                            <h2 class="text-base font-bold text-gray-800 mt-1">{{ content.title }}</h2>
                        </div>
                        <div class="border-t border-gray-200 pt-2 flex items-center gap-3">
                            <img
                                :src="getCoverUrl(getOwnerAvatar(), true)"
                                alt="Owner Avatar"
                                class="w-10 h-10 rounded-full object-cover border-2 border-blue-200"
                            />
                            <div>
                                <p class="font-semibold text-sm text-gray-700">{{ getOwnerName() }}</p>
                                <p class="text-xs text-gray-500">@{{ getOwnerUsername() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Purchase Form -->
                    <div class="bg-white p-8 rounded-xl shadow-md w-full text-center">
                        <h1 class="text-2xl font-bold text-gray-800 mb-2">Confirm Your Purchase</h1>
                        <p class="text-gray-600 mb-6">You are purchasing access to this {{ contentType }}.</p>

                        <div class="border-2 border-indigo-200 rounded-lg p-4 mb-6 text-left text-sm space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Price:</span>
                                <span class="font-semibold text-gray-800">{{ formatPrice(basePrice) }}</span>
                            </div>
                            <div v-if="voucherDiscount > 0" class="flex justify-between text-green-600">
                                <span class="text-gray-600">Voucher Discount:</span>
                                <span class="font-semibold">- {{ formatPrice(voucherDiscount) }}</span>
                            </div>
                            <div v-if="walletDeduction > 0" class="flex justify-between text-green-600">
                                <span class="text-gray-600">From Wallet:</span>
                                <span class="font-semibold">- {{ formatPrice(walletDeduction) }}</span>
                            </div>
                            <div class="border-t border-gray-200 my-2"></div>
                            <div class="flex justify-between text-lg">
                                <span class="font-bold text-gray-800">Total Amount:</span>
                                <span class="font-bold text-blue-800">{{ formatPrice(finalAmount) }}</span>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2"
                                >Voucher Code (Optional)</label
                            >
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    v-model="voucherCode"
                                    placeholder="Enter your code"
                                    class="block w-full text-center font-semibold px-3 py-2 border-2 border-gray-300 rounded-md focus:outline-none focus:border-indigo-400 sm:text-sm"
                                    :disabled="isApplyingVoucher || voucherDiscount > 0"
                                />
                                <ResponsiveButton
                                    @click="applyVoucher"
                                    type="dash-blue"
                                    class="px-4 py-2"
                                    :disabled="!voucherCode || isApplyingVoucher || voucherDiscount > 0"
                                >
                                    <span v-if="isApplyingVoucher">...</span>
                                    <span v-else>Apply</span>
                                </ResponsiveButton>
                            </div>
                        </div>

                        <div v-if="walletBalance > 0" class="mb-8 flex items-center justify-center">
                            <input
                                type="checkbox"
                                id="use-wallet"
                                v-model="useWalletBalance"
                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                            />
                            <label for="use-wallet" class="ml-2 block text-sm text-gray-900">
                                Use wallet balance (Available:
                                <span class="font-bold">{{ formatPrice(walletBalance) }}</span
                                >)
                            </label>
                        </div>

                        <ResponsiveButton
                            @click="proceedToPayment"
                            type="light-green"
                            :disabled="isSubmitting"
                            class="w-full bg-green-500 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-600 transition-all duration-300 disabled:opacity-50"
                        >
                            <span v-if="isSubmitting">Processing...</span>
                            <span v-else>Proceed to Pay {{ formatPrice(finalAmount) }}</span>
                        </ResponsiveButton>

                        <button @click="goBack" class="block mt-4 text-sm text-gray-500 hover:text-gray-800 mx-auto">
                            Go back to {{ contentType }} preview
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useToast } from 'vue-toast-notification'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import PublicLayout from '@/components/layout/PublicLayout.vue'

export default {
    name: 'CheckoutPage',
    components: { ResponsiveButton, PublicLayout },
    setup() {
        const toast = useToast()
        return { toast }
    },
    data() {
        return {
            content: null,
            contentType: null,
            contentId: null,
            isLoading: true,
            isSubmitting: false,
            error: null,
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
            return this.content ? parseFloat(this.content.price) : 0
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
        await this.initializeCheckout()
    },
    methods: {
        async initializeCheckout() {
            this.contentType = this.$route.params.type
            this.contentId = this.$route.params.id

            if (!this.contentType || !this.contentId) {
                this.error = 'Invalid checkout parameters'
                return
            }

            if (!['exam', 'course'].includes(this.contentType)) {
                this.error = 'Invalid content type'
                return
            }

            if (isNaN(Number(this.contentId))) {
                this.error = 'Invalid content ID'
                return
            }

            this.isLoading = true
            try {
                // Load content details. The endpoint shape differs by role:
                //  - students use the access-aware show endpoint (/exam/{id}, /course/{id})
                //  - creators (teacher/institute/instructor) use the *-detail endpoint
                let endpoint
                if (this.contentType === 'exam') {
                    endpoint =
                        this.userRole === 'student'
                            ? `/api/student/exam/${this.contentId}`
                            : `/api/${this.userRole}/exam-detail/${this.contentId}`
                } else {
                    endpoint = `/api/${this.userRole}/course/${this.contentId}`
                }

                const response = await apiCall('GET', endpoint)
                this.content = response[this.contentType] || response.data || response.exam || response.course

                // Some endpoints return purchased/price at the top level — merge them in.
                if (this.content) {
                    if (response.purchased !== undefined) this.content.purchased = response.purchased
                    if (
                        (this.content.price === undefined || this.content.price === null) &&
                        response.price !== undefined
                    ) {
                        this.content.price = response.price
                    }
                }

                // Load user wallet balance
                const user = JSON.parse(localStorage.getItem('user') || '{}')
                this.walletBalance = user.wallet ? parseFloat(user.wallet) : 0

                // Check if already purchased
                if (this.content.purchased === 'yes' || this.content.purchased === true) {
                    this.toast.info(`You have already purchased this ${this.contentType}.`)
                    this.goToContent()
                    return
                }
            } catch (error) {
                console.error('Failed to load content:', error)
                this.error = `Could not load ${this.contentType} details.`
            } finally {
                this.isLoading = false
            }
        },

        getOwnerName() {
            if (this.contentType === 'exam') {
                return this.content.teacher?.name || this.content.institute?.name || 'Unknown'
            }
            return this.content.teacher?.name || 'Unknown'
        },

        getOwnerUsername() {
            if (this.contentType === 'exam') {
                return this.content.teacher?.username || this.content.institute?.username || 'unknown'
            }
            return this.content.teacher?.username || 'unknown'
        },

        getOwnerAvatar() {
            if (this.contentType === 'exam') {
                return this.content.teacher?.avatar || this.content.institute?.avatar
            }
            return this.content.teacher?.avatar
        },

        formatPrice(amount) {
            const currency = 'Rial'
            if (typeof amount !== 'number') return `0 ${currency}`
            return `${Math.round(amount).toLocaleString('en-US')} ${currency}`
        },

        getCoverUrl(path, isProfile = false) {
            const fallback = isProfile ? '/imgHolders/profile.svg' : '/imgHolders/CardHolderImage.jpg'
            if (!path) return fallback
            if (path.startsWith('http')) return path
            return `/uploads/${path.replace(/\\/g, '')}`
        },

        async applyVoucher() {
            if (!this.voucherCode) return
            this.isApplyingVoucher = true
            try {
                // Validate against the admin-configured discounts (DiscountService).
                const resp = await apiCall('POST', '/api/discount/validate', {
                    code: this.voucherCode,
                    content_type: this.contentType,
                    amount: this.basePrice,
                })
                if (resp.success && resp.discount) {
                    this.voucherDiscount = Math.min(Number(resp.discount.discount_amount) || 0, this.basePrice)
                    this.toast.success(resp.message || 'Voucher applied successfully!')
                } else {
                    this.toast.error(resp.message || 'Invalid voucher code.')
                    this.voucherCode = ''
                }
            } catch (error) {
                this.toast.error(error?.response?.message || 'Invalid or expired voucher code.')
                this.voucherCode = ''
            } finally {
                this.isApplyingVoucher = false
            }
        },

        async proceedToPayment() {
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
                    // Redirect to payment gateway
                    window.location.href = response.payment_url
                } else if (response.success && response.final_amount === 0) {
                    // Free purchase or fully covered by wallet/discount
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
            if (this.contentType === 'exam') {
                if (this.userRole === 'student') {
                    this.$router.push(`/${this.userRole}/exam/${this.contentId}/detail`)
                } else {
                    this.$router.push(`/${this.userRole}/ayhoot/${this.contentId}`)
                }
            } else if (this.contentType === 'course') {
                this.$router.push(`/${this.userRole}/course/${this.contentId}`)
            }
        },

        goBack() {
            if (this.contentType === 'exam') {
                this.$router.push(`/discover/ayhoot/${this.contentId}/preview`)
            } else if (this.contentType === 'course') {
                this.$router.push(`/discover/course/${this.contentId}/preview`)
            } else {
                this.$router.go(-1)
            }
        },
    },
}
</script>
