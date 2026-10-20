<!--
  CategoriesBar — the categories carousel shown at the TOP of every panel
  (instructor/student) discover page. Self-contained: it fetches the category
  list itself and links each category to the role-prefixed discover route so the
  user stays inside their panel. Drop <CategoriesBar /> at the top of any
  discover page to get a consistent categories strip.
-->
<template>
    <CategoriesBarCarousel :categories="categories" :loading="isLoading" :base-path="basePath" />
</template>

<script>
import { apiCall } from '@/services/apiService'
import { getRole } from '@/utils/role'
import CategoriesBarCarousel from '@/components/discover/public/CategoriesCarousel.vue'

export default {
    name: 'CategoriesBar',
    components: { CategoriesBarCarousel },
    data() {
        return {
            isLoading: true,
            categories: [],
        }
    },
    computed: {
        basePath() {
            return `/${getRole()}/discover/categories`
        },
    },
    methods: {
        async fetchCategories() {
            this.isLoading = true
            try {
                const response = await apiCall('GET', '/api/home/categories')
                // The endpoint may return a paginator ({ data: [...] }) or a flat array.
                this.categories = response?.categories?.data || response?.categories || []
            } catch (error) {
                console.error('Failed to fetch categories:', error)
                this.categories = []
            } finally {
                this.isLoading = false
            }
        },
    },
    mounted() {
        this.fetchCategories()
    },
}
</script>
