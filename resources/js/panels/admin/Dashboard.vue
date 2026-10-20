<template>
    <div class="bg-gray-50 min-h-screen">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" />
        </div>
        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />

            <main class="flex-1 lg:ml-64 p-4 sm:p-6 overflow-y-auto mt-16">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">Admin Dashboard</h2>
                </div>

                <div v-if="loading">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div v-for="n in 4" :key="n" class="bg-white p-6 rounded-lg shadow-md animate-pulse">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-gray-200 mr-4"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                    <div class="h-8 bg-gray-200 rounded w-1/2"></div>
                                    <div class="h-3 bg-gray-200 rounded w-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-lg shadow-md h-96 animate-pulse"></div>
                        <div class="bg-white p-6 rounded-lg shadow-md h-96 animate-pulse"></div>
                    </div>
                </div>

                <div v-else>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
                            <div class="rounded-full bg-indigo-100 p-3 mr-4 text-indigo-600">
                                <svg
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M3.9999 14.9999V12.3312M9.33323 14.9999V12.3312M14.6666 14.9999V12.3312M19.9999 14.9999V12.3312M2.3999 18.3999H21.5999V21.5999H2.3999V18.3999ZM2.3999 8.7999V6.66657L11.6054 2.3999L21.5999 6.66657V8.7999H2.3999Z"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-gray-500 text-sm font-semibold">Total Institutes</h3>
                                <p class="text-3xl font-bold">{{ stats.institutes.total.toLocaleString() }}</p>
                                <p class="text-xs" :class="getChangeColor(stats.institutes.change)">
                                    <span class="font-semibold"
                                        >{{ stats.institutes.change >= 0 ? '+' : ''
                                        }}{{ stats.institutes.change }}%</span
                                    >
                                    vs last month
                                </p>
                                <p class="text-xs text-gray-400 mt-1">{{ stats.institutes.premium }} Premium Users</p>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
                            <div class="rounded-full bg-green-100 p-3 mr-4 text-green-600">
                                <svg
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M20.4399 7.75H3.56006M14.5 11.5C12.4253 11.5 9.5 11.5 9.5 11.5M20.5 8.25164V18.375C20.5 19.5486 19.5486 20.5 18.375 20.5H5.625C4.4514 20.5 3.5 19.5486 3.5 18.375V8.25164C3.5 7.92175 3.57681 7.59638 3.72434 7.30132L5.1845 4.381C5.45447 3.84107 6.00632 3.5 6.60999 3.5H17.39C17.9937 3.5 18.5455 3.84107 18.8155 4.381L20.2757 7.30132C20.4232 7.59638 20.5 7.92175 20.5 8.25164Z"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-gray-500 text-sm font-semibold">Total Teachers</h3>
                                <p class="text-3xl font-bold">{{ stats.teachers.total.toLocaleString() }}</p>
                                <p class="text-xs" :class="getChangeColor(stats.teachers.change)">
                                    <span class="font-semibold"
                                        >{{ stats.teachers.change >= 0 ? '+' : '' }}{{ stats.teachers.change }}%</span
                                    >
                                    vs last month
                                </p>
                                <p class="text-xs text-gray-400 mt-1">{{ stats.teachers.premium }} Premium Users</p>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
                            <div class="rounded-full bg-blue-100 p-3 mr-4 text-blue-600">
                                <svg
                                    width="24"
                                    height="24"
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
                            </div>
                            <div>
                                <h3 class="text-gray-500 text-sm font-semibold">Total Students</h3>
                                <p class="text-3xl font-bold">{{ stats.students.total.toLocaleString() }}</p>
                                <p class="text-xs" :class="getChangeColor(stats.students.change)">
                                    <span class="font-semibold"
                                        >{{ stats.students.change >= 0 ? '+' : '' }}{{ stats.students.change }}%</span
                                    >
                                    vs last month
                                </p>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
                            <div class="rounded-full bg-yellow-100 p-3 mr-4 text-yellow-600">
                                <svg
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M11.5202 6.2399V9.1199C11.5202 9.6501 11.95 10.0799 12.4802 10.0799H15.3602M17.2802 2.3999H10.56C9.49958 2.3999 8.63997 3.25951 8.63996 4.31989V6.2399M17.2802 2.3999H17.8157C18.0861 2.3999 18.3434 2.51377 18.5297 2.70973C18.8096 3.0042 19.2529 3.45762 19.6802 3.8399C20.015 4.13952 20.5411 4.67722 20.8562 5.00401C21.0265 5.18073 21.1202 5.4167 21.1202 5.66217L21.1202 6.2399M17.2802 2.3999V5.2799C17.2802 5.8101 17.71 6.2399 18.2402 6.2399H21.1202M21.1202 6.2399L21.1199 15.8399C21.1199 16.9003 20.2603 17.7599 19.1999 17.7599H15.3599M13.9202 7.6799C13.4929 7.29762 13.0496 6.84421 12.7697 6.54973C12.5834 6.35377 12.3261 6.2399 12.0557 6.2399H4.79996C3.73958 6.2399 2.87996 7.09951 2.87996 8.15989L2.87988 19.6799C2.87988 20.7402 3.73949 21.5999 4.79988 21.5999L13.4399 21.5999C14.5003 21.5999 15.3599 20.7403 15.3599 19.6799L15.3602 9.50217C15.3602 9.2567 15.2665 9.02073 15.0962 8.84401C14.7811 8.51722 14.255 7.97952 13.9202 7.6799Z"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-gray-500 text-sm font-semibold">Total Ayhoots</h3>
                                <p class="text-3xl font-bold">{{ stats.ayhoots.total.toLocaleString() }}</p>
                                <p class="text-xs" :class="getChangeColor(stats.ayhoots.change)">
                                    <span class="font-semibold"
                                        >{{ stats.ayhoots.change >= 0 ? '+' : '' }}{{ stats.ayhoots.change }}%</span
                                    >
                                    vs last month
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-3">
                            <h3 class="text-lg font-semibold text-gray-800 mb-1">User Signups</h3>
                            <div class="flex flex-wrap gap-2 justify-between items-center mb-4">
                                <div class="flex gap-1 bg-gray-100 p-1 rounded-md">
                                    <button
                                        v-for="type in userTypes"
                                        :key="type.key"
                                        @click="activeUserType = type.key"
                                        class="text-sm px-3 py-1 rounded transition"
                                        :class="
                                            activeUserType === type.key
                                                ? 'bg-white shadow text-blue-600 font-semibold'
                                                : 'text-gray-500 hover:bg-gray-200'
                                        "
                                    >
                                        {{ type.name }}
                                    </button>
                                </div>
                                <div class="flex gap-1 bg-gray-100 p-1 rounded-md">
                                    <button
                                        v-for="range in timeRanges"
                                        :key="range"
                                        @click="activeTimeRange = range"
                                        class="text-sm px-3 py-1 rounded transition capitalize"
                                        :class="
                                            activeTimeRange === range
                                                ? 'bg-white shadow text-blue-600 font-semibold'
                                                : 'text-gray-500 hover:bg-gray-200'
                                        "
                                    >
                                        {{ range }}
                                    </button>
                                </div>
                            </div>
                            <div class="h-72">
                                <LineChart :data="filteredSignupData" :grid="true" />
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-2">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">User Distribution</h3>
                            <div class="h-80 flex items-center justify-center">
                                <PieChart :data="userDistributionData" />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                        <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-3">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Latest Plan Purchases</h3>
                            <div class="overflow-x-auto">
                                <DataTable
                                    :columns="purchaseColumns"
                                    :rows="paginatedPurchases"
                                    wrapper-class=""
                                    table-class="min-w-full"
                                    thead-class=""
                                    tbody-class=""
                                    row-class="border-b border-gray-100"
                                    th-base-class="py-2 text-xs font-semibold text-gray-400 uppercase"
                                    td-base-class="py-3 text-sm"
                                    empty-text="No purchases yet."
                                >
                                    <template #cell:user="{ row: purchase }">
                                        <div class="flex items-center">
                                            <img
                                                :src="purchase.avatar"
                                                class="h-8 w-8 rounded-full object-cover mr-3"
                                            />
                                            <div>
                                                <p class="font-semibold text-sm text-gray-800">{{ purchase.name }}</p>
                                                <p class="text-xs text-gray-500">{{ purchase.type }}</p>
                                            </div>
                                        </div>
                                    </template>
                                    <template #cell:plan="{ row: purchase }">
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-semibold"
                                            :class="getPlanBadgeClass(purchase.plan)"
                                            >{{ purchase.plan }}</span
                                        >
                                    </template>
                                </DataTable>
                            </div>
                            <div class="flex justify-between items-center mt-4 text-sm">
                                <button
                                    @click="prevPage"
                                    :disabled="currentPage === 1"
                                    class="px-3 py-1 border rounded-md disabled:opacity-50"
                                >
                                    Previous
                                </button>
                                <span>Page {{ currentPage }} of {{ totalPages }}</span>
                                <button
                                    @click="nextPage"
                                    :disabled="currentPage === totalPages"
                                    class="px-3 py-1 border rounded-md disabled:opacity-50"
                                >
                                    Next
                                </button>
                            </div>
                        </div>

                        <div class="lg:col-span-2 flex flex-col space-y-6">
                            <div class="bg-white p-6 rounded-lg shadow-md">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Content Overview</h3>
                                <div class="space-y-4">
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <span class="text-sm font-semibold text-gray-700">Ayhoots</span>
                                            <span class="text-sm font-medium text-gray-500"
                                                >{{ contentOverview.ayhoots.toLocaleString() }} ({{
                                                    contentOverview.ayhoots_percent
                                                }}%)</span
                                            >
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div
                                                class="bg-blue-500 h-2.5 rounded-full"
                                                :style="{ width: contentOverview.ayhoots_percent + '%' }"
                                            ></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <span class="text-sm font-semibold text-gray-700">Courses</span>
                                            <span class="text-sm font-medium text-gray-500"
                                                >{{ contentOverview.courses.toLocaleString() }} ({{
                                                    contentOverview.courses_percent
                                                }}%)</span
                                            >
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div
                                                class="bg-purple-500 h-2.5 rounded-full"
                                                :style="{ width: contentOverview.courses_percent + '%' }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white p-6 rounded-lg shadow-md">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold text-gray-800">Assignments</h3>
                                    <div class="flex gap-1 bg-gray-100 p-1 rounded-md">
                                        <button
                                            v-for="range in timeRanges"
                                            :key="range"
                                            @click="activeAssignmentRange = range"
                                            class="text-xs px-2 py-0.5 rounded transition capitalize"
                                            :class="
                                                activeAssignmentRange === range
                                                    ? 'bg-white shadow text-blue-600 font-semibold'
                                                    : 'text-gray-500 hover:bg-gray-200'
                                            "
                                        >
                                            {{ range }}
                                        </button>
                                    </div>
                                </div>
                                <div class="h-40">
                                    <LineChart :data="filteredAssignmentData" :gradient="true" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { apiCall } from '@/services/apiService'
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import LineChart from '@/components/molecules/chart/LineChart.vue'
import PieChart from '@/components/molecules/chart/PieChart.vue'
import DataTable from '@/components/shared/DataTable.vue'

