<template>
    <div class="px-6 py-4 bg-white shadow-sm rounded-xl flex flex-col gap-y-2 hover:shadow-md text-start">
        <div class="w-full border-b-1 border-gray-300">
            <div class="w-max border-b-2 pb-2 border-gray-700 text-sm font-bold">
                {{ $t('dashboard.courses.latestCourses') }}
            </div>
        </div>
        <div v-if="isLoading" class="py-2 flex flex-col gap-y-2">
            <div v-for="n in 3" :key="n" class="flex p-1 gap-x-3 animate-pulse">
                <div class="w-40 h-24 rounded-s-md bg-gray-200"></div>
                <div class="flex-1 space-y-3 py-1">
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-200 rounded w-5/6"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                </div>
            </div>
        </div>
        <div v-else-if="courses.length" class="py-2 flex flex-col gap-y-2">
            <RouterLink
                v-for="course in courses"
                :key="course.id"
                :to="`/student/exam/courses`"
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
                    <div class="mt-2 w-full flex items-center justify-start gap-x-2">
                        <div class="w-full sm:flex items-center justify-between gap-x-1">
                            <div class="text-xs text-gray-500 flex items-center gap-x-1">
                                <svg
                                    width="16"
                                    height="16"
                                    viewBox="0 0 16 16"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M12.8001 9.12378C13.645 9.75504 14.4001 11.3444 14.4001 12.3237C14.4001 12.6288 14.1775 12.8762 13.9029 12.8762H13.6001M10.4001 6.53629C10.9467 6.22012 11.3144 5.62918 11.3144 4.95235C11.3144 4.27552 10.9467 3.68458 10.4001 3.36841M2.09728 12.8762H10.8553C11.1299 12.8762 11.3525 12.6288 11.3525 12.3237C11.3525 10.406 9.7503 8.85137 6.47629 8.85137C3.20227 8.85137 1.6001 10.406 1.6001 12.3237C1.6001 12.6288 1.82269 12.8762 2.09728 12.8762ZM8.30486 4.95235C8.30486 5.96224 7.48618 6.78092 6.47629 6.78092C5.4664 6.78092 4.64772 5.96224 4.64772 4.95235C4.64772 3.94246 5.4664 3.12378 6.47629 3.12378C7.48618 3.12378 8.30486 3.94246 8.30486 4.95235Z"
                                        stroke="#7C7C7C"
                                        stroke-linecap="round"
                                    />
                                </svg>
                                <span>{{
                                    $t('dashboard.courses.enrollsCount', { count: course.students_count || 0 })
                                }}</span>
                            </div>
                            <span class="text-xs text-gray-500"
                                >{{ $t('dashboard.courses.createdBy') }}@{{ course.teacher?.username }}</span
                            >
                        </div>
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
            <p class="mt-1 text-sm text-gray-500">{{ $t('dashboard.courses.noCoursesFoundForStudents') }}</p>
            <div class="mt-6">
                <ResponsiveButton to="/student/discover" type="dash-blue" class="inline-flex items-center px-2 py-1">{{
                    $t('dashboard.courses.discover')
                }}</ResponsiveButton>
            </div>
        </div>
        <div class="w-full flex items-center justify-center pt-2">
            <div v-if="isLoading" class="animate-pulse h-6 w-24 bg-gray-200 rounded-md"></div>
            <AyLinedButton v-else to="/student/exam/courses">{{
                $t('dashboard.courses.seeAll', { count: totalCourses })
            }}</AyLinedButton>
        </div>
    </div>
</template>

<script>
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import AyLinedButton from '@/components/atoms/button/AyLinedButton.vue'

export default {
    name: 'DashCoursesCard',
    components: {
        ResponsiveButton,
        AyLinedButton,
    },
    // Data is now passed via props from the parent component
    props: {
        courses: {
            type: Array,
            default: () => [],
        },
        totalCourses: {
            type: Number,
            default: 0,
        },
        isLoading: {
            type: Boolean,
            required: true,
        },
    },
    // Removed local data properties
    methods: {
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
    // Removed mounted hook and data fetching logic
}
</script>
