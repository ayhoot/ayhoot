<template>
    <div class="rounded-b-2xl mt-1 space-y-4">
        <div v-if="!players || players.length === 0" class="text-center py-12 text-gray-500 bg-white rounded-xl border border-gray-100">
            Everyone finished their session!
        </div>
        <div v-else class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th scope="col" class="w-10 px-3 py-3.5 text-center text-xs font-semibold text-gray-500"></th>
                            <th scope="col" class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                {{ t('reportPage.playersTable.player') || 'Player' }}
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                {{ t('reportPage.playersTable.unanswered') || 'Unanswered' }}
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                {{ t('reportPage.playersTable.score') || 'Score So Far' }}
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                {{ t('reportPage.info.status') || 'Status' }}
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">
                                {{ t('reportPage.playersTable.actions') || 'Actions' }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <template v-for="(player, index) in players" :key="player.id || player.student_id || index">
                            <tr
                                @click="togglePlayerExpand(player)"
                                class="hover:bg-amber-50/30 transition-colors cursor-pointer group"
                                :class="{ 'bg-amber-50/20': isPlayerExpanded(player) }"
                            >
                                <td class="px-3 py-4 text-center">
                                    <button
                                        type="button"
                                        class="p-1 rounded-md text-gray-400 group-hover:text-amber-600 hover:bg-amber-100/60 transition-all cursor-pointer"
                                    >
                                        <svg
                                            class="w-4 h-4 transition-transform duration-200"
                                            :class="{ 'rotate-180 text-amber-600': isPlayerExpanded(player) }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <img
                                            v-if="player.image || player.avatar"
                                            :src="player.image || player.avatar"
                                            class="w-8 h-8 rounded-full object-cover shrink-0 border border-gray-200"
                                            :alt="player.name"
                                        />
                                        <div
                                            v-else
                                            class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs shrink-0"
                                        >
                                            {{ (player.name || player.nickname || 'P').charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 truncate">
                                                {{ player.name || player.nickname || 'Anonymous' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-rose-600">
                                        {{ getUnansweredCount(player) }} / {{ totalQuestionsCount }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-800">
                                        {{ Number(player.total_score || player.score || 0).toLocaleString() }} {{ t('reportPage.playersTable.points') || 'pts' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                                        Incomplete
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right">
                                    <button
                                        type="button"
                                        @click.stop="togglePlayerExpand(player)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-md border transition-all cursor-pointer"
                                        :class="isPlayerExpanded(player)
                                            ? 'bg-amber-600 text-white border-amber-600'
                                            : 'bg-white text-amber-700 border-amber-300 hover:bg-amber-50'"
                                    >
                                        {{ isPlayerExpanded(player) ? (t('reportPage.playersTable.hideAnswers') || 'Hide Answers') : (t('reportPage.playersTable.viewAnswers') || 'View Answers') }}
                                    </button>
                                </td>
                            </tr>

                            <!-- Expanded Sub-Row -->
                            <tr v-if="isPlayerExpanded(player)" class="bg-gray-50/80">
                                <td colspan="6" class="p-4 sm:p-6">
                                    <div class="bg-white rounded-xl border border-amber-200 shadow-sm p-4 space-y-3">
                                        <h5 class="text-xs font-bold text-gray-700 pb-2 border-b border-gray-100">
                                            {{ player.name || 'Student' }}'s Answer Status
                                        </h5>
                                        <div class="space-y-2">
                                            <div
                                                v-for="(question, qIndex) in questionsList"
                                                :key="question.id || qIndex"
                                                class="p-2.5 rounded-lg border text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2"
                                                :class="isQuestionAnswered(player, question) ? 'bg-blue-50/20 border-blue-200' : 'bg-gray-50 border-gray-200'"
                                            >
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-gray-500">Q{{ question.order || qIndex + 1 }}.</span>
                                                    <span class="font-medium text-gray-800">{{ question.title || question.question_text }}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        v-if="isQuestionAnswered(player, question)"
                                                        class="px-2 py-0.5 rounded-md font-semibold"
                                                        :class="isQuestionCorrect(player, question) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                                                    >
                                                        {{ isQuestionCorrect(player, question) ? 'Correct ✓' : 'Incorrect ✗' }}
                                                    </span>
                                                    <span v-else class="px-2 py-0.5 rounded-md font-medium bg-gray-100 text-gray-600">
                                                        Unanswered
                                                    </span>
                                                    <span class="text-gray-600">
                                                        {{ getStudentAnswerText(player, question) || 'No answer' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import { useI18n } from 'vue-i18n'

export default {
    name: 'NotFinishedTab',
    props: {
        players: {
            type: Array,
            required: true,
            default: () => []
        },
        exam: {
            type: Object,
            required: true,
            default: () => ({})
        }
    },
    setup() {
        const { t } = useI18n()
        return { t }
    },
    data() {
        return {
            expandedPlayerIds: new Set(),
        }
    },
    computed: {
        questionsList() {
            const raw = this.exam.questions || this.exam.exam?.questions || []
            return Array.isArray(raw) ? raw : Object.values(raw)
        },
        totalQuestionsCount() {
            return this.questionsList.length || this.exam.total_questions || 1
        }
    },
    methods: {
        getPlayerKey(player) {
            return String(player.id || player.student_id || player.participant_id || player.name)
        },
        isPlayerExpanded(player) {
            return this.expandedPlayerIds.has(this.getPlayerKey(player))
        },
        togglePlayerExpand(player) {
            const key = this.getPlayerKey(player)
            if (this.expandedPlayerIds.has(key)) {
                this.expandedPlayerIds.delete(key)
            } else {
                this.expandedPlayerIds.add(key)
            }
            this.expandedPlayerIds = new Set(this.expandedPlayerIds)
        },
        getUnansweredCount(player) {
            const answered = (player.answered_questions || player.answers || []).length
            return Math.max(0, this.totalQuestionsCount - answered)
        },
        getPlayerAnswer(player, question) {
            if (!player) return null
            const answers = player.answered_questions || player.answers || []
            return answers.find(a =>
                String(a.question_id) === String(question.id) ||
                String(a.id) === String(question.id) ||
                (a.question_index !== undefined && Number(a.question_index) === Number(question.order - 1))
            )
        },
        isQuestionAnswered(player, question) {
            return !!this.getPlayerAnswer(player, question)
        },
        isQuestionCorrect(player, question) {
            const ans = this.getPlayerAnswer(player, question)
            return ans ? (ans.is_correct === true || ans.selected_option?.correct === true) : false
        },
        getStudentAnswerText(player, question) {
            const ans = this.getPlayerAnswer(player, question)
            if (!ans) return null
            if (typeof ans.answer === 'string') return ans.answer
            if (ans.selected_option?.text) return ans.selected_option.text
            if (ans.selected_option?.title) return ans.selected_option.title
            return 'Answered'
        }
    }
}
</script>
