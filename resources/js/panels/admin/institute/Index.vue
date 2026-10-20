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
                        <h2 class="text-3xl font-bold mb-2">Manage Institutes</h2>
                        <p class="text-gray-600">View, create, and manage all institutes</p>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                                <div
                                    class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto"
                                >
                                    <AyInput
                                        v-model="filters.search"
                                        placeholder="Search by name or username..."
                                        class="w-full sm:flex-1 sm:min-w-[250px]"
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
                                        <option value="">All Statuses</option>
                                        <option value="active">Active</option>
                                        <option value="deactive">Deactivated</option>
                                        <option value="block">Blocked</option>
                                    </AySelect>
                                    <AySelect v-model="filters.sort" class="w-full sm:w-auto">
                                        <option value="created_at">Date Created</option>
                                        <option value="name">Name</option>
                                    </AySelect>
                                </div>
                                <AyButton
                                    @click="openCreateModal"
                                    type="dash-blue"
                                    class="w-full sm:w-auto inline-flex items-center justify-center"
                                    >Create Institute</AyButton
                                >
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <DataTable
                                v-if="institutes.length && !isLoading"
                                :columns="instituteColumns"
                                :rows="institutes"
                                :wrapper-class="''"
                                row-class="hover:bg-gray-50"
                            >
                                <template #cell:institute="{ row: institute }">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-12 w-12 text-blue-500 bg-blue-100 rounded-xl flex items-center justify-center"
                                        >
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                                ></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ institute.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">@{{ institute.username }}</div>
                                        </div>
                                    </div>
                                </template>
                                <template #cell:labels="{ row: institute }">
                                    <div class="flex flex-wrap gap-1 max-w-[200px]">
                                        <span
                                            v-for="label in institute.labels"
                                            :key="label.id"
                                            class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full text-white"
                                            :style="{ backgroundColor: label.color }"
                                        >
                                            {{ label.title }}
                                        </span>
                                        <span
                                            v-if="!institute.labels || institute.labels.length === 0"
                                            class="text-xs text-gray-400"
                                            >N/A</span
                                        >
                                    </div>
                                </template>
                                <template #cell:status="{ row: institute }">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                        :class="statusClass(institute.status)"
                                        >{{ institute.status }}</span
                                    >
                                </template>
                                <template #cell:exams="{ row: institute }">
                                    <div>
                                        <span class="font-medium text-gray-800">{{ institute.exams_count }}</span>
                                    </div>
                                </template>
                                <template #cell:plan="{ row: institute }">
                                    {{ institute.plan?.title || 'Free plan' }}
                                </template>
                                <template #header:actions>
                                    <span class="sr-only">Actions</span>
                                </template>
                                <template #cell:actions="{ row: institute }">
                                    <div class="flex items-center justify-end gap-1">
                                        <ResponsiveButton
                                            @click="openManageLabelsModal(institute)"
                                            type="light-blue"
                                            class="text-xs px-2 py-1"
                                            >Manage Labels</ResponsiveButton
                                        >
                                        <ResponsiveButton
                                            type="light-gray"
                                            :to="`/admin/institute/profile/${institute.id}`"
                                            class="text-xs px-2 py-1"
                                            >View</ResponsiveButton
                                        >
                                        <template v-if="institute.status !== 'block'"
                                            ><ResponsiveButton
                                                @click="updateStatus(institute, 'block')"
                                                type="yellow"
                                                class="text-xs px-2 py-1"
                                                >Block</ResponsiveButton
                                            ></template
                                        >
                                        <template v-if="institute.status === 'block'"
                                            ><ResponsiveButton
                                                @click="updateStatus(institute, 'active')"
                                                type="light-green"
                                                class="text-xs px-2 py-1"
                                                >Unblock</ResponsiveButton
                                            ></template
                                        >
                                        <ResponsiveButton
                                            @click="confirmRemoveInstitute(institute)"
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
                                <p class="mt-2 text-sm text-gray-500">Loading institutes...</p>
                            </div>
                            <div v-else class="text-center py-12">
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No institutes found</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Try adjusting your filters or create a new institute.
                                </p>
                                <div class="mt-6">
                                    <AyButton @click="openCreateModal" type="dash-blue" class="inline-flex items-center"
                                        >+ Create Institute</AyButton
                                    >
                                </div>
                            </div>
                        </div>

                        <div v-if="!isLoading && pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200">
                            <!-- Pagination Controls -->
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Create Institute Modal -->
        <Modal v-model="showCreateModal" title="Create New Institute" size="md">
            <p class="text-sm text-gray-500 mb-4">Fill out the form below to add a new institute.</p>
            <form @submit.prevent="handleCreateInstitute" class="space-y-4">
                <AyInput
                    v-model="newInstitute.name"
                    label="Institute Name"
                    required
                    placeholder="e.g., Aylook University"
                />
                <AyInput v-model="newInstitute.username" label="Username" required placeholder="e.g., aylook_uni" />
                <AyInput
                    v-model="newInstitute.email"
                    type="email"
                    label="Email Address"
                    required
                    placeholder="e.g., contact@aylook.com"
                />
                <AyInput
                    v-model="newInstitute.password"
                    type="password"
                    label="Password"
                    required
                    placeholder="••••••••"
                />
            </form>
            <template #footer>
                <AyButton type="light-gray" @click="closeCreateModal">Cancel</AyButton>
                <AyButton type="dash-blue" @click="handleCreateInstitute">Create Institute</AyButton>
            </template>
        </Modal>

        <!-- Manage Labels Modal -->
        <Modal v-model="showManageLabelsModal" title="Manage Institute Labels" size="md">
            <p class="text-xs text-gray-500 mb-4">
                Select labels to assign to this institute. Actions are saved instantly.
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
    name: 'ManageInstitutes',
    components: { Sidebar, Header, AyButton, AyInput, AySelect, ResponsiveButton, DataTable, Modal },
    data() {
        return {
            isSidebarOpen: false,
            institutes: [],
            isLoading: true,
            filters: { search: '', sort: 'created_at', status: '' },
            pagination: { current_page: 1, from: 0, to: 0, total: 0, last_page: 1, links: [] },
            debounceTimer: null,
            showCreateModal: false,
            newInstitute: { name: '', username: '', email: '', password: '' }, // Added email field

            // Data properties for label management
            showManageLabelsModal: false,
            selectedInstituteForLabels: null,
            availableLabels: [],
        }
    },
    computed: {
        teacherName: () => JSON.parse(localStorage.getItem('user') || '{}').name || 'Admin',
        teacherUsername: () => JSON.parse(localStorage.getItem('user') || '{}').username || 'Admin',
        instituteColumns() {
            return [
                { key: 'institute', label: 'Institute', align: 'left', thClass: 'font-medium' },
                { key: 'labels', label: 'Labels', align: 'left', thClass: 'font-medium' },
                { key: 'status', label: 'Status', align: 'left', thClass: 'font-medium' },
                {
                    key: 'exams',
                    label: 'Tutors / Tutees',
                    align: 'left',
                    thClass: 'font-medium',
                    tdClass: 'text-gray-500',
                },
                { key: 'plan', label: 'Plan', align: 'left', thClass: 'font-medium', tdClass: 'text-gray-500 italic' },
                { key: 'actions', label: 'Actions', align: 'right', thClass: 'relative', tdClass: 'font-medium' },
            ]
        },
    },
    watch: {
        filters: {
            handler() {
                clearTimeout(this.debounceTimer)
                this.debounceTimer = setTimeout(() => {
                    this.pagination.current_page = 1
                    this.fetchInstitutes()
                }, 500)
            },
            deep: true,
        },
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        async fetchInstitutes() {
            this.isLoading = true
            try {
                const params = new URLSearchParams({ page: this.pagination.current_page, ...this.filters })
                const response = await apiCall('GET', `/api/admin/institute?${params.toString()}`)
                this.institutes = response.institutes.data
                this.pagination = response.institutes
            } catch (error) {
                this.$toast.error(error.message || 'Failed to load institutes.')
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
            if (page >= 1 && page <= this.pagination.last_page) {
                this.pagination.current_page = page
                this.fetchInstitutes()
            }
        },
        async confirmRemoveInstitute(institute) {
            if (
                await confirmDialog({
                    message: `Are you sure you want to remove "${institute.name}"? This action cannot be undone.`,
                    variant: 'danger',
                })
            ) {
                try {
                    await apiCall('GET', `/api/admin/institute/delete/${institute.id}`)
                    this.$toast.success(`Institute "${institute.name}" removed successfully.`)
                    this.fetchInstitutes()
                } catch (error) {
                    this.$toast.error(error.message || `Failed to remove institute.`)
                }
            }
        },
        async updateStatus(institute, newStatus) {
            try {
                const response = await apiCall('POST', `/api/admin/institute/${institute.id}/status`, {
                    status: newStatus,
                })
                const updatedInstitute = response.institute
                const index = this.institutes.findIndex((i) => i.id === institute.id)
                if (index !== -1) this.institutes[index] = updatedInstitute
                this.$toast.success(`Institute status updated to ${newStatus}.`)
            } catch (error) {
                this.$toast.error(error.message || `Failed to update status.`)
            }
        },
        openCreateModal() {
            // Reset the form when opening the modal
            this.newInstitute = { name: '', username: '', email: '', password: '' }
            this.showCreateModal = true
        },
        closeCreateModal() {
            this.showCreateModal = false
        },
        async handleCreateInstitute() {
            try {
                await apiCall('POST', '/api/admin/institute', this.newInstitute)
                this.$toast.success('Institute created successfully!')
                this.closeCreateModal()
                this.fetchInstitutes()
            } catch (error) {
                this.$toast.error(error.message || 'Failed to create institute.')
            }
        },
        statusClass(status) {
            return (
                {
                    active: 'bg-green-100 text-green-800',
                    block: 'bg-red-100 text-red-800',
                    deactive: 'bg-gray-200 text-gray-800',
                }[status] || 'bg-yellow-100 text-yellow-800'
            )
        },

        // --- Label Management Methods ---
        isLabelAssigned(labelId) {
            if (!this.selectedInstituteForLabels || !this.selectedInstituteForLabels.labels) {
                return false
            }
            return this.selectedInstituteForLabels.labels.some((l) => l.id === labelId)
        },
        openManageLabelsModal(institute) {
            this.selectedInstituteForLabels = institute
            this.showManageLabelsModal = true
        },
        closeManageLabelsModal() {
            this.showManageLabelsModal = false
            this.selectedInstituteForLabels = null
        },
        async toggleLabel(label, event) {
            const isChecked = event.target.checked
            const action = isChecked ? this.assignLabel : this.removeLabel
            await action(label, event)
        },
        async assignLabel(label, event) {
            try {
                const payload = { institute_id: this.selectedInstituteForLabels.id }
                await apiCall('POST', `/api/admin/label/${label.id}/assign-institute`, payload)

                const instituteIndex = this.institutes.findIndex((i) => i.id === this.selectedInstituteForLabels.id)
                if (instituteIndex !== -1) {
                    if (!this.institutes[instituteIndex].labels) this.institutes[instituteIndex].labels = []
                    this.institutes[instituteIndex].labels.push(label)
                }
                this.$toast.success(`"${label.title}" label assigned.`)
            } catch (error) {
                this.$toast.error(error.message || `Failed to assign "${label.title}".`)
                if (event) event.target.checked = false // Revert checkbox on failure
            }
        },
        async removeLabel(label, event) {
            try {
                const payload = { institute_id: this.selectedInstituteForLabels.id }
                await apiCall('POST', `/api/admin/label/${label.id}/remove-institute`, payload)

                const instituteIndex = this.institutes.findIndex((i) => i.id === this.selectedInstituteForLabels.id)
                if (instituteIndex !== -1 && this.institutes[instituteIndex].labels) {
                    this.institutes[instituteIndex].labels = this.institutes[instituteIndex].labels.filter(
                        (l) => l.id !== label.id
                    )
                }
                this.$toast.success(`"${label.title}" label removed.`)
            } catch (error) {
                this.$toast.error(error.message || `Failed to remove "${label.title}".`)
                if (event) event.target.checked = true // Revert checkbox on failure
            }
        },
    },
    mounted() {
        this.fetchInstitutes()
        this.fetchLabels() // Fetch all available labels when the component mounts
    },
}
</script>
