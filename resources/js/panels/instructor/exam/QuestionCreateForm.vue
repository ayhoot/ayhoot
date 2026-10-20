<template>
    <div class="bg-gray-50" :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'">
        <!-- Header -->
        <div class="fixed top-0 inset-x-0 z-50">
            <CreateHeader @toggle-sidebar="toggleSidebar" :submitQuestion="submitQuestion" :exitAndSave="exitAndSubmit"
                :exam="exam" :previewExam="previewExam" @update-settings="onUpdateExamSettings" />
        </div>

        <!-- Scientific Editor Modal (teleports to body) -->
        <ScientificEditorModal
            :show="showQuillModal"
            :content="scientificInputTemp"
            :toolbar-options="toolbarOptions"
            :editor-key="editorKey"
            @update:content="scientificInputTemp = $event"
            @save="saveFromQuill"
            @cancel="cancelQuillModal"
        />

        <div class="flex">
            <!-- Left + mobile question lists -->
            <QuestionList
                :questions="questions"
                :selected-id="selectedQuestion ? selectedQuestion.id : null"
                :preview-thumbnail="previewThumbnail"
                @select="loadQuestion"
                @delete="confirmDelete"
                @create="createNewQuestion"
            />

            <!-- Center Box - Question Content -->
            <div class="w-full md:w-4/6 px-4 p-4 md:mb-0 min-h-[calc(100vh-5rem)] md:min-h-[calc(100vh-4rem)] pb-18 md:pb-0 mt-16"
                :style="{
                    backgroundImage: `url(${question.bg || DEFAULT_BACKGROUND})`,
                    backgroundSize: 'cover',
                    backgroundPosition: 'center',
                }">
                <div class="p-4 flex flex-col items-between justify-between w-full h-full">
                    <div class="flex flex-col items-between justify-between h-full">
                        <div>
                            <!-- Question Title -->
                            <div class="flex items-center justify-between gap-2">
                                <div class="w-full">
                                    <input v-if="!isQuillTitleActive" type="text" v-model="question.title"
                                        :placeholder="$t('createQuestion.main.titlePlaceholder')"
                                        class="w-full px-3 py-2 shadow-[0_2px_0_rgba(136,136,136,1)] text-lg bg-white text-center outline-none focus:outline-none rounded-md font-semibold" />
                                    <div v-else @click="openScientificModal"
                                        class="w-full px-3 py-2 shadow-[0_2px_0_rgba(136,136,136,1)] text-lg bg-white text-center outline-none focus:outline-none rounded-md font-semibold select-none cursor-pointer"
                                        v-html="question.title || $t('createQuestion.main.scientificPlaceholder')">
                                    </div>
                                </div>
                                <ResponsiveButton type="light-blue" class="p-3 shrink-0" @click="openScientificModal">
                                    f(x)
                                </ResponsiveButton>
                            </div>
                        </div>

                        <!-- Image Upload Component -->
                        <FileUpload v-if="question.questionType !== 'slide'"
                            :modelValue="{ file: question.selectedFile, preview: question.previewImage }"
                            @update:modelValue="handleMainImageUpdate" />

                        <div class="pb-6">
                            <!-- Dynamic Question Type Components -->
                            <TrueFalse v-if="question.questionType === QUESTION_TYPES.TRUE_FALSE"
                                v-model="question.correctAnswer" />

                            <MultipleChoice v-else-if="question.questionType === QUESTION_TYPES.MULTIPLE_CHOICE"
                                :key="`mc-${question.questionType}-${editorKey}`"
                                :modelValue="{ options: question.options, correctOption: question.correctOption }"
                                @update:modelValue="
                                    (mcData) => {
                                        question.options = mcData.options
                                        question.correctOption = mcData.correctOption
                                    }
                                " :shapeMap="SHAPE_MAP" :getShapeColor="getShapeColor" :getShapeIcon="getShapeIcon"
                                :questionId="selectedQuestion ? selectedQuestion.id : 'new_q'" />

                            <ShortAnswer v-else-if="question.questionType === QUESTION_TYPES.ORDINARY" :modelValue="{
                                shortAnswer: question.shortAnswer,
                                alternativeAnswers: question.alternativeAnswers,
                                caseSensitive: question.caseSensitive,
                            }" @update:modelValue="
                                    (saData) => {
                                        question.shortAnswer = saData.shortAnswer
                                        question.alternativeAnswers = saData.alternativeAnswers
                                        question.caseSensitive = saData.caseSensitive
                                    }
                                " />

                            <Sequence v-else-if="question.questionType === QUESTION_TYPES.SEQUENCE" :modelValue="{
                                sequenceOptions: question.sequenceOptions,
                                allowPartialCredit: question.allowPartialCredit,
                            }" @update:modelValue="
                                    (seqData) => {
                                        question.sequenceOptions = seqData.sequenceOptions
                                        question.allowPartialCredit = seqData.allowPartialCredit
                                    }
                                " :shapeMap="SHAPE_MAP" :getShapeColor="getShapeColor" :getShapeIcon="getShapeIcon" />

                            <SlideContent v-else-if="question.questionType === QUESTION_TYPES.SLIDE"
                                v-model="question.slide" :toolbarOptions="toolbarOptions" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overlay for mobile -->
            <div v-if="isRightSidebarOpen" @click="isRightSidebarOpen = false"
                class="fixed inset-0 bg-black/50 z-40 md:hidden"></div>

            <!-- Right Sidebar (options panel) -->
            <div class="w-4/5 max-w-xs md:w-1/6 bg-white p-4 border-gray-200 mt-16 transition-transform duration-300 ease-in-out fixed top-0 h-full z-50 overflow-y-auto md:relative md:min-h-[calc(100vh-4rem)] md:translate-x-0"
                :class="{
                    'right-0 border-l': $i18n.locale !== 'fa',
                    'left-0 border-r': $i18n.locale === 'fa',
                    'translate-x-0': isRightSidebarOpen,
                    'translate-x-full': $i18n.locale !== 'fa' && !isRightSidebarOpen,
                    '-translate-x-full': $i18n.locale === 'fa' && !isRightSidebarOpen,
                }">
                <QuestionOptionsPanel :question="question" :bg-options="bgOptions" @type-change="onQuestionTypeChange" />
            </div>
        </div>
    </div>
