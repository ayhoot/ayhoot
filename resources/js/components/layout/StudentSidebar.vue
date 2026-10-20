<template>
    <div>
        <div
            class="fixed inset-0 bg-white z-50 transform transition-transform duration-300 xl:hidden"
            :class="isOpen ? 'translate-x-0' : 'ltr:-translate-x-full rtl:translate-x-full'"
        >
            <div class="flex flex-col justify-between w-full h-screen border-e border-gray-300 overflow-y-auto">
                <div>
                    <div class="flex justify-between items-center border-b border-gray-300 py-4 px-6">
                        <UpgradeButton to="/student/plans">{{ $t('dashboard.sidebar.upgrade') }}</UpgradeButton>
                        <button @click="$emit('close')" class="text-gray-600 focus:outline-none cursor-pointer">
                            <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
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
                    <div class="flex-shrink-0 px-4" v-if="institutes.length > 0">
                        <h3 class="text-xs font-semibold py-2 px-2 text-gray-400">
                            {{ $t('dashboard.sidebar.workspace') }}
                        </h3>
                        <button
                            @click="toggleInstituteSelector"
                            class="w-full p-3 rounded-md border-2 border-gray-400 transition-all cursor-pointer"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-400 font-bold">{{ currentInstitute }}</span>
                                <svg
                                    class="w-5 h-5 text-gray-400 transition-transform duration-300"
                                    :class="{ 'rotate-180': showInstituteSelector }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </div>
                        </button>
                        <transition name="slide-fade">
                            <div v-if="showInstituteSelector" class="mt-1 rounded-md overflow-hidden">
                                <div class="max-h-48 overflow-y-auto py-1">
                                    <button
                                        v-for="institute in institutes"
                                        :key="institute.id"
                                        @click="selectInstitute(institute.id)"
                                        class="w-full flex items-center rounded-md px-4 py-2 my-0.5 text-start text-sm hover:bg-[#3450AE] hover:text-white transition-colors cursor-pointer"
                                        :class="{
                                            'bg-[#3450AE] text-white font-bold':
                                                parseInt(selectedInstitute) === institute.id,
                                            'bg-gray-100': parseInt(selectedInstitute) !== institute.id,
                                        }"
                                    >
                                        <DashInstitutes class="me-2" />
                                        {{ institute.name }}
                                    </button>
                                    <button
                                        @click="selectInstitute(null)"
                                        class="w-full flex items-center rounded-md px-4 py-2 my-0.5 text-sm text-start hover:font-bold hover:bg-[#DCE9FF] hover:text-[#4C8BF5] transition-colors cursor-pointer"
                                        :class="{
                                            'bg-[#DCE9FF] text-[#4C8BF5] font-bold': selectedInstitute === null,
                                            'bg-gray-100 ': selectedInstitute !== null,
                                        }"
                                    >
                                        <DashFreelancer class="me-2" />
                                        {{ $t('dashboard.sidebar.freelance') }}
                                    </button>
                                </div>
                            </div>
                        </transition>
                    </div>
                    <h3 class="text-xs font-semibold py-2 px-6 text-gray-400">{{ $t('dashboard.sidebar.tools') }}</h3>
                    <ul class="flex-1 px-4">
                        <li v-for="(item, index) in computedMenuItems" :key="index" class="w-full py-1">
                            <div v-if="item.children">
                                <button
                                    @click="toggleDropdown(item.key)"
                                    :class="[
                                        'group w-full flex items-center justify-between gap-3 px-3 py-3',
                                        isLinkActive(item)
                                            ? 'bg-[#3450AE] text-white font-bold rounded-lg'
                                            : 'text-[#3450AE] font-medium hover:bg-gray-100 hover:rounded-lg',
                                    ]"
                                >
                                    <div class="flex items-center gap-3">
                                        <component :is="item.icon" class="w-5 h-5" />
                                        <span>{{ item.label }}</span>
                                    </div>
                                    <svg
                                        class="w-4 h-4 transition-transform duration-200"
                                        :class="{ 'rotate-180': openDropdowns[item.key] }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 9l-7 7-7-7"
                                        ></path>
                                    </svg>
                                </button>
                                <transition name="slide-fade">
                                    <ul v-if="openDropdowns[item.key]" class="ps-4 mt-1 space-y-1">
                                        <li v-for="child in item.children" :key="child.link || child.key">
                                            <div v-if="child.children">
                                                <button
                                                    @click="toggleDropdown(child.key)"
                                                    class="group w-full flex items-center justify-between text-[#3450AE] font-medium px-3 py-2 hover:bg-gray-100 hover:rounded-lg"
                                                >
                                                    <div class="flex items-center gap-3">
                                                        <component :is="child.icon" class="w-5 h-5" />
                                                        <span>{{ child.label }}</span>
                                                    </div>
                                                    <svg
                                                        class="w-4 h-4 transition-transform duration-200"
                                                        :class="{ 'rotate-180': openDropdowns[child.key] }"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 9l-7 7-7-7"
                                                        ></path>
                                                    </svg>
                                                </button>
                                                <transition name="slide-fade">
                                                    <ul v-if="openDropdowns[child.key]" class="ps-4 mt-1 space-y-1">
                                                        <li
                                                            v-if="isLoadingFolders || isLoadingGroups"
                                                            class="text-center text-xs text-gray-500 py-2"
                                                        >
                                                            {{ $t('dashboard.sidebar.loading') }}
                                                        </li>
                                                        <li
                                                            v-else-if="child.children.length === 0"
                                                            class="text-center text-xs text-gray-500 py-2"
                                                        >
                                                            {{ $t('dashboard.sidebar.noItemsFound') }}
                                                        </li>
                                                        <li v-for="subChild in child.children" :key="subChild.link">
                                                            <router-link
                                                                :to="subChild.link"
                                                                @click="$emit('close')"
                                                                :class="[
                                                                    $route.path === subChild.link
                                                                        ? 'bg-gray-200 text-[#3450AE] font-semibold'
                                                                        : 'text-gray-600 hover:bg-gray-100',
                                                                    'flex items-center gap-3 w-full px-3 py-2 rounded-md transition-colors',
                                                                ]"
                                                            >
                                                                <component :is="subChild.icon" class="w-5 h-5" />
                                                                <span>{{ subChild.label }}</span>
                                                            </router-link>
                                                        </li>
                                                    </ul>
                                                </transition>
                                            </div>
                                            <router-link
                                                v-else
                                                :to="child.link"
                                                @click="$emit('close')"
                                                :class="[
                                                    $route.path === child.link
                                                        ? 'bg-gray-200 text-[#3450AE] font-semibold'
                                                        : 'text-gray-600 hover:bg-gray-100',
                                                    'flex items-center gap-3 w-full px-3 py-2 rounded-md transition-colors',
                                                ]"
                                            >
                                                <component :is="child.icon" class="w-5 h-5" />
                                                <span>{{ child.label }}</span>
                                            </router-link>
                                        </li>
                                    </ul>
                                </transition>
                            </div>
                            <router-link
                                v-else
                                :to="item.link"
                                @click="$emit('close')"
                                :class="[
                                    isLinkActive(item)
                                        ? 'bg-[#3450AE] text-white font-bold rounded-lg'
                                        : 'text-[#3450AE] font-medium hover:bg-gray-100 hover:rounded-lg',
                                    'group flex items-center gap-3 px-3 py-3',
                                ]"
                            >
                                <component :is="item.icon" class="w-5 h-5" />
                                <span>{{ item.label }}</span>
                            </router-link>
                        </li>
                    </ul>
                </div>
                <ul>
                    <li class="p-4">
                        <router-link
                            to="/student/ticket"
                            :class="[
                                'group flex items-center gap-3 px-5 py-3',
                                isLinkActive({ link: '/student/ticket' })
                                    ? 'bg-[#3450AE] text-white rounded-md font-bold'
                                    : 'text-[#3450AE] hover:bg-gray-100 font-medium hover:rounded-md',
                            ]"
                        >
                            <DashSupport />
                            <p>{{ $t('dashboard.sidebar.support') }}</p>
                        </router-link>
                    </li>
                </ul>
            </div>
        </div>

        <div class="hidden xl:block">
            <div
                class="py-2 fixed flex flex-col justify-between pt-1 px-2 start-0 top-14 w-64 h-[calc(100vh-3.6rem)] bg-white border-e border-gray-300 overflow-y-auto"
            >
                <div>
                    <div class="flex-shrink-0" v-if="institutes.length > 0">
                        <div class="flex items-center py-2 px-2">
                            <h3 class="text-xs font-semibold text-gray-400">{{ $t('dashboard.sidebar.workspace') }}</h3>
                            <ResponsiveButton
                                to="/student/my-profile"
                                class="text-xs px-2 text-[#4C8BF5] cursor-pointer hover:text-[#3450AE] font-semibold"
                                >{{ $t('dashboard.sidebar.add') }}</ResponsiveButton
                            >
                        </div>
                        <div class="px-2 flex items-center justify-between">
                            <div class="ps-[10px] relative flex items-center justify-start">
                                <button
                                    @click="selectInstitute(null)"
                                    :title="$t('dashboard.sidebar.freelance')"
                                    class="p-2.5 ms-[-10px] border-2 border-white flex items-center justify-center rounded-full text-sm hover:bg-[#DCE9FF] hover:text-[#4C8BF5] transition-colors cursor-pointer"
                                    :class="{
                                        'bg-[#DCE9FF] text-[#4C8BF5] font-bold outline-2 outline-[#4C8BF5]':
                                            selectedInstitute === null,
                                        'bg-gray-200 ': selectedInstitute !== null,
                                    }"
                                >
                                    <DashFreelancer class="" />
                                    <p class="sr-only">{{ $t('dashboard.sidebar.freelance') }}</p>
                                </button>
                                <button
                                    v-for="(institute, index) in institutes"
                                    :key="institute.id"
                                    :title="institute.name"
                                    @click="selectInstitute(institute.id)"
                                    class="relative flex items-center justify-center ms-[-10px] rounded-full border-2 border-white text-sm transition-colors cursor-pointer"
                                    :class="{
                                        'bg-[#3450AE] text-white font-bold outline-2 outline-[#3450AE]':
                                            parseInt(selectedInstitute) === institute.id,
                                        'bg-gray-200 hover:bg-[#DCE9FF] hover:text-[#4C8BF5]':
                                            parseInt(selectedInstitute) !== institute.id,
                                        'ms-[-5px]': index > 0,
                                    }"
                                >
                                    <span
                                        class="text-xs font-bold w-10 h-10 flex items-center justify-center rounded-full"
                                        >{{ getInitials(institute.name) }}</span
                                    >
                                    <p class="sr-only">{{ institute.name }}</p>
                                </button>
                            </div>
                        </div>
                    </div>
                    <h3 class="text-xs font-semibold py-2 px-2 text-gray-400">{{ $t('dashboard.sidebar.tools') }}</h3>
                    <ul class="">
                        <li v-for="(item, index) in computedMenuItems" :key="index" class="py-0.5">
                            <router-link
                                :to="item.link"
                                :class="[
                                    'group flex items-center gap-3 px-5 py-3 transform transition-transform duration-300 ease-in-out',
                                    $route.path.startsWith(item.link)
                                        ? 'bg-[#3450AE] text-white rounded-md font-bold'
                                        : 'text-[#3450AE] hover:bg-gray-100 font-medium hover:rounded-md',
                                ]"
                            >
                                <component :is="item.icon" class="w-5 h-5" />
                                <p>{{ item.label }}</p>
                            </router-link>
                        </li>
                    </ul>
                </div>
                <ul>
                    <li>
                        <router-link
                            to="/student/ticket"
                            :class="[
                                'group flex items-center gap-3 px-5 py-3 transform transition-transform duration-300 ease-in-out',
                                {
                                    'bg-[#3450AE] text-white rounded-md font-bold':
                                        $route.path.startsWith('/student/ticket'),
                                    'text-[#3450AE] hover:bg-gray-100 font-medium hover:rounded-md':
                                        !$route.path.startsWith('/student/ticket'),
                                },
                            ]"
                        >
                            <DashSupport />
                            <p>{{ $t('dashboard.sidebar.support') }}</p>
                        </router-link>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useToast } from 'vue-toast-notification'
