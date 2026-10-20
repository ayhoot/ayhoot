<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center p-4">
        <div
            v-if="joinedSession && connectionState !== 'connected'"
            class="fixed top-4 left-1/2 -translate-x-1/2 z-[70] rounded-xl px-4 py-3 border text-sm font-medium shadow-lg"
            :class="connectionBannerClass"
        >
            {{ connectionBannerText }}
        </div>
        <div class="max-w-md w-full">
            <!-- Join Form -->
            <div v-if="!joinedSession" class="bg-white rounded-2xl shadow-xl p-8">
                <div class="text-center mb-8">
                    <div class="text-6xl mb-4">🎮</div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Join Live Session</h1>
                    <p class="text-gray-600">Enter the game PIN to join</p>
                </div>

                <form @submit.prevent="joinSession" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"> Game PIN </label>
                        <input
                            v-model="pin"
                            type="text"
                            maxlength="6"
                            placeholder="123456"
                            class="w-full px-4 py-3 text-2xl text-center border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            :class="{ 'border-red-500': error }"
                        />
                        <div v-if="error" class="text-red-500 text-sm mt-2">{{ error }}</div>
                    </div>

                    <div v-if="!user">
                        <label class="block text-sm font-medium text-gray-700 mb-2"> Your Name (optional) </label>
                        <input
                            v-model="nickname"
                            type="text"
                            placeholder="Enter your name"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="loading || pin.length !== 6"
                        class="w-full bg-blue-500 hover:bg-blue-600 disabled:bg-gray-300 text-white font-bold py-3 px-4 rounded-lg transition-colors"
                    >
                        {{ loading ? 'Joining...' : 'Join Session' }}
                    </button>
                </form>

                <div class="mt-8 text-center">
                    <p class="text-sm text-gray-600">Don't have a PIN? Ask your teacher for the game code</p>
                </div>
            </div>

            <!-- Waiting Room Modal with Blur -->
            <div
                v-else-if="session && session.status === 'waiting'"
                class="fixed inset-0 z-50 flex items-center justify-center"
            >
                <!-- Backdrop with blur -->
                <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>

                <!-- Modal Content -->
                <div class="relative bg-white rounded-2xl shadow-2xl p-8 max-w-md mx-4 text-center">
                    <div class="mb-6">
                        <div class="w-20 h-20 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg
                                class="w-10 h-10 text-blue-600 animate-spin"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                ></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Waiting for Teacher</h2>
                        <p class="text-gray-600">Please wait while the teacher starts the live session...</p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <p class="text-sm text-gray-500 mb-2">Session PIN</p>
                        <p class="text-3xl font-bold text-blue-600 font-mono">{{ session.pin }}</p>
                    </div>

                    <div class="bg-blue-50 rounded-lg p-4 mb-4">
                        <div class="text-center">
                            <div class="text-sm text-gray-600 mb-1">Session</div>
                            <div class="text-lg font-bold text-blue-600">
                                {{ session.title || session.exam?.title }}
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-700 mb-3 text-sm">Participants Joined</h3>
                        <div class="space-y-2 max-h-32 overflow-y-auto">
                            <div
                                v-for="participant in participants"
                                :key="participant.id"
                                class="flex items-center gap-3 p-2 bg-white rounded-lg"
                            >
                                <div
                                    class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm"
                                >
                                    {{ participant.name?.charAt(0).toUpperCase() || '?' }}
                                </div>
                                <div class="flex-1 text-left">
                                    <div class="font-medium text-sm text-gray-900">{{ participant.name }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3 text-gray-600 text-sm">
                            {{ participants.length }} student{{ participants.length !== 1 ? 's' : '' }} joined
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shared play UI -->
            <PlayGame
                :session="session"
                :currentQuestion="currentQuestion"
                :questionResults="questionResults"
                :finalRankings="finalRankings"
                :showingResults="showingResults"
                :hasAnswered="hasAnswered"
                :myAnswer="myAnswer"
                :myParticipantId="myParticipantId"
                :myFinalResult="myFinalResult"
                :questionTimeRemaining="questionTimeRemaining"
                :currentQuestionIndex="currentQuestionIndex"
                :totalQuestions="totalQuestions"
                :timePercentage="timePercentage"
                @submit-answer="submitAnswer"
                @leave-session="leaveSession"
            />
        </div>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { setupStudentSocket } from '@/services/liveSocket'
import QRCode from 'qrcode.vue'
import PlayGame from '@/components/PlayGame.vue'
import { apiCall } from '@/services/apiService'

export default {
    name: 'StudentJoinLive',
    components: {
        QRCode,
        PlayGame,
    },
    setup() {
        const route = useRoute()
        const router = useRouter()

        const pin = ref(route.params.pin || '')
        const nickname = ref('')
        const loading = ref(false)
        const error = ref('')
        const joinedSession = ref(false)
        const session = ref(null)
        const participants = ref([])
        const currentQuestion = ref(null)
        const questionResults = ref(null)
        const finalRankings = ref([])
        const showingResults = ref(false)
        const hasAnswered = ref(false)
        const myAnswer = ref(null)
        const myParticipantId = ref(null)
        const myFinalResult = ref(null)
        const questionTimer = ref(null)
        const questionTimeRemaining = ref(30)
        const user = ref(null)
        const connectionState = ref('connecting')
        const connectionErrorMessage = ref('')

        const currentQuestionIndex = computed(() => session.value?.current_question_index || 0)
        const totalQuestions = computed(() => session.value?.exam?.questions?.length || 0)
        const timePercentage = computed(() => {
            const totalTime = session.value?.current_question_time_limit / 1000 || 30
            return (questionTimeRemaining.value / totalTime) * 100
        })
        const connectionBannerClass = computed(() => {
            if (connectionState.value === 'error') {
                return 'bg-red-50 border-red-200 text-red-700'
            }
            if (connectionState.value === 'disconnected') {
                return 'bg-orange-50 border-orange-200 text-orange-700'
            }
            return 'bg-blue-50 border-blue-200 text-blue-700'
        })
        const connectionBannerText = computed(() => {
            if (connectionState.value === 'error') {
                return connectionErrorMessage.value || 'Connection error. Reconnecting...'
            }
            if (connectionState.value === 'disconnected') {
                return 'Connection lost. Using backup sync and reconnecting...'
            }
            return 'Connecting to live server...'
        })

        let echo = null
        let liveSocket = null

        const loadUser = async () => {
            try {
                const response = await apiCall('GET', '/api/user')
                user.value = response
                nickname.value = user.value.name
            } catch (error) {
                console.error('Error loading user:', error)
            }
        }

        const joinSession = async () => {
            if (pin.value.length !== 6) {
                error.value = 'Please enter a valid 6-digit PIN'
                return
            }

            loading.value = true
            error.value = ''

            try {
                const response = await apiCall('POST', `/api/live-session/${pin.value}/join`, {
                    nickname: nickname.value || 'Guest_' + Math.random().toString(36).substr(2, 6),
                })

                session.value = response.session
                myParticipantId.value = response.participant.id
                participants.value = [response.participant]
                joinedSession.value = true

                console.log('Joined session successfully:', {
                    session: session.value,
                    joinedSession: joinedSession.value,
                    sessionStatus: session.value.status,
                    participant: response.participant,
                    is_late_joiner: response.is_late_joiner,
                    should_wait: response.should_wait,
                })

                // Check if this is a late joiner (session already started)
                if (response.is_late_joiner) {
                    console.log('Late joiner detected - redirecting to play with current question')
                    // Redirect immediately for late joiners
                    if (response.redirect_url) {
                        router.push(response.redirect_url)
                        return
                    }
                }

                // Check if student should wait
                if (response.should_wait) {
                    console.log('Student should wait for teacher to start')
                    // Stay on waiting room - don't redirect yet
                    setupWebSocket()
                    return
                }

                // If backend returned a redirect to the exam play page (with live_pin), follow it
                if (response.redirect_url) {
                    console.log('Redirecting to play URL from live session join', response.redirect_url)
                    // Use router to navigate to the student exam play page including live_pin
                    router.push(response.redirect_url)
                    return
                }

                setupWebSocket()
            } catch (err) {
                // NOTE: do not name this `error` — it would shadow the `error` ref
                // and the message would never reach the template. apiCall throws an
                // Error whose `.response` is the parsed JSON body ({ message }).
                console.error('Error joining session:', err)
                error.value = err.response?.message || err.message || 'Failed to join session'
            } finally {
                loading.value = false
            }
        }

        const setupWebSocket = () => {
            try {
                connectionState.value = 'connecting'
                connectionErrorMessage.value = ''

                // Use the shared student socket helper so the event names stay in sync
                // with the backend broadcastAs() names (leading-dot custom events).
                liveSocket = setupStudentSocket(pin.value, {
                    participantJoined: (e) => {
                        const p = e?.participant || e
                        if (p && p.id && !participants.value.find((x) => x.id === p.id)) {
                            participants.value.push(p)
                        }
                    },
                    sessionStarted: (e) => {
                        if (e?.session) session.value = e.session
                        // When the host starts the session, move the student into the live
                        // exam play page where questions are answered in real time.
                        if (session.value && session.value.exam_id) {
                            router.push(
                                `/student/exam/${session.value.exam_id}/play?live_pin=${session.value.pin}&live_session_id=${session.value.id}`
                            )
                        }
                    },
                    sessionFinished: (e) => {
                        // Host ended the session while the student was still in the waiting room.
                        if (session.value) session.value.status = 'finished'
                        finalRankings.value = e?.rankings || []
                        stopQuestionTimer()
                        stopPolling()
                    },
                })

                echo = liveSocket.echo

                liveSocket.onConnection({
                    onConnected: () => {
                        connectionState.value = 'connected'
                        connectionErrorMessage.value = ''
                        stopPolling()
                    },
                    onStateChange: (states) => {
                        if (states.current === 'connecting') connectionState.value = 'connecting'
                    },
                    onDisconnected: () => {
                        connectionState.value = 'disconnected'
                        startPolling()
                    },
                    onUnavailable: () => {
                        connectionState.value = 'disconnected'
                        startPolling()
                    },
                    onError: (err) => {
                        connectionState.value = 'error'
                        connectionErrorMessage.value = err?.error?.message || 'WebSocket connection error.'
                        startPolling()
                    },
                })
            } catch (err) {
                console.error('Error setting up WebSocket:', err)
                connectionState.value = 'error'
                connectionErrorMessage.value = err?.message || 'Failed to initialize live connection.'
                startPolling()
            }
        }

        let pollingInterval = null

        const startPolling = () => {
            if (pollingInterval) return

            console.log('Starting polling as fallback')
            pollingInterval = setInterval(async () => {
                try {
                    const response = await apiCall('GET', `/api/live-session/${pin.value}`)
                    const updatedSession = response.session

                    // Update session if status changed
                    if (updatedSession.status !== session.value.status) {
                        session.value = updatedSession

                        // Handle different states
                        if (updatedSession.status === 'question' && !currentQuestion.value) {
                            // Load current question
                            const questionResponse = await apiCall(
                                'GET',
                                `/api/live-session/${pin.value}/current-question`
                            )
                            currentQuestion.value = questionResponse.question
                            startQuestionTimer(30)
                        }
                    }
                } catch (error) {
                    console.error('Polling error:', error)
                }
            }, 3000) // Poll every 3 seconds
        }

        const stopPolling = () => {
            if (pollingInterval) {
                clearInterval(pollingInterval)
                pollingInterval = null
            }
        }

        const startQuestionTimer = (timeLimit) => {
            stopQuestionTimer()
            questionTimeRemaining.value = timeLimit

            questionTimer.value = setInterval(() => {
                questionTimeRemaining.value--
                if (questionTimeRemaining.value <= 0) {
                    stopQuestionTimer()
                }
            }, 1000)
        }

        const stopQuestionTimer = () => {
            if (questionTimer.value) {
                clearInterval(questionTimer.value)
                questionTimer.value = null
            }
        }

        const submitAnswer = async (optionId) => {
            if (hasAnswered.value) return

            hasAnswered.value = true

            try {
                const limitSec = (session.value?.current_question_time_limit || 30000) / 1000
                const remSec = questionTimeRemaining.value ?? 0
                const timeSpent = Math.max(1, Math.round(limitSec - remSec))

                const response = await apiCall('POST', `/api/live-session/${session.value.id}/submit-answer`, {
                    question_id: currentQuestion.value.id,
                    selected_option_id: optionId,
                    time_spent: timeSpent,
                })

                myAnswer.value = response.answer
            } catch (error) {
                console.error('Error submitting answer:', error)
                hasAnswered.value = false
            }
        }

        const playCelebrationSound = () => {
            // Create and play celebration sound
            const audio = new Audio('/sounds/celebration.mp3')
            audio.play().catch((e) => console.log('Could not play sound:', e))
        }

        const leaveSession = () => {
            router.push('/student/dashboard')
        }

        const getTimeColor = () => {
            if (questionTimeRemaining.value <= 5) return 'text-red-600'
            if (questionTimeRemaining.value <= 10) return 'text-yellow-600'
            return 'text-green-600'
        }

        const getTimerColor = () => {
            if (questionTimeRemaining.value <= 5) return 'bg-red-500'
            if (questionTimeRemaining.value <= 10) return 'bg-yellow-500'
            return 'bg-green-500'
        }

        const getOptionClass = (optionId) => {
            if (hasAnswered.value) {
                return 'border-gray-300 bg-gray-50 cursor-not-allowed'
            }
            return 'border-blue-300 hover:border-blue-500 hover:bg-blue-50 cursor-pointer'
        }

        const getResultOptionClass = (stat) => {
            if (stat.option.is_correct) {
                return 'border-green-500 bg-green-50'
            }
            if (myAnswer.value && myAnswer.value.selected_option_id === stat.option.id) {
                return 'border-red-500 bg-red-50'
            }
            return 'border-gray-200 bg-gray-50'
        }

        const getResultBarClass = (stat) => {
            if (stat.option.is_correct) {
                return 'bg-green-500'
            }
            return 'bg-blue-500'
        }

        onMounted(async () => {
            // Wait for the user first so auto-join uses their real name instead of
            // a random Guest_… nickname (the previous code raced loadUser).
            await loadUser()

            // Auto-join if PIN is in URL (e.g. arriving from a scanned QR code).
            if (pin.value) {
                joinSession()
            }
        })

        onUnmounted(() => {
            stopQuestionTimer()
            stopPolling()
            if (liveSocket) {
                liveSocket.leave()
            } else if (echo) {
                echo.leave(`live-session.${pin.value}`)
            }
        })

        return {
            pin,
            nickname,
            loading,
            error,
            joinedSession,
            session,
            participants,
            currentQuestion,
            questionResults,
            finalRankings,
            showingResults,
            hasAnswered,
            myAnswer,
            myParticipantId,
            myFinalResult,
            questionTimeRemaining,
            currentQuestionIndex,
            totalQuestions,
            timePercentage,
            connectionState,
            connectionBannerClass,
            connectionBannerText,
            joinSession,
            submitAnswer,
            leaveSession,
            getTimeColor,
            getTimerColor,
            getOptionClass,
            getResultOptionClass,
            getResultBarClass,
        }
    },
}
</script>
