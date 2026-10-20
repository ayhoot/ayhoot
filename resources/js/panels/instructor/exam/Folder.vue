<template>
    <InstructorLayout>
                <div class="">
                    <!-- Header Section -->
                    <div class="pb-4">
                        <nav class="text-sm mb-2" aria-label="Breadcrumb">
                            <ol class="list-none p-0 inline-flex">
                                <li class="flex items-center">
                                    <router-link :to="`/${$role}/exam/folders`" class="text-gray-500 hover:text-gray-700">
                                        {{ t('folderDetail.breadcrumb.folders') }}
                                    </router-link>
                                    <svg
                                        class="fill-current w-3 h-3 mx-3 text-gray-500"
                                        :class="{ 'rotate-180': $i18n.locale === 'fa' }"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 320 512"
                                    >
                                        <path
                                            d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569 9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"
                                        />
                                    </svg>
                                </li>
                                <li class="flex items-center">
                                    <span class="text-gray-700">{{
                                        folder?.name || t('folderDetail.breadcrumb.folderDetails')
                                    }}</span>
                                </li>
                            </ol>
                        </nav>
                        <h2 class="text-3xl font-bold mb-2">{{ folder?.name || t('folderDetail.header.loading') }}</h2>
                        <p class="">{{ t('folderDetail.header.description') }}</p>
                    </div>

                    <!-- Table Card -->
                    <div class="overflow-hidden px-4">
                        <!-- Table Header with Filters -->
                        <div class="py-4 border-b border-gray-300">
                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                                <div class="relative sm:max-w-md w-full sm:w-auto sm:flex-1">
                                    <input
                                        type="text"
                                        v-model="filters.search"
                                        @input="handleSearch"
                                        :placeholder="t('folderDetail.searchPlaceholder')"
                                        class="inline-flex w-full p-2 text-sm font-bold text-gray-900 bg-gray-50 rounded-sm border-2 border-gray-700 focus:outline-none"
                                        :class="$i18n.locale === 'fa' ? 'ps-10' : 'pl-10'"
                                    />
                                    <div
                                        class="absolute inset-y-0 flex items-center pointer-events-none"
                                        :class="$i18n.locale === 'fa' ? 'start-0 ps-3' : 'left-0 pl-3'"
                                    >
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
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table Content -->
                        <div class="overflow-x-auto">
                            <!-- Loop over the combined 'filteredItems' array -->
                            <div
                                v-if="!isLoading && filteredItems.length"
                                class="py-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
                            >
                                <div
                                    v-for="item in filteredItems"
                                    :key="`${item.type}-${item.id}`"
                                    class="relative bg-white rounded-md shadow-sm border border-gray-200 hover:shadow-lg transition-shadow"
                                >
                                    <!-- Badge to show item type -->
                                    <span
                                        class="absolute top-2 text-xs font-bold py-1 px-2 rounded"
                                        :class="[
                                            $i18n.locale === 'fa' ? 'left-2' : 'right-2',
                                            {
                                                'bg-blue-100 text-blue-800': item.type === 'exam',
                                                'bg-green-100 text-green-800': item.type === 'course',
                                            },
                                        ]"
                                    >
                                        {{
                                            t(
                                                `folderDetail.card.itemType.${item.type === 'exam' ? 'ayhoot' : 'course'}`
                                            )
                                        }}
                                    </span>

                                    <img
                                        :src="item.cover ? '/uploads/' + item.cover : '/imgHolders/CardHolderImage.jpg'"
                                        :alt="t('folderDetail.card.imageAlt')"
                                        class="w-full h-32 object-cover rounded-t-md"
                                    />
                                    <div class="p-4">
                                        <h3
                                            class="text-sm font-bold truncate max-w-[16ch] overflow-hidden text-ellipsis pb-1"
                                            :title="item.title"
                                        >
                                            {{ item.title }}
                                        </h3>
                                        <div class="flex items-center text-gray-600 text-xs font-semibold">
                                            @{{
                                                folder.teacher?.username || t('folderDetail.card.byline.notAvailable')
                                            }}
                                            <span v-if="folder.institute" class="px-1 font-normal">{{
                                                t('folderDetail.card.byline.from')
                                            }}</span>
                                            @{{ folder.institute?.name || t('folderDetail.card.byline.selfCreated') }}
                                        </div>
                                        <div class="w-full flex justify-end text-xs pt-3 gap-1 rtl:gap-reverse">
                                            <ResponsiveButton
                                                v-if="item.type === 'exam'"
                                                class="text-xs w-full py-1 px-2"
                                                type="light-green"
                                                :to="`/${$role}/exam/${item.id}/create`"
                                                :title="t('folderDetail.card.actions.editAyhootTitle')"
                                            >
                                                {{ t('folderDetail.card.actions.edit') }}
                                            </ResponsiveButton>
                                            <ResponsiveButton
                                                v-if="item.type === 'course'"
                                                class="text-xs w-full py-1 px-2"
                                                type="light-green"
                                                :to="`/${$role}/course/${item.id}`"
                                                :title="t('folderDetail.card.actions.editCourseTitle')"
                                            >
                                                {{ t('folderDetail.card.actions.edit') }}
                                            </ResponsiveButton>
                                            <ResponsiveButton
                                                class="text-xs py-1 px-1"
                                                type="light-red"
                                                @click="confirmUnassignItem(item)"
                                                :title="t('folderDetail.card.actions.removeFromFolderTitle')"
                                            >
                                                <DashRemove />
                                            </ResponsiveButton>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty state messages -->
                            <div
                                v-else-if="!isLoading && folder && items.length === 0 && !filters.search"
                                class="text-center py-12"
                            >
                                <svg
                                    width="36"
                                    height="36"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    class="mx-auto"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M2.40093 8.41673L2.40084 17.941C2.40083 19.0456 3.29626 19.9411 4.40084 19.9411L19.5996 19.9411C20.7041 19.9411 21.5995 19.0457 21.5996 17.9412L21.5999 8.0125C21.5999 7.46021 21.1522 7.01247 20.5999 7.01247H12.0836L9.31857 4.05884H3.40028C2.84785 4.05884 2.40007 4.50605 2.40024 5.05848C2.40055 6.0093 2.40094 7.43621 2.40093 8.41673Z"
                                        stroke="currentColor"
                                        class="text-gray-400"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">
                                    {{ t('folderDetail.empty.title') }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ t('folderDetail.empty.description') }}
                                </p>
                            </div>

                            <div
                                v-else-if="!isLoading && filters.search && filteredItems.length === 0"
                                class="text-center py-12"
                            >
                                <svg
                                    class="mx-auto h-12 w-12 text-gray-400"
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
                                <h3 class="mt-2 text-sm font-medium text-gray-900">
                                    {{ t('folderDetail.searchEmpty.title', { searchTerm: filters.search }) }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ t('folderDetail.searchEmpty.description') }}
                                </p>
                            </div>

                            <div v-else-if="isLoading" class="text-center py-12">
                                <div
                                    class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"
                                ></div>
                                <p class="mt-2 text-sm text-gray-500">{{ t('folderDetail.loading') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
    </InstructorLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toast-notification'
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashRemove from '@/components/atoms/icons/DashRemove.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'FolderDetail',
    components: {
        InstructorLayout,
        AyButton,
        ResponsiveButton,
        DashRemove,
    },
    setup() {
        const { t } = useI18n()
        const $toast = useToast()
        return { t, $toast }
    },
    data() {
        return {
            folder: null,
            items: [], // Use a generic 'items' array
            isLoading: false,
            filters: {
                search: '',
                page: 1,
            },
            pagination: {
                current_page: 1,
                from: 0,
                to: 0,
                total: 0,
                last_page: 1,
            },
            itemsPerPage: 12,
            searchTimeout: null,
        }
    },
    computed: {
        folderIdFromRoute() {
            return this.$route.params.id ? parseInt(this.$route.params.id, 10) : null
        },
        filteredItems() {
            let itemsToFilter = [...this.items]
            if (this.filters.search) {
                const query = this.filters.search.toLowerCase()
                itemsToFilter = itemsToFilter.filter((item) => (item.title || '').toLowerCase().includes(query))
            }
            this.pagination.total = itemsToFilter.length
            this.pagination.last_page = Math.ceil(itemsToFilter.length / this.itemsPerPage) || 1
            if (this.filters.page > this.pagination.last_page) {
                this.filters.page = this.pagination.last_page || 1
            }
            this.pagination.current_page = this.filters.page
            const start = (this.pagination.current_page - 1) * this.itemsPerPage
            const end = start + this.itemsPerPage
            this.pagination.from = itemsToFilter.length > 0 ? start + 1 : 0
            this.pagination.to = Math.min(end, itemsToFilter.length)
            return itemsToFilter.slice(start, end)
        },
    },
    methods: {
        async fetchFolderDetail(idToFetch) {
            const currentFolderId = idToFetch || this.folderIdFromRoute
            if (!currentFolderId) {
                this.$toast.error(this.t('folderDetail.notifications.idMissing'))
                this.$router.push(`/${this.$role}/exam/folders`)
                return
            }
            this.isLoading = true
            try {
                const response = await apiCall('GET', `/api/instructor/folder/${currentFolderId}`)
                this.folder = response.folder
                const exams = (response.folder?.exams || []).map((exam) => ({ ...exam, type: 'exam' }))
                const courses = (response.folder?.courses || []).map((course) => ({ ...course, type: 'course' }))
                this.items = [...exams, ...courses]
                this.filters.page = 1
            } catch (error) {
                console.error('Error fetching folder details:', error)
                this.$toast.error(error.message || this.t('folderDetail.notifications.loadFailed'))
                this.folder = null
                this.items = []
            } finally {
                this.isLoading = false
            }
        },
        handleSearch() {
            if (this.searchTimeout) clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.filters.page = 1
            }, 300)
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page && page !== this.filters.page) {
                this.filters.page = page
            }
        },
        async confirmUnassignItem(item) {
            if (!this.folder || !this.folder.id) {
                this.$toast.error(this.t('folderDetail.notifications.contextMissing'))
                return
            }
            if (
                await confirmDialog({
                    message: this.t('folderDetail.notifications.confirmRemove', {
                        itemTitle: item.title,
                        folderName: this.folder.name,
                    }),
                    variant: 'danger',
                })
            ) {
                this.isLoading = true
                try {
                    if (item.type === 'exam') {
                        await apiCall('POST', `/api/instructor/folder/${this.folder.id}/unassign-exams`, {
                            exam_ids: [item.id],
                        })
                    } else if (item.type === 'course') {
                        await apiCall('POST', `/api/instructor/folder/${this.folder.id}/unassign-courses`, {
                            course_ids: [item.id],
                        })
                    }
                    this.$toast.success(this.t('folderDetail.notifications.removeSuccess', { itemTitle: item.title }))
                    this.items = this.items.filter((i) => !(i.id === item.id && i.type === item.type))
                } catch (error) {
                    console.error(`Error unassigning ${item.type} from folder:`, error)
                    this.$toast.error(
                        error.message ||
                            this.t('folderDetail.notifications.removeFailed', {
                                itemType: t(`folderDetail.card.itemType.${item.type}`),
                            })
                    )
                } finally {
                    this.isLoading = false
                }
            }
        },
    },
    created() {
        if (this.folderIdFromRoute) {
            this.fetchFolderDetail(this.folderIdFromRoute)
        }
    },
    watch: {
        folderIdFromRoute(newId, oldId) {
            if (newId && newId !== oldId) {
                this.fetchFolderDetail(newId)
            }
        },
    },
}
</script>
