<template>
    <InstructorLayout :secondary="false">
                <!-- Back button and title -->
                <div class="flex flex-col justify-center mb-6 gap-4">
                    <div class="flex items-center justify-start">
                        <ResponsiveButton type="light-gray" @click="$router.back()" class="p-2">
                            {{ $t('ticket.details.backToTickets') }}
                        </ResponsiveButton>
                    </div>
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-semibold text-gray-800">
                            {{ $t('ticket.details.title') }}
                        </h2>
                        <div class="flex items-center gap-2 relative">
                            <AyButton type="light-gray" @click="fetchData" class="gap-2">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                    />
                                </svg>
                                {{ $t('ticket.refresh') }}
                            </AyButton>
                        </div>
                    </div>
                </div>

                <!-- Loading state -->
                <div v-if="isLoading" class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="border-b border-gray-200 p-6">
                                <div class="flex justify-between items-start">
                                    <h3 class="text-xl font-semibold text-gray-800">
                                        {{ ticket.title }}
                                    </h3>
                                    <StatusBadge
                                        :status="ticket.status"
                                        :base-class="'px-2 py-1 text-xs font-medium rounded-full capitalize'"
                                    >
                                        {{ $t('ticket.' + ticket.status.toLowerCase()) }}
                                    </StatusBadge>
                                </div>
                                <div class="mt-4 text-sm text-gray-500">
                                    <span
                                        >{{ $t('ticket.details.createdOn') }} {{ formatDate(ticket.created_at) }}</span
                                    >
                                    <span v-if="ticket.updated_at && ticket.updated_at !== ticket.created_at">
                                        • {{ $t('ticket.details.updatedOn') }} {{ formatDate(ticket.updated_at) }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="prose max-w-none">
                                    <p class="text-gray-700 whitespace-pre-line">
                                        {{ ticket.message }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Reply section -->
                        <div class="mt-6 bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="border-b border-gray-200 p-4 flex justify-between items-center">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    {{ $t('ticket.details.conversation') }}
                                </h3>
                                <span class="text-sm text-gray-400 font-bold">
                                    {{ $t('ticket.details.repliesCount', ticket.replies ? ticket.replies.length : 0) }}
                                </span>
                            </div>

                            <!-- No replies state -->
                            <div
                                v-if="!ticket.replies || ticket.replies.length === 0"
                                class="flex items-center font-bold justify-center gap-2 p-10 text-gray-400 text-center"
                            >
                                <svg
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M19 10.5V6C19 4.89543 18.1046 4 17 4H5C3.89543 4 3 4.89543 3 6V13.8261C3 14.9307 3.89543 15.8261 5 15.8261H6.56522V20L10.7391 15.8261H11M16.163 18.3913L18.7717 21V18.3913H19C20.1046 18.3913 21 17.4959 21 16.3913V13C21 11.8954 20.1046 11 19 11H13C11.8954 11 11 11.8954 11 13V16.3913C11 17.4959 11.8954 18.3913 13 18.3913H16.163Z"
                                        class="stroke-current"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <p>{{ $t('ticket.details.noReplies') }}</p>
                            </div>

                            <!-- Replies list -->
                            <div v-else class="divide-y divide-gray-200">
                                <div v-for="(reply, index) in ticket.replies" :key="index" class="p-6">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mr-4">
                                            <div
                                                class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center"
                                            >
                                                <span class="text-gray-600 font-medium">{{
                                                    getInitials(reply.sender.name)
                                                }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-medium text-gray-900">
                                                {{ reply.sender.name }}
                                            </h4>
                                            <p class="text-sm text-gray-500">
                                                {{ formatDate(reply.created_at) }}
                                            </p>
                                            <div class="mt-2 text-gray-700 whitespace-pre-line">
                                                {{ reply.message }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reply form -->
                            <div class="p-4 bg-gray-50" v-if="ticket.status !== 'closed'">
                                <textarea
                                    v-model="replyMessage"
                                    rows="4"
                                    class="w-full inline-flex p-2 text-sm font-bold text-gray-900 bg-gray-50 rounded-sm border-2 border-gray-300 focus:outline-none"
                                    :placeholder="$t('ticket.details.replyPlaceholder')"
                                ></textarea>
                                <div class="mt-2 flex justify-end">
                                    <button
                                        @click="sendReply"
                                        :disabled="!replyMessage.trim() || replySending"
                                        class="text-white text-sm bg-[#3450AE] hover:bg-[#152B65] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px] flex items-center justify-center rounded-md px-4 py-2 cursor-pointer font-bold gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <span v-if="replySending">{{ $t('ticket.details.sending') }}</span>
                                        <span v-else>{{ $t('ticket.details.sendReply') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar/metadata -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    {{ $t('ticket.details.infoTitle') }}
                                </h3>
                            </div>
                            <div class="p-4 space-y-4">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-500">
                                        {{ $t('ticket.id') }}
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-900">#{{ ticket.id }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-500">
                                        {{ $t('ticket.status') }}
                                    </h4>
                                    <p class="mt-1">
                                        <StatusBadge
                                            :status="ticket.status"
                                            :base-class="'px-2 py-1 text-xs font-medium rounded-full capitalize'"
                                        >
                                            {{ $t('ticket.' + ticket.status.toLowerCase()) }}
                                        </StatusBadge>
                                    </p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-500">
                                        {{ $t('ticket.created') }}
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ formatDate(ticket.created_at, true) }}
                                    </p>
                                </div>
                                <div v-if="ticket.updated_at && ticket.updated_at !== ticket.created_at">
                                    <h4 class="text-sm font-medium text-gray-500">
                                        {{ $t('ticket.updated') }}
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ formatDate(ticket.updated_at, true) }}
                                    </p>
                                </div>
                                <div v-if="ticket.submitter">
                                    <h4 class="text-sm font-medium text-gray-500">
                                        {{ $t('ticket.by') }}
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ ticket.submitter }}
                                    </p>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 border-t border-gray-200">
                                <button
                                    v-if="ticket.status !== 'closed'"
                                    @click="closeTicket()"
                                    class="w-full flex items-center justify-center rounded-md px-4 py-2 cursor-pointer font-bold text-sm shadow-y-sm text-[#810a0a] bg-[#ff9393] hover:bg-[#bf5959] shadow-[0_2px_0_rgba(175,7,7,1)] hover:shadow-none hover:translate-y-[2px]"
                                >
                                    {{ $t('ticket.details.closeTicket') }}
                                </button>
                                <div
                                    v-else
                                    class="w-full text-center bg-gray-200 text-gray-600 px-4 py-2 rounded-md transition font-bold text-sm cursor-default"
                                >
                                    {{ $t('ticket.details.closedTicket') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    </InstructorLayout>
</template>

<script>
import { apiCall } from "@/services/apiService"
import { realtime } from '@/services/realtime'
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'TeacherTicketShow',
    components: {
        InstructorLayout,
        ResponsiveButton,
        AyButton,
        StatusBadge,
    },
    data() {
        return {
            ticket: {
                id: '',
                title: '',
                message: '',
                status: '',
                created_at: null,
                updated_at: null,
                replies: [],
                submitter: null,
            },
            replyMessage: '',
            isLoading: false,
            replySending: false,
        }
    },
    async mounted() {
        await this.fetchData()
        // Live updates: refetch when a reply/status change is broadcast for this ticket.
        this._ticketChannel = `ticket.${this.$route.params.id}`
        realtime.privateChannel(this._ticketChannel).listen('.ticket.updated', () => this.fetchData())
    },
    beforeUnmount() {
        if (this._ticketChannel) realtime.leave(this._ticketChannel)
    },
    methods: {
        async fetchData() {
            this.isLoading = true
            try {
                const ticketId = this.$route.params.id
                const response = await apiCall('GET', `/api/instructor/ticket/${ticketId}`)
                this.ticket = response.ticket
                if (!this.ticket.replies) {
                    this.ticket.replies = []
                }
            } catch (error) {
                console.error('Error fetching ticket:', error)
            } finally {
                this.isLoading = false
            }
        },
        async sendReply() {
            if (!this.replyMessage.trim()) return
            this.replySending = true
            try {
                const ticketId = this.$route.params.id
                await apiCall('POST', `/api/instructor/ticket/${ticketId}/reply`, {
                    message: this.replyMessage,
                })
                this.fetchData() // Refresh data to show the new reply
                this.replyMessage = ''
            } catch (error) {
                console.error('Error sending reply:', error)
            } finally {
                this.replySending = false
            }
        },
        async closeTicket() {
            // Add a confirmation dialog for better UX
            if (await confirmDialog({ message: this.$t('ticket.details.confirmClose', 'Are you sure you want to close this ticket?') })) {
                try {
                    await apiCall('GET', `/api/instructor/ticket/${this.ticket.id}/close`)
                    this.fetchData()
                } catch (error) {
                    console.error('Error closing ticket:', error)
                }
            }
        },
        formatDate(dateString, includeTime = false) {
            if (!dateString) return ''
            const date = new Date(dateString)
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            }
            if (includeTime) {
                options.hour = '2-digit'
                options.minute = '2-digit'
            }
            // Use the i18n locale for dynamic date formatting
            return date.toLocaleDateString(this.$i18n.locale, options)
        },
        getInitials(name) {
            if (!name) return '?'
            return name
                .split(' ')
                .map((word) => word[0])
                .join('')
                .toUpperCase()
                .substring(0, 2)
        },
    },
}
</script>
