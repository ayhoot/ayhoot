<template>
    <AyCard padding="lg">
        <router-link to="/admin/institute">Back to institutes</router-link>
        <h2 class="text-2xl font-semibold mb-6">{{ isEdit ? 'Edit institute' : 'Create institute' }}</h2>

        <form @submit.prevent="handleSubmit" class="space-y-4">
            <AyInput v-model="institute.name" label="Name" placeholder="Name" />
            <AyInput v-model="institute.username" label="Username" placeholder="Username" />
            <AyInput v-model="institute.password" type="password" label="Password" placeholder="Password" />
            <AyInput
                v-model="institute.password_confirmation"
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
        institute: Object,
        isEdit: Boolean,
    },
    methods: {
        async handleSubmit() {
            const url = this.isEdit ? `/api/admin/institute/${this.institute.id}` : `/api/admin/institute`
            await apiCall('POST', url, this.institute)
            this.$router.push('/admin/institute')
        },
    },
}
</script>
