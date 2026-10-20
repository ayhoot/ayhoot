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

                <!-- Folder Selector Section -->
                <div class="flex-shrink-0">
                    <button
                        @click="toggleFolderSelector"
                        class="w-full rounded-md p-3 transition-all hover:bg-gray-100 cursor-pointer"
                        :disabled="isLoadingFolders"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400 font-bold">
                                {{ currentFolderDisplayName }}
                            </span>
                            <svg
                                class="w-5 h-5 text-gray-400 transition-transform duration-300"
                                :class="{ 'rotate-180': showFolderSelector }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
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

                    <!-- Folder List Content -->
                    <transition name="folder-list-transition">
                        <div
                            v-show="showFolderSelector"
                            id="folder-list-content"
                            class="mt-1 overflow-hidden rounded-md"
                        >
                            <!-- SKELETON LOADER -->
                            <div v-if="isLoadingFolders" class="py-1 animate-pulse space-y-1">
                                <div
                                    v-for="n in 3"
                                    :key="n"
                                    class="my-0.5 flex w-full items-center justify-between rounded-md bg-gray-100 p-2"
                                >
                                    <div class="flex items-center w-full gap-2 rtl:gap-reverse">
                                        <div class="w-5 h-5 bg-gray-200 rounded"></div>
                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                    </div>
                                    <div class="w-5 h-5 bg-gray-200 rounded-sm"></div>
                                </div>
                            </div>

                            <!-- ACTUAL CONTENT -->
                            <div v-else class="overflow-y-auto py-1">
                                <div v-for="folder in folders" :key="folder.id" class="group relative w-full">
                                    <div
                                        class="flex items-center justify-between w-full rounded-md text-sm transition-colors my-0.5"
                                        :class="{
                                            'bg-[#3450AE] text-white font-bold': isFolderActive(folder.id),
                                            'bg-gray-100 hover:bg-[#3450AE] hover:text-white': !isFolderActive(
                                                folder.id
                                            ),
                                            'opacity-75': isSubmittingFolderAction,
                                        }"
                                    >
                                        <button
                                            @click="goToFolder(folder.id)"
                                            :disabled="isSubmittingFolderAction"
                                            class="flex flex-grow items-center px-4 py-2 cursor-pointer text-start"
                                            :class="[isSubmittingFolderAction ? 'cursor-not-allowed' : '']"
                                        >
                                            <DashFolder
                                                class="h-5 w-5 flex-shrink-0"
                                                :class="$i18n.locale === 'fa' ? 'ml-2' : 'mr-2'"
                                            />
                                            <span class="truncate">{{ folder.name }}</span>
                                        </button>
                                        <ResponsiveButton
                                            @click.stop="confirmDeleteFolder(folder.id)"
                                            type="danger-ghost"
                                            size="small"
                                            :aria-label="t('librarySidebar.actions.removeAriaLabel')"
                                            :class="$i18n.locale === 'fa' ? 'ml-2' : 'mr-2'"
                                            class="p-1 flex-shrink-0"
                                            :title="t('librarySidebar.actions.removeTitle')"
                                            :disabled="isSubmittingFolderAction"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </ResponsiveButton>
                                    </div>
                                </div>

                                <!-- Create Folder Input -->
                                <div
                                    v-if="creatingFolder"
                                    class="flex items-center w-full gap-2 rtl:gap-reverse px-3 py-2 bg-gray-100 rounded-md my-0.5"
                                >
                                    <input
                                        v-model.trim="newFolderName"
                                        type="text"
                                        :placeholder="t('librarySidebar.create.placeholder')"
                                        class="w-full px-2 py-1 text-sm border-b-2 border-gray-300 bg-transparent focus:border-blue-500 focus:outline-none flex-grow"
                                        @keyup.enter="saveNewFolder"
                                        @keyup.esc="cancelNewFolderCreation"
                                        :disabled="isSubmittingFolderAction"
                                        ref="newFolderInputRef"
                                    />
                                    <ResponsiveButton
                                        @click="saveNewFolder"
                                        type="light-gray"
                                        :aria-label="t('librarySidebar.actions.saveAriaLabel')"
                                        :disabled="isSubmittingFolderAction || !newFolderName.trim()"
                                    >
                                        <DashCheck v-if="!isSubmittingFolderAction" class="h-5 w-5" />
                                        <div
                                            v-else
                                            class="animate-spin rounded-full h-5 w-5 border-b-2 border-gray-600"
                                        ></div>
                                    </ResponsiveButton>
                                    <ResponsiveButton
                                        @click="cancelNewFolderCreation"
                                        type="light-gray"
                                        :aria-label="t('librarySidebar.actions.cancelAriaLabel')"
                                        :disabled="isSubmittingFolderAction"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </ResponsiveButton>
                                </div>

                                <!-- Create Folder Button -->
                                <button
                                    v-else
                                    @click="initiateNewFolderCreation"
                                    class="w-full flex items-center bg-gray-100 rounded-md px-4 py-2 my-0.5 text-start text-sm hover:bg-gray-300 hover:text-gray-900 transition-colors cursor-pointer"
                                    :disabled="isLoadingFolders || isSubmittingFolderAction"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 flex-shrink-0"
                                        :class="$i18n.locale === 'fa' ? 'ml-2' : 'mr-2'"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    {{ t('librarySidebar.create.button') }}
                                </button>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>

            <ul>
                <hr class="text-gray-300 my-2" />
                <li>
                    <router-link
                        :to="`/${$role}/exam/archive`"
                        :class="[
                            'group text-sm flex items-center gap-3 rtl:gap-reverse px-5 py-3 transform transition-transform duration-300 ease-in-out',
                            isMenuItemActive(`/${$role}/exam/archive`)
                                ? 'bg-[#DCE9FF] text-[#4C8BF5] rounded-md font-bold'
                                : 'text-[#7C7C7C] hover:bg-gray-100 font-medium hover:rounded-md',
                        ]"
                    >
                        <DashArchive class="w-5 h-5 flex-shrink-0" />
                        <p>{{ t('librarySidebar.menu.archive') }}</p>
                    </router-link>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { apiCall } from "@/services/apiService"
