<template>
    <div :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'" class="fixed top-0 inset-x-0">
        <div
            class="flex justify-between items-center py-3 px-6 border-b border-gray-300 z-50 bg-white"
            :class="{ 'text-right': isRTL, 'text-left': !isRTL }"
        >
            <div class="w-full flex justify-between">
                <div
                    :class="
                        isRTL
                            ? 'flex flex-row-reverse items-center gap-reverse gap-4 sm:gap-6'
                            : 'flex items-center gap-4 sm:gap-6'
                    "
                >
                    <button @click="$emit('toggle-sidebar')" class="block sm:hidden text-gray-600 focus:outline-none">
                        <span class="sr-only">{{ t('createQuestionHeader.openQuestionsList') }}</span>
                        <svg
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                            :class="isRTL ? 'transform rotate-180' : ''"
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
                    :class="{ 'flex-row-reverse gap-reverse': isRTL }"
                >
                    <div v-if="exam?.title" class="text-sm font-semibold text-gray-500 px-2">
                        {{ exam?.title }}
                    </div>
                    <div v-else class="text-sm font-semibold text-gray-500 px-2">
                        {{ t('createQuestionHeader.enterAyhootTitle') }}
                    </div>
                    <button
                        type="button"
                        class="px-4 py-1 bg-gray-600 group-hover:bg-gray-700 cursor-pointer text-white font-semibold text-sm rounded-sm"
                        @click.stop="openModal"
                    >
                        {{ t('createQuestionHeader.settings') }}
                    </button>
                </div>

                <div
                    :class="
                        isRTL
                            ? 'flex flex-row-reverse items-center gap-reverse gap-2 sm:gap-2'
                            : 'flex items-center gap-2 sm:gap-2'
                    "
                >
                    <ResponsiveButton class="block sm:hidden p-2" type="dark-blue" @click="openModal">
                        <DashSettings />
                    </ResponsiveButton>
                    <ResponsiveButton class="block sm:hidden p-2" type="light-gray">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M9.9999 2.39999C9.9999 1.84771 9.55219 1.39999 8.9999 1.39999C8.44762 1.39999 7.9999 1.84771 7.9999 2.39999H9.9999ZM7.9999 21.6C7.9999 22.1523 8.44762 22.6 8.9999 22.6C9.55219 22.6 9.9999 22.1523 9.9999 21.6H7.9999ZM5.9999 3.39999H17.9999V1.39999H5.9999V3.39999ZM20.5999 5.99999V18H22.5999V5.99999H20.5999ZM17.9999 20.6H5.9999V22.6H17.9999V20.6ZM3.3999 18V5.99999H1.3999V18H3.3999ZM5.9999 20.6C4.56396 20.6 3.3999 19.4359 3.3999 18H1.3999C1.3999 20.5405 3.45939 22.6 5.9999 22.6V20.6ZM20.5999 18C20.5999 19.4359 19.4358 20.6 17.9999 20.6V22.6C20.5404 22.6 22.5999 20.5405 22.5999 18H20.5999ZM17.9999 3.39999C19.4358 3.39999 20.5999 4.56405 20.5999 5.99999H22.5999C22.5999 3.45948 20.5404 1.39999 17.9999 1.39999V3.39999ZM5.9999 1.39999C3.45939 1.39999 1.3999 3.45948 1.3999 5.99999H3.3999C3.3999 4.56405 4.56396 3.39999 5.9999 3.39999V1.39999ZM7.9999 2.39999V21.6H9.9999V2.39999H7.9999ZM2.9999 9.99999H20.9999V7.99999H2.9999V9.99999Z"
                                fill="black"
                            />
                        </svg>
                    </ResponsiveButton>
                    <ResponsiveButton
                        class="hidden sm:flex items-center gap-1 p-2"
                        :class="isRTL ? 'flex-row-reverse gap-reverse' : ''"
                        type="light-gray"
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M9.9999 2.39999C9.9999 1.84771 9.55219 1.39999 8.9999 1.39999C8.44762 1.39999 7.9999 1.84771 7.9999 2.39999H9.9999ZM7.9999 21.6C7.9999 22.1523 8.44762 22.6 8.9999 22.6C9.55219 22.6 9.9999 22.1523 9.9999 21.6H7.9999ZM5.9999 3.39999H17.9999V1.39999H5.9999V3.39999ZM20.5999 5.99999V18H22.5999V5.99999H20.5999ZM17.9999 20.6H5.9999V22.6H17.9999V20.6ZM3.3999 18V5.99999H1.3999V18H3.3999ZM5.9999 20.6C4.56396 20.6 3.3999 19.4359 3.3999 18H1.3999C1.3999 20.5405 3.45939 22.6 5.9999 22.6V20.6ZM20.5999 18C20.5999 19.4359 19.4358 20.6 17.9999 20.6V22.6C20.5404 22.6 22.5999 20.5405 22.5999 18H20.5999ZM17.9999 3.39999C19.4358 3.39999 20.5999 4.56405 20.5999 5.99999H22.5999C22.5999 3.45948 20.5404 1.39999 17.9999 1.39999V3.39999ZM5.9999 1.39999C3.45939 1.39999 1.3999 3.45948 1.3999 5.99999H3.3999C3.3999 4.56405 4.56396 3.39999 5.9999 3.39999V1.39999ZM7.9999 2.39999V21.6H9.9999V2.39999H7.9999ZM2.9999 9.99999H20.9999V7.99999H2.9999V9.99999Z"
                                fill="black"
                            />
                        </svg>
                        <span>{{ t('createQuestionHeader.insert') }}</span>
                    </ResponsiveButton>
                    <ResponsiveButton class="block sm:hidden p-2" type="dark-gray" @click="previewExam">
                        <DashView />
                    </ResponsiveButton>
                    <ResponsiveButton
                        class="hidden sm:flex items-center gap-1 p-2"
                        :class="isRTL ? 'flex-row-reverse gap-reverse' : ''"
                        type="dark-gray"
                        @click="previewExam"
                    >
                        <DashView />
                        <span>{{ t('createQuestionHeader.preview') }}</span>
                    </ResponsiveButton>
                    <p class="hidden sm:block">|</p>
                    <ResponsiveButton class="block sm:hidden p-2" type="light-gray" @click="exitAndSave">
                        <DashExit />
                    </ResponsiveButton>
                    <ResponsiveButton class="hidden sm:block p-2" type="light-gray" @click="exitAndSave">
                        {{ t('createQuestionHeader.exit') }}
                    </ResponsiveButton>
                    <ResponsiveButton class="block sm:hidden p-2" type="dash-blue" @click="submitQuestion">
                        <DashSave />
                    </ResponsiveButton>
                    <ResponsiveButton class="hidden sm:block p-2" type="dash-blue" @click="submitQuestion">
                        {{ t('createQuestionHeader.save') }}
                    </ResponsiveButton>
                </div>
            </div>
        </div>

        <Modal
            v-model="showModal"
            size="2xl"
            :title="t('createQuestionHeader.ayhootSettings')"
            body-class=""
            @close="closeModal"
        >
                    <div class="py-3 px-6 bg-gray-50 grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-2 space-y-4">
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                    :class="{ 'text-right': isRTL }"
                                    >{{ t('createQuestionHeader.titleLabel') }}</label
                                >
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-1"
                                    :class="{ 'text-right': isRTL }"
                                    >{{ t('createQuestionHeader.titleSubtitle') }}</label
                                >
                                <input
                                    type="text"
                                    v-model="exam.title"
                                    :placeholder="t('createQuestionHeader.enterTitlePlaceholder')"
                                    class="w-full px-3 py-2 rounded-md border-2 border-gray-300 focus:outline-none sm:text-sm"
                                />

                                <label
                                    class="block text-sm font-semibold text-gray-700 mt-4"
                                    :class="{ 'text-right': isRTL }"
                                    >{{ t('createQuestionHeader.descriptionLabel') }}</label
                                >
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-1"
                                    :class="{ 'text-right': isRTL }"
                                    >{{ t('createQuestionHeader.descriptionSubtitle') }}</label
                                >
                                <textarea
                                    v-model="exam.description"
                                    rows="3"
                                    :placeholder="t('createQuestionHeader.enterDescriptionPlaceholder')"
                                    class="w-full px-3 py-2 rounded-md border-2 border-gray-300 focus:outline-none sm:text-sm"
                                ></textarea>
                            </div>

                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                    :class="{ 'text-right': isRTL }"
                                    >{{ t('createQuestionHeader.monetizeLabel') }}</label
                                >
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-3"
                                    :class="{ 'text-right': isRTL }"
                                    >{{ t('createQuestionHeader.monetizeSubtitle') }}</label
                                >

                                <div class="flex gap-1 text-sm" :class="{ 'flex-row-reverse': isRTL }">
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
                                        <span class="text-sm font-semibold text-gray-700">{{
                                            t('createQuestionHeader.free')
                                        }}</span>
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
                                        <span class="text-sm font-semibold text-gray-700">{{
                                            t('createQuestionHeader.paid')
                                        }}</span>
                                    </label>
                                </div>

                                <transition name="fade">
                                    <div v-if="exam.monetize === 'paid'" class="mt-2">
                                        <label
                                            class="block text-xs font-medium text-gray-500 mb-1"
                                            :class="{ 'text-right': isRTL }"
                                        >
                                            {{ t('createQuestionHeader.enterPriceLabel') }}
                                        </label>
                                        <input
                                            type="number"
                                            v-model="exam.price"
                                            :placeholder="t('createQuestionHeader.enterPricePlaceholder')"
                                            min="0"
                                            class="w-full border-2 border-gray-300 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm"
                                        />
                                    </div>
                                </transition>
                            </div>

                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700" :class="{ 'text-right': isRTL }">
                                            {{ t('createQuestionHeader.playMusicLabel') }}
                                        </label>
                                        <p class="text-xs text-gray-500" :class="{ 'text-right': isRTL }">
                                            {{ t('createQuestionHeader.playMusicSubtitle') }}
                                        </p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" v-model="exam.play_music" class="sr-only peer" />
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-1"
                                    :class="{ 'text-right': isRTL }"
                                    >{{ t('createQuestionHeader.coverImageLabel') }}</label
                                >
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-3"
                                    :class="{ 'text-right': isRTL }"
                                    >{{ t('createQuestionHeader.coverImageSubtitle') }}</label
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
                                        v-else-if="exam.cover && typeof exam.cover === 'string'"
                                        class="relative w-full h-60"
                                    >
                                        <img
                                            :src="`/uploads/${exam.cover}`"
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
                                            :class="{ 'flex-row-reverse': isRTL }"
                                        >
                                            <span
                                                class="bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500"
                                            >
                                                {{ t('createQuestionHeader.uploadFile') }}
                                            </span>
                                            <p :class="isRTL ? 'pr-1' : 'pl-1'">
                                                {{ t('createQuestionHeader.orDragDrop') }}
                                            </p>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            {{ t('createQuestionHeader.supportedFileTypes') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-1"
                                    :class="{ 'text-right': isRTL }"
                                    >{{ t('createQuestionHeader.categoryLabel') }}</label
                                >
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-3"
                                    :class="{ 'text-right': isRTL }"
                                    >{{ t('createQuestionHeader.categorySubtitle') }}</label
                                >
                                <select
                                    v-model="exam.category_id"
                                    class="w-full px-3 py-3 border-2 border-gray-300 cursor-pointer rounded-sm font-semibold text-gray-400 focus:outline-none sm:text-sm"
                                >
                                    <option :value="null">{{ t('createQuestionHeader.withoutCategory') || 'Select Category' }}</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.title }}
                                    </option>
                                </select>
                            </div>

                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-1"
                                    :class="{ 'text-right': isRTL }"
                                    >{{ t('createQuestionHeader.saveToFolderLabel') }}</label
                                >
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-3"
                                    :class="{ 'text-right': isRTL }"
                                    >{{ t('createQuestionHeader.saveToFolderSubtitle') }}</label
                                >
                                <select
                                    v-model="exam.folder_id"
                                    class="w-full px-3 py-3 border-2 border-gray-300 cursor-pointer rounded-sm font-semibold text-gray-400 focus:outline-none sm:text-sm"
                                >
                                    <option :value="null">{{ t('createQuestionHeader.withoutFolder') }}</option>
                                    <option v-for="folder in folders" :key="folder.id" :value="folder.id">
                                        {{ folder.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-1"
                                    :class="{ 'text-right': isRTL }"
                                    >{{ t('createQuestionHeader.assignToInstituteLabel') }}</label
                                >
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-3"
                                    :class="{ 'text-right': isRTL }"
                                    >{{ t('createQuestionHeader.assignToInstituteSubtitle') }}</label
                                >
                                <select
                                    v-model="exam.institute_id"
                                    class="w-full px-3 py-3 border-2 border-gray-300 cursor-pointer rounded-sm font-semibold text-gray-400 focus:outline-none sm:text-sm"
                                >
                                    <option :value="null">{{ t('createQuestionHeader.freelance') }}</option>
                                    <option v-for="institute in institutes" :key="institute.id" :value="institute.id">
                                        {{ institute.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <template #footer>
                        <div :class="isRTL ? 'flex flex-row-reverse gap-reverse gap-2' : 'flex items-center gap-2'">
                            <ResponsiveButton type="light-gray" class="cursor-pointer p-2" @click="closeModal">
                                {{ t('createQuestionHeader.cancel') }}
                            </ResponsiveButton>
                            <ResponsiveButton type="light-blue" class="cursor-pointer p-2" @click="saveExam">
                                {{ t('createQuestionHeader.save') }}
                            </ResponsiveButton>
                        </div>
                    </template>
        </Modal>
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
import Modal from '@/components/shared/Modal.vue'
import { apiCall } from "@/services/apiService"
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

export default {
    name: 'CreateHeader',
    components: {
        DashAyhootLogo,
        ResponsiveButton,
        DashSave,
        DashExit,
        DashSettings,
        DashView,
        DashRemove,
        Modal,
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
                monetize: 'free',
                price: null,
                cover: null,
                category_id: null,
                folder_id: null,
            }),
        },
    },
    data() {
        return {
            dropdownOpen: false,
            searchMode: false,
            showModal: false,
            previewImage: null,
            selectedFile: null,
            categories: [],
            folders: [],
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
            this.$emit('update-settings', this.exam)
            this.showModal = false
        },
        openModal(event) {
            if (event) event.stopPropagation()
            if (this.exam) {
                if (this.exam.institute_id === '' || this.exam.institute_id === undefined) {
                    this.exam.institute_id = null
                }
                if (this.exam.category_id === '' || this.exam.category_id === undefined) {
                    this.exam.category_id = null
                }
                if (this.exam.folder_id === '' || this.exam.folder_id === undefined) {
                    this.exam.folder_id = null
                }
            }
            this.showModal = true
        },

        async fetchExamData() {
            const response = await apiCall('GET', `/api/instructor/exam/${this.$route.params.id}`)
            this.exam = response.exam
        },

        async saveExam() {
            try {
                const selectedFolderId = this.exam.folder_id

                let requestData
                if (this.exam.cover instanceof File) {
                    const formData = new FormData()
                    formData.append('title', this.exam.title || '')
                    formData.append('description', this.exam.description || '')
                    formData.append('monetize', this.exam.monetize || 'free')
                    if (this.exam.monetize === 'paid' && this.exam.price) {
                        formData.append('price', this.exam.price)
                    }
                    formData.append('category_id', this.exam.category_id || '')
                    formData.append('institute_id', this.exam.institute_id || '')
                    formData.append('folder_id', this.exam.folder_id || '')
                    formData.append('play_music', this.exam.play_music ? 1 : 0)
                    formData.append('cover', this.exam.cover)
                    requestData = formData
                } else {
                    requestData = {
                        title: this.exam.title,
                        description: this.exam.description || '',
                        monetize: this.exam.monetize || 'free',
                        price: this.exam.monetize === 'paid' ? this.exam.price : null,
                        category_id: this.exam.category_id || null,
                        institute_id: this.exam.institute_id || null,
                        folder_id: this.exam.folder_id || null,
                        play_music: this.exam.play_music ? 1 : 0,
                        cover: typeof this.exam.cover === 'string' ? this.exam.cover : null,
                    }
                }

                const response = await apiCall('POST', `/api/instructor/exam/${this.$route.params.id}`, requestData)

                if (response && response.exam) {
                    Object.assign(this.exam, response.exam)
                    this.$emit('update-settings', response.exam)
                }

                if (selectedFolderId) {
                    await apiCall('POST', `/api/instructor/folder/${selectedFolderId}/assign-exams`, {
                        exam_ids: [this.$route.params.id],
                    })
                }

                this.$toast.success(response?.message || this.t('createQuestionHeader.saveSuccess') || 'Exam settings updated successfully')
                this.closeModal()
            } catch (error) {
                console.error('Error saving exam or assigning to folder:', error)
                let errMsg = this.t('createQuestionHeader.saveFailed') || 'Failed to save exam settings'
                if (error?.response?.errors) {
                    errMsg = Object.values(error.response.errors).flat().join(' ')
                } else if (error?.response?.message) {
                    errMsg = error.response.message
                }
                this.$toast.error(errMsg)
            }
        },

        submitForm() {
            this.saveExam()
        },

        async fetchCategories() {
            const response = await apiCall('GET', `/api/home`)
            this.categories = response.categories || []
        },

        async fetchFolders() {
            const response = await apiCall('GET', `/api/instructor/folder`)
            this.folders = response.folders || []
        },

        async fetchInstitutes() {
            // Affiliated institutes is a teacher-only concept; institutes don't
            // belong to institutes, so skip the (teacher-only) endpoint for them.
            if (this.$role !== 'teacher') return
            const response = await apiCall('GET', `/api/instructor/institutes`)
            this.institutes = response.institutes || []
        },
    },

    mounted() {
        this.fetchCategories()
        this.fetchFolders()
        this.fetchInstitutes()
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
