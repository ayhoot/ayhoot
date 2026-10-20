<template>
    <InstructorLayout>
                <div>
                    <!-- Header Section -->
                    <div class="pb-4">
                        <h2 class="text-3xl font-bold mb-2">{{ $t('ayhoot.title') }}</h2>
                        <p>{{ $t('ayhoot.description') }}</p>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <!-- Table Header with Filters -->
                        <ExamFilters 
                            v-model:search="filters.search"
                            v-model:status="filters.status"
                            v-model:sort="filters.sort"
                            :filters="filters"
                            @update:search="handleSearch"
                            @update:status="fetchExams"
                            @update:sort="fetchExams"
                        />

                        <!-- Table Content -->
                        <ExamTable 
                            :exams="filteredExams"
                            :isLoading="isLoading"
                            :formatDate="formatDate"
                            :canHostLive="canHostLive"
                            @host-live="openHostLiveModal"
                            @assign="openAssignModal"
                            @toggle-dropdown="toggleDropdown"
                        />

                        <!-- Pagination -->
                        <ExamPagination 
                            :pagination="pagination"
                            @change-page="changePage"
                        />
                    </div>

                    <!-- Modals -->
                    <ExamAssignModal 
                        :show="showAssignModal"
                        :groups="groups"
                        :isLoadingGroups="isLoadingGroups"
                        @close="cancelAssign"
                        @submit="handleAssignSubmit"
                    />

                    <ExamMoveFolderModal 
                        :show="showMoveToFolderModal"
                        :folders="filteredFoldersForMove"
                        :isLoading="isLoadingFolders"
                        :isAssigning="isAssigningToFolder"
                        v-model:searchQuery="folderSearchQuery"
                        @close="closeMoveToFolderModal"
                        @add="handleAddExamToFolder"
                        @remove="handleRemoveExamFromFolder"
                    />

                    <ExamAddToCourseModal 
                        :show="showAddToCourseModal"
                        :courses="filteredCoursesForAdd"
                        :isLoading="isLoadingCourses"
                        :isAssigning="isAssigningToCourse"
                        v-model:searchQuery="courseSearchQuery"
                        @close="closeAddToCourseModal"
                        @add="handleAssignExamToCourse"
                        @remove="handleRemoveExamFromCourse"
                    />

                    <!-- Dropdown -->
                    <div v-if="dropdownOpen !== null" :style="dropdownStyle"
                        class="fixed bg-white rounded-md shadow-lg border border-gray-200 z-[55] cursor-pointer"
                        :class="$i18n.locale === 'fa' ? 'text-right' : 'text-left'"
                        @click.stop="dropdownOpen = null">
                        <div v-for="action in dropdownActions" :key="action.label"
                            class="flex items-center w-full px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-100 transition-colors"
                            :class="$i18n.locale === 'fa' ? 'gap-reverse' : 'gap-2'"
                            @click="handleDropdownAction(action, selectedExamId)">
                            <component :is="action.icon" class="w-4 h-4 text-gray-500" />
                            <span>{{ action.label }}</span>
                        </div>
                    </div>
                </div>
        <template #overlays>
            <ExamHostLiveModal
                :show="showHostLiveModal"
                :selectedExam="selectedExam"
                :liveSessionData="liveSessionData"
                :creatingSession="creatingSession"
                v-model:title="hostLiveTitle"
                @close="showHostLiveModal = false"
                @create="createLiveSession"
                @go-to-host="goToHostPage"
            />
        </template>
    </InstructorLayout>
</template>
<script>
import { apiCall } from "@/services/apiService"
import { getRole } from '@/utils/role'
import { ref } from 'vue'
import QRCode from 'qrcode.vue'

import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import DashDuplicate from '@/components/atoms/icons/DashDuplicate.vue'
import DashRemove from '@/components/atoms/icons/DashRemove.vue'
import DashShare from '@/components/atoms/icons/DashShare.vue'
import DashMoveFolder from '@/components/atoms/icons/DashMoveFolder.vue'
import DashAddCourse from '@/components/atoms/icons/DashAddCourse.vue'
import DashArchive from '@/components/atoms/icons/DashArchive.vue'
import { RouterLink } from 'vue-router'

