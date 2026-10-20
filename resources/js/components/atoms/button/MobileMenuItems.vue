<template>
    <div class="relative group cursor-pointer">
        <div class="bg-[#4285F4] shadow-sm rounded-lg w-full py-2">
            <RouterLink
                v-for="(item, index) in menuItems"
                :key="index"
                :to="item.link"
                class="block px-4 py-2 text-white hover:bg-[#4286f4b6]"
                :class="{ 'font-bold text-lg': index === 0 }"
                :target="isExternalLink(item.link) ? '_blank' : '_self'"
            >
                {{ item.label }}
            </RouterLink>
        </div>
    </div>
</template>

<script>
export default {
    name: 'MobileMenuItems',
    props: {
        title: String,
        menuItems: {
            type: Array,
            default: () => [],
        },
    },
    methods: {
        isExternalLink(link) {
            return link.startsWith('http') || link.startsWith('//')
        },
    },
}
</script>
