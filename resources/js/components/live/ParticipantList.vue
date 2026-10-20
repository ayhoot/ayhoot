<template>
  <div class="xl:col-span-1 bg-white border border-gray-200/80 rounded-2xl overflow-hidden shadow-sm flex flex-col h-full max-h-[700px]">
    <!-- Header -->
    <div class="bg-gray-50/80 border-b border-gray-200/80 p-4 flex items-center justify-between shrink-0">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">
          Players ({{ participants.length }})
        </h2>
      </div>
      <AyButton
        type="light-gray"
        @click="$emit('refresh')"
        class="text-xs p-1.5 h-8 w-8 flex items-center justify-center rounded-lg"
        title="Refresh participant list"
      >
        <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
      </AyButton>
    </div>

    <!-- Participants Scroll Area -->
    <div class="flex-1 overflow-y-auto p-3 space-y-1.5 custom-scrollbar">
      <div v-if="participants.length === 0" class="text-center py-12 text-gray-400 text-xs font-medium">
        No participants joined yet
      </div>
      <div
        v-for="(p, idx) in participants"
        :key="p.id"
        @click="$emit('select-participant', p)"
        :class="selectedParticipantId === p.id
          ? 'bg-blue-50/80 border-blue-300 ring-1 ring-blue-300'
          : 'bg-white hover:bg-gray-50/80 border-gray-200/80 hover:border-gray-300'"
        class="p-2.5 rounded-xl border transition-all cursor-pointer flex items-center gap-2.5"
      >
        <div class="relative shrink-0">
          <img
            :src="p.avatar || '/images/default-avatar.png'"
            :alt="p.name"
            class="w-9 h-9 rounded-full object-cover bg-gray-100 border border-gray-200"
          />
          <span
            v-if="idx < 3"
            class="absolute -top-1 -right-1 w-4 h-4 rounded-full text-[9px] font-black flex items-center justify-center text-white"
            :class="idx === 0 ? 'bg-amber-500' : idx === 1 ? 'bg-slate-400' : 'bg-amber-700'"
          >
            {{ idx + 1 }}
          </span>
        </div>

        <div class="flex-1 min-w-0">
          <p class="text-xs font-bold text-gray-900 truncate">{{ p.name }}</p>
          <p class="text-[11px] text-gray-500 font-medium">{{ p.total_score || 0 }} pts</p>
        </div>

        <span class="text-xs font-mono font-bold text-gray-400 shrink-0">
          #{{ idx + 1 }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import AyButton from '@/components/atoms/button/AyButton.vue'

export default {
  name: 'ParticipantList',
  components: {
    AyButton
  },
  props: {
    participants: {
      type: Array,
      default: () => []
    },
    selectedParticipantId: [Number, String]
  },
  emits: ['refresh', 'select-participant']
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(209, 213, 219, 0.8);
  border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.8);
}
</style>
