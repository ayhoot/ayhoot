<template>
    <div class="bg-gray-50">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" :teacherName="teacherName" :teacherUsername="teacherUsername" />
        </div>

        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />

            <main
                class="flex-1 bg-gray-50 ml-0 sm:ml-0 md:ml-0 lg:ml-0 xl:ml-64 p-6 overflow-y-auto mt-16 min-h-[calc(100vh-4rem)]"
            >
                <div>
                    <div class="pb-4">
                        <h2 class="text-3xl font-bold mb-2">Manage News</h2>
                        <p class="text-gray-600">Manage all news articles</p>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                                <div
                                    class="flex flex-col sm:flex-row items-start sm:items-center flex-wrap gap-4 w-full sm:w-auto"
                                >
                                    <div class="relative w-full sm:flex-1 sm:min-w-[200px]">
                                        <input
                                            type="text"
                                            v-model="filters.search"
                                            @input="handleSearch"
                                            placeholder="Search news articles..."
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
                                    <select
                                        v-model="filters.sort"
                                        class="w-full sm:w-auto px-4 p-2 text-sm font-bold text-gray-900 bg-gray-50 rounded-sm border-2 border-gray-300 focus:outline-none focus:border-blue-500"
                                    >
                                        <option value="created_at_desc">Newest First</option>
                                        <option value="created_at_asc">Oldest First</option>
                                        <option value="title_asc">Title (A-Z)</option>
                                        <option value="title_desc">Title (Z-A)</option>
                                    </select>
                                </div>
                                <ResponsiveButton to="/admin/news/create" type="light-blue" class="p-2"
                                    >+ Create</ResponsiveButton
                                >
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <div v-if="isLoading" class="text-center py-12">
                                <div
                                    class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"
                                ></div>
                                <p class="mt-2 text-sm text-gray-500">Loading news articles...</p>
                            </div>
                            <DataTable
                                v-else-if="paginatedNews.length"
                                :columns="newsColumns"
                                :rows="paginatedNews"
                                :wrapper-class="''"
                                row-class="hover:bg-gray-50"
                            >
                                <template #header:actions><span class="sr-only">Actions</span></template>
                                <template #cell:title="{ row: news }">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-12 w-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600"
                                        >
                                            <svg
                                                width="20"
                                                height="20"
                                                viewBox="0 0 25 24"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    d="M2.89988 3.6001L22.0999 3.6001M12.4999 8.8001L22.0999 8.8001M12.4999 14.0001L22.0999 14.0001M2.89988 19.2001H22.0999M2.89988 14.4001L2.89941 8.4001L7.69988 11.4001L2.89988 14.4001Z"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div
                                                class="text-sm font-medium text-gray-900 truncate max-w-lg"
                                                :title="news.title"
                                            >
                                                {{ news.title }}
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template #cell:created="{ row: news }">{{ formatDate(news.created_at) }}</template>
                                <template #cell:actions="{ row: news }">
                                    <div class="flex items-center justify-end gap-2">
                                        <ResponsiveButton
                                            type="light-blue"
                                            class="text-xs px-3 py-1"
                                            @click="editNews(news.id)"
                                            >Edit</ResponsiveButton
                                        >
                                        <ResponsiveButton
                                            type="light-red"
                                            class="text-xs px-3 py-1"
                                            @click="removeNews(news.id)"
                                            >Remove</ResponsiveButton
                                        >
                                    </div>
                                </template>
                            </DataTable>
                            <div v-else class="text-center py-12">
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No news articles found</h3>
                                <p class="mt-1 text-sm text-gray-500">Try adjusting your search.</p>
                            </div>
                        </div>

                        <Pagination
                            v-if="!isLoading"
                            :pagination="pagination"
                            @page-changed="fetchData"
                            :wrapper-class="'px-6 py-4 border-t border-gray-100'"
                        />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script>
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Pagination from '@/components/shared/Pagination.vue'
import { apiCall } from '@/services/apiService'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'NewsIndex',
    components: { Sidebar, Header, ResponsiveButton, DataTable, Pagination },
    data() {
        return {
            isSidebarOpen: false,
            isLoading: true,
            newsItems: [],
            pagination: {},
            filters: {
                search: '',
                sort: 'created_at_desc',
            },
            searchTimeout: null,
        }
    },
    computed: {
        teacherName: () => JSON.parse(localStorage.getItem('user') || '{}').name || 'Admin',
        teacherUsername: () => JSON.parse(localStorage.getItem('user') || '{}').username || 'admin',
        newsColumns() {
            return [
                { key: 'title', label: 'Title', align: 'left', thClass: 'font-medium' },
                {
                    key: 'created',
                    label: 'Date Created',
                    align: 'left',
                    thClass: 'font-medium',
                    tdClass: 'text-gray-500',
                },
                { key: 'actions', label: '', align: 'right', thClass: 'relative', tdClass: 'font-medium' },
            ]
        },
        paginatedNews() {
            const items = [...this.newsItems]
            const [sortBy, sortOrder] = this.filters.sort.split('_')

            return items.sort((a, b) => {
                let valA = a[sortBy]
                let valB = b[sortBy]

                if (sortBy === 'title') {
                    valA = valA.toLowerCase()
                    valB = valB.toLowerCase()
                }

                if (valA < valB) return sortOrder === 'asc' ? -1 : 1
                if (valA > valB) return sortOrder === 'asc' ? 1 : -1
                return 0
            })
        },
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        async fetchData(page = 1) {
            this.isLoading = true
            const params = new URLSearchParams({ page, search: this.filters.search })
            const response = await apiCall('GET', `/api/admin/news?${params.toString()}`)
            this.newsItems = response.news.data
            this.pagination = {
                current_page: response.news.current_page,
                from: response.news.from,
                to: response.news.to,
                total: response.news.total,
                last_page: response.news.last_page,
                next_page_url: response.news.next_page_url,
                prev_page_url: response.news.prev_page_url,
            }
            this.isLoading = false
        },
        handleSearch() {
            clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.fetchData(1)
            }, 500)
        },
        formatDate: (dateString) =>
            dateString
                ? new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
                : 'N/A',
        editNews(newsId) {
            this.$router.push(`/admin/news/edit/${newsId}`)
        },
        async removeNews(newsId) {
            if (
                await confirmDialog({
                    message: 'Are you sure you want to remove this news article?',
                    variant: 'danger',
                })
            ) {
                await apiCall('GET', `/api/admin/news/delete/${newsId}`)
                await this.fetchData(this.pagination.current_page)
            }
        },
    },
    mounted() {
        this.fetchData()
    },
}
</script>
