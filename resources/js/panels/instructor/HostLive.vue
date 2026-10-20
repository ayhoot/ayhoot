<template>
  <InstructorLayout :secondary="false">
    <div class="w-full space-y-6 pb-8">
      <!-- Header Bar -->
      <HostHeader
        :exam-title="exam?.title"
        :exam-description="exam?.description"
        :qr-code-url="qrCodeUrl"
        :status="session?.status"
        :status-label="statusLabel"
        :pin="session?.pin"
        :participant-count="participants.length"
        :total-questions="totalQuestions"
        :exam-duration="examDuration"
        :formatted-time="formatTime(sessionTimeRemaining)"
        :timer-paused="timerPaused"
        :has-question-options="currentQuestion && currentQuestion.options?.length > 0"
        @start-session="startSession"
        @start-question="startQuestion"
        @pause-timer="pauseTimer"
        @resume-timer="resumeTimer"
      />

      <!-- Main Content Grid (Arena immediately below PIN section) -->
      <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <!-- Left Sidebar: Participants List -->
        <ParticipantList
          :participants="participants"
          :selected-participant-id="selectedParticipant?.id"
          @refresh="loadParticipants"
          @select-participant="selectedParticipant = $event"
        />

        <!-- Center: Question, Chart & Live Student Submissions -->
        <QuestionDisplay
          :current-question="currentQuestion"
          :current-question-index="session?.current_question_index"
          :total-questions="totalQuestions"
          :status="session?.status"
          :background-style="getBackgroundStyle()"
          :chart-data="chartData"
          :chart-options="chartOptions"
          :answer-counts="answerCounts"
          :submitted-answers="submittedAnswers"
          :time-percentage="timePercentage"
          :question-time-remaining="questionTimeRemaining"
          :auto-advance="autoAdvance"
          :is-navigating="isNavigating"
          :showing-results="showingResults"
          :question-results="questionResults"
          @previous="previousQuestion"
          @next="nextQuestion"
          @toggle-auto-advance="toggleAutoAdvance"
        />

        <!-- Right Sidebar: Leaderboard & Details -->
        <div class="xl:col-span-1 space-y-6">
          <ParticipantDetails :participant="selectedParticipant" />
          <LiveRankings :rankings="liveRankings" />
        </div>
      </div>

      <!-- Session Configuration Card (Below main arena) -->
      <SessionConfig
        :status="session?.status"
        :is-editing="isConfigEditing"
        :session-config="sessionConfig"
        @toggle-edit="isConfigEditing = !isConfigEditing"
        @update-config="updateSessionConfig"
      />

      <!-- Session Finished Card -->
      <SessionFinished
        :is-finished="session?.status === 'finished'"
        :rankings="finalRankings"
        @view-report="goToReport"
      />
    </div>
  </InstructorLayout>
</template>

<script>
import { computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useHostSession } from '@/composables/useHostSession'

// Components
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import HostHeader from '@/components/live/HostHeader.vue'
import SessionConfig from '@/components/live/SessionConfig.vue'
import ParticipantList from '@/components/live/ParticipantList.vue'
import QuestionDisplay from '@/components/live/QuestionDisplay.vue'
import ParticipantDetails from '@/components/live/ParticipantDetails.vue'
import LiveRankings from '@/components/live/LiveRankings.vue'
import SessionFinished from '@/components/live/SessionFinished.vue'

