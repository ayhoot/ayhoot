<template>
    <PageWrapper>
        <!-- Tabs -->
        <DashTabs :tabs="myTabs" />
        <!-- Header Section -->
        <div class="pb-4">
            <h2 class="text-3xl font-bold mb-2">{{ $t('ayhoot.tabs.assigned') || 'Assignments' }}</h2>
            <p>{{ $t('ayhoot.description') || 'All ayhoots assigned to you.' }}</p>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Table Header with Filters -->
            <div class="p-4 border-b border-gray-100">
                <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                    <!-- Left: Search -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto">
                        <AyInput type="text" v-model="filters.search" @update:model-value="handleSearch"
                            :placeholder="$t('ayhoot.searchPlaceholder') || 'Search assignments...'" class="bg-gray-50 font-bold min-w-80">
                            <template #icon>
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </template>
                        </AyInput>
                    </div>
                </div>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <DataTable v-if="filteredGames.length && !isLoading" :columns="assignmentColumns" :rows="filteredGames"
                    wrapper-class="" row-class="hover:bg-gray-50 transition-colors duration-150">
                    <template #cell:details="{ row: game }">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5.5 h-5.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div :class="$i18n.locale === 'fa' ? 'mr-4' : 'ml-4'">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-[18ch] overflow-hidden text-ellipsis"
                                    :title="game.title || game.exam?.title">
                                    {{ game.title || game.exam?.title }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ formatDate(game.created_at) }}
                                </div>
                            </div>
                        </div>
                    </template>
                    <template #cell:host="{ row: game }">
                        <div class="text-sm text-gray-900">
                            @{{ game.teacher?.username || game.institute?.username || game.exam?.teacher?.username || game.exam?.institute?.username || 'Instructor' }}
                        </div>
                    </template>
                    <template #cell:groups="{ row: game }">
                        <div class="text-sm text-gray-900">
                            {{ getGroupNames(game) }}
                        </div>
                    </template>
                    <template #cell:dueDate="{ row: game }">
                        <div class="text-sm text-gray-900">
                            {{ formatDate(game.end_at) }}
                        </div>
                    </template>
                    <template #cell:questions="{ row: game }">
                        <div class="text-sm text-gray-900">
                            {{ game.questions_count || game.exam?.questions_count || 0 }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $t('ayhoot.table.body.questions') || 'Questions' }}
                        </div>
                    </template>
                    <template #cell:status="{ row: game }">
                        <span v-if="game.student_attempt?.status === 'completed'"
                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            {{ $t('ayhoot.filters.completed') || 'Completed' }}
                        </span>
                        <span v-else-if="game.student_attempt?.status === 'started'"
                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            {{ $t('ayhoot.filters.active') || 'In Progress' }}
                        </span>
                        <span v-else
                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $t('ayhoot.filters.pending') || 'Pending' }}
                        </span>
                    </template>
                    <template #header:actions="{ column }">
                        <span class="sr-only">{{ column.label }}</span>
                    </template>
                    <template #cell:actions="{ row: game }">
                        <div class="flex items-center justify-end relative">
                            <ResponsiveButton type="light-blue"
                                :to="`/student/exam/${game.exam_id}/play?game=${game.id}`"
                                class="text-xs px-3 py-1 mx-0.5 font-bold">
                                {{ game.student_attempt?.status === 'completed' ? ($t('reportPage.header.report') || 'Results') : ($t('ayhoot.table.actions.play') || 'Play') }}
                            </ResponsiveButton>
                        </div>
                    </template>
                </DataTable>

                <!-- Empty State -->
                <div v-else-if="!isLoading" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $t('ayhoot.empty.title') || 'No assignments found' }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $t('ayhoot.empty.description') || 'Latest assignments will appear here.' }}</p>
                </div>

                <!-- Loading State -->
                <div v-else class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                    <p class="mt-2 text-sm text-gray-500">{{ $t('ayhoot.loading') || 'Loading your assignments...' }}</p>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination v-if="!isLoading && pagination.total > 0" :pagination="pagination" @page-changed="changePage"
                :wrapper-class="'px-6 py-4 border-t border-gray-100'" />
        </div>
    </PageWrapper>
