<template>
    <div ref="root" class="w-full relative">
        <label v-if="label" class="block mb-1 text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Control -->
        <button
            type="button"
            :disabled="disabled"
            :class="[
                'w-full flex items-center justify-between gap-2 px-3 py-2 border-2 rounded-md text-sm text-start bg-white transition-colors',
                error ? 'border-red-400' : open ? 'border-[#3450AE]' : 'border-gray-300',
                disabled && 'bg-gray-100 cursor-not-allowed opacity-70',
            ]"
            @click="toggle"
        >
            <span :class="selectedLabel ? 'text-gray-900 truncate' : 'text-gray-400 truncate'">
                {{ selectedLabel || placeholder }}
            </span>
            <svg class="h-4 w-4 shrink-0 text-gray-500 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>

        <!-- Panel -->
        <transition name="ay-ss">
            <div v-if="open" class="absolute z-40 mt-1 w-[var(--ay-ss-w)] max-w-full rounded-md bg-white shadow-lg border border-gray-100" :style="panelStyle">
                <div v-if="showSearch" class="p-2 border-b border-gray-100">
                    <input
                        ref="searchEl"
                        v-model="query"
                        type="text"
                        :placeholder="searchPlaceholder"
                        class="w-full px-2 py-1.5 text-sm border-2 border-gray-200 rounded focus:outline-none focus:border-[#3450AE]"
                    />
                </div>
                <ul class="max-h-56 overflow-y-auto py-1">
                    <li
                        v-for="opt in filtered"
                        :key="opt.value"
                        class="px-3 py-2 text-sm cursor-pointer flex items-center justify-between hover:bg-gray-50"
                        :class="opt.value === modelValue ? 'bg-blue-50 text-[#3450AE] font-medium' : 'text-gray-700'"
                        @click="pick(opt)"
                    >
                        <span class="truncate">{{ opt.label }}</span>
                        <svg v-if="opt.value === modelValue" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.3 3.29 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd" /></svg>
                    </li>
                    <li v-if="!filtered.length" class="px-3 py-3 text-sm text-gray-400 text-center">
                        {{ emptyText }}
                    </li>
                </ul>
            </div>
        </transition>

        <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
/**
 * Single-select with a search filter. v-model holds the selected value.
 * Options: [{ value, label }] or string[]. Translation-ready: pass already-translated
 * `label`, `placeholder`, `searchPlaceholder`, `emptyText`.
 */
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    modelValue: { type: [String, Number, null], default: null },
    options: { type: Array, default: () => [] },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    searchPlaceholder: { type: String, default: '' },
    emptyText: { type: String, default: '—' },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    error: { type: String, default: '' },
    // When false, the dropdown is a plain custom option list (no search box).
    // Useful for short filter lists (e.g. Content Type / Price / Sort By).
    searchable: { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue', 'change'])

const showSearch = computed(() => props.searchable)

const open = ref(false)
const query = ref('')
const root = ref(null)
const searchEl = ref(null)
const panelStyle = ref({})

const normalized = computed(() =>
    props.options.map((o) => (typeof o === 'object' && o !== null ? { value: o.value, label: o.label ?? o.value } : { value: o, label: o }))
)
const selectedLabel = computed(() => normalized.value.find((o) => o.value === props.modelValue)?.label ?? '')
const filtered = computed(() => {
    const q = query.value.trim().toLowerCase()
    if (!q) return normalized.value
    return normalized.value.filter((o) => String(o.label).toLowerCase().includes(q))
})

function toggle() {
    if (props.disabled) return
    open.value ? close() : show()
}
async function show() {
    open.value = true
    query.value = ''
    panelStyle.value = { '--ay-ss-w': root.value ? `${root.value.offsetWidth}px` : '16rem' }
    await nextTick()
    if (showSearch.value) searchEl.value?.focus()
}
function close() {
    open.value = false
}
function pick(opt) {
    emit('update:modelValue', opt.value)
    emit('change', opt.value)
    close()
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
.ay-ss-enter-active,
.ay-ss-leave-active { transition: opacity 0.12s ease; }
.ay-ss-enter-from,
.ay-ss-leave-to { opacity: 0; }
</style>
