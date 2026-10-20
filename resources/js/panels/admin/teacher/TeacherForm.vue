<template>
    <AyCard padding="lg">
        <router-link to="/admin/teacher">Back to teachers</router-link>
        <h2 class="text-2xl font-semibold mb-6">{{ isEdit ? 'Edit teacher' : 'Create teacher' }}</h2>

        <form @submit.prevent="handleSubmit" class="space-y-4">
            <AyInput v-model="teacher.first_name" label="First Name" placeholder="First Name" />
            <AyInput v-model="teacher.last_name" label="Last Name" placeholder="Last Name" />
            <AyInput v-model="teacher.email" label="Email" placeholder="Email" />
            <AyInput v-model="teacher.phone" label="Phone" placeholder="Phone" />
            <AyInput v-model="teacher.username" label="Username" placeholder="Username" />
            <AyInput v-model="teacher.password" type="password" label="Password" placeholder="Password" />
            <AyInput
                v-model="teacher.password_confirmation"
                type="password"
                label="Password Confirmation"
                placeholder="Password Confirmation"
            />

            <AyButton type="dark-blue" class="w-full mt-2" @click="handleSubmit">Save</AyButton>
        </form>
    </AyCard>
</template>

<script>
import { apiCall } from '@/services/apiService'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import AyCard from '@/components/shared/AyCard.vue'

export default {
    components: { AyInput, AyButton, AyCard },
    props: {
        teacher: Object,
        isEdit: Boolean,
    },
    methods: {
        async handleSubmit() {
            const url = this.isEdit ? `/api/admin/teacher/${this.teacher.id}` : `/api/admin/teacher`
            await apiCall('POST', url, this.teacher)
            this.$router.push('/admin/teacher')
        },
    },
}
</script>
