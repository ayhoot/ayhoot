<template>
    <InstructorLayout>
        <div class="">
            <!-- Tabs -->
            <!-- <DashTabs :tabs="myTabs" :folders="folders" /> -->
            <!-- Header Section -->
            <div class="pb-4">
                <h2 class="text-3xl font-bold mb-2">{{ t('purchasedContent.title') }}</h2>
                <p class="">{{ t('purchasedContent.description') }}</p>
            </div>

            <!-- Table Card -->
            <div class="overflow-hidden px-4">
                <!-- Table Header with Filters -->
                <div class="py-4 border-b border-gray-300 ">
                    <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                        <!-- Left: Search & Filters -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto">
                            <!-- Search -->
                            <div class="w-full sm:flex-1">
                                <AyInput v-model="filters.search" type="text"
                                    :placeholder="t('purchasedContent.searchPlaceholder')"
                                    class="min-w-80 focus:border-blue-500 font-bold" @update:model-value="handleSearch">
                                    <template #icon>
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </template>
                                </AyInput>
                            </div>
                            <AySelect v-model="filters.type" @update:model-value="handleTypeChange">
                                <option value="all">{{ t('purchasedContent.filters.all') }}</option>
                                <option value="exams">{{ t('purchasedContent.filters.exams') }}</option>
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
                            <img :src="exam.image || '/imgHolders/CardHolderImage.jpg'"
                                :alt="t('purchasedContent.card.imageAlt')"
                                class="w-full h-32 object-cover rounded-t-md" />
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-sm font-bold truncate max-w-[16ch] overflow-hidden text-ellipsis"
                                        :title="exam.title">
                                        {{ exam.title }}
                                    </h3>
                                    <span class="text-xs px-2 py-1 rounded-full font-medium" :class="exam.type === 'course'
                                        ? 'bg-blue-100 text-blue-800'
                                        : 'bg-green-100 text-green-800'
                                        ">
                                        {{
                                            exam.type === 'course'
                                                ? t('purchasedContent.courses')
                                                : t('purchasedContent.exams')
                                        }}
                                    </span>
                                </div>

                                <div class="flex items-center text-gray-600 text-xs font-semibold">
                                    @{{ exam.teacher?.username || '...' }}
                                    <span v-if="exam.institute" class="px-1 font-normal">{{
                                        t('purchasedContent.card.from')
                                        }}</span>
                                    @{{ exam.institute?.username || t('purchasedContent.card.selfEducated') }}
                                </div>

                                <div class="w-full flex justify-end text-xs pt-3 gap-1 rtl:gap-reverse">
                                    <ResponsiveButton class="text-xs w-full py-1 px-2" type="light-green" :to="exam.type === 'course'
                                        ? `/${$role}/course/${exam.id}`
                                        : `/${$role}/exam/${exam.id}/preview`
                                        ">
                                        {{ t('purchasedContent.card.open') }}
                                    </ResponsiveButton>

                                    <ResponsiveButton class="text-xs py-1 px-1" type="light-red"
                                        :title="t('purchasedContent.card.removeTitle')" @click="confirmDelete(exam.id)">
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
                            <AyButton :to="`/${$role}/exam/create`" type="dash-blue" class="inline-flex items-center">
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
        </div>
    </InstructorLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toast-notification'
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashRemove from '@/components/atoms/icons/DashRemove.vue'
import DashShare from '@/components/atoms/icons/DashShare.vue'
import DashTabs from '@/components/layout/InstructorDashTabs.vue'
import Pagination from '@/components/shared/Pagination.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'PurchasedIndex',
    components: {
        InstructorLayout,
        AyButton,
        ResponsiveButton,
        DashRemove,
        DashShare,
        DashTabs,
        Pagination,
        AySelect,
        AyInput,
    },
    setup() {
        const { t } = useI18n()
        const toast = useToast()
        return { t, toast }
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
                type: 'all', // all, exams, courses
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
                { label: this.t('ayhoot.tabs.ayhoots'), route: `/${this.$role}/exam` },
                { label: this.t('ayhoot.tabs.courses'), route: `/${this.$role}/exam/courses` },
                { label: this.t('ayhoot.tabs.purchased'), route: `/${this.$role}/exam/purchased` },
                { label: this.t('ayhoot.tabs.folders'), route: `/${this.$role}/exam/folders` },
                { label: this.t('ayhoot.tabs.archive'), route: `/${this.$role}/exam/archive` },
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
        async fetchFolders() {
            try {
                const response = await apiCall('GET', '/api/instructor/folder')
                this.folders = response.folders || []
            } catch (error) {
                console.error('Error fetching folders for tabs:', error)
            }
        },
        async fetchExams() {
            try {
                this.isLoading = true
                const queryParams = new URLSearchParams({
                    search: this.filters.search,
                    page: this.filters.page,
                    type: this.filters.type,
                }).toString()

                console.log('Fetching purchased content with params:', this.filters)
                const response = await apiCall('GET', `/api/instructor/purchased-content?${queryParams}`)
                console.log('Purchased content API response:', response)

                // Handle the new API response structure
                if (response.purchased_content && response.purchased_content.data) {
                    this.exams = response.purchased_content.data
                    this.pagination = {
                        current_page: response.purchased_content.current_page,
                        from: response.purchased_content.from,
                        to: response.purchased_content.to,
                        total: response.purchased_content.total,
                        last_page: response.purchased_content.last_page,
                    }
                } else {
                    this.exams = []
                    this.pagination = {
                        current_page: 1,
                        from: 0,
                        to: 0,
                        total: 0,
                        last_page: 0,
                    }
                }
                console.log('Processed exams:', this.exams.length, 'items')
            } catch (error) {
                console.error('Error fetching purchased content:', error)

                // Handle authentication errors specifically
                if (error.response && error.response.status === 401) {
                    console.log('Authentication error detected, redirecting to login')
                    this.toast.error(this.t('purchasedContent.notifications.authError'))
                    // Clear invalid token
                    localStorage.removeItem('token')
                    localStorage.removeItem('user')
                    localStorage.removeItem('role')
                    // Redirect to login
                    this.$router.push({ name: 'Login' })
                    return
                }

                // Handle other errors
                this.toast.error(this.t('purchasedContent.errorLoading'))
                this.exams = []
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
        handleTypeChange() {
            console.log('Content type changed to:', this.filters.type)
            this.filters.page = 1
            this.fetchExams()
        },
        checkAuthentication() {
            const token = localStorage.getItem('token')
            const userRole = localStorage.getItem('role')

            console.log('Authentication check:', { hasToken: !!token, role: userRole })

            if (!token) {
                console.log('No token found, redirecting to login')
                this.toast.error(this.t('purchasedContent.notifications.authError'))
                setTimeout(() => {
                    this.$router.push({ name: 'Login' })
                }, 1500)
                return false
            }

            if (userRole !== 'teacher' && userRole !== 'institute') {
                console.log('Invalid role for instructor route:', userRole)
                this.toast.error('Access denied. Please log in with a teacher or institute account.')
                setTimeout(() => {
                    this.$router.push({ name: 'Login' })
                }, 1500)
                return false
            }

            return true
        },
        handleUrlParams() {
            // Check authentication first
            if (!this.checkAuthentication()) {
                return
            }

            // Handle malformed URLs with multiple ? characters
            let searchString = window.location.search
            if (searchString.includes('?') && searchString.indexOf('?') !== searchString.lastIndexOf('?')) {
                // Fix malformed URL by replacing second ? with &
                searchString = searchString.replace(/\?(.+)\?/, '?$1&')
                console.log('Fixed malformed URL parameters:', searchString)
            }

            const urlParams = new URLSearchParams(searchString)

            // Handle content type filter from URL
            const type = urlParams.get('type')
            if (type && ['all', 'exams', 'courses'].includes(type)) {
                this.filters.type = type
            }

            // Handle payment success parameters
            const status = urlParams.get('status')
            const refId = urlParams.get('ref_id')
            const examId = urlParams.get('exam_id')

            if (status === 'success' && refId) {
                console.log('Payment success detected:', { status, refId, examId })

                // Check if we have valid authentication for payment success
                const token = localStorage.getItem('token')
                if (!token) {
                    console.log('Payment success but no auth token, showing login prompt')
                    this.toast.error('Payment successful! Please log in to access your purchased content.')
                    setTimeout(() => {
                        this.$router.push({
                            name: 'Login',
                            query: {
                                redirect: window.location.pathname,
                                purchaseSuccess: 'true',
                                examId: examId,
                            },
                        })
                    }, 2000)
                    return
                }

                // Show success message with delay to ensure page is loaded
                setTimeout(() => {
                    this.toast.success(this.t('purchasedContent.notifications.purchaseSuccess'))
                }, 1000)

                // If exam_id is provided, filter to exams
                if (examId) {
                    this.filters.type = 'exams'
                }

                // Clean up URL parameters after handling
                const cleanUrl =
                    window.location.pathname + (this.filters.type !== 'all' ? `?type=${this.filters.type}` : '')
                window.history.replaceState({}, document.title, cleanUrl)
            }
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
                    await apiCall('GET', `/api/instructor/exam/delete/${examId}`)
                    this.toast.success(this.t('purchasedContent.notifications.deleteSuccess'))
                    await this.fetchExams()
                } catch (error) {
                    this.toast.error(this.t('purchasedContent.notifications.deleteFailed'))
                    console.error('Error deleting exam:', error)
                }
            }
        },
    },
    async mounted() {
        try {
            // Handle query parameters from URL (includes auth check)
            this.handleUrlParams()

            // Only proceed if authentication is valid
            if (this.checkAuthentication()) {
                await this.fetchExams()
                await this.fetchFolders()
            }
        } catch (error) {
            console.error('Error in mounted hook:', error)
            this.toast.error(this.t('purchasedContent.errorLoading'))
        }
    },
}
</script>
