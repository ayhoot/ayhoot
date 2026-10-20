<template>
    <PageWrapper>
        <CategoriesBar />

        <!-- Page Title -->
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Discover Creators</h3>

                <!-- Page Title -->
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Discover Creators</h3>

                <!-- Search and Filter Controls -->
                <div class="bg-white p-4 rounded-lg shadow-sm mb-6 sticky top-16 z-40">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                        <!-- Search Input -->
                        <div class="md:col-span-1">
                            <label for="search" class="block text-sm font-semibold text-gray-700 mb-1">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
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
                                    class="block w-full font-semibold pl-10 pr-3 py-2 border-2 border-gray-300 rounded-sm leading-5 bg-white placeholder-gray-500 focus:outline-none sm:text-sm"
                                    placeholder="Search by name..."
                                />
                            </div>
                        </div>

                        <!-- Sort By -->
                        <div class="md:col-span-1">
                            <label for="sort-by" class="block text-sm font-semibold text-gray-700 mb-1">Sort By</label>
                            <select
                                v-model="selectedSort"
                                id="sort-by"
                                class="block w-full pl-3 pr-10 py-2 font-semibold border-2 border-gray-300 focus:outline-none sm:text-sm rounded-sm"
                            >
                                <option value="latest">Latest (Newest)</option>
                                <option value="oldest">Oldest</option>
                                <option value="most-content">Most Content</option>
                                <option value="most-tutees">Most Tutees</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- === SKELETON LOADING STATE === -->
                <div v-if="isLoading" class="pt-8">
                    <div class="grid grid-cols-2 md:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        <div
                            v-for="n in 10"
                            :key="`skeleton-${n}`"
                            class="group bg-white rounded-md shadow-sm w-full animate-pulse"
                        >
                            <div
                                class="rounded-t-md bg-gray-200 p-5 flex flex-col space-y-2.5 justify-center items-center"
                            >
                                <div class="w-16 h-16 bg-gray-300 rounded-full"></div>
                                <div class="h-4 bg-gray-300 rounded w-3/4"></div>
                                <div class="h-3 bg-gray-300 rounded w-1/2"></div>
                            </div>
                            <div class="p-3 w-full flex items-center justify-between">
                                <div class="flex flex-col items-center w-1/3 space-y-1">
                                    <div class="h-4 bg-gray-200 rounded w-4"></div>
                                    <div class="h-3 bg-gray-200 rounded w-10"></div>
                                </div>
                                <div class="flex flex-col items-center w-1/3 space-y-1">
                                    <div class="h-4 bg-gray-200 rounded w-4"></div>
                                    <div class="h-3 bg-gray-200 rounded w-10"></div>
                                </div>
                                <div class="flex flex-col items-center w-1/3 space-y-1">
                                    <div class="h-4 bg-gray-200 rounded w-4"></div>
                                    <div class="h-3 bg-gray-200 rounded w-10"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Creators Grid -->
                <div v-else-if="sortedAndFilteredCreators.length > 0" class="pt-8">
                    <div class="grid grid-cols-2 md:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        <RouterLink
                            v-for="creator in sortedAndFilteredCreators"
                            :key="creator.id"
                            :to="`/${$role}/profile/${creator.id}`"
                            class="group bg-white rounded-md shadow-sm w-full transition-transform transform hover:scale-101 hover:shadow-md duration-100"
                        >
                            <div
                                class="rounded-t-md bg-gradient-to-br from-[#0449B2] to-[#03738F] text-xs p-5 flex flex-col space-y-2.5 justify-center items-center"
                            >
                                <img
                                    :src="getAvatarUrl(creator.avatar)"
                                    :alt="creator.name"
                                    class="w-16 h-16 object-cover rounded-full border-2 border-white/50"
                                />
                                <div class="flex flex-col space-y-1 items-center justify-center">
                                    <div class="flex items-center justify-center gap-1 text-white">
                                        <h4 class="font-semibold text-white truncate text-center" :title="creator.name">
                                            {{ creator.name }}
                                        </h4>
                                        <DashVerified v-if="creator.verified_at" class="w-4 h-4 flex-shrink-0" />
                                    </div>
                                    <p
                                        class="text-white text-[0.6rem] bg-white/20 border border-white/50 text-center rounded-full w-fit p-1 px-2"
                                    >
                                        {{ creator.plan?.title || 'Tutor' }}
                                    </p>
                                </div>
                            </div>
                            <div class="p-3 w-full flex items-center justify-between">
                                <div class="text-xs text-center">
                                    <div class="font-bold">{{ creator.exams_count || 0 }}</div>
                                    <div class="font-semibold text-gray-500">Ayhoots</div>
                                </div>
                                <div class="text-xs text-center">
                                    <div class="font-bold">{{ creator.courses_count || 0 }}</div>
                                    <div class="font-semibold text-gray-500">Courses</div>
                                </div>
                                <div class="text-xs text-center">
                                    <div class="font-bold">{{ creator.students_count || 0 }}</div>
                                    <div class="font-semibold text-gray-500">Tutees</div>
                                </div>
                            </div>
                        </RouterLink>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center text-gray-500 py-16">
                    <p class="font-semibold text-lg">No Creators Found</p>
                    <p class="mt-1">Try adjusting your search or filters.</p>
        </div>
    </PageWrapper>
</template>

<script>
import { apiCall } from "@/services/apiService"
import PageWrapper from '@/components/shared/PageWrapper.vue'
import CategoriesBar from './CategoriesBar.vue'
import { RouterLink } from 'vue-router'
import DashVerified from '@/components/atoms/icons/DashVerified.vue'

export default {
    name: 'InstitutesIndex',
    components: {
        PageWrapper,
        RouterLink,
        DashVerified,
        CategoriesBar,
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
                    case 'latest':
                        return new Date(b.created_at) - new Date(a.created_at)
                    case 'oldest':
                        return new Date(a.created_at) - new Date(b.created_at)
                    case 'most-content':
                        const contentA = (a.exams_count || 0) + (a.courses_count || 0)
                        const contentB = (b.exams_count || 0) + (b.courses_count || 0)
                        return contentB - contentA
                    case 'most-tutees':
                        return (b.students_count || 0) - (a.students_count || 0)
                    default:
                        return 0
                }
            })
        },
    },
    methods: {
        async fetchDiscover() {
            this.isLoading = true
            try {
                const response = await apiCall('GET', `/api/${localStorage.getItem('role') || 'teacher'}/discover`)
                // Find the label with the title "Verified" and get its teachers
                const verifiedLabel = response.labels.find((label) => label.title === 'Verified')
                this.creators = verifiedLabel ? verifiedLabel.institutes || [] : []
            } catch (error) {
                console.error('Failed to fetch creators:', error)
                this.creators = []
            } finally {
                this.isLoading = false
            }
        },
        getAvatarUrl(avatarPath) {
            if (!avatarPath) {
                return '/imgHolders/profile.svg' // Default profile fallback
            }
            if (avatarPath.startsWith('http')) {
                return avatarPath
            }
            const cleanedPath = avatarPath.replace(/\\/g, '')
            return `/uploads/${cleanedPath}`
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
