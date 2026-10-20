<template>
    <PublicLayout padded>
        <!-- Page Title -->
        <h3 class="text-2xl font-bold text-gray-800 mb-4" :class="$i18n.locale === 'fa' ? 'text-right' : 'text-left'">
            {{ t('discover.creators.pageTitle') }}
        </h3>

        <!-- Search and Filter Controls (No changes here) -->
        <div
            class="bg-white p-4 rounded-lg shadow-sm mb-6 sticky top-16 z-40"
            :class="$i18n.locale === 'fa' ? 'text-right' : 'text-left'"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                <div class="md:col-span-1">
                    <label for="search" class="block text-sm font-semibold text-gray-700 mb-1">{{
                        t('discover.creators.filters.searchLabel')
                    }}</label>
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 flex items-center pointer-events-none"
                            :class="$i18n.locale === 'fa' ? 'right-0 pr-3' : 'left-0 pl-3'"
                        >
                            <svg
                                class="h-5 w-5 text-gray-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            id="search"
                            class="block w-full font-semibold py-2 border-2 border-gray-300 rounded-sm leading-5 bg-white placeholder-gray-500 focus:outline-none sm:text-sm"
                            :placeholder="t('discover.creators.filters.searchPlaceholder')"
                            :class="$i18n.locale === 'fa' ? 'pl-3 pr-10' : 'pl-10 pr-3'"
                        />
                    </div>
                </div>
                <div class="md:col-span-1">
                    <label for="sort-by" class="block text-sm font-semibold text-gray-700 mb-1">{{
                        t('discover.creators.filters.sortByLabel')
                    }}</label>
                    <select
                        v-model="selectedSort"
                        id="sort-by"
                        class="block w-full pl-3 pr-10 py-2 font-semibold border-2 border-gray-300 focus:outline-none sm:text-sm rounded-sm"
                        :class="$i18n.locale === 'fa' ? 'text-right' : 'text-left'"
                    >
                        <option value="latest">{{ t('discover.creators.filters.sortLatest') }}</option>
                        <option value="oldest">{{ t('discover.creators.filters.sortOldest') }}</option>
                        <option value="most-content">
                            {{ t('discover.creators.filters.sortMostContent') }}
                        </option>
                        <option value="most-tutees">{{ t('discover.creators.filters.sortMostTutees') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- SKELETON LOADING STATE (No changes here) -->
        <div v-if="isLoading" class="pt-8">
            <!-- ... -->
        </div>

        <!-- Creators Grid -->
        <div v-else-if="sortedAndFilteredCreators.length > 0" class="pt-8">
            <div class="grid grid-cols-2 md:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <RouterLink
                    v-for="creator in sortedAndFilteredCreators"
                    :key="`${creator.type}-${creator.id}`"
                    :to="getCreatorProfileLink(creator)"
                    class="group bg-white rounded-md shadow-sm w-full transition-transform transform hover:scale-101 hover:shadow-md duration-100"
                >
                    <div
                        class="rounded-t-md bg-gradient-to-br from-[#0449B2] to-[#03738F] text-xs p-5 flex flex-col space-y-2.5 justify-center items-center"
                    >
                        <!-- CHANGE: Use `creator.image` instead of `creator.avatar` -->
                        <img
                            :src="getAvatarUrl(creator.image)"
                            :alt="creator.name"
                            class="w-16 h-16 object-cover rounded-full border-2 border-white/50"
                        />
                        <div class="flex flex-col space-y-1 items-center justify-center">
                            <div
                                class="flex items-center justify-center gap-1 text-white"
                                :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }"
                            >
                                <h4 class="font-semibold text-white truncate text-center" :title="creator.name">
                                    {{ creator.name }}
                                </h4>
                                <!-- Note: `verified_at` is not in the API, so this icon will not show -->
                                <DashVerified v-if="creator.verified_at" class="w-4 h-4 flex-shrink-0" />
                            </div>
                            <!-- CHANGE: Use `creator.type` for the label -->
                            <p
                                class="text-white text-[0.6rem] bg-white/20 border border-white/50 text-center rounded-full w-fit p-1 px-2"
                            >
                                {{
                                    creator.type === 'institute'
                                        ? t('discover.byline.institute')
                                        : t('discover.byline.tutor')
                                }}
                            </p>
                        </div>
                    </div>
                    <div class="p-3 w-full flex items-center justify-between">
                        <div class="text-xs text-center">
                            <!-- CHANGE: Use `creator.ayhoots` -->
                            <div class="font-bold">{{ creator.ayhoots || 0 }}</div>
                            <div class="font-semibold text-gray-500">
                                {{ t('discover.creators.stats.ayhoots') }}
                            </div>
                        </div>
                        <div class="text-xs text-center">
                            <!-- CHANGE: Use `creator.courses` -->
                            <div class="font-bold">{{ creator.courses || 0 }}</div>
                            <div class="font-semibold text-gray-500">
                                {{ t('discover.creators.stats.courses') }}
                            </div>
                        </div>
                        <div class="text-xs text-center">
                            <!-- CHANGE: Use `creator.tutees` -->
                            <div class="font-bold">{{ creator.tutees || 0 }}</div>
                            <div class="font-semibold text-gray-500">
                                {{ t('discover.creators.stats.tutees') }}
                            </div>
                        </div>
                    </div>
                </RouterLink>
            </div>
        </div>

        <!-- Empty State (No changes here) -->
        <div v-else class="text-center text-gray-500 py-16">
            <p class="font-semibold text-lg">{{ t('discover.creators.empty.title') }}</p>
            <p class="mt-1">{{ t('discover.creators.empty.description') }}</p>
        </div>
    </PublicLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { RouterLink } from 'vue-router'
import DashVerified from '@/components/atoms/icons/DashVerified.vue'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import PublicLayout from '@/components/layout/PublicLayout.vue'

export default {
    name: 'CreatorsIndex',
    components: {
        PublicLayout,
        RouterLink,
        DashVerified,
    },
    setup() {
        const { locale, t } = useI18n()
        const isRTL = computed(() => locale.value === 'fa')
        return { isRTL, t }
    },
    data() {
        return {
            isSidebarOpen: false,
            creators: [],
            isLoading: true,
            searchQuery: '',
            selectedSort: 'latest',
        }
    },
    computed: {
        teacherName() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user.name || this.t('discover.defaultTeacher')
        },
        teacherUsername() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user.username || this.t('discover.defaultTeacher')
        },
        sortedAndFilteredCreators() {
            let filtered = [...this.creators]

            if (this.searchQuery) {
                const lowerCaseQuery = this.searchQuery.toLowerCase()
                filtered = filtered.filter(
                    (creator) =>
                        (creator.name && creator.name.toLowerCase().includes(lowerCaseQuery)) ||
                        (creator.username && creator.username.toLowerCase().includes(lowerCaseQuery))
                )
            }

            return filtered.sort((a, b) => {
                switch (this.selectedSort) {
                    // Note: `created_at` is not in the API, so latest/oldest may not work as expected.
                    // Sorting will be based on the order from the API.
                    case 'latest':
                        return new Date(b.created_at) - new Date(a.created_at)
                    case 'oldest':
                        return new Date(a.created_at) - new Date(b.created_at)

                    // CHANGE: Updated sorting logic with correct property names
                    case 'most-content':
                        const contentA = (a.ayhoots || 0) + (a.courses || 0)
                        const contentB = (b.ayhoots || 0) + (b.courses || 0)
                        return contentB - contentA
                    case 'most-tutees':
                        return (b.tutees || 0) - (a.tutees || 0)
                    default:
                        return 0
                }
            })
        },
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        async fetchDiscover() {
            this.isLoading = true
            try {
                const response = await apiCall('GET', '/api/home/creators')
                this.creators = response.creators || []
            } catch (error) {
                console.error('Failed to fetch creators:', error)
                this.creators = []
            } finally {
                this.isLoading = false
            }
        },
        getAvatarUrl(imagePath) {
            if (!imagePath) {
                return '/imgHolders/profile.svg'
            }
            if (imagePath.startsWith('http')) {
                return imagePath
            }
            const cleanedPath = imagePath.replace(/\\/g, '/')
            return `/uploads/${cleanedPath.replace(/^\//, '')}`
        },
        // CHANGE: Simplified this method to use the `type` property from the API.
        getCreatorProfileLink(creator) {
            if (creator.type === 'institute') {
                return `/institute/profile/${creator.id}`
            } else {
                // Assumes any other type is a teacher
                return `/teacher/profile/${creator.id}`
            }
        },
    },
    mounted() {
        this.fetchDiscover()
    },
}
</script>

<style scoped>
.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sticky {
    position: -webkit-sticky;
    position: sticky;
}
@keyframes pulse {
    50% {
        opacity: 0.6;
    }
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
