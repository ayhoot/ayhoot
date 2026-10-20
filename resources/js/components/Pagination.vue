<template>
    <div class="flex justify-between items-center mt-6">
      <button
        @click="changePage(pagination.current_page - 1)"
        :disabled="!pagination.prev_page_url"
        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md disabled:opacity-50"
      >
        Previous
      </button>
  
      <span class="text-gray-700">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
  
      <button
        @click="changePage(pagination.current_page + 1)"
        :disabled="!pagination.next_page_url"
        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md disabled:opacity-50"
      >
        Next
      </button>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      pagination: {
        type: Object,
        required: true,
      },
    },
    methods: {
      changePage(page) {
        this.$emit('page-changed', page);
      },
    },
  };
  </script>
  
  <style scoped>
  /* You can add Tailwind CSS styles if needed */
  </style>
  