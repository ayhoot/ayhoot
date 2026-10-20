<template>
    <div class="relative group cursor-pointer">
        <!-- Button as a Link -->
        <component
            :is="isExternal ? 'a' : 'router-link'"
            :href="isExternal ? to : null"
            :to="!isExternal ? to : null"
            class="flex items-center text-[#0F1D42] font-semibold transition-all duration-200 relative"
        >
            <!-- Icon -->
            <slot name="icon"></slot>

            <!-- Text with animated border -->
            <span
                class="ml-2 relative text-sm text-[#7797FF] group-hover:text-[#7797FF]"
            >
                <slot></slot>
                <span
                    class="absolute left-0 -bottom-1 w-0 h-[2px] bg-[#7797FF] transition-all duration-200 group-hover:w-full"
                ></span>
            </span>
        </component>
    </div>
</template>

<script>
export default {
    name: 'NavDropdown',
    props: {
        to: {
            type: String,
            default: '/', // Default placeholder link
        },
        menuItems: {
            type: Array,
            default: () => [], // Ensures an empty array if not provided
        },
    },
    computed: {
        isExternal() {
            return this.isExternalLink(this.to)
        },
    },
    methods: {
        isExternalLink(link) {
            return link.startsWith('http') || link.startsWith('//')
        },
    },
}
</script>
