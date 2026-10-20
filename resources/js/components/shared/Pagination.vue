<template>
    <div v-if="pagination && pagination.last_page > 1" :class="wrapperClass">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                {{ showingLabel }}
            </div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -gap-px" aria-label="Pagination">
                <button
                    @click="change(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                    <span class="sr-only">{{ previousLabel }}</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                    {{ pageLabel }}
                </span>
                <button
                    @click="change(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                    <span class="sr-only">{{ nextLabel }}</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            </nav>
        </div>
    </div>
</template>

<script setup>
/**
 * Shared pagination control. Replaces the inline "Showing X-Y of Z / Page A of B"
 * blocks copy-pasted across list pages (and the older ad-hoc Pagination variants).
 *
 *   <Pagination :pagination="pagination" @page-changed="changePage" />
 *
 * Expects a Laravel-paginator-shaped object: { current_page, last_page, from, to, total }.
 * Renders nothing when there is a single page. Labels are overridable for i18n.
 */
import { computed } from 'vue'

const props = defineProps({
    pagination: { type: Object, required: true },
    wrapperClass: { type: String, default: 'px-6 py-4 border-t border-gray-200 bg-white rounded-b-md' },
    // Optional label overrides (defaults preserve the existing English copy).
    showing: { type: String, default: '' },
    previousLabel: { type: String, default: 'Previous' },
    nextLabel: { type: String, default: 'Next' },
})

const emit = defineEmits(['page-changed'])

const showingLabel = computed(() => {
    if (props.showing) return props.showing
    const p = props.pagination
    return `Showing ${p.from}-${p.to} of ${p.total} results`
})

const pageLabel = computed(() => `Page ${props.pagination.current_page} of ${props.pagination.last_page}`)

function change(page) {
    if (page >= 1 && page <= props.pagination.last_page && page !== props.pagination.current_page) {
        emit('page-changed', page)
    }
}
</script>
