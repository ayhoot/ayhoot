<template>
    <Teleport to="body">
        <transition name="ay-modal">
            <div
                v-if="modelValue"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
            >
                <!-- Backdrop: blurred glass, not a solid color overlay -->
                <div class="fixed inset-0 bg-black/10 backdrop-blur-sm" @click="onBackdrop"></div>

                <!-- Panel -->
                <div :class="['relative z-10 flex flex-col w-full bg-white rounded-lg shadow-xl max-h-[90vh]', sizeClass]">
                    <!-- Header -->
                    <div
                        v-if="$slots.header || title || closable"
                        class="flex items-center justify-between px-6 py-4 border-b border-gray-200"
                    >
                        <slot name="header">
                            <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                        </slot>
                        <button
                            v-if="closable"
                            type="button"
                            class="ms-4 text-gray-400 hover:text-gray-600 transition"
                            :aria-label="'Close'"
                            @click="close"
                        >
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div :class="['overflow-y-auto', bodyClass]">
                        <slot />
                    </div>

                    <!-- Footer -->
                    <div
                        v-if="$slots.footer"
                        class="px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-2"
                    >
                        <slot name="footer" :close="close" />
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<script setup>
/**
 * Shared base modal. Consistent overlay + centered panel + header/body/footer slots.
 * Use with v-model for open state:
 *
 *   <Modal v-model="showModal" title="Edit" size="lg">
 *     ...body...
 *     <template #footer="{ close }">
 *       <AyButton @click="close">Cancel</AyButton>
 *       <AyButton @click="save">Save</AyButton>
 *     </template>
 *   </Modal>
 */
import { computed, watch, onBeforeUnmount } from 'vue'

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    title: { type: String, default: '' },
    size: { type: String, default: 'md' }, // sm | md | lg | xl | 2xl | full
    closable: { type: Boolean, default: true },
    closeOnBackdrop: { type: Boolean, default: true },
    closeOnEsc: { type: Boolean, default: true },
    bodyClass: { type: String, default: 'px-6 py-4' },
})

const emit = defineEmits(['update:modelValue', 'close'])

const SIZES = {
    sm: 'max-w-sm',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
    '2xl': 'max-w-6xl',
    full: 'max-w-[95vw]',
}
const sizeClass = computed(() => SIZES[props.size] || SIZES.md)

function close() {
    emit('update:modelValue', false)
    emit('close')
}

function onBackdrop() {
    if (props.closeOnBackdrop) close()
}

function onKeydown(e) {
    if (e.key === 'Escape' && props.closeOnEsc && props.modelValue) close()
}

watch(
    () => props.modelValue,
    (open) => {
        if (typeof document === 'undefined') return
        document.body.style.overflow = open ? 'hidden' : ''
        if (open) document.addEventListener('keydown', onKeydown)
        else document.removeEventListener('keydown', onKeydown)
    },
    { immediate: true }
)

onBeforeUnmount(() => {
    if (typeof document !== 'undefined') {
        document.removeEventListener('keydown', onKeydown)
        document.body.style.overflow = ''
    }
})
</script>

<style scoped>
.ay-modal-enter-active,
.ay-modal-leave-active {
    transition: opacity 0.2s ease;
}
.ay-modal-enter-from,
.ay-modal-leave-to {
    opacity: 0;
}
</style>