// Modular Components
import ExamFilters from './ExamFilters.vue'
import ExamTable from './ExamTable.vue'
import ExamPagination from './ExamPagination.vue'
import ExamAssignModal from './ExamAssignModal.vue'
import ExamMoveFolderModal from './ExamMoveFolderModal.vue'
import ExamAddToCourseModal from './ExamAddToCourseModal.vue'
import ExamHostLiveModal from './ExamHostLiveModal.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'ExamIndex',
    components: {
        InstructorLayout,
        DashDuplicate,
        DashRemove,
        DashShare,
        DashMoveFolder,
        DashArchive,
        DashAddCourse,
        RouterLink,
        ExamFilters,
        ExamTable,
        ExamPagination,
        ExamAssignModal,
        ExamMoveFolderModal,
        ExamAddToCourseModal,
        ExamHostLiveModal
    },
    data() {
        return {
            dropdownOpen: null,
            dropdownStyle: {},
            selectedExamId: null,
            exams: [],
            isLoading: true,
            showHostLiveModal: false,
            selectedExam: null,
            hostLiveTitle: '',
            liveSessionData: null,
            creatingSession: false,
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
            showAssignModal: false,
            selectedAssignExamId: null,
            assignForm: {
                date: '',
                time: '',
                questionTime: 'off',
                optionRandomize: 'off',
                selectedGroups: [], // For storing selected group IDs
            },
            groups: [],
            isLoadingGroups: false,
            isGroupDropdownOpen: false,
            showMoveToFolderModal: false,
            foldersForMove: [],
            examToMoveId: null,
            isLoadingFolders: false,
            isAssigningToFolder: null,
            folderSearchQuery: '',
            showAddToCourseModal: false,
            coursesForAdd: [],
            examToCourseId: null,
            isLoadingCourses: false,
            isAssigningToCourse: null,
            courseSearchQuery: '',
        }
    },
    computed: {
        myTabs() {
            return [
                { label: this.$t('ayhoot.tabs.ayhoots'), route: `/${getRole()}/exam` },
                { label: this.$t('ayhoot.tabs.courses'), route: `/${getRole()}/exam/courses` },
                { label: this.$t('ayhoot.tabs.purchased'), route: `/${getRole()}/exam/purchased` },
                { label: this.$t('ayhoot.tabs.folders'), route: `/${getRole()}/exam/folders` },
                { label: this.$t('ayhoot.tabs.archive'), route: `/${getRole()}/exam/archive` },
            ]
        },
        dropdownActions() {
            return [
                {
                    label: this.$t('ayhoot.dropdown.addToCourse'),
                    icon: DashAddCourse,
                    action: 'addCourse',
                },
                {
                    label: this.$t('ayhoot.dropdown.moveFolder'),
                    icon: DashMoveFolder,
                    action: 'moveFolder',
                },
                {
                    label: this.$t('ayhoot.dropdown.archive'),
                    icon: DashArchive,
                    action: 'archive',
                },
                { label: this.$t('ayhoot.dropdown.share'), icon: DashShare, action: 'share' },
                {
                    label: this.$t('ayhoot.dropdown.duplicate'),
                    icon: DashDuplicate,
                    action: 'duplicate',
                },
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
        filteredFoldersForMove() {
            if (!this.folderSearchQuery) {
                return this.foldersForMove
            }
            const query = this.folderSearchQuery.toLowerCase()
            return this.foldersForMove.filter((folder) => folder.name && folder.name.toLowerCase().includes(query))
        },
        filteredCoursesForAdd() {
            if (!this.courseSearchQuery) {
                return this.coursesForAdd
            }
            const query = this.courseSearchQuery.toLowerCase()
            return this.coursesForAdd.filter(
                (course) =>
                    (course.title && course.title.toLowerCase().includes(query)) ||
                    (course.name && course.name.toLowerCase().includes(query))
            )
        },
        groupSelectionText() {
            const count = this.assignForm.selectedGroups.length
            if (count === 0) {
                return this.$t('ayhoot.assignModal.selectGroupsPlaceholder')
            }
            return this.$t('ayhoot.assignModal.groupsSelected', { count })
        },
    },
    methods: {
        async fetchFolders() {
            try {
                const response = await apiCall('GET', `/api/instructor/folder`)
                this.folders = response.folders || []
            } catch (error) {
                console.error('Error fetching folders for tabs:', error)
            }
        },
        async fetchExams() {
            this.isLoading = true
            try {
                const queryParams = new URLSearchParams({
                    page: this.filters.page,
                    search: this.filters.search,
                    status: this.filters.status,
                    sort: this.filters.sort,
                }).toString()
                const response = await apiCall('GET', `/api/instructor/exam?${queryParams}`)
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
                const DROPDOWN_HEIGHT_ESTIMATE = 230
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
                case 'addCourse':
                    this.openAddToCourseModal(examId)
                    break
                case 'moveFolder':
                    this.openMoveToFolderModal(examId)
                    break
                case 'archive':
                    this.archiveExam(examId)
                    break
                case 'share':
                    this.shareExam(examId)
                    break
                case 'duplicate':
                    this.duplicateExam(examId)
                    break
                case 'delete':
                    this.deleteExam(examId)
                    break
                default:
                    console.warn('Unknown dropdown action:', action.action)
            }
        },
        async archiveExam(examId) {
            if (!(await confirmDialog({ message: this.$t('ayhoot.notifications.confirmArchive'), variant: 'primary' }))) {
                return
            }
            try {
                await apiCall('GET', `/api/instructor/exam/archive/${examId}`)
                this.$toast.success(this.$t('ayhoot.notifications.archiveSuccess'))
                await this.fetchExams()
            } catch (error) {
                console.error('Error archiving exam:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.archiveFailed'))
            }
        },
        async shareExam(id) {
            // Get the base URL of the frontend application (e.g., "http://localhost:5173" or "https://ayhoot.com")
            const publicBaseUrl = window.location.origin

            // Construct the full shareable link
            const link = `${publicBaseUrl}/${getRole()}/ayhoot/${id}`

            try {
                await navigator.clipboard.writeText(link)
                this.$toast.success(this.$t('ayhoot.notifications.linkCopied'))
            } catch (err) {
                console.error('Failed to copy link: ', err)
                this.$toast.error(this.$t('ayhoot.notifications.linkCopyFailed'))
            }
        },
        async duplicateExam(id) {
            await apiCall('GET', `/api/instructor/exam/duplicate/${id}`)
            await this.fetchExams()
        },
        async deleteExam(examId) {
            if (await confirmDialog({ message: this.$t('ayhoot.notifications.confirmDelete'), variant: 'danger' })) {
                try {
                    await apiCall('GET', `/api/instructor/exam/delete/${examId}`)
                    this.$toast.success(this.$t('ayhoot.notifications.deleteSuccess'))
                    await this.fetchExams()
                } catch (error) {
                    console.error('Error deleting exam:', error)
                    this.$toast.error(error.message || this.$t('ayhoot.notifications.deleteFailed'))
                }
            }
        },
        openAssignModal(examId) {
            this.selectedAssignExamId = examId
            this.assignForm = { date: '', time: '', questionTime: 'off', optionRandomize: 'off', selectedGroups: [] }
            this.showAssignModal = true
            this.fetchGroupsForModal()
        },
        cancelAssign() {
            this.showAssignModal = false
            this.selectedAssignExamId = null
            this.isGroupDropdownOpen = false
        },
        async handleAssignSubmit(formData) {
            if (this.isAssigning) return
            this.isAssigning = true

            const form = formData || this.assignForm
            if (!form.date || !form.time) {
                this.$toast.error(this.$t('ayhoot.notifications.dueDateRequired'))
                this.isAssigning = false
                return
            }
            if (!this.selectedAssignExamId) {
                this.$toast.error(this.$t('ayhoot.notifications.noExamSelected'))
                this.isAssigning = false
                return
            }
            const payload = {
                exam_id: this.selectedAssignExamId,
                title: form.title ? form.title.trim() : null,
                end_at: `${form.date} ${form.time}:00`,
                question_time: form.questionTime,
                randomize_answer: form.optionRandomize,
                group_ids: form.selectedGroups || [],
            }
            try {
                const response = await apiCall('POST', `/api/instructor/game`, payload)
                const gameId = response.id || response.game_id || response.game?.id
                if (!gameId) {
                    throw new Error(this.$t('ayhoot.notifications.gameIdNotFound'))
                }
                this.cancelAssign()
                this.$router.push(`/${getRole()}/report/assigned-ayhoots/${gameId}`)
            } catch (error) {
                console.error('Error creating game:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.assignFailed'))
            } finally {
                this.isAssigning = false
            }
        },
        async fetchGroupsForModal() {
            this.isLoadingGroups = true
            try {
                const response = await apiCall('GET', `/api/instructor/group`)
                this.groups = response.groups || []
            } catch (error) {
                console.error('Error fetching groups:', error)
                this.$toast.error(this.$t('ayhoot.assignModal.errorLoadingGroups'))
            } finally {
                this.isLoadingGroups = false
            }
        },
        toggleGroupSelection(group) {
            const index = this.assignForm.selectedGroups.indexOf(group.id)
            if (index > -1) {
                this.assignForm.selectedGroups.splice(index, 1) // Deselect
            } else {
                this.assignForm.selectedGroups.push(group.id) // Select
            }
        },
        handleClickOutside(event) {
            if (this.$refs.groupDropdown && !this.$refs.groupDropdown.contains(event.target)) {
                this.isGroupDropdownOpen = false
            }
        },
        openMoveToFolderModal(examId) {
            this.examToMoveId = examId
            this.folderSearchQuery = ''
            this.showMoveToFolderModal = true
            this.fetchFoldersForMove()
        },
        closeMoveToFolderModal() {
            this.showMoveToFolderModal = false
            this.foldersForMove = []
            this.examToMoveId = null
            this.isAssigningToFolder = null
        },
        async fetchFoldersForMove() {
            this.isLoadingFolders = true
            this.foldersForMove = []
            try {
                const response = await apiCall('GET', `/api/instructor/folder`)
                let rawFolders = []
                if (Array.isArray(response)) rawFolders = response
                else if (response?.data && Array.isArray(response.data)) rawFolders = response.data
                else if (response?.folders && Array.isArray(response.folders)) rawFolders = response.folders
                else if (response?.data?.data && Array.isArray(response.data.data)) rawFolders = response.data.data
                else console.warn('Folder API structure issue:', response)

                this.foldersForMove = rawFolders.map((folder) => ({
                    ...folder,
                    isExamAlreadyInFolder: folder.exams?.some((ex) => ex.id === this.examToMoveId) || false,
                }))
            } catch (error) {
                console.error('Fetch folders error:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.loadFoldersFailed'))
            } finally {
                this.isLoadingFolders = false
            }
        },
        async handleAddExamToFolder(folder) {
            if (!this.examToMoveId || !folder || folder.isExamAlreadyInFolder) return
            this.isAssigningToFolder = folder.id
            try {
                await apiCall('POST', `/api/instructor/folder/${folder.id}/assign-exams`, {
                    exam_ids: [this.examToMoveId],
                })
                this.$toast.success(this.$t('ayhoot.notifications.moveToFolderSuccess', { folderName: folder.name }))
                const fInList = this.foldersForMove.find((f) => f.id === folder.id)
                if (fInList) {
                    fInList.isExamAlreadyInFolder = true
                    if (!fInList.exams) fInList.exams = []
                    if (!fInList.exams.some((ex) => ex.id === this.examToMoveId)) {
                        fInList.exams.push({ id: this.examToMoveId })
                    }
                }
            } catch (error) {
                console.error('Assign to folder error:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.moveToFolderFailed'))
            } finally {
                this.isAssigningToFolder = null
            }
        },
        async handleRemoveExamFromFolder(folder) {
            if (!this.examToMoveId || !folder || !folder.isExamAlreadyInFolder) return
            this.isAssigningToFolder = folder.id
            try {
                await apiCall('POST', `/api/instructor/folder/${folder.id}/unassign-exams`, {
                    exam_ids: [this.examToMoveId],
                })
                this.$toast.success(
                    this.$t('ayhoot.notifications.removeFromFolderSuccess', { folderName: folder.name })
                )
                const fInList = this.foldersForMove.find((f) => f.id === folder.id)
                if (fInList) {
                    fInList.isExamAlreadyInFolder = false
                    if (fInList.exams) {
                        fInList.exams = fInList.exams.filter((ex) => ex.id !== this.examToMoveId)
                    }
                }
            } catch (error) {
                console.error('Error removing exam from folder:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.removeFromFolderFailed'))
            } finally {
                this.isAssigningToFolder = null
            }
        },
        openAddToCourseModal(examId) {
            this.examToCourseId = examId
            this.courseSearchQuery = ''
            this.showAddToCourseModal = true
            this.fetchCoursesForAdd()
        },
        closeAddToCourseModal() {
            this.showAddToCourseModal = false
            this.coursesForAdd = []
            this.examToCourseId = null
            this.isAssigningToCourse = null
        },
        async fetchCoursesForAdd() {
            this.isLoadingCourses = true
            this.coursesForAdd = []
            try {
                const response = await apiCall('GET', `/api/instructor/course`)
                let rawCourses = []

                // --- START OF MODIFICATION ---

                // This is the corrected logic. It correctly handles paginated and non-paginated responses.
                if (response?.courses?.data && Array.isArray(response.courses.data)) {
                    // Handles paginated response structure: { courses: { data: [...] } }
                    rawCourses = response.courses.data
                } else if (response?.courses && Array.isArray(response.courses)) {
                    // Handles simple array structure: { courses: [...] }
                    rawCourses = response.courses
                } else if (response?.data && Array.isArray(response.data)) {
                    // Handles simple array structure in a 'data' key: { data: [...] }
                    rawCourses = response.data
                } else if (Array.isArray(response)) {
                    // Handles a raw array response: [...]
                    rawCourses = response
                } else {
                    console.warn('Could not find a valid course array in the API response:', response)
                }

                // --- END OF MODIFICATION ---

                this.coursesForAdd = rawCourses.map((course) => ({
                    ...course,
                    isExamAlreadyInCourse: course.exams?.some((exam) => exam.id === this.examToCourseId) || false,
                }))
            } catch (error) {
                console.error('Error fetching courses:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.loadCoursesFailed'))
            } finally {
                this.isLoadingCourses = false
            }
        },
        async handleAssignExamToCourse(course) {
            if (!this.examToCourseId || !course || course.isExamAlreadyInCourse) return

            this.isAssigningToCourse = course.id
            try {
                await apiCall('POST', `/api/instructor/course/${course.id}/assign-exams`, {
                    exam_ids: [this.examToCourseId],
                })
                this.$toast.success(
                    this.$t('ayhoot.notifications.addToCourseSuccess', {
                        courseName: course.title || course.name,
                    })
                )
                const courseInList = this.coursesForAdd.find((c) => c.id === course.id)
                if (courseInList) {
                    courseInList.isExamAlreadyInCourse = true
                    if (!courseInList.exams) courseInList.exams = []
                    if (!courseInList.exams.some((ex) => ex.id === this.examToCourseId)) {
                        courseInList.exams.push({ id: this.examToCourseId })
                    }
                }
            } catch (error) {
                console.error('Error assigning exam to course:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.addToCourseFailed'))
            } finally {
                this.isAssigningToCourse = null
            }
        },
        async handleRemoveExamFromCourse(course) {
            if (!this.examToCourseId || !course || !course.isExamAlreadyInCourse) return
            this.isAssigningToCourse = course.id
            try {
                await apiCall('GET', `/api/instructor/course/${course.id}/remove-exam/${this.examToCourseId}`)
                this.$toast.success(
                    this.$t('ayhoot.notifications.removeFromCourseSuccess', {
                        courseName: course.title || course.name,
                    })
                )
                const courseInList = this.coursesForAdd.find((c) => c.id === course.id)
                if (courseInList) {
                    courseInList.isExamAlreadyInCourse = false
                    if (courseInList.exams) {
                        courseInList.exams = courseInList.exams.filter((ex) => ex.id !== this.examToCourseId)
                    }
                }
            } catch (error) {
                console.error('Error removing exam from course:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.removeFromCourseFailed'))
            } finally {
                this.isAssigningToCourse = null
            }
        },
        canHostLive(exam) {
            // Owner (role-aware) can host their exam. Institutes own via institute_id,
            // teachers via teacher_id — checking only teacher_id hid the button for institutes.
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            const isOwner = this.$role === 'institute'
                ? exam.institute_id === user.id
                : exam.teacher_id === user.id
            return isOwner || exam.questions_count > 0
        },
        openHostLiveModal(exam) {
            this.selectedExam = exam
            this.hostLiveTitle = `Live: ${exam.title}`
            this.liveSessionData = null
            this.showHostLiveModal = true
        },
        async createLiveSession() {
            this.creatingSession = true
            try {
                const examTitle = this.selectedExam.title || this.selectedExam.name || this.hostLiveTitle
                const response = await apiCall('POST', '/api/live-session/create', {
                    exam_id: this.selectedExam.id,
                    title: this.hostLiveTitle,
                    description: `Live session for exam: ${examTitle}`
                })
                
                this.liveSessionData = response
            } catch (error) {
                console.error('Error creating live session:', error)
                await alertDialog({ message: 'Failed to create live session. Please try again.' })
            } finally {
                this.creatingSession = false
            }
        },
        goToHostPage() {
            if (this.liveSessionData) {
                this.showHostLiveModal = false
                this.$router.push(`/${getRole()}/host-live/${this.liveSessionData.session.id}`)
            }
        },
    },
    watch: {
        isGroupDropdownOpen(isOpen) {
            if (isOpen) {
                document.addEventListener('click', this.handleClickOutside)
            } else {
                document.removeEventListener('click', this.handleClickOutside)
            }
        },
    },
    async mounted() {
        await this.fetchExams()
        await this.fetchFolders()
        document.addEventListener('click', this.closeDropdown)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.closeDropdown)
        document.removeEventListener('click', this.handleClickOutside)
        if (this.searchTimeout) clearTimeout(this.searchTimeout)
    },
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

.xl\:ml-128 {
    margin-left: 32rem;
}

.xl\:ms-64 {
    margin-right: 16rem;
    /* Adjust if your sidebar width is different */
}

@media (max-width: 1279px) {

    .xl\:ml-128,
    .xl\:ms-64 {
        margin-left: 0;
        margin-right: 0;
    }
}
</style>