</template>

<script>
import { apiCall } from '@/services/apiService'
import PageWrapper from '@/components/shared/PageWrapper.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashTabs from '@/components/layout/StudentDashTabs.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Pagination from '@/components/shared/Pagination.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'

export default {
    name: 'AssignmentsIndex',
    components: {
        PageWrapper,
        ResponsiveButton,
        DashTabs,
        DataTable,
        Pagination,
        AyInput,
    },
    data() {
        return {
            games: [],
            isLoading: false,
            filters: {
                search: '',
                page: 1,
            },
            pagination: {
                current_page: 1,
                from: 0,
                to: 0,
                total: 0,
                last_page: 1,
            },
            searchTimeout: null,
        }
    },
    computed: {
        myTabs() {
            return [
                { label: this.$t('ayhoot.tabs.ayhoots'), route: '/student/exam' },
                { label: this.$t('ayhoot.tabs.assigned'), route: '/student/exam/assignments' },
                { label: this.$t('ayhoot.tabs.courses'), route: '/student/courses' },
                { label: this.$t('ayhoot.tabs.purchased'), route: '/student/exam/purchased' },
            ]
        },
        assignmentColumns() {
            return [
                { key: 'details', label: this.$t('ayhoot.table.header.details') || 'Ayhoot Details', thClass: 'py-2' },
                { key: 'host', label: this.$t('ayhoot.table.header.creator') || 'Creator' },
                { key: 'groups', label: this.$t('ayhoot.assignModal.groups') || 'Groups' },
                { key: 'dueDate', label: this.$t('reportPage.info.dueDate') || 'Due Date' },
                { key: 'questions', label: this.$t('ayhoot.table.header.questions') || 'Questions' },
                { key: 'status', label: this.$t('ayhoot.table.header.status') || 'Status' },
                { key: 'actions', label: this.$t('ayhoot.table.header.actions') || 'Actions', align: 'right', thClass: 'relative', tdClass: 'font-medium' },
            ]
        },
        filteredGames() {
            return this.games
        },
    },
    methods: {
        async fetchGames() {
            try {
                this.isLoading = true
                const queryParams = new URLSearchParams({
                    page: this.filters.page,
                    search: this.filters.search,
                }).toString()
                const response = await apiCall('GET', `/api/student/game?${queryParams}`)
                this.games = response.data || []
                this.pagination = {
                    current_page: response.current_page || 1,
                    from: response.from || 0,
                    to: response.to || 0,
                    total: response.total || 0,
                    last_page: response.last_page || 1,
                }
            } catch (error) {
                console.error('Error fetching assignments:', error)
                this.$toast?.error(error.message || 'Failed to load assignments.')
            } finally {
                this.isLoading = false
            }
        },
        getGroupNames(game) {
            if (!game.groups || game.groups.length === 0) return '—'
            return game.groups.map(g => g.name || g.title).join(', ')
        },
        handleSearch() {
            if (this.searchTimeout) clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.filters.page = 1
                this.fetchGames()
            }, 300)
        },
        changePage(page) {
            this.filters.page = page
            this.fetchGames()
        },
        formatDate(date) {
            if (!date) return '—'
            try {
                const locale = this.$i18n.locale === 'fa' ? 'fa-IR' : 'en-US'
                return new Date(date).toLocaleDateString(locale, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                })
            } catch (e) {
                return '—'
            }
        },
    },
    async mounted() {
        await this.fetchGames()
    },
    beforeUnmount() {
        if (this.searchTimeout) clearTimeout(this.searchTimeout)
    },
}
</script>

<style scoped>
</style>
