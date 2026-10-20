<template>
    <div class="bg-gray-50 min-h-screen">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" />
        </div>
        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />

            <main class="flex-1 w-full p-4 md:p-6 mt-16 xl:ms-64 transition-all duration-300">
                <div class="max-w-6xl mx-auto">
                    <!-- Header Section -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                        <div>
                            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Banners</h1>
                            <p class="text-gray-500">Targeted marketing for specific cities and user roles.</p>
                        </div>
                        <ResponsiveButton @click="openCreateModal" type="light-blue" class="px-4 py-2">
                            <span class="flex items-center gap-2 px-2 py-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>
                                Create Banner
                            </span>
                        </ResponsiveButton>
                    </div>

                    <!-- Filter Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 mb-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <AyInput v-model="filters.search" placeholder="Search title...">
                                <template #icon>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </template>
                            </AyInput>
                            <AySelect v-model="filters.user_type">
                                <option value="">All User Types</option>
                                <option value="student">Student</option>
                                <option value="teacher">Teacher</option>
                                <option value="institute">Institute</option>
                            </AySelect>
                            <AySelect v-model="filters.city_id">
                                <option value="">All Locations</option>
                                <option value="all">All Cities (Global Only)</option>
                                <option v-for="city in cities" :key="city.id" :value="city.id">
                                    {{ city.name_en }}
                                </option>
                            </AySelect>
                            <ResponsiveButton type="light-blue" @click="fetchBanners" class="w-full py-2.5">
                                Filter Results
                            </ResponsiveButton>
                        </div>
                    </div>

                    <!-- Table Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <DataTable
                                v-if="!loading && banners.length > 0"
                                :columns="bannerColumns"
                                :rows="banners"
                                :wrapper-class="''"
                                row-class="hover:bg-blue-50/30 transition-colors"
                            >
                                <template #cell:visual="{ row: banner }">
                                    <img
                                        v-if="banner.image_url"
                                        :src="banner.image_url"
                                        class="h-12 w-20 object-cover rounded-lg shadow-sm"
                                    />
                                </template>
                                <template #cell:details="{ row: banner }">
                                    <div class="font-semibold text-gray-800">{{ banner.title }}</div>
                                </template>
                                <template #cell:location="{ row: banner }">
                                    <span v-if="banner.city?.name_en" class="font-medium text-gray-800">
                                        {{ banner.city.name_en }}
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200"
                                    >
                                        All Cities
                                    </span>
                                </template>
                                <template #cell:audience="{ row: banner }">
                                    <span
                                        :class="getUserTypeClass(banner.user_type)"
                                        class="px-3 py-1 rounded-full text-xs font-bold capitalize"
                                    >
                                        {{ banner.user_type }}
                                    </span>
                                </template>
                                <template #cell:status="{ row: banner }">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            :checked="banner.is_active"
                                            @change="toggleBannerStatus(banner)"
                                            class="sr-only peer"
                                        />
                                        <div
                                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"
                                        ></div>
                                    </label>
                                </template>
                                <template #cell:actions="{ row: banner }">
                                    <div class="flex items-center gap-3">
                                        <button
                                            @click="editBanner(banner)"
                                            class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            @click="deleteBanner(banner.id)"
                                            class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </DataTable>

                            <!-- Empty/Loading State -->
                            <div v-if="loading" class="p-12">
                                <div class="flex flex-col items-center gap-3">
                                    <div
                                        class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"
                                    ></div>
                                    <p class="text-gray-500 animate-pulse">Loading banners...</p>
                                </div>
                            </div>
                            <div v-else-if="banners.length === 0" class="text-center py-20">
                                <p class="text-gray-400 text-lg">No banners match your criteria</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Create/Edit Modal -->
        <Modal
            :model-value="showModal"
            @update:model-value="closeModal"
            :title="editingBanner ? 'Edit Banner' : 'Create New Banner'"
            size="lg"
        >
            <div class="grid grid-cols-1 gap-6">
                <!-- Title -->
                <AyInput v-model="form.title" label="Banner Title" placeholder="Enter a descriptive title" />

                <!-- Image Section -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide"
                        >Visual Content</label
                    >
                    <div
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleImageDrop"
                        :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-gray-50'"
                        class="relative border-2 border-dashed rounded-2xl p-8 text-center transition-all group hover:border-blue-400"
                    >
                        <input
                            type="file"
                            ref="imageInput"
                            @change="handleImageUpload"
                            accept="image/*"
                            class="hidden"
                        />

                        <div
                            v-if="!imagePreview && (!editingBanner || !editingBanner.image_url)"
                            @click="$refs.imageInput.click()"
                            class="cursor-pointer"
                        >
                            <div
                                class="bg-white w-12 h-12 rounded-full shadow-sm flex items-center justify-center mx-auto mb-3 text-blue-500 group-hover:scale-110 transition-transform"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600">
                                Drop image here or <span class="text-blue-600 underline">browse</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Supports: JPG, PNG, WEBP (Max 10MB)</p>
                        </div>

                        <div v-else class="relative inline-block w-full">
                            <img
                                :src="imagePreview || editingBanner.image_url"
                                class="w-full h-48 object-cover rounded-xl shadow-md border-4 border-white"
                            />
                            <button
                                type="button"
                                @click="removeImage"
                                class="absolute -top-3 -right-3 bg-red-500 text-white p-1.5 rounded-full shadow-lg hover:bg-red-600 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Grid Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <AySelect v-model="form.city_id" label="Target City">
                        <option value="">All Cities (Display in all cities)</option>
                        <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name_en }}</option>
                    </AySelect>
                    <AySelect v-model="form.user_type" label="User Role">
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="institute">Institute</option>
                    </AySelect>
                </div>

                <!-- Description -->
                <AyTextarea
                    v-model="form.description"
                    label="Description"
                    :rows="3"
                    placeholder="Enter banner details..."
                />
            </div>

            <template #footer>
                <ResponsiveButton
                    type="light-green"
                    @click="submitForm"
                    :disabled="isSubmitting"
                    class="flex-1 disabled:bg-blue-300 text-white py-3 font-bold"
                >
                    {{ isSubmitting ? 'Processing...' : editingBanner ? 'Update Banner' : 'Create Banner' }}
                </ResponsiveButton>
                <ResponsiveButton type="light-gray" @click="closeModal" class="px-6 py-3 font-bold">
                    Cancel
                </ResponsiveButton>
            </template>
        </Modal>
    </div>
