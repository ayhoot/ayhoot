<template>
    <div class="mt-8" :class="isRTL ? 'text-right' : 'text-left'">
        <!-- Section Title -->
        <h3 class="text-2xl font-bold text-gray-800 mb-4">
            {{ title }}
        </h3>

        <!-- Search and Filter Controls -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-6 sticky top-16 z-40">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <!-- Search Input -->
                <div class="lg:col-span-1">
                    <label for="search" class="block text-sm font-semibold text-gray-700 mb-1">{{
                        t('discover.recommendedAyhoots.filters.searchLabel')
                    }}</label>
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 flex items-center pointer-events-none"
                            :class="isRTL ? 'right-0 pr-3' : 'left-0 pl-3'"
                        >
                            <svg
                                class="h-5 w-5 text-gray-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            id="search"
                            class="block w-full font-semibold py-2 border-2 border-gray-300 rounded-sm leading-5 bg-white placeholder-gray-500 focus:outline-none sm:text-sm"
                            :placeholder="t('discover.recommendedAyhoots.filters.searchPlaceholder')"
                            :class="isRTL ? 'pl-3 pr-10 text-right' : 'pl-10 pr-3 text-left'"
                        />
                    </div>
                </div>
                <!-- Filter by Content Type -->
                <div class="lg:col-span-1">
                    <label for="content-type-filter" class="block text-sm font-semibold text-gray-700 mb-1">{{
                        t('category.filters.contentType')
                    }}</label>
                    <select
                        v-model="selectedContentType"
                        id="content-type-filter"
                        class="block w-full pl-3 pr-10 py-2 font-semibold border-2 border-gray-300 focus:outline-none sm:text-sm rounded-sm"
                        :class="isRTL ? 'text-right' : 'text-left'"
                    >
                        <option value="all">{{ t('category.filters.all') }}</option>
                        <option value="exam">{{ t('category.filters.exams') }}</option>
                        <option value="course">{{ t('category.filters.courses') }}</option>
                    </select>
                </div>
                <!-- Filter by Price -->
                <div class="lg:col-span-1">
                    <label for="price-filter" class="block text-sm font-semibold text-gray-700 mb-1">{{
                        t('discover.recommendedAyhoots.filters.priceLabel')
                    }}</label>
                    <select
                        v-model="selectedPriceFilter"
                        id="price-filter"
                        class="block w-full pl-3 pr-10 py-2 font-semibold border-2 border-gray-300 focus:outline-none sm:text-sm rounded-sm"
                        :class="isRTL ? 'text-right' : 'text-left'"
                    >
                        <option value="all">{{ t('discover.recommendedAyhoots.filters.priceAll') }}</option>
                        <option value="paid">{{ t('discover.recommendedAyhoots.filters.pricePaid') }}</option>
                        <option value="free">{{ t('discover.recommendedAyhoots.filters.priceFree') }}</option>
                    </select>
                </div>
                <!-- Sort By -->
                <div class="lg:col-span-1">
                    <label for="sort-by" class="block text-sm font-semibold text-gray-700 mb-1">{{
                        t('discover.recommendedAyhoots.filters.sortByLabel')
                    }}</label>
                    <select
                        v-model="selectedSort"
                        id="sort-by"
                        class="block w-full pl-3 pr-10 py-2 font-semibold border-2 border-gray-300 focus:outline-none sm:text-sm rounded-sm"
                        :class="isRTL ? 'text-right' : 'text-left'"
                    >
                        <option value="latest">{{ t('discover.recommendedAyhoots.filters.sortLatest') }}</option>
                        <option value="oldest">{{ t('discover.recommendedAyhoots.filters.sortOldest') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- === SKELETON LOADING STATE === -->
        <div v-if="loading && items.length === 0" class="pt-2">
            <div class="grid grid-cols-2 md:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <div
                    v-for="n in 10"
                    :key="`skeleton-${n}`"
                    class="group bg-gradient-to-b from-[#a7bde2] to-[#9c82c3] rounded-md shadow-sm w-full animate-pulse"
                >
                    <div class="rounded-t-md text-xs py-1 px-2 flex justify-between items-center h-7">
                        <div class="h-4 bg-gray-400/50 rounded w-16"></div>
                        <div class="h-4 bg-gray-400/50 rounded w-10"></div>
                    </div>
                    <div class="w-full h-32 bg-gray-400/50"></div>
                    <div class="p-3 space-y-3">
                        <div class="h-4 bg-gray-300/50 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-300/50 rounded w-full"></div>
                        <div class="h-3 bg-gray-300/50 rounded w-1/2 mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div v-else-if="sortedAndFilteredItems.length > 0" class="pt-2">
            <div class="grid grid-cols-2 md:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <RouterLink
                    v-for="item in sortedAndFilteredItems"
                    :key="`${item.type}-${item.id}`"
                    :to="
                        item.type === 'exam'
                            ? `/discover/ayhoot/${item.id}/preview`
                            : `/discover/course/${item.id}/preview`
                    "
                    class="group bg-gradient-to-b from-[#5A89DA] to-[#4C1D92] rounded-md shadow-sm w-full transition-transform transform hover:scale-101 hover:shadow-md duration-100"
                >
                    <!-- Card Header -->
                    <div class="rounded-t-md text-xs py-1 px-2 flex justify-between items-center">
                        <div class="flex gap-1 items-center text-white" :class="isRTL ? 'flex-row-reverse' : ''">
                            <span v-if="item.type === 'exam'" class="text-xs font-medium text-white">{{
                                t('discover.plays', { count: item.plays_count })
                            }}</span>
                            <span v-else class="text-xs font-medium text-white">{{
                                t('discover.students', { count: item.students_count })
                            }}</span>
                        </div>
                        <div class="text-[0.6rem] text-white bg-white/20 rounded-md px-2 py-0.5">
                            {{ formatPrice(item) }}
                        </div>
                    </div>

                    <!-- Cover Image -->
                    <div class="relative">
                        <span
                            class="absolute top-2 text-white text-xs font-semibold px-2 py-0.5 rounded-md z-10"
                            :class="[
                                isRTL ? 'left-2' : 'right-2',
                                {
                                    'bg-blue-500': item.type === 'course',
                                    'bg-amber-500': item.type === 'exam',
                                },
                            ]"
                        >
                            {{ item.type === 'exam' ? t('category.badge.ayhoot') : t('category.badge.course') }}
                        </span>

                        <img :src="getCoverUrl(item.cover)" :alt="item.title" class="w-full h-32 object-cover" />

                        <span
                            v-if="item.type === 'exam' && item.questions_count"
                            class="absolute opacity-0 group-hover:opacity-100 transition-opacity duration-300 bottom-2 text-xs text-white bg-black/40 px-2 py-1 rounded-md"
                            :class="isRTL ? 'right-2' : 'left-2'"
                        >
                            {{ t('discover.questions', { count: item.questions_count }) }}
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div class="p-3 w-full flex flex-col justify-between">
                        <h4 class="font-semibold text-white truncate" :title="item.title">{{ item.title }}</h4>
                        <p class="text-sm text-gray-100 truncate" :title="item.description">{{ item.description }}</p>
                        <div
                            class="mt-2 flex items-center justify-start gap-2"
                            :class="isRTL ? 'flex-row-reverse' : ''"
                        >
                            <RouterLink
                                v-if="item.teacher_id || item.institute_id"
                                :to="
                                    item.teacher_id
                                        ? `/teacher/profile/${item.teacher_id}`
                                        : `/institute/profile/${item.institute_id}`
                                "
                                @click.stop
                                class="text-xs text-white hover:underline z-10 relative"
                            >
                                {{ t('discover.allAyhoots.byline.by') }} @{{
                                    item.teacher?.username || item.institute?.username || t('discover.byline.institute')
                                }}
                            </RouterLink>
                            <div v-else class="text-xs text-white">
                                {{ t('discover.allAyhoots.byline.anonymous') }}
                            </div>
                        </div>
                    </div>
                </RouterLink>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center text-gray-500 py-16">
            <p class="font-semibold text-lg">{{ t('discover.recommendedAyhoots.empty.title') }}</p>
            <p class="mt-1">{{ t('discover.recommendedAyhoots.empty.description') }}</p>
        </div>

        <!-- Load More Button -->
        <div v-if="hasMore" class="text-center mt-6">
            <button
                @click="triggerLoadMore"
                :disabled="loading || isLoadingMore"
                class="text-gray-500 hover:text-gray-800 cursor-pointer font-semibold text-sm py-2 px-6 disabled:text-gray-300 disabled:cursor-not-allowed transition-colors"
            >
                <span v-if="isLoadingMore">{{ t('discover.loadingMore') }}</span>
                <span v-else>{{ t('discover.loadMore') }}</span>
            </button>
        </div>
    </div>
</template>

<script>
import { RouterLink } from 'vue-router'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

export default {
    name: 'MassContent',
    components: {
        RouterLink,
    },
    props: {
        items: {
            type: Array,
            required: true,
        },
        title: {
            type: String,
            required: true,
        },
        loading: {
            type: Boolean,
            default: false,
        },
        hasMoreExams: {
            type: Boolean,
            default: false,
        },
        hasMoreCourses: {
            type: Boolean,
            default: false,
        },
        isLoadingMore: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['load-more'],
    setup() {
        const { locale, t } = useI18n()
        const isRTL = computed(() => locale.value === 'fa')
        return { isRTL, t }
    },
    data() {
        return {
            searchQuery: '',
            selectedContentType: 'all',
            selectedPriceFilter: 'all',
            selectedSort: 'latest',
        }
    },
    computed: {
        hasMore() {
            if (this.selectedContentType === 'exam') {
                return this.hasMoreExams
            }
            if (this.selectedContentType === 'course') {
                return this.hasMoreCourses
            }
            return this.hasMoreExams || this.hasMoreCourses
        },
        sortedAndFilteredItems() {
            let filtered = [...this.items]

            // 1. Filter by Content Type
            if (this.selectedContentType !== 'all') {
                filtered = filtered.filter((item) => item.type === this.selectedContentType)
            }

            // 2. Filter by Price
            if (this.selectedPriceFilter !== 'all') {
                filtered = filtered.filter((item) => item.monetize === this.selectedPriceFilter)
            }

            // 3. Filter by Search Query
            if (this.searchQuery) {
                const lowerCaseQuery = this.searchQuery.toLowerCase()
                filtered = filtered.filter(
                    (item) =>
                        (item.title && item.title.toLowerCase().includes(lowerCaseQuery)) ||
                        (item.description && item.description.toLowerCase().includes(lowerCaseQuery)) ||
                        (item.teacher?.username && item.teacher.username.toLowerCase().includes(lowerCaseQuery)) ||
                        (item.institute?.username && item.institute.username.toLowerCase().includes(lowerCaseQuery))
                )
            }

            // 4. Sort
            return filtered.sort((a, b) => {
                switch (this.selectedSort) {
                    case 'oldest':
                        return new Date(a.created_at) - new Date(b.created_at)
                    case 'latest':
                    default:
                        return new Date(b.created_at) - new Date(a.created_at)
                }
            })
        },
    },
    methods: {
        getCoverUrl(coverPath) {
            if (!coverPath) {
                return '/imgHolders/CardHolderImage.jpg'
            }
            const cleanedPath = coverPath.replace(/\\\//g, '/')
            return `/uploads/${cleanedPath}`
        },
        formatPrice(item) {
            if (item.monetize === 'free') return this.t('discover.price.free')
            if (item.monetize === 'paid' && item.price) {
                return this.t('discover.price.paidAmount', { price: Number(item.price).toLocaleString() })
            }
            return this.t('discover.price.paid')
        },
        triggerLoadMore() {
            if (this.loading || this.isLoadingMore) return

            if (this.selectedContentType === 'exam') {
                if (this.hasMoreExams) this.$emit('load-more', { type: 'exam' })
            } else if (this.selectedContentType === 'course') {
                if (this.hasMoreCourses) this.$emit('load-more', { type: 'course' })
            } else if (this.selectedContentType === 'all') {
                if (this.hasMoreExams) this.$emit('load-more', { type: 'exam' })
                if (this.hasMoreCourses) this.$emit('load-more', { type: 'course' })
            }
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
.sticky {
    position: -webkit-sticky;
    position: sticky;
}
@keyframes pulse {
    50% {
        opacity: 0.6;
    }
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
