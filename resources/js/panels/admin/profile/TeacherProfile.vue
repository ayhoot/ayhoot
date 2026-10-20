<template>
    <div class="bg-gray-100 min-h-screen">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" :teacherName="userName" :teacherUsername="userUsername" />
        </div>

        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />

            <main class="bg-gray-100 ml-0 xl:ml-64 w-full overflow-y-auto pt-16 min-h-[calc(100vh-4rem)]">
                <div v-if="isLoading" class="flex items-center justify-center h-full min-h-[calc(100vh-4rem)]">
                    <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-indigo-500"></div>
                </div>

                <template v-else>
                    <div class="w-full relative">
                        <img
                            :src="user.cover"
                            :alt="user.name + ' cover image'"
                            class="w-full lg:h-[30vh] h-[15vh] object-cover"
                        />
                    </div>

                    <div class="w-full lg:px-12 px-6 -mt-12 sm:-mt-16 mb-8">
                        <div
                            class="flex flex-col lg:flex-row items-center lg:items-end justify-between bg-white p-4 sm:p-6 rounded-xl shadow-lg relative"
                        >
                            <div class="flex flex-col sm:flex-row items-center text-center sm:text-left">
                                <img
                                    :src="user.image"
                                    :alt="user.name"
                                    class="w-24 h-24 sm:w-32 sm:h-32 rounded-full object-cover border-4 border-white shadow-md -mt-12 sm:-mt-16 lg:-mt-20 mb-2 sm:mb-0 sm:mr-6"
                                />
                                <div class="sm:pt-2 space-y-1">
                                    <div class="flex items-center justify-center sm:justify-start gap-2">
                                        <h3 class="text-xl sm:text-2xl font-bold text-gray-800">{{ user.name }}</h3>
                                        <DashVerified v-if="user.verified" class="w-5 h-5 sm:w-6 sm:h-6" />
                                    </div>
                                    <div class="flex items-center justify-center sm:justify-start gap-3">
                                        <p class="text-sm font-semibold text-gray-600">{{ user.plan.name }}</p>
                                    </div>
                                    <a
                                        v-if="user.website"
                                        :href="user.website"
                                        target="_blank"
                                        class="text-xs sm:text-sm font-semibold text-blue-600 hover:underline"
                                    >
                                        {{ user.website }}
                                    </a>
                                    <p v-else class="text-xs sm:text-sm text-gray-500">No website provided</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 sm:gap-4 mt-4 lg:mt-0 text-gray-700">
                                <div class="text-center px-2">
                                    <p class="text-lg sm:text-xl font-bold">{{ user.stats.courses_count }}</p>
                                    <h3 class="text-xs sm:text-sm text-gray-500 uppercase">Courses</h3>
                                </div>
                                <div class="text-center px-2">
                                    <p class="text-lg sm:text-xl font-bold">{{ user.stats.exams_count }}</p>
                                    <h3 class="text-xs sm:text-sm text-gray-500 uppercase">Exams</h3>
                                </div>
                                <div class="text-center px-2">
                                    <p class="text-lg sm:text-xl font-bold">{{ user.stats.students_count }}</p>
                                    <h3 class="text-xs sm:text-sm text-gray-500 uppercase">Tutees</h3>
                                </div>
                                <ResponsiveButton @click="openPlanModal" type="yellow" class="p-2"
                                    >Change Plan</ResponsiveButton
                                >
                                <AyButton @click="openMessageModal" type="dash-blue" class="!px-4 !py-2"
                                    >Message</AyButton
                                >
                            </div>
                        </div>
                    </div>

                    <div class="w-full lg:px-12 px-6 mb-8">
                        <div class="bg-white p-4 px-6 rounded-xl shadow-lg">
                            <div class="flex justify-between items-center h-full">
                                <div>
                                    <h4 class="text-sm uppercase font-bold tracking-wider opacity-80">
                                        Wallet Balance
                                    </h4>
                                    <p class="text-3xl font-bold mt-1">{{ user.wallet }} T</p>
                                    <p class="text-sm font-mono opacity-80 mt-2">{{ user.card }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 sm:px-6 lg:px-12">
                        <div class="border-b border-gray-200">
                            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                                <li class="mr-2" v-for="tab in tabs" :key="tab.id">
                                    <button
                                        @click="activeTab = tab.id"
                                        :class="
                                            activeTab === tab.id
                                                ? 'text-blue-600 border-blue-600'
                                                : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'
                                        "
                                        class="inline-block p-4 border-b-2 rounded-t-lg font-semibold"
                                    >
                                        {{ tab.name }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="px-4 sm:px-6 lg:px-12 py-6">
                        <div v-if="isContentLoading" class="flex justify-center items-center py-12">
                            <div
                                class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"
                            ></div>
                        </div>
                        <template v-else>
                            <div v-show="activeTab === 'courses'">
                                <div v-if="content.courses && content.courses.length > 0">
                                    <!-- Pass the ENTIRE array to the component -->
                                    <CategoriesMassCourseProps :courses="content.courses" Category="Teacher's" />
                                </div>
                                <div v-else class="text-center py-12 bg-white rounded-xl shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900">No Courses Found</h3>
                                </div>
                            </div>
                            <div v-show="activeTab === 'exams'">
                                <div v-if="content.exams && content.exams.length > 0">
                                    <!-- Pass the ENTIRE array to the CORRECT component -->
                                    <CategoriesMassProps :ayhoots="content.exams" category="Teacher's" />
                                </div>
                                <div v-else class="text-center py-12 bg-white rounded-xl shadow-sm">
                                    <h3 class="text-lg font-medium text-gray-900">No Exams Found</h3>
                                </div>
                            </div>
                            <div v-show="activeTab === 'about'">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm">
                                        <h3 class="text-lg font-semibold text-gray-800 mb-3">About {{ user.name }}</h3>
                                        <p class="text-sm text-gray-600 leading-relaxed">
                                            {{ user.about || 'No information provided.' }}
                                        </p>
                                    </div>
                                    <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm">
                                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Affiliated Institutes</h3>
                                        <div
                                            v-if="user.institutes && user.institutes.length > 0"
                                            class="space-y-3 max-h-60 overflow-y-auto"
                                        >
                                            <router-link
                                                v-for="institute in user.institutes"
                                                :key="institute.id"
                                                :to="`/admin/institute/profile/${institute.id}`"
                                                class="block p-3 bg-gray-50 rounded-lg border hover:bg-gray-100"
                                            >
                                                <p class="text-sm font-medium text-gray-700">{{ institute.name }}</p>
                                            </router-link>
                                        </div>
                                        <p v-else class="text-sm text-gray-500 py-4 text-center">
                                            Not affiliated with any institutes.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </main>
        </div>

        <Teleport to="body">
            <div
                v-if="showPlanModal"
                @click.self="closePlanModal"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50"
            >
                <div class="bg-white w-full max-w-2xl mx-4 rounded-xl shadow-xl" @click.stop>
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Choose a Plan</h3>
                            <button @click="closePlanModal" class="text-gray-400 hover:text-gray-600 p-1 -mr-1">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div
                                v-for="plan in availablePlans"
                                :key="plan.id"
                                @click="selectedPlan = plan"
                                class="p-4 border-2 rounded-lg cursor-pointer"
                                :class="
                                    selectedPlan?.id === plan.id
                                        ? 'border-blue-500 bg-blue-50'
                                        : 'border-gray-200 hover:border-blue-400'
                                "
                            >
                                <h4 class="font-bold text-gray-800">{{ plan.name || plan.title }}</h4>
                                <p class="text-sm text-gray-600 mt-1">{{ plan.description || 'No description.' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-500 rounded-b-xl flex justify-end gap-3">
                        <ResponsiveButton @click="closePlanModal" type="light-gray" class="p-2"
                            >Cancel</ResponsiveButton
                        >
                        <ResponsiveButton
                            @click="updateUserPlan"
                            :disabled="!selectedPlan || selectedPlan.id === user.plan.id"
                            type="light-blue"
                            class="p-2"
                            >Confirm Change</ResponsiveButton
                        >
                    </div>
                </div>
            </div>
        </Teleport>

        <Teleport to="body">
            <div
                v-if="showMessageModal"
                @click.self="closeMessageModal"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50"
            >
                <div class="bg-white w-full max-w-lg mx-4 rounded-xl shadow-xl" @click.stop>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-start">
                            <h3 class="text-lg font-semibold text-gray-900">Send Message to {{ user.name }}</h3>
                            <button @click="closeMessageModal" class="text-gray-400 hover:text-gray-600 p-1 -mr-1">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                        <div>
                            <input
                                v-model="messageSubject"
                                type="text"
                                class="w-full p-3 border-2 border-gray-200 rounded-md transition"
                                placeholder="Subject"
                            />
                        </div>
                        <div>
                            <textarea
                                v-model="messageContent"
                                rows="4"
                                class="w-full p-3 border-2 border-gray-200 rounded-md transition"
                                placeholder="Type your message..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-500 rounded-b-xl flex justify-end gap-3">
                        <ResponsiveButton type="light-gray" @click="closeMessageModal" class="p-2"
                            >Cancel</ResponsiveButton
                        >
                        <ResponsiveButton type="light-blue" @click="sendMessage" class="p-2">Send</ResponsiveButton>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from 'vue-toast-notification'
import { apiCall } from '@/services/apiService'
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import DashVerified from '@/components/atoms/icons/DashVerified.vue'
import CategoriesMassProps from '@/components/discover/admin/CategoriesMassProps.vue'
import CategoriesMassCourseProps from '@/components/discover/admin/CategoriesMassCourseProps.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'

const $toast = useToast()
const route = useRoute()
const activeTab = ref('courses')
const isSidebarOpen = ref(false)
const isLoading = ref(true)
const isContentLoading = ref(false)
const teacherId = route.params.id

// --- IMPORTANT: Centralized URL Formatting ---
const getFullImageUrl = (relativePath) => {
    // !!! REPLACE THIS with your actual backend URL !!!

    if (!relativePath) {
        return null // Return null or a path to a default placeholder image
    }
    // Assumes Laravel's public storage link setup (php artisan storage:link)
    return `/${relativePath}`
}

const hasLoaded = ref({
    courses: false,
    exams: false,
})

const user = ref({
    name: '',
    username: '',
    verified: false,
    image: '/imgHolders/profile.svg',
    cover: '/imgHolders/institute.png',
    website: '',
    plan: { id: null, name: 'No Plan' },
    wallet: '0',
    card: '',
    about: '',
    institutes: [],
    stats: {},
})

const content = ref({
    exams: [],
    courses: [],
})

const tabs = [
    { id: 'courses', name: 'Courses' },
    { id: 'exams', name: 'Exams' },
    { id: 'about', name: 'About' },
]

const userName = computed(() => user.value?.name || 'Teacher')
const userUsername = computed(() => user.value?.username || 'teacher_user')

const showMessageModal = ref(false)
const messageContent = ref('')
const messageSubject = ref('')
const showPlanModal = ref(false)
const availablePlans = ref([])
const selectedPlan = ref(null)

const fetchInitialData = async () => {
    isLoading.value = true
    try {
        const [profileRes, plansRes] = await Promise.all([
            apiCall('GET', `/api/admin/teacher/${teacherId}`),
            apiCall('GET', `/api/admin/plan/get/all`),
        ])

        const teacherData = profileRes.teacher
        user.value = {
            name: teacherData.name,
            username: teacherData.username,
            verified: !!teacherData.verified_at,
            image: getFullImageUrl(teacherData.avatar) || '/imgHolders/profile.svg',
            cover: getFullImageUrl(teacherData.cover) || '/imgHolders/institute.png',
            website: teacherData.website,
            plan: teacherData.plan
                ? { id: teacherData.plan.id, name: teacherData.plan.name || teacherData.plan.title }
                : { id: null, name: 'No Plan' },
            wallet: teacherData.wallet || '0',
            card: teacherData.card || 'N/A',
            about: teacherData.description,
            institutes: teacherData.institutes,
            stats: {
                students_count: teacherData.students_count,
                exams_count: teacherData.exams_count,
                courses_count: teacherData.courses_count,
            },
        }
        availablePlans.value = plansRes.plans.data || plansRes.plans
        await fetchTabData(activeTab.value)
    } catch (error) {
        console.error('Failed to fetch initial teacher data:', error)
        $toast.error('Failed to load teacher profile.')
    } finally {
        isLoading.value = false
    }
}

const fetchTabData = async (tabId) => {
    if (hasLoaded.value[tabId] || tabId === 'about') return

    isContentLoading.value = true
    try {
        let response
        if (tabId === 'courses') {
            response = await apiCall('GET', `/api/admin/teacher/${teacherId}/courses`)
            content.value.courses = response.courses.data.map((c) => ({
                ...c,
                image: getFullImageUrl(c.cover),
            }))
        } else if (tabId === 'exams') {
            response = await apiCall('GET', `/api/admin/teacher/${teacherId}/exams`)
            content.value.exams = response.exams.data.map((e) => ({
                ...e,
                image: getFullImageUrl(e.cover),
            }))
        }
        hasLoaded.value[tabId] = true
    } catch (error) {
        console.error(`Failed to fetch data for tab ${tabId}:`, error)
        $toast.error(`Could not load ${tabId}.`)
    } finally {
        isContentLoading.value = false
    }
}

watch(activeTab, (newTab) => {
    fetchTabData(newTab)
})

onMounted(fetchInitialData)

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value
}

const openPlanModal = () => {
    if (availablePlans.value && Array.isArray(availablePlans.value)) {
        selectedPlan.value = availablePlans.value.find((p) => p.id === user.value.plan.id) || null
    }
    showPlanModal.value = true
}
const closePlanModal = () => {
    showPlanModal.value = false
}

const updateUserPlan = async () => {
    if (!selectedPlan.value || selectedPlan.value.id === user.value.plan.id) return
    try {
        const res = await apiCall('POST', `/api/admin/teacher/${teacherId}/plan`, { plan_id: selectedPlan.value.id })
        user.value.plan = { id: res.teacher.plan.id, name: res.teacher.plan.name || res.teacher.plan.title }
        $toast.success('Plan updated successfully!')
        closePlanModal()
    } catch (error) {
        $toast.error(error.message || 'Failed to update plan.')
    }
}

const openMessageModal = () => {
    showMessageModal.value = true
}
const closeMessageModal = () => {
    showMessageModal.value = false
    messageContent.value = ''
    messageSubject.value = ''
}

const sendMessage = async () => {
    if (!messageSubject.value.trim()) {
        return $toast.error('Subject cannot be empty.')
    }
    if (!messageContent.value.trim()) {
        return $toast.error('Message cannot be empty.')
    }
    try {
        await apiCall('POST', `/api/admin/ticket`, {
            recipient_type: 'teacher',
            recipient_id: teacherId,
            message: messageContent.value,
            title: messageSubject.value,
        })
        $toast.success('Message sent successfully!')
        closeMessageModal()
    } catch (error) {
        $toast.error(error.message || 'Failed to send message.')
    }
}
</script>
