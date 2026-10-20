<template>
    <Modal :model-value="show" size="sm" :title="modalTitle" @close="$emit('close')">
        <div class="space-y-4">
            <!-- Header Exam Info -->
            <div v-if="selectedExam" class="flex items-center gap-3 p-3 bg-blue-50/70 border border-blue-100 rounded-xl">
                <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-sm font-semibold text-gray-900 truncate">{{ selectedExam.title }}</h4>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ selectedExam.questions?.length || 0 }} {{ t('contentCard.questions') || 'questions' }}
                    </p>
                </div>
            </div>

            <!-- Session Title Input (Pre-Creation) -->
            <div v-if="!liveSessionData">
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                    {{ t('exam.hostLive.sessionTitle') || 'Session Title' }}
                </label>
                <AyInput
                    :model-value="title"
                    type="text"
                    :placeholder="`Live: ${selectedExam?.title || 'Session'}`"
                    @update:model-value="$emit('update:title', $event)"
                />
                <p class="text-xs text-gray-400 mt-1.5">
                    {{ t('exam.hostLive.helperText') || 'Participants will join this room in real time using the Game PIN or QR code.' }}
                </p>
            </div>

            <!-- Live Session Details (Post-Creation) -->
            <div v-else class="space-y-4 pt-1">
                <!-- PIN Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50/60 border border-blue-100 rounded-xl p-4 text-center relative overflow-hidden">
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-700">
                        {{ t('exam.hostLive.gamePin') || 'Game PIN' }}
                    </span>
                    <div class="text-3xl sm:text-4xl font-black text-gray-900 tracking-widest font-mono my-1">
                        {{ sessionPin || '...' }}
                    </div>
                    <div class="flex justify-center mt-2">
                        <AyButton
                            :type="isPinCopied ? 'light-green' : 'dash-blue'"
                            @click="copyPin"
                            class="text-xs px-3 py-1.5 gap-1.5"
                        >
                            <svg v-if="isPinCopied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                            <span>{{ isPinCopied ? (t('exam.hostLive.pinCopied') || 'PIN Copied!') : (t('exam.hostLive.copyPin') || 'Copy PIN') }}</span>
                        </AyButton>
                    </div>
                </div>

                <!-- QR Code Box -->
                <div class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <p class="text-xs font-semibold text-gray-600 mb-2">
                        {{ t('exam.hostLive.scanToJoin') || 'Scan with camera to join' }}
                    </p>
                    <div class="bg-white p-2.5 rounded-xl border border-gray-200 shadow-xs">
                        <QRCode
                            v-if="joinUrl"
                            :value="joinUrl"
                            :size="180"
                            level="M"
                            class="mx-auto block"
                        />
                    </div>
                    <div class="w-full mt-3 flex items-center gap-1.5">
                        <input
                            type="text"
                            readonly
                            :value="joinUrl"
                            class="flex-1 min-w-0 text-xs text-gray-600 bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 font-mono select-all focus:outline-none"
                            @click="$event.target.select()"
                        />
                        <AyButton
                            :type="isUrlCopied ? 'light-green' : 'light-gray'"
                            @click="copyUrl"
                            class="shrink-0 text-xs px-2.5 py-1.5"
                        >
                            {{ isUrlCopied ? (t('exam.hostLive.copied') || 'Copied') : (t('exam.hostLive.copy') || 'Copy') }}
                        </AyButton>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center gap-2 w-full">
                <!-- Create Button -->
                <AyButton
                    v-if="!liveSessionData"
                    type="light-green"
                    @click="$emit('create')"
                    :disabled="creatingSession"
                    class="flex-1 text-sm py-2"
                >
                    <span v-if="creatingSession" class="flex items-center gap-1.5">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ t('exam.hostLive.creating') || 'Creating...' }}
                    </span>
                    <span v-else>{{ t('exam.hostLive.createSession') || 'Create Session' }}</span>
                </AyButton>

                <!-- Go to Host Button -->
                <AyButton
                    v-if="liveSessionData"
                    type="dash-blue"
                    @click="$emit('go-to-host')"
                    class="flex-1 text-sm py-2"
                >
                    {{ t('exam.hostLive.goToHostPage') || 'Go to Host Page →' }}
                </AyButton>

                <!-- Cancel / Close -->
                <AyButton
                    type="light-gray"
                    @click="$emit('close')"
                    class="text-sm py-2 shrink-0"
                >
                    {{ liveSessionData ? (t('exam.hostLive.done') || 'Done') : (t('exam.hostLive.cancel') || 'Cancel') }}
                </AyButton>
            </div>
        </template>
    </Modal>
</template>

<script>
import QRCode from 'qrcode.vue'
import Modal from '@/components/shared/Modal.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toast-notification'

export default {
    name: 'ExamHostLiveModal',
    components: {
        QRCode,
        Modal,
        AyInput,
        AyButton
    },
    setup() {
        const { t } = useI18n()
        const toast = useToast()
        return { t, toast }
    },
    props: {
        show: {
            type: Boolean,
            default: false
        },
        selectedExam: {
            type: Object,
            default: null
        },
        liveSessionData: {
            type: Object,
            default: null
        },
        creatingSession: {
            type: Boolean,
            default: false
        },
        title: {
            type: String,
            default: ''
        }
    },
    emits: ['close', 'create', 'go-to-host', 'update:title'],
    data() {
        return {
            isPinCopied: false,
            isUrlCopied: false
        }
    },
    computed: {
        modalTitle() {
            const translated = this.t('exam.hostLive.title')
            return (translated && translated !== 'exam.hostLive.title') ? translated : 'Host Live Session'
        },
        sessionPin() {
            return this.liveSessionData?.pin || this.liveSessionData?.session?.pin || ''
        },
        joinUrl() {
            if (this.liveSessionData?.join_url) {
                return this.liveSessionData.join_url
            }
            const pin = this.sessionPin
            if (pin) {
                return `${window.location.origin}/hostlive/${pin}`
            }
            return ''
        }
    },
    methods: {
        async copyPin() {
            const pin = this.sessionPin
            if (!pin) return
            try {
                await navigator.clipboard.writeText(String(pin))
                this.isPinCopied = true
                this.toast.success(this.t('exam.hostLive.pinCopied') || 'PIN copied to clipboard!')
                setTimeout(() => {
                    this.isPinCopied = false
                }, 2000)
            } catch (e) {
                console.error('Failed to copy PIN:', e)
            }
        },
        async copyUrl() {
            const url = this.joinUrl
            if (!url) return
            try {
                await navigator.clipboard.writeText(url)
                this.isUrlCopied = true
                this.toast.success(this.t('exam.hostLive.urlCopied') || 'Join URL copied!')
                setTimeout(() => {
                    this.isUrlCopied = false
                }, 2000)
            } catch (e) {
                console.error('Failed to copy join URL:', e)
            }
        }
    }
}
</script>
