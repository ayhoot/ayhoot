<template>
    <PageWrapper>
      <h2 class="text-2xl font-semibold mb-6">{{ exam.title }} - Questions</h2>

      <!-- Add Question Button -->
      <router-link :to="`/${$role}/exam/${exam.id}/question/create`"
        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center my-9">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        <span class="ml-2">Add Question</span>
      </router-link>

      <!-- Display Questions -->
      <div v-if="questions.length">
        <div v-for="question in questions" :key="question.id" class="bg-white p-4 shadow-md rounded-lg mb-4">
          <h3 class="text-lg font-semibold">{{ question.question_text }}</h3>

          <component :is="getComponent(question.type)" :question="question" />

          <!-- Edit and Delete Buttons -->
          <div class="mt-3">
            <router-link :to="`/${$role}/exam/${exam.id}/question/${question.id}/edit`"
              class="text-blue-600 hover:underline">Edit</router-link>
            <span class="mx-2">|</span>
            <button @click="deleteQuestion(question.id)" class="text-red-600 hover:underline">Delete</button>
          </div>
        </div>
      </div>
      <p v-else class="text-center text-gray-500">No questions found.</p>

      <!-- Pagination -->
      <Pagination v-if="pagination.last_page > 1" :pagination="pagination" @page-changed="fetchData" />
      <Footer />
    </PageWrapper>
  </template>

  <script>
  import PageWrapper from '@/components/shared/PageWrapper.vue';
  import Footer from '@/components/layout/Footer.vue';
  import Pagination from '@/components/Pagination.vue';

  import MultipleChoice from '@/components/exam/question/MultipleChoice.vue';
  import TrueFalse from '@/components/exam/question/TrueFalse.vue';
  import ShortAnswer from '@/components/exam/question/ShortAnswer.vue';
  import LongAnswer from './LongAnswer.vue';
  import FileUpload from '@/components/exam/question/FileUpload.vue';

  import { apiCall } from "@/services/apiService";

  export default {
    name: 'TeacherExamQuestion',
    components: {
      PageWrapper,
      Footer,
      Pagination,
      MultipleChoice,
      TrueFalse,
      ShortAnswer,
      LongAnswer,
      FileUpload
    },
    data() {
      return {
        exam: {},
        questions: [],
        pagination: {}
      };
    },
    async mounted() {
      await this.fetchData(1);
    },
    methods: {
      async fetchData(page = 1) {
        try {
          const examId = this.$route.params.id;
          const response = await apiCall('GET', `/api/instructor/exam/${examId}/questions?page=${page}`);
          this.exam = response.exam;
          this.questions = response.questions.data;
          this.pagination = response.questions;
        } catch (error) {
          console.error('Error fetching questions:', error);
        }
      },
      async deleteQuestion(questionId) {
        await apiCall('GET', `/api/instructor/exam/question/delete/${questionId}`);
        this.fetchData();
      },
      getComponent(type) {
        const components = {
          mcq: 'MultipleChoice',
          true_false: 'TrueFalse',
          short_answer: 'ShortAnswer',
          long_answer: 'LongAnswer',
          file_upload: 'FileUpload'
        };
        return components[type] || 'div';
      }
    }
  };
  </script>
