<template>
    <InstructorLayout :secondary="false">
        <ExamForm :exam="exam" :isEdit="true" />
    </InstructorLayout>
</template>

<script>
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import ExamForm from './ExamForm.vue'
import { apiCall } from "@/services/apiService"

export default {
    components: {
        InstructorLayout,
        ExamForm,
    },
    data() {
        return { exam: {} }
    },
    async mounted() {
        const examId = this.$route.params.id
        const response = await apiCall('GET', `/api/instructor/exam/${examId}`)
        this.exam = response.exam
    },
}
</script>
