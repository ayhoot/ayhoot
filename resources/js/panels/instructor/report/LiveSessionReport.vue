<template>
    <InstructorLayout :secondary="false">
        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-10">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-blue-500"></div>
            <p class="mt-4 text-lg text-gray-600">{{ t('reportPage.loading') }}</p>
        </div>

        <!-- Error State -->
        <div v-else-if="fetchError" class="text-center py-10 bg-red-50 p-6 rounded-md shadow-sm">
            <p class="text-xl text-red-700 font-semibold">{{ t('reportPage.error.title') }}</p>
            <p class="text-md text-red-600 mt-2">{{ fetchError }}</p>
            <AyButton type="dash-blue" @click="fetchReport" class="mt-6 px-6 py-2 inline-flex">
                {{ t('reportPage.error.retry') }}
            </AyButton>
        </div>

        <!-- Main Content when report is loaded -->
        <template v-else-if="report">
            <div class="grid grid-cols-1 lg:grid-cols-4 w-full gap-8">
                <!-- 1st Col: Header, Title, Actions -->
                <div class="lg:col-span-3 flex flex-col justify-between">
                    <div class="pb-4">
                        <div class="flex items-center justify-between py-2">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-gray-500">{{ t('reportPage.header.report') }}</h3>
                                <span class="px-2 py-0.5 text-xs font-bold rounded-md bg-purple-100 text-purple-700 border border-purple-200">
                                    Host Live
                                </span>
                                <span v-if="report.session?.pin" class="text-xs font-mono font-bold bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md">
                                    PIN: {{ report.session.pin }}
                                </span>
                            </div>
                            <ResponsiveButton type="light-gray" @click="exportPdf" class="p-2 flex items-center gap-1 cursor-pointer">
                                <span>
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M4 15.2044L4 18.8925C4 19.4514 4.21071 19.9875 4.58579 20.3827C4.96086 20.778 5.46957 21 6 21H18C18.5304 21 19.0391 20.778 19.4142 20.3827C19.7893 19.9875 20 19.4514 20 18.8925V15.2044M12.0011 3V14.9425M12.0011 14.9425L16.5725 10.3793M12.0011 14.9425L7.42969 10.3793"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        />
                                    </svg>
                                </span>
                                <span>{{ t('reportPage.header.download') }}</span>
                            </ResponsiveButton>
                        </div>
                        <div class="flex items-center gap-x-2">
                            <div v-if="isEditingTitle || !report.title" class="flex items-center gap-2 w-full">
                                <input
                                    type="text"
                                    v-model="editableTitle"
                                    :placeholder="t('reportPage.title.placeholder')"
                                    class="flex-grow px-4 py-2 border-2 border-gray-300 rounded-md focus:outline-none text-2xl font-semibold"
                                    @keyup.enter="saveTitle"
                                    ref="titleInput"
                                />
                                <ResponsiveButton type="light-blue" @click="saveTitle" :disabled="isSavingTitle" class="p-3">
                                    <span v-if="isSavingTitle">{{ t('reportPage.title.saving') }}</span>
                                    <span v-else>{{ t('reportPage.title.save') }}</span>
                                </ResponsiveButton>
                                <ResponsiveButton type="light-gray" v-if="report.title" @click="cancelEditTitle" class="p-3">
                                    {{ t('reportPage.title.cancel') }}
                                </ResponsiveButton>
                            </div>
                            <div v-else class="flex items-center gap-3">
                                <p class="text-3xl font-semibold text-gray-900 py-2">{{ report.title }}</p>
                                <button
                                    @click="startEditTitle"
                                    class="p-1 text-blue-600 hover:text-blue-800 cursor-pointer"
                                    :title="t('reportPage.title.editTitle')"
                                >
                                    <DashEdit />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 2nd Col: Status, Completed Date, Host -->
                <div>
                    <div class="flex flex-col w-full text-sm">
                        <div class="flex items-center gap-2 w-full border-b border-gray-600 py-2">
                            <h3 class="font-medium">{{ t('reportPage.info.status') }}</h3>
                            <span class="text-xs font-semibold rounded-md px-2 py-0.5 bg-green-200 text-green-700">
                                {{ t('reportPage.status.finished') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1 w-full border-b border-gray-600 py-2">
                            <h3 class="font-medium">{{ t('reportPage.info.dueDate') }}</h3>
                            <h3>{{ formatDate(report.end_at || report.finished_at) }}</h3>
                        </div>
                        <div class="flex items-center gap-1 w-full border-b border-gray-600 py-2">
                            <h3 class="font-medium">{{ t('reportPage.info.hostedBy') }}</h3>
                            <h3>{{ report.host }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                    <li :class="$i18n.locale === 'fa' ? 'ml-2' : 'me-2'">
                        <button
                            @click="activeTab = 'summary'"
                            :class="activeTab === 'summary' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent'"
                            class="inline-block p-4 border-b-2 rounded-t-lg cursor-pointer font-semibold"
                        >
                            {{ t('reportPage.tabs.summary') }}
                        </button>
                    </li>
                    <li :class="$i18n.locale === 'fa' ? 'ml-2' : 'me-2'">
                        <button
                            @click="activeTab = 'players'"
                            :class="activeTab === 'players' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent'"
                            class="inline-block p-4 border-b-2 rounded-t-lg cursor-pointer font-semibold"
                        >
                            {{ t('reportPage.tabs.players', { count: report.players?.length || 0 }) }}
                        </button>
                    </li>
                    <li :class="$i18n.locale === 'fa' ? 'ml-2' : 'me-2'">
                        <button
                            @click="activeTab = 'questions'"
                            :class="activeTab === 'questions' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent'"
                            class="inline-block p-4 border-b-2 rounded-t-lg cursor-pointer font-semibold"
                        >
                            {{ t('reportPage.tabs.questions', { count: report.questions?.length || 0 }) }}
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <!-- Tab 1: Summary (Podium Leaderboard, Metrics, Difficult Questions, Need Help, Didn't Finish) -->
            <div v-show="activeTab === 'summary'" class="py-4 w-full">
                <SummaryTab
                    :exam="report"
                    :is-live="true"
                    :format-duration="formatDuration"
                    :get-chart-data-fn="getChartDataForQuestion"
                    :get-correct-percentage-fn="getCorrectPercentageForQuestion"
                    @deadline-changed="fetchReport"
                />
            </div>

            <!-- Tab 2: Players (All Players DataTable, Didn't Finish DataTable) -->
            <div v-show="activeTab === 'players'" class="py-4 w-full">
                <ul class="flex flex-wrap text-sm font-medium text-center border-b border-gray-200 rounded-t-xl bg-gray-100">
                    <li :class="$i18n.locale === 'fa' ? 'ml-2' : 'me-2'">
                        <button
                            @click="activeSubTab = 'all-users'"
                            :class="activeSubTab === 'all-users' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent'"
                            class="inline-block p-4 px-4 border-b-2 rounded-t-lg cursor-pointer font-semibold"
                        >
                            {{ t('reportPage.playersSubTabs.all', { count: report.players?.length || 0 }) }}
                        </button>
                    </li>
                    <li :class="$i18n.locale === 'fa' ? 'ml-2' : 'me-2'">
                        <button
                            @click="activeSubTab = 'not-finished'"
                            :class="activeSubTab === 'not-finished' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent'"
                            class="inline-block p-4 px-4 border-b-2 rounded-t-lg cursor-pointer font-semibold"
                        >
                            {{ t('reportPage.playersSubTabs.notFinished', { count: report.not_finished?.length || 0 }) }}
                        </button>
                    </li>
                </ul>
                <div v-show="activeSubTab === 'all-users'" class="w-full">
                    <AllPlayersTab :players="report.players || []" :exam="report" />
                </div>
                <div v-show="activeSubTab === 'not-finished'" class="w-full">
                    <NotFinishedTab :players="report.not_finished || []" :exam="report" />
                </div>
            </div>

            <!-- Tab 3: Questions (Questions list with Doughnut accuracy chart and Show Result modal) -->
            <div v-show="activeTab === 'questions'" class="py-4 w-full">
                <ul class="flex flex-wrap text-sm font-medium text-center border-b border-gray-200 rounded-t-xl bg-gray-100">
                    <li :class="$i18n.locale === 'fa' ? 'ml-2' : 'me-2'">
                        <button
                            @click="activeQTab = 'all-questions'"
                            :class="activeQTab === 'all-questions' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent'"
                            class="inline-block p-4 px-4 border-b-2 rounded-t-lg cursor-pointer font-semibold"
                        >
                            {{ t('reportPage.questionsSubTabs.all', { count: report.questions?.length || 0 }) }}
                        </button>
                    </li>
                </ul>
                <div v-show="activeQTab === 'all-questions'" class="w-full">
                    <AllQuestionsTab :exam="report" />
                </div>
            </div>

            <!-- Enhanced Printable Canvas for PDF Export -->
            <div ref="printCanvas" class="report-printable hidden bg-white p-8 space-y-6">
                <!-- PDF Header -->
                <div class="border-b-2 border-gray-900 pb-4 flex justify-between items-start">
                    <div>
                        <div class="text-xs font-black uppercase tracking-widest text-purple-600 mb-1">
                            Ayhoot Live Session Report
                        </div>
                        <h1 class="text-2xl font-black text-gray-900">{{ report.title }}</h1>
                        <p class="text-xs text-gray-600 mt-1">
                            <span v-if="report.session?.pin">PIN: {{ report.session.pin }} • </span>
                            Completed: {{ formatDate(report.end_at || report.finished_at) }} • Host: {{ report.host }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-black text-gray-900">{{ report.players?.length || 0 }}</div>
                        <div class="text-xs uppercase text-gray-500 font-bold">Total Players</div>
                    </div>
                </div>

                <!-- High-level stats -->
                <div class="grid grid-cols-4 gap-3">
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-center">
                        <div class="text-xs text-gray-500 font-bold uppercase">Players</div>
                        <div class="text-lg font-black text-gray-900 mt-0.5">{{ report.players?.length || 0 }}</div>
                    </div>
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-center">
                        <div class="text-xs text-gray-500 font-bold uppercase">Questions</div>
                        <div class="text-lg font-black text-gray-900 mt-0.5">{{ report.questions?.length || 0 }}</div>
                    </div>
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-center">
                        <div class="text-xs text-gray-500 font-bold uppercase">Duration</div>
                        <div class="text-lg font-black text-gray-900 mt-0.5">{{ formatDuration(report.duration) }}</div>
                    </div>
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-center">
                        <div class="text-xs text-gray-500 font-bold uppercase">Avg Score</div>
                        <div class="text-lg font-black text-emerald-600 mt-0.5">{{ report.avg_score || 0 }}%</div>
                    </div>
                </div>

                <!-- Top 3 Podium for Print -->
                <div v-if="report.players?.length" class="border border-amber-300 bg-amber-50/50 rounded-xl p-4">
                    <div class="text-xs font-black uppercase text-amber-700 tracking-wider mb-2">Final Podium</div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div v-if="report.players[1]" class="p-2 bg-white rounded-lg border border-gray-200">
                            <span class="font-bold text-slate-600 block">🥈 2nd Place</span>
                            <span class="font-black text-gray-900 block mt-1">{{ report.players[1].name }}</span>
                            <span class="font-mono text-blue-600 font-bold block">{{ report.players[1].total_score ?? report.players[1].score }} pts</span>
                        </div>
                        <div v-if="report.players[0]" class="p-2 bg-white rounded-lg border-2 border-amber-400 shadow-xs">
                            <span class="font-extrabold text-amber-600 block">👑 Champion (1st)</span>
                            <span class="font-black text-gray-900 block mt-1">{{ report.players[0].name }}</span>
                            <span class="font-mono text-emerald-600 font-bold block">{{ report.players[0].total_score ?? report.players[0].score }} pts</span>
                        </div>
                        <div v-if="report.players[2]" class="p-2 bg-white rounded-lg border border-gray-200">
                            <span class="font-bold text-orange-600 block">🥉 3rd Place</span>
                            <span class="font-black text-gray-900 block mt-1">{{ report.players[2].name }}</span>
                            <span class="font-mono text-blue-600 font-bold block">{{ report.players[2].total_score ?? report.players[2].score }} pts</span>
                        </div>
                    </div>
                </div>

                <!-- Players Table for Print -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 mb-2 uppercase">Participant Standings</h3>
                    <table class="w-full text-xs text-left border border-gray-200">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="p-2 border">#</th>
                                <th class="p-2 border">Player</th>
                                <th class="p-2 border text-center">Score</th>
                                <th class="p-2 border text-center">Correct Answers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(p, i) in report.players" :key="p.id || i">
                                <td class="p-2 border font-bold text-center">{{ i + 1 }}</td>
                                <td class="p-2 border font-semibold">{{ p.name }}</td>
                                <td class="p-2 border text-center font-mono font-bold text-blue-600">{{ p.total_score ?? p.score }} pts</td>
                                <td class="p-2 border text-center">{{ p.correct_answers }}/{{ report.questions?.length || 0 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Questions Summary for Print -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 mb-2 uppercase">Questions Breakdown</h3>
                    <table class="w-full text-xs text-left border border-gray-200">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="p-2 border w-12 text-center">#</th>
                                <th class="p-2 border">Question</th>
                                <th class="p-2 border text-center">Type</th>
                                <th class="p-2 border text-center">Accuracy</th>
                                <th class="p-2 border text-center">Correct</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(q, qi) in report.questions" :key="q.id || qi">
                                <td class="p-2 border font-bold text-center">{{ qi + 1 }}</td>
                                <td class="p-2 border font-medium">{{ stripHtml(q.title) }}</td>
                                <td class="p-2 border text-center uppercase">{{ (q.type || '').replace('_', ' ') }}</td>
                                <td class="p-2 border text-center font-bold" :class="Number(getCorrectPercentageForQuestion(q)) >= 70 ? 'text-emerald-600' : 'text-red-500'">
                                    {{ getCorrectPercentageForQuestion(q) }}%
                                </td>
                                <td class="p-2 border text-center">{{ getCorrectAnswerCount(q) }}/{{ report.players?.length || 0 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </InstructorLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toast-notification'
import InstructorLayout from '@/components/layout/InstructorLayout.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import AllPlayersTab from '@/components/reports/AllPlayersTab.vue'
import AllQuestionsTab from '@/components/reports/AllQuestionsTab.vue'
import NotFinishedTab from '@/components/reports/NotFinishedTab.vue'
import SummaryTab from '@/components/reports/SummaryTab.vue'
import DashEdit from '@/components/atoms/icons/DashEdit.vue'

export default {
    name: 'LiveSessionReport',
    components: {
        InstructorLayout,
        ResponsiveButton,
        AyButton,
        AllPlayersTab,
        AllQuestionsTab,
        NotFinishedTab,
        SummaryTab,
        DashEdit,
    },
    setup() {
        const { t } = useI18n()
        const $toast = useToast()
        return { t, $toast }
    },
    data() {
        return {
            sessionId: null,
            report: null,
            isLoading: true,
            fetchError: null,
            isEditingTitle: false,
            editableTitle: '',
            isSavingTitle: false,
            activeTab: 'summary',
            activeSubTab: 'all-users',
            activeQTab: 'all-questions',
        }
    },
    methods: {
        async fetchReport() {
            if (!this.sessionId) {
                this.fetchError = this.t('reportPage.notifications.idMissing') || 'Session ID missing'
                this.isLoading = false
                return
            }
            this.isLoading = true
            this.fetchError = null
            try {
                const response = await apiCall('GET', `/api/live-session/${this.sessionId}/report`)
                this.report = response
                this.editableTitle = this.report.title || ''
                if (!this.report.title) {
                    this.isEditingTitle = true
                    this.$nextTick(() => this.$refs.titleInput?.focus())
                }

                // If opened with ?export=pdf, automatically launch print dialog
                if (this.$route.query.export === 'pdf') {
                    this.$nextTick(() => {
                        setTimeout(() => this.exportPdf(), 400)
                    })
                }
            } catch (error) {
                console.error('Error fetching live session report:', error)
                this.fetchError = error.response?.data?.message || this.t('reportPage.notifications.fetchFailed') || 'Failed to load report'
            } finally {
                this.isLoading = false
            }
        },
        startEditTitle() {
            this.editableTitle = this.report.title
            this.isEditingTitle = true
            this.$nextTick(() => this.$refs.titleInput?.focus())
        },
        cancelEditTitle() {
            this.isEditingTitle = false
        },
        async saveTitle() {
            if (this.isSavingTitle || !this.editableTitle.trim()) return
            this.isSavingTitle = true
            try {
                await apiCall('POST', `/api/live-session/${this.sessionId}/update-config`, {
                    title: this.editableTitle.trim(),
                })
                this.report.title = this.editableTitle.trim()
                this.isEditingTitle = false
            } catch (error) {
                console.error('Error saving title:', error)
                this.$toast.error(error.response?.data?.message || this.t('reportPage.notifications.saveTitleFailed') || 'Failed to save title')
            } finally {
                this.isSavingTitle = false
            }
        },
        formatDate(dateString) {
            if (!dateString) return this.t('reportPage.notAvailable') || 'N/A'
            try {
                const locale = this.$i18n.locale === 'fa' ? 'fa-IR' : 'en-US'
                return new Date(String(dateString).replace(' ', 'T')).toLocaleString(locale, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true,
                })
            } catch (e) {
                return dateString
            }
        },
        formatDuration(seconds) {
            const duration = Number(seconds)
            if (!Number.isFinite(duration) || duration < 0) return '0s'

            const wholeSeconds = Math.floor(duration)
            const hours = Math.floor(wholeSeconds / 3600)
            const minutes = Math.floor((wholeSeconds % 3600) / 60)
            const remainingSeconds = wholeSeconds % 60
            if (hours > 0) {
                return `${hours}h ${minutes}m ${remainingSeconds}s`
            }
            return minutes > 0 ? `${minutes}m ${remainingSeconds}s` : `${remainingSeconds}s`
        },
        getCorrectAnswerCount(question) {
            if (Number.isFinite(Number(question?.correct_answers))) {
                return Math.max(0, Number(question.correct_answers))
            }

            if (!Array.isArray(question?.tutees_answers)) return 0
            return question.tutees_answers.reduce((total, answer) => {
                const isCorrect = answer?.selected_option?.correct ?? answer?.is_correct
                return total + (isCorrect === true ? 1 : 0)
            }, 0)
        },
        getCorrectPercentageForQuestion(question) {
            const totalPlayers = this.report?.players?.length || 0
            if (totalPlayers === 0) return '0.0'
            return ((this.getCorrectAnswerCount(question) / totalPlayers) * 100).toFixed(1)
        },
        getChartDataForQuestion(question) {
            const correct = this.getCorrectAnswerCount(question)
            const totalPlayers = this.report?.players?.length || 0
            return {
                labels: ['Correct', 'Incorrect'],
                datasets: [{
                    data: [correct, Math.max(0, totalPlayers - correct)],
                    backgroundColor: ['#4ade80', '#f87171'],
                    borderWidth: 0,
                }],
            }
        },
        stripHtml(html) {
            if (!html) return ''
            const tmp = document.createElement('div')
            tmp.innerHTML = html
            return tmp.textContent || tmp.innerText || ''
        },
        exportPdf() {
            if (!this.report) return
            const titleEl = document.title
            document.title = (this.report.title || 'Live Session Report') + ' — Report'
            document.body.classList.add('printing-report')
            const cleanup = () => {
                document.body.classList.remove('printing-report')
                document.title = titleEl
                window.removeEventListener('afterprint', cleanup)
            }
            window.addEventListener('afterprint', cleanup)
            setTimeout(() => window.print(), 100)
        },
    },
    created() {
        this.sessionId = this.$route.params.sessionId
        if (this.sessionId) {
            this.fetchReport()
        } else {
            this.isLoading = false
            this.fetchError = this.t('reportPage.notifications.idMissingUrl') || 'Session ID missing in URL'
        }
    },
}
</script>

<style>
@media print {
    body.printing-report * {
        visibility: hidden !important;
    }
    body.printing-report .report-printable,
    body.printing-report .report-printable * {
        visibility: visible !important;
    }
    body.printing-report .report-printable {
        display: block !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 24px !important;
        background: white !important;
        border: none !important;
        box-shadow: none !important;
    }
    body.printing-report .report-printable table,
    body.printing-report .report-printable tr {
        break-inside: avoid !important;
        page-break-inside: avoid !important;
    }
}
</style>
