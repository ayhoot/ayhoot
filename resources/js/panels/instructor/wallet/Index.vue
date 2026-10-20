<template>
    <InstructorLayout :secondary="false">
                <div class="">
                    <!-- Header Section -->
                    <div class="pb-4">
                        <h2 class="text-3xl font-bold mb-2">{{ t('wallet.title') }}</h2>
                        <p class="">{{ t('wallet.description') }}</p>
                    </div>

                    <!-- Wallet Summary Card -->
                    <div
                        class="relative flex flex-col justify-between p-8 rounded-xl shadow-lg text-white mb-6 overflow-hidden bg-linear-to-r from-purple-500 via-indigo-500 to-blue-500"
                    >
                        <div class="flex items-center w-full justify-between">
                            <div class="w-full">
                                <h3 class="text-lg font-medium text-gray-200">{{ t('wallet.card.currentBalance') }}</h3>
                                <p class="text-3xl font-bold mt-2">
                                    {{ formatCurrency(currentBalance, false) }}
                                </p>
                                <div class="mt-8">
                                    <h4 class="text-sm font-medium text-gray-200">{{ t('wallet.card.shabaNo') }}</h4>
                                    <p class="text-lg font-mono tracking-wider mt-1">
                                        {{ cardNumber || t('wallet.card.notSet') }}
                                    </p>
                                </div>
                            </div>
                            <div class="w-full">
                                <div class="gap-2 flex justify-end">
                                    <ResponsiveButton @click="openEditModal" type="yellow" class="p-2 px-4">
                                        {{ cardNumber ? t('wallet.card.editCard') : t('wallet.card.addCard') }}
                                    </ResponsiveButton>
                                    <ResponsiveButton class="p-2 px-4" type="dash-blue">
                                        {{ t('wallet.card.checkout') }}
                                    </ResponsiveButton>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- START: Tabs Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <!-- Tab Navigation -->
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex gap-6 px-4">
                                <button
                                    @click="activeTab = 'transactions'"
                                    :class="[
                                        'py-4 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'transactions'
                                            ? 'border-blue-500 text-blue-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    ]"
                                >
                                    {{ t('wallet.tabs.transactions') }}
                                </button>
                                <button
                                    @click="activeTab = 'invoices'"
                                    :class="[
                                        'py-4 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'invoices'
                                            ? 'border-blue-500 text-blue-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    ]"
                                >
                                    {{ t('wallet.tabs.invoices') }}
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content -->
                        <div>
                            <div v-if="isLoading" class="text-center py-12">
                                <div
                                    class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mx-auto"
                                ></div>
                                <p class="mt-4 text-gray-600">{{ t('wallet.loading') }}</p>
                            </div>
                            <!-- Transactions Tab -->
                            <div v-if="activeTab === 'transactions' && !isLoading">
                                <div class="p-4 border-b border-gray-100">
                                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                                        <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
                                            <div class="relative w-full sm:flex-1">
                                                <input
                                                    type="text"
                                                    v-model="filters.search"
                                                    :placeholder="t('wallet.transactions.searchPlaceholder')"
                                                    class="w-full inline-flex pl-10 p-2 text-sm font-bold text-gray-900 bg-gray-50 rounded-sm border-2 border-gray-300 focus:outline-none focus:border-blue-500"
                                                />
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                                >
                                                    <svg
                                                        class="h-5 w-5 text-gray-400"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="overflow-x-auto">
                                    <DataTable
                                        v-if="filteredTransactions.length"
                                        :columns="transactionColumns"
                                        :rows="filteredTransactions"
                                        wrapper-class=""
                                        row-class="hover:bg-gray-50"
                                        :td-base-class="'px-6 py-4 text-sm'"
                                    >
                                        <template #cell:details="{ row: transaction }">
                                            <div class="flex items-center">
                                                <div
                                                    class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center"
                                                    :class="statusClass(transaction.status)"
                                                >
                                                    <svg
                                                        v-if="transaction.status === 'successful'"
                                                        width="20"
                                                        height="20"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        class="text-green-600"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                    >
                                                        <path
                                                            d="M14.1003 18.5786H4.5006C3.17514 18.5786 2.10064 17.5041 2.10060 16.1787L2.10034 7.17888C2.10030 5.85337 3.17483 4.77881 4.50034 4.77881H18.8998C20.2253 4.77881 21.2998 5.85270 21.2998 7.17822L21.2999 11.3788M2.69988 8.97866H20.6999M21.8997 16.7786L19.4455 19.2212M19.4455 19.2212L17.0997 16.889M19.4455 19.2212L19.4455 13.7786"
                                                            class="stroke-current"
                                                            stroke-width="2"
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
                                                        class="text-red-600"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                    >
                                                        <path
                                                            d="M14.1003 18.5786H4.5006C3.17514 18.5786 2.10064 17.5041 2.10060 16.1787L2.10034 7.17888C2.10030 5.85337 3.17483 4.77881 4.50034 4.77881H18.8998C20.2253 4.77881 21.2998 5.85270 21.2998 7.17822L21.2999 11.3788M2.69988 8.97866H20.6999M17.0999 16.2212L19.5542 13.7787M19.5542 13.7787L21.8999 16.1108M19.5542 13.7787L19.5542 19.2212"
                                                            class="stroke-current"
                                                            stroke-width="2"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                        />
                                                    </svg>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ transaction.description }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 capitalize">
                                                        {{ transaction.gateway }} - {{ transaction.status }}
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <template #cell:amount="{ row: transaction }">
                                            <span
                                                class="text-sm font-semibold"
                                                :class="{
                                                    'text-green-600': transaction.status === 'successful',
                                                    'text-red-600': transaction.status !== 'successful',
                                                }"
                                                >{{ formatCurrency(transaction.amount) }}</span
                                            >
                                        </template>
                                        <template #cell:date="{ row: transaction }">
                                            {{ formatDate(transaction.date) }}
                                        </template>
                                    </DataTable>
                                    <div v-else class="text-center py-12">
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                                            {{ t('wallet.transactions.empty') }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Invoices Tab -->
                            <div v-if="activeTab === 'invoices' && !isLoading">
                                <div class="overflow-x-auto">
                                    <DataTable
                                        v-if="invoices.length"
                                        :columns="invoiceColumns"
                                        :rows="invoices"
                                        wrapper-class=""
                                        row-class="hover:bg-gray-50"
                                        :td-base-class="'px-6 py-4 text-sm'"
                                    >
                                        <template #header:actions="{ column }">
                                            <span class="sr-only">{{ column.label }}</span>
                                        </template>
                                        <template #cell:price="{ row: invoice }">
                                            {{ formatCurrency(invoice.price, false) }}
                                        </template>
                                        <template #cell:date="{ row: invoice }">
                                            {{ formatDate(invoice.date) }}
                                        </template>
                                        <template #cell:status="{ row: invoice }">
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                                :class="statusClass(invoice.status)"
                                                >{{ invoice.status }}</span
                                            >
                                        </template>
                                        <template #cell:actions="{ row: invoice }">
                                            <ResponsiveButton
                                                v-if="invoice.status === 'pending'"
                                                type="light-blue"
                                                class="text-xs px-3 py-1"
                                                @click="continuePayment(invoice.id)"
                                            >
                                                {{ t('wallet.invoices.continuePayment') }}
                                            </ResponsiveButton>
                                        </template>
                                    </DataTable>
                                    <div v-else class="text-center py-12">
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                                            {{ t('wallet.invoices.empty') }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END: Tabs Section -->
                </div>
        <template #overlays>
        <!-- Add/Edit Card Number Modal -->
        <Modal
            v-model="isModalOpen"
            :title="cardNumber ? t('wallet.modal.editTitle') : t('wallet.modal.addTitle')"
            size="sm"
        >
            <form @submit.prevent="saveCardNumber">
                <AyInput
                    v-model="modalCardNumber"
                    type="text"
                    :label="t('wallet.modal.shabaLabel')"
                    :placeholder="t('wallet.modal.shabaPlaceholder')"
                    @keyup.enter="saveCardNumber"
                />
            </form>
            <template #footer>
                <ResponsiveButton @click="closeModal" class="px-4 py-2" type="light-gray">{{
                    t('wallet.modal.cancel')
                }}</ResponsiveButton>
                <ResponsiveButton :disabled="isSavingCard" @click="saveCardNumber" class="px-4 py-2" type="light-blue">{{
                    t('wallet.modal.save')
                }}</ResponsiveButton>
            </template>
        </Modal>
        </template>
    </InstructorLayout>
</template>

<script>
import { apiCall } from "@/services/apiService"
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Modal from '@/components/shared/Modal.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import { useI18n } from 'vue-i18n'
import { useConfirm } from '@/composables/useConfirm'
import { useToast } from 'vue-toast-notification'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()
const toast = useToast()

export default {
    name: 'Wallet',
    components: { InstructorLayout, AyButton, ResponsiveButton, DataTable, Modal, AyInput },
    setup() {
        const { t, locale } = useI18n()
        return { t, locale }
    },
    data() {
        return {
            isLoading: true,
            isSavingCard: false,
            isModalOpen: false,
            modalCardNumber: '',
            teacher: JSON.parse(localStorage.getItem('user') || '{}'),
            transactions: [],
            invoices: [],
            activeTab: 'transactions',
            filters: { search: '' },
        }
    },
    computed: {
        transactionColumns() {
            return [
                { key: 'details', label: this.t('wallet.transactions.header.details'), align: 'left', thClass: 'py-2' },
                { key: 'amount', label: this.t('wallet.transactions.header.amount'), align: 'left' },
                { key: 'user', label: this.t('wallet.transactions.header.user'), align: 'left', tdClass: 'text-gray-700' },
                { key: 'date', label: this.t('wallet.transactions.header.date'), align: 'left', tdClass: 'text-gray-500' },
            ]
        },
        invoiceColumns() {
            return [
                { key: 'title', label: this.t('wallet.invoices.header.title'), align: 'left', tdClass: 'font-medium text-gray-900' },
                { key: 'price', label: this.t('wallet.invoices.header.price'), align: 'left', tdClass: 'text-gray-800' },
                { key: 'date', label: this.t('wallet.invoices.header.date'), align: 'left', tdClass: 'text-gray-500' },
                { key: 'status', label: this.t('wallet.invoices.header.status'), align: 'left' },
                { key: 'actions', label: this.t('wallet.invoices.header.actions'), align: 'right', thClass: 'relative', tdClass: 'flex items-center gap-2' },
            ]
        },
        cardNumber() {
            return this.teacher?.card || ''
        },
        currentBalance() {
            const val = parseFloat(this.teacher?.wallet ?? this.teacher?.wallet_balance ?? 0)
            return isNaN(val) ? 0 : val
        },
        filteredTransactions() {
            if (!this.filters.search) {
                return this.transactions
            }
            const query = this.filters.search.toLowerCase()
            return this.transactions.filter(
                (t) => t.description.toLowerCase().includes(query) || t.user.toLowerCase().includes(query)
            )
        },
    },
    methods: {
        openEditModal() {
            this.modalCardNumber = this.cardNumber
            this.isModalOpen = true
        },
        closeModal() {
            this.isModalOpen = false
        },
        async saveCardNumber() {
            const cardVal = (this.modalCardNumber || '').trim()
            if (!cardVal) return

            this.isSavingCard = true
            try {
                const response = await apiCall('POST', `/api/instructor/card/update`, { card: cardVal })
                const updated = response?.user || response?.teacher
                if (updated) {
                    this.teacher = { ...this.teacher, ...updated, card: cardVal }
                } else {
                    this.teacher = { ...this.teacher, card: cardVal }
                }
                localStorage.setItem('user', JSON.stringify(this.teacher))
                window.dispatchEvent(new Event('storage'))

                toast.success(response?.message || this.t('wallet.modal.savedSuccess') || 'SHABA number saved successfully')
                this.closeModal()
                await this.fetchData()
            } catch (error) {
                console.error('Failed to save card number:', error)
                toast.error(error?.response?.data?.message || error?.message || 'Failed to save card number')
            } finally {
                this.isSavingCard = false
            }
        },
        formatDate(dateString) {
            const dateLocale = this.locale === 'fa' ? 'fa-IR' : 'en-US'
            return new Date(dateString).toLocaleDateString(dateLocale, {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            })
        },
        formatCurrency(amount, includeSign = true) {
            const currency = this.t('wallet.currency')
            const numberLocale = this.locale === 'fa' ? 'fa-IR' : 'en-US'
            if (typeof amount !== 'number') return `0 ${currency}`
            const formatted = Math.round(amount).toLocaleString(numberLocale)
            if (!includeSign) return `${formatted} ${currency}`
            return `+ ${formatted} ${currency}`
        },
        statusClass(status) {
            const lowerStatus = (status || '').toLowerCase()
            return {
                'bg-green-100 text-green-800': lowerStatus === 'paid' || lowerStatus === 'successful',
                'bg-yellow-100 text-yellow-800': lowerStatus === 'pending',
                'bg-red-100 text-red-800': lowerStatus === 'failed' || lowerStatus === 'cancelled',
            }
        },
        async fetchData() {
            this.isLoading = true
            try {
                const [transactionsResponse, invoicesResponse, userResponse] = await Promise.allSettled([
                    apiCall('GET', `/api/instructor/transactions`),
                    apiCall('GET', `/api/instructor/invoices`),
                    apiCall('GET', `/api/user`),
                ])

                if (userResponse.status === 'fulfilled' && userResponse.value) {
                    const freshUser = userResponse.value
                    this.teacher = { ...this.teacher, ...freshUser }
                    localStorage.setItem('user', JSON.stringify(this.teacher))
                }

                if (transactionsResponse.status === 'fulfilled' && transactionsResponse.value?.transactions?.data) {
                    this.transactions = transactionsResponse.value.transactions.data.map((t) => ({
                        id: t.id,
                        description: t.invoice?.description || this.t('wallet.transactions.notAvailable'),
                        amount: parseFloat(t.amount),
                        user: t.invoice?.student?.name || t.invoice?.teacher?.name || this.t('wallet.transactions.system'),
                        date: t.created_at,
                        gateway: t.gateway,
                        status: t.status,
                    }))
                }

                if (invoicesResponse.status === 'fulfilled' && invoicesResponse.value?.invoices?.data) {
                    this.invoices = invoicesResponse.value.invoices.data.map((inv) => ({
                        id: inv.id,
                        title: inv.description || this.t('wallet.invoices.defaultTitle', { id: inv.invoice_number }),
                        price: parseFloat(inv.amount),
                        date: inv.invoice_date || inv.created_at,
                        status: inv.status,
                    }))
                }
            } catch (error) {
                console.error('Failed to fetch wallet data:', error)
            } finally {
                this.isLoading = false
            }
        },
        async continuePayment(invoiceId) {
            try {
                const response = await apiCall('GET', `/api/instructor/invoice/pay/${invoiceId}`);
                if (response.payment_url) {
                    window.location.href = response.payment_url; // Redirect to payment gateway
                } else if (response.error) {
                    await alertDialog({ message: response.error }); // Show error message from backend
                }
            } catch (error) {
                console.error('Failed to initiate payment:', error);
                await alertDialog({ message: 'An error occurred while trying to continue payment.' });
            }
        }
    },
    mounted() {
        this.fetchData()
    },
}
</script>

<style scoped>
.bg-linear-to-r {
    background-image: linear-gradient(to right, var(--tw-gradient-stops));
}
.from-purple-500 {
    --tw-gradient-from: #a855f7;
    --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(168, 85, 247, 0));
}
.via-indigo-500 {
    --tw-gradient-stops: var(--tw-gradient-from), #6366f1, var(--tw-gradient-to, rgba(99, 102, 241, 0));
}
.to-blue-500 {
    --tw-gradient-to: #3b82f6;
}
</style>