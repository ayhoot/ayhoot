<template>
    <div>
        <h3 class="text-xl font-bold text-gray-800 mb-4">{{ t('discover.coursesTitle') }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <RouterLink
                v-for="course in MassCourses"
                :key="course.id"
                :to="`/${$role}/course/${course.id}`"
                class="group bg-gradient-to-b from-[#5A89DA] to-[#4C1D92] rounded-md shadow-sm w-full transition-transform transform hover:scale-101 hover:shadow-md duration-100"
            >
                <div class="rounded-t-md text-xs py-1 px-2 flex justify-between items-center">
                    <div class="flex gap-1 items-center text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"
                            />
                        </svg>
                        <span class="text-xs font-medium text-white">{{
                            t('discover.enrolls', { count: '120+' })
                        }}</span>
                    </div>
                    <div class="text-[0.6rem] text-white bg-white/20 rounded-md px-2 py-0.5">
                        {{ formatPrice(course) }}
                    </div>
                </div>
                <div class="relative">
                    <img :src="getCoverUrl(course.cover)" :alt="course.title" class="w-full h-32 object-cover" />
                </div>
                <div class="p-3 w-full flex flex-col justify-between flex-grow">
                    <div>
                        <h4 class="font-semibold text-white truncate" :title="course.title">
                            {{ course.title }}
                        </h4>
                        <p class="text-sm text-gray-100 truncate" :title="course.description">
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
                            class="text-xs text-white hover:underline z-10 relative"
                        >
                            {{ t('discover.byline.byUser') }} @{{
                                course.teacher?.username || course.institute?.username
                            }}
                        </RouterLink>
                        <div v-else class="text-xs text-white">
                            {{ t('discover.byline.byUser', { user: t('discover.byline.anonymous') }) }}
                        </div>
                    </div>
                </div>
            </RouterLink>
        </div>
    </div>
</template>

<script>
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

export default {
    name: 'CategoriesMassCourseProps',
    components: {
        RouterLink,
    },
    props: {
        MassCourses: {
            type: Array,
            required: true,
            default: () => [],
        },
    },
    setup() {
        const { t } = useI18n()
        return { t }
    },
    methods: {
        getCoverUrl(coverPath) {
            if (!coverPath) {
                return '/imgHolders/CardHolderImage.jpg'
            }
            const cleanedPath = coverPath.replace(/\\\//g, '/')
            return `/uploads/${cleanedPath}`
        },
        formatPrice(course) {
            if (course.monetize === 'free') {
                return this.t('discover.price.free')
            }
            if (course.monetize === 'paid' && course.price) {
                return this.t('discover.price.paidAmount', { price: Number(course.price).toLocaleString() })
            }
            return this.t('discover.price.paid')
        },
    },
}
</script>

<style scoped>
.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
