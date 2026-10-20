<template>
    <component :is="headerComponent" v-bind="$attrs" />
</template>

<script>
/**
 * Role-aware panel header. Renders the student or instructor (teacher/institute)
 * header based on the current role, so shared pages (e.g. discover) don't hardcode
 * InstructorHeader — which calls /api/instructor/* and 403s for students.
 */
import StudentHeader from '@/components/layout/StudentHeader.vue'
import InstructorHeader from '@/components/layout/InstructorHeader.vue'
import { getRole } from '@/utils/role'

export default {
    name: 'RoleHeader',
    inheritAttrs: false,
    computed: {
        headerComponent() {
            return getRole() === 'student' ? StudentHeader : InstructorHeader
        },
    },
}
</script>
