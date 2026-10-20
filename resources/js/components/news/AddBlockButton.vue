<template>
    <div
        class="absolute left-[-50px] top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
    >
        <button
            @click="showMenu = !showMenu"
            class="w-8 h-8 flex items-center justify-center bg-gray-200 hover:bg-blue-600 hover:text-white rounded-full text-gray-600 font-light text-xl transition-all"
        >
            +
        </button>
        <div
            v-if="showMenu"
            @mouseleave="showMenu = false"
            class="absolute top-full mt-2 w-48 rounded-lg shadow-xl border border-gray-200 z-10 p-1 bg-white"
        >
            <a
                @click.prevent="add('paragraph')"
                class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h7"
                    ></path>
                </svg>
                <span>Paragraph</span>
            </a>
            <a
                @click.prevent="add('heading')"
                class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m-6-10h12"></path>
                </svg>
                <span>Heading</span>
            </a>
            <a
                @click.prevent="add('image')"
                class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    ></path>
                </svg>
                <span>Image</span>
            </a>
        </div>
    </div>
</template>

<script>
/** Hover "+" control that inserts a new content block (paragraph/heading/image). */
export default {
    name: 'AddBlockButton',
    emits: ['add'],
    data: () => ({ showMenu: false }),
    methods: {
        add(type) {
            this.$emit('add', type)
            this.showMenu = false
        },
    },
}
</script>
