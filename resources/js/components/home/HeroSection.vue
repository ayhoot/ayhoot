<template>
    <div
        class="relative h-screen bg-gray-900 flex items-center justify-center text-center text-white overflow-hidden select-none"
        @mouseenter="pauseAutoplay"
        @mouseleave="startAutoplay"
        @touchstart="handleTouchStart"
        @touchend="handleTouchEnd"
    >
        <!-- Slides -->
        <div
            v-for="(slide, index) in displaySlides"
            :key="slide.id || index"
            class="absolute inset-0 transition-opacity duration-700 ease-in-out"
            :class="[
                currentIndex === index ? 'opacity-100 z-10 pointer-events-auto' : 'opacity-0 z-0 pointer-events-none'
            ]"
        >
            <!-- Background Image -->
            <div
                class="absolute inset-0 bg-cover bg-center z-0"
                :style="{ backgroundImage: `url('${slide.image || '/imgHolders/LPLP.jpg'}')` }"
            >
                <p class="sr-only">{{ t('home.hero.seo') }}</p>
            </div>

            <!-- Overlay (exact match to current style) -->
            <div class="absolute inset-0 bg-black/30 z-10"></div>

            <!-- Content (exact match to current style) -->
            <div class="relative z-20 h-full flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl font-bold tracking-tight sm:text-6xl md:text-7xl max-w-5xl mx-auto transition-transform duration-500"
                    :class="currentIndex === index ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"
                >
                    {{ slide.title || t('home.hero.title') }}
                </h1>
                <p
                    v-if="slide.description !== null"
                    class="mt-6 text-lg leading-8 sm:text-xl md:text-2xl max-w-2xl mx-auto transition-transform duration-500 delay-100"
                    :class="currentIndex === index ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"
                >
                    {{ slide.description || t('home.hero.subtitle') }}
                </p>
                <div
                    v-if="slide.button_text !== null"
                    class="mt-4 flex justify-center transition-transform duration-500 delay-200"
                    :class="currentIndex === index ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"
                >
                    <AyButton :to="slide.button_link || '/signup'" type="yellow">
                        {{ slide.button_text || t('home.getStarted') }}
                    </AyButton>
                </div>
            </div>
        </div>

        <!-- Previous Button -->
        <button
            v-if="displaySlides.length > 1"
            @click="prevSlide"
            type="button"
            class="absolute start-3 sm:start-6 top-1/2 -translate-y-1/2 z-30 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-black/30 hover:bg-black/60 backdrop-blur-sm border border-white/20 text-white flex items-center justify-center cursor-pointer transition-all duration-200 hover:scale-105 active:scale-95 shadow-md"
            :aria-label="t('quizGame.header.prev') || 'Previous slide'"
        >
            <svg class="w-4 h-4 sm:w-5 sm:h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Next Button -->
        <button
            v-if="displaySlides.length > 1"
            @click="nextSlide"
            type="button"
            class="absolute end-3 sm:end-6 top-1/2 -translate-y-1/2 z-30 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-black/30 hover:bg-black/60 backdrop-blur-sm border border-white/20 text-white flex items-center justify-center cursor-pointer transition-all duration-200 hover:scale-105 active:scale-95 shadow-md"
            :aria-label="t('quizGame.header.next') || 'Next slide'"
        >
            <svg class="w-4 h-4 sm:w-5 sm:h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Pagination Indicators -->
        <div
            v-if="displaySlides.length > 1"
            class="absolute bottom-8 z-30 inset-x-0 flex justify-center items-center gap-2.5"
        >
            <button
                v-for="(_, index) in displaySlides"
                :key="index"
                @click="goToSlide(index)"
                type="button"
                :class="[
                    'transition-all duration-300 rounded-full cursor-pointer',
                    currentIndex === index
                        ? 'w-8 h-2.5 bg-yellow-400 shadow-md'
                        : 'w-2.5 h-2.5 bg-white/50 hover:bg-white/80'
                ]"
                :aria-label="`Slide ${index + 1}`"
            ></button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import AyButton from '@/components/atoms/button/AyButton.vue'
import { apiCall } from '@/services/apiService'

const { t } = useI18n()

const slides = ref([])
const currentIndex = ref(0)
let autoplayTimer = null
let touchStartX = 0

// Default fallback slide matching original HeroSection
const defaultSlide = computed(() => ({
    id: 'default',
    title: t('home.hero.title'),
    description: t('home.hero.subtitle'),
    image: '/imgHolders/LPLP.jpg',
    button_text: t('home.getStarted'),
    button_link: '/signup',
}))

const displaySlides = computed(() => {
    if (slides.value && slides.value.length > 0) {
        return slides.value
    }
    return [defaultSlide.value]
})

const fetchSlides = async () => {
    try {
        const response = await apiCall('GET', '/api/hero-slides')
        const list = Array.isArray(response?.slides) ? response.slides : []
        if (list.length > 0) {
            slides.value = list
        }
    } catch (e) {
        // Fallback silently to default slide
        console.debug('Using default hero slide:', e)
    }
}

const nextSlide = () => {
    if (displaySlides.value.length <= 1) return
    currentIndex.value = (currentIndex.value + 1) % displaySlides.value.length
}

const prevSlide = () => {
    if (displaySlides.value.length <= 1) return
    currentIndex.value =
        (currentIndex.value - 1 + displaySlides.value.length) % displaySlides.value.length
}

const goToSlide = (index) => {
    currentIndex.value = index
}

const startAutoplay = () => {
    stopAutoplay()
    if (displaySlides.value.length > 1) {
        autoplayTimer = setInterval(() => {
            nextSlide()
        }, 5500)
    }
}

const pauseAutoplay = () => {
    stopAutoplay()
}

const stopAutoplay = () => {
    if (autoplayTimer) {
        clearInterval(autoplayTimer)
        autoplayTimer = null
    }
}

const handleTouchStart = (e) => {
    if (e.touches && e.touches[0]) {
        touchStartX = e.touches[0].clientX
    }
}

const handleTouchEnd = (e) => {
    if (!touchStartX || !e.changedTouches || !e.changedTouches[0]) return
    const touchEndX = e.changedTouches[0].clientX
    const diff = touchEndX - touchStartX
    if (Math.abs(diff) > 50) {
        if (diff > 0) {
            prevSlide()
        } else {
            nextSlide()
        }
    }
    touchStartX = 0
}

onMounted(async () => {
    await fetchSlides()
    startAutoplay()
})

onUnmounted(() => {
    stopAutoplay()
})
</script>
