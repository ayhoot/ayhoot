<template>
    <div class="bg-gray-50">
        <!-- Header -->
        <div class="fixed top-0 inset-x-0 z-50">
            <CreateHeader
                :toggle-sidebar="toggleSidebar"
                :submitQuestion="submitQuestion"
                :exitAndSave="exitAndSubmit"
                :exam="exam"
                :previewExam="previewExam"
            />
        </div>

        <div class="flex">
            <!-- Left Sidebar - Question List -->
            <div
                class="w-full md:w-1/6 min-h-[calc(100vh-4rem)] bg-white overflow-y-auto p-4 border-r border-gray-200 hidden md:block mt-16"
            >
                <div v-if="questions.length">
                    <div
                        v-for="(q_item, index) in questions"
                        :key="q_item.id"
                        @click="loadQuestion(q_item.id)"
                        class="py-1 flex items-center justify-between cursor-pointer"
                    >
                        <button
                            class="pr-2 text-red-400 hover:text-red-700 shrink-0"
                            @click.stop="confirmDelete(q_item.id)"
                        >
                            <!-- Added .stop -->
                            <DashLilRemove />
                        </button>
                        <div
                            :class="[
                                'w-full rounded-sm border-2 flex flex-col space-y-1 p-2 py-2 cursor-pointer hover:bg-gray-100 transition-all',
                                selectedQuestion?.id === q_item.id
                                    ? 'border-dashed border-blue-300 bg-blue-100'
                                    : 'border-dashed border-gray-200',
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <span class="text-xs px-1 font-semibold text-gray-500">{{ index + 1 }} -</span>
                                    <span class="text-xs font-semibold truncate max-w-[16ch] text-gray-500">
                                        {{ stripHtml(q_item.title) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-1 items-center">
                                <span
                                    class="text-[0.6rem] px-1 rounded-full border"
                                    :class="getBadgeStyle(q_item.type)"
                                >
                                    {{ getBadgeLabel(q_item.type) }}
                                </span>
                                <span
                                    class="text-[0.6rem] px-1 rounded-full border bg-gray-100 text-gray-400"
                                    v-if="q_item.type !== 'slide'"
                                >
                                    {{ q_item.duration }}s
                                </span>
                                <span
                                    class="text-[0.6rem] px-1 rounded-full border-gray-800 border bg-gray-500 text-white"
                                    v-if="q_item.type !== 'slide' && q_item.difficulty_level"
                                >
                                    {{ q_item.difficulty_level }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Empty State & Preview Item ... (kept as is) -->
                <div v-if="questions.length === 0" class="py-1 flex items-center justify-between cursor-pointer">
                    <button class="pr-2 text-white"><DashLilRemove /></button>
                    <div
                        class="w-full rounded-sm border-2 flex flex-col p-2 py-4 cursor-pointer transition-all marching-ants border-dashed border-gray-200"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex justify-center w-full items-center">
                                <span class="text-xs font-semibold text-center w-full text-gray-200"
                                    >Add your first question</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="previewThumbnail" class="py-1 flex items-center justify-between cursor-pointer">
                    <button class="pr-2 text-white"><DashLilRemove /></button>
                    <div
                        class="w-full rounded-sm border-2 flex flex-col p-2 py-4 cursor-pointer transition-all marching-ants border-dashed border-gray-200"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex justify-center w-full items-center">
                                <span class="text-xs font-semibold text-center w-full text-gray-200"
                                    >Save for preview</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pl-6">
                    <ResponsiveButton type="light-blue" class="w-full p-1.5 mt-2" @click="createNewQuestion">
                        Add Question
                    </ResponsiveButton>
                </div>
            </div>

            <!-- Center Box - Question Content -->
            <div
                class="w-full md:w-4/6 px-4 p-4 md:mb-0 min-h-[calc(100vh-5rem)] md:min-h-[calc(100vh-4rem)] pb-18 md:pb-0 mt-16"
                :style="{
                    backgroundImage: `url(${question.bg || DEFAULT_BACKGROUND})`,
                    backgroundSize: 'cover',
                    backgroundPosition: 'center',
                }"
            >
                <div class="p-4 flex flex-col items-between justify-between w-full h-full">
                    <div class="flex flex-col items-between justify-between h-full">
                        <div>
                            <!-- Question Title -->
                            <div class="flex items-center justify-between gap-2">
                                <div class="w-full">
                                    <input
                                        v-if="!isQuillTitleActive"
                                        type="text"
                                        v-model="question.title"
                                        placeholder="Start typing your question here..."
                                        class="w-full px-3 py-2 shadow-[0_2px_0_rgba(136,136,136,1)] text-lg bg-white text-center outline-none focus:outline-none rounded-md font-semibold"
                                    />
                                    <div
                                        v-else
                                        @click="openScientificModal"
                                        class="w-full px-3 py-2 shadow-[0_2px_0_rgba(136,136,136,1)] text-lg bg-white text-center outline-none focus:outline-none rounded-md font-semibold select-none cursor-pointer"
                                        v-html="question.title || 'Click to edit with Scientific Input'"
                                    ></div>
                                </div>
                                <ResponsiveButton type="light-blue" class="p-3 shrink-0" @click="openScientificModal">
                                    f(x)
                                </ResponsiveButton>
                            </div>

                            <!-- Scientific Editor Modal -->
                            <Teleport to="body">
                                <div
                                    v-if="showQuillModal"
                                    class="fixed inset-0 z-[100] bg-black/50 flex items-center justify-center px-4 md:px-0"
                                >
                                    <div class="bg-white rounded-lg p-6 w-full max-w-3xl shadow-xl">
                                        <h2 class="text-xl font-bold mb-4">Scientific Input for Title</h2>
                                        <QuillEditor
                                            :key="editorKey"
                                            v-model:content="scientificInputTemp"
                                            contentType="html"
                                            theme="snow"
                                            class="w-full bg-white min-h-[200px]"
                                            :toolbar="toolbarOptions"
                                        />
                                        <div class="mt-6 flex justify-end gap-2">
                                            <ResponsiveButton
                                                type="light-gray"
                                                @click="cancelQuillModal"
                                                class="p-2 px-4"
                                                >Cancel</ResponsiveButton
                                            >
                                            <ResponsiveButton type="light-green" class="p-2 px-4" @click="saveFromQuill"
                                                >Save</ResponsiveButton
                                            >
                                        </div>
                                    </div>
                                </div>
                            </Teleport>
                        </div>

                        <!-- Image Upload Component -->
                        <FileUpload
                            v-if="question.questionType !== 'slide'"
                            :modelValue="{ file: question.selectedFile, preview: question.previewImage }"
                            @update:modelValue="handleMainImageUpdate"
                        />

                        <div class="pb-6">
                            <!-- Dynamic Question Type Components -->
                            <TrueFalse
                                v-if="question.questionType === QUESTION_TYPES.TRUE_FALSE"
                                v-model="question.correctAnswer"
                            />

                            <MultipleChoice
                                v-else-if="question.questionType === QUESTION_TYPES.MULTIPLE_CHOICE"
                                :modelValue="{ options: question.options, correctOption: question.correctOption }"
                                @update:modelValue="
                                    (mcData) => {
                                        question.options = mcData.options
                                        question.correctOption = mcData.correctOption
                                    }
                                "
                                :shapeMap="SHAPE_MAP"
                                :getShapeColor="getShapeColor"
                                :getShapeIcon="getShapeIcon"
                                :questionId="selectedQuestion ? selectedQuestion.id : 'new_q'"
                            />

                            <ShortAnswer
                                v-else-if="question.questionType === QUESTION_TYPES.ORDINARY"
                                :modelValue="{
                                    shortAnswer: question.shortAnswer,
                                    alternativeAnswers: question.alternativeAnswers,
                                    caseSensitive: question.caseSensitive,
                                }"
                                @update:modelValue="
                                    (saData) => {
                                        question.shortAnswer = saData.shortAnswer
                                        question.alternativeAnswers = saData.alternativeAnswers
                                        question.caseSensitive = saData.caseSensitive
                                    }
                                "
                            />

                            <Sequence
                                v-else-if="question.questionType === QUESTION_TYPES.SEQUENCE"
                                :modelValue="{
                                    sequenceOptions: question.sequenceOptions,
                                    allowPartialCredit: question.allowPartialCredit,
                                }"
                                @update:modelValue="
                                    (seqData) => {
                                        question.sequenceOptions = seqData.sequenceOptions
                                        question.allowPartialCredit = seqData.allowPartialCredit
                                    }
                                "
                                :shapeMap="SHAPE_MAP"
                                :getShapeColor="getShapeColor"
                                :getShapeIcon="getShapeIcon"
                            />

                            <SlideContent
                                v-else-if="question.questionType === QUESTION_TYPES.SLIDE"
                                v-model="question.slide"
                                :toolbarOptions="toolbarOptions"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Box - Additional Options -->
            <div
                class="w-full md:w-1/6 min-h-[calc(100vh-4rem)] bg-white p-4 border-l border-gray-200 hidden md:block mt-16"
            >
                <div class="rounded-lg">
                    <!-- Question Type Selection -->
                    <div class="mb-4">
                        <AySelect
                            :model-value="question.questionType"
                            label="Question Type"
                            @update:model-value="
                                (v) => {
                                    question.questionType = v
                                    onQuestionTypeChange()
                                }
                            "
                        >
                            <option value="multiple_choice">Quiz</option>
                            <option value="true_false">True or False</option>
                            <option value="ordinary">Short Answer</option>
                            <option value="sequence">Puzzle</option>
                            <option value="slide">Slide</option>
                        </AySelect>
                    </div>
                    <!-- Duration, Points, Difficulty, Background ... (kept mostly as is) -->
                    <div class="mb-4" v-if="question.questionType !== 'slide'">
                        <AySelect v-model="question.duration" label="Duration (seconds)">
                            <option value="10">10s</option>
                            <option value="20">20s</option>
                            <option value="30">30s</option>
                            <option value="40">40s</option>
                            <option value="50">50s</option>
                            <option value="60">1m</option>
                            <option value="120">2m</option>
                            <option value="180">3m</option>
                            <option value="240">4m</option>
                        </AySelect>
                    </div>
                    <div class="mb-4" v-if="question.questionType !== 'slide'">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Points</label>
                        <select
                            v-model="question.points"
                            class="w-full px-3 py-3 border-2 border-gray-300 cursor-pointer rounded-sm font-medium focus:outline-none sm:text-sm"
                        >
                            <option :value="0">No Point</option>
                            <option :value="5">Standard</option>
                            <option :value="10">Double Point</option>
                        </select>
                    </div>
                    <div class="mb-4" v-if="question.questionType !== 'slide'">
                        <AySelect v-model="question.difficultyLevel" label="Difficulty Level">
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </AySelect>
                    </div>
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold mb-2">Select Background</h3>
                        <div class="grid grid-cols-3 gap-2 pr-1">
                            <img
                                v-for="(img, idx) in bgOptions"
                                :key="idx"
                                :src="img"
                                class="w-full h-12 object-cover rounded cursor-pointer border-2 hover:border-blue-500 transition"
                                :class="{
                                    'border-blue-500': question.bg === img,
                                    'border-transparent': question.bg !== img,
                                }"
                                @click="selectBackground(img)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Question List (kept as is, but ensure data bindings are correct) -->
        <div
            class="fixed bottom-0 left-0 w-full bg-white/80 border-t border-gray-400 backdrop-blur z-40 block md:hidden"
        >
            <div class="flex items-center overflow-x-auto px-4 py-4 gap-2 scroll-x-no-expand">
                <div
                    v-for="(q_item, index) in questions"
                    :key="q_item.id"
                    @click="loadQuestion(q_item.id)"
                    class="min-w-[160px] flex-shrink-0 p-2 rounded-md border cursor-pointer text-xs bg-white hover:bg-gray-100"
                    :class="selectedQuestion?.id === q_item.id ? 'border-blue-400 bg-blue-100' : 'border-gray-300'"
                >
                    <div class="flex items-center justify-between">
                        <div class="font-semibold truncate max-w-[12ch]">
                            {{ index + 1 }} - {{ stripHtml(q_item.title) }}
                        </div>
                        <button class="text-red-400 hover:text-red-700 shrink-0" @click.stop="confirmDelete(q_item.id)">
                            <DashLilRemove />
                        </button>
                    </div>
                    <div class="flex flex-wrap items-center gap-1 mt-1">
                        <span class="text-[0.6rem] px-1 border rounded" :class="getBadgeStyle(q_item.type)">
                            {{ getBadgeLabel(q_item.type) }}
                        </span>
                        <span
                            v-if="q_item.type !== 'slide'"
                            class="text-[0.6rem] px-1 border rounded bg-gray-100 text-gray-500"
                        >
                            {{ q_item.duration }}s
                        </span>
                    </div>
                </div>
                <div class="min-w-[40px] flex-shrink-0">
                    <ResponsiveButton type="light-blue" class="px-4 py-2 text-2xl" @click="createNewQuestion"
                        >+</ResponsiveButton
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import CreateHeader from '@/components/layout/AdminCreateHeader.vue'
import { apiCall } from '@/services/apiService'
import DashLilRemove from '@/components/atoms/icons/DashLilRemove.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'

