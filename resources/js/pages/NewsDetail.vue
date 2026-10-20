<template>
    <PublicLayout footer>
        <div v-if="isLoading" class="flex items-center justify-center min-h-screen">
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce"></div>
                <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                <span class="ml-3 text-gray-600">{{ t('newsDetail.loading') }}</span>
            </div>
        </div>

        <div v-else-if="error" class="flex items-center justify-center min-h-screen">
            <div class="text-center space-y-4">
                <div class="text-red-600 text-5xl">⚠</div>
                <h2 class="text-xl font-semibold text-gray-900">{{ t('newsDetail.error.title') }}</h2>
                <p class="text-gray-600">{{ error }}</p>
                <button
                    @click="$router.go(-1)"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                >
                    ← {{ t('newsDetail.goBack') }}
                </button>
            </div>
        </div>

        <div v-else-if="newsItem" class="max-w-6xl mx-auto">
            <div class="grid grid-cols-12 gap-8 p-6 pt-24">
                <aside class="col-span-12 lg:col-span-3">
                    <div class="sticky top-24 space-y-6">
                        <div class="bg-gray-50 rounded-xl p-6">
                            <h3 class="font-semibold text-gray-900 mb-4">Article Info</h3>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="text-gray-500">Published:</span>
                                    <div class="font-medium">{{ formatDate(newsItem.created_at) }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-500">Category:</span>
                                    <div class="font-medium">News & Updates</div>
                                </div>
                                <div>
                                    <span class="text-gray-500">Reading time:</span>
                                    <div class="font-medium">{{ estimatedReadTime }} min read</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-6">
                            <h3 class="font-semibold text-gray-900 mb-4">Share Article</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    class="flex items-center justify-center p-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"
                                        />
                                    </svg>
                                </button>
                                <button
                                    class="flex items-center justify-center p-3 bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"
                                        />
                                    </svg>
                                </button>
                                <button
                                    class="flex items-center justify-center p-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"
                                        />
                                    </svg>
                                </button>
                                <button
                                    class="flex items-center justify-center p-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"
                                        ></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button
                            @click="$router.go(-1)"
                            class="w-full flex items-center justify-center p-3 border-2 border-gray-200 rounded-xl hover:border-gray-300 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"
                                ></path>
                            </svg>
                            {{ t('newsDetail.goBack') }}
                        </button>
                    </div>
                </aside>

                <article class="col-span-12 lg:col-span-9">
                    <header class="mb-8">
                        <div class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md font-medium">News</span>
                            <span>•</span>
                            <time>{{ formatDate(newsItem.created_at) }}</time>
                        </div>

                        <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 leading-tight mb-6">
                            {{ newsItem.title }}
                        </h1>
                    </header>

                    <figure v-if="newsItem.image" class="mb-12">
                        <img
                            :src="`/uploads/${newsItem.image.replace(/\\/g, '/')}`"
                            :alt="newsItem.title"
                            class="w-full h-auto rounded-2xl shadow-lg"
                        />
                    </figure>

                    <div class="prose prose-lg max-w-none">
                        <div v-for="(block, index) in contentBlocks" :key="`article-${index}`" class="mb-8">
                            <component
                                :is="`h${block.level || 2}`"
                                v-if="block.type === 'heading'"
                                v-html="block.content"
                                :class="[
                                    'font-bold text-gray-900 leading-tight',
                                    {
                                        'text-3xl mb-6': block.level === 1,
                                        'text-2xl mb-5': block.level === 2,
                                        'text-xl mb-4': block.level === 3,
                                        'text-lg mb-3': block.level >= 4,
                                        'text-left': !block.align || block.align === 'left',
                                        'text-center': block.align === 'center',
                                        'text-right': block.align === 'right',
                                    },
                                ]"
                            ></component>

                            <div
                                v-else-if="block.type === 'paragraph'"
                                v-html="block.content"
                                :class="[
                                    'text-gray-700 leading-8 text-lg mb-6',
                                    {
                                        'text-left': !block.align || block.align === 'left',
                                        'text-center': block.align === 'center',
                                        'text-right': block.align === 'right',
                                    },
                                ]"
                            ></div>

                            <figure
                                v-else-if="block.type === 'image' && block.content && block.content.src"
                                class="my-10"
                                :class="{
                                    'text-left': !block.align || block.align === 'left',
                                    'text-center': block.align === 'center',
                                    'text-right': block.align === 'right',
                                }"
                            >
                                <img
                                    :src="block.content.src"
                                    :alt="block.content.alt || t('newsDetail.contentImageAlt')"
                                    class="w-full h-auto rounded-xl shadow-md"
                                />
                                <figcaption
                                    v-if="block.content.caption"
                                    class="text-center text-sm text-gray-500 mt-3 italic"
                                >
                                    {{ block.content.caption }}
                                </figcaption>
                            </figure>
                        </div>
                    </div>

                    <div class="mt-16 pt-8 border-t border-gray-200">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-500">Tags:</span>
                                <div class="flex flex-wrap gap-2">
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">News</span>
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full"
                                        >Updates</span
                                    >
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full"
                                        >Article</span
                                    >
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <button class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                        ></path>
                                    </svg>
                                </button>
                                <button class="p-2 text-gray-400 hover:text-blue-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                                        ></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </PublicLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useI18n } from 'vue-i18n'
import PublicLayout from '@/components/layout/PublicLayout.vue'

export default {
    name: 'NewsDetail',
    components: {
        PublicLayout,
    },
    setup() {
        const { t, locale } = useI18n()
        return { t, locale }
    },
    data() {
        return {
            newsItem: null,
            isLoading: true,
            error: null,
        }
    },
    computed: {
        contentBlocks() {
            if (!this.newsItem || !this.newsItem.description) return []
            try {
                const parsed = JSON.parse(this.newsItem.description)
                return Array.isArray(parsed) ? parsed : [parsed]
            } catch (e) {
                console.error('Failed to parse news description JSON:', e)
                return [
                    {
                        type: 'paragraph',
                        content: this.newsItem.description || this.t('newsDetail.error.contentParse'),
                    },
                ]
            }
        },
        estimatedReadTime() {
            if (!this.newsItem || !this.newsItem.description) return 1
            const wordCount = this.newsItem.description.replace(/<[^>]*>/g, '').split(/\s+/).length
            return Math.max(1, Math.ceil(wordCount / 200))
        },
    },
    methods: {
        formatDate(dateString) {
            if (!dateString) return ''
            const dateLocale = this.locale === 'fa' ? 'fa-IR' : 'en-US'
            return new Date(dateString).toLocaleDateString(dateLocale, {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            })
        },
    },
    async mounted() {
        const newsId = this.$route.params.id
        try {
            const response = await apiCall('GET', `/api/news/${newsId}`)
            this.newsItem = response.news
        } catch (err) {
            console.error('Failed to fetch news detail:', err)
            this.error = this.t('newsDetail.error.loadFailed')
        } finally {
            this.isLoading = false
        }
    },
}
</script>
