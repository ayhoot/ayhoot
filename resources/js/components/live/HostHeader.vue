<template>
  <div class="bg-white border border-gray-200/80 rounded-2xl p-5 sm:p-6 mb-6 shadow-sm">
    <!-- Top Bar: Title, QR, Status, & Primary Actions -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-gray-100">
      <!-- Title & Details -->
      <div class="space-y-1">
        <div class="flex items-center gap-2.5">
          <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shadow-xs">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </span>
          <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">Live Session</h1>
        </div>
        <p class="text-sm font-medium text-gray-700 pt-0.5">{{ examTitle || 'Loading session details...' }}</p>
        <p v-if="examDescription" class="text-xs text-gray-500 line-clamp-1">{{ examDescription }}</p>
      </div>

      <!-- Right Controls: QR Preview + Status + Action Buttons -->
      <div class="flex flex-wrap items-center gap-4 sm:gap-6 self-start lg:self-auto">
        <!-- QR Thumbnail & Download -->
        <div class="flex flex-col items-center gap-1.5">
          <div class="w-16 h-16 sm:w-20 sm:h-20 p-1 bg-white border border-gray-200 rounded-xl shadow-xs flex items-center justify-center shrink-0 overflow-hidden">
            <img v-if="qrCodeUrl" :src="qrCodeUrl" alt="Session QR" class="w-full h-full object-contain" />
            <div v-else class="text-[10px] text-gray-400 font-semibold text-center">QR Code</div>
          </div>
          <button
            v-if="qrCodeUrl"
            type="button"
            @click="downloadQr"
            class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] sm:text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-md transition-colors shadow-2xs cursor-pointer"
            title="Download QR Code"
          >
            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span>Download</span>
          </button>
        </div>

        <!-- Status & Main CTA Buttons -->
        <div class="flex flex-col gap-2 items-start sm:items-end">
          <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500 font-medium">Status:</span>
            <StatusBadge :status="status">{{ statusLabel }}</StatusBadge>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <!-- Start Session -->
            <AyButton
              v-if="status === 'waiting' && participantCount > 0"
              type="light-green"
              @click="$emit('start-session')"
              class="text-xs sm:text-sm px-4 py-2 gap-1.5 shadow-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Start Session
            </AyButton>

            <!-- Waiting state disabled button hint -->
            <span
              v-else-if="status === 'waiting' && participantCount === 0"
              class="text-xs text-gray-400 italic bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200"
            >
              Waiting for participants to join...
            </span>

            <!-- Start Question -->
            <AyButton
              v-if="status === 'started' && hasQuestionOptions"
              type="dash-blue"
              @click="$emit('start-question')"
              class="text-xs sm:text-sm px-4 py-2 gap-1.5 shadow-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
              </svg>
              Start Question
            </AyButton>

            <!-- Distinguishable Pause/Resume Button in Actions -->
            <button
              v-if="status === 'started' || status === 'question'"
              type="button"
              @click="timerPaused ? $emit('resume-timer') : $emit('pause-timer')"
              :class="timerPaused 
                ? 'bg-emerald-600 hover:bg-emerald-700 text-white ring-2 ring-emerald-300 animate-pulse' 
                : 'bg-amber-500 hover:bg-amber-600 text-white ring-2 ring-amber-300'"
              class="text-xs sm:text-sm px-3.5 py-2 font-bold rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer"
              :title="timerPaused ? 'Resume Timer' : 'Pause Timer'"
            >
              <svg v-if="!timerPaused" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
              </svg>
              <span>{{ timerPaused ? 'Resume' : 'Pause' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- PIN & Stats Metrics Grid (Fully Responsive) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mt-5">
      <!-- 1. Game PIN -->
      <div class="bg-gray-50/80 rounded-xl p-3.5 border border-gray-200/80 flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Join PIN</span>
          <button
            v-if="pin"
            @click="copyPin"
            class="text-[11px] font-semibold text-blue-600 hover:text-blue-800 cursor-pointer"
            title="Copy Game PIN"
          >
            {{ isPinCopied ? 'Copied!' : 'Copy' }}
          </button>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-gray-900 mt-1 font-mono tracking-wider">
          {{ pin || '—' }}
        </div>
      </div>

      <!-- 2. Participants -->
      <div class="bg-gray-50/80 rounded-xl p-3.5 border border-gray-200/80 flex flex-col justify-between">
        <div class="flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Players</span>
        </div>
        <div class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">
          {{ participantCount }}
        </div>
      </div>

      <!-- 3. Total Questions -->
      <div class="bg-gray-50/80 rounded-xl p-3.5 border border-gray-200/80 flex flex-col justify-between">
        <div class="flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Questions</span>
        </div>
        <div class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">
          {{ totalQuestions }}
        </div>
      </div>

      <!-- 4. Duration -->
      <div class="bg-gray-50/80 rounded-xl p-3.5 border border-gray-200/80 flex flex-col justify-between">
        <div class="flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Est. Time</span>
        </div>
        <div class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">
          {{ examDuration || '—' }} <span class="text-sm font-medium text-gray-500">min</span>
        </div>
      </div>

      <!-- 5. Time Remaining / Controls -->
      <div class="col-span-2 sm:col-span-1 bg-blue-50/80 border border-blue-200 rounded-xl p-3.5 flex flex-col justify-between">
        <div class="text-[11px] text-blue-700 font-bold uppercase tracking-wider flex items-center justify-between">
          <span>Remaining</span>
          <div v-if="status === 'started' || status === 'question'" class="flex gap-1.5">
            <button
              v-if="!timerPaused"
              type="button"
              @click="$emit('pause-timer')"
              class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-lg shadow-xs transition-all flex items-center gap-1 cursor-pointer ring-1 ring-amber-300"
              title="Pause Timer"
            >
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              <span>Pause</span>
            </button>
            <button
              v-else
              type="button"
              @click="$emit('resume-timer')"
              class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-xs transition-all flex items-center gap-1 cursor-pointer ring-1 ring-emerald-300 animate-pulse"
              title="Resume Timer"
            >
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
              </svg>
              <span>Resume</span>
            </button>
          </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-blue-800 mt-1 flex items-center gap-2">
          {{ formattedTime }}
          <span v-if="timerPaused" class="text-xs px-2 py-0.5 bg-amber-100 text-amber-800 rounded font-semibold animate-pulse">Paused</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import AyButton from '@/components/atoms/button/AyButton.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import { useToast } from 'vue-toast-notification'

export default {
  name: 'HostHeader',
  components: {
    AyButton,
    StatusBadge
  },
  setup() {
    const toast = useToast()
    return { toast }
  },
  props: {
    examTitle: String,
    examDescription: String,
    qrCodeUrl: String,
    status: String,
    statusLabel: String,
    pin: String,
    participantCount: Number,
    totalQuestions: Number,
    examDuration: Number,
    formattedTime: String,
    timerPaused: Boolean,
    hasQuestionOptions: Boolean
  },
  emits: ['start-session', 'start-question', 'pause-timer', 'resume-timer'],
  data() {
    return {
      isPinCopied: false
    }
  },
  methods: {
    async copyPin() {
      if (!this.pin) return
      try {
        await navigator.clipboard.writeText(String(this.pin))
        this.isPinCopied = true
        this.toast.success('PIN copied to clipboard!')
        setTimeout(() => {
          this.isPinCopied = false
        }, 2000)
      } catch (e) {
        console.error('Failed to copy PIN:', e)
      }
    },
    downloadQr() {
      if (!this.qrCodeUrl) return
      const link = document.createElement('a')
      link.href = this.qrCodeUrl
      link.download = `session-pin-${this.pin || 'qr'}.png`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      this.toast.success('QR Code downloaded!')
    }
  }
}
</script>
