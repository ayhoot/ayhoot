<template>
    <div class="relative rounded-xl overflow-hidden shadow-sm hover:shadow-md" :dir="isRTL ? 'rtl' : 'ltr'"
        :class="{ 'text-right': isRTL, 'text-left': !isRTL }">
        <div class="flex transition-transform duration-500"
            :style="{ transform: `translateX(-${currentIndex * 100}%)` }">
            <div v-for="(banner, index) in banners" :key="index" class="flex-shrink-0 w-full">
                <div class="relative w-full h-38 rounded-xl overflow-hidden">
                    <!-- Background Image -->
                    <img
                        :src="banner.imageUrl"
                        :alt="banner.title"
                        class="absolute inset-0 w-full h-full object-cover"
                    />
                    <!-- Overlay Gradient -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                    <!-- Content -->
                    <div class="absolute inset-0 px-6 py-6 flex flex-col justify-end text-white">
                        <h2 class="text-xl font-bold truncate max-w-xs">
                            {{ banner.title }}
                        </h2>
                        <div class="space-y-3">
                            <p class="text-sm line-clamp-2 max-w-sm">
                                {{ banner.description }}
                            </p>
                            <!-- <ResponsiveButton :to="banner.cta.link" class="px-4 py-2 text-sm w-fit" type="dash-blue">
                                {{ banner.cta.label }}
                            </ResponsiveButton> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button v-if="banners.length > 1" @click="prevSlide"
            :class="isRTL ? 'absolute right-2 top-1/2 transform -translate-y-1/2 bg-white/20 text-white rounded-full p-1 cursor-pointer hover:bg-white/40' : 'absolute left-2 top-1/2 transform -translate-y-1/2 bg-white/20 text-white rounded-full p-1 cursor-pointer hover:bg-white/40'">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    :d="isRTL ? 'M9 5l7 7-7 7' : 'M15 19l-7-7 7-7'" />
            </svg>
        </button>
        <button v-if="banners.length > 1" @click="nextSlide"
            :class="isRTL ? 'absolute left-2 top-1/2 transform -translate-y-1/2 bg-white/20 text-white rounded-full p-1 cursor-pointer hover:bg-white/40' : 'absolute right-2 top-1/2 transform -translate-y-1/2 bg-white/20 text-white rounded-full p-1 cursor-pointer hover:bg-white/40'">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    :d="isRTL ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7'" />
            </svg>
        </button>
    </div>
</template>

<script>
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useI18n } from 'vue-i18n'
import { apiCall } from "@/services/apiService"

export default {
    name: 'DashBannerSlider',
    components: { ResponsiveButton },
    setup() {
        const { locale, t } = useI18n()
        const isRTL = computed(() => locale.value === 'fa')
        const bannerList = ref([])
        const loading = ref(true)
        
        const banners = computed(() => {
            if (bannerList.value.length > 0) {
                return bannerList.value.map(banner => ({
                    imageUrl: banner.image_url || '/imgHolders/cardimgholder.png',
                    title: banner.title,
                    description: banner.description,
                    cta: { label: t('dashboard.banner.getStarted'), link: `/${localStorage.getItem('role') || 'teacher'}/dashboard` },
                }))
            }
            
            // Fallback to hardcoded banners if database is empty
            return [
                {
                    imageUrl: '/imgHolders/cardimgholder.png',
                    title: t('dashboard.banner.slide1.title'),
                    description: t('dashboard.banner.slide1.description'),
                    cta: { label: t('dashboard.banner.getStarted'), link: `/${localStorage.getItem('role') || 'teacher'}/dashboard` },
                },
                {
                    imageUrl: '/imgHolders/cardimgholder.png',
                    title: t('dashboard.banner.slide2.title'),
                    description: t('dashboard.banner.slide2.description'),
                    cta: { label: t('dashboard.banner.getStarted'), link: `/${localStorage.getItem('role') || 'teacher'}/dashboard` },
                },
                {
                    imageUrl: '/imgHolders/cardimgholder.png',
                    title: t('dashboard.banner.slide3.title'),
                    description: t('dashboard.banner.slide3.description'),
                    cta: { label: t('dashboard.banner.getStarted'), link: `/${localStorage.getItem('role') || 'teacher'}/dashboard` },
                }
            ]
        })
        
        const fetchBanners = async () => {
            try {
                const user = JSON.parse(localStorage.getItem('user') || '{}')
                const role = localStorage.getItem('role') || 'teacher'
                const userType = role === 'institute' ? 'institute' : 'teacher'
                const cityParam = user.city_id ? `&city_id=${user.city_id}` : ''
                const response = await apiCall('get', `/api/banners?user_type=${userType}${cityParam}`)
                bannerList.value = response.banners || []
            } catch (error) {
                console.error('Failed to fetch banners:', error)
            } finally {
                loading.value = false
            }
        }
        
        const currentIndex = ref(0)
        let autoSlideInterval = null

        function nextSlide() {
            currentIndex.value = (currentIndex.value + 1) % banners.value.length
            restartAutoSlide()
        }
        function prevSlide() {
            currentIndex.value =
                (currentIndex.value - 1 + banners.value.length) % banners.value.length
            restartAutoSlide()
        }
        function startAutoSlide() {
            autoSlideInterval = setInterval(() => { nextSlide() }, 5000)
        }
        function restartAutoSlide() {
            clearInterval(autoSlideInterval)
            startAutoSlide()
        }

        onMounted(() => {
            fetchBanners()
            startAutoSlide()
        })
        onBeforeUnmount(() => clearInterval(autoSlideInterval))

        return {
            banners,
            currentIndex,
            nextSlide,
            prevSlide,
            isRTL,
            locale
        }
    }
}
</script>
