<template>
    <div class="px-6 py-4 bg-white shadow-sm rounded-xl flex flex-col gap-y-2 hover:shadow-md text-start">
        <div class="w-full border-b-1 border-gray-300">
            <div class="w-max border-b-2 pb-2 border-gray-700 text-sm font-bold">
                {{ $t('dashboard.liveSession.title') }}
            </div>
        </div>

        <div class="py-4">
            <div class="text-start mb-4">
                <div class="flex items-center gap-2 justify-start">
                    <svg width="20" height="20" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M23.8 12L11.1605 12M5.83431 16.1693H3.4M5.83431 12.1464H1M5.83431 8.12356H3.4M10.1074 4.59596L23.3627 11.0228C24.179 11.4186 24.179 12.5814 23.3627 12.9772L10.1074 19.4041C9.19931 19.8443 8.23411 18.9161 8.63861 17.9915L11.0695 12.4353C11.1909 12.1578 11.1909 11.8422 11.0695 11.5647L8.63861 6.00848C8.23412 5.08391 9.19931 4.15568 10.1074 4.59596Z"
                            stroke="black" stroke-width="2" stroke-linecap="round" />
                    </svg>

                    <h3 class="font-semibold text-lg text-gray-800 ">{{ $t('dashboard.liveSession.hostLive') }}</h3>
                </div>
                <p class="text-sm pt-1 text-gray-600">{{ $t('dashboard.liveSession.description') }}</p>
            </div>

            <!-- Select Exam Dropdown -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ $t('dashboard.liveSession.selectExam') }}
                </label>
                <AySelect v-model="selectedExamId" :disabled="loading || !exams.length">
                    <option value="">{{ $t('dashboard.liveSession.chooseExam') }}</option>
                    <option v-for="exam in exams" :key="exam.id" :value="exam.id">
                        {{ exam.title }} ({{ exam.questions_count || exam.questions?.length || 0 }} {{
                            $t('dashboard.liveSession.questions') }})
                    </option>
                </AySelect>
            </div>

            <!-- Session Title Input -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ $t('dashboard.liveSession.sessionTitle') }}
                </label>
                <AyInput v-model="sessionTitle" type="text" :placeholder="defaultSessionTitle" :disabled="loading" />
            </div>

            <!-- Host Live Button -->
            <AyButton type="dash-blue" @click="hostLiveSession" :disabled="!canHostSession" class="w-full py-3">
                <span v-if="loading" class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    {{ $t('dashboard.liveSession.creating') }}...
                </span>
                <span v-else>{{ $t('dashboard.liveSession.hostLiveBtn') }}</span>
            </AyButton>

            <!-- Success Message -->
            <div v-if="successMessage" class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-green-700 text-sm">{{ successMessage }}</span>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-red-700 text-sm">{{ errorMessage }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import AySelect from '@/components/atoms/form/AySelect.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'

export default {
    name: 'HostLiveCard',
    components: { AySelect, AyInput, AyButton },
    props: {
        exams: {
            type: Array,
            default: () => []
        },
        loading: {
            type: Boolean,
            default: false
        }
    },
    setup(props) {
        const router = useRouter()

        const selectedExamId = ref('')
        const sessionTitle = ref('')
        const loading = ref(false)
        const successMessage = ref('')
        const errorMessage = ref('')
        const userRole = ref(localStorage.getItem('role') || 'teacher')

        const selectedExam = computed(() => {
            return props.exams.find(exam => exam.id === selectedExamId.value)
        })

        const defaultSessionTitle = computed(() => {
            if (selectedExam.value) {
                return `Live: ${selectedExam.value.title}`
            }
            return 'Live Session'
        })

        const canHostSession = computed(() => {
            return !loading.value && selectedExamId.value && sessionTitle.value.trim()
        })

        const hostLiveSession = async () => {
            if (!canHostSession.value) return

            loading.value = true
            successMessage.value = ''
            errorMessage.value = ''

            try {
                const response = await axios.post('/api/live-session/create', {
                    exam_id: selectedExamId.value,
                    title: sessionTitle.value.trim() || defaultSessionTitle.value,
                    description: `Live session for exam: ${selectedExam.value?.title}`
                })

                successMessage.value = 'Live session created successfully!'

                // Redirect to appropriate host page after a short delay
                setTimeout(() => {
                    const hostRoute = `/${userRole.value}/host-live/${response.data.session.id}`
                    router.push(hostRoute)
                }, 1000)

            } catch (error) {
                console.error('Error creating live session:', error)
                errorMessage.value = error.response?.data?.message || 'Failed to create live session'
            } finally {
                loading.value = false
            }
        }

        onMounted(() => {
            // Auto-select first exam if available
            if (props.exams.length > 0) {
                selectedExamId.value = props.exams[0].id
            }
        })

        return {
            selectedExamId,
            sessionTitle,
            loading,
            successMessage,
            errorMessage,
            defaultSessionTitle,
            canHostSession,
            hostLiveSession
        }
    }
}
</script>
