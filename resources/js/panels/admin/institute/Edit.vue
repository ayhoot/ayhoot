<template>
    <div class="bg-gray-50">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" />
        </div>
        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />
            <div class="flex-1 sm:ml-64 p-6 overflow-y-auto mt-16">
                <InstituteForm :institute="institute" :isEdit="true" />
            </div>
        </div>
    </div>
</template>

<script>
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import InstituteForm from './InstituteForm.vue'
import { apiCall } from '@/services/apiService'

export default {
    components: {
        Sidebar,
        Header,
        InstituteForm,
    },
    data() {
        return { institute: {} }
    },
    async mounted() {
        const instituteId = this.$route.params.id
        const response = await apiCall(
            'GET',
            `/api/admin/institute/${instituteId}`
        )
        this.institute = response.institute
    },
}
</script>
