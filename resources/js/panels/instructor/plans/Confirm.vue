<template>
    <div class="fixed inset-0 flex flex-col items-center justify-center bg-gray-100 overflow-hidden p-4">
        <div class="absolute w-96 h-96 bg-indigo-200 rounded-full opacity-30 -top-10 -left-20 -z-10"></div>
        <div class="absolute w-96 h-96 bg-purple-200 rounded-lg opacity-20 -bottom-20 -right-10 -z-10"></div>
        <LoginNavBar class="fixed top-0 left-0 right-0 z-50" />
        <div v-if="isLoading" class="bg-white p-8 rounded-xl shadow-lg w-full max-w-lg text-center animate-pulse">
            <div class="h-8 bg-gray-200 rounded w-3/4 mx-auto mb-2"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto mb-10"></div>
            <div class="space-y-4">
                <div class="h-10 bg-gray-200 rounded-md w-full"></div>
                <div class="border-2 border-gray-200 rounded-lg p-6 space-y-3">
                    <div class="h-6 bg-gray-200 rounded w-1/2 mx-auto"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/3 mx-auto"></div>
                </div>
                <div class="h-10 bg-gray-200 rounded-md w-full"></div>
                <div class="h-12 bg-gray-300 rounded-lg w-full"></div>
            </div>
        </div>
        <div v-else-if="plan" class="bg-white p-8 rounded-xl shadow-lg w-full max-w-lg text-center">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ t('plans.confirm.title') }}</h1>
            <p class="text-gray-600 mb-6">
                {{ t('plans.confirm.subscribingTo', { name: formatPlanName(plan.title || plan.name) }) }}
            </p>
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ t('plans.confirm.chooseBillingCycle') }}</label>
                <div class="w-full flex justify-center gap-2">
                    <button v-for="option in durationOptions" :key="option.value"
                        @click="selectedDuration = option.value"
                        :class="['px-4 py-2 rounded-md font-semibold text-sm transition-colors duration-200 w-full', selectedDuration === option.value ? 'bg-blue-800 text-white shadow-md' : 'bg-gray-200 text-gray-700 hover:bg-gray-300']">
                        {{ getDurationLabel(option) }}
                        <span v-if="option.discount > 0" class="block text-xs opacity-80">{{ t('plans.confirm.savePercent', { percent: formatNumber(Math.round(option.discount * 100)) }) }}</span>
                    </button>
                </div>
            </div>
            <div class="border-2 border-indigo-200 rounded-lg p-4 mb-6 text-start text-sm space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ t('plans.confirm.basePrice') }}</span>
                    <span class="font-semibold text-gray-800">{{ formatPrice(basePrice) }} / {{ t('plans.month') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ t('plans.confirm.subtotal', { months: formatNumber(selectedDuration) }) }}</span>
                    <span class="font-semibold text-gray-800">{{ formatPrice(subtotal) }}</span>
                </div>
                <div v-if="durationDiscount > 0" class="flex justify-between text-green-600">
                    <span class="text-gray-600">{{ t('plans.confirm.longTermDiscount') }}</span>
                    <span class="font-semibold">- {{ formatPrice(durationDiscount) }}</span>
                </div>
                <div v-if="voucherDiscount > 0" class="flex justify-between text-green-600">
                    <span class="text-gray-600">{{ t('plans.confirm.voucherDiscount') }}</span>
                    <span class="font-semibold">- {{ formatPrice(voucherDiscount) }}</span>
                </div>
                <div v-if="walletDeduction > 0" class="flex justify-between text-green-600">
                    <span class="text-gray-600">{{ t('plans.confirm.fromWallet') }}</span>
                    <span class="font-semibold">- {{ formatPrice(walletDeduction) }}</span>
                </div>
                <div class="border-t border-gray-200 my-2"></div>
                <div class="flex justify-between text-lg">
                    <span class="font-bold text-gray-800">{{ t('plans.confirm.totalAmount') }}</span>
                    <span class="font-bold text-blue-800">{{ formatPrice(finalAmount) }}</span>
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ t('plans.confirm.voucherLabel') }}</label>
                <div class="flex gap-2 items-start">
                    <AyInput v-model="voucherCode" type="text" :placeholder="t('plans.confirm.voucherPlaceholder')"
                        :disabled="isApplyingVoucher || voucherDiscount > 0" />
                    <ResponsiveButton @click="applyVoucher" type="dash-blue" class="px-4 py-2"
                        :disabled="!voucherCode || isApplyingVoucher || voucherDiscount > 0">
                        <span v-if="isApplyingVoucher">{{ t('plans.confirm.processing') }}</span>
                        <span v-else>{{ t('plans.confirm.apply') }}</span>
                    </ResponsiveButton>
                </div>
            </div>
            <div v-if="walletBalance > 0" class="mb-8 flex items-center justify-center">
                <input type="checkbox" id="use-wallet" v-model="useWalletBalance"
                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" />
                <label for="use-wallet" class="mx-2 block text-sm text-gray-900">{{ t('plans.confirm.useWallet', { balance: formatPrice(walletBalance) }) }}</label>
            </div>
            <ResponsiveButton @click="proceedToPayment" type="light-green" :disabled="isSubmitting"
                class="w-full bg-green-500 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-600 transition-all duration-300 disabled:opacity-50">
                <span v-if="isSubmitting">{{ t('plans.confirm.processing') }}</span>
                <span v-else>{{ t('plans.confirm.proceedToPay', { amount: formatPrice(finalAmount) }) }}</span>
            </ResponsiveButton>
            <router-link :to="`/${$role}/plans`" class="block mt-4 text-sm text-gray-500 hover:text-gray-800">{{ t('plans.confirm.chooseDifferent') }}</router-link>
        </div>
    </div>
