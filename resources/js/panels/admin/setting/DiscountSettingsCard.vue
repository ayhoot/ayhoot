<template>
    <div class="bg-white p-6 sm:p-8 rounded-xl shadow-sm lg:col-span-1">
        <h3 class="text-xl font-bold text-gray-800">Discount Settings</h3>
        <p class="text-gray-500 mt-1 mb-6 text-sm">Manage multiple discount configurations.</p>

        <div class="space-y-3 mb-6">
            <div
                v-for="(discount, index) in discounts"
                :key="discount.id || index"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200"
            >
                <div>
                    <h4 class="font-semibold text-gray-800">{{ discount.code }}</h4>
                    <p class="text-sm text-gray-500">
                        {{ discount.percentage }}% - {{ formatDate(discount.startDate) }} to
                        {{ formatDate(discount.endDate) }}
                    </p>
                </div>
                <div class="flex gap-1 flex-shrink-0 ml-4">
                    <ResponsiveButton @click="editDiscount(discount, index)" type="light-blue" class="p-1 text-sm px-2"
                        >Edit</ResponsiveButton
                    >
                    <ResponsiveButton @click="deleteDiscount(index)" type="light-red" class="p-1 text-sm px-2"
                        >Delete</ResponsiveButton
                    >
                </div>
            </div>
        </div>

        <form @submit.prevent="saveDiscountSettings" class="space-y-6">
            <h4 class="font-semibold text-gray-800">
                {{ editingDiscountIndex !== null ? 'Edit Discount' : 'Add New Discount' }}
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="discount-percentage" class="block text-sm font-semibold text-gray-700"
                        >Discount Percentage</label
                    >
                    <div class="relative mt-1 rounded-md">
                        <input
                            type="number"
                            id="discount-percentage"
                            v-model.number="currentDiscount.percentage"
                            class="pr-12 block w-full px-3 py-2 border-2 border-gray-300 rounded-sm focus:outline-none sm:text-sm"
                            placeholder="10"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">%</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Generated Code</label>
                    <div v-if="currentDiscount.code" @click="copyCode" class="relative mt-1 cursor-pointer">
                        <input
                            :value="currentDiscount.code"
                            readonly
                            class="block w-full px-3 py-2 border-2 border-gray-200 bg-gray-100 rounded-sm focus:outline-none sm:text-sm font-mono cursor-pointer"
                        />
                        <div
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 pointer-events-none"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                    </div>
                    <div
                        v-else
                        class="mt-1 flex items-center justify-center h-[42px] px-3 py-2 border-2 border-gray-200 border-dashed bg-gray-50 rounded-sm"
                    >
                        <p class="text-sm text-gray-500">Enter a percentage.</p>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <AyInput v-model="currentDiscount.startDate" type="date" label="Start Date" />
                <AyInput v-model="currentDiscount.endDate" type="date" label="End Date" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Applicable Plans</label>
                <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div v-for="plan in availablePlans" :key="plan.id" class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                :id="'plan-' + plan.id"
                                :value="plan.id"
                                v-model="currentDiscount.plans"
                                type="checkbox"
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                            />
                        </div>
                        <div class="ml-3 text-sm">
                            <label :for="'plan-' + plan.id" class="font-medium text-gray-700">{{ plan.title }}</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <label class="flex items-center">
                    <input
                        id="courses-checkbox"
                        type="checkbox"
                        v-model="currentDiscount.courses"
                        @change="updateCoursesExams"
                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                    />
                    <span class="ml-2 text-sm font-medium text-gray-700">Applicable to Courses</span>
                </label>
            </div>

            <div class="mt-2">
                <label class="flex items-center">
                    <input
                        id="exams-checkbox"
                        type="checkbox"
                        v-model="currentDiscount.exams"
                        @change="updateCoursesExams"
                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                    />
                    <span class="ml-2 text-sm font-medium text-gray-700">Applicable to Exams</span>
                </label>
            </div>

            <div class="pt-2 flex gap-2">
                <AyButton type="light-blue">{{ editingDiscountIndex !== null ? 'Update' : 'Add' }} Discount</AyButton>
                <AyButton v-if="editingDiscountIndex !== null" type="light-gray" @click="cancelEditDiscount"
                    >Cancel</AyButton
                >
            </div>
        </form>
    </div>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useToast } from 'vue-toast-notification'
