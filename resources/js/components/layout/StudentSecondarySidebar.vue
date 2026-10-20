<template>
    <!-- Desktop Sidebar -->
    <div class="hidden sm:hidden md:hidden lg:hidden xl:block" :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'">
        <div
            :class="[
                'py-2 fixed flex flex-col justify-between pt-1 px-2 top-14 h-[calc(100vh-3.6rem)] w-64 bg-white overflow-y-auto',
                $i18n.locale === 'fa' ? 'right-64 border-l border-gray-300' : 'left-64 border-r border-gray-300',
            ]"
        >
            <!-- Top Section: Menu Items & folder Selector -->
            <div>
                <h3 class="text-xs font-semibold py-2 px-3 text-start text-gray-400">
                    {{ t('librarySidebar.title') }}
                </h3>

                <ul>
                    <li v-for="(item, index) in menuItems" :key="index" class="py-0.5">
                        <router-link
                            :to="item.link"
                            :class="[
                                'group text-sm flex items-center gap-3 rtl:gap-reverse px-5 py-3 transform transition-transform duration-300 ease-in-out',
                                isMenuItemActive(item.link)
                                    ? 'bg-[#DCE9FF] text-[#4C8BF5] rounded-md font-bold'
                                    : 'text-[#7C7C7C] hover:bg-gray-100 font-medium hover:rounded-md',
                            ]"
                        >
                            <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                            <p>{{ item.label }}</p>
                        </router-link>
                    </li>
                    <hr class="text-gray-300 my-2" />
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useToast } from 'vue-toast-notification'
import { useI18n } from 'vue-i18n'
import DashInstitutes from '@/components/atoms/icons/DashInstitutes.vue'
import DashArchive from '@/components/atoms/icons/DashArchive.vue'
import DashAyhoots from '@/components/atoms/icons/DashAyhoots.vue'
import DashFolder from '@/components/atoms/icons/DashFolder.vue'
import DashCourses from '@/components/atoms/icons/DashCourses.vue'
import DashPurchased from '@/components/atoms/icons/DashPurchased.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashCheck from '@/components/atoms/icons/DashCheck.vue'
import DashLibrary from '@/components/atoms/icons/DashLibrary.vue'
import DashGroupsIn from '@/components/atoms/icons/DashGroupsIn.vue'

export default {
    name: 'SecondarySidebar',
    components: {
        DashInstitutes,
        DashArchive,
        DashAyhoots,
        DashCourses,
        DashPurchased,
        DashFolder,
        DashLibrary,
        ResponsiveButton,
        DashCheck,
        DashGroupsIn,
    },
    setup() {
        const $toast = useToast()
        const { t } = useI18n()
        return { $toast, t }
    },
    data() {
        return {
            showFolderSelector: true,
            creatingFolder: false,
            newFolderName: '',
            isLoadingFolders: false,
            isSubmittingFolderAction: false,
            folders: [],
        }
    },
    computed: {
        menuItems() {
            return [
                {
                    label: this.t('librarySidebar.menu.ayhoots'),
                    link: '/student/exam',
                    icon: 'DashAyhoots',
                },
                {
                    label: this.t('librarySidebar.menu.courses'),
                    link: '/student/courses',
                    icon: 'DashCourses',
                },
                {
                    label: this.t('librarySidebar.menu.purchased'),
                    link: '/student/exam/purchased',
                    icon: 'DashPurchased',
                },
                {
                    label: this.t('librarySidebar.menu.groups'),
                    link: '/student/exam/assignments',
                    icon: 'DashGroupsIn',
                },
            ]
        },
    },
    methods: {
        isMenuItemActive(itemLink) {
            const currentPathSegments = this.$route.path.split('/').filter(Boolean)
            const itemLinkSegments = itemLink.split('/').filter(Boolean)

            if (currentPathSegments.length !== itemLinkSegments.length) {
                return false
            }
            return itemLinkSegments.every((segment, index) => segment === currentPathSegments[index])
        },
    },
    mounted() {},
}
</script>

<style scoped>
.folder-list-transition-enter-active,
.folder-list-transition-leave-active {
    transition: all 0.3s ease-out;
    max-height: 500px;
    opacity: 1;
}

.folder-list-transition-enter-from,
.folder-list-transition-leave-to {
    opacity: 0;
    max-height: 0;
    transform: translateY(-10px);
    overflow: hidden;
}

.flex-shrink-0 {
    flex-shrink: 0;
}

@keyframes pulse {
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
