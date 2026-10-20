<template>
    <div class="min-h-screen bg-gray-50" :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'">
        <!-- The create flow is a single title prompt; on success it routes to the
             question editor (/{role}/exam/:id/create). Cancelling returns to the list. -->
        <CreateContentModal kind="exam" :show="true" @close="goBack" />
    </div>
</template>

<script>
import CreateContentModal from '../CreateContentModal.vue'
import { getRole } from '@/utils/role'

export default {
    name: 'ExamCreate',
    components: { CreateContentModal },
    methods: {
        goBack() {
            this.$router.push(`/${getRole()}/exam`)
        },
    },
}
</script>
