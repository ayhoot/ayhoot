<template>
    <div class="px-6 py-4 bg-white shadow-sm rounded-xl flex flex-col gap-y-2 hover:shadow-md text-start">
        <div class="w-full border-b-1 border-gray-300">
            <div class="w-max border-b-2 pb-2 border-gray-700 text-sm font-bold">
                {{ $t('dashboard.ayhoots.latestAyhoots') }}
            </div>
        </div>
        <!-- MODIFIED: Use the 'loading' prop for the skeleton -->
        <div v-if="loading" class="py-2 flex flex-col gap-y-2">
            <div v-for="n in 3" :key="n" class="flex p-1 gap-x-3 animate-pulse">
                <div class="w-26 h-24 rounded-s-md bg-gray-200"></div>
                <div class="flex-1 space-y-3 py-1">
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-200 rounded w-5/6"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                </div>
            </div>
        </div>
        <!-- MODIFIED: Simplified condition to check for exams after loading is false -->
        <div v-else-if="exams.length > 0" class="py-2 flex flex-col gap-y-2">
            <RouterLink
                v-for="exam in exams"
                :key="exam.id"
                :to="`/${$role}/exam`"
                class="flex group bg-white rounded-md w-full transition-transform transform border border-gray-100 hover:scale-101 hover:shadow-sm duration-100"
            >
                <div class="relative">
                    <div v-if="fallbackImages[exam.id]">
                        <img
                            :src="getCoverUrl(exam.cover)"
                            :alt="exam.title"
                            class="sm:h-24 h-24 w-30 object-cover rounded-s-md bg-gray-200"
                        />
                    </div>
                    <img
                        v-else
                        :src="`/uploads/${exam.cover}`"
                        :alt="exam.title"
                        class="sm:h-24 h-24 w-30 object-cover rounded-s-md"
                        @error="onImageError(exam.id)"
                    />
                    <span
                        class="absolute opacity-0 group-hover:opacity-100 transition-opacity duration-300 bottom-2 start-2 text-xs text-white bg-black/30 p-1 rounded-md"
                        >{{ $t('dashboard.ayhoots.questionsCount', { count: exam.questions_count ?? 0 }) }}</span
                    >
                </div>
                <div class="p-3 w-full flex flex-col justify-between">
                    <h4
                        class="font-semibold text-sm text-gray-800 truncate sm:max-w-[16ch] md:max-w-[10ch] lg:max-w-[10ch]"
                    >
                        {{ exam.title }}
                    </h4>
                    <p class="text-xs text-gray-600 truncate sm:max-w-[16ch] md:max-w-[10ch] lg:max-w-[14ch]">
                        {{ exam.description || $t('dashboard.ayhoots.noDescription') }}
                    </p>
                    <div class="mt-2 text-xs text-gray-500 flex items-center gap-x-1">
                        <svg width="16" height="16" fill="none" viewBox="0 0 16 16" stroke="#7C7C7C">
                            <path
                                d="M12.8 9.12C13.645 9.755 14.4 11.344 14.4 12.324c0 .305-.222.552-.497.552H13.6M10.4 6.536C10.947 6.22 11.314 5.629 11.314 4.952c0-.677-.367-1.268-.914-1.584M2.097 12.876h8.758c.275 0 .497-.248.497-.553 0-1.917-1.602-3.472-4.876-3.472S1.6 10.406 1.6 12.324c0 .305.222.552.497.552ZM8.305 4.952c0 1.01-.819 1.829-1.829 1.829S4.648 5.962 4.648 4.952 5.467 3.124 6.476 3.124s1.829.819 1.829 1.828Z"
                                stroke-linecap="round"
                            />
                        </svg>
                        <span>{{ $t('dashboard.ayhoots.playsCount', { count: exam.plays_count ?? exam.students_count ?? 0 }) }}</span>
                    </div>
                </div>
            </RouterLink>
        </div>
        <!-- This now correctly displays when loading is false and there are no exams -->
        <div v-else class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $t('dashboard.ayhoots.noAyhootsFound') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $t('dashboard.ayhoots.noAyhootsSubtitle') }}</p>
            <div class="mt-6">
                <ResponsiveButton :to="`/${$role}/exam/create`" type="dash-blue" class="inline-flex items-center px-2 py-1">
                    {{ $t('dashboard.ayhoots.createAyhoot') }}</ResponsiveButton
                >
            </div>
        </div>
        <div class="w-full flex items-center justify-center pt-2">
            <!-- MODIFIED: Use the 'loading' prop for the button's loading state -->
            <div v-if="loading" class="animate-pulse h-6 w-24 bg-gray-200 rounded-md"></div>
            <AyLinedButton v-else :to="`/${$role}/exam`"
                >{{ $t('dashboard.ayhoots.seeAll', { count: totalExams }) }}
            </AyLinedButton>
        </div>
    </div>
</template>

<script>
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import AyLinedButton from '@/components/atoms/button/AyLinedButton.vue'
import { getAssetUrl } from '@/utils/url.js'

export default {
    name: 'DashAyhootsCard',
    components: { ResponsiveButton, AyLinedButton },
    props: {
        exams: { type: Array, default: () => [] },
        totalExams: { type: Number, default: 0 },
        // MODIFIED: Added loading prop to correctly handle the loading state
        loading: { type: Boolean, default: false },
    },
    data() {
        return {
            fallbackImages: {},
        }
    },
    // MODIFIED: Removed the incorrect 'isLoading' computed property
    methods: {
        onImageError(id) {
            this.fallbackImages = { ...this.fallbackImages, [id]: true }
        },
        getCoverUrl(coverPath) {
            // The helper function takes the path and a fallback image URL
            return getAssetUrl(coverPath, '/imgHolders/CardHolderImage.jpg')
        },
    },
}
</script>
