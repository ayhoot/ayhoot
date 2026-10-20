<template>
    <div class="flex-1 flex flex-col min-h-0 w-full" :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'">
        <!-- Question Content Section (Title & Media) -->
        <div class="flex-1 flex flex-col p-2 sm:p-3 md:p-4 min-h-0 overflow-hidden">
            <!-- Question Title — let it grow with content; only scroll if very long -->
            <div class="w-full max-w-5xl mx-auto shrink-0 mb-2 sm:mb-3">
                <div
                    v-if="isHTML(question.title)"
                    class="quill-content text-center text-gray-800 bg-white/95 px-4 py-3 rounded-md shadow-[0_2px_0_rgba(136,136,136,1)] text-base md:text-lg max-h-[40vh] overflow-y-auto"
                    v-html="question.title"
                ></div>
                <h2
                    v-else
                    class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800 text-center px-4 py-3 bg-white/95 shadow-[0_2px_0_rgba(136,136,136,1)] rounded-md w-full"
                >
                    {{ question.title }}
                </h2>

                <div
                    v-if="isTeacherMode"
                    class="mt-1 bg-red-100/90 border-red-500 text-red-700 px-3 py-1.5 rounded-sm text-xs sm:text-sm"
                    :class="$i18n.locale === 'fa' ? 'border-r-4' : 'border-l-4'"
                >
                    <p class="font-medium">⚠️ {{ $t('quizGame.teacherModeWarning') || 'Teacher Mode: You are viewing correct answers.' }}</p>
                </div>
            </div>

            <!-- Question Media (Image/Files) — capped so options always fit -->
            <div class="flex-1 flex justify-center items-center w-full min-h-0">
                <div
                    v-if="questionFiles.length > 0"
                    class="flex justify-center w-full items-center h-full gap-2 sm:gap-4 flex-wrap"
                >
                    <div
                        v-for="(file, index) in questionFiles"
                        :key="index"
                        class="flex justify-center items-center max-w-full max-h-full"
                    >
                        <img
                            :src="getFileUrl(file)"
                            :alt="`Question file ${index + 1}`"
                            class="max-w-full max-h-[45vh] object-contain rounded shadow-lg cursor-zoom-in transition-transform hover:scale-105"
                            @error="handleImageError"
                            @click="$emit('zoom', file)"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Options/Answer Section -->
        <div class="shrink-0 p-2 sm:p-3 md:p-4 bg-black/20 mt-auto">
            <div class="max-w-5xl mx-auto">
                <!-- View-Only Banner -->
                <div v-if="isViewingExistingAttempt && !isTeacherMode" class="mb-4 p-4 bg-amber-50 border-l-4 border-amber-400 rounded-r-lg shadow-sm">
                    <div class="flex items-start gap-3 text-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-700 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <div>
                            <h3 class="font-bold text-amber-900">{{ $t('quizGame.viewingExistingAttempt') || 'Viewing Previous Attempt' }}</h3>
                            <p class="text-sm text-amber-800 mt-1">{{ $t('quizGame.viewingExistingAttemptInfo') || 'You are reviewing answers. To change them, start a new session.' }}</p>
                        </div>
                    </div>
                </div>

                <!-- 1. Multiple Choice -->
                <div v-if="question.type === 'multiple_choice'" class="w-full">
                    <div class="grid grid-cols-2 gap-2 sm:gap-3">
                        <div
                            v-for="(option, index) in parsedOptions"
                            :key="option.id || index"
                            class="group"
                            @click="handleOptionSelect(index)"
                        >
                            <div
                                class="flex items-center justify-between p-2 sm:p-3 rounded-xl transition-all duration-200 border-2 min-h-[3.5rem]"
                                :class="[
                                    isOptionSelected(index)
                                        ? getShapeBg(option.shape) + ' border-transparent text-white shadow-lg'
                                        : (isTeacherMode && option.correct ? 'bg-green-100 border-green-500 text-green-800' : 'bg-white border-transparent text-gray-800 shadow-[0_4px_0_rgba(0,0,0,0.1)] hover:translate-y-[-2px] hover:shadow-md'),
                                    isDisabled ? 'cursor-not-allowed opacity-80' : 'cursor-pointer'
                                ]"
                            >
                                <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-lg shadow-inner"
                                            :class="getShapeContainerClass(option.shape)"
                                        >
                                            <QuizShape :type="option.shape" width="22" height="22" fill="white" />
                                        </div>
                                    </div>
                                    <span class="font-bold text-sm sm:text-base text-start leading-tight truncate">{{ option.text }}</span>
                                </div>
                                <img
                                    v-if="option.image"
                                    :src="getFileUrl(option.image, true)"
                                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg object-cover ms-2 border-2 border-white/20 flex-shrink-0"
                                    @error="handleImageError"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. True/False -->
                <div v-else-if="question.type === 'true_false'" class="w-full">
                    <div class="grid grid-cols-2 gap-2 sm:gap-3">
                        <div
                            v-for="(option, index) in parsedOptions"
                            :key="option.id || index"
                            class="group"
                            @click="handleOptionSelect(index)"
                        >
                            <div
                                class="flex items-center p-2 sm:p-3 rounded-xl transition-all duration-200 border-2 min-h-[3.5rem]"
                                :class="[
                                    isOptionSelected(index)
                                        ? getShapeBg(option.shape) + ' border-transparent text-white shadow-lg'
                                        : (isTeacherMode && option.correct ? 'bg-green-100 border-green-500 text-green-800' : 'bg-white border-transparent text-gray-800 shadow-[0_4px_0_rgba(0,0,0,0.1)] hover:translate-y-[-2px] hover:shadow-md'),
                                    isDisabled ? 'cursor-not-allowed opacity-80' : 'cursor-pointer'
                                ]"
                            >
                                <div class="flex-shrink-0 me-2 sm:me-3">
                                    <div
                                        class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-lg shadow-inner"
                                        :class="getShapeContainerClass(option.shape)"
                                    >
                                        <QuizShape :type="option.shape" width="24" height="24" fill="white" />
                                    </div>
                                </div>
                                <span class="font-black text-base sm:text-lg text-start truncate">{{ option.text }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Sequence (Draggable) -->
                <div v-else-if="question.type === 'sequence'" class="w-full max-w-2xl mx-auto">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white drop-shadow-sm">
                            {{ $t('quizGame.options.sequenceTitle') || 'Drag to reorder' }}
                        </h3>
                    </div>
                    <draggable
                        :list="internalSequence"
                        @change="handleSequenceChange"
                        class="flex flex-col gap-3"
                        item-key="id"
                        v-bind="dragOptions"
                        handle=".handle"
                        :disabled="isDisabled"
                    >
                        <template #item="{ element }">
                            <div
                                class="flex items-center p-3 bg-white rounded-xl border-2 border-transparent shadow-[0_4px_0_rgba(0,0,0,0.1)] transition-all"
                                :class="isDisabled ? 'opacity-80 cursor-not-allowed' : 'hover:translate-y-[-2px] cursor-grab active:cursor-grabbing'"
                            >
                                <div class="handle p-2 mr-2 cursor-move opacity-40 hover:opacity-100 transition-opacity">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                        <circle cx="9" cy="12" r="1" /><circle cx="9" cy="5" r="1" /><circle cx="9" cy="19" r="1" /><circle cx="15" cy="12" r="1" /><circle cx="15" cy="5" r="1" /><circle cx="15" cy="19" r="1" />
                                    </svg>
                                </div>
                                <div class="flex-shrink-0 mr-4">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-lg shadow-inner" :class="getShapeContainerClass(element.shape)">
                                        <QuizShape :type="element.shape" width="24" height="24" fill="white" />
                                    </div>
                                </div>
                                <span class="font-bold text-gray-700 text-lg flex-1">{{ element.text }}</span>
                            </div>
                        </template>
                    </draggable>
                </div>

                <!-- 4. Text/Ordinary Answer -->
                <div v-else-if="question.type === 'ordinary'" class="w-full max-w-lg mx-auto">
                    <div class="relative group">
                        <input
                            type="text"
                            :value="userAnswer"
                            @input="handleInput($event.target.value)"
                            :placeholder="$t('quizGame.options.placeholder') || 'Type your answer...'"
                            :disabled="isDisabled"
                            class="w-full p-6 text-2xl font-bold rounded-2xl bg-white border-4 border-transparent shadow-xl focus:border-blue-400 focus:outline-none text-gray-800 text-center transition-all placeholder-gray-300 disabled:opacity-80 disabled:bg-gray-50"
                        />
                        <div v-if="isTeacherMode" class="mt-4 p-4 bg-green-100 rounded-xl border-2 border-green-200 text-green-800 text-center">
                            <span class="text-xs font-black uppercase tracking-widest block mb-1 opacity-60">Correct Answer</span>
                            <span class="text-xl font-bold">{{ question.short_answer }}</span>
                        </div>
                    </div>
                </div>

                <!-- 5. Slide (Content only) -->
                <div v-else-if="question.type === 'slide'" class="w-full mx-auto">
                    <div class="bg-white rounded-2xl shadow-2xl p-8 max-h-[40vh] overflow-y-auto">
                        <div class="quill-content text-gray-800 text-lg leading-relaxed" v-html="question.slide"></div>
                    </div>
                </div>

                <!-- 6. Essay/Long Answer -->
                <div v-else-if="['essay', 'short_answer', 'long_answer', 'text'].includes(question.type)" class="w-full max-w-3xl mx-auto">
                    <textarea
                        :value="userAnswer"
                        @input="handleInput($event.target.value)"
                        :placeholder="$t('quizGame.options.answerPlaceholder') || 'Enter your detailed answer here...'"
                        :disabled="isDisabled"
                        class="w-full p-6 text-lg rounded-2xl bg-white border-4 border-transparent shadow-xl focus:border-blue-400 focus:outline-none text-gray-800 h-48 transition-all resize-none placeholder-gray-300 disabled:opacity-80"
                    ></textarea>
                    <div v-if="isTeacherMode && (question.long_answer || question.short_answer)" class="mt-4 p-4 bg-green-100 rounded-xl border-2 border-green-200 text-green-800">
                        <span class="text-xs font-black uppercase tracking-widest block mb-1 opacity-60">Correct Answer</span>
                        <div class="text-lg whitespace-pre-line">{{ question.long_answer || question.short_answer }}</div>
                    </div>
                </div>

                <!-- 7. File Upload -->
                <div v-else-if="question.type === 'file_upload'" class="w-full max-w-lg mx-auto">
                    <div 
                        class="bg-white p-8 rounded-2xl border-4 border-dashed border-gray-200 flex flex-col items-center justify-center text-center transition-all hover:border-blue-400 group"
                        :class="isDisabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                    >
                        <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-700">{{ $t('quizGame.options.uploadTitle') || 'Upload your file' }}</p>
                        <p class="text-sm text-gray-400 mt-2">{{ $t('quizGame.options.uploadInfo') || 'Images, PDF or Documents' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import draggable from 'vuedraggable'
import QuizShape from './QuizShape.vue'
import { parseOptions as parseOptionsUtil } from '@/services/apiService'

export default {
    name: 'QuestionViewer',
    components: { draggable, QuizShape },
    props: {
        question: { type: Object, required: true },
        userAnswer: { type: String, default: '' },
        selectedOption: { type: Number, default: null },
        userSequence: { type: Array, default: () => [] },
        isViewingExistingAttempt: { type: Boolean, default: false },
        isTeacherMode: { type: Boolean, default: false },
        isLiveSession: { type: Boolean, default: false },
        optionsToDisplay: { type: Array, default: () => [] } // Can be passed to preserve shuffling
    },
    emits: ['select', 'update:userAnswer', 'update:userSequence', 'zoom', 'image-error'],
    data() {
        return {
            internalSequence: []
        }
    },
    watch: {
        userSequence: {
            handler(val) {
                this.internalSequence = [...val]
            },
            immediate: true,
            deep: true
        },
        'question.id': {
            handler() {
                // Reset sequence when question changes
                if (this.question.type === 'sequence') {
                    const baseOptions = this.optionsToDisplay.length > 0 
                        ? this.optionsToDisplay 
                        : this.parseOptions(this.question.options)
                    this.internalSequence = [...baseOptions]
                }
            }
        }
    },
    computed: {
        parsedOptions() {
            if (this.optionsToDisplay && this.optionsToDisplay.length > 0) {
                return this.optionsToDisplay
            }
            return this.parseOptions(this.question.options)
        },
        questionFiles() {
            const files = this.question.files
            if (!files) return []
            if (Array.isArray(files)) return files
            if (typeof files === 'string') {
                try {
                    const parsed = JSON.parse(files)
                    return Array.isArray(parsed) ? parsed : [files]
                } catch (e) {
                    return [files]
                }
            }
            return []
        },
        isDisabled() {
            return this.isViewingExistingAttempt && !this.isTeacherMode
        },
        dragOptions() {
            return {
                animation: 250,
                ghostClass: 'opacity-0',
                dragClass: 'shadow-2xl'
            }
        }
    },
    methods: {
        isHTML: (str) => str && /<\/?[a-z][\s\S]*>/i.test(str),
        parseOptions(options) {
            return parseOptionsUtil(options)
        },
        handleOptionSelect(index) {
            if (this.isDisabled) return
            this.$emit('select', index)
        },
        handleInput(val) {
            this.$emit('update:userAnswer', val)
        },
        handleSequenceChange() {
            this.$emit('update:userSequence', this.internalSequence)
        },
        isOptionSelected(index) {
            return this.selectedOption === index
        },
        // Match the create-question editor exactly. parseOptions() normalizes the
        // stored "{color}{name}" shape down to the bare name ("redtriangle" ->
        // "triangle"), so map by shape NAME (the same SHAPE_MAP pairing the editor
        // uses), while still tolerating a raw "{color}{name}" value.
        shapeColorClass(shape) {
            const byName = {
                triangle: 'red',
                diamond: 'blue',
                circle: 'yellow',
                rectangle: 'green',
                pentagon: 'pink',
                hexagon: 'purple',
            }
            const s = (shape || '').toLowerCase()
            const name = Object.keys(byName).find((n) => s.includes(n))
            const color = name
                ? byName[name]
                : ['red', 'blue', 'yellow', 'green', 'pink', 'purple'].find((c) => s.includes(c)) || 'blue'
            return `bg-${color}-500`
        },
        getShapeBg(shape) {
            return this.shapeColorClass(shape)
        },
        getShapeContainerClass(shape) {
            return this.shapeColorClass(shape)
        },
        getFileUrl(path, isOption = false) {
            if (!path) return ''
            if (path.startsWith('data:') || path.startsWith('http')) return path
            // Strip backslashes (Windows-stored paths) + leading slashes.
            let cleanPath = path.replace(/\\/g, '/').replace(/^\/+/, '')
            // If the path already starts with 'uploads/' don't prepend it again
            // (was causing /uploads/uploads/... 404s).
            if (cleanPath.startsWith('uploads/')) return `/${cleanPath}`
            // Option images live under uploads/options/, question files under uploads/questions/.
            if (isOption) {
                const fileName = cleanPath.replace(/^options\//, '').split('/').pop()
                return `/uploads/options/${fileName}`
            }
            const fileName = cleanPath.replace(/^questions\//, '').split('/').pop()
            return `/uploads/questions/${fileName}`
        },
        handleImageError(e) {
            this.$emit('image-error', e)
        }
    }
}
</script>

<style scoped>
.quill-content :deep(p) { margin-bottom: 0.5rem; }
.quill-content :deep(img) { max-width: 100%; border-radius: 0.5rem; }
</style>
