<template>
    <div
        class="bg-gray-50"
        :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'"
        :class="{ 'font-payda': $i18n.locale === 'fa' }"
    >
        <!-- Header -->
        <div class="fixed top-0 inset-x-0 z-50">
            <CourseHeader
                :toggle-sidebar="toggleSidebar"
                :saveCourse="saveCourse"
                :exitAndSave="exitAndSaveHandler"
                :course="course"
                :previewCourse="previewCourseHandler"
                :loading="loading"
                @settings-updated="handleCourseSettingsUpdate"
            />
        </div>

        <div class="flex">
            <!-- Left Sidebar - Course Content Management -->
            <div
                class="w-full md:w-1/4 min-h-[calc(100vh-4rem)] bg-white overflow-y-auto p-4 mt-16 md:relative"
                :class="[
                    { 'hidden md:block': !isSidebarOpen, 'block absolute z-40 inset-0': isSidebarOpen },
                    $i18n.locale === 'fa' ? 'border-l' : 'border-r border-gray-200',
                ]"
            >
                <!-- Close button for mobile overlay sidebar -->
                <button
                    v-if="isSidebarOpen && isMobile"
                    @click="toggleSidebar"
                    class="absolute top-2 p-2 text-gray-600 hover:text-gray-800 z-50"
                    :class="$i18n.locale === 'fa' ? 'left-2' : 'right-2'"
                    :aria-label="$t('createCourse.sidebar.closeSidebar')"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

                <div class="space-y-2 mb-6">
                    <ResponsiveButton type="dash-blue" class="w-full p-2 gap-2" @click="openAyhootModal">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M15.0001 2.3999V5.9999C15.0001 6.66264 15.5373 7.1999 16.2001 7.1999H19.8001M18.0001 4.1999C17.466 3.72204 16.9118 3.15528 16.5619 2.78718C16.3291 2.54224 16.0074 2.3999 15.6695 2.3999H6.5998C5.27432 2.3999 4.19981 3.47441 4.1998 4.79989L4.19971 19.1998C4.1997 20.5253 5.27421 21.5998 6.5997 21.5998L17.3997 21.5999C18.7252 21.5999 19.7997 20.5254 19.7998 19.2L19.8001 6.47773C19.8001 6.1709 19.683 5.87594 19.4701 5.65503C19.0763 5.24655 18.4187 4.57442 18.0001 4.1999Z"
                                class="stroke-current"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        <span class="text-md">{{ $t('createCourse.sidebar.addAyhoot') }}</span>
                    </ResponsiveButton>
                    <ResponsiveButton type="light-blue" class="w-full p-2 gap-2" @click="openfileModal">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M11.6998 21.6001H5.69979C4.37431 21.6001 3.2998 20.5256 3.2998 19.2001L3.2999 4.80013C3.29991 3.47466 4.37442 2.40015 5.6999 2.40015H16.5002C17.8256 2.40015 18.9002 3.47466 18.9002 4.80015V9.60015M7.50018 7.20015H14.7002M7.50018 10.8001H14.7002M14.7002 15.5541V18.4985C14.7002 19.9534 16.2516 21.2879 17.7065 21.2879C19.1615 21.2879 20.7002 19.9535 20.7002 18.4985V14.7793C20.7002 14.009 20.2574 13.2273 19.2723 13.2273C18.2186 13.2273 17.7065 14.009 17.7065 14.7793V18.3435M7.50018 14.4001H11.1002"
                                class="stroke-current"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        <span class="text-md">{{ $t('createCourse.sidebar.addFile') }}</span>
                    </ResponsiveButton>
                    <ResponsiveButton type="light-blue" class="w-full p-2 gap-2" @click="openVideoModal">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                            />
                        </svg>
                        <span class="text-md">{{ $t('createCourse.sidebar.addVideo') }}</span>
                    </ResponsiveButton>
                </div>

                <h3 class="font-bold text-gray-700 mb-2">{{ $t('createCourse.sidebar.courseContent') }}</h3>
                <div class="space-y-2">
                    <div
                        v-for="(element, index) in course.content"
                        :key="element.db_id || element.type + '_' + (element.exam_id || index)"
                        @click="setActiveContent(element)"
                        class="p-2 rounded-lg flex items-center gap-2 cursor-pointer border-2 border-dashed"
                        :class="[
                            activeContentPreview && activeContentPreview.order === element.order
                                ? 'bg-blue-50 border-blue-300 hover:bg-blue-50'
                                : 'border-gray-200 hover:bg-gray-50',
                            { 'flex-row-reverse': $i18n.locale === 'fa' },
                        ]"
                    >
                        <div
                            class="flex-shrink-0 w-16 h-10 bg-gray-200 rounded overflow-hidden flex items-center justify-center"
                        >
                            <!-- Image thumbnail for image files -->
                            <img
                                v-if="element.type === 'file' && isImageFile(element.originalFileName) && (element.filePreviewUrl || element.path)"
                                :src="getImageUrl(element.filePreviewUrl || element.path)"
                                :alt="element.description + ' thumbnail'"
                                class="w-full h-full object-cover"
                                @error="handleThumbnailError"
                            />
                            <!-- Video thumbnail -->
                            <img
                                v-else-if="element.type === 'video' && element.filePreviewUrl"
                                :src="getImageUrl(element.filePreviewUrl)"
                                :alt="element.description + ' thumbnail'"
                                class="w-full h-full object-cover"
                                @error="handleThumbnailError"
                            />
                            <!-- Exam thumbnail -->
                            <img
                                v-else-if="element.type === 'exam' && element.cover"
                                :src="getImageUrl(element.cover)"
                                :alt="element.description + ' thumbnail'"
                                class="w-full h-full object-cover"
                                @error="handleThumbnailError"
                            />
                            <!-- Default icons for different file types -->
                            <div v-else class="text-gray-400 p-1">
                                <!-- PDF Icon -->
                                <svg
                                    v-if="element.type === 'file' && isPdfFile(element.originalFileName)"
                                    class="h-5 w-5 text-red-500"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                </svg>
                                <!-- Document Icon for other files -->
                                <svg
                                    v-else-if="element.type === 'file'"
                                    class="h-5 w-5 text-blue-500"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                </svg>
                                <!-- Video Icon -->
                                <svg
                                    v-else-if="element.type === 'video'"
                                    class="h-5 w-5 text-purple-500"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <!-- Exam Icon -->
                                <svg
                                    v-else-if="element.type === 'exam'"
                                    class="h-5 w-5 text-green-500"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="flex-grow min-w-0">
                            <div class="flex items-center justify-between">
                                <div
                                    class="flex items-center min-w-0 gap-1"
                                    :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }"
                                >
                                    <span class="text-xs font-bold text-gray-500 flex-shrink-0"
                                        >{{ element.order }}.</span
                                    >
                                    <span class="text-sm text-gray-700 truncate" :title="element.description">{{
                                        element.description
                                    }}</span>
                                    <span
                                        class="text-xs px-2 py-0.5 rounded-full capitalize font-semibold flex-shrink-0"
                                        :class="getContentItemBadgeStyle(element.type)"
                                    >
                                        {{
                                            $t(
                                                'createCourse.contentTypes.' +
                                                    (element.type === 'exam' ? 'ayhoot' : element.type)
                                            )
                                        }}
                                    </span>
                                </div>
                                <div class="flex gap-1 flex-shrink-0">
                                    <button
                                        @click.stop="removeContentItem(index)"
                                        class="p-1 text-red-500 hover:text-red-700 cursor-pointer"
                                        :aria-label="$t('createCourse.sidebar.removeAria')"
                                    >
                                        <DashLilRemove />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    v-if="!course.content || course.content.length === 0"
                    class="mt-4 bg-gray-50 p-4 py-8 rounded-md text-center text-gray-400 text-sm"
                >
                    {{ $t('createCourse.sidebar.emptyCourse') }}
                </div>
            </div>

            <!-- Center Box - Course Details & Content Preview -->
            <div
                class="flex-1 mt-16 bg-gray-50 min-h-[calc(100vh-4rem)] p-4 md:p-6"
                :class="{ 'md:w-3/4': !isSidebarOpen || !isMobile, 'w-full': true }"
            >
                <div v-if="activeContentPreview" class="bg-white p-4 md:p-6 rounded-2xl shadow">
                    <div
                        class="flex items-center justify-between mb-3 gap-2"
                        :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }"
                    >
                        <h2 class="text-xl font-semibold text-gray-800">
                            {{ activeContentPreview.order }}. {{ activeContentPreview.description }}
                        </h2>
                        <div
                            class="flex items-center gap-2 flex-shrink-0"
                            :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }"
                        >
                            <span
                                class="text-sm px-3 py-1 rounded-full capitalize font-semibold"
                                :class="getContentItemBadgeStyle(activeContentPreview.type)"
                            >
                                {{
                                    $t(
                                        'createCourse.contentTypes.' +
                                            (activeContentPreview.type === 'exam'
                                                ? 'ayhoot'
                                                : activeContentPreview.type)
                                    )
                                }}
                            </span>
                            <span
                                v-if="activeContentPreview.monetize === 'paid'"
                                class="text-sm px-3 py-1 rounded-full capitalize font-semibold bg-gray-100 text-gray-500"
                            >
                                {{ activeContentPreview.price !== null ? activeContentPreview.price : 'free' }}
                            </span>
                        </div>
                    </div>

                    <!-- Exam Preview -->
                    <div v-if="activeContentPreview.type === 'exam'">
                        <div class="bg-gray-50 p-4 rounded-xl space-y-3">
                            <img
                                v-if="activeContentPreview.cover"
                                :src="getImageUrl(activeContentPreview.cover)"
                                alt="Ayhoot Cover"
                                class="w-full h-auto max-h-90 object-cover rounded-md mb-3 bg-gray-200"
                                @error="handlePreviewImageError"
                            />
                            <div
                                v-else
                                class="w-full h-48 bg-gray-200 rounded-md mb-3 flex items-center justify-center text-gray-400 text-lg"
                            >
                                {{ $t('createCourse.preview.noCover') }}
                            </div>

                            <div
                                class="flex items-center justify-between gap-1"
                                :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }"
                            >
                                <p class="text-lg text-gray-800 font-semibold">
                                    {{ activeContentPreview.exam_title || 'Ayhoot' }}
                                </p>
                                <div
                                    class="flex items-center gap-1"
                                    :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }"
                                >
                                    <span
                                        class="font-medium px-3 py-1 text-sm bg-purple-100 text-purple-500 rounded-full capitalize"
                                    >
                                        {{ $t('createCourse.preview.category') }}:
                                        {{ activeContentPreview.category_id || 'No Category' }}
                                    </span>
                                    <span
                                        class="font-medium px-3 py-1 text-sm bg-pink-100 text-pink-500 rounded-full capitalize"
                                    >
                                        {{ $t('createCourse.preview.questions') }}:
                                        {{ activeContentPreview.questions_count || 0 }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Video Preview -->
                    <div v-else-if="activeContentPreview.type === 'video'">
                        <div
                            v-if="isEmbedCode(activeContentPreview.video_url)"
                            ref="embedContainer"
                            class="w-full aspect-video bg-black rounded-md"
                            v-html="activeContentPreview.video_url"
                        ></div>
                        <video
                            v-else-if="activeContentPreview.path || activeContentPreview.filePreviewUrl"
                            controls
                            class="w-full rounded-md max-h-[70vh] bg-black"
                            :src="activeContentPreview.path || activeContentPreview.filePreviewUrl"
                            preload="metadata"
                        >
                            {{ $t('createCourse.preview.videoNotSupported') }}
                        </video>
                        <div
                            v-else-if="activeContentPreview.file && !activeContentPreview.filePreviewUrl"
                            class="text-sm text-gray-600 mt-1"
                        >
                            <div class="flex items-center gap-2 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-yellow-500 flex-shrink-0"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm14.553 1.106A1 1 0 0016 8.447V11.553a1 1 0 001.553.849l3-1.501a1 1 0 000-1.698l-3-1.5z"
                                    />
                                </svg>
                                <p class="truncate font-medium" :title="activeContentPreview.file.name">
                                    {{ activeContentPreview.file.name }}
                                    <span class="text-gray-500 font-normal italic"
                                        >({{ $t('createCourse.preview.pendingUpload') }})</span
                                    >
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- File Preview (Image, PDF, other) -->
                    <div
                        v-else-if="
                            activeContentPreview.type === 'file' &&
                            (activeContentPreview.path || activeContentPreview.filePreviewUrl)
                        "
                    >
                        <div
                            v-if="isImageFile(activeContentPreview.originalFileName)"
                            class="bg-white p-1 rounded-md border"
                        >
                            <img
                                :src="getImageUrl(activeContentPreview.path || activeContentPreview.filePreviewUrl)"
                                :alt="activeContentPreview.originalFileName"
                                class="w-full max-h-[70vh] object-contain rounded-md"
                                @error="handlePreviewImageError"
                            />
                        </div>
                        <!-- PDF Preview -->
                        <div
                            v-else-if="isPdfFile(activeContentPreview.originalFileName) && activeContentPreview.path"
                            class="text-center p-8 bg-red-50 border border-red-200 rounded-md"
                        >
                            <svg class="h-16 w-16 text-red-500 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">{{ activeContentPreview.originalFileName }}</p>
                            <p class="text-sm text-gray-600 mb-4">PDF Document</p>
                            <div class="space-y-2">
                                <a
                                    :href="getImageUrl(activeContentPreview.path)"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold mr-2"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    {{ $t('createCourse.preview.viewPdf') }}
                                </a>
                                <a
                                    :href="getImageUrl(activeContentPreview.path)"
                                    download
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-semibold"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    {{ $t('createCourse.preview.download') }}
                                </a>
                            </div>
                            <p class="text-xs text-gray-500 mt-4">{{ $t('createCourse.preview.pdfPreviewNote') }}</p>
                        </div>
                        <!-- PDF Pending Upload -->
                        <div
                            v-else-if="
                                isPdfFile(activeContentPreview.originalFileName) && activeContentPreview.filePreviewUrl
                            "
                            class="text-center p-8 bg-yellow-50 border border-yellow-200 rounded-md"
                        >
                            <svg class="h-16 w-16 text-yellow-500 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">{{ activeContentPreview.originalFileName }}</p>
                            <p class="text-sm text-gray-600 mb-4">PDF Document</p>
                            <p class="text-xs text-gray-500">{{ $t('createCourse.preview.pendingUpload') }}</p>
                        </div>
                        <div v-else class="text-sm text-gray-600 mt-1">
                            <div class="flex items-center gap-2 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-blue-500 flex-shrink-0"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <p class="truncate font-medium" :title="activeContentPreview.originalFileName">
                                    {{ activeContentPreview.originalFileName }}
                                </p>
                                <a
                                    v-if="activeContentPreview.path"
                                    :href="activeContentPreview.path"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="ml-auto text-sm text-blue-600 hover:underline flex-shrink-0 font-semibold"
                                    >{{ $t('createCourse.preview.download') }}</a
                                >
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $t('createCourse.preview.noPreviewAvailable') }}
                            </p>
                        </div>
                    </div>
                    <div
                        v-else-if="
                            activeContentPreview.type === 'file' &&
                            activeContentPreview.file &&
                            !activeContentPreview.filePreviewUrl
                        "
                        class="text-sm text-gray-600 mt-1"
                    >
                        <div class="flex items-center gap-2 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6 text-yellow-500 flex-shrink-0"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <p class="truncate font-medium" :title="activeContentPreview.file.name">
                                {{ activeContentPreview.file.name }}
                                <span class="text-gray-500 font-normal italic"
                                    >({{ $t('createCourse.preview.pendingUpload') }})</span
                                >
                            </p>
                        </div>
                    </div>
                    <div
                        v-else-if="!activeContentPreview.path && !activeContentPreview.file"
                        class="text-sm text-gray-500 mt-1 py-8 text-center"
                    >
                        <p class="italic text-lg">{{ $t('createCourse.preview.detailsOnSave') }}</p>
                        <p class="text-xs mt-1">{{ $t('createCourse.preview.awaitingProcessing') }}</p>
                    </div>
                </div>
                <div v-else class="text-center text-gray-500 py-10">
                    <svg
                        class="mx-auto h-12 w-12 text-gray-300"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            vector-effect="non-scaling-stroke"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        {{ $t('createCourse.preview.noContentSelected') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $t('createCourse.preview.clickToPreview') }}</p>
                    <p class="mt-1 text-sm text-gray-500" v-if="!course.content || course.content.length === 0">
                        {{ $t('createCourse.sidebar.emptyCourse') }}
                    </p>
                </div>
            </div>
        </div>
        <!-- End of main flex container for sidebar and content -->

        <Teleport to="body">
            <!-- Ayhoot Modal -->
            <div
                v-if="showAyhootModal"
                @click="showAyhootModal = false"
                class="fixed inset-0 z-[60] bg-black/50 flex items-center justify-center p-4"
            >
                <div
                    @click.stop
                    class="bg-white rounded-lg p-4 md:p-6 w-full max-w-xs sm:max-w-md md:max-w-2xl shadow-xl flex flex-col"
                    :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'"
                >
                    <h2 class="text-lg font-bold mb-4">{{ $t('createCourse.ayhootModal.title') }}</h2>
                    <div class="mb-4">
                        <input
                            type="text"
                            v-model="examSearchQuery"
                            @input="handleExamSearchDebounce"
                            :placeholder="$t('createCourse.ayhootModal.searchPlaceholder')"
                            class="w-full px-3 py-2 border-2 border-gray-300 rounded-md"
                        />
                    </div>

                    <div
                        v-if="isLoadingExams"
                        class="text-center text-gray-500 py-4 flex-grow flex items-center justify-center"
                    >
                        {{ $t('createCourse.ayhootModal.loading') }}
                    </div>

                    <ul
                        v-if="!isLoadingExams && filteredExamsForModal.length > 0"
                        class="max-h-60 overflow-y-auto border-2 border-gray-300 rounded-md flex-grow"
                    >
                        <li
                            v-for="exam in filteredExamsForModal"
                            :key="exam.id"
                            @click="selectExamForCourse(exam)"
                            class="p-2 hover:bg-gray-100 cursor-pointer border-b last:border-b-0"
                            :class="{ 'bg-blue-50 hover:bg-blue-100': isExamAlreadyInCourse(exam.id) }"
                        >
                            {{ exam.title }}
                            <span v-if="exam.subject" class="text-xs text-gray-600"> ({{ exam.subject }})</span>
                            <span
                                v-if="isExamAlreadyInCourse(exam.id)"
                                class="text-xs text-blue-600 font-semibold"
                                :class="$i18n.locale === 'fa' ? 'mr-2' : 'ml-2'"
                                >({{ $t('createCourse.ayhootModal.alreadyAdded') }})</span
                            >
                            <span
                                class="text-xs text-gray-500 block sm:inline"
                                :class="$i18n.locale === 'fa' ? 'sm:mr-2' : 'sm:ml-2'"
                            >
                                {{ exam.questions_count || 0 }} {{ $t('createCourse.ayhootModal.questions') }}
                                <span
                                    v-if="exam.status"
                                    class="capitalize p-0.5 rounded text-[0.7rem]"
                                    :class="[
                                        $i18n.locale === 'fa' ? 'mr-1' : 'ml-1',
                                        exam.status === 'published'
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-yellow-100 text-yellow-700',
                                    ]"
                                >
                                    {{ exam.status }}
                                </span>
                            </span>
                        </li>
                    </ul>
                    <div
                        v-if="!isLoadingExams && availableExams.length === 0 && !examSearchQuery"
                        class="text-gray-500 py-4 text-center flex-grow flex items-center justify-center"
                    >
                        {{ $t('createCourse.ayhootModal.noAyhootsAvailable') }}
                    </div>
                    <div
                        v-if="!isLoadingExams && filteredExamsForModal.length === 0 && examSearchQuery"
                        class="text-gray-500 py-4 text-center flex-grow flex items-center justify-center"
                    >
                        {{ $t('createCourse.ayhootModal.noResults') }}
                    </div>

                    <div
                        v-if="!isLoadingExams && examPagination.total > 0 && examPagination.last_page > 1"
                        class="mt-4 flex justify-between items-center text-sm"
                        :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }"
                    >
                        <ResponsiveButton
                            type="light-gray"
                            @click="changeExamPage(examPagination.current_page - 1)"
                            :disabled="examPagination.current_page <= 1 || isLoadingExams"
                            class="p-2"
                        >
                            {{ $t('createCourse.ayhootModal.previous') }}
                        </ResponsiveButton>
                        <span>
                            {{
                                $t('createCourse.ayhootModal.paginationInfo', {
                                    current: examPagination.current_page,
                                    last: examPagination.last_page,
                                    from: examPagination.from,
                                    to: examPagination.to,
                                    total: examPagination.total,
                                })
                            }}
                        </span>
                        <ResponsiveButton
                            type="light-gray"
                            @click="changeExamPage(examPagination.current_page + 1)"
                            :disabled="examPagination.current_page >= examPagination.last_page || isLoadingExams"
                            class="p-2"
                        >
                            {{ $t('createCourse.ayhootModal.next') }}
                        </ResponsiveButton>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <ResponsiveButton type="light-gray" @click="showAyhootModal = false" class="p-2">{{
                            $t('createCourse.ayhootModal.done')
                        }}</ResponsiveButton>
                    </div>
                </div>
            </div>
        </Teleport>

        <Teleport to="body">
            <div
                v-if="showFileUploadModal"
                @click="closeFileUploadModal"
                class="fixed inset-0 z-[60] bg-black/50 flex items-center justify-center p-4"
            >
                <div
                    @click.stop
                    class="bg-white rounded-lg p-4 md:p-6 w-full max-w-xs sm:max-w-md md:max-w-lg shadow-xl"
                    :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'"
                >
                    <h2 class="text-lg font-bold mb-4">
                        {{ $t(editingContentItem?.db_id ? 'createCourse.modal.edit' : 'createCourse.modal.add') }}
                        {{ $t(currentContentType === 'file' ? 'createCourse.modal.file' : 'createCourse.modal.video') }}
                    </h2>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{
                            $t('createCourse.modal.descriptionLabel')
                        }}</label>
                        <input
                            type="text"
                            v-model="currentContentItem.description"
                            :placeholder="$t('createCourse.modal.descriptionPlaceholder')"
                            class="w-full px-3 py-2 border-2 border-gray-300 rounded-md"
                        />
                    </div>
                    <div class="mb-4">
                        <!-- File Upload / URL for Video -->
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{
                                currentContentType === 'video'
                                    ? $t('createCourse.modal.videoUrlSubtitle')
                                    : $t('createCourse.modal.fileLabel')
                            }}
                        </label>

                        <!-- Video URL / Embed Code input (URL only) -->
                        <div v-if="currentContentType === 'video'" class="mb-3">
                            <textarea
                                id="videoUrl"
                                v-model="currentContentItem.video_url"
                                placeholder="<div id=...><script... Or https://.../video.mp4"
                                class="w-full px-3 py-2 border-2 border-gray-300 rounded-md text-sm font-mono"
                                rows="4"
                            ></textarea>
                        </div>

                        <!-- File upload dropzone only for file content type -->
                        <div v-if="currentContentType === 'file'">
                            <!-- Existing File Info / Preview -->
                            <div
                                v-if="
                                    currentContentItem.file ||
                                    currentContentItem.filePreviewUrl ||
                                    (editingContentItem?.db_id && currentContentItem.originalFileName)
                                "
                                class="my-2 p-2 border-2 border-gray-300 rounded-md"
                            >
                                <img
                                    v-if="
                                        (currentContentItem.filePreviewUrl &&
                                            currentContentItem.file &&
                                            isImageFile(currentContentItem.file.name)) ||
                                        (editingContentItem?.db_id &&
                                            isImageFile(currentContentItem.originalFileName) &&
                                            currentContentItem.path &&
                                            !currentContentItem.file &&
                                            currentContentType === 'file')
                                    "
                                    :src="getImageUrl(currentContentItem.filePreviewUrl || currentContentItem.path)"
                                    alt="Preview"
                                    class="max-h-32 w-full object-cover rounded mb-2"
                                />
                                <p
                                    class="text-sm text-gray-700 truncate"
                                    :title="currentContentItem.file?.name || currentContentItem.originalFileName"
                                >
                                    {{
                                        currentContentItem.file?.name ||
                                        currentContentItem.originalFileName ||
                                        $t('createCourse.modal.uploadedFile')
                                    }}
                                </p>
                                <button
                                    v-if="currentContentItem.file || currentContentItem.filePreviewUrl"
                                    @click="removeSelectedFile"
                                    type="button"
                                    class="text-xs font-bold text-red-600 bg-red-200 p-2 rounded-md hover:text-red-800 hover:underline mt-1 cursor-pointer"
                                >
                                    {{ $t('createCourse.modal.removeFile') }}
                                </button>
                                <button
                                    v-else-if="
                                        editingContentItem?.db_id &&
                                        currentContentItem.originalFileName &&
                                        !currentContentItem.file
                                    "
                                    @click="prepareToReplaceFile"
                                    type="button"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline mt-1"
                                >
                                    {{ $t('createCourse.modal.replaceFile') }}
                                </button>
                            </div>

                            <!-- File Input Dropzone -->
                            <div
                                v-if="
                                    !currentContentItem.file &&
                                    !(
                                        editingContentItem?.db_id &&
                                        currentContentItem.originalFileName &&
                                        !currentContentItem.file
                                    )
                                "
                                class="mt-1 flex justify-center p-4 border-2 border-gray-300 border-dashed rounded-md cursor-pointer"
                                @dragover.prevent
                                @drop.prevent="handleFileDrop"
                                @click="triggerFileInput"
                            >
                                <input
                                    ref="fileUploadInput"
                                    type="file"
                                    class="hidden"
                                    @change="handleFileSelect"
                                    :accept="
                                        currentContentType === 'file'
                                            ? '.pdf,.doc,.docx,.ppt,.pptx,.txt,.zip,.rar,.jpg,.jpeg,.png,.gif,.webp'
                                            : 'video/*'
                                    "
                                />
                                <div class="space-y-1 text-center">
                                    <svg
                                        class="mx-auto h-10 w-10 text-gray-400"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 48 48"
                                    >
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                    <div
                                        class="flex text-sm text-gray-600"
                                        :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }"
                                    >
                                        <span
                                            class="relative bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500"
                                            >{{ $t('createCourse.modal.uploadFile') }}</span
                                        >
                                        <p class="px-1">{{ $t('createCourse.modal.orDragDrop') }}</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        {{
                                            currentContentType === 'file'
                                                ? $t('createCourse.modal.fileTypesHint')
                                                : $t('createCourse.modal.videoTypesHint')
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <ResponsiveButton type="light-gray" @click="closeFileUploadModal" class="p-2">{{
                            $t('createCourse.modal.cancel')
                        }}</ResponsiveButton>
                        <ResponsiveButton
                            :type="currentContentType === 'file' ? 'dash-blue' : 'dash-blue'"
                            class="p-2"
                            @click="saveFileContent"
                            :disabled="loading"
                        >
                            {{
                                $t(
                                    editingContentItem?.db_id
                                        ? 'createCourse.modal.saveChanges'
                                        : 'createCourse.modal.addToCourse'
                                )
                            }}
                        </ResponsiveButton>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script>
import CourseHeader from '@/components/layout/InstructorCourseHeader.vue'
import { apiCall } from '@/services/apiService'
import DashLilRemove from '@/components/atoms/icons/DashLilRemove.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

const DEFAULT_CONTENT_ITEM = {
    type: '',
    exam_id: null,
    exam_title: '',
    cover: null,
    order: 0,
    file: null,
    originalFileName: null,
    path: null,
    filePreviewUrl: null,
    description: '',
    db_id: null,
    video_url: null,
    monetize: 'free',
    price: null,
    exam_status: 'N/A',
    category_id: null,
    questions_count: 0,
    isSaved: false,
}

const DEFAULT_COURSE_STRUCTURE = {
    id: null,
    title: '',
    description: null,
    status: 'active',
    cover: null,
    category_id: null,
    institute_id: null,
    monetize: 'free',
    price: null,
    content: [],
    originalOptionsString: null,
}

const IMAGE_EXTENSIONS = /\.(jpg|jpeg|png|gif|webp)$/i
const PDF_EXTENSIONS = /\.pdf$/i

export default {
    name: 'AdminCreateCourse',
    components: { CourseHeader, DashLilRemove, ResponsiveButton },
    data() {
        return {
            isSidebarOpen: false,
            course: { ...DEFAULT_COURSE_STRUCTURE },
            activeContentPreview: null,
            showAyhootModal: false,
            availableExams: [],
            isLoadingExams: false,
            examSearchQuery: '',
            examSearchFilters: { search: '', status: '', sort: 'created_at', page: 1 },
            examPagination: { current_page: 1, from: 0, to: 0, total: 0, last_page: 1 },
            examSearchTimeout: null,
            showFileUploadModal: false,
            currentContentType: 'file',
            currentContentItem: { ...DEFAULT_CONTENT_ITEM },
            editingContentItem: null,
            editingContentItemIndex: -1,
            loading: false,
            windowWidth: window.innerWidth,
        }
    },
    computed: {
        isMobile() {
            return this.windowWidth < 768
        },
        filteredExamsForModal() {
            if (!this.examSearchQuery) {
                return this.availableExams
            }
            const query = this.examSearchQuery.toLowerCase()
            return this.availableExams.filter(
                (exam) =>
                    (exam.title && exam.title.toLowerCase().includes(query)) ||
                    (exam.subject && exam.subject.toLowerCase().includes(query))
            )
        },
    },
    watch: {
        activeContentPreview(newVal) {
            if (newVal && newVal.type === 'video' && this.isEmbedCode(newVal.video_url)) {
                this.$nextTick(() => {
                    this.executeScriptsInEmbed()
                })
            }
        },
        'course.content': {
            handler(newContent) {
                if (newContent && newContent.length > 0) {
                    const currentActiveOrder = this.activeContentPreview ? this.activeContentPreview.order : null
                    const activeStillExists =
                        currentActiveOrder !== null && newContent.some((item) => item.order === currentActiveOrder)

                    if (!activeStillExists) {
                        this.setActiveContent(newContent[0])
                    } else if (currentActiveOrder !== null) {
                        const newActiveVersion = newContent.find((item) => item.order === currentActiveOrder)
                        if (newActiveVersion) this.setActiveContent(newActiveVersion)
                        else this.setActiveContent(newContent[0])
                    }
                } else if ((!newContent || newContent.length === 0) && this.activeContentPreview) {
                    this.activeContentPreview = null
                }
            },
            deep: true,
        },
    },
    async mounted() {
        if (this.$route.params.id) {
            await this.fetchCourseDetails(this.$route.params.id)
        } else {
            this.course = JSON.parse(JSON.stringify(DEFAULT_COURSE_STRUCTURE))
            this.updateContentOrder()
            this.activeContentPreview = null
        }
        window.addEventListener('resize', this.handleResize)
        this.handleResize()
    },
    beforeUnmount() {
        this.course.content.forEach((item) => {
            if (item.filePreviewUrl) URL.revokeObjectURL(item.filePreviewUrl)
        })
        if (this.currentContentItem.filePreviewUrl) URL.revokeObjectURL(this.currentContentItem.filePreviewUrl)
        window.removeEventListener('resize', this.handleResize)
        if (this.examSearchTimeout) clearTimeout(this.examSearchTimeout)
    },
    methods: {
        isEmbedCode(code) {
            if (!code || typeof code !== 'string') return false
            return /<\s*(script|iframe)/i.test(code)
        },
        executeScriptsInEmbed() {
            const container = this.$refs.embedContainer
            if (!container) return
            const scripts = container.querySelectorAll('script')
            scripts.forEach((oldScript) => {
                const newScript = document.createElement('script')
                Array.from(oldScript.attributes).forEach((attr) => {
                    newScript.setAttribute(attr.name, attr.value)
                })
                if (oldScript.innerHTML) {
                    newScript.innerHTML = oldScript.innerHTML
                }
                oldScript.parentNode.replaceChild(newScript, oldScript)
            })
        },
        handleCourseSettingsUpdate(updatedSettings) {
            const settingsToUpdate = {
                title: updatedSettings.title,
                description: updatedSettings.description,
                cover: updatedSettings.cover,
                category_id: updatedSettings.category_id,
                institute_id: updatedSettings.institute_id,
                monetize: updatedSettings.monetize,
                price: updatedSettings.price,
                status: updatedSettings.status,
            }
            this.course = { ...this.course, ...settingsToUpdate }
        },
        setActiveContent(item) {
            this.activeContentPreview = item
            if (this.isMobile && this.isSidebarOpen) {
                this.toggleSidebar()
            }
        },
        isImageFile(filename) {
            if (!filename) return false
            return IMAGE_EXTENSIONS.test(filename.toLowerCase())
        },
        isPdfFile(filename) {
            if (!filename) return false
            return PDF_EXTENSIONS.test(filename.toLowerCase())
        },
        getThumbnailUrl(item) {
            if (!item) return null
            if (item.type === 'exam') {
                return item.cover ? (item.cover.startsWith('http') ? item.cover : `/uploads/${item.cover}`) : null
            } else if (item.type === 'file') {
                if (this.isImageFile(item.originalFileName)) {
                    return item.filePreviewUrl || item.path || null
                }
                return null
            } else if (item.type === 'video') {
                if (item.filePreviewUrl && this.isImageFile(item.originalFileName)) return item.filePreviewUrl // Custom thumbnail
                return null
            }
            return null
        },
        handleResize() {
            this.windowWidth = window.innerWidth
        },
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        _mapServerContentItem(serverItem, allExamsDetailsMap) {
            const baseItem = {
                ...DEFAULT_CONTENT_ITEM,
                db_id: serverItem.content_item_db_id || serverItem.id || null,
                type: serverItem.type,
                description: serverItem.title || serverItem.description || 'Untitled Content',
                order: serverItem.order || 0,
                isSaved: true,
            }

            if (baseItem.type === 'exam') {
                baseItem.exam_id = serverItem.id || serverItem.exam_id
                const examDetails = allExamsDetailsMap.get(baseItem.exam_id)
                if (examDetails) {
                    baseItem.exam_title = examDetails.title || `Ayhoot ID: ${baseItem.exam_id}`
                    if (
                        !baseItem.description ||
                        baseItem.description === 'Untitled Content' ||
                        baseItem.description === serverItem.title
                    ) {
                        baseItem.description = examDetails.title || baseItem.exam_title
                    }
                    baseItem.cover = examDetails.cover
                    baseItem.monetize = examDetails.monetize
                    baseItem.price = examDetails.price
                    baseItem.exam_status = examDetails.status
                    baseItem.category_id = examDetails.category_id
                    baseItem.questions_count = examDetails.questions_count
                } else {
                    baseItem.exam_title = `Ayhoot ID: ${baseItem.exam_id}`
                    if (baseItem.description === 'Untitled Content') {
                        baseItem.description = baseItem.exam_title
                    }
                }
            } else if (baseItem.type === 'file' || baseItem.type === 'video') {
                // Correctly handle file path by removing /storage prefix
                let rawPath = serverItem.path || serverItem.file_path || null
                if (rawPath && typeof rawPath === 'string' && rawPath.startsWith('/storage')) {
                    rawPath = rawPath.substring('/storage'.length)
                }
                baseItem.path = rawPath

                baseItem.originalFileName =
                    serverItem.file_name ||
                    serverItem.originalFileName ||
                    (baseItem.path
                        ? baseItem.path.substring(baseItem.path.lastIndexOf('/') + 1)
                        : 'untitled.' + baseItem.type)
                baseItem.description = serverItem.title || baseItem.description

                // For videos, check for an embed URL from the 'url' or 'video_url' property
                if (baseItem.type === 'video') {
                    const embedOrUrl = serverItem.url || serverItem.video_url
                    if (embedOrUrl) {
                        baseItem.video_url = embedOrUrl
                        // If it's a direct URL to a video file (not embed code) and path is not set,
                        // use it as the path. This handles cases where video URL is a direct link.
                        if (!baseItem.path && !this.isEmbedCode(embedOrUrl)) {
                            baseItem.path = embedOrUrl
                        }
                    }
                }
            }
            return baseItem
        },
        async fetchCourseDetails(courseIdToFetch) {
            if (!courseIdToFetch) return
            this.isLoading = true
            try {
                const response = await apiCall('GET', `/api/instructor/course/${courseIdToFetch}`)
                // Ownership guard: only the course owner may edit its content.
                if (response.is_owner === false) {
                    this.$toast.error(this.$t('createCourseModal.notOwner'))
                    this.$router.push(`/${this.$role}/exam/courses`)
                    return
                }
                if (response.course) {
                    const fetchedCourse = response.course

                    this.course.content.forEach((item) => {
                        if (item.filePreviewUrl) URL.revokeObjectURL(item.filePreviewUrl)
                    })

                    let processedContentItems = []
                    const allExamsDetailsMap = new Map((fetchedCourse.exams || []).map((exam) => [exam.id, exam]))

                    let timelineSource = []

                    if (fetchedCourse.options && typeof fetchedCourse.options === 'string') {
                        try {
                            const parsedOptions = JSON.parse(fetchedCourse.options)
                            if (parsedOptions && Array.isArray(parsedOptions.timeline)) {
                                timelineSource = parsedOptions.timeline
                            }
                        } catch (e) {
                            console.error('Error parsing course.options JSON string:', e)
                            this.$toast.error('Failed to parse course content (options).')
                        }
                    } else if (
                        fetchedCourse.content &&
                        Array.isArray(fetchedCourse.content) &&
                        fetchedCourse.content.length > 0
                    ) {
                        timelineSource = fetchedCourse.content.map((c) => ({
                            type: c.type,
                            id: c.type === 'exam' ? c.exam_id : c.id,
                            title: c.description,
                            path: c.path,
                            content_item_db_id: c.id,
                            cover: c.cover,
                            exam_id: c.exam_id,
                            file_name: c.originalFileName,
                            video_url: c.video_url,
                        }))
                        fetchedCourse.content.forEach((cItem) => {
                            if (cItem.type === 'exam' && cItem.exam_id && !allExamsDetailsMap.has(cItem.exam_id)) {
                                allExamsDetailsMap.set(cItem.exam_id, {
                                    id: cItem.exam_id,
                                    title: cItem.description,
                                    cover: cItem.cover,
                                    monetize: cItem.monetize,
                                    price: cItem.price,
                                    status: cItem.exam_status,
                                    category_id: cItem.category_id,
                                    questions_count: cItem.questions_count,
                                })
                            }
                        })
                    }

                    if (timelineSource.length > 0) {
                        processedContentItems = timelineSource.map((timelineItem, index) => {
                            const mappedItem = this._mapServerContentItem(timelineItem, allExamsDetailsMap)
                            mappedItem.order = index + 1
                            return mappedItem
                        })
                    }

                    const tempCourseData = { ...fetchedCourse }
                    delete tempCourseData.content
                    delete tempCourseData.options
                    delete tempCourseData.exams

                    this.course = {
                        ...DEFAULT_COURSE_STRUCTURE,
                        ...tempCourseData,
                        content: processedContentItems,
                        originalOptionsString:
                            typeof fetchedCourse.options === 'string'
                                ? fetchedCourse.options
                                : JSON.stringify(fetchedCourse.options),
                    }

                    this.updateContentOrder()
                } else {
                    this.$toast.error('Course not found.')
                    this.$router.push(`/${this.$role}/exam/courses`)
                }
            } catch (error) {
                console.error('Error fetching course details:', error)
                this.$toast.error(error.message || error.response?.data?.message || 'Failed to load course details.')
                this.course = JSON.parse(JSON.stringify(DEFAULT_COURSE_STRUCTURE))
                this.activeContentPreview = null
            } finally {
                this.isLoading = false
            }
        },
        async openAyhootModal() {
            if (!this.course.id) {
                const confirmSave = await confirmDialog({ message: this.$t('createCourse.alerts.saveFirstToAddAyhoot'), variant: 'primary' })
                if (confirmSave) {
                    const saved = await this.saveCourse()
                    if (!saved) return
                } else {
                    return
                }
            }
            if (!this.course.id) {
                this.$toast.info(this.$t('createCourse.alerts.mustSaveForAyhoot'))
                return
            }
            this.examSearchFilters = { search: this.examSearchQuery, status: '', sort: 'created_at', page: 1 }
            await this.fetchExamsForModal()
            this.showAyhootModal = true
        },
        async fetchExamsForModal() {
            this.isLoadingExams = true
            try {
                const queryParams = new URLSearchParams(this.examSearchFilters).toString()
                const response = await apiCall('GET', `/api/instructor/exam?${queryParams}`)
                this.availableExams = response.exams.data || []
                this.examPagination = {
                    current_page: response.exams.current_page,
                    from: response.exams.from || 0,
                    to: response.exams.to || 0,
                    total: response.exams.total || 0,
                    last_page: response.exams.last_page || 1,
                }
            } catch (error) {
                console.error('Error fetching exams for modal:', error)
                this.availableExams = []
                this.$toast.error(
                    error.message || error.response?.data?.message || this.$t('createCourse.alerts.loadExamsFailed')
                )
            } finally {
                this.isLoadingExams = false
            }
        },
        handleExamSearchDebounce() {
            if (this.examSearchTimeout) clearTimeout(this.examSearchTimeout)
            this.examSearchTimeout = setTimeout(() => {
                this.examSearchFilters.page = 1
                this.examSearchFilters.search = this.examSearchQuery
                this.fetchExamsForModal()
            }, 300)
        },
        changeExamPage(newPage) {
            if (
                newPage >= 1 &&
                newPage <= this.examPagination.last_page &&
                newPage !== this.examSearchFilters.page &&
                !this.isLoadingExams
            ) {
                this.examSearchFilters.page = newPage
                this.fetchExamsForModal()
            }
        },
        isExamAlreadyInCourse(examId) {
            return this.course.content.some((item) => item.type === 'exam' && item.exam_id === examId)
        },
        async selectExamForCourse(exam) {
            if (!this.course.id) {
                this.$toast.error(this.$t('createCourse.alerts.mustSaveForAyhoot'))
                this.showAyhootModal = false
                return
            }
            if (this.isExamAlreadyInCourse(exam.id)) {
                this.$toast.info(this.$t('createCourse.alerts.alreadyInCourse', { title: exam.title }))
                return
            }
            this.loading = true
            try {
                await apiCall('POST', `/api/instructor/course/${this.course.id}/assign-exams`, { exam_ids: [exam.id] })
                this.$toast.success(this.$t('createCourse.alerts.addAyhootSuccess', { title: exam.title }))
                await this.fetchCourseDetails(this.course.id)
            } catch (error) {
                console.error('Error assigning exam:', error)
                this.$toast.error(
                    error.message || error.response?.data?.message || this.$t('createCourse.alerts.addAyhootFailed')
                )
            } finally {
                this.loading = false
            }
        },
        async openModal(type) {
            if (!this.course.id && (type === 'file' || type === 'video')) {
                const confirmSave = await confirmDialog({ message: this.$t('createCourse.alerts.saveFirstToAddContent'), variant: 'primary' })
                if (confirmSave) {
                    const saved = await this.saveCourse()
                    if (saved && this.course.id) {
                        this._proceedOpenModal(type)
                    }
                    return
                } else {
                    return
                }
            }
            if (!this.course.id && (type === 'file' || type === 'video')) {
                this.$toast.info(this.$t('createCourse.alerts.mustSaveForContent'))
                return
            }
            this._proceedOpenModal(type)
        },
        _proceedOpenModal(type) {
            this.currentContentType = type
            this.editingContentItem = null
            this.editingContentItemIndex = -1
            if (this.currentContentItem.filePreviewUrl) URL.revokeObjectURL(this.currentContentItem.filePreviewUrl)
            this.currentContentItem = { ...DEFAULT_CONTENT_ITEM, type: type, order: this.course.content.length + 1 }
            this.showFileUploadModal = true
        },
        openfileModal() {
            this.openModal('file')
        },
        openVideoModal() {
            this.openModal('video')
        },
        openEditContentModal(item, index) {
            if (item.type === 'exam') {
                this.$toast.info(this.$t('createCourse.alerts.editAyhootInfo'))
                return
            }
            this.editingContentItem = JSON.parse(JSON.stringify(item))
            if (this.currentContentItem.filePreviewUrl) URL.revokeObjectURL(this.currentContentItem.filePreviewUrl)

            this.currentContentItem = { ...DEFAULT_CONTENT_ITEM, ...JSON.parse(JSON.stringify(item)) }
            this.currentContentItem.file = null
            this.currentContentItem.filePreviewUrl = null
            if (this.currentContentItem.path && !this.currentContentItem.file) {
                this.currentContentItem.filePreviewUrl = null
            }

            this.editingContentItemIndex = index
            this.currentContentType = item.type
            this.showFileUploadModal = true
        },
        triggerFileInput() {
            this.$refs.fileUploadInput?.click()
        },
        _processSelectedFile(file) {
            if (this.currentContentItem.filePreviewUrl) URL.revokeObjectURL(this.currentContentItem.filePreviewUrl)
            this.currentContentItem.filePreviewUrl = null

            this.currentContentItem.file = file
            this.currentContentItem.originalFileName = file.name

            if (file && this.isImageFile(file.name)) {
                this.currentContentItem.filePreviewUrl = URL.createObjectURL(file)
            }
        },
        handleFileDrop(event) {
            const file = event.dataTransfer.files[0]
            if (file) {
                this._processSelectedFile(file)
            }
        },
        handleFileSelect(event) {
            const file = event.target.files[0]
            if (file) {
                this._processSelectedFile(file)
            }
            event.target.value = null
        },
        removeSelectedFile() {
            if (this.currentContentItem.filePreviewUrl) URL.revokeObjectURL(this.currentContentItem.filePreviewUrl)
            this.currentContentItem.file = null
            this.currentContentItem.filePreviewUrl = null

            if (!(this.editingContentItem && this.editingContentItem.db_id && this.currentContentItem.path)) {
                this.currentContentItem.originalFileName = null
            }
            if (this.$refs.fileUploadInput) this.$refs.fileUploadInput.value = null
        },
        prepareToReplaceFile() {
            this.currentContentItem.originalFileName = null
            this.currentContentItem.path = null
            this.removeSelectedFile()
        },
        async saveFileContent() {
            if (!this.currentContentItem.description.trim()) {
                this.$toast.error(this.$t('createCourse.alerts.descriptionEmpty'))
                return
            }

            if (!this.editingContentItem?.db_id) {
                if (this.currentContentType === 'file' && !this.currentContentItem.file) {
                    this.$toast.error(this.$t('createCourse.alerts.selectAFile'))
                    return
                }
                if (
                    this.currentContentType === 'video' &&
                    !this.currentContentItem.video_url?.trim()
                ) {
                    this.$toast.error(this.$t('createCourse.alerts.provideVideo'))
                    return
                }
                if (!this.course.id) {
                    this.$toast.error(this.$t('createCourse.alerts.mustSaveCourse'))
                    return
                }

                this.loading = true
                const formData = new FormData()
                formData.append('type', this.currentContentType)
                formData.append('title', this.currentContentItem.description)
                if (this.currentContentType === 'file' && this.currentContentItem.file) {
                    formData.append('file', this.currentContentItem.file)
                }
                if (this.currentContentType === 'video' && this.currentContentItem.video_url) {
                    formData.append('video_url', this.currentContentItem.video_url.trim())
                }

                try {
                    const response = await apiCall(
                        'POST',
                        `/api/instructor/course/add-content/${this.course.id}`,
                        formData,
                        true
                    )
                    this.$toast.success(
                        this.$t('createCourse.alerts.contentAddedSuccess', {
                            type:
                                this.currentContentType === 'file'
                                    ? this.$t('createCourse.modal.file')
                                    : this.$t('createCourse.modal.video'),
                        })
                    )
                    if (response.course) {
                        await this.fetchCourseDetails(this.course.id)
                    } else {
                        await this.fetchCourseDetails(this.course.id)
                    }
                    this.closeFileUploadModal(false)
                } catch (error) {
                    console.error(`Error adding ${this.currentContentType}:`, error)
                    this.$toast.error(
                        error.message ||
                            error.response?.data?.message ||
                            this.$t('createCourse.alerts.contentAddFailed', {
                                type:
                                    this.currentContentType === 'file'
                                        ? this.$t('createCourse.modal.file')
                                        : this.$t('createCourse.modal.video'),
                            })
                    )
                } finally {
                    this.loading = false
                }
            } else {
                const itemToUpdate = { ...this.course.content[this.editingContentItemIndex] }

                itemToUpdate.description = this.currentContentItem.description
                if (this.currentContentType === 'video') {
                    itemToUpdate.video_url = this.currentContentItem.video_url?.trim() || null
                    itemToUpdate.file = null
                    itemToUpdate.originalFileName = null
                    itemToUpdate.filePreviewUrl = null
                    itemToUpdate.path = itemToUpdate.video_url
                } else if (this.currentContentItem.file) {
                    itemToUpdate.file = this.currentContentItem.file
                    itemToUpdate.originalFileName = this.currentContentItem.file.name
                    itemToUpdate.filePreviewUrl = this.currentContentItem.filePreviewUrl
                    itemToUpdate.path = null
                    itemToUpdate.video_url = null
                }

                this.course.content.splice(this.editingContentItemIndex, 1, itemToUpdate)

                if (this.activeContentPreview && this.activeContentPreview.order === itemToUpdate.order) {
                    this.setActiveContent(itemToUpdate)
                }

                this.closeFileUploadModal(false)
                this.$toast.info(this.$t('createCourse.alerts.changesStaged'))
            }
        },
        closeFileUploadModal(keepFileForLocalAdd = false) {
            if (!keepFileForLocalAdd && this.currentContentItem.filePreviewUrl) {
                URL.revokeObjectURL(this.currentContentItem.filePreviewUrl)
            }
            this.currentContentItem = { ...DEFAULT_CONTENT_ITEM }
            this.showFileUploadModal = false
            this.editingContentItem = null
            this.editingContentItemIndex = -1
            if (this.$refs.fileUploadInput) this.$refs.fileUploadInput.value = null
        },
        onContentOrderChange() {
            this.updateContentOrder()
            if (this.course.id) {
                this.$toast.info(this.$t('createCourse.alerts.orderChanged'))
            }
        },
        updateContentOrder() {
            this.course.content.forEach((item, index) => {
                item.order = index + 1
            })
            if (this.activeContentPreview) {
                const currentActiveId =
                    this.activeContentPreview.db_id ||
                    (this.activeContentPreview.type === 'exam' ? this.activeContentPreview.exam_id : null)
                const currentActiveType = this.activeContentPreview.type
                const currentActiveOrder = this.activeContentPreview.order

                let foundActive = false

                for (let i = 0; i < this.course.content.length; i++) {
                    const item = this.course.content[i]
                    if (currentActiveId && item.db_id && item.db_id === currentActiveId) {
                        this.activeContentPreview = item
                        foundActive = true
                        break
                    }
                    if (!foundActive && item.type === currentActiveType && item.order === currentActiveOrder) {
                        if (item.type === 'exam' && item.exam_id === this.activeContentPreview.exam_id) {
                            this.activeContentPreview = item
                            foundActive = true
                            break
                        } else if (
                            item.type !== 'exam' &&
                            item.originalFileName === this.activeContentPreview.originalFileName &&
                            item.description === this.activeContentPreview.description
                        ) {
                            this.activeContentPreview = item
                            foundActive = true
                            break
                        }
                    }
                }
                if (!foundActive && this.course.content.length > 0) {
                    this.activeContentPreview =
                        this.course.content.find((item) => item.order === currentActiveOrder) || this.course.content[0]
                } else if (this.course.content.length === 0) {
                    this.activeContentPreview = null
                }
            } else if (this.course.content.length > 0) {
                this.activeContentPreview = this.course.content[0]
            }
        },

        async removeContentItem(index) {
            const itemToRemove = this.course.content[index]
            if (!itemToRemove) return

            const ok = await confirmDialog({
                title: this.$t('createCourse.alerts.confirmRemoveTitle'),
                message: this.$t('createCourse.alerts.confirmRemoveText', {
                    description: itemToRemove.description || this.$t('createCourse.alerts.thisItem'),
                }),
                confirmText: this.$t('createCourse.alerts.confirmRemoveButton'),
                cancelText: this.$t('createCourse.modal.cancel'),
                variant: 'danger',
            })

            if (ok) {
                if (this.course.id && itemToRemove.isSaved) {
                    this.loading = true
                    try {
                        const payload = { content_index: index }
                        await apiCall('POST', `/api/instructor/course/remove-content/${this.course.id}`, payload)
                        await alertDialog({
                            title: this.$t('createCourse.alerts.removedTitle'),
                            message: this.$t('createCourse.alerts.removedText'),
                        })

                        await this.fetchCourseDetails(this.course.id)
                    } catch (error) {
                        console.error('Error removing content item:', error)
                        await alertDialog({
                            title: this.$t('createCourse.alerts.errorTitle'),
                            message:
                                error.message ||
                                error.response?.data?.message ||
                                this.$t('createCourse.alerts.removeFailedText'),
                        })
                    } finally {
                        this.loading = false
                    }
                } else {
                    this.removeContentLocally(index, itemToRemove)
                    this.$toast.info(this.$t('createCourse.alerts.itemRemovedLocally'))
                }
            }
        },

        removeContentLocally(index, itemToRemove) {
            if (itemToRemove.filePreviewUrl) {
                URL.revokeObjectURL(itemToRemove.filePreviewUrl)
            }
            const wasActive = this.activeContentPreview && this.activeContentPreview.order === itemToRemove.order
            this.course.content.splice(index, 1)
            this.updateContentOrder()

            if (wasActive) {
                if (this.course.content.length > 0) {
                    const newActiveIndex = Math.min(index, this.course.content.length - 1)
                    this.setActiveContent(newActiveIndex >= 0 ? this.course.content[newActiveIndex] : null)
                } else {
                    this.activeContentPreview = null
                }
            } else if (!this.activeContentPreview && this.course.content.length > 0) {
                this.setActiveContent(this.course.content[0])
            }
        },

        getContentItemBadgeStyle(type) {
            const typeName = type === 'exam' ? 'Ayhoot' : type
            const styles = {
                Ayhoot: 'bg-blue-100 text-blue-700',
                file: 'bg-green-100 text-green-700',
                video: 'bg-purple-100 text-purple-700',
            }
            return styles[typeName] || 'bg-gray-100 text-gray-700'
        },
        async saveCourse() {
            if (!this.course.title.trim()) {
                this.$toast.error(this.$t('createCourse.alerts.titleEmpty'))
                return false
            }
            this.loading = true
            const formData = new FormData()
            formData.append('title', this.course.title || '')

            if (this.course.description) formData.append('description', this.course.description)
            if (this.course.category_id) formData.append('category_id', this.course.category_id)
            if (this.course.institute_id !== undefined && this.course.institute_id !== null) {
                formData.append('institute_id', this.course.institute_id)
            } else {
                formData.append('institute_id', '')
            }

            formData.append('monetize', this.course.monetize || 'free')
            if (this.course.monetize === 'paid' && this.course.price !== null && this.course.price !== '') {
                formData.append('price', this.course.price)
            }
            // formData.append('status', this.course.status || 'draft')

            if (this.course.cover instanceof File) {
                formData.append('cover', this.course.cover)
            } else if (this.course.cover === null || this.course.cover === '') {
            }

            if (this.course.id) {
                formData.append('course_id', this.course.id)
                formData.append('_method', 'POST')
            }

            const timelineForBackend = this.course.content.map((item, index) => {
                const timelineItem = {
                    type: item.type,
                    title: item.description,
                }
                if (item.type === 'exam') {
                    timelineItem.id = item.exam_id
                }

                if (item.db_id) {
                    timelineItem.content_item_db_id = item.db_id
                }

                if (item.file instanceof File) {
                    const formDataFileKey = item.db_id ? `cfile_update_${item.db_id}` : `cfile_new_${index}`
                    formData.append(formDataFileKey, item.file, item.file.name)
                    timelineItem.file_form_data_key = formDataFileKey
                } else if (item.path) {
                    timelineItem.path = item.path
                }
                if (item.type === 'video' && item.video_url && !item.file && !item.path) {
                    timelineItem.path = item.video_url
                }
                return timelineItem
            })

            const optionsPayload = { timeline: timelineForBackend }
            formData.append('options', JSON.stringify(optionsPayload))

            try {
                const url = this.course.id ? `/api/instructor/course/${this.course.id}` : '/api/instructor/course'
                const method = 'POST'

                const response = await apiCall(method, url, formData, true)

                if (response.course) {
                    this.$toast.success(
                        this.$t(
                            this.course.id ? 'createCourse.alerts.courseUpdated' : 'createCourse.alerts.courseCreated'
                        )
                    )

                    this.course.content.forEach((ci) => {
                        if (ci.filePreviewUrl) URL.revokeObjectURL(ci.filePreviewUrl)
                    })

                    const newCourseId = response.course.id
                    const isNewCourseCreation = !this.course.id && newCourseId

                    const activePreviewOrderBeforeFetch = this.activeContentPreview
                        ? this.activeContentPreview.order
                        : null

                    await this.fetchCourseDetails(newCourseId || this.course.id)

                    if (activePreviewOrderBeforeFetch !== null) {
                        const newActiveItem = this.course.content.find(
                            (item) => item.order === activePreviewOrderBeforeFetch
                        )
                        if (newActiveItem) {
                            this.setActiveContent(newActiveItem)
                        } else if (this.course.content.length > 0) {
                            this.setActiveContent(this.course.content[0])
                        }
                    }

                    if (isNewCourseCreation && this.$route.params.id !== String(newCourseId)) {
                        this.$router.replace({
                            name: this.$route.name,
                            params: { id: newCourseId },
                            query: this.$route.query,
                        })
                    }
                    return true
                } else {
                    this.$toast.error(response.message || 'An unknown error occurred during save.')
                    return false
                }
            } catch (error) {
                console.error('Error saving course:', error)
                let errorMessage = this.$t('createCourse.alerts.saveFailed')
                if (error.response?.data?.message) {
                    errorMessage = error.response.data.message
                } else if (error.response?.data?.errors) {
                    const errors = error.response.data.errors
                    const firstErrorKey = Object.keys(errors)[0]
                    if (errors[firstErrorKey] && errors[firstErrorKey][0]) {
                        errorMessage = errors[firstErrorKey][0]
                    }
                }
                this.$toast.error(errorMessage)
                return false
            } finally {
                this.loading = false
            }
        },
        async exitAndSaveHandler() {
            this.$router.push(`/${this.$role}/exam/courses`)
        },
        async previewCourseHandler() {
            if (!this.course.id) {
                this.$toast.info(this.$t('createCourse.alerts.saveToPreviewInfo'))
                const confirmSave = await confirmDialog({ message: this.$t('createCourse.alerts.saveToPreviewConfirm'), variant: 'primary' })
                if (confirmSave) {
                    const saved = await this.saveCourse()
                    if (!saved) return
                } else {
                    return
                }
            }
            if (!this.course.id) {
                this.$toast.error(this.$t('createCourse.alerts.saveFailedPreview'))
                return
            }

            const saved = await this.saveCourse()
            if (saved && this.course.id) {
                this.$router.push(`/${this.$role}/course/${this.course.id}/preview`)
            } else if (!saved) {
                this.$toast.error(this.$t('createCourse.alerts.saveFailedLatestContent'))
            }
        },
        handleThumbnailError(event) {
            console.error('Thumbnail failed to load:', event.target.src)
            // Hide the broken image by setting display to none
            event.target.style.display = 'none'
        },
        handlePreviewImageError(event) {
            console.error('Preview image failed to load:', event.target.src)
        },
        handleVideoError(event) {
            console.error('Video failed to load:', event.target.src)
        },
        handleVideoLoaded(event) {
            console.log('Video loaded successfully:', event.target.src)
        },
        getVideoFileName(url) {
            if (!url) return 'Video'
            try {
                const urlObj = new URL(url)
                const pathname = urlObj.pathname
                const filename = pathname.split('/').pop()
                return filename || 'Video File'
            } catch (e) {
                return 'Video File'
            }
        },
        getImageUrl(path) {
            console.log('getImageUrl called with path:', path)
            
            if (!path) {
                console.log('No path provided, returning default image')
                return '/imgHolders/CardHolderImage.jpg'
            }
            
            // If it's a blob URL (for newly uploaded files), return as is
            if (path.startsWith('blob:')) {
                console.log('Blob URL detected:', path)
                return path
            }
            
            // If it's already a full URL, return as is
            if (path.startsWith('http://') || path.startsWith('https://')) {
                console.log('Full URL detected:', path)
                return path
            }
            
            // If it starts with /, it's already a proper path
            if (path.startsWith('/')) {
                console.log('Absolute path detected:', path)
                return path
            }
            
            // Otherwise, prepend /uploads/
            const finalPath = `/uploads/${path.replace(/\\/g, '/')}`
            console.log('Relative path converted to:', finalPath)
            return finalPath
        },
        async handlePdfError(event) {
            console.error('PDF failed to load:', event.target.href)
            await alertDialog({ message: 'PDF file could not be loaded. The file may not exist or there may be a server issue.' })
        },
        getFileTypeLabel(filename) {
            if (!filename) return 'Document'
            const extension = filename.split('.').pop()?.toLowerCase()
            const typeMap = {
                'pdf': 'PDF Document',
                'doc': 'Word Document',
                'docx': 'Word Document',
                'ppt': 'PowerPoint Presentation',
                'pptx': 'PowerPoint Presentation',
                'txt': 'Text File',
                'zip': 'ZIP Archive',
                'rar': 'RAR Archive',
                'jpg': 'JPEG Image',
                'jpeg': 'JPEG Image',
                'png': 'PNG Image',
                'gif': 'GIF Image',
                'webp': 'WebP Image',
                'mp4': 'MP4 Video',
                'avi': 'AVI Video',
                'mov': 'MOV Video',
                'wmv': 'WMV Video',
                'flv': 'FLV Video',
                'webm': 'WebM Video'
            }
            return typeMap[extension] || `${extension?.toUpperCase()} File` || 'Document'
        },
    },
}
</script>

<style scoped>
.max-h-96 {
    max-height: 24rem;
}
.max-h-\[60vh\] {
    max-height: 60vh;
}
.max-h-\[70vh\] {
    max-height: 70vh;
}
.min-h-\[500px\] {
    min-height: 500px;
}
.max-h-80 {
    max-height: 20rem;
}
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
.aspect-video {
    aspect-ratio: 16 / 9;
}
.font-payda {
    font-family: 'Peyda', sans-serif !important;
}
</style>
