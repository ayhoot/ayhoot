<template>
    <InstructorLayout :secondary="false">
                <div class="">
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
                                    <div class="w-full sm:flex-1">
                                        <AyInput
                                            v-model="filters.search"
                                            type="text"
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
                                    </div>
                                    <!-- Status Filter -->
                                    <AySelect v-model="filters.status">
                                        <option value="">{{ $t('report.filters.allStatus') }}</option>
                                        <option value="active">{{ $t('report.status.active') }}</option>
                                        <option value="draft">{{ $t('report.status.draft') }}</option>
                                        <option value="completed">{{ $t('report.status.completed') }}</option>
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
                                v-if="filteredExams.length && !isLoading"
                                :columns="examColumns"
                                :rows="filteredExams"
                                wrapper-class=""
                                row-class="hover:bg-gray-50 transition-colors duration-150"
                            >
                                <template #header:actions="{ column }">
                                    <span class="sr-only">{{ column.label }}</span>
                                </template>
                                <template #cell:details="{ row: exam }">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex-shrink-0 h-12 w-12 rounded-xl flex items-center justify-center"
                                            :class="exam.live_session_id ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600'"
                                        >
                                            <svg
                                                v-if="exam.live_session_id"
                                                class="w-6 h-6 text-purple-600"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M13 10V3L4 14h7v7l9-11h-7z"
                                                />
                                            </svg>
                                            <svg
                                                v-else
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    d="M15.0001 2.3999V5.9999C15.0001 6.66264 15.5373 7.1999 16.2001 7.1999H19.8001M8.40008 17.9999V13.1999M12.0001 17.9999L12.0001 8.3999M15.6001 17.9999V13.1999M18.0001 4.1999C17.466 3.72204 16.9118 3.15528 16.5619 2.78718C16.3291 2.54224 16.0074 2.3999 15.6695 2.3999H6.5998C5.27432 2.3999 4.19981 3.47441 4.1998 4.79989L4.19971 19.1998C4.1997 20.5253 5.27421 21.5998 6.5997 21.5998L17.3997 21.5999C18.7252 21.5999 19.7997 20.5254 19.7998 19.2L19.8001 6.47773C19.8001 6.1709 19.683 5.87594 19.4701 5.65503C19.0763 5.24655 18.4187 4.57442 18.0001 4.1999Z"
                                                    class="stroke-current text-blue-600"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </div>
                                        <div class="">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    v-if="exam.live_session_id"
                                                    class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md bg-purple-100 text-purple-700 border border-purple-200 shrink-0"
                                                >
                                                    Host Live
                                                </span>
                                                <span
                                                    v-else
                                                    class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md bg-blue-100 text-blue-700 border border-blue-200 shrink-0"
                                                >
                                                    Assignment
                                                </span>
                                                <div
                                                    class="text-sm font-medium text-gray-900 truncate max-w-[16ch] sm:max-w-[28ch] overflow-hidden text-ellipsis"
                                                    :title="exam.title"
                                                >
                                                    {{ exam.title }}
                                                </div>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ formatDate(exam.created_at) }}
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template #cell:status="{ row: exam }">
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                        :class="{
                                            'bg-green-100 text-green-800':
                                                exam.status === 'published' || exam.status === 'active' || exam.status === 'completed',
                                            'bg-yellow-100 text-yellow-800': exam.status === 'draft',
                                            'bg-gray-100 text-gray-800': exam.status === 'completed',
                                            'bg-red-100 text-red-800': exam.status === 'archived',
                                        }"
                                    >
                                        {{ $t('report.status.' + exam.status.toLowerCase(), exam.status) }}
                                    </span>
                                </template>
                                <template #cell:plays="{ row: exam }">
                                    <div class="text-sm text-gray-900">{{ exam.students_count || 0 }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $t('report.table.sublabel.tutees') }}
                                    </div>
                                </template>
                                <template #cell:questions="{ row: exam }">
                                    <div class="text-sm text-gray-900">{{ exam.questions_count || 0 }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $t('report.table.sublabel.questions') }}
                                    </div>
                                </template>
                                <template #cell:visibility="{ row: exam }">
                                    <div class="text-sm text-gray-900">{{ exam.visibility || 'Public' }}</div>
                                </template>
                                <template #cell:actions="{ row: exam }">
                                    <div class="flex items-center justify-end relative">
                                        <ResponsiveButton
                                            type="light-blue"
                                            @click="viewReport(exam)"
                                            class="text-xs px-2 py-1 mx-0.5 cursor-pointer"
                                            >{{ $t('report.table.actions.view') }}</ResponsiveButton
                                        >
                                        <ResponsiveButton
                                            type="light-gray"
                                            @click="downloadReport(exam)"
                                            class="text-xs px-2 py-1 mx-0.5 cursor-pointer"
                                            >{{ $t('report.table.actions.download') }}</ResponsiveButton
                                        >
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
                                    <AyButton :to="`/${$role}/exam`" type="dash-blue" class="inline-flex items-center">{{
                                        $t('report.emptyState.assignButton')
                                    }}</AyButton>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <Pagination
                            v-if="!isLoading"
                            :pagination="pagination"
                            @page-changed="changePage"
                            :wrapper-class="'px-6 py-4 border-t border-gray-100'"
                        />
                    </div>
                </div>
    </InstructorLayout>
