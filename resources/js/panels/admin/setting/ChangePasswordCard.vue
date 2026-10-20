<template>
    <div class="bg-white p-6 sm:p-8 rounded-xl shadow-sm lg:col-span-1">
        <h3 class="text-xl font-bold text-gray-800">Change Password</h3>
        <p class="text-gray-500 mt-1 mb-6 text-sm">Update your administrator password for security.</p>
        <form @submit.prevent="changePassword" class="space-y-4">
            <AyInput
                v-model="passwordData.current"
                type="password"
                label="Current Password"
                placeholder="••••••••"
                :error="errors.current?.[0]"
            />
            <AyInput
                v-model="passwordData.new"
                type="password"
                label="New Password"
                placeholder="••••••••"
                :error="errors.new?.[0]"
            />
            <AyInput
                v-model="passwordData.new_confirmation"
                type="password"
                label="Confirm New Password"
                placeholder="••••••••"
            />
            <div class="pt-2">
                <AyButton type="light-blue">Update Password</AyButton>
            </div>
        </form>
    </div>
</template>

<script>
import { apiCall } from '@/services/apiService'
import { useToast } from 'vue-toast-notification'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'

export default {
    name: 'ChangePasswordCard',
    components: { AyInput, AyButton },
    setup() {
        const $toast = useToast()
        return { $toast }
    },
    data() {
        return {
            passwordData: { current: '', new: '', new_confirmation: '' },
            errors: {},
        }
    },
    methods: {
        async changePassword() {
            this.errors = {}
            try {
                const response = await apiCall('POST', '/api/admin/setting/change-password', this.passwordData)
                this.$toast.success(response.message)
                this.passwordData = { current: '', new: '', new_confirmation: '' }
            } catch (error) {
                if (error.response?.errors) this.errors = error.response.errors
            }
        },
    },
}
</script>
