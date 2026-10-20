<template>
    <div class="bg-gray-50">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" :teacherName="teacherName" :teacherUsername="teacherUsername" />
        </div>

        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />

            <main
                class="flex-1 bg-gray-50 ml-0 sm:ml-0 md:ml-0 lg:ml-0 xl:ml-64 p-6 overflow-y-auto mt-16 min-h-[calc(100vh-4rem)]">
                <div>
                    <div class="pb-4">
                        <h2 class="text-3xl font-bold mb-2">Manage Ayhoots</h2>
                        <p>Manage all created Ayhoots</p>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                                <div
                                    class="flex flex-col sm:flex-row items-start sm:items-center flex-wrap gap-4 w-full sm:w-auto">
                                    <AyInput v-model="filters.search" placeholder="Search ayhoots..."
                                        class="w-full sm:flex-1 sm:min-w-[200px]">
                                        <template #icon>
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </template>
                                    </AyInput>
                                    <AySelect v-model="filters.status" class="w-full sm:w-auto">
                                        <option value="">All Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="published">Published</option>
                                        <option value="rejected">Rejected</option>
                                    </AySelect>
                                    <AySelect v-model="filters.sort" class="w-full sm:w-auto">
                                        <option value="created_at">Date Created</option>
                                        <option value="title">Title</option>
                                    </AySelect>
                                    <AySelect v-model="filters.accessibility" class="w-full sm:w-auto">
                                        <option value="">All Accessibility</option>
                                        <option value="free">Free</option>
                                        <option value="paid">Paid</option>
                                    </AySelect>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <DataTable v-if="exams.length && !isLoading" :columns="examColumns" :rows="exams"
                                :wrapper-class="''" row-class="hover:bg-gray-50">
                                <template #header:actions><span class="sr-only">Actions</span></template>
                                <template #cell:details="{ row: exam }">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-12 w-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                            <svg class="w-5.5 h-5.5 text-blue-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 truncate max-w-[20ch]"
                                                :title="exam.title">
                                                {{ exam.title }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                by
                                                <RouterLink v-if="exam.teacher_id"
                                                    :to="'/admin/teacher/' + exam.teacher_id"
                                                    class="font-semibold hover:underline">@{{ exam.teacher?.username }}
                                                </RouterLink>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template #cell:status="{ row: exam }">
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                        :class="statusClass(exam.status)">{{ exam.status }}</span>
                                </template>
                                <template #cell:labels="{ row: exam }">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="label in exam.labels" :key="label.id"
                                            class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full text-white"
                                            :style="{ backgroundColor: label.color }">
                                            {{ label.title }}
                                        </span>
                                        <span v-if="!exam.labels || exam.labels.length === 0"
                                            class="text-xs text-gray-400">N/A</span>
                                    </div>
                                </template>
                                <template #cell:plays="{ row: exam }">
                                    {{ exam.students_count || 0 }}
                                </template>
                                <template #cell:questions="{ row: exam }">
                                    {{ exam.questions_count || 0 }}
                                </template>
                                <template #cell:created="{ row: exam }">
                                    {{ formatDate(exam.created_at) }}
                                </template>
                                <template #cell:actions="{ row: exam }">
                                    <div class="flex items-center justify-end relative">
                                        <ResponsiveButton v-if="exam.status === 'pending'" type="light-green"
                                            class="text-xs px-2 py-1 mx-0.5"
                                            @click="updateExamStatus(exam, 'published')">Publish</ResponsiveButton>
                                        <ResponsiveButton v-if="exam.status === 'pending'" type="light-red"
                                            class="text-xs px-2 py-1 mx-0.5" @click="openRejectionModal(exam)">Reject
                                        </ResponsiveButton>
                                        <ResponsiveButton
                                            v-if="exam.status === 'published' || exam.status === 'rejected'"
                                            type="yellow" class="text-xs px-2 py-1 mx-0.5"
                                            @click="updateExamStatus(exam, 'pending')">Pend</ResponsiveButton>
                                        <ResponsiveButton type="light-gray" class="text-xs px-2 py-1 mx-0.5"
                                            @click="() => $router.push(`/admin/exam/${exam.id}`)">View
                                        </ResponsiveButton>
                                        <ResponsiveButton type="light-blue" class="text-xs px-2 py-1 mx-0.5"
                                            @click="openRecommendModal(exam)">
                                            Manage Label
                                        </ResponsiveButton>

                                        <ResponsiveButton type="light-red" class="text-xs px-2 py-1 mx-0.5"
                                            @click="deleteExam(exam)">Remove</ResponsiveButton>
                                    </div>
                                </template>
                            </DataTable>

                            <div v-else-if="isLoading" class="text-center py-12">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900">
                                </div>
                                <p class="mt-2 text-sm text-gray-500">Loading ayhoots...</p>
                            </div>
                            <div v-else class="text-center py-12">
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No ayhoots found</h3>
                                <p class="mt-1 text-sm text-gray-500">Try adjusting your filters.</p>
                            </div>
                        </div>

                        <Pagination v-if="!isLoading" :pagination="pagination" @page-changed="changePage"
                            :wrapper-class="'px-6 py-4 border-t border-gray-100'" />
                    </div>

                    <Modal v-model="showRejectionModal" title="Reject Ayhoot" size="md">
                        <div class="space-y-4">
                            <AySelect v-model="rejectionData.reason" label="Rejection Reason">
                                <option disabled value="">Please select a reason</option>
                                <option value="inappropriate">Inappropriate Content</option>
                                <option value="quality">Low Quality Content</option>
                                <option value="other">Other</option>
                            </AySelect>
                            <AyTextarea v-model="rejectionData.note" label="Optional Note" :rows="3"
                                placeholder="Provide more details..." />
                        </div>
                        <template #footer>
                            <AyButton type="light-gray" @click="closeRejectionModal">Cancel</AyButton>
                            <button @click="handleRejectionSubmit" :disabled="!rejectionData.reason"
                                class="text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded-md font-semibold text-sm disabled:opacity-50 cursor-pointer">
                                Confirm Rejection
                            </button>
                        </template>
                    </Modal>

                    <Modal v-model="showRecommendModal" title="Recommend Ayhoot" size="md">
                        <p class="text-xs text-gray-500 mb-4">
                            Select labels to recommend this Ayhoot. Actions are saved instantly.
                        </p>
                        <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                            <label v-for="label in availableLabels" :key="label.id"
                                class="flex items-center p-3 rounded-lg cursor-pointer transition-all duration-200 border-2"
                                :style="{
                                    borderColor: isLabelAssigned(label.id) ? label.color : '#E5E7EB',
                                    backgroundColor: isLabelAssigned(label.id) ? `${label.color}1A` : 'white',
                                }">
                                <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded mr-3 text-white"
                                    :style="{ backgroundColor: label.color }">
                                    <div v-html="label.logo"></div>
                                </div>
                                <span class="flex-grow font-semibold text-sm" :style="{ color: label.color }">{{
                                    label.title
                                    }}</span>
                                <input type="checkbox" :checked="isLabelAssigned(label.id)"
                                    @change="toggleLabel(label, $event)"
                                    class="h-5 w-5 rounded text-blue-600 focus:ring-blue-500 border-gray-300" />
                            </label>
                        </div>
                        <template #footer>
                            <AyButton type="light-blue" @click="closeRecommendModal">Done</AyButton>
                        </template>
                    </Modal>
                </div>
            </main>
        </div>
    </div>
