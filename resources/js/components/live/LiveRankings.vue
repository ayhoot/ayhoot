<template>
  <div class="bg-white border border-gray-200/80 rounded-2xl overflow-hidden shadow-sm">
    <!-- Header -->
    <div class="bg-gray-50/80 border-b border-gray-200/80 px-5 py-3.5 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
          <path d="M19 5h-2V3a1 1 0 00-1-1H8a1 1 0 00-1 1v2H5a3 3 0 00-3 3v1a5 5 0 004.9 5H8.1A6 6 0 0011 16.9V19H8a1 1 0 000 2h8a1 1 0 000-2h-3v-2.1a6 6 0 002.9-2.9h1.2A5 5 0 0022 9V8a3 3 0 00-3-3zM4 9V8a1 1 0 011-1h2v4.8A3 3 0 014 9zm16 0a3 3 0 01-3 2.8V7h2a1 1 0 011 1v1z" />
        </svg>
        <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Live Leaderboard</h2>
      </div>
      <span class="text-[11px] font-semibold text-gray-500">
        Top {{ Math.min(rankings.length, 10) }}
      </span>
    </div>

    <!-- Rankings List -->
    <div class="p-3.5 space-y-1.5">
      <div v-if="rankings.length === 0" class="text-center py-8 text-gray-400 text-xs font-medium">
        Leaderboard will appear once answers are scored
      </div>
      <div
        v-for="(p, idx) in rankings.slice(0, 10)"
        :key="p.id"
        class="flex items-center gap-3 p-2.5 rounded-xl border transition-all"
        :class="getRankingRowClass(idx)"
      >
        <!-- Rank Icon / Number -->
        <div class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-xs shrink-0" :class="getRankBadgeClass(idx)">
          <svg v-if="idx === 0" class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 5h-2V3a1 1 0 00-1-1H8a1 1 0 00-1 1v2H5a3 3 0 00-3 3v1a5 5 0 004.9 5H8.1A6 6 0 0011 16.9V19H8a1 1 0 000 2h8a1 1 0 000-2h-3v-2.1a6 6 0 002.9-2.9h1.2A5 5 0 0022 9V8a3 3 0 00-3-3zM4 9V8a1 1 0 011-1h2v4.8A3 3 0 014 9zm16 0a3 3 0 01-3 2.8V7h2a1 1 0 011 1v1z" />
          </svg>
          <svg v-else-if="idx === 1" class="w-4 h-4 text-slate-600" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" clip-rule="evenodd" />
          </svg>
          <svg v-else-if="idx === 2" class="w-4 h-4 text-orange-700" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" clip-rule="evenodd" />
          </svg>
          <span v-else>#{{ idx + 1 }}</span>
        </div>

        <!-- Name -->
        <div class="flex-1 min-w-0">
          <p class="text-xs font-bold text-gray-900 truncate">{{ p.name || p.nickname || 'Guest' }}</p>
        </div>

        <!-- Score -->
        <span class="text-xs font-black text-gray-800 shrink-0 font-mono">
          {{ p.total_score || 0 }} <span class="text-[10px] font-medium text-gray-400">pts</span>
        </span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LiveRankings',
  props: {
    rankings: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    getRankBadgeClass(idx) {
      if (idx === 0) return 'bg-amber-100 text-amber-900 text-sm'
      if (idx === 1) return 'bg-slate-200 text-slate-800 text-sm'
      if (idx === 2) return 'bg-orange-100 text-orange-900 text-sm'
      return 'bg-gray-100 text-gray-600'
    },
    getRankingRowClass(idx) {
      if (idx === 0) return 'bg-amber-50/60 border-amber-200'
      if (idx === 1) return 'bg-slate-50/60 border-slate-200'
      if (idx === 2) return 'bg-orange-50/60 border-orange-200'
      return 'bg-white border-gray-200/80 hover:bg-gray-50/80'
    }
  }
}
</script>
