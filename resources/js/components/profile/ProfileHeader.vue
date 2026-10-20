<template>
  <div class="w-full relative mt-15 sm:mt-0">
    <img
      :src="getAssetUrl(user.cover)"
      :alt="t('publicProfile.coverImageAlt', { name: user.name })"
      class="w-full lg:h-[30vh] h-[15vh] object-cover bg-gray-200"
    />
  </div>

  <div class="w-full lg:px-12 px-6 -mt-12 sm:-mt-16 mb-8">
    <div
      class="flex flex-col lg:flex-row items-center lg:items-end justify-between bg-white p-4 sm:p-6 rounded-xl shadow-lg relative"
    >
      <div class="flex flex-col sm:flex-row items-center text-center sm:text-left">
        <img
          :src="getAssetUrl(user.image, true)"
          :alt="user.name"
          class="w-24 h-24 sm:w-32 sm:h-32 rounded-full object-cover border-4 border-white shadow-md -mt-12 sm:-mt-16 lg:-mt-20 mb-2 sm:mb-0 bg-gray-200"
          :class="isRTL ? 'sm:ml-6' : 'sm:mr-6'"
        />
        <div class="sm:pt-2">
          <div class="flex items-center justify-center sm:justify-start gap-1">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-800">{{ user.name }}</h3>
            <DashVerified v-if="user.verified" class="w-5 h-5 sm:w-6 sm:h-6" />
          </div>
          <a
            v-if="user.website"
            :href="user.website"
            target="_blank"
            rel="noopener noreferrer"
            class="text-xs sm:text-sm font-semibold text-blue-600 hover:underline"
          >
            {{ user.website }}
          </a>
          <p v-else class="text-xs sm:text-sm text-gray-500">
            {{ t('publicProfile.noWebsite') }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-6 sm:gap-8 mt-4 lg:mt-0 text-gray-700">
        <div class="text-center">
          <p class="text-lg sm:text-xl font-bold">{{ user.contentCount || 0 }}</p>
          <h3 class="text-xs sm:text-sm text-gray-500 uppercase">
            {{ t('publicProfile.stats.content') }}
          </h3>
        </div>
        <div class="text-center">
          <p class="text-lg sm:text-xl font-bold">{{ user.plays || 0 }}</p>
          <h3 class="text-xs sm:text-sm text-gray-500 uppercase">
            {{ t('publicProfile.stats.plays') }}
          </h3>
        </div>
        <div class="text-center">
          <p class="text-lg sm:text-xl font-bold">{{ user.tutees || 0 }}</p>
          <h3 class="text-xs sm:text-sm text-gray-500 uppercase">
            {{ t('publicProfile.stats.tutees') }}
          </h3>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import DashVerified from '@/components/atoms/icons/DashVerified.vue'

const { t } = useI18n()
const props = defineProps({
  user: Object,
  isRTL: Boolean
})

const getAssetUrl = (path, isProfileAsset = false) => {
  const fallback = isProfileAsset ? '/imgHolders/profile.svg' : '/imgHolders/CardHolderImage.jpg'
  if (!path) return fallback
  if (path.startsWith('http')) return path
  return `/uploads/${path.replace(/\\/g, '')}`
}
</script>
