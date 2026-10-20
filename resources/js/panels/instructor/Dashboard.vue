<template>
    <InstructorLayout :secondary="false">
            <div
                class="grid grid-cols-1 lg:grid-cols-4 w-full sm:gap-2 gap-4"
            >
                <div class="px-1">
                    <DashProfileCard :dashboardData="fullDashboardData" :loading="isLoading" />
                </div>
                <div class="lg:col-span-2 px-1 flex flex-col space-y-4">
                    <DashBannerCard />
                    <DashCoursesCard :courses="courses" :totalCourses="displayTotalCourses" :loading="isLoading" />
                </div>
                <div class="px-1 flex flex-col space-y-4">
                    <!-- MODIFIED: Pass the `isLoading` state as the `loading` prop -->
                    <DashAyhootsCard :exams="exams" :totalExams="displayTotalExams" :loading="isLoading" />
                    <HostLiveCard :exams="exams" :loading="isLoading" />
                    <DashReportsCard :loading="isLoading" />
                </div>
            </div>
    </InstructorLayout>
</template>

<script>
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import Footer from '@/components/layout/Footer.vue'
import DashProfileCard from '@/components/layout/InstructorDashProfileCard.vue'
import DashBannerCard from '@/components/layout/InstructorDashBannerCard.vue'
import DashCoursesCard from '@/components/layout/InstructorDashCoursesCard.vue'
import DashAyhootsCard from '@/components/layout/InstructorDashAyhootsCard.vue'
import HostLiveCard from '@/components/shared/HostLiveCard.vue'
import DashReportsCard from '@/components/layout/InstructorDashReportsCard.vue'
import { apiCall } from "@/services/apiService"

export default {
    name: 'Dashboard',
    components: {
        InstructorLayout,
        Footer,
        DashProfileCard,
        DashBannerCard,
        DashCoursesCard,
        DashAyhootsCard,
        HostLiveCard,
        DashReportsCard,
    },
    data() {
        return {
            isLoading: true,
            fullDashboardData: null,
            teacherDashboardData: null,
            instituteDashboardData: null,
            currentInstituteId: null,
            exams: [],
            courses: [],
        }
    },
    computed: {
        displayTotalStudents() {
            return this.currentInstituteId && this.instituteDashboardData
                ? this.instituteDashboardData.total_students
                : this.teacherDashboardData
                  ? this.teacherDashboardData.total_students
                  : 0
        },
        displayTotalCourses() {
            return this.currentInstituteId && this.instituteDashboardData
                ? this.instituteDashboardData.total_courses
                : this.teacherDashboardData
                  ? this.teacherDashboardData.total_courses
                  : 0
        },
        displayTotalExams() {
            return this.currentInstituteId && this.instituteDashboardData
                ? this.instituteDashboardData.total_exams
                : this.teacherDashboardData
                  ? this.teacherDashboardData.total_exams
                  : 0
        },
    },
    async mounted() {
        await this.fetchDashboardData()
        window.addEventListener('refresh-dashboard-data', this.fetchDashboardData)
    },
    beforeUnmount() {
        window.removeEventListener('refresh-dashboard-data', this.fetchDashboardData)
    },
    methods: {
        async fetchDashboardData() {
            this.isLoading = true
            try {
                const response = await apiCall('GET', `/api/instructor/dashboard`)
                if (response) {
                    this.fullDashboardData = response
                    this.teacherDashboardData = response.dashboard.teacher_data
                    this.instituteDashboardData = response.dashboard.institute_data
                    this.currentInstituteId = response.dashboard.current_institute_id
                    this.exams = response.exams || []
                    this.courses = response.courses || []
                }
            } catch (error) {
                console.error('Error fetching dashboard data:', error)
                this.$toast.error('Failed to load dashboard data.')
            } finally {
                this.isLoading = false
            }
        },
    },
}
</script>
