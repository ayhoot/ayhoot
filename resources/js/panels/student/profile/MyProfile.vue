<template>
    <PageWrapper>
        <div v-if="isLoading" class="flex items-center justify-center h-[calc(100vh-4rem)]">
            <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-indigo-500"></div>
        </div>
        <div v-else-if="student" class="w-full">
            <!-- Cover and Profile Image Section -->
            <div class="relative mb-16">
                <div class="h-48 sm:h-64 md:h-72 bg-cover bg-center bg-gray-200"
                    :style="student.cover ? { backgroundImage: `url(${student.cover})` } : {}"></div>
                <div class="absolute top-3" :class="isRTL ? 'right-4' : 'left-4'">
                    <input type="file" ref="coverImageInput" @change="handleImageUpload('cover', $event)" class="hidden"
                        accept="image/*" />
                    <ResponsiveButton type="light-gray" @click="$refs.coverImageInput.click()"
                        class="p-2 text-sm flex items-center bg-white/80 hover:bg-white rounded-lg shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="isRTL ? 'ml-1.5' : 'mr-1.5'"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        {{ t('myProfile.uploadCover') }}
                    </ResponsiveButton>
                </div>
                <div class="absolute -bottom-12 left-1/2 -translate-x-1/2 sm:translate-x-0 sm:-bottom-8 flex flex-col items-center sm:items-start"
                    :class="isRTL ? 'sm:right-8' : 'sm:left-8'">
                    <div class="relative group">
                        <img v-if="student.avatar" :src="student.avatar" :alt="t('myProfile.profilePictureAlt')"
                            class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-white shadow-sm object-cover" />
                        <div v-else
                            class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-white shadow-sm bg-indigo-500 flex items-center justify-center text-white text-4xl sm:text-5xl font-bold">
                            {{ studentInitials }}
                        </div>
                        <input type="file" ref="profileImageInput" @change="handleImageUpload('avatar', $event)"
                            class="hidden" accept="image/*" />
                        <button @click="$refs.profileImageInput.click()"
                            class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="absolute top-4" :class="isRTL ? 'left-4' : 'right-4'">
                    <ResponsiveButton type="light-gray" @click="shareProfile"
                        class="p-2 text-sm flex items-center bg-white/80 hover:bg-white rounded-lg shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="isRTL ? 'ml-1.5' : 'mr-1.5'"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12s-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                        </svg>
                        {{ t('myProfile.shareProfile') }}
                    </ResponsiveButton>
                </div>
            </div>
            <!-- Profile Details and Form Section -->
            <div class="px-4 sm:px-6 lg:px-8 pb-8">
                <div class="max-w-3xl mx-auto space-y-6">
                    <!-- Student Info & Stats -->
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                            <div>
                                <div class="flex items-center mb-1 gap-2">
                                    <h1 class="text-2xl font-bold text-gray-800">{{ student.name }}</h1>
                                </div>
                                <p class="text-sm text-gray-500">@{{ student.username }}</p>
                                <p class="text-sm text-gray-500">{{ student.email }}</p>
                            </div>
                            <div class="flex gap-4 sm:gap-6 mt-4 sm:mt-0 text-center">
                                <div>
                                    <p class="text-xl font-semibold text-gray-700">
                                        {{ dashboard?.student_data?.total_exams_attended || 0 }}
                                    </p>
                                    <p class="text-xs text-gray-500 uppercase">
                                        {{ t('myProfile.stats.ayhoots') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xl font-semibold text-gray-700">
                                        {{ dashboard?.student_data?.total_courses || 0 }}
                                    </p>
                                    <p class="text-xs text-gray-500 uppercase">
                                        {{ t('myProfile.stats.courses') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- About Me Form -->
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <label class="block text-lg font-semibold text-gray-800 mb-4">{{
                            t('myProfile.aboutMe.title')
                        }}</label>
                        <div class="flex flex-col sm:flex-row gap-4 mb-4">
                            <input type="text" v-model="formData.first_name"
                                class="w-full p-3 border-2 border-gray-200 rounded-md focus:outline-none focus:border-indigo-400 text-sm"
                                :placeholder="t('myProfile.aboutMe.firstNamePlaceholder')" />
                            <input type="text" v-model="formData.last_name"
                                class="w-full p-3 border-2 border-gray-200 rounded-md focus:outline-none focus:border-indigo-400 text-sm"
                                :placeholder="t('myProfile.aboutMe.lastNamePlaceholder')" />
                        </div>
                        <textarea v-model="formData.description" rows="4"
                            class="w-full p-3 border-2 border-gray-200 rounded-md focus:outline-none focus:border-indigo-400 text-sm"
                            :placeholder="t('myProfile.aboutMe.descriptionPlaceholder')"></textarea>
                    </div>
                    <!-- Website Form -->
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <label class="block text-lg font-semibold text-gray-800 mb-2">{{
                            t('myProfile.website.title')
                        }}</label>
                        <input type="url" v-model="formData.website"
                            class="w-full p-3 border-2 border-gray-200 rounded-md focus:outline-none focus:border-indigo-400 text-sm"
                            :placeholder="t('myProfile.website.placeholder')" />
                    </div>
                </div>
                <div class="mt-8 flex max-w-3xl mx-auto" :class="isRTL ? 'justify-start' : 'justify-end'">
                    <ResponsiveButton type="dash-blue" @click="updateProfile" class="px-8 py-3 text-base font-semibold">
                        {{ t('myProfile.updateProfile') }}</ResponsiveButton>
                </div>
            </div>
        </div>
        <!-- Toast Notification -->
        <div v-if="showToast"
            class="fixed bottom-5 p-4 rounded-md shadow-lg text-white z-[70] transition-all duration-300 ease-in-out"
            :class="[toastType === 'success' ? 'bg-gray-700' : 'bg-red-600', isRTL ? 'left-6' : 'right-6']">
            {{ toastMessage }}
        </div>
    </PageWrapper>
</template>

<script>
import PageWrapper from '@/components/shared/PageWrapper.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import { apiCall } from '@/services/apiService'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

export default {
    name: 'MyProfile',
    components: { PageWrapper, ResponsiveButton },
    setup() {
        const { t, locale } = useI18n()
        const isRTL = computed(() => locale.value === 'fa')
        return { t, isRTL }
    },
    data() {
        return {
            isLoading: true,
            student: null,
            dashboard: null,
            formData: {
                first_name: '',
                last_name: '',
                description: '',
                website: '',
            },
            selectedFiles: {
                avatar: null,
                cover: null,
            },
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
        studentInitials() {
            if (!this.student?.name) return '?'
            const parts = this.student.name.trim().split(' ').filter(Boolean)
            return parts.length === 1
                ? parts[0][0].toUpperCase()
                : (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
        },
    },
    methods: {
        async fetchProfileData() {
            this.isLoading = true
            try {
                // Fetch data from the student dashboard endpoint
                const response = await apiCall('GET', '/api/student/dashboard')
                this.dashboard = response.dashboard
                this.student = response.dashboard?.student_data?.student
                if (this.student) {
                    this.formData = {
                        first_name: this.student.first_name || '',
                        last_name: this.student.last_name || '',
                        description: this.student.description || '',
                        website: this.student.website || '',
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
        async updateProfile() {
            // Revoke temporary blob URLs to prevent memory leaks
            if (this.tempImageUrls.avatar) URL.revokeObjectURL(this.tempImageUrls.avatar)
            if (this.tempImageUrls.cover) URL.revokeObjectURL(this.tempImageUrls.cover)
            this.tempImageUrls = { avatar: null, cover: null }

            this.showToastMessage(this.t('myProfile.notifications.updatingProfile'), 'success')
            try {
                const formData = new FormData()
                formData.append('first_name', this.formData.first_name)
                formData.append('last_name', this.formData.last_name)
                formData.append('description', this.formData.description)
                formData.append('website', this.formData.website)
                formData.append('_method', 'POST') // Use POST with method spoofing for file uploads

                if (this.selectedFiles.avatar) {
                    formData.append('avatar', this.selectedFiles.avatar)
                }
                if (this.selectedFiles.cover) {
                    formData.append('cover', this.selectedFiles.cover)
                }

                // Update profile using the student endpoint
                const response = await apiCall('POST', '/api/student/profile/update', formData)

                this.student = response.student // Assuming the API returns the updated student object
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
                // 5MB Limit
                this.showToastMessage(this.t('myProfile.notifications.fileTooLarge'), 'error')
                return
            }
            this.selectedFiles[type] = file
            // Revoke the old temporary URL if it exists
            if (this.tempImageUrls[type]) {
                URL.revokeObjectURL(this.tempImageUrls[type])
            }
            // Create a new temporary URL for preview
            const tempUrl = URL.createObjectURL(file)
            this.tempImageUrls[type] = tempUrl
            if (this.student) {
                this.student[type] = tempUrl // Update the student object to show the preview
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
