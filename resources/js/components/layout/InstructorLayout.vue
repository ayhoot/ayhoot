<template>
    <!-- Thin adapter over the role-aware PageWrapper so the whole app shares ONE
         layout implementation. The only difference for instructor pages is that
         the secondary sidebar defaults ON. All slots (default, #secondary,
         #overlays) are forwarded transparently, so pages need no changes. -->
    <PageWrapper :show-secondary-sidebar="secondary">
        <template v-for="(_, name) in $slots" #[name]="slotProps">
            <slot :name="name" v-bind="slotProps || {}" />
        </template>
    </PageWrapper>
</template>

<script>
import PageWrapper from '@/components/shared/PageWrapper.vue'

export default {
    name: 'InstructorLayout',
    components: { PageWrapper },
    props: {
        // Instructor pages show the secondary sidebar by default.
        secondary: { type: Boolean, default: true },
    },
}
</script>
