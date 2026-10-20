<template>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm">
      <h3 class="text-lg font-semibold text-gray-800 mb-3">
        {{ t('publicProfile.aboutSection.title', { name: user.name }) }}
      </h3>
      <p class="text-sm text-gray-600 leading-relaxed">
        {{ user.about || t('publicProfile.aboutSection.noInfo') }}
      </p>
    </div>
    <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm">
      <div v-if="isTeacherProfile">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
          {{ t('publicProfile.teacher.institutesTitle') }}
        </h3>
        <div v-if="user.institutes?.length > 0" class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
          <RouterLink
            v-for="institute in user.institutes"
            :key="institute.id"
            :to="`/institute/profile/${institute.id}`"
            class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:border-blue-400 transition-colors duration-150"
          >
            <img :src="getAssetUrl(institute.avatar, true)" :alt="institute.name" class="w-8 h-8 rounded-full object-cover bg-gray-300" />
            <span class="text-sm font-medium text-gray-700">{{ institute.name }}</span>
          </RouterLink>
        </div>
        <p v-else class="text-sm text-gray-500 py-4 text-center">{{ t('publicProfile.teacher.noInstitutes') }}</p>
      </div>

      <div v-else>
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
          {{ t('publicProfile.institute.teachersTitle') }}
        </h3>
        <div v-if="user.teachers?.length > 0" class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
          <RouterLink
            v-for="teacher in user.teachers"
            :key="teacher.id"
            :to="`/teacher/profile/${teacher.id}`"
            class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:border-blue-400 transition-colors duration-150"
          >
            <img :src="getAssetUrl(teacher.avatar, true)" :alt="teacher.name" class="w-8 h-8 rounded-full object-cover bg-gray-300" />
            <span class="text-sm font-medium text-gray-700">{{ teacher.name }}</span>
          </RouterLink>
        </div>
        <p v-else class="text-sm text-gray-500 py-4 text-center">{{ t('publicProfile.institute.noTeachers') }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { RouterLink } from 'vue-router'

const { t } = useI18n()
defineProps({
  user: Object,
  isTeacherProfile: Boolean
})

const getAssetUrl = (path, isProfileAsset = false) => {
  const fallback = isProfileAsset ? '/imgHolders/profile.svg' : '/imgHolders/CardHolderImage.jpg'
  if (!path) return fallback
  if (path.startsWith('http')) return path
  return `/uploads/${path.replace(/\\/g, '')}`
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #c4c4c4; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a1a1a1; }
</style>
