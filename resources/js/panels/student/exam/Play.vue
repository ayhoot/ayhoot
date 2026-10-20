<template>
    <div class="fixed inset-0 flex flex-col overflow-hidden font-sans" :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'">
        <!-- Confirm modal should be available regardless of whether questions are loaded -->
        <ConfirmModal
            v-if="showResetModal"
            :title="$t('quizGame.resetModal.title') || 'Reset previous attempt?'"
            :message="$t('quizGame.resetModal.message') || 'A previous attempt exists. Remove previous answers and start over?'"
            :confirmText="$t('quizGame.resetModal.reset') || 'Reset'"
            :cancelText="$t('quizGame.resetModal.keep') || 'Keep'"
            @confirm="onConfirmReset"
            @cancel="onCancelReset"
        />

        <div v-if="loading" class="h-full w-full flex items-center justify-center">
            <h2 class="text-2xl font-bold">{{ $t('quizGame.loading') }}</h2>
        </div>

        <div v-else-if="questions.length === 0" class="h-full w-full flex items-center justify-center">
            <h2 class="text-2xl font-bold">{{ $t('quizGame.empty') }}</h2>
        </div>

        <div v-else class="h-full w-full flex flex-col relative">
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat z-0" :style="getBackgroundStyle()">
                <div class="absolute inset-0 bg-black/10"></div>
            </div>

            <div class="relative z-10 flex flex-col h-full w-full text-white">
                        
                <!-- Header -->
                <div class="shrink-0 flex items-center justify-between gap-2 px-3 sm:px-5 md:px-7 py-2 sm:py-3 bg-white/85 text-gray-800 shadow-sm">
                    <div class="flex items-center gap-2 min-w-0">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M15.0001 2.3999V5.9999C15.0001 6.66264 15.5373 7.1999 16.2001 7.1999H19.8001M8.40008 7.1999H10.8001M8.40008 10.7999H15.6001M8.40008 14.3999H15.6001M18.0001 4.1999C17.466 3.72204 16.9118 3.15528 16.5619 2.78718C16.3291 2.54224 16.0074 2.3999 15.6695 2.3999H6.5998C5.27432 2.3999 4.19981 3.47441 4.1998 4.79989L4.19971 19.1998C4.1997 20.5253 5.27421 21.5998 6.5997 21.5998L17.3997 21.5999C18.7252 21.5999 19.7997 20.5254 19.7998 19.2L19.8001 6.47773C19.8001 6.1709 19.683 5.87594 19.4701 5.65503C19.0763 5.24655 18.4187 4.57442 18.0001 4.1999Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            />
                        </svg>
                        <h1 class="text-base sm:text-lg md:text-xl font-bold truncate">
                            {{ currentQuestion.exam?.title || $t('quizGame.header.defaultTitle') }}
                        </h1>
                        <!-- Eye Icon Indicator for View-Only Mode -->
                        <div v-if="isViewingExistingAttempt && !isTeacherMode" class="hidden sm:flex items-center gap-1 ms-2 px-2 py-0.5 bg-amber-100 rounded-full border border-amber-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <span class="text-[10px] font-semibold text-amber-700">{{ $t('quizGame.viewOnlyMode') || 'View Only' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                        <span class="font-bold bg-[#4285F4] shadow-[0_2px_0_rgba(52,80,174,1)] rounded-sm px-2.5 py-1.5 text-white text-xs sm:text-sm">
                            {{ currentIndex + 1 }}/{{ questions.length }}
                        </span>
                        <div v-if="!isTeacherMode" class="bg-[#bbb12e] font-bold shadow-[0_2px_0_rgba(99,86,48,1)] px-2.5 py-1.5 rounded-sm text-white text-xs sm:text-sm">
                            <span class="hidden md:inline">{{ $t('quizGame.header.questionTime') }}</span>
                            {{ formatTime(questionTimeLeft) }}
                        </div>
                        <div v-if="!isTeacherMode" class="bg-[#FF6464] font-bold shadow-[0_2px_0_rgba(192,51,51,1)] px-2.5 py-1.5 rounded-sm text-white text-xs sm:text-sm">
                            <span class="hidden md:inline">{{ $t('quizGame.header.examTime') }}</span>
                            {{ formatTime(totalTimeLeft) }}
                        </div>
                        <button
                            v-if="shouldPlayBackgroundMusic"
                            @click="toggleMute"
                            :title="isMuted ? ($t('quizGame.header.unmute') || 'Unmute') : ($t('quizGame.header.mute') || 'Mute')"
                            type="button"
                            class="px-2 py-1.5 bg-[#4A5568] hover:bg-[#2D3748] shadow-[0_2px_0_rgba(26,32,44,1)] hover:shadow-none hover:translate-y-[2px] rounded-sm font-bold text-xs sm:text-sm transition-all duration-200 text-white cursor-pointer flex items-center justify-center"
                        >
                            <svg v-if="isMuted" class="w-4 h-4 text-red-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                <line x1="23" y1="9" x2="17" y2="15"></line>
                                <line x1="17" y1="9" x2="23" y2="15"></line>
                            </svg>
                            <svg v-else class="w-4 h-4 text-green-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                            </svg>
                        </button>
                        <button
                            v-if="isTeacherMode"
                            @click="editQuestion"
                            class="px-3 py-1.5 bg-[#EBCD1F] hover:bg-[#8A7812] shadow-[0_2px_0_rgba(138,121,18,1)] hover:shadow-none hover:translate-y-[2px] rounded-sm font-bold text-xs sm:text-sm transition-all duration-200 text-white cursor-pointer"
                        >
                            {{ $t('quizGame.header.edit') }}
                        </button>
                        <button
                            v-if="showNavigationButtons && !isAssignmentMode"
                            @click="previousQuestion"
                            :disabled="isPreviousDisabled"
                            class="px-3 py-1.5 bg-[#3450AE] hover:bg-[#152B65] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px] rounded-sm font-bold text-xs sm:text-sm transition-all duration-200 text-white disabled:bg-gray-400 disabled:cursor-not-allowed cursor-pointer"
                        >
                            {{ $t('quizGame.header.prev') }}
                        </button>
                        <button
                            v-if="showNavigationButtons"
                            @click="handleNextOrFinish"
                            :disabled="isNextDisabled"
                            class="px-3 py-1.5 bg-[#3450AE] hover:bg-[#152B65] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px] rounded-sm font-bold text-xs sm:text-sm transition-all duration-200 text-white disabled:bg-gray-400 disabled:cursor-not-allowed cursor-pointer"
                        >
                            {{ currentIndex === questions.length - 1 ? $t('quizGame.header.finish') : $t('quizGame.header.next') }}
                        </button>
                    </div>
                </div>
                
                <!-- Waiting for Teacher Banner -->
                <div v-if="liveSessionWaiting" class="absolute inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
                    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md mx-4 text-center">
                        <div class="text-6xl mb-4">⏳</div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $t('quizGame.waitingForTeacher') || 'Waiting for Teacher' }}</h2>
                        <p class="text-gray-600">{{ $t('quizGame.waitingForTeacherMessage') || 'The teacher will start the session soon. Please wait...' }}</p>
                        <div class="mt-6 flex items-center justify-center gap-2">
                            <div class="w-3 h-3 bg-purple-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-3 h-3 bg-purple-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-3 h-3 bg-purple-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                    </div>
                </div>                <!-- Main container for Question Content and Options -->
                <div class="flex-1 flex flex-col min-h-0 relative">
                    <QuestionViewer
                        :question="currentQuestion"
                        v-model:userAnswer="userAnswer"
                        :selectedOption="selectedOption"
                        v-model:userSequence="userSequence"
                        :isViewingExistingAttempt="isViewingExistingAttempt"
                        :isTeacherMode="isTeacherMode"
                        :isLiveSession="isLiveSession"
                        :optionsToDisplay="optionsToDisplay"
                        @select="selectOption"
                        @zoom="zoomImage"
                        @image-error="handleImageError"
                    />

                    <!-- Feedback Overlays — compact toast in the corner so it doesn't obscure the question. -->
                    <canvas id="confetti-canvas" class="fixed inset-0 pointer-events-none z-40 w-full h-full"></canvas>
                    <transition name="fade">
                        <div
                            v-if="showFeedback"
                            class="fixed top-20 inset-x-0 z-50 flex justify-center pointer-events-none transition-all duration-300"
                        >
                            <div class="flex items-center justify-center pointer-events-auto">
                                <Vue3Lottie
                                    :animationLink="feedbackAnimationLink"
                                    :loop="false"
                                    :autoplay="true"
                                    class="w-28 h-28 sm:w-36 sm:h-36"
                                />
                            </div>
                        </div>
                    </transition>

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

                <!-- Results Modal -->
                <transition name="fade">
                    <div
                        v-if="showResultsModal"
                        class="fixed inset-0 z-[998] flex items-center justify-center bg-black/70 transition-all duration-300"
                    >
                        <div
                            class="bg-white rounded-2xl shadow-2xl p-6 text-gray-800 font-bold max-w-lg w-full text-center transform scale-100 transition-all duration-300 mx-4"
                        >
                            <Vue3Lottie
                                :animationLink="resultsAnimation"
                                :loop="false"
                                :autoplay="true"
                                class="w-48 h-48 md:w-60 md:h-60 mx-auto -mt-20 md:-mt-24 mb-4"
                            />
                            <!-- MODIFIED: Dynamic Title -->
                            <h2 class="text-lg md:text-2xl font-extrabold text-blue-600 mb-2">
                                {{ resultsMessage }}
                            </h2>
                            <p class="text-sm md:text-base text-gray-600 mb-6">
                                {{ $t('quizGame.results.completed') }}
                            </p>

                            <div class="bg-gray-100 p-4 rounded-lg mb-8">
                                <p class="text-lg md:text-xl text-gray-700 mb-2">
                                    {{ $t('quizGame.results.yourScore') }}
                                </p>
                                <p class="text-xl md:text-2xl font-bold text-gray-700">
                                    {{ finalScore }}<span class="text-xl md:text-2xl">%</span>
                                </p>
                                <p class="text-sm md:text-base text-gray-500 mt-2">
                                    {{
                                        $t('quizGame.results.scoreDetails', {
                                            correctAnswers: correctAnswers,
                                            totalQuestions: totalScorableQuestions,
                                        })
                                    }}
                                </p>
                                <!-- Rank and Leaderboard Section -->
                        <div v-if="rankInfo" class="mt-6 pt-6 border-t border-gray-200 w-full">
                            <!-- User Rank Badge -->
                            <div class="flex items-center justify-between mb-6 bg-blue-50 p-4 rounded-xl border-2 border-blue-100 relative overflow-hidden">
                                <div class="flex flex-col items-start z-10">
                                    <span class="text-blue-600 font-bold uppercase tracking-wider text-[10px] md:text-xs">
                                        {{ $t('quizGame.results.rank') }}
                                    </span>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-3xl font-black text-blue-700">{{ rankInfo.rank || '-' }}</span>
                                        <span class="text-sm font-bold text-blue-400">/ {{ rankInfo.total_players || '-' }}</span>
                                    </div>
                                </div>
                                
                                <!-- Subtle Background Icon for decoration -->
                                <div class="absolute -right-2 -bottom-2 opacity-10">
                                    <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-blue-600">
                                    <path d="M40 56.6667C34.4213 56.6667 29.5657 60.8834 27.0589 67.104C25.8615 70.075 27.5796 73.3334 29.8626 73.3334H50.1373C52.4203 73.3334 54.1383 70.075 52.941 67.104C50.4343 60.8834 45.5787 56.6667 40 56.6667Z" stroke="#163300" stroke-width="6" stroke-linecap="round"/>
                                    <path d="M61.6667 16.6667H65.674C69.677 16.6667 71.6783 16.6667 72.7227 17.9246C73.7667 19.1824 73.3327 21.0705 72.4643 24.8465L71.1617 30.5103C69.203 39.0287 62.0363 45.3627 53.3333 46.6667" stroke="#163300" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M18.3333 16.6667H14.326C10.3231 16.6667 8.32164 16.6667 7.27747 17.9246C6.2333 19.1824 6.66747 21.0705 7.53584 24.8465L8.83827 30.5103C10.7971 39.0287 17.9637 45.3627 26.6667 46.6667" stroke="#163300" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M40 56.6667C50.0693 56.6667 58.55 41.1264 61.099 19.9697C61.804 14.1186 62.1567 11.1931 60.2893 8.92989C58.4223 6.66669 55.4077 6.66669 49.378 6.66669H30.6219C24.5925 6.66669 21.5778 6.66669 19.7106 8.92989C17.8434 11.1931 18.1958 14.1186 18.9008 19.9697C21.45 41.1264 29.9308 56.6667 40 56.6667Z" stroke="#163300" stroke-width="6" stroke-linecap="round"/>
                                    </svg>

                                </div>
                            </div>

                            <!-- Leaderboard List -->
                            <div v-if="rankInfo.leaderboard && rankInfo.leaderboard.length" class="text-start">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 px-1">
                                    {{ $t('quizGame.results.leaderboardTop') }}
                                </p>
                                
                                <div class="space-y-2">
                                    <div 
                                        v-for="(p, idx) in rankInfo.leaderboard" 
                                        :key="idx"
                                        class="flex items-center justify-between p-3 rounded-lg transition-all duration-200 border"
                                        :class="[
                                            idx === 0 
                                            ? 'bg-yellow-50 border-yellow-200 shadow-[0_2px_0_rgba(254,240,138,1)]' 
                                            : 'bg-white border-gray-100 shadow-[0_2px_0_rgba(243,244,246,1)]'
                                        ]"
                                    >
                                        <div class="flex items-center gap-3">
                                            <!-- Position Circle -->
                                            <div 
                                                class="w-7 h-7 flex items-center justify-center rounded-full text-xs font-black shadow-inner"
                                                :class="[
                                                    idx === 0 ? 'bg-yellow-400 text-white' : 
                                                    idx === 1 ? 'bg-gray-300 text-white' : 
                                                    idx === 2 ? 'bg-orange-300 text-white' : 'bg-gray-100 text-gray-500'
                                                ]"
                                            >
                                                {{ idx + 1 }}
                                            </div>
                                            
                                            <!-- Player Name -->
                                            <span class="text-sm font-bold text-gray-700 truncate max-w-[150px]">
                                                {{ p.name }}
                                            </span>
                                        </div>

                                        <!-- Score Tag -->
                                        <div class="flex items-center">
                                            <span class="text-xs font-black px-2 py-1 rounded bg-gray-50 border border-gray-100 text-gray-600">
                                                {{ p.score }}%
                                            </span>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>

                            <button
                                @click="closeResultsModalAndNavigate"
                                class="w-full px-6 py-3 bg-blue-500 hover:bg-blue-600 shadow-[0_3px_0_rgba(29,78,216,1)] hover:shadow-none hover:translate-y-[3px] rounded-lg font-bold text-lg text-white transition-all duration-200 cursor-pointer"
                            >
                                {{ $t('quizGame.results.backToDashboard') }}
                            </button>
                        </div>
                    </div>
                </transition>
                <!-- END Results Modal -->

                <!-- Image Zoom Modal -->
                <transition name="fade">
                    <div
                        v-if="zoomedImageUrl"
                        @click="unzoomImage"
                        class="fixed inset-0 z-[999] flex items-center justify-center bg-black/70 cursor-zoom-out"
                    >
                        <img
                            :src="zoomedImageUrl"
                            :alt="$t('quizGame.question.altZoomedImage')"
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
import { setupStudentSocket } from '@/services/liveSocket'
import QuestionViewer from '@/components/quiz/QuestionViewer.vue'
import confetti from 'canvas-confetti'
import { Vue3Lottie } from 'vue3-lottie'
import ConfirmModal from '@/components/ConfirmModal.vue'
import { useConfirm } from '@/composables/useConfirm'

