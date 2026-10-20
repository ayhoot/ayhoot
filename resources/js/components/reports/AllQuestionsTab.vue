<template>
    <div class="overflow-x-auto rounded-b-2xl mt-1">
        <div v-if="!exam.questions || exam.questions.length === 0" class="text-center py-10 text-gray-500">
            No questions available in this report.
        </div>
        <DataTable v-else :wrapper-class="''" :columns="columns" :rows="exam.questions">
            <template #header:actions>
                <span class="sr-only">Actions</span>
            </template>
            <template #cell:question="{ row: question }">
                <div class="flex items-center">
                    <div class="flex font-semibold items-center justify-center text-sm mr-3">
                        {{ question?.order }}.
                    </div>
                    <div class="ml-1">
                        <div
                            class="text-sm font-medium text-gray-900 truncate max-w-[25ch] sm:max-w-[40ch] md:max-w-[50ch]"
                            :title="question?.title"
                        >
                            {{ question?.title || 'N/A' }}
                        </div>
                    </div>
                </div>
            </template>
            <template #cell:type="{ row: question }">
                <div class="text-sm text-gray-900 uppercase">
                    {{ question?.type?.replace('_', ' ') || 'N/A' }}
                </div>
            </template>
            <template #cell:accuracy="{ row: question }">
                <div class="text-sm text-gray-900">
                    <div class="text-xs flex items-center gap-2">
                        <div class="w-7 h-7 shrink-0">
                            <!-- Slightly larger chart -->
                            <Doughnut
                                :data="getOverallChartDataForQuestion(question)"
                                :options="smallChartOptions"
                            />
                        </div>
                        <span> {{ getOverallCorrectPercentage(question) }}% </span>
                    </div>
                </div>
            </template>
            <template #cell:actions="{ row: question }">
                <div class="flex items-center justify-end relative">
                    <ResponsiveButton
                        type="light-blue"
                        class="text-xs px-2 py-1 mx-0.5"
                        @click="openModal(question)"
                    >
                        Show Result
                    </ResponsiveButton>
                </div>
            </template>
        </DataTable>
    </div>

    <!-- Question Details Modal -->
    <div
        v-if="isModalOpen && selectedQuestion"
        class="fixed inset-0 bg-black/60 flex items-center justify-center z-[100] p-4"
    >
        <div
            ref="modalRef"
            class="bg-white rounded-xl max-h-[90vh] sm:max-h-[80vh] overflow-y-auto shadow-xl w-full sm:w-[80%] md:w-[70%] lg:w-[60%] relative flex flex-col"
        >
            <div
                class="sticky top-0 bg-white z-10 flex items-center justify-between border-b border-gray-200 py-3 px-6"
            >
                <h2 class="text-lg font-bold text-gray-700 truncate" :title="selectedQuestion.title">
                    {{ selectedQuestion.title }}
                </h2>
                <button class="text-gray-500 hover:text-gray-700 cursor-pointer text-2xl" @click="closeModal">×</button>
            </div>

            <div class="flex-grow overflow-y-auto">
                <div class="px-6 bg-gray-50 py-4 w-full sm:flex items-start gap-4">
                    <img
                        v-if="selectedQuestion.image"
                        :src="selectedQuestion.image"
                        :alt="selectedQuestion.title"
                        class="sm:w-40 w-full h-auto object-cover rounded-lg lg:col-span-1 mb-4 sm:mb-0 shrink-0"
                    />
                    <div class="w-full pt-0">
                        <div
                            v-for="option in selectedQuestion.options"
                            :key="option.id"
                            class="flex w-full items-center gap-2 mb-2 text-sm"
                        >
                            <div class="w-full flex items-center gap-2">
                                <span
                                    class="p-1.5 flex items-center justify-center rounded-sm shrink-0"
                                    :class="getShapeBg(option.shape)"
                                >
                                    <svg
                                        v-if="getShapeIcon(option.shape) === 'triangle'"
                                        class="w-4 h-4 text-white"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                    >
                                        <path d="M12 4L4 20h16L12 4z" />
                                    </svg>
                                    <svg
                                        v-else-if="getShapeIcon(option.shape) === 'diamond'"
                                        class="w-4 h-4 text-white"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                    >
                                        <path d="M12 2l10 10-10 10L2 12 12 2z" />
                                    </svg>
                                    <svg
                                        v-else-if="getShapeIcon(option.shape) === 'circle'"
                                        class="w-4 h-4 text-white"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                    >
                                        <circle cx="12" cy="12" r="10" />
                                    </svg>
                                    <svg
                                        v-else-if="getShapeIcon(option.shape) === 'rectangle'"
                                        class="w-4 h-4 text-white"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                    >
                                        <rect x="4" y="4" width="16" height="16" rx="2" ry="2" />
                                    </svg>
                                    <svg
                                        v-else-if="getShapeIcon(option.shape) === 'pentagon'"
                                        class="w-4 h-4 text-white"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                    >
                                        <path d="M12 2l8 6-3 10H7L4 8l8-6z" />
                                    </svg>
                                    <svg
                                        v-else-if="getShapeIcon(option.shape) === 'hexagon'"
                                        class="w-4 h-4 text-white"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                    >
                                        <path d="M9 0L17.6603 5V15L9 20L0.339746 15V5L9 0Z" fill="#white" />
                                    </svg>
                                </span>
                                <span class="sm:text-sm text-xs w-full truncate" :title="option.title">{{
                                    option.title
                                }}</span>
                            </div>
                            <span v-if="option.correct" class="text-green-500 text-lg shrink-0">✔️</span>
                            <span v-else class="text-red-500 text-lg shrink-0">❌</span>
                            <div class="w-full bg-gray-200 rounded-full h-3 min-w-[50px]">
                                <div
                                    class="bg-blue-600 text-xs h-3 rounded-full"
                                    :style="{ width: getAnswerPercent(option) + '%' }"
                                ></div>
                            </div>
                            <span class="text-xs text-gray-600 font-semibold shrink-0">{{
                                option.submitted_answers ?? option.count ?? '0'
                            }}</span>
                        </div>
                    </div>
                </div>

                <div
                    class="sm:flex w-full text-sm items-center justify-between space-y-2 sm:space-y-0 sm:gap-4 px-6 py-3 border-t border-b"
                >
                    <div class="w-full text-center sm:text-left">
                        <div class="py-1 flex items-center justify-between sm:justify-start gap-2">
                            <strong>Order:</strong>
                            <span>{{ selectedQuestion.order }}/{{ exam.questions.length }}</span>
                        </div>
                    </div>
                    <div class="w-full text-center sm:text-left">
                        <div class="py-1 flex items-center justify-between sm:justify-start gap-2">
                            <strong class="text-nowrap">Correct Answers:</strong>
                            <span>{{ getOverallCorrectPercentage(selectedQuestion) }}%</span>
                        </div>
                    </div>
                    <div class="w-full text-center sm:text-left">
                        <div class="py-1 flex items-center justify-between sm:justify-start gap-2">
                            <strong>Avg. Duration:</strong>
                            <span>{{ selectedQuestion.avg_duration }}s</span>
                        </div>
                    </div>
                </div>

                <div class="mt-1 px-6 pb-6">
                    <h3 class="text-md font-semibold my-3 text-gray-700">
                        Player Answers ({{ selectedQuestion.tutees_answers?.length || 0 }})
                    </h3>
                    <div class="overflow-x-auto rounded-lg border max-h-60">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100 sticky top-0 z-[5]">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Player
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Answered
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Result
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Points
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Time
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-if="
                                        !selectedQuestion.tutees_answers || selectedQuestion.tutees_answers.length === 0
                                    "
                                >
                                    <td colspan="5" class="px-4 py-3 text-center text-sm text-gray-500">
                                        No answers recorded for this question.
                                    </td>
                                </tr>
                                <tr
                                    v-for="ans in selectedQuestion.tutees_answers"
                                    :key="ans.playerId"
                                    class="hover:bg-gray-50 transition-colors duration-150 text-sm"
                                >
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img
                                                v-if="ans.image"
                                                :src="ans.image"
                                                class="w-6 h-6 rounded-full mr-2 shrink-0"
                                                :alt="ans.name"
                                            />
                                            <div
                                                class="text-sm font-medium text-gray-900 truncate max-w-[12ch]"
                                                :title="ans.name"
                                            >
                                                {{ ans.name || 'N/A' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm flex items-center gap-2 text-gray-900">
                                            <span
                                                v-if="ans.selected_option.shape"
                                                class="p-1 flex items-center justify-center rounded-sm shrink-0"
                                                :class="getShapeBg(ans.selected_option.shape)"
                                            >
                                                <svg
                                                    v-if="getShapeIcon(ans.selected_option.shape) === 'triangle'"
                                                    class="w-3 h-3 text-white"
                                                    viewBox="0 0 24 24"
                                                    fill="currentColor"
                                                >
                                                    <path d="M12 4L4 20h16L12 4z" />
                                                </svg>
                                                <svg
                                                    v-else-if="getShapeIcon(ans.selected_option.shape) === 'diamond'"
                                                    class="w-3 h-3 text-white"
                                                    viewBox="0 0 24 24"
                                                    fill="currentColor"
                                                >
                                                    <path d="M12 2l10 10-10 10L2 12 12 2z" />
                                                </svg>
                                                <svg
                                                    v-else-if="getShapeIcon(ans.selected_option.shape) === 'circle'"
                                                    class="w-3 h-3 text-white"
                                                    viewBox="0 0 24 24"
                                                    fill="currentColor"
                                                >
                                                    <circle cx="12" cy="12" r="10" />
                                                </svg>
                                                <svg
                                                    v-else-if="getShapeIcon(ans.selected_option.shape) === 'rectangle'"
                                                    class="w-3 h-3 text-white"
                                                    viewBox="0 0 24 24"
                                                    fill="currentColor"
                                                >
                                                    <rect x="4" y="4" width="16" height="16" rx="2" ry="2" />
                                                </svg>
                                            </span>
                                            <span class="truncate max-w-[15ch]" :title="ans.selected_option.title">{{
                                                ans.selected_option.title || 'No Answer'
                                            }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <span v-if="ans.selected_option.correct === true" class="text-green-500"
                                            >✔️ Correct</span
                                        >
                                        <span v-else-if="ans.selected_option.correct === false" class="text-red-500"
                                            >❌ Incorrect</span
                                        >
                                        <span v-else class="text-gray-500">🔘 No Answer</span>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-900">
                                        {{ ans.selected_option.score || '0' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-900">
                                        {{ ans.selected_option.answered_duration || '0' }}s
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue' // Adjust path
import DataTable from '@/components/shared/DataTable.vue'
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, DoughnutController } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, ArcElement, DoughnutController)

export default {
    name: 'AllQuestionsTab',
    components: {
        ResponsiveButton,
        DataTable,
        Doughnut,
    },
    props: {
        exam: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            selectedQuestion: null,
            isModalOpen: false,
        }
    },
    computed: {
        columns() {
            return [
                { key: 'question', label: 'Question', align: 'left' },
                { key: 'type', label: 'Type', align: 'left' },
                { key: 'accuracy', label: 'Accuracy', align: 'left' },
                { key: 'actions', label: '', align: 'right', tdClass: 'font-medium' },
            ]
        },
        smallChartOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false },
                },
            }
        },
    },
    mounted() {
        document.addEventListener('keydown', this.handleEscKey)
    },
    beforeUnmount() {
        document.removeEventListener('keydown', this.handleEscKey)
        // Remove click outside listener if it was added here.
        // The original template had it, but it's more robust to handle within openModal/closeModal
    },
    methods: {
        openModal(question) {
            const processedQuestion = { ...question } // Shallow copy

            // Construct tutees_answers
            processedQuestion.tutees_answers = (this.exam.players || []).map((player) => {
                // Try to find the player's answer for this question using multiple possible keys
                const answeredQuestionData = (player.answered_questions || []).find((aq) =>
                    aq && (aq.id === question.id || aq.question_id === question.id || aq.questionId === question.id)
                )

                let playerSelectedOptionTitle = 'No Answer'
                let matchedOptionForShape = null
                let selectedOptionDetails = {
                    title: 'No Answer',
                    shape: null,
                    correct: null, // null for no answer
                    score: 0,
                    answered_duration: 0,
                }

                if (answeredQuestionData) {
                    // Support multiple shapes of answer payloads returned by backend
                    // prefer selected_option.* if present, otherwise fallback to top-level fields
                    const sel = answeredQuestionData.selected_option || {}

                    playerSelectedOptionTitle = sel.title || sel.text || sel.option_text || answeredQuestionData.answer_text || answeredQuestionData.answer || sel.answer || 'No Answer'

                    // If still 'No Answer' but option_id or selected_option_id exists, match against question options
                    const optionIdToMatch = sel.id || answeredQuestionData.selected_option_id
                    if ((playerSelectedOptionTitle === 'No Answer' || !playerSelectedOptionTitle) && optionIdToMatch) {
                        const optMatch = (question.options || []).find((opt) => opt.id == optionIdToMatch)
                        if (optMatch) {
                            playerSelectedOptionTitle = optMatch.title || optMatch.text || optMatch.option_text || 'Option'
                        }
                    }

                    // correctness can be in selected_option.correct or answeredQuestionData.is_correct
                    if (typeof sel.correct !== 'undefined') selectedOptionDetails.correct = sel.correct
                    else if (typeof answeredQuestionData.is_correct !== 'undefined') selectedOptionDetails.correct = answeredQuestionData.is_correct

                    // score may be under selected_option.score or points_earned
                    selectedOptionDetails.score = sel.score ?? answeredQuestionData.points_earned ?? 0

                    // duration/time may be under selected_option.answered_duration or answeredQuestionData.time
                    selectedOptionDetails.answered_duration = sel.answered_duration ?? answeredQuestionData.time ?? 0

                    selectedOptionDetails.title = playerSelectedOptionTitle

                    // Try to find the original option for shape (useful for MCQ)
                    if (question.type === 'multiple_choice' || question.type === 'true_false') {
                        matchedOptionForShape = (question.options || []).find((opt) => 
                            (opt.title || opt.text || opt.option_text) === playerSelectedOptionTitle ||
                            (optionIdToMatch && opt.id == optionIdToMatch)
                        )
                        if (matchedOptionForShape) selectedOptionDetails.shape = matchedOptionForShape.shape
                    }

                    // For sequence questions, parse JSON arrays into readable text
                    if (
                        question.type === 'sequence' &&
                        typeof playerSelectedOptionTitle === 'string' &&
                        playerSelectedOptionTitle.startsWith('[')
                    ) {
                        try {
                            const sequenceArray = JSON.parse(playerSelectedOptionTitle)
                            selectedOptionDetails.title = Array.isArray(sequenceArray)
                                ? sequenceArray.join(', ')
                                : playerSelectedOptionTitle
                        } catch (e) {
                            /* ignore parse error, use raw string */
                        }
                    }
                }

                return {
                    playerId: player.id,
                    name: player.name,
                    image: player.image,
                    selected_option: selectedOptionDetails,
                }
            })

            // Compute submitted_answers for each option (support option.text, title, option_text, or opt.count)
            if (Array.isArray(processedQuestion.options)) {
                processedQuestion.options.forEach((opt) => {
                    if (opt.submitted_answers === undefined && opt.count !== undefined) {
                        opt.submitted_answers = opt.count
                    } else if (opt.submitted_answers === undefined) {
                        const optText = (opt.text || opt.title || opt.option_text || '').toLowerCase().trim()
                        const optId = opt.id
                        opt.submitted_answers = processedQuestion.tutees_answers.filter((t) => {
                            const sub = t?.selected_option || {}
                            if (optId && sub.id == optId) return true
                            const subText = (sub.title || sub.text || sub.option_text || '').toLowerCase().trim()
                            return optText && subText === optText
                        }).length
                    }
                })
            }

            // Compute correct_answers and avg_duration for the question if missing
            if (typeof processedQuestion.correct_answers !== 'number') {
                processedQuestion.correct_answers = processedQuestion.tutees_answers.reduce(
                    (s, t) => (t?.selected_option?.correct === true ? s + 1 : s),
                    0
                )
            }
            if (typeof processedQuestion.avg_duration !== 'number') {
                const durations = processedQuestion.tutees_answers.map((t) => t?.selected_option?.answered_duration || 0)
                const total = durations.reduce((a, b) => a + b, 0)
                processedQuestion.avg_duration = durations.length ? Math.round(total / durations.length) : 0
            }

            this.selectedQuestion = processedQuestion
            this.isModalOpen = true
            document.body.style.overflow = 'hidden' // Prevent background scroll
            this.$nextTick(() => {
                if (this.$refs.modalRef) this.$refs.modalRef.focus() // For accessibility, focus modal
                document.addEventListener('click', this.handleClickOutsideModal, true)
            })
        },
        closeModal() {
            this.isModalOpen = false
            this.selectedQuestion = null
            document.body.style.overflow = '' // Restore background scroll
            document.removeEventListener('click', this.handleClickOutsideModal, true)
        },
        handleClickOutsideModal(event) {
            const modalContent = this.$refs.modalRef
            if (modalContent && !modalContent.contains(event.target)) {
                // Check if the click was on the modal backdrop itself, not on a button that might open another modal
                if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
                    this.closeModal()
                }
            }
        },
        handleEscKey(event) {
            if (event.key === 'Escape' && this.isModalOpen) {
                this.closeModal()
            }
        },
        getOverallCorrectPercentage(question) {
            if (!question || !this.exam.players || this.exam.players.length === 0) return '0.0'
            const totalPlayers = this.exam.players.length
            let correctAnswers = 0
            if (typeof question.correct_answers === 'number') correctAnswers = question.correct_answers
            else if (Array.isArray(question.tutees_answers)) {
                correctAnswers = question.tutees_answers.reduce((s, t) => (t?.selected_option?.correct === true ? s + 1 : s), 0)
            }
            const percent = (correctAnswers / totalPlayers) * 100
            return percent.toFixed(1)
        },
        getOverallChartDataForQuestion(question) {
            let correct = 0
            if (typeof question.correct_answers === 'number') correct = question.correct_answers
            else if (Array.isArray(question.tutees_answers)) {
                correct = question.tutees_answers.reduce((s, t) => (t?.selected_option?.correct === true ? s + 1 : s), 0)
            }
            const total = this.exam.players?.length || 1
            const incorrect = Math.max(0, total - correct)

            return {
                labels: ['Correct', 'Incorrect'],
                datasets: [
                    {
                        data: [correct, incorrect],
                        backgroundColor: ['#4ade80', '#f87171'],
                        borderWidth: 0,
                    },
                ],
            }
        },
        getShapeBg(shape) {
            if (!shape || typeof shape !== 'string') return 'bg-gray-300' // Default or hidden
            if (shape.startsWith('red')) return 'bg-red-500'
            if (shape.startsWith('blue')) return 'bg-blue-500'
            if (shape.startsWith('yellow')) return 'bg-yellow-400'
            if (shape.startsWith('green')) return 'bg-green-500'
            if (shape.startsWith('purple')) return 'bg-purple-500'
            if (shape.startsWith('pink')) return 'bg-pink-500'
            if (shape.startsWith('orange')) return 'bg-orange-500'
            return 'bg-gray-400' // Fallback for unknown shapes
        },
        getShapeIcon(shape) {
            if (!shape || typeof shape !== 'string') return null
            if (shape.includes('triangle')) return 'triangle'
            if (shape.includes('diamond')) return 'diamond'
            if (shape.includes('circle')) return 'circle'
            if (shape.includes('rectangle') || shape.includes('square')) return 'rectangle' // Allow square
            if (shape.includes('pentagon')) return 'pentagon'
            if (shape.includes('hexagon')) return 'hexagon'
            return null
        },
        getAnswerPercent(option) {
            const totalPlayers = this.exam.players?.length || 0
            const count = option.submitted_answers ?? option.count ?? 0
            if (totalPlayers === 0 || !count) return 0
            return ((count / totalPlayers) * 100).toFixed(1)
        },
    },
}
</script>

<style scoped>
/* Scoped styles from original component */
.container-fluid {
    width: 100%;
    padding-right: 1rem;
    padding-left: 1rem;
    margin-right: auto;
    margin-left: auto;
}

@media (min-width: 640px) {
    .container-fluid {
        padding-right: 1.5rem;
        padding-left: 1.5rem;
    }
}

.transition-shadow {
    transition: all 0.3s ease;
}

.hover\:shadow-lg:hover {
    box-shadow:
        0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>
