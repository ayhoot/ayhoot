<template>
    <div class="w-full">
        <label v-if="label" :for="inputId" class="block mb-1 text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="relative">
            <!-- Optional leading icon (e.g. a search glyph). -->
            <span v-if="$slots.icon" class="absolute inset-y-0 flex items-center pointer-events-none text-gray-500"
                :class="$i18n.locale === 'fa' ? 'start-0 ps-3' : 'left-0 pl-3'">
                <slot name="icon" />
            </span>
            <input :id="inputId" :type="type" :value="modelValue" :placeholder="placeholder" :disabled="disabled"
                :required="required" :class="[
                    'w-full py-2 min-w-60 font-bold bg-gray-50 border-2 rounded-md focus:outline-none sm:text-sm transition-colors',
                    $slots.icon ? ($i18n.locale === 'fa' ? 'ps-10 pe-3' : 'pl-10 pr-3') : 'px-3',
                    error ? 'border-red-400 focus:border-red-500' : 'border-gray-300 focus:border-blue-500',
                    disabled && 'bg-gray-100 cursor-not-allowed opacity-70',
                ]" @input="$emit('update:modelValue', $event.target.value)" @blur="$emit('blur', $event)" />
        </div>
        <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
/**
 * Shared text input atom. Use with v-model:
 *
 *   <AyInput v-model="form.name" :label="$t('...')" required :error="errors.name" />
 */
import { computed, getCurrentInstance } from 'vue'

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: '' },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    error: { type: String, default: '' },
    id: { type: String, default: '' },
})

defineEmits(['update:modelValue', 'blur'])

const uid = getCurrentInstance()?.uid ?? 0
const inputId = computed(() => props.id || `ay-input-${uid}`)
</script>
