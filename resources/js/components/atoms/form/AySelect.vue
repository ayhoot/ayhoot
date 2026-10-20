<template>
    <div class="w-full">
        <label v-if="label" :for="selectId" class="block mb-1 text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <select :id="selectId" :value="modelValue" :disabled="disabled" :required="required" :class="[
            'w-full px-3 py-2 font-bold text-gray-500 border-2 rounded-md focus:outline-none sm:text-sm bg-white transition-colors',
            error ? 'border-red-400 focus:border-red-500' : 'border-gray-300 focus:border-blue-500',
            disabled && 'bg-gray-100 cursor-not-allowed opacity-70',
        ]" @change="$emit('update:modelValue', $event.target.value)">
            <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
            <slot>
                <option v-for="opt in normalizedOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                </option>
            </slot>
        </select>
        <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
/**
 * Shared select atom. Pass options as [{ value, label }] or provide <option> via the default slot.
 *
 *   <AySelect v-model="form.status" :options="statusOptions" :label="$t('...')" />
 */
import { computed, getCurrentInstance } from 'vue'

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    error: { type: String, default: '' },
    id: { type: String, default: '' },
    options: { type: Array, default: () => [] },
})

defineEmits(['update:modelValue'])

const uid = getCurrentInstance()?.uid ?? 0
const selectId = computed(() => props.id || `ay-select-${uid}`)

// Accept ["a","b"] or [{value,label}] transparently.
const normalizedOptions = computed(() =>
    props.options.map((o) =>
        typeof o === 'object' && o !== null ? { value: o.value, label: o.label ?? o.value } : { value: o, label: o }
    )
)
</script>
