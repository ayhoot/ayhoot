<template>
    <component :is="sidebarComponent" v-bind="$attrs" />
</template>

<script>
/**
 * Role-aware secondary sidebar. Renders the student or instructor secondary
 * sidebar based on the current role, for shared pages (e.g. discover).
 */
import StudentSecondarySidebar from '@/components/layout/StudentSecondarySidebar.vue'
import InstructorSecondarySidebar from '@/components/layout/InstructorSecondarySidebar.vue'
import { getRole } from '@/utils/role'

export default {
    name: 'RoleSecondarySidebar',
    inheritAttrs: false,
    computed: {
        sidebarComponent() {
            return getRole() === 'student' ? StudentSecondarySidebar : InstructorSecondarySidebar
        },
    },
}
</script>
