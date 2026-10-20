<template>
    <div class="relative">
        <button
            @click="showMenu = !showMenu"
            class="opacity-0 group-hover:opacity-100 w-7 h-7 flex items-center justify-center text-gray-400 hover:bg-gray-200 rounded-md transition-opacity duration-200"
        >
            <svg viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                <path
                    d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"
                ></path>
            </svg>
        </button>
        <div
            v-if="showMenu"
            @mouseleave="showMenu = false"
            class="absolute left-0 top-full mt-2 w-56 bg-white rounded-lg shadow-md border border-gray-200 z-10 p-1"
        >
            <div
                v-if="type === 'heading' || type === 'paragraph' || type === 'image'"
                class="p-2 flex justify-around items-center border-b border-gray-200"
            >
                <button @click.prevent="align('left')" class="p-1.5 rounded-md hover:bg-gray-100">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M2.4001 3.6001L21.6001 3.6001M2.4001 8.8001L16.5242 8.8001M2.4001 14.0001L21.6001 14.0001M2.4001 19.2001L12.6622 19.2001"
                            stroke="black"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>
                <button @click.prevent="align('center')" class="p-1.5 rounded-md hover:bg-gray-100">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M21.5999 19.8H2.3999M19.7999 14.6H5.67577M21.5999 9.40005H2.3999M17.3999 4.80005L7.13783 4.80005"
                            stroke="black"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>
                <button @click.prevent="align('right')" class="p-1.5 rounded-md hover:bg-gray-100">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M21.5999 19.2001H2.3999M21.5999 14.0001H7.47576M21.5999 8.8001H2.3999M21.5999 3.6001L11.3378 3.6001"
                            stroke="black"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>
            </div>
            <div v-if="type === 'heading'" class="p-2 flex justify-around items-center border-b border-gray-200">
                <button @click.prevent="setLevel(1)" class="p-1.5 rounded-md hover:bg-gray-100 font-bold text-lg">
                    H1
                </button>
                <button @click.prevent="setLevel(2)" class="p-1.5 rounded-md hover:bg-gray-100 font-bold text-md">
                    H2
                </button>
                <button @click.prevent="setLevel(3)" class="p-1.5 rounded-md hover:bg-gray-100 font-bold text-sm">
                    H3
                </button>
            </div>
            <div class="px-3 py-2 text-xs text-gray-400">Change to</div>
            <a
                @click.prevent="changeType('paragraph')"
                class="block font-bold px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md cursor-pointer"
                >Paragraph</a
            >
            <a
                @click.prevent="changeType('heading')"
                class="block font-bold px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md cursor-pointer"
                >Heading</a
            >
            <a
                @click.prevent="changeType('image')"
                class="block font-bold px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md cursor-pointer"
                >Image</a
            >
            <div class="border-t border-gray-200 my-1"></div>
            <a
                @click.prevent="deleteBlock"
                class="block font-bold px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md cursor-pointer"
                >Delete</a
            >
        </div>
    </div>
</template>

<script>
/** Per-block control menu (align / heading level / change type / delete). */
export default {
    name: 'BlockControls',
    props: ['index', 'type'],
    emits: ['delete', 'change-type', 'align', 'set-level'],
    data: () => ({ showMenu: false }),
    methods: {
        deleteBlock() {
            this.$emit('delete', this.index)
            this.showMenu = false
        },
        changeType(type) {
            this.$emit('change-type', { index: this.index, type })
            this.showMenu = false
        },
        align(direction) {
            this.$emit('align', { index: this.index, direction })
        },
        setLevel(level) {
            this.$emit('set-level', { index: this.index, level })
        },
    },
}
</script>
