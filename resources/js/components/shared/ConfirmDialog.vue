<template>
    <Modal
        :model-value="state.open"
        :title="state.title"
        size="sm"
        :closable="false"
        :close-on-backdrop="state.showCancel"
        @update:model-value="onModelUpdate"
    >
        <p v-if="state.message" class="text-sm text-gray-600 whitespace-pre-line">{{ state.message }}</p>

        <template #footer>
            <button
                v-if="state.showCancel"
                type="button"
                class="px-4 py-2 rounded-md text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition"
                @click="resolve(false)"
            >
                {{ state.cancelText }}
            </button>
            <button
                type="button"
                :class="[
                    'px-4 py-2 rounded-md text-sm font-medium text-white transition',
                    state.variant === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700',
                ]"
                @click="resolve(true)"
            >
                {{ state.confirmText }}
            </button>
        </template>
    </Modal>
</template>

<script setup>
/**
 * Singleton dialog that renders confirm()/alert() calls from useConfirm.
 * Mount once globally (App.vue). Do not use directly — call useConfirm() instead.
 */
import Modal from './Modal.vue'
import { confirmState as state, resolveConfirm } from '@/composables/useConfirm'

function resolve(result) {
    resolveConfirm(result)
}

// Backdrop/esc dismissal counts as cancel (false).
function onModelUpdate(open) {
    if (!open) resolveConfirm(false)
}
</script>
