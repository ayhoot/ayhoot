<template>
    <Pagination
        :pagination="pagination"
        @page-changed="$emit('change-page', $event)"
        :wrapper-class="'px-6 py-4 border-t border-gray-100'"
    />
</template>

<script>
import Pagination from '@/components/shared/Pagination.vue'

export default {
    name: 'StudentExamPagination',
    components: {
        Pagination,
    },
    emits: ['change-page'],
    props: {
        pagination: {
            type: Object,
            required: true
        }
    }
}
</script>
