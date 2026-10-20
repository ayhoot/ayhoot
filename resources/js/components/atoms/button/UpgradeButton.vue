<template>
    <div class="relative group cursor-pointer">
        <!-- Button as a Link -->
        <component
            :is="isExternal ? 'a' : 'router-link'"
            :href="isExternal ? to : null"
            :to="!isExternal ? to : null"
            class="flex items-center text-[#FBBC04] font-bold transition-all duration-200 relative"
        >
            <!-- Icon -->
            <!-- <slot name="icon"></slot> -->
            <Diamond />

            <!-- Text with animated border -->
            <span class="mx-2 relative text-sm text-[#FBBC04] group-hover:text-[#FBBC04]">
                <slot></slot>
                <span
                    class="absolute left-0 -bottom-1 w-0 h-[2px] bg-[#FBBC04] transition-all duration-200 group-hover:w-full"
                ></span>
            </span>
        </component>
    </div>
</template>

<script>
import Diamond from '../icons/Diamond.vue'

export default {
    name: 'UpgradeButton',
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
    components: {
        Diamond,
    },
}
</script>
