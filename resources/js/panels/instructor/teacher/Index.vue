<template>
    <InstructorLayout :secondary="false">
                <div class="">
                    <!-- Tabs -->
                    <div class="mb-6 border-b border-gray-200">
                        <nav class="-mb-px flex gap-8" aria-label="Tabs">
                            <button
                                @click="activeTab = 'allTutors'"
                                :class="[
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm cursor-pointer',
                                    activeTab === 'allTutors'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                ]"
                            >
                                {{ $t('manageTutors.tabs.all') }}
                            </button>
                            <button
                                @click="activeTab = 'tutorRequests'"
                                :class="[
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm cursor-pointer',
                                    activeTab === 'tutorRequests'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                ]"
                            >
                                {{ $t('manageTutors.tabs.requests') }}
                                <span
                                    v-if="teacherRequests.length > 0"
                                    class="inline-block py-0.5 px-2.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                    :class="$i18n.locale === 'fa' ? 'mr-2' : 'ml-2'"
                                >
                                    {{ teacherRequests.length }}
                                </span>
                            </button>
                        </nav>
                    </div>

                    <!-- All Tutors Tab -->
                    <div v-if="activeTab === 'allTutors'">
                        <div class="pb-4">
                            <h2 class="text-3xl font-bold mb-2">{{ $t('manageTutors.allTutors.title') }}</h2>
                            <p class="">{{ $t('manageTutors.allTutors.description') }}</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-4 border-b border-gray-100">
                                <div
                                    class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0"
                                >
                                    <div
                                        class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto"
                                    >
                                        <div class="w-full sm:flex-1">
                                            <AyInput
                                                v-model="filters.search"
                                                type="text"
                                                :placeholder="$t('manageTutors.allTutors.searchPlaceholder')"
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
                                        </div>
                                        <AySelect
                                            v-model="filters.sort"
                                            @update:model-value="fetchTutees"
                                        >
                                            <option value="created_at">
                                                {{ $t('manageTutors.allTutors.filters.dateJoined') }}
                                            </option>
                                            <option value="name">
                                                {{ $t('manageTutors.allTutors.filters.name') }}
                                            </option>
                                        </AySelect>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <ResponsiveButton
                                            @click="copyInviteLink"
                                            :type="isCopied ? 'light-green' : 'dark-green'"
                                            class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 p-2"
                                            :title="generalInviteLink"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ isCopied ? $t('manageTutors.inviteModal.copied') : $t('manageTutors.inviteModal.copyInviteLink') }}</span>
                                        </ResponsiveButton>
                                        <ResponsiveButton
                                            @click="openInviteByEmailModal"
                                            type="dash-blue"
                                            class="w-full sm:w-auto inline-flex items-center justify-center sm:justify-start p-2"
                                        >
                                            {{ $t('manageTutors.allTutors.inviteByEmail') }}
                                        </ResponsiveButton>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <DataTable
                                    v-if="filteredTutees.length"
                                    :columns="tuteeColumns"
                                    :rows="filteredTutees"
                                    wrapper-class=""
                                    row-class="hover:bg-gray-50 transition-colors duration-150"
                                >
                                    <template #header:actions="{ column }">
                                        <span class="sr-only">{{ column.label }}</span>
                                    </template>
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
                                            <div :class="$i18n.locale === 'fa' ? 'mr-4' : 'ml-4'">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{
                                                        tutee.name ||
                                                        tutee.title ||
                                                        $t('manageTutors.allTutors.table.body.notAvailable')
                                                    }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    @{{
                                                        tutee.username ||
                                                        $t('manageTutors.allTutors.table.body.notAvailable')
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <template #cell:email="{ row: tutee }">
                                        <div v-if="tutee.email" class="text-sm text-gray-900">
                                            {{ tutee.email }}
                                        </div>
                                        <div v-else class="text-sm text-gray-500 italic">
                                            {{ $t('manageTutors.allTutors.table.body.noEmail') }}
                                        </div>
                                    </template>
                                    <template #cell:joinedAt="{ row: tutee }">
                                        <div class="text-sm text-gray-900">
                                            {{ formatDate(tutee.created_at) }}
                                        </div>
                                    </template>
                                    <template #cell:actions="{ row: tutee }">
                                        <div class="flex items-center justify-end gap-1">
                                            <ResponsiveButton
                                                type="light-blue"
                                                :to="`/teacher/profile/${tutee.id}`"
                                                class="text-xs px-2 py-1 mx-0.5"
                                                >{{
                                                    $t('manageTutors.allTutors.table.body.profile')
                                                }}</ResponsiveButton
                                            >

                                            <ResponsiveButton
                                                type="light-red"
                                                @click="confirmRemoveTutee(tutee.id, tutee.name || tutee.title)"
                                                class="text-xs px-2 py-1 mx-0.5"
                                                >{{
                                                    $t('manageTutors.allTutors.table.body.remove')
                                                }}</ResponsiveButton
                                            >
                                        </div>
                                    </template>
                                </DataTable>
                                <div v-else-if="!isLoading" class="text-center py-12">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                                        {{ $t('manageTutors.allTutors.empty.title') }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500" v-if="filters.search">
                                        {{ $t('manageTutors.allTutors.empty.description') }}
                                    </p>
                                </div>
                                <div v-else class="text-center py-12">
                                    <p class="mt-2 text-sm text-gray-500">
                                        {{ $t('manageTutors.allTutors.empty.loading') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tutor Requests Tab -->
                    <div v-if="activeTab === 'tutorRequests'">
                        <div class="pb-4">
                            <h2 class="text-3xl font-bold mb-2">{{ $t('manageTutors.tutorRequests.title') }}</h2>
                            <p class="">{{ $t('manageTutors.tutorRequests.description') }}</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="overflow-x-auto">
                                <DataTable
                                    v-if="teacherRequests.length > 0"
                                    :columns="requestColumns"
                                    :rows="teacherRequests"
                                    wrapper-class=""
                                    row-class=""
                                >
                                    <template #header:actions="{ column }">
                                        <span class="sr-only">{{ column.label }}</span>
                                    </template>
                                    <template #cell:tutor="{ row: request }">
                                        <div class="flex items-center">
                                            <div :class="$i18n.locale === 'fa' ? 'mr-4' : 'ml-4'">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{
                                                        request.teacher.name ||
                                                        $t('manageTutors.allTutors.table.body.notAvailable')
                                                    }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    @{{
                                                        request.teacher.username ||
                                                        $t('manageTutors.allTutors.table.body.notAvailable')
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <template #cell:requestDate="{ row: request }">
                                        {{ formatDate(request.created_at) }}
                                    </template>
                                    <template #cell:actions="{ row: request }">
                                        <template v-if="request.status === 'pending'">
                                            <ResponsiveButton
                                                type="light-green"
                                                @click="acceptRequest(request.id)"
                                                class="text-xs px-2 py-1 mx-0.5"
                                                >{{
                                                    $t('manageTutors.tutorRequests.table.body.accept')
                                                }}</ResponsiveButton
                                            >
                                            <ResponsiveButton
                                                type="light-red"
                                                @click="rejectRequest(request.id)"
                                                class="text-xs px-2 py-1 mx-0.5"
                                                >{{
                                                    $t('manageTutors.tutorRequests.table.body.reject')
                                                }}</ResponsiveButton
                                            >
                                        </template>
                                    </template>
                                </DataTable>
                                <div v-else-if="!isLoadingRequests" class="text-center py-12">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                                        {{ $t('manageTutors.tutorRequests.empty.title') }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $t('manageTutors.tutorRequests.empty.description') }}
                                    </p>
                                </div>
                                <div v-else class="text-center py-12">
                                    <p class="mt-2 text-sm text-gray-500">
                                        {{ $t('manageTutors.tutorRequests.empty.loading') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        <template #overlays>
        <!-- Invite by Email Modal -->
        <Modal v-model="showInviteByEmailModal" :title="$t('manageTutors.inviteModal.title')" size="sm">
            <form id="inviteByEmailForm" @submit.prevent="handleInviteByEmail">
                <p class="text-sm text-gray-500 mb-4">
                    {{ $t('manageTutors.inviteModal.description') }}
                </p>
                <AyInput
                    id="email"
                    type="email"
                    v-model="inviteEmail"
                    required
                    :label="$t('manageTutors.inviteModal.emailLabel')"
                    :placeholder="$t('manageTutors.inviteModal.emailPlaceholder')"
                />
            </form>

            <!-- Direct copyable link section -->
            <div class="mt-5 pt-4 border-t border-gray-100">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    {{ $t('manageTutors.inviteModal.orShareLink') }}
                </label>
                <div class="relative flex items-center">
                    <input
                        type="text"
                        :value="generalInviteLink"
                        readonly
                        class="w-full pl-3 pr-20 py-2 text-xs border border-gray-300 rounded-lg bg-gray-50 text-gray-600 focus:outline-none"
                    />
                    <button
                        type="button"
                        @click="copyInviteLink"
                        class="absolute right-1 px-3 py-1 text-xs font-medium bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors cursor-pointer"
                    >
                        {{ isCopied ? $t('manageTutors.inviteModal.copied') : $t('manageTutors.inviteModal.copyLink') }}
                    </button>
                </div>
            </div>

            <!-- Last sent dedicated invite link if any -->
            <div v-if="lastSentInviteLink" class="mt-3 p-2.5 bg-green-50 border border-green-200 rounded-lg text-xs text-green-800">
                <div class="font-medium mb-1">{{ $i18n.locale === 'fa' ? 'لینک اختصاصی دعوت ساخته شد:' : 'Dedicated invite link created:' }}</div>
                <div class="flex items-center gap-2">
                    <input type="text" :value="lastSentInviteLink" readonly class="flex-1 p-1 text-xs bg-white border border-green-300 rounded" />
                    <button type="button" @click="copyText(lastSentInviteLink)" class="px-2 py-1 bg-green-600 text-white rounded text-xs cursor-pointer">
                        {{ $i18n.locale === 'fa' ? 'کپی' : 'Copy' }}
                    </button>
                </div>
            </div>
            <template #footer="{ close }">
                <button
                    @click="close"
                    type="button"
                    class="text-[#0F1D42] bg-[#E0E0E0] hover:bg-[#C8C8C8] shadow-[0_2px_0_rgba(180,180,180,1)] hover:shadow-none hover:translate-y-[2px] px-4 py-2 rounded-md cursor-pointer font-semibold text-sm transition-all"
                >
                    {{ $t('manageTutors.inviteModal.cancel') }}
                </button>
                <AyButton
                    type="submit"
                    form="inviteByEmailForm"
                    :disabled="isInviting"
                    class="w-full sm:w-auto inline-flex items-center justify-center sm:justify-start"
                    :class="{ 'opacity-50 cursor-not-allowed': isInviting }"
                >
                    <span v-if="isInviting">{{ $t('manageTutors.inviteModal.sending') }}</span>
                    <span v-else>{{ $t('manageTutors.inviteModal.send') }}</span>
                </AyButton>
            </template>
        </Modal>
        </template>
    </InstructorLayout>
</template>

<script>
import { apiCall } from "@/services/apiService"
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Modal from '@/components/shared/Modal.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'AllTeachers',
    components: {
        InstructorLayout,
        AyButton,
        ResponsiveButton,
        DataTable,
        Modal,
        AyInput,
        AySelect,
    },
    data() {
        return {
            activeTab: 'allTutors',
            tutees: [],
            isLoading: false,
            filters: {
                search: '',
                status: '',
                sort: 'created_at',
                page: 1,
            },
            pagination: { current_page: 1, from: 0, to: 0, total: 0, last_page: 1 },
            searchTimeout: null,
            teacherRequests: [],
            isLoadingRequests: false,
            showInviteByEmailModal: false,
            inviteEmail: '',
            isInviting: false,
            serverInviteLink: '',
            serverInviteId: '',
            lastSentInviteLink: '',
            isCopied: false,
        }
    },
    computed: {
        tuteeColumns() {
            const actionsAlign = this.$i18n.locale === 'fa' ? 'left' : 'right'
            return [
                { key: 'tutee', label: this.$t('manageTutors.allTutors.table.header.tutee'), thClass: 'py-2 font-medium' },
                { key: 'email', label: this.$t('manageTutors.allTutors.table.header.email'), thClass: 'font-medium' },
                { key: 'joinedAt', label: this.$t('manageTutors.allTutors.table.header.joinedAt'), thClass: 'font-medium' },
                { key: 'actions', label: this.$t('manageTutors.allTutors.table.header.actions'), align: actionsAlign, thClass: 'relative', tdClass: 'font-medium' },
            ]
        },
        requestColumns() {
            const actionsAlign = this.$i18n.locale === 'fa' ? 'left' : 'right'
            return [
                { key: 'tutor', label: this.$t('manageTutors.tutorRequests.table.header.tutor'), thClass: 'font-medium' },
                { key: 'requestDate', label: this.$t('manageTutors.tutorRequests.table.header.requestDate'), thClass: 'font-medium', tdClass: 'text-gray-900' },
                { key: 'status', label: this.$t('manageTutors.tutorRequests.table.header.status'), thClass: 'font-medium', tdClass: 'text-gray-500 capitalize' },
                { key: 'actions', label: this.$t('manageTutors.tutorRequests.table.header.actions'), align: actionsAlign, thClass: 'relative', tdClass: 'font-medium gap-2' },
            ]
        },
        filteredTutees() {
            // Search filtering is now handled server-side via fetchTutees
            return this.tutees
        },
        generalInviteLink() {
            if (this.serverInviteLink) return this.serverInviteLink
            const inviteId = this.serverInviteId || JSON.parse(localStorage.getItem('user') || '{}').invite_id
            if (!inviteId) return ''
            return `${window.location.origin}/join-institute/${inviteId}`
        },
    },
    methods: {
        formatDate(dateString) {
            if (!dateString) return this.$t('manageTutors.allTutors.table.body.notAvailable')
            const locale = this.$i18n.locale === 'fa' ? 'fa-IR' : 'en-US'
            return new Date(dateString).toLocaleDateString(locale, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            })
        },
        async fetchTutees() {
            this.isLoading = true
            try {
                const queryParams = new URLSearchParams(this.filters).toString()
                const response = await apiCall('GET', `/api/instructor/teachers?${queryParams}`)
                this.tutees = response.teachers.data || []
                if (response.invite_link) {
                    this.serverInviteLink = response.invite_link
                }
                if (response.invite_id) {
                    this.serverInviteId = response.invite_id
                }
                this.pagination = {
                    current_page: response.teachers.current_page,
                    from: response.teachers.from,
                    to: response.teachers.to,
                    total: response.teachers.total,
                    last_page: response.teachers.last_page,
                }
            } catch (error) {
                this.$toast.error(error.message || this.$t('manageTutors.notifications.loadFailed'))
            } finally {
                this.isLoading = false
            }
        },
        handleSearch() {
            clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.filters.page = 1
                this.fetchTutees()
            }, 300)
        },
        async confirmRemoveTutee(tuteeId, tuteeName) {
            const ok = await confirmDialog({
                title: this.$t('manageTutors.notifications.swal.removeTitle'),
                message: this.$t('manageTutors.notifications.swal.removeText', { tuteeName }),
                confirmText: this.$t('manageTutors.notifications.swal.confirmRemoveButton'),
                cancelText: this.$t('manageTutors.notifications.swal.cancelButton'),
                variant: 'danger',
            })

            if (ok) {
                try {
                    await apiCall('GET', `/api/instructor/teachers/remove/${tuteeId}`)
                    await this.fetchTutees()
                    await alertDialog({
                        title: this.$t('manageTutors.notifications.swal.removedTitle'),
                        message: this.$t('manageTutors.notifications.swal.removedText', { tuteeName }),
                        confirmText: this.$t('manageTutors.notifications.swal.okButton'),
                    })
                } catch (error) {
                    this.$toast.error(error.message || this.$t('manageTutors.notifications.removeFailed'))
                }
            }
        },
        async fetchTeacherRequests() {
            this.isLoadingRequests = true
            try {
                const response = await apiCall('GET', `/api/instructor/teachers/request`)
                this.teacherRequests = response.teacherRequests.data || []
            } catch (error) {
                this.$toast.error(error.message || this.$t('manageTutors.notifications.loadRequestsFailed'))
            } finally {
                this.isLoadingRequests = false
            }
        },
        async acceptRequest(requestId) {
            try {
                await apiCall('GET', `/api/instructor/teachers/request/${requestId}/accept`)
                this.$toast.success(this.$t('manageTutors.notifications.acceptSuccess'))
                await this.fetchTeacherRequests()
                await this.fetchTutees()
            } catch (error) {
                this.$toast.error(error.message || this.$t('manageTutors.notifications.acceptFailed'))
            }
        },
        async rejectRequest(requestId) {
            const ok = await confirmDialog({
                title: this.$t('manageTutors.notifications.swal.rejectTitle'),
                message: this.$t('manageTutors.notifications.swal.rejectText'),
                confirmText: this.$t('manageTutors.notifications.swal.confirmRejectButton'),
                cancelText: this.$t('manageTutors.notifications.swal.cancelButton'),
                variant: 'danger',
            })

            if (ok) {
                try {
                    await apiCall('GET', `/api/instructor/teachers/request/${requestId}/reject`)
                    this.$toast.success(this.$t('manageTutors.notifications.rejectSuccess'))
                    await this.fetchTeacherRequests()
                } catch (error) {
                    this.$toast.error(error.message || this.$t('manageTutors.notifications.rejectFailed'))
                }
            }
        },
        openInviteByEmailModal() {
            this.inviteEmail = ''
            this.showInviteByEmailModal = true
        },
        closeInviteByEmailModal() {
            this.showInviteByEmailModal = false
        },
        async handleInviteByEmail() {
            if (!this.inviteEmail || this.isInviting) return
            this.isInviting = true
            try {
                const response = await apiCall('POST', `/api/instructor/teachers/invite`, { email: this.inviteEmail })
                this.$toast.success(response.message || this.$t('manageTutors.notifications.inviteSuccess'))
                if (response.invite_link) {
                    this.lastSentInviteLink = response.invite_link
                }
                this.inviteEmail = ''
            } catch (error) {
                this.$toast.error(error.message || this.$t('manageTutors.notifications.inviteFailed'))
            } finally {
                this.isInviting = false
            }
        },
        async copyInviteLink() {
            if (!this.generalInviteLink) {
                this.$toast.error(this.$t('manageTutors.inviteModal.linkNotReady'))
                return
            }
            await this.copyText(this.generalInviteLink)
        },
        async copyText(text) {
            try {
                await navigator.clipboard.writeText(text)
                this.isCopied = true
                this.$toast.success(this.$t('manageTutors.inviteModal.copySuccess'))
                setTimeout(() => {
                    this.isCopied = false
                }, 2000)
            } catch (e) {
                this.$toast.error('Failed to copy to clipboard')
            }
        },
    },
    async mounted() {
        await this.fetchTutees()
        await this.fetchTeacherRequests()
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
</style>
