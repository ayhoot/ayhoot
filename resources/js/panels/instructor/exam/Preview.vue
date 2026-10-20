<template>
    <div
        class="h-screen w-screen flex flex-col overflow-y-auto font-sans"
        :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'"
        :class="{ 'font-payda': $i18n.locale === 'fa' }"
    >
        <div v-if="loading" class="h-full w-full flex items-center justify-center">
            <h2 class="text-2xl font-bold">{{ $t('preview.loading') }}</h2>
        </div>

        <div v-else-if="questions.length === 0" class="h-full w-full flex items-center justify-center">
            <h2 class="text-2xl font-bold">{{ $t('preview.noQuestions') }}</h2>
        </div>

        <div v-else class="h-full w-full flex flex-col relative">
            <!-- Background image -->
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat z-0" :style="getBackgroundStyle()">
                <div class="absolute inset-0 bg-black/10"></div>
            </div>

            <!-- Content container -->
            <div class="relative z-10 flex flex-col h-full w-full text-white">
                <!-- Header -->
                <div class="flex items-center justify-between p-2 px-4 sm:px-8 bg-white/80 text-gray-800">
                    <div
                        class="flex justify-between items-center w-full"
                        :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }"
                    >
                        <div class="flex items-center gap-2" :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }">
                            <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M15.0001 2.3999V5.9999C15.0001 6.66264 15.5373 7.1999 16.2001 7.1999H19.8001M8.40008 7.1999H10.8001M8.40008 10.7999H15.6001M8.40008 14.3999H15.6001M18.0001 4.1999C17.466 3.72204 16.9118 3.15528 16.5619 2.78718C16.3291 2.54224 16.0074 2.3999 15.6695 2.3999H6.5998C5.27432 2.3999 4.19981 3.47441 4.1998 4.79989L4.19971 19.1998C4.1997 20.5253 5.27421 21.5998 6.5997 21.5998L17.3997 21.5999C18.7252 21.5999 19.7997 20.5254 19.7998 19.2L19.8001 6.47773C19.8001 6.1709 19.683 5.87594 19.4701 5.65503C19.0763 5.24655 18.4187 4.57442 18.0001 4.1999Z"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <h1 class="text-xl md:text-2xl font-bold text-center">
                                {{ currentQuestion.exam?.title || $t('preview.header.defaultTitle') }}
                            </h1>
                        </div>
                        <div
                            class="flex justify-between items-center gap-2"
                            :class="{ 'flex-row-reverse': $i18n.locale === 'fa' }"
                        >
                            <span
                                class="font-bold bg-[#4285F4] shadow-[0_2px_0_rgba(52,80,174,1)] rounded-sm p-2 text-sm sm:text-base"
                            >
                                {{ currentIndex + 1 }}/{{ questions.length }}
                            </span>
                            <button
                                @click="backTo"
                                class="hidden sm:block px-4 py-2 bg-[#EBCD1F] hover:bg-[#8A7812] shadow-[0_2px_0_rgba(138,121,18,1)] hover:shadow-none hover:translate-y-[2px] rounded-sm font-bold text-base transition-all duration-200 text-white cursor-pointer"
                            >
                                {{ $t('preview.header.exit') }}
                            </button>
                            <button
                                @click="backTo"
                                class="block sm:hidden p-2 bg-[#EBCD1F] hover:bg-[#8A7812] shadow-[0_2px_0_rgba(138,121,18,1)] hover:shadow-none hover:translate-y-[2px] rounded-sm transition-all duration-200 text-white cursor-pointer"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"
                                    />
                                    <path
                                        fill-rule="evenodd"
                                        d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                            <button
                                @click="previousQuestion"
                                :disabled="currentIndex === 0"
                                class="hidden sm:block px-4 py-2 bg-[#3450AE] hover:bg-[#152B65] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px] rounded-sm font-bold text-base transition-all duration-200 text-white disabled:bg-gray-400 disabled:cursor-not-allowed cursor-pointer"
                            >
                                {{ $t('preview.header.prev') }}
                            </button>
                            <button
                                @click="previousQuestion"
                                :disabled="currentIndex === 0"
                                class="block sm:hidden p-2 bg-[#3450AE] hover:bg-[#152B65] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px] rounded-sm transition-all duration-200 text-white disabled:bg-gray-400 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                            <button
                                @click="nextQuestion"
                                :disabled="currentIndex === questions.length - 1"
                                class="hidden sm:block px-4 py-2 bg-[#3450AE] hover:bg-[#152B65] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px] rounded-sm font-bold text-base transition-all duration-200 text-white disabled:bg-gray-400 disabled:cursor-not-allowed cursor-pointer"
                            >
                                {{ nextButtonText }}
                            </button>
                            <button
                                @click="nextQuestion"
                                :disabled="currentIndex === questions.length - 1"
                                class="block sm:hidden p-2 bg-[#3450AE] hover:bg-[#152B65] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px] rounded-sm transition-all duration-200 text-white disabled:bg-gray-400 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="flex-1 flex flex-col min-h-0">
                    <QuestionViewer
                        :question="currentQuestion"
                        :isTeacherMode="true"
                        @zoom="isImageExpanded = true"
                        @image-error="handleImageError"
                    />

                    <!-- Progress Bar -->
                    <div class="z-100">
                        <div class="h-2 bg-gray-700/50 overflow-hidden">
                            <div
                                class="h-full bg-blue-500 transition-all duration-300"
                                :style="{ width: progressPercentage + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div
                v-if="isImageExpanded && currentQuestionFiles.length > 0"
                @click="closeImage"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4 cursor-pointer backdrop-blur-sm"
            >
                <img
                    :src="getFileUrl(currentQuestionFiles[0])"
                    alt="Expanded question image"
                    class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
                    @click.stop
                />
            </div>
        </Teleport>
    </div>
