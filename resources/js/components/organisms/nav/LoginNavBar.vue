<template>
    <header
        class="w-full transition-all duration-300 ease-in-out"
        :class="{
            'fixed inset-x-0 top-0 z-50 bg-white shadow-md': isSticky,
            'absolute inset-x-0 top-0 z-50': !isSticky,
        }"
    >
        <nav
            class="flex items-center bg-white justify-between px-6 py-4 shadow-sm lg:px-8"
            aria-label="Global"
        >
            <!-- mobile header -->
            <div class="w-full flex lg:hidden">
                <div class="w-full flex items-center justify-between lg:hidden">
                    <!-- Mobile menu button -->
                    <RouterLink to="/">
                        <span class="">
                            <AyhootLogo />
                        </span>
                    </RouterLink>
                </div>
            </div>
            <!-- desktop header -->
            <div class="hidden lg:flex lg:items-center lg:gap-x-8">
                <RouterLink to="/">
                    <span class="">
                        <AyhootLogo />
                    </span>
                </RouterLink>
            </div>
        </nav>
    </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const isSticky = ref(false)

const handleScroll = () => {
    if (window.scrollY > 50) {
        isSticky.value = true
    } else {
        isSticky.value = false
    }
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
})

const isMenuOpen = ref(false)

const InstitutesItems = [
    { label: 'Use Cases', link: '/institutes/useCases' },
    { label: 'Solutions', link: '/institutes/solutions' },
    { label: 'Plans and Pricing', link: '/plans' },
]
const SchoolsItems = [
    { label: 'Use Cases', link: '/schools/useCases' },
    { label: 'Solutions', link: '/schools/solutions' },
    { label: 'Plans and Pricing', link: '/plans' },
]
</script>

<script>
import AyButton from '../../atoms/button/AyButton.vue'
import MobileMenuItems from '../../atoms/button/MobileMenuItems.vue'
import HigherEducations from '../../atoms/icons/HigherEducations.vue'
import Institutes from '../../atoms/icons/Institutes.vue'
import Explore from '../../atoms/icons/Explore.vue'
import Schools from '../../atoms/icons/Schools.vue'
import AyhootLogo from '../../atoms/icons/AyhootLogo.vue'
import ABVAyhootLogo from '../../atoms/icons/ABVAyhootLogo.vue'
import ABVAyhootLogoLight from '../../atoms/icons/ABVAyhootLogoLight.vue'
import NavDropdown from '../../molecules/dropdown/NavDropdown.vue'
export default {
    name: 'LoginNavBar',
    components: {
        AyButton,
        NavDropdown,
        MobileMenuItems,
    },
    data() {
        return {
            menuGroups: [
                {
                    title: 'Institutes',
                    items: [
                        { label: 'Institutes', link: '/institutes' },
                        { label: 'Use Cases', link: '/institutes/useCases' },
                        { label: 'Solutions', link: '/institutes/solutions' },
                        { label: 'Plans and Pricing', link: '/plans' },
                    ],
                },
                {
                    title: 'Schools',
                    items: [
                        { label: 'Schools', link: '/schools' },
                        { label: 'Use Cases', link: '/schools/useCases' },
                        { label: 'Solutions', link: '/schools/solutions' },
                        { label: 'Plans and Pricing', link: '/plans' },
                    ],
                },
                {
                    title: 'Higher Education',
                    items: [
                        {
                            label: 'Higher Educations',
                            link: '/higher-educations',
                        },
                        {
                            label: 'Use Cases',
                            link: '/higher-educations/useCases',
                        },
                        {
                            label: 'Solutions',
                            link: '/higher-educations/solutions',
                        },
                        { label: 'Plans and Pricing', link: '/plans' },
                    ],
                },
                {
                    title: 'About Us',
                    items: [
                        {
                            label: 'About Us',
                            link: '/abouts-us',
                        },
                        {
                            label: 'Contact',
                            link: '/about-us/contact',
                        },
                        {
                            label: 'Team',
                            link: '/about-us/team',
                        },
                    ],
                },
                {
                    title: 'Explore',
                    items: [{ label: 'Expolre', link: '/explore' }],
                },
            ],
        }
    },
}
</script>
<style scoped></style>
