<!-- CORRECTED: src/views/discover/layout/CategoriesMassCourseProps.vue -->
<template>
    <div class="">
        <div class="flex items-center justify-between mb-2 pr-4">
            <h3 class="font-bold text-gray-700 mb-2">All {{ Category }} Courses</h3>
        </div>
        <div class="pr-4">
            <div class="grid grid-cols-2 md:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <RouterLink
                    v-for="course in courses"
                    :key="course.id"
                    :to="`/teacher/course/${course.id}`"
                    class="group bg-gradient-to-b from-[#5A89DA] to-[#4C1D92] rounded-md shadow-sm w-full transition-transform transform hover:scale-101 hover:shadow-md duration-100"
                >
                    <div class="rounded-t-md text-xs py-1 px-2 flex justify-between items-center">
                        <div class="flex gap-1 items-center text-white">
                            <svg
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M19 11V8.2C19 7.0799 19 6.51984 18.782 6.09202C18.5903 5.71569 18.2843 5.40973 17.908 5.21799C17.4802 5 16.9201 5 15.8 5H7.2C6.0799 5 5.51984 5 5.09202 5.21799C4.71569 5.40973 4.40973 5.71569 4.21799 6.09202C4 6.51984 4 7.07989 4 8.2V13.8C4 14.9201 4 15.4802 4.21799 15.908C4.40973 16.2843 4.71569 16.5903 5.09202 16.782C5.51984 17 6.07989 17 7.2 17H14"
                                    class="stroke-current"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />
                                <path d="M8 13H12" class="stroke-current" stroke-width="2" stroke-linecap="round" />
                                <path d="M8 9H15" class="stroke-current" stroke-width="2" stroke-linecap="round" />
                                <circle cx="18" cy="15" r="1" class="stroke-current" stroke-width="2" />
                                <path
                                    d="M20 20C20 20 19.5 19 18 19C16.5 19 16 20 16 20"
                                    class="stroke-current"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />
                            </svg>
                            <span class="text-xs font-medium text-white">{{ course.plays }} Enrolls</span>
                        </div>
                        <div class="text-[0.6rem] text-white bg-white/20 rounded-md px-2 py-0.5">
                            {{ course.price }}
                        </div>
                    </div>
                    <div class="relative">
                        <!-- MODIFIED LINE: Use the new method to format the image src -->
                        <img :src="formatImageUrl(course.image)" :alt="course.title" class="w-full h-30 object-cover" />
                    </div>
                    <div class="p-3 w-full flex flex-col justify-between">
                        <h4
                            class="font-semibold text-white truncate sm:max-w-[16ch] md:max-w-[14ch] lg:max-w-[16ch] overflow-hidden text-ellipsis"
                        >
                            {{ course.title }}
                        </h4>
                        <p
                            class="text-sm text-gray-100 truncate sm:max-w-[20ch] md:max-w-[16ch] lg:max-w-[22ch] overflow-hidden text-ellipsis"
                        >
                            {{ course.description }}
                        </p>
                        <div class="mt-2 flex items-center justify-start gap-2">
                            <div class="">
                                <div class="text-xs text-white">@{{ course.author }}</div>
                                <div class="text-xs text-white">5 resources</div>
                            </div>
                        </div>
                    </div>
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<script>
import { RouterLink } from 'vue-router'
import AyLinedButton from '@/components/atoms/button/AyLinedButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'

export default {
    name: 'CategoriesMassCourseProps',
    components: {
        AyLinedButton,
        ResponsiveButton,
        RouterLink,
    },
    props: {
        courses: {
            type: Array,
            required: true,
        },
        Category: {
            type: String,
            required: true,
        },
    },
    methods: {
        // NEW METHOD: Formats the image URL
        formatImageUrl(relativePath) {
            if (!relativePath) {
                // Return a placeholder if the image path is missing
                return '/imgHolders/CardHolderImage.jpg'
            }
            // Construct the full URL, assuming Laravel's public storage link
            return `/uploads/${relativePath}`
        },
    },
}
</script>

<style scoped>
/* Smooth scrolling */
.scroll-smooth {
    scroll-behavior: smooth;
}
</style>
