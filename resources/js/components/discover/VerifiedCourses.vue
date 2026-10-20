<template>
    <div class="mt-8 pr-4">
        <div
            class="sm:flex items-center sm:gap-40 justify-between mb-2 bg-gradient-to-br py-8 px-4 from-[#5A89DA] to-[#4C1D92] rounded-lg shadow-sm"
        >
            <!-- Left Side: Static Content -->
            <div class="px-4 sm:w-60 py-6 sm:py-0">
                <h3 class="font-bold text-[#EDC02C] py-2">{{ t('discover.verifiedCourses.title') }}</h3>
                <div class="font-medium text-sm text-white pb-2">{{ t('discover.verifiedCourses.description') }}</div>
                <div>
                    <ResponsiveButton :to="`/${$role}/discover/courses`" class="text-xs py-1 px-2" type="white">{{
                        t('discover.explore')
                    }}</ResponsiveButton>
                </div>
            </div>

            <!-- Right Side: Grid -->
            <!-- Skeleton Loader -->
            <div
                v-if="loading"
                class="grid grid-cols-2 md:grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:pr-4 w-full"
            >
                <div v-for="n in 4" :key="n" class="bg-white rounded-md shadow-sm animate-pulse">
                    <div class="rounded-t-md text-xs py-1 px-2 flex justify-between items-center">
                        <div class="h-4 bg-gray-200 rounded w-16"></div>
                        <div class="h-4 bg-gray-200 rounded w-12"></div>
                    </div>
                    <div class="h-32 bg-gray-200"></div>
                    <div class="p-3 space-y-3">
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-200 rounded w-full"></div>
                        <div class="flex justify-between items-center mt-2">
                            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actual Content Grid -->
            <div v-else class="grid grid-cols-2 md:grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:pr-4 w-full">
                <RouterLink
                    v-for="course in verified_courses"
                    :key="course.id"
                    :to="`/${$role}/course/${course.id}`"
                    class="bg-white rounded-md shadow-sm w-full transition-transform transform hover:scale-101 hover:shadow-md duration-100"
                >
                    <!-- Top Metadata Section -->
                    <div class="rounded-t-md text-xs py-1 px-2 flex justify-between items-center">
                        <div class="flex gap-1 items-center text-blue-500">
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M19.1999 13.6857C20.4672 14.6326 21.5999 17.0167 21.5999 18.4857C21.5999 18.9433 21.266 19.3143 20.8541 19.3143H20.3999M15.5999 9.8045C16.4197 9.33025 16.9713 8.44383 16.9713 7.42859C16.9713 6.41334 16.4197 5.52693 15.5999 5.05268M3.14567 19.3143H16.2827C16.6946 19.3143 17.0285 18.9433 17.0285 18.4857C17.0285 15.6091 14.6252 13.2771 9.71419 13.2771C4.80317 13.2771 2.3999 15.6091 2.3999 18.4857C2.3999 18.9433 2.7338 19.3143 3.14567 19.3143ZM12.457 7.42859C12.457 8.94343 11.229 10.1714 9.71419 10.1714C8.19935 10.1714 6.97133 8.94343 6.97133 7.42859C6.97133 5.91375 8.19935 4.68573 9.71419 4.68573C11.229 4.68573 12.457 5.91375 12.457 7.42859Z"
                                    stroke="currentcolor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />
                            </svg>
                            <span class="text-xs font-medium text-blue-500">{{
                                t('discover.enrolls', { count: '120+' })
                            }}</span>
                        </div>
                        <div class="text-[0.6rem] text-blue-500 bg-blue-100 rounded-md px-2 py-0.5 font-semibold">
                            {{ formatPrice(course) }}
                        </div>
                    </div>
                    <img :src="getAssetUrl(course.cover)" :alt="course.title" class="w-full h-32 object-cover" />
                    <div class="p-3 w-full flex-grow flex flex-col justify-between">
                        <div>
                            <h4 class="font-semibold text-gray-800 truncate" :title="course.title">
                                {{ course.title }}
                            </h4>
                            <p class="text-sm text-gray-600 truncate" :title="course.description">
                                {{ course.description || t('discover.noDescription') }}
                            </p>
                        </div>
                        <div class="mt-2 flex items-center justify-start gap-2">
                            <RouterLink
                                v-if="course.teacher_id || course.institute_id"
                                :to="
                                    course.teacher_id
                                        ? `/teacher/profile/${course.teacher_id}`
                                        : `/institute/profile/${course.institute_id}`
                                "
                                @click.stop
                                class="text-xs text-gray-500 hover:underline hover:text-indigo-600"
                            >
                                {{ t('discover.byline.byUser') }}
                                @{{ course.teacher?.username || course.institute?.username }}
                            </RouterLink>
                        </div>
                    </div>
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<script>
import { useI18n } from 'vue-i18n'
import { RouterLink } from 'vue-router'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

export default {
    name: 'VerifiedCourses',
    components: {
        ResponsiveButton,
        RouterLink,
    },
    props: {
        verified_courses: {
            type: Array,
            required: true,
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
        getAssetUrl(path) {
            if (!path) {
                return '/imgHolders/CardHolderImage.jpg'
            }
            if (path.startsWith('http')) {
                return path
            }
            const cleanedPath = path.replace(/\\/g, '')
            return `${API_BASE_URL}/uploads/${cleanedPath}`
        },
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
        formatPrice(course) {
            if (course.monetize === 'free') return this.t('discover.price.free')
            const numberPrice = Number(course.price)
            if (isNaN(numberPrice)) {
                return this.t('discover.price.paid')
            }
            return this.t('discover.price.paidAmount', { price: numberPrice.toLocaleString('en-US') })
        },
    },
}
</script>

<style scoped>
@keyframes pulse {
    50% {
        opacity: 0.5;
    }
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