// Import Child Components
import FileUpload from './FileUpload.vue'
import TrueFalse from './TrueFalse.vue'
import MultipleChoice from './MultipleChoice.vue'
import ShortAnswer from './ShortAnswer.vue'
import Sequence from './Sequence.vue'
import SlideContent from './Slide.vue'
import { useConfirm } from '@/composables/useConfirm'

// Constants (Keep these in the parent)
const QUESTION_TYPES = {
    MULTIPLE_CHOICE: 'multiple_choice',
    TRUE_FALSE: 'true_false',
    ORDINARY: 'ordinary',
    SEQUENCE: 'sequence',
    SLIDE: 'slide',
}
const SHAPE_MAP = [
    { name: 'triangle', color: 'red', icon: 'triangle' },
    { name: 'diamond', color: 'blue', icon: 'diamond' },
    { name: 'circle', color: 'yellow', icon: 'circle' },
    { name: 'rectangle', color: 'green', icon: 'rectangle' },
    { name: 'pentagon', color: 'pink', icon: 'pentagon' },
    { name: 'hexagon', color: 'purple', icon: 'hexagon' },
]
const DEFAULT_BACKGROUND = '/imgHolders/blue.jpg'
const BACKGROUND_OPTIONS = [
    '/imgHolders/blue.jpg',
    '/imgHolders/green.jpg',
    '/imgHolders/orange.jpg',
    '/imgHolders/pink.jpg',
    '/imgHolders/purple.jpg',
    '/imgHolders/yellow.jpg',
]

