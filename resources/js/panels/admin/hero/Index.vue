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
                            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Hero Slider</h1>
                            <p class="text-gray-500">Manage background images, titles, descriptions, and buttons for the landing page hero slider.</p>
                        </div>
                        <ResponsiveButton @click="openCreateModal" type="light-blue" class="px-4 py-2">
                            <span class="flex items-center gap-2 px-2 py-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create Slide
                            </span>
                        </ResponsiveButton>
                    </div>

                    <!-- Table Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <DataTable
                                v-if="!loading && slides.length > 0"
                                :columns="slideColumns"
                                :rows="slides"
                                :wrapper-class="''"
                                row-class="hover:bg-blue-50/30 transition-colors"
                            >
                                <template #cell:visual="{ row: slide }">
                                    <div class="w-24 h-14 rounded-lg overflow-hidden bg-gray-900 border border-gray-200 relative">
                                        <img
                                            v-if="slide.image"
                                            :src="slide.image"
                                            class="w-full h-full object-cover"
                                            alt="Slide background"
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center text-xs text-gray-400">
                                            Default
                                        </div>
                                    </div>
                                </template>

                                <template #cell:details="{ row: slide }">
                                    <div class="max-w-md py-1">
                                        <div class="font-bold text-gray-900 text-sm">
                                            {{ slide.title || '(No Title)' }}
                                        </div>
                                        <div class="text-xs text-gray-500 line-clamp-2 mt-0.5">
                                            {{ slide.description || '(No Description)' }}
                                        </div>
                                    </div>
                                </template>

                                <template #cell:button="{ row: slide }">
                                    <div v-if="slide.button_text" class="text-xs">
                                        <span class="inline-block px-2.5 py-1 bg-yellow-100 text-yellow-800 font-bold rounded-md border border-yellow-300">
                                            {{ slide.button_text }}
                                        </span>
                                        <div class="text-gray-400 text-[11px] truncate max-w-[150px] mt-1 font-mono">
                                            {{ slide.button_link || '#' }}
                                        </div>
                                    </div>
                                    <span v-else class="text-gray-400 text-xs italic">No button</span>
                                </template>

                                <template #cell:order="{ row: slide }">
                                    <span class="inline-flex items-center justify-center w-7 h-7 text-xs font-bold text-gray-700 bg-gray-100 rounded-full">
                                        {{ slide.display_order }}
                                    </span>
                                </template>

                                <template #cell:status="{ row: slide }">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            :checked="slide.is_active"
                                            @change="toggleSlideStatus(slide)"
                                            class="sr-only peer"
                                        />
                                        <div
                                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"
                                        ></div>
                                    </label>
                                </template>

                                <template #cell:actions="{ row: slide }">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="editSlide(slide)"
                                            class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors cursor-pointer"
                                            title="Edit slide"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="deleteSlide(slide.id)"
                                            class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors cursor-pointer"
                                            title="Delete slide"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </DataTable>

                            <!-- Loading State -->
                            <div v-if="loading" class="p-12 text-center text-gray-500">
                                <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                                Loading slides...
                            </div>

                            <!-- Empty State -->
                            <div v-else-if="slides.length === 0" class="p-12 text-center">
                                <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800 mb-1">No Hero Slides Configured</h3>
                                <p class="text-gray-500 text-sm max-w-sm mx-auto mb-6">
                                    The home page is currently displaying the default fallback hero section. Add slides here to customize the hero slider.
                                </p>
                                <ResponsiveButton @click="openCreateModal" type="light-blue" class="px-5 py-2.5">
                                    Create First Slide
                                </ResponsiveButton>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Create / Edit Slide Modal -->
        <Modal :model-value="showModal" @close="closeModal" size="lg" :title="editingSlide ? 'Edit Hero Slide' : 'Create Hero Slide'">
            <div class="space-y-4">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Title</label>
                    <AyInput v-model="form.title" placeholder="e.g. Discover, Play and Learn." />
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                    <AyTextarea v-model="form.description" :rows="3" placeholder="e.g. An interactive gamified learning platform." />
                </div>

                <!-- Background Image Upload -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Background Image</label>
                    <div
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop"
                        :class="[
                            'border-2 border-dashed rounded-xl p-4 text-center cursor-pointer transition-all duration-200',
                            isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400 bg-gray-50'
                        ]"
                        @click="$refs.fileInput.click()"
                    >
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleFileSelect"
                        />

                        <!-- Image Preview -->
                        <div v-if="imagePreview || form.image" class="relative group max-w-md mx-auto mb-2">
                            <img
                                :src="imagePreview || form.image"
                                class="w-full h-40 object-cover rounded-lg shadow-sm"
                                alt="Slide preview"
                            />
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center text-white text-xs font-bold">
                                Click or drop to replace image
                            </div>
                        </div>

                        <div v-else class="py-4">
                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-sm text-gray-600 font-medium">Drag & drop image here, or <span class="text-blue-600 underline">browse</span></p>
                            <p class="text-xs text-gray-400 mt-1">Recommended high-resolution landscape images (1920x1080)</p>
                        </div>
                    </div>
                </div>

                <!-- Custom Image URL (optional) -->
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Or enter image URL path (e.g. /imgHolders/LPLP.jpg)</label>
                    <AyInput v-model="form.image" placeholder="Image URL / path" />
                </div>

                <!-- Button Details -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Button Text</label>
                        <AyInput v-model="form.button_text" placeholder="e.g. Get Started" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Button Link (URL)</label>
                        <AyInput v-model="form.button_link" placeholder="e.g. /signup or https://..." />
                    </div>
                </div>

                <!-- Display Order & Active State -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Display Order</label>
                        <AyInput v-model.number="form.display_order" type="number" placeholder="1" />
                    </div>
                    <div class="flex items-center sm:pt-6">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ms-3 text-sm font-semibold text-gray-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <template #footer>
                <ResponsiveButton
                    type="light-green"
                    @click="submitForm"
                    :disabled="isSubmitting"
                    class="flex-1 text-white py-3 font-bold"
                >
                    {{ isSubmitting ? 'Saving...' : editingSlide ? 'Update Slide' : 'Create Slide' }}
                </ResponsiveButton>
                <ResponsiveButton type="light-gray" @click="closeModal" class="px-6 py-3 font-bold">
                    Cancel
                </ResponsiveButton>
            </template>
        </Modal>
    </div>
