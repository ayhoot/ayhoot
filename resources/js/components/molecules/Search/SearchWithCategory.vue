<template>
    <form class="max-w-lg mx-auto">
        <div class="flex">
            <!-- Dropdown button -->
            <button
                id="cat-dropdown-button"
                @click.prevent.stop="toggleCatDropdown"
                class="shrink-0 z-10 inline-flex items-center py-2 px-4 text-sm font-bold text-center text-gray-900 bg-gray-100 border-2 border-gray-300 rounded-s-sm hover:bg-gray-200"
                type="button"
            >
                {{ selectedCategory }}
                <svg
                    class="w-2.5 h-2.5 ms-2.5"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 10 6"
                >
                    <path
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="m1 1 4 4 4-4"
                    />
                </svg>
            </button>
            <!-- Dropdown menu -->
            <div
                v-if="catdropdown"
                id="cat-dropdown"
                class="z-10 bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 absolute mt-12"
            >
                <ul
                    class="py-2 text-sm text-gray-700"
                    aria-labelledby="cat-dropdown-button"
                >
                    <li v-for="(category, index) in categories" :key="index">
                        <button
                            type="button"
                            @click="selectCategory(category)"
                            class="inline-flex w-full px-4 font-bold py-2 hover:bg-gray-100 text-left"
                        >
                            {{ category }}
                        </button>
                    </li>
                </ul>
            </div>
            <!-- Search input -->
            <div class="relative w-full">
                <input
                    type="search"
                    id="search-dropdown"
                    class="block p-2 w-full z-20 text-sm font-bold text-gray-900 bg-gray-50 rounded-e-sm border-s-gray-50 border-y-2 border border-gray-300 focus:outline-none"
                    placeholder="Search..."
                />
                <button
                    type="submit"
                    class="absolute top-0 end-0 px-4 text-sm font-medium h-full text-white bg-[#3450AE] rounded-e-md cursor-pointer hover:bg-blue-800"
                >
                    <svg
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M16.927 17.04L20.4001 20.4M19.2801 11.44C19.2801 15.7699 15.77 19.28 11.4401 19.28C7.11019 19.28 3.6001 15.7699 3.6001 11.44C3.6001 7.11009 7.11019 3.6 11.4401 3.6C15.77 3.6 19.2801 7.11009 19.2801 11.44Z"
                            stroke="#ffffff"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                    </svg>

                    <span class="sr-only">Search</span>
                </button>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'SearchWithCategory',
    data() {
        return {
            catdropdown: false,
            categories: ['Ayhoots', 'Tutees', 'Tutors', 'Institutes'],
            selectedCategory: 'Ayhoots',
        }
    },
    methods: {
        toggleCatDropdown() {
            this.catdropdown = !this.catdropdown
        },
        selectCategory(category) {
            this.selectedCategory = category
            this.catdropdown = false
        },
    },
}
</script>

<style scoped></style>