</template>

<script>
import CreateHeader from '@/components/layout/InstructorCreateHeader.vue'
import { apiCall } from '@/services/apiService'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import FileUpload from '@/components/exam/question/FileUpload.vue'
import TrueFalse from '@/components/exam/question/TrueFalse.vue'
import MultipleChoice from '@/components/exam/question/MultipleChoice.vue'
import ShortAnswer from '@/components/exam/question/ShortAnswer.vue'
import Sequence from '@/components/exam/question/Sequence.vue'
import SlideContent from '@/components/exam/question/Slide.vue'
import QuestionList from './question/QuestionList.vue'
import QuestionOptionsPanel from './question/QuestionOptionsPanel.vue'
import ScientificEditorModal from './question/ScientificEditorModal.vue'
import { useConfirm } from '@/composables/useConfirm'
import {
    QUESTION_TYPES,
    SHAPE_MAP,
    DEFAULT_BACKGROUND,
    BACKGROUND_OPTIONS,
    TOOLBAR_OPTIONS,
    getDefaultQuestion,
    createOption,
    createSequenceItem,
    stripHtml,
    getShapeColor,
    getShapeIcon,
    parseApiQuestion,
    buildQuestionFormData,
    validateQuestion,
} from '@/utils/questionModel'

const { confirm: confirmDialog } = useConfirm()

