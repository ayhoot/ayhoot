<template>
    <div
        class="hidden xl:block"
        :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'"
        :class="{ 'font-payda': $i18n.locale === 'fa' }"
    >
        <aside
            :class="[
                'fixed top-14 flex h-[calc(100vh-3.6rem)] w-64 flex-col justify-between overflow-y-auto border-gray-300 bg-white px-2 pt-1',
                $i18n.locale === 'fa' ? 'right-64 border-l' : 'left-64 border-r',
            ]"
            aria-label="Groups Sidebar"
        >
            <div>
                <nav aria-labelledby="tutees-menu-heading">
                    <h3 id="tutees-menu-heading" class="py-2 px-3 text-start text-xs font-semibold text-gray-400">
                        {{ t('groupsSidebar.menuTitle') }}
                    </h3>
                    <ul>
                        <li v-for="item in menuItems" :key="item.link" class="py-0.5">
                            <router-link
                                :to="item.link"
                                :class="[
                                    'group flex transform items-center gap-3 rtl:gap-reverse px-5 py-3 text-sm transition-transform duration-300 ease-in-out',
                                    isMenuItemActive(item.link)
                                        ? 'rounded-md bg-[#DCE9FF] font-bold text-[#4C8BF5]'
                                        : 'font-medium text-[#7C7C7C] hover:rounded-md hover:bg-gray-100',
                                ]"
                            >
                                <component :is="item.icon" class="h-5 w-5 flex-shrink-0" aria-hidden="true" />
                                <span>{{ item.label }}</span>
                            </router-link>
                        </li>
                    </ul>
                </nav>
                <hr class="my-2 text-gray-300" />

                <div class="flex-shrink-0">
                    <button
                        @click="toggleGroupSelector"
                        class="w-full rounded-md p-3 transition-all hover:bg-gray-100 cursor-pointer"
                        :aria-expanded="isGroupSelectorUIVisible.toString()"
                        aria-controls="group-list-content"
                        :disabled="isLoadingGroups"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-gray-400">
                                {{ currentGroupNameDisplay }}
                            </span>
                            <svg
                                class="h-5 w-5 text-gray-400 transition-transform duration-300"
                                :class="{ 'rotate-180': isGroupSelectorUIVisible }"
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

                    <transition name="group-list-transition">
                        <div
                            v-show="isGroupSelectorUIVisible"
                            id="group-list-content"
                            class="mt-1 overflow-hidden rounded-md"
                        >
                            <div v-if="isLoadingGroups" class="py-1 animate-pulse space-y-1">
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
                            <div v-else class="overflow-y-auto py-1">
                                <div
                                    v-for="group in groups"
                                    :key="group.id"
                                    :class="[
                                        'my-0.5 flex w-full items-center justify-between rounded-md text-start text-sm transition-colors',
                                        isActiveGroup(group.id)
                                            ? 'bg-[#3450AE] font-bold text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-[#3450AE] hover:text-white',
                                        isSubmittingGroup ? 'opacity-75' : '',
                                    ]"
                                >
                                    <button
                                        @click="handleGroupSelect(group.id)"
                                        :disabled="isSubmittingGroup"
                                        class="flex flex-grow items-center px-4 py-2 cursor-pointer"
                                        :class="[isSubmittingGroup ? 'cursor-not-allowed' : '']"
                                    >
                                        <DashGroups class="me-2 h-5 w-5 flex-shrink-0" aria-hidden="true" />
                                        <span class="truncate">{{ group.title }}</span>
                                    </button>
                                    <ResponsiveButton
                                        @click.stop="confirmDeleteGroup(group.id)"
                                        type="danger-ghost"
                                        size="small"
                                        :aria-label="t('groupsSidebar.actions.removeAriaLabel')"
                                        :class="$i18n.locale === 'fa' ? 'ml-2' : 'mr-2'"
                                        class="p-1 flex-shrink-0"
                                        :disabled="isSubmittingGroup"
                                        :title="t('groupsSidebar.actions.removeTitle')"
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

                                <div v-if="isCreatingNewGroup" class="my-0.5 rounded-md bg-gray-100 px-3 py-2">
                                    <div class="flex w-full items-center gap-2 rtl:gap-reverse">
                                        <input
                                            v-model.trim="newGroupNameInput"
                                            type="text"
                                            :placeholder="t('groupsSidebar.create.placeholder')"
                                            class="w-full flex-grow border-b-2 border-gray-300 bg-transparent px-2 py-1 text-sm focus:border-blue-500 focus:outline-none"
                                            @keyup.enter="handleSaveNewGroup"
                                            @keyup.esc="cancelNewGroupCreation"
                                            ref="newGroupInputRef"
                                            :aria-label="t('groupsSidebar.create.ariaLabel')"
                                            :disabled="isSubmittingGroup"
                                        />
                                        <ResponsiveButton
                                            @click="handleSaveNewGroup"
                                            type="light-gray"
                                            :aria-label="t('groupsSidebar.actions.saveAriaLabel')"
                                            :disabled="!newGroupNameInput.trim() || isSubmittingGroup"
                                        >
                                            <DashCheck class="h-5 w-5" aria-hidden="true" />
                                        </ResponsiveButton>
                                        <ResponsiveButton
                                            @click="cancelNewGroupCreation"
                                            type="light-gray"
                                            :aria-label="t('groupsSidebar.actions.cancelAriaLabel')"
                                            :disabled="isSubmittingGroup"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </ResponsiveButton>
                                    </div>
                                </div>
                                <button
                                    v-else
                                    @click="initiateNewGroupCreation"
                                    class="my-0.5 flex w-full items-center rounded-md bg-gray-100 px-4 py-2 text-start text-sm text-gray-700 transition-colors hover:bg-gray-300 hover:text-gray-900 cursor-pointer"
                                    :disabled="isSubmittingGroup || isLoadingGroups"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="me-2 h-5 w-5 flex-shrink-0"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    {{ t('groupsSidebar.create.button') }}
                                </button>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </aside>
    </div>
