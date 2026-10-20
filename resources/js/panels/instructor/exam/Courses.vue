<template>
    <InstructorLayout>
                <div class="">
                    <!-- Tabs -->
                    <!-- <DashTabs :tabs="myTabs" :folders="folders" /> -->
                    <!-- Header Section -->
                    <div class="pb-4">
                        <h2 class="text-3xl font-bold mb-2">{{ t('course.title') }}</h2>
                        <p class="">{{ t('course.description') }}</p>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <!-- Table Header with Filters -->
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                                <!-- Left: Search & Filters -->
                                <div
                                    class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto"
                                >
                                    <!-- Search -->
                                    <div class="w-full sm:flex-1">
                                        <AyInput
                                            type="text"
                                            v-model="filters.search"
                                            @update:model-value="handleSearch"
                                            :placeholder="t('course.searchPlaceholder')"
                                        >
                                            <template #icon>
                                                <svg
                                                    class="h-5 w-5 text-gray-400"
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
                                    </div>

                                    <!-- Status Filter -->
                                    <AySelect
                                        v-model="filters.status"
                                        @update:modelValue="fetchCourses"
                                        class="w-full sm:w-auto"
                                    >
                                        <option value="">{{ t('course.filters.allStatus') }}</option>
                                        <option value="active">{{ t('course.filters.active') }}</option>
                                        <option value="draft">{{ t('course.filters.draft') }}</option>
                                        <option value="completed">{{ t('course.filters.completed') }}</option>
                                    </AySelect>

                                    <!-- Sort By -->
                                    <AySelect
                                        v-model="filters.sort"
                                        @update:modelValue="fetchCourses"
                                        class="w-full sm:w-auto"
                                    >
                                        <option value="created_at">{{ t('course.filters.dateCreated') }}</option>
                                        <option value="title">{{ t('course.filters.title') }}</option>
                                        <option value="status">{{ t('course.filters.status') }}</option>
                                    </AySelect>
                                </div>

                                <AyButton
                                    :to="`/${$role}/course/create`"
                                    type="dash-blue"
                                    class="w-full sm:w-auto inline-flex items-center justify-center sm:justify-start"
                                >
                                    {{ t('course.create') }}
                                </AyButton>
                            </div>
                        </div>

                        <!-- Table Content -->
                        <div class="overflow-x-auto">
                            <DataTable
                                v-if="filteredCourses.length && !isLoading"
                                :columns="courseColumns"
                                :rows="filteredCourses"
                                wrapper-class=""
                                row-class="hover:bg-gray-50 transition-colors duration-150"
                            >
                                <template #header:actions="{ column }">
                                    <span class="sr-only">{{ column.label }}</span>
                                </template>
                                <template #cell:details="{ row: course }">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 text-purple-700 h-12 w-12 bg-purple-100 rounded-xl flex items-center justify-center"
                                        >
                                            <svg
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    d="M19 11V8.2C19 7.0799 19 6.51984 18.782 6.09202C18.5903 5.71569 18.2843 5.40973 17.908 5.21799C17.4802 5 16.9201 5 15.8 5H7.2C6.0799 5 5.51984 5 5.09202 5.21799C4.71569 5.40973 4.40973 5.71569 4.21799 6.09202C4 6.51984 4 7.07989 4 8.2V13.8C4 14.9201 4 15.4802 4.21799 15.908C4.40973 16.2843 4.71569 16.5903 5.09202 16.782C5.51984 17 6.07989 17 7.2 17H14"
                                                    class="stroke-current"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                />
                                                <path
                                                    d="M8 13H12"
                                                    class="stroke-current"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                />
                                                <path
                                                    d="M8 9H15"
                                                    class="stroke-current"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                />
                                                <circle
                                                    cx="18"
                                                    cy="15"
                                                    r="1"
                                                    class="stroke-current"
                                                    stroke-width="2"
                                                />
                                                <path
                                                    d="M20 20C20 20 19.5 19 18 19C16.5 19 16 20 16 20"
                                                    class="stroke-current"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                />
                                            </svg>
                                        </div>
                                        <div :class="$i18n.locale === 'fa' ? 'mr-4' : 'ml-4'">
                                            <div
                                                class="text-sm font-medium text-gray-900 truncate max-w-[16ch] overflow-hidden text-ellipsis"
                                                :title="course.title"
                                            >
                                                {{ course.title }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ formatDate(course.created_at) }}
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template #cell:status="{ row: course }">
                                    <StatusBadge
                                        :status="course.status"
                                        :base-class="'px-2 py-1 text-xs font-medium rounded-full capitalize'"
                                    >
                                        {{ t('course.filters.' + (course.status || 'unknown')) }}
                                    </StatusBadge>
                                </template>
                                <template #cell:category="{ row: course }">
                                    <div class="text-sm text-gray-900">
                                        {{
                                            course.category?.title ||
                                            course.category?.name ||
                                            course.subject ||
                                            t('course.table.body.notAvailable')
                                        }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ t('course.table.body.categoryLabel') }}
                                    </div>
                                </template>
                                <template #cell:ayhoots="{ row: course }">
                                    <div class="text-sm text-gray-900">
                                        {{ course.exams_count ?? course.exams?.length ?? 0 }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ t('course.table.body.ayhootsLabel') }}
                                    </div>
                                </template>
                                <template #cell:visibility="{ row: course }">
                                    <div class="text-sm text-gray-900 capitalize">
                                        {{ course.visibility || t('course.filters.public') }}
                                    </div>
                                </template>
                                <template #cell:actions="{ row: course }">
                                    <div class="flex items-center justify-end relative">
                                        <ResponsiveButton
                                            type="yellow"
                                            class="text-xs px-2 py-1 mx-0.5"
                                            @click="() => $router.push(`/${$role}/course/${course.id}/create`)"
                                        >
                                            {{ t('course.table.actions.edit') }}</ResponsiveButton
                                        >
                                        <ResponsiveButton
                                            type="light-gray"
                                            class="text-xs px-2 py-1 mx-0.5"
                                            @click="toggleDropdown(course.id, $event)"
                                        >
                                            {{ t('course.table.actions.more') }}
                                        </ResponsiveButton>
                                    </div>
                                </template>
                            </DataTable>

                            <div v-else-if="isLoading" class="text-center py-12">
                                <div
                                    class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"
                                ></div>
                                <p class="mt-2 text-sm text-gray-500">{{ t('course.loading') }}</p>
                            </div>

                            <div v-else class="text-center py-12">
                                <svg
                                    width="36"
                                    height="36"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    class="mx-auto"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M19 11V8.2C19 7.0799 19 6.51984 18.782 6.09202C18.5903 5.71569 18.2843 5.40973 17.908 5.21799C17.4802 5 16.9201 5 15.8 5H7.2C6.0799 5 5.51984 5 5.09202 5.21799C4.71569 5.40973 4.40973 5.71569 4.21799 6.09202C4 6.51984 4 7.07989 4 8.2V13.8C4 14.9201 4 15.4802 4.21799 15.908C4.40973 16.2843 4.71569 16.5903 5.09202 16.782C5.51984 17 6.07989 17 7.2 17H14"
                                        class="stroke-current"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                    />
                                    <path
                                        d="M8 13H12"
                                        class="stroke-current"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                    />
                                    <path
                                        d="M8 9H15"
                                        class="stroke-current"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                    />
                                    <circle cx="18" cy="15" r="1" class="stroke-current" stroke-width="1.5" />
                                    <path
                                        d="M20 20C20 20 19.5 19 18 19C16.5 19 16 20 16 20"
                                        class="stroke-current"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                    />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ t('course.empty.title') }}</h3>
                                <p class="mt-1 text-sm text-gray-500" v-if="filters.search">
                                    {{ t('course.empty.descriptionWithSearch') }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500" v-else>
                                    {{ t('course.empty.description') }}
                                </p>
                                <div class="mt-6">
                                    <AyButton
                                        :to="`/${$role}/course/create`"
                                        type="dash-blue"
                                        class="inline-flex items-center"
                                    >
                                        {{ t('course.empty.create') }}
                                    </AyButton>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown -->
                        <div
                            v-if="dropdownOpen !== null"
                            :style="dropdownStyle"
                            class="fixed bg-white rounded-md shadow-lg border border-gray-200 z-[55] cursor-pointer"
                            :class="$i18n.locale === 'fa' ? 'text-right' : 'text-left'"
                            @click.stop="dropdownOpen = null"
                        >
                            <div
                                v-for="action in dropdownActions"
                                :key="action.label"
                                class="flex items-center w-full px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-100 transition-colors"
                                :class="$i18n.locale === 'fa' ? 'gap-reverse' : 'gap-2'"
                                @click="handleDropdownAction(action, selectedCourseId)"
                            >
                                <component :is="action.icon" class="w-4 h-4 text-gray-500" />
                                <span>{{ action.label }}</span>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <Pagination
                            v-if="!isLoading"
                            :pagination="pagination"
                            @page-changed="changePage"
                            :wrapper-class="'px-6 py-4 border-t border-gray-100'"
                        />
                    </div>

                    <!-- Assign Modal (Course Specific) -->
                    <div
                        v-if="showAssignModal"
                        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50"
                        @click.self="cancelAssign"
                    >
                        <div
                            class="bg-white w-full max-w-4xl mx-4 rounded-xl shadow-xl flex overflow-hidden"
                            @click.stop
                        >
                            <div
                                class="relative w-1/2 bg-cover bg-center hidden sm:block"
                                style="background-image: url('/imgHolders/CardHolderImage.jpg')"
                            >
                                <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center p-6">
                                    <p class="text-white text-2xl font-bold text-center">
                                        {{ t('course.assignModal.title') }}
                                    </p>
                                    <p class="text-white text-md font-semibold text-center mt-3">
                                        {{ t('course.assignModal.description') }}
                                    </p>
                                </div>
                            </div>
                            <div class="w-full sm:w-1/2">
                                <h3 class="text-xl font-semibold text-gray-900 p-6 border-b border-gray-200">
                                    {{ t('course.assignModal.formTitle') }}
                                </h3>
                                <form @submit.prevent="handleAssignSubmit" class="flex flex-col">
                                    <div class="p-6 bg-gray-50">
                                        <div
                                            class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0"
                                            :class="$i18n.locale === 'fa' ? 'sm:gap-reverse' : 'sm:gap-4'"
                                        >
                                            <div class="w-full sm:w-1/2">
                                                <label class="block font-semibold text-gray-700 mb-1 text-sm">{{
                                                    t('course.assignModal.dueDate')
                                                }}</label>
                                                <input
                                                    type="date"
                                                    v-model="assignForm.date"
                                                    required
                                                    class="w-full px-3 py-2 border-2 border-gray-300 rounded-sm focus:outline-none focus:border-blue-500 cursor-pointer text-sm"
                                                />
                                            </div>
                                            <div class="w-full sm:w-1/2">
                                                <label class="block font-semibold text-gray-700 mb-1 text-sm">{{
                                                    t('course.assignModal.time')
                                                }}</label>
                                                <input
                                                    type="time"
                                                    v-model="assignForm.time"
                                                    required
                                                    class="w-full px-3 py-2 border-2 border-gray-300 rounded-sm focus:outline-none focus:border-blue-500 cursor-pointer text-sm"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-6 space-y-3">
                                        <div class="text-gray-500 text-sm font-medium">
                                            {{ t('course.assignModal.optionsTitle') }}
                                        </div>
                                        <div class="flex items-center justify-between py-2">
                                            <span class="font-semibold text-gray-700 text-sm">{{
                                                t('course.assignModal.allowLate')
                                            }}</span>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    class="sr-only peer"
                                                    :checked="assignForm.allowLate === 'on'"
                                                    @change="
                                                        assignForm.allowLate = $event.target.checked ? 'on' : 'off'
                                                    "
                                                />
                                                <div
                                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:bg-blue-600 transition-all"
                                                ></div>
                                                <div
                                                    class="absolute bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5"
                                                    :class="$i18n.locale === 'fa' ? 'right-1 top-1' : 'left-1 top-1'"
                                                ></div>
                                            </label>
                                        </div>
                                        <hr class="border-gray-200" />
                                        <div class="flex items-center justify-between py-2">
                                            <span class="font-semibold text-gray-700 text-sm">{{
                                                t('course.assignModal.participantLimit')
                                            }}</span>
                                            <RouterLink
                                                to="#"
                                                class="underline text-blue-500 text-sm hover:text-blue-600"
                                                >{{ t('course.assignModal.upgrade') }}
                                            </RouterLink>
                                        </div>
                                    </div>
                                    <div
                                        class="flex gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200"
                                        :class="$i18n.locale === 'fa' ? 'justify-start' : 'justify-end'"
                                    >
                                        <button
                                            type="button"
                                            @click="cancelAssign"
                                            class="text-[#0F1D42] bg-[#E0E0E0] hover:bg-[#C8C8C8] shadow-[0_2px_0_rgba(180,180,180,1)] hover:shadow-none hover:translate-y-[2px] px-4 py-2 rounded-md cursor-pointer font-semibold text-sm transition-all"
                                        >
                                            {{ t('course.assignModal.cancel') }}
                                        </button>
                                        <button
                                            type="submit"
                                            class="text-white bg-[#42C287] hover:bg-[#156B43] shadow-[0_2px_0_rgba(21,107,67,1)] hover:shadow-none hover:translate-y-[2px] px-4 py-2 rounded-md cursor-pointer font-semibold text-sm transition-all"
                                        >
                                            {{ t('course.assignModal.create') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Move to Folder Modal -->
                    <Modal
                        v-model="showMoveToFolderModal"
                        :title="t('course.moveFolderModal.title')"
                        size="sm"
                        body-class=""
                        @close="closeMoveToFolderModal"
                    >
                        <div>
                            <div class="p-6">
                                <div v-if="isLoadingFolders" class="text-center py-6">
                                    <div
                                        class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"
                                    ></div>
                                    <p class="mt-3 text-sm text-gray-500">{{ t('course.moveFolderModal.loading') }}</p>
                                </div>
                                <div
                                    v-else-if="foldersForMove.length > 0"
                                    class="max-h-72 overflow-y-auto custom-scrollbar pr-2 space-y-1"
                                >
                                    <ul class="divide-y divide-gray-100">
                                        <li
                                            v-for="folder in foldersForMove"
                                            :key="folder.id"
                                            class="flex justify-between items-center py-3"
                                        >
                                            <span class="text-sm text-gray-700 font-medium">{{ folder.name }}</span>
                                            <button
                                                @click="
                                                    !folder.isCourseAlreadyInFolder &&
                                                    handleAssignCourseToFolder(folder)
                                                "
                                                :disabled="
                                                    folder.isCourseAlreadyInFolder || isAssigningToFolder === folder.id
                                                "
                                                class="text-xs px-3 py-1.5 rounded-md font-semibold transition-all duration-150"
                                                :class="{
                                                    'bg-blue-500 hover:bg-blue-600 text-white':
                                                        !folder.isCourseAlreadyInFolder &&
                                                        !(isAssigningToFolder === folder.id),
                                                    'bg-gray-200 text-gray-500 cursor-not-allowed':
                                                        folder.isCourseAlreadyInFolder,
                                                    'bg-blue-300 text-white cursor-wait':
                                                        !folder.isCourseAlreadyInFolder &&
                                                        isAssigningToFolder === folder.id,
                                                }"
                                            >
                                                <span v-if="isAssigningToFolder === folder.id">{{
                                                    t('course.moveFolderModal.adding')
                                                }}</span>
                                                <span v-else-if="folder.isCourseAlreadyInFolder">{{
                                                    t('course.moveFolderModal.added')
                                                }}</span>
                                                <span v-else>{{ t('course.moveFolderModal.add') }}</span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <div v-else class="text-center py-6">
                                    <svg
                                        class="mx-auto h-12 w-12 text-gray-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"
                                        />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-600 font-medium">
                                        {{ t('course.moveFolderModal.empty.title') }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-400">
                                        {{ t('course.moveFolderModal.empty.description') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <template #footer>
                            <AyButton type="light-gray" @click="closeMoveToFolderModal">
                                {{ t('course.moveFolderModal.done') }}
                            </AyButton>
                        </template>
                    </Modal>
                </div>
    </InstructorLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useI18n } from 'vue-i18n'
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashDuplicate from '@/components/atoms/icons/DashDuplicate.vue'
import DashRemove from '@/components/atoms/icons/DashRemove.vue'
import DashShare from '@/components/atoms/icons/DashShare.vue'
import DashMoveFolder from '@/components/atoms/icons/DashMoveFolder.vue'
import DashArchive from '@/components/atoms/icons/DashArchive.vue'
import DashTabs from '@/components/layout/InstructorDashTabs.vue'
import DataTable from '@/components/shared/DataTable.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import Pagination from '@/components/shared/Pagination.vue'
import Modal from '@/components/shared/Modal.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import { RouterLink } from 'vue-router'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'CoursesIndex',
    components: {
        InstructorLayout,
        AyButton,
        ResponsiveButton,
        DashDuplicate,
        DashRemove,
        DashShare,
        DashMoveFolder,
        DashArchive,
        DashTabs,
        DataTable,
        StatusBadge,
        Pagination,
        Modal,
        AySelect,
        AyInput,
        RouterLink,
    },
    setup() {
        const { t } = useI18n()
        return { t }
    },
    data() {
        return {
            dropdownOpen: null,
            dropdownStyle: {},
            selectedCourseId: null,
            courses: [],
            isLoading: true,
            filters: {
                search: '',
                status: '',
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
            folders: [],
            showAssignModal: false,
            selectedAssignCourseId: null,
            assignForm: {
                date: '',
                time: '',
                allowLate: 'off',
            },
            showMoveToFolderModal: false,
            foldersForMove: [],
            courseToMoveId: null,
            isLoadingFolders: false,
            isAssigningToFolder: null,
        }
    },
    computed: {
        courseColumns() {
            const actionsAlign = this.$i18n.locale === 'fa' ? 'left' : 'right'
            return [
                { key: 'details', label: this.t('course.table.header.details'), thClass: 'py-2 font-medium' },
                { key: 'status', label: this.t('course.table.header.status'), thClass: 'font-medium' },
                { key: 'category', label: this.t('course.table.header.category'), thClass: 'font-medium' },
                { key: 'ayhoots', label: this.t('course.table.header.ayhoots'), thClass: 'font-medium' },
                { key: 'visibility', label: this.t('course.table.header.visibility'), thClass: 'font-medium' },
                { key: 'actions', label: this.t('course.table.header.actions'), align: actionsAlign, thClass: 'relative', tdClass: 'font-medium' },
            ]
        },
        myTabs() {
            return [
                { label: this.t('ayhoot.tabs.ayhoots'), route: `/${this.$role}/exam` },
                { label: this.t('ayhoot.tabs.courses'), route: `/${this.$role}/exam/courses` },
                { label: this.t('ayhoot.tabs.purchased'), route: `/${this.$role}/exam/purchased` },
                { label: this.t('ayhoot.tabs.folders'), route: `/${this.$role}/exam/folders` },
                { label: this.t('ayhoot.tabs.archive'), route: `/${this.$role}/exam/archive` },
            ]
        },
        dropdownActions() {
            return [
                {
                    label: this.t('course.dropdown.moveFolder'),
                    icon: DashMoveFolder,
                    action: 'moveFolder',
                },
                {
                    label: this.t('course.dropdown.archive'),
                    icon: DashArchive,
                    action: 'archiveCourse',
                },
                { label: this.t('course.dropdown.share'), icon: DashShare, action: 'shareCourse' },
                {
                    label: this.t('course.dropdown.duplicate'),
                    icon: DashDuplicate,
                    action: 'duplicateCourse',
                },
                {
                    label: this.t('course.dropdown.delete'),
                    icon: DashRemove,
                    action: 'deleteCourse',
                },
            ]
        },
        filteredCourses() {
            let coursesToFilter = Array.isArray(this.courses) ? [...this.courses] : []

            if (this.filters.search) {
                const query = this.filters.search.toLowerCase()
                coursesToFilter = coursesToFilter.filter(
                    (course) =>
                        (course.title && course.title.toLowerCase().includes(query)) ||
                        (course.subject && course.subject.toLowerCase().includes(query))
                )
            }

            if (this.filters.status) {
                coursesToFilter = coursesToFilter.filter((course) => course.status === this.filters.status)
            }

            coursesToFilter.sort((a, b) => {
                if (this.filters.sort === 'title') {
                    return (a.title || '').localeCompare(b.title || '')
                } else if (this.filters.sort === 'status') {
                    return (a.status || '').localeCompare(b.status || '')
                } else {
                    return new Date(b.created_at) - new Date(a.created_at)
                }
            })
            return coursesToFilter
        },
    },
    methods: {
        async fetchFolders() {
            try {
                const response = await apiCall('GET', '/api/instructor/folder')
                this.folders = response.folders || []
            } catch (error) {
                console.error('Error fetching folders for tabs:', error)
            }
        },
        async fetchCourses() {
            this.isLoading = true
            try {
                const queryParams = new URLSearchParams({
                    page: this.filters.page,
                    search: this.filters.search,
                    status: this.filters.status,
                    sort: this.filters.sort,
                }).toString()
                const response = await apiCall('GET', `/api/instructor/course?${queryParams}`)

                if (response && Array.isArray(response.courses)) {
                    this.courses = response.courses
                    this.pagination = {
                        current_page: this.filters.page,
                        from: this.courses.length > 0 ? 1 : 0,
                        to: this.courses.length,
                        total: this.courses.length,
                        last_page: 1,
                    }
                } else if (response.courses && response.courses.data && Array.isArray(response.courses.data)) {
                    this.courses = response.courses.data
                    this.pagination = {
                        current_page: response.courses.current_page,
                        from: response.courses.from || 0,
                        to: response.courses.to || 0,
                        total: response.courses.total || 0,
                        last_page: response.courses.last_page || 1,
                    }
                } else {
                    this.courses = []
                    this.pagination = { current_page: 1, from: 0, to: 0, total: 0, last_page: 1 }
                    this.$toast.error(this.t('course.notifications.dataFormatError'))
                    console.warn('Unexpected API response structure for courses:', response)
                }
            } catch (error) {
                console.error('Error fetching courses:', error)
                this.courses = []
                this.pagination = { current_page: 1, from: 0, to: 0, total: 0, last_page: 1 }
                this.$toast.error(error.message || this.t('course.notifications.loadFailed'))
            } finally {
                this.isLoading = false
            }
        },
        handleSearch() {
            if (this.searchTimeout) clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.filters.page = 1
                this.fetchCourses()
            }, 500)
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page && page !== this.filters.page) {
                this.filters.page = page
                this.fetchCourses()
            }
        },
        formatDate(dateString) {
            if (!dateString) return this.t('course.table.body.notAvailable')
            try {
                const locale = this.$i18n.locale === 'fa' ? 'fa-IR' : 'en-US'
                return new Date(dateString).toLocaleDateString(locale, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                })
            } catch (e) {
                return this.t('course.table.body.invalidDate')
            }
        },
        // --- START: Updated Method ---
        toggleDropdown(courseId, event) {
            event.stopPropagation()
            if (this.dropdownOpen === courseId) {
                this.dropdownOpen = null
                this.selectedCourseId = null
            } else {
                const button = event.currentTarget
                const buttonRect = button.getBoundingClientRect()
                const windowHeight = window.innerHeight
                // A reasonable estimate for the dropdown's height, including padding.
                const DROPDOWN_HEIGHT_ESTIMATE = 200
                const DROPDOWN_WIDTH = 160 // The min-width set on the dropdown
                let topStyle = 'auto'
                let bottomStyle = 'auto'
                let leftStyle = 'auto'
                let rightStyle = 'auto'

                // Check if there's enough space below. If not, and there's space above, open upwards.
                if (
                    windowHeight - buttonRect.bottom < DROPDOWN_HEIGHT_ESTIMATE &&
                    buttonRect.top > DROPDOWN_HEIGHT_ESTIMATE
                ) {
                    // Open upwards
                    bottomStyle = `${windowHeight - buttonRect.top + 4}px`
                } else {
                    // Open downwards (default)
                    topStyle = `${buttonRect.bottom + 4}px`
                }

                // Handle LTR vs RTL positioning
                if (this.$i18n.locale === 'fa') {
                    // For RTL, align the dropdown's left edge with the button's left edge.
                    leftStyle = `${buttonRect.left}px`
                } else {
                    // For LTR, align the dropdown's right edge with the button's right edge.
                    leftStyle = `${buttonRect.right - DROPDOWN_WIDTH}px`
                }

                this.dropdownStyle = {
                    position: 'fixed',
                    top: topStyle,
                    bottom: bottomStyle,
                    left: leftStyle,
                    right: rightStyle,
                    minWidth: `${DROPDOWN_WIDTH}px`,
                }

                this.dropdownOpen = courseId
                this.selectedCourseId = courseId
            }
        },
        // --- END: Updated Method ---
        closeDropdown() {
            this.dropdownOpen = null
            this.selectedCourseId = null
        },
        handleDropdownAction(action, courseId) {
            this.closeDropdown() // Close dropdown immediately after action click
            switch (action.action) {
                case 'moveFolder':
                    this.openMoveToFolderModal(courseId)
                    break
                case 'archiveCourse':
                    this.archiveCourse(courseId)
                    break
                case 'shareCourse':
                    this.shareCourse(courseId)
                    break
                case 'duplicateCourse':
                    this.duplicateCourse(courseId)
                    break
                case 'deleteCourse':
                    this.deleteCourse(courseId)
                    break
                default:
                    console.warn('Unknown dropdown action:', action.action)
            }
        },
        async archiveCourse(id) {
            await apiCall('GET', `/api/instructor/course/archive/${id}`)
            this.$toast.success(this.t('course.notifications.duplicateSuccess'))
            await this.fetchCourses()
        },
        shareCourse(id) {
            console.log('Action: Share course', id)
            this.$toast.info(this.t('course.notifications.shareNotImplemented'))
        },
        async duplicateCourse(courseId) {
            try {
                await apiCall('GET', `/api/instructor/course/duplicate/${courseId}`)
                this.$toast.success(this.t('course.notifications.duplicateSuccess'))
                await this.fetchCourses()
            } catch (error) {
                this.$toast.error(this.t('course.notifications.duplicateFailed'))
            }
        },
        async deleteCourse(courseId) {
            if (await confirmDialog({ message: this.t('course.notifications.confirmDelete'), variant: 'danger' })) {
                try {
                    await apiCall('GET', `/api/instructor/course/delete/${courseId}`)
                    this.$toast.success(this.t('course.notifications.deleteSuccess'))
                    await this.fetchCourses()
                } catch (error) {
                    console.error('Error deleting course:', error)
                    this.$toast.error(error.message || this.t('course.notifications.deleteFailed'))
                }
            }
        },
        openAssignModal(courseId) {
            this.selectedAssignCourseId = courseId
            this.assignForm = { date: '', time: '', allowLate: 'off' }
            this.showAssignModal = true
        },
        cancelAssign() {
            this.showAssignModal = false
            this.selectedAssignCourseId = null
        },
        async handleAssignSubmit() {
            if (!this.assignForm.date || !this.assignForm.time) {
                this.$toast.error(this.t('course.notifications.dueDateRequired'))
                return
            }
            if (!this.selectedAssignCourseId) {
                this.$toast.error(this.t('course.notifications.noCourseSelected'))
                return
            }
            const payload = {
                course_id: this.selectedAssignCourseId,
                end_at: `${this.assignForm.date} ${this.assignForm.time}:00`,
                allow_late_submission: this.assignForm.allowLate,
            }
            try {
                const response = await apiCall('POST', '/api/instructor/game', payload)
                const gameId = response.id || response.game_id || response.game?.id
                if (!gameId) throw new Error(this.t('course.notifications.gameIdNotFound'))
                this.$toast.success(this.t('course.notifications.assignSuccess'))
                this.cancelAssign()
                this.$router.push(`/${this.$role}/report/assigned-courses/${gameId}`)
            } catch (error) {
                console.error('Error creating game (assigning course):', error)
                this.$toast.error(error.message || this.t('course.notifications.assignFailed'))
            }
        },
        openMoveToFolderModal(courseId) {
            this.courseToMoveId = courseId
            this.showMoveToFolderModal = true
            this.fetchFoldersForMove()
        },
        closeMoveToFolderModal() {
            this.showMoveToFolderModal = false
            this.foldersForMove = []
            this.courseToMoveId = null
            this.isAssigningToFolder = null
        },
        async fetchFoldersForMove() {
            this.isLoadingFolders = true
            this.foldersForMove = []
            try {
                const response = await apiCall('GET', '/api/instructor/folder')
                let rawFolders = []
                if (Array.isArray(response)) rawFolders = response
                else if (response?.data && Array.isArray(response.data)) rawFolders = response.data
                else if (response?.folders && Array.isArray(response.folders)) rawFolders = response.folders
                else if (response?.data?.data && Array.isArray(response.data.data)) rawFolders = response.data.data
                else console.warn('Folder API structure issue:', response)
                this.foldersForMove = rawFolders.map((folder) => ({
                    ...folder,
                    isCourseAlreadyInFolder:
                        folder.courses?.some((c) => c.id === this.courseToMoveId) ||
                        folder.course_ids?.includes(this.courseToMoveId) ||
                        false,
                }))
            } catch (error) {
                console.error('Error fetching folders for move:', error)
                this.$toast.error(error.message || this.t('course.notifications.loadFoldersFailed'))
            } finally {
                this.isLoadingFolders = false
            }
        },
        async handleAssignCourseToFolder(folder) {
            if (!this.courseToMoveId || !folder || folder.isCourseAlreadyInFolder) return
            this.isAssigningToFolder = folder.id
            try {
                await apiCall('GET', `/api/instructor/course/${this.courseToMoveId}/move-to-folder/${folder.id}`)
                this.$toast.success(this.t('course.notifications.moveToFolderSuccess', { folderName: folder.name }))
                const folderInList = this.foldersForMove.find((f) => f.id === folder.id)
                if (folderInList) {
                    folderInList.isCourseAlreadyInFolder = true
                    if (!folderInList.courses) folderInList.courses = []
                    if (!folderInList.courses.some((c) => c.id === this.courseToMoveId))
                        folderInList.courses.push({ id: this.courseToMoveId })
                }
                await this.fetchCourses()
            } catch (error) {
                console.error('Error assigning course to folder:', error)
                this.$toast.error(error.message || this.t('course.notifications.moveToFolderFailed'))
            } finally {
                this.isAssigningToFolder = null
            }
        },
    },
    async mounted() {
        await this.fetchFolders()
        await this.fetchCourses()
        document.addEventListener('click', this.closeDropdown)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.closeDropdown)
        if (this.searchTimeout) clearTimeout(this.searchTimeout)
    },
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

.xl\:ml-128 {
    margin-left: 32rem;
}

.xl\:ms-64 {
    margin-right: 16rem;
}

@media (max-width: 1279px) {
    .xl\:ml-128,
    .xl\:ms-64 {
        margin-left: 0;
        margin-right: 0;
    }
}
</style>
