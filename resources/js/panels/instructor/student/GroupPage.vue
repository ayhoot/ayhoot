<template>
    <InstructorLayout>
        <template #secondary="{ isOpen, close }">
            <GroupsSidebar :isOpen="isOpen" @close="close" group-route-param-key="id" />
        </template>
                <div class="">
                    <!-- Tabs -->
                    <DashTabs
                        :tabs="myTabs"
                        :dropdownItems="groups"
                        :dropdownTriggerLabel="t('groupTutees.selectGroupDropdown')"
                        :dropdownItemPath="`/${$role}/students/group/`"
                    />
                    <nav class="text-sm mb-2" aria-label="Breadcrumb">
                        <ol class="list-none p-0 inline-flex">
                            <li class="flex items-center">
                                <router-link
                                    :to="`/${$role}/students/owned-groups`"
                                    class="text-gray-500 hover:text-gray-700"
                                    >{{ t('groupTutees.breadcrumb.groups') }}</router-link
                                >
                                <svg
                                    class="fill-current w-3 h-3 mx-3 text-gray-500"
                                    :class="{ 'rotate-180': $i18n.locale === 'fa' }"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 320 512"
                                >
                                    <path
                                        d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569 9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"
                                    />
                                </svg>
                            </li>
                            <li class="flex items-center">
                                <span class="text-gray-700">{{ groupName || '' }}</span>
                            </li>
                        </ol>
                    </nav>
                    <!-- Header Section -->
                    <div class="pb-4">
                        <h2 class="text-3xl font-bold mb-2">
                            {{ t('groupTutees.header.title', { groupName: groupName || '' }) }}
                        </h2>
                        <p class="">{{ t('groupTutees.header.description') }}</p>
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
                                    <!-- Search -->
                                    <AyInput
                                        v-model="filters.search"
                                        type="text"
                                        :placeholder="t('groupTutees.filters.searchPlaceholder')"
                                        @update:model-value="handleSearch"
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
                                    <!-- Sort By -->
                                    <AySelect
                                        v-model="filters.sort"
                                        @update:modelValue="handleFilterChange"
                                    >
                                        <option value="joined_at">{{ t('groupTutees.filters.dateJoined') }}</option>
                                        <option value="name">{{ t('groupTutees.filters.name') }}</option>
                                    </AySelect>
                                </div>

                                <!-- Right: Action Buttons -->
                                <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                                    <AyButton
                                        @click="openAssignModal"
                                        type="light-blue"
                                        class="w-full sm:w-auto inline-flex items-center justify-center sm:justify-start"
                                    >
                                        {{ t('groupTutees.assignGroup') }}
                                    </AyButton>
                                    <AyButton
                                        @click="copyInviteLink"
                                        type="dash-blue"
                                        class="w-full sm:w-auto inline-flex items-center justify-center sm:justify-start"
                                    >
                                        {{ t('groupTutees.copyInviteLink') }}
                                    </AyButton>
                                </div>
                            </div>
                        </div>

                        <!-- Table Content -->
                        <div class="overflow-x-auto">
                            <DataTable
                                v-if="filteredStudents.length && !isLoading"
                                :columns="studentColumns"
                                :rows="filteredStudents"
                                wrapper-class=""
                                row-class="hover:bg-gray-50 transition-colors duration-150"
                            >
                                <template #header:actions="{ column }">
                                    <span class="sr-only">{{ column.label }}</span>
                                </template>
                                <template #cell:tutee="{ row: student }">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-12 w-12 text-pink-500 bg-pink-100 rounded-xl flex items-center justify-center"
                                        >
                                            <svg
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    d="M20.3995 21.6L20.3998 18.0003C20.4 16.012 18.7881 14.4 16.7998 14.4H7.2005C5.21244 14.4 3.60073 16.0116 3.6005 17.9996L3.6001 21.6M15.6001 6.00002C15.6001 7.98825 13.9883 9.60002 12.0001 9.60002C10.0119 9.60002 8.4001 7.98825 8.4001 6.00002C8.4001 4.0118 10.0119 2.40002 12.0001 2.40002C13.9883 2.40002 15.6001 4.0118 15.6001 6.00002Z"
                                                    class="stroke-current"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </div>
                                        <div :class="$i18n.locale === 'fa' ? 'mr-4' : 'ml-4'">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ student.name || student.title }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                @{{
                                                    student.username || t('groupTutees.table.body.notAvailable')
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template #cell:joinedAt="{ row: student }">
                                    <div class="text-sm text-gray-900">
                                        {{
                                            formatDate(
                                                student.pivot?.joined_at ||
                                                    student.created_at ||
                                                    student.joined_at
                                            )
                                        }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ t('groupTutees.table.body.viaLink') }}
                                    </div>
                                </template>
                                <template #cell:institute="{ row: student }">
                                    <div class="text-sm text-gray-900">
                                        {{
                                            student.institute?.name || t('groupTutees.table.body.selfEducated')
                                        }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ t('groupTutees.table.body.studiesIn') }}
                                    </div>
                                </template>
                                <template #cell:actions="{ row: student }">
                                    <div class="flex items-center justify-end gap-1 rtl:gap-reverse">
                                        <ResponsiveButton
                                            type="light-blue"
                                            :to="`/${$role}/students/profile/${student.id}`"
                                            class="text-xs px-2 py-1 mx-0.5"
                                            >{{ t('groupTutees.table.actions.profile') }}</ResponsiveButton
                                        >
                                        <ResponsiveButton
                                            type="light-red"
                                            @click="
                                                confirmRemoveStudent(student.id, student.name || student.title)
                                            "
                                            class="text-xs px-2 py-1 mx-0.5"
                                            >{{ t('groupTutees.table.actions.remove') }}</ResponsiveButton
                                        >
                                    </div>
                                </template>
                            </DataTable>

                            <div v-else-if="!isLoading" class="text-center py-12">
                                <svg
                                    width="36"
                                    height="36"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    class="mx-auto"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M2.3999 18.2168C3.49855 16.3843 5.90641 15.1301 9.66302 15.1301C13.4196 15.1301 15.8275 16.3843 16.9261 18.2168M17.6211 13.7406C19.6105 14.7352 20.6052 15.7299 21.5999 17.7193M15.5521 6.1376C16.4882 6.64115 17.1245 7.62991 17.1245 8.7673C17.1245 9.87183 16.5244 10.8362 15.6325 11.3522M12.6471 8.76727C12.6471 10.4153 11.3111 11.7513 9.66302 11.7513C8.01496 11.7513 6.67895 10.4153 6.67895 8.76727C6.67895 7.11922 8.01496 5.7832 9.66302 5.7832C11.3111 5.7832 12.6471 7.11922 12.6471 8.76727Z"
                                        class="stroke-current text-gray-400"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                    />
                                </svg>

                                <h3 class="mt-2 text-sm font-medium text-gray-900">
                                    {{ t('groupTutees.empty.title') }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500" v-if="filters.search && students.length > 0">
                                    {{ t('groupTutees.empty.descriptionWithSearch', { searchTerm: filters.search }) }}
                                </p>
                                <p
                                    class="mt-1 text-sm text-gray-500"
                                    v-else-if="!filters.search && students.length === 0"
                                >
                                    {{ t('groupTutees.empty.description') }}
                                </p>
                                <div class="mt-6">
                                    <AyButton @click="copyInviteLink" type="dash-blue" class="inline-flex items-center">
                                        {{ t('groupTutees.empty.invite') }}
                                    </AyButton>
                                </div>
                            </div>

                            <div v-else class="text-center py-12">
                                <div
                                    class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"
                                ></div>
                                <p class="mt-2 text-sm text-gray-500">{{ t('groupTutees.loading') }}</p>
                            </div>
                        </div>

                        <Pagination
                            v-if="!isLoading && students.length > 0"
                            :pagination="pagination"
                            @page-changed="changePage"
                            :wrapper-class="'px-6 py-4 border-t border-gray-100'"
                        />
                    </div>
                </div>
        <template #overlays>
        <!-- Assign Group Modal -->
        <Modal v-model="isAssignModalOpen" :title="assignTexts.title" size="md" body-class="" @close="closeAssignModal">
            <div class="p-4">
                <AyInput
                    v-model="instituteSearchQuery"
                    :placeholder="assignTexts.searchPlaceholder"
                />
            </div>
            <div class="max-h-96 overflow-y-auto p-4 pt-0">
                <div v-if="isInstitutesLoading" class="text-center py-4">
                    <p>{{ assignTexts.loading }}</p>
                </div>
                <ul v-else-if="filteredInstitutes.length > 0" class="space-y-2">
                    <li
                        v-for="institute in filteredInstitutes"
                        :key="institute.id"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded-md"
                    >
                        <div>
                            <p class="font-semibold">{{ institute.name }}</p>
                            <p class="text-sm text-gray-500">@{{ institute.username }}</p>
                        </div>
                        <ResponsiveButton
                            :type="isInstituteAssigned(institute) ? 'light-red' : 'dash-blue'"
                            :disabled="groupAssignments.length > 0 && !isInstituteAssigned(institute)"
                            class="p-2"
                            @click="
                                isInstituteAssigned(institute)
                                    ? unassignInstitute(institute.id)
                                    : assignInstitute(institute.id)
                            "
                        >
                            {{
                                isInstituteAssigned(institute)
                                    ? t('groupTutees.assignModal.unassign')
                                    : t('groupTutees.assignModal.assign')
                            }}
                        </ResponsiveButton>
                    </li>
                </ul>
                <div v-else class="text-center py-4">
                    <p>{{ assignTexts.noResults }}</p>
                </div>
            </div>
        </Modal>
        </template>
    </InstructorLayout>
</template>

<script>
import { apiCall } from "@/services/apiService"
import { getRole } from '@/utils/role'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toast-notification'
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import GroupsSidebar from '@/components/layout/InstructorGroupsSidebar.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashTabs from '@/components/layout/InstructorDashTabs.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Pagination from '@/components/shared/Pagination.vue'
import Modal from '@/components/shared/Modal.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'GroupStudentsPage',
    components: {
        InstructorLayout,
        AyButton,
        GroupsSidebar,
        ResponsiveButton,
        DashTabs,
        DataTable,
        Pagination,
        Modal,
        AyInput,
        AySelect,
    },
    setup() {
        const { t } = useI18n()
        const $toast = useToast()
        return { t, $toast }
    },
    data() {
        return {
            students: [],
            groupName: '',
            groupToken: null,
            isLoading: false,
            filters: {
                search: '',
                sort: 'joined_at',
                page: 1,
            },
            pagination: {
                current_page: 1,
                from: 0,
                to: 0,
                total: 0,
                last_page: 1,
            },
            itemsPerPage: 10,
            searchTimeout: null,
            groups: [],
            // Assign Modal State
            isAssignModalOpen: false,
            institutes: [],
            groupAssignments: [],
            isInstitutesLoading: false,
            instituteSearchQuery: '',
        }
    },
    computed: {
        studentColumns() {
            const actionsAlign = this.$i18n.locale === 'fa' ? 'left' : 'right'
            return [
                { key: 'tutee', label: this.t('groupTutees.table.header.tutee'), thClass: 'py-2 font-medium' },
                { key: 'joinedAt', label: this.t('groupTutees.table.header.joinedAt'), thClass: 'font-medium' },
                { key: 'institute', label: this.t('groupTutees.table.header.institute'), thClass: 'font-medium' },
                { key: 'actions', label: this.t('groupTutees.table.header.actions'), align: actionsAlign, thClass: 'relative', tdClass: 'font-medium' },
            ]
        },
        myTabs() {
            return [
                { label: this.t('ownedGroupsPage.tabs.allTutees'), route: `/${getRole()}/students` },
                { label: this.t('ownedGroupsPage.tabs.ownedGroups'), route: `/${getRole()}/students/owned-groups` },
                { label: this.t('ownedGroupsPage.tabs.assignedGroups'), route: `/${getRole()}/students/assigned-groups` },
                { label: this.t('ownedGroupsPage.tabs.selectGroup'), route: `/${getRole()}/students` },
            ]
        },
        filteredStudents() {
            let filtered = [...this.students]
            if (this.filters.search) {
                const query = this.filters.search.toLowerCase()
                filtered = filtered.filter(
                    (student) =>
                        (student.name && student.name.toLowerCase().includes(query)) ||
                        (student.username && student.username.toLowerCase().includes(query)) ||
                        (student.title && student.title.toLowerCase().includes(query))
                )
            }
            filtered.sort((a, b) => {
                if (this.filters.sort === 'name') {
                    const nameA = a.name || a.title || ''
                    const nameB = b.name || b.title || ''
                    return nameA.localeCompare(nameB)
                } else {
                    const dateA = new Date(a.pivot?.joined_at || a.created_at || a.joined_at || 0)
                    const dateB = new Date(b.pivot?.joined_at || b.created_at || b.joined_at || 0)
                    return dateB - dateA
                }
            })
            this.pagination.total = filtered.length
            this.pagination.last_page = Math.ceil(filtered.length / this.itemsPerPage) || 1
            if (this.filters.page > this.pagination.last_page) {
                this.filters.page = this.pagination.last_page
            }
            this.pagination.current_page = this.filters.page
            const start = (this.pagination.current_page - 1) * this.itemsPerPage
            const end = start + this.itemsPerPage
            this.pagination.from = filtered.length > 0 ? start + 1 : 0
            this.pagination.to = Math.min(end, filtered.length)
            return filtered.slice(start, end)
        },
        groupId() {
            return this.$route.params.id ? parseInt(this.$route.params.id, 10) : null
        },
        filteredInstitutes() {
            if (!this.instituteSearchQuery) {
                return this.institutes
            }
            const query = this.instituteSearchQuery.toLowerCase()
            return this.institutes.filter(
                (institute) =>
                    (institute.name && institute.name.toLowerCase().includes(query)) ||
                    (institute.username && institute.username.toLowerCase().includes(query))
            )
        },
        // Role-aware labels: teacher assigns to an institute, institute to a teacher.
        assignTexts() {
            const suffix = getRole() === 'institute' ? 'Teacher' : ''
            return {
                title: this.t(`groupTutees.assignModal.title${suffix}`),
                searchPlaceholder: this.t(`groupTutees.assignModal.searchPlaceholder${suffix}`),
                loading: this.t(`groupTutees.assignModal.loading${suffix}`),
                noResults: this.t(`groupTutees.assignModal.noResults${suffix}`),
            }
        },
    },
    methods: {
        async fetchGroups() {
            try {
                const data = await apiCall('GET', `/api/instructor/group`)
                this.groups = data.groups || []
            } catch (error) {
                console.error('Failed to fetch groups for tabs:', error)
                this.groups = []
            }
        },
        async fetchGroupDetails(idToFetch) {
            const currentGroupId = idToFetch || this.groupId
            if (!currentGroupId) {
                this.$toast.error(this.t('groupTutees.notifications.idMissing'))
                this.$router.push(`/${getRole()}/students/owned-groups`)
                return
            }
            this.isLoading = true
            try {
                const response = await apiCall('GET', `/api/instructor/group/${currentGroupId}`)
                if (response.group) {
                    this.students = response.group.students || []
                    this.groupName = response.group.title || this.t('groupTutees.unknownGroup')
                    this.groupToken = response.group.token || null
                    this.groupAssignments = response.group.group_assignments || []
                    document.title = this.t('groupTutees.pageTitle', { groupName: this.groupName })
                    this.filters.page = 1
                } else {
                    this.students = []
                    this.groupName = this.t('groupTutees.unknownGroup')
                    this.groupToken = null
                    this.groupAssignments = []
                    this.$toast.error(this.t('groupTutees.notifications.detailsFetchFailed'))
                }
            } catch (error) {
                console.error('Error fetching group details:', error)
                this.$toast.error(
                    error.message || error.response?.data?.message || this.t('groupTutees.notifications.loadFailed')
                )
                this.students = []
                this.groupName = this.t('groupTutees.errorLoadingGroup')
                this.groupToken = null
                this.groupAssignments = []
            } finally {
                this.isLoading = false
            }
        },
        async copyInviteLink() {
            if (!this.groupToken) {
                this.$toast.error(this.t('groupTutees.notifications.tokenNotAvailable'))
                return
            }
            const inviteLink = `${window.location.origin}/student/join-group/${this.groupToken}`
            try {
                await navigator.clipboard.writeText(inviteLink)
                this.$toast.success(this.t('groupTutees.notifications.copySuccess'))
            } catch (err) {
                console.error('Failed to copy link:', err)
                this.$toast.error(this.t('groupTutees.notifications.copyFailed'))
            }
        },
        handleSearch() {
            if (this.searchTimeout) clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.filters.page = 1
            }, 300)
        },
        handleFilterChange() {
            this.filters.page = 1
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page && page !== this.filters.page) {
                this.filters.page = page
            }
        },
        formatDate(dateString) {
            if (!dateString) return this.t('groupTutees.table.body.notAvailable')
            const locale = this.$i18n.locale === 'fa' ? 'fa-IR' : 'en-US'
            return new Date(dateString).toLocaleDateString(locale, { year: 'numeric', month: 'short', day: 'numeric' })
        },
        async confirmRemoveStudent(studentId, studentName) {
            if (!this.groupId) {
                this.$toast.error(this.t('groupTutees.notifications.contextMissing'))
                return
            }
            const nameToDisplay = studentName || this.t('groupTutees.notifications.thisTutee')
            if (await confirmDialog({ message: this.t('groupDetail.notifications.confirmRemove'), variant: 'danger' })) {
                try {
                    this.isLoading = true
                    await apiCall('GET', `/api/instructor/group/${studentId}/${this.groupId}/remove`)
                    this.$toast.success(
                        this.t('groupTutees.notifications.removeSuccess', {
                            studentName: nameToDisplay,
                            groupName: this.groupName,
                        })
                    )
                    await this.fetchGroupDetails(this.groupId)
                } catch (error) {
                    console.error('Error removing tutee from group:', error)
                    this.$toast.error(
                        error.message ||
                            error.response?.data?.message ||
                            this.t('groupTutees.notifications.removeFailed')
                    )
                } finally {
                    this.isLoading = false
                }
            }
        },
        // Modal Methods
        openAssignModal() {
            this.isAssignModalOpen = true
            this.fetchInstitutes()
        },
        closeAssignModal() {
            this.isAssignModalOpen = false
            this.instituteSearchQuery = ''
        },
        // The assign target is role-dependent: a teacher assigns the group to an
        // institute it belongs to; an institute assigns it to one of its teachers.
        // The list var stays `institutes` to avoid churning the template; it holds
        // whichever target type applies. Both target types expose name/username.
        assignTargetKey() {
            return getRole() === 'institute' ? 'teacher_id' : 'institute_id'
        },
        async fetchInstitutes() {
            this.isInstitutesLoading = true
            try {
                if (getRole() === 'institute') {
                    const data = await apiCall('GET', `/api/instructor/teachers`)
                    this.institutes = data.teachers?.data || data.teachers || []
                } else {
                    const data = await apiCall('GET', `/api/instructor/institutes`)
                    this.institutes = data.institutes || []
                }
            } catch (error) {
                console.error('Failed to fetch assign targets:', error)
                this.$toast.error(this.t('groupTutees.assignModal.loadFailed'))
                this.institutes = []
            } finally {
                this.isInstitutesLoading = false
            }
        },
        isInstituteAssigned(institute) {
            if (!this.groupAssignments || this.groupAssignments.length === 0) {
                return false
            }
            const key = this.assignTargetKey()
            return this.groupAssignments.some((assignment) => assignment[key] === institute.id)
        },
        async assignInstitute(instituteId) {
            try {
                await apiCall('POST', `/api/instructor/group/${this.groupId}/assign`, {
                    [this.assignTargetKey()]: instituteId,
                })
                this.$toast.success(this.t('groupTutees.notifications.assignSuccess'))
                await this.fetchGroupDetails(this.groupId)
                this.closeAssignModal()
            } catch (error) {
                console.error('Failed to assign group:', error)
                this.$toast.error(error.response?.data?.message || this.t('groupTutees.notifications.assignFailed'))
            }
        },
        async unassignInstitute(instituteId) {
            try {
                await apiCall('POST', `/api/instructor/group/${this.groupId}/unassign`, {
                    [this.assignTargetKey()]: instituteId,
                })
                this.$toast.success(this.t('groupTutees.notifications.unassignSuccess'))
                await this.fetchGroupDetails(this.groupId)
                this.closeAssignModal()
            } catch (error) {
                console.error('Failed to unassign group:', error)
                this.$toast.error(error.response?.data?.message || this.t('groupTutees.notifications.unassignFailed'))
            }
        },
    },
    created() {
        if (this.groupId) {
            this.fetchGroupDetails(this.groupId)
        }
    },
    watch: {
        groupId(newId, oldId) {
            if (newId && newId !== oldId) {
                this.fetchGroupDetails(newId)
            }
        },
    },
    async mounted() {
        await this.fetchGroups()
    },
}
</script>
