import { ref, isRef, computed, onMounted, onUnmounted, watch } from 'vue'
import { apiCall } from "@/services/apiService"
import { setupHostSocket } from '@/services/liveSocket'
import QRCode from 'qrcode'

export function useHostSession(sessionId) {
  // Accept either a ref or a plain id; the rest of the composable uses sessionId.value.
  sessionId = isRef(sessionId) ? sessionId : ref(sessionId)
  const session = ref(null)
  const exam = ref(null)
  const participants = ref([])
  const selectedParticipant = ref(null)
  const currentQuestion = ref(null)
  const questionResults = ref(null)
  const finalRankings = ref([])
  const questionTimeRemaining = ref(30)
  const sessionTimeRemaining = ref(0)
  const timerPaused = ref(false)
  const answerCounts = ref({})
  const submittedAnswers = ref([])
  const liveRankings = ref([])
  const showingResults = ref(false)
  const qrCodeUrl = ref(null)
  const autoAdvance = ref(false)
  const isNavigating = ref(false)
  const isConfigEditing = ref(false)
  const sessionConfig = ref({
    auto_advance: false,
    teacher_paced: true,
    show_results: true
  })

  const totalQuestions = computed(() => exam.value?.questions?.length || 0)
  const examDuration = computed(() => {
    if (exam.value?.duration) return exam.value.duration
    if (exam.value?.questions) {
      const totalSeconds = exam.value.questions.reduce((sum, q) => sum + (q.duration || q.time || 30), 0)
      return Math.ceil(totalSeconds / 60)
    }
    return 0
  })
  const timePercentage = computed(() => {
    const total = (session.value?.current_question_time_limit || 30000) / 1000
    return Math.max(0, (questionTimeRemaining.value / total) * 100)
  })

  let socket = null
  let questionTimer = null
  let sessionTimer = null
  let lastSessionSaveAt = 0

  const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return `${mins}:${secs.toString().padStart(2, '0')}`
  }

  const getAnswerCount = (optionId) => answerCounts.value[optionId] || 0

  const parseOptions = (options) => {
    if (!options) return []
    if (Array.isArray(options)) return options
    if (typeof options === 'string') {
      try {
        const parsed = JSON.parse(options)
        return Array.isArray(parsed) ? parsed : []
      } catch (e) {
        return []
      }
    }
    return []
  }

  const formatQuestionData = (question) => {
    if (!question) return null
    return {
      ...question,
      options: parseOptions(question.options),
      sequence_options: question.sequence_options ? parseOptions(question.sequence_options) : null,
      files: Array.isArray(question.files) ? question.files : (
        typeof question.files === 'string' 
          ? JSON.parse(question.files) 
          : question.files ? [question.files] : []
      ),
      q_options: typeof question.q_options === 'string' 
        ? JSON.parse(question.q_options) 
        : question.q_options,
    }
  }

  const updateLiveRankings = () => {
    liveRankings.value = [...participants.value].sort((a, b) => (b.total_score || 0) - (a.total_score || 0))
    saveSessionData()
  }

  const saveSessionData = () => {
    try {
      const now = Date.now()
      if (now - lastSessionSaveAt < 3000) return
      lastSessionSaveAt = now

      const data = {
        session: session.value ? {
          id: session.value.id,
          pin: session.value.pin,
          status: session.value.status,
          current_question_index: session.value.current_question_index,
          current_question_started_at: session.value.current_question_started_at,
          current_question_time_limit: session.value.current_question_time_limit,
        } : null,
        participants: participants.value.map(p => ({
          id: p.id,
          student_id: p.student_id,
          name: p.name,
          avatar: p.avatar,
          total_score: p.total_score || 0,
          correct_answers: p.correct_answers || 0,
          current_rank: p.current_rank || null,
          average_answer_time: p.average_answer_time || null,
        })),
        answerCounts: answerCounts.value,
        liveRankings: liveRankings.value.slice(0, 50),
        timestamp: Date.now(),
      }
      localStorage.setItem(`liveSession_${sessionId.value}`, JSON.stringify(data))
    } catch (e) {
      console.warn('Failed to save session data:', e)
    }
  }

  const restoreSessionData = () => {
    try {
      const data = JSON.parse(localStorage.getItem(`liveSession_${sessionId.value}`))
      if (data && Date.now() - data.timestamp < 3600000) {
        if (data.participants) participants.value = data.participants
        if (data.answerCounts) answerCounts.value = data.answerCounts
        return true
      }
    } catch (e) {
      console.warn('Failed to restore session data:', e)
    }
    return false
  }

  const startQuestionTimer = (durationMs, startedAt = null) => {
    if (questionTimer) clearInterval(questionTimer)
    
    if (startedAt) {
      const serverStartTime = new Date(startedAt).getTime()
      const now = Date.now()
      const elapsedMs = now - serverStartTime
      const remainingMs = Math.max(0, durationMs - elapsedMs)
      questionTimeRemaining.value = Math.ceil(remainingMs / 1000)
    } else {
      questionTimeRemaining.value = Math.ceil(durationMs / 1000)
    }
    
    questionTimer = setInterval(() => {
      if (questionTimeRemaining.value > 0) {
        questionTimeRemaining.value--
      } else {
        clearInterval(questionTimer)
        questionTimer = null
        if (autoAdvance.value) {
          setTimeout(() => nextQuestion(), 500)
        }
      }
    }, 1000)
  }

  const startSessionTimer = (totalSeconds) => {
    if (sessionTimer) clearInterval(sessionTimer)
    timerPaused.value = false
    
    if (session.value?.started_at) {
      const startTime = new Date(session.value.started_at).getTime()
      const now = Date.now()
      const elapsedSeconds = Math.floor((now - startTime) / 1000)
      sessionTimeRemaining.value = Math.max(0, totalSeconds - elapsedSeconds)
    } else {
      sessionTimeRemaining.value = totalSeconds
    }
    
    sessionTimer = setInterval(() => {
      if (!timerPaused.value && sessionTimeRemaining.value > 0) {
        sessionTimeRemaining.value--
      } else if (sessionTimeRemaining.value <= 0) {
        clearInterval(sessionTimer)
        sessionTimer = null
      }
    }, 1000)
  }

  const pauseTimer = async () => {
    timerPaused.value = true
    try {
      await apiCall('POST', `/api/live-session/${sessionId.value}/timer-pause`, {
        paused: true,
        remainingSeconds: sessionTimeRemaining.value
      })
    } catch (e) {
      console.warn('Failed to broadcast pause:', e)
    }
  }

  const resumeTimer = async () => {
    timerPaused.value = false
    try {
      await apiCall('POST', `/api/live-session/${sessionId.value}/timer-pause`, {
        paused: false,
        remainingSeconds: sessionTimeRemaining.value
      })
    } catch (e) {
      console.warn('Failed to broadcast resume:', e)
    }
  }

  const startSession = async () => {
    try {
      await apiCall('POST', `/api/live-session/${sessionId.value}/start`, {})
    } catch (e) {
      console.error('Failed to start session:', e)
    }
  }

  const startQuestion = async () => {
    try {
      await apiCall('POST', `/api/live-session/${sessionId.value}/start-question`, {
        time_limit: currentQuestion.value?.duration || 30
      })
    } catch (e) {
      console.error('Failed to start question:', e)
    }
  }

  const nextQuestion = async () => {
    if (isNavigating.value) return
    isNavigating.value = true
    try {
      const currentIndex = session.value?.current_question_index || 0
      const total = exam.value?.questions?.length || 0
      
      if (currentIndex >= (total - 1)) {
        await finishSession()
        return
      }
      
      if (questionTimer) clearInterval(questionTimer)
      answerCounts.value = {}
      submittedAnswers.value = []
      
      const response = await apiCall('POST', `/api/live-session/${sessionId.value}/next-question`, {
        auto_advance: autoAdvance.value
      })
      
      if (response?.session) session.value = response.session
      if (response?.question) currentQuestion.value = formatQuestionData(response.question)
    } catch (e) {
      console.error('Failed to move to next question:', e)
    } finally {
      setTimeout(() => {
        isNavigating.value = false
      }, 400)
    }
  }

  const previousQuestion = async () => {
    if (isNavigating.value) return
    isNavigating.value = true
    try {
      const currentIndex = session.value?.current_question_index || 0
      if (currentIndex <= 0) return
      
      if (questionTimer) clearInterval(questionTimer)
      answerCounts.value = {}
      submittedAnswers.value = []
      
      const response = await apiCall('POST', `/api/live-session/${sessionId.value}/previous-question`, {})
      // State will be updated via WebSocket
    } catch (e) {
      console.error('Failed to move to previous question:', e)
    } finally {
      setTimeout(() => {
        isNavigating.value = false
      }, 400)
    }
  }

  const finishSession = async () => {
    try {
      if (questionTimer) clearInterval(questionTimer)
      if (sessionTimer) clearInterval(sessionTimer)
      
      const resp = await apiCall('POST', `/api/live-session/${sessionId.value}/finish`, {})
      if (resp?.session) {
        if (session.value) Object.assign(session.value, resp.session)
        else session.value = resp.session
      }
      if (resp?.final_rankings) {
        finalRankings.value = resp.final_rankings
      }
    } catch (e) {
      console.error('Failed to finish session:', e)
    }
  }

  const toggleAutoAdvance = () => {
    autoAdvance.value = !autoAdvance.value
  }

  const updateSessionConfig = async () => {
    try {
      await apiCall('POST', `/api/live-session/${sessionId.value}/update-config`, {
        ...sessionConfig.value
      })
      isConfigEditing.value = false
    } catch (e) {
      console.error('Failed to update session config:', e)
    }
  }

  const loadQrCode = async () => {
    try {
      if (!session.value?.pin) return
      const resp = await apiCall('GET', `/api/live-session/${session.value.pin}/qr`)
      if (resp?.join_url) {
        qrCodeUrl.value = await QRCode.toDataURL(resp.join_url, {
          errorCorrectionLevel: 'H',
          width: 200,
          margin: 1,
        })
      }
    } catch (e) {
      console.warn('Failed to generate QR code:', e)
    }
  }

  const loadParticipants = async () => {
    try {
      const pResp = await apiCall('GET', `/api/live-session/${sessionId.value}/participants`)
      if (pResp?.participants) {
        participants.value = pResp.participants.map(p => ({
          id: p.id,
          student_id: p.student_id,
          name: p.nickname || (p.student?.name || 'Guest'),
          avatar: p.avatar || (p.student?.avatar || '/images/default-avatar.png'),
          total_score: p.total_score || 0,
          correct_answers: p.correct_answers || 0,
          current_rank: p.current_rank,
          average_answer_time: p.average_answer_time,
        }))
        updateLiveRankings()
      }
    } catch (e) {
      console.warn('Failed to load participants:', e)
    }
  }

  const loadSession = async () => {
    try {
      restoreSessionData()
      const resp = await apiCall('GET', `/api/live-session/id/${sessionId.value}`)
      
      session.value = resp.session
      exam.value = resp.exam || resp.session?.exam
      
      if (resp.session?.config) {
        sessionConfig.value = {
          auto_advance: resp.session.config.auto_advance || false,
          teacher_paced: resp.session.config.teacher_paced !== false,
          show_results: resp.session.config.show_results !== false,
        }
      }
      
      if (!exam.value && session.value?.exam) exam.value = session.value.exam
      
      currentQuestion.value = formatQuestionData(resp.current_question)

      if (resp.current_answers && Array.isArray(resp.current_answers)) {
        submittedAnswers.value = resp.current_answers
        resp.current_answers.forEach(a => {
          if (a.selected_option_id) {
            answerCounts.value[a.selected_option_id] = (answerCounts.value[a.selected_option_id] || 0) + 1
          }
        })
      }

      let totalDurationMinutes = exam.value?.duration
      if (!totalDurationMinutes && exam.value?.questions) {
        const totalSeconds = exam.value.questions.reduce((sum, q) => sum + (q.duration || q.time || 30), 0)
        totalDurationMinutes = Math.ceil(totalSeconds / 60)
      }

      if (totalDurationMinutes && session.value?.status !== 'waiting') {
        startSessionTimer(totalDurationMinutes * 60)
      } else if (totalDurationMinutes) {
        sessionTimeRemaining.value = totalDurationMinutes * 60
      }

      if (currentQuestion.value && session.value.current_question_time_limit) {
        startQuestionTimer(
          session.value.current_question_time_limit,
          session.value.current_question_started_at
        )
      }

      await loadParticipants()
      await loadQrCode()

      socket = setupHostSocket(session.value.pin, {
        participantJoined: (e) => {
          if (!participants.value.find(p => p.id === e.participant.id)) {
            participants.value.push({
              id: e.participant.id,
              student_id: e.participant.student_id,
              name: e.participant.name,
              avatar: e.participant.avatar,
              total_score: 0,
              correct_answers: 0,
              current_rank: null,
              average_answer_time: null,
            })
            updateLiveRankings()
          }
        },
        sessionStarted: (e) => {
          if (e?.session) {
            Object.assign(session.value, e.session)
            if (exam.value?.duration || exam.value?.questions) {
              let duration = exam.value.duration || Math.ceil(exam.value.questions.reduce((sum, q) => sum + (q.duration || q.time || 30), 0) / 60)
              startSessionTimer(duration * 60)
            }
            saveSessionData()
          }
        },
        answerSubmitted: (e) => {
          const optId = e.answer?.selected_option_id
          if (optId) answerCounts.value[optId] = (answerCounts.value[optId] || 0) + 1
          
          const pId = e.participant?.id
          const newAns = {
            participant_id: pId,
            name: e.participant?.name || 'Student',
            avatar: e.participant?.avatar,
            selected_option_id: optId,
            answer_text: e.answer?.answer_text,
            is_correct: e.answer?.is_correct,
            points_earned: e.answer?.points_earned,
            answer_time_ms: e.answer?.answer_time_ms,
            submitted_at: new Date()
          }
          const existingIdx = submittedAnswers.value.findIndex(a => 
            (pId && a.participant_id === pId) || (a.name === newAns.name)
          )
          if (existingIdx !== -1) {
            submittedAnswers.value[existingIdx] = newAns
          } else {
            submittedAnswers.value.push(newAns)
          }

          if (e.participant?.id) {
            const idx = participants.value.findIndex(p => p.id === e.participant.id)
            if (idx !== -1) {
              participants.value[idx] = {
                ...participants.value[idx],
                total_score: e.participant.total_score || participants.value[idx].total_score,
                correct_answers: e.participant.correct_answers || participants.value[idx].correct_answers,
                current_rank: e.participant.current_rank || participants.value[idx].current_rank,
                average_answer_time: e.participant.average_answer_time || participants.value[idx].average_answer_time,
              }
            }
          }
          updateLiveRankings()
          saveSessionData()
        },
        questionStarted: (e) => {
          currentQuestion.value = formatQuestionData(e.question)
          answerCounts.value = {}
          submittedAnswers.value = []
          startQuestionTimer(e.time_limit_ms, e.started_at)
          showingResults.value = false
          questionResults.value = null
        },
        nextQuestion: (e) => {
          if (e.session) Object.assign(session.value, e.session)
          currentQuestion.value = formatQuestionData(e.question)
          answerCounts.value = {}
          submittedAnswers.value = []
          showingResults.value = false
          questionResults.value = null
          startQuestionTimer(e.time_limit_ms || (30 * 1000), e.started_at)
          saveSessionData()
        },
        previousQuestion: (e) => {
          if (e.session) Object.assign(session.value, e.session)
          currentQuestion.value = formatQuestionData(e.question)
          answerCounts.value = {}
          submittedAnswers.value = []
          showingResults.value = false
          questionResults.value = null
          startQuestionTimer(e.time_limit_ms || (30 * 1000), e.started_at)
          saveSessionData()
        },
        configUpdated: (e) => {
          if (e.config) {
            sessionConfig.value = {
              auto_advance: e.config.auto_advance || false,
              teacher_paced: e.config.teacher_paced !== false,
              show_results: e.config.show_results !== false,
            }
          }
        },
        questionResults: (e) => {
          questionResults.value = e
          showingResults.value = true
          if (questionTimer) clearInterval(questionTimer)
          setTimeout(() => {
            showingResults.value = false
            questionResults.value = null
          }, 5000)
        },
        sessionFinished: (e) => {
          if (session.value) session.value.status = 'finished'
          finalRankings.value = e.final_rankings || e.rankings || []
          if (questionTimer) clearInterval(questionTimer)
          if (sessionTimer) clearInterval(sessionTimer)
        }
      })
    } catch (e) {
      console.error('Failed to load session:', e)
    }
  }

  onMounted(() => {
    loadSession()
  })

  onUnmounted(() => {
    if (questionTimer) clearInterval(questionTimer)
    if (sessionTimer) clearInterval(sessionTimer)
    // Socket cleanup should be handled by the socket service or here
  })

  return {
    session, exam, participants, selectedParticipant, currentQuestion, 
    questionResults, finalRankings, questionTimeRemaining, sessionTimeRemaining,
    timerPaused, answerCounts, submittedAnswers, liveRankings, showingResults, qrCodeUrl,
    autoAdvance, isConfigEditing, sessionConfig, totalQuestions, examDuration,
    timePercentage, formatTime, getAnswerCount, pauseTimer, resumeTimer,
    startSession, startQuestion, nextQuestion, previousQuestion, finishSession,
    toggleAutoAdvance, updateSessionConfig, loadParticipants, isNavigating
  }
}
