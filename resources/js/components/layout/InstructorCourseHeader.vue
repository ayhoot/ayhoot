<template>
    <div
        :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'"
        class="fixed top-0 inset-x-0"
        :class="{ 'font-payda': $i18n.locale === 'fa' }"
    >
        <div
            class="flex justify-between items-center py-3 px-6 border-b border-gray-300 z-50 bg-white"
            :class="{ 'text-right': $i18n.locale === 'fa', 'text-left': !$i18n.locale === 'fa' }"
        >
            <div class="w-full flex justify-between">
                <div class="flex items-center gap-4 sm:gap-6" :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }">
                    <button @click="$emit('toggle-sidebar')" class="block sm:hidden text-gray-600 focus:outline-none">
                        <span class="sr-only">{{ $t('courseHeader.openContentList') }}</span>
                        <svg
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                            :class="$i18n.locale === 'fa' ? 'transform rotate-180' : ''"
                        >
                            <path d="M5 7H19" stroke="#33363F" stroke-width="2" stroke-linecap="round" />
                            <path d="M5 12H19" stroke="#33363F" stroke-width="2" stroke-linecap="round" />
                            <path d="M5 17H19" stroke="#33363F" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                    <RouterLink :to="`/${$role}/dashboard`">
                        <DashAyhootLogo />
                    </RouterLink>
                </div>

                <div
                    @click="openModal"
                    class="items-center gap-14 border-2 border-gray-300 rounded-lg p-1 group cursor-pointer hidden sm:flex"
                    :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }"
                >
                    <div v-if="course?.title" class="text-sm font-semibold text-gray-500 px-2">
                        {{ course?.title }}
                    </div>
                    <div v-else class="text-sm font-semibold text-gray-500 px-2">
                        {{ $t('courseHeader.enterCourseTitle') }}
                    </div>
                    <Button
                        type="light-gray"
                        class="px-4 py-1 bg-gray-600 group-hover:bg-gray-700 cursor-pointer text-white font-semibold text-sm rounded-sm"
                        @click.stop="openModal"
                    >
                        {{ $t('courseHeader.settings') }}
                    </Button>
                </div>

                <div class="flex items-center gap-2 sm:gap-2" :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }">
                    <ResponsiveButton class="block sm:hidden p-2" type="dark-blue" @click="openModal">
                        <DashSettings />
                    </ResponsiveButton>

                    <ResponsiveButton class="block sm:hidden p-2" type="light-gray" @click="exitAndSave">
                        <DashExit />
                    </ResponsiveButton>
                    <ResponsiveButton class="hidden sm:block p-2" type="light-gray" @click="exitAndSave">
                        {{ $t('courseHeader.exit') }}
                    </ResponsiveButton>
                    <ResponsiveButton class="block sm:hidden p-2" type="dash-blue" @click="saveCourse">
                        <DashSave />
                    </ResponsiveButton>
                    <ResponsiveButton class="hidden sm:block p-2" type="dash-blue" @click="saveCourse">
                        {{ $t('courseHeader.save') }}
                    </ResponsiveButton>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black/30 z-50 flex items-center justify-center">
            <form @submit.prevent="handleSaveCourse">
                <div
                    ref="modalContent"
                    class="bg-white rounded-lg shadow-sm max-w-6xl w-[90%] max-h-[90vh] overflow-y-auto relative"
                    :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'"
                >
                    <div class="flex items-center justify-between border-b border-gray-200 py-3 px-6">
                        <div
                            class="flex items-center gap-2 font-bold text-gray-500"
                            :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }"
                        >
                            <DashSettings />
                            <span>{{ $t('courseHeader.modal.title') }}</span>
                        </div>
                        <div class="flex items-center gap-2" :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }">
                            <ResponsiveButton type="light-gray" class="cursor-pointer p-2" @click="closeModal">
                                {{ $t('courseHeader.modal.cancel') }}
                            </ResponsiveButton>
                            <ResponsiveButton type="light-blue" class="cursor-pointer p-2" @click="submitForm">
                                {{ $t('courseHeader.modal.save') }}
                            </ResponsiveButton>
                        </div>
                    </div>
                    <div class="py-3 px-6 bg-gray-50 grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-2 space-y-4">
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                    :class="{ 'text-right': $i18n.locale === 'fa' }"
                                    >{{ $t('courseHeader.modal.titleLabel') }}</label
                                >
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-1"
                                    :class="{ 'text-right': $i18n.locale === 'fa' }"
                                    >{{ $t('courseHeader.modal.titleSubtitle') }}</label
                                >
                                <input
                                    type="text"
                                    v-model="course.title"
                                    :placeholder="$t('courseHeader.modal.enterTitlePlaceholder')"
                                    class="w-full px-3 py-2 rounded-md border-2 border-gray-300 focus:outline-none sm:text-sm"
                                />
                                <label
                                    class="block text-sm font-semibold text-gray-700 mt-4"
                                    :class="{ 'text-right': $i18n.locale === 'fa' }"
                                    >{{ $t('courseHeader.modal.descriptionLabel') }}</label
                                >
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-1"
                                    :class="{ 'text-right': $i18n.locale === 'fa' }"
                                    >{{ $t('courseHeader.modal.descriptionSubtitle') }}</label
                                >
                                <textarea
                                    v-model="course.description"
                                    rows="3"
                                    :placeholder="$t('courseHeader.modal.enterDescriptionPlaceholder')"
                                    class="w-full px-3 py-2 rounded-md border-2 border-gray-300 focus:outline-none sm:text-sm"
                                ></textarea>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                    :class="{ 'text-right': $i18n.locale === 'fa' }"
                                    >{{ $t('courseHeader.modal.monetizeLabel') }}</label
                                >
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-3"
                                    :class="{ 'text-right': $i18n.locale === 'fa' }"
                                    >{{ $t('courseHeader.modal.monetizeSubtitle') }}</label
                                >
                                <div class="flex gap-1 text-sm" :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }">
                                    <label
                                        class="flex items-center gap-2 px-2 py-1 border-2 rounded-sm cursor-pointer transition hover:border-blue-500"
                                        :class="
                                            course.monetize === 'free'
                                                ? 'border-blue-500 bg-blue-50'
                                                : 'border-gray-300'
                                        "
                                    >
                                        <input type="radio" value="free" v-model="course.monetize" class="hidden" />
                                        <span
                                            class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                            :class="course.monetize === 'free' ? 'border-blue-500' : 'border-gray-400'"
                                        >
                                            <span
                                                v-if="course.monetize === 'free'"
                                                class="w-2 h-2 bg-blue-500 rounded-full"
                                            ></span>
                                        </span>
                                        <span class="text-sm font-semibold text-gray-700">{{
                                            $t('courseHeader.modal.free')
                                        }}</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-2 px-2 py-1 border-2 rounded-sm cursor-pointer transition hover:border-blue-500"
                                        :class="
                                            course.monetize === 'paid'
                                                ? 'border-blue-500 bg-blue-50'
                                                : 'border-gray-300'
                                        "
                                    >
                                        <input type="radio" value="paid" v-model="course.monetize" class="hidden" />
                                        <span
                                            class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                            :class="course.monetize === 'paid' ? 'border-blue-500' : 'border-gray-400'"
                                        >
                                            <span
                                                v-if="course.monetize === 'paid'"
                                                class="w-2 h-2 bg-blue-500 rounded-full"
                                            ></span>
                                        </span>
                                        <span class="text-sm font-semibold text-gray-700">{{
                                            $t('courseHeader.modal.paid')
                                        }}</span>
                                    </label>
                                </div>
                                <transition name="fade">
                                    <div v-if="course.monetize === 'paid'" class="mt-2">
                                        <label
                                            class="block text-xs font-medium text-gray-500 mb-1"
                                            :class="{ 'text-right': $i18n.locale === 'fa' }"
                                            >{{ $t('courseHeader.modal.enterPriceLabel') }}</label
                                        >
                                        <input
                                            type="number"
                                            v-model="course.price"
                                            :placeholder="$t('courseHeader.modal.enterPricePlaceholder')"
                                            min="0"
                                            class="w-full border-2 border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm"
                                        />
                                    </div>
                                </transition>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-1"
                                    :class="{ 'text-right': $i18n.locale === 'fa' }"
                                    >{{ $t('courseHeader.modal.coverImageLabel') }}</label
                                >
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-3"
                                    :class="{ 'text-right': $i18n.locale === 'fa' }"
                                    >{{ $t('courseHeader.modal.coverImageSubtitle') }}</label
                                >
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
                                    <div
                                        v-else-if="course.cover && typeof course.cover === 'string'"
                                        class="relative w-full h-60"
                                    >
                                        <img
                                            :src="`/uploads/${course.cover}`"
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
                                        <div
                                            class="flex text-sm text-gray-600 justify-center"
                                            :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }"
                                        >
                                            <span
                                                class="bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500"
                                                >{{ $t('courseHeader.modal.uploadFile') }}</span
                                            >
                                            <p :class="$i18n.locale === 'fa' ? 'pr-1' : 'pl-1'">
                                                {{ $t('courseHeader.modal.orDragDrop') }}
                                            </p>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            {{ $t('courseHeader.modal.supportedFileTypes') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-1"
                                    :class="{ 'text-right': $i18n.locale === 'fa' }"
                                    >{{ $t('courseHeader.modal.categoryLabel') }}</label
                                >
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-3"
                                    :class="{ 'text-right': $i18n.locale === 'fa' }"
                                    >{{ $t('courseHeader.modal.categorySubtitle') }}</label
                                >
                                <select
                                    v-model="course.category_id"
                                    class="w-full px-3 py-3 border-2 border-gray-300 cursor-pointer rounded-sm font-semibold text-gray-400 focus:outline-none sm:text-sm"
                                >
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.title }}
                                    </option>
                                </select>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-1"
                                    :class="{ 'text-right': $i18n.locale === 'fa' }"
                                    >{{ $t('courseHeader.modal.assignToInstituteLabel') }}</label
                                >
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-3"
                                    :class="{ 'text-right': $i18n.locale === 'fa' }"
                                    >{{ $t('courseHeader.modal.assignToInstituteSubtitle') }}</label
                                >
                                <select
                                    v-model="course.institute_id"
                                    class="w-full px-3 py-3 border-2 border-gray-300 cursor-pointer rounded-sm font-semibold text-gray-400 focus:outline-none sm:text-sm"
                                >
                                    <option value="">{{ $t('courseHeader.modal.freelance') }}</option>
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
    </div>
