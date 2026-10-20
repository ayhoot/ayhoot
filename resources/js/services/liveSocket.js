import { realtime } from '@/services/realtime'

// Setup a host WebSocket connection and return helpers to bind event handlers.
// Uses the shared realtime service so every feature multiplexes one connection.
export function setupHostSocket(pin, handlers = {}) {
    const channelName = `live-session.${pin}`
    const channel = realtime.channel(channelName)

    let handlersAlreadyBound = false

    const bindHandlers = (h = handlers) => {
        if (!channel || handlersAlreadyBound) return
        handlersAlreadyBound = true
        if (h.participantJoined) channel.listen('.participant.joined', (d) => h.participantJoined(d))
        if (h.sessionStarted) channel.listen('.session.started', (d) => h.sessionStarted(d))
        if (h.questionStarted) channel.listen('.question.started', (d) => h.questionStarted(d))
        if (h.answerSubmitted) channel.listen('.answer.submitted', (d) => h.answerSubmitted(d))
        if (h.questionResults) channel.listen('.question.results', (d) => h.questionResults(d))
        if (h.nextQuestion) channel.listen('.question.next', (d) => h.nextQuestion(d))
        if (h.previousQuestion) channel.listen('.question.previous', (d) => h.previousQuestion(d))
        if (h.sessionFinished) channel.listen('.session.finished', (d) => h.sessionFinished(d))
        if (h.timerPaused) channel.listen('.timer.paused', (d) => h.timerPaused(d))
        if (h.configUpdated) channel.listen('.config.updated', (d) => h.configUpdated(d))
    }

    

    const leave = () => realtime.leave(channelName)
    const onConnection = (cbs = {}) => realtime.onConnection(cbs)

    channel.subscribed(() => {
        if (Object.keys(handlers).length > 0 && !handlersAlreadyBound) bindHandlers(handlers)
    })

    return { echo: realtime.getEcho(), channel, bindHandlers, leave, onConnection }
}

// Setup a student WebSocket connection for real-time question updates.
export function setupStudentSocket(pin, handlers = {}) {
    const channelName = `live-session.${pin}`
    const channel = realtime.channel(channelName)

    let handlersAlreadyBound = false

    const bindHandlers = (h = handlers) => {
        if (!channel || handlersAlreadyBound) return
        handlersAlreadyBound = true

        // Participant joined - used by the waiting room to show who has joined
        if (h.participantJoined) channel.listen('.participant.joined', (d) => h.participantJoined(d))
        if (h.questionStarted) channel.listen('.question.started', (d) => h.questionStarted(d))
        if (h.questionNext) channel.listen('.question.next', (d) => h.questionNext(d))
        if (h.questionPrevious) channel.listen('.question.previous', (d) => h.questionPrevious(d))
        if (h.sessionStarted) channel.listen('.session.started', (d) => h.sessionStarted(d))
        if (h.questionResults) channel.listen('.question.results', (d) => h.questionResults(d))
        if (h.sessionFinished) channel.listen('.session.finished', (d) => h.sessionFinished(d))
        if (h.timerPaused) channel.listen('.timer.paused', (d) => h.timerPaused(d))
        if (h.configUpdated) channel.listen('.config.updated', (d) => h.configUpdated(d))
    }

    const leave = () => realtime.leave(channelName)
    const onConnection = (cbs = {}) => realtime.onConnection(cbs)

    channel.subscribed(() => {
        if (Object.keys(handlers).length > 0 && !handlersAlreadyBound) bindHandlers(handlers)
    })

    return { echo: realtime.getEcho(), channel, bindHandlers, leave, onConnection }
}
