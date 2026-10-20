<template>
    <div class="bg-gray-50 min-h-screen">
      <div class="fixed top-0 inset-x-0 z-50">
        <Header @toggle-sidebar="toggleSidebar" />
      </div>
      <div class="flex">
        <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />
        <div class="flex-1 sm:ml-64 p-6 overflow-y-auto mt-16">
          <!-- Back button and title -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
            <div>
              <button @click="$router.back()"
                class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to tickets
              </button>
              <h2 class="text-2xl font-semibold text-gray-800">Ticket Details</h2>
            </div>
            <div class="flex gap-2">
              <button @click="fetchData"
                class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50 transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
              </button>
              <button @click="toggleStatusDropdown"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center gap-2 relative">
                Update Status
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <!-- Status dropdown -->
                <div v-if="showStatusDropdown"
                  class="absolute right-0 top-full mt-1 w-48 bg-white rounded-md shadow-lg z-10">
                  <div class="py-1">
                    <button @click="updateStatus('open')"
                      class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Open</button>
                    <button @click="updateStatus('answered')"
                      class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Answered</button>
                    <button @click="updateStatus('closed')"
                      class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Closed</button>
                  </div>
                </div>
              </button>
            </div>
          </div>
  
          <!-- Loading state -->
          <div v-if="isLoading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
          </div>
  
          <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Ticket information panel -->
            <div class="lg:col-span-2">
              <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 p-6">
                  <div class="flex justify-between items-start">
                    <h3 class="text-xl font-semibold text-gray-800">{{ ticket.title }}</h3>
                    <StatusBadge :status="ticket.status">
                      {{ ticket.status }}
                    </StatusBadge>
                  </div>
                  <div class="mt-4 text-sm text-gray-500">
                    <span>Created on {{ formatDate(ticket.created_at) }}</span>
                    <span v-if="ticket.updated_at && ticket.updated_at !== ticket.created_at"> • Updated on {{
                      formatDate(ticket.updated_at) }}</span>
                  </div>
                </div>
                <div class="p-6">
                  <div class="prose max-w-none">
                    <p class="text-gray-700 whitespace-pre-line">{{ ticket.message }}</p>
                  </div>
                </div>
              </div>
  
              <!-- Reply section -->
              <div class="mt-6 bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 p-4 flex justify-between items-center">
                  <h3 class="text-lg font-semibold text-gray-800">Conversation</h3>
                  <span class="text-sm text-gray-500">{{ ticket.replies ? ticket.replies.length : 0 }} replies</span>
                </div>
  
                <!-- No replies state -->
                <div v-if="!ticket.replies || ticket.replies.length === 0" class="p-10 text-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-4" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                  <p class="text-gray-500">No replies yet</p>
                </div>
  
                <!-- Replies list -->
                <div v-else class="divide-y divide-gray-200">
                  <div v-for="(reply, index) in ticket.replies" :key="index" class="p-6">
                    <div class="flex items-start">
                      <div class="flex-shrink-0 mr-4">
                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                          <span class="text-gray-600 font-medium">{{ getInitials(reply.sender_type) }}</span>
                        </div>
                      </div>
                      <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-gray-900">{{ reply.sender.name }}</h4>
                        <p class="text-sm text-gray-500">{{ formatDate(reply.created_at) }}</p>
                        <div class="mt-2 text-gray-700 whitespace-pre-line">{{ reply.message }}</div>
                      </div>
                    </div>
                  </div>
                </div>
  
                <!-- Reply form -->
                <div class="p-4 bg-gray-50">
                  <textarea v-model="replyMessage" rows="4"
                    class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Write your reply..."></textarea>
                  <div class="mt-2 flex justify-end">
                    <button @click="sendReply" :disabled="!replyMessage.trim() || replySending"
                      class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                      <span v-if="replySending">Sending...</span>
                      <span v-else>Send Reply</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
  
            <!-- Sidebar/metadata -->
            <div class="lg:col-span-1">
              <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                  <h3 class="text-lg font-semibold text-gray-800">Ticket Information</h3>
                </div>
                <div class="p-4 space-y-4">
                  <div>
                    <h4 class="text-sm font-medium text-gray-500">Ticket ID</h4>
                    <p class="mt-1 text-sm text-gray-900">#{{ ticket.id }}</p>
                  </div>
                  <div>
                    <h4 class="text-sm font-medium text-gray-500">Status</h4>
                    <p class="mt-1">
                      <StatusBadge :status="ticket.status">
                        {{ ticket.status }}
                      </StatusBadge>
                    </p>
                  </div>
                  <div>
                    <h4 class="text-sm font-medium text-gray-500">Created</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(ticket.created_at, true) }}</p>
                  </div>
                  <div v-if="ticket.updated_at && ticket.updated_at !== ticket.created_at">
                    <h4 class="text-sm font-medium text-gray-500">Last Updated</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(ticket.updated_at, true) }}</p>
                  </div>
                  <div v-if="ticket.submitter">
                    <h4 class="text-sm font-medium text-gray-500">Submitted By</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ ticket.submitter }}</p>
                  </div>
                  <div v-if="ticket.assigned_to">
                    <h4 class="text-sm font-medium text-gray-500">Assigned To</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ ticket.assigned_to }}</p>
                  </div>
                </div>
                <div class="p-4 bg-gray-50 border-t border-gray-200">
                  <button v-if="ticket.status !== 'closed'" @click="updateStatus('closed')"
                    class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition">
                    Close Ticket
                  </button>
                  <button v-else @click="updateStatus('open')"
                    class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">
                    Reopen Ticket
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { apiCall } from '@/services/apiService'
  import { realtime } from '@/services/realtime'
  import Sidebar from '@/components/layout/AdminSidebar.vue'
  import Header from '@/components/layout/AdminHeader.vue'
  import StatusBadge from '@/components/shared/StatusBadge.vue'

  export default {
    name: 'AdminTicketShow',
    components: {
      Sidebar,
      Header,
      StatusBadge
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
          replies: []
        },
        replyMessage: '',
        isSidebarOpen: false,
        isLoading: false,
        replySending: false,
        showStatusDropdown: false
      }
    },
  
    async mounted() {
      await this.fetchData()
      // Live updates: refetch when a reply/status change is broadcast for this ticket.
      this._ticketChannel = `ticket.${this.$route.params.id}`
      realtime.privateChannel(this._ticketChannel).listen('.ticket.updated', () => this.fetchData())
      // Close dropdown when clicking outside
      document.addEventListener('click', this.closeDropdownOnClickOutside)
    },

    beforeUnmount() {
      if (this._ticketChannel) realtime.leave(this._ticketChannel)
      document.removeEventListener('click', this.closeDropdownOnClickOutside)
    },
  
    methods: {
      async fetchData() {
        this.isLoading = true
        try {
          const ticketId = this.$route.params.id
          const response = await apiCall('GET', `/api/admin/ticket/${ticketId}`)
          this.ticket = response.ticket
          // Ensure replies is an array
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
          const response = await apiCall('POST', `/api/admin/ticket/${ticketId}/reply`, {
            message: this.replyMessage
          })
          this.fetchData()
          this.replyMessage = ''
        } catch (error) {
          console.error('Error sending reply:', error)
        } finally {
          this.replySending = false
        }
      },
  
      toggleSidebar() {
        this.isSidebarOpen = !this.isSidebarOpen
      },
  
      toggleStatusDropdown(event) {
        event.stopPropagation() // Prevent the document click handler from firing
        this.showStatusDropdown = !this.showStatusDropdown
      },
  
      closeDropdownOnClickOutside(event) {
        // If the click is outside the dropdown toggle button, close the dropdown
        if (!event.target.closest('button[data-dropdown-toggle]')) {
          this.showStatusDropdown = false
        }
      },
  
      async updateStatus(status, showRefresh = true) {
        this.showStatusDropdown = false
        const ticketId = this.$route.params.id
        try {
          const response = await apiCall('POST', `/api/admin/ticket/${ticketId}/status`, {
            status: status
          })
          this.ticket.status = response.ticket.status

          if (showRefresh) {
            this.$toast.success(`Ticket status updated to ${status}`)
            // fetch updated ticket data
            this.fetchData()
          }
        } catch (error) {
          console.error('Error updating ticket status:', error)
        }
      },
  
      formatDate(dateString, includeTime = false) {
        if (!dateString) return ''
  
        const date = new Date(dateString)
        const options = {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        }
  
        if (includeTime) {
          options.hour = '2-digit'
          options.minute = '2-digit'
        }
  
        return date.toLocaleDateString('en-US', options)
      },
  
      getInitials(name) {
        if (!name) return '?'
        return name.split(' ')
          .map(word => word[0])
          .join('')
          .toUpperCase()
          .substring(0, 2)
      }
    }
  }
  </script>