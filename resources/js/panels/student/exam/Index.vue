<template>
    <PageWrapper>
        <div>
            <!-- Tabs -->
            <DashTabs :tabs="myTabs" :folders="folders" />
            <!-- Header Section -->
            <div class="pb-4">
                <h2 class="text-3xl font-bold mb-2">{{ $t('ayhoot.title') }}</h2>
                <p>{{ $t('ayhoot.description') }}</p>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <StudentExamFilters v-model:search="filters.search" v-model:sort="filters.sort"
                    @update:search="handleSearch" @update:sort="fetchExams" />

                <StudentExamTable :exams="filteredExams" :isLoading="isLoading" :formatDate="formatDate"
                    @toggle-dropdown="toggleDropdown" />

                <!-- Dropdown -->
                <div v-if="dropdownOpen !== null" :style="dropdownStyle"
                    class="fixed bg-white rounded-md shadow-lg border border-gray-200 z-[55] cursor-pointer"
                    :class="$i18n.locale === 'fa' ? 'text-right' : 'text-left'" @click.stop="dropdownOpen = null">
                    <div v-for="action in dropdownActions" :key="action.label"
                        class="flex items-center w-full px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-100 transition-colors"
                        :class="$i18n.locale === 'fa' ? 'gap-reverse' : 'gap-2'"
                        @click="handleDropdownAction(action, selectedExamId)">
                        <component :is="action.icon" class="w-4 h-4 text-gray-500" />
                        <span>{{ action.label }}</span>
                    </div>
                </div>

                <StudentExamPagination v-if="!isLoading" :pagination="pagination" @change-page="changePage" />
            </div>
        </div>
    </PageWrapper>
</template>

<script>
import { apiCall } from '@/services/apiService'
import PageWrapper from '@/components/shared/PageWrapper.vue'
import DashRemove from '@/components/atoms/icons/DashRemove.vue'
import DashShare from '@/components/atoms/icons/DashShare.vue'
import DashTabs from '@/components/layout/StudentDashTabs.vue'
import StudentExamFilters from './StudentExamFilters.vue'
import StudentExamTable from './StudentExamTable.vue'
import StudentExamPagination from './StudentExamPagination.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'ExamIndex',
    components: {
        PageWrapper,
        DashTabs,
        StudentExamFilters,
        StudentExamTable,
        StudentExamPagination,
    },
    data() {
        return {
            dropdownOpen: null,
            dropdownStyle: {},
            selectedExamId: null,
            exams: [],
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
            searchTimeout: null,
            folders: [],
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
        dropdownActions() {
            return [
                { label: this.$t('ayhoot.dropdown.share'), icon: DashShare, action: 'share' },
                {
                    label: this.$t('ayhoot.dropdown.delete'),
                    icon: DashRemove,
                    action: 'delete',
                },
            ]
        },
        filteredExams() {
            if (!this.filters.search && this.exams.length > 0) return this.exams

            const query = this.filters.search.toLowerCase()
            return this.exams.filter(
                (exam) =>
                    exam.title.toLowerCase().includes(query) ||
                    (exam.subject && exam.subject.toLowerCase().includes(query))
            )
        },
    },
    methods: {
        async fetchExams() {
            this.isLoading = true
            try {
                const queryParams = new URLSearchParams({
                    page: this.filters.page,
                    search: this.filters.search,
                    status: this.filters.status,
                    sort: this.filters.sort,
                }).toString()
                const response = await apiCall('GET', `/api/student/exam?${queryParams}`)
                this.exams = response.exams.data || []
                this.pagination = {
                    current_page: response.exams.current_page,
                    from: response.exams.from || 0,
                    to: response.exams.to || 0,
                    total: response.exams.total || 0,
                    last_page: response.exams.last_page || 1,
                }
            } catch (error) {
                console.error('Error fetching exams:', error)
                this.exams = []
                this.$toast.error(error.message || this.$t('ayhoot.notifications.loadFailed'))
            } finally {
                this.isLoading = false
            }
        },
        handleSearch() {
            if (this.searchTimeout) clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.filters.page = 1
                this.fetchExams()
            }, 500)
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page && page !== this.filters.page) {
                this.filters.page = page
                this.fetchExams()
            }
        },
        formatDate(dateString) {
            if (!dateString) return this.$t('ayhoot.table.body.notAvailable')
            try {
                const locale = this.$i18n.locale === 'fa' ? 'fa-IR' : 'en-US'
                return new Date(dateString).toLocaleDateString(locale, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                })
            } catch (e) {
                return this.$t('ayhoot.table.body.invalidDate')
            }
        },
        toggleDropdown(examId, event) {
            event.stopPropagation()
            if (this.dropdownOpen === examId) {
                this.dropdownOpen = null
                this.selectedExamId = null
            } else {
                const button = event.currentTarget
                const buttonRect = button.getBoundingClientRect()
                const windowHeight = window.innerHeight
                const DROPDOWN_HEIGHT_ESTIMATE = 100
                const DROPDOWN_WIDTH = 160
                let topStyle = 'auto'
                let bottomStyle = 'auto'
                let leftStyle = 'auto'

                if (
                    windowHeight - buttonRect.bottom < DROPDOWN_HEIGHT_ESTIMATE &&
                    buttonRect.top > DROPDOWN_HEIGHT_ESTIMATE
                ) {
                    bottomStyle = `${windowHeight - buttonRect.top + 4}px`
                } else {
                    topStyle = `${buttonRect.bottom + 4}px`
                }

                if (this.$i18n.locale === 'fa') {
                    leftStyle = `${buttonRect.left}px`
                } else {
                    leftStyle = `${buttonRect.right - DROPDOWN_WIDTH}px`
                }

                this.dropdownStyle = {
                    position: 'fixed',
                    top: topStyle,
                    bottom: bottomStyle,
                    left: leftStyle,
                    minWidth: `${DROPDOWN_WIDTH}px`,
                }

                this.dropdownOpen = examId
                this.selectedExamId = examId
            }
        },
        closeDropdown() {
            this.dropdownOpen = null
            this.selectedExamId = null
        },
        handleDropdownAction(action, examId) {
            this.closeDropdown()
            switch (action.action) {
                case 'share':
                    this.shareExam(examId)
                    break
                case 'delete':
                    this.deleteExam(examId)
                    break
                default:
                    console.warn('Unknown dropdown action:', action.action)
            }
        },
        async shareExam(id) {
            const publicBaseUrl = window.location.origin
            const link = `${publicBaseUrl}/student/exam/${id}/detail`
            try {
                await navigator.clipboard.writeText(link)
                this.$toast.success(this.$t('ayhoot.notifications.linkCopied'))
            } catch (err) {
                console.error('Failed to copy link: ', err)
                this.$toast.error(this.$t('ayhoot.notifications.linkCopyFailed'))
            }
        },
        async deleteExam(examId) {
            if (await confirmDialog({ message: this.$t('ayhoot.notifications.confirmDelete'), variant: 'danger' })) {
                try {
                    await apiCall('GET', `/api/student/exam/delete/${examId}`)
                    this.$toast.success(this.$t('ayhoot.notifications.deleteSuccess'))
                    await this.fetchExams()
                } catch (error) {
                    console.error('Error deleting exam:', error)
                    this.$toast.error(error.message || this.$t('ayhoot.notifications.deleteFailed'))
                }
            }
        },
    },
    async mounted() {
        await this.fetchExams()
        document.addEventListener('click', this.closeDropdown)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.closeDropdown)
        if (this.searchTimeout) clearTimeout(this.searchTimeout)
    },
}
</script>
