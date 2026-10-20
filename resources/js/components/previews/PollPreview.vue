<template>
    <div
        class="rounded-lg shadow-sm bg-white cursor-pointer"
        @click="toggleDropdown"
        @keydown.enter.space="toggleDropdown"
        :aria-expanded="isOpen"
    >
        <!-- Header Section -->
        <div class="p-1 flex items-center justify-between pr-4">
            <div class="flex items-center gap-3">
                <img
                    v-if="question.picture"
                    :src="question.picture"
                    alt="Question Image"
                    class="w-20 h-14 object-cover rounded-md"
                />
                <div>
                    <span class="text-xs text-gray-400">Poll</span>
                    <h3 class="text-sm font-semibold truncate w-[20ch] sm:w-[20ch] md:w-[30ch] lg:w-[40ch]">
                        {{ question.title }}
                    </h3>
                </div>
            </div>

            <!-- Toggle Button -->
            <ResponsiveButton
                type="light-gray"
                @click.stop="toggleDropdown"
                @keydown.enter.space="toggleDropdown"
                :aria-expanded="isOpen"
            >
                <DashArrowDown :class="{ 'rotate-180': isOpen }" class="transition-transform duration-300" />
            </ResponsiveButton>
        </div>

        <!-- Options List -->
        <transition name="slide-fade">
            <ul v-if="isOpen">
                <li
                    v-for="(option, index) in question.options"
                    :key="option.id"
                    class="flex items-center justify-between gap-3 p-1 text-xs cursor-pointer border-t border-gray-300"
                >
                    <div class="flex items-center gap-3">
                        <!-- Shape Icon with dynamic colors -->
                        <div class="w-6 h-6 flex items-center justify-center rounded bg-gray-400">
                            <svg viewBox="0 0 24 24" class="w-4 h-4 fill-white">
                                <circle cx="12" cy="12" r="10" />
                            </svg>
                        </div>

                        <!-- Option Text -->
                        <span>{{ option.title }}</span>
                    </div>
                </li>
            </ul>
        </transition>
    </div>
</template>

<script>
import { ref } from 'vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashArrowDown from '@/components/atoms/icons/DashArrowDown.vue'
import DashWrong from '@/components/atoms/icons/DashWrong.vue'
import DashCorrect from '@/components/atoms/icons/DashCorrect.vue'

export default {
    name: 'PollPreview',
    components: {
        ResponsiveButton,
        DashArrowDown,
        DashWrong,
        DashCorrect,
    },
    props: {
        question: {
            type: Object,
            required: true,
        },
    },
    setup() {
        const isOpen = ref(false)

        const toggleDropdown = () => {
            isOpen.value = !isOpen.value
        }

        // Define more colors and shapes to support up to 8 items
        const shapeStyles = [
            'bg-red-500',
            'bg-blue-500',
            'bg-yellow-500',
            'bg-green-500',
            'bg-purple-500',
            'bg-orange-500',
            'bg-teal-500',
            'bg-pink-500',
        ]

        const shapes = ['triangle', 'diamond', 'circle', 'rectangle', 'pentagon', 'hexagon', 'triangle', 'circle']

        return {
            isOpen,
            toggleDropdown,
            shapeStyles,
            shapes,
        }
    },
}
</script>

<style scoped>
/* Smooth dropdown animation */
.slide-fade-enter-active {
    transition: all 0s ease-out;
}
.slide-fade-leave-active {
    transition: all 0s ease-in;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
}
</style>
