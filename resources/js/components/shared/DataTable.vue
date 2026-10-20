<template>
    <div :class="wrapperClass">
        <table :class="tableClass">
            <thead :class="theadClass">
                <tr>
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        scope="col"
                        :class="thClass(col)"
                    >
                        <slot :name="`header:${col.key}`" :column="col">{{ col.label }}</slot>
                    </th>
                </tr>
            </thead>
            <tbody :class="tbodyClass">
                <!-- Loading state -->
                <tr v-if="loading">
                    <td :colspan="columns.length" class="px-6 py-8 text-center text-sm text-gray-500">
                        <slot name="loading">{{ loadingText }}</slot>
                    </td>
                </tr>

                <!-- Empty state -->
                <tr v-else-if="!rows || rows.length === 0">
                    <td :colspan="columns.length" class="px-6 py-8 text-center text-sm text-gray-500">
                        <slot name="empty">{{ emptyText }}</slot>
                    </td>
                </tr>

                <!-- Rows -->
                <tr
                    v-else
                    v-for="(row, index) in rows"
                    :key="resolveRowKey(row, index)"
                    :class="rowClass"
                    @click="$emit('row-click', row)"
                >
                    <td v-for="col in columns" :key="col.key" :class="tdClass(col)">
                        <slot :name="`cell:${col.key}`" :row="row" :value="getValue(row, col.key)" :index="index">
                            {{ getValue(row, col.key) }}
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
/**
 * Generic, slot-based data table that preserves the project's existing table UI.
 *
 * Renders the shared wrapper / header / row scaffolding (including the RTL-aware
 * text alignment that was previously copy-pasted into every table) and exposes
 * per-column slots so each page keeps its exact cell markup:
 *
 *   <DataTable :columns="columns" :rows="rows" :loading="loading" :empty-text="t('...')">
 *     <template #cell:status="{ row }"> ...custom badge... </template>
 *     <template #cell:actions="{ row }"> ...buttons... </template>
 *   </DataTable>
 *
 * Column shape: { key, label, align?: 'auto'|'left'|'right'|'center', thClass?, tdClass? }
 *   - align defaults to 'auto' (right in fa, left otherwise) to match prior behaviour.
 *
 * The base/scaffold classes (table, thead, tbody, th, td) default to the project's
 * most common table style but are overridable as props, so tables that deviate
 * (different padding, header weight, header background, no whitespace-nowrap, ...)
 * can still be rendered pixel-identically.
 */
import { useI18n } from 'vue-i18n'

const props = defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    rowKey: { type: [String, Function], default: 'id' },
    loading: { type: Boolean, default: false },
    emptyText: { type: String, default: '' },
    loadingText: { type: String, default: '' },

    // Scaffold classes (overridable; defaults match the common project style)
    wrapperClass: { type: String, default: 'bg-white rounded-lg shadow-sm overflow-hidden' },
    tableClass: { type: String, default: 'min-w-full divide-y divide-gray-200' },
    theadClass: { type: String, default: 'bg-gray-50' },
    tbodyClass: { type: String, default: 'bg-white divide-y divide-gray-200' },
    rowClass: { type: String, default: 'hover:bg-gray-50 transition' },
    thBaseClass: { type: String, default: 'px-6 py-3 text-xs font-bold uppercase tracking-wider text-gray-500' },
    tdBaseClass: { type: String, default: 'px-6 py-4 whitespace-nowrap text-sm' },
})

defineEmits(['row-click'])

const { locale } = useI18n()

function alignClass(col) {
    const align = col.align || 'auto'
    if (align === 'center') return 'text-center'
    if (align === 'left') return 'text-left'
    if (align === 'right') return 'text-right'
    return locale.value === 'fa' ? 'text-right' : 'text-left'
}

function thClass(col) {
    return [props.thBaseClass, alignClass(col), col.thClass || '']
}

function tdClass(col) {
    return [props.tdBaseClass, alignClass(col), col.tdClass || 'text-gray-700']
}

function resolveRowKey(row, index) {
    if (typeof props.rowKey === 'function') return props.rowKey(row, index)
    return row?.[props.rowKey] ?? index
}

/** Supports dotted keys like 'user.name'. */
function getValue(row, key) {
    if (!key) return ''
    return key.split('.').reduce((acc, part) => (acc == null ? acc : acc[part]), row)
}
</script>
