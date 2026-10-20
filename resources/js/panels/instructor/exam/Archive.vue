<template>
    <InstructorLayout>
                <div class="">
                    <!-- Tabs -->
                    <!-- <DashTabs :tabs="myTabs" :folders="folders" /> -->
                    <!-- Header Section -->
                    <div class="pb-4">
                        <h2 class="text-3xl font-bold mb-2">{{ $t('ayhoot.archive.title') }}</h2>
                        <p class="">{{ $t('ayhoot.archive.description') }}</p>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <!-- Table Header with Filters -->
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                                <!-- Left: Search & Filters -->
                                <div
                                    class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto">
                                    <!-- Search -->
                                    <div class="w-full sm:flex-1">
                                        <AyInput v-model="filters.search" type="text"
                                            :placeholder="$t('ayhoot.searchPlaceholder')"
                                            @update:model-value="handleSearch">
                                            <template #icon>
                                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            </template>
                                        </AyInput>
                                    </div>

                                    <!-- Status Filter -->
                                    <AySelect v-model="filters.status" @update:modelValue="fetchExams"
                                        class="w-full sm:w-auto">
                                        <option value="">{{ $t('ayhoot.filters.allStatus') }}</option>
                                        <option value="pending">{{ $t('ayhoot.filters.pending') }}</option>
                                        <option value="published">{{ $t('ayhoot.filters.published') }}</option>
                                        <option value="rejected">{{ $t('ayhoot.filters.rejected') }}</option>
                                    </AySelect>

                                    <!-- Sort By -->
                                    <AySelect v-model="filters.sort" @update:modelValue="fetchExams"
                                        class="w-full sm:w-auto">
                                        <option value="created_at">{{ $t('ayhoot.filters.dateCreated') }}</option>
                                        <option value="title">{{ $t('ayhoot.filters.title') }}</option>
                                        <option value="status">{{ $t('ayhoot.filters.status') }}</option>
                                    </AySelect>
                                </div>
                            </div>
                        </div>

                        <!-- Table Content -->
                        <div class="overflow-x-auto">
                            <DataTable v-if="filteredExams.length && !isLoading" :columns="examColumns"
                                :rows="filteredExams" wrapper-class=""
                                row-class="hover:bg-gray-50 transition-colors duration-150">
                                <template #header:actions="{ column }">
                                    <span class="sr-only">{{ column.label }}</span>
                                </template>
                                <template #cell:details="{ row: exam }">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-12 w-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                            <svg class="w-5.5 h-5.5 text-blue-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div :class="$i18n.locale === 'fa' ? 'mr-4' : 'ml-4'">
                                            <div class="text-sm font-medium text-gray-900 truncate max-w-[16ch] overflow-hidden text-ellipsis"
                                                :title="exam.title">
                                                {{ exam.title }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ formatDate(exam.created_at) }}
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template #cell:status="{ row: exam }">
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                        :class="{
                                            'bg-green-100 text-green-800':
                                                exam.status === 'published' || exam.status === 'active',
                                            'bg-yellow-100 text-yellow-800':
                                                exam.status === 'draft' || exam.status === 'pending',
                                            'bg-gray-100 text-gray-800': exam.status === 'completed',
                                            'bg-red-100 text-red-800':
                                                exam.status === 'archived' || exam.status === 'rejected',
                                        }">
                                        {{ $t('ayhoot.filters.' + exam.status) }}
                                    </span>
                                </template>
                                <template #cell:category="{ row: exam }">
                                    <div class="text-sm text-gray-900">
                                        {{ exam?.category?.title || $t('ayhoot.table.body.noCategory') }}
                                    </div>
                                    <div v-if="exam?.category?.title" class="text-sm text-gray-500">
                                        {{ $t('ayhoot.table.body.categoryLabel') }}
                                    </div>
                                </template>
                                <template #cell:plays="{ row: exam }">
                                    <div class="text-sm text-gray-900">
                                        {{ exam.students_count || 0 }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $t('ayhoot.table.body.tutees') }}
                                    </div>
                                </template>
                                <template #cell:questions="{ row: exam }">
                                    <div class="text-sm text-gray-900">
                                        {{ exam.questions_count || 0 }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $t('ayhoot.table.body.questions') }}
                                    </div>
                                </template>
                                <template #cell:accessibility="{ row: exam }">
                                    <div v-if="exam.monetize === 'free'" class="text-sm text-gray-900">
                                        {{ $t('ayhoot.table.body.free') }}
                                    </div>
                                    <div v-else class="text-sm text-gray-900">
                                        {{ exam.price ? '$' + exam.price : $t('ayhoot.table.body.notForSale') }}
                                    </div>
                                </template>
                                <template #cell:actions="{ row: exam }">
                                    <div class="flex items-center justify-end relative">
                                        <ResponsiveButton type="light-gray" class="text-xs px-2 py-1 mx-0.5"
                                            @click="toggleDropdown(exam.id, $event)">
                                            {{ $t('ayhoot.table.actions.more') }}
                                        </ResponsiveButton>
                                    </div>
                                </template>
                            </DataTable>

                            <div v-else-if="isLoading" class="text-center py-12">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900">
                                </div>
                                <p class="mt-2 text-sm text-gray-500">{{ $t('ayhoot.loading') }}</p>
                            </div>

                            <div v-else class="text-center py-12">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" class="mx-auto"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M15.0001 2.3999V5.9999C15.0001 6.66264 15.5373 7.1999 16.2001 7.1999H19.8001M8.40008 7.1999H10.8001M8.40008 10.7999H15.6001M8.40008 14.3999H15.6001M18.0001 4.1999C17.466 3.72204 16.9118 3.15528 16.5619 2.78718C16.3291 2.54224 16.0074 2.3999 15.6695 2.3999H6.5998C5.27432 2.3999 4.19981 3.47441 4.1998 4.79989L4.19971 19.1998C4.1997 20.5253 5.27421 21.5998 6.5997 21.5998L17.3997 21.5999C18.7252 21.5999 19.7997 20.5254 19.7998 19.2L19.8001 6.47773C19.8001 6.1709 19.683 5.87594 19.4701 5.65503C19.0763 5.24655 18.4187 4.57442 18.0001 4.1999Z"
                                        class="stroke-current" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $t('ayhoot.empty.title') }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ $t('ayhoot.archive.emptyDescription') }}</p>
                                <div class="mt-6"></div>
                            </div>
                        </div>

                        <!-- Dropdown -->
                        <div v-if="dropdownOpen !== null" :style="dropdownStyle"
                            class="fixed bg-white rounded-md shadow-lg border border-gray-200 z-[55] cursor-pointer"
                            :class="$i18n.locale === 'fa' ? 'text-right' : 'text-left'"
                            @click.stop="dropdownOpen = null">
                            <div v-for="action in dropdownActions" :key="action.label"
                                class="flex items-center w-full px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-100 transition-colors"
                                :class="$i18n.locale === 'fa' ? 'gap-reverse' : 'gap-2'"
                                @click="handleDropdownAction(action, selectedExamId)">
                                <component :is="action.icon" class="w-4 h-4 text-gray-500" />
                                <span>{{ action.label }}</span>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <Pagination v-if="!isLoading" :pagination="pagination" @page-changed="changePage"
                            :wrapper-class="'px-6 py-4 border-t border-gray-100'" />
                    </div>

                    <!-- Assign Modal -->
                    <div v-if="showAssignModal"
                        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50"
                        @click.self="cancelAssign">
                        <div class="bg-white w-full max-w-4xl mx-4 rounded-xl shadow-xl flex overflow-hidden"
                            @click.stop>
                            <div class="relative w-1/2 bg-cover bg-center hidden sm:block"
                                style="background-image: url('/imgHolders/CardHolderImage.jpg')">
                                <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center p-6">
                                    <p class="text-white text-2xl font-bold text-center">
                                        {{ $t('ayhoot.assignModal.title') }}
                                    </p>
                                    <p class="text-white text-md font-semibold text-center mt-3">
                                        {{ $t('ayhoot.assignModal.description') }}
                                    </p>
                                </div>
                            </div>
                            <div class="w-full sm:w-1/2">
                                <h3 class="text-xl font-semibold text-gray-900 p-6 border-b border-gray-200">
                                    {{ $t('ayhoot.assignModal.formTitle') }}
                                </h3>
                                <form @submit.prevent="handleAssignSubmit" class="flex flex-col">
                                    <div class="p-6 bg-gray-50">
                                        <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0"
                                            :class="$i18n.locale === 'fa' ? 'sm:gap-reverse' : 'sm:gap-4'">
                                            <div class="w-full sm:w-1/2">
                                                <label class="block font-semibold text-gray-700 mb-1 text-sm">{{
                                                    $t('ayhoot.assignModal.dueDate')
                                                    }}</label>
                                                <input type="date" v-model="assignForm.date" required
                                                    class="w-full px-3 py-2 border-2 border-gray-300 rounded-sm focus:outline-none focus:border-blue-500 cursor-pointer text-sm" />
                                            </div>
                                            <div class="w-full sm:w-1/2">
                                                <label class="block font-semibold text-gray-700 mb-1 text-sm">{{
                                                    $t('ayhoot.assignModal.time')
                                                    }}</label>
                                                <input type="time" v-model="assignForm.time" required
                                                    class="w-full px-3 py-2 border-2 border-gray-300 rounded-sm focus:outline-none focus:border-blue-500 cursor-pointer text-sm" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-6 space-y-3">
                                        <div class="text-gray-500 text-sm font-medium">
                                            {{ $t('ayhoot.assignModal.optionsTitle') }}
                                        </div>
                                        <div class="flex items-center justify-between py-2">
                                            <span class="font-semibold text-gray-700 text-sm">{{
                                                $t('ayhoot.assignModal.questionTimer')
                                                }}</span>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="sr-only peer"
                                                    :checked="assignForm.questionTime === 'on'" @change="
                                                        assignForm.questionTime = $event.target.checked ? 'on' : 'off'
                                                        " />
                                                <div
                                                    class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition-all peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500">
                                                </div>
                                                <div class="absolute bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5"
                                                    :class="$i18n.locale === 'fa' ? 'right-1 top-1' : 'left-1 top-1'">
                                                </div>
                                            </label>
                                        </div>
                                        <hr class="border-gray-200" />
                                        <div class="flex items-center justify-between py-2">
                                            <span class="font-semibold text-gray-700 text-sm">{{
                                                $t('ayhoot.assignModal.optionRandomize')
                                                }}</span>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="sr-only peer"
                                                    :checked="assignForm.optionRandomize === 'on'" @change="
                                                        assignForm.optionRandomize = $event.target.checked
                                                            ? 'on'
                                                            : 'off'
                                                        " />
                                                <div
                                                    class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition-all peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500">
                                                </div>
                                                <div class="absolute bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5"
                                                    :class="$i18n.locale === 'fa' ? 'right-1 top-1' : 'left-1 top-1'">
                                                </div>
                                            </label>
                                        </div>
                                        <hr class="border-gray-200" />
                                        <div class="flex items-center justify-between py-2">
                                            <span class="font-semibold text-gray-700 text-sm">{{
                                                $t('ayhoot.assignModal.participantLimit')
                                                }}</span>
                                            <RouterLink to="#"
                                                class="underline text-blue-500 text-sm hover:text-blue-600">{{
                                                $t('ayhoot.assignModal.upgrade') }}
                                            </RouterLink>
                                        </div>
                                    </div>
                                    <div class="flex gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200"
                                        :class="$i18n.locale === 'fa' ? 'justify-start' : 'justify-end'">
                                        <button type="button" @click="cancelAssign"
                                            class="text-[#0F1D42] bg-[#E0E0E0] hover:bg-[#C8C8C8] shadow-[0_2px_0_rgba(180,180,180,1)] hover:shadow-none hover:translate-y-[2px] px-4 py-2 rounded-md cursor-pointer font-semibold text-sm transition-all">
                                            {{ $t('ayhoot.assignModal.cancel') }}
                                        </button>
                                        <button type="submit"
                                            class="text-white bg-[#42C287] hover:bg-[#156B43] shadow-[0_2px_0_rgba(21,107,67,1)] hover:shadow-none hover:translate-y-[2px] px-4 py-2 rounded-md cursor-pointer font-semibold text-sm transition-all">
                                            {{ $t('ayhoot.assignModal.create') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Move to Folder Modal -->
                    <Modal v-model="showMoveToFolderModal" :title="$t('ayhoot.moveFolderModal.title')" size="sm"
                        body-class="" @close="closeMoveToFolderModal">
                        <div>
                            <div class="p-6">
                                <p class="text-xs text-gray-500 mb-4">{{ $t('ayhoot.moveFolderModal.description') }}</p>
                                <div class="mb-4">
                                    <AyInput v-model="folderSearchQuery"
                                        :placeholder="$t('ayhoot.moveFolderModal.searchPlaceholder')" />
                                </div>
                                <div v-if="isLoadingFolders" class="text-center py-6">
                                    <div
                                        class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500">
                                    </div>
                                    <p class="mt-3 text-sm text-gray-500">{{ $t('ayhoot.moveFolderModal.loading') }}</p>
                                </div>
                                <div v-else-if="filteredFoldersForMove.length > 0"
                                    class="max-h-72 overflow-y-auto custom-scrollbar pr-2 space-y-1">
                                    <ul class="divide-y divide-gray-100">
                                        <li v-for="folder in filteredFoldersForMove" :key="folder.id"
                                            class="flex justify-between border-b border-gray-300 items-center py-2">
                                            <span class="text-sm text-gray-700 font-medium truncate"
                                                :title="folder.name">
                                                {{ folder.name }}
                                            </span>
                                            <ResponsiveButton type="light-green" v-if="!folder.isExamAlreadyInFolder"
                                                @click="handleAddExamToFolder(folder)"
                                                :disabled="isAssigningToFolder === folder.id"
                                                class="text-xs px-3 py-1.5 rounded-md font-semibold" :class="{
                                                    'bg-blue-300 cursor-wait': isAssigningToFolder === folder.id,
                                                }">
                                                <span v-if="isAssigningToFolder === folder.id">{{
                                                    $t('ayhoot.moveFolderModal.adding')
                                                    }}</span>
                                                <span v-else>{{ $t('ayhoot.moveFolderModal.add') }}</span>
                                            </ResponsiveButton>
                                            <ResponsiveButton type="light-red" v-else
                                                @click="handleRemoveExamFromFolder(folder)"
                                                :disabled="isAssigningToFolder === folder.id"
                                                class="text-xs px-3 py-1.5 rounded-md font-semibold"
                                                :class="{ 'bg-red-300 cursor-wait': isAssigningToFolder === folder.id }">
                                                <span v-if="isAssigningToFolder === folder.id">{{
                                                    $t('ayhoot.moveFolderModal.removing')
                                                    }}</span>
                                                <span v-else>{{ $t('ayhoot.moveFolderModal.remove') }}</span>
                                            </ResponsiveButton>
                                        </li>
                                    </ul>
                                </div>
                                <div v-else class="text-center py-6">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-600 font-medium">
                                        {{
                                            folderSearchQuery
                                                ? $t('ayhoot.moveFolderModal.empty.titleWithSearch')
                                                : $t('ayhoot.moveFolderModal.empty.title')
                                        }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-400">
                                        {{ $t('ayhoot.moveFolderModal.empty.description') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <template #footer>
                            <AyButton type="light-gray" @click="closeMoveToFolderModal">
                                {{ $t('ayhoot.moveFolderModal.done') }}
                            </AyButton>
                        </template>
                    </Modal>

                    <!-- Add to Course Modal -->
                    <Modal v-model="showAddToCourseModal" :title="$t('ayhoot.addCourseModal.title')" size="sm"
                        body-class="" @close="closeAddToCourseModal">
                        <div>
                            <div class="p-6">
                                <p class="text-xs text-gray-500 mb-4">{{ $t('ayhoot.addCourseModal.description') }}</p>
                                <div class="mb-4">
                                    <AyInput v-model="courseSearchQuery"
                                        :placeholder="$t('ayhoot.addCourseModal.searchPlaceholder')" />
                                </div>
                                <div v-if="isLoadingCourses" class="text-center py-6">
                                    <div
                                        class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500">
                                    </div>
                                    <p class="mt-3 text-sm text-gray-500">{{ $t('ayhoot.addCourseModal.loading') }}</p>
                                </div>
                                <div v-else-if="filteredCoursesForAdd.length > 0"
                                    class="max-h-72 overflow-y-auto custom-scrollbar pr-2 space-y-1">
                                    <ul class="divide-y divide-gray-100">
                                        <li v-for="course in filteredCoursesForAdd" :key="course.id"
                                            class="flex justify-between items-center py-2 border-b border-gray-300">
                                            <span class="text-sm text-gray-700 font-medium truncate"
                                                :title="course.title || course.name">
                                                {{ course.title || course.name }}
                                            </span>
                                            <ResponsiveButton type="light-green" v-if="!course.isExamAlreadyInCourse"
                                                @click="handleAssignExamToCourse(course)"
                                                :disabled="isAssigningToCourse === course.id"
                                                class="text-xs px-3 py-1.5 rounded-md font-semibold e" :class="{
                                                    'bg-green-300 cursor-wait': isAssigningToCourse === course.id,
                                                }">
                                                <span v-if="isAssigningToCourse === course.id">{{
                                                    $t('ayhoot.addCourseModal.adding')
                                                    }}</span>
                                                <span v-else>{{ $t('ayhoot.addCourseModal.add') }}</span>
                                            </ResponsiveButton>
                                            <ResponsiveButton type="light-red" v-else
                                                @click="handleRemoveExamFromCourse(course)"
                                                :disabled="isAssigningToCourse === course.id"
                                                class="text-xs px-3 py-1.5 rounded-md font-semibold"
                                                :class="{ 'bg-red-300 cursor-wait': isAssigningToCourse === course.id }">
                                                <span v-if="isAssigningToCourse === course.id">{{
                                                    $t('ayhoot.addCourseModal.removing')
                                                    }}</span>
                                                <span v-else>{{ $t('ayhoot.addCourseModal.remove') }}</span>
                                            </ResponsiveButton>
                                        </li>
                                    </ul>
                                </div>
                                <div v-else class="text-center py-6">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-600 font-medium">
                                        {{
                                            courseSearchQuery
                                                ? $t('ayhoot.addCourseModal.empty.titleWithSearch')
                                                : $t('ayhoot.addCourseModal.empty.title')
                                        }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-400">
                                        {{ $t('ayhoot.addCourseModal.empty.description') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <template #footer>
                            <AyButton type="light-gray" @click="closeAddToCourseModal">
                                {{ $t('ayhoot.addCourseModal.done') }}
                            </AyButton>
                        </template>
                    </Modal>
                </div>
    </InstructorLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashDuplicate from '@/components/atoms/icons/DashDuplicate.vue'
import DashRemove from '@/components/atoms/icons/DashRemove.vue'
import DashShare from '@/components/atoms/icons/DashShare.vue'
import DashMoveFolder from '@/components/atoms/icons/DashMoveFolder.vue'
import DashAddCourse from '@/components/atoms/icons/DashAddCourse.vue'
import DashArchive from '@/components/atoms/icons/DashArchive.vue'
import DashTabs from '@/components/layout/InstructorDashTabs.vue'
import DataTable from '@/components/shared/DataTable.vue'
import Pagination from '@/components/shared/Pagination.vue'
import Modal from '@/components/shared/Modal.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import { RouterLink } from 'vue-router'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'ExamIndex',
    components: {
        InstructorLayout,
        AyButton,
        ResponsiveButton,
        DashDuplicate,
        DashRemove,
        DashShare,
        DashMoveFolder,
        DashArchive,
        DashAddCourse,
        DashTabs,
        DataTable,
        Pagination,
        Modal,
        AyInput,
        AySelect,
        RouterLink,
    },
    data() {
        return {
            dropdownOpen: null,
            dropdownStyle: {},
            selectedExamId: null,
            exams: [],
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
            selectedAssignExamId: null,
            assignForm: {
                date: '',
                time: '',
                questionTime: 'off',
                optionRandomize: 'off',
            },
            showMoveToFolderModal: false,
            foldersForMove: [],
            examToMoveId: null,
            isLoadingFolders: false,
            isAssigningToFolder: null,
            folderSearchQuery: '',
            showAddToCourseModal: false,
            coursesForAdd: [],
            examToCourseId: null,
            isLoadingCourses: false,
            isAssigningToCourse: null,
            courseSearchQuery: '',
        }
    },
    computed: {
        examColumns() {
            const actionsAlign = this.$i18n.locale === 'fa' ? 'left' : 'right'
            return [
                { key: 'details', label: this.$t('ayhoot.table.header.details'), thClass: 'py-2 font-medium' },
                { key: 'status', label: this.$t('ayhoot.table.header.status'), thClass: 'font-medium' },
                { key: 'category', label: this.$t('ayhoot.table.header.category'), thClass: 'font-medium' },
                { key: 'plays', label: this.$t('ayhoot.table.header.plays'), thClass: 'font-medium' },
                { key: 'questions', label: this.$t('ayhoot.table.header.questions'), thClass: 'font-medium' },
                { key: 'accessibility', label: this.$t('ayhoot.table.header.accessibility'), thClass: 'font-medium' },
                { key: 'actions', label: this.$t('ayhoot.table.header.actions'), align: actionsAlign, thClass: 'relative', tdClass: 'font-medium' },
            ]
        },
        myTabs() {
            return [
                { label: this.$t('ayhoot.tabs.ayhoots'), route: `/${this.$role}/exam` },
                { label: this.$t('ayhoot.tabs.courses'), route: `/${this.$role}/exam/courses` },
                { label: this.$t('ayhoot.tabs.purchased'), route: `/${this.$role}/exam/purchased` },
                { label: this.$t('ayhoot.tabs.folders'), route: `/${this.$role}/exam/folders` },
                { label: this.$t('ayhoot.tabs.archive'), route: `/${this.$role}/exam/archive` },
            ]
        },
        dropdownActions() {
            return [
                {
                    label: this.$t('ayhoot.dropdown.Unarchive'),
                    icon: DashArchive,
                    action: 'archive',
                },
                {
                    label: this.$t('ayhoot.dropdown.delete'),
                    icon: DashRemove,
                    action: 'delete',
                },
            ]
        },
        filteredExams() {
            if (!this.filters.search && this.exams.length > 0) return this.exams

            const query = this.filters.search.toLowerCase()
            return this.exams.filter(
                (exam) =>
                    exam.title.toLowerCase().includes(query) ||
                    (exam.subject && exam.subject.toLowerCase().includes(query))
            )
        },
        filteredFoldersForMove() {
            if (!this.folderSearchQuery) {
                return this.foldersForMove
            }
            const query = this.folderSearchQuery.toLowerCase()
            return this.foldersForMove.filter((folder) => folder.name && folder.name.toLowerCase().includes(query))
        },
        filteredCoursesForAdd() {
            if (!this.courseSearchQuery) {
                return this.coursesForAdd
            }
            const query = this.courseSearchQuery.toLowerCase()
            return this.coursesForAdd.filter(
                (course) =>
                    (course.title && course.title.toLowerCase().includes(query)) ||
                    (course.name && course.name.toLowerCase().includes(query))
            )
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
        async fetchExams() {
            this.isLoading = true
            try {
                const queryParams = new URLSearchParams({
                    page: this.filters.page,
                    search: this.filters.search,
                    status: this.filters.status,
                    sort: this.filters.sort,
                }).toString()
                const response = await apiCall('GET', `/api/instructor/exam/archives/list?${queryParams}`)
                this.exams = response.exams.data || []
                this.pagination = {
                    current_page: response.exams.current_page,
                    from: response.exams.from || 0,
                    to: response.exams.to || 0,
                    total: response.exams.total || 0,
                    last_page: response.exams.last_page || 1,
                }
            } catch (error) {
                console.error('Error fetching exams:', error)
                this.exams = []
                this.$toast.error(error.message || this.$t('ayhoot.notifications.loadFailed'))
            } finally {
                this.isLoading = false
            }
        },
        handleSearch() {
            if (this.searchTimeout) clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.filters.page = 1
                this.fetchExams()
            }, 500)
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page && page !== this.filters.page) {
                this.filters.page = page
                this.fetchExams()
            }
        },
        formatDate(dateString) {
            if (!dateString) return this.$t('ayhoot.table.body.notAvailable')
            try {
                const locale = this.$i18n.locale === 'fa' ? 'fa-IR' : 'en-US'
                return new Date(dateString).toLocaleDateString(locale, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                })
            } catch (e) {
                return this.$t('ayhoot.table.body.invalidDate')
            }
        },
        toggleDropdown(examId, event) {
            event.stopPropagation()
            if (this.dropdownOpen === examId) {
                this.dropdownOpen = null
                this.selectedExamId = null
            } else {
                const button = event.currentTarget
                const buttonRect = button.getBoundingClientRect()
                const windowHeight = window.innerHeight
                const windowWidth = window.innerWidth
                const DROPDOWN_HEIGHT_ESTIMATE = 230
                const DROPDOWN_WIDTH = 160
                let topStyle = 'auto'
                let bottomStyle = 'auto'
                let leftStyle = 'auto'
                let rightStyle = 'auto'

                if (
                    windowHeight - buttonRect.bottom < DROPDOWN_HEIGHT_ESTIMATE &&
                    buttonRect.top > DROPDOWN_HEIGHT_ESTIMATE
                ) {
                    bottomStyle = `${windowHeight - buttonRect.top + 4}px`
                } else {
                    topStyle = `${buttonRect.bottom + 4}px`
                }

                if (this.$i18n.locale === 'fa') {
                    leftStyle = `${buttonRect.left}px`
                } else {
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

                this.dropdownOpen = examId
                this.selectedExamId = examId
            }
        },
        closeDropdown() {
            this.dropdownOpen = null
            this.selectedExamId = null
        },
        handleDropdownAction(action, examId) {
            this.closeDropdown()
            switch (action.action) {
                case 'addCourse':
                    this.openAddToCourseModal(examId)
                    break
                case 'moveFolder':
                    this.openMoveToFolderModal(examId)
                    break
                case 'archive':
                    this.archiveExam(examId)
                    break
                case 'share':
                    this.shareExam(examId)
                    break
                case 'duplicate':
                    this.duplicateExam(examId)
                    break
                case 'delete':
                    this.deleteExam(examId)
                    break
                default:
                    console.warn('Unknown dropdown action:', action.action)
            }
        },
        async archiveExam(examId) {
            if (!(await confirmDialog({ message: this.$t('ayhoot.notifications.confirmArchive') }))) {
                return
            }
            try {
                await apiCall('GET', `/api/instructor/exam/archive/${examId}`)
                this.$toast.success(this.$t('ayhoot.notifications.archiveSuccess'))
                await this.fetchExams()
            } catch (error) {
                console.error('Error archiving exam:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.archiveFailed'))
            }
        },
        async shareExam(id) {
            // Get the base URL of the frontend application (e.g., "http://localhost:5173" or "https://ayhoot.com")
            const publicBaseUrl = window.location.origin

            // Construct the full shareable link
            const link = `${publicBaseUrl}/institute/ayhoot/${id}`

            try {
                await navigator.clipboard.writeText(link)
                this.$toast.success(this.$t('ayhoot.notifications.linkCopied'))
            } catch (err) {
                console.error('Failed to copy link: ', err)
                this.$toast.error(this.$t('ayhoot.notifications.linkCopyFailed'))
            }
        },
        async duplicateExam(id) {
            await apiCall('GET', `/api/instructor/exam/duplicate/${id}`)
            await this.fetchExams()
        },
        async deleteExam(examId) {
            if (await confirmDialog({ message: this.$t('ayhoot.notifications.confirmDelete'), variant: 'danger' })) {
                try {
                    await apiCall('GET', `/api/instructor/exam/delete/${examId}`)
                    this.$toast.success(this.$t('ayhoot.notifications.deleteSuccess'))
                    await this.fetchExams()
                } catch (error) {
                    console.error('Error deleting exam:', error)
                    this.$toast.error(error.message || this.$t('ayhoot.notifications.deleteFailed'))
                }
            }
        },
        openAssignModal(examId) {
            this.selectedAssignExamId = examId
            this.assignForm = { date: '', time: '', questionTime: 'off', optionRandomize: 'off' }
            this.showAssignModal = true
        },
        cancelAssign() {
            this.showAssignModal = false
            this.selectedAssignExamId = null
        },
        async handleAssignSubmit() {
            if (!this.assignForm.date || !this.assignForm.time) {
                this.$toast.error(this.$t('ayhoot.notifications.dueDateRequired'))
                return
            }
            if (!this.selectedAssignExamId) {
                this.$toast.error(this.$t('ayhoot.notifications.noExamSelected'))
                return
            }
            const payload = {
                exam_id: this.selectedAssignExamId,
                end_at: `${this.assignForm.date} ${this.assignForm.time}:00`,
                question_time: this.assignForm.questionTime,
                randomize_answer: this.assignForm.optionRandomize,
                group_ids: this.assignForm.selectedGroups || [],
            }
            try {
                const response = await apiCall('POST', '/api/instructor/game', payload)
                const gameId = response.id || response.game_id || response.game?.id
                if (!gameId) {
                    throw new Error(this.$t('ayhoot.notifications.gameIdNotFound'))
                }
                this.cancelAssign()
                this.$router.push(`/${this.$role}/report/assigned-ayhoots/${gameId}`)
            } catch (error) {
                console.error('Error creating game:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.assignFailed'))
            }
        },
        openMoveToFolderModal(examId) {
            this.examToMoveId = examId
            this.folderSearchQuery = ''
            this.showMoveToFolderModal = true
            this.fetchFoldersForMove()
        },
        closeMoveToFolderModal() {
            this.showMoveToFolderModal = false
            this.foldersForMove = []
            this.examToMoveId = null
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
                    isExamAlreadyInFolder: folder.exams?.some((ex) => ex.id === this.examToMoveId) || false,
                }))
            } catch (error) {
                console.error('Fetch folders error:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.loadFoldersFailed'))
            } finally {
                this.isLoadingFolders = false
            }
        },
        async handleAddExamToFolder(folder) {
            if (!this.examToMoveId || !folder || folder.isExamAlreadyInFolder) return
            this.isAssigningToFolder = folder.id
            try {
                await apiCall('POST', `/api/instructor/folder/${folder.id}/assign-exams`, {
                    exam_ids: [this.examToMoveId],
                })
                this.$toast.success(this.$t('ayhoot.notifications.moveToFolderSuccess', { folderName: folder.name }))
                const fInList = this.foldersForMove.find((f) => f.id === folder.id)
                if (fInList) {
                    fInList.isExamAlreadyInFolder = true
                    if (!fInList.exams) fInList.exams = []
                    if (!fInList.exams.some((ex) => ex.id === this.examToMoveId)) {
                        fInList.exams.push({ id: this.examToMoveId })
                    }
                }
            } catch (error) {
                console.error('Assign to folder error:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.moveToFolderFailed'))
            } finally {
                this.isAssigningToFolder = null
            }
        },
        async handleRemoveExamFromFolder(folder) {
            if (!this.examToMoveId || !folder || !folder.isExamAlreadyInFolder) return
            this.isAssigningToFolder = folder.id
            try {
                await apiCall('POST', `/api/instructor/folder/${folder.id}/unassign-exams`, {
                    exam_ids: [this.examToMoveId],
                })
                this.$toast.success(
                    this.$t('ayhoot.notifications.removeFromFolderSuccess', { folderName: folder.name })
                )
                const fInList = this.foldersForMove.find((f) => f.id === folder.id)
                if (fInList) {
                    fInList.isExamAlreadyInFolder = false
                    if (fInList.exams) {
                        fInList.exams = fInList.exams.filter((ex) => ex.id !== this.examToMoveId)
                    }
                }
            } catch (error) {
                console.error('Error removing exam from folder:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.removeFromFolderFailed'))
            } finally {
                this.isAssigningToFolder = null
            }
        },
        openAddToCourseModal(examId) {
            this.examToCourseId = examId
            this.courseSearchQuery = ''
            this.showAddToCourseModal = true
            this.fetchCoursesForAdd()
        },
        closeAddToCourseModal() {
            this.showAddToCourseModal = false
            this.coursesForAdd = []
            this.examToCourseId = null
            this.isAssigningToCourse = null
        },
        async fetchCoursesForAdd() {
            this.isLoadingCourses = true
            this.coursesForAdd = []
            try {
                const response = await apiCall('GET', '/api/instructor/course')
                let rawCourses = []
                if (Array.isArray(response)) rawCourses = response
                else if (response?.data && Array.isArray(response.data)) rawCourses = response.data
                else if (response?.courses && Array.isArray(response.courses)) rawCourses = response.courses
                else if (response?.data?.data && Array.isArray(response.data.data)) rawCourses = response.data.data
                else console.warn('Course API structure issue:', response)

                this.coursesForAdd = rawCourses.map((course) => ({
                    ...course,
                    isExamAlreadyInCourse: course.exams?.some((exam) => exam.id === this.examToCourseId) || false,
                }))
            } catch (error) {
                console.error('Error fetching courses:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.loadCoursesFailed'))
            } finally {
                this.isLoadingCourses = false
            }
        },
        async handleAssignExamToCourse(course) {
            if (!this.examToCourseId || !course || course.isExamAlreadyInCourse) return

            this.isAssigningToCourse = course.id
            try {
                await apiCall('POST', `/api/instructor/course/${course.id}/assign-exams`, {
                    exam_ids: [this.examToCourseId],
                })
                this.$toast.success(
                    this.$t('ayhoot.notifications.addToCourseSuccess', {
                        courseName: course.title || course.name,
                    })
                )
                const courseInList = this.coursesForAdd.find((c) => c.id === course.id)
                if (courseInList) {
                    courseInList.isExamAlreadyInCourse = true
                    if (!courseInList.exams) courseInList.exams = []
                    if (!courseInList.exams.some((ex) => ex.id === this.examToCourseId)) {
                        courseInList.exams.push({ id: this.examToCourseId })
                    }
                }
            } catch (error) {
                console.error('Error assigning exam to course:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.addToCourseFailed'))
            } finally {
                this.isAssigningToCourse = null
            }
        },
        async handleRemoveExamFromCourse(course) {
            if (!this.examToCourseId || !course || !course.isExamAlreadyInCourse) return
            this.isAssigningToCourse = course.id
            try {
                await apiCall('GET', `/api/instructor/course/${course.id}/remove-exam/${this.examToCourseId}`)
                this.$toast.success(
                    this.$t('ayhoot.notifications.removeFromCourseSuccess', {
                        courseName: course.title || course.name,
                    })
                )
                const courseInList = this.coursesForAdd.find((c) => c.id === course.id)
                if (courseInList) {
                    courseInList.isExamAlreadyInCourse = false
                    if (courseInList.exams) {
                        courseInList.exams = courseInList.exams.filter((ex) => ex.id !== this.examToCourseId)
                    }
                }
            } catch (error) {
                console.error('Error removing exam from course:', error)
                this.$toast.error(error.message || this.$t('ayhoot.notifications.removeFromCourseFailed'))
            } finally {
                this.isAssigningToCourse = null
            }
        },
    },
    async mounted() {
        await this.fetchExams()
        await this.fetchFolders()
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
    /* Adjust if your sidebar width is different */
}

@media (max-width: 1279px) {

    .xl\:ml-128,
    .xl\:ms-64 {
        margin-left: 0;
        margin-right: 0;
    }
}
</style>
