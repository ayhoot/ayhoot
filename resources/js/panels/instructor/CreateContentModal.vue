<template>
    <Modal :model-value="show" size="md" :title="$t(`${cfg.i18n}.title`)" @close="$emit('close')">
        <p class="text-sm text-gray-500 mb-4">{{ $t(`${cfg.i18n}.subtitle`) }}</p>
        <AyInput
            v-model="title"
            :label="$t(`${cfg.i18n}.titleLabel`)"
            :placeholder="$t(`${cfg.i18n}.titlePlaceholder`)"
            required
            @keyup.enter="submit"
        />

        <div v-if="kind === 'exam'" class="mt-4 flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
            <div>
                <label class="text-sm font-semibold text-gray-700 block">
                    {{ $t('createExamModal.playMusicLabel') }}
                </label>
                <p class="text-xs text-gray-500">
                    {{ $t('createExamModal.playMusicSubtitle') }}
                </p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="playMusic" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
        </div>

        <template #footer>
            <AyButton type="light-gray" :disabled="loading" @click="$emit('close')">
                {{ $t(`${cfg.i18n}.cancel`) }}
            </AyButton>
            <AyButton type="dash-blue" :disabled="loading" @click="submit">
                {{ loading ? $t(`${cfg.i18n}.creating`) : $t(`${cfg.i18n}.create`) }}
            </AyButton>
        </template>
    </Modal>
</template>

<script>
import Modal from '@/components/shared/Modal.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import { apiCall } from '@/services/apiService'
import { getRole } from '@/utils/role'

/**
 * Title-first creation modal for an exam or a course.
 * Creates the entity with a single title, then routes to its question/content
 * editor. The backend gates creation by subscription (PermissionService); that
 * 403 reason is surfaced by the global apiCall handler, so the wrong plan shows
 * a clear message instead of a silent failure.
 */
const CONFIG = {
    exam: {
        endpoint: '/api/instructor/exam',
        buildPayload: (title, playMusic) => ({ title, monetize: 'free', status: 'published', play_music: playMusic ? 1 : 0 }),
        resultKey: 'exam',
        i18n: 'createExamModal',
        redirect: (role, id) => `/${role}/exam/${id}/create`,
    },
    course: {
        endpoint: '/api/instructor/course',
        buildPayload: (title) => ({ title }),
        resultKey: 'course',
        i18n: 'createCourseModal',
        redirect: (role, id) => `/${role}/course/${id}/create`,
    },
}

export default {
    name: 'CreateContentModal',
    components: { Modal, AyButton, AyInput },
    props: {
        show: { type: Boolean, default: false },
        kind: { type: String, default: 'exam', validator: (v) => ['exam', 'course'].includes(v) },
    },
    emits: ['close', 'created'],
    data() {
        return { title: '', playMusic: false, loading: false }
    },
    computed: {
        cfg() {
            return CONFIG[this.kind] || CONFIG.exam
        },
    },
    watch: {
        // Reset the field each time the modal is reopened.
        show(open) {
            if (open) {
                this.title = ''
                this.playMusic = false
                this.loading = false
            }
        },
    },
    methods: {
        async submit() {
            if (!this.title.trim()) {
                this.$toast.error(this.$t(`${this.cfg.i18n}.titleRequired`))
                return
            }
            this.loading = true
            try {
                const response = await apiCall('POST', this.cfg.endpoint, this.cfg.buildPayload(this.title.trim(), this.playMusic))
                const entity = response[this.cfg.resultKey] || response
                const id = entity?.id
                if (!id) throw new Error('no-id')
                this.$emit('created', id)
                this.$router.push(this.cfg.redirect(getRole(), id))
            } catch (error) {
                // apiCall already toasts auth/permission/server errors; only add a
                // generic message for the cases it leaves to the caller.
                if (![401, 403, 404, 419, 500, 503].includes(error.status)) {
                    this.$toast.error(this.$t(`${this.cfg.i18n}.createFailed`))
                }
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
