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
                    <!-- Tabs -->
                    <!-- Header Section -->
                    <div class="pb-4">
                        <h2 class="text-3xl font-bold mb-2">Manage Tutees</h2>
                        <p class="">Manage all tutees</p>
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
                                        @update:model-value="handleSearch"
                                        placeholder="Search tutees by name..."
                                        class="w-full sm:flex-1"
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
                                    <!-- Sort By -->
                                    <AySelect
                                        v-model="filters.sort"
                                        @update:model-value="fetchTutees"
                                        class="w-full sm:w-auto"
                                    >
                                        <option value="created_at">Date Joined</option>
                                        <option value="name">Name</option>
                                    </AySelect>
                                </div>

                                <!-- Right: Create Button -->
                                <!-- <AyButton
                                    @click="openInviteModal"
                                    type="dash-blue"
                                    class="w-full sm:w-auto inline-flex items-center justify-center sm:justify-start"
                                >
                                    Invite Tutee
                                </AyButton> -->
                            </div>
                        </div>

                        <!-- Table Content -->
                        <div class="overflow-x-auto">
                            <DataTable
                                v-if="filteredTutees.length"
                                :columns="tuteeColumns"
                                :rows="filteredTutees"
                                :wrapper-class="''"
                                row-class="hover:bg-gray-50 transition-colors duration-150"
                            >
                                <template #cell:tutee="{ row: tutee }">
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
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ tutee.name || tutee.title || 'N/A' }}
                                            </div>
                                            <div class="text-sm text-gray-500">@{{ tutee.username || 'N/A' }}</div>
                                        </div>
                                    </div>
                                </template>

                                <template #cell:email="{ row: tutee }">
                                    <div class="text-sm text-gray-900">
                                        {{ tutee?.email }}
                                    </div>
                                    <div class="text-sm text-gray-500" v-if="tutee.invitation_method">
                                        Via {{ tutee.invitation_method }}
                                    </div>
                                </template>

                                <template #cell:plan="{ row: tutee }">
                                    <div v-if="tutee.groups && tutee.groups.length > 0">
                                        <div
                                            v-for="group in tutee.groups"
                                            :key="group.id"
                                            class="text-sm text-gray-900 leading-tight"
                                            :title="group.title"
                                        >
                                            {{ group.title }}
                                        </div>
                                    </div>
                                    <div v-else class="text-sm text-gray-500 italic">Free plan</div>
                                </template>

                                <template #cell:plays="{ row: tutee }">
                                    <div class="text-sm text-gray-900">
                                        {{ tutee?.plays || 'No plays yet' }}
                                    </div>
                                </template>

                                <template #cell:joined="{ row: tutee }">
                                    <div class="text-sm text-gray-900">
                                        {{ formatDate(tutee.created_at) }}
                                    </div>
                                    <div class="text-sm text-gray-500" v-if="tutee.invitation_method">
                                        Via {{ tutee.invitation_method }}
                                    </div>
                                </template>

                                <template #cell:actions="{ row: tutee }">
                                    <div class="flex items-center justify-end gap-1">
                                        <ResponsiveButton
                                            type="light-blue"
                                            :to="`/admin/student/profile/${tutee.id}`"
                                            class="text-xs px-2 py-1 mx-0.5"
                                            >Profile</ResponsiveButton
                                        >

                                        <ResponsiveButton
                                            type="light-red"
                                            @click="confirmRemoveTutee(tutee.id, tutee.name || tutee.title)"
                                            class="text-xs px-2 py-1 mx-0.5"
                                            >Remove</ResponsiveButton
                                        >
                                    </div>
                                </template>
                            </DataTable>
                            <div v-else-if="!isLoading" class="text-center py-12">
                                <svg
                                    class="mx-auto h-12 w-12 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-2.121M12 6a3 3 0 11-6 0 3 3 0 016 0zm0 0c0 1.657 1.343 3 3 3s3-1.343 3-3c0-1.657-1.343-3-3-3s-3 1.343-3 3zm-1 9a5.002 5.002 0 00-4.473 2.765A5.002 5.002 0 0011 22.5a5.002 5.002 0 004.473-2.735A5.002 5.002 0 0011 15zm-9 5H1m0-2.121A3.001 3.001 0 015.356 18H7"
                                    />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No tutees found</h3>
                                <p class="mt-1 text-sm text-gray-500" v-if="filters.search">
                                    No tutees match your search.
                                </p>
                                <p class="mt-1 text-sm text-gray-500" v-else>Get started by inviting a new tutee.</p>
                                <div class="mt-6">
                                    <!-- <AyButton
                                        @click="openInviteModal"
                                        type="dash-blue"
                                        class="inline-flex items-center"
                                    >
                                        + Invite Tutee
                                    </AyButton> -->
                                </div>
                            </div>
                            <div v-else class="text-center py-12">
                                <div
                                    class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"
                                ></div>
                                <p class="mt-2 text-sm text-gray-500">Loading tutees...</p>
                            </div>
                        </div>

                        <Pagination v-if="!isLoading" :pagination="pagination" @page-changed="changePage" />
                    </div>
                </div>
            </main>
        </div>

        <!-- General Invite Tutee Modal -->
        <Modal v-model="showInviteModal" title="Invite a Tutee" size="md">
            <p class="text-sm text-gray-500 mb-4">Share this link with anyone you want to invite as your tutee.</p>

            <div class="relative mt-2">
                <input
                    type="text"
                    :value="generalInviteLink"
                    readonly
                    class="w-full pl-3 pr-20 py-2 border-2 border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-0 sm:text-sm"
                />
                <button
                    @click="copyGeneralInviteLink"
                    class="absolute inset-y-0 right-0 flex items-center px-4 bg-blue-500 text-white text-sm font-semibold rounded-r-md hover:bg-blue-600 transition-colors"
                >
                    Copy
                </button>
            </div>
            <template #footer>
                <AyButton type="light-gray" @click="closeInviteModal">Done</AyButton>
            </template>
        </Modal>

        <!-- Add Tutee to Group Modal -->
        <Modal v-model="showAddToGroupModal" size="md">
            <template #header>
                <h3 class="text-lg font-semibold text-gray-900">
                    Add "{{ selectedTuteeForGroupAction?.name || selectedTuteeForGroupAction?.title || 'Tutee' }}" to
                    Group
                </h3>
            </template>
            <p class="text-xs text-gray-500 mb-4">Select a group to assign this tutee.</p>

            <div class="mb-4">
                <AyInput v-model="groupSearchQuery" placeholder="Search groups..." />
            </div>

            <div v-if="isLoadingGroupsForModal" class="text-center py-6">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <p class="mt-3 text-sm text-gray-500">Loading groups...</p>
            </div>
            <div
                v-else-if="filteredGroupsForModal.length > 0"
                class="max-h-72 overflow-y-auto custom-scrollbar pr-2 space-y-1"
            >
                <ul class="divide-y divide-gray-100">
                    <li
                        v-for="group in filteredGroupsForModal"
                        :key="group.id"
                        class="flex justify-between items-center py-2 border-b border-gray-300"
                    >
                        <span class="text-sm text-gray-700 font-medium truncate" :title="group.title">{{
                            group.title
                        }}</span>
                        <ResponsiveButton
                            type="light-green"
                            v-if="!isTuteeInGroup(group)"
                            @click="handleAddTuteeToGroup(group)"
                            :disabled="processingGroupId === group.id"
                            class="text-xs px-3 py-1.5 rounded-md font-semibold"
                            :class="{ 'bg-green-300 cursor-wait': processingGroupId === group.id }"
                        >
                            <span v-if="processingGroupId === group.id">Adding...</span>
                            <span v-else>Add</span>
                        </ResponsiveButton>
                        <ResponsiveButton
                            type="light-red"
                            v-else
                            @click="handleRemoveTuteeFromGroup(group)"
                            :disabled="processingGroupId === group.id"
                            class="text-xs px-3 py-1.5 rounded-md font-semibold"
                            :class="{ 'bg-red-300 cursor-wait': processingGroupId === group.id }"
                        >
                            <span v-if="processingGroupId === group.id">Removing...</span>
                            <span v-else>Remove</span>
                        </ResponsiveButton>
                    </li>
                </ul>
            </div>
            <div v-else class="text-center py-6">
                <svg
                    class="mx-auto h-12 w-12 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"
                    />
                </svg>
                <p class="mt-2 text-sm text-gray-600 font-medium">
                    No groups found{{ groupSearchQuery ? ' matching your search' : '' }}.
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    <router-link
                        to="/teacher/students/owned-groups"
                        @click="closeAddToGroupModal"
                        class="text-blue-500 font-semibold hover:underline"
                        >Create one</router-link
                    >
                    or ensure they are not archived.
                </p>
            </div>
            <template #footer>
                <AyButton type="light-gray" @click="closeAddToGroupModal">Done</AyButton>
            </template>
        </Modal>
    </div>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useConfirm } from '@/composables/useConfirm'
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Pagination from '@/components/shared/Pagination.vue'
import Modal from '@/components/shared/Modal.vue'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'AllTuteesPage',
    components: {
        Sidebar,
        Header,
        AyButton,
        AyInput,
        AySelect,
        ResponsiveButton,
        DataTable,
        Pagination,
        Modal,
    },
    data() {
        return {
            isSidebarOpen: false,
            tutees: [],
            isLoading: false,
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
            myTabs: [
                { label: 'All Subscribers', route: '/teacher/students' },
                {
                    label: 'Assigned Groups',
                    route: '/teacher/students/assigned-groups',
                },
                {
                    label: 'Owned Groups',
                    route: '/teacher/students/owned-groups',
                },
                {
                    label: 'Recent Groups',
                    route: '/teacher/students/recent-groups',
                },
            ],
            showInviteModal: false,
            showAddToGroupModal: false,
            selectedTuteeForGroupAction: null,
            allGroups: [],
            groupSearchQuery: '',
            isLoadingGroupsForModal: false,
            processingGroupId: null,
        }
    },
    computed: {
        tuteeColumns() {
            return [
                { key: 'tutee', label: 'Tutee' },
                { key: 'email', label: 'Email' },
                { key: 'plan', label: 'Plan' },
                { key: 'plays', label: 'Plays' },
                { key: 'joined', label: 'Joined at' },
                { key: 'actions', label: 'Actions', align: 'right', thClass: 'sr-only' },
            ]
        },
        filteredTutees() {
            let filtered = [...this.tutees]
            if (this.filters.search) {
                const query = this.filters.search.toLowerCase()
                filtered = filtered.filter(
                    (tutee) =>
                        (tutee.name && tutee.name.toLowerCase().includes(query)) ||
                        (tutee.username && tutee.username.toLowerCase().includes(query)) ||
                        (tutee.title && tutee.title.toLowerCase().includes(query))
                )
            }
            if (this.filters.status) {
                filtered = filtered.filter((tutee) => tutee.status === this.filters.status)
            }
            filtered.sort((a, b) => {
                if (this.filters.sort === 'name') {
                    const nameA = a.name || a.title || ''
                    const nameB = b.name || b.title || ''
                    return nameA.localeCompare(nameB)
                } else if (this.filters.sort === 'status') {
                    return (a.status || '').localeCompare(b.status || '')
                } else {
                    return new Date(b.created_at) - new Date(a.created_at)
                }
            })
            return filtered
        },
        teacherName() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user.name || 'Teacher'
        },
        teacherUsername() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user.username || 'Teacher'
        },
        teacherId() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user.id || null
        },
        generalInviteLink() {
            if (!this.teacherId) return 'Loading link...'
            return `${window.location.origin}/student/join/teacher/${this.teacherId}`
        },
        filteredGroupsForModal() {
            if (!this.groupSearchQuery) {
                return this.allGroups
            }
            const query = this.groupSearchQuery.toLowerCase()
            return this.allGroups.filter((group) => group.title && group.title.toLowerCase().includes(query))
        },
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        async fetchTutees() {
            this.isLoading = true
            try {
                const queryParams = new URLSearchParams(this.filters).toString()
                const response = await apiCall('GET', `/api/admin/student?${queryParams}`)
                if (response.students && response.students.data) {
                    this.tutees = response.students.data
                    this.pagination = {
                        current_page: response.students.current_page,
                        from: response.students.from,
                        to: response.students.to,
                        total: response.students.total,
                        last_page: response.students.last_page,
                    }
                } else if (response.data && Array.isArray(response.data)) {
                    this.tutees = response.data
                    this.pagination = {
                        current_page: response.current_page || 1,
                        from: response.from || 0,
                        to: response.to || 0,
                        total: response.total || 0,
                        last_page: response.last_page || 1,
                    }
                } else if (Array.isArray(response)) {
                    this.tutees = response
                    this.pagination = {
                        current_page: 1,
                        from: 1,
                        to: this.tutees.length,
                        total: this.tutees.length,
                        last_page: 1,
                    }
                } else {
                    this.tutees = []
                    this.$toast.error('Unexpected response structure for tutees.')
                    this.pagination.last_page = 1
                }
            } catch (error) {
                console.error('Error fetching tutees:', error)
                this.$toast.error(error.message || 'Failed to load tutees.')
                this.tutees = []
                this.pagination.last_page = 1
            } finally {
                this.isLoading = false
            }
        },
        handleSearch() {
            if (this.searchTimeout) clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.filters.page = 1
                this.fetchTutees()
            }, 300)
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page && page !== this.filters.page) {
                this.filters.page = page
                this.fetchTutees()
            }
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A'
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            })
        },
        async confirmRemoveTutee(tuteeId, tuteeName) {
            if (
                await confirmDialog({
                    message: `Are you sure you want to remove the tutee "${
                        tuteeName || 'this tutee'
                    }"? This action cannot be undone.`,
                    variant: 'danger',
                })
            ) {
                try {
                    this.isLoading = true
                    await apiCall('GET', `/api/admin/student/delete/${tuteeId}`)
                    this.$toast.success(`Tutee "${tuteeName || 'ID: ' + tuteeId}" removed successfully.`)
                    await this.fetchTutees()
                } catch (error) {
                    console.error('Error removing tutee:', error)
                    this.$toast.error(error.message || `Failed to remove tutee.`)
                } finally {
                    this.isLoading = false
                }
            }
        },
        openInviteModal() {
            this.showInviteModal = true
        },
        closeInviteModal() {
            this.showInviteModal = false
        },
        async copyGeneralInviteLink() {
            if (!this.generalInviteLink || this.generalInviteLink.includes('Loading')) {
                this.$toast.error('Link is not ready yet.')
                return
            }
            try {
                await navigator.clipboard.writeText(this.generalInviteLink)
                this.$toast.success('Invitation link copied to clipboard!')
            } catch (err) {
                console.error('Failed to copy link:', err)
                this.$toast.error('Could not copy link.')
            }
        },
        async fetchAllGroupsIfNotLoaded() {
            if (this.allGroups.length > 0 && !this.isLoadingGroupsForModal) {
                return
            }
            this.isLoadingGroupsForModal = true
            try {
                const response = await apiCall('GET', '/api/admin/group')
                if (response.groups && Array.isArray(response.groups)) {
                    this.allGroups = response.groups
                } else {
                    this.allGroups = []
                    this.$toast.error('Could not load groups for modal.')
                }
            } catch (error) {
                console.error('Error fetching groups for modal:', error)
                this.$toast.error(error.message || 'Failed to load groups for modal.')
                this.allGroups = []
            } finally {
                this.isLoadingGroupsForModal = false
            }
        },
        openAddToGroupModal(tutee) {
            this.selectedTuteeForGroupAction = tutee
            this.groupSearchQuery = ''
            this.showAddToGroupModal = true
            this.fetchAllGroupsIfNotLoaded()
        },
        closeAddToGroupModal() {
            this.showAddToGroupModal = false
            this.selectedTuteeForGroupAction = null
            this.processingGroupId = null
        },
        isTuteeInGroup(group) {
            if (!this.selectedTuteeForGroupAction || !group.students) {
                return false
            }
            return group.students.some((student) => student.id === this.selectedTuteeForGroupAction.id)
        },
        async handleAddTuteeToGroup(group) {
            if (!this.selectedTuteeForGroupAction) return
            this.processingGroupId = group.id
            try {
                await apiCall('GET', `/api/admin/group/${this.selectedTuteeForGroupAction.id}/${group.id}/add`)
                this.$toast.success(
                    `Added ${this.selectedTuteeForGroupAction.name || this.selectedTuteeForGroupAction.title} to ${
                        group.title
                    }.`
                )

                const studentToAdd = { ...this.selectedTuteeForGroupAction }
                if (!group.students) group.students = []
                if (!group.students.some((s) => s.id === studentToAdd.id)) {
                    group.students.push(studentToAdd)
                }
                await this.fetchTutees()
            } catch (error) {
                console.error('Error adding tutee to group:', error)
                this.$toast.error(error.message || 'Failed to add tutee to group.')
            } finally {
                this.processingGroupId = null
            }
        },
        async handleRemoveTuteeFromGroup(group) {
            if (!this.selectedTuteeForGroupAction) return
            this.processingGroupId = group.id
            try {
                await apiCall('GET', `/api/admin/group/${this.selectedTuteeForGroupAction.id}/${group.id}/remove`)
                this.$toast.success(
                    `Removed ${this.selectedTuteeForGroupAction.name || this.selectedTuteeForGroupAction.title} from ${
                        group.title
                    }.`
                )

                if (group.students) {
                    group.students = group.students.filter(
                        (student) => student.id !== this.selectedTuteeForGroupAction.id
                    )
                }
                await this.fetchTutees()
            } catch (error) {
                console.error('Error removing tutee from group:', error)
                this.$toast.error(error.message || 'Failed to remove tutee from group.')
            } finally {
                this.processingGroupId = null
            }
        },
    },
    async mounted() {
        await this.fetchTutees()
    },
}
</script>

<style scoped>
.container-fluid {
    width: 100%;
    padding-right: 1rem;
    padding-left: 1rem;
    margin-right: auto;
    margin-left: auto;
}

@media (min-width: 640px) {
    .container-fluid {
        padding-right: 1.5rem;
        padding-left: 1.5rem;
    }
}
.transition-shadow {
    transition: box-shadow 0.3s ease;
}
.hover\:shadow-lg:hover {
    box-shadow:
        0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
.max-h-80 {
    /* For modal group list */
    max-height: 20rem;
}
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
</style>