import { useConfirm } from '@/composables/useConfirm'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'

const { confirm: confirmDialog } = useConfirm()

export default {
    name: 'DiscountSettingsCard',
    components: { ResponsiveButton, AyInput, AyButton },
    setup() {
        const $toast = useToast()
        return { $toast }
    },
    data() {
        return {
            discounts: [],
            currentDiscount: {
                percentage: null,
                plans: [],
                startDate: '',
                endDate: '',
                code: null,
                courses: false,
                exams: false,
            },
            editingDiscountIndex: null,
            availablePlans: [],
            errors: {},
        }
    },
    watch: {
        'currentDiscount.percentage'(newValue) {
            if (newValue && newValue > 0) this.generateCode()
            else this.currentDiscount.code = null
        },
    },
    mounted() {
        this.fetchSettings()
        this.fetchPlans()
    },
    methods: {
        async fetchSettings() {
            try {
                const response = await apiCall('GET', '/api/admin/setting')
                if (response.discount_settings) {
                    this.discounts = Array.isArray(response.discount_settings)
                        ? response.discount_settings
                        : [response.discount_settings]
                }
            } catch (error) {
                this.$toast.error('Could not load settings.')
            }
        },
        async fetchPlans() {
            try {
                const response = await apiCall('GET', '/api/admin/plan/get/all')
                this.availablePlans = response.plans
            } catch (error) {
                console.error('Error fetching plans:', error)
            }
        },
        updateCoursesExams() {
            if (this.currentDiscount.courses && this.currentDiscount.exams) {
                this.currentDiscount.courses = false
                this.currentDiscount.exams = false
            } else if (this.currentDiscount.courses) {
                this.currentDiscount.exams = false
            } else if (this.currentDiscount.exams) {
                this.currentDiscount.courses = false
            }
        },
        async saveDiscountSettings() {
            this.errors = {}
            try {
                const discountData = {
                    discounts: this.discounts,
                    currentDiscount: this.currentDiscount,
                    editingIndex: this.editingDiscountIndex,
                }
                const response = await apiCall('POST', '/api/admin/setting/discount', discountData)
                this.$toast.success(response.message)
                this.resetCurrentDiscount()
                this.fetchSettings()
            } catch (error) {
                if (error.response?.errors) this.errors = error.response.errors
            }
        },
        editDiscount(discount, index) {
            this.currentDiscount = { ...discount }
            this.editingDiscountIndex = index
            this.errors = {}
        },
        cancelEditDiscount() {
            this.resetCurrentDiscount()
        },
        async deleteDiscount(index) {
            if (await confirmDialog({ message: 'Are you sure you want to delete this discount?', variant: 'danger' })) {
                this.discounts.splice(index, 1)
                try {
                    const response = await apiCall('POST', '/api/admin/setting/discount', {
                        discounts: this.discounts,
                        currentDiscount: {},
                        editingIndex: null,
                    })
                    this.$toast.success('Discount deleted successfully')
                    this.fetchSettings()
                } catch (error) {
                    if (error.response?.errors) this.errors = error.response.errors
                    else this.$toast.error('Failed to delete discount')
                }
            }
        },
        resetCurrentDiscount() {
            this.currentDiscount = {
                percentage: null,
                plans: [],
                startDate: '',
                endDate: '',
                code: null,
                courses: false,
                exams: false,
            }
            this.editingDiscountIndex = null
        },
        generateCode() {
            const randomChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
            let result = ''
            for (let i = 0; i < 6; i++) {
                result += randomChars.charAt(Math.floor(Math.random() * randomChars.length))
            }
            this.currentDiscount.code = `SAVE${this.currentDiscount.percentage}-${result}`
        },
        async copyCode() {
            if (!this.currentDiscount.code) return
            try {
                await navigator.clipboard.writeText(this.currentDiscount.code)
                this.$toast.success('Code copied to clipboard!')
            } catch (err) {
                console.error('Failed to copy code: ', err)
            }
        },
        formatDate(date) {
            if (!date) return 'N/A'
            return new Date(date).toLocaleDateString()
        },
    },
}
</script>
