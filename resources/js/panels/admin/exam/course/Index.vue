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
                <div class="">
                    <div class="pb-4">
                        <h2 class="text-3xl font-bold mb-2">Manage Courses</h2>
                        <p class="">Review, approve, and manage all courses.</p>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                                <div
                                    class="flex flex-col sm:flex-row items-start sm:items-center flex-wrap gap-4 w-full sm:w-auto"
                                >
                                    <AyInput
                                        v-model="filters.search"
                                        placeholder="Search courses..."
                                        class="w-full sm:flex-1 sm:min-w-[200px]"
                                    >
                                        <template #icon>
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                                />
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
                            <DataTable
                                v-if="courses.length && !isLoading"
                                :columns="courseColumns"
                                :rows="courses"
                                :wrapper-class="''"
                                row-class="hover:bg-gray-50 transition-colors duration-150"
                            >
                                <template #header:actions><span class="sr-only">Actions</span></template>
                                <template #cell:details="{ row: course }">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-12 w-12 bg-blue-100 rounded-xl flex items-center justify-center"
                                        >
                                            <svg
                                                class="w-6 h-6 text-blue-600"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"
                                                ></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div
                                                class="text-sm font-medium text-gray-900 truncate max-w-[20ch]"
                                                :title="course.title"
                                            >
                                                {{ course.title }}
                                            </div>
                                            <div class="flex gap-1 items-center">
                                                <RouterLink
                                                    v-if="course.teacher_id"
                                                    :to="'/admin/teacher/' + course.teacher_id"
                                                    class="text-sm text-gray-500 font-semibold"
                                                    >@{{ course.teacher?.username }}</RouterLink
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template #cell:status="{ row: course }">
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                        :class="{
                                            'bg-green-100 text-green-800': course.status === 'published',
                                            'bg-yellow-100 text-yellow-800': course.status === 'pending',
                                            'bg-red-100 text-red-800': course.status === 'rejected',
                                        }"
                                        >{{ course.status }}</span
                                    >
                                </template>
                                <template #cell:labels="{ row: course }">
                                    <div class="flex flex-wrap gap-1 max-w-[200px]">
                                        <span
                                            v-for="label in course.labels"
                                            :key="label.id"
                                            class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full text-white"
                                            :style="{ backgroundColor: label.color }"
                                            >{{ label.title }}</span
                                        >
                                        <span
                                            v-if="!course.labels || course.labels.length === 0"
                                            class="text-xs text-gray-400"
                                            >N/A</span
                                        >
                                    </div>
                                </template>
                                <template #cell:exams="{ row: course }">
                                    {{ course.exams?.length || 0 }}
                                </template>
                                <template #cell:created="{ row: course }">
                                    {{ formatDate(course.created_at) }}
                                </template>
                                <template #cell:actions="{ row: course }">
                                    <div class="flex items-center justify-end">
                                        <ResponsiveButton
                                            type="light-blue"
                                            class="text-xs px-2 py-1 mx-0.5"
                                            @click="openManageLabelsModal(course)"
                                            >Labels</ResponsiveButton
                                        >
                                        <ResponsiveButton
                                            v-if="course.status === 'pending'"
                                            type="light-green"
                                            class="text-xs px-2 py-1 mx-0.5"
                                            @click="updateStatus(course, 'published')"
                                            >Publish</ResponsiveButton
                                        >
                                        <ResponsiveButton
                                            v-if="course.status === 'pending'"
                                            type="light-red"
                                            class="text-xs px-2 py-1 mx-0.5"
                                            @click="openRejectionModal(course)"
                                            >Reject</ResponsiveButton
                                        >
                                        <ResponsiveButton
                                            v-if="course.status === 'published'"
                                            type="yellow"
                                            class="text-xs px-2 py-1 mx-0.5"
                                            @click="updateStatus(course, 'pending')"
                                            >Pend</ResponsiveButton
                                        >
                                        <ResponsiveButton
                                            type="light-gray"
                                            class="text-xs px-2 py-1 mx-0.5"
                                            @click="() => $router.push(`/admin/course/${course.id}`)"
                                            >View</ResponsiveButton
                                        >
                                    </div>
                                </template>
                            </DataTable>

                            <div v-else-if="isLoading" class="text-center py-12">
                                <div
                                    class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"
                                ></div>
                                <p class="mt-2 text-sm text-gray-500">Loading courses...</p>
                            </div>
                            <div v-else class="text-center py-12">
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No courses found</h3>
                                <p class="mt-1 text-sm text-gray-500">Try adjusting your filters.</p>
                            </div>
                        </div>

                        <div v-if="!isLoading && pagination.last_page > 1" class="px-6 py-4 border-t border-gray-100">
                            <!-- Pagination Controls Here -->
                        </div>
                    </div>

                    <!-- Rejection Modal -->
                    <Modal v-model="showRejectionModal" title="Reject Course" size="md">
                        <!-- ... modal content ... -->
                    </Modal>

                    <!-- Manage Labels Modal -->
                    <Modal v-model="showManageLabelsModal" title="Manage Course Labels" size="md">
                        <p class="text-xs text-gray-500 mb-4">
                            Select labels to assign to this course. Actions are saved instantly.
                        </p>
                        <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                            <label
                                v-for="label in availableLabels"
                                :key="label.id"
                                class="flex items-center p-3 rounded-lg cursor-pointer transition-all duration-200 border-2"
                                :style="{
                                    borderColor: isLabelAssigned(label.id) ? label.color : '#E5E7EB',
                                    backgroundColor: isLabelAssigned(label.id) ? `${label.color}1A` : 'white',
                                }"
                            >
                                <span class="flex-grow font-semibold text-sm" :style="{ color: label.color }">{{
                                    label.title
                                }}</span>
                                <input
                                    type="checkbox"
                                    :checked="isLabelAssigned(label.id)"
                                    @change="toggleLabel(label, $event)"
                                    class="h-5 w-5 rounded text-blue-600 focus:ring-blue-500 border-gray-300"
                                />
                            </label>
                        </div>
                        <template #footer>
                            <AyButton type="light-blue" @click="closeManageLabelsModal">Done</AyButton>
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
import DataTable from '@/components/shared/DataTable.vue'
import Modal from '@/components/shared/Modal.vue'
import { RouterLink } from 'vue-router'

