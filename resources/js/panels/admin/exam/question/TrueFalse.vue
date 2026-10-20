<template>
    <div class="w-full">
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
                    <span class="ml-2">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 3L19.7942 16.5H4.20577L12 3Z" fill="white" />
                        </svg>
                    </span>
                    <span class="ml-2 font-bold text-white">True</span>
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
                    <span class="ml-2">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="5" y="5" width="14" height="14" fill="white" />
                        </svg>
                    </span>
                    <span class="ml-2 font-bold text-white">False</span>
                </label>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TrueFalse',
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
