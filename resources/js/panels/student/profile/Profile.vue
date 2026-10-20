<template>
    <PageWrapper>
                <!-- Skeleton Loader -->
                <div v-if="isLoading">
                    <div class="w-full relative mt-15 sm:mt-0">
                        <div class="w-full lg:h-[30vh] h-[15vh] bg-gray-200 animate-pulse"></div>
                    </div>
                    <div class="w-full lg:px-12 px-6 -mt-12 sm:-mt-16 mb-8">
                        <div
                            class="bg-white p-4 sm:p-6 rounded-xl shadow-lg flex flex-col lg:flex-row items-center justify-between animate-pulse"
                        >
                            <div class="flex flex-col sm:flex-row items-center text-center sm:text-left">
                                <div
                                    class="w-24 h-24 sm:w-32 sm:h-32 rounded-full bg-gray-300 border-4 border-white -mt-12 sm:-mt-16 lg:-mt-20 mb-2 sm:mb-0"
                                    :class="isRTL ? 'sm:ml-6' : 'sm:mr-6'"
                                ></div>
                                <div class="space-y-2">
                                    <div class="h-8 bg-gray-300 rounded w-48"></div>
                                    <div class="h-4 bg-gray-300 rounded w-32"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-8 mt-4 lg:mt-0">
                                <div class="h-10 bg-gray-200 rounded w-16"></div>
                                <div class="h-10 bg-gray-200 rounded w-16"></div>
                                <div class="h-10 bg-gray-200 rounded w-16"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="flex items-center justify-center h-full p-8 text-center">
                    <div class="bg-white p-8 rounded-xl shadow-md">
                        <h3 class="text-xl font-bold text-red-600">{{ t('publicProfile.error.title') }}</h3>
                        <p class="text-gray-600 mt-2">{{ error }}</p>
                    </div>
                </div>

                <!-- Actual Profile Content -->
                <div v-else-if="user">
                    <!-- "Invite to Tutors" Banner -->
                    <div
                        v-if="isTeacherProfile && !isLoading && showAddToTutorsBanner"
                        class="sticky top-20 z-30 mx-4 sm:mx-6 lg:mx-12"
                    >
                        <div
                            class="flex items-center justify-between p-3 bg-blue-600/50 backdrop-blur-sm text-white rounded-sm shadow-sm"
                        >
                            <span class="font-semibold text-sm">{{ t('publicProfile.actions.addToTutors') }}</span>
                            <div class="flex items-center gap-2">
                                <ResponsiveButton @click="addToTutors" type="light-blue" class="p-2">
                                    {{ t('publicProfile.actions.add') }}
                                </ResponsiveButton>
                                <button @click="dismissAddToTutorsBanner" class="p-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Header Section -->
                    <div
                        class="w-full relative sm:mt-0"
                        :class="isTeacherProfile ? (showAddToTutorsBanner ? '-mt-1' : 'mt-15') : 'mt-15'"
                    >
                        <img
                            :src="getAssetUrl(user.cover, true)"
                            :alt="t('publicProfile.coverImageAlt', { name: user.name })"
                            class="w-full lg:h-[30vh] h-[15vh] object-cover bg-gray-200"
                        />
                    </div>

                    <!-- User Info & Stats - Overlapping -->
                    <div class="w-full lg:px-12 px-6 mb-8 -mt-12 sm:-mt-16">
                        <div
                            class="flex flex-col lg:flex-row items-center lg:items-end justify-between bg-white p-4 sm:p-6 rounded-xl shadow-lg relative"
                        >
                            <!-- Left side: Image and Name/Website -->
                            <div class="flex flex-col sm:flex-row items-center text-center sm:text-left">
                                <img
                                    :src="getAssetUrl(user.image, true)"
                                    :alt="user.name"
                                    class="w-24 h-24 sm:w-32 sm:h-32 rounded-full object-cover border-4 border-white shadow-md -mt-12 sm:-mt-16 lg:-mt-20 mb-2 sm:mb-0 bg-gray-200"
                                    :class="isRTL ? 'sm:ml-6' : 'sm:mr-6'"
                                />
                                <div class="sm:pt-2">
                                    <div class="flex items-center justify-center sm:justify-start gap-1">
                                        <h3 class="text-xl sm:text-2xl font-bold text-gray-800">{{ user.name }}</h3>
                                        <DashVerified v-if="user.verified" class="w-5 h-5 sm:w-6 sm:h-6" />
                                    </div>
                                    <a
                                        v-if="user.website"
                                        :href="user.website"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-xs sm:text-sm font-semibold text-blue-600 hover:underline"
                                    >
                                        {{ user.website }}
                                    </a>
                                    <p v-else class="text-xs sm:text-sm text-gray-500">
                                        {{ t('publicProfile.noWebsite') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Right side: Stats -->
                            <div class="flex items-center gap-6 sm:gap-8 mt-4 lg:mt-0 text-gray-700">
                                <div class="text-center">
                                    <p class="text-lg sm:text-xl font-bold">{{ user.contentCount || 0 }}</p>
                                    <h3 class="text-xs sm:text-sm text-gray-500 uppercase">
                                        {{ t('publicProfile.stats.content') }}
                                    </h3>
                                </div>
                                <div class="text-center">
                                    <p class="text-lg sm:text-xl font-bold">{{ user.plays || 0 }}</p>
                                    <h3 class="text-xs sm:text-sm text-gray-500 uppercase">
                                        {{ t('publicProfile.stats.plays') }}
                                    </h3>
                                </div>
                                <div class="text-center">
                                    <p class="text-lg sm:text-xl font-bold">{{ user.tutees || 0 }}</p>
                                    <h3 class="text-xs sm:text-sm text-gray-500 uppercase">
                                        {{ t('publicProfile.stats.tutees') }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="px-4 sm:px-6 lg:px-12">
                        <div class="border-b border-gray-200">
                            <ul
                                class="flex flex-wrap -mb-px text-sm font-medium text-center"
                                :class="isRTL ? 'items-end' : ''"
                            >
                                <li :class="isRTL ? 'ml-2' : 'mr-2'">
                                    <button
                                        @click="activeTab = 'content'"
                                        :class="
                                            activeTab === 'content'
                                                ? 'text-blue-600 border-blue-600'
                                                : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'
                                        "
                                        class="inline-block p-4 border-b-2 rounded-t-lg font-semibold transition-colors duration-150"
                                    >
                                        {{ t('publicProfile.tabs.content') }}
                                    </button>
                                </li>
                                <li :class="isRTL ? 'ml-2' : 'mr-2'">
                                    <button
                                        @click="activeTab = 'about'"
                                        :class="
                                            activeTab === 'about'
                                                ? 'text-blue-600 border-blue-600'
                                                : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'
                                        "
                                        class="inline-block p-4 border-b-2 rounded-t-lg font-semibold transition-colors duration-150"
                                    >
                                        {{ t('publicProfile.tabs.about') }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="px-4 sm:px-6 lg:px-12 py-6">
                        <div v-show="activeTab === 'content'">
                            <div v-if="user.courses?.length > 0 || user.ayhoots?.length > 0">
                                <div v-if="user.courses?.length > 0" class="mb-8">
                                    <CategoriesMassCourseProps :MassCourses="user.courses" />
                                </div>
                                <div v-if="user.ayhoots?.length > 0">
                                    <CategoriesMassProps :MassAyhoots="user.ayhoots" />
                                </div>
                            </div>
                            <div v-else class="py-12">
                                <div
                                    class="flex flex-col items-center justify-center text-center p-8 rounded-xl shadow-sm"
                                >
                                    <svg
                                        class="mx-auto h-12 w-12 text-gray-400 mb-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                        />
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900">
                                        {{ t('publicProfile.empty.title') }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">{{ t('publicProfile.empty.description') }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-show="activeTab === 'about'">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-2 p-6 rounded-xl shadow-sm">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-3">
                                        {{ t('publicProfile.aboutSection.title', { name: user.name }) }}
                                    </h3>
                                    <p class="text-sm text-gray-600 leading-relaxed">
                                        {{ user.about || t('publicProfile.aboutSection.noInfo') }}
                                    </p>
                                </div>
                                <div class="lg:col-span-1 p-6 rounded-xl shadow-sm">
                                    <!-- Teacher Profile View -->
                                    <div v-if="isTeacherProfile">
                                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                            {{ t('publicProfile.teacher.institutesTitle') }}
                                        </h3>
                                        <div
                                            v-if="user.institutes?.length > 0"
                                            class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar"
                                        >
                                            <RouterLink
                                                v-for="institute in user.institutes"
                                                :key="institute.id"
                                                :to="`/institute/profile/${institute.id}`"
                                                class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:border-blue-400 transition-colors duration-150"
                                            >
                                                <img
                                                    :src="getAssetUrl(institute.avatar, true)"
                                                    :alt="institute.name"
                                                    class="w-8 h-8 rounded-full object-cover bg-gray-300"
                                                />
                                                <span class="text-sm font-medium text-gray-700">{{
                                                    institute.name
                                                }}</span>
                                            </RouterLink>
                                        </div>
                                        <p v-else class="text-sm text-gray-500 py-4 text-center">
                                            {{ t('publicProfile.teacher.noInstitutes') }}
                                        </p>
                                    </div>

                                    <!-- Institute Profile View -->
                                    <div v-else>
                                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                            {{ t('publicProfile.institute.teachersTitle') }}
                                        </h3>
                                        <div
                                            v-if="user.teachers?.length > 0"
                                            class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar"
                                        >
                                            <RouterLink
                                                v-for="teacher in user.teachers"
                                                :key="teacher.id"
                                                :to="`/teacher/profile/${teacher.id}`"
                                                class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:border-blue-400 transition-colors duration-150"
                                            >
                                                <img
                                                    :src="getAssetUrl(teacher.avatar, true)"
                                                    :alt="teacher.name"
                                                    class="w-8 h-8 rounded-full object-cover bg-gray-300"
                                                />
                                                <span class="text-sm font-medium text-gray-700">{{
                                                    teacher.name
                                                }}</span>
                                            </RouterLink>
                                        </div>
                                        <p v-else class="text-sm text-gray-500 py-4 text-center">
                                            {{ t('publicProfile.institute.noTeachers') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    </PageWrapper>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { apiCall } from '@/services/apiService'
import PageWrapper from '@/components/shared/PageWrapper.vue'
import DashVerified from '@/components/atoms/icons/DashVerified.vue'
import CategoriesMassProps from '@/components/discover/CategoriesMassProps.vue'
import CategoriesMassCourseProps from '@/components/discover/CategoriesMassCourseProps.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

const { t, locale } = useI18n()
const isRTL = computed(() => locale.value === 'fa')
const route = useRoute()
const isLoading = ref(true)
const error = ref(null)
const user = ref(null)
const activeTab = ref('content')
const showAddToTutorsBanner = ref(true)

const profileType = ref('teacher')
const isTeacherProfile = computed(() => profileType.value === 'teacher')
const isInstituteProfile = computed(() => profileType.value === 'institute')

const getAssetUrl = (path, isProfileAsset = false) => {
    const fallback = isProfileAsset ? '/imgHolders/profile.svg' : '/imgHolders/CardHolderImage.jpg'
    if (!path) return fallback
    if (path.startsWith('http')) return path
    return `/uploads/${path.replace(/\\/g, '')}`
}

const normalizeData = (data, type) => {
    if (type === 'teacher' && data.teacher) {
        return {
            name: data.teacher.name || t('publicProfile.unnamedTeacher'),
            username: data.teacher.username,
            verified: !!data.teacher.verified_at,
            image: data.teacher.avatar,
            cover: data.teacher.cover,
            website: data.teacher.website,
            about: data.teacher.description,
            plays: data.plays_count || 0,
            tutees: data.students_count || 0,
            institutes: data.teacher.institutes || [],
            ayhoots: data.teacher.exams || [],
            courses: data.teacher.courses || [],
            contentCount: (data.teacher.exams?.length || 0) + (data.teacher.courses?.length || 0),
        }
    }
    if (type === 'institute' && data.institute) {
        return {
            name: data.institute.name || t('publicProfile.unnamedInstitute'),
            username: data.institute.username,
            verified: !!data.institute.verified_at,
            image: data.institute.avatar,
            cover: data.institute.cover,
            website: data.institute.website,
            about: data.institute.description,
            plays: data.plays_count || 0,
            tutees: data.students_count || 0,
            teachers: data.institute.teachers || [],
            ayhoots: data.institute.exams || [],
            courses: data.institute.courses || [],
            contentCount: (data.institute.exams?.length || 0) + (data.institute.courses?.length || 0),
        }
    }
    return null
}

const fetchProfileData = async (id, path) => {
    isLoading.value = true
    error.value = null
    user.value = null
    showAddToTutorsBanner.value = true // Reset banner on new profile load

    try {
        let dataType = 'teacher'
        if (route.query.type === 'institute' || path.includes('/institute/profile') || path.includes('/student/institute/profile')) {
            dataType = 'institute'
        } else if (route.query.type === 'teacher' || path.includes('/teacher/profile') || path.includes('/student/teacher/profile')) {
            dataType = 'teacher'
        }

        let apiUrl = `/api/student/${dataType}-detail/${id}`
        let response = null

        try {
            response = await apiCall('GET', apiUrl)
            if (!response.teacher && !response.institute) {
                throw new Error('Not found')
            }
        } catch (e) {
            if (!route.query.type) {
                const altType = dataType === 'teacher' ? 'institute' : 'teacher'
                response = await apiCall('GET', `/api/student/${altType}-detail/${id}`)
                dataType = altType
            } else {
                throw e
            }
        }

        if (response.institute && !response.teacher) {
            dataType = 'institute'
        } else if (response.teacher && !response.institute) {
            dataType = 'teacher'
        }

        profileType.value = dataType
        const normalized = normalizeData(response, dataType)

        if (!normalized) {
            throw new Error(t('publicProfile.error.invalidData'))
        }
        user.value = normalized
    } catch (err) {
        console.error('Profile page error:', err)
        error.value = t('publicProfile.error.loadFailed')
    } finally {
        isLoading.value = false
    }
}

const dismissAddToTutorsBanner = () => {
    showAddToTutorsBanner.value = false
}

const addToTutors = async () => {
    // Placeholder for API call logic
    console.log(`Adding teacher ${user.value.name} to tutors...`)
    await alertDialog({ message: t('publicProfile.actions.added') })
    dismissAddToTutorsBanner()
}

watch(
    [() => route.params.id, () => route.query.type, () => route.path],
    ([newId]) => {
        if (newId) {
            fetchProfileData(newId, route.path)
        }
    },
    { immediate: true }
)
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
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    50% {
        opacity: 0.5;
    }
}
</style>
