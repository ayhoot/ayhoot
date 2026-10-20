<template>
    <div class="flex items-center w-full pb-4 overflow-x-auto sm:hidden custom-scrollbar">
        <!-- Render all tabs that are NOT the dropdown trigger -->
        <router-link
            v-for="tab in nonDropdownTabs"
            :key="tab.route"
            :to="tab.route"
            class="flex-shrink-0 text-center whitespace-nowrap px-3 py-1 mx-0.5 text-sm hover:bg-blue-200 rounded-md font-bold hover:text-gray-800 text-gray-700"
            :class="{ 'text-white bg-blue-800': isActive(tab.route) }"
        >
            {{ tab.label }}
        </router-link>

        <!-- Render the Dropdown Trigger Button (e.g., "Owned Groups") -->
        <div v-if="dropdownTab" class="flex-shrink-0 mx-0.5">
            <button
                @click.stop="toggleDropdown"
                class="text-center whitespace-nowrap px-3 py-1 text-sm rounded-md font-bold transition-colors"
                :class="{
                    'text-white bg-blue-800': isDropdownActive,
                    'hover:bg-blue-200 hover:text-gray-800 text-gray-700': !isDropdownActive,
                }"
            >
                <span class="flex items-center">
                    {{ dropdownTab.label }}
                    <svg
                        class="w-4 h-4 ml-1 transition-transform duration-200 -rotate-90"
                        :class="{ 'rotate-90': isDropdownOpen }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
            </button>
        </div>

        <!-- Horizontal expansion for dropdown items (e.g., Groups) -->
        <transition-group name="list" tag="div" class="flex items-center">
            <router-link
                v-if="isDropdownOpen"
                v-for="item in dropdownItems"
                :key="item.id"
                :to="`${dropdownItemPath}${item.id}`"
                class="flex-shrink-0 text-center whitespace-nowrap px-3 py-1 mx-0.5 text-sm rounded-md font-bold transition-colors bg-gray-100 text-gray-600 hover:bg-blue-200 hover:text-gray-800"
                :class="{ 'text-white bg-blue-800': isActive(`${dropdownItemPath}${item.id}`) }"
            >
                {{ item.name || item.title }}
                <!-- Handle both 'name' and 'title' properties -->
            </router-link>
        </transition-group>
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
        // Generic props for the dropdown functionality
        dropdownItems: {
            type: Array,
            default: () => [],
        },
        dropdownTriggerLabel: {
            type: String,
            default: '',
        },
        dropdownItemPath: {
            type: String,
            default: '/',
        },
    },
    data() {
        return {
            isDropdownOpen: false,
        }
    },
    computed: {
        // Filter out the dropdown trigger to render it separately
        nonDropdownTabs() {
            return this.tabs.filter((tab) => tab.label !== this.dropdownTriggerLabel)
        },
        // Find the dropdown trigger tab object
        dropdownTab() {
            return this.tabs.find((tab) => tab.label === this.dropdownTriggerLabel)
        },
        // Check if any dropdown item link is active
        isDropdownActive() {
            return this.$route.path.startsWith(this.dropdownItemPath)
        },
    },
    methods: {
        isActive(route) {
            return this.$route.path === route
        },
        toggleDropdown() {
            this.isDropdownOpen = !this.isDropdownOpen
        },
        closeDropdown() {
            this.isDropdownOpen = false
        },
        handleClickOutside(event) {
            if (this.$el && !this.$el.contains(event.target)) {
                this.closeDropdown()
            }
        },
    },
    mounted() {
        document.addEventListener('click', this.handleClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside)
    },
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
}
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: scale(0.9) translateX(-10px);
}
</style>
