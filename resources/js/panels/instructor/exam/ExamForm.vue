<template>
    <div>
        <div class="fixed inset-0 bg-gray-100 overflow-hidden">
            <div class="min-h-screen flex items-center justify-center p-4">
                <!-- Decorative Background -->
                <div
                    class="absolute w-100 h-100 bg-pink-200 rounded-full opacity-30"
                    style="top: 10%; left: -10%; transform: rotate(25deg); z-index: 0"
                ></div>
                <div
                    class="absolute w-100 h-100 bg-blue-200 rounded-lg opacity-20"
                    style="top: 40%; right: -10%; transform: rotate(-15deg); z-index: 0"
                ></div>

                <!-- Form Card -->
                <div class="bg-white w-full max-w-2xl p-6 rounded-lg shadow-sm z-10 relative">
                    <form @submit.prevent="handleSubmit">
                        <!-- Title Input -->
                        <div class="mb-4">
                            <label for="title" class="block text-lg font-bold text-gray-700">{{
                                t('ayhootCreate.title')
                            }}</label>
                            <input
                                type="text"
                                id="title"
                                v-model="exam.title"
                                :placeholder="t('ayhootCreate.placeholder')"
                                class="mt-2 block w-full px-3 py-2 font-semibold border-2 border-gray-300 rounded-md focus:outline-none"
                            />
                        </div>

                        <!-- Background Music Toggle -->
                        <div class="mb-4 flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div>
                                <label class="text-sm font-bold text-gray-700 block">
                                    {{ t('ayhootCreate.playMusic') }}
                                </label>
                                <p class="text-xs text-gray-500">
                                    {{ t('ayhootCreate.playMusicSubtitle') }}
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="exam.play_music" class="sr-only peer" />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- Submit & Cancel Buttons -->
                        <div class="flex items-center justify-end gap-2">
                            <button
                                type="button"
                                class="p-2 text-[#0F1D42] bg-[#C0C0C0] hover:bg-[#979797] shadow-[0_2px_0_rgba(136,136,136,1)] hover:shadow-none hover:translate-y-[2px] rounded-md cursor-pointer font-semibold text-sm"
                                @click="$router.back()"
                            >
                                {{ t('ayhootCreate.cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="p-2 text-white bg-[#3450AE] hover:bg-[#152B65] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px] rounded-md cursor-pointer font-semibold text-sm"
                            >
                                {{ isEdit ? t('ayhootCreate.update') : t('ayhootCreate.create') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { apiCall } from "@/services/apiService"
import { getRole } from '@/utils/role'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import { useI18n } from 'vue-i18n'

export default {
    props: {
        exam: Object,
        isEdit: Boolean,
    },
    components: {
        ResponsiveButton,
    },
    setup() {
        const { t } = useI18n()
        return { t }
    },
    methods: {
        async handleSubmit() {
            const url = this.isEdit ? `/api/instructor/exam/${this.exam.id}` : `/api/instructor/exam`
            // On edit, preserve the exam's existing monetize/price/status instead of
            // resetting them; on create, default to a published free exam.
            const response = await apiCall('POST', url, {
                title: this.exam.title,
                monetize: this.exam.monetize || 'free',
                status: this.exam.status || 'published',
                price: this.exam.price ?? 0,
                play_music: this.exam.play_music ? 1 : 0,
            })
            // After editing an existing exam, return to the exam list (so the save is
            // visible); after creating a new one, go to the question editor to add Qs.
            this.$router.push(
                this.isEdit ? `/${getRole()}/exam` : `/${getRole()}/exam/${response.exam.id}/create`
            )
        },
    },
}
</script>
