<template>
    <div class="mb-4">
        <h3 class="font-bold text-gray-700 mb-4">{{ t('discover.categories.title') }}</h3>

        <!-- Skeleton Loader State -->
        <div v-if="loading" class="flex overflow-hidden gap-4 w-full px-8 lg:px-16 py-1">
            <div
                v-for="n in 6"
                :key="n"
                class="animate-pulse flex-shrink-0 w-[calc(100%/2-0.5rem)] md:w-[calc(100%/4-0.75rem)] lg:w-[calc(100%/6-0.84rem)] h-32 bg-gray-200 rounded-lg"
            ></div>
        </div>

        <!-- Actual Carousel -->
        <div v-else-if="categories && categories.length > 0" class="relative">
            <div class="flex items-center">
                <!-- Prev Button -->
                <button
                    @click="prev"
                    class="absolute z-10 bg-white/50 hover:bg-white backdrop-blur-sm cursor-pointer p-2 rounded-full shadow-md transition-opacity duration-300 text-black"
                    :class="[
                        $i18n.locale === 'fa' ? 'right-0 lg:right-5' : 'left-0 lg:left-5',
                        { 'opacity-0 pointer-events-none': !canScrollPrev },
                    ]"
                    :aria-label="t('discover.categories.previous')"
                >
                    <DashArrowLeft :class="{ 'rotate-180': $i18n.locale === 'fa' }" />
                </button>

                <!-- Carousel Container -->
                <div
                    ref="carousel"
                    @scroll="handleScroll"
                    class="flex overflow-x-auto gap-4 scroll-smooth snap-x snap-mandatory w-full px-8 lg:px-16 py-1"
                >
                    <RouterLink
                        v-for="category in categories"
                        :key="category.id"
                        :to="`${basePath}/${category.id}`"
                        :style="{ backgroundImage: `url('${getCategoryImageUrl(category.logo)}')` }"
                        class="snap-start relative p-4 flex-shrink-0 w-[calc(100%/2-0.5rem)] md:w-[calc(100%/4-0.75rem)] lg:w-[calc(100%/6-0.84rem)] h-32 bg-cover bg-center flex items-end justify-start text-md text-white font-bold rounded-lg shadow-sm cursor-pointer transition-transform transform hover:scale-103 hover:shadow-lg duration-200 ease-in-out group"
                    >
                        <div
                            class="absolute inset-0 bg-black/30 group-hover:bg-black/50 transition-colors duration-300 rounded-lg"
                        ></div>
                        <span class="relative z-10">{{ category.title }}</span>
                    </RouterLink>
                </div>

                <!-- Next Button -->
                <button
                    @click="next"
                    class="absolute z-10 bg-white/50 hover:bg-white backdrop-blur-sm p-2 rounded-full cursor-pointer shadow-md transition-opacity duration-300"
                    :class="[
                        $i18n.locale === 'fa' ? 'left-0 lg:left-5' : 'right-0 lg:right-5',
                        { 'opacity-0 pointer-events-none': !canScrollNext },
                    ]"
                    :aria-label="t('discover.categories.next')"
                >
                    <DashArrowRight :class="{ 'rotate-180': $i18n.locale === 'fa' }" />
                </button>
            </div>
        </div>

        <!-- "No Data" message -->
        <div v-else class="text-center text-gray-500 py-8">{{ t('discover.categories.empty') }}</div>
    </div>
</template>

<script>
import { useI18n } from 'vue-i18n'
import { RouterLink } from 'vue-router'
import DashArrowRight from '@/components/atoms/icons/DashArrowRight.vue'
import DashArrowLeft from '@/components/atoms/icons/DashArrowLeft.vue'

export default {
    name: 'CategoriesCarousel',
    components: {
        DashArrowRight,
        DashArrowLeft,
        RouterLink,
    },
    props: {
        categories: {
            type: Array,
            required: true,
            default: () => [],
        },
        loading: {
            type: Boolean,
            default: true,
        },
        // Route prefix each category links to. Defaults to the public discover
        // route; panel pages pass `/${role}/discover/categories` so links stay
        // inside the instructor/student panel.
        basePath: {
            type: String,
            default: '/discover/categories',
        },
    },
    setup() {
        const { t } = useI18n()
        return { t }
    },
    data() {
        return {
            canScrollPrev: false,
            canScrollNext: true,
        }
    },
    methods: {
        getCategoryImageUrl(logoPath) {
            if (!logoPath) {
                return '/imgHolders/CardHolderImage.jpg'
            }
            if (/^https?:\/\//.test(logoPath)) return encodeURI(logoPath)
            // Normalise Windows backslashes, drop any leading slash, then encode —
            // category filenames can contain spaces which break a CSS url().
            const cleanedPath = logoPath.replace(/\\/g, '/').replace(/^\/+/, '')
            return encodeURI('/' + cleanedPath)
        },
        next() {
            const carousel = this.$refs.carousel
            if (!carousel) return
            carousel.scrollBy({ left: carousel.clientWidth, behavior: 'smooth' })
        },
        prev() {
            const carousel = this.$refs.carousel
            if (!carousel) return
            carousel.scrollBy({ left: -carousel.clientWidth, behavior: 'smooth' })
        },
        handleScroll() {
            const carousel = this.$refs.carousel
            if (!carousel) return
            const tolerance = 2
            this.canScrollPrev = carousel.scrollLeft > tolerance
            const maxScrollLeft = carousel.scrollWidth - carousel.clientWidth
            this.canScrollNext = carousel.scrollLeft < maxScrollLeft - tolerance
        },
        updateButtonStates() {
            this.$nextTick(() => {
                if (this.$refs.carousel) {
                    const isScrollable = this.$refs.carousel.scrollWidth > this.$refs.carousel.clientWidth
                    if (!isScrollable) {
                        this.canScrollNext = false
                        this.canScrollPrev = false
                    } else {
                        this.handleScroll()
                    }
                }
            })
        },
    },
    mounted() {
        if (!this.loading) {
            this.updateButtonStates()
        }
        window.addEventListener('resize', this.updateButtonStates)
    },
    beforeUnmount() {
        window.removeEventListener('resize', this.updateButtonStates)
    },
    watch: {
        categories() {
            this.updateButtonStates()
        },
        loading(newVal) {
            if (newVal === false) {
                this.updateButtonStates()
            }
        },
    },
}
</script>

<style scoped>
.overflow-x-auto {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.overflow-x-auto::-webkit-scrollbar {
    display: none;
}
@keyframes pulse {
    50% {
        opacity: 0.5;
    }
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