export default {
    name: 'CourseIndex',
    components: { Sidebar, Header, ResponsiveButton, AyButton, AyInput, AySelect, DataTable, Modal, RouterLink },
    data() {
        return {
            isSidebarOpen: false,
            isLoading: true,
            courses: [],
            filters: { search: '', status: '', sort: 'created_at', accessibility: '' },
            pagination: {
                current_page: 1,
                from: 0,
                to: 0,
                total: 0,
                last_page: 1,
                links: [],
                prev_page_url: null,
                next_page_url: null,
            },
            debounceTimer: null,
            showRejectionModal: false,
            selectedCourseForAction: null,
            rejectionData: { reason: '', note: '' },
            // State for label management
            showManageLabelsModal: false,
            selectedCourseForLabels: null,
            availableLabels: [],
        }
    },
    computed: {
        teacherName() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user.name || 'Admin'
        },
        teacherUsername() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user.username || 'admin'
        },
        courseColumns() {
            return [
                { key: 'details', label: 'Course Details', align: 'left', thClass: 'font-medium tracking-wider' },
                { key: 'status', label: 'Status', align: 'left', thClass: 'font-medium tracking-wider' },
                { key: 'labels', label: 'Labels', align: 'left', thClass: 'font-medium tracking-wider' },
                {
                    key: 'exams',
                    label: 'Exams',
                    align: 'left',
                    thClass: 'font-medium tracking-wider',
                    tdClass: 'text-gray-900',
                },
                {
                    key: 'created',
                    label: 'Date Created',
                    align: 'left',
                    thClass: 'font-medium tracking-wider',
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
                    this.fetchCourses()
                }, 500)
            },
            deep: true,
        },
        'pagination.current_page'() {
            this.fetchCourses()
        },
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        async fetchCourses() {
            this.isLoading = true
            try {
                const params = new URLSearchParams({ page: this.pagination.current_page, ...this.filters })
                const response = await apiCall('GET', `/api/admin/course?${params.toString()}`)
                this.courses = response.courses.data || []
                this.pagination = response.courses
            } catch (error) {
                this.$toast.error(error.message || 'Failed to load courses.')
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
            }
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A'
            return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
        },
        async updateStatus(course, newStatus) {
            try {
                await apiCall('POST', `/api/admin/course/${course.id}/status`, { status: newStatus })
                this.$toast.success(`Course status updated to ${newStatus}.`)
                const courseIndex = this.courses.findIndex((c) => c.id === course.id)
                if (courseIndex !== -1) this.courses[courseIndex].status = newStatus
            } catch (error) {
                this.$toast.error(error.message || 'Failed to update status.')
            }
        },
        openRejectionModal(course) {
            this.selectedCourseForAction = course
            this.rejectionData = { reason: '', note: '' }
            this.showRejectionModal = true
        },
        closeRejectionModal() {
            this.showRejectionModal = false
            this.selectedCourseForAction = null
        },
        async handleRejectionSubmit() {
            // Your existing rejection logic
        },

        // --- Label Management Methods ---
        isLabelAssigned(labelId) {
            if (!this.selectedCourseForLabels || !this.selectedCourseForLabels.labels) return false
            return this.selectedCourseForLabels.labels.some((l) => l.id === labelId)
        },
        openManageLabelsModal(course) {
            this.selectedCourseForLabels = course
            this.showManageLabelsModal = true
        },
        closeManageLabelsModal() {
            this.showManageLabelsModal = false
            this.selectedCourseForLabels = null
        },
        async toggleLabel(label, event) {
            const isChecked = event.target.checked
            const action = isChecked ? this.assignLabel : this.removeLabel
            await action(label, event)
        },
        async assignLabel(label, event) {
            try {
                const payload = { course_id: this.selectedCourseForLabels.id }
                await apiCall('POST', `/api/admin/label/${label.id}/assign-course`, payload)
                const courseIndex = this.courses.findIndex((c) => c.id === this.selectedCourseForLabels.id)
                if (courseIndex !== -1) {
                    if (!this.courses[courseIndex].labels) this.courses[courseIndex].labels = []
                    this.courses[courseIndex].labels.push(label)
                }
                this.$toast.success(`"${label.title}" label assigned.`)
            } catch (error) {
                this.$toast.error(error.message || `Failed to assign "${label.title}".`)
                if (event) event.target.checked = false
            }
        },
        async removeLabel(label, event) {
            try {
                const payload = { course_id: this.selectedCourseForLabels.id }
                await apiCall('POST', `/api/admin/label/${label.id}/remove-course`, payload)
                const courseIndex = this.courses.findIndex((c) => c.id === this.selectedCourseForLabels.id)
                if (courseIndex !== -1 && this.courses[courseIndex].labels) {
                    this.courses[courseIndex].labels = this.courses[courseIndex].labels.filter((l) => l.id !== label.id)
                }
                this.$toast.success(`"${label.title}" label removed.`)
            } catch (error) {
                this.$toast.error(error.message || `Failed to remove "${label.title}".`)
                if (event) event.target.checked = true
            }
        },
    },
    async mounted() {
        // Fetch both courses and labels when the component mounts
        await Promise.all([this.fetchCourses(), this.fetchLabels()])
    },
}
</script>
