<template>
    <header
        class="w-full transition-all duration-300 ease-in-out"
        :class="{
            'fixed inset-x-0 top-0 z-50 bg-white shadow-md': isSticky,
            'absolute inset-x-0 top-0 z-50': !isSticky,
        }"
    >
        <nav class="flex items-center bg-white justify-between px-6 py-2 lg:px-8" aria-label="Global">
            <!-- MOBILE NAV -->
            <div class="w-full flex lg:hidden">
                <div class="w-full flex items-center justify-between lg:hidden">
                    <RouterLink to="/">
                        <span>
                            <ABVAyhootLogo />
                        </span>
                    </RouterLink>
                    <div class="flex items-center gap-2">
                        <template v-if="!isLoggedIn">
                            <ResponsiveButton class="p-2" type="dark-green" to="/signup">
                                {{ $t('nav.signUp') }}
                            </ResponsiveButton>
                            <ResponsiveButton class="p-2" type="dark-blue" to="/login">
                                <DashLogin />
                            </ResponsiveButton>
                        </template>
                        <template v-else>
                            <RouterLink
                                :to="dashboardLink"
                                class="relative flex items-center justify-center rounded-md cursor-pointer font-semibold text-sm p-2 text-white bg-[#152B65] hover:bg-[#0F1D42] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px] transition-all duration-200"
                            >
                                <svg
                                    width="20"
                                    height="20"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M3.23779 19.5C4.5632 17.2892 7.46807 15.7762 12.0001 15.7762C16.5321 15.7762 19.4369 17.2892 20.7623 19.5M15.6001 8.1C15.6001 10.0882 13.9883 11.7 12.0001 11.7C10.0118 11.7 8.40007 10.0882 8.40007 8.1C8.40007 6.11177 10.0118 4.5 12.0001 4.5C13.9883 4.5 15.6001 6.11177 15.6001 8.1Z"
                                        stroke="white"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </RouterLink>
                            <ResponsiveButton type="light-red" @click="logout" class="p-2">
                                <svg
                                    width="20"
                                    height="20"
                                    viewBox="0 0 25 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M12.5 11.5V4M7.20835 6C5.54751 7.46589 4.5 9.61061 4.5 12C4.5 16.4183 8.08172 20 12.5 20C16.9183 20 20.5 16.4183 20.5 12C20.5 9.61061 19.4525 7.46589 17.7916 6"
                                        stroke="white"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </ResponsiveButton>
                        </template>
                        <LanguageSwitcher />
                        <button
                            type="button"
                            class="inline-flex rounded-md p-2.5 text-gray-700"
                            @click="isMenuOpen = true"
                        >
                            <span class="sr-only">{{ $t('nav.openMenu') }}</span>
                            <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path d="M5 7H19" stroke="#33363F" stroke-width="2" stroke-linecap="round" />
                                <path d="M5 12H19" stroke="#33363F" stroke-width="2" stroke-linecap="round" />
                                <path d="M5 17H19" stroke="#33363F" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- DESKTOP NAV -->
            <div class="hidden lg:flex lg:items-center lg:gap-x-4">
                <RouterLink to="/">
                    <span>
                        <AyhootLogo />
                    </span>
                </RouterLink>
                <AyButton type="dark-blue" to="/news">{{ $t('nav.news') }}</AyButton>
                <NavDropdown to="/ayhoot-for-institutes" :menuItems="institutesItems">
                    <template #icon>
                        <Institutes />
                    </template>
                    {{ $t('nav.institutes') }}
                </NavDropdown>
                <NavDropdown to="/ayhoot-for-tutors" :menuItems="schoolsItems">
                    <template #icon>
                        <Schools />
                    </template>
                    {{ $t('nav.tutors') }}
                </NavDropdown>
                <!-- <NavDropdown to="/" :menuItems="schoolsItems">
                    <template #icon>
                        <HigherEducations />
                    </template>
                    {{ $t('nav.tutees') }}
                </NavDropdown> -->
                <NavDropdown to="/discover">
                    <template #icon>
                        <Explore />
                    </template>
                    {{ $t('nav.explore') }}
                </NavDropdown>
            </div>

            <!-- RIGHT SIDE (DESKTOP) -->
            <div class="hidden lg:flex lg:flex-1 lg:justify-end lg:items-center lg:gap-x-3">
                <template v-if="!isLoggedIn">
                    <ResponsiveButton class="p-2" type="dark-green" to="/signup">
                        {{ $t('nav.signUpFree') }}
                    </ResponsiveButton>
                    <NavDropdown to="/login">{{ $t('nav.logIn') }}</NavDropdown>
                </template>
                <template v-else>
                    <span class="text-sm font-semibold text-gray-600">
                        {{ $t('nav.welcome', { name: userName }) }}
                    </span>
                    <ResponsiveButton type="dash-blue" :to="dashboardLink" class="p-2">
                        {{ $t('nav.dashboard') }}
                    </ResponsiveButton>
                    <ResponsiveButton type="light-red" @click="logout" class="p-2">
                        {{ $t('nav.logOut') }}
                    </ResponsiveButton>
                </template>
                <LanguageSwitcher />
            </div>
        </nav>

        <!-- MOBILE MENU -->
        <div v-if="isMenuOpen" class="lg:hidden fixed inset-0 z-50 bg-black bg-opacity-50">
            <div class="fixed overflow-auto inset-y-0 end-0 z-50 w-full sm:max-w-sm bg-[#F4F7FF] px-6 py-2">
                <div class="w-full flex items-center justify-between lg:hidden">
                    <RouterLink to="/">
                        <ABVAyhootLogoLight />
                    </RouterLink>
                    <button
                        type="button"
                        class="inline-flex rounded-md p-2.5 text-gray-700"
                        @click="isMenuOpen = false"
                    >
                        <span class="sr-only">{{ $t('nav.closeMenu') }}</span>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M18 6L6 18"
                                stroke="#4285F4"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M6 6L18 18"
                                stroke="#4285F4"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </button>
                </div>

                <div class="mt-6 flow-root">
                    <div class="-my-6 divide-y divide-gray-500/10">
                        <div class="space-y-2 py-6">
                            <div class="space-y-3">
                                <MobileMenuItems
                                    v-for="(group, index) in menuGroups"
                                    :key="index"
                                    :title="group.title"
                                    :menuItems="group.items"
                                />
                            </div>
                        </div>

                        <div class="py-6">
                            <template v-if="!isLoggedIn">
                                <RouterLink
                                    to="/login"
                                    @click="isMenuOpen = false"
                                    class="-mx-3 block rounded-lg px-3 py-2.5 text-base font-semibold leading-7 text-gray-900 hover:bg-gray-50"
                                >
                                    {{ $t('nav.logIn') }}
                                </RouterLink>
                                <RouterLink
                                    to="/signup"
                                    @click="isMenuOpen = false"
                                    class="-mx-3 block rounded-lg px-3 py-2.5 text-base font-semibold leading-7 text-gray-900 hover:bg-gray-50"
                                >
                                    {{ $t('nav.signUpFree') }}
                                </RouterLink>
                            </template>
                            <template v-else>
                                <RouterLink
                                    :to="dashboardLink"
                                    @click="isMenuOpen = false"
                                    class="-mx-3 block rounded-lg px-3 py-2.5 text-base font-semibold leading-7 text-gray-900 hover:bg-gray-50"
                                >
                                    {{ $t('nav.dashboard') }}
                                </RouterLink>
                                <button
                                    @click="logout"
                                    class="-mx-3 block w-full text-start rounded-lg px-3 py-2.5 text-base font-semibold leading-7 text-gray-900 hover:bg-gray-50"
                                >
                                    {{ $t('nav.logOut') }}
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

