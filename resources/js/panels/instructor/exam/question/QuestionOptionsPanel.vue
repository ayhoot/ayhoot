<template>
    <div class="rounded-lg">
        <!-- Question Type -->
        <div class="mb-4">
            <label
                class="block text-sm font-semibold text-gray-700 mb-1"
                :class="{ 'text-right': $i18n.locale === 'fa' }"
            >{{ $t('createQuestion.options.questionType') }}</label>
            <select
                v-model="question.questionType"
                @change="$emit('type-change')"
                class="w-full px-3 py-3 border-2 border-gray-300 cursor-pointer rounded-sm font-medium focus:outline-none sm:text-sm"
            >
                <option value="multiple_choice">{{ $t('createQuestion.questionTypes.quiz') }}</option>
                <option value="true_false">{{ $t('createQuestion.questionTypes.trueOrFalse') }}</option>
                <option value="ordinary">{{ $t('createQuestion.questionTypes.shortAnswer') }}</option>
                <option value="sequence">{{ $t('createQuestion.questionTypes.puzzle') }}</option>
                <option value="slide">{{ $t('createQuestion.questionTypes.slide') }}</option>
            </select>
        </div>

        <!-- Duration -->
        <div class="mb-4" v-if="question.questionType !== 'slide'">
            <label
                class="block text-sm font-semibold text-gray-700 mb-1"
                :class="{ 'text-right': $i18n.locale === 'fa' }"
            >{{ $t('createQuestion.options.duration') }}</label>
            <AySelect v-model="question.duration">
                <option value="10">10s</option>
                <option value="20">20s</option>
                <option value="30">30s</option>
                <option value="40">40s</option>
                <option value="50">50s</option>
                <option value="60">1m</option>
                <option value="120">2m</option>
                <option value="180">3m</option>
                <option value="240">4m</option>
            </AySelect>
        </div>

        <!-- Points -->
        <div class="mb-4" v-if="question.questionType !== 'slide'">
            <label
                class="block text-sm font-semibold text-gray-700 mb-1"
                :class="{ 'text-right': $i18n.locale === 'fa' }"
            >{{ $t('createQuestion.options.points') }}</label>
            <select
                v-model="question.points"
                class="w-full px-3 py-3 border-2 border-gray-300 cursor-pointer rounded-sm font-medium focus:outline-none sm:text-sm"
            >
                <option :value="0">{{ $t('createQuestion.pointValues.noPoint') }}</option>
                <option :value="5">{{ $t('createQuestion.pointValues.standard') }}</option>
                <option :value="10">{{ $t('createQuestion.pointValues.doublePoint') }}</option>
            </select>
        </div>

        <!-- Difficulty -->
        <div class="mb-4" v-if="question.questionType !== 'slide'">
            <label
                class="block text-sm font-semibold text-gray-700 mb-1"
                :class="{ 'text-right': $i18n.locale === 'fa' }"
            >{{ $t('createQuestion.options.difficulty') }}</label>
            <AySelect v-model="question.difficultyLevel">
                <option value="easy">{{ $t('createQuestion.difficultyLevels.easy') }}</option>
                <option value="medium">{{ $t('createQuestion.difficultyLevels.medium') }}</option>
                <option value="hard">{{ $t('createQuestion.difficultyLevels.hard') }}</option>
            </AySelect>
        </div>

        <!-- Background -->
        <div class="mb-4">
            <h3 class="text-sm font-semibold mb-2" :class="{ 'text-right': $i18n.locale === 'fa' }">
                {{ $t('createQuestion.options.background') }}
            </h3>
            <div class="grid grid-cols-3 gap-2" :class="$i18n.locale === 'fa' ? 'pl-1' : 'pr-1'">
                <img
                    v-for="(img, idx) in bgOptions"
                    :key="idx"
                    :src="img"
                    class="w-full h-12 object-cover rounded cursor-pointer border-2 hover:border-blue-500 transition"
                    :class="{
                        'border-blue-500': question.bg === img,
                        'border-transparent': question.bg !== img,
                    }"
                    @click="question.bg = img"
                />
            </div>
        </div>
    </div>
</template>

<script>
import AySelect from '@/components/atoms/form/AySelect.vue'

export default {
    name: 'QuestionOptionsPanel',
    components: { AySelect },
    props: {
        // The shared reactive question object; fields are edited in place via v-model.
        question: { type: Object, required: true },
        bgOptions: { type: Array, default: () => [] },
    },
    emits: ['type-change'],
}
</script>
