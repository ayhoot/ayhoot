<template>
    <PageWrapper>
        <!-- Tabs -->
        <DashTabs :tabs="myTabs" :folders="folders" />

        <!-- Header Section -->
        <div class="pb-4">
            <h2 class="text-3xl font-bold mb-2">{{ t('myCourses.title') }}</h2>
            <p>{{ t('myCourses.description') }}</p>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Table Header with Filters -->
            <div class="p-4 border-b border-gray-100">
                <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                    <!-- Left: Search & Filters -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto">
                        <!-- Search -->
                        <AyInput type="text" v-model="filters.search" @update:model-value="handleSearch"
                            :placeholder="t('myCourses.searchPlaceholder')" class="min-w-80 bg-gray-50 font-bold">
                            <template #icon>
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </template>
                        </AyInput>

                        <!-- Sort By -->
                        <AySelect v-model="filters.sort" @update:model-value="fetchCourses">
                            <option value="latest">{{ t('myCourses.filters.latest') }}</option>
                            <option value="oldest">{{ t('myCourses.filters.oldest') }}</option>
                        </AySelect>
                    </div>

                    <!-- Discover button on the right -->
                    <AyButton to="/student/discover/courses" type="dash-blue"
                        class="w-full sm:w-auto inline-flex items-center justify-center sm:justify-start">
                        {{ t('myCourses.empty.browse') }}
                    </AyButton>
                </div>
            </div>

            <!-- Table Content / Loading / Empty State -->
            <div class="overflow-x-auto">
                <DataTable v-if="courses.length && !isLoading" :columns="courseColumns" :rows="courses"
                    row-class="hover:bg-gray-50 transition-colors duration-150">
                    <template #header:actions="{ column }">
                        <span class="sr-only">{{ column.label }}</span>
                    </template>

                    <template #cell:details="{ row: course }">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 h-12 w-12 bg-purple-100 rounded-xl flex items-center justify-center overflow-hidden">
                                <img :src="getAssetUrl(course.cover)" :alt="course.title"
                                    class="w-full h-full object-cover" />
                            </div>
                            <div :class="$i18n.locale === 'fa' ? 'mr-4' : 'ml-4'">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-[20ch] overflow-hidden text-ellipsis"
                                    :title="course.title">
                                    {{ course.title }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ formatDate(course.created_at) }}
                                </div>
                            </div>
                        </div>
                    </template>

                    <template #cell:instructor="{ row: course }">
                        <div class="text-sm text-gray-900 font-medium">
                            {{ course.teacher?.name || course.institute?.name || '...' }}
                        </div>
                        <div class="text-sm text-gray-500 font-semibold">
                            @{{ course.teacher?.username || course.institute?.username || '...' }}
                        </div>
                    </template>

                    <template #cell:category="{ row: course }">
                        <div class="text-sm text-gray-900">
                            {{ course.category?.title || t('ayhoot.table.body.noCategory') }}
                        </div>
                    </template>

                    <template #cell:accessibility="{ row: course }">
                        <div v-if="course.monetize === 'free'" class="text-sm text-gray-900">
                            {{ t('ayhoot.table.body.free') }}
                        </div>
                        <div v-else class="text-sm text-gray-900 font-medium">
                            {{ course.price ? Number(course.price).toLocaleString() + ' Rials' :
                                t('ayhoot.table.body.free') }}
                        </div>
                    </template>

                    <template #cell:actions="{ row: course }">
                        <div class="flex items-center justify-end">
                            <ResponsiveButton type="light-blue" :to="`/student/course/${course.id}`"
                                class="text-xs px-2 py-1 mx-0.5">
                                {{ t('contentCard.viewCourse') }}
                            </ResponsiveButton>
                        </div>
                    </template>
                </DataTable>

                <!-- Loading State -->
                <div v-else-if="isLoading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                    <p class="mt-2 text-sm text-gray-500">{{ t('ayhoot.loading') }}</p>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12 px-4">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" class="mx-auto text-gray-400"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ t('myCourses.empty.title') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ t('myCourses.empty.description') }}</p>
                    <div class="mt-6">
                        <AyButton to="/student/discover/courses" type="dash-blue" class="inline-flex items-center">
                            {{ t('myCourses.empty.browse') }}
                        </AyButton>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination v-if="!isLoading && courses.length" :pagination="pagination" @page-changed="changePage"
                wrapper-class="px-6 py-4 border-t border-gray-100" />
        </div>
    </PageWrapper>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useI18n } from 'vue-i18n'