import 'vue-toast-notification/dist/theme-sugar.css'
import UpgradeButton from '@/components/atoms/button/UpgradeButton.vue'
import DashLibrary from '@/components/atoms/icons/DashLibrary.vue'
import DashHome from '@/components/atoms/icons/DashHome.vue'
import DashMembers from '@/components/atoms/icons/DashMembers.vue'
import DashInstitutes from '@/components/atoms/icons/DashInstitutes.vue'
import DashSupport from '@/components/atoms/icons/DashSupport.vue'
import DashReport from '@/components/atoms/icons/DashReport.vue'
import DashFreelancer from '@/components/atoms/icons/DashFreelancer.vue'
import DashDiscover from '@/components/atoms/icons/DashDiscover.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashAyhoots from '@/components/atoms/icons/DashAyhoots.vue'
import DashCourses from '@/components/atoms/icons/DashCourses.vue'
import DashPurchased from '@/components/atoms/icons/DashPurchased.vue'
import DashFolder from '@/components/atoms/icons/DashFolder.vue'
import DashArchive from '@/components/atoms/icons/DashArchive.vue'
import DashGroups from '@/components/atoms/icons/DashGroups.vue'
import DashSubscribers from '@/components/atoms/icons/DashSubscribers.vue'
import DashOwnedGroups from '@/components/atoms/icons/DashOwnedGroups.vue'
import DashGroupsIn from '@/components/atoms/icons/DashGroupsIn.vue'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