</template>

<script>
import { apiCall } from '@/services/apiService'
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import AyTextarea from '@/components/atoms/form/AyTextarea.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Pagination from '@/components/shared/Pagination.vue'
import Modal from '@/components/shared/Modal.vue'
import { RouterLink } from 'vue-router'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'ExamIndex',
    components: {
        Sidebar,
        Header,
        ResponsiveButton,
        AyButton,
        AyInput,
        AySelect,
        AyTextarea,
        DataTable,
        RouterLink,
        Pagination,
        Modal,
    },
    data() {
        return {
            isSidebarOpen: false,
            isLoading: true,
            exams: [],
            filters: {
                search: '',
                status: '',
                sort: 'created_at',
                accessibility: '',
            },
            pagination: { current_page: 1, from: 0, to: 0, total: 0, last_page: 1, links: [] },
            debounceTimer: null,
            showRejectionModal: false,
            selectedExamForAction: null,
            rejectionData: { reason: '', note: '' },
            showRecommendModal: false,
            selectedExamForRecommendation: null,
            availableLabels: [],
        }
    },
    computed: {
        teacherName: () => JSON.parse(localStorage.getItem('user') || '{}').name || 'Admin',
        teacherUsername: () => JSON.parse(localStorage.getItem('user') || '{}').username || 'admin',
        examColumns() {
            return [
                { key: 'details', label: 'Ayhoot Details', align: 'left', thClass: 'font-medium' },
                { key: 'status', label: 'Status', align: 'left', thClass: 'font-medium' },
                { key: 'labels', label: 'Labels', align: 'left', thClass: 'font-medium' },
                { key: 'plays', label: 'Plays', align: 'left', thClass: 'font-medium', tdClass: 'text-gray-900' },
                {
                    key: 'questions',
                    label: 'Questions',
                    align: 'left',
                    thClass: 'font-medium',
                    tdClass: 'text-gray-900',
                },
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
    },
    watch: {
        filters: {
            handler() {
                clearTimeout(this.debounceTimer)
                this.debounceTimer = setTimeout(() => {
                    this.pagination.current_page = 1
                    this.fetchExams()
                }, 500)
            },
            deep: true,
        },
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        async fetchExams() {
            this.isLoading = true
            try {
                const params = new URLSearchParams({ page: this.pagination.current_page, ...this.filters })
                const response = await apiCall('GET', `/api/admin/exam?${params.toString()}`)
                this.exams = response.exams.data || []
                this.pagination = response.exams
            } catch (error) {
                this.$toast.error(error.message || 'Failed to load Ayhoots.')
            } finally {
                this.isLoading = false
            }
        },
        async fetchLabels() {
            try {
                this.availableLabels = await apiCall('GET', '/api/admin/label')
            } catch (error) {
                this.$toast.error('Failed to load labels.')
            }
        },
        changePage(page) {
            const pageNumber = Number(page)
            if (!isNaN(pageNumber) && pageNumber >= 1 && pageNumber <= this.pagination.last_page) {
                this.pagination.current_page = pageNumber
                this.fetchExams()
            }
        },
        formatDate: (dateString) =>
            dateString
                ? new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
                : 'N/A',
        statusClass(status) {
            return {
                'bg-green-100 text-green-800': ['published', 'active'].includes(status),
                'bg-yellow-100 text-yellow-800': ['pending', 'draft'].includes(status),
                'bg-red-100 text-red-800': ['rejected', 'archived'].includes(status),
                'bg-gray-100 text-gray-800': ![
                    'published',
                    'active',
                    'pending',
                    'draft',
                    'rejected',
                    'archived',
                ].includes(status),
            }
        },
        isRecommended(exam) {
            return exam && exam.labels && exam.labels.length > 0
        },
        isLabelAssigned(labelId) {
            if (!this.selectedExamForRecommendation || !this.selectedExamForRecommendation.labels) {
                return false
            }
            return this.selectedExamForRecommendation.labels.some((l) => l.id === labelId)
        },
        async updateExamStatus(exam, newStatus, rejectionPayload = {}) {
            try {
                const payload = { status: newStatus, ...rejectionPayload }
                await apiCall('POST', `/api/admin/exam/${exam.id}/status`, payload)
                this.$toast.success(`Ayhoot status updated to ${newStatus}.`)
                const examIndex = this.exams.findIndex((e) => e.id === exam.id)
                if (examIndex !== -1) this.exams[examIndex].status = newStatus
            } catch (error) {
                this.$toast.error(error.message || 'Failed to update status.')
            }
        },
        async deleteExam(exam) {
            if (
                await confirmDialog({
                    message: `Are you sure you want to permanently delete "${exam.title}"?`,
                    variant: 'danger',
                })
            ) {
                try {
                    await apiCall('GET', `/api/admin/exam/delete/${exam.id}`)
                    this.$toast.success('Ayhoot deleted successfully.')
                    this.exams = this.exams.filter((e) => e.id !== exam.id)
                } catch (error) {
                    this.$toast.error(error.message || 'Failed to delete Ayhoot.')
                }
            }
        },
        openRejectionModal(exam) {
            this.selectedExamForAction = exam
            this.rejectionData = { reason: '', note: '' }
            this.showRejectionModal = true
        },
        closeRejectionModal() {
            this.showRejectionModal = false
            this.selectedExamForAction = null
        },
        handleRejectionSubmit() {
            if (!this.rejectionData.reason) return this.$toast.error('A rejection reason is required.')
            this.updateExamStatus(this.selectedExamForAction, 'rejected', this.rejectionData)
            this.closeRejectionModal()
        },
        openRecommendModal(exam) {
            this.selectedExamForRecommendation = exam
            this.showRecommendModal = true
        },
        closeRecommendModal() {
            this.showRecommendModal = false
            this.selectedExamForRecommendation = null
        },
        async toggleLabel(label, event) {
            const isChecked = event.target.checked
            if (isChecked) {
                await this.assignRecommendation(label, event)
            } else {
                await this.removeRecommendation(label, event)
            }
        },
        async assignRecommendation(label, event) {
            try {
                const payload = { exam_id: this.selectedExamForRecommendation.id }
                await apiCall('POST', `/api/admin/label/${label.id}/assign-exam`, payload)

                const examIndex = this.exams.findIndex((e) => e.id === this.selectedExamForRecommendation.id)
                if (examIndex !== -1) {
                    if (!this.exams[examIndex].labels) this.exams[examIndex].labels = []
                    this.exams[examIndex].labels.push(label)
                }
                this.$toast.success(`"${label.title}" label assigned.`)
            } catch (error) {
                this.$toast.error(error.message || `Failed to assign "${label.title}".`)
                if (event) event.target.checked = false
            }
        },
        async removeRecommendation(label, event) {
            try {
                const payload = { exam_id: this.selectedExamForRecommendation.id }
                await apiCall('POST', `/api/admin/label/${label.id}/remove-exam`, payload)

                const examIndex = this.exams.findIndex((e) => e.id === this.selectedExamForRecommendation.id)
                if (examIndex !== -1 && this.exams[examIndex].labels) {
                    this.exams[examIndex].labels = this.exams[examIndex].labels.filter((l) => l.id !== label.id)
                }

                this.$toast.success(`"${label.title}" label removed.`)
            } catch (error) {
                this.$toast.error(error.message || `Failed to remove "${label.title}". Check API routes.`)
                if (event) event.target.checked = true
            }
        },
    },
    mounted() {
        this.fetchExams()
        this.fetchLabels()
    },
}
</script>