import PageWrapper from '@/components/shared/PageWrapper.vue'
import DashTabs from '@/components/layout/StudentDashTabs.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import Pagination from '@/components/shared/Pagination.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import DataTable from '@/components/shared/DataTable.vue'

export default {
    name: 'StudentMyCourses',
    components: {
        PageWrapper,
        DashTabs,
        AyButton,
        ResponsiveButton,
        Pagination,
        AySelect,
        AyInput,
        DataTable
    },
    setup() {
        const { t } = useI18n()
        return { t }
    },
    data() {
        return {
            isLoading: false,
            courses: [],
            folders: [],
            page: 1,
            pagination: { current_page: 1, from: 0, to: 0, total: 0, last_page: 1 },
            filters: { search: '', sort: 'latest' },
            searchTimeout: null,
        }
    },
    computed: {
        myTabs() {
            return [
                { label: this.t('ayhoot.tabs.ayhoots'), route: '/student/exam' },
                { label: this.t('ayhoot.tabs.assigned'), route: '/student/exam/assignments' },
                { label: this.t('ayhoot.tabs.courses'), route: '/student/courses' },
                { label: this.t('ayhoot.tabs.purchased'), route: '/student/exam/purchased' },
            ]
        },
        courseColumns() {
            return [
                { key: 'details', label: this.t('ayhoot.table.header.details'), thClass: 'py-2' },
                { key: 'instructor', label: this.t('contentCard.teacher') },
                { key: 'category', label: this.t('ayhoot.table.header.category') },
                { key: 'accessibility', label: this.t('ayhoot.table.header.accessibility') },
                { key: 'actions', label: this.t('ayhoot.table.header.actions'), align: 'right', thClass: 'relative', tdClass: 'font-medium' },
            ]
        }
    },
    async mounted() {
        await this.fetchCourses()
    },
    methods: {
        getAssetUrl(path) {
            if (!path) return '/imgHolders/CardHolderImage.jpg'
            if (path.startsWith('http')) return path
            const base = import.meta.env.VITE_API_BASE_URL || ''
            return `${base}/uploads/${path.replace(/\\/g, '')}`
        },
        formatDate(dateString) {
            if (!dateString) return this.t('ayhoot.table.body.notAvailable')
            try {
                const locale = this.$i18n.locale === 'fa' ? 'fa-IR' : 'en-US'
                return new Date(dateString).toLocaleDateString(locale, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                })
            } catch (e) {
                return this.t('ayhoot.table.body.invalidDate')
            }
        },
        async fetchCourses() {
            this.isLoading = true
            try {
                const qs = new URLSearchParams({
                    page: this.page,
                    search: this.filters.search,
                    sort: this.filters.sort,
                }).toString()
                const response = await apiCall('GET', `/api/student/course?${qs}`)
                this.courses = response.courses.data || []
                this.pagination = {
                    current_page: response.courses.current_page,
                    from: response.courses.from || 0,
                    to: response.courses.to || 0,
                    total: response.courses.total || 0,
                    last_page: response.courses.last_page || 1,
                }
            } catch (error) {
                console.error('Error fetching courses:', error)
                this.courses = []
            } finally {
                this.isLoading = false
            }
        },
        handleSearch() {
            // Debounce: re-fetch 400ms after the user stops typing, resetting to page 1.
            if (this.searchTimeout) clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.page = 1
                this.fetchCourses()
            }, 400)
        },
        changePage(page) {
            this.page = page
            this.fetchCourses()
        },
    },
}
</script>
