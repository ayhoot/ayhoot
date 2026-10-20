<template>
    <PageWrapper>
        <CategoriesBar />

        <!-- Page Title -->
        <h3 class="text-2xl font-bold text-gray-800 mb-4" :class="isRTL ? 'text-right' : 'text-left'">
            {{ t('discover.allCourses.pageTitle') }}
        </h3>

        <!-- Search and Filter Controls -->
        <div
            class="bg-white p-4 rounded-lg shadow-sm mb-6 sticky top-16 z-40"
            :class="isRTL ? 'text-right' : 'text-left'"
        >
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <!-- Search Input -->
                <div class="md:col-span-1">
                    <label for="search" class="block text-sm font-semibold text-gray-700 mb-1">{{
                        t('discover.allCourses.filters.searchLabel')
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
                            :placeholder="t('discover.allCourses.filters.searchPlaceholder')"
                            :class="isRTL ? 'pl-3 pr-10 text-right' : 'pl-10 pr-3 text-left'"
                        />
                    </div>
                </div>
                <!-- Filter by Price -->
                <div class="md:col-span-1">
                    <label for="price-filter" class="block text-sm font-semibold text-gray-700 mb-1">{{
                        t('discover.allCourses.filters.priceLabel')
                    }}</label>
                    <select
                        v-model="selectedFilter"
                        id="price-filter"
                        class="block w-full pl-3 pr-10 py-2 font-semibold border-2 border-gray-300 focus:outline-none sm:text-sm rounded-sm"
                        :class="isRTL ? 'text-right' : 'text-left'"
                    >
                        <option value="all">{{ t('discover.allCourses.filters.priceAll') }}</option>
                        <option value="paid">{{ t('discover.allCourses.filters.pricePaid') }}</option>
                        <option value="free">{{ t('discover.allCourses.filters.priceFree') }}</option>
                    </select>
                </div>
                <!-- Sort By -->
                <div class="md:col-span-1">
                    <label for="sort-by" class="block text-sm font-semibold text-gray-700 mb-1">{{
                        t('discover.allCourses.filters.sortByLabel')
                    }}</label>
                    <select
                        v-model="selectedSort"
                        id="sort-by"
                        class="block w-full pl-3 pr-10 py-2 font-semibold border-2 border-gray-300 focus:outline-none sm:text-sm rounded-sm"
                        :class="isRTL ? 'text-right' : 'text-left'"
                    >
                        <option value="latest">{{ t('discover.allCourses.filters.sortLatest') }}</option>
                        <option value="oldest">{{ t('discover.allCourses.filters.sortOldest') }}</option>
                        <option value="most-resources">
                            {{ t('discover.allCourses.filters.sortMostResources') }}
                        </option>
                        <option value="least-resources">
                            {{ t('discover.allCourses.filters.sortLeastResources') }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Skeleton Loading State -->
        <div v-if="isInitialLoading" class="pt-8">
            <div class="grid grid-cols-2 md:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <AyhootCardSkeleton v-for="n in 10" :key="n" />
            </div>
        </div>

        <!-- Courses Grid -->
        <div v-else-if="sortedAndFilteredCourses.length > 0">
            <div class="pt-8 grid grid-cols-2 md:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <RouterLink
                    v-for="course in sortedAndFilteredCourses"
                    :key="course.id"
                    :to="`/${$role}/course/${course.id}`"
                    class="group bg-gradient-to-b from-[#5A89DA] to-[#4C1D92] rounded-md shadow-sm w-full transition-transform transform hover:scale-101 hover:shadow-md duration-100 flex flex-col"
                >
                    <div class="rounded-t-md text-xs py-1 px-2 flex justify-between items-center">
                        <div
                            class="flex gap-1 items-center text-white"
                            :class="isRTL ? 'flex-row-reverse' : ''"
                        >
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M19.1999 13.6857C20.4672 14.6326 21.5999 17.0167 21.5999 18.4857C21.5999 18.9433 21.266 19.3143 20.8541 19.3143H20.3999M15.5999 9.8045C16.4197 9.33025 16.9713 8.44383 16.9713 7.42859C16.9713 6.41334 16.4197 5.52693 15.5999 5.05268M3.14567 19.3143H16.2827C16.6946 19.3143 17.0285 18.9433 17.0285 18.4857C17.0285 15.6091 14.6252 13.2771 9.71419 13.2771C4.80317 13.2771 2.3999 15.6091 2.3999 18.4857C2.3999 18.9433 2.7338 19.3143 3.14567 19.3143ZM12.457 7.42859C12.457 8.94343 11.229 10.1714 9.71419 10.1714C8.19935 10.1714 6.97133 8.94343 6.97133 7.42859C6.97133 5.91375 8.19935 4.68573 9.71419 4.68573C11.229 4.68573 12.457 5.91375 12.457 7.42859Z"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />
                            </svg>
                            <span class="text-xs font-medium text-white">{{
                                t('discover.enrolls', { count: course.students_count || 0 })
                            }}</span>
                        </div>
                        <div class="text-[0.6rem] text-white bg-white/20 rounded-md px-2 py-0.5">
                            {{ formatPrice(course) }}
                        </div>
                    </div>
                    <div class="relative">
                        <img
                            :src="getCoverUrl(course.cover)"
                            :alt="course.title"
                            class="w-full h-32 object-cover"
                        />
                    </div>
                    <div class="p-3 w-full flex-grow flex flex-col justify-between">
                        <div>
                            <h4 class="font-semibold text-white truncate" :title="course.title">
                                {{ course.title }}
                            </h4>
                            <p class="text-sm text-gray-100 truncate" :title="course.description">
                                {{ course.description || t('discover.noDescription') }}
                            </p>
                        </div>
                        <div
                            class="mt-2 flex items-center justify-start gap-2"
                            :class="isRTL ? 'flex-row-reverse' : ''"
                        >
                            <RouterLink
                                v-if="course.teacher_id || course.institute_id"
                                :to="
                                    course.teacher_id
                                        ? `/${$role}/profile/${course.teacher_id}`
                                        : `/${$role}/profile/${course.institute_id}`
                                "
                                @click.stop
                                class="text-xs text-white hover:underline z-10 relative"
                            >
                                {{ t('discover.allCourses.byline.by') }} @{{
                                    course.teacher?.username ||
                                    course.institute?.username ||
                                    t('discover.byline.anonymous')
                                }}
                            </RouterLink>
                            <div v-else class="text-xs text-white">
                                {{ t('discover.allCourses.byline.anonymous') }}
                            </div>
                        </div>
                    </div>
                </RouterLink>
            </div>

            <!-- Load More Button -->
            <div v-if="currentPage < lastPage" class="text-center mt-6">
                <button
                    @click="loadMoreCourses"
                    :disabled="isLoadingMore"
                    class="text-gray-500 hover:text-gray-800 cursor-pointer font-semibold text-sm py-2 px-6 disabled:text-gray-300 disabled:cursor-not-allowed transition-colors"
                >
                    <span v-if="isLoadingMore">{{ t('discover.loadingMore') }}</span>
                    <span v-else>{{ t('discover.loadMore') }}</span>
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center text-gray-500 py-16">
            <p class="font-semibold text-lg">{{ t('discover.allCourses.empty.title') }}</p>
            <p class="mt-1">{{ t('discover.allCourses.empty.description') }}</p>
        </div>
    </PageWrapper>
</template>

<script>
import { apiCall } from "@/services/apiService"
import PageWrapper from '@/components/shared/PageWrapper.vue'
import CategoriesBar from './CategoriesBar.vue'
import { RouterLink } from 'vue-router'
import AyhootCardSkeleton from './AyhootCardSkeleton.vue'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

export default {
    name: 'DiscoverCoursesIndex',
    components: {
        PageWrapper,
        RouterLink,
        AyhootCardSkeleton,
        CategoriesBar,
    },
    setup() {
        const { locale, t } = useI18n()
        const isRTL = computed(() => locale.value === 'fa')
        return { isRTL, t }
    },
    data() {
        return {
            allCourses: [],
            isInitialLoading: true,
            isLoadingMore: false,
            currentPage: 1,
            lastPage: 1,
            searchQuery: '',
            selectedFilter: 'all',
            selectedSort: 'latest',
        }
    },
    computed: {
        sortedAndFilteredCourses() {
            let filtered = [...this.allCourses]

            if (this.selectedFilter !== 'all') {
                filtered = filtered.filter((course) => course.monetize === this.selectedFilter)
            }

            if (this.searchQuery) {
                const lowerCaseQuery = this.searchQuery.toLowerCase()
                filtered = filtered.filter(
                    (course) =>
                        (course.title && course.title.toLowerCase().includes(lowerCaseQuery)) ||
                        (course.description && course.description.toLowerCase().includes(lowerCaseQuery)) ||
                        (course.teacher?.username && course.teacher.username.toLowerCase().includes(lowerCaseQuery)) ||
                        (course.institute?.username && course.institute.username.toLowerCase().includes(lowerCaseQuery))
                )
            }

            return filtered.sort((a, b) => {
                switch (this.selectedSort) {
                    case 'latest':
                        return new Date(b.created_at) - new Date(a.created_at)
                    case 'oldest':
                        return new Date(a.created_at) - new Date(b.created_at)
                    case 'most-resources':
                        return this.getResourceCount(b) - this.getResourceCount(a)
                    case 'least-resources':
                        return this.getResourceCount(a) - this.getResourceCount(b)
                    default:
                        return 0
                }
            })
        },
    },
    methods: {
        async fetchCourses(isLoadMore = false) {
            if (!isLoadMore) {
                this.isInitialLoading = true
            } else {
                this.isLoadingMore = true
            }

            try {
                const response = await apiCall('GET', `/api/discover/courses?page=${this.currentPage}`)
                const paginatedData = response.courses || response.recommended_courses
                const newCourses = paginatedData?.data || []

                if (isLoadMore) {
                    this.allCourses.push(...newCourses)
                } else {
                    this.allCourses = newCourses
                }

                this.lastPage = paginatedData?.last_page || 1
            } catch (error) {
                console.error('Failed to fetch Courses:', error)
                if (!isLoadMore) {
                    this.allCourses = []
                }
            } finally {
                if (!isLoadMore) {
                    this.isInitialLoading = false
                } else {
                    this.isLoadingMore = false
                }
            }
        },
        loadMoreCourses() {
            if (this.currentPage < this.lastPage && !this.isLoadingMore) {
                this.currentPage++
                this.fetchCourses(true)
            }
        },
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
    mounted() {
        this.fetchCourses()
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
</style>
