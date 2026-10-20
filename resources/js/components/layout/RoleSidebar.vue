<template>
    <component :is="sidebarComponent" v-bind="$attrs" />
</template>

<script>
/**
 * Role-aware panel sidebar. Renders the student or instructor sidebar based on the
 * current role, so shared pages (e.g. discover) don't hardcode InstructorSidebar.
 */
import StudentSidebar from '@/components/layout/StudentSidebar.vue'
import InstructorSidebar from '@/components/layout/InstructorSidebar.vue'
import { getRole } from '@/utils/role'

export default {
    name: 'RoleSidebar',
    inheritAttrs: false,
    computed: {
        sidebarComponent() {
            return getRole() === 'student' ? StudentSidebar : InstructorSidebar
        },
    },
}
</script>
