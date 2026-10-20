<template>
    <PageWrapper>
        <div class="grid grid-cols-1 lg:grid-cols-4 sm:gap-2 gap-4 w-full">
            <!-- 1st Col -->
            <div class="px-1">
                <!-- Props passed to DashProfileCard -->
                <DashProfileCard
                    :loading="isLoading"
                    :student="student"
                    :totalCourses="totalCourses"
                    :totalExams="totalExamsAttended"
                />
            </div>
            <!-- 2nd Col -->
            <div class="lg:col-span-2 px-1 flex flex-col space-y-4">
                <DashBannerCard />
                <!-- Props passed to DashCoursesCard -->
                <DashCoursesCard :courses="latestCourses" :totalCourses="totalCourses" :loading="isLoading" />
            </div>
            <!-- 3rd Col -->
            <div class="px-1 flex flex-col space-y-4">
                <!-- Props passed to DashAyhootsCard -->
                <DashAyhootsCard :exams="latestExams" :totalExams="totalExamsAttended" :loading="isLoading" />
                <!-- Props passed to DashReportsCard -->
                <DashReportsCard
                    :reports="latestReports"
                    :totalReports="latestReports.length"
                    :loading="isLoading"
                />
            </div>
        </div>
    </PageWrapper>
</template>

<script>
import { apiCall } from '@/services/apiService'
import PageWrapper from '@/components/shared/PageWrapper.vue'
import DashProfileCard from '@/components/layout/StudentDashProfileCard.vue'
import DashCoursesCard from '@/components/layout/StudentDashCoursesCard.vue'
import DashAyhootsCard from '@/components/layout/StudentDashAyhootsCard.vue'
import DashReportsCard from '@/components/layout/StudentDashReportsCard.vue'
import DashBannerCard from '@/components/layout/StudentDashBannerCard.vue'

export default {
    name: 'Dashboard',
    components: {
        PageWrapper,
        DashProfileCard,
        DashCoursesCard,
        DashAyhootsCard,
        DashReportsCard,
        DashBannerCard,
    },
    data() {
        return {
            isLoading: true,
            dashboardData: null,
        }
    },
    computed: {
        studentData() {
            // Provide a fallback empty object to prevent errors if dashboardData is null
            return this.dashboardData?.student_data || {}
        },
        student() {
            return this.studentData.student || null
        },
        latestCourses() {
            return this.studentData.latest_courses || []
        },
        latestExams() {
            return this.studentData.latest_exams || []
        },
        latestReports() {
            return this.studentData.latest_reports || []
        },
        totalCourses() {
            return this.studentData.total_courses || 0
        },
        totalExamsAttended() {
            return this.studentData.total_exams_attended || 0
        },
    },
    async mounted() {
        await this.fetchDashboardData()
    },
    methods: {
        async fetchDashboardData() {
            this.isLoading = true // Show skeletons
            try {
                const response = await apiCall('GET', '/api/student/dashboard')
                this.dashboardData = response.dashboard
            } catch (error) {
                console.error('Error fetching student dashboard data:', error)
                // Ensure data is null on failure so templates don't break
                this.dashboardData = null
            } finally {
                this.isLoading = false
            }
        },
    },
}
</script>
