<template>
    <router-link
        :to="`/news/${newsItem.id}`"
        class="group block border border-gray-200 bg-white rounded-lg hover:shadow-sm transition-shadow duration-300 overflow-hidden"
    >
        <div class="relative">
            <img
                :src="imageUrl"
                :alt="newsItem.title"
                class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
        </div>
        <div class="p-6 z-50">
            <p class="text-xs font-semibold text-gray-400 mb-2">{{ formatDate(newsItem.created_at) }}</p>
            <h3
                class="text-xl font-bold text-gray-900 leading-tight mb-3 group-hover:text-blue-00 transition-colors duration-300"
            >
                {{ newsItem.title }}
            </h3>
            <p class="text-gray-600 text-sm line-clamp-3">
                {{ snippet }}
            </p>
        </div>
    </router-link>
</template>

<script>
import { RouterLink } from 'vue-router'

export default {
    name: 'NewsCard',
    components: {
        RouterLink,
    },
    props: {
        newsItem: {
            type: Object,
            required: true,
        },
    },
    computed: {
        imageUrl() {
            return this.newsItem.image
                ? `/uploads/${this.newsItem.image.replace(/\\/g, '/')}`
                : '/imgHolders/fallback-news.jpg'
        },
        snippet() {
            try {
                const blocks = JSON.parse(this.newsItem.description)
                const firstParagraph = blocks.find((block) => block.type === 'paragraph')
                if (firstParagraph && firstParagraph.content) {
                    const tempDiv = document.createElement('div')
                    tempDiv.innerHTML = firstParagraph.content
                    return tempDiv.textContent || tempDiv.innerText || ''
                }
                return 'No description available.'
            } catch (e) {
                return 'No description available.'
            }
        },
    },
    methods: {
        formatDate(dateString) {
            if (!dateString) return ''
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            })
        },
    },
}
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
