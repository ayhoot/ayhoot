<template>
    <div class="flex items-center justify-between p-2 px-8 bg-white/80 text-gray-800 shrink-0">
        <div class="flex justify-between items-center w-full">
            <div class="flex items-center gap-2">
                <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M15.0001 2.3999V5.9999C15.0001 6.66264 15.5373 7.1999 16.2001 7.1999H19.8001M8.40008 7.1999H10.8001M8.40008 10.7999H15.6001M8.40008 14.3999H15.6001M18.0001 4.1999C17.466 3.72204 16.9118 3.15528 16.5619 2.78718C16.3291 2.54224 16.0074 2.3999 15.6695 2.3999H6.5998C5.27432 2.3999 4.19981 3.47441 4.1998 4.79989L4.19971 19.1998C4.1997 20.5253 5.27421 21.5998 6.5997 21.5998L17.3997 21.5999C18.7252 21.5999 19.7997 20.5254 19.7998 19.2L19.8001 6.47773C19.8001 6.1709 19.683 5.87594 19.4701 5.65503C19.0763 5.24655 18.4187 4.57442 18.0001 4.1999Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                <h1 class="text-sm md:text-2xl font-bold text-center">
                    {{ title || $t('quizGame.header.defaultTitle') }}
                </h1>
                <!-- Eye Icon Indicator for View-Only Mode -->
                <div v-if="isViewingExistingAttempt" class="flex items-center gap-1 ml-4 px-3 py-1 bg-amber-100 rounded-full border border-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <span class="text-xs font-semibold text-amber-700">{{ $t('quizGame.viewOnlyMode') || 'View Only' }}</span>
                </div>
            </div>
            <div class="flex sm:justify-between justify-end items-center gap-2 sm:gap-2 py-4 sm:py-0">
                <span
                    class="font-bold bg-[#4285F4] hover:bg-[#3450AE] shadow-[0_2px_0_rgba(52,80,174,1)] hover:shadow-none hover:translate-y-[2px] rounded-sm p-2 cursor-pointer text-white text-xs md:text-sm"
                >
                    {{ currentIndex + 1 }}/{{ totalQuestions }}
                </span>
                <div
                    class="bg-[#bbb12e] hover:bg-[rgb(133,116,65)] font-bold shadow-[0_2px_0_rgba(99,86,48,1)] hover:shadow-none hover:translate-y-[2px] p-2 rounded-sm cursor-pointer text-white text-xs md:text-sm"
                >
                    <span class="hidden sm:inline">{{ $t('quizGame.header.questionTime') }}</span>
                    {{ formatTime(questionTimeLeft) }}
                </div>
                <div
                    class="bg-[#FF6464] hover:bg-[rgb(192,51,51)] shadow-[0_2px_0_rgba(192,51,51,1)] hover:shadow-none hover:translate-y-[2px] p-2 rounded-sm cursor-pointer text-white text-xs md:text-sm"
                >
                    <span class="font-bold">
                        <span class="hidden sm:inline">{{ $t('quizGame.header.examTime') }}</span>
                        {{ formatTime(totalTimeLeft) }}
                    </span>
                </div>
                <button
                    v-if="isTeacherMode"
                    @click="$emit('edit')"
                    class="px-3 py-2 bg-[#EBCD1F] hover:bg-[#8A7812] shadow-[0_2px_0_rgba(138,121,18,1)] hover:shadow-none hover:translate-y-[2px] rounded-sm font-bold text-xs md:text-base transition-all duration-200 text-white cursor-pointer"
                >
                    {{ $t('quizGame.header.edit') }}
                </button>
                <button
                    v-if="showNavigationButtons"
                    @click="$emit('previous')"
                    :disabled="isPreviousDisabled"
                    class="px-3 py-2 bg-[#3450AE] hover:bg-[#152B65] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px] rounded-sm font-bold text-xs md:text-base transition-all duration-200 text-white disabled:bg-gray-400 disabled:cursor-not-allowed cursor-pointer"
                >
                    {{ $t('quizGame.header.prev') }}
                </button>
                <button
                    v-if="showNavigationButtons"
                    @click="$emit('next')"
                    :disabled="isNextDisabled"
                    class="px-3 py-2 bg-[#3450AE] hover:bg-[#152B65] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px] rounded-sm font-bold text-xs md:text-base transition-all duration-200 text-white disabled:bg-gray-400 disabled:cursor-not-allowed cursor-pointer"
                >
                    {{ isLastQuestion ? $t('quizGame.header.finish') : $t('quizGame.header.next') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'QuizHeader',
    props: {
        title: String,
        currentIndex: Number,
        totalQuestions: Number,
        questionTimeLeft: Number,
        totalTimeLeft: Number,
        isTeacherMode: Boolean,
        isViewingExistingAttempt: Boolean,
        showNavigationButtons: Boolean,
        isPreviousDisabled: Boolean,
        isNextDisabled: Boolean,
        isLastQuestion: Boolean
    },
    methods: {
        formatTime(seconds) {
            if (seconds < 0) seconds = 0
            const m = Math.floor(seconds / 60)
            const s = Math.floor(seconds % 60)
            return `${m}:${s < 10 ? '0' : ''}${s}`
        }
    }
}
</script>
