<template>
    <div class="bg-gray-50" :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'">
        <div class="fixed top-0 inset-x-0 z-50">
            <component :is="headerComponent" @toggle-sidebar="toggleSidebar" :teacherName="userName"
                :teacherUsername="userUsername" />
        </div>
        <div class="flex">
            <component :is="sidebarComponent" :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />

            <!-- Default secondary sidebar by role; override via the #secondary slot
                 (receives { isOpen, close }) for pages with a custom one. -->
            <slot v-if="computedShowSecondarySidebar" name="secondary" :isOpen="isSidebarOpen" :close="closeSidebar">
                <component v-if="secondarySidebarComponent" :is="secondarySidebarComponent" :isOpen="isSidebarOpen"
                    @close="isSidebarOpen = false" />
            </slot>

            <main class="flex-1 bg-gray-50 overflow-y-auto mt-16 p-4 px-6 min-h-[calc(100vh-4rem)]"
                :class="mainClasses">
                <slot></slot>
            </main>
        </div>

        <!-- Page-level modals / overlays that must sit outside <main>. -->
        <slot name="overlays" />
    </div>
</template>

<script>
// Static imports so chrome (Sidebar/Header) is bundled and resolved once at
// app boot — no async waterfall or remount flicker when navigating between
// pages that all use PageWrapper.
import { getRole } from '@/utils/role'
import StudentHeader from '@/components/layout/StudentHeader.vue'
import StudentSidebar from '@/components/layout/StudentSidebar.vue'
import StudentSecondarySidebar from '@/components/layout/StudentSecondarySidebar.vue'
import InstructorHeader from '@/components/layout/InstructorHeader.vue'
import InstructorSidebar from '@/components/layout/InstructorSidebar.vue'
import InstructorSecondarySidebar from '@/components/layout/InstructorSecondarySidebar.vue'

export default {
    name: 'PageWrapper',
    props: {
        showSecondarySidebar: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            isSidebarOpen: false,
            isDesktopScreen: typeof window !== 'undefined' ? window.innerWidth >= 1280 : false
        }
    },
    computed: {
        userRole() {
            return getRole() || 'student'
        },
        userName() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user.name || (this.userRole === 'student' ? 'Student' : 'Instructor')
        },
        userUsername() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user.username || (this.userRole === 'student' ? 'student' : 'instructor')
        },
        headerComponent() {
            return this.userRole === 'student' ? StudentHeader : InstructorHeader
        },
        sidebarComponent() {
            return this.userRole === 'student' ? StudentSidebar : InstructorSidebar
        },
        computedShowSecondarySidebar() {
            return this.showSecondarySidebar && this.isDesktopScreen
        },
        secondarySidebarComponent() {
            if (!this.computedShowSecondarySidebar) return null
            return this.userRole === 'student' ? StudentSecondarySidebar : InstructorSecondarySidebar
        },
        mainClasses() {
            const isRtl = this.$i18n.locale === 'fa'
            const classes = []

            if (this.computedShowSecondarySidebar) {
                classes.push(isRtl ? 'xl:mr-128' : 'xl:ml-128')
            } else {
                classes.push(isRtl ? 'xl:mr-64' : 'xl:ml-64')
            }
            return classes
        }
    },
    mounted() {
        this.handleResize()
        window.addEventListener('resize', this.handleResize)
    },
    beforeUnmount() {
        window.removeEventListener('resize', this.handleResize)
    },
    methods: {
        handleResize() {
            this.isDesktopScreen = window.innerWidth >= 1280
        },
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        closeSidebar() {
            this.isSidebarOpen = false
        }
    }
}
</script>

<style scoped>
.xl\:ml-128 {
    margin-left: 32rem;
}

.xl\:mr-128 {
    margin-right: 32rem;
}
</style>
