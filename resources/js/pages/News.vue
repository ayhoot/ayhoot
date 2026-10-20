<template>
    <PublicLayout footer>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Page Header -->
            <div class="text-start py-10">
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight sm:text-5xl">
                    {{ t('newsIndex.title') }}
                </h1>
                <p class="mt-3 max-w-2xl text-gray-500">{{ t('newsIndex.description') }}</p>
            </div>

            <!-- Loading Skeleton -->
            <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="n in 6" :key="n" class="bg-white rounded-lg shadow-md overflow-hidden animate-pulse">
                    <div class="h-48 bg-gray-200"></div>
                    <div class="p-6">
                        <div class="h-6 bg-gray-200 rounded w-3/4 mb-4"></div>
                        <div class="h-4 bg-gray-200 rounded w-full mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!newsList.length" class="text-center py-16">
                <h3 class="text-2xl font-semibold text-gray-700">{{ t('newsIndex.empty.title') }}</h3>
                <p class="text-gray-500 mt-2">
                    {{ t('newsIndex.empty.description') }}
                </p>
            </div>

            <!-- News Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <NewsCard v-for="news in newsList" :key="news.id" :news-item="news" />
            </div>

            <!-- Pagination -->
            <div v-if="!isLoading && pagination && pagination.last_page > 1" class="mt-12">
                <Pagination
                    :current-page="pagination.current_page"
                    :last-page="pagination.last_page"
                    :total="pagination.total"
                    @page-change="fetchNews"
                />
            </div>
        </div>
    </PublicLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useI18n } from 'vue-i18n'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import NewsCard from '@/components/molecules/card/NewsCard.vue'
import Pagination from '@/components/molecules/Pagination.vue'

export default {
    name: 'NewsIndex',
    components: {
        PublicLayout,
        NewsCard,
        Pagination,
    },
    setup() {
        const { t } = useI18n()
        return { t }
    },
    data() {
        return {
            newsList: [],
            pagination: null,
            isLoading: true,
        }
    },
    methods: {
        async fetchNews(page = 1) {
            this.isLoading = true
            try {
                const response = await apiCall('GET', `/api/news?page=${page}`)
                this.newsList = response.news.data
                this.pagination = response.news
            } catch (error) {
                console.error('Failed to fetch news:', error)
                // You could use a toast notification here with a translated message
                // For example: this.$toast.error(this.t('newsIndex.notifications.loadFailed'))
            } finally {
                this.isLoading = false
            }
        },
    },
    mounted() {
        this.fetchNews(1)
    },
}
</script>
