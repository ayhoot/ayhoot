<template>
    <component
        :is="tag"
        :to="isInternal ? to : undefined"
        :href="!isInternal ? to : undefined"
        :target="!isInternal ? '_blank' : undefined"
        :class="[
            'relative flex items-center justify-center rounded-md cursor-pointer font-semibold text-sm',
            'shadow-y-sm',
            {
                'text-white bg-[#152B65] hover:bg-[#0F1D42] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px]':
                    type === 'dark-blue',
                'text-white bg-[#4285F4] hover:bg-[#3450AE] shadow-[0_2px_0_rgba(52,80,174,1)] hover:shadow-none hover:translate-y-[2px]':
                    type === 'light-blue',
                'text-white bg-[#3450AE] hover:bg-[#152B65] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px]':
                    type === 'dash-blue',
                'text-[#0F1D42] bg-[#EBCD1F] hover:bg-[#8A7812] shadow-[0_2px_0_rgba(138,121,18,1)] hover:shadow-none hover:translate-y-[2px]':
                    type === 'yellow',
                'text-white bg-[#42C287] hover:bg-[#156B43] shadow-[0_2px_0_rgba(21,107,67,1)] hover:shadow-none hover:translate-y-[2px]':
                    type === 'light-green',
                'text-white bg-[#616161] hover:bg-[#2B2B2B] shadow-[0_2px_0_rgba(60,60,60,1)] hover:shadow-none hover:translate-y-[2px]':
                    type === 'dark-gray',
                'text-white bg-[#3E9949] hover:bg-[#297832] shadow-[0_2px_0_rgba(21,107,67,1)] hover:shadow-none hover:translate-y-[2px]':
                    type === 'dark-green',
                'text-[#0F1D42]  bg-[#C0C0C0] hover:bg-[#979797] shadow-[0_2px_0_rgba(136,136,136,1)] hover:shadow-none hover:translate-y-[2px]':
                    type === 'light-gray',
                'text-white  bg-[#e06565] hover:bg-[rgb(192,51,51)] shadow-[0_2px_0_rgba(192,51,51,1)] hover:shadow-none hover:translate-y-[2px]':
                    type === 'light-red',
                'text-gray-700  bg-white hover:bg-gray-50 shadow-[0_2px_0_rgba(183,183,183,1)] hover:shadow-none hover:translate-y-[2px]':
                    type === 'white',
                'text-red-600 bg-transparent border border-red-600 hover:bg-red-50':
                    type === 'danger-ghost',
                'text-blue-600 bg-blue-600 hover:bg-blue-700 shadow-[0_2px_0_rgba(21,107,67,1)] hover:shadow-none hover:translate-y-[2px]':
                    type === 'primary',
                'text-gray-600 bg-gray-500 hover:bg-gray-600 shadow-[0_2px_0_rgba(60,60,60,1)] hover:shadow-none hover:translate-y-[2px]':
                    type === 'secondary',
            },
            disabled && 'opacity-50 cursor-not-allowed',
        ]"
        :disabled="tag === 'button' ? disabled : undefined"
    >
        <!-- @click="onClick" commented out to avoid clicking on the button -->
        <slot></slot>
        <!-- Alert Circle -->
        <span
            v-if="hasAlert"
            class="absolute top-0 right-0 -mt-1 -mr-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white"
        ></span>
    </component>
</template>

<script>
export default {
    name: 'ResponsiveButton',
    props: {
        type: {
            type: String,
            default: 'primary',
            validator: (value) =>
                [
                    'dark-blue',
                    'light-blue',
                    'dash-blue',
                    'yellow',
                    'light-green',
                    'dark-gray',
                    'dark-green',
                    'light-gray',
                    'light-red',
                    'white',
                    'danger-ghost',
                    'primary',
                    'secondary',
                ].includes(value),
        },
        to: {
            type: String,
            default: null,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        // handleClick: {
        //     type: Function,
        //     default: null,
        // },
        hasAlert: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        isInternal() {
            return this.to && this.to.startsWith('/')
        },
        tag() {
            if (this.to) {
                return this.isInternal ? 'router-link' : 'a'
            }
            return 'button'
        },
    },
    methods: {
        onClick(event) {
            // Emit the default click event
            this.$emit('click', event)
            // Call the passed handleClick prop if available
            if (this.handleClick) {
                this.handleClick(event)
            }
        },
    },
}
</script>

<style scoped></style>
