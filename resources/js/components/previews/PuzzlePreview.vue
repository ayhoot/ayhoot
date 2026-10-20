<template>
    <div
        class="rounded-lg shadow-sm bg-white cursor-pointer"
        @click="toggleDropdown"
        @keydown.enter.space="toggleDropdown"
        :aria-expanded="isOpen"
    >
        <div class="p-1 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <img :src="imageUrl" alt="Question Image" class="w-20 h-14 object-cover rounded-md bg-gray-200" />
                <div>
                    <span class="text-xs text-gray-400">Puzzle</span>
                    <h3
                        class="text-sm font-semibold truncate w-[20ch] sm:w-[20ch] md:w-[30ch] lg:w-[40ch]"
                        v-html="question.title"
                    ></h3>
                </div>
            </div>
            <ResponsiveButton
                type="light-gray"
                @click.stop="toggleDropdown"
                @keydown.enter.space.stop="toggleDropdown"
                :aria-expanded="isOpen"
                class="mx-2"
            >
                <DashArrowDown :class="{ 'rotate-180': isOpen }" class="transition-transform duration-300" />
            </ResponsiveButton>
        </div>
        <transition name="slide-fade">
            <!--
                CRITICAL FIX:
                This now loops over `parsedOptions`, which correctly handles both string and array data types.
                This ensures the options are always displayed.
            -->
            <ul v-if="isOpen">
                <li
                    v-for="option in parsedOptions"
                    :key="option.id"
                    class="flex items-center justify-between gap-3 p-2 text-sm border-t border-gray-200"
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
                    <span class="pr-4 font-semibold text-gray-500">#{{ option.order + 1 }}</span>
                </li>
            </ul>
        </transition>
    </div>
</template>

<script>
import { ref, computed, defineComponent } from 'vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import DashArrowDown from '@/components/atoms/icons/DashArrowDown.vue'



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

export default {
    name: 'PuzzlePreview',
    components: { ResponsiveButton, DashArrowDown },
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
            let finalUrl = '/imgHolders/bg3.jpg'
            if (props.question.files && typeof props.question.files === 'string') {
                try {
                    const filesArray = JSON.parse(props.question.files)
                    if (Array.isArray(filesArray) && filesArray.length > 0 && filesArray[0]) {
                        finalUrl = `${API_BASE_URL}/uploads/${filesArray[0]}`
                    }
                } catch (error) {
                    console.error('Failed to parse puzzle question files JSON:', error)
                }
            }
            return finalUrl
        })

        // --- THE CRITICAL FIX ---
        // This computed property is now robust. It handles `options` whether it's
        // a JSON string OR a pre-parsed JavaScript array.
        const parsedOptions = computed(() => {
            const options = props.question.options

            // Case 1: The data is ALREADY an array. Use it directly.
            if (Array.isArray(options)) {
                return options.sort((a, b) => a.order - b.order)
            }

            // Case 2: The data is a string that needs to be parsed.
            if (typeof options === 'string') {
                try {
                    const parsed = JSON.parse(options)
                    return Array.isArray(parsed) ? parsed.sort((a, b) => a.order - b.order) : []
                } catch (error) {
                    console.error('Failed to parse puzzle options JSON:', error)
                    return []
                }
            }

            // Fallback for null, undefined, or other invalid types.
            return []
        })

        const shapeColorMap = {
            red: 'bg-red-500',
            blue: 'bg-blue-500',
            yellow: 'bg-yellow-500',
            green: 'bg-green-500',
            purple: 'bg-purple-500',
            orange: 'bg-orange-500',
            pink: 'bg-pink-500',
        }
        const shapeTypeMap = {
            triangle: TriangleSvg,
            diamond: DiamondSvg,
            circle: CircleSvg,
            rectangle: RectangleSvg,
        }

        const getShapeColorClass = (shapeString = '') => {
            const color = Object.keys(shapeColorMap).find((c) => shapeString.toLowerCase().includes(c))
            return color ? shapeColorMap[color] : 'bg-gray-400'
        }

        const getShapeSvg = (shapeString = '') => {
            const shape = Object.keys(shapeTypeMap).find((s) => shapeString.toLowerCase().includes(s))
            return shape ? shapeTypeMap[shape] : 'div'
        }

        return {
            isOpen,
            toggleDropdown,
            imageUrl,
            parsedOptions, // Expose the robust parsed options to the template
            getShapeColorClass,
            getShapeSvg,
        }
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
