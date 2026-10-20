<template>
  <div v-if="isFinished" class="mt-6 bg-gradient-to-br from-amber-500/10 via-orange-500/5 to-purple-500/10 border border-amber-200 rounded-3xl p-6 sm:p-8 text-center shadow-lg">
    <div class="max-w-2xl mx-auto">
      <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shadow-md animate-bounce">
        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
          <path d="M19 5h-2V3a1 1 0 00-1-1H8a1 1 0 00-1 1v2H5a3 3 0 00-3 3v1a5 5 0 004.9 5H8.1A6 6 0 0011 16.9V19H8a1 1 0 000 2h8a1 1 0 000-2h-3v-2.1a6 6 0 002.9-2.9h1.2A5 5 0 0022 9V8a3 3 0 00-3-3zM4 9V8a1 1 0 011-1h2v4.8A3 3 0 014 9zm16 0a3 3 0 01-3 2.8V7h2a1 1 0 011 1v1z" />
        </svg>
      </div>
      <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">
        Session Completed!
      </h2>
      <p class="text-gray-600 text-sm mt-1">
        All questions have concluded. Here are the top performers:
      </p>

      <!-- Podium Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mt-6 items-end">
        <!-- 2nd Place -->
        <div v-if="rankings[1]" class="bg-white/95 rounded-2xl p-4 border border-gray-200 shadow-sm order-2 sm:order-1">
          <div class="w-10 h-10 mx-auto mb-1.5 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center border border-slate-200">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
              <path fill-rule="evenodd" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" clip-rule="evenodd" />
            </svg>
          </div>
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500">2nd Place</span>
          <p class="text-base font-bold text-gray-900 truncate mt-1">{{ rankings[1].name || rankings[1].nickname || 'Guest' }}</p>
          <p class="text-xs font-black text-blue-600 font-mono mt-0.5">{{ rankings[1].score ?? rankings[1].total_score ?? 0 }} pts</p>
        </div>

        <!-- 1st Place (Winner) -->
        <div v-if="rankings[0]" class="bg-white rounded-2xl p-5 border-2 border-amber-300 shadow-md order-1 sm:order-2 -mt-2">
          <div class="w-12 h-12 mx-auto mb-1.5 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center border-2 border-amber-300 shadow-xs">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
              <path d="M19 5h-2V3a1 1 0 00-1-1H8a1 1 0 00-1 1v2H5a3 3 0 00-3 3v1a5 5 0 004.9 5H8.1A6 6 0 0011 16.9V19H8a1 1 0 000 2h8a1 1 0 000-2h-3v-2.1a6 6 0 002.9-2.9h1.2A5 5 0 0022 9V8a3 3 0 00-3-3zM4 9V8a1 1 0 011-1h2v4.8A3 3 0 014 9zm16 0a3 3 0 01-3 2.8V7h2a1 1 0 011 1v1z" />
            </svg>
          </div>
          <span class="text-xs font-extrabold uppercase tracking-wider text-amber-600">Champion</span>
          <p class="text-lg font-black text-gray-900 truncate mt-1">{{ rankings[0].name || rankings[0].nickname || 'Guest' }}</p>
          <p class="text-sm font-black text-emerald-600 font-mono mt-0.5">{{ rankings[0].score ?? rankings[0].total_score ?? 0 }} pts</p>
        </div>

        <!-- 3rd Place -->
        <div v-if="rankings[2]" class="bg-white/95 rounded-2xl p-4 border border-gray-200 shadow-sm order-3">
          <div class="w-10 h-10 mx-auto mb-1.5 rounded-full bg-orange-100 text-orange-700 flex items-center justify-center border border-orange-200">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
              <path fill-rule="evenodd" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" clip-rule="evenodd" />
            </svg>
          </div>
          <span class="text-xs font-bold uppercase tracking-wider text-orange-600">3rd Place</span>
          <p class="text-base font-bold text-gray-900 truncate mt-1">{{ rankings[2].name || rankings[2].nickname || 'Guest' }}</p>
          <p class="text-xs font-black text-blue-600 font-mono mt-0.5">{{ rankings[2].score ?? rankings[2].total_score ?? 0 }} pts</p>
        </div>
      </div>

      <!-- Action Button -->
      <div class="mt-8 flex flex-col items-center gap-2">
        <AyButton
          type="dash-blue"
          @click="$emit('view-report')"
          class="text-sm px-6 py-2.5 gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          View Full Session Report →
        </AyButton>
        <span class="text-xs text-gray-400">Auto-redirecting in a few moments...</span>
      </div>
    </div>
  </div>
</template>

<script>
import AyButton from '@/components/atoms/button/AyButton.vue'

export default {
  name: 'SessionFinished',
  components: {
    AyButton
  },
  emits: ['view-report'],
  props: {
    isFinished: Boolean,
    rankings: {
      type: Array,
      default: () => []
    }
  }
}
</script>
