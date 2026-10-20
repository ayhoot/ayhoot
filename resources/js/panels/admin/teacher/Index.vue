<template>
    <div class="bg-gray-50 min-h-screen">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" :teacherName="teacherName" :teacherUsername="teacherUsername" />
        </div>
        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />
            <main class="flex-1 bg-gray-50 ml-0 xl:ml-64 p-6 overflow-y-auto mt-16 min-h-[calc(100vh-4rem)]">
                <div>
                    <div class="pb-4">
                        <h2 class="text-3xl font-bold mb-2">Manage Tutors</h2>
                        <p class="text-gray-600">View, create, and manage all tutors</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                                <div
                                    class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto"
                                >
                                    <AyInput
                                        v-model="filters.search"
                                        @update:model-value="handleSearch"
                                        placeholder="Search by name or username..."
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
                                    <AySelect
                                        v-model="filters.sort"
                                        @update:model-value="fetchTutors"
                                        class="w-full sm:w-auto"
                                    >
                                        <option value="created_at">Date Joined</option>
                                        <option value="name">Name</option>
                                    </AySelect>
                                </div>
                                <AyButton
                                    @click="openCreateModal"
                                    type="dash-blue"
                                    class="w-full sm:w-auto inline-flex items-center justify-center sm:justify-start"
                                    >Create Tutor</AyButton
                                >
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <DataTable
                                v-if="filteredTutors.length"
                                :columns="tutorColumns"
                                :rows="filteredTutors"
                                :wrapper-class="''"
                                row-class="hover:bg-gray-50 transition-colors duration-150"
                            >
                                <template #cell:tutor="{ row: tutor }">
                                    <div class="flex items-center">
                                        <img
                                            :src="tutor.avatar || '/imgHolders/profile.svg'"
                                            class="h-12 w-12 rounded-xl object-cover"
                                            alt="Tutor avatar"
                                        />
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ tutor.name || 'N/A' }}
                                            </div>
                                            <div class="text-sm text-gray-500">@{{ tutor.username || 'N/A' }}</div>
                                        </div>
                                    </div>
                                </template>
                                <template #cell:labels="{ row: tutor }">
                                    <div class="flex flex-wrap gap-1 max-w-[200px]">
                                        <span
                                            v-for="label in tutor.labels"
                                            :key="label.id"
                                            class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full text-white"
                                            :style="{ backgroundColor: label.color }"
                                        >
                                            {{ label.title }}
                                        </span>
                                        <span
                                            v-if="!tutor.labels || tutor.labels.length === 0"
                                            class="text-xs text-gray-400"
                                            >N/A</span
                                        >
                                    </div>
                                </template>
                                <template #cell:status="{ row: tutor }">
                                    <span
                                        v-if="tutor.status === 'block'"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"
                                        >Banned</span
                                    >
                                    <span
                                        v-else-if="tutor.verified_at"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                                        >Verified</span
                                    >
                                    <span
                                        v-else
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"
                                        >Pending</span
                                    >
                                </template>
                                <template #cell:plan="{ row: tutor }">
                                    {{ tutor.plan?.title || 'Free plan' }}
                                </template>
                                <template #cell:ayhoots="{ row: tutor }">
                                    <div>
                                        <span class="text-gray-800">{{ tutor.exams_count || 'N/A' }}</span>
                                    </div>
                                </template>
                                <template #cell:courses="{ row: tutor }">
                                    <div>
                                        <span class="text-gray-800">{{ tutor.courses_count || 'N/A' }}</span>
                                    </div>
                                </template>
                                <template #cell:tutees="{ row: tutor }">
                                    <div>
                                        <span class="text-gray-800">{{ tutor.students_count || 'N/A' }}</span>
                                    </div>
                                </template>
                                <template #cell:institute="{ row: tutor }">
                                    <div
                                        v-if="tutor.institutes && tutor.institutes.length > 0"
                                        class="flex items-center gap-2"
                                    >
                                        <span class="text-gray-700 font-medium">{{ tutor.institutes[0].name }}</span>
                                        <span
                                            v-if="tutor.institutes.length > 1"
                                            class="px-2 py-0.5 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full"
                                        >
                                            +{{ tutor.institutes.length - 1 }}
                                        </span>
                                    </div>
                                    <span v-else>None</span>
                                </template>
                                <template #header:actions>
                                    <span class="sr-only">Actions</span>
                                </template>
                                <template #cell:actions="{ row: tutor }">
                                    <div class="flex items-center justify-end gap-1">
                                        <ResponsiveButton
                                            @click="openManageLabelsModal(tutor)"
                                            type="light-blue"
                                            class="text-xs px-2 py-1"
                                            >Labels</ResponsiveButton
                                        >
                                        <ResponsiveButton
                                            type="light-gray"
                                            :to="`/admin/teacher/profile/${tutor.id}`"
                                            class="text-xs px-2 py-1"
                                            >Profile</ResponsiveButton
                                        >
                                        <ResponsiveButton
                                            @click="toggleVerification(tutor)"
                                            type="dash-blue"
                                            class="text-xs px-2 py-1"
                                            >{{ tutor.verified_at ? 'Unverify' : 'Verify' }}</ResponsiveButton
                                        >
                                        <ResponsiveButton
                                            type="dark-gray"
                                            @click="toggleBan(tutor)"
                                            class="text-xs px-2 py-1"
                                            >{{ tutor.status === 'block' ? 'Unban' : 'Ban' }}</ResponsiveButton
                                        >
                                        <ResponsiveButton
                                            @click="confirmRemoveTutor(tutor.id, tutor.name)"
                                            type="light-red"
                                            class="text-xs px-2 py-1"
                                            >Remove</ResponsiveButton
                                        >
                                    </div>
                                </template>
                            </DataTable>
                            <div v-else-if="isLoading" class="text-center py-12">
                                <div
                                    class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"
                                ></div>
                                <p class="mt-2 text-sm text-gray-500">Loading tutors...</p>
                            </div>
                            <div v-else class="text-center py-12">
                                <svg
                                    class="mx-auto h-12 w-12 text-gray-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        vector-effect="non-scaling-stroke"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                                    />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No tutors found</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by creating a new tutor.</p>
                                <div class="mt-6">
                                    <AyButton
                                        @click="openCreateModal"
                                        type="dash-blue"
                                        class="inline-flex items-center"
                                    >
                                        + Create Tutor
                                    </AyButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Create Tutor Modal -->
        <Modal v-model="showCreateModal" title="Create New Tutor" size="md">
            <p class="text-sm text-gray-500 mb-4">Fill out the form below to add a new tutor account.</p>
            <form @submit.prevent="handleCreateTutor" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <AyInput v-model="newTutor.first_name" label="First Name" required placeholder="John" />
                    <AyInput v-model="newTutor.last_name" label="Last Name" required placeholder="Doe" />
                </div>
                <AyInput v-model="newTutor.username" label="Username" required placeholder="johndoe" />
                <AyInput
                    v-model="newTutor.email"
                    type="email"
                    label="Email Address"
                    required
                    placeholder="john.doe@example.com"
                />
                <AyInput v-model="newTutor.password" type="password" label="Password" required placeholder="••••••••" />
            </form>
            <template #footer>
                <AyButton type="light-gray" @click="closeCreateModal">Cancel</AyButton>
                <AyButton type="dash-blue" @click="handleCreateTutor">Create Tutor</AyButton>
            </template>
        </Modal>

        <!-- Manage Labels Modal -->
        <Modal v-model="showManageLabelsModal" title="Manage Tutor Labels" size="md">
            <p class="text-xs text-gray-500 mb-4">
                Select labels to assign to this tutor. Actions are saved instantly.
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
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useToast } from 'vue-toast-notification'
import 'vue-toast-notification/dist/theme-sugar.css'
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Modal from '@/components/shared/Modal.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'ManageTutors',
    components: { Sidebar, Header, AyButton, AyInput, AySelect, ResponsiveButton, DataTable, Modal },
    setup() {
        const $toast = useToast()
        return { $toast }
    },
    data() {
        return {
            isSidebarOpen: false,
            tutors: [],
            isLoading: false,
            filters: { search: '', sort: 'created_at', page: 1 },
            pagination: { current_page: 1, from: 0, to: 0, total: 0, last_page: 1 },
            searchTimeout: null,
            showCreateModal: false,
            newTutor: { first_name: '', last_name: '', username: '', email: '', password: '' },
            // Data for label management
            showManageLabelsModal: false,
            selectedTutorForLabels: null,
            availableLabels: [],
        }
    },
    computed: {
        tutorColumns() {
            return [
                { key: 'tutor', label: 'Tutor', align: 'left', thClass: 'font-medium' },
                { key: 'labels', label: 'Labels', align: 'left', thClass: 'font-medium' },
                { key: 'status', label: 'Status', align: 'left', thClass: 'font-medium' },
                { key: 'plan', label: 'Plan', align: 'left', thClass: 'font-medium', tdClass: 'text-gray-500 italic' },
                { key: 'ayhoots', label: 'Ayhoots', align: 'left', thClass: 'font-medium', tdClass: 'text-gray-500' },
                { key: 'courses', label: 'Courses', align: 'left', thClass: 'font-medium', tdClass: 'text-gray-500' },
                { key: 'tutees', label: 'Tutees', align: 'left', thClass: 'font-medium', tdClass: 'text-gray-500' },
                {
                    key: 'institute',
                    label: 'Institute',
                    align: 'left',
                    thClass: 'font-medium',
                    tdClass: 'text-gray-500',
                },
                { key: 'actions', label: 'Actions', align: 'right', thClass: 'relative', tdClass: 'font-medium' },
            ]
        },
        filteredTutors() {
            return this.tutors
        },
        teacherName() {
            try {
                return JSON.parse(localStorage.getItem('user')).name || 'Admin'
            } catch (e) {
                return 'Admin'
            }
        },
        teacherUsername() {
            try {
                return JSON.parse(localStorage.getItem('user')).username || 'Admin'
            } catch (e) {
                return 'Admin'
            }
        },
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        async fetchTutors() {
            this.isLoading = true
            try {
                const queryParams = new URLSearchParams(this.filters).toString()
                const response = await apiCall('GET', `/api/admin/teacher?${queryParams}`)
                this.tutors = response.teachers.data || []
                this.pagination = response.teachers
            } catch (error) {
                this.$toast.error(error.message || 'Failed to load tutors.')
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
        handleSearch() {
            clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.filters.page = 1
                this.fetchTutors()
            }, 300)
        },
        async confirmRemoveTutor(tutorId, tutorName) {
            if (
                await confirmDialog({ message: `Are you sure you want to remove "${tutorName}"?`, variant: 'danger' })
            ) {
                try {
                    const response = await apiCall('GET', `/api/admin/teacher/delete/${tutorId}`)
                    this.$toast.success(response.message)
                    await this.fetchTutors()
                } catch (error) {
                    this.$toast.error(error.message || `Failed to remove tutor.`)
                }
            }
        },
        async toggleVerification(tutor) {
            const action = tutor.verified_at ? 'unverify' : 'verify'
            if (await confirmDialog({ message: `Are you sure you want to ${action} "${tutor.name}"?` })) {
                try {
                    const response = await apiCall('POST', `/api/admin/teacher/${tutor.id}/${action}`)
                    this.$toast.success(response.message)
                    await this.fetchTutors()
                } catch (error) {
                    this.$toast.error(error.message || `Failed to ${action} tutor.`)
                }
            }
        },
        async toggleBan(tutor) {
            const action = tutor.status === 'block' ? 'unban' : 'ban'
            if (await confirmDialog({ message: `Are you sure you want to ${action} "${tutor.name}"?` })) {
                try {
                    const response = await apiCall('POST', `/api/admin/teacher/${tutor.id}/${action}`)
                    this.$toast.success(response.message)
                    await this.fetchTutors()
                } catch (error) {
                    this.$toast.error(error.message || `Failed to ${action} tutor.`)
                }
            }
        },
        openCreateModal() {
            this.newTutor = { first_name: '', last_name: '', username: '', email: '', password: '' }
            this.showCreateModal = true
        },
        closeCreateModal() {
            this.showCreateModal = false
        },
        async handleCreateTutor() {
            try {
                const response = await apiCall('POST', '/api/admin/teacher', this.newTutor)
                this.$toast.success(response.message)
                this.closeCreateModal()
                await this.fetchTutors()
            } catch (error) {
                const errorMessage = error.response?.errors
                    ? Object.values(error.response.errors).flat().join(' ')
                    : error.message || 'Failed to create tutor.'
                this.$toast.error(errorMessage)
            }
        },
        isLabelAssigned(labelId) {
            if (!this.selectedTutorForLabels || !this.selectedTutorForLabels.labels) return false
            return this.selectedTutorForLabels.labels.some((l) => l.id === labelId)
        },
        openManageLabelsModal(tutor) {
            this.selectedTutorForLabels = tutor
            this.showManageLabelsModal = true
        },
        closeManageLabelsModal() {
            this.showManageLabelsModal = false
            this.selectedTutorForLabels = null
        },
        async toggleLabel(label, event) {
            const isChecked = event.target.checked
            const action = isChecked ? this.assignLabel : this.removeLabel
            await action(label, event)
        },
        async assignLabel(label, event) {
            try {
                const payload = { teacher_id: this.selectedTutorForLabels.id }
                await apiCall('POST', `/api/admin/label/${label.id}/assign-teacher`, payload)

                const tutorIndex = this.tutors.findIndex((t) => t.id === this.selectedTutorForLabels.id)
                if (tutorIndex !== -1) {
                    if (!this.tutors[tutorIndex].labels) this.tutors[tutorIndex].labels = []
                    this.tutors[tutorIndex].labels.push(label)
                }
                this.$toast.success(`"${label.title}" label assigned.`)
            } catch (error) {
                this.$toast.error(error.message || `Failed to assign "${label.title}".`)
                if (event) event.target.checked = false
            }
        },
        async removeLabel(label, event) {
            try {
                const payload = { teacher_id: this.selectedTutorForLabels.id }
                await apiCall('POST', `/api/admin/label/${label.id}/remove-teacher`, payload)

                const tutorIndex = this.tutors.findIndex((t) => t.id === this.selectedTutorForLabels.id)
                if (tutorIndex !== -1 && this.tutors[tutorIndex].labels) {
                    this.tutors[tutorIndex].labels = this.tutors[tutorIndex].labels.filter((l) => l.id !== label.id)
                }
                this.$toast.success(`"${label.title}" label removed.`)
            } catch (error) {
                this.$toast.error(error.message || `Failed to remove "${label.title}".`)
                if (event) event.target.checked = true
            }
        },
    },
    async mounted() {
        await Promise.all([this.fetchTutors(), this.fetchLabels()])
    },
}
</script>
