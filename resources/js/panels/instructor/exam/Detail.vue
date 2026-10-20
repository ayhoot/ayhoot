<template>
    <InstructorLayout :secondary="false">
                <!-- Loading State -->
                <div v-if="isLoading" class="flex items-center justify-center h-full p-8">
                    <div class="text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mb-2"></div>
                        <p class="text-gray-500 font-semibold">{{ t('ayhootPreview.loading') }}</p>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="flex items-center justify-center h-full p-8">
                    <p class="text-red-500 font-semibold">{{ error }}</p>
                </div>

                <!-- Content -->
                <div v-else-if="ayhoot">
                    <!-- Sticky Header with Author Info and Options -->
                    <div class="sticky top-0 bg-white/80 backdrop-blur-sm z-40 border-b border-gray-200">
                        <div class="w-full px-6 py-3 flex items-center justify-between">
                            <!-- profile card -->
                            <div v-if="creator" class="flex items-center gap-2">
                                <RouterLink :to="creator.profileUrl">
                                    <img
                                        :src="getCoverUrl(creator.avatar, true)"
                                        :alt="creator.name"
                                        class="w-10 h-10 object-cover rounded-full border-2 border-blue-400"
                                    />
                                </RouterLink>
                                <div>
                                    <div class="flex items-center gap-1">
                                        <h3 class="font-bold text-sm text-gray-900">{{ creator.name }}</h3>
                                        <DashVerified v-if="creator.verified_at" />
                                    </div>
                                    <RouterLink :to="creator.profileUrl" class="text-xs font-semibold text-gray-500 hover:text-blue-600 transition-colors">
                                        @{{ creator.username }}
                                    </RouterLink>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="relative" ref="optionsDropdown">
                                    <ResponsiveButton @click="toggleOptions" class="p-1 px-2" type="light-gray">
                                        {{ t('ayhootPreview.options.button') }}
                                    </ResponsiveButton>
                                    <!-- Options Menu -->
                                    <div
                                        v-if="isOptionsOpen"
                                        :class="[
                                            'absolute mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50',
                                            { 'right-0': !isRTL, 'left-0': isRTL },
                                        ]"
                                    >
                                        <ul class="text-sm font-semibold py-1">
                                            <li
                                                class="px-1 relative group"
                                                :class="{
                                                    'cursor-not-allowed text-gray-400': !ayhoot.addtocourse,
                                                    'cursor-pointer hover:bg-gray-100': ayhoot.addtocourse,
                                                }"
                                                @click="ayhoot.addtocourse ? handleAddToCourse() : null"
                                            >
                                                <div class="p-2 flex items-center gap-2 rounded-md">
                                                    <DashCourses />
                                                    <span>{{ t('ayhootPreview.options.addToCourse') }}</span>
                                                </div>
                                                <div
                                                    v-if="!ayhoot.addtocourse"
                                                    class="absolute -top-8 hidden group-hover:block bg-black text-white text-xs z-100 font-medium rounded-md px-2 py-1"
                                                    :class="{ 'right-0': isRTL, 'left-0': !isRTL }"
                                                >
                                                    {{ t('ayhootPreview.options.notAllowedTooltip') }}
                                                </div>
                                            </li>
                                            <li
                                                class="px-1 relative group"
                                                :class="{
                                                    'cursor-not-allowed text-gray-400': !ayhoot.duplicatable,
                                                    'cursor-pointer hover:bg-gray-100': ayhoot.duplicatable,
                                                }"
                                                @click="ayhoot.duplicatable ? handleDuplicate() : null"
                                            >
                                                <div class="p-2 flex items-center gap-2 rounded-md">
                                                    <DashDuplicateII />
                                                    <span>{{ t('ayhootPreview.options.duplicate') }}</span>
                                                </div>
                                                <div
                                                    v-if="!ayhoot.duplicatable"
                                                    class="absolute -top-8 hidden group-hover:block bg-black text-white text-xs z-100 font-medium rounded-md px-2 py-1"
                                                    :class="{ 'right-0': isRTL, 'left-0': !isRTL }"
                                                >
                                                    {{ t('ayhootPreview.options.notAllowedTooltip') }}
                                                </div>
                                            </li>

                                            <li class="px-1 cursor-pointer hover:bg-gray-100" @click="handleShare">
                                                <div class="p-2 flex items-center gap-2 rounded-md">
                                                    <DashShare />
                                                    <span>{{ t('ayhootPreview.options.share') }}</span>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-4 w-full gap-4 p-6">
                        <!-- 1st Col - Main Info and Questions -->
                        <div class="lg:col-span-3">
                            <div class="grid grid-cols-1 lg:grid-cols-4 w-full gap-8 pb-4">
                                <div class="lg:col-span-1">
                                    <img
                                        :src="getCoverUrl(ayhoot.cover)"
                                        :alt="ayhoot.title"
                                        class="w-full aspect-video lg:aspect-square object-cover rounded-lg bg-gray-200 shadow-sm"
                                    />
                                </div>
                                <div class="lg:col-span-3">
                                    <div class="flex items-center font-semibold gap-1 text-gray-600 text-sm py-2">
                                        <DashAyhoot /><span class="uppercase tracking-wider">{{ t('ayhootPreview.details.title') }}</span>
                                    </div>
                                    <h2 class="text-3xl font-extrabold text-gray-900 leading-tight">{{ ayhoot.title }}</h2>
                                    
                                    <div v-if="ayhoot.description" class="mt-4">
                                        <p class="text-sm font-bold text-gray-400 uppercase tracking-tighter">
                                            {{ t('ayhootPreview.details.description') }}
                                        </p>
                                        <p class="font-medium text-gray-700 leading-relaxed">{{ ayhoot.description }}</p>
                                    </div>

                                    <div class="mt-6">
                                        <p class="text-sm font-bold text-gray-400 uppercase tracking-tighter mb-3">
                                            {{ t('ayhootPreview.details.details') }}
                                        </p>
                                        <div class="flex items-center flex-wrap gap-x-2 gap-y-2 text-sm text-gray-600">
                                            <div
                                                v-if="ayhoot.category?.title"
                                                class="flex items-center gap-x-2 bg-gray-200 p-2 px-3 rounded-md"
                                                :title="t('ayhootPreview.details.category')"
                                            >
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M9.0666 20.1699C9.61889 20.1699 10.0666 19.7222 10.0666 19.1699C10.0666 18.6177 9.61889 18.1699 9.0666 18.1699V20.1699ZM4.26718 15.9701L3.26718 15.9701L4.26718 15.9701ZM5.2666 2.39986C5.26658 1.84757 4.81884 1.39988 4.26656 1.3999C3.71427 1.39993 3.26658 1.84766 3.2666 2.39995L5.2666 2.39986ZM9.0666 8.31677C9.61889 8.31677 10.0666 7.86906 10.0666 7.31677C10.0666 6.76449 9.61889 6.31677 9.0666 6.31677V8.31677ZM18.6666 9.86657L18.6666 8.86657L18.6666 9.86657ZM13.8666 9.86655L13.8666 10.8665H13.8666L13.8666 9.86655ZM19.7334 2.3999L20.7334 2.39992C20.7334 2.1347 20.6281 1.88034 20.4405 1.6928C20.253 1.50526 19.9987 1.3999 19.7334 1.3999V2.3999ZM19.7333 8.79993L20.7333 8.79995V8.79995L19.7333 8.79993ZM12.7999 8.79987L11.7999 8.79987V8.79987L12.7999 8.79987ZM12.8 4.53324L12.1356 3.78583C11.9221 3.9756 11.8 4.2476 11.8 4.53323L12.8 4.53324ZM15.1999 2.3999V1.3999C14.955 1.3999 14.7186 1.48978 14.5356 1.6525L15.1999 2.3999ZM18.6666 21.5999L18.6666 20.5999L18.6666 21.5999ZM13.8666 21.5999L13.8666 22.5999L13.8666 21.5999ZM19.7334 14.1332L20.7334 14.1333C20.7334 13.868 20.6281 13.6137 20.4405 13.4261C20.253 13.2386 19.9987 13.1332 19.7334 13.1332V14.1332ZM19.7333 20.5333L20.7333 20.5333V20.5333L19.7333 20.5333ZM12.7999 20.5332L11.7999 20.5332V20.5332L12.7999 20.5332ZM12.8 16.2666L12.1356 15.5192C11.9221 15.7089 11.8 15.9809 11.8 16.2666L12.8 16.2666ZM15.1999 14.1332V13.1332C14.955 13.1332 14.7186 13.2231 14.5356 13.3858L15.1999 14.1332ZM9.0666 18.1699H7.46718V20.1699H9.0666V18.1699ZM5.26718 15.97L5.26681 7.31673L3.26681 7.31681L3.26718 15.9701L5.26718 15.97ZM5.26681 7.31673L5.2666 2.39986L3.2666 2.39995L3.26681 7.31681L5.26681 7.31673ZM9.0666 6.31677H4.26681V8.31677H9.0666V6.31677ZM7.46718 18.1699C6.25219 18.1699 5.26724 17.185 5.26718 15.97L3.26718 15.9701C3.26728 18.2896 5.14766 20.1699 7.46718 20.1699V18.1699ZM18.6666 8.86657L13.8666 8.86655L13.8666 10.8665L18.6666 10.8666L18.6666 8.86657ZM18.7334 2.39988L18.7333 8.79991L20.7333 8.79995L20.7334 2.39992L18.7334 2.39988ZM18.6666 10.8666C19.808 10.8666 20.7333 9.94132 20.7333 8.79995L18.7333 8.7999C18.7333 8.83672 18.7034 8.86657 18.6666 8.86657L18.6666 10.8666ZM11.7999 8.79987C11.7999 9.94126 12.7252 10.8665 13.8666 10.8665L13.8666 8.86655C13.8298 8.86655 13.7999 8.8367 13.7999 8.79988L11.7999 8.79987ZM13.7999 8.79988L13.8 4.53324L11.8 4.53323L11.7999 8.79987L13.7999 8.79988ZM15.1999 3.3999H19.7334V1.3999H15.1999V3.3999ZM14.5356 1.6525L12.1356 3.78583L13.4643 5.28064L15.8643 3.14731L14.5356 1.6525ZM18.6666 20.5999L13.8666 20.5999L13.8666 22.5999L18.6666 22.5999L18.6666 20.5999ZM18.7334 14.1332L18.7333 20.5332L20.7333 20.5333L20.7334 14.1333L18.7334 14.1332ZM18.6666 22.5999C19.808 22.5999 20.7333 21.6747 20.7333 20.5333L18.7333 20.5333C18.7333 20.5701 18.7034 20.5999 18.6666 20.5999L18.6666 22.5999ZM11.7999 20.5332C11.7999 21.6746 12.7252 22.5999 13.8666 22.5999L13.8666 20.5999C13.8298 20.5999 13.7999 20.57 13.7999 20.5332L11.7999 20.5332ZM13.7999 20.5332L13.8 16.2666L11.8 16.2666L11.7999 20.5332L13.7999 20.5332ZM15.1999 15.1332H19.7334V13.1332H15.1999V15.1332ZM14.5356 13.3858L12.1356 15.5192L13.4643 17.014L15.8643 14.8806L14.5356 13.3858Z" class="fill-current" />
                                                </svg>
                                                <span class="font-semibold">{{ ayhoot.category.title }}</span>
                                            </div>
                                            <div
                                                class="flex items-center gap-x-2 bg-blue-200 p-2 px-3 rounded-md"
                                                :title="t('ayhootPreview.details.price')"
                                            >
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-blue-500">
                                                    <path d="M7.79995 7.8H16.2M7.79995 12.6H16.2M5.75995 3H18.24C19.1015 3 19.8 3.80589 19.8 4.8V21L17.2 19.2L14.6 21L12 19.2L9.39995 21L6.79995 19.2L4.19995 21V4.8C4.19995 3.80589 4.89839 3 5.75995 3Z" class="stroke-current" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <span class="font-semibold text-blue-600">{{ formatPrice(ayhoot.price) }}</span>
                                            </div>
                                            <div
                                                v-if="ayhoot.institute"
                                                class="flex items-center gap-x-2 bg-gray-200 p-2 px-3 rounded-md"
                                                :title="t('ayhootPreview.details.institute')"
                                            >
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3.9999 14.9999V12.3312M9.33323 14.9999V12.3312M14.6666 14.9999V12.3312M19.9999 14.9999V12.3312M2.3999 18.3999H21.5999V21.5999H2.3999V18.3999ZM2.3999 8.7999V6.66657L11.6054 2.3999L21.5999 6.66657V8.7999H2.3999Z" stroke="currentcolor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <span class="font-semibold">{{ ayhoot.institute.name }}</span>
                                            </div>
                                            <div class="flex text-sm gap-2 items-center bg-gray-200 p-2 px-3 rounded-md">
                                                <svg width="20" height="20" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M16.0993 12.8093L15.5538 11.9713L15.5398 11.9803L15.5262 11.9899L16.0993 12.8093ZM16.0993 11.4332L15.5064 12.2384L15.551 12.2712L15.599 12.299L16.0993 11.4332ZM10.5839 7.37187L11.1769 6.56662L11.1487 6.54585L11.1191 6.52709L10.5839 7.37187ZM9.42766 7.97847L10.4277 7.97557L10.4276 7.96682L10.4274 7.95807L9.42766 7.97847ZM9.45078 15.9562L8.45079 15.9591L8.45087 15.9874L8.45255 16.0156L9.45078 15.9562ZM10.6494 16.621L11.1337 17.4959L11.1796 17.4705L11.2226 17.4405L10.6494 16.621ZM6.4999 3.3999H18.4999V1.3999H6.4999V3.3999ZM21.0999 5.9999V17.9999H23.0999V5.9999H21.0999ZM18.4999 20.5999H6.4999V22.5999H18.4999V20.5999ZM3.8999 17.9999V5.9999H1.8999V17.9999H3.8999ZM6.4999 20.5999C5.06396 20.5999 3.8999 19.4358 3.8999 17.9999H1.8999C1.8999 20.5404 3.95939 22.5999 6.4999 22.5999V20.5999ZM21.0999 17.9999C21.0999 19.4358 19.9358 20.5999 18.4999 20.5999V22.5999C21.0404 22.5999 23.0999 20.5404 23.0999 17.9999H21.0999ZM18.4999 3.3999C19.9358 3.3999 21.0999 4.56396 21.0999 5.9999H23.0999C23.0999 3.45939 21.0404 1.3999 18.4999 1.3999V3.3999ZM6.4999 1.3999C3.95939 1.3999 1.8999 3.45939 1.8999 5.9999H3.8999C3.8999 4.56396 5.06396 3.3999 6.4999 3.3999V1.3999ZM16.6449 13.6474C17.0793 13.3646 17.6284 12.8602 17.6274 12.1C17.6263 11.3173 17.0491 10.827 16.5997 10.5673L15.599 12.299C15.7036 12.3595 15.7229 12.3909 15.7083 12.3723C15.6867 12.3448 15.6276 12.2501 15.6274 12.1027C15.6272 11.9591 15.6833 11.8745 15.692 11.8627C15.6963 11.8569 15.6655 11.8985 15.5538 11.9713L16.6449 13.6474ZM16.6923 10.6279L11.1769 6.56662L9.99098 8.17711L15.5064 12.2384L16.6923 10.6279ZM11.1191 6.52709C10.6484 6.22898 9.98756 6.05818 9.36255 6.35001C8.6823 6.66763 8.41466 7.35185 8.42786 7.99888L10.4274 7.95807C10.4263 7.89951 10.4384 7.91844 10.4077 7.97728C10.373 8.04359 10.3053 8.11708 10.2087 8.1622C10.0227 8.24903 9.9477 8.1526 10.0488 8.21664L11.1191 6.52709ZM8.42766 7.98137L8.45079 15.9591L10.4508 15.9533L10.4277 7.97557L8.42766 7.98137ZM8.45255 16.0156C8.48436 16.5499 8.67011 17.2622 9.34054 17.6203C10.0006 17.9728 10.6879 17.7426 11.1337 17.4959L10.1652 15.746C10.0661 15.8009 10.0271 15.804 10.0428 15.8018C10.0665 15.7985 10.1641 15.7928 10.2827 15.8561C10.403 15.9204 10.4551 16.0077 10.4677 16.0346C10.476 16.0523 10.4562 16.018 10.449 15.8968L8.45255 16.0156ZM11.2226 17.4405L16.6725 13.6288L15.5262 11.9899L10.0763 15.8015L11.2226 17.4405Z" fill="currentColor" />
                                                </svg>
                                                <span class="font-semibold">{{ ayhoot.plays || 0 }}</span>
                                                <span class="font-semibold">{{ t('ayhootPreview.details.plays') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action buttons for Mobile Screen -->
                            <div class="lg:hidden mt-4">
                                <h3 class="font-bold text-sm text-gray-500">
                                    {{ t('ayhootPreview.session.title') }}
                                </h3>
                                <div class="flex flex-col space-y-2 pt-2">
                                    <!-- Host Live Button -->
                                    <button
                                        v-if="canHostLive"
                                        @click="showHostLiveModal = true"
                                        class="flex group items-center w-full bg-gradient-to-r from-green-500 to-blue-600 text-white px-4 py-3 rounded-md transition duration-300 hover:from-green-600 hover:to-blue-700 shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px]"
                                    >
                                        <div class="w-full font-bold">🎮 Host Live</div>
                                    </button>
                                    
                                    <router-link
                                        v-if="canViewContent"
                                        :to="lectureLink"
                                        class="flex group items-center w-full bg-[#3450AE] text-white px-4 py-3 rounded-md transition duration-300 hover:bg-[#152B65] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px]"
                                    >
                                        <div class="w-full font-bold">{{ t('ayhootPreview.session.preview') }}</div>
                                        <DashArrowRight />
                                    </router-link>
                                    <button
                                        v-else
                                        @click="handlePurchase"
                                        class="flex group items-center w-full bg-[#3450AE] text-white px-4 py-3 rounded-md transition duration-300 hover:bg-[#152B65] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px]"
                                    >
                                        <div class="w-full font-bold">{{ t('ayhootPreview.session.buy') }}</div>
                                        <DashArrowRight />
                                    </button>
                                </div>
                            </div>

                            <!-- Questions section -->
                            <div v-if="canViewContent">
                                <h3 class="font-bold text-sm text-gray-500 mt-6 mb-4">
                                    {{
                                        t('ayhootPreview.questionsTitle', {
                                            count: ayhoot.questions ? ayhoot.questions.length : 0,
                                        })
                                    }}
                                </h3>
                                <div class="space-y-4">
                                    <component
                                        v-for="question in ayhoot.questions"
                                        :is="getComponent(question.type)"
                                        :key="question.id"
                                        :question="question"
                                    />
                                </div>
                            </div>
                            <!-- Locked Content Message -->
                            <div v-else class="mt-6 p-6 py-12 border border-blue-400 bg-blue-50 rounded-lg text-center shadow-inner">
                                <div class="mb-4">
                                    <svg class="mx-auto h-12 w-12 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <p class="font-bold text-xl text-blue-900">{{ t('ayhootPreview.contentLocked') }}</p>
                                <p class="text-blue-600 mt-1">Please purchase this ayhoot to view the questions and preview the content.</p>
                            </div>
                        </div>

                        <!-- 2nd Col - Action Buttons (Desktop) -->
                        <div class="hidden lg:block lg:col-span-1">
                            <div class="sticky top-24">
                                <h3 class="font-bold text-sm text-gray-500 uppercase tracking-wider mb-2">{{ t('ayhootPreview.session.title') }}</h3>
                                <div class="flex flex-col space-y-3">
                                    <!-- Host Live Button -->
                                    <button
                                        v-if="canHostLive"
                                        @click="showHostLiveModal = true"
                                        class="flex group items-center w-full bg-gradient-to-r from-green-500 to-blue-600 text-white px-4 py-3 rounded-md transition duration-300 hover:from-green-600 hover:to-blue-700 shadow-[0_4px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px]"
                                    >
                                        <div class="w-full font-bold text-lg text-center">🎮 Host Live</div>
                                    </button>
                                    
                                    <router-link
                                        v-if="canViewContent"
                                        :to="lectureLink"
                                        class="flex group items-center w-full bg-[#3450AE] text-white px-4 py-3 rounded-md transition duration-300 hover:bg-[#152B65] shadow-[0_4px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px]"
                                    >
                                        <div class="w-full font-bold text-lg text-center">{{ t('ayhootPreview.session.preview') }}</div>
                                        <DashArrowRight class="ml-2" />
                                    </router-link>
                                    <button
                                        v-else
                                        @click="handlePurchase"
                                        class="flex group items-center w-full bg-[#3450AE] text-white px-4 py-3 rounded-md transition duration-300 hover:bg-[#152B65] shadow-[0_4px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px]"
                                    >
                                        <div class="w-full font-bold text-lg text-center">{{ t('ayhootPreview.session.buy') }}</div>
                                        <DashArrowRight class="ml-2" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

        <template #overlays>
        <!-- Host Live Modal -->
        <Modal v-model="showHostLiveModal" title="🎮 Host Live Session" size="sm">
            <!-- Session Title Input -->
            <div class="mb-4">
                <AyInput
                    v-model="hostLiveTitle"
                    label="Session Title"
                    type="text"
                    :placeholder="`Live: ${ayhoot?.title || 'Session'}`"
                />
            </div>

            <!-- QR Code Display -->
            <div v-if="liveSessionData" class="text-center mb-2">
                <div class="mb-4">
                    <div class="text-sm text-gray-600 mb-2">Game PIN</div>
                    <div class="text-3xl font-bold text-blue-600 tracking-wider">
                        {{ liveSessionData.pin }}
                    </div>
                </div>

                <div class="mb-4">
                    <div class="text-sm text-gray-600 mb-2">Scan to Join</div>
                    <div class="bg-gray-100 rounded-lg p-4 inline-block">
                        <QRCode
                            :value="liveSessionData.join_url"
                            :size="200"
                            level="M"
                            class="mx-auto"
                        />
                    </div>
                </div>

                <div class="text-sm text-gray-600 break-all">
                    {{ liveSessionData.join_url }}
                </div>
            </div>

            <!-- Action Buttons -->
            <template #footer>
                <AyButton
                    v-if="!liveSessionData"
                    type="light-green"
                    :disabled="creatingSession"
                    @click="createLiveSession"
                >
                    <span v-if="creatingSession">Creating...</span>
                    <span v-else>Create Session</span>
                </AyButton>

                <AyButton
                    v-if="liveSessionData"
                    type="dash-blue"
                    @click="goToHostPage"
                >
                    Go to Host Page
                </AyButton>

                <AyButton type="light-gray" @click="showHostLiveModal = false">
                    Close
                </AyButton>
            </template>
        </Modal>
        </template>
    </InstructorLayout>
</template>

<script>
import { apiCall } from "@/services/apiService"
import { getRole } from '@/utils/role'
import { useI18n } from 'vue-i18n'
import { computed, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import QRCode from 'qrcode.vue'
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import DashVerified from '@/components/atoms/icons/DashVerified.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashCourses from '@/components/atoms/icons/DashCourses.vue'
import DashDuplicateII from '@/components/atoms/icons/DashDuplicateII.vue'
import DashShare from '@/components/atoms/icons/DashShare.vue'
import DashAyhoot from '@/components/atoms/icons/DashAyhoot.vue'
import DashArrowRight from '@/components/atoms/icons/DashArrowRight.vue'
import QuizPreview from '@/components/previews/QuizPreview.vue'
import PuzzlePreview from '@/components/previews/PuzzlePreview.vue'
import TypeAnswerPreview from '@/components/previews/TypeAnswerPreview.vue'
import TrueOrFalsePreview from '@/components/previews/TrueOrFalsePreview.vue'
import SlidePreview from '@/components/previews/SlidePreview.vue'
import PollPreview from '@/components/previews/PollPreview.vue'
import Modal from '@/components/shared/Modal.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'AyhootPreview',
    components: {
        InstructorLayout,
        DashVerified,
        ResponsiveButton,
        DashCourses,
        DashDuplicateII,
        DashShare,
        QuizPreview,
        TrueOrFalsePreview,
        PuzzlePreview,
        TypeAnswerPreview,
        SlidePreview,
        PollPreview,
        DashAyhoot,
        DashArrowRight,
        QRCode,
        RouterLink,
        Modal,
        AyButton,
        AyInput,
    },
    setup() {
        const { t, locale } = useI18n()
        const isRTL = computed(() => locale.value === 'fa')
        const router = useRouter()
        return { t, isRTL, router }
    },
    data() {
        return {
            isOptionsOpen: false,
            isLoading: true,
            error: null,
            ayhoot: null,
            examData: null,
            showHostLiveModal: false,
            hostLiveTitle: '',
            liveSessionData: null,
            creatingSession: false,
        }
    },
    computed: {
        creator() {
            if (!this.ayhoot) return null

            if (this.ayhoot.teacher) {
                return {
                    ...this.ayhoot.teacher,
                    profileUrl: `/teacher/profile/${this.ayhoot.teacher.id}`,
                }
            }

            if (this.ayhoot.institute) {
                return {
                    ...this.ayhoot.institute,
                    profileUrl: `/institute/profile/${this.ayhoot.institute.id}`,
                }
            }
            return null
        },
        lectureLink() {
            return this.ayhoot ? `/${getRole()}/ayhoot/${this.ayhoot.id}/preview` : '#'
        },
        canViewContent() {
            if (!this.examData) return false
            // Check flags from the root API response
            return this.examData.has_access || this.examData.is_owner || this.examData.is_free
        },
        canHostLive() {
            // Can host live if they have access to the exam (owner or has access)
            return this.canViewContent && this.ayhoot?.questions?.length > 0
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
                poll: 'PollPreview',
            }
            return componentMap[type] || 'div'
        },
        getCoverUrl(path, isProfile = false) {
            const fallback = isProfile ? '/imgHolders/profile.svg' : '/imgHolders/CardHolderImage.jpg'
            if (!path) return fallback
            if (path.startsWith('http') || path.startsWith('data:')) return path
            return `/uploads/${path.replace(/\\/g, '')}`
        },
        formatPrice(price) {
            if (this.examData?.is_free || !price || parseFloat(price) === 0) {
                return this.t('discover.price.free')
            }
            return this.t('discover.price.paidAmount', { price: Number(price).toLocaleString() })
        },
        async fetchAyhootData() {
            this.isLoading = true
            this.error = null
            try {
                const examId = this.$route.params.id
                if (!examId) throw new Error(this.t('ayhootPreview.errorIdMissing'))

                // Fetch exam details
                const examResponse = await apiCall('GET', `/api/instructor/exam/${examId}/show`)
                
                // Store access meta data from root level
                this.examData = {
                    has_access: examResponse.has_access,
                    needs_purchase: examResponse.needs_purchase,
                    is_owner: examResponse.is_owner,
                    is_free: examResponse.is_free,
                    price: examResponse.price
                }

                // Fetch questions
                const questionsResponse = await this.fetchQuestionsWithErrorHandling(examId)

                const normalizedQuestions = (questionsResponse.questions || []).map((q) => {
                    return {
                        ...q,
                        // Check if strings, otherwise use directly if they are already objects
                        options: typeof q.options === 'string' ? JSON.parse(q.options || '[]') : (q.options || []),
                        q_options: typeof q.q_options === 'string' ? JSON.parse(q.q_options || '{}') : (q.q_options || {}),
                        sequence_options: typeof q.sequence_options === 'string' ? JSON.parse(q.sequence_options || '[]') : (q.sequence_options || []),
                    }
                })

                this.ayhoot = {
                    ...examResponse.exam,
                    questions: normalizedQuestions,
                    duplicatable: false,
                    addtocourse: false,
                }
            } catch (err) {
                console.error('Failed to fetch Ayhoot data:', err)
                this.error = err.response?.data?.message || err.message || this.t('ayhootPreview.errorLoading')
            } finally {
                this.isLoading = false
            }
        },
        async fetchQuestionsWithErrorHandling(examId) {
            try {
                return await apiCall('GET', `/api/instructor/exam/${examId}/questions`)
            } catch (error) {
                if (error.response?.status === 402) {
                    return { questions: [] }
                }
                return { questions: [] }
            }
        },
        toggleOptions() {
            this.isOptionsOpen = !this.isOptionsOpen
            if (this.isOptionsOpen) {
                document.addEventListener('click', this.handleClickOutside)
            } else {
                document.removeEventListener('click', this.handleClickOutside)
            }
        },
        handleClickOutside(event) {
            if (this.$refs.optionsDropdown && !this.$refs.optionsDropdown.contains(event.target)) {
                this.isOptionsOpen = false
                document.removeEventListener('click', this.handleClickOutside)
            }
        },
        handleAddToCourse() {
            this.isOptionsOpen = false
        },
        handleDuplicate() {
            this.isOptionsOpen = false
        },
        async handleShare() {
            this.isOptionsOpen = false
            const url = window.location.href
            try {
                await navigator.clipboard.writeText(url)
            } catch (err) {
                console.error('Failed to copy: ', err)
            }
        },
        async handlePurchase() {
            const token = localStorage.getItem('token')
            if (!token) {
                const currentPath = this.$route.fullPath
                this.$router.push(`/login?redirect=${encodeURIComponent(currentPath)}`)
                return
            }
            this.$router.push(`/checkout/exam/${this.ayhoot.id}`)
        },
        handleWindowFocus() {
            this.fetchAyhootData()
        },
        async createLiveSession() {
            this.creatingSession = true
            try {
                const response = await apiCall('POST', '/api/live-session/create', {
                    exam_id: this.ayhoot.id,
                    title: this.hostLiveTitle || `Live: ${this.ayhoot.title}`,
                    description: `Live session for exam: ${this.ayhoot.title}`
                })
                
                this.liveSessionData = response
            } catch (error) {
                console.error('Error creating live session:', error)
                await alertDialog({ message: 'Failed to create live session. Please try again.' })
            } finally {
                this.creatingSession = false
            }
        },
        goToHostPage() {
            if (this.liveSessionData) {
                this.showHostLiveModal = false
                this.$router.push(`/${getRole()}/host-live/${this.liveSessionData.session.id}`)
            }
        },
    },
    mounted() {
        this.fetchAyhootData()
        window.addEventListener('focus', this.handleWindowFocus)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside)
        window.removeEventListener('focus', this.handleWindowFocus)
    },
}
</script>