<template>
    <div class="animate-pulse bg-white rounded-md shadow-sm w-full overflow-hidden">
        <!-- Faking the gradient top bar with a solid color -->
        <div class="bg-gray-300 h-8 flex justify-between items-center px-2">
            <div class="h-3 bg-gray-400 rounded w-1/3"></div>
            <div class="h-3 bg-gray-400 rounded w-1/4"></div>
        </div>
        <!-- Image Placeholder -->
        <div class="bg-gray-300 w-full h-32"></div>
        <!-- Content Placeholder -->
        <div class="p-3 space-y-3">
            <!-- Title -->
            <div class="h-4 bg-gray-300 rounded w-4/5"></div>
            <!-- Description -->
            <div class="h-3 bg-gray-300 rounded w-full"></div>
            <!-- Author -->
            <div class="h-3 bg-gray-300 rounded w-1/2 mt-1"></div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AyhootCardSkeleton',
}
</script>