export default {
  name: 'HostLive',
  components: {
    InstructorLayout,
    HostHeader,
    SessionConfig,
    ParticipantList,
    QuestionDisplay,
    ParticipantDetails,
    LiveRankings,
    SessionFinished
  },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const sessionId = route.params.sessionId

    const {
      session, exam, participants, selectedParticipant, currentQuestion, 
      questionResults, finalRankings, questionTimeRemaining, sessionTimeRemaining,
      timerPaused, answerCounts, submittedAnswers, liveRankings, showingResults, qrCodeUrl,
      autoAdvance, isConfigEditing, sessionConfig, totalQuestions, examDuration,
      timePercentage, formatTime, getAnswerCount, pauseTimer, resumeTimer,
      startSession, startQuestion, nextQuestion, previousQuestion, finishSession,
      toggleAutoAdvance, updateSessionConfig, loadParticipants, isNavigating
    } = useHostSession(sessionId)

    const statusLabel = computed(() => {
      const status = session.value?.status
      return status === 'waiting' ? 'Waiting...' :
             status === 'started' ? 'Started' :
             status === 'question' ? 'Question' :
             status === 'finished' ? 'Finished' : '—'
    })

    const chartData = computed(() => {
      const options = currentQuestion.value?.options || []
      const labels = options.map((opt, idx) => `${['A', 'B', 'C', 'D'][idx] || (idx + 1)}. ${opt.text || opt.option_text || opt.title || 'Choice ' + (idx + 1)}`)
      const data = options.map(opt => getAnswerCount(opt.id) || 0)
      const colors = ['#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899']
      
      return {
        labels,
        datasets: [{
          data,
          backgroundColor: colors.slice(0, options.length),
          borderColor: '#1e293b',
          borderWidth: 2,
        }]
      }
    })

    const chartOptions = {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: '#c0c5ce',
            font: { size: 11 },
          }
        },
        tooltip: {
          callbacks: {
            label: (context) => `${context.label}: ${context.parsed} responses`
          }
        }
      }
    }

    const getImageUrl = (filePath) => {
      if (!filePath) return '/imgHolders/CardHolderImage.jpg'
      if (filePath.startsWith('http')) return filePath
      const cleanP = filePath.replace(/\\/g, '').replace(/^\//, '')
      if (cleanP.startsWith('uploads/questions/')) return `/${cleanP}`
      if (cleanP.startsWith('questions/')) return `/uploads/${cleanP}`
      return `/uploads/questions/${cleanP}`
    }

    const getBackgroundStyle = () => {
      const defaultBgUrl = '/imgHolders/bg3.jpg'
      let primaryBgUrl = ''

      if (currentQuestion.value && currentQuestion.value.q_options) {
        try {
          const options = typeof currentQuestion.value.q_options === 'string' 
            ? JSON.parse(currentQuestion.value.q_options) 
            : currentQuestion.value.q_options
          if (options && options.background_image) {
            primaryBgUrl = getImageUrl(options.background_image)
          }
        } catch (e) {}
      }

      if (primaryBgUrl) {
        return `background-image: linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2)), url('${primaryBgUrl}'), url('${defaultBgUrl}'); background-size: cover; background-position: center;`
      } else {
        return `background-image: url('${defaultBgUrl}'); background-size: cover; background-position: center;`
      }
    }

    // Navigate to this session's detailed report (stats + questions + per-student answers).
    const goToReport = () => {
      router.push(`/${localStorage.getItem('role') || 'teacher'}/live-report/${sessionId}`)
    }

    // After finishing, show the podium briefly then redirect to the full report.
    watch(() => session.value?.status, (newStatus) => {
      if (newStatus === 'finished') {
        setTimeout(goToReport, 5000)
      }
    })

    return {
      session, exam, participants, selectedParticipant, currentQuestion, 
      questionResults, finalRankings, questionTimeRemaining, sessionTimeRemaining,
      timerPaused, answerCounts, submittedAnswers, liveRankings, showingResults, qrCodeUrl,
      autoAdvance, isConfigEditing, sessionConfig, totalQuestions, examDuration,
      timePercentage, statusLabel, formatTime, getAnswerCount, pauseTimer, resumeTimer,
      startSession, startQuestion, nextQuestion, previousQuestion, finishSession,
      toggleAutoAdvance, updateSessionConfig, loadParticipants, chartData, chartOptions,
      getBackgroundStyle, goToReport, isNavigating
    }
  }
}
</script>

<style scoped>
div {
  transition: all 150ms cubic-bezier(0.4, 0, 0.2, 1);
}

::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(229, 231, 235, 0.5);
}

::-webkit-scrollbar-thumb {
  background: rgba(59, 130, 246, 0.5);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(59, 130, 246, 0.8);
}
</style>
