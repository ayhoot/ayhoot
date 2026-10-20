<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="onCancel"></div>
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full z-10">
        <h3 class="text-lg font-semibold mb-2">{{ title }}</h3>
        <p class="text-sm text-gray-700 mb-4">{{ message }}</p>
        <div class="flex justify-end gap-2">
          <button @click="onCancel" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">{{ cancelText }}</button>
          <button @click="onConfirm" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">{{ confirmText }}</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script>
export default {
  name: 'ConfirmModal',
  props: {
    title: { type: String, default: 'Confirm' },
    message: { type: String, default: 'Are you sure?' },
    confirmText: { type: String, default: 'Yes' },
    cancelText: { type: String, default: 'Cancel' },
  },
  emits: ['confirm', 'cancel'],
  methods: {
    onConfirm() { this.$emit('confirm') },
    onCancel() { this.$emit('cancel') },
  },
}
</script>

<style scoped>
/* minimal styling; project CSS will override */
</style>