import { getRole } from '@/utils/role'
import { useConfirm } from '@/composables/useConfirm'
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

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

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
    },
    setup() {
        const $toast = useToast()
        const { t } = useI18n()
        return { toast: $toast, t }
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
                    link: `/${getRole()}/exam`,
                    icon: 'DashAyhoots',
                },
                {
                    label: this.t('librarySidebar.menu.courses'),
                    link: `/${getRole()}/exam/courses`,
                    icon: 'DashCourses',
                },
                {
                    label: this.t('librarySidebar.menu.purchased'),
                    link: `/${getRole()}/exam/purchased`,
                    icon: 'DashPurchased',
                },
            ]
        },
        currentFolderDisplayName() {
            const activeFolderId = this.getActiveFolderId()
            if (activeFolderId) {
                const folder = this.folders.find((f) => f.id === activeFolderId)
                return folder ? folder.name : this.t('librarySidebar.selectFolder')
            }
            return this.t('librarySidebar.selectFolder')
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
        isFolderActive(folderId) {
            return this.$route.path.startsWith(`/${getRole()}/exam/folder/${folderId}`)
        },
        getActiveFolderId() {
            const currentPath = this.$route.path
            const folderMatch = currentPath.match(/\/(teacher|institute)\/exam\/folder\/(\d+)/)
            return folderMatch ? parseInt(folderMatch[2], 10) : null
        },
        async fetchFolders() {
            this.isLoadingFolders = true
            try {
                const response = await apiCall('GET', `/api/instructor/folder`)
                this.folders = response.folders || []
            } catch (error) {
                console.error('Error fetching folders:', error)
                await alertDialog({
                    title: this.t('librarySidebar.notifications.loadFailed'),
                })
                this.folders = []
            } finally {
                this.isLoadingFolders = false
            }
        },
        toggleFolderSelector() {
            this.showFolderSelector = !this.showFolderSelector
        },
        goToFolder(folderId) {
            if (this.$route.path.startsWith(`/${getRole()}/exam/folder/${folderId}`)) {
                return
            }
            this.$router.push(`/${getRole()}/exam/folder/${folderId}`).catch((err) => {
                if (err.name !== 'NavigationDuplicated' && err.name !== 'NavigationCancelled') {
                    console.error('Router push error:', err)
                }
            })
        },
        initiateNewFolderCreation() {
            this.creatingFolder = true
            this.newFolderName = ''
            this.$nextTick(() => {
                this.$refs.newFolderInputRef?.focus()
            })
        },
        cancelNewFolderCreation() {
            this.creatingFolder = false
            this.newFolderName = ''
        },
        async saveNewFolder() {
            if (this.newFolderName.trim() === '') {
                await alertDialog({
                    title: this.t('librarySidebar.notifications.nameRequired'),
                })
                return
            }
            this.isSubmittingFolderAction = true
            try {
                await apiCall('POST', `/api/instructor/folder`, {
                    name: this.newFolderName.trim(),
                })
                this.cancelNewFolderCreation()
                await this.fetchFolders()
                this.toast.success(this.t('librarySidebar.notifications.createSuccess'))
            } catch (error) {
                console.error('Error creating folder:', error)
                if (!(error.response && error.response.message)) {
                    await alertDialog({
                        title: this.t('librarySidebar.notifications.createFailed'),
                    })
                }
            } finally {
                this.isSubmittingFolderAction = false
            }
        },
        async confirmDeleteFolder(folderId) {
            const folderToDelete = this.folders.find((f) => f.id === folderId)
            if (folderToDelete) {
                const ok = await confirmDialog({
                    title: this.t('librarySidebar.confirmation.title'),
                    message: this.t('librarySidebar.confirmation.delete', { name: folderToDelete.name }),
                    confirmText: this.t('librarySidebar.confirmation.confirmButton'),
                    cancelText: this.t('librarySidebar.confirmation.cancelButton'),
                    variant: 'danger',
                })

                if (ok) {
                    this.deleteFolder(folderId)
                }
            }
        },
        async deleteFolder(folderId) {
            this.isSubmittingFolderAction = true
            try {
                await apiCall('GET', `/api/instructor/folder/delete/${folderId}`)
                await this.fetchFolders()
                this.toast.success(this.t('librarySidebar.notifications.deleteSuccess'))
                if (this.isFolderActive(folderId)) {
                    this.$router.push(`/${getRole()}/exam`)
                }
            } catch (error) {
                console.error('Error deleting folder:', error)
                if (!(error.response && error.response.message)) {
                    await alertDialog({
                        title: this.t('librarySidebar.notifications.deleteFailed'),
                    })
                }
            } finally {
                this.isSubmittingFolderAction = false
            }
        },
    },
    mounted() {
        this.fetchFolders()
    },
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
