<template>
    <div class="bg-slate-100 py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    {{ t('home.creators.title') }}
                </h2>
                <h3 class="sr-only">{{ t('home.creators.seo') }}</h3>
            </div>
            <div
                class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-4 sm:grid-cols-2 lg:mx-0 lg:max-w-none lg:grid-cols-4"
            >
                <div v-for="creator in creators" :key="creator.id">
                    <router-link
                        :to="`/${creator.type}/profile/${creator.id}`"
                        class="profile-card group relative block p-6 rounded-3xl overflow-hidden transition-all duration-300 ease-in-out hover:-translate-y-2 bg-white shadow-md hover:shadow-xl"
                    >
                        <div class="relative z-10 text-center">
                            <div class="relative w-32 h-32 mx-auto mb-8">
                                <div
                                    class="relative w-full h-full rounded-full overflow-hidden bg-gradient-to-br from-yellow-400 to-orange-400 flex items-center justify-center"
                                >
                                    <img
                                        v-if="creator.image"
                                        :src="creator.image"
                                        :alt="creator.name"
                                        class="w-full h-full object-cover"
                                        @error="handleImageError"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div class="avatar-border absolute inset-0 rounded-full"></div>
                            </div>

                            <div class="profile-info">
                                <h3
                                    class="text-lg font-bold mb-2 bg-gradient-to-r from-yellow-400 to-orange-400 bg-clip-text text-transparent"
                                >
                                    {{ creator.name }}
                                </h3>

                                <div class="flex justify-around mt-8 py-4 border-y border-slate-200">
                                    <div class="flex flex-col gap-2">
                                        <span
                                            class="text-lg font-bold bg-gradient-to-r from-yellow-400 to-orange-400 bg-clip-text text-transparent"
                                            >{{ creator.tutees }}</span
                                        >
                                        <span class="text-sm text-slate-500">{{ t('home.creators.tutees') }}</span>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <span
                                            class="text-lg font-bold bg-gradient-to-r from-yellow-400 to-orange-400 bg-clip-text text-transparent"
                                            >{{ creator.ayhoots }}</span
                                        >
                                        <span class="text-sm text-slate-500">{{ t('home.creators.ayhoots') }}</span>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <span
                                            class="text-lg font-bold bg-gradient-to-r from-yellow-400 to-orange-400 bg-clip-text text-transparent"
                                            >{{ creator.courses }}</span
                                        >
                                        <span class="text-sm text-slate-500">{{ t('home.creators.courses') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-shine absolute top-0 -left-full w-full h-full"></div>
                        <div
                            class="card-border absolute inset-0 rounded-3xl opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        ></div>
                        <div
                            class="card-glow absolute inset-0 bg-gradient-to-r from-yellow-400 to-orange-400 rounded-3xl opacity-0 blur-2xl transition-opacity duration-300 group-hover:opacity-10"
                        ></div>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { apiCall } from "@/services/apiService"

const { t, locale } = useI18n()
const creators = ref([])

const handleImageError = (event) => {
    event.target.style.display = 'none'
}

const fetchCreators = async () => {
    try {
        const response = await apiCall('GET', '/api/home')
        const list = Array.isArray(response.creators) ? response.creators.slice(0, 4) : []
        creators.value = list.map((creator) => ({
            id: creator.id,
            type: creator.type,
            name: creator.name,
            image: creator.image,
            tutees: creator.tutees,
            ayhoots: creator.ayhoots,
            courses: creator.courses,
        }))
    } catch (error) {
        console.error('Error fetching creators:', error)
        creators.value = []
    }
}

onMounted(() => {
    fetchCreators()
})
</script>

<style scoped>
.card-shine {
    background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.03), transparent);
    animation: shine 8s infinite;
}

@keyframes shine {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(200%);
    }
}

.card-border,
.avatar-border {
    border: 2px solid transparent;
    background: linear-gradient(45deg, #facc15, #fb923c) border-box;
    -webkit-mask:
        linear-gradient(#fff 0 0) padding-box,
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: destination-out;
    mask-composite: exclude;
}
</style>
