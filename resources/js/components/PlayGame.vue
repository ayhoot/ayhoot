<template>
    <div>
        <!-- Waiting Room (handled by parent when appropriate) -->

        <!-- Question Screen -->
        <div v-if="session && session.status === 'question' && currentQuestion" class="bg-white rounded-2xl shadow-xl p-8">
            <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">
                        Question {{ currentQuestionIndex + 1 }} of {{ totalQuestions }}
                    </span>
                    <span class="text-lg font-bold" :class="getTimeColor()">
                        {{ questionTimeRemaining }}s
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div 
                        class="h-3 rounded-full transition-all duration-1000"
                        :class="getTimerColor()"
                        :style="{ width: `${timePercentage}%` }"
                    ></div>
                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">{{ currentQuestion.question_text }}</h2>
                <img 
                    v-if="currentQuestion.slide"
                    :src="`/uploads/${currentQuestion.slide}`"
                    :alt="currentQuestion.question_text"
                    class="w-full max-w-sm mx-auto rounded-lg mb-6"
                >
            </div>

            <div class="space-y-3">
                <button 
                    v-for="option in currentQuestion.options" 
                    :key="option.id"
                    @click="$emit('submit-answer', option.id)"
                    :disabled="hasAnswered"
                    class="w-full p-4 text-left border-2 rounded-lg transition-all hover:scale-105"
                    :class="getOptionClass(option.id)"
                >
                    <div class="font-medium">{{ option.option_text }}</div>
                </button>
            </div>

            <div v-if="hasAnswered" class="mt-6 text-center">
                <div class="text-green-600 font-semibold">✓ Answer submitted!</div>
                <p class="text-gray-600 text-sm mt-2">Waiting for other students...</p>
            </div>
        </div>

        <!-- Results Screen -->
        <div v-else-if="showingResults && questionResults" class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-6">
                <div class="text-4xl mb-2">📊</div>
                <h2 class="text-2xl font-bold text-gray-800">Question Results</h2>
            </div>

            <div class="space-y-3 mb-6">
                <div 
                    v-for="stat in questionResults.option_stats" 
                    :key="stat.option.id"
                    class="p-4 border-2 rounded-lg"
                    :class="getResultOptionClass(stat)"
                >
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-medium">{{ stat.option.option_text }}</span>
                        <span class="text-sm font-bold">{{ Math.round(stat.percentage) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div 
                            class="h-2 rounded-full"
                            :class="getResultBarClass(stat)"
                            :style="{ width: `${stat.percentage}%` }"
                        ></div>
                    </div>
                    <div class="text-sm text-gray-600 mt-1">
                        {{ stat.count }} students chose this
                    </div>
                    <div v-if="stat.option.is_correct" class="text-green-600 font-medium mt-2">
                        ✓ Correct Answer
                    </div>
                    <div v-else-if="myAnswer && myAnswer.selected_option_id === stat.option.id" class="text-red-600 font-medium mt-2">
                        ✗ Your Answer
                    </div>
                </div>
            </div>

            <div v-if="myAnswer" class="bg-blue-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-800 mb-2">Your Performance</h3>
                <div class="flex justify-between items-center">
                    <span class="text-blue-600">
                        {{ myAnswer.is_correct ? '✓ Correct!' : '✗ Incorrect' }}
                    </span>
                    <span class="font-bold text-blue-800">
                        +{{ myAnswer.points_earned }} points
                    </span>
                </div>
                <div class="text-sm text-blue-600 mt-1">
                    Answer time: {{ (myAnswer.answer_time_ms / 1000).toFixed(2) }}s
                </div>
            </div>

            <div>
                <h3 class="font-semibold text-gray-700 mb-3">Current Rankings</h3>
                <div class="space-y-2">
                    <div 
                        v-for="(participant, index) in questionResults.rankings.slice(0, 10)" 
                        :key="participant.id"
                        class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg"
                        :class="{ 'bg-yellow-50 border-2 border-yellow-400': participant.id === myParticipantId }"
                    >
                        <div class="font-bold text-gray-600 w-6">#{{ participant.rank }}</div>
                        <img 
                            :src="participant.avatar" 
                            :alt="participant.name"
                            class="w-8 h-8 rounded-full"
                        >
                        <div class="flex-1">
                            <div class="font-medium">{{ participant.name }}</div>
                            <div class="text-sm text-gray-500">{{ participant.score }} points</div>
                        </div>
                        <div v-if="participant.id === myParticipantId" class="text-yellow-600 font-bold">
                            YOU
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Final Results -->
        <div v-else-if="session && session.status === 'finished'" class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <div class="text-6xl mb-4 animate-bounce">🎉</div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Session Complete!</h2>
                <p class="text-gray-600">Great job! Here are the final results</p>
            </div>

            <div v-if="myFinalResult" class="bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg p-6 mb-6">
                <h3 class="text-xl font-bold mb-3">Your Final Result</h3>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold">#{{ myFinalResult.rank }}</div>
                        <div class="text-sm opacity-90">Rank</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ myFinalResult.score }}</div>
                        <div class="text-sm opacity-90">Points</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ myFinalResult.correct_answers }}</div>
                        <div class="text-sm opacity-90">Correct</div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Final Rankings</h3>
                <div class="space-y-3">
                    <div 
                        v-for="(participant, index) in finalRankings" 
                        :key="participant.id"
                        class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg"
                        :class="index < 3 ? 'border-2 border-yellow-400' : ''"
                    >
                        <div class="text-2xl w-12 text-center">
                            {{ index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : `#${index + 1}` }}
                        </div>
                        <img 
                            :src="participant.avatar" 
                            :alt="participant.name"
                            class="w-12 h-12 rounded-full"
                        >
                        <div class="flex-1">
                            <div class="font-bold">{{ participant.name }}</div>
                            <div class="text-sm text-gray-600">
                                {{ participant.score }} points • {{ participant.correct_answers }} correct
                            </div>
                        </div>
                        <div v-if="participant.id === myParticipantId" class="text-yellow-600 font-bold">
                            YOU
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center">
                <button 
                    @click="$emit('leave-session')"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition-colors"
                >
                    Leave Session
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'PlayGame',
    props: {
        session: Object,
        currentQuestion: Object,
        questionResults: Object,
        finalRankings: Array,
        showingResults: Boolean,
        hasAnswered: Boolean,
        myAnswer: Object,
        myParticipantId: [Number, String],
        myFinalResult: Object,
        questionTimeRemaining: Number,
        currentQuestionIndex: Number,
        totalQuestions: Number,
        timePercentage: Number
    },
    methods: {
        getTimeColor() {
            if (this.questionTimeRemaining <= 5) return 'text-red-600'
            if (this.questionTimeRemaining <= 10) return 'text-yellow-600'
            return 'text-green-600'
        },
        getTimerColor() {
            if (this.questionTimeRemaining <= 5) return 'bg-red-500'
            if (this.questionTimeRemaining <= 10) return 'bg-yellow-500'
            return 'bg-green-500'
        },
        getOptionClass(optionId) {
            if (this.hasAnswered) {
                return 'border-gray-300 bg-gray-50 cursor-not-allowed'
            }
            return 'border-blue-300 hover:border-blue-500 hover:bg-blue-50 cursor-pointer'
        },
        getResultOptionClass(stat) {
            if (stat.option.is_correct) {
                return 'border-green-500 bg-green-50'
            }
            if (this.myAnswer && this.myAnswer.selected_option_id === stat.option.id) {
                return 'border-red-500 bg-red-50'
            }
            return 'border-gray-200 bg-gray-50'
        },
        getResultBarClass(stat) {
            if (stat.option.is_correct) {
                return 'bg-green-500'
            }
            return 'bg-blue-500'
        }
    }
}
</script>
