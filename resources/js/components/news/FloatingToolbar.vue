<template>
    <div class="absolute z-50 bg-gray-800 text-white rounded-md shadow-lg flex items-center p-1 gap-1 transition-all">
        <button @click="exec('bold')" class="p-2 rounded hover:bg-gray-700">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M6 12V20H14.1C16.2539 20 18 18.2091 18 16C18 13.7909 16.2539 12 14.1 12H6ZM6 12H12.9C15.0539 12 16.8 10.2091 16.8 8C16.8 5.79086 15.0539 4 12.9 4H6V12Z"
                    stroke="white"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </button>
        <button @click="exec('italic')" class="p-2 rounded hover:bg-gray-700">
            <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M11.0526 19L14.9474 5M11.0526 19H7M11.0526 19H15.1052M14.9474 5H10.8948M14.9474 5H19"
                    stroke="white"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </button>
        <button @click="exec('underline')" class="p-2 rounded hover:bg-gray-700">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M20 20H4M17.7143 5.14286V10.8571C17.7143 14.0131 15.1559 16.5714 12 16.5714C8.84409 16.5714 6.28571 14.0131 6.28571 10.8571V5.14286M4.57143 4H8M16 4L19.4286 4"
                    stroke="white"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </button>
        <button @click="exec('strikeThrough')" class="p-2 rounded hover:bg-gray-700">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M4 12H10.7996L14.7088 12M14.7088 12C17.2125 12 19.2417 14.0147 19.2417 16.5C19.2417 18.9853 17.212 21 14.7084 21H11.9336C9.19211 21 6.90524 19.0676 6.38008 16.5M14.7088 12L21 12M19.2414 8.625C19.2414 5.5184 16.7043 3 13.5747 3H10.8C8.29631 3 6.26667 5.01472 6.26667 7.5V8.625"
                    stroke="white"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </button>
        <div class="w-px h-5 bg-gray-600 mx-1"></div>
        <button @click="createLink" class="p-2 rounded hover:bg-gray-700">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M20.2982 12.2412L12.3721 20.1673C10.2147 22.3247 6.97931 22.5794 4.77977 20.3799C2.62244 18.2225 2.90283 15.0972 5.10237 12.8976L14.0122 3.98783C15.3758 2.6242 17.5711 2.6242 18.9347 3.98783C20.2984 5.35147 20.2984 7.54679 18.9347 8.91042L9.8685 17.9767C9.18884 18.6563 8.08688 18.6563 7.40721 17.9767C6.72754 17.297 6.72754 16.195 7.40721 15.5154L15.4898 7.4328"
                    stroke="white"
                    stroke-width="2"
                    stroke-linecap="round"
                />
            </svg>
        </button>
    </div>
</template>

<script>
/** Floating rich-text formatting toolbar shown on text selection. */
export default {
    name: 'FloatingToolbar',
    emits: ['exec-command'],
    methods: {
        exec(command) {
            this.$emit('exec-command', command)
        },
        createLink() {
            const url = prompt('Enter the URL:')
            if (url) {
                this.$emit('exec-command', 'createLink', url)
            }
        },
    },
}
</script>
