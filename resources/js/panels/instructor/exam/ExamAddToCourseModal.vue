<template>
    <Modal :model-value="show" size="sm" :title="$t('ayhoot.addCourseModal.title')" @close="$emit('close')">
        <div>
                <p class="text-xs text-gray-500 mb-4">{{ $t('ayhoot.addCourseModal.description') }}</p>
                <div class="mb-4">
                    <AyInput
                        :model-value="searchQuery"
                        :placeholder="$t('ayhoot.addCourseModal.searchPlaceholder')"
                        @update:model-value="$emit('update:searchQuery', $event)"
                    />
                </div>
                <div v-if="isLoading" class="text-center py-6">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500">
                    </div>
                    <p class="mt-3 text-sm text-gray-500">{{ $t('ayhoot.addCourseModal.loading') }}</p>
                </div>
                <div v-else-if="courses.length > 0" class="max-h-72 overflow-y-auto custom-scrollbar pr-2 space-y-1">
                    <ul class="divide-y divide-gray-100">
                        <li v-for="course in courses" :key="course.id"
                            class="flex justify-between items-center py-2 border-b border-gray-300">
                            <span class="text-sm text-gray-700 font-medium truncate" :title="course.title || course.name">
                                {{ course.title || course.name }}
                            </span>
                            <ResponsiveButton type="light-green" v-if="!course.isExamAlreadyInCourse"
                                @click="$emit('add', course)" :disabled="isAssigning === course.id"
                                class="text-xs px-3 py-1.5 rounded-md font-semibold" :class="{
                                    'bg-green-300 cursor-wait': isAssigning === course.id,
                                }">
                                <span v-if="isAssigning === course.id">{{
                                    $t('ayhoot.addCourseModal.adding')
                                    }}</span>
                                <span v-else>{{ $t('ayhoot.addCourseModal.add') }}</span>
                            </ResponsiveButton>
                            <ResponsiveButton type="light-red" v-else @click="$emit('remove', course)"
                                :disabled="isAssigning === course.id" class="text-xs px-3 py-1.5 rounded-md font-semibold"
                                :class="{ 'bg-red-300 cursor-wait': isAssigning === course.id }">
                                <span v-if="isAssigning === course.id">{{
                                    $t('ayhoot.addCourseModal.removing')
                                    }}</span>
                                <span v-else>{{ $t('ayhoot.addCourseModal.remove') }}</span>
                            </ResponsiveButton>
                        </li>
                    </ul>
                </div>
                <div v-else class="text-center py-6">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-600 font-medium">
                        {{
                            searchQuery
                                ? $t('ayhoot.addCourseModal.empty.titleWithSearch')
                                : $t('ayhoot.addCourseModal.empty.title')
                        }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">
                        {{ $t('ayhoot.addCourseModal.empty.description') }}
                    </p>
                </div>
        </div>
        <template #footer>
            <AyButton type="light-gray" @click="$emit('close')">
                {{ $t('ayhoot.addCourseModal.done') }}
            </AyButton>
        </template>
    </Modal>
</template>

<script>
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import Modal from '@/components/shared/Modal.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'

export default {
    name: 'ExamAddToCourseModal',
    components: {
        ResponsiveButton,
        Modal,
        AyInput,
        AyButton
    },
    props: {
        show: {
            type: Boolean,
            default: false
        },
        courses: {
            type: Array,
            default: () => []
        },
        isLoading: {
            type: Boolean,
            default: false
        },
        isAssigning: {
            type: Number,
            default: null
        },
        searchQuery: {
            type: String,
            default: ''
        }
    }
}
</script>
