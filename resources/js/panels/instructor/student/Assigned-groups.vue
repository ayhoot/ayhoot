<template>
    <InstructorLayout>
        <template #secondary="{ isOpen, close }">
            <GroupsSidebar :isOpen="isOpen" @close="close" group-route-param-key="id" />
        </template>
                <div class="">
                    <!-- Tabs -->
                    <!-- <DashTabs
                        :tabs="myTabs"
                        :dropdownItems="groups"
                        :dropdownTriggerLabel="t('groupsPage.selectGroupDropdown')"
                        :dropdownItemPath="`/${$role}/students/group/`"
                    /> -->
                    <!-- Header Section -->
                    <div class="pb-4">
                        <h2 class="text-3xl font-bold mb-2">{{ t('groupsPage.title') }}</h2>
                        <p class="">{{ t('groupsPage.description') }}</p>
                    </div>

                    <!-- Table Card -->
                    <div class="overflow-hidden px-4">
                        <!-- Table Header with Filters -->
                        <div class="py-4 border-b border-gray-300">
                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                                <!-- Left: Search & Filters -->
                                <div
                                    class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto"
                                >
                                    <!-- Search -->
                                    <AyInput
                                        v-model="filters.search"
                                        type="text"
                                        :placeholder="t('groupsPage.searchPlaceholder')"
                                        @update:model-value="handleSearch"
                                    >
                                        <template #icon>
                                            <svg
                                                class="h-5 w-5 text-gray-700"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                                />
                                            </svg>
                                        </template>
                                    </AyInput>

                                    <!-- Sort By -->
                                    <AySelect
                                        v-model="filters.sort"
                                        @update:modelValue="fetchGroupsData"
                                    >
                                        <option value="created_at">{{ t('groupsPage.filters.dateCreated') }}</option>
                                        <option value="title">{{ t('groupsPage.filters.title') }}</option>
                                    </AySelect>
                                </div>
                            </div>
                        </div>

                        <!-- Table Content -->
                        <div class="overflow-x-auto">
                            <div
                                v-if="filteredGroups.length && !isLoading"
                                class="py-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
                            >
                                <div
                                    v-for="group in filteredGroups"
                                    :key="group.id"
                                    class="p-4 bg-white rounded-md shadow-sm border border-gray-200 hover:shadow-lg transition-shadow cursor-pointer"
                                >
                                    <h3 class="text-sm font-bold pb-5 truncate" :title="group.title">
                                        {{ group.title }}
                                    </h3>

                                    <div class="flex items-center text-gray-600 text-xs">
                                        <p
                                            class="font-medium text-gray-400"
                                            :class="$i18n.locale === 'fa' ? 'pl-2' : 'pr-2'"
                                        >
                                            {{ t('groupsPage.card.tuteesLabel') }}
                                        </p>
                                        {{ group.students?.length || 0 }}
                                    </div>
                                    <div class="flex items-center text-gray-600 text-xs">
                                        <p
                                            class="font-medium text-gray-400"
                                            :class="$i18n.locale === 'fa' ? 'pl-2' : 'pr-2'"
                                        >
                                            {{ t('groupsPage.card.instituteLabel') }}
                                        </p>
                                        {{ group.institute?.name || t('groupsPage.card.notAvailable') }}
                                    </div>
                                    <div class="flex items-center text-gray-600 text-xs">
                                        <p
                                            class="font-medium text-gray-400"
                                            :class="$i18n.locale === 'fa' ? 'pl-2' : 'pr-2'"
                                        >
                                            {{ t('groupsPage.card.createdLabel') }}
                                        </p>
                                        {{ formatDate(group.created_at) }}
                                    </div>
                                    <div class="w-full flex justify-between pt-4 text-xs gap-1 rtl:gap-reverse">
                                        <ResponsiveButton
                                            class="text-xs w-full py-1 px-2"
                                            type="light-green"
                                            @click="goToGroupDetail(group.id)"
                                        >
                                            {{ t('groupsPage.card.viewGroup') }}
                                        </ResponsiveButton>

                                        <ResponsiveButton
                                            class="text-xs py-1 px-1"
                                            type="light-red"
                                            @click.stop="confirmDeleteGroup(group.id, group.title)"
                                            :title="t('groupsPage.card.removeTitle')"
                                        >
                                            <DashRemove />
                                        </ResponsiveButton>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-else-if="!isLoading" class="text-center py-12">
                                <svg
                                    width="36"
                                    height="36"
                                    viewBox="0 0 24 24"
                                    class="mx-auto"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M2.3999 21.6L2.40031 17.9996C2.40053 16.0116 4.01224 14.4 6.00031 14.4H13.1998M21.5999 16.6286L19.1999 19.2M19.1999 19.2L16.7999 16.6286M19.1999 19.2V12.6M17.3999 2.40002C18.8561 3.2163 19.7999 4.5249 19.7999 6.00002C19.7999 7.47515 18.8561 8.78375 17.3999 9.60002M14.3999 6.00002C14.3999 7.98825 12.7881 9.60002 10.7999 9.60002C8.81167 9.60002 7.1999 7.98825 7.1999 6.00002C7.1999 4.0118 8.81167 2.40002 10.7999 2.40002C12.7881 2.40002 14.3999 4.0118 14.3999 6.00002Z"
                                        stroke="currentColor"
                                        class="text-gray-400"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">
                                    {{ t('groupsPage.empty.title') }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500" v-if="filters.search">
                                    {{ t('groupsPage.empty.descriptionWithSearch') }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500" v-else>
                                    {{ t('groupsPage.empty.description') }}
                                </p>
                            </div>

                            <!-- Loading State -->
                            <div v-else class="text-center py-12">
                                <div
                                    class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"
                                ></div>
                                <p class="mt-2 text-sm text-gray-500">{{ t('groupsPage.loading') }}</p>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <Pagination v-if="!isLoading" :pagination="pagination" @page-changed="changePage" />
                    </div>
                </div>
    </InstructorLayout>
</template>

<script>
import { apiCall } from "@/services/apiService"
import { getRole } from '@/utils/role'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toast-notification'
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import GroupsSidebar from '@/components/layout/InstructorGroupsSidebar.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashRemove from '@/components/atoms/icons/DashRemove.vue'
import DashTabs from '@/components/layout/InstructorDashTabs.vue'
import Pagination from '@/components/shared/Pagination.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'TeacherGroupsPage',
    components: {
        InstructorLayout,
        AyButton,
        GroupsSidebar,
        ResponsiveButton,
        DashRemove,
        DashTabs,
        Pagination,
        AyInput,
        AySelect,
    },
    setup() {
        const { t } = useI18n()
        const $toast = useToast()
        return { t, $toast }
    },
    data() {
        return {
            groupsData: [],
            isLoading: false,
            filters: {
                search: '',
                sort: 'created_at',
                page: 1,
            },
            pagination: {
                current_page: 1,
                from: 0,
                to: 0,
                total: 0,
                last_page: 1,
            },
            searchTimeout: null,
            groups: [],
        }
    },
    computed: {
        myTabs() {
            return [
                { label: this.t('groupsPage.tabs.allTutees'), route: `/${getRole()}/students` },
                { label: this.t('groupsPage.tabs.ownedGroups'), route: `/${getRole()}/students/owned-groups` },
                { label: this.t('groupsPage.tabs.assignedGroups'), route: `/${getRole()}/students/assigned-groups` },
                { label: this.t('groupsPage.tabs.selectGroup'), route: `/${getRole()}/students` },
            ]
        },
        filteredGroups() {
            let filtered = [...this.groupsData]
            if (this.filters.search) {
                const query = this.filters.search.toLowerCase()
                filtered = filtered.filter((group) => group.title && group.title.toLowerCase().includes(query))
            }
            filtered.sort((a, b) => {
                if (this.filters.sort === 'title') {
                    return (a.title || '').localeCompare(b.title || '')
                } else {
                    return new Date(b.created_at) - new Date(a.created_at)
                }
            })
            return filtered
        },
    },
    methods: {
        goToGroupDetail(groupId) {
            this.$router.push(`/${getRole()}/students/group/${groupId}`)
        },
        async fetchGroups() {
            try {
                const data = await apiCall('GET', `/api/instructor/group`)
                this.groups = data.groups || []
            } catch (error) {
                console.error('Failed to fetch groups for tabs:', error)
                this.groups = []
            }
        },
        async fetchGroupsData() {
            this.isLoading = true
            try {
                const queryParams = new URLSearchParams({
                    page: this.filters.page,
                    search: this.filters.search,
                    sort: this.filters.sort,
                }).toString()
                const response = await apiCall('GET', `/api/instructor/group/assigned?${queryParams}`)

                if (response.groups && Array.isArray(response.groups)) {
                    this.groupsData = response.groups
                    this.pagination = {
                        current_page: 1,
                        from: 1,
                        to: this.groupsData.length,
                        total: this.groupsData.length,
                        last_page: 1,
                    }
                } else if (response.groups && response.groups.data && Array.isArray(response.groups.data)) {
                    this.groupsData = response.groups.data
                    this.pagination = {
                        current_page: response.groups.current_page,
                        from: response.groups.from,
                        to: response.groups.to,
                        total: response.groups.total,
                        last_page: response.groups.last_page,
                    }
                } else if (response.data && Array.isArray(response.data)) {
                    this.groupsData = response.data
                    this.pagination = {
                        current_page: response.current_page,
                        from: response.from,
                        to: response.to,
                        total: response.total,
                        last_page: response.last_page,
                    }
                } else {
                    this.groupsData = []
                    this.$toast.error(this.t('groupsPage.notifications.structureError'))
                }
            } catch (error) {
                console.error('Error fetching groups:', error)
                this.$toast.error(error.message || this.t('groupsPage.notifications.loadFailed'))
                this.groupsData = []
                this.pagination.last_page = 1
            } finally {
                this.isLoading = false
            }
        },
        handleSearch() {
            if (this.searchTimeout) clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.filters.page = 1
                this.fetchGroupsData()
            }, 300)
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page && page !== this.filters.page) {
                this.filters.page = page
                this.fetchGroupsData()
            }
        },
        formatDate(dateString) {
            if (!dateString) return this.t('groupsPage.card.notAvailable')
            const locale = this.$i18n.locale === 'fa' ? 'fa-IR' : 'en-US'
            return new Date(dateString).toLocaleDateString(locale, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            })
        },
        async confirmDeleteGroup(groupId, groupTitle) {
            if (await confirmDialog({ message: this.t('groups.notifications.confirmDelete'), variant: 'danger' })) {
                try {
                    this.isLoading = true
                    await apiCall('GET', `/api/instructor/group/delete/${groupId}`)
                    this.$toast.success(
                        this.t('groupsPage.notifications.deleteSuccess', {
                            groupTitle: groupTitle || `ID: ${groupId}`,
                        })
                    )
                    await this.fetchGroupsData()
                } catch (error) {
                    console.error('Error deleting group:', error)
                    this.$toast.error(error.message || this.t('groupsPage.notifications.deleteFailed'))
                } finally {
                    this.isLoading = false
                }
            }
        },
    },
    async mounted() {
        await this.fetchGroupsData()
        await this.fetchGroups()
    },
}
</script>
