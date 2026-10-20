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
                        <h2 class="text-3xl font-bold mb-2">Activity Log</h2>
                        <p class="text-gray-600">View and filter all system and user activities</p>
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
                                            v-model="searchQuery"
                                            @input="handleSearch"
                                            type="text"
                                            placeholder="Search logs..."
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
                                        v-model="roleFilter"
                                        @change="applyFilters"
                                        class="w-full sm:w-auto px-4 p-2 text-sm font-bold text-gray-900 bg-gray-50 rounded-sm border-2 border-gray-300 focus:outline-none"
                                    >
                                        <option value="">All Roles</option>
                                        <option value="admin">Admin</option>
                                        <option value="teacher">Teacher</option>
                                        <option value="institute">Institute</option>
                                        <option value="student">Student</option>
                                    </select>
                                    <select
                                        v-model="sortOrder"
                                        @change="applyFilters"
                                        class="w-full sm:w-auto px-4 p-2 text-sm font-bold text-gray-900 bg-gray-50 rounded-sm border-2 border-gray-300 focus:outline-none"
                                    >
                                        <option value="desc">Newest First</option>
                                        <option value="asc">Oldest First</option>
                                    </select>
                                    <select
                                        v-model="perPage"
                                        @change="applyFilters"
                                        class="w-full sm:w-auto px-4 p-2 text-sm font-bold text-gray-900 bg-gray-50 rounded-sm border-2 border-gray-300 focus:outline-none"
                                    >
                                        <option :value="10">10 / page</option>
                                        <option :value="25">25 / page</option>
                                        <option :value="50">50 / page</option>
                                        <option :value="100">100 / page</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Table Content -->
                        <div class="overflow-x-auto">
                            <DataTable
                                v-if="activities.data && activities.data.length"
                                :columns="activityColumns"
                                :rows="activities.data"
                                :wrapper-class="''"
                                :td-base-class="'px-6 py-4 text-sm'"
                                row-class="hover:bg-gray-50 transition-colors duration-150"
                            >
                                <template #cell:user="{ row: activity }">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div
                                                class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm"
                                            >
                                                {{ getUserInitial(activity) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ getUserName(activity) }}
                                            </div>
                                            <div class="text-sm text-gray-500 capitalize">
                                                {{ getUserRole(activity) }}
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template #cell:log="{ row: activity }">
                                    <div class="text-sm text-gray-700 max-w-lg break-words">
                                        {{ activity.log }}
                                    </div>
                                </template>
                                <template #cell:date="{ row: activity }">
                                    {{ formatDate(activity.created_at) }}
                                </template>
                            </DataTable>
                            <div v-else-if="!loading" class="text-center py-12">
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No activities found</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    There are no activities matching your current filters.
                                </p>
                            </div>
                            <div v-else class="text-center py-12">
                                <div
                                    class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"
                                ></div>
                                <p class="mt-2 text-sm text-gray-500">Loading activities...</p>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <Pagination v-if="!loading" :pagination="pagination" @page-changed="fetchData" />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script>
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Pagination from '@/components/shared/Pagination.vue'
import { apiCall } from '@/services/apiService'

export default {
    name: 'AdminActivity',
    components: { Sidebar, Header, DataTable, Pagination },
    computed: {
        activityColumns() {
            return [
                { key: 'user', label: 'User', tdClass: 'whitespace-nowrap' },
                { key: 'log', label: 'Log' },
                { key: 'date', label: 'Date', tdClass: 'whitespace-nowrap text-gray-500' },
            ]
        },
    },
    data() {
        return {
            activities: { data: [] },
            pagination: {},
            isSidebarOpen: false,
            loading: false,
            searchQuery: '',
            roleFilter: '',
            sortBy: 'created_at', // This is now a fixed value as per the UI design
            sortOrder: 'desc',
            perPage: 10,
            searchTimeout: null,
        }
    },
    async mounted() {
        await this.fetchData(1)
    },
    methods: {
        async fetchData(page = 1) {
            if (page < 1 || (page > this.pagination.last_page && this.pagination.last_page)) return
            this.loading = true
            try {
                const params = new URLSearchParams()
                params.append('page', page)

                if (this.searchQuery) {
                    params.append('search', this.searchQuery)
                }

                if (this.roleFilter) {
                    params.append(`${this.roleFilter}_id`, true)
                }

                params.append('sort_by', this.sortBy)
                params.append('sort_order', this.sortOrder)
                params.append('per_page', this.perPage)

                const response = await apiCall('GET', `/api/admin/activity?${params.toString()}`)
                this.activities = response.activities
                this.pagination = {
                    current_page: response.activities.current_page,
                    from: response.activities.from,
                    to: response.activities.to,
                    total: response.activities.total,
                    last_page: response.activities.last_page,
                }
            } catch (error) {
                console.error('Error fetching activities:', error)
            } finally {
                this.loading = false
            }
        },
        handleSearch() {
            clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.fetchData(1)
            }, 300)
        },
        applyFilters() {
            this.fetchData(1)
        },
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        getUserName(activity) {
            if (typeof activity.user === 'object' && activity.user !== null) {
                return activity.user.name
            }
            return activity.user || 'System'
        },
        getUserInitial(activity) {
            const name = this.getUserName(activity)
            return name ? name.charAt(0).toUpperCase() : 'S'
        },
        getUserRole(activity) {
            if (typeof activity.user === 'object' && activity.user !== null) {
                return activity.user.role
            }
            return 'system'
        },
        formatDate(dateString) {
            if (!dateString) return ''
            const date = new Date(dateString)
            return new Intl.DateTimeFormat('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            }).format(date)
        },
    },
}
</script>
