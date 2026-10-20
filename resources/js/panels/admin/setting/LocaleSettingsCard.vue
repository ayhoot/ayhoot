<template>
    <div class="bg-white p-6 sm:p-8 rounded-xl shadow-sm lg:col-span-1">
        <h3 class="text-xl font-bold text-gray-800">Locale Settings</h3>
        <p class="text-gray-500 mt-1 mb-6 text-sm">Configure language and locale filtering.</p>

        <div class="space-y-4">
            <!-- Enable/Disable Toggle -->
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Enable Locale Filtering</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="localeSettings.locale_filtering_enabled"
                        @change="updateLocaleSettings"
                        class="sr-only peer"
                    />
                    <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"
                    ></div>
                </label>
            </div>

            <div v-if="localeSettings.locale_filtering_enabled" class="space-y-4">
                <!-- Default Locale -->
                <AySelect
                    :model-value="localeSettings.default_locale"
                    label="Default Locale"
                    @update:model-value="(v) => onDefaultLocale(v)"
                >
                    <option value="en">English (en)</option>
                    <option value="fa">فارسی (fa)</option>
                    <option value="ar">العربية (ar)</option>
                </AySelect>

                <!-- Supported Locales -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Supported Locales</label>
                    <div class="space-y-2">
                        <label v-for="loc in localeOptions" :key="loc.value" class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                :value="loc.value"
                                v-model="localeSettings.supported_locales"
                                @change="updateLocaleSettings"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="text-sm text-gray-700">{{ loc.label }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-4">
                <p class="text-gray-500 text-sm">
                    Locale filtering is disabled. All content will be visible regardless of language.
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useToast } from 'vue-toast-notification'
import AySelect from '@/components/atoms/form/AySelect.vue'

export default {
    name: 'LocaleSettingsCard',
    components: { AySelect },
    setup() {
        const $toast = useToast()
        return { $toast }
    },
    data() {
        return {
            localeSettings: {
                locale_filtering_enabled: false,
                supported_locales: ['en'],
                default_locale: 'en',
            },
            localeOptions: [
                { value: 'en', label: 'English (en)' },
                { value: 'fa', label: 'فارسی (fa)' },
                { value: 'ar', label: 'العربية (ar)' },
            ],
        }
    },
    mounted() {
        this.fetchLocaleSettings()
    },
    methods: {
        onDefaultLocale(value) {
            this.localeSettings.default_locale = value
            this.updateLocaleSettings()
        },
        async fetchLocaleSettings() {
            try {
                const response = await apiCall('GET', '/api/admin/setting/locale')
                this.localeSettings = response
            } catch (error) {
                console.error('Failed to load locale settings:', error)
            }
        },
        async updateLocaleSettings() {
            try {
                if (!this.localeSettings.supported_locales.includes(this.localeSettings.default_locale)) {
                    this.localeSettings.supported_locales.push(this.localeSettings.default_locale)
                }
                await apiCall('POST', '/api/admin/setting/locale', this.localeSettings)
                this.$toast.success('Locale settings updated successfully')
            } catch (error) {
                console.error('Failed to update locale settings:', error)
                this.$toast.error('Failed to update locale settings')
            }
        },
    },
}
</script>