</template>

<script>
import { apiCall } from "@/services/apiService";
import { useToast } from 'vue-toast-notification';
import { useI18n } from 'vue-i18n';
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue';
import LoginNavBar from '@/components/organisms/nav/LoginNavBar.vue';
import AyInput from '@/components/atoms/form/AyInput.vue';

export default {
    name: 'PlanConfirmPage',
    components: { ResponsiveButton, LoginNavBar, AyInput },
    setup() {
        const toast = useToast();
        const { t, te, locale } = useI18n();
        return { toast, t, te, locale };
    },
    data() {
        return {
            plan: null,
            isLoading: true,
            isSubmitting: false,
            selectedDuration: 1,
            durationOptions: [],
            voucherCode: '',
            voucherDiscount: 0,
            isApplyingVoucher: false,
            useWalletBalance: false,
            walletBalance: 0,
        };
    },
    computed: {
        basePrice() {
            return this.plan ? parseFloat(this.plan.price) : 0;
        },
        subtotal() {
            return this.basePrice * this.selectedDuration;
        },
        durationDiscount() {
            const option = this.durationOptions.find((opt) => opt.value === this.selectedDuration);
            return option ? this.subtotal * option.discount : 0;
        },
        priceAfterDiscounts() {
            return this.subtotal - this.durationDiscount - this.voucherDiscount;
        },
        walletDeduction() {
            if (!this.useWalletBalance) return 0;
            return Math.min(this.priceAfterDiscounts, this.walletBalance);
        },
        finalAmount() {
            const final = this.priceAfterDiscounts - this.walletDeduction;
            return Math.max(0, final);
        },
    },
    async mounted() {
        const planId = this.$route.params.id;
        if (!planId || planId === '[object Object]') {
            this.toast.error('Invalid plan selected. Please try again.');
            this.$router.push(`/${localStorage.getItem('role') || 'teacher'}/plans`);
            return;
        }
        this.isLoading = true;
        try {
            const planResponse = await apiCall('GET', `/api/instructor/plans/${planId}`);
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            this.plan = planResponse.plan;
            this.durationOptions = planResponse.plan.duration_options || [];
            this.walletBalance = user.wallet ? parseFloat(user.wallet) : 0;
        } catch (error) {
            this.toast.error(this.t('plans.alerts.loadFailed'));
            this.$router.push(`/${localStorage.getItem('role') || 'teacher'}/plans`);
        } finally {
            this.isLoading = false;
        }
    },
    methods: {
        formatPlanName(name) {
            if (!name) return '';
            const key = `plans.names.${name}`;
            return this.te(key) ? this.t(key) : name;
        },
        formatNumber(num) {
            if (num === null || num === undefined) return '';
            const numLocale = this.locale === 'fa' ? 'fa-IR' : 'en-US';
            return Number(num).toLocaleString(numLocale);
        },
        getDurationLabel(option) {
            if (option.value === 1 && this.te('plans.confirm.months1')) return this.t('plans.confirm.months1');
            if (option.value === 3 && this.te('plans.confirm.months3')) return this.t('plans.confirm.months3');
            if (option.value === 12 && this.te('plans.confirm.months12')) return this.t('plans.confirm.months12');
            return option.label;
        },
        formatPrice(amount) {
            if (typeof amount !== 'number' || isNaN(amount)) amount = 0;
            const numLocale = this.locale === 'fa' ? 'fa-IR' : 'en-US';
            const formatted = Math.round(amount).toLocaleString(numLocale);
            const currency = this.t('plans.currency');
            return `${formatted} ${currency}`;
        },
        async applyVoucher() {
            if (!this.voucherCode) return;
            this.isApplyingVoucher = true;
            this.toast.info('Applying voucher...');

            try {
                const response = await apiCall('POST', `/api/instructor/apply/voucher`, {
                    voucher_code: this.voucherCode,
                    plan_id: this.plan.id,
                    duration: this.selectedDuration
                });

                if (response.discount_amount) {
                    this.voucherDiscount = response.discount_amount;
                    this.toast.success(`Voucher applied! ${response.percentage}% discount applied.`);
                } else {
                    this.toast.error(response.message || 'Invalid voucher code');
                }
            } catch (error) {
                // apiService already shows a toast for handled statuses (e.g. 409
                // voucher conflict); only add one for cases it doesn't, and never
                // surface the raw "HTTP 409" message.
                if (![401, 403, 404, 409, 419, 500, 503].includes(error.status)) {
                    this.toast.error(error.response?.message || 'Failed to apply voucher');
                }
            } finally {
                this.isApplyingVoucher = false;
            }
        },
        async proceedToPayment() {
            this.isSubmitting = true;
            try {
                const payload = {
                    duration: this.selectedDuration,
                    use_wallet: this.useWalletBalance,
                    voucher_code: this.voucherDiscount > 0 ? this.voucherCode : null,
                };
                const response = await apiCall('POST', `/api/instructor/plan/${this.plan.id}/select`, payload);
                if (response.payment_url) {
                    this.toast.info('Redirecting to payment gateway...');
                    window.location.href = response.payment_url;
                } else if (response.redirect_url) {
                    this.toast.success(response.message || 'Subscription activated successfully!');
                    this.$router.push(response.redirect_url);
                }
            } catch (error) {
                // apiCall function will show a toast for errors from the backend.
                console.error("Payment submission error:", error);
                this.isSubmitting = false;
            }
        },
    },
};
</script>