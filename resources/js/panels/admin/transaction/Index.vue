<template>
    <div class="bg-gray-50 min-h-screen">
        <!-- Header -->
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" :teacherName="adminName" :teacherUsername="adminUsername" />
        </div>

        <div class="flex">
            <!-- Sidebar -->
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />

            <!-- Main Content -->
            <main class="flex-1 bg-gray-50 ml-0 xl:ml-64 p-6 overflow-y-auto mt-16 min-h-[calc(100vh-4rem)]">
                <div>
                    <!-- Page Header -->
                    <div class="pb-4">
                        <h2 class="text-3xl font-bold mb-2">Manage Transactions</h2>
                        <p class="text-gray-600">Review and process all platform transactions.</p>
                    </div>

                    <!-- Data Table Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <!-- Table Filters -->
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto">
                                    <!-- Search Input -->
                                    <div class="relative w-full sm:w-64">
                                        <input
                                            type="text"
                                            v-model="searchQuery"
                                            @input="handleSearch"
                                            placeholder="Search by transaction ID..."
                                            class="pl-10 p-2 w-full text-sm font-bold text-gray-900 bg-gray-50 rounded-sm border-2 border-gray-300 focus:outline-none"
                                        />
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                        </div>
                                    </div>
                                    <!-- Status Filter Dropdown -->
                                    <select v-model="statusFilter" @change="applyFilters" class="w-full sm:w-auto px-4 p-2 text-sm font-bold text-gray-900 bg-gray-50 rounded-sm border-2 border-gray-300 focus:outline-none">
                                        <option value="">All Statuses</option>
                                        <option value="successful">Successful</option>
                                        <option value="pending">Pending</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Table Content -->
                        <div class="overflow-x-auto">
                            <div v-if="loading" class="text-center py-12">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                                <p class="mt-2 text-sm text-gray-500">Loading transactions...</p>
                            </div>
                            <DataTable
                                v-else-if="processedTransactions.length"
                                :columns="transactionColumns"
                                :rows="processedTransactions"
                                :wrapper-class="''"
                                row-class="hover:bg-gray-50 transition-colors duration-150"
                            >
                                <!-- User Cell -->
                                <template #cell:user="{ row: transaction }">
                                    <div class="flex items-center">
                                        <img :src="transaction.user.image" class="h-12 w-12 rounded-xl object-cover" alt="User avatar" />
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ transaction.user.name }}</div>
                                            <div class="text-sm text-gray-500">{{ transaction.user.username }}</div>
                                        </div>
                                    </div>
                                </template>
                                <!-- Amount Cell -->
                                <template #cell:amount="{ row: transaction }">{{ transaction.formattedAmount }}</template>
                                <!-- Transaction ID Cell -->
                                <template #cell:transaction_id="{ row: transaction }">
                                    <span @click="copyToClipboard(transaction.transaction_id, 'Transaction ID')" class="block" title="Click to copy">
                                        {{ transaction.transaction_id }}
                                    </span>
                                </template>
                                <!-- Date Cell -->
                                <template #cell:date="{ row: transaction }">{{ transaction.formattedDate }}</template>
                                <!-- Status Cell -->
                                <template #cell:status="{ row: transaction }">
                                    <span :class="transaction.statusPillClass" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ transaction.status }}
                                    </span>
                                </template>
                                <!-- Details/Actions Cell -->
                                <template #header:actions><span class="sr-only">Details</span></template>
                                <template #cell:actions="{ row: transaction }">
                                    <button class="text-blue-600 hover:text-blue-900" :title="transaction.description">Details</button>
                                </template>
                            </DataTable>
                            <div v-else class="text-center py-12">
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No Transactions Found</h3>
                                <p class="mt-1 text-sm text-gray-500">There are no transactions matching your current filters.</p>
                            </div>
                        </div>
                        
                        <!-- Pagination Controls -->
                        <Pagination
                            v-if="!loading"
                            :pagination="pagination"
                            @page-changed="fetchData"
                            :wrapper-class="'px-6 py-4 border-t border-gray-200 bg-white'"
                        />

                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script>
import Sidebar from '@/components/layout/AdminSidebar.vue';
import Header from '@/components/layout/AdminHeader.vue';
import { apiCall } from '@/services/apiService'
import DataTable from '@/components/shared/DataTable.vue'
import Pagination from '@/components/shared/Pagination.vue'

