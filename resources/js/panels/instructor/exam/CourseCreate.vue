<template>
    <div class="min-h-screen bg-gray-50" :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'">
        <!-- Title-first create flow: create the course, then route to its content
             editor (/{role}/course/:id/create). Cancelling returns to the list. -->
        <CreateContentModal kind="course" :show="true" @close="goBack" />
    </div>
</template>

<script>
import CreateContentModal from '../CreateContentModal.vue'
import { getRole } from '@/utils/role'

export default {
    name: 'CourseCreate',
    components: { CreateContentModal },
    methods: {
        goBack() {
            this.$router.push(`/${getRole()}/exam/courses`)
        },
    },
}
</script>
