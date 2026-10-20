<template>
    <div class="w-full" :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'">
        <div class="py-6 px-6 bg-white rounded-md">
            <QuillEditor
                contentType="html"
                theme="snow"
                :content="modelValue"
                @update:content="(newContent) => $emit('update:modelValue', newContent)"
                :placeholder="$t('createQuestion.slide.placeholder')"
                class="py-2 ay-quill-editor"
                :toolbar="toolbarOptions"
                :style="{ height: '20rem' }"
            />
        </div>
    </div>
</template>

<script>
// QuillEditor should be globally registered or imported here
// import { QuillEditor } from '@vueup/vue-quill';
// import '@vueup/vue-quill/dist/vue-quill.snow.css';

export default {
    name: 'SlideContent',
    // components: { QuillEditor }, // If imported locally
    props: {
        modelValue: {
            // HTML content of the slide
            type: String,
            default: '',
        },
        toolbarOptions: {
            type: [Array, Object], // Or String for preset toolbars
            default: () => [
                [{ header: [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ script: 'sub' }, { script: 'super' }],
                [{ indent: '-1' }, { indent: '+1' }],
                [{ direction: 'rtl' }], // This is important for RTL support inside the editor
                [{ align: [] }],
                ['link', 'image', 'video'],
                ['clean'],
            ],
        },
    },
    emits: ['update:modelValue'],
}
</script>
<style>
/* Scoped styles for Quill if needed, or ensure global styles are sufficient */
.ay-quill-editor .ql-editor {
    min-height: 18rem; /* Ensure the editor area has a good default height */
}
</style>
