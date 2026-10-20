<template>
    <InstructorLayout>
        <template #secondary="{ isOpen, close }">
            <GroupsSidebar :isOpen="isOpen" @close="close" group-route-param-key="id" />
        </template>
        <div class="">
            <!-- Tabs -->
            <!-- <DashTabs
                        :tabs="translatedTabs"
                        :dropdownItems="groups"
                        :dropdownTriggerLabel="t('teacherStudent.tabs.select')"
                        :dropdownItemPath="`/${$role}/students/group/`"
                    /> -->
            <!-- Header Section -->
            <div class="pb-4">
                <h2 class="text-3xl font-bold mb-2">{{ t('teacherStudent.title') }}</h2>
                <p class="">{{ t('teacherStudent.subtitle') }}</p>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Table Header with Filters -->
                <div class="p-4 border-b border-gray-100">
                    <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto">
                            <AyInput v-model="filters.search" type="text"
                                :placeholder="t('teacherStudent.searchPlaceholder')" @update:model-value="handleSearch">
                                <template #icon>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </template>
                            </AyInput>
                            <AySelect v-model="filters.sort" @update:modelValue="fetchTutees">
                                <option value="created_at">{{ t('teacherStudent.filters.dateJoined') }}</option>
                                <option value="name">{{ t('teacherStudent.filters.name') }}</option>
                            </AySelect>
                        </div>
                        <AyButton @click="openInviteModal" type="dash-blue"
                            class="w-full sm:w-auto inline-flex items-center justify-center sm:justify-start">{{
                                t('teacherStudent.inviteTutee') }}</AyButton>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <DataTable v-if="filteredTutees.length" :columns="tuteeColumns" :rows="filteredTutees"
                        wrapper-class="" row-class="hover:bg-gray-50 transition-colors duration-150">
                        <template #header:actions="{ column }">
                            <span class="sr-only">{{ column.label }}</span>
                        </template>
                        <template #cell:tutee="{ row: tutee }">
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex-shrink-0 h-12 w-12 text-pink-500 bg-pink-100 rounded-xl flex items-center justify-center">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M20.3995 21.6L20.3998 18.0003C20.4 16.012 18.7881 14.4 16.7998 14.4H7.2005C5.21244 14.4 3.60073 16.0116 3.6005 17.9996L3.6001 21.6M15.6001 6.00002C15.6001 7.98825 13.9883 9.60002 12.0001 9.60002C10.0119 9.60002 8.4001 7.98825 8.4001 6.00002C8.4001 4.0118 10.0119 2.40002 12.0001 2.40002C13.9883 2.40002 15.6001 4.0118 15.6001 6.00002Z"
                                            class="stroke-current" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div class="">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ tutee.name || tutee.title || 'N/A' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @{{ tutee.username || 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template #cell:groups="{ row: tutee }">
                            <div v-if="tutee.groups && tutee.groups.length > 0">
                                <div v-for="group in tutee.groups" :key="group.id"
                                    class="text-sm text-gray-900 leading-tight" :title="group.title">
                                    {{ group.title }}
                                </div>
                            </div>
                            <div v-else class="text-sm text-gray-500 italic">
                                {{ t('teacherStudent.table.noGroup') }}
                            </div>
                        </template>
                        <template #cell:joinedAt="{ row: tutee }">
                            <div class="text-sm text-gray-900">{{ formatDate(tutee.created_at) }}</div>
                            <div class="text-sm text-gray-500" v-if="tutee.invitation_method">
                                {{ t('teacherStudent.table.via', { method: tutee.invitation_method }) }}
                            </div>
                        </template>
                        <template #cell:actions="{ row: tutee }">
                            <div class="flex items-center justify-end gap-1">
                                <ResponsiveButton type="light-blue" :to="`/${$role}/students/profile/${tutee.id}`"
                                    class="text-xs px-2 py-1 mx-0.5">{{ t('teacherStudent.table.actions.profile') }}
                                </ResponsiveButton>
                                <ResponsiveButton type="dash-blue" @click="openAddToGroupModal(tutee)"
                                    class="text-xs px-2 py-1 mx-0.5">{{
                                        t('teacherStudent.table.actions.addToGroup')
                                    }}</ResponsiveButton>
                                <ResponsiveButton type="light-red"
                                    @click="confirmRemoveTutee(tutee.id, tutee.name || tutee.title)"
                                    class="text-xs px-2 py-1 mx-0.5">{{ t('teacherStudent.table.actions.remove') }}
                                </ResponsiveButton>
                            </div>
                        </template>
                    </DataTable>
                    <div v-else-if="!isLoading" class="text-center py-12">
                        <svg class="mx-auto text-gray-400" width="36" height="36" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M19.1999 13.6857C20.4672 14.6326 21.5999 17.0167 21.5999 18.4857C21.5999 18.9433 21.266 19.3143 20.8541 19.3143H20.3999M15.5999 9.8045C16.4197 9.33025 16.9713 8.44383 16.9713 7.42859C16.9713 6.41334 16.4197 5.52693 15.5999 5.05268M3.14567 19.3143H16.2827C16.6946 19.3143 17.0285 18.9433 17.0285 18.4857C17.0285 15.6091 14.6252 13.2771 9.71419 13.2771C4.80317 13.2771 2.3999 15.6091 2.3999 18.4857C2.3999 18.9433 2.7338 19.3143 3.14567 19.3143ZM12.457 7.42859C12.457 8.94343 11.229 10.1714 9.71419 10.1714C8.19935 10.1714 6.97133 8.94343 6.97133 7.42859C6.97133 5.91375 8.19935 4.68573 9.71419 4.68573C11.229 4.68573 12.457 5.91375 12.457 7.42859Z"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            {{ t('teacherStudent.empty.title') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500" v-if="filters.search">
                            {{ t('teacherStudent.empty.searchSubtitle') }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500" v-else>
                            {{ t('teacherStudent.empty.defaultSubtitle') }}
                        </p>
                        <div class="mt-6">
                            <AyButton @click="openInviteModal" type="dash-blue" class="inline-flex items-center">+ {{
                                t('teacherStudent.inviteTutee') }}
                            </AyButton>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                        <p class="mt-2 text-sm text-gray-500">{{ t('teacherStudent.loading') }}</p>
                    </div>
                </div>

                <!-- Pagination -->
                <Pagination v-if="!isLoading" :pagination="pagination" @page-changed="changePage" />
            </div>
        </div>
        <template #overlays>
            <!-- Modals -->
            <Modal v-model="showInviteModal" :title="t('teacherStudent.inviteModal.title')" size="sm">
                <p class="text-sm text-gray-500 mb-4">{{ t('teacherStudent.inviteModal.description') }}</p>
                <div class="relative mt-2">
                    <input type="text" :value="generalInviteLink" readonly
                        class="w-full pl-3 pr-20 py-2 border-2 border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-0 sm:text-sm" />
                    <button @click="copyGeneralInviteLink"
                        class="absolute inset-y-0 right-0 flex items-center px-4 bg-blue-500 text-white text-sm font-semibold rounded-r-md hover:bg-blue-600 transition-colors">
                        {{ t('teacherStudent.inviteModal.copy') }}
                    </button>
                </div>
                <template #footer="{ close }">
                    <button @click="close"
                        class="text-[#0F1D42] bg-[#E0E0E0] hover:bg-[#C8C8C8] shadow-[0_2px_0_rgba(180,180,180,1)] hover:shadow-none hover:translate-y-[2px] px-4 py-2 rounded-md cursor-pointer font-semibold text-sm transition-all">
                        {{ t('teacherStudent.inviteModal.done') }}
                    </button>
                </template>
            </Modal>

            <Modal v-model="showAddToGroupModal" :title="groupModalTitle" size="sm" @close="closeAddToGroupModal">
                <p class="text-xs text-gray-500 mb-4">{{ t('teacherStudent.groupModal.description') }}</p>
                <div class="mb-4">
                    <AyInput v-model="groupSearchQuery"
                        :placeholder="t('teacherStudent.groupModal.searchPlaceholder')" />
                </div>
                <div>
                    <div v-if="isLoadingGroupsForModal" class="text-center py-6">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                        <p class="mt-3 text-sm text-gray-500">{{ t('teacherStudent.groupModal.loading') }}</p>
                    </div>
                    <div v-else-if="filteredGroupsForModal.length > 0"
                        class="max-h-72 overflow-y-auto custom-scrollbar pr-2 space-y-1">
                        <ul class="divide-y divide-gray-100">
                            <li v-for="group in filteredGroupsForModal" :key="group.id"
                                class="flex justify-between items-center py-2 border-b border-gray-300">
                                <span class="text-sm text-gray-700 font-medium truncate" :title="group.title">{{
                                    group.title
                                }}</span>
                                <ResponsiveButton type="light-green" v-if="!isTuteeInGroup(group)"
                                    @click="handleAddTuteeToGroup(group)" :disabled="processingGroupId === group.id"
                                    class="text-xs px-3 py-1.5 rounded-md font-semibold"
                                    :class="{ 'bg-green-300 cursor-wait': processingGroupId === group.id }"><span
                                        v-if="processingGroupId === group.id">{{
                                            t('teacherStudent.groupModal.adding')
                                        }}</span><span v-else>{{ t('teacherStudent.groupModal.add') }}</span>
                                </ResponsiveButton>
                                <ResponsiveButton type="light-red" v-else @click="handleRemoveTuteeFromGroup(group)"
                                    :disabled="processingGroupId === group.id"
                                    class="text-xs px-3 py-1.5 rounded-md font-semibold"
                                    :class="{ 'bg-red-300 cursor-wait': processingGroupId === group.id }"><span
                                        v-if="processingGroupId === group.id">{{
                                            t('teacherStudent.groupModal.removing')
                                        }}</span><span v-else>{{
                                            t('teacherStudent.groupModal.remove')
                                        }}</span></ResponsiveButton>
                            </li>
                        </ul>
                    </div>
                    <div v-else class="text-center py-6">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-600 font-medium">
                            {{ t('teacherStudent.groupModal.empty.title')
                            }}{{ groupSearchQuery ? t('teacherStudent.groupModal.empty.searchSuffix') : '' }}.
                        </p>
                        <p class="mt-1 text-xs text-gray-400">
                            <router-link :to="`/${$role}/students/owned-groups`" @click="closeAddToGroupModal"
                                class="text-blue-500 font-semibold hover:underline">{{
                                    t('teacherStudent.groupModal.empty.createLink') }}</router-link>
                            {{ t('teacherStudent.groupModal.empty.subtitle') }}
                        </p>
                    </div>
                </div>
                <template #footer="{ close }">
                    <button @click="close"
                        class="text-[#0F1D42] bg-[#E0E0E0] hover:bg-[#C8C8C8] shadow-[0_2px_0_rgba(180,180,180,1)] hover:shadow-none hover:translate-y-[2px] px-4 py-2 rounded-md cursor-pointer font-semibold text-sm transition-all">
                        {{ t('teacherStudent.groupModal.done') }}
                    </button>
                </template>
            </Modal>
        </template>
    </InstructorLayout>
</template>

<script>
import { apiCall } from "@/services/apiService"
import { getRole } from '@/utils/role'
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
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toast-notification'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'AllTuteesPage',
    components: { InstructorLayout, AyButton, GroupsSidebar, ResponsiveButton, DashTabs, DataTable, Pagination, Modal, AyInput, AySelect },
    setup() {
        const { t, locale } = useI18n()
        const $toast = useToast()
        return { t, locale, $toast }
    },
    data() {
        return {
            tutees: [],
            isLoading: false,
            filters: { search: '', sort: 'created_at', page: 1 },
            pagination: { current_page: 1, from: 0, to: 0, total: 0, last_page: 1 },
            searchTimeout: null,
            groups: [],
            showInviteModal: false,
            showAddToGroupModal: false,
            selectedTuteeForGroupAction: null,
            allGroups: [],
            groupSearchQuery: '',
            isLoadingGroupsForModal: false,
            processingGroupId: null,
            serverInviteId: null,
            serverInviteLink: null,
        }
    },
    computed: {
        tuteeColumns() {
            return [
                { key: 'tutee', label: this.t('teacherStudent.table.header.tutee'), thClass: 'py-2 font-medium' },
                { key: 'groups', label: this.t('teacherStudent.table.header.groups'), thClass: 'font-medium' },
                { key: 'joinedAt', label: this.t('teacherStudent.table.header.joinedAt'), thClass: 'font-medium' },
                { key: 'actions', label: this.t('teacherStudent.table.header.actions'), align: 'right', thClass: 'relative', tdClass: 'font-medium' },
            ]
        },
        filteredTutees() {
            // Server-side filtering is primary
            return this.tutees
        },
        teacherId() {
            return JSON.parse(localStorage.getItem('user') || '{}').id || null
        },
        generalInviteLink() {
            // The student join route is /student/join-tutor/:token where the token is
            // the teacher's invite_id (backend joinCommunity looks up Teacher by invite_id).
            const inviteId = this.serverInviteId || JSON.parse(localStorage.getItem('user') || '{}').invite_id
            if (!inviteId) return this.t('teacherStudent.inviteModal.linkNotReady')
            return `${window.location.origin}/student/join-tutor/${inviteId}`
        },
        filteredGroupsForModal() {
            if (!this.groupSearchQuery) return this.allGroups
            const query = this.groupSearchQuery.toLowerCase()
            return this.allGroups.filter((group) => group.title && group.title.toLowerCase().includes(query))
        },
        groupModalTitle() {
            const tuteeName = this.selectedTuteeForGroupAction?.name || this.selectedTuteeForGroupAction?.title
            return tuteeName
                ? this.t('teacherStudent.groupModal.title', { name: tuteeName })
                : this.t('teacherStudent.groupModal.defaultTitle')
        },
        translatedTabs() {
            return [
                { label: this.t('teacherStudent.tabs.all'), route: `/${getRole()}/students` },
                { label: this.t('teacherStudent.tabs.owned'), route: `/${getRole()}/students/owned-groups` },
                { label: this.t('teacherStudent.tabs.assigned'), route: `/${getRole()}/students/assigned-groups` },
            ]
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
        async fetchTutees() {
            this.isLoading = true
            try {
                const queryParams = new URLSearchParams(this.filters).toString()
                const response = await apiCall('GET', `/api/instructor/students?${queryParams}`)
                this.tutees = response.students?.data || response.data || []
                if (response.invite_id) {
                    this.serverInviteId = response.invite_id
                    try {
                        const user = JSON.parse(localStorage.getItem('user') || '{}')
                        user.invite_id = response.invite_id
                        localStorage.setItem('user', JSON.stringify(user))
                    } catch (e) {
                        // ignore localStorage parsing errors
                    }
                }
                if (response.invite_link) {
                    this.serverInviteLink = response.invite_link
                }
                this.pagination = {
                    current_page: response.students?.current_page || response.current_page || 1,
                    from: response.students?.from || 0,
                    to: response.students?.to || 0,
                    total: response.students?.total || 0,
                    last_page: response.students?.last_page || 1,
                }
            } catch (error) {
                this.$toast.error(error.message || this.t('teacherStudent.alerts.fetchError'))
                this.tutees = []
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
            return new Date(dateString).toLocaleDateString(this.locale, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            })
        },
        async confirmRemoveTutee(tuteeId, tuteeName) {
            const confirmMessage = tuteeName
                ? this.t('teacherStudent.alerts.removeConfirm', { name: tuteeName })
                : this.t('teacherStudent.alerts.removeConfirmFallback')
            if (await confirmDialog({ message: confirmMessage, variant: 'danger' })) {
                try {
                    await apiCall('GET', `/api/instructor/student/delete/${tuteeId}`)
                    const successMessage = tuteeName
                        ? this.t('teacherStudent.alerts.removeSuccess', { name: tuteeName })
                        : this.t('teacherStudent.alerts.removeSuccessFallback')
                    this.$toast.success(successMessage)
                    await this.fetchTutees()
                } catch (error) {
                    this.$toast.error(error.message || this.t('teacherStudent.alerts.removeError'))
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
            if (!this.generalInviteLink || this.generalInviteLink.includes('...')) {
                this.$toast.error(this.t('teacherStudent.inviteModal.linkNotReady'))
                return
            }
            try {
                await navigator.clipboard.writeText(this.generalInviteLink)
                this.$toast.success(this.t('teacherStudent.inviteModal.copySuccess'))
            } catch (err) {
                this.$toast.error(this.t('teacherStudent.inviteModal.copyError'))
            }
        },
        async fetchAllGroupsIfNotLoaded() {
            if (this.allGroups.length > 0 && !this.isLoadingGroupsForModal) return
            this.isLoadingGroupsForModal = true
            try {
                const response = await apiCall('GET', `/api/instructor/group`)
                this.allGroups = response.groups || []
            } catch (error) {
                this.$toast.error(error.message || this.t('teacherStudent.groupModal.loadError'))
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
            if (!this.selectedTuteeForGroupAction || !group.students) return false
            return group.students.some((student) => student.id === this.selectedTuteeForGroupAction.id)
        },
        async handleAddTuteeToGroup(group) {
            if (!this.selectedTuteeForGroupAction) return
            this.processingGroupId = group.id
            try {
                await apiCall('GET', `/api/instructor/group/${this.selectedTuteeForGroupAction.id}/${group.id}/add`)
                this.$toast.success(
                    this.t('teacherStudent.alerts.addToGroupSuccess', {
                        tuteeName: this.selectedTuteeForGroupAction.name || 'Tutee',
                        groupTitle: group.title,
                    })
                )
                if (!group.students) group.students = []
                group.students.push({ ...this.selectedTuteeForGroupAction })
                await this.fetchTutees()
            } catch (error) {
                this.$toast.error(error.message || this.t('teacherStudent.alerts.addToGroupError'))
            } finally {
                this.processingGroupId = null
            }
        },
        async handleRemoveTuteeFromGroup(group) {
            if (!this.selectedTuteeForGroupAction) return
            this.processingGroupId = group.id
            try {
                await apiCall('GET', `/api/instructor/group/${this.selectedTuteeForGroupAction.id}/${group.id}/remove`)
                this.$toast.success(
                    this.t('teacherStudent.alerts.removeFromGroupSuccess', {
                        tuteeName: this.selectedTuteeForGroupAction.name || 'Tutee',
                        groupTitle: group.title,
                    })
                )
                if (group.students)
                    group.students = group.students.filter((s) => s.id !== this.selectedTuteeForGroupAction.id)
                await this.fetchTutees()
            } catch (error) {
                this.$toast.error(error.message || this.t('teacherStudent.alerts.removeFromGroupError'))
            } finally {
                this.processingGroupId = null
            }
        },
    },
    async mounted() {
        await this.fetchTutees()
        await this.fetchGroups()
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

.xl\:ml-128 {
    margin-left: 32rem;
}

@media (max-width: 1279px) {
    .xl\:ml-128 {
        margin-left: 0;
    }
}
</style>
