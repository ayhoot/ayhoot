<template>
    <PublicLayout padded>
        <CategoriesCarousel :categories="categories" :loading="isLoading" />
        <TopPicksGrid :top_ayhoots="top_ayhoots" :loading="isLoading" />
        <VerifiedCourses :verified_courses="verifiedCourses" :loading="isLoading" />
        <VerifiedCreators :creators="verifiedCreators" :loading="isLoading" />
        <RecommendedByAyhoot :recommended="recommendedAyhoots" :loading="isLoading" />
        <VerifiedInstitutes :institutes="verifiedInstitutes" :loading="isLoading" />
        <LabeledCollection v-for="label in otherLabels" :key="label.id" :label="label" :loading="isLoading" />

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
import PublicLayout from '@/components/layout/PublicLayout.vue'
import CategoriesCarousel from '@/components/discover/public/CategoriesCarousel.vue'
import TopPicksGrid from '@/components/discover/public/TopPicksGrid.vue'
import RecommendedByAyhoot from '@/components/discover/public/RecommendedByAyhoot.vue'
import VerifiedCourses from '@/components/discover/public/VerifiedCourses.vue'
import VerifiedCreators from '@/components/discover/public/VerifiedCreators.vue'
import VerifiedInstitutes from '@/components/discover/public/VerifiedInstitutes.vue'
import LabeledCollection from '@/components/discover/public/LabeledCollection.vue'
import MassContent from '@/components/discover/public/MassContent.vue'

export default {
    name: 'DiscoverIndex',
    components: {
        PublicLayout,
        CategoriesCarousel,
        TopPicksGrid,
        RecommendedByAyhoot,
        VerifiedCourses,
        VerifiedCreators,
        VerifiedInstitutes,
        LabeledCollection,
        MassContent,
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
            categories: [],
            top_ayhoots: [],
            labels: [],
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
        recommendedAyhoots() {
            const recLabel = this.labels.find((label) => label.title.toLowerCase() === 'recommended')
            return recLabel?.exams || []
        },
        verifiedCreators() {
            const verifiedLabel = this.labels.find((label) => label.title.toLowerCase() === 'verified')
            return verifiedLabel?.teachers || []
        },
        verifiedInstitutes() {
            const verifiedLabel = this.labels.find((label) => label.title.toLowerCase() === 'verified')
            return verifiedLabel?.institutes || []
        },
        verifiedCourses() {
            const verifiedLabel = this.labels.find((label) => label.title.toLowerCase() === 'verified')
            return verifiedLabel?.courses || []
        },
        otherLabels() {
            const exclude = ['recommended', 'verified']
            return this.labels.filter((label) => !exclude.includes(label.title.toLowerCase()))
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
                const [response, examsRes, coursesRes] = await Promise.all([
                    apiCall('GET', '/api/discover'),
                    apiCall('GET', '/api/discover/exams?page=1'),
                    apiCall('GET', '/api/discover/courses?page=1'),
                ])

                this.categories = response.categories || []
                this.top_ayhoots = response.top_ayhoots || []
                this.labels = response.labels || []

                const examPaginator = examsRes?.exams
                this.allExams = examPaginator?.data || []
                this.examLastPage = examPaginator?.last_page || 1

                const coursePaginator = coursesRes?.courses
                this.allCourses = coursePaginator?.data || []
                this.courseLastPage = coursePaginator?.last_page || 1
            } catch (error) {
                console.error('Failed to fetch discovery data:', error)
                this.$toast?.error(this.t('discover.fetchError'))
            } finally {
                this.isLoading = false
            }
        },
        async loadMoreContent({ type }) {
            if (this.isLoadingMore) return
            this.isLoadingMore = true

            const promises = []

            if ((type === 'exam' || type === 'all') && this.hasMoreExams) {
                const nextPage = this.examPage + 1
                promises.push(
                    apiCall('GET', `/api/discover/exams?page=${nextPage}`)
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
                    apiCall('GET', `/api/discover/courses?page=${nextPage}`)
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
