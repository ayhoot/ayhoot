<template>
    <PublicLayout>
        <!-- Skeleton Loader -->
        <ProfileSkeleton v-if="isLoading" :isRTL="isRTL" />

        <!-- Error State -->
        <div v-else-if="error" class="flex items-center justify-center h-full p-8 text-center">
            <div class="bg-white p-8 rounded-xl shadow-md">
                <h3 class="text-xl font-bold text-red-600">{{ t('publicProfile.error.title') }}</h3>
                <p class="text-gray-600 mt-2">{{ error }}</p>
            </div>
        </div>

        <!-- Actual Profile Content -->
        <div v-else-if="user">
            <ProfileHeader :user="user" :isRTL="isRTL" />

            <!-- Tabs -->
            <div class="px-4 sm:px-6 lg:px-12">
                <div class="border-b border-gray-200">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" :class="isRTL ? 'items-end' : ''">
                        <li v-for="tab in ['content', 'about']" :key="tab" :class="isRTL ? 'ml-2' : 'mr-2'">
                            <button
                                @click="activeTab = tab"
                                :class="
                                    activeTab === tab
                                        ? 'text-blue-600 border-blue-600'
                                        : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'
                                "
                                class="inline-block p-4 border-b-2 rounded-t-lg font-semibold transition-colors duration-150"
                            >
                                {{ t(`publicProfile.tabs.${tab}`) }}
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
                            class="flex flex-col items-center justify-center text-center bg-white p-8 rounded-xl shadow-sm"
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
                            <h3 class="text-lg font-medium text-gray-900">{{ t('publicProfile.empty.title') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ t('publicProfile.empty.description') }}</p>
                        </div>
                    </div>
                </div>

                <div v-show="activeTab === 'about'">
                    <ProfileAbout :user="user" :is-teacher-profile="isTeacherProfile" />
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { apiCall } from '@/services/apiService'

// Components
import PublicLayout from '@/components/layout/PublicLayout.vue'
import ProfileHeader from '../components/profile/ProfileHeader.vue'
import ProfileSkeleton from '../components/profile/ProfileSkeleton.vue'
import ProfileAbout from '../components/profile/ProfileAbout.vue'
import CategoriesMassProps from '@/components/discover/CategoriesMassProps.vue'
import CategoriesMassCourseProps from '@/components/discover/CategoriesMassCourseProps.vue'

const { t, locale } = useI18n()
const isRTL = computed(() => locale.value === 'fa')
const route = useRoute()
const isLoading = ref(true)
const error = ref(null)
const user = ref(null)
const activeTab = ref('content')
const profileType = ref('teacher')

const isTeacherProfile = computed(() => profileType.value === 'teacher')

const normalizeData = (data, type) => {
    const entity = data[type]
    if (!entity) return null
    return {
        name: entity.name || t(`publicProfile.unnamed${type.charAt(0).toUpperCase() + type.slice(1)}`),
        username: entity.username,
        verified: !!entity.verified_at,
        image: entity.avatar,
        cover: entity.cover,
        website: entity.website,
        about: entity.description,
        plays: data.plays_count || 0,
        tutees: data.students_count || 0,
        institutes: entity.institutes || [],
        teachers: entity.teachers || [],
        ayhoots: entity.exams || [],
        courses: entity.courses || [],
        contentCount: (entity.exams?.length || 0) + (entity.courses?.length || 0),
    }
}

const fetchProfileData = async (id, path) => {
    isLoading.value = true
    error.value = null
    user.value = null

    try {
        let isTeacher = !path.includes('/institute/profile')
        if (route.query.type === 'institute') isTeacher = false
        if (route.query.type === 'teacher') isTeacher = true
        let dataType = isTeacher ? 'teacher' : 'institute'
        let apiUrl = `/api/home/${dataType}/${id}`

        let response = null
        try {
            response = await apiCall('GET', apiUrl)
            if (!response[dataType]) {
                throw new Error('Not found')
            }
        } catch (e) {
            if (!route.query.type) {
                const altType = dataType === 'teacher' ? 'institute' : 'teacher'
                response = await apiCall('GET', `/api/home/${altType}/${id}`)
                dataType = altType
            } else {
                throw e
            }
        }

        profileType.value = dataType
        const normalized = normalizeData(response, dataType)

        if (!normalized) throw new Error(t('publicProfile.error.invalidData'))
        user.value = normalized
    } catch (err) {
        console.error('Profile page error:', err)
        error.value = t('publicProfile.error.loadFailed')
    } finally {
        isLoading.value = false
    }
}

watch(
    [() => route.params.id, () => route.query.type, () => route.path],
    ([newId]) => {
        if (newId) fetchProfileData(newId, route.path)
    },
    { immediate: true }
)
</script>