const purchaseColumns = [
    { key: 'user', label: 'User', align: 'left' },
    { key: 'plan', label: 'Plan', align: 'left' },
    { key: 'invoice.amount', label: 'Amount', align: 'left', tdClass: 'font-semibold text-gray-700' },
    { key: 'date', label: 'Date', align: 'left', tdClass: 'text-gray-500' },
]

const loading = ref(true)
const isSidebarOpen = ref(false)

// --- FIX: Initialize state with the correct structure ---
const stats = ref({
    institutes: { total: 0, change: 0, premium: 0 },
    teachers: { total: 0, change: 0, premium: 0 },
    students: { total: 0, change: 0 },
    ayhoots: { total: 0, change: 0 },
})
const signupData = ref({})
const userDistributionData = ref([])
const purchases = ref([])
const contentOverview = ref({
    ayhoots: 0,
    courses: 0,
    ayhoots_percent: 0,
    courses_percent: 0,
})
const assignmentData = ref({})

const activeUserType = ref('students')
const activeTimeRange = ref('monthly')
const userTypes = ref([
    { key: 'students', name: 'Students' },
    { key: 'teachers', name: 'Teachers' },
    { key: 'institutes', name: 'Institutes' },
])
const timeRanges = ref(['weekly', 'monthly', 'yearly'])
const currentPage = ref(1)
const itemsPerPage = ref(7)
const activeAssignmentRange = ref('monthly')

