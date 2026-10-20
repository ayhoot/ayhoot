<template>
    <ErrorPageLayout
        errorCode="401"
        title="Unauthorized"
        description="Your session has expired or you need to be logged in to access this page."
    >
        <template #actions>
            <RouterLink
                to="/login"
                class="w-full px-5 py-2 text-sm tracking-wide text-white transition-colors duration-200 bg-blue-500 rounded-lg shrink-0 sm:w-auto hover:bg-blue-600 dark:hover:bg-blue-500 dark:bg-blue-600"
            >
                Go to Login
            </RouterLink>
        </template>
    </ErrorPageLayout>
</template>

<script setup>
import ErrorPageLayout from './ErrorPageLayout.vue'
import { RouterLink } from 'vue-router'
</script>
