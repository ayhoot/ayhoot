<template>
    <div
        class="rounded-lg shadow-sm bg-white cursor-pointer"
        @click="toggleDropdown"
        @keydown.enter.space="toggleDropdown"
        :aria-expanded="isOpen"
    >
        <!-- Header Section -->
        <div class="p-1 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <img :src="imageUrl" alt="Question Image" class="w-20 h-14 object-cover rounded-md bg-gray-200" />
                <div>
                    <span class="text-xs text-gray-400">Quiz</span>
                    <!-- MODIFIED: Use v-html to render HTML content from question.title -->
                    <h3
                        class="text-sm font-semibold truncate w-[20ch] sm:w-[20ch] md:w-[30ch] lg:w-[40ch]"
                        v-html="question.title"
                    ></h3>
                </div>
            </div>

            <!-- Toggle Button -->
            <ResponsiveButton
                type="light-gray"
                @click.stop="toggleDropdown"
                @keydown.enter.space="toggleDropdown"
                :aria-expanded="isOpen"
                class="mx-2"
            >
                <DashArrowDown :class="{ 'rotate-180': isOpen }" class="transition-transform duration-300" />
            </ResponsiveButton>
        </div>

        <!-- Options List -->
        <transition name="slide-fade">
            <ul v-if="isOpen">
                <li
                    v-for="option in question.options"
                    :key="option.id"
                    class="flex items-center justify-between gap-3 p-2 text-sm cursor-pointer border-t border-gray-200"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="w-6 h-6 flex items-center justify-center rounded"
                            :class="getShapeColorClass(option.shape)"
                        >
                            <component :is="getShapeSvg(option.shape)" class="w-4 h-4 fill-white" />
                        </div>
                        <span>{{ option.text }}</span>
                    </div>
                    <!-- <div v-if="option.correct" class="text-green-600">
                        <DashCorrect />
                    </div>
                    <div v-else class="text-red-600">
                        <DashWrong />
                    </div> -->
                </li>
            </ul>
        </transition>
    </div>
</template>

<script>
import { ref, defineComponent, computed } from 'vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashArrowDown from '@/components/atoms/icons/DashArrowDown.vue'
import DashWrong from '@/components/atoms/icons/DashWrong.vue'
import DashCorrect from '@/components/atoms/icons/DashCorrect.vue'



// --- SVGs Definitions ---
const TriangleSvg = defineComponent({
    template: `<svg viewBox="0 0 100 100"><polygon points="50,10 90,85 10,85" /></svg>`,
})
const DiamondSvg = defineComponent({
    template: `<svg viewBox="0 0 24 24"><polygon points="12,2 22,12 12,22 2,12" /></svg>`,
})
const CircleSvg = defineComponent({ template: `<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /></svg>` })
const RectangleSvg = defineComponent({
    template: `<svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" /></svg>`,
})
// --- NEW: Define Pentagon and Hexagon SVGs ---
const PentagonSvg = defineComponent({
    template: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M10 2.5L18 8.22949L14.9443 17.5H5.05573L2 8.22949L10 2.5Z" fill="white"/>
</svg>
`,
})
const HexagonSvg = defineComponent({
    template: `<svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M10.5 2L17.5 6V14L10.5 18L3.5 14V6L10.5 2Z" fill="white"/>
</svg>
`,
})

export default {
    name: 'QuizPreview',
    components: { ResponsiveButton, DashArrowDown, DashWrong, DashCorrect },
    props: {
        question: { type: Object, required: true },
    },
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
                    console.error('Failed to parse question files JSON:', error)
                }
            }
            return finalUrl
        })

        const shapeColorMap = {
            red: 'bg-red-500',
            blue: 'bg-blue-500',
            yellow: 'bg-yellow-500',
            green: 'bg-green-500',
            purple: 'bg-purple-500',
            orange: 'bg-orange-500',
            pink: 'bg-pink-500', // --- NEW: Added pink color ---
        }
        const shapeTypeMap = {
            triangle: TriangleSvg,
            diamond: DiamondSvg,
            circle: CircleSvg,
            rectangle: RectangleSvg,
            pentagon: PentagonSvg, // --- NEW: Added pentagon shape ---
            hexagon: HexagonSvg, // --- NEW: Added hexagon shape ---
        }

        const getShapeColorClass = (shapeString = '') => {
            const color = Object.keys(shapeColorMap).find((c) => shapeString.toLowerCase().includes(c))
            return color ? shapeColorMap[color] : 'bg-gray-400'
        }

        const getShapeSvg = (shapeString = '') => {
            const shape = Object.keys(shapeTypeMap).find((s) => shapeString.toLowerCase().includes(s))
            return shape ? shapeTypeMap[shape] : 'div'
        }

        return { isOpen, toggleDropdown, getShapeColorClass, getShapeSvg, imageUrl }
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
    transform: translateY(-10px);
}
</style>