const filteredSignupData = computed(() => {
    if (!signupData.value[activeTimeRange.value]) return []
    return signupData.value[activeTimeRange.value][activeUserType.value] || []
})

const filteredAssignmentData = computed(() => {
    if (!assignmentData.value[activeAssignmentRange.value]) return []
    return assignmentData.value[activeAssignmentRange.value] || []
})

const totalPages = computed(() => {
    return Math.ceil(purchases.value.length / itemsPerPage.value)
})

const paginatedPurchases = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return purchases.value.slice(start, end)
})

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value
}
const getChangeColor = (change) => (change >= 0 ? 'text-green-500' : 'text-red-500')
const getPlanBadgeClass = (plan) => {
    if (!plan) return 'bg-gray-100 text-gray-800'
    const planName = plan.toLowerCase()
    if (planName.includes('golden')) return 'bg-yellow-100 text-yellow-800'
    if (planName.includes('silver')) return 'bg-gray-200 text-gray-800'
    if (planName.includes('bronze')) return 'bg-orange-100 text-orange-800'
    return 'bg-blue-100 text-blue-800'
}
const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++
}
const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--
}

const fetchDashboardData = async () => {
    loading.value = true
    try {
        const response = await apiCall('GET', '/api/admin/dashboard')
        stats.value = response.stats
        signupData.value = response.charts.signups
        userDistributionData.value = response.stats.distribution
        purchases.value = response.latest_purchases
        contentOverview.value = response.stats.content_overview
        assignmentData.value = response.charts.assignments
    } catch (error) {
        console.error('Failed to fetch dashboard data:', error)
    } finally {
        loading.value = false
    }
}

onMounted(fetchDashboardData)
</script>
