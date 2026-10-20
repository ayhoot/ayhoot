<template>
    <div ref="root" class="w-full relative">
        <label v-if="label" class="block mb-1 text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Control: shows selected chips -->
        <div
            :class="[
                'w-full min-h-[2.6rem] flex flex-wrap items-center gap-1.5 px-2 py-1.5 border-2 rounded-md bg-white cursor-pointer transition-colors',
                error ? 'border-red-400' : open ? 'border-[#3450AE]' : 'border-gray-300',
                disabled && 'bg-gray-100 cursor-not-allowed opacity-70',
            ]"
            @click="toggle"
        >
            <template v-if="selected.length">
                <span
                    v-for="opt in selected"
                    :key="opt.value"
                    class="inline-flex items-center gap-1 bg-blue-50 text-[#3450AE] text-xs font-medium px-2 py-1 rounded"
                >
                    {{ opt.label }}
                    <button type="button" class="hover:text-red-500" @click.stop="remove(opt.value)" :aria-label="removeLabel">
                        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.3 4.3a1 1 0 011.4 0L10 8.6l4.3-4.3a1 1 0 111.4 1.4L11.4 10l4.3 4.3a1 1 0 01-1.4 1.4L10 11.4l-4.3 4.3a1 1 0 01-1.4-1.4L8.6 10 4.3 5.7a1 1 0 010-1.4z" clip-rule="evenodd" /></svg>
                    </button>
                </span>
            </template>
            <span v-else class="text-sm text-gray-400 px-1">{{ placeholder }}</span>

            <svg class="h-4 w-4 text-gray-500 ms-auto transition-transform" :class="open && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </div>

        <!-- Panel -->
        <transition name="ay-ms">
            <div v-if="open" class="absolute z-40 mt-1 w-full rounded-md bg-white shadow-lg border border-gray-100">
                <div v-if="searchable" class="p-2 border-b border-gray-100">
                    <input
                        ref="searchEl"
                        v-model="query"
                        type="text"
                        :placeholder="searchPlaceholder"
                        class="w-full px-2 py-1.5 text-sm border-2 border-gray-200 rounded focus:outline-none focus:border-[#3450AE]"
                        @click.stop
                    />
                </div>
                <ul class="max-h-56 overflow-y-auto py-1">
                    <li
                        v-for="opt in filtered"
                        :key="opt.value"
                        class="px-3 py-2 text-sm cursor-pointer flex items-center gap-2 hover:bg-gray-50"
                        @click.stop="toggleValue(opt.value)"
                    >
                        <span
                            class="h-4 w-4 shrink-0 rounded border-2 flex items-center justify-center"
                            :class="isSelected(opt.value) ? 'bg-[#3450AE] border-[#3450AE]' : 'border-gray-300'"
                        >
                            <svg v-if="isSelected(opt.value)" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.3 3.29 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd" /></svg>
                        </span>
                        <span class="truncate text-gray-700">{{ opt.label }}</span>
                    </li>
                    <li v-if="!filtered.length" class="px-3 py-3 text-sm text-gray-400 text-center">{{ emptyText }}</li>
                </ul>
            </div>
        </transition>

        <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
/**
 * Multi-select with chips and (optional) search. v-model holds an array of values.
 * Options: [{ value, label }] or string[]. Translation-ready: pass already-translated
 * `label`, `placeholder`, `searchPlaceholder`, `emptyText`, `removeLabel`.
 */
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    options: { type: Array, default: () => [] },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    searchable: { type: Boolean, default: true },
    searchPlaceholder: { type: String, default: '' },
    emptyText: { type: String, default: '—' },
    removeLabel: { type: String, default: 'Remove' },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    error: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'change'])

const open = ref(false)
const query = ref('')
const root = ref(null)
const searchEl = ref(null)

const normalized = computed(() =>
    props.options.map((o) => (typeof o === 'object' && o !== null ? { value: o.value, label: o.label ?? o.value } : { value: o, label: o }))
)
const selected = computed(() => normalized.value.filter((o) => props.modelValue.includes(o.value)))
const filtered = computed(() => {
    const q = query.value.trim().toLowerCase()
    if (!q) return normalized.value
    return normalized.value.filter((o) => String(o.label).toLowerCase().includes(q))
})

function isSelected(v) {
    return props.modelValue.includes(v)
}
function emitValue(arr) {
    emit('update:modelValue', arr)
    emit('change', arr)
}
function toggleValue(v) {
    if (isSelected(v)) emitValue(props.modelValue.filter((x) => x !== v))
    else emitValue([...props.modelValue, v])
}
function remove(v) {
    emitValue(props.modelValue.filter((x) => x !== v))
}

function toggle() {
    if (props.disabled) return
    open.value ? close() : show()
}
async function show() {
    open.value = true
    query.value = ''
    if (props.searchable) {
        await nextTick()
        searchEl.value?.focus()
    }
}
function close() {
    open.value = false
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
.ay-ms-enter-active,
.ay-ms-leave-active { transition: opacity 0.12s ease; }
.ay-ms-enter-from,
.ay-ms-leave-to { opacity: 0; }
</style>
