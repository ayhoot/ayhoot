<template>
  <div class="w-full">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-2 pb-4">
          <div v-for="(option, index) in localOptions" :key="option.fieldId || index"
              class="flex items-center bg-white hover:bg-gray-50 shadow-[0_2px_0_rgba(136,136,136,1)] p-2 rounded-md cursor-pointer relative w-full"
              :class="{ 'ring-2 ring-offset-1 ring-blue-500': localCorrectOption === index }">

              <input type="radio" :checked="localCorrectOption === index" @change="updateCorrectOption(index)"
                     :name="'mc_option_' + questionId" :value="index" class="form-radio h-5 w-5 text-indigo-600 mr-2 shrink-0" />

              <div class="flex items-center gap-2 w-full relative">
                  <div :class="['px-2 h-12 sm:h-16 flex items-center justify-center rounded-sm shrink-0', getShapeColor(option.shape)]">
                      <!-- Shape SVGs -->
                      <svg v-if="getShapeIcon(option.shape) === 'triangle'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3L19.7942 16.5H4.20577L12 3Z" fill="white" /></svg>
                      <svg v-else-if="getShapeIcon(option.shape) === 'diamond'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3L21 12L12 21L3 12L12 3Z" fill="white" /></svg>
                      <svg v-else-if="getShapeIcon(option.shape) === 'circle'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" fill="white" /></svg>
                      <svg v-else-if="getShapeIcon(option.shape) === 'rectangle'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="5" y="5" width="14" height="14" fill="white" /></svg>
                      <svg v-else-if="getShapeIcon(option.shape) === 'pentagon'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3L20.5595 9.21885L17.2901 19.2812H6.70993L3.44049 9.21885L12 3Z" fill="white" /></svg>
                      <svg v-else-if="getShapeIcon(option.shape) === 'hexagon'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3L19.7942 7.5V16.5L12 21L4.20577 16.5V7.5L12 3Z" fill="white" /></svg>
                  </div>

                  <div class="flex-1 flex items-center gap-2 relative">
                      <div v-if="option.imagePreview" class="relative w-16 h-16 sm:w-20 sm:h-20 shrink-0">
                          <img :src="option.imagePreview" class="object-cover w-full h-full rounded" />
                          <button type="button" @click="removeOptionImage(index)"
                              class="absolute cursor-pointer -top-1 -right-1 bg-white hover:bg-gray-100 rounded-full text-xs shadow text-red-500 hover:text-red-700 p-0.5">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                          </button>
                      </div>
                      <input v-model="option.text" @input="emitUpdate" class="flex-1 focus:outline-none rounded px-2 py-1 text-sm sm:text-base" placeholder="Add Answer" />
                      <label class="cursor-pointer bg-gray-100 rounded-md p-1.5 hover:bg-gray-200 shrink-0">
                          <input type="file" accept="image/*" @change="onOptionImageChange($event, index)" class="hidden" />
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" /></svg>
                      </label>
                      <button @click="removeOption(index)" class="ml-1 text-red-500 hover:text-red-700 shrink-0 p-1">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                      </button>
                  </div>
              </div>
          </div>
      </div>
      <ResponsiveButton @click="addOption" class="w-full p-3" type="light-blue">
          <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
          Add Option
      </ResponsiveButton>
  </div>
</template>

<script>
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'; // Adjust path
import { nextTick } from 'vue';

