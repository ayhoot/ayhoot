<template>
  <div class="xl:col-span-2 space-y-6">
    <!-- Current Question Card -->
    <div class="bg-white border border-gray-200/80 rounded-2xl overflow-hidden shadow-sm">
      <!-- Question Header -->
      <div class="bg-gray-50/80 border-b border-gray-200/80 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
            Q
          </span>
          <h2 class="text-base font-bold text-gray-900">
            Question {{ (currentQuestionIndex || 0) + 1 }} of {{ totalQuestions }}
          </h2>
        </div>
        <span v-if="currentQuestion?.type" class="px-2.5 py-1 bg-white border border-gray-200 rounded-full text-xs font-semibold text-gray-600 uppercase tracking-wider">
          {{ currentQuestion.type.replace('_', ' ') }}
        </span>
      </div>

      <div class="p-6 bg-white" :style="backgroundStyle">
        <div v-if="currentQuestion">
          <!-- Question Title/Text -->
          <div v-if="currentQuestion.title || currentQuestion.question_text" class="mb-5">
            <div
              v-if="isHTML(currentQuestion.title || currentQuestion.question_text)"
              class="text-gray-900 text-lg sm:text-xl font-bold leading-relaxed quill-content bg-white/90 backdrop-blur-xs p-4 rounded-xl border border-gray-100"
              v-html="currentQuestion.title || currentQuestion.question_text"
            ></div>
            <p v-else class="text-gray-900 text-lg sm:text-xl font-bold leading-relaxed bg-white/90 backdrop-blur-xs p-4 rounded-xl border border-gray-100">
              {{ currentQuestion.title || currentQuestion.question_text }}
            </p>
          </div>

          <!-- Question Image/Slide -->
          <div v-if="currentQuestion.slide || (currentQuestion.files && currentQuestion.files.length > 0)" class="mb-5">
            <div
              v-if="currentQuestion.type === 'slide' && currentQuestion.slide"
              class="bg-gray-50/95 backdrop-blur-xs rounded-xl p-5 quill-content text-gray-900 border border-gray-200"
              v-html="currentQuestion.slide"
            ></div>
            <div v-else-if="currentQuestion.files && currentQuestion.files.length > 0" class="flex gap-3 flex-wrap">
              <img
                v-for="(file, idx) in currentQuestion.files"
                :key="idx"
                :src="getImageUrl(file)"
                :alt="`Question media ${idx + 1}`"
                class="max-w-full max-h-56 object-contain rounded-xl border border-gray-200 shadow-xs"
                @error="handleImageError"
              />
            </div>
          </div>

          <!-- Response Progress & Options -->
          <div class="mt-6 space-y-4">
            <div v-if="currentQuestion.type === 'multiple_choice' || currentQuestion.type === 'true_false'">
              <div v-if="currentQuestion.options && currentQuestion.options.length > 0" class="space-y-2.5">
                <div class="flex items-center justify-between text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                  <span>Answer Choices</span>
                  <span>{{ totalResponses }} Total Responses</span>
                </div>

                <div
                  v-for="(opt, idx) in currentQuestion.options"
                  :key="opt.id"
                  class="p-3.5 bg-gray-50/90 rounded-xl border border-gray-200/80 transition-all hover:bg-gray-100/80"
                >
                  <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                      <span
                        class="w-6 h-6 rounded-md flex items-center justify-center font-bold text-xs text-white shrink-0 shadow-xs"
                        :style="{ backgroundColor: getOptionColor(idx) }"
                      >
                        {{ ['A', 'B', 'C', 'D'][idx] || String.fromCharCode(65 + idx) }}
                      </span>
                      <span class="text-gray-900 font-semibold text-sm truncate">
                        {{ opt.text || opt.option_text || opt.title }}
                      </span>
                    </div>
                    <span class="text-xs font-bold text-gray-600 shrink-0 ml-2">
                      {{ getAnswerCount(opt.id) }} responses ({{ Math.round(getBarPercentage(opt.id)) }}%)
                    </span>
                  </div>

                  <!-- Progress Bar -->
                  <div class="h-3 w-full bg-gray-200/80 rounded-full overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all duration-500"
                      :style="{
                        width: getBarPercentage(opt.id) + '%',
                        backgroundColor: getOptionColor(idx)
                      }"
                    ></div>
                  </div>
                </div>
              </div>
              
              <!-- Optional Distribution Chart -->
              <div v-if="hasResponses" class="bg-gray-50/80 rounded-xl p-4 border border-gray-200/80 mt-4">
                <div class="text-xs font-bold text-gray-500 mb-3 uppercase tracking-wider">Answer Distribution Chart</div>
                <div class="max-h-60 flex items-center justify-center">
                  <Pie
                    v-if="chartData.labels.length > 0"
                    :data="chartData"
                    :options="chartOptions"
                    class="max-h-56"
                  />
                </div>
              </div>
            </div>
            
            <!-- Other Question Types -->
            <div v-else class="space-y-3">
              <div class="bg-gray-50/80 rounded-xl p-4 border border-gray-200/80">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-semibold text-gray-700">Total Submissions:</span>
                  <span class="text-base font-bold text-gray-900">{{ totalResponses }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ typeDescription }}</p>
              </div>
            </div>

            <!-- Live Student Submissions List -->
            <div class="mt-5 pt-4 border-t border-gray-100">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                  <span class="w-6 h-6 rounded-md bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </span>
                  <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                    Student Answers
                  </h3>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                  {{ (submittedAnswers || []).length }} Responses
                </span>
              </div>

              <!-- Empty State -->
              <div
                v-if="!submittedAnswers || submittedAnswers.length === 0"
                class="p-4 rounded-xl bg-gray-50/70 border border-dashed border-gray-200 text-center"
              >
                <div v-if="status === 'question'" class="flex items-center justify-center gap-2 text-xs font-medium text-gray-500">
                  <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                  <span>Waiting for student responses...</span>
                </div>
                <p v-else class="text-xs text-gray-400">No responses submitted for this question yet.</p>
              </div>

              <!-- Answers List -->
              <div v-else class="max-h-64 overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                <div
                  v-for="(sub, sIdx) in submittedAnswers"
                  :key="sub.participant_id || sIdx"
                  class="p-2.5 bg-gray-50/90 hover:bg-gray-100/90 rounded-xl border border-gray-200/80 flex items-center justify-between gap-3 transition-colors"
                >
                  <!-- Student Info -->
                  <div class="flex items-center gap-2.5 min-w-0">
                    <img
                      :src="sub.avatar || '/images/default-avatar.png'"
                      :alt="sub.name"
                      class="w-7 h-7 rounded-full object-cover border border-gray-200 shrink-0"
                      @error="handleAvatarError"
                    />
                    <div class="min-w-0">
                      <p class="text-xs font-bold text-gray-900 truncate">{{ sub.name || 'Student' }}</p>
                      <p v-if="sub.answer_time_ms" class="text-[10px] text-gray-500">
                        {{ (sub.answer_time_ms / 1000).toFixed(1) }}s
                      </p>
                    </div>
                  </div>

                  <!-- Answer Content -->
                  <div class="flex items-center gap-2 shrink-0">
                    <div class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-gray-200 max-w-[180px] sm:max-w-xs truncate text-gray-800">
                      <span class="text-gray-400 text-[10px] mr-1">Answer:</span>
                      <span>{{ formatAnswerText(sub) }}</span>
                    </div>

                    <!-- Correctness / Score Badge -->
                    <span
                      v-if="sub.is_correct === true"
                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"
                    >
                      <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                      <span>+{{ sub.points_earned || 1000 }}</span>
                    </span>
                    <span
                      v-else-if="sub.is_correct === false"
                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200"
                    >
                      <svg class="w-3 h-3 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                      <span>0</span>
                    </span>
                    <span
                      v-else
                      class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200"
                    >
                      Submitted
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Timer Bar -->
          <div v-if="status === 'question'" class="mt-6 space-y-1.5">
            <div class="flex items-center justify-between text-xs font-bold">
              <span class="text-gray-500 uppercase tracking-wider">Time Limit</span>
              <span :class="questionTimeRemaining <= 5 ? 'text-red-600 animate-pulse font-extrabold' : 'text-gray-700'">
                {{ questionTimeRemaining }}s remaining
              </span>
            </div>
            <div class="h-2.5 w-full bg-gray-100 rounded-full overflow-hidden border border-gray-200">
              <div
                class="h-full rounded-full transition-all duration-1000"
                :class="questionTimeRemaining <= 5 ? 'bg-red-500' : 'bg-gradient-to-r from-blue-500 to-indigo-600'"
                :style="{ width: timePercentage + '%' }"
              ></div>
            </div>
          </div>

          <!-- Question Navigation Controls (Using AyButton) -->
          <div v-if="status === 'question'" class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-200">
            <!-- Previous Button -->
            <AyButton
              type="light-gray"
              @click="$emit('previous')"
              :disabled="currentQuestionIndex === 0 || isNavigating"
              class="w-full sm:flex-1 text-xs py-2 gap-1.5"
            >
              ← Previous
            </AyButton>

            <!-- Auto-Advance Toggle -->
            <AyButton
              :type="autoAdvance ? 'light-green' : 'light-gray'"
              @click="$emit('toggle-auto-advance')"
              class="w-full sm:flex-1 text-xs py-2 gap-1.5"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Auto-Advance {{ autoAdvance ? 'ON' : 'OFF' }}</span>
            </AyButton>

            <!-- Next / Finish Button -->
            <AyButton
              type="dash-blue"
              :disabled="isNavigating"
              @click="$emit('next')"
              class="w-full sm:flex-1 text-xs py-2 gap-1.5"
            >
              <svg v-if="isNavigating" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ currentQuestionIndex >= (totalQuestions - 1) ? 'Finish Exam' : 'Next Question' }}</span>
              <span v-if="!isNavigating">→</span>
            </AyButton>
          </div>
        </div>

        <div v-else class="text-center py-16">
          <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3 shadow-xs animate-bounce">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p class="text-gray-500 text-sm font-medium">Waiting to display question...</p>
        </div>
      </div>
    </div>

    <!-- Question Results Card (When Showing Results) -->
    <div v-if="showingResults && questionResults" class="bg-white border border-emerald-200 rounded-2xl overflow-hidden shadow-sm">
      <div class="bg-emerald-50/80 border-b border-emerald-100 px-6 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h2 class="text-base font-bold text-emerald-900">Question Results</h2>
        </div>
        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
          Completed
        </span>
      </div>
      <div class="p-6 space-y-3.5">
        <div
          v-for="stat in questionResults.option_stats"
          :key="stat.option.id"
          class="p-3 rounded-xl border"
          :class="stat.option.is_correct ? 'bg-emerald-50/60 border-emerald-200' : 'bg-gray-50/70 border-gray-200'"
        >
          <div class="flex items-center justify-between mb-1.5">
            <span class="font-semibold text-sm flex items-center gap-1.5" :class="stat.option.is_correct ? 'text-emerald-900' : 'text-gray-800'">
              <svg v-if="stat.option.is_correct" class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
              </svg>
              {{ stat.option.option_text || stat.option.text || '(Option)' }}
            </span>
            <span class="text-xs font-bold" :class="stat.option.is_correct ? 'text-emerald-700' : 'text-gray-500'">
              {{ stat.count }} ({{ Math.round(stat.percentage) }}%)
            </span>
          </div>
          <div class="h-2.5 w-full bg-gray-200/80 rounded-full overflow-hidden">
            <div
              class="h-full rounded-full transition-all"
              :class="stat.option.is_correct ? 'bg-emerald-500' : 'bg-blue-400'"
              :style="{ width: stat.percentage + '%' }"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Pie } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import AyButton from '@/components/atoms/button/AyButton.vue'

