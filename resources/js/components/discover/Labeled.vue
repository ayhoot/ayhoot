<template>
    <div class="bg-gray-50 min-h-screen">
        <!-- Header -->
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" :teacherName="teacherName" :teacherUsername="teacherUsername" />
        </div>

        <div class="flex">
            <!-- Sidebar -->
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />

            <!-- Main Content -->
            <main class="flex-1 bg-gray-50 xl:ms-64 p-4 sm:p-6 overflow-y-auto mt-16 min-h-[calc(100vh-4rem)]">
                <CategoriesBar />

                <!-- Page Title -->
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Discover {label.title} Ayhoots</h3>

                <!-- Search and Filter Controls -->
                <div class="bg-white p-4 rounded-lg shadow-sm mb-6 sticky top-16 z-40">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
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
                                    placeholder="Search by title, description..."
                                />
                            </div>
                        </div>
                        <!-- Filter by Price -->
                        <div class="md:col-span-1">
                            <label for="price-filter" class="block text-sm font-semibold text-gray-700 mb-1"
                                >Price</label
                            >
                            <select
                                v-model="selectedFilter"
                                id="price-filter"
                                class="block w-full pl-3 pr-10 py-2 font-semibold border-2 border-gray-300 focus:outline-none sm:text-sm rounded-sm"
                            >
                                <option value="all">All</option>
                                <option value="paid">Paid</option>
                                <option value="free">Free</option>
                            </select>
                        </div>
                        <!-- Sort By -->
                        <div class="md:col-span-1">
                            <label for="sort-by" class="block text-sm font-semibold text-gray-700 mb-1">Sort By</label>
                            <select
                                v-model="selectedSort"
                                id="sort-by"
                                class="block w-full pl-3 pr-10 py-2 font-semibold border-2 border-gray-300 focus:outline-none sm:text-sm rounded-sm"
                            >
                                <option value="latest">Latest</option>
                                <option value="oldest">Oldest</option>
                                <option value="most-played">Most Played</option>
                                <option value="least-played">Least Played</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- === SKELETON LOADING STATE === -->
                <div v-if="isLoading" class="pt-8">
                    <div class="grid grid-cols-2 md:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        <!-- Render 10 skeleton cards -->
                        <div
                            v-for="n in 10"
                            :key="`skeleton-${n}`"
                            class="group bg-gradient-to-b from-[#a7bde2] to-[#9c82c3] rounded-md shadow-sm w-full animate-pulse"
                        >
                            <div class="rounded-t-md text-xs py-1 px-2 flex justify-between items-center h-7">
                                <div class="h-4 bg-gray-400/50 rounded w-16"></div>
                                <div class="h-4 bg-gray-400/50 rounded w-10"></div>
                            </div>
                            <div class="w-full h-32 bg-gray-400/50"></div>
                            <div class="p-3 space-y-3">
                                <div class="h-4 bg-gray-300/50 rounded w-3/4"></div>
                                <div class="h-3 bg-gray-300/50 rounded w-full"></div>
                                <div class="h-3 bg-gray-300/50 rounded w-1/2 mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ayhoots Grid -->
                <div v-else-if="sortedAndFilteredAyhoots.length > 0" class="pt-8">
                    <div class="grid grid-cols-2 md:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        <RouterLink
                            v-for="ayhoot in sortedAndFilteredAyhoots"
                            :key="ayhoot.id"
                            :to="`/${$role}/ayhoot/${ayhoot.id}`"
                            class="group bg-gradient-to-b from-[#5A89DA] to-[#4C1D92] rounded-md shadow-sm w-full transition-transform transform hover:scale-101 hover:shadow-md duration-100"
                        >
                            <div class="rounded-t-md text-xs py-1 px-2 flex justify-between items-center">
                                <div class="flex gap-1 items-center text-white">
                                    <svg
                                        width="14"
                                        height="14"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M15.5993 12.8093L15.0538 11.9713L15.0398 11.9803L15.0262 11.9899L15.5993 12.8093ZM15.5993 11.4332L15.0064 12.2384L15.051 12.2712L15.099 12.299L15.5993 11.4332ZM10.0839 7.37187L10.6769 6.56662L10.6487 6.54585L10.6191 6.52709L10.0839 7.37187ZM8.92766 7.97847L9.92765 7.97557L9.92763 7.96682L9.92745 7.95807L8.92766 7.97847ZM8.95078 15.9562L7.95079 15.9591L7.95087 15.9874L7.95255 16.0156L8.95078 15.9562ZM10.1494 16.621L10.6337 17.4959L10.6796 17.4705L10.7226 17.4405L10.1494 16.621ZM5.9999 3.3999H17.9999V1.3999H5.9999V3.3999ZM20.5999 5.9999V17.9999H22.5999V5.9999H20.5999ZM17.9999 20.5999H5.9999V22.5999H17.9999V20.5999ZM3.3999 17.9999V5.9999H1.3999V17.9999H3.3999ZM5.9999 20.5999C4.56396 20.5999 3.3999 19.4358 3.3999 17.9999H1.3999C1.3999 20.5404 3.45939 22.5999 5.9999 22.5999V20.5999ZM20.5999 17.9999C20.5999 19.4358 19.4358 20.5999 17.9999 20.5999V22.5999C20.5404 22.5999 22.5999 20.5404 22.5999 17.9999H20.5999ZM17.9999 3.3999C19.4358 3.3999 20.5999 4.56396 20.5999 5.9999H22.5999C22.5999 3.45939 20.5404 1.3999 17.9999 1.3999V3.3999ZM5.9999 1.3999C3.45939 1.3999 1.3999 3.45939 1.3999 5.9999H3.3999C3.3999 4.56396 4.56396 3.3999 5.9999 3.3999V1.3999ZM16.1449 13.6474C16.5793 13.3646 17.1284 12.8602 17.1274 12.1C17.1263 11.3173 16.5491 10.827 16.0997 10.5673L15.099 12.299C15.2036 12.3595 15.2229 12.3909 15.2083 12.3723C15.1867 12.3448 15.1276 12.2501 15.1274 12.1027C15.1272 11.9591 15.1833 11.8745 15.192 11.8627C15.1963 11.8569 15.1655 11.8985 15.0538 11.9713L16.1449 13.6474ZM16.1923 10.6279L10.6769 6.56662L9.49098 8.17711L15.0064 12.2384L16.1923 10.6279ZM10.6191 6.52709C10.1484 6.22898 9.48756 6.05818 8.86255 6.35001C8.1823 6.66763 7.91466 7.35185 7.92786 7.99888L9.92745 7.95807C9.92625 7.89951 9.93844 7.91844 9.90768 7.97728C9.87303 8.04359 9.80534 8.11708 9.70869 8.1622C9.52273 8.24903 9.4477 8.1526 9.5488 8.21664L10.6191 6.52709ZM7.92766 7.98137L7.95079 15.9591L9.95078 15.9533L9.92765 7.97557L7.92766 7.98137ZM7.95255 16.0156C7.98436 16.5499 8.17011 17.2622 8.84054 17.6203C9.50063 17.9728 10.1879 17.7426 10.6337 17.4959L9.66523 15.746C9.56611 15.8009 9.52715 15.804 9.5428 15.8018C9.56646 15.7985 9.66412 15.7928 9.78273 15.8561C9.90301 15.9204 9.95507 16.0077 9.96769 16.0346C9.976 16.0523 9.95623 16.018 9.94901 15.8968L7.95255 16.0156ZM10.7226 17.4405L16.1725 13.6288L15.0262 11.9899L9.57631 15.8015L10.7226 17.4405Z"
                                            fill="white"
                                        />
                                    </svg>
                                    <span class="text-xs font-medium text-white">{{ ayhoot.plays_count }} plays</span>
                                </div>
                                <div class="text-[0.6rem] text-white bg-white/20 rounded-md px-2 py-0.5">
                                    {{ formatPrice(ayhoot) }}
                                </div>
                            </div>
                            <div class="relative">
                                <img
                                    :src="getCoverUrl(ayhoot.cover)"
                                    :alt="ayhoot.title"
                                    class="w-full h-32 object-cover"
                                />
                                <span
                                    v-if="ayhoot.questions_count"
                                    class="absolute opacity-0 group-hover:opacity-100 transition-opacity duration-300 bottom-2 left-2 text-xs text-white bg-black/40 px-2 py-1 rounded-md"
                                    >{{ ayhoot.questions_count }} questions</span
                                >
                            </div>
                            <div class="p-3 w-full flex flex-col justify-between">
                                <h4 class="font-semibold text-white truncate" :title="ayhoot.title">
                                    {{ ayhoot.title }}
                                </h4>
                                <p class="text-sm text-gray-100 truncate" :title="ayhoot.description">
                                    {{ ayhoot.description }}
                                </p>
                                <!-- UPDATED BLOCK -->
                                <div class="mt-2 flex items-center justify-start gap-2">
                                    <RouterLink
                                        v-if="ayhoot.teacher_id || ayhoot.institute_id"
                                        :to="
                                            ayhoot.teacher_id
                                                ? `/${$role}/profile/${ayhoot.teacher_id}`
                                                : `/${$role}/profile/${ayhoot.institute_id}`
                                        "
                                        @click.stop
                                        class="text-xs text-white hover:underline z-10 relative"
                                    >
                                        by @{{ ayhoot.teacher?.username || 'Institute' }}
                                    </RouterLink>
                                    <div v-else class="text-xs text-white">by Anonymous</div>
                                </div>
                                <!-- END UPDATED BLOCK -->
                            </div>
                        </RouterLink>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center text-gray-500 py-16">
                    <p class="font-semibold text-lg">No Ayhoots Found</p>
                    <p class="mt-1">Try adjusting your search or filters.</p>
                </div>
            </main>
        </div>
    </div>
