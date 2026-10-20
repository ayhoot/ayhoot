<template>
    <div v-if="!loading && institutes && institutes.length > 0" class="mt-8 pr-4">
        <div class="flex items-center justify-between mb-2 pr-4">
            <h3 class="font-bold text-gray-700 mb-2">{{ t('discover.verifiedInstitutes.title') }}</h3>
            <AyLinedButton to="/discover/institutes">{{ t('discover.seeAll') }}</AyLinedButton>
        </div>
        <div class="grid w-full grid-cols-1 md:grid-cols-3 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <RouterLink
                v-for="institute in institutes"
                :key="institute.id"
                :to="`/institute/profile/${institute.id}`"
                class="group bg-white rounded-md shadow-sm w-full transition-transform transform hover:scale-101 hover:shadow-md duration-100"
            >
                <div
                    class="rounded-t-md bg-gradient-to-tr from-[#1053C1] to-[#429FE3] text-xs p-5 flex flex-col space-y-2.5 justify-center items-center"
                >
                    <img
                        :src="getAvatarUrl(institute.avatar)"
                        :alt="institute.name"
                        class="w-16 h-16 object-cover rounded-full border border-white"
                    />
                    <div class="flex flex-col space-y-1 items-center justify-center">
                        <div class="flex items-center justify-center gap-1 text-white">
                            <h4
                                class="font-semibold text-white truncate sm:max-w-[16ch] md:max-w-[14ch] lg:max-w-[16ch] overflow-hidden text-ellipsis"
                            >
                                {{ institute.name }}
                            </h4>
                            <DashVerified v-if="institute.verified_at" />
                        </div>
                        <p
                            class="text-white text-[0.6rem] bg-white/20 border border-white text-center rounded-full w-fit p-1 px-2"
                        >
                            {{ institute.plan?.title || t('discover.verifiedInstitutes.institute') }}
                        </p>
                    </div>
                </div>
                <div class="p-3 w-full flex items-center justify-between">
                    <div class="text-xs text-center">
                        <div>{{ institute.exams_count || 0 }}</div>
                        <div class="font-semibold text-gray-500">{{ t('discover.verifiedInstitutes.ayhoots') }}</div>
                    </div>
                    <div class="text-xs text-center">
                        <div>{{ institute.courses_count || 0 }}</div>
                        <div class="font-semibold text-gray-500">{{ t('discover.verifiedInstitutes.courses') }}</div>
                    </div>
                    <div class="text-xs text-center">
                        <div>{{ institute.teachers_count || 0 }}</div>
                        <div class="font-semibold text-gray-500">{{ t('discover.verifiedInstitutes.tutors') }}</div>
                    </div>
                    <div class="text-xs text-center">
                        <div>{{ institute.students_count || 0 }}</div>
                        <div class="font-semibold text-gray-500">{{ t('discover.verifiedInstitutes.tutees') }}</div>
                    </div>
                </div>
            </RouterLink>
        </div>
    </div>
    <!-- Skeleton Loader -->
    <div v-if="loading" class="mt-8 pr-4">
        <div class="h-6 bg-gray-200 rounded-md w-48 mb-4 animate-pulse"></div>
        <div class="grid w-full grid-cols-1 md:grid-cols-3 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div v-for="n in 5" :key="n" class="bg-white rounded-md shadow-sm w-full animate-pulse">
                <div
                    class="rounded-t-md bg-gray-200 text-xs p-5 flex flex-col space-y-2.5 justify-center items-center h-[152px]"
                >
                    <div class="w-16 h-16 bg-gray-300 rounded-full"></div>
                    <div class="h-4 bg-gray-300 rounded w-2/3"></div>
                </div>
                <div class="p-3 w-full flex items-center justify-around">
                    <div class="text-xs text-center w-full space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto"></div>
                        <div class="h-3 bg-gray-300 rounded w-2/3 mx-auto"></div>
                    </div>
                    <div class="text-xs text-center w-full space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto"></div>
                        <div class="h-3 bg-gray-300 rounded w-2/3 mx-auto"></div>
                    </div>
                    <div class="text-xs text-center w-full space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto"></div>
                        <div class="h-3 bg-gray-300 rounded w-2/3 mx-auto"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { useI18n } from 'vue-i18n'
import { RouterLink } from 'vue-router'
import AyLinedButton from '@/components/atoms/button/AyLinedButton.vue'
import DashVerified from '@/components/atoms/icons/DashVerified.vue'

export default {
    name: 'VerifiedInstitutes',
    components: {
        RouterLink,
        AyLinedButton,
        DashVerified,
    },
    props: {
        institutes: {
            type: Array,
            default: () => [],
        },
        loading: {
            type: Boolean,
            default: true,
        },
    },
    setup() {
        const { t } = useI18n()
        return { t }
    },
    methods: {
        getAvatarUrl(avatarPath) {
            if (!avatarPath) return '/imgHolders/institute.png'
            if (avatarPath.startsWith('http')) return avatarPath
            return `/uploads/${avatarPath.replace(/\\/g, '/')}`
        },
    },
}
</script>