</template>

<script>
import { apiCall } from "@/services/apiService"
import { getRole } from '@/utils/role'
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashRemove from '@/components/atoms/icons/DashRemove.vue'
import DashShare from '@/components/atoms/icons/DashShare.vue'
import DashTabs from '@/components/layout/InstructorDashTabs.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Pagination from '@/components/shared/Pagination.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

export default {
    name: 'ExamIndex',
    components: {
        InstructorLayout,
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
            exams: [], // Raw data from API
            isLoading: true,
            filters: {
                search: '',
                status: '',
                sort: 'created_at',
                page: 1,
            },
            pagination: {
                current_page: 1,
                from: 0,
                to: 0,
                total: 0,
                last_page: 1,
            },
        }
    },
    computed: {
        examColumns() {
            return [
                { key: 'details', label: this.$t('report.table.header.details'), thClass: 'py-2 font-medium' },
                { key: 'status', label: this.$t('report.table.header.status'), thClass: 'font-medium' },
                { key: 'plays', label: this.$t('report.table.header.plays'), thClass: 'font-medium' },
                { key: 'questions', label: this.$t('report.table.header.questions'), thClass: 'font-medium' },
                { key: 'visibility', label: this.$t('report.table.header.visibility'), thClass: 'font-medium' },
                { key: 'actions', label: this.$t('report.table.header.actions'), align: 'right', thClass: 'relative', tdClass: 'font-medium' },
            ]
        },
        translatedTabs() {
            return [
                { label: this.t('dashboard.sidebar.menu.ayhoots'), route: `/${getRole()}/exam` },
                { label: this.t('dashboard.sidebar.menu.courses'), route: `/${getRole()}/exam/courses` },
                { label: this.t('dashboard.sidebar.menu.purchased'), route: `/${getRole()}/exam/purchased` },
                { label: this.t('dashboard.sidebar.menu.folders'), route: `/${getRole()}/exam/folders` },
                { label: this.t('dashboard.sidebar.menu.archive'), route: `/${getRole()}/exam/archive` },
            ]
        },
        // FRONTEND FILTERING LOGIC
        filteredExams() {
            let result = [...this.exams]

            // 1. Filter by Status
            if (this.filters.status) {
                result = result.filter((exam) => exam.status === this.filters.status)
            }

            // 2. Filter by Search (Title)
            if (this.filters.search) {
                const searchLower = this.filters.search.toLowerCase()
                result = result.filter((exam) => exam.title.toLowerCase().includes(searchLower))
            }

            // 3. Sorting
            result.sort((a, b) => {
                if (this.filters.sort === 'created_at') {
                    return new Date(b.created_at) - new Date(a.created_at)
                } else if (this.filters.sort === 'title') {
                    return a.title.localeCompare(b.title)
                } else if (this.filters.sort === 'status') {
                    return a.status.localeCompare(b.status)
                }
                return 0
            })

            return result
        },
    },
    methods: {
        viewReport(exam) {
            const role = getRole()
            if (exam?.live_session_id) {
                this.$router.push(`/${role}/live-report/${exam.live_session_id}`)
            } else if (typeof exam === 'object' && exam?.id) {
                this.$router.push(`/${role}/report/assigned-ayhoots/${exam.id}`)
            } else if (exam) {
                this.$router.push(`/${role}/report/assigned-ayhoots/${exam}`)
            }
        },
        downloadReport(exam) {
            const role = getRole()
            if (exam?.live_session_id) {
                this.$router.push(`/${role}/live-report/${exam.live_session_id}?export=pdf`)
            } else if (typeof exam === 'object' && exam?.id) {
                this.$router.push(`/${role}/report/assigned-ayhoots/${exam.id}`)
            } else if (exam) {
                this.$router.push(`/${role}/report/assigned-ayhoots/${exam}`)
            }
        },
        async fetchExams() {
            this.isLoading = true
            try {
                // We only pass the page to the API, searching and status filtering is frontend-only
                const response = await apiCall('GET', `/api/instructor/game?page=${this.filters.page}`)

                if (response && response.games) {
                    this.exams = response.games.data || []
                    this.pagination = {
                        current_page: response.games.current_page,
                        from: response.games.from || 0,
                        to: response.games.to || 0,
                        total: response.games.total || 0,
                        last_page: response.games.last_page || 1,
                    }
                }
            } catch (error) {
                console.error('Error fetching exams:', error)
                this.exams = []
            } finally {
                this.isLoading = false
            }
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page && page !== this.filters.page) {
                this.filters.page = page
                this.fetchExams()
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
