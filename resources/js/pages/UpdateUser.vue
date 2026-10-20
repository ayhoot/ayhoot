<template>
    <PublicLayout nav="login">
        <div
            class="relative flex flex-col items-center justify-center min-h-[calc(100vh-4rem)] bg-gray-100 overflow-hidden"
        >
            <div class="bg-white p-8 rounded-2xl shadow-sm w-full max-w-sm z-50">
                <h1 class="text-2xl font-bold text-center mb-6">Update Profile</h1>

                <!-- General Error Message -->
                <div v-if="generalError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-red-600 text-sm">{{ generalError }}</p>
                </div>

                <form @submit.prevent="handleSubmit">
                    <!-- INSTITUTE ROLE: Single Name Field -->
                    <div v-if="role === 'institute'">
                        <div class="py-1">
                            <label for="name" class="block text-xs font-bold">Institute Name</label>
                            <input
                                type="text"
                                id="name"
                                v-model="name"
                                placeholder="Institute Name"
                                :class="[
                                    'mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 sm:text-sm',
                                    errors.name ? 'border-red-500' : 'border-black',
                                ]"
                                @input="clearErrors"
                            />
                            <span v-if="errors.name" class="text-red-500 text-xs mt-1">
                                {{ errors.name[0] }}
                            </span>
                        </div>
                    </div>

                    <!-- OTHER ROLES: First + Last Name -->
                    <div v-else>
                        <div class="py-1">
                            <label for="first_name" class="block text-xs font-bold">First Name</label>
                            <input
                                type="text"
                                id="first_name"
                                v-model="first_name"
                                placeholder="First Name"
                                :class="[
                                    'mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 sm:text-sm',
                                    errors.first_name ? 'border-red-500' : 'border-black',
                                ]"
                                @input="clearErrors"
                            />
                            <span v-if="errors.first_name" class="text-red-500 text-xs mt-1">
                                {{ errors.first_name[0] }}
                            </span>
                        </div>

                        <div class="py-1">
                            <label for="last_name" class="block text-xs font-bold">Last Name</label>
                            <input
                                type="text"
                                id="last_name"
                                v-model="last_name"
                                placeholder="Last Name"
                                :class="[
                                    'mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 sm:text-sm',
                                    errors.last_name ? 'border-red-500' : 'border-black',
                                ]"
                                @input="clearErrors"
                            />
                            <span v-if="errors.last_name" class="text-red-500 text-xs mt-1">
                                {{ errors.last_name[0] }}
                            </span>
                        </div>
                    </div>

                    <AyButton
                        class="py-2 w-full mt-4"
                        type="dark-blue"
                        :handleClick="handleSubmit"
                        :disabled="isSubmitting"
                    >
                        {{ isSubmitting ? 'Updating...' : 'Update Profile' }}
                    </AyButton>
                </form>
            </div>
        </div>
    </PublicLayout>
</template>

<script>
import { apiCall } from '@/services/apiService'
import AyButton from '@/components/atoms/button/AyButton.vue'
import PublicLayout from '@/components/layout/PublicLayout.vue'

export default {
    name: 'UpdateUser',
    components: { AyButton, PublicLayout },
    data() {
        return {
            name: '', // Added for institute role
            first_name: '',
            last_name: '',
            errors: {},
            generalError: '',
            isSubmitting: false,
            role: localStorage.getItem('role'),
        }
    },
    created() {
        // Pre-fill form with existing data
        const user = JSON.parse(localStorage.getItem('user'))
        if (this.role === 'institute') {
            this.name = user.name || ''
        } else {
            this.first_name = user.first_name || ''
            this.last_name = user.last_name || ''
        }
    },
    methods: {
        clearErrors() {
            this.errors = {}
            this.generalError = ''
        },
        async handleSubmit() {
            try {
                this.isSubmitting = true
                this.clearErrors()

                const role = this.role
                const payload = { role }

                // Prepare payload based on role
                if (role === 'institute') {
                    payload.name = this.name
                } else {
                    payload.first_name = this.first_name
                    payload.last_name = this.last_name
                }

                const response = await apiCall('POST', '/api/update-user', payload)

                if (response.success) {
                    const user = JSON.parse(localStorage.getItem('user'))

                    // Update local storage based on role
                    if (role === 'institute') {
                        user.name = this.name
                    } else {
                        user.first_name = this.first_name
                        user.last_name = this.last_name
                    }

                    localStorage.setItem('user', JSON.stringify(user))

                    // Handle redirection
                    if (response.role == 'teacher' && response.user.institutes.length > 0) {
                        this.$router.push('/teacher/select-institute')
                    } else {
                        this.$router.push(`/${response.role}/dashboard`)
                    }
                } else {
                    this.generalError = response.message || 'An error occurred while updating'
                }
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = error.response.data.errors
                } else if (error.response?.data?.message) {
                    this.generalError = error.response.data.message
                } else {
                    this.generalError = 'An unexpected error occurred'
                }
            } finally {
                this.isSubmitting = false
            }
        },
    },
}
</script>
