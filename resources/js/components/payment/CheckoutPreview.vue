<template>
  <div class="border-2 border-gray-200 rounded-lg p-4">
    <div class="flex items-start gap-4">
      <img
        :src="getCoverUrl(content.cover)"
        :alt="content.title"
        class="w-20 h-20 object-cover rounded-lg bg-gray-200"
      />
      <div class="flex-1">
        <div class="text-xs font-semibold text-gray-500 uppercase">{{ contentType }} Purchase</div>
        <h3 class="text-lg font-bold text-gray-800">{{ content.title }}</h3>
        <div class="flex items-center gap-2 mt-2">
          <img
            :src="getCoverUrl(ownerAvatar, true)"
            alt="Owner Avatar"
            class="w-6 h-6 rounded-full object-cover"
          />
          <span class="text-sm text-gray-600">{{ ownerName }}</span>
        </div>
        <div class="text-lg font-bold text-blue-600 mt-2">{{ formatPrice(content.price) }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  content: Object,
  contentType: String,
  ownerName: String,
  ownerAvatar: String
})

const formatPrice = (amount) => {
  const currency = 'Rial'
  if (typeof amount !== 'number') return `0 ${currency}`
  return `${Math.round(amount).toLocaleString('en-US')} ${currency}`
}

const getCoverUrl = (path, isProfile = false) => {
  const fallback = isProfile ? '/imgHolders/profile.svg' : '/imgHolders/CardHolderImage.jpg'
  if (!path) return fallback
  if (path.startsWith('http')) return path
  return `/uploads/${path.replace(/\\/g, '')}`
}
</script>
