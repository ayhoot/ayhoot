<template>
    <div ref="root" class="relative inline-block text-start">
        <!-- Trigger -->
        <div @click="toggle">
            <slot name="trigger" :open="open">
                <button
                    type="button"
                    :disabled="disabled"
                    class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium rounded-md border-2 border-gray-300 bg-white hover:bg-gray-50 focus:outline-none focus:border-[#3450AE] disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span>{{ label }}</span>
                    <svg class="h-4 w-4 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
            </slot>
        </div>

        <!-- Menu -->
        <transition name="ay-dd">
            <div
                v-if="open"
                :class="[
                    'absolute z-40 mt-1 min-w-[10rem] rounded-md bg-white shadow-lg border border-gray-100 py-1',
                    align === 'end' ? 'end-0' : 'start-0',
                ]"
                role="menu"
            >
                <slot :close="close">
                    <button
                        v-for="(item, i) in items"
                        :key="item.value ?? i"
                        type="button"
                        :disabled="item.disabled"
                        class="flex w-full items-center gap-2 px-4 py-2 text-sm text-start hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="item.danger ? 'text-red-600 hover:bg-red-50' : 'text-gray-700'"
                        role="menuitem"
                        @click="onSelect(item)"
                    >
                        <component :is="item.icon" v-if="item.icon" class="h-4 w-4" />
                        <span>{{ item.label }}</span>
                    </button>
                </slot>
            </div>
        </transition>
    </div>
</template>

<script setup>
/**
 * Generic dropdown menu. Either pass `items` ([{ label, value, icon?, danger?, disabled? }])
 * and listen to @select, or provide a custom default slot (receives { close }).
 * The trigger defaults to a labelled button; override via the #trigger slot.
 * Translation-ready: pass already-translated `label` / item labels.
 */
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    label: { type: String, default: '' },
    items: { type: Array, default: () => [] },
    align: { type: String, default: 'start' }, // 'start' | 'end'
    disabled: { type: Boolean, default: false },
    closeOnSelect: { type: Boolean, default: true },
})

const emit = defineEmits(['select', 'open', 'close'])

const open = ref(false)
const root = ref(null)

function toggle() {
    if (props.disabled) return
    open.value ? close() : show()
}
function show() {
    open.value = true
    emit('open')
}
function close() {
    if (!open.value) return
    open.value = false
    emit('close')
}
function onSelect(item) {
    if (item.disabled) return
    emit('select', item)
    if (props.closeOnSelect) close()
}

function onDocClick(e) {
    if (open.value && root.value && !root.value.contains(e.target)) close()
}
function onKey(e) {
    if (e.key === 'Escape') close()
}

onMounted(() => {
    document.addEventListener('click', onDocClick)
    document.addEventListener('keydown', onKey)
})
onBeforeUnmount(() => {
    document.removeEventListener('click', onDocClick)
    document.removeEventListener('keydown', onKey)
})
</script>

<style scoped>
.ay-dd-enter-active,
.ay-dd-leave-active {
    transition: opacity 0.12s ease, transform 0.12s ease;
}
.ay-dd-enter-from,
.ay-dd-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>
