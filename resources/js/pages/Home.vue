<template>
    <PublicLayout footer>
        <HeroSection />
        <FeaturesSection />
        <TopCreatorsSection />
        <TuteesPlaySection />
        <TestimonialsSection />
    </PublicLayout>
</template>

<script>
import PublicLayout from '@/components/layout/PublicLayout.vue'
import HeroSection from '../components/home/HeroSection.vue'
import FeaturesSection from '../components/home/FeaturesSection.vue'
import TopCreatorsSection from '../components/home/TopCreatorsSection.vue'
import TuteesPlaySection from '../components/home/TuteesPlaySection.vue'
import TestimonialsSection from '../components/home/TestimonialsSection.vue'

export default {
    name: 'Home',
    components: {
        PublicLayout,
        HeroSection,
        FeaturesSection,
        TopCreatorsSection,
        TuteesPlaySection,
        TestimonialsSection,
    },
}
</script>
