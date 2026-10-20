<template>
    <div class="bg-gray-50">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" />
        </div>
        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />
            <div class="flex-1 sm:ml-64 p-6 overflow-y-auto mt-16">
                <PlanForm :is-edit="true" :plan-id="$route.params.id" />
            </div>
        </div>
    </div>
</template>

<script>
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import PlanForm from './PlanForm.vue'
import { apiCall } from '@/services/apiService'

export default {
    components: {
        Sidebar,
        Header,
        PlanForm,
    },
    data() {
        return { plan: {} }
    },
    async mounted() {
        const planId = this.$route.params.id
        const response = await apiCall('GET', `/api/admin/plan/${planId}`)
        this.plan = response.plan
    },
}
</script>
