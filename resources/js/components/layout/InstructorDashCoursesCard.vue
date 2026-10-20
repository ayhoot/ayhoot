<template>
    <div class="px-6 py-4 bg-white shadow-sm rounded-xl flex flex-col gap-y-2 hover:shadow-md text-start">
        <div class="w-full border-b-1 border-gray-300">
            <div class="w-max border-b-2 pb-2 border-gray-700 text-sm font-bold">
                {{ $t('dashboard.courses.latestCourses') }}
            </div>
        </div>
        <!-- MODIFIED: Uses the `loading` prop now -->
        <div v-if="loading" class="py-2 flex flex-col gap-y-2">
            <div v-for="n in 3" :key="n" class="flex p-1 gap-x-3 animate-pulse">
                <div class="w-40 h-24 rounded-s-md bg-gray-200"></div>
                <div class="flex-1 space-y-3 py-1">
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-200 rounded w-5/6"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                </div>
            </div>
        </div>
        <!-- MODIFIED: Uses the `courses` prop now -->
        <div v-else-if="courses.length" class="py-2 flex flex-col gap-y-2">
            <RouterLink
                v-for="course in courses"
                :key="course.id"
                :to="`/${$role}/exam/courses`"
                class="flex group bg-white rounded-md w-full transition-transform transform border border-gray-100 hover:scale-101 hover:shadow-sm duration-100"
            >
                <div class="relative">
                    <img
                        :src="course.cover ? `/uploads/${course.cover}` : '/imgHolders/CardHolderImage.jpg'"
                        :alt="course.title"
                        class="sm:h-24 h-full w-40 object-cover rounded-s-md"
                    />
                    <span
                        class="absolute opacity-0 group-hover:opacity-100 transition-opacity duration-300 bottom-2 start-2 text-xs text-white bg-black/30 p-1 rounded-md"
                        >{{ $t('dashboard.courses.resourcesCount', { count: getResourceCount(course) }) }}</span
                    >
                </div>
                <div class="p-3 w-full flex flex-col justify-between">
                    <h4
                        class="font-semibold text-gray-800 truncate sm:max-w-[16ch] md:max-w-[14ch] lg:max-w-[16ch] overflow-hidden text-ellipsis"
                    >
                        {{ course.title }}
                    </h4>
                    <p
                        class="text-sm text-gray-600 truncate max-w-full sm:max-w-[16ch] md:max-w-[20ch] lg:max-w-[20ch] overflow-hidden text-ellipsis"
                    >
                        {{ course.description || $t('dashboard.courses.noDescription') }}
                    </p>
                    <div class="mt-2 w-full flex items-center justify-end">
                        <span class="text-xs text-gray-500"
                            >{{ $t('dashboard.courses.createdBy') }}@{{
                                course.teacher?.username || course.institute?.username || course.teacher?.name || course.institute?.name
                            }}</span
                        >
                    </div>
                </div>
            </RouterLink>
        </div>
        <div v-else class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $t('dashboard.courses.noCoursesFound') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $t('dashboard.courses.noCoursesSubtitle') }}</p>
            <div class="mt-6">
                <ResponsiveButton
                    :to="`/${$role}/course/create`"
                    type="dash-blue"
                    class="inline-flex items-center px-2 py-1"
                    >{{ $t('dashboard.courses.createCourse') }}</ResponsiveButton
                >
            </div>
        </div>
        <div class="w-full flex items-center justify-center pt-2">
            <div v-if="loading" class="animate-pulse h-6 w-24 bg-gray-200 rounded-md"></div>
            <!-- MODIFIED: Uses the `totalCourses` prop -->
            <AyLinedButton v-else :to="`/${$role}/exam/courses`">{{
                $t('dashboard.courses.seeAll', { count: totalCourses })
            }}</AyLinedButton>
        </div>
    </div>
</template>

<script>
// REMOVED: import { apiCall } from "@/services/apiService"
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import AyLinedButton from '@/components/atoms/button/AyLinedButton.vue'

export default {
    name: 'DashCoursesCard',
    components: {
        ResponsiveButton,
        AyLinedButton,
    },
    // MODIFIED: Define props to accept data from the parent
    props: {
        courses: {
            type: Array,
            required: true,
            default: () => [],
        },
        loading: {
            type: Boolean,
            default: true,
        },
        totalCourses: {
            type: Number,
            default: 0,
        },
    },
    // REMOVED: Internal data is no longer needed as it comes from props
    data() {
        return {}
    },
    methods: {
        // REMOVED: fetchCourses() method is no longer needed
        getResourceCount(course) {
            try {
                if (course && course.options) {
                    const options = JSON.parse(course.options)
                    return options.timeline?.length || 0
                }
                return 0
            } catch (e) {
                console.error('Failed to parse course options:', e)
                return 0
            }
        },
    },
    // REMOVED: mounted() hook is no longer needed
}
</script>