const { confirm: confirmDialog, alert: alertDialog } = useConfirm()

const SHAPE_MAP = [
    { name: 'triangle', color: 'red', icon: 'triangle' },
    { name: 'diamond', color: 'blue', icon: 'diamond' },
    { name: 'circle', color: 'yellow', icon: 'circle' },
    { name: 'rectangle', color: 'green', icon: 'rectangle' },
    { name: 'pentagon', color: 'pink', icon: 'pentagon' },
    { name: 'hexagon', color: 'purple', icon: 'hexagon' },
]

const EXAM_TIMER_STORAGE_KEY_PREFIX = 'exam_timer_'

export default {
    name: 'QuizGame',
    components: {
        QuestionViewer,
        Vue3Lottie,
        ConfirmModal,
    },
    data() {
        return {
            questions: [],
            currentIndex: 0,
            loading: true,
            totalTimeLeft: 0,
            examStartTime: null,
            examDeadlineAt: null,
            globalTimerInterval: null,
            questionTimeLeft: 0,
            questionDeadlineAt: null,
            questionTimerInterval: null,
            selectedOption: null,
            userAnswer: '',
            userSequence: [],
            questionUiStartTime: null,
            showFeedback: false,
            feedbackTimeout: null,
            isCorrectAnswer: false,
            isExamFinished: false,
            isExamTimeout: false,
            attemptId: null,
            serverTimeAtLoad: null,
            gameId: null,
            examId: null,
            isAssignment: false,
            submittedAnswersLog: [],
            dragOptions: { animation: 200, ghostClass: 'ghost', handle: '.handle' },
            confettiAnimation: '/imgHolders/correct.json',
            wrongAnimation: '/imgHolders/incorrect.json',
            timeoutAnimation: '/imgHolders/noanswer.json',
            sessionAnswersStore: [],
            apiBaseUrl: '',
            zoomedImageUrl: null,
            // Data for results modal
            showResultsModal: false,
            finalScore: 0,
            correctAnswers: 0,
            totalScorableQuestions: 0,
            resultsAnimation: '/imgHolders/results-trophy.json', // Path to your results Lottie animation
            rankInfo: null,
            attemptEchoChannel: null,
            // reset modal state
            showResetModal: false,
            resetModalAttempt: null,
            resetModalResolve: null,
            // when true, user is viewing existing attempt and cannot change answers
            isViewingExistingAttempt: false,
            // Live session socket
            socket: null,
            livePin: null,
            isLiveSession: false,
            liveSessionId: null,
            liveSessionWaiting: false, // True when waiting for teacher to start
            liveSessionStatus: 'waiting', // waiting, started, question, finished
            liveSessionConfig: null, // Session configuration (auto_advance, teacher_paced, etc.)
            optionsToDisplay: [], // Shuffled options for MC/TF questions
            shouldPlayBackgroundMusic: false,
            isMuted: false,
            bgAudio: null,
            audioInteractionHandler: null,
        }
    },
    computed: {
        currentQuestion() {
            return this.questions[this.currentIndex] || { exam: {} }
        },
        
        showNavigationButtons() {
            // In non-live sessions, always show buttons
            if (!this.isLiveSession) return true
            
            // In live sessions, check the config
            if (!this.liveSessionConfig) return false
            
            // If auto_advance is true, hide buttons (teacher controls everything)
            // If auto_advance is false, show buttons (students can navigate)
            const autoAdvance = this.liveSessionConfig.auto_advance !== false // Default to true
            return !autoAdvance // Show buttons when auto_advance is OFF
        },
        
        progressPercentage() {
            if (this.questions.length === 0) return 0
            return ((this.currentIndex + 1) / this.questions.length) * 100
        },
        currentQuestionFiles() {
            const files = this.currentQuestion.files;
            if (!files) return [];
            
            // If it's already an array (as seen in your JSON), return it directly
            if (Array.isArray(files)) return files;
            
            // If it's a string (sometimes Laravel returns it stringified), try to parse it
            if (typeof files === 'string') {
                try {
                    const parsed = JSON.parse(files);
                    return Array.isArray(parsed) ? parsed : [];
                } catch (e) {
                    // If it's just a single filename string, wrap it in an array
                    return [files];
                }
            }
            return [];
        },
        isNextDisabled() {
            if (this.showFeedback) return true
            
            // In live sessions with teacher-paced mode, navigation is controlled by teacher
            if (this.isLiveSession && this.liveSessionConfig?.teacher_paced) {
                return true // Always disabled when teacher controls navigation
            }
            
            // In live sessions with auto_advance off, also teacher-controlled
            if (this.isLiveSession && this.liveSessionConfig && !this.liveSessionConfig.auto_advance) {
                return true // Always disabled when teacher controls navigation
            }
            
            // When viewing an existing attempt, allow navigation/finish even if answers are present
            if (this.isViewingExistingAttempt && !this.isTeacherMode) return false
            
            // For live sessions with auto_advance on, require answer before proceeding
            if (this.isLiveSession) {
                const type = this.currentQuestion.type
                if (type === 'slide') return false // Slides can always advance
                
                // Check if student has answered current question
                if (type === 'multiple_choice' || type === 'true_false') {
                    return this.selectedOption === null
                }
                if (type === 'ordinary') {
                    return (this.userAnswer || '').trim() === ''
                }
                if (type === 'sequence') {
                    const sequenceData = this.currentQuestion.sequence_options || this.currentQuestion.options
                    return this.userSequence.length === 0 && this.parseOptions(sequenceData).length > 0
                }
            }
            
            // Regular exam mode
            const type = this.currentQuestion.type
            if (type === 'multiple_choice' || type === 'true_false') {
                return this.selectedOption === null
            }
            if (type === 'ordinary') {
                return (this.userAnswer || '').trim() === ''
            }
            if (type === 'sequence') {
                const sequenceData = this.currentQuestion.sequence_options || this.currentQuestion.options
                return this.userSequence.length === 0 && this.parseOptions(sequenceData).length > 0
            }
            return false
        },
        isAssignmentMode() {
            return Boolean(this.isAssignment || this.gameId) && !this.isLiveSession && !this.isTeacherMode
        },
        isPreviousDisabled() {
            // Disabled in assignment mode
            if (this.isAssignmentMode) return true

            // Previous is disabled at first question
            if (this.currentIndex === 0) return true
            
            // Disabled during feedback
            if (this.showFeedback) return true
            
            // Disabled while waiting for teacher
            if (this.liveSessionWaiting) return true
            
            // In teacher-paced live sessions, student navigation is disabled
            if (this.isLiveSession && this.liveSessionConfig && this.liveSessionConfig.teacher_paced) {
                return true
            }
            
            // In live sessions with auto_advance off, navigation is controlled by teacher (fallback)
            if (this.isLiveSession && this.liveSessionConfig && !this.liveSessionConfig.auto_advance) {
                return true
            }
            
            return false
        },
        isTeacherMode() {
            return this.$route.path.includes('/teacher/') || this.$route.path.includes('/institute/')
        },
        feedbackAnimationLink() {
            if (
                this.isExamTimeout ||
                (this.questionTimeLeft <= 0 && this.currentQuestion.type !== 'slide' && !this.checkIfUserAnswered())
            )
                return this.timeoutAnimation
            return this.isCorrectAnswer ? this.confettiAnimation : this.wrongAnimation
        },
        examTimerStorageKey() {
            return `${EXAM_TIMER_STORAGE_KEY_PREFIX}${this.gameId}_${this.examId}`
        },
        // NEW COMPUTED PROPERTY
        resultsMessage() {
            if (this.finalScore >= 75) {
                return this.$t('quizGame.results.feedback.great')
            } else if (this.finalScore >= 50) {
                return this.$t('quizGame.results.feedback.good')
            } else if (this.finalScore >= 25) {
                return this.$t('quizGame.results.feedback.average')
            } else {
                return this.$t('quizGame.results.feedback.poor')
            }
        },
    },
    watch: {
        showFeedback(newVal) {
            if (newVal) {
                if (this.isCorrectAnswer && !this.isExamTimeout) {
                    this.launchConfetti()
                    this.playCorrectAudio()
                } else {
                    this.playIncorrectAudio()
                }
            }
        },
        totalTimeLeft(newVal) {
            if (this.examStartTime && !this.isTeacherMode) {
                localStorage.setItem(
                    this.examTimerStorageKey,
                    JSON.stringify({
                        timeLeft: newVal,
                        startTime: this.examStartTime,
                        lastUpdated: Date.now(),
                    })
                )
            }
        },
    },
    async mounted() {
        this.examId = this.$route.params.examId || this.$route.params.id || this.$route.query.exam_id || this.$route.query.exam
        this.gameId = this.$route.params.gameId || this.$route.params.game || this.$route.query.game || this.$route.query.game_id || this.$route.query.gameId || null
        if (this.gameId) {
            this.isAssignment = true
        }

        window.addEventListener('beforeunload', this.handleBeforeUnload)
        await this.fetchQuestionsAndInitialize()
    },
    beforeUnmount() {
        this.stopBackgroundMusic()
        if (this.feedbackTimeout) {
            clearTimeout(this.feedbackTimeout)
            this.feedbackTimeout = null
        }
        this.clearGlobalTimer()
        this.clearQuestionTimer()
        window.removeEventListener('beforeunload', this.handleBeforeUnload)
        this.unsubscribeAttemptChannel()
        // Cleanup live socket if present
        if (this.socket) {
            this.socket.leave()
            this.socket = null
        }
    },
    methods: {
        clearTimersForAttempt(attemptId, examId) {
            try {
                if (!attemptId && !examId) return
                // Remove per-question timers for this attempt
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i)
                    if (!key) continue
                    if (attemptId && key.startsWith(`q_timer_${attemptId}_`)) {
                        localStorage.removeItem(key)
                        // adjust index and continue
                        i--
                        continue
                    }
                    // exam timer keys may include gameId or wildcard; remove any exam_timer entries that end with _{examId}
                    if (examId && key.startsWith('exam_timer_') && key.endsWith(`_${examId}`)) {
                        localStorage.removeItem(key)
                        i--
                        continue
                    }
                }
                // Also remove any exam timer entries that reference this exam id more generally
                Object.keys(localStorage).forEach((k) => {
                    if (k && examId && k.includes(`_${examId}`) && (k.startsWith('exam_timer_') || k.startsWith('q_timer_'))) {
                        localStorage.removeItem(k)
                    }
                })
            } catch (e) {
                console.warn('clearTimersForAttempt failed', e)
            }
        },
        zoomImage(file) {
            this.zoomedImageUrl = this.getFileUrl(file)
        },
        unzoomImage() {
            this.zoomedImageUrl = null
        },
        handleBeforeUnload(event) {
            if (!this.isExamFinished && this.questions.length > 0 && !this.isTeacherMode) {
                // Persist an absolute deadline on page exit so reload/background time
                // cannot grant extra time. Normal question navigation stores remaining
                // time instead, because inactive questions are paused.
                this.saveQuestionTimerState(true)
                event.preventDefault()
                event.returnValue = this.$t('quizGame.notifications.beforeUnload')
            }
        },
        async fetchQuestionsAndInitialize() {
            this.loading = true
            try {
                const livePin = this.$route.query.live_pin || null
                const liveSessionId = this.$route.query.live_session_id || null
                const qs = livePin ? `?live_pin=${livePin}` : ''
                
                // Store live session info
                if (livePin) {
                    this.livePin = livePin
                    this.isLiveSession = true
                }
                if (liveSessionId) {
                    this.liveSessionId = liveSessionId
                }
                
                console.log('[PLAY] Initializing with live session:', {
                    livePin: this.livePin,
                    liveSessionId: this.liveSessionId,
                    isLiveSession: this.isLiveSession
                })

                // If a live PIN is present, ensure the student is registered as a participant
                // by calling the live-session join endpoint. This creates/persists the
                // participant and triggers server-side broadcast to hosts so joins are real-time.
                let sessionData = null
                if (livePin) {
                    try {
                        const joinResponse = await apiCall('POST', `/api/live-session/${livePin}/join`, {
                            // server will use authenticated student name when available
                        })
                        // Try multiple ways to get session data
                        sessionData = joinResponse?.session || joinResponse?.data?.session || null
                        
                        console.log('[PLAY] Joined live session - full response:', joinResponse)
                        console.log('[PLAY] Session data extracted:', {
                            hasSession: !!sessionData,
                            status: sessionData?.status,
                            statusType: typeof sessionData?.status,
                            current_question_index: sessionData?.current_question_index,
                            started_at: sessionData?.started_at,
                            responseKeys: Object.keys(joinResponse || {})
                        })
                        
                        // Ensure status is properly set
                        if (sessionData && !sessionData.status) {
                            console.warn('[PLAY] Session data missing status field, checking response structure')
                        }
                    } catch (joinErr) {
                        console.error('[PLAY] Live session join failed:', joinErr)
                        // continue — the subsequent game API will return an error if participant missing
                    }
                }

                let apiUrl = `/api/student/exam/${this.examId}/game`
                if (this.gameId) {
                    apiUrl += `/${this.gameId}`
                }

                // append live_pin to game API if present
                apiUrl += qs

                let response = await apiCall('GET', apiUrl)

                // If backend indicates a previous completed attempt exists, ask the student
                if (response && response.previous_attempt_exists) {
                    // Show modal and await user's decision. Keep a copy of the initial response.
                    const initialResponse = response

                    // Allow the modal to render (don't hide it behind the loading screen)
                    this.loading = false

                    const choice = await this.askResetModal(response.previous_attempt)

                    if (choice === 'reset') {
                        // Show loading while performing reset network call
                        this.loading = true
                        this.isViewingExistingAttempt = false
                        // Use & if query string already exists, else use ?
                        const resetUrl = apiUrl + (qs ? '&' : '?') + 'reset_attempt=1'
                        response = await apiCall('GET', resetUrl)
                        // Clear any stored timers for previous attempt
                        const prevAttemptId = initialResponse.previous_attempt?.id || response.attempt_id
                        this.clearTimersForAttempt(prevAttemptId, this.examId)
                    } else {
                        // Use the initial response to avoid an extra API call.
                        // Preserve previous answers but allow the student to continue the exam.
                        // Keep previous answers and switch to view-only mode
                        this.isViewingExistingAttempt = true
                        // Note: we keep loading false so the UI shows the prompt/result immediately
                        response = {
                            ...initialResponse,
                            attempt_id: initialResponse.previous_attempt?.id,
                            remaining_time_seconds: initialResponse.previous_attempt?.remaining_time_seconds ?? 0,
                        }
                        // Clear timers so we rely on server time for display
                        const prevAttemptId2 = initialResponse.previous_attempt?.id
                        this.clearTimersForAttempt(prevAttemptId2, this.examId)
                    }
                }

                if (response && (response.attempt_completed || response.exam_timed_out)) {
                    this.loading = false
                    this.isExamFinished = true
                    await alertDialog({ message: response.message || this.$t('quizGame.notifications.sessionInactive') })
                    return
                }

                // start API returned; proceed to ensure we have questions available

                // Ensure we have an examId available (use response if needed)
                const examIdFromResponse = response?.exam?.id || this.examId
                if (examIdFromResponse) this.examId = examIdFromResponse

                // Some backend paths may return previous_attempt_exists with minimal exam payload
                // (no questions) to prompt the user. If so, fetch the questions list explicitly.
                let questionsFromResponse = response?.exam?.questions ?? []
                if (!questionsFromResponse || questionsFromResponse.length === 0) {
                    try {
                        const fallbackQs = this.gameId ? (qs ? `${qs}&game=${this.gameId}` : `?game=${this.gameId}`) : qs
                        const qsResp = await apiCall('GET', `/api/student/exam/${examIdFromResponse}/questions${fallbackQs}`) // requires auth
                        questionsFromResponse = qsResp.questions || []
                    } catch (qerr) {
                        console.warn('Failed to fetch questions fallback:', qerr)
                        questionsFromResponse = []
                    }
                }

                this.questions = questionsFromResponse
                if (!this.questions || this.questions.length === 0) {
                    this.loading = false
                    return
                }

                // Only prefill sessionAnswersStore if viewing existing attempt (user chose "Keep")
                if (this.isViewingExistingAttempt && response.previous_attempt && Array.isArray(response.previous_attempt.answers)) {
                    this.sessionAnswersStore = new Array(this.questions.length).fill(null)
                    response.previous_attempt.answers.forEach((ans) => {
                        const idx = this.questions.findIndex((q) => q.id === ans.question_id)
                        if (idx === -1) return
                        const q = this.questions[idx]
                        const saved = { fromApi: true }
                        try {
                            if (q.type === 'multiple_choice' || q.type === 'true_false') {
                                const opts = this.parseOptions(q.options)
                                const found = opts.findIndex((o) => String(o.text) === String(ans.answer))
                                if (found !== -1) saved.selectedOption = found
                            } else if (q.type === 'ordinary') {
                                saved.userAnswer = ans.answer
                            } else if (q.type === 'sequence') {
                                if (typeof ans.answer === 'string') {
                                    const seq = JSON.parse(ans.answer)
                                    const originalOptions = this.parseOptions(q.options)
                                    saved.userSequence = seq
                                        .map((text) => originalOptions.find((opt) => opt.text === text) || { text })
                                        .map((item, i) => ({ ...item, id: item.id || i.toString() }))
                                }
                            }
                        } catch (e) {
                            console.warn('Could not restore answer for question', q.id, e)
                        }
                        this.sessionAnswersStore[idx] = saved
                    })
                }

                if (response.game_id || response.is_assignment) {
                    this.gameId = response.game_id || this.gameId
                    this.isAssignment = true
                }
                // If viewing existing attempt, ensure UI is in read-only state and prefilled
                if (this.isViewingExistingAttempt) {
                    console.debug('Prefilling answers for existing attempt, switching to view-only mode')
                }
                this.examId = response.exam.id
                this.attemptId = response.attempt_id
                this.totalTimeLeft = response.remaining_time_seconds
                this.serverTimeAtLoad = response.server_time_at_load
                // The backend is authoritative for the attempt lifetime.  Keep an
                // absolute deadline so throttled/background tabs cannot slow the timer.
                this.examDeadlineAt = Date.now() + Math.max(0, Number(this.totalTimeLeft) || 0) * 1000

                if (!this.$route.params.gameId && this.gameId) {
                    this.$router.replace({
                        name: this.$route.name,
                        params: { ...this.$route.params, gameId: this.gameId },
                        query: this.$route.query,
                    })
                }

                this.examStartTime =
                    Date.now() -
                    (this.questions.reduce((sum, q) => sum + (Number(q.duration) > 0 ? Number(q.duration) : 30), 0) - this.totalTimeLeft) * 1000

                // If sessionAnswersStore wasn't populated from previous_attempt above, initialize empty array
                if (!this.sessionAnswersStore || this.sessionAnswersStore.length !== this.questions.length) {
                    this.sessionAnswersStore = new Array(this.questions.length).fill(null)
                }

                const examData = response?.exam || this.questions[0]?.exam || null
                this.shouldPlayBackgroundMusic = Boolean(
                    examData?.play_music === true || examData?.play_music === 1 || examData?.play_music === '1'
                )
                if (this.shouldPlayBackgroundMusic) {
                    this.initBackgroundMusic()
                }

                this.preloadAssets()
                await this.initializeQuestion()
                
                // Setup live session socket if this is a live session
                if (livePin) {
                    this.livePin = livePin
                    this.isLiveSession = true
                    
                    // Load session config if available
                    if (sessionData?.config) {
                        this.liveSessionConfig = sessionData.config
                        console.log('[STUDENT] Session config loaded:', this.liveSessionConfig)
                    }
                    
                    // Check session status to determine if we should wait
                    // Session can be: waiting, started, question, finished
                    // For late joiners, also check if session has active question index
                    const sessionStatus = sessionData?.status
                    const hasActiveQuestion = sessionData?.current_question_index !== undefined && sessionData?.current_question_index >= 0
                    
                    console.log('[STUDENT] Determining waiting state for potential late joiner:', {
                        hasSessionData: !!sessionData,
                        sessionStatus: sessionStatus,
                        hasActiveQuestion: hasActiveQuestion,
                        current_question_index: sessionData?.current_question_index,
                        sessionConfig: this.liveSessionConfig,
                        willWait: !sessionStatus || (sessionStatus === 'waiting' && !hasActiveQuestion)
                    })
                    
                    // Session is NOT waiting if:
                    // 1. Status is not 'waiting' (should be 'started' or 'question'), OR
                    // 2. Has an active question index (late joiner case)
                    if (sessionData && (sessionStatus !== 'waiting' || hasActiveQuestion)) {
                        // Session already started or in progress - don't show waiting overlay
                        this.liveSessionWaiting = false
                        this.liveSessionStatus = sessionStatus || (hasActiveQuestion ? 'question' : 'started')
                        this.startGlobalTimer()
                        console.log('[STUDENT] ✓ Joined active session - starting immediately', {
                            status: this.liveSessionStatus,
                            reason: sessionStatus !== 'waiting' ? 'status not waiting' : 'has active question',
                            currentQuestionIndex: sessionData.current_question_index
                        })
                    } else {
                        // Session not started yet OR no session data - show waiting overlay
                        this.liveSessionWaiting = true
                        this.liveSessionStatus = 'waiting'
                        console.log('[STUDENT] ⏳ Live session mode - waiting for teacher to start', {
                            reason: !sessionStatus ? 'no status' : sessionStatus === 'waiting' ? 'status is waiting' : 'unexpected',
                            hasActiveQuestion: hasActiveQuestion
                        })
                    }
                    
                    this.setupLiveSessionSocket()
                } else {
                    // Regular exam mode - start timers normally
                    this.startGlobalTimer()
                }
            } catch (error) {
                console.error('Error fetching questions or exam state:', error)
                this.loading = false
                if (error.response && error.response.data) {
                    const data = error.response.data
                    if (data.attempt_completed || data.exam_timed_out) {
                        this.isExamFinished = true
                        await alertDialog({ message: data.message || this.$t('quizGame.notifications.sessionInactive') })
                    } else if (data.redirect_to_pay) {
                        await alertDialog({ message: data.message || this.$t('quizGame.notifications.paymentRequired') })
                    } else {
                        await alertDialog({ message: this.$t('quizGame.notifications.loadError') })
                    }
                } else {
                    await alertDialog({ message: this.$t('quizGame.notifications.unknownLoadError') })
                }
            } finally {
                if (this.loading) this.loading = false
            }
        },
        loadPersistedTimerState() {
            if (this.isTeacherMode) return
            const storedState = localStorage.getItem(this.examTimerStorageKey)
            if (storedState) {
                try {
                    const { timeLeft, startTime, lastUpdated } = JSON.parse(storedState)
                    const elapsedSinceLastUpdate = Math.floor((Date.now() - lastUpdated) / 1000)
                    const newTimeLeft = timeLeft - elapsedSinceLastUpdate

                    if (newTimeLeft > 0) {
                        this.totalTimeLeft = newTimeLeft
                        this.examStartTime = startTime
                    } else {
                        this.totalTimeLeft = 0
                        this.examStartTime = startTime
                        this.handleExamTimeout()
                    }
                } catch (e) {
                    console.error('Error parsing stored timer state:', e)
                    localStorage.removeItem(this.examTimerStorageKey)
                }
            }
        },
        preloadAssets() {
            this.questions.forEach((q) => {
                if (q.q_options) {
                    try {
                        let o = q.q_options
                        if (typeof o === 'string') o = JSON.parse(o)
                        if (o && o.background_image) {
                            const i = new Image()
                            i.src = this.getFileUrl(o.background_image)
                        }
                    } catch (e) {}
                }
                if (q.files) {
                    try {
                        let fs = q.files
                        if (typeof fs === 'string') fs = JSON.parse(fs)
                        fs.forEach((f) => {
                            if (Array.isArray(f)) {
                                f.forEach((sf) => {
                                    const i = new Image()
                                    i.src = this.getFileUrl(sf)
                                })
                            } else {
                                const i = new Image()
                                i.src = this.getFileUrl(f)
                            }
                        })
                    } catch (e) {}
                }
                if (q.options) {
                    try {
                        let o = q.options
                        if (typeof o === 'string') o = JSON.parse(o)
                        if (Array.isArray(o)) {
                            o.forEach((opt) => {
                                if (opt.image) {
                                    const i = new Image()
                                    i.src = this.getFileUrl(opt.image)
                                }
                            })
                        }
                    } catch (e) {}
                }
            })
        },
        async initializeQuestion() {
            if (this.questions.length === 0 || !this.currentQuestion.id) return
            this.questionUiStartTime = Date.now()
            this.selectedOption = null
            this.userAnswer = ''
            this.userSequence = []
            this.isQuestionTimedOut = false
            this.optionsToDisplay = [] // Initialize options display array

            const questionId = this.currentQuestion.id
            const questionDuration = Number(this.currentQuestion.duration) > 0
                ? Number(this.currentQuestion.duration)
                : 30
            if (!this.isTeacherMode && this.attemptId) {
                const timerKey = `q_timer_${this.attemptId}_${questionId}`
                const storedTimer = localStorage.getItem(timerKey)
                this.questionDeadlineAt = null
                if (storedTimer !== null) {
                    try {
                        const parsed = JSON.parse(storedTimer)
                        if (parsed && Number.isFinite(Number(parsed.deadlineAt))) {
                            this.questionDeadlineAt = Number(parsed.deadlineAt)
                        } else if (parsed && Number.isFinite(Number(parsed.remainingSeconds))) {
                            this.questionDeadlineAt = Date.now() + Math.max(0, Number(parsed.remainingSeconds)) * 1000
                        } else if (Number.isFinite(Number(parsed))) {
                            // Backwards compatibility with the old remaining-seconds value.
                            this.questionDeadlineAt = Date.now() + Math.max(0, Number(parsed)) * 1000
                        }
                    } catch (e) {
                        const oldRemaining = Number.parseInt(storedTimer, 10)
                        if (Number.isFinite(oldRemaining)) {
                            this.questionDeadlineAt = Date.now() + Math.max(0, oldRemaining) * 1000
                        }
                    }
                }
                if (!this.questionDeadlineAt) this.questionDeadlineAt = Date.now() + questionDuration * 1000
                this.questionTimeLeft = Math.max(0, Math.ceil((this.questionDeadlineAt - Date.now()) / 1000))
            } else {
                this.questionTimeLeft = questionDuration
                this.questionDeadlineAt = Date.now() + questionDuration * 1000
            }

            this.startQuestionTimer()

            // Shuffle MC/TF options for variety
            if (this.currentQuestion.type === 'multiple_choice' || this.currentQuestion.type === 'true_false') {
                const originalOptions = this.parseOptions(this.currentQuestion.options)
                this.optionsToDisplay = [...originalOptions].sort(() => Math.random() - 0.5)
                console.log('[QUESTION-INIT] Shuffled', this.currentQuestion.type, 'options:', this.optionsToDisplay.length)
            }

            let restoredFromSession = false
            if (this.sessionAnswersStore[this.currentIndex]) {
                const savedState = this.sessionAnswersStore[this.currentIndex]
                this.selectedOption = savedState.selectedOption
                this.userAnswer = savedState.userAnswer
                this.userSequence = savedState.userSequence ? [...savedState.userSequence] : []
                restoredFromSession = true
                console.log('[SEQUENCE] Restored from session:', {
                    type: this.currentQuestion.type,
                    restored: restoredFromSession,
                    userSequenceLength: this.userSequence.length
                })
            } else if (!this.isTeacherMode) {
                try {
                    const apiAnswer = await this.fetchStudentAnswerFromAPI(questionId)
                    console.log('[ANSWER-RESTORE] API answer:', apiAnswer)
                    if (apiAnswer && apiAnswer.answer !== null && apiAnswer.answer !== undefined) {
                        console.log('[ANSWER-RESTORE] Restoring answer:', apiAnswer.answer)
                        this.restoreAnswerFromApiData(apiAnswer.answer)
                        this.sessionAnswersStore[this.currentIndex] = {
                            selectedOption: this.selectedOption,
                            userAnswer: this.userAnswer,
                            userSequence: [...this.userSequence],
                            fromApi: true,
                        }
                        restoredFromSession = true
                    } else {
                        console.log('[ANSWER-RESTORE] No previous answer to restore')
                    }
                } catch (error) {
                    if (error.response && error.response.status !== 404) {
                        console.warn(`Could not fetch previous answer for question ${questionId}:`, error.message)
                    }
                }
            }

            // Initialize sequence questions - make sure we always initialize if not restored
            if (this.currentQuestion.type === 'sequence') {
                // Sequence questions use sequence_options field, fallback to options
                const sequenceData = this.currentQuestion.sequence_options || this.currentQuestion.options
                console.log('[SEQUENCE-INIT] Initializing sequence question:', {
                    hasSequenceOptions: !!this.currentQuestion.sequence_options,
                    hasOptions: !!this.currentQuestion.options,
                    sequenceDataType: typeof sequenceData,
                    sequenceDataLength: Array.isArray(sequenceData) ? sequenceData.length : 'not array',
                    alreadyRestored: restoredFromSession && this.userSequence.length > 0,
                    sequenceData: sequenceData
                })
                
                // Only initialize if not already restored with proper data
                if (!restoredFromSession || this.userSequence.length === 0) {
                    const parsedOptions = this.parseOptions(sequenceData)
                    console.log('[SEQUENCE-INIT] Parsed options:', parsedOptions.length, 'items')
                    
                    this.userSequence = parsedOptions
                        .map((opt, idx) => ({ ...opt, id: opt.id || idx.toString() }))
                        .sort(() => Math.random() - 0.5)
                    
                    console.log('[SEQUENCE-INIT] Initialized with', this.userSequence.length, 'shuffled items')
                }
            }
        },
        async fetchStudentAnswerFromAPI(questionId) {
            try {
                const livePin = this.$route.query.live_pin || null
                const qs = livePin ? `?live_pin=${livePin}` : ''
                let apiUrl = `${this.apiBaseUrl}/api/student/exam/question/${this.examId}/${questionId}`
                if (this.gameId) {
                    apiUrl += `/${this.gameId}`
                }
                apiUrl += qs
                const response = await apiCall('GET', apiUrl)
                return response
            } catch (error) {
                if (error.response && error.response.status === 404) return null
                throw error
            }
        },
        async askResetModal(attempt) {
            return new Promise(async (resolve) => {
                this.resetModalAttempt = attempt
                this.showResetModal = true
                // Ensure modal DOM mounts before assigning resolver
                await this.$nextTick()
                console.debug('Reset modal shown for attempt', attempt)
                this.resetModalResolve = resolve
            })
        },
        onConfirmReset() {
            if (this.resetModalResolve) this.resetModalResolve('reset')
            this.showResetModal = false
            this.resetModalAttempt = null
            this.resetModalResolve = null
        },
        onCancelReset() {
            if (this.resetModalResolve) this.resetModalResolve('use_existing')
            this.showResetModal = false
            this.resetModalAttempt = null
            this.resetModalResolve = null
        },
        restoreAnswerFromApiData(apiAnswerContent) {
            console.log('[RESTORE] Restoring answer, type:', this.currentQuestion.type, 'content:', apiAnswerContent)
            
            // Don't restore if content is null or undefined
            if (apiAnswerContent === null || apiAnswerContent === undefined) {
                console.log('[RESTORE] Skipping restore - no content')
                return
            }
            
            const qType = this.currentQuestion.type
            if (qType === 'multiple_choice' || qType === 'true_false') {
                const options = this.parseOptions(this.currentQuestion.options)
                const foundIndex = options.findIndex((opt) => opt.text === apiAnswerContent)
                if (foundIndex !== -1) this.selectedOption = foundIndex
            } else if (qType === 'ordinary') {
                this.userAnswer = apiAnswerContent
            } else if (qType === 'sequence') {
                try {
                    // Handle both JSON string and already-parsed array
                    let apiSequenceTexts = apiAnswerContent
                    if (typeof apiAnswerContent === 'string') {
                        // Try to parse as JSON
                        try {
                            apiSequenceTexts = JSON.parse(apiAnswerContent)
                        } catch (parseError) {
                            console.warn('Could not parse sequence answer as JSON, treating as single value:', parseError)
                            // If it's not valid JSON, treat it as a single-item array
                            apiSequenceTexts = [apiAnswerContent]
                        }
                    }
                    
                    // Ensure it's an array
                    if (!Array.isArray(apiSequenceTexts)) {
                        console.warn('Sequence answer is not an array, converting:', apiSequenceTexts)
                        apiSequenceTexts = [apiSequenceTexts]
                    }
                    
                    // Use sequence_options instead of options for sequence questions
                    const originalOptions = this.parseOptions(this.currentQuestion.sequence_options || this.currentQuestion.options)
                    console.log('[SEQUENCE-RESTORE] Original options:', originalOptions)
                    this.userSequence = apiSequenceTexts
                        .map((text) => originalOptions.find((opt) => opt.text === text) || { text, id: text })
                        .filter(Boolean)
                        .map((item, idx) => ({ ...item, id: item.id || idx.toString() }))
                    
                    console.log('[SEQUENCE-RESTORE] Successfully restored sequence:', this.userSequence.length, 'items')
                } catch (e) {
                    console.error('Could not parse sequence answer from API', e, 'Raw value:', apiAnswerContent)
                }
            }
        },
        startGlobalTimer() {
            this.clearGlobalTimer()
            if (!this.examDeadlineAt) {
                this.examDeadlineAt = Date.now() + Math.max(0, Number(this.totalTimeLeft) || 0) * 1000
            }
            const update = () => {
                this.totalTimeLeft = Math.max(0, Math.ceil((this.examDeadlineAt - Date.now()) / 1000))
                if (this.totalTimeLeft <= 0 && !this.isExamFinished) {
                    this.handleClientSideExamTimeoutDisplay()
                }
            }
            update()
            if (this.totalTimeLeft > 0) this.globalTimerInterval = setInterval(update, 250)
        },
        handleClientSideExamTimeoutDisplay() {
            this.clearGlobalTimer()
            this.clearQuestionTimer()
            if (this.isExamFinished) return
            this.isExamTimeout = true
            // Submit the current answer (empty if none), then finish -> report page.
            // No feedback overlay / lock: processAnswerSubmission routes to finishQuiz(true).
            this.processAnswerSubmission(!this.checkIfUserAnswered(), true)
        },
        clearGlobalTimer() {
            if (this.globalTimerInterval) clearInterval(this.globalTimerInterval)
            this.globalTimerInterval = null
        },
        
        // Live session socket setup and handlers
        setupLiveSessionSocket() {
            try {
                console.log('[STUDENT] Setting up live session socket with PIN:', this.livePin)
                this.socket = setupStudentSocket(this.livePin, {
                    questionStarted: this.handleQuestionStartedFromSocket,
                    questionNext: this.handleQuestionNextFromSocket,
                    questionPrevious: this.handleQuestionPreviousFromSocket,
                    sessionStarted: this.handleSessionStartedFromSocket,
                    questionResults: this.handleQuestionResultsFromSocket,
                    sessionFinished: this.handleSessionFinishedFromSocket,
                    timerPaused: this.handleTimerPausedFromSocket,
                    configUpdated: this.handleConfigUpdatedFromSocket,
                })
                
                // Setup connection event handlers
                this.socket.onConnection({
                    onConnected: () => console.log('[STUDENT] Socket connected to live session'),
                    onDisconnected: () => console.log('[STUDENT] Socket disconnected from live session'),
                    onError: (err) => console.error('[STUDENT] Socket error:', err),
                })
                
                console.log('[STUDENT] Live session socket setup complete')
            } catch (e) {
                console.error('[STUDENT] Failed to setup live session socket:', e)
            }
        },
        
        handleQuestionStartedFromSocket(data) {
            console.log('[STUDENT] Handling question.started event:', data)
            if (!data.question) {
                console.warn('[STUDENT] No question in event data')
                return
            }

            // Instantly clear any active feedback animation or pending navigation timeout
            if (this.feedbackTimeout) {
                clearTimeout(this.feedbackTimeout)
                this.feedbackTimeout = null
            }
            this.showFeedback = false
            
            // Update session config if provided
            if (data.session && data.session.config) {
                this.liveSessionConfig = data.session.config
                console.log('[STUDENT] Session config updated:', this.liveSessionConfig)
            }
            
            // Update session status
            this.liveSessionWaiting = false
            this.liveSessionStatus = 'question'
            
            // Update the current question from the socket event
            const newQuestion = data.question
            
            // Find the matching question in questions array authoritatively
            let questionIndex = this.questions.findIndex(q => q.id === newQuestion.id)
            if (questionIndex === -1 && typeof data.question_index === 'number') {
                questionIndex = data.question_index
            }

            if (questionIndex !== -1 && questionIndex < this.questions.length) {
                // Update the question data from socket broadcast
                this.questions[questionIndex] = {
                    ...this.questions[questionIndex],
                    ...newQuestion
                }
                // Move authoritatively to this question
                this.currentIndex = questionIndex
                console.log('[STUDENT] Authoritatively moved to question index', questionIndex)
            } else {
                console.warn('[STUDENT] Question not in local list, question_id:', newQuestion.id)
            }

            // Clear feedback/answers for new question
            this.showFeedback = false
            this.isCorrectAnswer = false
            this.selectedOption = null
            this.userAnswer = ''
            this.userSequence = []
            this.questionUiStartTime = Date.now()

            // Initialize question UI elements
            this.initializeQuestion()
            
            // SYNCHRONIZED TIMER: Calculate remaining time from server timestamp (applied after initializeQuestion)
            if (data.started_at && data.time_limit_ms) {
                const serverStartTime = new Date(data.started_at).getTime()
                const now = Date.now()
                const elapsedMs = now - serverStartTime
                const remainingMs = Math.max(0, data.time_limit_ms - elapsedMs)
                this.questionTimeLeft = Math.ceil(remainingMs / 1000)
                this.questionDeadlineAt = now + remainingMs
                
                console.log('[STUDENT] Timer synchronized:', {
                    serverStartTime: data.started_at,
                    elapsedMs,
                    remainingMs,
                    questionTimeLeft: this.questionTimeLeft
                })
            } else {
                // Fallback to time_limit_ms if started_at is not available
                this.questionTimeLeft = Math.ceil((data.time_limit_ms || 30000) / 1000)
                this.questionDeadlineAt = Date.now() + this.questionTimeLeft * 1000
                console.warn('[STUDENT] No started_at timestamp, using fallback timer')
            }
            
            this.clearQuestionTimer()
            this.startQuestionTimer()
            
            // For sequence questions, initialize the sequence from the new question
            if (newQuestion.type === 'sequence') {
                const sequenceData = newQuestion.sequence_options || newQuestion.options
                this.userSequence = this.parseOptions(sequenceData)
                    .map((opt, idx) => ({ ...opt, id: opt.id || idx.toString() }))
                    .sort(() => Math.random() - 0.5)
            }
            
            console.log('[STUDENT] Question updated, timer set to', this.questionTimeLeft, 'seconds')
        },
        
        handleSessionStartedFromSocket(data) {
            console.log('[STUDENT] Handling session.started event:', data)
            
            // Update session config if provided
            if (data.session && data.session.config) {
                this.liveSessionConfig = data.session.config
                console.log('[STUDENT] Session config updated:', this.liveSessionConfig)
            }
            
            this.liveSessionWaiting = false
            this.liveSessionStatus = 'started'
            // Enable timers and buttons
            this.startGlobalTimer()
            console.log('[STUDENT] ✓ Session started - timers enabled')
        },
        
        handleQuestionResultsFromSocket(data) {
            console.log('[STUDENT] Handling question.results event:', data)
            // Can display results or feedback here if desired
        },
        
        async handleSessionFinishedFromSocket(data) {
            console.log('[STUDENT] Handling session.finished event:', data)
            console.log('[STUDENT] 📤 Teacher clicked Finish')
            
            if (this.feedbackTimeout) {
                clearTimeout(this.feedbackTimeout)
                this.feedbackTimeout = null
            }
            this.showFeedback = false
            
            // Submit any unsubmitted answer before finishing
            await this.submitCurrentQuestionAnswerBeforeNavigation()
            
            // Finish quiz and navigate to report
            this.finishQuiz()
        },
        
        handleTimerPausedFromSocket(data) {
            console.log('[STUDENT] Handling timer.paused event:', data)
            
            if (data.paused) {
                console.log('[STUDENT] Teacher paused the timer - stopping both exam and question timers')
                // CRITICAL: Pause BOTH timers (exam timer + question timer)
                this.clearQuestionTimer()
                this.clearGlobalTimer()
                // Freeze the displayed values; the old absolute deadlines must not
                // continue aging while the teacher has paused the session.
                this.questionDeadlineAt = null
                this.examDeadlineAt = null
                
                // Also pause any active timer intervals manually
                if (this.questionTimerInterval) {
                    clearInterval(this.questionTimerInterval)
                    this.questionTimerInterval = null
                }
                if (this.globalTimerInterval) {
                    clearInterval(this.globalTimerInterval)
                    this.globalTimerInterval = null
                }
                
                console.log('[STUDENT] ✓ Both timers paused')
            } else {
                console.log('[STUDENT] Teacher resumed the timer - restarting both exam and question timers')
                // Restart both timers
                this.startQuestionTimer()
                this.startGlobalTimer()
                
                console.log('[STUDENT] ✓ Both timers resumed')
            }
        },
        
        handleConfigUpdatedFromSocket(data) {
            console.log('[STUDENT] Handling config.updated event:', data)
            
            if (data.config) {
                this.liveSessionConfig = data.config
                console.log('[STUDENT] ✓ Session config updated in real-time:', this.liveSessionConfig)
                
                // Update UI to reflect changes (e.g., next button state)
                // This happens automatically through computed properties,
                // but good to log for debugging
                console.log('[STUDENT] Config changes:', {
                    auto_advance: this.liveSessionConfig.auto_advance,
                    teacher_paced: this.liveSessionConfig.teacher_paced,
                    show_results: this.liveSessionConfig.show_results
                })
            }
        },
        
        // Submit current question answer when teacher forces navigation
        // This ensures answers are saved even when teacher clicks next/previous
        async submitCurrentQuestionAnswerBeforeNavigation() {
            const currentQ = this.currentQuestion
            if (!currentQ || this.isTeacherMode) return // Don't submit in teacher mode
            
            // Check if already submitted for current question
            if (this.submittedAnswersLog[this.currentIndex]?.qId === currentQ.id) {
                return
            }

            const answerPayload = this.getFormattedAnswer()
            const timeSpentOnQuestion = Math.floor((Date.now() - (this.questionUiStartTime || Date.now())) / 1000)
            
            console.log('[TEACHER-NAV-ANSWER] Submitting answer before teacher-forced navigation:', {
                questionId: currentQ.id,
                questionType: currentQ.type,
                answerPayload: answerPayload,
                timeSpent: timeSpentOnQuestion,
            })
            
            // Don't submit if answer is null/empty (student hasn't answered)
            if (answerPayload === null || (typeof answerPayload === 'string' && answerPayload.trim() === '')) {
                console.log('[TEACHER-NAV-ANSWER] Skipping submission - no answer provided')
                return
            }
            
            // Submit the answer
            try {
                const result = await this.submitAnswerToAPI(answerPayload, timeSpentOnQuestion)
                this.submittedAnswersLog[this.currentIndex] = {
                    qId: currentQ.id,
                    ans: answerPayload,
                    correct: result.is_correct,
                    time: timeSpentOnQuestion,
                    apiResp: result.response,
                }
                console.log('[TEACHER-NAV-ANSWER] ✓ Answer submitted before navigation:', {
                    is_correct: result.is_correct,
                    points_earned: result.response?.points_earned
                })
            } catch (error) {
                console.error('[TEACHER-NAV-ANSWER] ✗ Failed to submit answer before navigation:', error.message)
                // Continue navigation even if submission fails - don't block the UI
            }
        },
        
        async handleQuestionNextFromSocket(data) {
            console.log('[STUDENT] Handling question.next event:', data)
            console.log('[STUDENT] 📤 Teacher clicked Next')
            
            // 1. Instantly clear any active feedback animation or timeout
            if (this.feedbackTimeout) {
                clearTimeout(this.feedbackTimeout)
                this.feedbackTimeout = null
            }
            this.showFeedback = false

            // 2. Silently submit student answer for the question being left
            this.submitCurrentQuestionAnswerBeforeNavigation()

            // 3. If payload contains question data, authoritatively route to it
            if (data && data.question) {
                this.handleQuestionStartedFromSocket(data)
            }
        },
        
        async handleQuestionPreviousFromSocket(data) {
            console.log('[STUDENT] Handling question.previous event:', data)
            console.log('[STUDENT] 📤 Teacher clicked Previous')
            
            // 1. Instantly clear any active feedback animation or timeout
            if (this.feedbackTimeout) {
                clearTimeout(this.feedbackTimeout)
                this.feedbackTimeout = null
            }
            this.showFeedback = false

            // 2. Silently submit student answer for the question being left
            this.submitCurrentQuestionAnswerBeforeNavigation()

            // 3. If payload contains question data, authoritatively route to it
            if (data && data.question) {
                this.handleQuestionStartedFromSocket(data)
            }
        },
        
        startQuestionTimer() {
            this.clearQuestionTimer()
            if (!this.questionDeadlineAt) {
                this.questionDeadlineAt = Date.now() + Math.max(0, Number(this.questionTimeLeft) || 0) * 1000
            }
            let timeoutHandled = false
            const update = async () => {
                this.questionTimeLeft = Math.max(0, Math.ceil((this.questionDeadlineAt - Date.now()) / 1000))
                if (this.questionTimeLeft <= 0 && !timeoutHandled) {
                    timeoutHandled = true
                    this.clearQuestionTimer()

                    if (!this.isTeacherMode && this.attemptId && this.currentQuestion.id) {
                        const timerKey = `q_timer_${this.attemptId}_${this.currentQuestion.id}`
                        localStorage.removeItem(timerKey)
                    }

                    // Auto-advance on timeout for ANY non-slide question. If the student
                    // picked/typed an answer it's submitted as-is; otherwise an empty
                    // submission is recorded and the quiz moves to the next question.
                    if (this.currentQuestion.type !== 'slide' && !this.showFeedback && !this.isViewingExistingAttempt) {
                        const answered = this.checkIfUserAnswered()
                        if (!answered) this.isCorrectAnswer = false
                        // Submit the answer and auto-advance immediately — no feedback
                        // overlay / button lock when the timer runs out.
                        await this.processAnswerSubmission(!answered, true)
                    }
                }
            }
            update()
            if (this.questionTimeLeft > 0) this.questionTimerInterval = setInterval(update, 250)
        },
        clearQuestionTimer() {
            if (this.questionTimerInterval) clearInterval(this.questionTimerInterval)
            this.questionTimerInterval = null
        },
        handleExamTimeout() {
            this.clearGlobalTimer()
            this.clearQuestionTimer()
            if (this.isExamFinished) return
            this.isExamTimeout = true
            // Submit current answer then finish -> report. No feedback overlay / lock.
            this.processAnswerSubmission(!this.checkIfUserAnswered(), true)
        },
        selectOption(index) {
            console.debug('selectOption called', index, 'isViewingExistingAttempt', this.isViewingExistingAttempt)
            if (this.isViewingExistingAttempt && !this.isTeacherMode) {
                // Prevent changing answers while viewing an existing attempt
                return
            }
            this.selectedOption = index
        },
        saveCurrentAnswerStateToSession() {
            this.sessionAnswersStore[this.currentIndex] = {
                selectedOption: this.selectedOption,
                userAnswer: this.userAnswer,
                userSequence: this.userSequence ? [...this.userSequence] : [],
                fromApi: this.sessionAnswersStore[this.currentIndex]?.fromApi || false,
            }
        },
        saveQuestionTimerState(useAbsoluteDeadline = false) {
            if (this.isTeacherMode || !this.attemptId || !this.currentQuestion?.id) return
            const timerKey = `q_timer_${this.attemptId}_${this.currentQuestion.id}`
            const state = useAbsoluteDeadline
                ? { deadlineAt: this.questionDeadlineAt || Date.now() }
                : { remainingSeconds: Math.max(0, Number(this.questionTimeLeft) || 0) }
            localStorage.setItem(timerKey, JSON.stringify(state))
        },
        async processAnswerSubmission(isQuestionTimeout = false, timedOut = false) {
            if (this.isViewingExistingAttempt && !this.isTeacherMode) {
                // Do not submit or modify answers when the user chose to view existing attempt
                await alertDialog({ message: this.$t ? this.$t('quizGame.notifications.viewingExistingAttempt') : 'Viewing previous attempt — reset to start a new one.' })
                return
            }
            this.clearQuestionTimer()
            this.clearGlobalTimer()

            this.saveCurrentAnswerStateToSession()
            this.saveQuestionTimerState()

            const answerPayload = this.getFormattedAnswer()
            const timeSpentOnQuestion = Math.floor((Date.now() - (this.questionUiStartTime || Date.now())) / 1000)
            let correct = false

            console.log('[ANSWER-SUBMISSION] Processing answer:', {
                questionType: this.currentQuestion.type,
                questionId: this.currentQuestion.id,
                answerPayload: answerPayload,
                timeSpentOnQuestion: timeSpentOnQuestion,
                isTeacherMode: this.isTeacherMode,
                isLiveSession: this.isLiveSession,
            })

            if (this.currentQuestion.type === 'slide') {
                correct = true
            } else if (isQuestionTimeout && !this.checkIfUserAnswered()) {
                // Timed out with no answer: record an empty submission server-side
                // so the attempt has a row for this question (counts as wrong).
                correct = false
                if (!this.isTeacherMode) {
                    try {
                        await this.submitAnswerToAPI(null, timeSpentOnQuestion, true)
                    } catch (e) {
                        console.warn('[ANSWER-SUBMISSION] Empty timeout submission failed:', e)
                    }
                    this.submittedAnswersLog[this.currentIndex] = {
                        qId: this.currentQuestion.id,
                        ans: null,
                        correct: false,
                        time: timeSpentOnQuestion,
                        timedOut: true,
                    }
                }
            } else if (answerPayload !== null && !this.isTeacherMode) {
                try {
                    console.log('[ANSWER-SUBMISSION] Submitting answer to API...')
                    const result = await this.submitAnswerToAPI(answerPayload, timeSpentOnQuestion)
                    console.log('[ANSWER-SUBMISSION] API response received:', {
                        is_correct: result.is_correct,
                        hasResponse: !!result.response,
                    })
                    correct = result.is_correct
                    this.submittedAnswersLog[this.currentIndex] = {
                        qId: this.currentQuestion.id,
                        ans: answerPayload,
                        correct,
                        time: timeSpentOnQuestion,
                        apiResp: result.response,
                    }
                } catch (error) {
                    console.error('[ANSWER-SUBMISSION] Error submitting answer:', error)
                    this.submittedAnswersLog[this.currentIndex] = {
                        qId: this.currentQuestion.id,
                        ans: answerPayload,
                        correct: false,
                        time: timeSpentOnQuestion,
                        error,
                    }
                }
            } else if (this.isTeacherMode && answerPayload !== null) {
                correct = this.localCheckAnswer(answerPayload)
                this.submittedAnswersLog[this.currentIndex] = {
                    qId: this.currentQuestion.id,
                    ans: answerPayload,
                    correct,
                    time: timeSpentOnQuestion,
                    teacherMode: true,
                }
            }

            this.isCorrectAnswer = correct
            // On a timeout (question or exam) advance silently — no feedback overlay,
            // no disabled buttons. Only show feedback for a real user-submitted answer.
            if (!this.isExamTimeout && !timedOut && this.currentQuestion.type !== 'slide') {
                this.showFeedback = true
            }

            if (this.feedbackTimeout) {
                clearTimeout(this.feedbackTimeout)
                this.feedbackTimeout = null
            }

            this.feedbackTimeout = setTimeout(
                () => {
                    this.showFeedback = false
                    this.feedbackTimeout = null

                    // Check if the exam is over first
                    if (this.isExamFinished) {
                        this.finishQuiz()
                        return
                    }
                    if (this.isExamTimeout) {
                        this.finishQuiz(true)
                        return
                    }

                    // In teacher-paced live session, do not auto-advance by oneself! Wait for teacher.
                    if (this.isLiveSession && (this.liveSessionConfig?.teacher_paced || !this.liveSessionConfig?.auto_advance)) {
                        console.log('[STUDENT] Live session in teacher-paced mode: waiting for teacher to advance')
                        return
                    }

                    // If not finished, proceed to the next question or finish normally
                    this.startGlobalTimer()

                    if (this.currentIndex < this.questions.length - 1) {
                        this.currentIndex++
                        this.initializeQuestion()
                    } else {
                        this.finishQuiz()
                    }
                },
                (this.currentQuestion.type === 'slide' || timedOut || this.isExamTimeout) ? 100 : 3000
            )
        },
        getFormattedAnswer() {
            const q = this.currentQuestion
            if (q.type === 'multiple_choice' || q.type === 'true_false') {
                const answer = this.selectedOption !== null
                    ? (this.optionsToDisplay.length > 0 ? this.optionsToDisplay : this.parseOptions(q.options))[this.selectedOption]?.text || this.selectedOption.toString()
                    : null
                if (this.isLiveSession) {
                    console.log('[FORMATTED-ANSWER] Multiple choice answer:', {
                        selectedOption: this.selectedOption,
                        answer: answer
                    })
                }
                return answer
            }
            if (q.type === 'ordinary') {
                const answer = (this.userAnswer || '').trim() || null
                if (this.isLiveSession) {
                    console.log('[FORMATTED-ANSWER] Ordinary answer:', {
                        userAnswer: this.userAnswer,
                        answer: answer
                    })
                }
                return answer
            }
            if (q.type === 'slide') return 'viewed'
            // Essay-style answers (free text in userAnswer).
            if (['essay', 'short_answer', 'long_answer', 'text'].includes(q.type)) {
                return (this.userAnswer || '').trim() || null
            }
            if (q.type === 'sequence') {
                // For sequence: return JSON of sequence items, or empty array if not answered
                const answer = this.userSequence.length > 0 
                    ? JSON.stringify(this.userSequence.map((item) => item.text))
                    : JSON.stringify([]) // Return empty array instead of null
                if (this.isLiveSession) {
                    console.log('[FORMATTED-ANSWER] Sequence answer:', {
                        userSequenceLength: this.userSequence.length,
                        userSequence: this.userSequence,
                        answer: answer,
                        parsedAnswer: answer ? JSON.parse(answer) : null
                    })
                }
                return answer
            }
            return null
        },
        
        // Get the option ID for live session submission
        getSelectedOptionId() {
            const q = this.currentQuestion
            if (q.type === 'multiple_choice' || q.type === 'true_false') {
                if (this.selectedOption !== null) {
                    const options = this.optionsToDisplay.length > 0 ? this.optionsToDisplay : this.parseOptions(q.options)
                    const selectedOpt = options[this.selectedOption]
                    // Return the actual option ID (from database or generated)
                    return selectedOpt?.id || null
                }
            }
            return null
        },
        
        async submitAnswerToAPI(answer, timeSpent, timedOut = false) {
            console.log('[STUDENT-ANSWER] Submitting answer:', {
                isLiveSession: this.isLiveSession,
                liveSessionId: this.liveSessionId,
                questionId: this.currentQuestion.id,
                questionType: this.currentQuestion.type,
                answer: answer,
                timeSpent: timeSpent
            })
            
            // UNIFIED SYSTEM: Always use regular endpoint, add live session params if needed
            const payload = {
                exam_id: this.examId,
                game_id: this.gameId,
                attempt_id: this.attemptId,
                answers: [{ 
                    question_id: this.currentQuestion.id, 
                    answer, 
                    time: timeSpent, 
                    timed_out: timedOut 
                }],
            }
            
            // Add live session parameters if in live session
            if (this.isLiveSession && this.liveSessionId) {
                payload.is_live_session = true
                payload.live_session_id = this.liveSessionId
                console.log('[STUDENT-ANSWER] Adding live session params:', {
                    is_live_session: true,
                    live_session_id: this.liveSessionId
                })
            }
            
            const livePin = this.$route.query.live_pin || null
            const qs = livePin ? `?live_pin=${livePin}` : ''
            
            try {
                const response = await apiCall('POST', `/api/student/exam/question${qs}`, payload)
                
                console.log('[STUDENT-ANSWER] ✓ Answer submitted successfully:', {
                    is_correct: response.results?.[0]?.is_correct,
                    points_earned: response.results?.[0]?.points_earned
                })
                
                const qResult = response.results?.find((r) => r.question_id === this.currentQuestion.id)
                if (response.attempt_status === 'completed' || response.exam_timed_out) {
                    this.isExamFinished = true
                }
                
                return { is_correct: qResult?.is_correct || false, response }
            } catch (e) {
                console.error('[STUDENT-ANSWER] ✗ Answer submission failed:', e)
                throw e
            }
        },
        localCheckAnswer(answerPayload) {
            const q = this.currentQuestion
            if (q.type === 'multiple_choice' || q.type === 'true_false')
                return this.selectedOption !== null
                    ? (this.optionsToDisplay.length > 0 ? this.optionsToDisplay : this.parseOptions(q.options))[this.selectedOption]?.correct === true
                    : false
            return false
        },
        checkIfUserAnswered() {
            const qType = this.currentQuestion.type
            if (qType === 'multiple_choice' || qType === 'true_false') return this.selectedOption !== null
            if (qType === 'ordinary') return (this.userAnswer || '').trim() !== ''
            if (qType === 'sequence') return this.userSequence.length > 0
            // Essay-style answers all live in userAnswer.
            if (['essay', 'short_answer', 'long_answer', 'text'].includes(qType)) {
                return (this.userAnswer || '').trim() !== ''
            }
            return false
        },
        previousQuestion() {
            if (this.isAssignmentMode) return
            if (this.showFeedback || this.currentIndex === 0) return
            this.clearQuestionTimer()
            this.saveCurrentAnswerStateToSession()
            this.saveQuestionTimerState()
            this.currentIndex--
            this.initializeQuestion()
        },
        handleNextOrFinish() {
            if (this.showFeedback) return
            // If viewing an existing attempt, allow navigation/finish without submitting
            if (this.isViewingExistingAttempt && !this.isTeacherMode) {
                if (this.currentIndex < this.questions.length - 1) {
                    this.currentIndex++
                    this.initializeQuestion()
                } else {
                    // Finish and navigate to report
                    this.finishQuiz()
                }
                return
            }

            this.processAnswerSubmission()
        },
        async finishQuiz(timedOut = false) {
            this.isExamFinished = true
            this.stopBackgroundMusic()
            this.clearGlobalTimer()
            this.clearQuestionTimer()

            if (!this.isTeacherMode) {
                localStorage.removeItem(this.examTimerStorageKey)
                if (this.attemptId) {
                    this.questions.forEach((q) => {
                        const timerKey = `q_timer_${this.attemptId}_${q.id}`
                        localStorage.removeItem(timerKey)
                    })
                }
            }

            const validSubs = this.submittedAnswersLog.filter(
                (a) => a && this.questions.find((q) => q.id === a.qId)?.type !== 'slide'
            )
            const correctCount = validSubs.filter((a) => a.correct).length
            const totalScorable = this.questions.filter((q) => q.type !== 'slide').length
            const score = totalScorable > 0 ? Math.round((correctCount / totalScorable) * 100) : 0

            // Try to fetch server report (to get authoritative score, points, and rank)
            if (this.attemptId && !this.isTeacherMode) {
                try {
                    const resp = await apiCall('GET', `/api/student/exam/exam/results/${this.attemptId}`)
                    if (resp && resp.exam_report) {
                        const rpt = resp.exam_report
                        this.finalScore = rpt.student_performance?.score ?? score
                        this.correctAnswers = rpt.student_performance?.correct_answers ?? correctCount
                        this.totalScorableQuestions = rpt.student_performance?.total_questions ?? totalScorable
                        this.rankInfo = rpt.rank_info ?? null
                    } else {
                        this.finalScore = score
                        this.correctAnswers = correctCount
                        this.totalScorableQuestions = totalScorable
                        this.rankInfo = null
                    }
                } catch (e) {
                    console.warn('Could not fetch exam report:', e)
                    this.finalScore = score
                    this.correctAnswers = correctCount
                    this.totalScorableQuestions = totalScorable
                    this.rankInfo = null
                }
            } else {
                this.finalScore = score
                this.correctAnswers = correctCount
                this.totalScorableQuestions = totalScorable
            }

            this.showResultsModal = true

            // Subscribe to real-time updates for this attempt (if Echo is configured)
            if (this.attemptId) {
                this.subscribeAttemptChannel()
            }

            // Immediately navigate to the report page after exam ends
            if (!this.isTeacherMode && this.attemptId) {
                this.$router.push('/student/report/assigned-ayhoots/' + this.attemptId)
            }
        },
        closeResultsModalAndNavigate() {
            this.showResultsModal = false
            this.$router.push('/student/report/assigned-ayhoots/' + this.attemptId)
        },
        parseOptions(options) {
            return parseOptionsUtil(options)
        },
        getShuffledOptions(options, questionId) {
            if (!options) return []
            
            const parsed = this.parseOptions(options)
            
            // If we haven't shuffled this question's options yet, do it now and cache it
            if (!this.shuffledOptionsMap[questionId]) {
                const shuffled = parsed.slice().sort(() => Math.random() - 0.5)
                this.shuffledOptionsMap[questionId] = shuffled
                console.log('[SHUFFLE-OPTIONS] Shuffled options for question', questionId, '- count:', shuffled.length)
            }
            
            return this.shuffledOptionsMap[questionId]
        },
        formatTime: (s) => {
            const n = Number(s)
            if (!Number.isFinite(n) || n < 0) return '0:00'
            return `${Math.floor(n / 60)}:${(n % 60).toString().padStart(2, '0')}`
        },
        getBackgroundStyle() {
            const defaultBgUrl = '/imgHolders/bg3.jpg'
            let primaryBgUrl = ''

            if (this.currentQuestion && this.currentQuestion.q_options) {
                try {
                    let options = this.currentQuestion.q_options
                    // If it's a string, parse it; if it's already an object, use it directly
                    if (typeof options === 'string') {
                        options = JSON.parse(options)
                    }
                    if (options && options.background_image) {
                        // Use getImageUrl to properly format the background image path
                        primaryBgUrl = this.getImageUrl(options.background_image, false)
                    }
                } catch (e) {
                    console.error('Could not parse q_options for background, using default.', e)
                }
            }

            if (primaryBgUrl) {
                return `background-image: url('${primaryBgUrl}'), url('${defaultBgUrl}')`
            } else {
                return `background-image: url('${defaultBgUrl}')`
            }
        },
        getFileUrl(filePath) {
            if (!filePath) return ''
            
            // Remove backslashes and any existing leading slashes
            const cleanP = filePath.replace(/\\/g, '').replace(/^\//, '')
            
            // If it's already a full URL, return it
            if (cleanP.startsWith('http')) return cleanP

            // If it already contains the correct path, just prepend a slash
            if (cleanP.startsWith('uploads/questions/')) return `/${cleanP}`
            
            // For question files (the "files" array in your JSON), 
            // we assume they belong in the questions folder
            return `/uploads/questions/${cleanP}`
        },

        getImageUrl(path, isOptionImage = false) {
            if (!path) return '/imgHolders/CardHolderImage.jpg'
            if (path.startsWith('http')) return path
            
            const cleanP = path.replace(/\\/g, '').replace(/^\//, '')

            // 1. Logic for Option Images
            if (isOptionImage || cleanP.startsWith('option_') || cleanP.startsWith('tf_option_')) {
                if (cleanP.startsWith('uploads/options/')) return `/${cleanP}`
                // Remove 'options/' if it's already there to prevent /options/options/
                const fileName = cleanP.replace('options/', '')
                return `/uploads/options/${fileName}`
            }

            // 2. Logic for everything else (Questions)
            if (cleanP.startsWith('uploads/questions/')) return `/${cleanP}`
            const fileName = cleanP.replace('questions/', '')
            return `/uploads/questions/${fileName}`
        },
        // getFileUrl(filePath) {
        //     if (!filePath) return ''
        //     const cleanP = filePath.replace(/\\/g, '')
            
        //     // If it's already a full path starting with /uploads, check if it's an option image
        //     if (cleanP.startsWith('/uploads')) {
        //         // Check if it's an option image but missing the options/ subdirectory
        //         const pathWithoutPrefix = cleanP.replace(/^\/uploads\//, '')
        //         if ((pathWithoutPrefix.startsWith('option_') || pathWithoutPrefix.startsWith('tf_option_')) && !pathWithoutPrefix.startsWith('options/')) {
        //             return `/uploads/options/${pathWithoutPrefix}`
        //         }
        //         return cleanP
        //     }
            
        //     // If it's a relative path, check if it's an option image
        //     const pathWithoutSlash = cleanP.replace(/^\//, '')
        //     if ((pathWithoutSlash.startsWith('option_') || pathWithoutSlash.startsWith('tf_option_')) && !pathWithoutSlash.startsWith('options/')) {
        //         return `/uploads/options/${pathWithoutSlash}`
        //     }
            
        //     // If it already has options/ prefix, use it as is
        //     if (pathWithoutSlash.startsWith('options/')) {
        //         return `/uploads/${pathWithoutSlash}`
        //     }
            
        //     return `/uploads/${pathWithoutSlash}`
        // },
        // getImageUrl(path, isOptionImage = false) {
        //     if (!path) return '/imgHolders/CardHolderImage.jpg'
        //     if (path.startsWith('http')) return path
        //     if (path.startsWith('/uploads/')) {
        //         // If it's already a full path, check if it needs options/ subdirectory
        //         const pathWithoutPrefix = path.replace(/^\/uploads\//, '')
        //         if ((pathWithoutPrefix.startsWith('option_') || pathWithoutPrefix.startsWith('tf_option_')) && !pathWithoutPrefix.startsWith('options/')) {
        //             return `/uploads/options/${pathWithoutPrefix}`
        //         }
        //         return path
        //     }
            
        //     // If path already contains uploads/options/ or uploads/questions/, just add leading slash
        //     if (path.startsWith('uploads/options/') || path.startsWith('uploads/questions/')) {
        //         return `/${path}`
        //     }
            
        //     // Handle different image types with correct paths
        //     if (isOptionImage || path.startsWith('option_') || path.startsWith('tf_option_')) {
        //         return `/uploads/options/${path}`
        //     } else if (path.includes('questions/') || path.startsWith('question_')) {
        //         return `/uploads/questions/${path}`
        //     } else {
        //         return `/uploads/${path.replace(/\\/g, '')}`
        //     }
        // },
        handleImageError: (e) => {
            // Instead of hiding the image, show a placeholder
            e.target.src = '/imgHolders/CardHolderImage.jpg'
            console.warn('Img load error, showing placeholder:', e.target.src)
        },
        isHTML: (str) => str && /<\/?[a-z][\s\S]*>/i.test(str),
        launchConfetti() {
            const cvs = document.getElementById('confetti-canvas')
            if (!cvs) return
            const cnf = confetti.create(cvs, { resize: true, useWorker: true })
            const dur = 2000,
                end = Date.now() + dur
            ;(function frame() {
                cnf({ particleCount: 3, spread: 60, origin: { y: 0.6 } })
                cnf({
                    particleCount: 2,
                    spread: 100,
                    angle: 90,
                    origin: { x: Math.random(), y: Math.random() * 0.5 + 0.2 },
                })
                if (Date.now() < end) requestAnimationFrame(frame)
            })()
        },
        editQuestion() {
            // Role-aware: a hardcoded /teacher path was blocked for institutes in preview.
            const role = localStorage.getItem('role') || 'teacher'
            this.$router.push(`/${role}/exam/${this.examId}/create`)
        },
        getShapeColor: (shapeName) => `bg-${SHAPE_MAP.find((s) => shapeName?.includes(s.name))?.color || 'gray'}-500`,
        getShapeContainerClass(shapeName) {
            const shape = SHAPE_MAP.find((s) => shapeName?.includes(s.name))
            return `bg-${shape?.color || 'gray'}-500 rounded-sm`
        },
        getShapeSvgComponent(shapeName) {
            const s = SHAPE_MAP.find((s) => shapeName?.includes(s.name))
            if (!s) return 'RectangleSvg'
            switch (s.icon) {
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
        subscribeAttemptChannel() {
            try {
                if (!this.attemptId) return
                if (window && window.Echo) {
                    const channelNames = [`attempt.${this.attemptId}`, `exam_attempt.${this.attemptId}`, `attempts.${this.attemptId}`]
                    for (const name of channelNames) {
                        try {
                            const ch = window.Echo.private ? window.Echo.private(name) : window.Echo.channel(name)
                            if (ch) {
                                this.attemptEchoChannel = ch
                                ch.listen('AttemptUpdated', (e) => {
                                    if (e.rank_info) this.rankInfo = e.rank_info
                                    if (e.student_performance) {
                                        this.finalScore = e.student_performance.score ?? this.finalScore
                                        this.correctAnswers = e.student_performance.correct_answers ?? this.correctAnswers
                                        this.totalScorableQuestions = e.student_performance.total_questions ?? this.totalScorableQuestions
                                    }
                                })
                                break
                            }
                        } catch (err) {
                            // try next pattern
                        }
                    }
                }
            } catch (e) {
                console.warn('subscribeAttemptChannel failed', e)
            }
        },
        unsubscribeAttemptChannel() {
            try {
                if (this.attemptEchoChannel && window && window.Echo) {
                    try {
                        if (this.attemptEchoChannel.leave) this.attemptEchoChannel.leave()
                    } catch (e) {}
                    this.attemptEchoChannel = null
                }
            } catch (e) {
                console.warn('unsubscribeAttemptChannel failed', e)
            }
        },
        playCorrectAudio() {
            if (this.isMuted) return
            try {
                const audio = new Audio('/musics/Correct.mp3')
                audio.volume = 0.7
                audio.play().catch((e) => console.debug('Could not play correct audio:', e))
            } catch (e) {
                console.debug('Audio error:', e)
            }
        },
        playIncorrectAudio() {
            if (this.isMuted) return
            try {
                const audio = new Audio('/musics/Incorrect.mp3')
                audio.volume = 0.7
                audio.play().catch((e) => console.debug('Could not play incorrect audio:', e))
            } catch (e) {
                console.debug('Audio error:', e)
            }
        },
        initBackgroundMusic() {
            if (!this.shouldPlayBackgroundMusic) return
            if (!this.bgAudio) {
                this.bgAudio = new Audio('/musics/Background.mp3')
                this.bgAudio.loop = true
                this.bgAudio.volume = 0.25
                this.bgAudio.muted = this.isMuted
            }
            const playPromise = this.bgAudio.play()
            if (playPromise !== undefined) {
                playPromise.catch((err) => {
                    console.debug('Autoplay prevented, will play on first interaction:', err)
                    this.audioInteractionHandler = () => {
                        if (this.bgAudio && this.shouldPlayBackgroundMusic && !this.isExamFinished) {
                            this.bgAudio.play().catch(() => {})
                        }
                        this.removeAudioInteractionListeners()
                    }
                    window.addEventListener('pointerdown', this.audioInteractionHandler, { once: true })
                    window.addEventListener('keydown', this.audioInteractionHandler, { once: true })
                })
            }
        },
        removeAudioInteractionListeners() {
            if (this.audioInteractionHandler) {
                window.removeEventListener('pointerdown', this.audioInteractionHandler)
                window.removeEventListener('keydown', this.audioInteractionHandler)
                this.audioInteractionHandler = null
            }
        },
        stopBackgroundMusic() {
            this.removeAudioInteractionListeners()
            if (this.bgAudio) {
                try {
                    this.bgAudio.pause()
                    this.bgAudio.currentTime = 0
                } catch (e) {
                    console.debug('Error stopping background audio:', e)
                }
                this.bgAudio = null
            }
        },
        toggleMute() {
            this.isMuted = !this.isMuted
            if (this.bgAudio) {
                this.bgAudio.muted = this.isMuted
            }
        },
    },
}
</script>

<style>
.ghost {
    opacity: 0.5;
    background: #c8ebfb;
    border: 1px dashed #4aa5d4;
}

.sortable-drag {
    opacity: 0.8;
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    transition:
        transform 0.2s,
        box-shadow 0.2s;
}

.quill-content :deep(.ql-align-center) {
    text-align: center;
}

.quill-content :deep(.ql-align-right) {
    text-align: right;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.h-30 {
    height: 7.5rem;
}

.w-30 {
    width: 7.5rem;
}

.max-h-70 {
    max-height: 17.5rem;
}

.cursor-move {
    cursor: move;
}

.cursor-zoom-in {
    cursor: zoom-in;
}

.cursor-zoom-out {
    cursor: zoom-out;
}
</style>
