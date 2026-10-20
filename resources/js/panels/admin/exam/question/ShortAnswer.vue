<template>
  <div class="w-full">
      <div class="bg-white rounded-md p-2 hover:bg-gray-50 shadow-[0_2px_0_rgba(136,136,136,1)]">
          <input type="text" :value="modelValue.shortAnswer"
                 @input="updateField('shortAnswer', $event.target.value)"
                 placeholder="Enter the correct answer"
                 class="flex-1 focus:outline-none rounded px-2 py-1 w-full" />
      </div>

      <div class="mt-3">
          <label v-if="modelValue.alternativeAnswers.length > 0" class="block text-sm font-medium text-gray-700 mb-2">
              Alternative Answers (optional)
          </label>
          <div v-for="(answer, index) in modelValue.alternativeAnswers" :key="index" class="flex mb-2 w-full">
              <div class="bg-white flex items-center justify-between w-full rounded-md p-2 hover:bg-gray-50 shadow-[0_2px_0_rgba(136,136,136,1)]">
                  <input type="text" :value="answer"
                         @input="updateAlternativeAnswer(index, $event.target.value)"
                         placeholder="Alternative answer"
                         class="flex-1 focus:outline-none rounded px-2 py-1 w-full" />
                  <button @click="removeAltAnswer(index)" class="ml-2 text-red-500 hover:text-red-700 p-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>
              </div>
          </div>
          <ResponsiveButton @click="addAltAnswer" class="w-full p-3" type="light-blue">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
              Add Alternative Answer
          </ResponsiveButton>
      </div>

      <div class="mt-3 flex items-center">
          <input type="checkbox" id="case-sensitive" :checked="modelValue.caseSensitive"
                 @change="updateField('caseSensitive', $event.target.checked)"
                 class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
          <label for="case-sensitive" class="ml-2 block text-sm text-gray-700">
              Case sensitive
          </label>
      </div>
  </div>
</template>

<script>
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'; // Adjust path

export default {
  name: 'ShortAnswer',
  components: { ResponsiveButton },
  props: {
      modelValue: { // { shortAnswer: '', alternativeAnswers: [], caseSensitive: false }
          type: Object,
          default: () => ({ shortAnswer: '', alternativeAnswers: [], caseSensitive: false })
      }
  },
  emits: ['update:modelValue'],
  methods: {
      updateField(field, value) {
          this.$emit('update:modelValue', { ...this.modelValue, [field]: value });
      },
      updateAlternativeAnswer(index, value) {
          const newAlternatives = [...this.modelValue.alternativeAnswers];
          newAlternatives[index] = value;
          this.updateField('alternativeAnswers', newAlternatives);
      },
      addAltAnswer() {
          const newAlternatives = [...this.modelValue.alternativeAnswers, ''];
          this.updateField('alternativeAnswers', newAlternatives);
      },
      removeAltAnswer(index) {
          const newAlternatives = this.modelValue.alternativeAnswers.filter((_, i) => i !== index);
          this.updateField('alternativeAnswers', newAlternatives);
      }
  }
};
</script>