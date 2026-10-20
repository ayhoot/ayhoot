<template>
  <div class="mt-4 bg-white border border-gray-200/80 rounded-2xl p-5 shadow-sm">
    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Session Settings</h2>
      </div>
      <AyButton
        v-if="status === 'waiting' || status === 'started'"
        :type="isEditing ? 'dash-blue' : 'light-gray'"
        @click="$emit('toggle-edit')"
        class="text-xs px-3 py-1.5"
      >
        {{ isEditing ? 'Done' : 'Edit Settings' }}
      </AyButton>
    </div>
    
    <!-- View Mode -->
    <div v-if="!isEditing" class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mt-4">
      <div v-for="(config, key) in configs" :key="key" class="bg-gray-50/80 rounded-xl p-3.5 border border-gray-200/80 flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-gray-800">{{ config.label }}</span>
          <span
            :class="config.value ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700'"
            class="px-2 py-0.5 text-[11px] font-bold rounded-full"
          >
            {{ config.value ? 'Enabled' : 'Disabled' }}
          </span>
        </div>
        <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ config.description }}</p>
      </div>
    </div>

    <!-- Edit Mode -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mt-4">
      <div v-for="(config, key) in configs" :key="key" class="bg-blue-50/50 rounded-xl p-3.5 border border-blue-200 flex flex-col justify-between">
        <label class="flex items-center justify-between cursor-pointer">
          <span class="text-xs font-bold text-gray-900">{{ config.label }}</span>
          <input
            type="checkbox"
            v-model="config.internalValue"
            @change="$emit('update-config', { [key]: config.internalValue })"
            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
          />
        </label>
        <p class="text-xs text-gray-500 mt-2">{{ config.description }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import AyButton from '@/components/atoms/button/AyButton.vue'

export default {
  name: 'SessionConfig',
  components: {
    AyButton
  },
  props: {
    status: String,
    isEditing: Boolean,
    sessionConfig: Object
  },
  emits: ['toggle-edit', 'update-config'],
  computed: {
    configs() {
      return {
        auto_advance: {
          label: 'Auto Advance',
          value: this.sessionConfig?.auto_advance,
          internalValue: this.sessionConfig?.auto_advance,
          description: 'Automatically advance to next question when time expires'
        },
        teacher_paced: {
          label: 'Teacher Paced',
          value: this.sessionConfig?.teacher_paced,
          internalValue: this.sessionConfig?.teacher_paced,
          description: 'Only teacher controls question navigation'
        },
        show_results: {
          label: 'Show Results',
          value: this.sessionConfig?.show_results,
          internalValue: this.sessionConfig?.show_results,
          description: 'Display results to students after each question'
        }
      }
    }
  }
}
</script>
