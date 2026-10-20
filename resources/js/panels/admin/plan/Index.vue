<template>
    <div class="bg-gray-50 min-h-screen">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" />
        </div>
        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />
            <div class="flex-1 sm:ml-64 p-6 overflow-y-auto mt-16">
                <h2 class="text-2xl font-semibold mb-6">Plans</h2>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-100">
                        <div class="flex flex-col sm:flex-row justify-end items-center space-y-4 sm:space-y-0">
                            <AyButton to="/admin/plan/create" type="dash-blue" class="inline-flex items-center">
                                Create Plan
                            </AyButton>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <DataTable
                            v-if="filteredPlans.length"
                            :columns="planColumns"
                            :rows="filteredPlans"
                            :wrapper-class="''"
                            row-class="hover:bg-gray-50 transition-colors duration-150"
                        >
                            <template #header:actions><span class="sr-only">Actions</span></template>
                            <template #cell:name="{ row: plan }">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-xl flex items-center justify-center"
                                    >
                                        <svg
                                            width="18"
                                            height="18"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M8.625 19.3333V6.28571M14.8125 17.1429V4.02679M3 17.7143V4L8.47826 6.28571L14.7391 4L21 6.28571V20L14.7391 17.7143L8.47826 20L3 17.7143Z"
                                                stroke="#00985D"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ plan.title || 'unknown' }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template #cell:price="{ row: plan }">
                                <div class="text-sm text-gray-900">{{ plan.price || 'N/A' }}</div>
                                <div class="text-xs text-gray-500">in Rials</div>
                            </template>
                            <template #cell:users="{ row: plan }">
                                <div class="text-sm text-gray-900">{{ plan.user || '0' }}</div>
                                <div class="text-xs text-gray-500">Subscribers</div>
                            </template>
                            <template #cell:actions="{ row: plan }">
                                <div class="flex items-center justify-end gap-1">
                                    <ResponsiveButton
                                        type="light-blue"
                                        :to="`/admin/plan/${plan.id}/edit`"
                                        class="text-xs px-2 py-1 mx-0.5"
                                        >View</ResponsiveButton
                                    >
                                    <ResponsiveButton
                                        type="dash-blue"
                                        :to="`/admin/plan/${plan.id}/edit`"
                                        class="text-xs px-2 py-1 mx-0.5"
                                        >Edit</ResponsiveButton
                                    >
                                    <ResponsiveButton
                                        @click="confirmDelete(plan.id)"
                                        type="light-red"
                                        class="text-xs px-2 py-1 mx-0.5"
                                        >Delete</ResponsiveButton
                                    >
                                </div>
                            </template>
                        </DataTable>
                        <div v-else-if="!isLoading" class="text-center py-12">
                            <svg
                                class="mx-auto h-12 w-12 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No plans found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new plan.</p>
                            <div class="mt-6">
                                <AyButton to="/admin/plan/create" type="dash-blue" class="inline-flex items-center"
                                    >+ Create Plan</AyButton
                                >
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <div
                                class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"
                            ></div>
                            <p class="mt-2 text-sm text-gray-500">Loading plans...</p>
                        </div>
                    </div>
                    <Pagination
                        v-if="!isLoading"
                        :pagination="pagination"
                        @page-changed="changePage"
                        :wrapper-class="'px-6 py-4 border-t border-gray-200 bg-white'"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Pagination from '@/components/shared/Pagination.vue'
import { apiCall } from '@/services/apiService'
import { useToast } from 'vue-toast-notification'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'AdminPlan',
    components: {
        Sidebar,
        Header,
        AyButton,
        ResponsiveButton,
        DataTable,
        Pagination,
    },
    setup() {
        const $toast = useToast()
        return { $toast }
    },
    data() {
        return {
            plans: [],
            isSidebarOpen: false,
            isLoading: false,
            filters: {
                search: '',
                plan: '',
                sort: 'created_at',
                page: 1,
            },
            pagination: {
                current_page: 1,
                from: 0,
                to: 0,
                total: 0,
                last_page: 0,
            },
        }
    },
    computed: {
        filteredPlans() {
            return this.plans
        },
        planColumns() {
            return [
                { key: 'name', label: 'Name', align: 'left', thClass: 'font-medium tracking-wider' },
                { key: 'price', label: 'Price', align: 'left', thClass: 'font-medium tracking-wider' },
                { key: 'users', label: 'Users', align: 'left', thClass: 'font-medium tracking-wider' },
                { key: 'actions', label: '', align: 'right', thClass: 'relative', tdClass: 'font-medium' },
            ]
        },
    },
    async mounted() {
        await this.fetchData()
    },
    methods: {
        async fetchData() {
            this.isLoading = true
            try {
                const queryParams = new URLSearchParams(this.filters).toString()
                const response = await apiCall('GET', `/api/admin/plan?${queryParams}`)
                this.plans = response.plans.data || []
                this.pagination = {
                    current_page: response.plans.current_page || 1,
                    from: response.plans.from || 0,
                    to: response.plans.to || 0,
                    total: response.plans.total || 0,
                    last_page: response.plans.last_page || 1,
                }
            } catch (error) {
                this.$toast.error('Failed to load plans.')
            } finally {
                this.isLoading = false
            }
        },
        handleSearch() {
            if (this.searchTimeout) clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.filters.page = 1
                this.fetchData()
            }, 300)
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page && page !== this.filters.page) {
                this.filters.page = page
                this.fetchData()
            }
        },
        async confirmDelete(planId) {
            if (await confirmDialog({ message: 'Are you sure you want to delete this plan? This action cannot be undone.', variant: 'danger' })) {
                try {
                    await apiCall('GET', `/api/admin/plan/delete/${planId}`)
                    this.$toast.success('Plan deleted successfully.')
                    await this.fetchData()
                } catch (error) {
                    this.$toast.error(error.message || 'Failed to delete plan.')
                }
            }
        },
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
    },
}
</script>
