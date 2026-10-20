<template>
    <div class="h-screen w-screen flex flex-col overflow-y-auto font-sans" :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'">
        <!-- Loading State -->
        <div v-if="loading" class="h-full w-full flex items-center justify-center">
            <h2 class="text-2xl font-bold">{{ $t('preview.loading') }}</h2>
        </div>

        <!-- Empty State -->
        <div v-else-if="questions.length === 0" class="h-full w-full flex items-center justify-center">
            <h2 class="text-2xl font-bold">{{ $t('preview.noQuestions') }}</h2>
        </div>

        <!-- Quiz Container -->
        <div v-else class="h-full w-full flex flex-col relative">
            <!-- Background -->
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat z-0" :style="getBackgroundStyle()">
                <div class="absolute inset-0 bg-black/10"></div>
            </div>

            <div class="relative z-10 flex flex-col h-full w-full text-white">
                <!-- Header -->
                <QuizHeader
                    :title="currentQuestion.exam?.title"
                    :currentIndex="currentIndex"
                    :totalQuestions="questions.length"
                    :isTeacherMode="true"
                    :showNavigationButtons="true"
                    :isPreviousDisabled="currentIndex === 0"
                    :isNextDisabled="currentIndex === questions.length - 1"
                    :isLastQuestion="currentIndex === questions.length - 1"
                    @edit="exitPreview"
                    @previous="previousQuestion"
                    @next="nextQuestion"
                />

                <!-- Main container -->
                <div class="flex-1 flex flex-col min-h-0">
                    <QuestionViewer
                        :question="currentQuestion"
                        :isTeacherMode="true"
                        @zoom="zoomImage"
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

                <!-- Image Zoom Modal -->
                <transition name="fade">
                    <div
                        v-if="zoomedImageUrl"
                        @click="unzoomImage"
                        class="fixed inset-0 z-[999] flex items-center justify-center bg-black/70 cursor-zoom-out"
                    >
                        <img
                            :src="zoomedImageUrl"
                            class="max-w-[90vw] max-h-[90vh] object-contain rounded-lg shadow-2xl"
                        />
                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>

<script>
import { apiCall, parseOptions as parseOptionsUtil } from '@/services/apiService'
import { getRole } from '@/utils/role'

// Modular Components
import QuizHeader from '@/components/quiz/QuizHeader.vue'
import QuestionViewer from '@/components/quiz/QuestionViewer.vue'

export default {
    name: 'InstructorAyhootPreview',
    components: {
        QuizHeader,
        QuestionViewer
    },
    data() {
        return {
            questions: [],
            currentIndex: 0,
            loading: true,
            zoomedImageUrl: null,
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
        currentQuestionFiles() {
            if (!this.currentQuestion.files) return []
            try {
                const files = typeof this.currentQuestion.files === 'string' 
                    ? JSON.parse(this.currentQuestion.files) 
                    : this.currentQuestion.files
                return Array.isArray(files) ? files : []
            } catch (e) {
                return []
            }
        }
    },
    async mounted() {
        await this.checkAccessAndFetch()
    },
    methods: {
        async checkAccessAndFetch() {
            this.loading = true
            try {
                const examResponse = await apiCall('GET', `/api/instructor/exam/${this.$route.params.id}`)
                if (examResponse.exam.monetize === 'paid' && !examResponse.has_access) {
                    this.$router.replace(`/${getRole()}/ayhoot/${this.$route.params.id}`)
                    return
                }
                await this.fetchQuestions()
            } catch (error) {
                console.error('Error checking access:', error)
                this.$router.replace(`/${getRole()}/ayhoot/${this.$route.params.id}`)
            } finally {
                this.loading = false
            }
        },
        async fetchQuestions() {
            this.loading = true
            try {
                const response = await apiCall('GET', `/api/instructor/exam/${this.$route.params.id}/questions`)
                this.questions = response.questions || []
                this.initializeQuestion()
            } catch (error) {
                console.error('Error fetching questions:', error)
            } finally {
                this.loading = false
            }
        },
        initializeQuestion() {
            // Handled by QuestionViewer
        },
        previousQuestion() {
            if (this.currentIndex > 0) {
                this.currentIndex--
                this.initializeQuestion()
            }
        },
        nextQuestion() {
            if (this.currentIndex < this.questions.length - 1) {
                this.currentIndex++
                this.initializeQuestion()
            }
        },
        zoomImage(file) {
            this.zoomedImageUrl = this.getFileUrl(file)
        },
        unzoomImage() {
            this.zoomedImageUrl = null
        },
        getFileUrl(filePath) {
            if (!filePath) return ''
            if (filePath.startsWith('http')) return filePath
            return `/uploads/questions/${filePath.replace(/\\/g, '').replace(/^\//, '')}`
        },
        getBackgroundStyle() {
             if (this.currentQuestion && this.currentQuestion.q_options) {
                try {
                    const options = JSON.parse(this.currentQuestion.q_options)
                    if (options.background_image) {
                        return { backgroundImage: `url('${options.background_image}')` }
                    }
                } catch (e) {}
            }
            return { backgroundImage: `url('/imgHolders/bg3.jpg')` }
        },
        exitPreview() {
            this.$router.push(`/${getRole()}/ayhoot/${this.$route.params.id}`)
        }
    }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
.cursor-zoom-out {
    cursor: zoom-out;
}
</style>
