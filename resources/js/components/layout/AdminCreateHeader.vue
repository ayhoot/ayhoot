<template>
    <div class="fixed top-0 inset-x-0">
        <div class="flex justify-between items-center py-3 px-6 border-b-1 border-gray-300 z-50 bg-white">
            <div class="w-full flex justify-between">
                <div class="flex items-center gap-4 sm:gap-6">
                    <button @click="$emit('toggle-sidebar')" class="block sm:hidden text-gray-600 focus:outline-none">
                        <span class="sr-only">Open questions list</span>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 7H19" stroke="#33363F" stroke-width="2" stroke-linecap="round" />
                            <path d="M5 12H19" stroke="#33363F" stroke-width="2" stroke-linecap="round" />
                            <path d="M5 17H19" stroke="#33363F" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                    <RouterLink to="/teacher/dashboard">
                        <DashAyhootLogo />
                    </RouterLink>
                </div>

                <div
                    @click="openModal"
                    class="items-center gap-14 border-2 border-gray-300 rounded-lg p-1 group cursor-pointer hidden sm:flex"
                >
                    <div v-if="exam?.title" class="text-sm font-semibold text-gray-500 px-2">
                        {{ exam?.title }}
                    </div>
                    <div v-else class="text-sm font-semibold text-gray-500 px-2">Enter Ayhoot title...</div>
                    <Button
                        type="light-gray"
                        class="px-4 py-1 bg-gray-600 group-hover:bg-gray-700 cursor-pointer text-white font-semibold text-sm rounded-sm"
                        @click="openModal"
                    >
                        Settings
                    </Button>
                </div>

                <div class="flex items-center gap-2 sm:gap-2">
                    <ResponsiveButton class="block sm:hidden p-2" type="dark-blue" @click="openModal">
                        <DashSettings />
                    </ResponsiveButton>
                    <ResponsiveButton class="block sm:hidden p-2" type="dark-gray">
                        <DashView />
                    </ResponsiveButton>
                    <ResponsiveButton
                        class="hidden sm:flex items-center gap-1 p-2"
                        type="dark-gray"
                        @click="previewExam"
                    >
                        <DashView />
                        <span>Preview</span>
                    </ResponsiveButton>
                    <p class="hidden sm:block">|</p>
                    <ResponsiveButton class="block sm:hidden p-2" type="light-gray" @click="exitAndSave">
                        <DashExit />
                    </ResponsiveButton>
                    <ResponsiveButton class="hidden sm:block p-2" type="light-gray" @click="exitAndSave">
                        Exit
                    </ResponsiveButton>
                    <ResponsiveButton class="block sm:hidden p-2" type="dash-blue" @click="submitQuestion">
                        <DashSave />
                    </ResponsiveButton>
                    <ResponsiveButton class="hidden sm:block p-2" type="dash-blue" @click="submitQuestion">
                        Save
                    </ResponsiveButton>
                </div>
            </div>
        </div>
    </div>
    <div v-if="showModal" class="fixed inset-0 bg-black/30 z-50 flex items-center justify-center">
        <form @submit.prevent="handleSaveExam">
            <div
                ref="modalContent"
                class="bg-white rounded-lg shadow-sm max-w-6xl w-[90%] max-h-[90vh] overflow-y-auto relative"
            >
                <div class="flex items-center justify-between border-b border-gray-200 py-3 px-6">
                    <div class="flex items-center gap-2 font-bold text-gray-500">
                        <div class="flex items-center gap-2">
                            <DashSettings />
                            <span> Ayhoot Settings </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <ResponsiveButton type="light-gray" class="cursor-pointer p-2" @click="closeModal">
                            Cancel
                        </ResponsiveButton>
                        <ResponsiveButton type="light-blue" class="cursor-pointer p-2" @click="submitForm()">
                            Save
                        </ResponsiveButton>
                    </div>
                </div>
                <div class="py-3 px-6 bg-gray-50 grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2 space-y-4">
                        <!-- Title and Description -->
                        <div class="bg-white p-4 rounded-lg shadow-sm">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">Title</label>
                                <label class="block text-xs font-medium text-gray-500 mb-1"
                                    >Enter a title for your Ayhoot.</label
                                >
                                <input
                                    type="text"
                                    v-model="exam.title"
                                    placeholder="Enter title here"
                                    class="w-full px-3 py-2 rounded-md border-2 border-gray-300 focus:outline-none sm:text-sm"
                                />
                            </div>
                            <div class="pt-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-1"
                                    >Description (Optional)</label
                                ><label class="block text-xs font-medium text-gray-500 mb-1"
                                    >Provide a short description for your Ayhoot to increase visibility.</label
                                >
                                <textarea
                                    v-model="exam.description"
                                    rows="3"
                                    placeholder="Description..."
                                    class="w-full px-3 py-2 rounded-md border-2 border-gray-300 focus:outline-none sm:text-sm"
                                ></textarea>
                            </div>
                        </div>
                        <!-- Monetize -->
                        <div class="bg-white p-4 rounded-lg shadow-sm">
                            <div class="">
                                <label class="block text-sm font-semibold text-gray-700 mb-1"> Monetize </label>
                                <label class="block text-xs font-medium text-gray-500 mb-3">
                                    Choose if you want to monetize your Ayhoot.
                                </label>

                                <div class="flex gap-1 text-sm">
                                    <label
                                        class="flex items-center gap-2 px-2 py-1 border-2 rounded-sm cursor-pointer transition hover:border-blue-500"
                                        :class="
                                            exam.monetize === 'free' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'
                                        "
                                    >
                                        <input type="radio" value="free" v-model="exam.monetize" class="hidden" />
                                        <span
                                            class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                            :class="exam.monetize === 'free' ? 'border-blue-500' : 'border-gray-400'"
                                        >
                                            <span
                                                v-if="exam.monetize === 'free'"
                                                class="w-2 h-2 bg-blue-500 rounded-full"
                                            ></span>
                                        </span>
                                        <span class="text-sm font-semibold text-gray-700">Free</span>
                                    </label>

                                    <label
                                        class="flex items-center gap-2 px-2 py-1 border-2 rounded-sm cursor-pointer transition hover:border-blue-500"
                                        :class="
                                            exam.monetize === 'paid' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'
                                        "
                                    >
                                        <input type="radio" value="paid" v-model="exam.monetize" class="hidden" />
                                        <span
                                            class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                            :class="exam.monetize === 'paid' ? 'border-blue-500' : 'border-gray-400'"
                                        >
                                            <span
                                                v-if="exam.monetize === 'paid'"
                                                class="w-2 h-2 bg-blue-500 rounded-full"
                                            ></span>
                                        </span>
                                        <span class="text-sm font-semibold text-gray-700">Paid</span>
                                    </label>
                                </div>

                                <transition name="fade">
                                    <div v-if="exam.monetize === 'paid'" class="mt-2">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">
                                            Enter Price
                                        </label>
                                        <input
                                            type="number"
                                            v-model="exam.price"
                                            placeholder="e.g. 1,230,000 "
                                            min="0"
                                            class="w-full border-2 border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm"
                                        />
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <!-- Cover Image section with fixed conditional rendering -->
                        <div class="bg-white p-4 rounded-lg shadow-sm">
                            <label class="block text-sm font-semibold text-gray-700 mb-1"> Cover image </label>
                            <label class="block text-xs font-medium text-gray-500 mb-3">
                                Add a cover image to make your Ayhoot stand out.
                            </label>

                            <div
                                class="mt-1 flex justify-center px-2 pt-2 pb-2 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer relative"
                                @dragover.prevent
                                @drop.prevent="handleDrop"
                                @click="triggerFileSelect"
                            >
                                <input
                                    ref="fileInput"
                                    id="file-upload"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handleFileChange"
                                />

                                <!-- Display preview if previewImage exists -->
                                <div v-if="previewImage" class="relative w-full h-60">
                                    <img
                                        :src="previewImage"
                                        class="object-cover w-full h-full rounded-md"
                                        alt="Preview"
                                    />
                                    <button
                                        class="absolute top-2 right-2 bg-white rounded-full p-1 shadow hover:bg-gray-100"
                                        @click.stop="removeImage"
                                    >
                                        <DashRemove />
                                    </button>
                                </div>
                                <!-- Display existing image if exam.cover exists and no previewImage -->
                                <div
                                    v-else-if="exam.cover && typeof exam.cover === 'string'"
                                    class="relative w-full h-60"
                                >
                                    <img
                                        :src="'/uploads/' + exam.cover"
                                        class="object-cover w-full h-full rounded-md"
                                        alt="Preview"
                                    />
                                    <button
                                        class="absolute top-2 right-2 bg-white rounded-full p-1 shadow hover:bg-gray-100"
                                        @click.stop="removeImage"
                                    >
                                        <DashRemove />
                                    </button>
                                </div>
                                <!-- Default upload UI when no image is available -->
                                <div v-else class="space-y-1 text-center">
                                    <svg
                                        class="mx-auto h-12 w-12 text-gray-400"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 48 48"
                                    >
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <span
                                            class="bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500"
                                        >
                                            Upload a file
                                        </span>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                </div>
                            </div>
                        </div>
                        <!-- Save to Folder -->
                        <div class="bg-white p-4 rounded-lg shadow-sm">
                            <label class="block text-sm font-semibold text-gray-700 mb-1"> Category </label>
                            <label class="block text-xs font-medium text-gray-500 mb-3">
                                Define the category of your Ayhoot.
                            </label>
                            <select
                                v-model="exam.category_id"
                                class="w-full px-3 py-3 border-2 border-gray-300 cursor-pointer rounded-sm font-semibold text-gray-400 focus:outline-none sm:text-sm"
                            >
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.title }}
                                </option>
                            </select>
                        </div>
                        <!-- Save to Folder -->
                        <div class="bg-white p-4 rounded-lg shadow-sm">
                            <label class="block text-sm font-semibold text-gray-700 mb-1"> Save to </label>
                            <label class="block text-xs font-medium text-gray-500 mb-3">
                                Choose the folder to save your Ayhoot.
                            </label>
                            <select
                                v-model="exam.folder_id"
                                class="w-full px-3 py-3 border-2 border-gray-300 cursor-pointer rounded-sm font-semibold text-gray-400 focus:outline-none sm:text-sm"
                            >
                                <option value="">Without Folder</option>
                                <option v-for="folder in folders" :key="folder.id" :value="folder.id">
                                    {{ folder.name }}
                                </option>
                            </select>
                        </div>
                        <!-- Save to Institute -->
                        <div class="bg-white p-4 rounded-lg shadow-sm">
                            <label class="block text-sm font-semibold text-gray-700 mb-1"> Assign to Institute </label>
                            <label class="block text-xs font-medium text-gray-500 mb-3">
                                Choose the institute to assign your Ayhoot.
                            </label>
                            <select
                                v-model="exam.institute_id"
                                class="w-full px-3 py-3 border-2 border-gray-300 cursor-pointer rounded-sm font-semibold text-gray-400 focus:outline-none sm:text-sm"
                            >
                                <option value="">Freelance</option>
                                <option v-for="institute in institutes" :key="institute.id" :value="institute.id">
                                    {{ institute.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import DashAyhootLogo from '@/components/atoms/icons/DashAyhootLogo.vue'
import UpgradeButton from '@/components/atoms/button/UpgradeButton.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import Notification from '@/components/atoms/icons/Notification.vue'
import Signout from '@/components/atoms/icons/Signout.vue'
import DashSave from '@/components/atoms/icons/DashSave.vue'
import DashExit from '@/components/atoms/icons/DashExit.vue'
import SearchWithCategory from '@/components/molecules/Search/SearchWithCategory.vue'
import DashSettings from '@/components/atoms/icons/DashSettings.vue'
import DashView from '@/components/atoms/icons/DashView.vue'
import DashRemove from '@/components/atoms/icons/DashRemove.vue'
import { apiCall } from '@/services/apiService'

export default {
    name: 'CreateHeader',
    components: {
        DashAyhootLogo,
        UpgradeButton,
        AyButton,
        ResponsiveButton,
        Notification,
        Signout,
        DashSave,
        DashExit,
        SearchWithCategory,
        DashSettings,
        DashView,
        DashRemove,
    },
    props: {
        submitQuestion: {
            type: Function,
            default: null,
        },

        exitAndSave: {
            type: Function,
            default: null,
        },
        previewExam: {
            type: Function,
            default: null,
        },
        exam: {
            type: Object,
            default: () => ({
                title: '',
                description: '',
                type: 'multiple',
                institute_id: '',
                teacher_id: '',
                status: 'published',
            }),
        },
    },
    data() {
        return {
            dropdownOpen: false,
            searchMode: false,
            showModal: false,
            price: null,
            previewImage: null,
            selectedFile: null,
            categories: {},
            folders: {},
            institutes: {},
        }
    },

    methods: {
        triggerFileSelect() {
            this.$refs.fileInput.click()
        },
        handleFileChange(event) {
            const file = event.target.files[0]
            if (!file || !file.type.startsWith('image/')) return
            this.exam.cover = file
            const reader = new FileReader()

            reader.onload = (e) => {
                this.previewImage = e.target.result
            }
            reader.readAsDataURL(file)
        },
        handleDrop(event) {
            const file = event.dataTransfer.files[0]
            if (file) this.handleFileChange({ target: { files: [file] } })
        },
        setPreview(file) {
            if (!file.type.startsWith('image/')) return

            this.selectedFile = file
            const reader = new FileReader()
            reader.onload = (e) => {
                this.previewImage = e.target.result
            }
            reader.readAsDataURL(file)
        },
        removeImage() {
            this.previewImage = null
            if (typeof this.exam.cover === 'string') {
                this.exam.cover = ''
            } else {
                this.exam.cover = null
            }
            this.$refs.fileInput.value = null
        },

        closeModal() {
            this.$emit('update-settings', this.exam) // Emit the updated data
            this.showModal = false
        },
        openModal(event) {
            event.stopPropagation()
            this.showModal = true
        },

        handleClickOutside(event) {
            const modalContent = this.$refs.modalContent
            if (modalContent && !modalContent.contains(event.target)) {
                this.showModal = false
            }
        },
        async fetchExamDetail() {
            const response = await apiCall('GET', `/api/admin/exam/${this.$route.params.id}`)
            this.exam = response.exam
        },

        async handleSaveExam() {
            try {
                if (this.exam.cover instanceof File) {
                    const response = await apiCall('POST', `/api/admin/exam/${this.$route.params.id}`, this.exam)
                    if (response.message) {
                    }

                    this.closeModal()
                } else {
                    const response = await apiCall('POST', `/api/admin/exam/${this.$route.params.id}`, this.exam)

                    if (response.message) {
                    }

                    this.closeModal()
                }
            } catch (error) {
                console.error('Error saving exam:', error)
            }
        },

        async submitForm() {
            this.submitForm()
        },

        async fetchCategories() {
            const response = await apiCall('GET', `/api/home`)
            this.categories = response.categories
        },

        async fetchFolders() {
            const response = await apiCall('GET', `/api/admin/folder`)
            this.folders = response.folders
        },

        async fetchInstitutes() {
            const response = await apiCall('GET', `/api/admin/institutes`)
            this.institutes = response.institutes
        },
    },

    mounted() {
        // Add the event listener when the component is mounted
        document.addEventListener('click', this.handleClickOutside)
        this.fetchCategories()
        this.fetchFolders()
        this.fetchInstitutes()
    },
    beforeUnmount() {
        // Remove the event listener when the component is destroyed
        document.removeEventListener('click', this.handleClickOutside)
    },
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