</template>

<script>
import { apiCall } from "@/services/apiService"
import { getRole } from '@/utils/role'
import { useConfirm } from '@/composables/useConfirm'
import { useToast } from 'vue-toast-notification'
import { useI18n } from 'vue-i18n'
import DashSubscribers from '@/components/atoms/icons/DashSubscribers.vue'
import DashGroupsIn from '@/components/atoms/icons/DashGroupsIn.vue'
import DashOwnedGroups from '@/components/atoms/icons/DashOwnedGroups.vue'
import DashGroups from '@/components/atoms/icons/DashGroups.vue'
import DashCheck from '@/components/atoms/icons/DashCheck.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'GroupsSidebar',
    components: { DashSubscribers, DashGroupsIn, DashOwnedGroups, DashGroups, DashCheck, ResponsiveButton },
    props: {
        groupRouteParamKey: {
            type: String,
            default: 'groupId',
        },
    },
    setup() {
        const { t } = useI18n()
        const $toast = useToast()
        return { t, $toast }
    },
    data() {
        return {
            isGroupSelectorUIVisible: true,
            isCreatingNewGroup: false,
            newGroupNameInput: '',
            groups: [],
            isLoadingGroups: false,
            isSubmittingGroup: false,
        }
    },
    computed: {
        menuItems() {
            return [
                {
                    label: this.t('groupsSidebar.menu.allSubscribers'),
                    link: `/${getRole()}/students`,
                    icon: 'DashSubscribers',
                },
                {
                    label: this.t('groupsSidebar.menu.assignedGroups'),
                    link: `/${getRole()}/students/assigned-groups`,
                    icon: 'DashGroupsIn',
                },
                {
                    label: this.t('groupsSidebar.menu.ownedGroups'),
                    link: `/${getRole()}/students/owned-groups`,
                    icon: 'DashOwnedGroups',
                },
            ]
        },
        currentGroupNameDisplay() {
            const currentGroup = this.groups.find((group) =>
                this.$route.path.startsWith(`/${getRole()}/students/group/${group.id}`)
            )
            return currentGroup ? currentGroup.title : this.t('groupsSidebar.selectGroup')
        },
    },
    created() {
        this.fetchGroups()
    },
    methods: {
        isMenuItemActive(itemLink) {
            const currentPath = this.$route.path
            return currentPath === itemLink
        },
        isActiveGroup(groupId) {
            return this.$route.path.startsWith(`/${getRole()}/students/group/${groupId}`)
        },
        toggleGroupSelector() {
            this.isGroupSelectorUIVisible = !this.isGroupSelectorUIVisible
        },
        handleGroupSelect(groupId) {
            if (this.isActiveGroup(groupId)) return
            this.$router.push(`/${getRole()}/students/group/${groupId}`).catch((err) => {
                if (err.name !== 'NavigationDuplicated' && err.name !== 'NavigationCancelled') {
                    console.error('Router push error:', err)
                }
            })
        },
        initiateNewGroupCreation() {
            this.isCreatingNewGroup = true
            this.newGroupNameInput = ''
            this.$nextTick(() => {
                this.$refs.newGroupInputRef?.focus()
            })
        },
        cancelNewGroupCreation() {
            this.isCreatingNewGroup = false
            this.newGroupNameInput = ''
        },
        async fetchGroups() {
            this.isLoadingGroups = true
            try {
                const data = await apiCall('GET', `/api/instructor/group`)
                this.groups = data.groups || []
            } catch (error) {
                console.error('Failed to fetch groups:', error)
                await alertDialog({
                    title: this.t('groupsSidebar.notifications.loadFailed'),
                })
                this.groups = []
            } finally {
                this.isLoadingGroups = false
            }
        },
        async handleSaveNewGroup() {
            if (!this.newGroupNameInput.trim()) {
                await alertDialog({
                    title: this.t('groupsSidebar.notifications.nameRequired'),
                })
                return
            }
            this.isSubmittingGroup = true
            try {
                await apiCall('POST', `/api/instructor/group`, { title: this.newGroupNameInput.trim() })
                this.cancelNewGroupCreation()
                await this.fetchGroups()
                this.$toast.success(this.t('groupsSidebar.notifications.createSuccess'))
            } catch (error) {
                console.error('Failed to save new group:', error)
                await alertDialog({
                    title: this.t('groupsSidebar.notifications.createFailed'),
                })
            } finally {
                this.isSubmittingGroup = false
            }
        },
        async confirmDeleteGroup(groupId) {
            const groupToDelete = this.groups.find((g) => g.id === groupId)
            if (groupToDelete) {
                const ok = await confirmDialog({
                    title: this.t('groupsSidebar.confirmation.title'),
                    message: this.t('groupsSidebar.confirmation.delete', { title: groupToDelete.title }),
                    confirmText: this.t('groupsSidebar.confirmation.confirmButton'),
                    cancelText: this.t('groupsSidebar.confirmation.cancelButton'),
                    variant: 'danger',
                })

                if (ok) {
                    this.handleDeleteGroup(groupId)
                }
            }
        },
        async handleDeleteGroup(groupId) {
            this.isSubmittingGroup = true
            try {
                await apiCall('GET', `/api/instructor/group/delete/${groupId}`)
                await this.fetchGroups()
                this.$toast.success(this.t('groupsSidebar.notifications.deleteSuccess'))
            } catch (error) {
                console.error(`Failed to delete group ${groupId}:`, error)
                await alertDialog({
                    title: this.t('groupsSidebar.notifications.deleteFailed'),
                })
            } finally {
                this.isSubmittingGroup = false
            }
        },
    },
}
</script>

<style scoped>
.group-list-transition-enter-active,
.group-list-transition-leave-active {
    transition: all 0.3s ease-out;
    max-height: 500px;
    opacity: 1;
}
.group-list-transition-enter-from,
.group-list-transition-leave-to {
    opacity: 0;
    max-height: 0;
    transform: translateY(-10px);
    overflow: hidden;
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    50% {
        opacity: 0.5;
    }
}
.font-payda {
    font-family: 'Peyda', sans-serif !important;
}
</style>
