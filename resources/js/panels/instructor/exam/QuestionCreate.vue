<template>
    <PageWrapper>
      <h2 class="text-2xl font-semibold mb-6">Create Question</h2>

      <form @submit.prevent="submitQuestion" class="bg-white p-6 shadow-md rounded-lg">
        <!-- Select Question Type -->
        <div class="mb-4">
          <label class="block text-gray-700">Question Type</label>
          <select v-model="question.type" @change="updateComponent" class="w-full border p-2 rounded">
            <option value="mcq">Multiple Choice</option>
            <option value="true_false">True/False</option>
            <option value="short_answer">Short Answer</option>
            <option value="long_answer">Long Answer</option>
            <option value="file_upload">File Upload</option>
          </select>
        </div>

        <!-- Dynamic Question Form -->
        <component :is="currentComponent" v-model="question" />

        <button type="submit"
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">
          Save Question
        </button>
      </form>
      <Footer />
    </PageWrapper>
  </template>

  <script>
  import PageWrapper from '@/components/shared/PageWrapper.vue';
import { getRole } from '@/utils/role'
  import Footer from '@/components/layout/Footer.vue';
  import { apiCall } from "@/services/apiService";

  // Import Question Type Components
  import MultipleChoice from '@/components/exam/question/MultipleChoice.vue';
  import TrueFalse from '@/components/exam/question/TrueFalse.vue';
  import ShortAnswer from '@/components/exam/question/ShortAnswer.vue';
  import LongAnswer from './LongAnswer.vue';
  import FileUpload from '@/components/exam/question/FileUpload.vue';

  export default {
    name: 'CreateQuestion',
    components: {
      PageWrapper,
      Footer,
      MultipleChoice,
      TrueFalse,
      ShortAnswer,
      LongAnswer,
      FileUpload
    },
    data() {
      return {
        question: {
          type: 'mcq',
          exam_id: this.$route.params.id,
          question_text: '',
          options: [],
          correct_answer: '',
          file: null
        },
        currentComponent: 'MultipleChoice'
      };
    },
    methods: {
      updateComponent() {
        const components = {
          mcq: 'MultipleChoice',
          true_false: 'TrueFalse',
          short_answer: 'ShortAnswer',
          long_answer: 'LongAnswer',
          file_upload: 'FileUpload'
        };
        this.currentComponent = components[this.question.type] || 'div';
      },
      async submitQuestion() {
        try {
          await apiCall('POST', `/api/instructor/exam/${this.question.exam_id}/question`, this.question);
          this.$router.push(`/${getRole()}/exam/${this.question.exam_id}/questions`);
        } catch (error) {
          console.error('Error saving question:', error);
        }
      }
    }
  };
  </script>
