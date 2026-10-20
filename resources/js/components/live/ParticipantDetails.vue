<template>
  <div v-if="participant" class="bg-white border border-gray-200/80 rounded-2xl overflow-hidden shadow-sm">
    <!-- Header -->
    <div class="bg-gray-50/80 border-b border-gray-200/80 px-5 py-3.5 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Candidate Details</h2>
      </div>
      <span class="text-[11px] font-mono px-2 py-0.5 bg-gray-200/70 text-gray-700 rounded-md font-semibold">
        ID: {{ participant.student_id || participant.id }}
      </span>
    </div>

    <!-- Body -->
    <div class="p-5 space-y-4">
      <div class="flex items-center gap-3">
        <img
          :src="participant.avatar || '/images/default-avatar.png'"
          :alt="participant.name"
          class="w-12 h-12 rounded-full border border-gray-200 object-cover bg-gray-100 shadow-xs"
        />
        <div class="min-w-0">
          <p class="text-sm font-bold text-gray-900 truncate">{{ participant.name }}</p>
          <p class="text-xs text-gray-500 font-medium">Live Participant</p>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 gap-2.5">
        <div class="bg-blue-50/70 rounded-xl p-3 border border-blue-100">
          <div class="text-[11px] text-blue-700 font-bold uppercase tracking-wider">Score</div>
          <div class="text-xl font-black text-blue-900 mt-0.5">{{ participant.total_score || 0 }}</div>
        </div>
        <div class="bg-emerald-50/70 rounded-xl p-3 border border-emerald-100">
          <div class="text-[11px] text-emerald-700 font-bold uppercase tracking-wider">Correct</div>
          <div class="text-xl font-black text-emerald-900 mt-0.5">{{ participant.correct_answers || 0 }}</div>
        </div>
      </div>

      <div class="bg-gray-50/80 rounded-xl p-3 border border-gray-200/80 flex items-center justify-between">
        <span class="text-xs font-semibold text-gray-600">Avg Response Time</span>
        <span class="text-sm font-bold text-gray-900">
          {{ participant.average_answer_time ? (participant.average_answer_time / 1000).toFixed(2) : '—' }}s
        </span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ParticipantDetails',
  props: {
    participant: {
      type: Object,
      default: null
    }
  }
}
</script>
