<template>
    <PageWrapper>
                <div v-if="isLoading" class="text-center py-10">Loading report...</div>
                <div v-if="error" class="text-center py-10 text-red-500">Error: {{ error }}</div>

                <div v-if="!isLoading && !error && exam.id">
                    <div class="grid grid-cols-1 lg:grid-cols-4 w-full gap-8">
                        <!-- 1st Col - Takes full width on small screens, 75% on large screens -->
                        <div class="lg:col-span-3 flex flex-col justify-between">
                            <!-- Header Section -->
                            <div class="pb-4">
                                <div class="flex items-center justify-between py-2">
                                    <h3 class="font-semibold text-gray-500">Report</h3>
                                    <ResponsiveButton type="light-gray" class="p-2 flex items-center gap-1">
                                        <span>
                                            <svg
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    d="M4 15.2044L4 18.8925C4 19.4514 4.21071 19.9875 4.58579 20.3827C4.96086 20.778 5.46957 21 6 21H18C18.5304 21 19.0391 20.778 19.4142 20.3827C19.7893 19.9875 20 19.4514 20 18.8925V15.2044M12.0011 3V14.9425M12.0011 14.9425L16.5725 10.3793M12.0011 14.9425L7.42969 10.3793"
                                                    stroke="black"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </span>
                                        <span>Download Report</span>
                                    </ResponsiveButton>
                                </div>
                                <!-- <div class="flex items-center gap-x-2" v-if="isEditingTitle">
                                    <input
                                        v-model="editedTitle"
                                        :style="{
                                            width: `${editedTitle.length * 0.8 + 2}ch`,
                                            minWidth: '150px',
                                        }"
                                        class="font-bold py-4 text-xl sm:text-3xl focus:outline-none border-b"
                                        @keyup.enter="submitTitleEdit"
                                    />
                                    <ResponsiveButton
                                        type="light-green"
                                        @click="submitTitleEdit"
                                        class="text-blue-600 p-1 font-semibold cursor-pointer"
                                    >
                                        Save
                                    </ResponsiveButton>
                                    <ResponsiveButton
                                        type="light-red"
                                        @click="cancelTitleEdit"
                                        class="text-gray-500 p-1 cursor-pointer"
                                    >
                                        Cancel
                                    </ResponsiveButton>
                                </div> -->
                                <div class="flex items-center gap-x-2">
                                    <h3 class="font-bold py-4 text-xl sm:text-3xl">
                                        {{ exam?.title || 'No title' }}
                                    </h3>
                                    <!-- <button @click="startEditingTitle" class="text-blue-600 cursor-pointer">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="w-5 h-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z"
                                            />
                                        </svg>
                                    </button> -->
                                </div>
                            </div>
                        </div>
                        <!-- 2nd Col - Hidden on small screens, 25% on large screens -->
                        <div>
                            <div class="flex flex-col w-full text-sm">
                                <div class="flex items-center gap-2 w-full border-b border-gray-600 py-2">
                                    <h3 class="font-medium">{{ exam.publish_type }} Ayhoot</h3>
                                    <span
                                        class="text-xs font-semibold bg-green-200 text-green-600 rounded-md px-2 py-0.5"
                                    >
                                        {{ exam.status }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 w-full border-b border-gray-600 py-2">
                                    <h3 class="font-medium">Start Date</h3>
                                    <h3 class="">{{ formatDate(exam.created_at) }}</h3>
                                </div>
                                <div class="flex items-center gap-1 w-full border-b border-gray-600 py-2">
                                    <h3 class="font-medium">End Date</h3>
                                    <h3 class="">{{ formatDate(exam.ended_at) }}</h3>
                                </div>
                                <div class="flex items-center gap-1 w-full border-b border-gray-600 py-2">
                                    <h3 class="font-medium">Hosted by</h3>
                                    <h3 class="">{{ exam.host }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Tabs -->
                    <div class="border-b border-gray-200 dark:border-gray-700 mt-4">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                            <li class="me-2">
                                <button
                                    @click="activeTab = 'summary'"
                                    :class="
                                        activeTab === 'summary'
                                            ? 'text-blue-600 border-blue-600'
                                            : 'text-gray-500 border-transparent'
                                    "
                                    class="inline-block p-4 border-b-2 rounded-t-lg cursor-pointer font-semibold"
                                >
                                    Summary
                                </button>
                            </li>

                            <li class="me-2">
                                <button
                                    @click="activeTab = 'questions'"
                                    :class="
                                        activeTab === 'questions'
                                            ? 'text-blue-600 border-blue-600'
                                            : 'text-gray-500 border-transparent'
                                    "
                                    class="inline-block p-4 border-b-2 rounded-t-lg cursor-pointer font-semibold"
                                >
                                    Questions ({{ exam.questions?.length || 0 }})
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div v-show="activeTab === 'summary'" class="py-4 w-full">
                        <SummaryTab
                            :exam="exam"
                            :format-duration="formatDuration"
                            :get-chart-data-fn="getChartDataForQuestion"
                            :get-correct-percentage-fn="getCorrectPercentageForQuestion"
                        />
                    </div>

                    <div v-show="activeTab === 'questions'" class="py-4 w-full">
                        <ul
                            class="flex flex-wrap text-sm font-medium text-center border-b border-gray-200 rounded-t-xl bg-gray-100"
                        >
                            <li class="me-2">
                                <button
                                    @click="activeQTab = 'all-questions'"
                                    :class="
                                        activeQTab === 'all-questions'
                                            ? 'text-blue-600 border-blue-600'
                                            : 'text-gray-500 border-transparent'
                                    "
                                    class="inline-block p-4 px-4 border-b-2 rounded-t-lg cursor-pointer font-semibold"
                                >
                                    All Questions ({{ exam?.questions?.length || 0 }})
                                </button>
                            </li>
                        </ul>
                        <div v-show="activeQTab === 'all-questions'" class="w-full">
                            <AllQuestionsTab :exam="exam" />
                        </div>
                    </div>
                </div>
        <!-- <Footer /> -->
    </PageWrapper>
</template>

<script>
import { apiCall } from '@/services/apiService'
import PageWrapper from '@/components/shared/PageWrapper.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import SummaryTab from '@/components/reports/SummaryTab.vue'
import AllQuestionsTab from '@/components/reports/AllQuestionsTab.vue'

export default {
    name: 'ExamReportPage', // Renamed for clarity, was AssignedAyhoot
    components: {
        PageWrapper,
        ResponsiveButton,
        SummaryTab,
        AllQuestionsTab,
    },
    data() {
        return {
            isEditingTitle: false,
            activeTab: 'summary',
            activeQTab: 'all-questions',
            editedTitle: '',
            exam: {
                id: null,
                title: '',
                publish_type: '',
                status: '',
                created_at: '',
                ended_at: '',
                host: '',
                duration: 0,
                avg_score: 0,
                questions: [],
                players: [],
                difficult_questions: [],
                help_needers: [], // Calculated
                not_finished: [], // Calculated
            },
            isLoading: false,
            error: null,
        }
    },
    methods: {
        startEditingTitle() {
            this.editedTitle = this.exam?.title || ''
            this.isEditingTitle = true
        },
        cancelTitleEdit() {
            this.isEditingTitle = false
        },
        async submitTitleEdit() {
            // API call to update title would go here
            // For now, just update locally and exit edit mode
            if (this.exam) {
                this.exam.title = this.editedTitle
            }
            this.isEditingTitle = false
            // Example: await apiCall('PUT', `/api/student/exam/exam/${this.exam.id}/title`, { title: this.editedTitle });
            // Then potentially re-fetch or update from response.
        },
        async fetchExamReport() {
            this.isLoading = true
            this.error = null
            try {
                const examId = this.$route.params.id
                const response = await apiCall('GET', `/api/student/exam/exam/results/${examId}`)

                if (!response || !response.exam_report) {
                    throw new Error('Invalid API response format')
                }

                const report = response.exam_report
                this.exam = {
                    ...report,
                    questions: report.questions || [],
                    players: report.players || [],
                    difficult_questions: report.difficult_questions || [],
                    // help_needers and not_finished will be calculated below
                }

                // Ensure avg_score exists for overall charts (fallback to players average or student_performance)
                if (typeof report.avg_score === 'number') this.exam.avg_score = report.avg_score
                else if (this.exam.players && this.exam.players.length) {
                    const sumScores = this.exam.players.reduce((s, p) => s + (parseFloat(p.score) || 0), 0)
                    this.exam.avg_score = +(sumScores / this.exam.players.length).toFixed(2)
                } else {
                    this.exam.avg_score = this.exam.student_performance?.score || 0
                }

                // Normalize per-question derived fields so charts and tabs can use them reliably
                this.exam.questions = (this.exam.questions || []).map((q) => {
                    const question = { ...q }

                    // ensure tutees_answers array exists
                    question.tutees_answers = question.tutees_answers || []

                    // compute correct_answers if not provided
                    if (typeof question.correct_answers !== 'number') {
                        question.correct_answers = question.tutees_answers.reduce(
                            (s, t) => (t?.selected_option?.correct === true ? s + 1 : s),
                            0
                        )
                    }

                    // compute submitted_answers per option
                    question.options = (question.options || []).map((opt) => {
                        const option = { ...opt }
                        const optText = (option.text || option.title || '').toLowerCase()
                        option.submitted_answers = question.tutees_answers.filter((t) => {
                            const sub = t?.selected_option || {}
                            const subText = (sub.title || sub.text || '').toLowerCase()
                            return subText === optText
                        }).length
                        return option
                    })

                    // compute avg_duration if missing
                    if (typeof question.avg_duration !== 'number') {
                        const durations = question.tutees_answers.map((t) => t?.selected_option?.answered_duration || 0)
                        const total = durations.reduce((a, b) => a + b, 0)
                        question.avg_duration = durations.length ? Math.round(total / durations.length) : 0
                    }

                    return question
                })

                // Calculate help needers: players with < 35% correct answers
                if (this.exam.players && Array.isArray(this.exam.players) && this.exam.questions.length > 0) {
                    this.exam.help_needers = this.exam.players.filter(
                        (player) => player && (player.correct_answers / this.exam.questions.length) * 100 < 35
                    )
                } else {
                    this.exam.help_needers = []
                }

                // Calculate not finished: players with unanswered questions
                if (this.exam.players && Array.isArray(this.exam.players)) {
                    this.exam.not_finished = this.exam.players.filter(
                        (player) => player && player.unanswered_questions > 0
                    )
                } else {
                    this.exam.not_finished = []
                }
            } catch (error) {
                console.error('Error fetching exam report:', error)
                
                // Handle 403 errors gracefully - show a user-friendly message
                if (error.response && error.response.status === 403) {
                    this.error = 'You do not have permission to view this exam report. The exam may have expired or you may not have completed it properly.'
                } else {
                    this.error = error.message || 'Failed to load exam report.'
                }
            } finally {
                this.isLoading = false
            }
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A'
            try {
                return new Date(dateString).toLocaleString()
            } catch (e) {
                return 'Invalid Date'
            }
        },
        formatDuration(seconds) {
            if (typeof seconds !== 'number' || isNaN(seconds) || seconds < 0) {
                return '0s'
            }
            const minutes = Math.floor(seconds / 60)
            const remainingSeconds = seconds % 60
            if (minutes > 0) {
                return `${minutes}m ${remainingSeconds}s`
            }
            return `${remainingSeconds}s`
        },
        // These are for individual question charts, pass them to SummaryTab for difficult questions
        getCorrectPercentageForQuestion(question) {
            if (!question || !this.exam.players || this.exam.players.length === 0) return '0'
            const totalPlayers = this.exam.players.length
            // Fallback: compute correct answers from tutees_answers if correct_answers missing
            let correctAnswers = 0
            if (typeof question.correct_answers === 'number') correctAnswers = question.correct_answers
            else if (Array.isArray(question.tutees_answers)) {
                correctAnswers = question.tutees_answers.reduce((sum, t) => {
                    const c = t?.selected_option?.correct
                    return sum + (c === true ? 1 : 0)
                }, 0)
            }
            const percent = (correctAnswers / totalPlayers) * 100
            return percent.toFixed(1)
        },
        getChartDataForQuestion(question) {
            let correct = 0
            if (typeof question.correct_answers === 'number') correct = question.correct_answers
            else if (Array.isArray(question.tutees_answers)) {
                correct = question.tutees_answers.reduce((s, t) => (t?.selected_option?.correct === true ? s + 1 : s), 0)
            }
            const total = this.exam.players?.length || 1 // Ensure players array exists
            const incorrect = Math.max(0, total - correct)

            return {
                labels: ['Correct', 'Incorrect'],
                datasets: [
                    {
                        data: [correct, incorrect],
                        backgroundColor: ['#4ade80', '#f87171'], // Green for correct, Red for incorrect
                        borderWidth: 0,
                    },
                ],
            }
        },
    },
    async mounted() {
        await this.fetchExamReport()
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
/* Basic tooltip styling if not provided by a framework/global CSS */
.tooltip {
    position: relative;
    display: inline-block;
}

.tooltip .tooltip-content {
    visibility: hidden;
    width: max-content;
    max-width: 200px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 5px 8px;
    position: absolute;
    z-index: 10;
    bottom: 125%; /* Position above the button */
    left: 50%;
    margin-left: -60px; /* Adjust to center */
    opacity: 0;
    transition: opacity 0.3s;
    font-size: 0.75rem; /* text-xs */
}

.tooltip:hover .tooltip-content {
    visibility: visible;
    opacity: 1;
}

.tooltip.tooltip-left .tooltip-content {
    bottom: auto;
    top: 50%;
    transform: translateY(-50%);
    left: auto;
    right: 125%; /* Position to the left */
    margin-left: 0;
    margin-right: 5px;
}
</style>