</template>

<script>
import { apiCall, parseOptions as parseOptionsUtil } from '@/services/apiService'

import QuestionViewer from '@/components/quiz/QuestionViewer.vue'

const SHAPE_MAP = [
    { name: 'triangle', color: 'red', icon: 'triangle' },
    { name: 'diamond', color: 'blue', icon: 'diamond' },
    { name: 'circle', color: 'yellow', icon: 'circle' },
    { name: 'rectangle', color: 'green', icon: 'rectangle' },
    { name: 'pentagon', color: 'pink', icon: 'pentagon' },
    { name: 'hexagon', color: 'purple', icon: 'hexagon' },
]

export default {
    name: 'PreviewExam',
    components: { QuestionViewer },
    data() {
        return {
            questions: [],
            currentIndex: 0,
            loading: true,
            isImageExpanded: false,
        }
    },
    computed: {
        currentQuestion() {
            return this.questions[this.currentIndex] || { exam: {} }
        },
        progressPercentage() {
            if (this.questions.length === 0) return 0
            return ((this.currentIndex + 1) / this.questions.length) * 100
        },
        nextButtonText() {
            return this.currentIndex === this.questions.length - 1
                ? this.$t('preview.header.endOfPreview')
                : this.$t('preview.header.next')
        },
        currentQuestionFiles() {
            if (!this.currentQuestion.files) return []
            try {
                const files = JSON.parse(this.currentQuestion.files)
                return Array.isArray(files) ? files : []
            } catch (e) {
                return []
            }
        },
        alternativeAnswers() {
            if (!this.currentQuestion.alternative_answers) return []
            try {
                return JSON.parse(this.currentQuestion.alternative_answers)
            } catch (e) {
                return []
            }
        },
        correctSequence() {
            if (this.currentQuestion.type !== 'sequence') return []
            const options = this.parseOptions(this.currentQuestion.options)
            return [...options].sort((a, b) => a.order - b.order)
        },
    },
    async created() {
        await this.fetchQuestions()
        if (this.questions.length > 0) {
            this.preloadAssets()
        }
    },
    methods: {
        expandImage() {
            this.isImageExpanded = true
        },
        closeImage() {
            this.isImageExpanded = false
        },
        isHTML: (content) => content && /<\/?[a-z][\s\S]*>/i.test(content),
        async fetchQuestions() {
            this.loading = true
            try {
                const response = await apiCall('GET', `/api/instructor/exam/${this.$route.params.id}/questions`)
                this.questions = response.questions
            } catch (error) {
                console.error('Error fetching questions:', error)
            } finally {
                this.loading = false
            }
        },
        preloadAssets() {
            this.questions.forEach((question) => {
                if (question.q_options) {
                    try {
                        const options = JSON.parse(question.q_options)
                        if (options.background_image) {
                            new Image().src = this.getFileUrl(options.background_image)
                        }
                    } catch (e) {
                        /* silent fail */
                    }
                }
                if (question.files) {
                    try {
                        const files = JSON.parse(question.files)
                        if (Array.isArray(files)) {
                            files.forEach((file) => (new Image().src = this.getFileUrl(file)))
                        }
                    } catch (e) {
                        /* silent fail */
                    }
                }
            })
        },
        parseOptions(options) {
            return parseOptionsUtil(options)
        },
        previousQuestion() {
            if (this.currentIndex > 0) {
                this.currentIndex--
            }
        },
        nextQuestion() {
            if (this.currentIndex < this.questions.length - 1) {
                this.currentIndex++
            }
        },
        backTo() {
            this.$router.back()
        },
        getBackgroundStyle() {
            if (this.currentQuestion && this.currentQuestion.q_options) {
                try {
                    const options = JSON.parse(this.currentQuestion.q_options)
                    if (options.background_image) {
                        return { backgroundImage: `url('${options.background_image}')` }
                    }
                } catch (e) {
                    console.error('Error parsing q_options:', e)
                }
            }
            return {}
        },
        getFileUrl(filePath) {
            if (!filePath) return ''
            if (filePath.startsWith('data:image')) {
                return filePath
            }
            const cleanPath = filePath.replace(/\\/g, '/')
            
            // If it's already a full path starting with /uploads, check if it's an option image
            if (cleanPath.startsWith('/uploads')) {
                // Check if it's an option image but missing the options/ subdirectory
                const pathWithoutPrefix = cleanPath.replace(/^\/uploads\//, '')
                if ((pathWithoutPrefix.startsWith('option_') || pathWithoutPrefix.startsWith('tf_option_')) && !pathWithoutPrefix.startsWith('options/')) {
                    return `/uploads/options/${pathWithoutPrefix}`
                }
                return cleanPath
            }
            
            // If it's a relative path, check if it's an option image
            const pathWithoutSlash = cleanPath.replace(/^\//, '')
            if ((pathWithoutSlash.startsWith('option_') || pathWithoutSlash.startsWith('tf_option_')) && !pathWithoutSlash.startsWith('options/')) {
                return `/uploads/options/${pathWithoutSlash}`
            }
            
            // If it already has options/ prefix, use it as is
            if (pathWithoutSlash.startsWith('options/')) {
                return `/uploads/${pathWithoutSlash}`
            }
            
            return `/uploads/${pathWithoutSlash}`
        },
        handleImageError(event) {
            console.error('Image failed to load:', event.target.src)
            event.target.style.display = 'none'
        },
        getShapeColor: (shapeName) => SHAPE_MAP.find((s) => shapeName?.includes(s.name))?.color || 'gray',
        getShapeContainerClass(shapeName) {
            const color = this.getShapeColor(shapeName)
            return `bg-${color}-500 rounded-sm`
        },
        getShapeSvgComponent(shapeName) {
            const shape = SHAPE_MAP.find((s) => shapeName?.includes(s.name))
            if (!shape) return 'RectangleSvg'
            switch (shape.icon) {
                case 'triangle':
                    return 'TriangleSvg'
                case 'diamond':
                    return 'DiamondSvg'
                case 'circle':
                    return 'CircleSvg'
                case 'rectangle':
                    return 'RectangleSvg'
                case 'pentagon':
                    return 'PentagonSvg'
                case 'hexagon':
                    return 'HexagonSvg'
                default:
                    return 'RectangleSvg'
            }
        },
    },
}
</script>

<style>
.font-payda {
    font-family: 'Peyda', sans-serif !important;
}
.quill-content h1,
.quill-content h2,
.quill-content h3 {
    font-size: revert;
    font-weight: revert;
}
.quill-content p {
    font-size: revert;
}
.quill-content ol,
.quill-content ul {
    list-style: revert;
    margin: revert;
    padding: revert;
}
.quill-content .ql-align-center {
    text-align: center;
}
.quill-content .ql-align-right {
    text-align: right;
}
.quill-content .ql-align-left {
    text-align: left;
}
.h-30 {
    height: 7.5rem;
}
.w-30 {
    width: 7.5rem;
}
</style>
