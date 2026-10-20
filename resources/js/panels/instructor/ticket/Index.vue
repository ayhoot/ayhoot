<template>
    <InstructorLayout :secondary="false">
                <div class="sm:flex justify-between items-center mb-6">
                    <div class="mb-4">
                        <h2 class="text-3xl font-bold mb-2 text-gray-900">{{ $t('ticket.title') }}</h2>
                        <p class="text-gray-700">
                            {{ $t('ticket.description') }}
                        </p>
                    </div>
                    <AyButton :to="`/${$role}/ticket/create`" type="dash-blue">
                        {{ $t('ticket.createTicket') }}
                    </AyButton>
                </div>

                <!-- Search and filter bar -->
                <div class="bg-white p-4 rounded-lg shadow-sm mb-6 flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <AyInput v-model="searchQuery" type="text" :placeholder="$t('ticket.search')">
                            <template #icon>
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </template>
                        </AyInput>
                    </div>
                    <AySelect v-model="statusFilter">
                        <option value="">{{ $t('ticket.status') }}</option>
                        <option value="open">{{ $t('ticket.open') }}</option>
                        <option value="answered">{{ $t('ticket.answered') }}</option>
                        <option value="closed">{{ $t('ticket.closed') }}</option>
                    </AySelect>
                </div>

                <!-- Loading state -->
                <div v-if="isLoading" class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>

                <!-- Empty state -->
                <div v-else-if="filteredTickets.length === 0"
                    class="bg-white rounded-lg shadow-sm p-12 flex flex-col items-center justify-center text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">
                        {{ $t('ticket.noTicketsFound') }}
                    </h3>
                    <p class="text-gray-500 mb-6">
                        {{
                            !searchQuery && !statusFilter
                                ? $t('ticket.createFirst')
                                : $t('ticket.tryChanging')
                        }}
                    </p>
                    <AyButton type="dash-blue" v-if="!searchQuery && !statusFilter" :to="`/${$role}/ticket/create`">
                        + {{ $t('ticket.createTicket') }}
                    </AyButton>
                    <AyButton v-else @click="clearFilters" type="light-gray">
                        {{ $t('ticket.clearFilters') }}
                    </AyButton>
                </div>

                <!-- Tickets list -->
                <DataTable v-else :columns="ticketColumns" :rows="filteredTickets">
                    <template #cell:id="{ row }">#{{ row.id }}</template>
                    <template #cell:status="{ row }">
                        <StatusBadge :status="row.status">{{ $t(row.status) }}</StatusBadge>
                    </template>
                    <template #cell:created="{ row }">
                        {{ formatDate(row.created_at) }}
                    </template>
                    <template #cell:actions="{ row }">
                        <ResponsiveButton type="light-blue" @click="viewTicket(row.id)"
                            class="p-1 px-2 text-xs">
                            {{ $t('ticket.view') }}
                        </ResponsiveButton>
                    </template>
                </DataTable>

                <!-- Pagination -->
                <div v-if="filteredTickets.length > 0"
                    class="flex items-center justify-between bg-white px-4 py-3 mt-4 rounded-lg shadow-sm">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            {{ $t('ticket.previous') }}
                        </button>
                        <button
                            class="ms-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            {{ $t('ticket.next') }}
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium">1</span> to
                                <span class="font-medium">{{ filteredTickets.length }}</span>
                                of
                                <span class="font-medium">{{ filteredTickets.length }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <AyButton type="light-gray" @click="fetchData">
                                {{ $t('ticket.refresh') }}
                            </AyButton>
                        </div>
                    </div>
                </div>
    </InstructorLayout>
</template>

<script>
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import { apiCall } from "@/services/apiService"
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DataTable from '@/components/shared/DataTable.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'

export default {
    name: 'TeacherTicket',
    components: {
        InstructorLayout,
        AyButton,
        ResponsiveButton,
        DataTable,
        StatusBadge,
        AySelect,
        AyInput,
    },
    data() {
        return {
            tickets: [],
            isLoading: false,
            searchQuery: '',
            statusFilter: '',
        }
    },

    computed: {
        ticketColumns() {
            return [
                { key: 'id', label: this.$t('ticket.id'), tdClass: 'font-medium text-gray-900' },
                { key: 'title', label: this.$t('ticket.title'), tdClass: 'text-gray-700 max-w-sm truncate' },
                { key: 'status', label: this.$t('ticket.status') },
                { key: 'created', label: this.$t('ticket.created'), tdClass: 'text-gray-500' },
                { key: 'actions', label: this.$t('ticket.actions'), tdClass: 'font-medium' },
            ]
        },
        filteredTickets() {
            return this.tickets.filter((ticket) => {
                const matchesSearch =
                    !this.searchQuery ||
                    ticket.title
                        .toLowerCase()
                        .includes(this.searchQuery.toLowerCase())
                const matchesStatus =
                    !this.statusFilter || ticket.status === this.statusFilter
                return matchesSearch && matchesStatus
            })
        },
    },

    async mounted() {
        await this.fetchData()
    },

    methods: {
        async fetchData() {
            this.isLoading = true
            try {
                const response = await apiCall('GET', `/api/instructor/ticket`)
                this.tickets = response.tickets.data
            } catch (error) {
                console.error('Error fetching tickets:', error)
            } finally {
                this.isLoading = false
            }
        },

        viewTicket(ticketId) {
            this.$router.push(`/${this.$role}/ticket/${ticketId}`)
        },

        clearFilters() {
            this.searchQuery = ''
            this.statusFilter = ''
        },

        formatDate(dateString) {
            if (!dateString) return '-'
            const date = new Date(dateString)
            return date.toLocaleDateString()
        },
    },
}
</script>
