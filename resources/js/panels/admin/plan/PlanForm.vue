<template>
    <div class="bg-gray-50 min-h-screen p-4 sm:p-6 lg:p-4">
        <div class="mx-auto">
            <!-- Header -->
            <div class="mb-6 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                <div>
                    <router-link
                        to="/admin/plan"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-2"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Plans
                    </router-link>
                    <h1 class="text-3xl font-bold text-gray-800 mt-1">
                        {{ isEdit ? 'Edit Plan' : 'Create a New Plan' }}
                    </h1>
                </div>
            </div>

            <!-- Form & Preview Grid -->
            <form @submit.prevent="submitForm">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Form Fields (Left and Middle columns) -->
                    <div class="lg:col-span-2 bg-white p-6 sm:p-8 rounded-xl shadow-lg">
                        <!-- All your form fields remain exactly the same. No changes needed in the <template> part for inputs/selectors. -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <!-- SECTION 1: CORE DETAILS -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Plan Details</h3>
                            </div>
                            <!-- Title -->
                            <AyInput v-model="plan.title" label="Title" placeholder="e.g., Bronze Plan" />
                            <!-- Price -->
                            <AyInput
                                v-model="plan.price"
                                type="number"
                                label="Price"
                                :disabled="plan.is_free"
                                placeholder="e.g., 50000"
                            />
                            <!-- Description -->
                            <div class="md:col-span-2">
                                <AyTextarea
                                    v-model="plan.description"
                                    label="Description"
                                    :rows="3"
                                    placeholder="A brief description of the plan..."
                                />
                            </div>
                            <!-- Mark as Popular -->
                            <div>
                                <label for="is_popular" class="block text-sm font-semibold text-gray-700"
                                    >Mark as Popular?</label
                                >
                                <select
                                    id="is_popular"
                                    v-model="plan.is_popular"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md sm:text-sm"
                                >
                                    <option :value="false">No</option>
                                    <option :value="true">Yes</option>
                                </select>
                            </div>
                            <!-- Is Free -->
                            <div>
                                <label for="is_free" class="block text-sm font-semibold text-gray-700"
                                    >Is this plan free?</label
                                >
                                <select
                                    id="is_free"
                                    v-model="plan.is_free"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md sm:text-sm"
                                >
                                    <option :value="true">Yes, it's free</option>
                                    <option :value="false">No, it's paid</option>
                                </select>
                            </div>

                            <!-- SECTION 2: VISUALS -->
                            <div class="md:col-span-2 mt-4">
                                <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">
                                    Visual Customization
                                </h3>
                            </div>
                            <!-- Color Selector -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Plan Color</label>
                                <div class="flex flex-wrap gap-3">
                                    <button
                                        type="button"
                                        v-for="color in availableColors"
                                        :key="color.value"
                                        @click="plan.color = color.value"
                                        class="h-10 w-10 rounded-full transition-transform duration-150 ease-in-out transform hover:scale-110 flex items-center justify-center"
                                        :style="{ backgroundColor: color.value }"
                                        :class="{ 'ring-2 ring-offset-2 ring-indigo-500': plan.color === color.value }"
                                    >
                                        <svg
                                            v-if="plan.color === color.value"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-6 w-6 text-white"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <!-- Logo Selector -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Plan Logo</label>
                                <div class="grid grid-cols-3 sm:grid-cols-5 gap-4">
                                    <div
                                        v-for="logo in availableLogos"
                                        :key="logo"
                                        @click="plan.logo = logo"
                                        class="p-3 bg-gray-100 rounded-lg cursor-pointer flex items-center justify-center transition-all"
                                        :class="{ 'ring-2 ring-indigo-500 bg-indigo-50': plan.logo === logo }"
                                    >
                                        <img :src="logo" alt="Plan Logo" class="h-16 w-16 object-contain" />
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 3: PLAN FEATURES (DYNAMIC) -->
                            <div class="md:col-span-2 mt-4">
                                <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Plan Features</h3>
                                <p v-if="loadingFeatures" class="text-gray-500">Loading features...</p>
                                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div v-for="feature in allFeatures" :key="feature.id" class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :id="`feature-${feature.id}`"
                                            :value="feature.id"
                                            v-model="plan.feature_ids"
                                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                        />
                                        <label :for="`feature-${feature.id}`" class="ml-3 block text-sm text-gray-700">
                                            {{ feature.title }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Preview Card (Right column) -->
                    <!-- This section also remains unchanged, it will work automatically with the v-model data. -->
                    <div class="lg:col-span-1">
                        <!-- Sticky Wrapper -->
                        <div class="sticky top-8">
                            <!-- Preview Card -->
                            <div
                                :class="[
                                    'relative flex flex-col p-6 rounded-xl border transition-all duration-300 text-left',
                                    plan.is_popular
                                        ? 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white border-indigo-700 shadow-xl'
                                        : 'bg-white border-gray-200',
                                ]"
                            >
                                <div
                                    v-if="plan.is_popular"
                                    class="absolute top-0 -translate-y-1/2 left-1/2 -translate-x-1/2 px-3 py-1 text-sm font-semibold text-white bg-purple-600 rounded-full"
                                >
                                    Most Popular
                                </div>
                                <div class="flex-grow">
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-16 h-16 bg-white/20 rounded-lg p-2 flex-shrink-0">
                                            <img
                                                :src="plan.logo"
                                                alt="Plan Logo"
                                                class="w-full h-full object-contain"
                                            />
                                        </div>
                                        <div>
                                            <h2
                                                class="text-2xl font-bold"
                                                :class="[plan.is_popular ? 'text-white' : 'text-gray-800']"
                                            >
                                                {{ plan.title || 'Plan Title' }}
                                            </h2>
                                        </div>
                                    </div>
                                    <p
                                        class="mt-2 text-sm min-h-[40px]"
                                        :class="[plan.is_popular ? 'text-indigo-200' : 'text-gray-500']"
                                    >
                                        {{ plan.description || 'Plan description goes here...' }}
                                    </p>
                                    <div class="mt-6">
                                        <span
                                            class="text-4xl font-extrabold"
                                            :class="[plan.is_popular ? 'text-white' : 'text-gray-900']"
                                        >
                                            <span v-if="!plan.is_free && plan.price > 0">${{ plan.price }}</span>
                                            <span v-else>Free</span>
                                        </span>
                                        <span
                                            v-if="!plan.is_free && plan.price > 0"
                                            class="text-base font-medium"
                                            :class="[plan.is_popular ? 'text-indigo-200' : 'text-gray-500']"
                                            >/month</span
                                        >
                                    </div>
                                    <ul class="mt-6 space-y-3">
                                        <li
                                            v-for="featureId in plan.feature_ids"
                                            :key="featureId"
                                            class="flex items-center gap-3"
                                        >
                                            <svg
                                                :class="[
                                                    'flex-shrink-0 w-5 h-5',
                                                    plan.is_popular ? 'text-green-300' : 'text-green-500',
                                                ]"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                            <span :class="[plan.is_popular ? 'text-indigo-100' : 'text-gray-600']">{{
                                                getFeatureTitle(featureId)
                                            }}</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="mt-8">
                                    <button
                                        type="button"
                                        :class="[
                                            'w-full py-3 px-6 text-base font-semibold rounded-lg transition-all',
                                            plan.is_popular
                                                ? 'bg-white text-indigo-600 hover:bg-indigo-50'
                                                : 'bg-indigo-500 text-white hover:bg-indigo-600',
                                        ]"
                                        :style="!plan.is_popular ? { backgroundColor: plan.color } : {}"
                                    >
                                        Choose {{ plan.title || 'Plan' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 pt-6 border-t border-gray-200 flex justify-start">
                    <AyButton type="light-blue" :disabled="isSubmitting">
                        <span v-if="isSubmitting">Saving...</span>
                        <span v-else>{{ isEdit ? 'Update Plan' : 'Create Plan' }}</span>
                    </AyButton>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { apiCall } from '@/services/apiService' // <-- IMPORT your apiCall helper
import { useToast } from 'vue-toast-notification'
import 'vue-toast-notification/dist/theme-sugar.css' // Or your preferred theme
import AyInput from '@/components/atoms/form/AyInput.vue'
import AyTextarea from '@/components/atoms/form/AyTextarea.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'

export default {
    components: { AyInput, AyTextarea, AyButton },
    props: {
        isEdit: { type: Boolean, default: false },
        planId: { type: [String, Number], default: null },
    },
    // Use setup() to initialize composables like useToast
    setup() {
        const $toast = useToast()
        return { $toast }
    },
    data() {
        return {
            plan: {
                title: '',
                description: '',
                color: '#4f46e5',
                logo: '/plans/Free.png',
                price: 0,
                is_popular: false,
                is_free: true,
                feature_ids: [],
            },
            allFeatures: [],
            loadingFeatures: false,
            isSubmitting: false,
            // UI helper data remains the same
            availableColors: [
                { name: 'Indigo', value: '#4f46e5' },
                { name: 'Purple', value: '#8A2BE2' },
                { name: 'Teal', value: '#14b8a6' },
                { name: 'Sky', value: '#0ea5e9' },
                { name: 'Rose', value: '#f43f5e' },
                { name: 'Charcoal', value: '#36454F' },
            ],
            availableLogos: [
                '/plans/Free.png',
                '/plans/Bronze.png',
                '/plans/Silver.png',
                '/plans/Gold.png',
                '/plans/Premium.png',
            ],
        }
    },
    watch: {
        'plan.is_free'(isFree) {
            if (isFree) {
                this.plan.price = 0
            }
        },
    },
    async mounted() {
        await this.fetchAllFeatures()
        if (this.isEdit && this.planId) {
            await this.fetchPlanData()
        }
    },
    methods: {
        getFeatureTitle(id) {
            const feature = this.allFeatures.find((f) => f.id === id)
            return feature ? feature.title : ''
        },
        async fetchAllFeatures() {
            this.loadingFeatures = true
            try {
                const response = await apiCall('GET', '/api/admin/plan/features')
                this.allFeatures = response.features
            } catch (error) {
                this.$toast.error('Could not load plan features.')
            } finally {
                this.loadingFeatures = false
            }
        },
        async fetchPlanData() {
            try {
                // Use apiCall instead of axios
                const response = await apiCall('GET', `/api/admin/plan/${this.planId}`)
                this.plan = response.plan
            } catch (error) {
                this.$toast.error('Could not load plan data for editing.')
                this.$router.push('/admin/plan')
            }
        },
        async submitForm() {
            this.isSubmitting = true
            try {
                const payload = { ...this.plan, price: Number(this.plan.price) || 0 }
                let response

                if (this.isEdit) {
                    response = await apiCall('POST', `/api/admin/plan/${this.planId}`, payload)
                } else {
                    response = await apiCall('POST', '/api/admin/plan', payload)
                }

                this.$toast.success(response.message)
                this.$router.push('/admin/plan')
            } catch (error) {
                // Check for validation errors from the backend
                if (error.response && error.response.errors) {
                    const validationErrors = Object.values(error.response.errors)
                    validationErrors.forEach((err) => {
                        this.$toast.error(err[0]) // Display each validation error
                    })
                } else {
                    // Generic error toast for other issues
                    this.$toast.error('An unexpected error occurred. Please try again.')
                }
            } finally {
                this.isSubmitting = false
            }
        },
    },
}
</script>