export default {
    name: 'Sidebar',
    components: {
        UpgradeButton,
        DashLibrary,
        DashHome,
        DashMembers,
        DashInstitutes,
        DashSupport,
        DashFreelancer,
        DashDiscover,
        ResponsiveButton,
        DashReport,
        DashAyhoots,
        DashCourses,
        DashPurchased,
        DashFolder,
        DashArchive,
        DashGroups,
        DashGroupsIn,
        DashSubscribers,
        DashOwnedGroups,
    },
    props: {
        isOpen: { type: Boolean, default: false },
    },
    setup() {
        const { t } = useI18n()
        const $toast = useToast()

        const menuItemsStructure = computed(() => [
            {
                key: 'dashboard',
                label: t('dashboard.sidebar.menu.dashboard'),
                link: '/student/dashboard',
                icon: 'DashHome',
            },
            {
                key: 'discover',
                label: t('dashboard.sidebar.menu.discover'),
                link: '/student/discover',
                icon: 'DashDiscover',
            },
            {
                key: 'library',
                label: t('dashboard.sidebar.menu.library'),
                icon: 'DashLibrary',
                children: [
                    { label: t('dashboard.sidebar.menu.ayhoots'), link: '/student/exam', icon: 'DashAyhoots' },
                    { label: t('dashboard.sidebar.menu.courses'), link: '/student/courses', icon: 'DashCourses' },
                    {
                        label: t('dashboard.sidebar.menu.purchased'),
                        link: '/student/exam/purchased',
                        icon: 'DashPurchased',
                    },
                    {
                        label: t('dashboard.sidebar.menu.assignedGroups'),
                        link: '/student/exam/assignments',
                        icon: 'DashGroupsIn',
                    },
                ],
            },

            { key: 'reports', label: t('dashboard.sidebar.menu.reports'), link: '/student/report', icon: 'DashReport' },
        ])

        return { $toast, t, menuItemsStructure }
    },
    data() {
        return {
            showInstituteSelector: true,
            selectedInstitute: localStorage.getItem('selectedInstitute') || null,
            institutes: [],
            folders: [],
            groups: [],
            isLoadingFolders: false,
            isLoadingGroups: false,
            openDropdowns: { library: false, folders: false, tutees: false, selectGroup: false },
        }
    },
    computed: {
        currentInstitute() {
            const selectedId = localStorage.getItem('selectedInstitute')
            if (!selectedId) return this.t('dashboard.sidebar.freelance')
            const institute = this.institutes.find((i) => i.id === parseInt(selectedId))
            return institute ? institute.name : this.t('dashboard.sidebar.selectInstitutePlaceholder')
        },
        computedMenuItems() {
            const menu = JSON.parse(JSON.stringify(this.menuItemsStructure))

            const libraryItem = menu.find((item) => item.key === 'library')
            if (libraryItem) {
                // Since library itself doesn't have a link, check for active children
                libraryItem.link = '/student/exam' // Set a base link for parent active state check
                const foldersItem = libraryItem.children.find((child) => child.key === 'folders')
                if (foldersItem) {
                    foldersItem.children = this.folders.map((folder) => ({
                        label: folder.name,
                        link: `/student/exam/folder/${folder.id}`,
                        icon: 'DashFolder',
                    }))
                }
            }

            const tuteesItem = menu.find((item) => item.key === 'tutees')
            if (tuteesItem) {
                // Since tutees itself doesn't have a link, check for active children
                tuteesItem.link = '/student/students' // Set a base link for parent active state check
                const selectGroupItem = tuteesItem.children.find((child) => child.key === 'selectGroup')
                if (selectGroupItem) {
                    selectGroupItem.children = this.groups.map((group) => ({
                        label: group.title,
                        link: `/student/students/group/${group.id}`,
                        icon: 'DashGroups',
                    }))
                }
            }
            return menu
        },
    },
    created() {
        const user = JSON.parse(localStorage.getItem('user') || '{}')
        if (user && user.institutes) {
            this.institutes = user.institutes
        }
    },
    methods: {
        toggleDropdown(key) {
            this.openDropdowns[key] = !this.openDropdowns[key]
        },

        isLinkActive(item) {
            if (!item) return false
            // For parent items, check if the route starts with its base link.
            if (item.children && item.link) {
                return this.$route.path.startsWith(item.link)
            }
            // For regular items, check for an exact match or if it's a parent route.
            if (item.link) {
                return this.$route.path.startsWith(item.link)
            }
            return false
        },
        getInitials(name) {
            if (!name) return ''
            const words = name.split(' ')
            return words.map((word) => word.charAt(0).toUpperCase()).join('')
        },
        toggleInstituteSelector() {
            this.showInstituteSelector = !this.showInstituteSelector
        },
        async selectInstitute(instituteId) {
            try {
                const response = await apiCall('POST', '/api/select-institute', {
                    institute_id: instituteId,
                })
                localStorage.setItem('token', response.token)
                if (instituteId) {
                    localStorage.setItem('selectedInstitute', instituteId)
                    this.selectedInstitute = instituteId
                } else {
                    localStorage.removeItem('selectedInstitute')
                    this.selectedInstitute = null
                }
                this.showInstituteSelector = false
                // Instead of reload, we navigate to the dashboard to refresh the view
                this.$router.push('/student/dashboard').then(() => {
                    // Force a data refresh if already on dashboard
                    if (this.$route.path.includes('dashboard')) {
                        window.dispatchEvent(new CustomEvent('refresh-dashboard-data'));
                    }
                })

            } catch (error) {
                console.error('Failed to select institute:', error)
            }
        },
    },
}
</script>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.3s ease-out;
    max-height: 500px;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
    max-height: 0;
}
</style>