export default {
    name: 'TransactionIndex',
    components: { Sidebar, Header, DataTable, Pagination },
    data() {
        return {
            isSidebarOpen: false,
            loading: false,
            transactions: { data: [] },
            pagination: {},
            searchQuery: '',
            statusFilter: '', // 'successful', 'pending', 'failed'
            searchTimeout: null,
        };
    },
    computed: {
        adminName() {
            return JSON.parse(localStorage.getItem('user') || '{}').name || 'Admin';
        },
        adminUsername() {
            return JSON.parse(localStorage.getItem('user') || '{}').username || 'admin';
        },
        transactionColumns() {
            return [
                { key: 'user', label: 'User / Payer', align: 'left', thClass: 'font-medium' },
                { key: 'amount', label: 'Amount', align: 'left', thClass: 'font-medium', tdClass: 'text-gray-700 font-mono' },
                {
                    key: 'transaction_id',
                    label: 'Transaction ID',
                    align: 'left',
                    thClass: 'font-medium',
                    tdClass: 'text-gray-700 font-mono cursor-pointer hover:text-blue-600',
                },
                { key: 'date', label: 'Date', align: 'left', thClass: 'font-medium', tdClass: 'text-gray-500' },
                { key: 'status', label: 'Status', align: 'left', thClass: 'font-medium' },
                { key: 'actions', label: 'Details', align: 'right', thClass: 'relative', tdClass: 'font-medium' },
            ];
        },
        processedTransactions() {
            if (!this.transactions.data) return [];
            return this.transactions.data.map(t => {
                let user = {
                    name: 'Teacher/Institute Purchase',
                    username: `Teacher ID: ${t.invoice.teacher_id}`,
                    image: '/imgHolders/profile.svg' // Default image
                };

                if (t.invoice.student) {
                    user.name = t.invoice.student.name;
                    user.username = `@${t.invoice.student.username}`;
                    // Assuming student object has an image property
                    user.image = t.invoice.student.image || '/imgHolders/profile.svg';
                }

                const statusClasses = {
                    successful: 'bg-green-100 text-green-800',
                    pending: 'bg-yellow-100 text-yellow-800',
                    failed: 'bg-red-100 text-red-800'
                };
                
                return {
                    ...t,
                    user,
                    formattedAmount: new Intl.NumberFormat('en-US', { style: 'currency', currency: t.invoice.currency }).format(t.amount / 100), // Assuming amount is in cents/minor units
                    transaction_id: t.transaction_id.replace('S', ''), // Clean up the ID for display
                    formattedDate: new Intl.DateTimeFormat('en-US', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(t.created_at)),
                    statusPillClass: statusClasses[t.status] || 'bg-gray-100 text-gray-800',
                    description: t.invoice.description || 'No description available.'
                };
            });
        }
    },
    mounted() {
        this.fetchData(1);
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen;
        },
        async fetchData(page = 1) {
            if (this.loading || page < 1 || (this.pagination.last_page && page > this.pagination.last_page)) return;
            
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page,
                    search: this.searchQuery,
                    status: this.statusFilter
                });
                
                const response = await apiCall('GET', `/api/admin/transactions?${params.toString()}`);
                this.transactions = response.transactions;
                this.pagination = {
                    current_page: response.transactions.current_page,
                    from: response.transactions.from,
                    to: response.transactions.to,
                    total: response.transactions.total,
                    last_page: response.transactions.last_page,
                };
            } catch (error) {
                console.error('Failed to fetch transactions:', error);
                this.$toast.error('Could not load transactions.');
            } finally {
                this.loading = false;
            }
        },
        handleSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.fetchData(1); // Reset to first page on new search
            }, 300); // Debounce search input
        },
        applyFilters() {
            this.fetchData(1); // Reset to first page on filter change
        },
        async copyToClipboard(text, fieldName) {
            try {
                await navigator.clipboard.writeText(text);
                this.$toast.success(`${fieldName} copied to clipboard!`);
            } catch (err) {
                console.error('Failed to copy text: ', err);
                this.$toast.error('Failed to copy to clipboard.');
            }
        },
    }
};
</script>