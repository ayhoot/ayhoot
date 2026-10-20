<template>
    <span :class="[baseClass, colorClass]">
        <slot>{{ label }}</slot>
    </span>
</template>

<script setup>
/**
 * Unified status pill. Replaces the per-file `statusBadgeClass()` colour maps.
 *
 *   <StatusBadge :status="row.status">{{ $t(row.status) }}</StatusBadge>
 *
 * Colour is derived from `status` via a shared map. Pass `variant` to force a
 * colour for one-off cases. The label goes in the default slot (so each caller
 * keeps its own translation, e.g. $t(status) / $t('ticket.'+status)).
 */
const props = defineProps({
    status: { type: String, default: '' },
    // Force a colour: 'green' | 'yellow' | 'red' | 'blue' | 'gray'
    variant: { type: String, default: '' },
    label: { type: String, default: '' },
    baseClass: { type: String, default: 'px-2 py-1 text-xs font-medium rounded-full' },
})

// status (lowercased) -> colour
const STATUS_COLORS = {
    // positive / active
    published: 'green', active: 'green', approved: 'green', accepted: 'green',
    answered: 'green', success: 'green', successful: 'green', paid: 'green',
    verified: 'green', confirmed: 'green', enabled: 'green', completed_success: 'green',
    // pending / in progress
    draft: 'yellow', pending: 'yellow', in_progress: 'yellow', processing: 'yellow',
    review: 'yellow', waiting: 'yellow',
    // open / informational
    open: 'blue', new: 'blue', info: 'blue',
    // neutral / done
    closed: 'gray', completed: 'gray', finished: 'gray', inactive: 'gray',
    disabled: 'gray', expired: 'gray',
    // negative
    archived: 'red', rejected: 'red', cancelled: 'red', canceled: 'red',
    failed: 'red', banned: 'red', error: 'red',
}

const COLOR_CLASSES = {
    green: 'bg-green-100 text-green-800',
    yellow: 'bg-yellow-100 text-yellow-800',
    red: 'bg-red-100 text-red-800',
    blue: 'bg-blue-100 text-blue-800',
    gray: 'bg-gray-100 text-gray-800',
}

import { computed } from 'vue'

const colorClass = computed(() => {
    const variant = props.variant || STATUS_COLORS[(props.status || '').toLowerCase()] || 'gray'
    return COLOR_CLASSES[variant] || COLOR_CLASSES.gray
})
</script>
