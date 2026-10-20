<template>
    <PageWrapper>
        <!-- Tabs -->
        <DashTabs :tabs="myTabs" :folders="folders" />
        <!-- Header Section -->
        <div class="pb-4">
            <h2 class="text-3xl font-bold mb-2">{{ t('purchasedContent.title') }}</h2>
            <p>{{ t('purchasedContent.description') }}</p>
        </div>

        <!-- Table Card -->
        <div class="overflow-hidden px-4">
            <!-- Table Header with Filters -->
            <div class="py-4 border-b border-gray-300">
                <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                    <!-- Left: Search & Filters -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto">
                        <!-- Search -->
                        <AyInput type="text" v-model="filters.search" @update:model-value="handleSearch"
                            :placeholder="t('purchasedContent.searchPlaceholder')"
                            class="font-bold bg-gray-50 min-w-80">
                            <template #icon>
                                <svg class="h-5 w-5 text-gray-700" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </template>
                        </AyInput>
                        <AySelect v-model="filters.type" @update:model-value="handleTypeChange">
                            <option value="ayhoots">{{ t('purchasedContent.filters.exams') }}</option>
                            <option value="courses">{{ t('purchasedContent.filters.courses') }}</option>
                        </AySelect>
                    </div>
                </div>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <div v-if="filteredExams.length && !isLoading"
                    class="py-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <div v-for="exam in filteredExams" :key="exam.id" class="bg-white rounded-md shadow-sm">
                        <img :src="getAssetUrl(exam.image || exam.cover)" :alt="t('purchasedContent.card.imageAlt')"
                            class="w-full h-32 object-cover rounded-t-md" />
                        <div class="p-4">
                            <h3 class="text-sm font-bold truncate max-w-[16ch] overflow-hidden text-ellipsis pb-1"
                                :title="exam.title">
                                {{ exam.title }}
                            </h3>

                            <div class="flex items-center text-gray-600 text-xs font-semibold">
                                @{{ exam.teacher?.username || '...' }}
                                <span v-if="exam.institute" class="px-1 font-normal">{{
                                    t('purchasedContent.card.from')
                                    }}</span>
                                @{{ exam.institute?.username || t('purchasedContent.card.selfEducated') }}
                            </div>

                            <div class="w-full flex justify-end text-xs pt-3 gap-1 rtl:gap-reverse">
                                <ResponsiveButton class="text-xs w-full py-1 px-2" type="light-green"
                                    :to="filters.type === 'courses' ? `/student/course/${exam.id}` : `/student/exam/${exam.id}/detail`">
                                    {{ t('purchasedContent.card.open') }}
                                </ResponsiveButton>

                                <ResponsiveButton v-if="filters.type !== 'courses'" class="text-xs py-1 px-1"
                                    type="light-red" :title="t('purchasedContent.card.removeTitle')"
                                    @click="confirmDelete(exam.id)">
                                    <DashRemove />
                                </ResponsiveButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="!isLoading" class="text-center py-12">
                    <svg width="36" height="36" class="mx-auto" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <g id="Iconly/Regular/Light/Buy">
                            <g id="Buy">
                                <path id="Stroke 1"
                                    d="M2.75012 3.25003L4.83012 3.61003L5.79312 15.083C5.87012 16.02 6.65312 16.739 7.59312 16.736H18.5021C19.3991 16.738 20.1601 16.078 20.2871 15.19L21.2361 8.63203C21.3421 7.89903 20.8331 7.21903 20.1011 7.11303C20.0371 7.10403 5.16412 7.09903 5.16412 7.09903"
                                    stroke="currentColor" class="text-gray-400" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path id="Stroke 3" d="M14.1251 10.7949H16.8981" stroke="currentColor"
                                    class="text-gray-400" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path id="Stroke 5" fill-rule="evenodd" clip-rule="evenodd"
                                    d="M7.15447 20.2026C7.45547 20.2026 7.69847 20.4466 7.69847 20.7466C7.69847 21.0476 7.45547 21.2916 7.15447 21.2916C6.85347 21.2916 6.61047 21.0476 6.61047 20.7466C6.61047 20.4466 6.85347 20.2026 7.15447 20.2026Z"
                                    stroke="currentColor" class="text-gray-400" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path id="Stroke 7" fill-rule="evenodd" clip-rule="evenodd"
                                    d="M18.4347 20.2026C18.7357 20.2026 18.9797 20.4466 18.9797 20.7466C18.9797 21.0476 18.7357 21.2916 18.4347 21.2916C18.1337 21.2916 17.8907 21.0476 17.8907 20.7466C17.8907 20.4466 18.1337 20.2026 18.4347 20.2026Z"
                                    stroke="currentColor" class="text-gray-400" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </g>
                        </g>
                    </svg>

                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        {{ t('purchasedContent.empty.title') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">{{ t('purchasedContent.empty.description') }}</p>
                    <div class="mt-6">
                        <AyButton to="/student/discover" type="dash-blue" class="inline-flex items-center">
                            {{ t('purchasedContent.empty.explore') }}
                        </AyButton>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-else class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                    <p class="mt-2 text-sm text-gray-500">{{ t('purchasedContent.loading') }}</p>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination v-if="!isLoading" :pagination="pagination" @page-changed="changePage"
                :wrapper-class="'px-6 py-4 border-t border-gray-100'" />
        </div>
    </PageWrapper>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toast-notification'
import PageWrapper from '@/components/shared/PageWrapper.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashRemove from '@/components/atoms/icons/DashRemove.vue'
import DashTabs from '@/components/layout/StudentDashTabs.vue'
import Pagination from '@/components/shared/Pagination.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog } = useConfirm()

export default {
    name: 'PurchasedIndex',
    components: {
        PageWrapper,
        AyButton,
        ResponsiveButton,
        DashRemove,
        DashTabs,
        Pagination,
        AySelect,
        AyInput,
    },
    setup() {
        const { t } = useI18n()
        const $toast = useToast()
        return { t, $toast }
    },
    data() {
        return {
            exams: [],
            isLoading: false,
            filters: {
                search: '',
                status: '',
                sort: 'created_at',
                page: 1,
                type: 'ayhoots',
            },
            pagination: {
                current_page: 1,
                from: 0,
                to: 0,
                total: 0,
                last_page: 0,
            },
            folders: [],
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
        filteredExams() {
            let filtered = [...this.exams]
            if (this.filters.search) {
                const query = this.filters.search.toLowerCase()
                filtered = filtered.filter(
                    (exam) =>
                        exam.title.toLowerCase().includes(query) ||
                        (exam.subject && exam.subject.toLowerCase().includes(query))
                )
            }
            if (this.filters.status) {
                filtered = filtered.filter((exam) => exam.status === this.filters.status)
            }
            filtered.sort((a, b) => {
                if (this.filters.sort === 'title') {
                    return a.title.localeCompare(b.title)
                } else if (this.filters.sort === 'status') {
                    return a.status.localeCompare(b.status)
                } else {
                    return new Date(b.created_at) - new Date(a.created_at)
                }
            })
            return filtered
        },
    },
    methods: {
        async fetchExams() {
            try {
                this.isLoading = true
                const queryParams = new URLSearchParams(this.filters).toString()

                let response
                if (this.filters.type === 'courses') {
                    response = await apiCall('GET', `/api/student/course/purchased?${queryParams}`)
                    const page = response.courses || {}
                    this.exams = page.data || []
                    this.pagination = {
                        current_page: page.current_page,
                        from: page.from || 0,
                        to: page.to || 0,
                        total: page.total || 0,
                        last_page: page.last_page || 0,
                    }
                } else {
                    response = await apiCall('GET', `/api/student/purchased/exams?${queryParams}`)
                    const page = response.exam || {}
                    this.exams = page.data || []
                    this.pagination = {
                        current_page: page.current_page,
                        from: page.from || 0,
                        to: page.to || 0,
                        total: page.total || 0,
                        last_page: page.last_page || 0,
                    }
                }
            } catch (error) {
                console.error('Error fetching exams:', error)
            } finally {
                this.isLoading = false
            }
        },
        handleSearch() {
            if (this.searchTimeout) clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.filters.page = 1
                this.fetchExams()
            }, 300)
        },
        changePage(page) {
            this.filters.page = page
            this.fetchExams()
        },
        getAssetUrl(path) {
            if (!path) return '/imgHolders/CardHolderImage.jpg'
            if (path.startsWith('http') || path.startsWith('/')) return path
            const base = import.meta.env.VITE_API_BASE_URL || ''
            return `${base}/uploads/${path.replace(/\\/g, '')}`
        },
        handleTypeChange() {
            this.filters.page = 1
            this.exams = []
            this.fetchExams()
        },
        formatDate(dateString) {
            if (!dateString) return ''
            const locale = this.$i18n.locale === 'fa' ? 'fa-IR' : 'en-US'
            return new Date(dateString).toLocaleDateString(locale, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            })
        },
        async confirmDelete(examId) {
            if (await confirmDialog({ message: this.t('purchasedContent.notifications.confirmDelete'), variant: 'danger' })) {
                try {
                    await apiCall('GET', `/api/student/exam/delete/${examId}`)
                    this.$toast.success(this.t('purchasedContent.notifications.deleteSuccess'))
                    await this.fetchExams()
                } catch (error) {
                    this.$toast.error(this.t('purchasedContent.notifications.deleteFailed'))
                    console.error('Error deleting exam:', error)
                }
            }
        },
    },
    async mounted() {
        await this.fetchExams()
    },
}
</script>