ChartJS.register(ArcElement, Tooltip, Legend)

export default {
  name: 'QuestionDisplay',
  components: {
    Pie,
    AyButton
  },
  props: {
    currentQuestion: Object,
    currentQuestionIndex: Number,
    totalQuestions: Number,
    status: String,
    backgroundStyle: String,
    chartData: Object,
    chartOptions: Object,
    answerCounts: Object,
    timePercentage: Number,
    questionTimeRemaining: Number,
    autoAdvance: Boolean,
    isNavigating: Boolean,
    showingResults: Boolean,
    questionResults: Object,
    submittedAnswers: {
      type: Array,
      default: () => []
    }
  },
  emits: ['previous', 'next', 'toggle-auto-advance'],
  computed: {
    hasResponses() {
      return Object.keys(this.answerCounts || {}).length > 0 || (this.submittedAnswers && this.submittedAnswers.length > 0)
    },
    totalResponses() {
      const fromCounts = Object.values(this.answerCounts || {}).reduce((a, b) => a + Number(b), 0)
      const fromList = (this.submittedAnswers || []).length
      return Math.max(fromCounts, fromList)
    },
    typeDescription() {
      const type = this.currentQuestion?.type
      if (type === 'ordinary') return 'Students can type their answers freely.'
      if (type === 'sequence') return 'Students arrange given items in correct sequence.'
      if (type === 'slide') return 'Informational slide — no participant answer needed.'
      return 'Open-ended question.'
    }
  },
  methods: {
    formatAnswerText(sub) {
      if (sub.answer_text) {
        if (typeof sub.answer_text === 'string' && sub.answer_text.startsWith('[')) {
          try {
            const arr = JSON.parse(sub.answer_text)
            if (Array.isArray(arr)) return arr.join(', ')
          } catch (e) {}
        }
        return sub.answer_text
      }
      if (sub.selected_option_id && this.currentQuestion?.options) {
        const opt = this.currentQuestion.options.find(o => o.id == sub.selected_option_id)
        if (opt) return opt.text || opt.option_text || opt.title || `Choice ${sub.selected_option_id}`
      }
      return 'Submitted'
    },
    handleAvatarError(e) {
      e.target.src = '/images/default-avatar.png'
    },
    isHTML(str) {
      return str && /<\/?[a-z][\s\S]*>/i.test(str)
    },
    getOptionColor(idx) {
      const palette = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899']
      return palette[idx % palette.length]
    },
    getImageUrl(filePath) {
       if (!filePath) return '/imgHolders/CardHolderImage.jpg'
       if (filePath.startsWith('http')) return filePath
       const cleanP = filePath.replace(/\\/g, '').replace(/^\//, '')
       if (cleanP.startsWith('uploads/questions/')) return `/${cleanP}`
       if (cleanP.startsWith('questions/')) return `/uploads/${cleanP}`
       return `/uploads/questions/${cleanP}`
    },
    handleImageError(e) {
      e.target.src = '/imgHolders/CardHolderImage.jpg'
    },
    getAnswerCount(optionId) {
      return this.answerCounts?.[optionId] || 0
    },
    getBarPercentage(optionId) {
      const total = this.totalResponses || 1
      return (this.getAnswerCount(optionId) / Math.max(total, 1)) * 100
    }
  }
}
</script>
