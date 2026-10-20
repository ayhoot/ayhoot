<template>
    <InstructorLayout :secondary="false">
                <!-- Loading State -->
                <div v-if="isLoading" class="text-center py-10">
                    <div
                        class="inline-block animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-blue-500">
                    </div>
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
                        <!-- 1st Col -->
                        <div class="lg:col-span-3 flex flex-col justify-between">
                            <div class="pb-4">
                                <div class="flex items-center justify-between py-2">
                                    <h3 class="font-semibold text-gray-500">{{ t('reportPage.header.report') }}</h3>
                                    <ResponsiveButton type="light-gray" class="p-2 flex items-center gap-1">
                                        <span>
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M4 15.2044L4 18.8925C4 19.4514 4.21071 19.9875 4.58579 20.3827C4.96086 20.778 5.46957 21 6 21H18C18.5304 21 19.0391 20.778 19.4142 20.3827C19.7893 19.9875 20 19.4514 20 18.8925V15.2044M12.0011 3V14.9425M12.0011 14.9425L16.5725 10.3793M12.0011 14.9425L7.42969 10.3793"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <span>{{ t('reportPage.header.download') }}</span>
                                    </ResponsiveButton>
                                </div>
                                <div class="flex items-center gap-x-2">
                                    <div v-if="isEditingTitle || !report.title"
                                        class="flex items-center gap-2 w-full">
                                        <input type="text" v-model="editableTitle"
                                            :placeholder="t('reportPage.title.placeholder')"
                                            class="flex-grow px-4 py-2 border-2 border-gray-300 rounded-md focus:outline-none text-2xl font-semibold"
                                            @keyup.enter="saveTitle" ref="titleInput" />
                                        <ResponsiveButton type="light-blue" @click="saveTitle"
                                            :disabled="isSavingTitle" class="p-3">
                                            <span v-if="isSavingTitle">{{ t('reportPage.title.saving') }}</span>
                                            <span v-else>{{ t('reportPage.title.save') }}</span>
                                        </ResponsiveButton>
                                        <ResponsiveButton type="light-gray" v-if="report.title"
                                            @click="cancelEditTitle" class="p-3">
                                            {{ t('reportPage.title.cancel') }}
                                        </ResponsiveButton>
                                    </div>
                                    <div v-else class="flex items-center gap-3">
                                        <p class="text-3xl font-semibold text-gray-900 py-2">{{ report.title }}</p>
                                        <button @click="startEditTitle"
                                            class="p-1 text-blue-600 hover:text-blue-800 cursor-pointer"
                                            :title="t('reportPage.title.editTitle')">
                                            <DashEdit />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- 2nd Col -->
                        <div>
                            <div class="flex flex-col w-full text-sm">
                                <div class="flex items-center gap-2 w-full border-b border-gray-600 py-2">
                                    <h3 class="font-medium">{{ t('reportPage.info.status') }}</h3>
                                    <span v-if="gameStatus" class="text-xs font-semibold rounded-md px-2 py-0.5"
                                        :class="[gameStatus.bgColor, gameStatus.textColor]">
                                        {{ t(gameStatus.textKey) }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 w-full border-b border-gray-600 py-2">
                                    <h3 class="font-medium">{{ t('reportPage.info.dueDate') }}</h3>
                                    <h3 class="">{{ formatDate(report.end_at) }}</h3>
                                </div>
                                <div class="flex items-center gap-1 w-full border-b border-gray-600 py-2">
                                    <h3 class="font-medium">{{ t('reportPage.info.hostedBy') }}</h3>
                                    <h3 class="">{{ report.host }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                            <li :class="$i18n.locale === 'fa' ? 'ml-2' : 'me-2'">
                                <button @click="activeTab = 'summary'" :class="
                                    activeTab === 'summary'
                                        ? 'text-blue-600 border-blue-600'
                                        : 'text-gray-500 border-transparent'
                                " class="inline-block p-4 border-b-2 rounded-t-lg cursor-pointer font-semibold">
                                    {{ t('reportPage.tabs.summary') }}
                                </button>
                            </li>
                            <li :class="$i18n.locale === 'fa' ? 'ml-2' : 'me-2'">
                                <button @click="activeTab = 'players'" :class="
                                    activeTab === 'players'
                                        ? 'text-blue-600 border-blue-600'
                                        : 'text-gray-500 border-transparent'
                                " class="inline-block p-4 border-b-2 rounded-t-lg cursor-pointer font-semibold">
                                    {{ t('reportPage.tabs.players', { count: report.players.length }) }}
                                </button>
                            </li>
                            <li :class="$i18n.locale === 'fa' ? 'ml-2' : 'me-2'">
                                <button @click="activeTab = 'questions'" :class="
                                    activeTab === 'questions'
                                        ? 'text-blue-600 border-blue-600'
                                        : 'text-gray-500 border-transparent'
                                " class="inline-block p-4 border-b-2 rounded-t-lg cursor-pointer font-semibold">
                                    {{ t('reportPage.tabs.questions', { count: report.questions.length }) }}
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab Content -->
                    <div v-show="activeTab === 'summary'" class="py-4 w-full">
                        <SummaryTab
                            :exam="report"
                            :format-duration="formatDuration"
                            :get-chart-data-fn="getChartDataForQuestion"
                            :get-correct-percentage-fn="getCorrectPercentageForQuestion"
                            @deadline-changed="fetchReport"
                        />
                    </div>
                    <div v-show="activeTab === 'players'" class="py-4 w-full">
                        <ul
                            class="flex flex-wrap text-sm font-medium text-center border-b border-gray-200 rounded-t-xl bg-gray-100">
                            <li :class="$i18n.locale === 'fa' ? 'ml-2' : 'me-2'">
                                <button @click="aciveSubTab = 'all-users'" :class="
                                    aciveSubTab === 'all-users'
                                        ? 'text-blue-600 border-blue-600'
                                        : 'text-gray-500 border-transparent'
                                " class="inline-block p-4 px-4 border-b-2 rounded-t-lg cursor-pointer font-semibold">
                                    {{ t('reportPage.playersSubTabs.all', { count: report.players.length }) }}
                                </button>
                            </li>
                            <li :class="$i18n.locale === 'fa' ? 'ml-2' : 'me-2'">
                                <button @click="aciveSubTab = 'not-finished'" :class="
                                    aciveSubTab === 'not-finished'
                                        ? 'text-blue-600 border-blue-600'
                                        : 'text-gray-500 border-transparent'
                                " class="inline-block p-4 px-4 border-b-2 rounded-t-lg cursor-pointer font-semibold">
                                    {{ t('reportPage.playersSubTabs.notFinished', { count: report.not_finished.length })
                                    }}
                                </button>
                            </li>
                        </ul>
                        <div v-show="aciveSubTab === 'all-users'" class="w-full">
                            <AllPlayersTab :players="report.players" :exam="report" />
                        </div>
                        <div v-show="aciveSubTab === 'not-finished'" class="w-full">
                            <NotFinishedTab :players="report.not_finished" :exam="report" />
                        </div>
                    </div>
                    <div v-show="activeTab === 'questions'" class="py-4 w-full">
                        <ul
                            class="flex flex-wrap text-sm font-medium text-center border-b border-gray-200 rounded-t-xl bg-gray-100">
                            <li :class="$i18n.locale === 'fa' ? 'ml-2' : 'me-2'">
                                <button @click="activeQTab = 'all-questions'" :class="
                                    activeQTab === 'all-questions'
                                        ? 'text-blue-600 border-blue-600'
                                        : 'text-gray-500 border-transparent'
                                " class="inline-block p-4 px-4 border-b-2 rounded-t-lg cursor-pointer font-semibold">
                                    {{ t('reportPage.questionsSubTabs.all', { count: report.questions.length }) }}
                                </button>
                            </li>
                        </ul>
                        <div v-show="activeQTab === 'all-questions'" class="w-full">
                            <AllQuestionsTab :exam="report" />
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
    name: 'AssignedAyhoot',
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
            gameId: null,
            report: null,
            isLoading: true,
            fetchError: null,
            isEditingTitle: false,
            editableTitle: '',
            isSavingTitle: false,
            activeTab: 'summary',
            aciveSubTab: 'all-users',
            activeQTab: 'all-questions',
        }
    },
    computed: {
        gameStatus() {
            if (!this.report || !this.report.end_at) {
                return { textKey: 'reportPage.status.unknown', bgColor: 'bg-gray-200', textColor: 'text-gray-700' }
            }
            const endDate = new Date(this.report.end_at.replace(' ', 'T'))
            return endDate < new Date()
                ? { textKey: 'reportPage.status.finished', bgColor: 'bg-green-200', textColor: 'text-green-700' }
                : { textKey: 'reportPage.status.inProgress', bgColor: 'bg-yellow-200', textColor: 'text-yellow-700' }
        },
    },
    methods: {
        async fetchReport() {
            if (!this.gameId) {
                this.fetchError = this.t('reportPage.notifications.idMissing')
                this.isLoading = false
                return
            }
            this.isLoading = true
            this.fetchError = null
            try {
                this.report = await apiCall('GET', `/api/instructor/game/${this.gameId}/report`)
                this.editableTitle = this.report.title || ''
                if (!this.report.title) {
                    this.isEditingTitle = true
                    this.$nextTick(() => this.$refs.titleInput?.focus())
                }
            } catch (error) {
                console.error('Error fetching report:', error)
                this.fetchError = error.response?.data?.message || this.t('reportPage.notifications.fetchFailed')
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
                const response = await apiCall('POST', `/api/instructor/game/${this.gameId}`, {
                    title: this.editableTitle.trim(),
                })
                this.report.title = response.game.title
                this.isEditingTitle = false
            } catch (error) {
                console.error('Error saving title:', error)
                this.$toast.error(error.response?.data?.message || this.t('reportPage.notifications.saveTitleFailed'))
            } finally {
                this.isSavingTitle = false
            }
        },
        formatDate(dateString) {
            if (!dateString) return this.t('reportPage.notAvailable')
            try {
                const locale = this.$i18n.locale === 'fa' ? 'fa-IR' : 'en-US'
                return new Date(dateString.replace(' ', 'T')).toLocaleString(locale, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true,
                })
            } catch (e) {
                return this.t('reportPage.invalidDate')
            }
        },
        formatDuration(seconds) {
            const duration = Number(seconds)
            if (!Number.isFinite(duration) || duration < 0) return '0s'

            const wholeSeconds = Math.floor(duration)
            const minutes = Math.floor(wholeSeconds / 60)
            const remainingSeconds = wholeSeconds % 60
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
    },
    created() {
        this.gameId = this.$route.params.id
        if (this.gameId) {
            this.fetchReport()
        } else {
            this.isLoading = false
            this.fetchError = this.t('reportPage.notifications.idMissingUrl')
        }
    },
}
</script>
