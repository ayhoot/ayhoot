<template>
    <PageWrapper>
                <div>
                    <!-- Tabs -->
                    <DashTabs :tabs="translatedTabs" />
                    <!-- Header Section -->
                    <div class="pb-4">
                        <h2 class="text-3xl font-bold mb-2">{{ $t('report.title') }}</h2>
                        <p class="">{{ $t('report.subtitle') }}</p>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <!-- Table Header with Filters -->
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                                <div
                                    class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto"
                                >
                                    <!-- Search -->
                                    <AyInput
                                        type="text"
                                        v-model="filters.search"
                                        :placeholder="$t('report.searchPlaceholder')"
                                    >
                                        <template #icon>
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
                                        </template>
                                    </AyInput>
                                    <!-- Status Filter -->
                                    <AySelect v-model="filters.status">
                                        <!-- UPDATED STATUS OPTIONS -->
                                        <option value="">{{ $t('report.filters.allStatus') }}</option>
                                        <option value="completed">{{ $t('report.status.completed') }}</option>
                                        <option value="started">{{ $t('report.status.started') }}</option>
                                    </AySelect>
                                    <!-- Sort By -->
                                    <AySelect v-model="filters.sort">
                                        <option value="created_at">{{ $t('report.filters.dateCreated') }}</option>
                                        <option value="title">{{ $t('report.filters.title') }}</option>
                                        <option value="status">{{ $t('report.filters.status') }}</option>
                                    </AySelect>
                                </div>
                            </div>
                        </div>

                        <!-- Table Content -->
                        <div class="overflow-x-auto">
                            <DataTable
                                v-if="paginatedExams.length && !isLoading"
                                :columns="reportColumns"
                                :rows="paginatedExams"
                                wrapper-class=""
                                row-class="hover:bg-gray-50 transition-colors duration-150"
                                row-key="id"
                            >
                                <template #cell:details="{ row: attempt }">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex-shrink-0 h-12 w-12 bg-purple-100 rounded-xl flex items-center justify-center"
                                        >
                                            <svg
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    d="M15.0001 2.3999V5.9999C15.0001 6.66264 15.5373 7.1999 16.2001 7.1999H19.8001M8.40008 17.9999V13.1999M12.0001 17.9999L12.0001 8.3999M15.6001 17.9999V13.1999M18.0001 4.1999C17.466 3.72204 16.9118 3.15528 16.5619 2.78718C16.3291 2.54224 16.0074 2.3999 15.6695 2.3999H6.5998C5.27432 2.3999 4.19981 3.47441 4.1998 4.79989L4.19971 19.1998C4.1997 20.5253 5.27421 21.5998 6.5997 21.5998L17.3997 21.5999C18.7252 21.5999 19.7997 20.5254 19.7998 19.2L19.8001 6.47773C19.8001 6.1709 19.683 5.87594 19.4701 5.65503C19.0763 5.24655 18.4187 4.57442 18.0001 4.1999Z"
                                                    class="stroke-current text-purple-600"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </div>
                                        <div class="">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="text-sm font-medium text-gray-900 truncate max-w-[16ch] overflow-hidden text-ellipsis"
                                                    :title="attempt.game?.title || attempt.exam?.title || attempt.game?.exam?.title || 'Untitled Exam'"
                                                >
                                                    {{ attempt.game?.title || attempt.exam?.title || attempt.game?.exam?.title || 'Untitled Exam' }}
                                                </div>
                                                <!-- ASSIGNMENT BADGE -->
                                                <span v-if="attempt.game_id" class="px-2 py-0.5 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">
                                                    {{ $t('report.table.assignmentBadge') }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ formatDate(attempt.created_at) }}
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template #cell:status="{ row: attempt }">
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                        :class="{
                                            'bg-blue-100 text-blue-800': attempt.status === 'started', // <-- ADDED STYLE
                                            'bg-green-100 text-green-800':
                                                attempt.status === 'published' || attempt.status === 'active',
                                            'bg-yellow-100 text-yellow-800':
                                                attempt.status === 'draft' || attempt.status === 'in-progress',
                                            'bg-gray-100 text-gray-800': attempt.status === 'completed',
                                            'bg-red-100 text-red-800': attempt.status === 'archived',
                                        }"
                                    >
                                        {{
                                            $t('report.status.' + (attempt.status ? attempt.status.toLowerCase() : 'unknown'), attempt.status || 'N/A')
                                        }}
                                    </span>
                                </template>
                                <template #cell:category="{ row: attempt }">
                                    <div class="text-sm text-gray-900">
                                        {{ attempt.exam?.category?.title || attempt.game?.exam?.category?.title || 'N/A' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $t('report.table.sublabel.category') }}
                                    </div>
                                </template>
                                <template #cell:completedAt="{ row: attempt }">
                                    {{ formatDate(attempt.completed_at) }}
                                </template>
                                <template #cell:visibility="{ row: attempt }">
                                    <div class="text-sm text-gray-900">
                                        {{ attempt.exam?.visibility || attempt.game?.exam?.visibility || 'Public' }}
                                    </div>
                                </template>
                                <template #header:actions="{ column }">
                                    <span class="sr-only">{{ column.label }}</span>
                                </template>
                                <template #cell:actions="{ row: attempt }">
                                    <div class="flex items-center justify-end relative">
                                        <ResponsiveButton
                                            type="light-blue"
                                            @click="viewReport(attempt.id)"
                                            class="text-xs px-2 py-1 mx-0.5 cursor-pointer"
                                            >{{ $t('report.table.actions.view') }}</ResponsiveButton
                                        >
                                        <ResponsiveButton type="light-gray" class="text-xs px-2 py-1 mx-0.5">{{
                                            $t('report.table.actions.download')
                                        }}</ResponsiveButton>
                                    </div>
                                </template>
                            </DataTable>

                            <div v-else-if="isLoading" class="text-center py-12">
                                <div
                                    class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"
                                ></div>
                                <p class="mt-2 text-sm text-gray-500">{{ $t('report.loading') }}</p>
                            </div>

                            <div v-else class="text-center py-12">
                                <svg
                                    width="36"
                                    height="36"
                                    class="mx-auto"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M15.0001 2.3999V5.9999C15.0001 6.66264 15.5373 7.1999 16.2001 7.1999H19.8001M8.40008 17.9999V13.1999M12.0001 17.9999L12.0001 8.3999M15.6001 17.9999V13.1999M18.0001 4.1999C17.466 3.72204 16.9118 3.15528 16.5619 2.78718C16.3291 2.54224 16.0074 2.3999 15.6695 2.3999H6.5998C5.27432 2.3999 4.19981 3.47441 4.1998 4.79989L4.19971 19.1998C4.1997 20.5253 5.27421 21.5998 6.5997 21.5998L17.3997 21.5999C18.7252 21.5999 19.7997 20.5254 19.7998 19.2L19.8001 6.47773C19.8001 6.1709 19.683 5.87594 19.4701 5.65503C19.0763 5.24655 18.4187 4.57442 18.0001 4.1999Z"
                                        stroke="black"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">
                                    {{ $t('report.emptyState.title') }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">{{ $t('report.emptyState.subtitle') }}</p>
                                <div class="mt-6">
                                    <AyButton
                                        to="/student/discover"
                                        type="dash-blue"
                                        class="inline-flex items-center"
                                        >{{ $t('report.emptyState.playButton') }}</AyButton
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <Pagination
                            v-if="!isLoading"
                            :pagination="paginationData"
                            @page-changed="changePage"
                            :wrapper-class="'px-6 py-4 border-t border-gray-100'"
                        />
                    </div>
                </div>
    </PageWrapper>
</template>

<script>
import { apiCall } from '@/services/apiService'
import PageWrapper from '@/components/shared/PageWrapper.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashRemove from '@/components/atoms/icons/DashRemove.vue'
import DashShare from '@/components/atoms/icons/DashShare.vue'
import DashTabs from '@/components/layout/StudentDashTabs.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Pagination from '@/components/shared/Pagination.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

export default {
    name: 'ExamIndex',
    components: {
        PageWrapper,
        AyButton,
        ResponsiveButton,
        DashRemove,
        DashShare,
        DashTabs,
        DataTable,
        Pagination,
        AySelect,
        AyInput,
        RouterLink,
    },
    setup() {
        const { t, locale } = useI18n()
        return { t, locale }
    },
    data() {
        return {
            exams: [], // This holds ALL reports from the API
            isLoading: true,
            filters: {
                search: '',
                status: '',
                sort: 'created_at',
            },
            pagination: {
                current_page: 1,
                per_page: 10,
            },
        }
    },
    computed: {
        reportColumns() {
            return [
                { key: 'details', label: this.t('report.table.header.details'), thClass: 'py-2' },
                { key: 'status', label: this.t('report.table.header.status') },
                { key: 'category', label: this.t('report.table.header.category') },
                { key: 'completedAt', label: this.t('report.table.header.completedAt'), tdClass: 'text-gray-900' },
                { key: 'visibility', label: this.t('report.table.header.visibility') },
                { key: 'actions', label: this.t('report.table.header.actions'), align: 'right', thClass: 'relative', tdClass: 'font-medium' },
            ]
        },
        translatedTabs() {
            return [
                { label: this.t('dashboard.sidebar.menu.ayhoots'), route: '/student/exam' },
                { label: this.t('dashboard.sidebar.menu.courses'), route: '/student/courses' },
                { label: this.t('dashboard.sidebar.menu.purchased'), route: '/student/exam/purchased' },
            ]
        },
        filteredExams() {
            let attempts = [...this.exams]

            if (this.filters.search) {
                const searchTerm = this.filters.search.toLowerCase()
                attempts = attempts.filter((attempt) => {
                    const title = (attempt.game?.title || attempt.exam?.title || attempt.game?.exam?.title || '').toLowerCase()
                    return title.includes(searchTerm)
                })
            }

            if (this.filters.status) {
                attempts = attempts.filter((attempt) => attempt.status === this.filters.status)
            }

            attempts.sort((a, b) => {
                const sortKey = this.filters.sort
                if (sortKey === 'title') {
                    const titleA = (a.game?.title || a.exam?.title || a.game?.exam?.title || '').toLowerCase()
                    const titleB = (b.game?.title || b.exam?.title || b.game?.exam?.title || '').toLowerCase()
                    return titleA.localeCompare(titleB)
                }
                if (sortKey === 'created_at') {
                    return new Date(b.created_at) - new Date(a.created_at)
                }
                return (a[sortKey] || '').localeCompare(b[sortKey] || '')
            })

            return attempts
        },
        paginationInfo() {
            const total = this.filteredExams.length
            const last_page = Math.ceil(total / this.pagination.per_page)
            const from = total > 0 ? (this.pagination.current_page - 1) * this.pagination.per_page + 1 : 0
            const to = Math.min(this.pagination.current_page * this.pagination.per_page, total)

            return { total, last_page, from, to }
        },
        paginationData() {
            return {
                ...this.paginationInfo,
                current_page: this.pagination.current_page,
            }
        },
        paginatedExams() {
            const start = (this.pagination.current_page - 1) * this.pagination.per_page
            const end = start + this.pagination.per_page
            return this.filteredExams.slice(start, end)
        },
    },
    watch: {
        filters: {
            handler() {
                this.pagination.current_page = 1
            },
            deep: true,
        },
    },
    methods: {
        viewReport(attemptId) {
            if (attemptId) {
                this.$router.push(`/student/report/assigned-ayhoots/${attemptId}`)
            } else {
                console.error('Cannot navigate: Attempt ID is missing.')
            }
        },
        async fetchExams() {
            this.isLoading = true
            try {
                const response = await apiCall('GET', `/api/student/get-reports`)
                this.exams = response.answers.data || []
            } catch (error) {
                console.error('Error fetching exams:', error)
                this.exams = []
            } finally {
                this.isLoading = false
            }
        },
        changePage(page) {
            if (page >= 1 && page <= this.paginationInfo.last_page) {
                this.pagination.current_page = page
            }
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A'
            try {
                return new Date(dateString).toLocaleDateString(this.locale, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                })
            } catch (e) {
                return 'Invalid Date'
            }
        },
    },
    async mounted() {
        await this.fetchExams()
    },
}
</script>