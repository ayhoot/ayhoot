<template>
    <!-- Skeleton Loader (No text, no changes needed) -->
    <div v-if="loading" class="p-6 bg-white shadow-sm rounded-xl flex flex-col gap-y-2 animate-pulse">
        <div class="flex items-center justify-start gap-x-3">
            <div class="rounded-xl bg-gray-200 flex-shrink-0"
                style="width: 64px; height: 64px; min-width: 64px; min-height: 64px;"></div>
            <div class="w-full space-y-2">
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
            </div>
        </div>
        <div class="bg-gray-100 px-4 py-2 rounded-lg space-y-3">
            <div class="h-3 bg-gray-200 rounded w-28"></div>
            <div class="flex items-center gap-x-2">
                <div class="h-4 w-4 bg-gray-200 rounded-full"></div>
                <div class="h-3 bg-gray-200 rounded w-full"></div>
            </div>
            <div class="flex items-center gap-x-2">
                <div class="h-4 w-4 bg-gray-200 rounded-full"></div>
                <div class="h-3 bg-gray-200 rounded w-full"></div>
            </div>
        </div>
        <div class="bg-gray-100 px-4 py-2 rounded-lg space-y-3">
            <div class="h-3 bg-gray-200 rounded w-28"></div>
            <div class="h-3 bg-gray-200 rounded w-full mt-2"></div>
        </div>
    </div>

    <!-- Actual Content -->
    <div v-else-if="student" class="p-6 bg-white shadow-sm rounded-xl flex flex-col gap-y-2 hover:shadow-md">
        <!-- Profile Header -->
        <div class="flex items-center justify-start gap-x-3">
            <img v-if="student.avatar" :src="student.avatar" :alt="student.name"
                class="object-cover rounded-xl border-3 border-gray-300 flex-shrink-0"
                style="width: 64px; height: 64px; min-width: 64px; min-height: 64px;" />
            <div v-else
                class="rounded-xl border-3 border-gray-300 bg-gray-100 flex justify-center items-center flex-shrink-0"
                style="width: 64px; height: 64px; min-width: 64px; min-height: 64px;">
                <span class="font-bold text-gray-500 select-none text-xl">{{ studentInitials }}</span>
            </div>

            <div class="w-full text-left">
                <h3 class="font-semibold text-start">{{ student.name }}</h3>
                <div class="w-full flex items-center justify-between">
                    <h6 class="text-[#3450AE] font-semibold text-sm">@{{ student.username }}</h6>
                    <RouterLink to="/student/my-profile">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M9.19976 13.0344H13.1998M2.7998 13.0344L5.71046 12.4479C5.86498 12.4168 6.00686 12.3407 6.11828 12.2292L12.6341 5.70985C12.9465 5.39729 12.9463 4.89063 12.6336 4.57832L11.2533 3.1996C10.9408 2.88742 10.4344 2.88764 10.1221 3.20008L3.60565 9.72011C3.49444 9.83138 3.4185 9.97297 3.38734 10.1272L2.7998 13.0344Z"
                                stroke="#3450AE" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </RouterLink>
                </div>
            </div>
        </div>

        <!-- Activity Overview -->
        <div class="bg-gray-100 px-4 py-2 rounded-lg">
            <h3 class="text-sm font-semibold">{{ $t('studentProfileCard.activityOverview.title') }}</h3>
            <div class="flex items-center gap-x-1 pt-2 py-1">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M10.2999 21.6001H5.49984C4.17436 21.6001 3.09984 20.5256 3.09985 19.2001L3.09995 4.80013C3.09995 3.47466 4.17447 2.40015 5.49995 2.40015H16.3002C17.6257 2.40015 18.7002 3.47466 18.7002 4.80015V11.4001M13.9002 18.2001L16.1002 20.4001L20.9002 15.6M7.30023 7.20015H14.5002M7.30023 10.8001H14.5002M7.30023 14.4001H10.9002"
                        stroke="#364153" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="text-gray-700 font-semibold text-sm">{{ totalExams }}</span>
                <span class="text-gray-500 text-sm">{{ $t('studentProfileCard.activityOverview.playedAyhoots') }}</span>
            </div>
            <div class="flex items-center gap-x-1 py-1">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M19 11V8.2C19 7.0799 19 6.51984 18.782 6.09202C18.5903 5.71569 18.2843 5.40973 17.908 5.21799C17.4802 5 16.9201 5 15.8 5H7.2C6.0799 5 5.51984 5 5.09202 5.21799C4.71569 5.40973 4.40973 5.71569 4.21799 6.09202C4 6.51984 4 7.07989 4 8.2V13.8C4 14.9201 4 15.4802 4.21799 15.908C4.40973 16.2843 4.71569 16.5903 5.09202 16.782C5.51984 17 6.07989 17 7.2 17H14"
                        stroke="#364153" stroke-width="2.5" stroke-linecap="round" />
                    <path d="M8 13H12M8 9H15" stroke="#364153" stroke-width="2.5" stroke-linecap="round" />
                    <circle cx="18" cy="15" r="1" stroke="#364153" stroke-width="2.5" />
                    <path d="M20 20C20 20 19.5 19 18 19C16.5 19 16 20 16 20" stroke="#364153" stroke-width="2.5"
                        stroke-linecap="round" />
                </svg>
                <span class="text-gray-700 font-semibold text-sm">{{ totalCourses }}</span>
                <span class="text-gray-500 text-sm">{{
                    $t('studentProfileCard.activityOverview.enrolledCourses')
                    }}</span>
            </div>
        </div>

        <!-- Account Info -->
        <div class="bg-gray-100 px-4 py-2 rounded-lg">
            <h3 class="text-sm font-semibold">{{ $t('studentProfileCard.accountInfo.title') }}</h3>
            <div v-if="student.email" class="w-full flex items-center gap-x-1 justify-between text-sm mt-2">
                <h3 class="text-gray-500">{{ $t('studentProfileCard.accountInfo.emailLabel') }}</h3>
                <span class="font-semibold text-gray-800 text-end">{{ student.email }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'

// --- PROPS ---
const props = defineProps({
    loading: {
        type: Boolean,
        required: true,
    },
    student: {
        type: Object,
        default: () => null,
    },
    totalCourses: {
        type: Number,
        default: 0,
    },
    totalExams: {
        type: Number,
        default: 0,
    },
})

// --- COMPUTED ---
const studentInitials = computed(() => {
    const name = props.student?.name || ''
    const parts = name.trim().split(' ').filter(Boolean)
    if (parts.length === 0) return '?'
    // Use first_name and last_name if name is not available
    if (parts.length < 2 && props.student?.first_name) {
        return (props.student.first_name[0] + (props.student.last_name ? props.student.last_name[0] : '')).toUpperCase()
    }
    return (parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase()
})

const capitalizedStatus = computed(() => {
    if (!props.student?.status) return ''
    return props.student.status.charAt(0).toUpperCase() + props.student.status.slice(1)
})
</script>

<style scoped>
@keyframes pulse {
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
