<template>
    <PageWrapper>
        <div class="bg-white w-full p-6">
                    <div class="flex items-center justify-start mb-6 gap-2">
                        <h1 class="text-3xl font-bold text-gray-800">
                            {{ $t('ticket.createTicket') }}
                        </h1>
                    </div>

                    <form @submit.prevent="submitTicket" class="space-y-5">
                        <div class="relative">
                            <label for="title" class="block text-sm font-bold text-gray-500 mb-1">{{
                                $t('ticket.title')
                            }}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <!-- Pencil Icon -->
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 text-gray-400"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    id="title"
                                    v-model="ticket.title"
                                    required
                                    :placeholder="$t('ticket.titlePlaceholder')"
                                    class="w-full inline-flex pl-10 p-2 text-sm font-bold text-gray-900 bg-gray-50 rounded-sm border-2 border-gray-300 focus:outline-none"
                                />
                            </div>
                        </div>

                        <div class="relative">
                            <label for="message" class="block text-sm font-bold text-gray-500 mb-1">{{
                                $t('ticket.descriptionTitle')
                            }}</label>
                            <div class="relative">
                                <div class="absolute top-3 left-3 flex items-start pointer-events-none">
                                    <!-- Clipboard Icon -->
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 text-gray-400"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path
                                            d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"
                                        ></path>
                                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                    </svg>
                                </div>
                                <textarea
                                    id="message"
                                    v-model="ticket.message"
                                    required
                                    :placeholder="$t('ticket.descriptionPlaceholder')"
                                    class="w-full inline-flex pl-10 p-2 text-sm font-bold text-gray-900 bg-gray-50 rounded-sm border-2 border-gray-300 focus:outline-none"
                                    rows="5"
                                ></textarea>
                            </div>
                        </div>

                        <div class="pt-2">
                            <transition name="fade">
                                <div
                                    v-if="formErrors.length"
                                    class="bg-red-50 text-red-700 p-3 rounded-md mb-4 border border-red-200"
                                >
                                    <div class="flex">
                                        <!-- Alert Circle Icon -->
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-red-500 mr-2"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        <span class="text-sm">{{ $t('ticket.formError') }}</span>
                                    </div>
                                    <ul class="list-disc pl-10 mt-1 text-sm">
                                        <li v-for="(error, index) in formErrors" :key="index">
                                            {{ error }}
                                        </li>
                                    </ul>
                                </div>
                            </transition>

                            <button
                                type="submit"
                                :disabled="isLoading"
                                class="flex w-full items-center gap-2 justify-center rounded-md px-4 py-2 cursor-pointer font-bold text-sm', 'shadow-y-sm text-white bg-[#3450AE] hover:bg-[#152B65] shadow-[0_2px_0_rgba(15,29,66,1)] hover:shadow-none hover:translate-y-[2px]"
                            >
                                <template v-if="!isLoading">
                                    <!-- Send Icon -->
                                    <div class="mx-2">
                                        <svg
                                            width="20"
                                            height="20"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M8.3999 11.8299H10.5159M4.7999 21.6C3.47442 21.6 2.3999 20.5255 2.3999 19.2V4.80002C2.3999 3.47454 3.47442 2.40002 4.7999 2.40002H19.1999C20.5254 2.40002 21.5999 3.47454 21.5999 4.80002V19.2C21.5999 20.5255 20.5254 21.6 19.1999 21.6H4.7999ZM8.3999 15.2416V8.25942C8.3999 7.47105 9.23117 6.9596 9.93489 7.31502L16.2188 10.4887C16.9605 10.8633 17.0008 11.9073 16.2902 12.3379L10.0063 16.1464C9.30119 16.5737 8.3999 16.0661 8.3999 15.2416Z"
                                                stroke="white"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                            />
                                        </svg>
                                    </div>
                                </template>
                                <template v-else>
                                    <!-- Loader Icon -->
                                    <div class="mx-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 animate-spin"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <line x1="12" y1="2" x2="12" y2="6"></line>
                                            <line x1="12" y1="18" x2="12" y2="22"></line>
                                            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                                            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                                            <line x1="2" y1="12" x2="6" y2="12"></line>
                                            <line x1="18" y1="12" x2="22" y2="12"></line>
                                            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                                            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                                        </svg>
                                    </div>
                                </template>
                                {{ isLoading ? $t('ticket.submitting') : $t('ticket.createTicket') }}
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 text-center text-sm text-gray-500">
                        <div class="flex items-center justify-center">
                            <!-- Info Icon -->
                            <span class="hidden md:block lg:block">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 mr-1 text-gray-400"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                            </span>
                            <span>
                                {{ $t('ticket.info') }}
                            </span>
                        </div>
                    </div>
        </div>
    </PageWrapper>
</template>

<script>
import { ref, reactive } from 'vue'
import { apiCall } from '@/services/apiService'
import PageWrapper from '@/components/shared/PageWrapper.vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

export default {
    name: 'TicketForm',
    components: {
        PageWrapper,
    },
    setup() {
        const router = useRouter()
        const { t } = useI18n()

        const ticket = reactive({
            title: '',
            message: '',
        })

        const isLoading = ref(false)
        const formErrors = ref([])

        const validateForm = () => {
            const errors = []
            // These keys (e.g., 'ticket.validation.titleRequired') should be added to your i18n files.
            if (!ticket.title.trim()) {
                errors.push(t('ticket.validation.titleRequired', 'Title is required.'))
            } else if (ticket.title.length < 5) {
                errors.push(t('ticket.validation.titleMinLength', { min: 5 }, 'Title must be at least 5 characters.'))
            }

            if (!ticket.message.trim()) {
                errors.push(t('ticket.validation.descriptionRequired', 'Description is required.'))
            } else if (ticket.message.length < 10) {
                errors.push(
                    t(
                        'ticket.validation.descriptionMinLength',
                        { min: 10 },
                        'Description must be at least 10 characters.'
                    )
                )
            }

            formErrors.value = errors
            return errors.length === 0
        }

        const submitTicket = async () => {
            if (!validateForm()) {
                return
            }

            isLoading.value = true
            try {
                await apiCall('POST', '/api/student/ticket', ticket)
                ticket.title = ''
                ticket.message = ''
                router.push('/student/ticket') // Redirect after successful submission
            } catch (error) {
                console.error('Error creating ticket:', error)
                formErrors.value = [
                    t('ticket.validation.submissionFailed', 'Failed to create ticket. Please try again.'),
                ]
            } finally {
                isLoading.value = false
            }
        }

        return {
            ticket,
            isLoading,
            formErrors,
            submitTicket,
        }
    },
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
