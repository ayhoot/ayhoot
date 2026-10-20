<template>
    <div class="rounded-lg shadow-sm bg-white">
        <div class="p-1 flex items-center gap-3">
            <div class="flex items-center gap-3">
                <!-- MODIFIED: Image now always shows, with a fallback -->
                <img :src="imageUrl" alt="Question Image" class="w-20 h-14 object-cover rounded-md bg-gray-200" />
                <div>
                    <span class="text-xs text-gray-400">Slide</span>
                    <!-- MODIFIED: Use v-html to render HTML content from question.title -->
                    <h3
                        class="text-sm font-semibold truncate w-[20ch] sm:w-[20ch] md:w-[30ch] lg:w-[40ch]"
                        v-html="question.title"
                    ></h3>
                </div>
            </div>
        </div>
        <!-- MODIFIED: Use v-html to also render the slide content as HTML -->
        <div
            v-if="question.slide"
            class="w-full border-t border-gray-200 text-sm text-gray-700 px-4 py-3"
            v-html="question.slide"
        ></div>
    </div>
</template>

<script>
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

export default {
    name: 'SlidePreview',
    props: {
        question: { type: Object, required: true },
    },
    computed: {
        imageUrl() {
            let finalUrl = '/imgHolders/bg3.jpg' // Default fallback image
            if (this.question.files && typeof this.question.files === 'string') {
                try {
                    const filesArray = JSON.parse(this.question.files)
                    if (Array.isArray(filesArray) && filesArray.length > 0 && filesArray[0]) {
                        finalUrl = `${API_BASE_URL}/uploads/${filesArray[0]}`
                    }
                } catch (error) {
                    console.error('Failed to parse slide question files JSON:', error)
                }
            }
            return finalUrl
        },
    },
}
</script>
