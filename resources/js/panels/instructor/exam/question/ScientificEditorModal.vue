<template>
    <Modal
        :model-value="show"
        size="xl"
        :title="$t('createQuestion.modal.scientificTitle')"
        @close="$emit('cancel')"
    >
        <QuillEditor
            :key="editorKey"
            :content="content"
            @update:content="$emit('update:content', $event)"
            contentType="html"
            theme="snow"
            class="w-full bg-white min-h-[200px]"
            :toolbar="toolbarOptions"
        />
        <template #footer>
            <ResponsiveButton type="light-gray" class="p-2 px-4" @click="$emit('cancel')">
                {{ $t('createQuestion.modal.cancel') }}
            </ResponsiveButton>
            <ResponsiveButton type="light-green" class="p-2 px-4" @click="$emit('save')">
                {{ $t('createQuestion.modal.save') }}
            </ResponsiveButton>
        </template>
    </Modal>
</template>

<script>
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import Modal from '@/components/shared/Modal.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'

export default {
    name: 'ScientificEditorModal',
    components: { QuillEditor, Modal, ResponsiveButton },
    props: {
        show: { type: Boolean, default: false },
        content: { type: String, default: '' },
        toolbarOptions: { type: Array, default: () => [] },
        editorKey: { type: [Number, String], default: 0 },
    },
    emits: ['update:content', 'save', 'cancel'],
}
</script>
