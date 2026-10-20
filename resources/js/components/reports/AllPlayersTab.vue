<template>
    <div class="rounded-b-2xl mt-1 space-y-4">
        <!-- Top Toolbar: Search & Expand All -->
        <div v-if="players && players.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-3 px-1">
            <div class="relative w-full sm:w-72">
                <input
                    type="text"
                    v-model="searchQuery"
                    :placeholder="t('reportPage.playersTable.searchPlaceholder') || 'Search players...'"
                    class="w-full px-3 py-1.5 pl-9 text-xs border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 bg-white"
                />
                <svg
                    class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto">
                <button
                    type="button"
                    @click="toggleExpandAll"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors cursor-pointer"
                >
                    <svg
                        class="w-3.5 h-3.5 transition-transform"
                        :class="{ 'rotate-180': isAllExpanded }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    <span>
                        {{ isAllExpanded ? (t('reportPage.playersTable.collapseAll') || 'Collapse All') : (t('reportPage.playersTable.expandAll') || 'Expand All') }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!players || players.length === 0" class="text-center py-12 text-gray-500 bg-white rounded-xl border border-gray-100">
            {{ t('reportPage.playersTable.noPlayers') || 'No players joined this session yet.' }}
        </div>

        <!-- Players Table with Expandable Answer Sheets -->
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
                                {{ t('reportPage.playersTable.rank') || 'Rank' }}
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                {{ t('reportPage.playersTable.score') || 'Score' }}
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                {{ t('reportPage.playersTable.correctAnswers') || 'Correct Answers' }}
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                {{ t('reportPage.playersTable.lastActivity') || 'Last Activity' }}
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">
                                {{ t('reportPage.playersTable.actions') || 'Actions' }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <template v-for="(player, index) in filteredPlayers" :key="player.id || player.student_id || index">
                            <!-- Main Student Row -->
                            <tr
                                @click="togglePlayerExpand(player)"
                                class="hover:bg-blue-50/40 transition-colors cursor-pointer group"
                                :class="{ 'bg-blue-50/30': isPlayerExpanded(player) }"
                            >
                                <!-- Expand Chevron Icon -->
                                <td class="px-3 py-4 text-center">
                                    <button
                                        type="button"
                                        class="p-1 rounded-md text-gray-400 group-hover:text-blue-600 hover:bg-blue-100/60 transition-all cursor-pointer"
                                        :aria-expanded="isPlayerExpanded(player)"
                                    >
                                        <svg
                                            class="w-4 h-4 transition-transform duration-200"
                                            :class="{ 'rotate-180 text-blue-600': isPlayerExpanded(player) }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </td>

                                <!-- Player Info -->
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
                                            class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0"
                                        >
                                            {{ (player.name || player.nickname || 'P').charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 truncate">
                                                {{ player.name || player.nickname || 'Anonymous' }}
                                            </div>
                                            <div v-if="player.student?.email" class="text-xs text-gray-400 truncate">
                                                {{ player.student.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Rank -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold"
                                        :class="{
                                            'bg-amber-100 text-amber-800 border border-amber-200': (player.rank || index + 1) === 1,
                                            'bg-gray-100 text-gray-700 border border-gray-200': (player.rank || index + 1) === 2,
                                            'bg-amber-50 text-amber-900 border border-amber-100': (player.rank || index + 1) === 3,
                                            'text-gray-700': (player.rank || index + 1) > 3,
                                        }"
                                    >
                                        #{{ player.rank || index + 1 }}
                                    </span>
                                </td>

                                <!-- Score -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-blue-600">
                                        {{ Number(player.total_score || player.score || 0).toLocaleString() }} {{ t('reportPage.playersTable.points') || 'pts' }}
                                    </div>
                                </td>

                                <!-- Correct Answers / Total -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ getCorrectCount(player) }}/{{ totalQuestionsCount }}
                                        </span>
                                        <span
                                            class="text-xs font-semibold px-1.5 py-0.5 rounded-md"
                                            :class="getAccuracyBadgeClass(getAccuracy(player))"
                                        >
                                            {{ getAccuracy(player) }}%
                                        </span>
                                    </div>
                                </td>

                                <!-- Last Activity -->
                                <td class="px-4 py-4 whitespace-nowrap text-xs text-gray-500">
                                    {{ formatDate(player.updated_at || player.created_at || player.completed_at) }}
                                </td>

                                <!-- Action Button -->
                                <td class="px-4 py-4 whitespace-nowrap text-right">
                                    <button
                                        type="button"
                                        @click.stop="togglePlayerExpand(player)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-md border transition-all cursor-pointer"
                                        :class="isPlayerExpanded(player)
                                            ? 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700'
                                            : 'bg-white text-blue-600 border-blue-200 hover:bg-blue-50'"
                                    >
                                        <span>
                                            {{ isPlayerExpanded(player) ? (t('reportPage.playersTable.hideAnswers') || 'Hide Answers') : (t('reportPage.playersTable.viewAnswers') || 'View Answers') }}
                                        </span>
                                        <svg
                                            class="w-3 h-3 transition-transform duration-200"
                                            :class="{ 'rotate-180': isPlayerExpanded(player) }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>

                            <!-- Expanded Student Answer Details Sub-Row -->
                            <tr v-if="isPlayerExpanded(player)" class="bg-gray-50/80">
                                <td colspan="7" class="p-4 sm:p-6">
                                    <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-4 sm:p-5 space-y-4">
                                        <!-- Header Ribbon: Student Name + Performance Summary Pills -->
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-bold text-gray-900">
                                                    {{ player.name || player.nickname || 'Player' }}'s Answer Sheet
                                                </span>
                                                <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                                    Rank #{{ player.rank || index + 1 }}
                                                </span>
                                            </div>

                                            <!-- Stat Pills -->
                                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                                <span class="px-2.5 py-1 rounded-md font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    {{ getCorrectCount(player) }} {{ t('reportPage.playersTable.correct') || 'Correct' }}
                                                </span>
                                                <span class="px-2.5 py-1 rounded-md font-semibold bg-rose-50 text-rose-700 border border-rose-200 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    {{ getIncorrectCount(player) }} {{ t('reportPage.playersTable.incorrect') || 'Incorrect' }}
                                                </span>
                                                <span class="px-2.5 py-1 rounded-md font-semibold bg-gray-100 text-gray-700 border border-gray-200 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                    </svg>
                                                    {{ getUnansweredCount(player) }} {{ t('reportPage.playersTable.unanswered') || 'Unanswered' }}
                                                </span>
                                                <span class="px-2.5 py-1 rounded-md font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                    {{ Number(player.total_score || player.score || 0).toLocaleString() }} {{ t('reportPage.playersTable.points') || 'pts' }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Question-by-Question Result Cards -->
                                        <div v-if="questionsList.length === 0" class="text-sm text-gray-500 py-4 text-center">
                                            No questions recorded for this exam.
                                        </div>
                                        <div v-else class="space-y-3">
                                            <div
                                                v-for="(question, qIndex) in questionsList"
                                                :key="question.id || qIndex"
                                                class="rounded-lg border transition-all p-3.5"
                                                :class="getQuestionCardBorderClass(player, question)"
                                            >
                                                <!-- Question Header -->
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                                    <div class="flex items-start gap-2 flex-1">
                                                        <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-md shrink-0">
                                                            Q{{ question.order || qIndex + 1 }}
                                                        </span>
                                                        <h5 class="text-xs sm:text-sm font-semibold text-gray-800 leading-snug">
                                                            {{ question.title || question.question_text || 'Question ' + (qIndex + 1) }}
                                                        </h5>
                                                    </div>

                                                    <!-- Result Badge & Metadata -->
                                                    <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                                                        <!-- Correct Badge -->
                                                        <span
                                                            v-if="isQuestionCorrect(player, question)"
                                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"
                                                        >
                                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                            {{ t('reportPage.playersTable.correct') || 'Correct' }}
                                                            <span v-if="getAnswerPoints(player, question) > 0" class="text-[10px] opacity-80">
                                                                (+{{ getAnswerPoints(player, question) }})
                                                            </span>
                                                        </span>

                                                        <!-- Incorrect Badge -->
                                                        <span
                                                            v-else-if="isQuestionAnswered(player, question)"
                                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200"
                                                        >
                                                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                            {{ t('reportPage.playersTable.incorrect') || 'Incorrect' }}
                                                        </span>

                                                        <!-- Unanswered Badge -->
                                                        <span
                                                            v-else
                                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200"
                                                        >
                                                            {{ t('reportPage.playersTable.unanswered') || 'Unanswered' }}
                                                        </span>

                                                        <!-- Time Taken Pill -->
                                                        <span
                                                            v-if="getAnswerDuration(player, question)"
                                                            class="text-[11px] font-mono text-gray-500 bg-gray-100/70 px-2 py-0.5 rounded-md"
                                                        >
                                                            ⏱ {{ getAnswerDuration(player, question) }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Question Answers Display -->
                                                <div class="mt-2.5 pt-2.5 border-t border-gray-100 text-xs grid grid-cols-1 md:grid-cols-2 gap-2">
                                                    <!-- Student's Choice -->
                                                    <div
                                                        class="p-2.5 rounded-lg flex items-start gap-2"
                                                        :class="isQuestionCorrect(player, question)
                                                            ? 'bg-emerald-50/70 border border-emerald-200 text-emerald-900'
                                                            : (isQuestionAnswered(player, question)
                                                                ? 'bg-rose-50/70 border border-rose-200 text-rose-900'
                                                                : 'bg-gray-50 border border-dashed border-gray-200 text-gray-500')"
                                                    >
                                                        <span class="font-bold shrink-0">
                                                            {{ t('reportPage.playersTable.studentAnswer') || "Student's Answer" }}:
                                                        </span>
                                                        <span class="font-medium break-words">
                                                            {{ getStudentAnswerText(player, question) || (t('reportPage.playersTable.noAnswerSubmitted') || 'No answer submitted') }}
                                                        </span>
                                                    </div>

                                                    <!-- Correct Choice (when incorrect or unanswered) -->
                                                    <div
                                                        v-if="!isQuestionCorrect(player, question) && getCorrectAnswerText(question)"
                                                        class="p-2.5 rounded-lg bg-emerald-50/80 border border-emerald-200 text-emerald-900 flex items-start gap-2"
                                                    >
                                                        <span class="font-bold text-emerald-800 shrink-0">
                                                            ✓ {{ t('reportPage.playersTable.correctAnswer') || 'Correct Answer' }}:
                                                        </span>
                                                        <span class="font-semibold text-emerald-900 break-words">
                                                            {{ getCorrectAnswerText(question) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer: Collapse Button -->
                                        <div class="flex justify-end pt-2">
                                            <button
                                                type="button"
                                                @click="togglePlayerExpand(player)"
                                                class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors cursor-pointer"
                                            >
                                                {{ t('reportPage.playersTable.hideAnswers') || 'Hide Answers' }} ↑
                                            </button>
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
    name: 'AllPlayersTab',
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
            searchQuery: '',
        }
    },
    computed: {
        sortedPlayers() {
            return [...(this.players || [])].sort((a, b) => {
                const scoreB = Number(b.total_score || b.score || 0)
                const scoreA = Number(a.total_score || a.score || 0)
                if (scoreB !== scoreA) return scoreB - scoreA
                const rankA = Number(a.rank || a.current_rank || 9999)
                const rankB = Number(b.rank || b.current_rank || 9999)
                return rankA - rankB
            })
        },
        filteredPlayers() {
            if (!this.searchQuery.trim()) return this.sortedPlayers
            const query = this.searchQuery.trim().toLowerCase()
            return this.sortedPlayers.filter(p => {
                const name = String(p.name || p.nickname || '').toLowerCase()
                const email = String(p.student?.email || '').toLowerCase()
                return name.includes(query) || email.includes(query)
            })
        },
        questionsList() {
            const raw = this.exam.questions || this.exam.exam?.questions || []
            return Array.isArray(raw) ? raw : Object.values(raw)
        },
        totalQuestionsCount() {
            return this.questionsList.length || this.exam.total_questions || 1
        },
        isAllExpanded() {
            return this.filteredPlayers.length > 0 &&
                this.filteredPlayers.every(p => this.isPlayerExpanded(p))
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
            // Trigger reactivity for Set
            this.expandedPlayerIds = new Set(this.expandedPlayerIds)
        },
        toggleExpandAll() {
            if (this.isAllExpanded) {
                this.expandedPlayerIds.clear()
            } else {
                this.filteredPlayers.forEach(p => this.expandedPlayerIds.add(this.getPlayerKey(p)))
            }
            this.expandedPlayerIds = new Set(this.expandedPlayerIds)
        },
        getPlayerAnswer(player, question) {
            if (!player) return null
            const answers = player.answered_questions || player.answers || []
            if (!Array.isArray(answers) || answers.length === 0) return null

            return answers.find(a => {
                if (a.question_id !== undefined && a.question_id !== null && question?.id !== undefined) {
                    if (String(a.question_id) === String(question.id)) return true
                }
                if (a.id !== undefined && a.id !== null && question?.id !== undefined) {
                    if (String(a.id) === String(question.id)) return true
                }
                if (a.question_index !== undefined && a.question_index !== null && question?.order !== undefined) {
                    if (Number(a.question_index) === Number(question.order - 1)) return true
                }
                return false
            })
        },
        isQuestionAnswered(player, question) {
            const ans = this.getPlayerAnswer(player, question)
            return ans !== null && ans !== undefined
        },
        isQuestionCorrect(player, question) {
            const ans = this.getPlayerAnswer(player, question)
            if (!ans) return false
            if (ans.is_correct === true || ans.is_correct === 1 || ans.is_correct === '1') return true
            if (ans.selected_option && (ans.selected_option.correct === true || ans.selected_option.is_correct === true)) return true
            return false
        },
        getStudentAnswerText(player, question) {
            const ans = this.getPlayerAnswer(player, question)
            if (!ans) return null
            if (typeof ans.answer === 'string' && ans.answer.trim()) {
                return ans.answer.trim()
            }
            if (ans.selected_option) {
                if (typeof ans.selected_option === 'string') return ans.selected_option
                if (ans.selected_option.text) return ans.selected_option.text
                if (ans.selected_option.title) return ans.selected_option.title
            }
            // Check selected_option_id mapped to question.options
            if (ans.selected_option_id !== undefined && ans.selected_option_id !== null && question?.options) {
                const opts = Array.isArray(question.options) ? question.options : []
                const matched = opts.find(o => String(o.id) === String(ans.selected_option_id))
                if (matched) return matched.text || matched.title || `Option ${ans.selected_option_id}`
            }
            if (ans.answer_text) return ans.answer_text
            return 'Answer submitted'
        },
        getCorrectAnswerText(question) {
            if (!question) return null
            const opts = Array.isArray(question.options)
                ? question.options
                : (typeof question.options === 'string' ? (JSON.parse(question.options) || []) : [])
            const correctOpt = opts.find(o => o.correct === true || o.is_correct === true)
            return correctOpt ? (correctOpt.text || correctOpt.title || null) : null
        },
        getAnswerDuration(player, question) {
            const ans = this.getPlayerAnswer(player, question)
            if (!ans) return null
            if (ans.time !== undefined && ans.time !== null) return `${Number(ans.time).toFixed(1)}s`
            if (ans.answered_duration !== undefined && ans.answered_duration !== null) return `${Number(ans.answered_duration).toFixed(1)}s`
            if (ans.answer_time_ms) return `${(Number(ans.answer_time_ms) / 1000).toFixed(1)}s`
            return null
        },
        getAnswerPoints(player, question) {
            const ans = this.getPlayerAnswer(player, question)
            if (!ans) return 0
            if (ans.points_earned !== undefined && ans.points_earned !== null) return Number(ans.points_earned)
            if (ans.selected_option?.score !== undefined) return Number(ans.selected_option.score)
            return this.isQuestionCorrect(player, question) ? 1000 : 0
        },
        getQuestionCardBorderClass(player, question) {
            if (this.isQuestionCorrect(player, question)) {
                return 'bg-emerald-50/20 border-emerald-200'
            }
            if (this.isQuestionAnswered(player, question)) {
                return 'bg-rose-50/20 border-rose-200'
            }
            return 'bg-gray-50/40 border-gray-200'
        },
        getCorrectCount(player) {
            if (Number.isFinite(Number(player.correct_answers))) {
                return Number(player.correct_answers)
            }
            const answers = player.answered_questions || player.answers || []
            return answers.filter(a => a.is_correct === true || a.selected_option?.correct === true).length
        },
        getIncorrectCount(player) {
            const total = this.totalQuestionsCount
            const correct = this.getCorrectCount(player)
            const answered = (player.answered_questions || player.answers || []).length
            return Math.max(0, answered - correct)
        },
        getUnansweredCount(player) {
            const total = this.totalQuestionsCount
            const answered = (player.answered_questions || player.answers || []).length
            return Math.max(0, total - answered)
        },
        getAccuracy(player) {
            const total = this.totalQuestionsCount || 1
            const correct = this.getCorrectCount(player)
            return Math.round((correct / total) * 100)
        },
        getAccuracyBadgeClass(accuracy) {
            if (accuracy >= 80) return 'bg-emerald-100 text-emerald-800'
            if (accuracy >= 50) return 'bg-blue-100 text-blue-800'
            if (accuracy >= 30) return 'bg-amber-100 text-amber-800'
            return 'bg-rose-100 text-rose-800'
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A'
            try {
                const locale = this.$i18n.locale === 'fa' ? 'fa-IR' : 'en-US'
                return new Date(String(dateString).replace(' ', 'T')).toLocaleString(locale, {
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })
            } catch (e) {
                return dateString
            }
        }
    }
}
</script>
