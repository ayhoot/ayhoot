<template>
    <ErrorPageLayout
        errorCode="403"
        title="Access Forbidden"
        description="You do not have the necessary permissions to access this resource. Please contact an administrator if you believe this is a mistake."
    >
        <template #actions>
            <button
                @click="goBack"
                class="flex items-center justify-center w-1/2 px-5 py-2 text-sm text-gray-700 transition-colors duration-200 bg-white border rounded-lg dark:text-gray-200 gap-x-2 sm:w-auto dark:hover:bg-gray-800 dark:bg-gray-900 hover:bg-gray-100 dark:border-gray-700"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="w-5 h-5 rtl:rotate-180"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75L3 12m0 0l3.75-3.75M3 12h18" />
                </svg>
                <span>Go back</span>
            </button>

            <RouterLink
                to="/"
                class="w-1/2 px-5 py-2 text-sm tracking-wide text-white transition-colors duration-200 bg-blue-500 rounded-lg shrink-0 sm:w-auto hover:bg-blue-600 dark:hover:bg-blue-500 dark:bg-blue-600"
            >
                Take me home
            </RouterLink>
        </template>
    </ErrorPageLayout>
</template>

<script setup>
import ErrorPageLayout from './ErrorPageLayout.vue'
import { useRouter, RouterLink } from 'vue-router'

const router = useRouter()
const goBack = () => {
    router.go(-1)
}
</script>
