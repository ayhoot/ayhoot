<template>
    <div class="bg-gray-50 min-h-screen">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" />
        </div>

        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />

            <main
                class="flex-1 bg-gray-50 ml-0 sm:ml-0 md:ml-0 lg:ml-0 xl:ml-64 p-6 overflow-y-auto mt-16 min-h-[calc(100vh-4rem)]"
            >
                <div>
                    <!-- Header Section -->
                    <div class="pb-4">
                        <h2 class="text-3xl font-bold mb-2">Support Tickets</h2>
                        <p class="text-gray-600">View, create, and manage all support tickets</p>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <!-- Table Header with Filters -->
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                                <!-- Left: Search & Filters -->
                                <div
                                    class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto"
                                >
                                    <div class="relative w-full sm:w-64">
                                        <input
                                            type="text"
                                            v-model="filters.search"
                                            @input="handleSearch"
                                            placeholder="Search by title..."
                                            class="inline-flex pl-10 p-2 w-full text-sm font-bold text-gray-900 bg-gray-50 rounded-sm border-2 border-gray-300 focus:outline-none"
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
                                    <select
                                        v-model="filters.status"
                                        @change="fetchData"
                                        class="w-full sm:w-auto px-4 p-2 text-sm font-bold text-gray-900 bg-gray-50 rounded-sm border-2 border-gray-300 focus:outline-none"
                                    >
                                        <option value="">All Statuses</option>
                                        <option value="open">Open</option>
                                        <option value="answered">Answered</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>

                                <!-- Right: Create Button -->
                                <!-- <router-link
                                    to="/admin/ticket/create"
                                    class="w-full sm:w-auto text-white bg-blue-600 hover:bg-blue-700 shadow-[0_2px_0_#1d4ed8] hover:shadow-none hover:translate-y-[2px] px-4 py-2 rounded-md cursor-pointer font-semibold text-sm transition-all inline-flex items-center justify-center gap-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2.5"
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>
                                    Create Ticket
                                </router-link> -->
                            </div>
                        </div>

                        <!-- Table Content -->
                        <div class="overflow-x-auto">
                            <DataTable
                                v-if="tickets.length"
                                :columns="ticketColumns"
                                :rows="tickets"
                                :wrapper-class="''"
                                row-class="hover:bg-gray-50 transition-colors duration-150"
                            >
                                <template #cell:id="{ row }">#{{ row.id }}</template>
                                <template #cell:title="{ row }">{{ row.title }}</template>
                                <template #cell:status="{ row }">
                                    <StatusBadge
                                        :status="row.status"
                                        :base-class="'px-2 py-1 text-xs font-medium rounded-full capitalize'"
                                    >
                                        {{ row.status }}
                                    </StatusBadge>
                                </template>
                                <template #cell:created="{ row }">
                                    {{ formatDate(row.created_at) }}
                                </template>
                                <template #header:actions>
                                    <span class="sr-only">Actions</span>
                                </template>
                                <template #cell:actions="{ row }">
                                    <button
                                        @click="viewTicket(row.id)"
                                        class="text-white bg-blue-500 hover:bg-blue-600 shadow-[0_2px_0_#2563eb] hover:shadow-none hover:translate-y-[2px] text-xs px-3 py-1.5 rounded-md font-semibold transition-all"
                                    >
                                        View
                                    </button>
                                </template>
                            </DataTable>
                            <div v-else-if="!isLoading" class="text-center py-12">
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No tickets found</h3>
                                <p class="mt-1 text-sm text-gray-500" v-if="filters.search || filters.status">
                                    No tickets match your filter criteria.
                                    <button @click="clearFilters" class="text-blue-600 hover:underline font-medium">
                                        Clear filters
                                    </button>
                                </p>
                                <p class="mt-1 text-sm text-gray-500" v-else>Get started by creating a new ticket.</p>
                            </div>
                            <div v-else class="text-center py-12">
                                <div
                                    class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"
                                ></div>
                                <p class="mt-2 text-sm text-gray-500">Loading tickets...</p>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <Pagination v-if="!isLoading" :pagination="pagination" @page-changed="changePage" />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script>
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import { apiCall } from '@/services/apiService'
import DataTable from '@/components/shared/DataTable.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import Pagination from '@/components/shared/Pagination.vue'

export default {
    name: 'AdminTicket',
    components: {
        Sidebar,
        Header,
        DataTable,
        StatusBadge,
        Pagination,
    },
    computed: {
        ticketColumns() {
            return [
                { key: 'id', label: 'ID', align: 'left', thClass: 'font-medium', tdClass: 'font-medium text-gray-500' },
                {
                    key: 'title',
                    label: 'Title',
                    align: 'left',
                    thClass: 'font-medium',
                    tdClass: 'text-gray-800 font-medium max-w-sm truncate',
                },
                { key: 'status', label: 'Status', align: 'left', thClass: 'font-medium' },
                { key: 'created', label: 'Created', align: 'left', thClass: 'font-medium', tdClass: 'text-gray-500' },
                { key: 'actions', label: 'Actions', align: 'right', thClass: 'relative', tdClass: 'font-medium' },
            ]
        },
    },
    data() {
        return {
            tickets: [],
            isSidebarOpen: false,
            isLoading: false,
            filters: { search: '', status: '', page: 1 },
            pagination: { current_page: 1, from: 0, to: 0, total: 0, last_page: 1 },
            searchTimeout: null,
        }
    },
    async mounted() {
        await this.fetchData()
    },
    methods: {
        async fetchData() {
            this.isLoading = true
            try {
                // Reset page to 1 whenever filters change, except for pagination clicks
                if (!this.searchTimeout) {
                    this.filters.page = 1
                }
                const queryParams = new URLSearchParams(this.filters).toString()
                const response = await apiCall('GET', `/api/admin/ticket?${queryParams}`)

                this.tickets = response.tickets.data
                this.pagination = {
                    current_page: response.tickets.current_page,
                    from: response.tickets.from,
                    to: response.tickets.to,
                    total: response.tickets.total,
                    last_page: response.tickets.last_page,
                }
            } catch (error) {
                console.error('Error fetching tickets:', error)
                this.tickets = [] // Clear tickets on error
            } finally {
                this.isLoading = false
            }
        },
        handleSearch() {
            clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.fetchData()
                this.searchTimeout = null
            }, 300)
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page && page !== this.filters.page) {
                this.filters.page = page
                this.fetchData()
            }
        },
        viewTicket(ticketId) {
            this.$router.push(`/admin/ticket/${ticketId}`)
        },
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        clearFilters() {
            this.filters.search = ''
            this.filters.status = ''
            this.fetchData()
        },
        formatDate(dateString) {
            if (!dateString) return '-'
            const options = { year: 'numeric', month: 'short', day: 'numeric' }
            return new Date(dateString).toLocaleDateString(undefined, options)
        },
    },
}
</script>
