<template>
    <div class="flex items-center w-full pb-4 overflow-x-auto sm:hidden">
        <router-link
            v-for="tab in tabs"
            :key="tab.route"
            :to="tab.route"
            class="text-center w-full whitespace-nowrap px-3 py-1 mx-0.5 text-sm hover:bg-blue-200 rounded-md font-bold hover:text-gray-800"
            :class="{
                ' text-white font-bold bg-blue-800': isActive(tab.route),
            }"
        >
            {{ tab.label }}
        </router-link>
    </div>
</template>

<script>
export default {
    name: 'DashTabs',
    props: {
        tabs: {
            type: Array,
            required: true,
            default: () => [],
        },
    },
    methods: {
        isActive(route) {
            return this.$route.path === route
        },
    },
}
</script>
