<template>
    <form @submit.prevent="saveBannerSettings" class="bg-white p-6 sm:p-8 rounded-xl shadow-sm lg:col-span-2">
        <h3 class="text-xl font-bold text-gray-800">Notification Banner</h3>
        <p class="text-gray-500 mt-1 mb-6 text-sm">Set a site-wide banner for important announcements.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Banner Image</label>
                    <div
                        @click="$refs.bannerImageInput.click()"
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md cursor-pointer hover:border-indigo-500"
                    >
                        <div class="space-y-1 text-center">
                            <img
                                v-if="banner.imageUrl"
                                :src="banner.imageUrl"
                                class="mx-auto h-24 w-auto object-contain rounded-md mb-2"
                                alt="Image Preview"
                            />
                            <svg
                                v-else
                                class="mx-auto h-12 w-12 text-gray-400"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 48 48"
                                aria-hidden="true"
                            >
                                <path
                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <p class="pl-1">{{ banner.image ? banner.image.name : 'Click to upload an image' }}</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                        </div>
                    </div>
                    <input
                        ref="bannerImageInput"
                        type="file"
                        @change="handleBannerImageUpload"
                        class="hidden"
                        accept="image/*"
                    />
                    <p v-if="errors.banner_image" class="text-red-600 text-sm mt-1">{{ errors.banner_image[0] }}</p>
                </div>
            </div>
            <div class="space-y-4">
                <AyInput
                    v-model="banner.title"
                    label="Title"
                    placeholder="Summer Sale Is On!"
                    :error="errors.banner_title?.[0]"
                />
                <AyTextarea
                    v-model="banner.description"
                    label="Description"
                    :rows="3"
                    placeholder="Get 20% off all plans..."
                    :error="errors.banner_description?.[0]"
                />
                <AyInput
                    v-model="banner.ctaTitle"
                    label="CTA Button Title"
                    placeholder="View Plans"
                    :error="errors.banner_cta_title?.[0]"
                />
                <AyInput
                    v-model="banner.ctaLink"
                    label="CTA Link"
                    placeholder="/pricing"
                    :error="errors.banner_cta_link?.[0]"
                />
            </div>
            <div class="md:col-span-2 pt-2">
                <AyButton type="light-blue">Save Banner</AyButton>
            </div>
        </div>
    </form>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useToast } from 'vue-toast-notification'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AyTextarea from '@/components/atoms/form/AyTextarea.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'

export default {
    name: 'BannerSettingsCard',
    components: { AyInput, AyTextarea, AyButton },
    setup() {
        const $toast = useToast()
        return { $toast }
    },
    data() {
        return {
            baseUrl: window.location.origin,
            banner: { image: null, imageUrl: null, title: '', description: '', ctaTitle: '', ctaLink: '' },
            errors: {},
        }
    },
    mounted() {
        this.fetchSettings()
    },
    methods: {
        async fetchSettings() {
            try {
                const response = await apiCall('GET', '/api/admin/setting')
                if (response.banner_settings) {
                    this.banner = { ...this.banner, ...response.banner_settings }
                    if (response.banner_settings.imageUrl)
                        this.banner.imageUrl = `${this.baseUrl}/${response.banner_settings.imageUrl}`
                }
            } catch (error) {
                this.$toast.error('Could not load settings.')
            }
        },
        handleBannerImageUpload(event) {
            const file = event.target.files[0]
            if (!file) return
            this.banner.image = file
            this.banner.imageUrl = URL.createObjectURL(file)
        },
        async saveBannerSettings() {
            this.errors = {}
            const formData = new FormData()
            formData.append('banner_title', this.banner.title || '')
            formData.append('banner_description', this.banner.description || '')
            formData.append('banner_cta_title', this.banner.ctaTitle || '')
            formData.append('banner_cta_link', this.banner.ctaLink || '')
            if (this.banner.image) {
                formData.append('banner_image', this.banner.image)
            }
            try {
                const response = await apiCall('POST', '/api/admin/setting/banner', formData)
                this.$toast.success(response.message)
                this.fetchSettings()
            } catch (error) {
                if (error.response?.errors) this.errors = error.response.errors
            }
        },
    },
}
</script>
