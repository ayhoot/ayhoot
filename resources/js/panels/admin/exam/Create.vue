<template>
    <div class="bg-gray-50">
        <div class="flex h-screen items-center justify-between">
            <div class="flex-1">
                <ExamForm :exam="exam" :isEdit="false" />
            </div>
        </div>
    </div>
</template>

<script>
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import ExamForm from './ExamForm.vue'

export default {
    components: {
        Sidebar,
        Header,
        ExamForm,
    },
    data() {
        return {
            exam: {
                title: '',
                description: '',
                type: 'multiple',
                institute_id: '',
                teacher_id: '',
                status: 'published',
            },
        }
    },
}
</script>
