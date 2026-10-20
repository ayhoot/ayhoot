<template>
    <div class="bg-gray-50 min-h-screen">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" :teacherName="teacherName" :teacherUsername="teacherUsername" />
        </div>

        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />

            <main
                class="bg-gray-50 ml-0 sm:ml-0 md:ml-0 lg:ml-0 xl:ml-64 w-full overflow-y-auto mt-16 min-h-[calc(100vh-4rem)]"
            >
                <!-- Loading State -->
                <div v-if="isLoading" class="flex items-center justify-center min-h-[calc(100vh-4rem)]">
                    <p class="text-gray-500 font-semibold">Loading Course...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="flex items-center justify-center min-h-[calc(100vh-4rem)]">
                    <p class="text-red-500 font-semibold">{{ error }}</p>
                </div>

                <!-- Content -->
                <div v-else-if="course" class="grid grid-cols-1 lg:grid-cols-4 w-full gap-4 p-6">
                    <!-- 1st Col - Takes full width on small screens, 75% on large screens -->
                    <div class="lg:col-span-3">
                        <div class="grid grid-cols-1 lg:grid-cols-4 w-full sm:gap-8 pb-4">
                            <img
                                :src="getAssetUrl(course.cover)"
                                :alt="course.title"
                                class="w-full h-70 object-cover rounded-lg lg:col-span-1 bg-gray-200"
                            />
                            <div class="lg:col-span-3">
                                <!-- Course info -->
                                <div class="flex items-center justify-between py-2">
                                    <div class="flex items-center font-semibold gap-1 text-gray-600 text-sm">
                                        <DashCourses />
                                        <span>Course</span>
                                        <span
                                            v-if="course.status"
                                            class="px-3 py-1 ml-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                            :class="statusClass(course.status)"
                                        >
                                            {{ course.status }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Title and description -->
                                <h2 class="text-2xl font-bold" v-html="course.sanitizedTitle"></h2>

                                <!-- Profile informations -->
                                <div v-if="course.teacher" class="flex items-center py-2 gap-2">
                                    <RouterLink :to="`/admin/teacher/profile/${course.teacher.id}`">
                                        <img
                                            :src="getAssetUrl(course.teacher.avatar, true)"
                                            :alt="course.teacher.name"
                                            class="w-10 h-10 object-cover rounded-full border-2 border-blue-400"
                                        />
                                    </RouterLink>
                                    <div>
                                        <div class="flex items-center gap-1">
                                            <h3 class="font-bold text-sm">{{ course.teacher.name }}</h3>
                                            <DashVerified v-if="course.teacher.verified_at" />
                                        </div>
                                        <RouterLink
                                            :to="`/admin/teacher/profile/${course.teacher.id}`"
                                            class="text-xs font-semibold text-gray-500"
                                        >
                                            @{{ course.teacher.username }}
                                        </RouterLink>
                                    </div>
                                </div>

                                <p v-if="course.description" class="pt-2 text-sm font-semibold text-gray-500">
                                    Description
                                </p>
                                <p class="font-medium pb-2 whitespace-pre-wrap">{{ course.description }}</p>

                                <!-- Statistics of course -->
                                <p class="pt-2 text-sm font-semibold text-gray-500">Details</p>
                                <div class="flex w-full items-center flex-wrap gap-x-2 gap-y-2 pt-2">
                                    <!-- Category -->
                                    <div
                                        v-if="course.category"
                                        class="flex items-center gap-x-2 bg-gray-200 p-2 px-3 rounded-md"
                                    >
                                        <svg
                                            width="20"
                                            height="20"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M9.0666 20.1699C9.61889 20.1699 10.0666 19.7222 10.0666 19.1699C10.0666 18.6177 9.61889 18.1699 9.0666 18.1699V20.1699ZM4.26718 15.9701L3.26718 15.9701L4.26718 15.9701ZM5.2666 2.39986C5.26658 1.84757 4.81884 1.39988 4.26656 1.3999C3.71427 1.39993 3.26658 1.84766 3.2666 2.39995L5.2666 2.39986ZM9.0666 8.31677C9.61889 8.31677 10.0666 7.86906 10.0666 7.31677C10.0666 6.76449 9.61889 6.31677 9.0666 6.31677V8.31677ZM18.6666 9.86657L18.6666 8.86657L18.6666 9.86657ZM13.8666 9.86655L13.8666 10.8665H13.8666L13.8666 9.86655ZM19.7334 2.3999L20.7334 2.39992C20.7334 2.1347 20.6281 1.88034 20.4405 1.6928C20.253 1.50526 19.9987 1.3999 19.7334 1.3999V2.3999ZM19.7333 8.79993L20.7333 8.79995V8.79995L19.7333 8.79993ZM12.7999 8.79987L11.7999 8.79987V8.79987L12.7999 8.79987ZM12.8 4.53324L12.1356 3.78583C11.9221 3.9756 11.8 4.2476 11.8 4.53323L12.8 4.53324ZM15.1999 2.3999V1.3999C14.955 1.3999 14.7186 1.48978 14.5356 1.6525L15.1999 2.3999ZM18.6666 21.5999L18.6666 20.5999L18.6666 21.5999ZM13.8666 21.5999L13.8666 22.5999L13.8666 21.5999ZM19.7334 14.1332L20.7334 14.1333C20.7334 13.868 20.6281 13.6137 20.4405 13.4261C20.253 13.2386 19.9987 13.1332 19.7334 13.1332V14.1332ZM19.7333 20.5333L20.7333 20.5333V20.5333L19.7333 20.5333ZM12.7999 20.5332L11.7999 20.5332V20.5332L12.7999 20.5332ZM12.8 16.2666L12.1356 15.5192C11.9221 15.7089 11.8 15.9809 11.8 16.2666L12.8 16.2666ZM15.1999 14.1332V13.1332C14.955 13.1332 14.7186 13.2231 14.5356 13.3858L15.1999 14.1332ZM9.0666 18.1699H7.46718V20.1699H9.0666V18.1699ZM5.26718 15.97L5.26681 7.31673L3.26681 7.31681L3.26718 15.9701L5.26718 15.97ZM5.26681 7.31673L5.2666 2.39986L3.2666 2.39995L3.26681 7.31681L5.26681 7.31673ZM9.0666 6.31677H4.26681V8.31677H9.0666V6.31677ZM7.46718 18.1699C6.25219 18.1699 5.26724 17.185 5.26718 15.97L3.26718 15.9701C3.26728 18.2896 5.14766 20.1699 7.46718 20.1699V18.1699ZM18.6666 8.86657L13.8666 8.86655L13.8666 10.8665L18.6666 10.8666L18.6666 8.86657ZM18.7334 2.39988L18.7333 8.79991L20.7333 8.79995L20.7334 2.39992L18.7334 2.39988ZM18.6666 10.8666C19.808 10.8666 20.7333 9.94132 20.7333 8.79995L18.7333 8.7999C18.7333 8.83672 18.7034 8.86657 18.6666 8.86657L18.6666 10.8666ZM11.7999 8.79987C11.7999 9.94126 12.7252 10.8665 13.8666 10.8665L13.8666 8.86655C13.8298 8.86655 13.7999 8.8367 13.7999 8.79988L11.7999 8.79987ZM13.7999 8.79988L13.8 4.53324L11.8 4.53323L11.7999 8.79987L13.7999 8.79988ZM15.1999 3.3999H19.7334V1.3999H15.1999V3.3999ZM14.5356 1.6525L12.1356 3.78583L13.4643 5.28064L15.8643 3.14731L14.5356 1.6525ZM18.6666 20.5999L13.8666 20.5999L13.8666 22.5999L18.6666 22.5999L18.6666 20.5999ZM18.7334 14.1332L18.7333 20.5332L20.7333 20.5333L20.7334 14.1333L18.7334 14.1332ZM18.6666 22.5999C19.808 22.5999 20.7333 21.6747 20.7333 20.5333L18.7333 20.5332C18.7333 20.5701 18.7034 20.5999 18.6666 20.5999L18.6666 22.5999ZM11.7999 20.5332C11.7999 21.6746 12.7252 22.5999 13.8666 22.5999L13.8666 20.5999C13.8298 20.5999 13.7999 20.57 13.7999 20.5332L11.7999 20.5332ZM13.7999 20.5332L13.8 16.2666L11.8 16.2666L11.7999 20.5332L13.7999 20.5332ZM15.1999 15.1332H19.7334V13.1332H15.1999V15.1332ZM14.5356 13.3858L12.1356 15.5192L13.4643 17.014L15.8643 14.8806L14.5356 13.3858Z"
                                                class="fill-current"
                                            />
                                        </svg>
                                        <span class="font-semibold text-sm">{{ course.category.title }}</span>
                                    </div>
                                    <!-- Activities -->
                                    <div class="flex items-center gap-x-2 bg-gray-200 p-2 px-3 rounded-md">
                                        <span class="font-semibold text-sm"
                                            >{{ course.activities.length }} Activities</span
                                        >
                                    </div>
                                    <!-- Price -->
                                    <div class="flex items-center gap-x-2 bg-blue-200 p-2 px-3 rounded-md">
                                        <svg
                                            width="20"
                                            height="20"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="text-blue-500"
                                        >
                                            <path
                                                d="M7.79995 7.8H16.2M7.79995 12.6H16.2M5.75995 3H18.24C19.1015 3 19.8 3.80589 19.8 4.8V21L17.2 19.2L14.6 21L12 19.2L9.39995 21L6.79995 19.2L4.19995 21V4.8C4.19995 3.80589 4.89839 3 5.75995 3Z"
                                                class="stroke-current"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                        <span class="font-semibold text-sm text-blue-600">{{
                                            course.monetize === 'free' ? 'Free' : formatPrice(course.price) + ' R'
                                        }}</span>
                                    </div>
                                    <!-- Institute -->
                                    <div
                                        v-if="course.institute"
                                        class="flex items-center gap-x-2 bg-gray-200 p-2 px-3 rounded-md"
                                    >
                                        <svg
                                            width="20"
                                            height="20"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M3.9999 14.9999V12.3312M9.33323 14.9999V12.3312M14.6666 14.9999V12.3312M19.9999 14.9999V12.3312M2.3999 18.3999H21.5999V21.5999H2.3999V18.3999ZM2.3999 8.7999V6.66657L11.6054 2.3999L21.5999 6.66657V8.7999H2.3999Z"
                                                stroke="currentcolor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                        <span class="font-semibold text-sm">{{ course.institute.name }}</span>
                                    </div>
                                    <!-- Enrolls -->
                                    <div class="flex items-center gap-x-2 bg-gray-200 p-2 px-3 rounded-md">
                                        <svg
                                            width="20"
                                            height="20"
                                            viewBox="0 0 25 24"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M16.0993 12.8093L15.5538 11.9713L15.5398 11.9803L15.5262 11.9899L16.0993 12.8093ZM16.0993 11.4332L15.5064 12.2384L15.551 12.2712L15.599 12.299L16.0993 11.4332ZM10.5839 7.37187L11.1769 6.56662L11.1487 6.54585L11.1191 6.52709L10.5839 7.37187ZM9.42766 7.97847L10.4277 7.97557L10.4276 7.96682L10.4274 7.95807L9.42766 7.97847ZM9.45078 15.9562L8.45079 15.9591L8.45087 15.9874L8.45255 16.0156L9.45078 15.9562ZM10.6494 16.621L11.1337 17.4959L11.1796 17.4705L11.2226 17.4405L10.6494 16.621ZM6.4999 3.3999H18.4999V1.3999H6.4999V3.3999ZM21.0999 5.9999V17.9999H23.0999V5.9999H21.0999ZM18.4999 20.5999H6.4999V22.5999H18.4999V20.5999ZM3.8999 17.9999V5.9999H1.8999V17.9999H3.8999ZM6.4999 20.5999C5.06396 20.5999 3.8999 19.4358 3.8999 17.9999H1.8999C1.8999 20.5404 3.95939 22.5999 6.4999 22.5999V20.5999ZM21.0999 17.9999C21.0999 19.4358 19.9358 20.5999 18.4999 20.5999V22.5999C21.0404 22.5999 23.0999 20.5404 23.0999 17.9999H21.0999ZM18.4999 3.3999C19.9358 3.3999 21.0999 4.56396 21.0999 5.9999H23.0999C23.0999 3.45939 21.0404 1.3999 18.4999 1.3999V3.3999ZM6.4999 1.3999C3.95939 1.3999 1.8999 3.45939 1.8999 5.9999H3.8999C3.8999 4.56396 5.06396 3.3999 6.4999 3.3999V1.3999ZM16.6449 13.6474C17.0793 13.3646 17.6284 12.8602 17.6274 12.1C17.6263 11.3173 17.0491 10.827 16.5997 10.5673L15.599 12.299C15.7036 12.3595 15.7229 12.3909 15.7083 12.3723C15.6867 12.3448 15.6276 12.2501 15.6274 12.1027C15.6272 11.9591 15.6833 11.8745 15.692 11.8627C15.6963 11.8569 15.6655 11.8985 15.5538 11.9713L16.6449 13.6474ZM16.6923 10.6279L11.1769 6.56662L9.99098 8.17711L15.5064 12.2384L16.6923 10.6279ZM11.1191 6.52709C10.6484 6.22898 9.98756 6.05818 9.36255 6.35001C8.6823 6.66763 8.41466 7.35185 8.42786 7.99888L10.4274 7.95807C10.4263 7.89951 10.4384 7.91844 10.4077 7.97728C10.373 8.04359 10.3053 8.11708 10.2087 8.1622C10.0227 8.24903 9.9477 8.1526 10.0488 8.21664L11.1191 6.52709ZM8.42766 7.98137L8.45079 15.9591L10.4508 15.9533L10.4277 7.97557L8.42766 7.98137ZM8.45255 16.0156C8.48436 16.5499 8.67011 17.2622 9.34054 17.6203C10.0006 17.9728 10.6879 17.7426 11.1337 17.4959L10.1652 15.746C10.0661 15.8009 10.0271 15.804 10.0428 15.8018C10.0665 15.7985 10.1641 15.7928 10.2827 15.8561C10.403 15.9204 10.4551 16.0077 10.4677 16.0346C10.476 16.0523 10.4562 16.018 10.449 15.8968L8.45255 16.0156ZM11.2226 17.4405L16.6725 13.6288L15.5262 11.9899L10.0763 15.8015L11.2226 17.4405Z"
                                                class="fill-current"
                                            />
                                        </svg>
                                        <span class="font-semibold text-sm">{{ course.enrolls_count || 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- content rendering here -->
                        <div class="grid grid-cols-1 lg:grid-cols-5 w-full gap-4 py-4 border-t border-gray-300">
                            <div class="lg:col-span-2">
                                <h3 class="text-gray-500 font-bold pb-2">Content</h3>
                                <div
                                    v-if="course.activities && course.activities.length > 0"
                                    class="bg-gray-200 rounded-xl p-2"
                                >
                                    <h3 class="font-bold text-xs pb-2 px-2">
                                        {{ course.activities.length }} Activities
                                    </h3>
                                    <div class="flex flex-col space-y-1">
                                        <div
                                            v-for="activity in course.activities"
                                            :key="activity.timeline_id"
                                            class="flex items-center cursor-pointer gap-2 p-1 bg-white rounded-lg border hover:shadow-sm transition-shadow"
                                            :class="
                                                selectedActivity?.timeline_id === activity.timeline_id
                                                    ? 'border-blue-800 border-2 shadow-md'
                                                    : 'border-gray-300'
                                            "
                                            @click="selectActivity(activity)"
                                        >
                                            <div class="relative flex-shrink-0 flex items-center justify-center">
                                                <img
                                                    :src="activity.cover"
                                                    alt="Activity cover"
                                                    class="w-20 h-14 object-cover rounded-md bg-gray-300"
                                                />
                                                <div
                                                    v-if="activity.type === 'video'"
                                                    class="absolute inset-0 flex items-center justify-center bg-black/30 rounded-md"
                                                >
                                                    <svg
                                                        class="w-6 h-6 text-white"
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
                                                            clip-rule="evenodd"
                                                        ></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex flex-col space-y-1 overflow-hidden">
                                                <h3 class="text-gray-400 text-xs capitalize">{{ activity.type }}</h3>
                                                <p
                                                    class="text-sm font-bold truncate"
                                                    v-html="activity.sanitizedTitle || activity.title"
                                                ></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:col-span-3">
                                <h3 class="text-gray-500 font-bold pb-2">Activity Preview</h3>
                                <div class="p-2 min-h-96 bg-gray-200 rounded-xl overflow-y-auto">
                                    <div v-if="selectedActivity" class="w-full flex flex-col space-y-4">
                                        <h3
                                            class="font-bold text-lg pb-2 px-2"
                                            v-html="selectedActivity.sanitizedTitle || selectedActivity.title"
                                        ></h3>

                                        <!-- Ayhoot-specific preview -->
                                        <div v-if="selectedActivity.type === 'ayhoot'">
                                            <img
                                                :src="selectedActivity.cover"
                                                :alt="selectedActivity.title"
                                                class="w-full h-auto max-h-[40vh] object-cover rounded-lg bg-gray-800"
                                            />
                                            <h3 class="text-gray-500 py-3 px-4 text-sm font-semibold">Description</h3>
                                            <p class="text-sm px-4 font-medium whitespace-pre-wrap">
                                                {{ selectedActivity.description }}
                                            </p>
                                            <div class="px-4 py-4">
                                                <ResponsiveButton
                                                    :to="`/admin/exam/${selectedActivity.id}`"
                                                    type="light-gray"
                                                    class="w-full py-2"
                                                    >View Ayhoot</ResponsiveButton
                                                >
                                            </div>
                                            <h3 class="px-2 font-bold text-gray-500 text-xs py-2">Questions</h3>
                                            <div class="flex flex-col space-y-1">
                                                <component
                                                    v-for="question in selectedActivity.questions"
                                                    :is="getComponent(question.type)"
                                                    :key="question.id"
                                                    :question="question"
                                                    class="w-full"
                                                />
                                            </div>
                                        </div>

                                        <!-- Document-specific preview -->
                                        <div v-if="selectedActivity.type === 'document'">
                                            <!-- PDF Preview -->
                                            <div v-if="selectedActivityDocumentType === 'pdf'">
                                                <embed
                                                    :src="selectedActivity.path"
                                                    type="application/pdf"
                                                    class="w-full h-[60vh] rounded-lg"
                                                />
                                            </div>

                                            <!-- TXT Preview -->
                                            <div v-else-if="selectedActivityDocumentType === 'txt'">
                                                <iframe
                                                    :src="selectedActivity.path"
                                                    class="w-full h-[60vh] bg-white rounded-lg border"
                                                ></iframe>
                                            </div>

                                            <!-- Office Docs Preview (using Google Docs Viewer) -->
                                            <div
                                                v-else-if="
                                                    ['doc', 'docx', 'ppt', 'pptx'].includes(
                                                        selectedActivityDocumentType
                                                    )
                                                "
                                            >
                                                <p class="text-xs text-center text-gray-500 pb-2">
                                                    Preview loaded via Google Docs Viewer.
                                                    <a :href="selectedActivity.path" target="_blank" class="underline"
                                                        >Open original</a
                                                    >.
                                                </p>
                                                <iframe
                                                    :src="`https://docs.google.com/gview?url=${selectedActivity.path}&embedded=true`"
                                                    class="w-full h-[60vh] border-0 rounded-lg"
                                                ></iframe>
                                            </div>

                                            <!-- Fallback for other document types (e.g., images) -->
                                            <div v-else>
                                                <img
                                                    :src="selectedActivity.path"
                                                    :alt="selectedActivity.title"
                                                    class="w-full h-auto max-h-[40vh] object-cover rounded-lg bg-gray-300"
                                                />
                                            </div>

                                            <!-- Download button for all document types -->
                                            <div class="p-4">
                                                <a
                                                    :href="selectedActivity.path"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    download
                                                    class="block w-full text-center"
                                                >
                                                    <ResponsiveButton type="light-gray" class="w-full py-2">
                                                        Download {{ selectedActivityDocumentType.toUpperCase() }} File
                                                    </ResponsiveButton>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Video-specific preview -->
                                        <div v-if="selectedActivity.type === 'video'">
                                            <video
                                                :src="selectedActivity.path"
                                                controls
                                                class="w-full h-auto rounded-lg bg-black"
                                            >
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                    </div>
                                    <div v-else class="flex items-center justify-center h-full text-gray-500 p-8">
                                        Select an activity to see a preview.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2nd Col - Action Buttons (Desktop) -->
                    <div class="hidden lg:block lg:col-span-1">
                        <h3 class="font-bold text-sm text-gray-500">Course Management</h3>
                        <div class="flex flex-col space-y-2 pt-2">
                            <!-- Buttons based on status -->
                            <template v-if="course.status === 'pending'">
                                <button
                                    @click="updateStatus('published')"
                                    class="flex group items-center justify-center w-full bg-green-600 text-white px-4 py-3 rounded-md transition duration-300 hover:bg-green-700 shadow-[0_2px_0_rgb(21,128,61)] hover:shadow-none hover:translate-y-[2px]"
                                >
                                    <div class="w-full font-bold">Publish</div>
                                </button>
                                <button
                                    @click="openRejectionModal"
                                    class="flex group items-center justify-center w-full bg-red-600 text-white px-4 py-3 rounded-md transition duration-300 hover:bg-red-700 shadow-[0_2px_0_rgb(185,28,28)] hover:shadow-none hover:translate-y-[2px]"
                                >
                                    <div class="w-full font-bold">Reject</div>
                                </button>
                            </template>
                            <template v-else-if="course.status === 'published' || course.status === 'rejected'">
                                <button
                                    @click="updateStatus('pending')"
                                    class="flex group items-center justify-center w-full bg-yellow-500 text-white px-4 py-3 rounded-md transition duration-300 hover:bg-yellow-600 shadow-[0_2px_0_rgb(217,119,6)] hover:shadow-none hover:translate-y-[2px]"
                                >
                                    <div class="w-full font-bold">Pend Action</div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Rejection Modal -->
                <Modal v-model="showRejectionModal" title="Reject Course" size="md">
                    <div class="space-y-4">
                        <AySelect v-model="rejectionData.reason" label="Rejection Reason">
                            <option disabled value="">Please select a reason</option>
                            <option value="inappropriate">Inappropriate Content</option>
                            <option value="quality">Low Quality Content</option>
                            <option value="other">Other</option>
                        </AySelect>
                        <AyTextarea
                            v-model="rejectionData.note"
                            label="Optional Note"
                            :rows="3"
                            placeholder="Provide more details..."
                        />
                    </div>
                    <template #footer>
                        <AyButton type="light-gray" @click="closeRejectionModal">Cancel</AyButton>
                        <button
                            @click="handleRejectionSubmit"
                            :disabled="!rejectionData.reason"
                            class="text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded-md font-semibold text-sm disabled:opacity-50 cursor-pointer"
                        >
                            Confirm Rejection
                        </button>
                    </template>
                </Modal>
            </main>
        </div>
    </div>
</template>

<script>
import { apiCall } from '@/services/apiService'
import DOMPurify from 'dompurify'
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import { RouterLink } from 'vue-router'
import DashVerified from '@/components/atoms/icons/DashVerified.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashCourses from '@/components/atoms/icons/DashCourses.vue'
import DashArrowRight from '@/components/atoms/icons/DashArrowRight.vue'
import QuizPreview from '@/components/previews/QuizPreview.vue'
import PuzzlePreview from '@/components/previews/PuzzlePreview.vue'
import TypeAnswerPreview from '@/components/previews/TypeAnswerPreview.vue'
import TrueOrFalsePreview from '@/components/previews/TrueOrFalsePreview.vue'
import SlidePreview from '@/components/previews/SlidePreview.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import AyTextarea from '@/components/atoms/form/AyTextarea.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import Modal from '@/components/shared/Modal.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'CoursePreview',
    components: {
        Sidebar,
        Header,
        RouterLink,
        DashVerified,
        ResponsiveButton,
        DashCourses,
        DashArrowRight,
        QuizPreview,
        TrueOrFalsePreview,
        PuzzlePreview,
        TypeAnswerPreview,
        SlidePreview,
        AySelect,
        AyTextarea,
        AyButton,
        Modal,
    },
    data() {
        return {
            isSidebarOpen: false,
            isLoading: true,
            error: null,
            course: null,
            selectedActivity: null,
            // --- NEW DATA ---
            showRejectionModal: false,
            rejectionData: {
                reason: '',
                note: '',
            },
        }
    },
    computed: {
        teacherName() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user?.name || 'Teacher'
        },
        teacherUsername() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user?.username || 'Teacher'
        },
        selectedActivityDocumentType() {
            if (this.selectedActivity?.type !== 'document' || !this.selectedActivity.path) {
                return null
            }
            const extension = this.selectedActivity.path.split('.').pop()
            return extension ? extension.split('?')[0].toLowerCase() : null
        },
    },
    methods: {
        getComponent(type) {
            const componentMap = {
                multiple_choice: 'QuizPreview',
                true_false: 'TrueOrFalsePreview',
                sequence: 'PuzzlePreview',
                ordinary: 'TypeAnswerPreview',
                slide: 'SlidePreview',
            }
            return componentMap[type] || 'div'
        },
        getAssetUrl(path, isProfile = false) {
            const fallback = isProfile ? '/imgHolders/profile.svg' : '/imgHolders/CardHolderImage.jpg'
            if (!path) return fallback
            if (path.startsWith('http')) return path
            if (path.startsWith('/')) return path
            return `/uploads/${path.replace(/\\/g, '/')}`
        },
        formatPrice(price) {
            if (price === null || price === undefined) return ''
            return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
        },
        selectActivity(activity) {
            this.selectedActivity = activity
        },
        async fetchCourseData() {
            this.isLoading = true
            this.error = null
            try {
                const courseId = this.$route.params.id
                const response = await apiCall('GET', `/api/admin/course/${courseId}`)
                const rawCourse = response.course

                // Some columns are model-cast to arrays while others stay raw JSON
                // strings, so only JSON.parse strings.
                const parseMaybe = (value, fallback) => {
                    if (value === null || value === undefined || value === '') return fallback
                    if (typeof value !== 'string') return value
                    try {
                        return JSON.parse(value)
                    } catch {
                        return fallback
                    }
                }

                const options = parseMaybe(rawCourse.options, {})
                const timeline = options.timeline || []

                const questionFetchPromises = timeline
                    .filter((item) => item.type === 'exam')
                    .map((item) => apiCall('GET', `/api/admin/exam/${item.id}/questions`))

                const questionResponses = await Promise.all(questionFetchPromises)

                let questionResponseIndex = 0
                const activities = timeline
                    .map((item, index) => {
                        if (item.type === 'exam') {
                            const examDetails = rawCourse.exams.find((e) => e.id === item.id)
                            if (!examDetails) return null

                            const questionsData = questionResponses[questionResponseIndex++]
                            const normalizedQuestions = questionsData.questions.map((q) => {
                                const qOptions = parseMaybe(q.q_options, {})
                                return {
                                    ...q,
                                    options: parseMaybe(q.options, []),
                                    sequence_options: parseMaybe(q.sequence_options, []),
                                    sanitizedTitle: DOMPurify.sanitize(q.title),
                                    sanitizedSlide: q.slide ? DOMPurify.sanitize(q.slide) : '',
                                    backgroundImageUrl: this.getAssetUrl(qOptions.background_image),
                                }
                            })

                            return {
                                ...examDetails,
                                type: 'ayhoot',
                                timeline_id: `exam-${item.id}-${index}`,
                                cover: this.getAssetUrl(examDetails.cover),
                                sanitizedTitle: DOMPurify.sanitize(examDetails.title),
                                questions: normalizedQuestions,
                            }
                        } else if (item.type === 'file') {
                            const docType = item.path?.split('.').pop()?.toLowerCase() || ''
                            const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp']
                            let cover = '/imgHolders/bg1.jpg' // default placeholder
                            if (imageTypes.includes(docType)) {
                                cover = this.getAssetUrl(item.path)
                            }

                            return {
                                ...item,
                                type: 'document',
                                timeline_id: `file-${index}`,
                                cover: cover,
                                path: this.getAssetUrl(item.path),
                                sanitizedTitle: DOMPurify.sanitize(item.title),
                            }
                        } else if (item.type === 'video') {
                            return {
                                ...item,
                                type: 'video',
                                timeline_id: `video-${index}`,
                                cover: '/imgHolders/bg1.jpg',
                                path: this.getAssetUrl(item.path),
                                sanitizedTitle: DOMPurify.sanitize(item.title),
                            }
                        }
                        return null
                    })
                    .filter(Boolean)

                this.course = {
                    ...rawCourse,
                    sanitizedTitle: DOMPurify.sanitize(rawCourse.title),
                    activities,
                    enrolls_count: response.enrollsCount,
                }

                if (this.course.activities.length > 0) {
                    this.selectedActivity = this.course.activities[0]
                }
            } catch (err) {
                console.error('Failed to fetch Course data:', err)
                this.error = 'Could not load the Course. Please try again later.'
            } finally {
                this.isLoading = false
            }
        },
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        // --- NEW METHODS ---
        statusClass(status) {
            return {
                'bg-green-100 text-green-800': ['published'].includes(status),
                'bg-yellow-100 text-yellow-800': ['pending'].includes(status),
                'bg-red-100 text-red-800': ['rejected'].includes(status),
                'bg-gray-100 text-gray-800': !['published', 'pending', 'rejected'].includes(status),
            }
        },
        async updateStatus(newStatus, rejectionPayload = {}) {
            if (!this.course) return
            try {
                const payload = { status: newStatus, ...rejectionPayload }
                await apiCall('POST', `/api/admin/course/${this.course.id}/status`, payload)
                await alertDialog({ message: `Course status updated to ${newStatus}.` })
                this.course.status = newStatus
            } catch (error) {
                await alertDialog({ message: error.message || 'Failed to update course status.' })
                console.error('Failed to update status:', error)
            }
        },
        openRejectionModal() {
            this.rejectionData = { reason: '', note: '' }
            this.showRejectionModal = true
        },
        closeRejectionModal() {
            this.showRejectionModal = false
        },
        async handleRejectionSubmit() {
            if (!this.rejectionData.reason) {
                await alertDialog({ message: 'A rejection reason is required.' })
                return
            }
            await this.updateStatus('rejected', this.rejectionData)
            this.closeRejectionModal()
        },
    },
    mounted() {
        this.fetchCourseData()
    },
}
</script>

<style scoped>
.whitespace-pre-wrap {
    white-space: pre-wrap;
}
</style>