</template>

<script>
import Header from '@/components/layout/AdminHeader.vue'
import Sidebar from '@/components/layout/AdminSidebar.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Modal from '@/components/shared/Modal.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AyTextarea from '@/components/atoms/form/AyTextarea.vue'
import { apiCall } from '@/services/apiService.js'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'AdminHeroSlideIndex',
    components: {
        Header,
        Sidebar,
        DataTable,
        Modal,
        ResponsiveButton,
        AyInput,
        AyTextarea,
    },
    data() {
        return {
            isSidebarOpen: false,
            slides: [],
            loading: true,
            isSubmitting: false,
            showModal: false,
            editingSlide: null,
            isDragging: false,
            imagePreview: null,
            form: {
                title: '',
                description: '',
                button_text: 'Get Started',
                button_link: '/signup',
                image: '',
                image_file: null,
                display_order: 1,
                is_active: true,
            },
        }
    },
    computed: {
        slideColumns() {
            return [
                { key: 'visual', label: 'Preview', sortable: false },
                { key: 'details', label: 'Title & Description', sortable: false },
                { key: 'button', label: 'Action Button', sortable: false },
                { key: 'order', label: 'Order', sortable: true },
                { key: 'status', label: 'Active', sortable: false },
                { key: 'actions', label: 'Actions', sortable: false },
            ]
        },
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        async fetchSlides() {
            this.loading = true
            try {
                const response = await apiCall('GET', '/api/admin/hero-slides')
                this.slides = Array.isArray(response?.slides) ? response.slides : []
            } catch (error) {
                console.error('Failed to fetch hero slides:', error)
                this.slides = []
            } finally {
                this.loading = false
            }
        },
        openCreateModal() {
            this.editingSlide = null
            this.imagePreview = null
            this.form = {
                title: '',
                description: '',
                button_text: 'Get Started',
                button_link: '/signup',
                image: '',
                image_file: null,
                display_order: this.slides.length + 1,
                is_active: true,
            }
            this.showModal = true
        },
        editSlide(slide) {
            this.editingSlide = slide
            this.imagePreview = slide.image || null
            this.form = {
                title: slide.title || '',
                description: slide.description || '',
                button_text: slide.button_text || '',
                button_link: slide.button_link || '',
                image: slide.image || '',
                image_file: null,
                display_order: slide.display_order ?? 1,
                is_active: Boolean(slide.is_active),
            }
            this.showModal = true
        },
        closeModal() {
            this.showModal = false
            this.editingSlide = null
            this.imagePreview = null
        },
        handleFileSelect(e) {
            const file = e.target.files[0]
            if (file) this.processFile(file)
        },
        handleDrop(e) {
            this.isDragging = false
            const file = e.dataTransfer.files[0]
            if (file) this.processFile(file)
        },
        processFile(file) {
            if (!file.type.startsWith('image/')) {
                alertDialog({ message: 'Please select a valid image file.' })
                return
            }
            this.form.image_file = file
            const reader = new FileReader()
            reader.onload = (e) => {
                this.imagePreview = e.target.result
            }
            reader.readAsDataURL(file)
        },
        async submitForm() {
            this.isSubmitting = true
            try {
                const fd = new FormData()
                fd.append('title', this.form.title || '')
                fd.append('description', this.form.description || '')
                fd.append('button_text', this.form.button_text || '')
                fd.append('button_link', this.form.button_link || '')
                fd.append('display_order', this.form.display_order ?? 1)
                fd.append('is_active', this.form.is_active ? 1 : 0)

                if (this.form.image_file) {
                    fd.append('image_file', this.form.image_file)
                }
                if (this.form.image) {
                    fd.append('image', this.form.image)
                }

                if (this.editingSlide) {
                    await apiCall('POST', `/api/admin/hero-slides/${this.editingSlide.id}`, fd)
                } else {
                    await apiCall('POST', '/api/admin/hero-slides', fd)
                }

                this.closeModal()
                await this.fetchSlides()
            } catch (error) {
                const msg = error?.response?.data?.message || error?.message || 'An error occurred while saving the slide.'
                await alertDialog({ message: msg })
            } finally {
                this.isSubmitting = false
            }
        },
        async toggleSlideStatus(slide) {
            try {
                const response = await apiCall('PATCH', `/api/admin/hero-slides/${slide.id}/toggle-active`)
                slide.is_active = response?.slide?.is_active ?? !slide.is_active
            } catch (error) {
                console.error('Failed to toggle status:', error)
                await alertDialog({ message: 'Failed to update slide status.' })
            }
        },
        async deleteSlide(id) {
            const confirmed = await confirmDialog({
                title: 'Delete Hero Slide',
                message: 'Are you sure you want to delete this slide? This action cannot be undone.',
                confirmText: 'Delete',
                cancelText: 'Cancel',
            })
            if (!confirmed) return

            try {
                await apiCall('DELETE', `/api/admin/hero-slides/${id}`)
                await this.fetchSlides()
            } catch (error) {
                console.error('Failed to delete slide:', error)
                await alertDialog({ message: 'Failed to delete slide.' })
            }
        },
    },
    mounted() {
        this.fetchSlides()
    },
}
</script>
