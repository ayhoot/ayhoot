<!-- CategoryIndex.vue -->
<template>
    <PublicLayout padded>
        <div
            class="w-full flex justify-center items-center rounded-xl mb-4 bg-[url('/imgHolders/cardimgholder.png')] bg-cover bg-center bg-no-repeat"
        >
            <div class="text-center py-16 w-full rounded-xl bg-black/40 text-white">
                <h2 class="text-3xl font-bold mb-2">All {{ subjectName }} Contents</h2>
                <p class="">Discover the best {{ subjectName }} contents available on Ayhoot!</p>
            </div>
        </div>

        <!-- Featured sections -->
        <TopPicksGrid :top_ayhoots="topExams.slice(0, 5)" :loading="isLoading" />
        <VerifiedCourses :verified_courses="verifiedCourses.slice(0, 5)" :loading="isLoading" />

        <!-- MAIN CONTENT COMPONENT -->
        <MassContent
            :title="t('category.allContentTitle')"
            :items="allContent"
            :loading="isLoading"
            :is-loading-more="isLoadingMore"
            :has-more-exams="hasMoreExams"
            :has-more-courses="hasMoreCourses"
            @load-more="loadMoreContent"
        />
    </PublicLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useI18n } from 'vue-i18n'
import TopPicksGrid from '@/components/discover/public/TopPicksGrid.vue'
import VerifiedCourses from '@/components/discover/public/VerifiedCourses.vue'
import MassContent from '@/components/discover/public/MassContent.vue'
import PublicLayout from '@/components/layout/PublicLayout.vue'

export default {
    name: 'CategoryIndex',
    components: {
        TopPicksGrid,
        VerifiedCourses,
        MassContent,
        PublicLayout,
    },
    setup() {
        const { t } = useI18n()
        return { t }
    },
    data() {
        return {
            isSidebarOpen: false,
            isLoading: true,
            isLoadingMore: false,
            subjectName: '',
            topExams: [],
            verifiedCourses: [],
            allExams: [],
            allCourses: [],
            examPage: 1,
            examLastPage: 1,
            coursePage: 1,
            courseLastPage: 1,
        }
    },
    computed: {
        teacherName() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user.name || this.t('discover.defaultTeacher')
        },
        teacherUsername() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user.username || this.t('discover.defaultTeacher')
        },
        allContent() {
            const typedExams = this.allExams.map((exam) => ({ ...exam, type: 'exam' }))
            const typedCourses = this.allCourses.map((course) => ({ ...course, type: 'course' }))
            return [...typedExams, ...typedCourses]
        },
        hasMoreExams() {
            return this.examPage < this.examLastPage
        },
        hasMoreCourses() {
            return this.coursePage < this.courseLastPage
        },
    },
    watch: {
        '$route.params.subject'(newVal, oldVal) {
            if (newVal && newVal !== oldVal) {
                this.fetchDiscover()
            }
        },
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        async fetchDiscover() {
            this.isLoading = true
            this.examPage = 1
            this.coursePage = 1
            this.allExams = []
            this.allCourses = []

            try {
                const subjectId = this.$route.params.subject
                const categoryParam = subjectId ? `&category_id=${subjectId}` : ''

                const [categoryRes, examsRes, coursesRes] = await Promise.all([
                    apiCall('GET', `/api/home/category/${subjectId}`),
                    apiCall('GET', `/api/discover/exams?page=1${categoryParam}`),
                    apiCall('GET', `/api/discover/courses?page=1${categoryParam}`),
                ])

                this.subjectName = categoryRes.category?.title || 'Category'
                this.topExams = categoryRes.top_ayhoots || []
                this.verifiedCourses = categoryRes.verified_courses || []

                const examPaginator = examsRes?.exams
                this.allExams = examPaginator?.data || []
                this.examLastPage = examPaginator?.last_page || 1

                const coursePaginator = coursesRes?.courses
                this.allCourses = coursePaginator?.data || []
                this.courseLastPage = coursePaginator?.last_page || 1
            } catch (error) {
                console.error('Failed to fetch category data:', error)
                this.$toast?.error(this.t('discover.fetchError'))
            } finally {
                this.isLoading = false
            }
        },
        async loadMoreContent({ type }) {
            if (this.isLoadingMore) return
            this.isLoadingMore = true

            const subjectId = this.$route.params.subject
            const categoryParam = subjectId ? `&category_id=${subjectId}` : ''
            const promises = []

            if ((type === 'exam' || type === 'all') && this.hasMoreExams) {
                const nextPage = this.examPage + 1
                promises.push(
                    apiCall('GET', `/api/discover/exams?page=${nextPage}${categoryParam}`)
                        .then((res) => {
                            const newExams = res?.exams?.data || []
                            this.allExams = [...this.allExams, ...newExams]
                            this.examPage = nextPage
                            this.examLastPage = res?.exams?.last_page || this.examLastPage
                        })
                )
            }

            if ((type === 'course' || type === 'all') && this.hasMoreCourses) {
                const nextPage = this.coursePage + 1
                promises.push(
                    apiCall('GET', `/api/discover/courses?page=${nextPage}${categoryParam}`)
                        .then((res) => {
                            const newCourses = res?.courses?.data || []
                            this.allCourses = [...this.allCourses, ...newCourses]
                            this.coursePage = nextPage
                            this.courseLastPage = res?.courses?.last_page || this.courseLastPage
                        })
                )
            }

            try {
                await Promise.all(promises)
            } catch (error) {
                console.error('Failed to load more discover content:', error)
            } finally {
                this.isLoadingMore = false
            }
        },
    },
    mounted() {
        this.fetchDiscover()
    },
}
</script>
