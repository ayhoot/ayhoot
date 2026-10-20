<template>
    <PageWrapper>
        <ExamForm :exam="exam" :isEdit="true" />
    </PageWrapper>
</template>

<script>
import PageWrapper from '@/components/shared/PageWrapper.vue'
import ExamForm from './ExamForm.vue'
import { apiCall } from '@/services/apiService'

export default {
    components: {
        PageWrapper,
        ExamForm,
    },
    data() {
        return { exam: {} }
    },
    async mounted() {
        const examId = this.$route.params.id
        const response = await apiCall('GET', `/api/teacher/exam/${examId}`)
        this.exam = response.exam
    },
}
</script>
