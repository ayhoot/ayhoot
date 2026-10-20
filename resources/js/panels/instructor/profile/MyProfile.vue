<template>
    <InstructorLayout :secondary="false">
                <div v-if="isLoading" class="flex items-center justify-center h-[calc(100vh-4rem)]">
                    <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-indigo-500"></div>
                </div>
                <div v-else-if="teacher" class="w-full">
                    <div class="relative mb-16">
                        <div
                            class="h-48 sm:h-64 md:h-72 bg-cover bg-center bg-gray-200 rounded-xl"
                            :style="teacher.cover ? { backgroundImage: `url(${teacher.cover})` } : {}"
                        ></div>
                        <div class="absolute top-3" :class="isRTL ? 'right-4' : 'left-4'">
                            <input
                                type="file"
                                ref="coverImageInput"
                                @change="handleImageUpload('cover', $event)"
                                class="hidden"
                                accept="image/*"
                            />
                            <ResponsiveButton
                                type="light-gray"
                                @click="$refs.coverImageInput.click()"
                                class="p-2 text-sm flex items-center bg-white/80 hover:bg-white rounded-lg shadow"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    :class="isRTL ? 'ml-1.5' : 'mr-1.5'"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
                                    />
                                </svg>
                                {{ t('myProfile.uploadCover') }}
                            </ResponsiveButton>
                        </div>
                        <div
                            class="absolute -bottom-12 left-1/2 -translate-x-1/2 sm:translate-x-0 sm:-bottom-8 flex flex-col items-center sm:items-start"
                            :class="isRTL ? 'sm:right-8' : 'sm:left-8'"
                        >
                            <div class="relative group">
                                <img
                                    v-if="teacher.avatar"
                                    :src="teacher.avatar"
                                    :alt="t('myProfile.profilePictureAlt')"
                                    class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-white shadow-sm object-cover"
                                />
                                <div
                                    v-else
                                    class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-white shadow-sm bg-indigo-500 flex items-center justify-center text-white text-4xl sm:text-5xl font-bold"
                                >
                                    {{ teacherInitials }}
                                </div>
                                <input
                                    type="file"
                                    ref="profileImageInput"
                                    @change="handleImageUpload('avatar', $event)"
                                    class="hidden"
                                    accept="image/*"
                                />
                                <button
                                    @click="$refs.profileImageInput.click()"
                                    class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-8 w-8 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="absolute top-4" :class="isRTL ? 'left-4' : 'right-4'">
                            <ResponsiveButton
                                type="light-gray"
                                @click="shareProfile"
                                class="p-2 text-sm flex items-center bg-white/80 hover:bg-white rounded-lg shadow"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    :class="isRTL ? 'ml-1.5' : 'mr-1.5'"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12s-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"
                                    />
                                </svg>
                                {{ t('myProfile.shareProfile') }}
                            </ResponsiveButton>
                        </div>
                    </div>
                    <div class="px-4 sm:px-6 lg:px-8 pb-8">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-2 space-y-6">
                                <div class="bg-white p-6 rounded-xl shadow-sm">
                                    <div
                                        class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6"
                                    >
                                        <div>
                                            <div class="flex items-center mb-1 gap-2">
                                                <h1 class="text-2xl font-bold text-gray-800">{{ teacher.name }}</h1>
                                            </div>
                                            <p class="text-sm text-gray-500">@{{ teacher.username }}</p>
                                            <p class="text-sm text-gray-500">{{ teacher.email }}</p>
                                        </div>
                                        <div class="flex gap-4 sm:gap-6 mt-4 sm:mt-0 text-center">
                                            <div>
                                                <p class="text-xl font-semibold text-gray-700">
                                                    {{ dashboard?.teacher_data?.total_exams || 0 }}
                                                </p>
                                                <p class="text-xs text-gray-500 uppercase">
                                                    {{ t('myProfile.stats.ayhoots') }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xl font-semibold text-gray-700">
                                                    {{ dashboard?.teacher_data?.total_courses || 0 }}
                                                </p>
                                                <p class="text-xs text-gray-500 uppercase">
                                                    {{ t('myProfile.stats.courses') }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xl font-semibold text-gray-700">
                                                    {{ dashboard?.teacher_data?.total_students || 0 }}
                                                </p>
                                                <p class="text-xs text-gray-500 uppercase">
                                                    {{ t('myProfile.stats.tutees') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white p-6 rounded-xl shadow-sm">
                                    <label class="block text-lg font-semibold text-gray-800 mb-4">{{
                                        t('myProfile.aboutMe.title')
                                    }}</label>
                                    <div class="flex flex-col sm:flex-row gap-4 mb-4">
                                        <!-- Institutes have a single name; teachers have first/last name. -->
                                        <AyInput
                                            v-if="$role === 'institute'"
                                            v-model="formData.name"
                                            type="text"
                                            :placeholder="t('myProfile.aboutMe.namePlaceholder')"
                                        />
                                        <template v-else>
                                            <AyInput
                                                v-model="formData.first_name"
                                                type="text"
                                                :placeholder="t('myProfile.aboutMe.firstNamePlaceholder')"
                                            />
                                            <AyInput
                                                v-model="formData.last_name"
                                                type="text"
                                                :placeholder="t('myProfile.aboutMe.lastNamePlaceholder')"
                                            />
                                        </template>
                                    </div>
                                    <textarea
                                        v-model="formData.description"
                                        rows="4"
                                        class="w-full p-3 border-2 border-gray-200 rounded-md focus:outline-none focus:border-indigo-400 text-sm"
                                        :placeholder="t('myProfile.aboutMe.descriptionPlaceholder')"
                                    ></textarea>
                                </div>
                                <div class="bg-white p-6 rounded-xl shadow-sm">
                                    <label class="block text-lg font-semibold text-gray-800 mb-2">{{
                                        t('myProfile.website.title')
                                    }}</label>
                                    <AyInput
                                        v-model="formData.website"
                                        type="url"
                                        :placeholder="t('myProfile.website.placeholder')"
                                    />
                                </div>
                            </div>
                            <div class="lg:col-span-1 space-y-6">
                                <div class="bg-white p-6 rounded-xl shadow-sm">
                                    <div class="flex justify-between items-center mb-3">
                                        <h3 class="text-lg font-semibold text-gray-800">
                                            {{ t('myProfile.institutes.title') }}
                                        </h3>
                                        <ResponsiveButton
                                            type="light-blue"
                                            @click="openAddInstituteModal"
                                            class="text-sm px-3 py-1.5"
                                            >{{ t('myProfile.institutes.add') }}</ResponsiveButton
                                        >
                                    </div>
                                    <ul
                                        v-if="teacher.institutes?.length"
                                        class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar"
                                    >
                                        <li
                                            v-for="institute in teacher.institutes"
                                            :key="institute.id"
                                            class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border text-sm border-gray-200 hover:bg-gray-100"
                                        >
                                            <span class="text-sm text-gray-700">{{ institute.name }}</span>
                                            <ResponsiveButton
                                                type="light-red"
                                                @click="removeInstitute(institute.id)"
                                                class="p-1 px-2 text-xs"
                                                >{{ t('myProfile.institutes.remove') }}</ResponsiveButton
                                            >
                                        </li>
                                    </ul>
                                    <p v-else class="text-sm bg-gray-50 rounded-lg text-gray-500 p-4 text-center">
                                        {{ t('myProfile.institutes.empty') }}
                                    </p>
                                </div>
                                <div class="bg-white p-6 rounded-xl shadow-sm">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-3">
                                        {{ t('myProfile.account.title') }}
                                    </h3>
                                    <div class="space-y-4">
                                        <AyInput
                                            v-model="passwordForm.old_password"
                                            type="password"
                                            :label="t('myProfile.account.currentPasswordLabel')"
                                            :placeholder="t('myProfile.account.currentPasswordPlaceholder')"
                                        />
                                        <AyInput
                                            v-model="passwordForm.new_password"
                                            type="password"
                                            :label="t('myProfile.account.newPasswordLabel')"
                                            :placeholder="t('myProfile.account.newPasswordPlaceholder')"
                                        />
                                        <AyInput
                                            v-model="passwordForm.confirm_password"
                                            type="password"
                                            :label="t('myProfile.account.confirmPasswordLabel')"
                                            :placeholder="t('myProfile.account.confirmPasswordPlaceholder')"
                                        />
                                        <ResponsiveButton
                                            type="dash-blue"
                                            @click="changePassword"
                                            class="w-full px-4 py-2 text-sm"
                                            >{{ t('myProfile.account.changePassword') }}</ResponsiveButton
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-8 flex" :class="isRTL ? 'justify-start' : 'justify-end'">
                            <ResponsiveButton
                                type="dash-blue"
                                @click="updateProfile"
                                class="px-8 py-3 text-base font-semibold"
                                >{{ t('myProfile.updateProfile') }}</ResponsiveButton
                            >
                        </div>
                    </div>
                </div>
        <template #overlays>
        <Modal v-model="showAddInstituteModal" :title="t('myProfile.addInstituteModal.title')" size="sm">
            <div class="mb-4">
                <AyInput
                    v-model="instituteSearchTerm"
                    type="text"
                    :label="t('myProfile.addInstituteModal.searchLabel')"
                    :placeholder="t('myProfile.addInstituteModal.searchPlaceholder')"
                />
            </div>
            <div class="max-h-60 overflow-y-auto border-2 border-gray-200 rounded-md custom-scrollbar">
                <ul v-if="filteredInstitutes.length">
                    <li
                        v-for="inst in filteredInstitutes"
                        :key="inst.id"
                        @click="selectInstitute(inst)"
                        class="p-3 hover:bg-gray-100 cursor-pointer text-sm text-gray-500 border-b border-gray-100 last:border-b-0"
                    >
                        {{ inst.name }}
                    </li>
                </ul>
                <p v-else class="p-3 text-sm text-gray-500 text-center">
                    {{
                        instituteSearchTerm
                            ? t('myProfile.addInstituteModal.emptySearch')
                            : t('myProfile.addInstituteModal.emptyInitial')
                    }}
                </p>
            </div>
            <template #footer>
                <ResponsiveButton type="light-gray" @click="closeAddInstituteModal" class="px-4 py-2 text-sm"
                    >{{ t('myProfile.addInstituteModal.cancel') }}
                </ResponsiveButton>
                <ResponsiveButton
                    type="light-blue"
                    @click="submitAddInstitute"
                    class="px-4 py-2 text-sm"
                    :disabled="!selectedInstitute"
                    >{{ t('myProfile.addInstituteModal.sendRequest') }}</ResponsiveButton
                >
            </template>
        </Modal>
        <div
            v-if="showToast"
            class="fixed bottom-5 p-4 rounded-md shadow-lg text-white z-[70] transition-all duration-300 ease-in-out"
            :class="[toastType === 'success' ? 'bg-gray-700' : 'bg-red-600', isRTL ? 'left-6' : 'right-6']"
        >
            {{ toastMessage }}
        </div>
        </template>
    </InstructorLayout>
</template>

<script>
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import Modal from '@/components/shared/Modal.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import { apiCall } from "@/services/apiService"
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

export default {
    name: 'MyProfile',
    components: { InstructorLayout, ResponsiveButton, Modal, AyInput },
    setup() {
        const { t, locale } = useI18n()
        const isRTL = computed(() => locale.value === 'fa')
        return { t, isRTL }
    },
    data() {
        return {
            isLoading: true,
            teacher: null,
            dashboard: null,
            formData: {
                name: '',
                first_name: '',
                last_name: '',
                description: '',
                website: '',
            },
            passwordForm: {
                old_password: '',
                new_password: '',
                confirm_password: '',
            },
            selectedFiles: {
                avatar: null,
                cover: null,
            },
            showAddInstituteModal: false,
            instituteSearchTerm: '',
            availableInstitutes: [],
            selectedInstitute: null,
            showToast: false,
            toastMessage: '',
            toastType: 'success',
            tempImageUrls: {
                avatar: null,
                cover: null,
            },
        }
    },
    computed: {
        teacherInitials() {
            if (!this.teacher?.name) return '?'
            const parts = this.teacher.name.trim().split(' ').filter(Boolean)
            return parts.length === 1
                ? parts[0][0].toUpperCase()
                : (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
        },
        filteredInstitutes() {
            const affiliatedIds = new Set(this.teacher?.institutes?.map((i) => i.id))
            const excludeAffiliated = (inst) => !affiliatedIds.has(inst.id)
            const filtered = this.availableInstitutes.filter(excludeAffiliated)
            if (!this.instituteSearchTerm) return filtered
            const term = this.instituteSearchTerm.toLowerCase()
            return filtered.filter((i) => i.name.toLowerCase().includes(term))
        },
    },
    methods: {
        async fetchProfileData() {
            this.isLoading = true
            try {
                const response = await apiCall('GET', `/api/instructor/dashboard`)
                this.dashboard = response.dashboard
                this.teacher = response.teacher
                if (this.teacher) {
                    this.formData = {
                        name: this.teacher.name || '',
                        first_name: this.teacher.first_name || '',
                        last_name: this.teacher.last_name || '',
                        description: this.teacher.description || '',
                        website: this.teacher.website || '',
                    }
                } else {
                    throw new Error(this.t('myProfile.notifications.dataNotFound'))
                }
            } catch (error) {
                console.error('Profile fetch error:', error)
                this.showToastMessage(this.t('myProfile.notifications.loadProfileFailed'), 'error')
            } finally {
                this.isLoading = false
            }
        },
        async fetchAllInstitutes() {
            try {
                const response = await apiCall('GET', `/api/instructor/get-all-institutes`)
                // MODIFIED: Correctly access the nested 'data' array from the paginated response
                this.availableInstitutes = response.institutes?.data || []
            } catch (error) {
                this.showToastMessage(this.t('myProfile.notifications.loadInstitutesFailed'), 'error')
            }
        },
        async updateProfile() {
            if (this.tempImageUrls.avatar) URL.revokeObjectURL(this.tempImageUrls.avatar)
            if (this.tempImageUrls.cover) URL.revokeObjectURL(this.tempImageUrls.cover)
            this.tempImageUrls = { avatar: null, cover: null }

            this.showToastMessage(this.t('myProfile.notifications.updatingProfile'), 'success')
            try {
                const formData = new FormData()
                // Institutes have a single `name`; teachers have first/last name.
                if (this.$role === 'institute') {
                    formData.append('name', this.formData.name)
                } else {
                    formData.append('first_name', this.formData.first_name)
                    formData.append('last_name', this.formData.last_name)
                }
                formData.append('description', this.formData.description)
                formData.append('website', this.formData.website)
                formData.append('_method', 'POST')
                if (this.selectedFiles.avatar) {
                    formData.append('avatar', this.selectedFiles.avatar)
                }
                if (this.selectedFiles.cover) {
                    formData.append('cover', this.selectedFiles.cover)
                }
                const response = await apiCall('POST', `/api/instructor/profile/update`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                this.teacher = response.teacher
                this.selectedFiles.avatar = null
                this.selectedFiles.cover = null
                this.showToastMessage(this.t('myProfile.notifications.updateSuccess'), 'success')
            } catch (error) {
                const message = error.response?.data?.message || this.t('myProfile.notifications.updateFailed')
                this.showToastMessage(message, 'error')
            }
        },
        handleImageUpload(type, event) {
            const file = event.target.files[0]
            if (!file) return
            if (file.size > 5 * 1024 * 1024) {
                this.showToastMessage(this.t('myProfile.notifications.fileTooLarge'), 'error')
                return
            }
            this.selectedFiles[type] = file
            if (this.tempImageUrls[type]) {
                URL.revokeObjectURL(this.tempImageUrls[type])
            }
            const tempUrl = URL.createObjectURL(file)
            this.tempImageUrls[type] = tempUrl
            if (this.teacher) {
                this.teacher[type] = tempUrl
            }
            const messageKey = type === 'avatar' ? 'avatarSelected' : 'coverSelected'
            this.showToastMessage(this.t(`myProfile.notifications.${messageKey}`), 'success')
        },
        async shareProfile() {
            try {
                await navigator.clipboard.writeText(window.location.href)
                this.showToastMessage(this.t('myProfile.notifications.linkCopied'), 'success')
            } catch (error) {
                this.showToastMessage(this.t('myProfile.notifications.linkCopyFailed'), 'error')
            }
        },
        openAddInstituteModal() {
            this.selectedInstitute = null
            this.instituteSearchTerm = ''
            this.showAddInstituteModal = true
            if (!this.availableInstitutes.length) {
                this.fetchAllInstitutes()
            }
        },
        closeAddInstituteModal() {
            this.showAddInstituteModal = false
        },
        selectInstitute(institute) {
            this.selectedInstitute = institute
            this.instituteSearchTerm = institute.name
        },
        async submitAddInstitute() {
            if (!this.selectedInstitute) {
                return this.showToastMessage(this.t('myProfile.notifications.selectInstitute'), 'error')
            }
            try {
                await apiCall('POST', `/api/instructor/institutes/request-join`, {
                    institute_id: this.selectedInstitute.id,
                })
                this.showToastMessage(
                    this.t('myProfile.notifications.requestSent', { name: this.selectedInstitute.name }),
                    'success'
                )
                this.closeAddInstituteModal()
            } catch (error) {
                const message = error.response?.data?.message || this.t('myProfile.notifications.requestFailed')
                this.showToastMessage(message, 'error')
            }
        },
        async removeInstitute(instituteId) {
            try {
                await apiCall('GET', `/api/instructor/institutes/delete/${instituteId}`)
                this.teacher.institutes = this.teacher.institutes.filter((i) => i.id !== instituteId)
                this.showToastMessage(this.t('myProfile.notifications.removeSuccess'), 'success')
            } catch (error) {
                this.showToastMessage(this.t('myProfile.notifications.removeFailed'), 'error')
            }
        },
        async changePassword() {
            const { old_password, new_password, confirm_password } = this.passwordForm
            if (!old_password || !new_password || !confirm_password) {
                return this.showToastMessage(this.t('myProfile.notifications.passwordFieldsRequired'), 'error')
            }
            if (new_password !== confirm_password) {
                return this.showToastMessage(this.t('myProfile.notifications.passwordsNoMatch'), 'error')
            }
            if (new_password.length < 6) {
                return this.showToastMessage(this.t('myProfile.notifications.passwordTooShort'), 'error')
            }
            try {
                await apiCall('POST', `/api/instructor/profile/change-password`, {
                    old_password: this.passwordForm.old_password,
                    new_password: this.passwordForm.new_password,
                    new_password_confirmation: this.passwordForm.confirm_password,
                })
                this.showToastMessage(this.t('myProfile.notifications.passwordChangeSuccess'), 'success')
                this.passwordForm = { old_password: '', new_password: '', confirm_password: '' }
            } catch (error) {
                const message = error.response?.data?.message || this.t('myProfile.notifications.passwordChangeFailed')
                this.showToastMessage(message, 'error')
            }
        },
        showToastMessage(message, type = 'success') {
            this.toastMessage = message
            this.toastType = type
            this.showToast = true
            setTimeout(() => (this.showToast = false), 4000)
        },
    },
    mounted() {
        this.fetchProfileData()
    },
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #c4c4c4;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}
main {
    padding-top: 4rem;
}
.relative.mb-16 {
    margin-bottom: 4.5rem;
}
</style>