export default {
  name: 'MultipleChoice',
  components: { ResponsiveButton },
  props: {
      modelValue: { // { options: [], correctOption: null }
          type: Object,
          default: () => ({ options: [], correctOption: null })
      },
      shapeMap: { type: Array, required: true },
      getShapeColor: { type: Function, required: true },
      getShapeIcon: { type: Function, required: true },
      questionId: { type: [String, Number], default: 'q1'} // For unique radio button names
  },
  emits: ['update:modelValue'],
  data() {
      return {
          localOptions: [],
          localCorrectOption: null,
      };
  },
  watch: {
      modelValue: {
          handler(newValue) {
              this.localOptions = JSON.parse(JSON.stringify(newValue.options || [])).map(opt => ({
                  ...opt,
                  // fieldId is used to force re-render of specific option input if needed, useful for drag-n-drop later if IDs change
                  fieldId: opt.id || this.generateId(), 
                  imagePreview: opt.image // Assuming image is already a URL or base64
              }));
              this.localCorrectOption = newValue.correctOption;
              this.updateOptionCorrectness();
          },
          deep: true,
          immediate: true
      }
  },
  methods: {
      generateId() {
          return Math.random().toString(36).substr(2, 9);
      },
      addOption() {
          if (this.localOptions.length >= 6) {
              // this.$toast.error('Maximum 6 options allowed'); // Parent can handle toast
              return;
          }
          const shapeIndex = this.localOptions.length % this.shapeMap.length;
          const shape = this.shapeMap[shapeIndex];
          this.localOptions.push({
              text: '',
              id: String.fromCharCode(65 + this.localOptions.length), // Assign 'A', 'B', 'C'...
              shape: `${shape.color}${shape.name}`,
              image: null,
              imagePreview: null,
              file: null, // To store the File object
              correct: false,
              fieldId: this.generateId()
          });
          this.updateOptionIDsAndCorrectness();
          this.emitUpdate();
      },
      removeOption(index) {
          if (this.localOptions.length <= 2) {
              // this.$toast.error('Minimum 2 options required'); // Parent can handle toast
              return;
          }
          this.localOptions.splice(index, 1);
          if (this.localCorrectOption === index) {
              this.localCorrectOption = null; // Or set to 0 if you want first option selected
          } else if (this.localCorrectOption > index) {
              this.localCorrectOption--;
          }
          this.updateOptionIDsAndCorrectness();
          this.emitUpdate();
      },
      updateCorrectOption(index) {
          this.localCorrectOption = index;
          this.updateOptionCorrectness();
          this.emitUpdate();
      },
      updateOptionCorrectness() {
          this.localOptions.forEach((opt, idx) => {
              opt.correct = (idx === this.localCorrectOption);
          });
      },
      updateOptionIDsAndCorrectness() {
          this.localOptions.forEach((option, index) => {
              option.id = String.fromCharCode(65 + index);
              option.correct = (index === this.localCorrectOption);
          });
      },
      onOptionImageChange(event, index) {
          const file = event.target.files[0];
          if (file) {
              const reader = new FileReader();
              reader.onload = (e) => {
                  this.localOptions[index].imagePreview = e.target.result;
                  this.localOptions[index].image = e.target.result; // Or keep original if server path
                  this.localOptions[index].file = file; // Store the actual file object
                  this.emitUpdate();
              };
              reader.readAsDataURL(file);
          }
      },
      removeOptionImage(index) {
          this.localOptions[index].image = null;
          this.localOptions[index].imagePreview = null;
          this.localOptions[index].file = null;
          this.emitUpdate();
      },
      emitUpdate() {
           // Ensure correct flags are set before emitting
          this.updateOptionCorrectness();
          // Use nextTick to ensure DOM is updated if needed before parent processes, though usually not necessary for data emits
          nextTick(() => {
              this.$emit('update:modelValue', {
                  options: JSON.parse(JSON.stringify(this.localOptions)), // Deep clone
                  correctOption: this.localCorrectOption
              });
          });
      }
  },
  created() {
    // Initial population
    if (this.modelValue && this.modelValue.options) {
      this.localOptions = JSON.parse(JSON.stringify(this.modelValue.options)).map(opt => ({
          ...opt,
          fieldId: opt.id || this.generateId(),
          imagePreview: opt.image // Assuming image is already a URL or base64
      }));
      this.localCorrectOption = this.modelValue.correctOption;
      this.updateOptionIDsAndCorrectness(); // Ensure IDs and correct flags are set
    }
  }
};
</script>