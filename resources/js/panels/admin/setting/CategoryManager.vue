<template>
    <div class="bg-white p-6 sm:p-8 rounded-xl shadow-sm lg:col-span-2">
        <h3 class="text-xl font-bold text-gray-800">Manage Categories</h3>
        <p class="text-gray-500 mt-1 mb-6 text-sm">Create, edit, or delete content categories.</p>
        <div class="space-y-3 mb-8">
            <div
                v-for="category in categories"
                :key="category.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200"
            >
                <div class="flex items-center gap-4">
                    <img
                        v-if="category.logo"
                        :src="`${baseUrl}/${category.logo}`"
                        class="w-10 h-10 rounded-lg object-cover bg-gray-200 border-2"
                        :style="{ borderColor: category.color || 'transparent' }"
                    />
                    <div
                        v-else
                        class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center text-gray-400 border-2"
                        :style="{ borderColor: category.color || 'transparent' }"
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
                                d="M4 6h16M4 10h16M4 14h16M4 18h16"
                            />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">{{ category.title }}</h4>
                        <p class="text-sm text-gray-500">{{ category.description }}</p>
                    </div>
                </div>
                <div class="flex gap-1 flex-shrink-0 ml-4">
                    <ResponsiveButton @click="editCategory(category)" type="light-blue" class="p-1 text-sm px-2"
                        >Edit</ResponsiveButton
                    >
                    <ResponsiveButton @click="deleteCategory(category.id)" type="light-red" class="p-1 text-sm px-2"
                        >Delete</ResponsiveButton
                    >
                </div>
            </div>
        </div>
        <form @submit.prevent="submitCategory" class="p-4 border-t border-gray-200">
            <h4 class="font-semibold text-gray-800 mb-4">
                {{ editingCategory ? 'Edit Category' : 'Add New Category' }}
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <AyInput
                    v-model="newCategory.title"
                    label="Title"
                    placeholder="e.g., Science"
                    :error="errors.title?.[0]"
                />
                <AyInput
                    v-model="newCategory.description"
                    label="Description"
                    placeholder="Topics related to science"
                    :error="errors.description?.[0]"
                />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Icon Image</label>
                    <div
                        @click="$refs.categoryImageInput.click()"
                        class="mt-1 flex justify-center h-32 px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md cursor-pointer hover:border-indigo-500"
                    >
                        <div class="space-y-1 text-center">
                            <img
                                v-if="newCategory.imageUrlPreview"
                                :src="newCategory.imageUrlPreview"
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
                        ref="categoryImageInput"
                        type="file"
                        @change="handleCategoryImageUpload"
                        class="hidden"
                        accept="image/*"
                    />
                    <p v-if="errors.logo" class="text-red-600 text-sm mt-1">{{ errors.logo[0] }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Color</label>
                    <input
                        type="color"
                        v-model="newCategory.color"
                        class="w-full h-32 rounded-md border-2 border-gray-300 p-1 cursor-pointer"
                    />
                </div>
            </div>
            <div class="flex gap-2">
                <AyButton type="light-blue">{{ editingCategory ? 'Update' : 'Add' }} Category</AyButton>
                <AyButton v-if="editingCategory" type="light-gray" @click="cancelEditCategory">Cancel</AyButton>
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
    name: 'CategoryManager',
    components: { ResponsiveButton, AyInput, AyButton },
    setup() {
        const $toast = useToast()
        return { $toast }
    },
    data() {
        return {
            baseUrl: window.location.origin,
            categories: [],
            newCategory: { title: '', description: '', imageFile: null, imageUrlPreview: null, color: '#4285F4' },
            editingCategory: null,
            errors: {},
        }
    },
    mounted() {
        this.fetchCategories()
    },
    methods: {
        async fetchCategories() {
            try {
                const response = await apiCall('GET', '/api/admin/category')
                this.categories = response.categories.data
            } catch (error) {
                console.error('Error fetching categories:', error)
            }
        },
        handleCategoryImageUpload(event) {
            const file = event.target.files[0]
            if (!file) return
            this.newCategory.imageFile = file
            this.newCategory.imageUrlPreview = URL.createObjectURL(file)
        },
        async submitCategory() {
            this.errors = {}
            const formData = new FormData()
            formData.append('title', this.newCategory.title || '')
            formData.append('description', this.newCategory.description || '')
            formData.append('color', this.newCategory.color || '#4285F4')
            if (this.newCategory.imageFile) {
                formData.append('logo', this.newCategory.imageFile)
            }
            try {
                const url = this.editingCategory
                    ? `/api/admin/category/${this.editingCategory.id}`
                    : '/api/admin/category'
                const response = await apiCall('POST', url, formData)
                this.$toast.success(response.message)
                this.cancelEditCategory()
                this.fetchCategories()
            } catch (error) {
                if (error.response?.errors) this.errors = error.response.errors
            }
        },
        editCategory(category) {
            this.editingCategory = category
            this.newCategory.title = category.title
            this.newCategory.description = category.description
            this.newCategory.color = category.color || '#4285F4'
            this.newCategory.imageUrlPreview = category.logo ? `${this.baseUrl}/${category.logo}` : null
            this.newCategory.imageFile = null
            this.errors = {}
        },
        cancelEditCategory() {
            this.editingCategory = null
            this.newCategory = { title: '', description: '', imageFile: null, imageUrlPreview: null, color: '#4285F4' }
            this.errors = {}
        },
        async deleteCategory(id) {
            if (await confirmDialog({ message: 'Are you sure?', variant: 'danger' })) {
                try {
                    const response = await apiCall('GET', `/api/admin/category/delete/${id}`)
                    this.$toast.success(response.message)
                    this.fetchCategories()
                } catch (error) {
                    console.error(error)
                }
            }
        },
    },
}
</script>