</template>

<script>
import DashAyhootLogo from '@/components/atoms/icons/DashAyhootLogo.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashSave from '@/components/atoms/icons/DashSave.vue'
import DashExit from '@/components/atoms/icons/DashExit.vue'
import DashSettings from '@/components/atoms/icons/DashSettings.vue'
import DashView from '@/components/atoms/icons/DashView.vue'
import DashRemove from '@/components/atoms/icons/DashRemove.vue'
import Button from '@/components/atoms/button/AyButton.vue'
import { apiCall } from "@/services/apiService"
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

export default {
    name: 'CourseHeader',
    components: {
        DashAyhootLogo,
        ResponsiveButton,
        Button,
        DashSave,
        DashExit,
        DashSettings,
        DashView,
        DashRemove,
    },
    props: {
        saveCourse: Function,
        exitAndSave: Function,
        previewCourse: Function,
        course: {
            type: Object,
            default: () => ({
                title: '',
                description: '',
                type: 'multiple',
                institute_id: '',
                teacher_id: '',
                status: 'published',
                monetize: 'free',
                price: null,
                cover: null,
                category_id: null,
            }),
        },
    },
    data() {
        return {
            showModal: false,
            previewImage: null,
            categories: [],
            institutes: [],
        }
    },
    setup() {
        const { locale, t } = useI18n()
        const isRTL = computed(() => locale.value === 'fa')
        return { isRTL, t }
    },
    methods: {
        triggerFileSelect() {
            this.$refs.fileInput.click()
        },
        handleFileChange(event) {
            const file = event.target.files[0]
            if (!file || !file.type.startsWith('image/')) return
            this.course.cover = file
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
        removeImage() {
            this.previewImage = null
            if (typeof this.course.cover === 'string') {
                this.course.cover = ''
            } else {
                this.course.cover = null
            }
            this.$refs.fileInput.value = null
        },
        closeModal() {
            this.$emit('settings-updated', this.course)
            this.showModal = false
        },
        openModal(event) {
            event.stopPropagation()
            this.showModal = true
        },
        handleClickOutside(event) {
            const modalContent = this.$refs.modalContent
            if (modalContent && !modalContent.contains(event.target)) {
                this.closeModal()
            }
        },
        async handleSaveCourse() {
            this.$emit('settings-updated', this.course)
            this.closeModal()
        },
        async submitForm() {
            await this.handleSaveCourse()
        },
        async fetchCategories() {
            const response = await apiCall('GET', `/api/home`)
            this.categories = response.categories || []
        },
        async fetchInstitutes() {
            // Teacher-only: institutes have no affiliated institutes list.
            if (this.$role !== 'teacher') return
            const response = await apiCall('GET', `/api/instructor/institutes`)
            this.institutes = response.institutes || []
        },
    },
    mounted() {
        document.addEventListener('click', this.handleClickOutside)
        this.fetchCategories()
        this.fetchInstitutes()
    },
    beforeUnmount() {
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
.font-payda {
    font-family: 'Peyda', sans-serif !important;
}
</style>
