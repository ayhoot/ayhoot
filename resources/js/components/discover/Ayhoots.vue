<template>
    <PageWrapper>
        <CategoriesBar />

        <h3 class="text-2xl font-bold text-gray-800 mb-4" :class="isRTL ? 'text-right' : 'text-left'">
            {{ t('discover.allAyhoots.pageTitle') }}
        </h3>

        <div
            class="bg-white p-4 rounded-lg shadow-sm mb-6 sticky top-16 z-40"
            :class="isRTL ? 'text-right' : 'text-left'"
        >
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <!-- Search Input -->
                <div>
                    <label for="search" class="block text-sm font-semibold text-gray-700 mb-1">{{
                        t('discover.allAyhoots.filters.searchLabel')
                    }}</label>
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 flex items-center pointer-events-none"
                            :class="isRTL ? 'right-0 pr-3' : 'left-0 pl-3'"
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
                            :placeholder="t('discover.allAyhoots.filters.searchPlaceholder')"
                            class="block w-full font-semibold py-2 border-2 border-gray-300 rounded-sm leading-5 bg-white placeholder-gray-500 focus:outline-none sm:text-sm"
                            :class="isRTL ? 'pl-3 pr-10 text-right' : 'pl-10 pr-3 text-left'"
                        />
                    </div>
                </div>

                <!-- Filter by Price -->
                <div>
                    <label for="price-filter" class="block text-sm font-semibold text-gray-700 mb-1">{{
                        t('discover.allAyhoots.filters.priceLabel')
                    }}</label>
                    <select
                        v-model="selectedFilter"
                        id="price-filter"
                        class="block w-full pl-3 pr-10 py-2 font-semibold border-2 border-gray-300 focus:outline-none sm:text-sm rounded-sm"
                        :class="isRTL ? 'text-right' : 'text-left'"
                    >
                        <option value="all">{{ t('discover.allAyhoots.filters.priceAll') }}</option>
                        <option value="paid">{{ t('discover.allAyhoots.filters.pricePaid') }}</option>
                        <option value="free">{{ t('discover.allAyhoots.filters.priceFree') }}</option>
                    </select>
                </div>

                <!-- Sort By -->
                <div>
                    <label for="sort-by" class="block text-sm font-semibold text-gray-700 mb-1">{{
                        t('discover.allAyhoots.filters.sortByLabel')
                    }}</label>
                    <select
                        v-model="selectedSort"
                        id="sort-by"
                        class="block w-full pl-3 pr-10 py-2 font-semibold border-2 border-gray-300 focus:outline-none sm:text-sm rounded-sm"
                        :class="isRTL ? 'text-right' : 'text-left'"
                    >
                        <option value="latest">{{ t('discover.allAyhoots.filters.sortLatest') }}</option>
                        <option value="oldest">{{ t('discover.allAyhoots.filters.sortOldest') }}</option>
                        <option value="most-played">
                            {{ t('discover.allAyhoots.filters.sortMostPlayed') }}
                        </option>
                        <option value="least-played">
                            {{ t('discover.allAyhoots.filters.sortLeastPlayed') }}
                        </option>
                    </select>
                </div>
            </div>
        </div>


                <!-- Skeleton loading state -->
                <div v-if="isInitialLoading" class="pt-8">
                    <div class="grid grid-cols-2 md:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
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

                <!-- Ayhoots grid -->
                <div v-else-if="sortedAndFilteredAyhoots.length > 0">
                    <div class="pt-8 grid grid-cols-2 md:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        <RouterLink
                            v-for="ayhoot in sortedAndFilteredAyhoots"
                            :key="ayhoot.id"
                            :to="`/${$role}/ayhoot/${ayhoot.id}`"
                            class="group bg-gradient-to-b from-[#5A89DA] to-[#4C1D92] rounded-md shadow-sm w-full transition-transform transform hover:scale-101 hover:shadow-md duration-100"
                        >
                            <div class="rounded-t-md text-xs py-1 px-2 flex justify-between items-center">
                                <div
                                    class="flex gap-1 items-center text-white"
                                    :class="isRTL ? 'flex-row-reverse' : ''"
                                >
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
                                    <span class="text-xs font-medium text-white">{{
                                        t('discover.plays', { count: ayhoot.plays_count || 0 })
                                    }}</span>
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
                                    class="absolute opacity-0 group-hover:opacity-100 transition-opacity duration-300 bottom-2 text-xs text-white bg-black/40 px-2 py-1 rounded-md"
                                    :class="isRTL ? 'right-2' : 'left-2'"
                                    >{{ t('discover.questions', { count: ayhoot.questions_count }) }}</span
                                >
                            </div>
                            <div class="p-3 w-full flex flex-col justify-between">
                                <h4 class="font-semibold text-white truncate" :title="ayhoot.title">
                                    {{ ayhoot.title }}
                                </h4>
                                <p class="text-sm text-gray-100 truncate" :title="ayhoot.description">
                                    {{ ayhoot.description }}
                                </p>
                                
                                <!-- Host Live Button -->
                                <button
                                    v-if="canHostLive(ayhoot)"
                                    @click.stop="openHostLiveModal(ayhoot)"
                                    class="mt-2 w-full bg-[#42C287] hover:bg-[#156B43] text-white text-xs font-bold py-2 px-3 rounded-md transition-colors flex items-center justify-center gap-1 shadow-[0_2px_0_rgba(21,107,67,1)] hover:shadow-none hover:translate-y-[2px]"
                                >
                                    🎮 {{ t('discover.hostLive') }}
                                </button>
                                
                                <div
                                    class="mt-2 flex items-center justify-start gap-2"
                                    :class="isRTL ? 'flex-row-reverse' : ''"
                                >
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
                                        {{ t('discover.allAyhoots.byline.by') }} @{{
                                            ayhoot.teacher?.username ||
                                            ayhoot.institute?.username ||
                                            t('discover.byline.anonymous')
                                        }}
                                    </RouterLink>
                                    <div v-else class="text-xs text-white">
                                        {{ t('discover.allAyhoots.byline.anonymous') }}
                                    </div>
                                </div>
                            </div>
                        </RouterLink>
                    </div>

                    <div v-if="currentPage < lastPage" class="text-center mt-4">
                        <button
                            @click="loadMoreAyhoots"
                            :disabled="isLoadingMore"
                            class="text-gray-500 cursor-pointer font-semibold text-sm py-2 px-6 disabled:text-gray-100 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="isLoadingMore">{{ t('discover.loadingMore') }}</span>
                            <span v-else>{{ t('discover.loadMore') }}</span>
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center text-gray-500 py-16">
                    <p class="font-semibold text-lg">{{ t('discover.allAyhoots.empty.title') }}</p>
                    <p class="mt-1">{{ t('discover.allAyhoots.empty.description') }}</p>
                </div>
            </PageWrapper>


        <!-- Host Live Modal -->
        <div v-if="showHostLiveModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-800">🎮 Host Live Session</h3>
                    <button @click="showHostLiveModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Session Title Input -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Session Title</label>
                    <input 
                        v-model="hostLiveTitle" 
                        type="text"
                        :placeholder="`Live: ${selectedAyhoot?.title || 'Session'}`"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <!-- QR Code Display -->
                <div v-if="liveSessionData" class="text-center mb-6">
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
                <div class="flex gap-3">
                    <button 
                        v-if="!liveSessionData"
                        @click="createLiveSession"
                        :disabled="creatingSession"
                        class="flex-1 bg-green-500 hover:bg-green-600 disabled:bg-gray-300 text-white font-bold py-3 px-4 rounded-lg transition-colors"
                    >
                        <span v-if="creatingSession">Creating...</span>
                        <span v-else>Create Session</span>
                    </button>
                    
                    <button 
                        v-if="liveSessionData"
                        @click="goToHostPage"
                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition-colors"
                    >
                        Go to Host Page
                    </button>
                    
                    <button 
                        @click="showHostLiveModal = false"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-4 rounded-lg transition-colors"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