// Components
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
import LanguageSwitcher from '../../../components/LanguageSwitcher.vue'
import ResponsiveButton from '../../atoms/button/ResponsiveButton.vue'
import DashLogin from '../../atoms/icons/DashLogin.vue'

const { t } = useI18n()
const router = useRouter()

const isSticky = ref(false)
const isMenuOpen = ref(false)
const isLoggedIn = ref(false)
const dashboardLink = ref('/')
const userName = ref('')

// Check login status
const checkLoginStatus = () => {
    try {
        const userString = localStorage.getItem('user')
        const role = localStorage.getItem('role')
        const token = localStorage.getItem('token')

        // Defense against "undefined" or "null" strings being saved
        if (userString && userString !== 'undefined' && userString !== 'null' && role && token) {
            const user = JSON.parse(userString)
            if (user && typeof user === 'object') {
                isLoggedIn.value = true
                userName.value = user.name || user.username || ''

                switch (role) {
                    case 'teacher':
                        dashboardLink.value = '/teacher/dashboard'
                        break
                    case 'student':
                        dashboardLink.value = '/student/dashboard'
                        break
                    case 'institute':
                        dashboardLink.value = '/institute/dashboard'
                        break
                    default:
                        dashboardLink.value = '/'
                }
            } else {
                throw new Error('Invalid user data structure')
            }
        } else {
            isLoggedIn.value = false
            userName.value = ''
        }
    } catch (error) {
        console.error('[NavBar] Failed to parse user from localStorage:', error)
        isLoggedIn.value = false
        userName.value = ''

        // If data is corrupt, clear it to stop potential loops
        if (error instanceof SyntaxError) {
            localStorage.removeItem('user')
            localStorage.removeItem('token')
            localStorage.removeItem('role')
        }
    }
}

