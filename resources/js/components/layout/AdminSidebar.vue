<template>
    <!-- For mobile: use fixed positioning and slide in/out -->
    <div
        class="fixed inset-0 left-0 bg-white z-50 transform transition-transform duration-300 sm:hidden"
        :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        <div class="flex flex-col justify-between w-full h-screen border-r border-gray-300 overflow-y-auto">
            <!-- Close Button -->
            <div class="flex justify-between items-center border-b border-gray-300 py-4 px-6">
                <!-- <RouterLink>
                    <DashAyhootLogo />
                </RouterLink> -->
                <UpgradeButton to="/upgrade">Upgrade</UpgradeButton>

                <button @click="$emit('close')" class="text-gray-600 focus:outline-none">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M18 6L6 18"
                            stroke="#0F1D42"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M6 6L18 18"
                            stroke="#0F1D42"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>
            </div>
            <ul class="flex-1 p-2">
                <li v-for="(item, index) in menuItems" :key="index" class="w-full py-1">
                    <router-link
                        :to="item.link"
                        :class="[
                            'group flex items-center gap-3 font-bold px-3 py-2 transform transition-transform duration-300 ease-in-out',
                            $route.path.startsWith(item.link)
                                ? 'bg-[#3450AE] text-white rounded-lg'
                                : 'text-[#3450AE] hover:bg-[#3450AE] hover:text-white hover:rounded-lg',
                        ]"
                        @click="$emit('close')"
                    >
                        {{ item.label }}
                    </router-link>
                </li>
            </ul>
            <ul>
                <li>
                    <router-link
                        to="/admin/ticket"
                        :class="[
                            'group flex items-center gap-3 px-5 py-3 transform transition-transform duration-300 ease-in-out',
                            {
                                'bg-[#3450AE] text-white rounded-md font-bold': $route.path.startsWith('/admin/ticket'),
                                'text-[#3450AE] hover:bg-gray-100 font-medium hover:rounded-md':
                                    !$route.path.startsWith('/admin/ticket'),
                            },
                        ]"
                    >
                        <DashSupport />
                        <p>Support</p>
                    </router-link>
                </li>
            </ul>
            <!-- <footer class="px-6 py-3 mt-auto">
                <button @click="logout" class="py-2 px-4 rounded-md">
                    Logout
                </button>
            </footer> -->
        </div>
    </div>

    <!-- Desktop Sidebar remains as is -->
    <div class="hidden sm:block">
        <div
            class="fixed flex flex-col justify-between pt-4 px-2 left-0 top-14 w-64 h-[calc(100vh-4rem)] bg-white border-r border-gray-300 overflow-y-auto"
        >
            <ul class="">
                <li v-for="(item, index) in menuItems" :key="index" class="py-0.5">
                    <router-link
                        :to="item.link"
                        :class="[
                            'group flex items-center gap-3 px-5 py-3 transform transition-transform duration-300 ease-in-out',
                            $route.path.startsWith(item.link)
                                ? 'bg-[#3450AE] text-white rounded-md font-bold'
                                : 'text-[#3450AE] hover:bg-gray-100 font-medium hover:rounded-md',
                        ]"
                    >
                        <component :is="item.icon" class="w-5 h-5 group-hover:stroke-[#FFFFFF]" />
                        <p>
                            {{ item.label }}
                        </p>
                    </router-link>
                </li>
            </ul>
            <ul>
                <li>
                    <router-link
                        to="/admin/ticket"
                        :class="[
                            'group flex items-center gap-3 px-5 py-3 transform transition-transform duration-300 ease-in-out',
                            {
                                'bg-[#3450AE] text-white rounded-md font-bold': $route.path.startsWith('/admin/ticket'),
                                'text-[#3450AE] hover:bg-gray-100 font-medium hover:rounded-md':
                                    !$route.path.startsWith('/admin/ticket'),
                            },
                        ]"
                    >
                        <DashSupport />
                        <p>Support</p>
                    </router-link>
                </li>
            </ul>
            
        </div>
    </div>
</template>

<script>
import UpgradeButton from '@/components/atoms/button/UpgradeButton.vue'
import Diamond from '@/components/atoms/icons/Diamond.vue'
import DashAyhootLogo from '@/components/atoms/icons/DashAyhootLogo.vue'
import DashAyhoots from '@/components/atoms/icons/DashAyhoots.vue'
import DashCourses from '@/components/atoms/icons/DashCourses.vue'
import DashHome from '@/components/atoms/icons/DashHome.vue'
import DashMembers from '@/components/atoms/icons/DashMembers.vue'
import DashInstitutes from '@/components/atoms/icons/DashInstitutes.vue'
import DashTeacher from '@/components/atoms/icons/DashTeacher.vue'
import DashPlans from '@/components/atoms/icons/DashPlans.vue'
import DashSettings from '@/components/atoms/icons/DashSettings.vue'
import DashLogs from '@/components/atoms/icons/DashLogs.vue'
import DashNews from '@/components/atoms/icons/DashNews.vue'
import DashSupport from '@/components/atoms/icons/DashSupport.vue'
import DashTransaction from '@/components/atoms/icons/DashTransaction.vue'
import DashDuplicate from '@/components/atoms/icons/DashDuplicate.vue'

export default {
    name: 'Sidebar',
    components: {
        Diamond,
        UpgradeButton,
        DashAyhootLogo,
        DashAyhoots,
        DashHome,
        DashMembers,
        DashInstitutes,
        DashTeacher,
        DashPlans,
        DashSettings,
        DashLogs,
        DashSupport,
        DashCourses,
        DashTransaction,
        DashNews,
        DashDuplicate,
    },
    data() {
        return {
            menuItems: [
                {
                    label: 'Dashboard',
                    link: '/admin/dashboard',
                    icon: DashHome,
                },
                {
                    label: 'Ayhoots',
                    link: '/admin/exam',
                    icon: DashAyhoots,
                },
                {
                    label: 'Courses',
                    link: '/admin/course',
                    icon: DashCourses,
                },
                {
                    label: 'Tutees',
                    link: '/admin/student',
                    icon: DashMembers,
                },
                {
                    label: 'Institutes',
                    link: '/admin/institute',
                    icon: DashInstitutes,
                },
                {
                    label: 'Tutors',
                    link: '/admin/teacher',
                    icon: DashTeacher,
                },
                { label: 'Plans', link: '/admin/plan', icon: DashPlans },
                {
                    label: 'Transactions',
                    link: '/admin/transaction',
                    icon: DashTransaction,
                },
                {
                    label: 'Hero Slides',
                    link: '/admin/hero-slides',
                    icon: DashDuplicate,
                },
                {
                    label: 'Banners',
                    link: '/admin/banner',
                    icon: DashNews,
                },
                {
                    label: 'Settings',
                    link: '/admin/setting',
                    icon: DashSettings,
                },
                { label: 'Activities', link: '/admin/activity', icon: DashLogs },
                { label: 'News', link: '/admin/news', icon: DashNews },
            ],
        }
    },
    props: {
        // menuItems: {
        //     type: Array,
        //     default: () => [],
        // },
        isOpen: {
            type: Boolean,
            default: false,
        },
    },

    methods: {
        logout() {
            localStorage.removeItem('token')
            this.$router.push({ name: 'AdminLogin' })
        },
    },
}
</script>

<style scoped></style>