const getDefaultQuestion = () => ({
    questionType: QUESTION_TYPES.MULTIPLE_CHOICE,
    title: '',
    duration: 20,
    points: 5,
    previewImage: null,
    selectedFile: null, // For main question image
    bg: DEFAULT_BACKGROUND,
    // Type-specific fields
    correctAnswer: 'true', // For True/False
    options: [],
    correctOption: null, // For Multiple Choice
    shortAnswer: '',
    alternativeAnswers: [],
    caseSensitive: false, // For Short Answer
    sequenceOptions: [],
    allowPartialCredit: true, // For Sequence (allowPartialCredit boolean)
    slide: '', // For Slide
    explanation: '',
    difficultyLevel: 'medium',
    category: '',
    // Internal state or temporary fields
    files: [], // For payload, stores File objects from main image upload
    originalMainImageFile: null, // To track if main image changed
})

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

export default {
    name: 'AdminCreateQuestion',
    components: {
        CreateHeader,
        DashLilRemove,
        ResponsiveButton,
        FileUpload,
        TrueFalse,
        MultipleChoice,
        ShortAnswer,
        Sequence,
        SlideContent,
        AySelect,
    },
    data() {
        return {
            DEFAULT_BACKGROUND, // Make it available in template
            QUESTION_TYPES,
            SHAPE_MAP, // Make constants available

            isSidebarOpen: false,
            selectedQuestion: null, // Stores the loaded question object from API for comparison/ID

            showQuillModal: false,
            scientificInputTemp: '', // Temp for title quill editor
            editorKey: 0, // To re-render Quill if needed

            previewThumbnail: false, // For the "Save for preview" placeholder in left sidebar
            questions: [], // List of all questions in the exam
            exam: {}, // Current exam details

            question: getDefaultQuestion(), // Current question being edited

            toolbarOptions: [
                /* ... your toolbar options ... */ [{ header: [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ script: 'sub' }, { script: 'super' }],
                [{ indent: '-1' }, { indent: '+1' }],
                [{ direction: 'rtl' }],
                [{ color: [] }, { background: [] }],
                [{ align: [] }],
                ['clean'],
                ['link', 'image', 'video'],
            ],
            bgOptions: BACKGROUND_OPTIONS,
            loading: false,
            isEditMode: false, // To know if we are editing an existing q or creating new
        }
    },
    computed: {
        isQuillTitleActive() {
            // Determine if title should be treated as Quill content
            // This could be based on a flag or by detecting HTML in question.title
            const htmlTagRegex = /<[^>]*>/g
            return this.question.title && htmlTagRegex.test(this.question.title)
        },
    },
    async mounted() {
        await this.fetchExamDetail() // Fetch exam details first
        await this.fetchQuestionsForExam() // Then fetch questions
        if (this.$route.query.loadFirst && this.questions.length > 0) {
            this.loadQuestion(this.questions[0].id)
        } else {
            this.createNewQuestion() // Initialize with a default new question
        }
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
        selectBackground(imgUrl) {
            this.question.bg = imgUrl
        },

        handleMainImageUpdate(imageData) {
            // { file: File|null, preview: string|null }
            this.question.selectedFile = imageData.file
            this.question.previewImage = imageData.preview
        },

        onQuestionTypeChange() {
            // Reset type-specific fields when type changes
            const commonFields = {
                title: this.question.title,
                duration: this.question.duration,
                points: this.question.points,
                bg: this.question.bg,
                difficultyLevel: this.question.difficultyLevel,
                explanation: this.question.explanation,
                category: this.question.category,
                // Keep main image if already set
                selectedFile: this.question.selectedFile,
                previewImage: this.question.previewImage,
            }
            const newQuestionBase = getDefaultQuestion()
            this.question = {
                ...newQuestionBase, // Start with defaults for the new type
                ...commonFields, // Override with common fields
                questionType: this.question.questionType, // Ensure new type is set
            }

            // Initialize options for MC and Sequence if they are selected
            if (this.question.questionType === QUESTION_TYPES.MULTIPLE_CHOICE && this.question.options.length === 0) {
                this.question.options = [this.createOption(), this.createOption()]
                this.question.correctOption = 0
            } else if (
                this.question.questionType === QUESTION_TYPES.SEQUENCE &&
                this.question.sequenceOptions.length === 0
            ) {
                this.question.sequenceOptions = [this.createSequenceItem(0), this.createSequenceItem(1)]
            }
        },

        createOption(index = 0) {
            // Helper for MC
            const shapeIndex = index % SHAPE_MAP.length
            const shape = SHAPE_MAP[shapeIndex]
            return {
                text: '',
                id: String.fromCharCode(65 + index),
                shape: `${shape.color}${shape.name}`,
                image: null,
                imagePreview: null,
                file: null,
                correct: false,
                fieldId: Math.random().toString(36).substr(2, 9), // for unique key
            }
        },
        createSequenceItem(index = 0) {
            // Helper for Sequence
            const shapeIndex = index % SHAPE_MAP.length
            const shape = SHAPE_MAP[shapeIndex]
            return {
                text: '',
                id: String.fromCharCode(65 + index),
                shape: `${shape.color}${shape.name}`,
                order: index,
                fieldId: Math.random().toString(36).substr(2, 9),
            }
        },

        createNewQuestion() {
            this.selectedQuestion = null // Clear any loaded question object
            this.isEditMode = false // Not in edit mode
            this.question = getDefaultQuestion() // Use your helper to get a fresh default question object

            // Reset any specific UI elements tied to a loaded question, if necessary
            this.previewThumbnail = false // If this indicates a 'new question' placeholder in sidebar

            // If Quill editor for title needs re-rendering (e.g. to clear content properly)
            this.editorKey++

            // Initialize default options for MC if it's the default type
            if (this.question.questionType === QUESTION_TYPES.MULTIPLE_CHOICE) {
                this.question.options = [this.createOption(0), this.createOption(1)]
                this.question.correctOption = 0
            }
            // You can add similar initializations for other types if needed
        },

        async loadQuestion(questionId) {
            this.loading = true
            try {
                const response = await apiCall('GET', `/api/admin/question/${questionId}`)
                if (response && response.question) {
                    const apiQuestion = response.question
                    this.selectedQuestion = { ...apiQuestion } // Store the original loaded question
                    this.isEditMode = true

                    const newQState = getDefaultQuestion() // Start with defaults
                    newQState.id = apiQuestion.id // Keep ID for update
                    newQState.title = apiQuestion.title
                    newQState.questionType = apiQuestion.type
                    newQState.duration = apiQuestion.duration
                    newQState.points = apiQuestion.points
                    newQState.explanation = apiQuestion.explanation || ''
                    newQState.difficultyLevel = apiQuestion.difficulty_level || 'medium'
                    newQState.category = apiQuestion.category_id || ''
                    newQState.slide = apiQuestion.slide || ''

                    // Main Image
                    if (apiQuestion.files) {
                        try {
                            let filesArray = Array.isArray(apiQuestion.files)
                                ? apiQuestion.files
                                : JSON.parse(apiQuestion.files || '[]')
                            if (filesArray.length > 0) {
                                newQState.previewImage = '/uploads/' + filesArray[0]
                                newQState.originalMainImageFile = '/uploads/' + filesArray[0] // Store original path
                                newQState.selectedFile = null // Reset selected file, will be set if user uploads new
                            }
                        } catch (e) {
                            console.error('Error parsing main files:', e)
                        }
                    }

                    // Q_Options (Background)
                    if (apiQuestion.q_options) {
                        try {
                            const qOpts = JSON.parse(apiQuestion.q_options)
                            newQState.bg = qOpts.background_image || DEFAULT_BACKGROUND
                        } catch (e) {
                            newQState.bg = DEFAULT_BACKGROUND
                        }
                    } else {
                        newQState.bg = DEFAULT_BACKGROUND
                    }

                    // Type-specific options
                    if (apiQuestion.type === QUESTION_TYPES.TRUE_FALSE) {
                        const opts = JSON.parse(apiQuestion.options || '[]')
                        const trueOpt = opts.find((o) => o.text === 'True')
                        newQState.correctAnswer = trueOpt && trueOpt.correct ? 'true' : 'false'
                    } else if (apiQuestion.type === QUESTION_TYPES.MULTIPLE_CHOICE) {
                        try {
                            newQState.options = JSON.parse(apiQuestion.options || '[]').map((opt) => ({
                                ...opt,
                                imagePreview: opt.image
                                    ? opt.image.startsWith('data:')
                                        ? opt.image
                                        : '/uploads/' + opt.image
                                    : null,
                                file: null, // Will be set if user uploads new
                                fieldId: opt.id || Math.random().toString(36).substr(2, 9),
                            }))
                            const correctIdx = newQState.options.findIndex((o) => o.correct)
                            newQState.correctOption = correctIdx !== -1 ? correctIdx : null
                        } catch (e) {
                            console.error('Error parsing MC options:', e)
                            newQState.options = []
                        }
                    } else if (apiQuestion.type === QUESTION_TYPES.ORDINARY) {
                        newQState.shortAnswer = apiQuestion.short_answer || ''
                        newQState.alternativeAnswers = Array.isArray(apiQuestion.alternative_answers)
                            ? apiQuestion.alternative_answers
                            : JSON.parse(apiQuestion.alternative_answers || '[]')
                        newQState.caseSensitive = !!apiQuestion.case_sensitive
                    } else if (apiQuestion.type === QUESTION_TYPES.SEQUENCE) {
                        try {
                            newQState.sequenceOptions = JSON.parse(apiQuestion.options || '[]').map((opt) => ({
                                ...opt,
                                fieldId: opt.id || Math.random().toString(36).substr(2, 9),
                            })) // API stores sequence items in 'options' field
                            newQState.allowPartialCredit = !!apiQuestion.allow_partial_credit
                        } catch (e) {
                            console.error('Error parsing Sequence options:', e)
                            newQState.sequenceOptions = []
                        }
                    }
                    this.question = newQState
                    this.previewThumbnail = false
                } else {
                    this.$toast.error('Failed to load question details.')
                }
            } catch (error) {
                console.error('Error fetching question details:', error)
                this.$toast.error('Error loading question.')
            } finally {
                this.loading = false
            }
        },

        async fetchQuestionsForExam() {
            if (!this.exam.id) return // Ensure exam ID is available
            try {
                const response = await apiCall('GET', `/api/admin/exam/${this.exam.id}/questions`)
                this.questions = response.questions || []
            } catch (error) {
                console.error('Error fetching questions:', error)
            }
        },
        async fetchExamDetail() {
            try {
                const response = await apiCall('GET', `/api/admin/exam/${this.$route.params.id}`)
                this.exam = response.exam || {}
            } catch (error) {
                console.error('Error fetching exam:', error)
                this.$toast.error('Failed to load exam details')
            }
        },
        async confirmDelete(questionId) {
            if (await confirmDialog({ message: 'Are you sure you want to delete this question?', variant: 'danger' })) {
                try {
                    await apiCall('GET', `/api/admin/question/delete/${questionId}`) // Consider DELETE method
                    await this.fetchQuestionsForExam()
                    if (this.selectedQuestion && this.selectedQuestion.id === questionId) {
                        this.createNewQuestion() // If deleted q was loaded, reset form
                    }
                    this.$toast.success('Question deleted successfully')
                } catch (error) {
                    this.$toast.error('Failed to delete question')
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
            this.scientificInputTemp = '' // Reset temp
        },
        saveFromQuill() {
            if (
                !this.stripHtml(this.scientificInputTemp).trim() &&
                this.question.questionType !== QUESTION_TYPES.SLIDE
            ) {
                this.$toast.error('Question title cannot be empty')
                return
            }
            this.question.title = this.scientificInputTemp
            this.showQuillModal = false
        },
        stripHtml(html) {
            if (!html) return ''
            const doc = new DOMParser().parseFromString(html, 'text/html')
            return doc.body.textContent || ''
        },

        preparePayload() {
            const formData = new FormData()
            formData.append('title', this.question.title)
            formData.append('exam_id', this.exam.id) // Use fetched exam ID
            formData.append('type', this.question.questionType)
            formData.append('duration', this.question.duration)
            formData.append('point', this.question.points) // 'point' singular as per original
            formData.append('difficulty_level', this.question.difficultyLevel)
            formData.append('explanation', this.question.explanation)
            formData.append('category_id', this.question.category)
            formData.append('slide', this.question.slide)

            const qOptions = { background_image: this.question.bg }
            formData.append('q_options', JSON.stringify(qOptions))

            // Main question image
            if (this.question.selectedFile) {
                // If a new file is selected
                formData.append('files[0]', this.question.selectedFile)
            } else if (this.question.previewImage === null && this.question.originalMainImageFile) {
                // Image was removed, send a signal to backend if needed, or handle by not sending file
                formData.append('remove_main_image', 'true')
            }

            // Type-specific data
            switch (this.question.questionType) {
                case QUESTION_TYPES.TRUE_FALSE:
                    formData.append(
                        'options',
                        JSON.stringify([
                            { text: 'True', correct: this.question.correctAnswer === 'true', shape: 'greenrectangle' }, // Example shapes
                            { text: 'False', correct: this.question.correctAnswer === 'false', shape: 'redtriangle' },
                        ])
                    )
                    // 'correct_answer' might be redundant if options define it, but follow original if needed
                    formData.append('correct_answer', this.question.correctAnswer)
                    break
                case QUESTION_TYPES.MULTIPLE_CHOICE:
                    // Ensure correctOption is an index
                    const correctOptIndex =
                        typeof this.question.correctOption === 'number' ? this.question.correctOption : -1
                    const mcOptions = this.question.options.map((opt, index) => {
                        const optionData = { ...opt, correct: index === correctOptIndex }
                        delete optionData.file // Don't send file object in JSON
                        delete optionData.imagePreview
                        // 'image' field should be filename string if already on server, or null/empty if new
                        if (opt.file) {
                            // New file uploaded for this option
                            formData.append(`option_files[${index}]`, opt.file, `option_${opt.id}_${opt.file.name}`)
                            optionData.image = `option_${opt.id}_${opt.file.name}` // Placeholder for backend to link
                        } else if (!opt.image && opt.imagePreview && opt.imagePreview.startsWith('/uploads/')) {
                            // Existing image, keep path
                            optionData.image = opt.imagePreview.replace('/uploads/', '')
                        } else if (!opt.file && !opt.imagePreview) {
                            optionData.image = null // Image removed or never existed
                        }
                        // else: imagePreview is base64 of a previously loaded image that wasn't re-uploaded, backend must know not to delete it.
                        // or if image is a server path, it's already set.
                        return optionData
                    })
                    formData.append('options', JSON.stringify(mcOptions))
                    formData.append(
                        'correct_option',
                        correctOptIndex !== -1 ? String.fromCharCode(65 + correctOptIndex) : null
                    ) // Send 'A', 'B', etc. or null
                    break
                case QUESTION_TYPES.ORDINARY:
                    formData.append('short_answer', this.question.shortAnswer)
                    formData.append('alternative_answers', JSON.stringify(this.question.alternativeAnswers || []))
                    formData.append('case_sensitive', this.question.caseSensitive ? '1' : '0')
                    break
                case QUESTION_TYPES.SEQUENCE:
                    const seqOptions = this.question.sequenceOptions.map((opt) => {
                        const optionData = { ...opt } // text, id, shape, order
                        // Sequence options don't have individual image uploads in this refactor, handled by main image
                        return optionData
                    })
                    formData.append('options', JSON.stringify(seqOptions)) // Sequence items are stored in 'options'
                    formData.append('allow_partial_credit', this.question.allowPartialCredit ? '1' : '0')
                    break
            }
            return formData
        },

        async submitQuestion() {
            if (!this.exam.id) {
                this.$toast.error('Exam ID is missing. Cannot save question.')
                return
            }
            // Allow empty title for slides, otherwise check
            if (this.question.questionType !== QUESTION_TYPES.SLIDE && !this.stripHtml(this.question.title).trim()) {
                this.$toast.error('Question title cannot be empty.')
                return
            }
            // Basic validation for MC options
            if (this.question.questionType === QUESTION_TYPES.MULTIPLE_CHOICE) {
                if (this.question.options.length < 2) {
                    this.$toast.error('Multiple choice questions require at least 2 options.')
                    return
                }
                if (this.question.options.some((opt) => !opt.text.trim())) {
                    this.$toast.error('All multiple choice options must have text.')
                    return
                }
                if (
                    this.question.correctOption === null ||
                    this.question.correctOption < 0 ||
                    this.question.correctOption >= this.question.options.length
                ) {
                    this.$toast.error('Please select a correct answer for the multiple choice question.')
                    return
                }
            }
            // Add more validations as needed for other types

            this.loading = true
            const wasEditing = this.isEditMode // Keep track if it was an edit
            const editedQuestionId = this.selectedQuestion?.id

            try {
                const payload = this.preparePayload()
                const endpoint =
                    this.isEditMode && this.selectedQuestion?.id
                        ? `/api/admin/question/${this.selectedQuestion.id}`
                        : '/api/admin/question'

                if (this.isEditMode && this.selectedQuestion?.id) {
                    payload.append('_method', 'POST')
                }

                const response = await apiCall('POST', endpoint, payload, { 'Content-Type': 'multipart/form-data' })

                this.$toast.success('Question saved successfully!')

                // 1. Refresh the question list in the sidebar
                await this.fetchQuestionsForExam()

                // 2. Decide how to reset/reload the form
                if (wasEditing && response && response.question && response.question.id) {
                    // If it was an edit and we got a valid response for the updated question, reload it.
                    // This ensures the form shows the latest saved state of the edited question.
                    this.loadQuestion(response.question.id)
                } else if (response && response.question && response.question.id && !wasEditing) {
                    // If it was a NEW question and successfully saved,
                    // you might want to load this newly created question to allow further edits immediately,
                    // OR reset to a completely new blank form.
                    // Option A: Load the newly created question
                    // this.loadQuestion(response.question.id);

                    // Option B (Current implementation): Reset to a new blank question form
                    this.createNewQuestion()
                } else {
                    // Fallback: if something unexpected or if it was a new question, reset to a completely new blank form
                    this.createNewQuestion()
                }
            } catch (error) {
                console.error('Error saving question:', error)
                let errorMessage = 'Failed to save question.'
                if (error.response && error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message
                } else if (typeof error === 'string') {
                    errorMessage = error
                }
                this.$toast.error(errorMessage)
            } finally {
                this.loading = false
            }
        },
        async exitAndSubmit() {
            await this.submitQuestion()
            // Only redirect if not loading (i.e., submission was attempted)
            if (!this.loading) this.$router.push('/admin/exam')
        },
        async previewExam() {
            await this.submitQuestion()
            if (!this.loading) this.$router.push(`/admin/exam/${this.exam.id}/preview`)
        },

        // UI Helpers
        getBadgeLabel(type) {
            /* ... (kept as is) ... */
            const labels = {
                [QUESTION_TYPES.MULTIPLE_CHOICE]: 'Quiz',
                [QUESTION_TYPES.TRUE_FALSE]: 'True/False',
                [QUESTION_TYPES.ORDINARY]: 'Short Ans',
                [QUESTION_TYPES.SLIDE]: 'Slide',
                [QUESTION_TYPES.SEQUENCE]: 'Puzzle',
            }
            return labels[type] || 'Unknown'
        },
        getBadgeStyle(type) {
            /* ... (kept as is) ... */
            const styles = {
                [QUESTION_TYPES.MULTIPLE_CHOICE]: 'bg-red-100 text-red-400 border-red-400',
                [QUESTION_TYPES.TRUE_FALSE]: 'bg-blue-100 text-blue-400 border-blue-400',
                [QUESTION_TYPES.ORDINARY]: 'bg-purple-100 text-purple-400 border-purple-400',
                [QUESTION_TYPES.SLIDE]: 'bg-gray-100 text-gray-400 border-gray-400',
                [QUESTION_TYPES.SEQUENCE]: 'bg-yellow-100 text-yellow-400 border-yellow-400',
            }
            return styles[type] || 'bg-gray-100 text-gray-400 border-gray-400'
        },
        getShapeColor(shapeName) {
            /* ... (kept as is) ... */
            if (!shapeName) return 'bg-gray-300'
            const shape = SHAPE_MAP.find((s) => shapeName.toLowerCase().includes(s.color))
            return shape ? `bg-${shape.color}-500` : 'bg-gray-300' // Match by color part of shapeName
        },
        getShapeIcon(shapeName) {
            /* ... (kept as is) ... */
            if (!shapeName) return ''
            const shape = SHAPE_MAP.find((s) => shapeName.toLowerCase().includes(s.name))
            return shape?.icon || '' // Match by name part
        },
    },
}
</script>

<style scoped>
.scroll-x-no-expand {
    /* ... (kept as is) ... */
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    width: 100%;
    max-width: 100%;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
}
.scroll-x-no-expand > * {
    flex: 0 0 auto;
}

/* Style for Quill in modal if needed */
.fixed.inset-0 .bg-white .ql-toolbar.ql-snow {
    border-top-left-radius: 0.5rem; /* Tailwind rounded-lg */
    border-top-right-radius: 0.5rem;
}
.fixed.inset-0 .bg-white .ql-container.ql-snow {
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}
</style>
