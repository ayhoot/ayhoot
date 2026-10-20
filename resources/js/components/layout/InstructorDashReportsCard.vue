<template>
    <div class="px-6 py-4 bg-white shadow-sm rounded-xl flex flex-col gap-y-2 hover:shadow-md text-start">
        <div class="w-full border-b-1 border-gray-300">
            <div class="w-max border-b-2 pb-2 border-gray-700 text-sm font-bold">{{ $t('dashboard.reports.latestReports') }}</div>
        </div>
        <div v-if="isLoading" class="py-2 flex flex-col gap-y-3">
            <div v-for="n in 3" :key="n" class="flex items-center p-1 gap-x-3 animate-pulse">
                <div class="w-5 h-5 rounded-full bg-gray-200"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-3 bg-gray-200 rounded w-4/5"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/5"></div>
                </div>
            </div>
        </div>
        <div v-else-if="reports.length" class="py-2 flex flex-col gap-y-2">
            <RouterLink v-for="report in reports" :key="report.id" :to="`/${$role}/report/assigned-ayhoots/${report.id}`" class="flex group bg-white items-center rounded-md w-full transition-transform transform border border-gray-100 hover:scale-101 hover:shadow-sm duration-100">
                <div class="px-2">
                    <svg width="18" height="18" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21.75 12C21.75 16.9706 17.7206 21 12.75 21C7.77944 21 3.75 16.9706 3.75 12C3.75 7.02944 7.77944 3 12.75 3C17.7206 3 21.75 7.02944 21.75 12Z" stroke="#FB9C04" stroke-width="2" stroke-linejoin="round"/><path d="M8.25 13.5L13.5 6.375V11.25H17.25L12 17.625V13.5H8.25Z" stroke="#FB9C04" stroke-width="2" stroke-linejoin="round"/></svg>
                </div>
                <div class="py-1 w-full flex flex-col justify-between">
                    <h4 class="font-semibold text-xs text-[#3450AE] truncate sm:max-w-[16ch] md:max-w-[20ch] lg:max-w-[24ch] overflow-hidden text-ellipsis">{{ getReportStatusText(report.end_at) }}</h4>
                    <h4 class="font-semibold text-sm text-gray-800 truncate sm:max-w-[16ch] md:max-w-[14ch] lg:max-w-[18ch] overflow-hidden text-ellipsis">{{ report.title }}</h4>
                </div>
            </RouterLink>
        </div>
        <div v-else class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $t('dashboard.reports.noReportsFound') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $t('dashboard.reports.noReportsSubtitle') }}</p>
            <div class="mt-6">
                <ResponsiveButton :to="`/${$role}/report`" type="dash-blue" class="inline-flex items-center px-2 py-1">{{ $t('dashboard.reports.exploreAyhoots') }}</ResponsiveButton>
            </div>
        </div>
        <div class="w-full flex items-center justify-center pt-2">
            <div v-if="isLoading" class="animate-pulse h-6 w-24 bg-gray-200 rounded-md"></div>
            <AyLinedButton v-else :to="`/${$role}/report`">{{ $t('dashboard.reports.seeAll', { count: totalReports }) }}</AyLinedButton>
        </div>
    </div>
</template>

<script>
import { apiCall } from "@/services/apiService"
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import AyLinedButton from '@/components/atoms/button/AyLinedButton.vue'

export default {
    name: 'DashReportsCard',
    components: {
        ResponsiveButton,
        AyLinedButton,
    },
    data() {
        return {
            isLoading: true,
            reports: [],
            totalReports: 0,
        }
    },
    methods: {
        async fetchReports() {
            try {
                this.isLoading = true
                const response = await apiCall('GET', `/api/instructor/game`)
                this.reports = response.data || []
                this.totalReports = response.total || 0
            } catch (error) {
                console.error('Error fetching reports:', error)
                this.reports = []
                this.totalReports = 0
            } finally {
                this.isLoading = false
            }
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A'
            return new Date(dateString).toLocaleDateString(this.$i18n.locale, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            })
        },
        getReportStatusText(endDateString) {
            if (!endDateString) {
                return this.$t('dashboard.reports.status.noEndDate')
            }
            const endDate = new Date(endDateString)
            const now = new Date()
            const formattedDate = this.formatDate(endDateString)

            if (endDate < now) {
                return this.$t('dashboard.reports.status.endedOn', { date: formattedDate })
            } else {
                return this.$t('dashboard.reports.status.willEndOn', { date: formattedDate })
            }
        },
    },
    async mounted() {
        await this.fetchReports()
    },
}
</script>