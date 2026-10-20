<template>
    <Pagination :pagination="pagination" @page-changed="$emit('change-page', $event)" />
</template>

<script>
import Pagination from '@/components/shared/Pagination.vue'

export default {
    name: 'ExamPagination',
    components: { Pagination },
    props: {
        pagination: {
            type: Object,
            required: true
        }
    },
    emits: ['change-page'],
}
</script>
