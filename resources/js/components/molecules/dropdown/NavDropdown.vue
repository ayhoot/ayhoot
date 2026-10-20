<template>
    <div class="relative group cursor-pointer">
        <component
            :is="isExternal ? 'a' : 'router-link'"
            :href="isExternal ? to : null"
            :to="!isExternal ? to : null"
            class="flex items-center text-[#0F1D42] font-semibold transition-all duration-200 relative"
        >
            <slot name="icon"></slot>

            <span class="ms-2 relative text-sm text-[#0F1D42] group-hover:text-blue-600">
                <slot></slot>
                <span
                    class="absolute start-0 -bottom-1 w-0 h-[2px] bg-blue-600 transition-all duration-200 group-hover:w-full"
                ></span>
            </span>
        </component>

        <div
            v-if="menuItems.length > 0"
            class="absolute start-0 mt-5 w-48 bg-white shadow-md rounded-md opacity-0 invisible transition-all duration-200 group-hover:opacity-100 group-hover:visible"
        >
            <ul class="">
                <li v-for="(item, index) in menuItems" :key="index">
                    <component
                        :is="isExternalLink(item.link) ? 'a' : 'router-link'"
                        :href="isExternalLink(item.link) ? item.link : null"
                        :to="!isExternalLink(item.link) ? item.link : null"
                        class="text-xs block px-2 font-semibold py-2 text-gray-700 hover:text-blue-600 transition-all text-start"
                    >
                        {{ item.label }}
                    </component>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    name: 'NavDropdown',
    props: {
        to: {
            type: String,
            default: '/',
        },
        menuItems: {
            type: Array,
            default: () => [],
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