export default {
    name: 'AdminCreateQuestion',
    components: {
        CreateHeader,
        ResponsiveButton,
        FileUpload,
        TrueFalse,
        MultipleChoice,
        ShortAnswer,
        Sequence,
        SlideContent,
        QuestionList,
        QuestionOptionsPanel,
        ScientificEditorModal,
    },
    data() {
        return {
            DEFAULT_BACKGROUND,
            QUESTION_TYPES,
            SHAPE_MAP,
            isRightSidebarOpen: false,
            selectedQuestion: null,
            showQuillModal: false,
            scientificInputTemp: '',
            editorKey: 0,
            previewThumbnail: false,
            questions: [],
            exam: {},
            question: getDefaultQuestion(),
            toolbarOptions: TOOLBAR_OPTIONS,
            bgOptions: BACKGROUND_OPTIONS,
            loading: false,
            isEditMode: false,
        }
    },
    computed: {
        isQuillTitleActive() {
            const htmlTagRegex = /<[^>]*>/g
            return this.question.title && htmlTagRegex.test(this.question.title)
        },
    },
    async mounted() {
        await this.fetchExamDetail()
        if (!this.exam.id) return // guard redirected away
        await this.fetchQuestionsForExam()
        if (this.$route.query.loadFirst && this.questions.length > 0) {
            this.loadQuestion(this.questions[0].id)
        } else {
            this.createNewQuestion()
        }
    },
    methods: {
        // Expose pure helpers to the template (passed as props to child editors).
        getShapeColor,
        getShapeIcon,
        toggleSidebar() {
            this.isRightSidebarOpen = !this.isRightSidebarOpen
        },
        handleMainImageUpdate(imageData) {
            this.question.selectedFile = imageData.file
            this.question.previewImage = imageData.preview
        },
        onQuestionTypeChange() {
            const commonFields = {
                title: this.question.title,
                duration: this.question.duration,
                points: this.question.points,
                bg: this.question.bg,
                difficultyLevel: this.question.difficultyLevel,
                explanation: this.question.explanation,
                category: this.question.category,
                selectedFile: this.question.selectedFile,
                previewImage: this.question.previewImage,
            }
            this.question = { ...getDefaultQuestion(), ...commonFields, questionType: this.question.questionType }
            this.editorKey++

            if (this.question.questionType === QUESTION_TYPES.MULTIPLE_CHOICE && this.question.options.length === 0) {
                this.question.options = [createOption(0), createOption(1)]
                this.question.correctOption = 0
            } else if (this.question.questionType === QUESTION_TYPES.SEQUENCE && this.question.sequenceOptions.length === 0) {
                this.question.sequenceOptions = [createSequenceItem(0), createSequenceItem(1)]
            }
        },
        createNewQuestion() {
            this.selectedQuestion = null
            this.isEditMode = false
            this.question = getDefaultQuestion()
            this.previewThumbnail = false
            this.editorKey++
            if (this.question.questionType === QUESTION_TYPES.MULTIPLE_CHOICE) {
                this.question.options = [createOption(0), createOption(1)]
                this.question.correctOption = 0
            }
        },
        async loadQuestion(questionId) {
            this.loading = true
            try {
                const response = await apiCall('GET', `/api/instructor/question/${questionId}`)
                if (response && response.question) {
                    this.selectedQuestion = { ...response.question }
                    this.isEditMode = true
                    this.question = parseApiQuestion(response.question)
                    this.previewThumbnail = false
                } else {
                    this.$toast.error(this.$t('createQuestion.alerts.loadFailed'))
                }
            } catch (error) {
                console.error('Error fetching question details:', error)
                this.$toast.error(this.$t('createQuestion.alerts.loadFailed'))
            } finally {
                this.loading = false
            }
        },
        async fetchQuestionsForExam() {
            if (!this.exam.id) return
            try {
                const response = await apiCall('GET', `/api/instructor/exam/${this.exam.id}/questions`)
                this.questions = response.questions || []
            } catch (error) {
                console.error('Error fetching questions:', error)
            }
        },
        async fetchExamDetail() {
            try {
                const response = await apiCall('GET', `/api/instructor/exam/${this.$route.params.id}`)
                // Ownership guard: only the exam's owner may create/edit its questions.
                if (response && response.is_owner === false) {
                    this.$toast.error(this.$t('createQuestion.alerts.notOwner'))
                    this.$router.push(`/${this.$role}/exam`)
                    return
                }
                this.exam = response.exam || {}
            } catch (error) {
                console.error('Error fetching exam:', error)
                this.$toast.error(this.$t('createQuestion.alerts.examLoadFailed'))
            }
        },
        async confirmDelete(questionId) {
            if (await confirmDialog({ message: this.$t('createQuestion.alerts.confirmDelete'), variant: 'danger' })) {
                try {
                    await apiCall('GET', `/api/instructor/question/delete/${questionId}`)
                    await this.fetchQuestionsForExam()
                    if (this.selectedQuestion && this.selectedQuestion.id === questionId) {
                        this.createNewQuestion()
                    }
                    this.$toast.success(this.$t('createQuestion.alerts.deleteSuccess'))
                } catch (error) {
                    this.$toast.error(this.$t('createQuestion.alerts.deleteFailed'))
                }
            }
        },
        openScientificModal() {
            this.scientificInputTemp = this.question.title
            this.showQuillModal = true
            this.editorKey++
        },
        cancelQuillModal() {
            this.showQuillModal = false
            this.scientificInputTemp = ''
        },
        saveFromQuill() {
            if (!stripHtml(this.scientificInputTemp).trim() && this.question.questionType !== QUESTION_TYPES.SLIDE) {
                this.$toast.error(this.$t('createQuestion.alerts.titleEmpty'))
                return
            }
            this.question.title = this.scientificInputTemp
            this.showQuillModal = false
        },
        async submitQuestion() {
            if (!this.exam.id) {
                this.$toast.error(this.$t('createQuestion.alerts.missingExamId'))
                return
            }
            const validationError = validateQuestion(this.question, this.$t)
            if (validationError) {
                this.$toast.error(validationError)
                return
            }
            this.loading = true
            const wasEditing = this.isEditMode
            try {
                const payload = buildQuestionFormData(this.question, this.exam.id)
                const endpoint =
                    this.isEditMode && this.selectedQuestion?.id
                        ? `/api/instructor/question/${this.selectedQuestion.id}`
                        : '/api/instructor/question'

                if (this.isEditMode && this.selectedQuestion?.id) {
                    payload.append('_method', 'POST')
                }

                const response = await apiCall('POST', endpoint, payload, { 'Content-Type': 'multipart/form-data' })
                this.$toast.success(this.$t('createQuestion.alerts.saveSuccess'))
                await this.fetchQuestionsForExam()
                if (wasEditing && response && response.question && response.question.id) {
                    this.loadQuestion(response.question.id)
                } else {
                    this.createNewQuestion()
                }
            } catch (error) {
                console.error('Error saving question:', error)
                let errorMessage = this.$t('createQuestion.alerts.saveFailed')
                if (error.response && error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message
                }
                this.$toast.error(errorMessage)
            } finally {
                this.loading = false
            }
        },
        async exitAndSubmit() {
            if (!this.loading) this.$router.push(`/${this.$role}/exam`)
        },
        async previewExam() {
            await this.submitQuestion()
            if (!this.loading) this.$router.push(`/${this.$role}/exam/${this.exam.id}/preview`)
        },
        onUpdateExamSettings(updatedExam) {
            if (updatedExam) {
                this.exam = { ...this.exam, ...updatedExam }
            }
        },
    },
}
</script>
