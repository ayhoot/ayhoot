<template>
    <div class="bg-white p-6 sm:p-8 rounded-xl shadow-sm lg:col-span-2">
        <h3 class="text-xl font-bold text-gray-800">Manage Labels</h3>
        <p class="text-gray-500 mt-1 mb-6 text-sm">Create, edit, or delete exam labels.</p>
        <div class="space-y-3 mb-8">
            <div
                v-for="label in labels"
                :key="label.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200"
            >
                <div class="flex items-center gap-4">
                    <img
                        v-if="label.logo"
                        :src="`${baseUrl}/${label.logo}`"
                        class="w-10 h-10 rounded-lg object-cover bg-gray-200 border-2"
                        :style="{ borderColor: label.color || 'transparent' }"
                    />
                    <div
                        v-else
                        class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center text-gray-400 border-2"
                        :style="{ borderColor: label.color || 'transparent' }"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"
                            />
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">{{ label.title }}</h4>
                        <p class="text-sm text-gray-500">{{ label.description }}</p>
                    </div>
                </div>
                <div class="flex gap-1 flex-shrink-0 ml-4">
                    <ResponsiveButton @click="editLabel(label)" type="light-blue" class="p-1 text-sm px-2"
                        >Edit</ResponsiveButton
                    >
                    <ResponsiveButton @click="deleteLabel(label.id)" type="light-red" class="p-1 text-sm px-2"
                        >Delete</ResponsiveButton
                    >
                </div>
            </div>
        </div>
        <form @submit.prevent="submitLabel" class="p-4 border-t border-gray-200">
            <h4 class="font-semibold text-gray-800 mb-4">{{ editingLabel ? 'Edit Label' : 'Add New Label' }}</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <AyInput v-model="newLabel.title" label="Title" placeholder="e.g., Featured" />
                <AyInput
                    v-model="newLabel.description"
                    label="Description"
                    placeholder="Featured exams for the homepage"
                />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Label Image</label>
                    <div
                        @click="$refs.labelImageInput.click()"
                        class="mt-1 flex justify-center h-32 px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md cursor-pointer hover:border-indigo-500"
                    >
                        <div class="space-y-1 text-center">
                            <img
                                v-if="newLabel.imageUrlPreview"
                                :src="newLabel.imageUrlPreview"
                                class="mx-auto h-16 w-auto object-contain rounded-md mb-2"
                                alt="Image Preview"
                            />
                            <svg
                                v-else
                                class="mx-auto h-12 w-12 text-gray-400"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 48 48"
                                aria-hidden="true"
                            >
                                <path
                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <p class="pl-1 text-xs">Click to upload</p>
                            </div>
                        </div>
                    </div>
                    <input
                        ref="labelImageInput"
                        type="file"
                        @change="handleLabelImageUpload"
                        class="hidden"
                        accept="image/*"
                    />
                    <p v-if="errors.logo" class="text-red-600 text-sm mt-1">{{ errors.logo[0] }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Label Color</label>
                    <input
                        type="color"
                        v-model="newLabel.color"
                        class="w-full h-32 rounded-md border-2 border-gray-300 p-1 cursor-pointer"
                    />
                </div>
            </div>
            <div class="flex gap-2">
                <AyButton type="dark-green">{{ editingLabel ? 'Update' : 'Add' }} Label</AyButton>
                <AyButton v-if="editingLabel" type="light-gray" @click="cancelEditLabel">Cancel</AyButton>
            </div>
        </form>
    </div>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useToast } from 'vue-toast-notification'
import { useConfirm } from '@/composables/useConfirm'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'

const { confirm: confirmDialog } = useConfirm()

export default {
    name: 'LabelManager',
    components: { ResponsiveButton, AyInput, AyButton },
    setup() {
        const $toast = useToast()
        return { $toast }
    },
    data() {
        return {
            baseUrl: window.location.origin,
            labels: [],
            newLabel: { title: '', description: '', imageFile: null, imageUrlPreview: null, color: '#34A853' },
            editingLabel: null,
            errors: {},
        }
    },
    mounted() {
        this.fetchLabels()
    },
    methods: {
        async fetchLabels() {
            try {
                this.labels = await apiCall('GET', '/api/admin/label')
            } catch (error) {
                console.error('Error fetching labels:', error)
            }
        },
        handleLabelImageUpload(event) {
            const file = event.target.files[0]
            if (!file) return
            this.newLabel.imageFile = file
            this.newLabel.imageUrlPreview = URL.createObjectURL(file)
        },
        async submitLabel() {
            this.errors = {}
            const formData = new FormData()
            formData.append('title', this.newLabel.title || '')
            formData.append('description', this.newLabel.description || '')
            formData.append('color', this.newLabel.color || '#34A853')
            if (this.newLabel.imageFile) {
                formData.append('logo', this.newLabel.imageFile)
            }
            try {
                const url = this.editingLabel ? `/api/admin/label/${this.editingLabel.id}` : '/api/admin/label'
                const response = await apiCall('POST', url, formData)
                this.$toast.success(response.message)
                this.cancelEditLabel()
                this.fetchLabels()
            } catch (error) {
                if (error.response?.errors) this.errors = error.response.errors
            }
        },
        editLabel(label) {
            this.editingLabel = label
            this.newLabel.title = label.title
            this.newLabel.description = label.description
            this.newLabel.color = label.color || '#34A853'
            this.newLabel.imageUrlPreview = label.logo ? `${this.baseUrl}/${label.logo}` : null
            this.newLabel.imageFile = null
            this.errors = {}
        },
        cancelEditLabel() {
            this.editingLabel = null
            this.newLabel = { title: '', description: '', imageFile: null, imageUrlPreview: null, color: '#34A853' }
            this.errors = {}
        },
        async deleteLabel(id) {
            if (await confirmDialog({ message: 'Are you sure?', variant: 'danger' })) {
                try {
                    const response = await apiCall('GET', `/api/admin/label/delete/${id}`)
                    this.$toast.success(response.message)
                    this.fetchLabels()
                } catch (error) {
                    this.$toast.error(error.response?.data?.message || 'Failed to delete label.')
                }
            }
        },
    },
}
</script>
