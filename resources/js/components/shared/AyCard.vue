<template>
    <div :class="['bg-white rounded-lg shadow-sm border border-gray-100', paddingClass]">
        <!-- Header -->
        <div
            v-if="$slots.header || title"
            :class="['flex items-center justify-between', bodyPadded ? '' : 'px-5 pt-4', $slots.default ? 'mb-3' : '']"
        >
            <slot name="header">
                <div>
                    <h3 v-if="title" class="text-base font-semibold text-gray-900">{{ title }}</h3>
                    <p v-if="subtitle" class="text-xs text-gray-500 mt-0.5">{{ subtitle }}</p>
                </div>
            </slot>
            <slot name="actions" />
        </div>

        <!-- Body -->
        <div :class="bodyPadded ? '' : 'px-5 pb-4'">
            <slot />
        </div>

        <!-- Footer -->
        <div v-if="$slots.footer" class="px-5 py-3 border-t border-gray-100 flex items-center justify-end gap-2">
            <slot name="footer" />
        </div>
    </div>
</template>

<script setup>
/**
 * Generic content card. Translation-ready: pass already-translated `title`/`subtitle`
 * (e.g. :title="$t('...')") or use the #header / #actions / #footer slots.
 *
 *   <AyCard :title="$t('students.title')">
 *     ...content...
 *     <template #actions><AyButton ...>+</AyButton></template>
 *   </AyCard>
 */
import { computed } from 'vue'

const props = defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    // 'none' | 'sm' | 'md' | 'lg' — controls inner padding of the whole card body region
    padding: { type: String, default: 'md' },
})

const PADDINGS = { none: '', sm: 'p-3', md: 'p-5', lg: 'p-6' }
// When padding is applied on the outer card, header/body don't add their own.
const bodyPadded = computed(() => props.padding !== 'none')
const paddingClass = computed(() => PADDINGS[props.padding] ?? PADDINGS.md)
</script>
