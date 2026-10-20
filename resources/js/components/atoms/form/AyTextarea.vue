<template>
    <div class="w-full">
        <label v-if="label" :for="textareaId" class="block mb-1 text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <textarea :id="textareaId" :value="modelValue" :placeholder="placeholder" :rows="rows" :disabled="disabled"
            :required="required" :class="[
                'w-full px-3 py-2 border-2 font-bold rounded-md focus:outline-none sm:text-sm transition-colors resize-y',
                error ? 'border-red-400 focus:border-red-500' : 'border-gray-300 focus:border-blue-500',
                disabled && 'bg-gray-100 cursor-not-allowed opacity-70',
            ]" @input="$emit('update:modelValue', $event.target.value)" @blur="$emit('blur', $event)"></textarea>
        <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
/**
 * Shared multi-line text input atom — the <textarea> sibling of AyInput.
 *
 *   <AyTextarea v-model="form.description" :label="$t('...')" :rows="4" />
 */
import { computed, getCurrentInstance } from 'vue'

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    rows: { type: [String, Number], default: 3 },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    error: { type: String, default: '' },
    id: { type: String, default: '' },
})

defineEmits(['update:modelValue', 'blur'])

const uid = getCurrentInstance()?.uid ?? 0
const textareaId = computed(() => props.id || `ay-textarea-${uid}`)
</script>
