<template>
  <div>
    <div class="w-full relative mt-15 sm:mt-0">
      <div class="w-full lg:h-[30vh] h-[15vh] bg-gray-200 animate-pulse"></div>
    </div>
    <div class="w-full lg:px-12 px-6 -mt-12 sm:-mt-16 mb-8">
      <div
        class="bg-white p-4 sm:p-6 rounded-xl shadow-lg flex flex-col lg:flex-row items-center justify-between animate-pulse"
      >
        <div class="flex flex-col sm:flex-row items-center text-center sm:text-left">
          <div
            class="w-24 h-24 sm:w-32 sm:h-32 rounded-full bg-gray-300 border-4 border-white -mt-12 sm:-mt-16 lg:-mt-20 mb-2 sm:mb-0"
            :class="isRTL ? 'sm:ml-6' : 'sm:mr-6'"
          ></div>
          <div class="space-y-2">
            <div class="h-8 bg-gray-300 rounded w-48"></div>
            <div class="h-4 bg-gray-300 rounded w-32"></div>
          </div>
        </div>
        <div class="flex items-center gap-8 mt-4 lg:mt-0">
          <div class="h-10 bg-gray-200 rounded w-16"></div>
          <div class="h-10 bg-gray-200 rounded w-16"></div>
          <div class="h-10 bg-gray-200 rounded w-16"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  isRTL: Boolean
})
</script>

<style scoped>
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
  50% { opacity: 0.5; }
}
</style>