</template>

<script>
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import Header from '@/components/layout/AdminHeader.vue'
import Sidebar from '@/components/layout/AdminSidebar.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Modal from '@/components/shared/Modal.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import AyTextarea from '@/components/atoms/form/AyTextarea.vue'
import { apiCall } from '@/services/apiService.js'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'BannerManagement',
    components: { Header, Sidebar, ResponsiveButton, DataTable, Modal, AyInput, AySelect, AyTextarea },
    data() {
        return {
            isSidebarOpen: false,
            banners: [],
            cities: [],
            loading: true,
            isSubmitting: false,
            showCreateModal: false,
            editingBanner: null,
            isDragging: false,
            imagePreview: null,
            filters: { search: '', user_type: '', city_id: '' },
            form: { title: '', description: '', city_id: '', user_type: '', image_file: null },
        }
    },
    computed: {
        bannerColumns() {
            return [
                { key: 'visual', label: 'Visual' },
                { key: 'details', label: 'Details' },
                { key: 'location', label: 'Location' },
                { key: 'audience', label: 'Audience' },
                { key: 'status', label: 'Status' },
                { key: 'actions', label: 'Actions' },
            ]
        },
        showModal() {
            return this.showCreateModal || !!this.editingBanner
        },
    },
    watch: {
        showModal(val) {
            // Scroll Lock logic
            if (val) document.body.style.overflow = 'hidden'
            else document.body.style.overflow = ''
        },
    },
    mounted() {
        this.fetchBanners()
        this.fetchCities()
    },
    methods: {
        async fetchBanners() {
            this.loading = true
            try {
                const params = new URLSearchParams(this.filters)
                const response = await apiCall('get', `/api/admin/banners?${params.toString()}`)
                this.banners = response.banners.data || response.banners
            } catch (error) {
                console.error('Fetch error:', error)
            } finally {
                this.loading = false
            }
        },
        async fetchCities() {
            const response = await apiCall('get', '/api/admin/cities/all')
            this.cities = response.cities
        },
        openCreateModal() {
            this.resetForm()
            this.showCreateModal = true
        },
        editBanner(banner) {
            this.editingBanner = banner
            this.form = {
                title: banner.title,
                description: banner.description,
                city_id: banner.city_id || '',
                user_type: banner.user_type,
                image_file: null,
            }
            this.imagePreview = null
        },
        async submitForm() {
            this.isSubmitting = true
            try {
                const fd = new FormData()
                fd.append('title', this.form.title)
                fd.append('description', this.form.description)
                fd.append('city_id', this.form.city_id || '')
                fd.append('user_type', this.form.user_type)
                if (this.form.image_file) fd.append('image', this.form.image_file)

                if (this.editingBanner) {
                    // For PUT with files in Laravel, we use POST + _method override
                    fd.append('_method', 'PUT')
                    await apiCall('post', `/api/admin/banners/${this.editingBanner.id}`, fd)
                } else {
                    await apiCall('post', '/api/admin/banners', fd)
                }
                this.closeModal()
                this.fetchBanners()
            } catch (error) {
                const msg = error?.response?.data?.message || error?.response?.message || error?.message || 'An error occurred while saving.'
                await alertDialog({ message: msg })
            } finally {
                this.isSubmitting = false
            }
        },
        async deleteBanner(id) {
            if (await confirmDialog({ message: 'Delete this banner permanently?', variant: 'danger' })) {
                try {
                    await apiCall('delete', `/api/admin/banners/${id}`)
                    this.fetchBanners()
                } catch (error) {
                    await alertDialog({ message: error?.response?.message || 'Could not delete the banner.' })
                }
            }
        },
        async toggleBannerStatus(banner) {
            // Optimistic flip, reverted if the request fails.
            const previous = banner.is_active
            banner.is_active = !previous
            try {
                await apiCall('patch', `/api/admin/banners/${banner.id}/toggle-active`)
            } catch (error) {
                banner.is_active = previous
                await alertDialog({ message: error?.response?.message || 'Could not update banner status.' })
            }
        },
        handleImageUpload(e) {
            const file = e.target.files[0]
            this.processFile(file)
        },
        handleImageDrop(e) {
            this.isDragging = false
            this.processFile(e.dataTransfer.files[0])
        },
        processFile(file) {
            if (file && file.type.startsWith('image/')) {
                this.form.image_file = file
                const reader = new FileReader()
                reader.onload = (e) => (this.imagePreview = e.target.result)
                reader.readAsDataURL(file)
            }
        },
        removeImage() {
            this.form.image_file = null
            this.imagePreview = null
            if (this.editingBanner) this.editingBanner.image_url = null
        },
        closeModal() {
            this.showCreateModal = false
            this.editingBanner = null
            this.resetForm()
        },
        resetForm() {
            this.form = { title: '', description: '', city_id: '', user_type: '', image_file: null }
            this.imagePreview = null
        },
        getUserTypeClass(type) {
            const map = {
                student: 'bg-blue-100 text-blue-700',
                teacher: 'bg-purple-100 text-purple-700',
                institute: 'bg-orange-100 text-orange-700',
            }
            return map[type] || 'bg-gray-100 text-gray-700'
        },
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
    },
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.scale-enter-active,
.scale-leave-active {
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.scale-enter-from,
.scale-leave-to {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
}

/* Custom scrollbar for modal */
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}
</style>
