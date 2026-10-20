<template>
    <div class="rounded-lg shadow-sm bg-white cursor-pointer" @click="toggleDropdown">
        <div class="p-1 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <!-- MODIFIED: Image now always shows, with a fallback -->
                <img :src="imageUrl" alt="Question Image" class="w-20 h-14 object-cover rounded-md bg-gray-200" />
                <div>
                    <span class="text-xs text-gray-400">Type Answer</span>
                    <!-- MODIFIED: Use v-html to render HTML content from question.title -->
                    <h3
                        class="text-sm font-semibold truncate w-[20ch] sm:w-[20ch] md:w-[30ch] lg:w-[40ch]"
                        v-html="question.title"
                    ></h3>
                </div>
            </div>
            <ResponsiveButton type="light-gray" @click.stop="toggleDropdown" class="mx-2">
                <DashArrowDown :class="{ 'rotate-180': isOpen }" class="transition-transform duration-300" />
            </ResponsiveButton>
        </div>
        <transition name="slide-fade">
            <div v-if="isOpen" class="p-2 text-xs border-t border-gray-300">
                <span class="font-medium text-gray-500">Correct answer: </span>
                <span class="font-semibold text-gray-800">This answer is private.</span>
            </div>
        </transition>
    </div>
</template>

<script>
import { ref, computed } from 'vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashArrowDown from '@/components/atoms/icons/DashArrowDown.vue'

export default {
    name: 'TypeAnswerPreview',
    components: { ResponsiveButton, DashArrowDown },
    props: { question: { type: Object, required: true } },
    setup(props) {
        const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

        const isOpen = ref(false)
        const toggleDropdown = () => {
            isOpen.value = !isOpen.value
        }

        const imageUrl = computed(() => {
            let finalUrl = '/imgHolders/bg3.jpg' // Default fallback image
            if (props.question.files && typeof props.question.files === 'string') {
                try {
                    const filesArray = JSON.parse(props.question.files)
                    if (Array.isArray(filesArray) && filesArray.length > 0 && filesArray[0]) {
                        finalUrl = `${API_BASE_URL}/uploads/${filesArray[0]}`
                    }
                } catch (error) {
                    console.error('Failed to parse type-answer question files JSON:', error)
                }
            }
            return finalUrl
        })

        return { isOpen, toggleDropdown, imageUrl }
    },
}
</script>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.2s ease-in-out;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}
</style>
