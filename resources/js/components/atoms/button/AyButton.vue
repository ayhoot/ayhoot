<template>
    <component
        :is="tag"
        :to="isInternal ? to : undefined"
        :href="!isInternal ? to : undefined"
        :target="!isInternal ? '_blank' : undefined"
        :class="[
            'flex items-center justify-center rounded-md px-4 py-2 cursor-pointer font-bold text-sm',
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
                'text-[#0F1D42] bg-[#C0C0C0] hover:bg-[#979797] shadow-[0_2px_0_rgba(136,136,136,1)] hover:shadow-none hover:translate-y-[2px]':
                    type === 'light-gray',
            },
            disabled && 'opacity-50 cursor-not-allowed',
        ]"
        :disabled="tag === 'button' ? disabled : undefined"
        @click="onClick"
    >
        <slot></slot>
    </component>
</template>

<script>
export default {
    name: 'AyButton',
    emits: ['click'],
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
        handleClick: {
            type: Function,
            default: null,
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
