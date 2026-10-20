<template>
    <div class="bg-gray-50">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" />
        </div>
        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />
            <div class="flex-1 sm:ml-64 p-6 overflow-y-auto mt-16">
                <TeacherForm :teacher="teacher" :isEdit="true" />
            </div>
        </div>
    </div>
</template>

<script>
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import TeacherForm from './TeacherForm.vue'
import { apiCall } from '@/services/apiService'

export default {
    components: {
        Sidebar,
        Header,
        TeacherForm,
    },
    data() {
        return { teacher: {} }
    },
    async mounted() {
        const teacherId = this.$route.params.id
        const response = await apiCall('GET', `/api/admin/teacher/${teacherId}`)
        this.teacher = response.teacher
    },
}
</script>
