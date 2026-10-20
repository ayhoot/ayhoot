<template>
    <div class="bg-gray-50">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" />
        </div>
        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />
            <div class="flex-1 sm:ml-64 p-6 overflow-y-auto mt-16">
                <router-link :to="`/admin/exam/${selectedExam.id}/questions/create`"
                    class="text-blue-600 hover:text-blue-900 flex items-center mb-7">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Create new question
                </router-link>

                <!-- Questions Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="text-lg font-semibold">Questions for Exam: {{ selectedExam.title }}</h3>
                    </div>



                    <div class="overflow-x-auto">
                        <DataTable
                            v-if="questions.length"
                            :columns="questionColumns"
                            :rows="questions"
                            :wrapper-class="''"
                            row-class=""
                        >
                            <template #cell:question="{ row: question }">
                                <div class="text-sm text-gray-900">{{ question.title }}</div>

                            </template>
                            <template #cell:actions="{ row: question }">
                                <router-link :to="`/admin/exam/${selectedExam.id}/question/${question.id}/edit`"
                                    class="text-indigo-600 hover:text-indigo-900">Edit</router-link>
                                <button @click="confirmDelete(question.id)"
                                    class="text-red-600 hover:text-red-900 ml-2">Delete</button>
                            </template>
                        </DataTable>

                        <div v-else class="text-center py-12">
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No questions found for this exam.</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import DataTable from '@/components/shared/DataTable.vue'
import { apiCall } from '@/services/apiService'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'AdminQuestions',
    components: {
        Sidebar,
        Header,
        AyButton,
        DataTable,
    },
    data() {
        return {
            questions: [],
            selectedExam: {}, // Object to hold the selected exam details
            isSidebarOpen: false,
        }
    },
    computed: {
        questionColumns() {
            return [
                { key: 'question', label: 'Question', align: 'left', thClass: 'font-medium tracking-wider' },
                { key: 'actions', label: 'Actions', align: 'right', thClass: 'font-medium tracking-wider', tdClass: 'font-medium' },
            ]
        },
    },
    async mounted() {
        await this.fetchExamDetails(); // Fetch exam details and questions on mount
    },
    methods: {
        async fetchExamDetails() {
            const examId = this.$route.params.id;
            try {
                const response = await apiCall('GET', `/api/admin/exam/${examId}/question`);
                this.selectedExam = response.exam; // Store exam details
                this.questions = response.questions; // Store questions for the exam
            } catch (error) {
                console.error('Error fetching exam details:', error);
            }
        },
        async confirmDelete(questionId) {
            if (await confirmDialog({ message: 'Are you sure you want to delete this question?', variant: 'danger' })) {
                try {
                    await apiCall('GET', `/api/admin/questions/${questionId}`);
                    this.fetchExamDetails(); // Refresh the questions list
                } catch (error) {
                    console.error('Error deleting question:', error);
                }
            }
        },
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen;
        },
    },
}
</script>

<style scoped>
/* Add any additional styles here */
</style>