<template>
    <div class="bg-white/60 p-4 rounded-lg shadow-sm my-4" :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'">
        <label class="block font-semibold text-gray-700 mb-1" :class="{ 'text-right': $i18n.locale === 'fa' }">
            {{ $t('createQuestion.fileUpload.title') }}
        </label>
        <label class="block text-sm font-medium text-gray-500 mb-3" :class="{ 'text-right': $i18n.locale === 'fa' }">
            {{ $t('createQuestion.fileUpload.subtitle') }}
        </label>

        <div
            class="mt-1 flex justify-center px-2 py-4 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer relative"
            @dragover.prevent
            @drop.prevent="handleDrop"
            @click="triggerFileSelect"
        >
            <input ref="fileInputRef" type="file" accept="image/*" class="hidden" @change="handleFileChange" />

            <div v-if="modelValue.preview" class="relative w-full h-60">
                <img :src="modelValue.preview" class="object-contain w-full h-full rounded-md" alt="Preview" />
                <button
                    class="absolute top-2"
                    :class="$i18n.locale === 'fa' ? 'left-2' : 'right-2'"
                    @click.stop="removeImage"
                >
                    <DashRemove />
                </button>
            </div>

            <div v-else class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path
                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                <div
                    class="flex text-sm text-gray-600 justify-center"
                    :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }"
                >
                    <span class="bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500">
                        {{ $t('createQuestion.fileUpload.uploadFile') }}
                    </span>
                    <p :class="$i18n.locale === 'fa' ? 'pr-1' : 'pl-1'">
                        {{ $t('createQuestion.fileUpload.orDragDrop') }}
                    </p>
                </div>
                <p class="text-xs text-gray-500">{{ $t('createQuestion.fileUpload.supportedFileTypes') }}</p>
            </div>
        </div>
    </div>
</template>

<script>
import DashRemove from '@/components/atoms/icons/DashRemove.vue'

export default {
    name: 'FileUpload',
    components: { DashRemove },
    props: {
        modelValue: {
            // Expects an object like { file: null, preview: null }
            type: Object,
            default: () => ({ file: null, preview: null }),
        },
    },
    emits: ['update:modelValue'],
    methods: {
        triggerFileSelect() {
            this.$refs.fileInputRef?.click()
        },
        handleFileChange(event) {
            const file = event.target.files[0]
            if (file) {
                this.setFile(file)
            }
        },
        handleDrop(event) {
            const file = event.dataTransfer.files[0]
            if (file) {
                this.setFile(file)
            }
        },
        setFile(file) {
            const reader = new FileReader()
            reader.onload = (e) => {
                this.$emit('update:modelValue', { file: file, preview: e.target.result })
            }
            reader.readAsDataURL(file)
        },
        removeImage() {
            this.$emit('update:modelValue', { file: null, preview: null })
            if (this.$refs.fileInputRef) {
                this.$refs.fileInputRef.value = null
            }
        },
    },
}
</script>