// Logout handler
const logout = () => {
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    localStorage.removeItem('role')
    checkLoginStatus()
    isMenuOpen.value = false
    router.push('/login')
}

// Sticky nav
const handleScroll = () => {
    isSticky.value = window.scrollY > 50
}

// Lifecycle
onMounted(() => {
    checkLoginStatus()
    window.addEventListener('scroll', handleScroll)
    window.addEventListener('storage', checkLoginStatus)
})
onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
    window.removeEventListener('storage', checkLoginStatus)
})

// Computed menu groups
const institutesItems = computed(() => [
    { label: t('nav.menuItems.useCases'), link: '/ayhoot-for-institutes' },
    { label: t('nav.menuItems.solutions'), link: '/ayhoot-for-institutes#solutions' },
    { label: t('nav.menuItems.plansAndPricing'), link: '/ayhoot-for-institutes#pricing' },
])
const schoolsItems = computed(() => [
    { label: t('nav.menuItems.useCases'), link: '/ayhoot-for-tutors' },
    { label: t('nav.menuItems.solutions'), link: '/ayhoot-for-tutors#solutions' },
    { label: t('nav.menuItems.plansAndPricing'), link: '/ayhoot-for-tutors#pricing' },
])

const menuGroups = computed(() => [
    {
        title: t('nav.institutes'),
        items: [
            { label: t('nav.institutes'), link: '/ayhoot-for-institutes' },
            { label: t('nav.menuItems.useCases'), link: '/ayhoot-for-institutes' },
            { label: t('nav.menuItems.solutions'), link: '/ayhoot-for-institutes#solutions' },
            { label: t('nav.menuItems.plansAndPricing'), link: '/ayhoot-for-institutes#pricing' },
        ],
    },
    {
        title: t('nav.tutors'),
        items: [
            { label: t('nav.tutors'), link: '/ayhoot-for-tutors' },
            { label: t('nav.menuItems.useCases'), link: '/ayhoot-for-tutors' },
            { label: t('nav.menuItems.solutions'), link: '/ayhoot-for-tutors#solutions' },
            { label: t('nav.menuItems.plansAndPricing'), link: '/ayhoot-for-tutors#pricing' },
        ],
    },
    // {
    //     title: t('nav.mobileMenu.higherEducation'),
    //     items: [
    //         { label: t('nav.mobileMenu.higherEducation'), link: '/higher-educations' },
    //         { label: t('nav.menuItems.useCases'), link: '/higher-educations/useCases' },
    //         { label: t('nav.menuItems.solutions'), link: '/higher-educations/solutions' },
    //         { label: t('nav.menuItems.plansAndPricing'), link: '/plans' },
    //     ],
    // },
    {
        title: t('nav.mobileMenu.aboutUs'),
        items: [
            { label: t('nav.mobileMenu.aboutUs'), link: '/about-us' },
            { label: t('nav.mobileMenu.contact'), link: '/about-us/contact' },
            { label: t('nav.mobileMenu.team'), link: '/about-us/team' },
        ],
    },
    {
        title: t('nav.explore'),
        items: [{ label: t('nav.explore'), link: '/discover' }],
    },
])
</script>
