<template>
  <div class="space-y-6">
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-2">Content Type</label>
      <select
        :value="contentType"
        @input="$emit('update:contentType', $event.target.value)"
        class="w-full px-3 py-2 border-2 border-gray-300 rounded-md focus:outline-none focus:border-indigo-400"
      >
        <option value="">Select content type</option>
        <option value="course">Course</option>
        <option value="exam">Exam (Ayhoot)</option>
      </select>
    </div>

    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-2">Content ID</label>
      <input
        type="number"
        :value="contentId"
        @input="$emit('update:contentId', $event.target.value)"
        placeholder="Enter content ID"
        class="w-full px-3 py-2 border-2 border-gray-300 rounded-md focus:outline-none focus:border-indigo-400"
      />
    </div>

    <ResponsiveButton
      @click="$emit('load-content')"
      type="dash-blue"
      :disabled="!contentType || !contentId || isLoadingContent"
      class="w-full py-3"
    >
      <span v-if="isLoadingContent">Loading Content...</span>
      <span v-else>Load Content</span>
    </ResponsiveButton>

    <div class="border-t border-gray-200 pt-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <button
          @click="$router.push('/discover/courses')"
          class="p-4 border-2 border-blue-200 rounded-lg hover:bg-blue-50 text-left"
        >
          <div class="font-semibold text-blue-800">Browse Courses</div>
          <div class="text-sm text-gray-600">Explore available courses</div>
        </button>
        <button
          @click="$router.push('/discover/ayhoots')"
          class="p-4 border-2 border-green-200 rounded-lg hover:bg-green-50 text-left"
        >
          <div class="font-semibold text-green-800">Browse Exams</div>
          <div class="text-sm text-gray-600">Explore available exams</div>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'

defineProps({
  contentType: String,
  contentId: [String, Number],
  isLoadingContent: Boolean
})

defineEmits(['update:contentType', 'update:contentId', 'load-content'])
</script>
