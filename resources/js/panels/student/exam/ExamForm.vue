<template>
    <div>
        <div class="fixed inset-0 flex items-center justify-center bg-gray-100 overflow-hidden">
            <div
                class="absolute w-100 h-100 bg-pink-200 rounded-full opacity-30"
                style="top: 10%; left: -10%; transform: rotate(25deg)"
            ></div>
            <div
                class="absolute w-100 h-100 bg-blue-200 rounded-lg opacity-20"
                style="top: 40%; right: -10%; transform: rotate(-15deg)"
            ></div>
            <div class="bg-white w-2xl p-6 rounded-lg shadow-sm">
                <form @submit.prevent="handleSubmit">
                    <!-- Title Input -->
                    <div class="mb-4">
                        <label for="title" class="block text-lg font-bold text-gray-700">Start creating</label>
                        <input
                            type="text"
                            id="title"
                            v-model="exam.title"
                            placeholder="Enter title for your Ayhoot ..."
                            class="mt-2 block w-full px-3 py-2 font-semibold border-2 border-gray-300 rounded-md focus:outline-none"
                        />
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-2">
                        <ResponsiveButton class="p-2" type="light-gray" @click="$router.back()">
                            Cancel
                        </ResponsiveButton>
                        <ResponsiveButton type="light-blue" class="p-2" @click="handleSubmit">
                            {{ isEdit ? 'Update Exam' : 'Create Exam' }}
                        </ResponsiveButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { apiCall } from '@/services/apiService'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'

export default {
    props: {
        exam: Object,
        isEdit: Boolean,
    },
    components: {
        ResponsiveButton,
    },
    data() {
        return {
            teachers: [],
            institutes: [],
            teacher_search: '',
            institute_search: '',
        }
    },
    computed: {
        filteredTeachers() {
            return this.teachers.filter((teacher) =>
                teacher.name.toLowerCase().includes(this.teacher_search.toLowerCase())
            )
        },
        filteredInstitutes() {
            return this.institutes.filter((institute) =>
                institute.name.toLowerCase().includes(this.institute_search.toLowerCase())
            )
        },
    },
    async mounted() {
        await this.fetchTeachers()
        await this.fetchInstitutes()
    },
    methods: {
        async handleSubmit() {
            const url = this.isEdit ? `/api/teacher/exam/${this.exam.id}` : `/api/teacher/exam`
            const response = await apiCall('POST', url, this.exam)
            this.$router.push(`/teacher/exam/${response.exam.id}/create`)
        },
        // async fetchTeachers() {
        //     const response = await apiCall('GET', '/api/teacher/teacher')
        //     this.teachers = response.teachers.data
        // },
        // async fetchInstitutes() {
        //     const response = await apiCall('GET', '/api/teacher/institute')
        //     this.institutes = response.institutes.data
        // },
    },
}
</script>
