<template>
    <div class="w-full" :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'">
        <div class="flex gap-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 pb-4 w-full">
                <label
                    class="flex items-center bg-green-500 hover:bg-green-600 shadow-[0_2px_0_rgba(28,162,54,1)] p-3 rounded-md w-full cursor-pointer"
                    :class="{ 'ring-2 ring-offset-2 ring-white': modelValue === 'true' }"
                >
                    <input
                        type="radio"
                        :checked="modelValue === 'true'"
                        @change="$emit('update:modelValue', 'true')"
                        name="true-false-option"
                        value="true"
                        class="form-radio h-4 w-4 text-indigo-600 sr-only"
                    />
                    <span :class="$i18n.locale === 'fa' ? 'mr-2' : 'ml-2'">
                        <QuizShape type="triangle" width="30" height="30" fill="white" />
                    </span>
                    <span class="font-bold text-white" :class="$i18n.locale === 'fa' ? 'mr-2' : 'ml-2'">{{
                        $t('createQuestion.trueFalse.true')
                    }}</span>
                </label>
                <label
                    class="flex items-center bg-red-500 hover:bg-red-600 shadow-[0_2px_0_rgba(207,28,28,1)] p-3 rounded-md w-full cursor-pointer"
                    :class="{ 'ring-2 ring-offset-2 ring-white': modelValue === 'false' }"
                >
                    <input
                        type="radio"
                        :checked="modelValue === 'false'"
                        @change="$emit('update:modelValue', 'false')"
                        name="true-false-option"
                        value="false"
                        class="form-radio h-4 w-4 text-indigo-600 sr-only"
                    />
                    <span :class="$i18n.locale === 'fa' ? 'mr-2' : 'ml-2'">
                        <QuizShape type="rectangle" width="30" height="30" fill="white" />
                    </span>
                    <span class="font-bold text-white" :class="$i18n.locale === 'fa' ? 'mr-2' : 'ml-2'">{{
                        $t('createQuestion.trueFalse.false')
                    }}</span>
                </label>
            </div>
        </div>
    </div>
</template>

<script>
import QuizShape from '@/components/quiz/QuizShape.vue'

export default {
    name: 'TrueFalse',
    components: { QuizShape },
    props: {
        modelValue: {
            // Correct answer: 'true' or 'false'
            type: String,
            default: 'true',
        },
    },
    emits: ['update:modelValue'],
}
</script>