</template>

<script>
// MODIFIED: Import the global apiCall function
import { apiCall } from "@/services/apiService"
import { getRole } from '@/utils/role'
import { ref } from 'vue'
import QRCode from 'qrcode.vue'
import PageWrapper from '@/components/shared/PageWrapper.vue'
import CategoriesBar from './CategoriesBar.vue'
import { RouterLink } from 'vue-router'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useConfirm } from '@/composables/useConfirm'

// MODIFIED: Removed the local apiCall function
// async function apiCall(...) { ... }

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'AllAyhootsIndex',
    components: { PageWrapper, RouterLink, QRCode, CategoriesBar },
    data() {
        return {
            isSidebarOpen: false,
            allAyhoots: [],
            isInitialLoading: true,
            isLoadingMore: false,
            searchQuery: '',
            selectedFilter: 'all',
            selectedSort: 'latest',
            currentPage: 1,
            lastPage: 1,
            showHostLiveModal: false,
            selectedAyhoot: null,
            hostLiveTitle: '',
            liveSessionData: null,
            creatingSession: false,
        }
    },
    setup() {
        const { locale, t } = useI18n()
        const isRTL = computed(() => locale.value === 'fa')
        return { isRTL, t }
    },
    computed: {
        sortedAndFilteredAyhoots() {
            let filtered = [...this.allAyhoots]
            if (this.selectedFilter !== 'all') {
                filtered = filtered.filter((ayhoot) => ayhoot.monetize === this.selectedFilter)
            }
            if (this.searchQuery) {
                const lowerCaseQuery = this.searchQuery.toLowerCase()
                filtered = filtered.filter(
                    (ayhoot) =>
                        (ayhoot.title && ayhoot.title.toLowerCase().includes(lowerCaseQuery)) ||
                        (ayhoot.description && ayhoot.description.toLowerCase().includes(lowerCaseQuery)) ||
                        (ayhoot.teacher?.username && ayhoot.teacher.username.toLowerCase().includes(lowerCaseQuery)) ||
                        (ayhoot.institute?.username && ayhoot.institute.username.toLowerCase().includes(lowerCaseQuery))
                )
            }
            return filtered.sort((a, b) => {
                switch (this.selectedSort) {
                    case 'latest':
                        return new Date(b.created_at) - new Date(a.created_at)
                    case 'oldest':
                        return new Date(a.created_at) - new Date(b.created_at)
                    case 'most-played':
                        return (b.plays_count || 0) - (a.plays_count || 0)
                    case 'least-played':
                        return (a.plays_count || 0) - (b.plays_count || 0)
                    default:
                        return 0
                }
            })
        },
    },
    methods: {
        async fetchDiscover(isLoadMore = false) {
            if (!isLoadMore) {
                this.isInitialLoading = true
            } else {
                this.isLoadingMore = true
            }

            try {
                const response = await apiCall('GET', `/api/${getRole()}/get-all-exams?page=${this.currentPage}`)
                const paginatedData = response.exams
                const newAyhoots = paginatedData?.data || []

                if (isLoadMore) {
                    this.allAyhoots.push(...newAyhoots)
                } else {
                    this.allAyhoots = newAyhoots
                }

                this.lastPage = paginatedData?.last_page || 1
            } catch (error) {
                console.error('Failed to fetch Ayhoots:', error)
                if (!isLoadMore) {
                    this.allAyhoots = []
                }
            } finally {
                if (!isLoadMore) {
                    this.isInitialLoading = false
                } else {
                    this.isLoadingMore = false
                }
            }
        },
        loadMoreAyhoots() {
            if (this.currentPage < this.lastPage && !this.isLoadingMore) {
                this.currentPage++
                this.fetchDiscover(true)
            }
        },
        // MODIFIED: Updated to use the environment variable
        getCoverUrl(coverPath) {
            const API_BASE_URL = import.meta.env.VITE_BASE_URL
            const fallbackUrl = '/imgHolders/CardHolderImage.jpg'

            if (!coverPath) {
                return fallbackUrl
            }

            if (coverPath.startsWith('http') || coverPath.startsWith('data:image')) {
                return coverPath
            }

            const cleanedPath = coverPath.replace(/\\/g, '/').replace(/^\//, '')
            return `${API_BASE_URL}/uploads/${cleanedPath}`
        },
        formatPrice(ayhoot) {
            if (ayhoot.monetize === 'free') return this.t('discover.price.free')
            if (ayhoot.monetize === 'paid' && ayhoot.price) {
                return this.t('discover.price.paidAmount', { price: Number(ayhoot.price).toLocaleString() })
            }
            return this.t('discover.price.paid')
        },
        canHostLive(ayhoot) {
            // Check if user has access to host this ayhoot
            // For now, check if it's owned or has access (you may need to adjust this logic)
            const user = JSON.parse(localStorage.getItem('user') || '{}')
            return ayhoot.teacher_id === user.id || ayhoot.institute_id === user.id || ayhoot.has_access
        },
        openHostLiveModal(ayhoot) {
            this.selectedAyhoot = ayhoot
            this.hostLiveTitle = `Live: ${ayhoot.title}`
            this.liveSessionData = null
            this.showHostLiveModal = true
        },
        async createLiveSession() {
            this.creatingSession = true
            try {
                const response = await apiCall('POST', '/api/live-session/create', {
                    exam_id: this.selectedAyhoot.id,
                    title: this.hostLiveTitle,
                    description: `Live session for exam: ${this.selectedAyhoot.title}`
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