</template>

<script>
import { apiCall } from "@/services/apiService"
import Sidebar from '@/components/layout/RoleSidebar.vue'
import Header from '@/components/layout/RoleHeader.vue'
import CategoriesBar from './CategoriesBar.vue'
import { RouterLink } from 'vue-router'

export default {
    name: 'LabeledIndex',
    components: {
        Sidebar,
        Header,
        RouterLink,
        CategoriesBar,
    },
    data() {
        return {
            isSidebarOpen: false,
            ayhoots: [],
            isLoading: true,
            searchQuery: '',
            selectedFilter: 'all',
            selectedSort: 'latest',
        }
    },
    computed: {
        teacherName() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user.name || 'Teacher'
        },
        teacherUsername() {
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return user.username || 'Teacher'
        },
        sortedAndFilteredAyhoots() {
            let filtered = [...this.ayhoots]

            if (this.selectedFilter !== 'all') {
                filtered = filtered.filter((ayhoot) => ayhoot.monetize === this.selectedFilter)
            }

            if (this.searchQuery) {
                const lowerCaseQuery = this.searchQuery.toLowerCase()
                filtered = filtered.filter(
                    (ayhoot) =>
                        (ayhoot.title && ayhoot.title.toLowerCase().includes(lowerCaseQuery)) ||
                        (ayhoot.description && ayhoot.description.toLowerCase().includes(lowerCaseQuery)) ||
                        (ayhoot.teacher?.username && ayhoot.teacher.username.toLowerCase().includes(lowerCaseQuery))
                )
            }

            return filtered.sort((a, b) => {
                switch (this.selectedSort) {
                    case 'latest':
                        return new Date(b.created_at) - new Date(a.created_at)
                    case 'oldest':
                        return new Date(a.created_at) - new Date(b.created_at)
                    case 'most-played':
                        return b.plays_count - a.plays_count
                    case 'least-played':
                        return a.plays_count - b.plays_count
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
                const response = await apiCall('GET', `/api/${localStorage.getItem('role') || 'teacher'}/discover`)
                this.ayhoots = response.recommended_by_ayhoot || []
            } catch (error) {
                console.error('Failed to fetch Ayhoots:', error)
                this.ayhoots = []
            } finally {
                this.isLoading = false
            }
        },
        getCoverUrl(coverPath) {
            if (!coverPath) {
                return '/imgHolders/CardHolderImage.jpg'
            }
            const cleanedPath = coverPath.replace(/\\\//g, '/')
            return `/uploads/${cleanedPath}`
        },
        formatPrice(ayhoot) {
            if (ayhoot.monetize === 'free') {
                return 'Free'
            }
            if (ayhoot.monetize === 'paid' && ayhoot.price) {
                return `${Number(ayhoot.price).toLocaleString()} R`
            }
            return 'Paid'
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
