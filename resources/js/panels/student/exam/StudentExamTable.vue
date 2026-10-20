<template>
    <div class="overflow-x-auto">
        <DataTable v-if="exams.length && !isLoading" :columns="examColumns" :rows="exams" wrapper-class=""
            row-class="hover:bg-gray-50 transition-colors duration-150">
            <template #cell:details="{ row: exam }">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5.5 h-5.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div :class="$i18n.locale === 'fa' ? 'mr-4' : 'ml-4'">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <div class="text-sm font-medium text-gray-900 truncate max-w-[16ch] overflow-hidden text-ellipsis"
                                :title="exam.title">
                                {{ exam.title }}
                            </div>
                            <span v-if="exam.is_assigned && !exam.assignment_completed"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 whitespace-nowrap">
                                {{ $t('ayhoot.table.body.assignment') }}
                            </span>
                            <span v-else-if="exam.is_assigned && exam.assignment_completed"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 whitespace-nowrap">
                                {{ $t('ayhoot.table.body.assignmentCompleted') }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ formatDate(exam.created_at) }}
                        </div>
                    </div>
                </div>
            </template>
            <template #cell:status="{ row: exam }">
                <StatusBadge :status="exam.status"
                    :base-class="'px-2 py-1 text-xs font-medium rounded-full capitalize'">
                    {{ $t('ayhoot.filters.' + exam.status) }}
                </StatusBadge>
            </template>
            <template #cell:category="{ row: exam }">
                <div class="text-sm text-gray-900">
                    {{ exam?.category?.title || $t('ayhoot.table.body.noCategory') }}
                </div>
                <div v-if="exam?.category?.title" class="text-sm text-gray-500">
                    {{ $t('ayhoot.table.body.categoryLabel') }}
                </div>
            </template>
            <template #cell:plays="{ row: exam }">
                <div class="text-sm text-gray-900">
                    {{ exam.students_count || 0 }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ $t('ayhoot.table.body.tutees') }}
                </div>
            </template>
            <template #cell:questions="{ row: exam }">
                <div class="text-sm text-gray-900">
                    {{ exam.questions_count || 0 }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ $t('ayhoot.table.body.questions') }}
                </div>
            </template>
            <template #cell:accessibility="{ row: exam }">
                <div v-if="exam.monetize === 'free'" class="text-sm text-gray-900">
                    {{ $t('ayhoot.table.body.free') }}
                </div>
                <div v-else class="text-sm text-gray-900">
                    {{ exam.price ? exam.price + ' Rials' : $t('ayhoot.table.body.notForSale') }}
                </div>
            </template>
            <template #header:actions="{ column }">
                <span class="sr-only">{{ column.label }}</span>
            </template>
            <template #cell:actions="{ row: exam }">
                <div class="flex items-center justify-end relative">
                    <ResponsiveButton type="light-blue"
                        :to="exam.is_assigned && !exam.assignment_completed
                            ? `/student/exam/${exam.id}/play?game=${exam.assigned_game_id}`
                            : `/student/exam/${exam.id}/play`"
                        class="text-xs px-2 py-1 mx-0.5">
                        {{ $t('ayhoot.table.actions.play') }}
                    </ResponsiveButton>

                    <ResponsiveButton type="light-gray" class="text-xs px-2 py-1 mx-0.5"
                        @click="$emit('toggle-dropdown', exam.id, $event)">
                        {{ $t('ayhoot.table.actions.more') }}
                    </ResponsiveButton>
                </div>
            </template>
        </DataTable>

        <div v-else-if="isLoading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900">
            </div>
            <p class="mt-2 text-sm text-gray-500">{{ $t('ayhoot.loading') }}</p>
        </div>

        <div v-else class="text-center py-12">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" class="mx-auto"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M15.0001 2.3999V5.9999C15.0001 6.66264 15.5373 7.1999 16.2001 7.1999H19.8001M8.40008 7.1999H10.8001M8.40008 10.7999H15.6001M8.40008 14.3999H15.6001M18.0001 4.1999C17.466 3.72204 16.9118 3.15528 16.5619 2.78718C16.3291 2.54224 16.0074 2.3999 15.6695 2.3999H6.5998C5.27432 2.3999 4.19981 3.47441 4.1998 4.79989L4.19971 19.1998C4.1997 20.5253 5.27421 21.5998 6.5997 21.5998L17.3997 21.5999C18.7252 21.5999 19.7997 20.5254 19.7998 19.2L19.8001 6.47773C19.8001 6.1709 19.683 5.87594 19.4701 5.65503C19.0763 5.24655 18.4187 4.57442 18.0001 4.1999Z"
                    class="stroke-current" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $t('ayhoot.empty.title') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $t('ayhoot.empty.description') }}</p>
            <div class="mt-6">
                <AyButton to="/student/discover" type="dash-blue" class="inline-flex items-center">
                    {{ $t('ayhoot.discover') }}
                </AyButton>
            </div>
        </div>
    </div>
</template>

<script>
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import DataTable from '@/components/shared/DataTable.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'

export default {
    name: 'StudentExamTable',
    components: {
        ResponsiveButton,
        AyButton,
        DataTable,
        StatusBadge
    },
    props: {
        exams: {
            type: Array,
            required: true
        },
        isLoading: {
            type: Boolean,
            default: false
        },
        formatDate: {
            type: Function,
            required: true
        }
    },
    computed: {
        examColumns() {
            return [
                { key: 'details', label: this.$t('ayhoot.table.header.details'), thClass: 'py-2' },
                { key: 'status', label: this.$t('ayhoot.table.header.status') },
                { key: 'category', label: this.$t('ayhoot.table.header.category') },
                { key: 'plays', label: this.$t('ayhoot.table.header.plays') },
                { key: 'questions', label: this.$t('ayhoot.table.header.questions') },
                { key: 'accessibility', label: this.$t('ayhoot.table.header.accessibility') },
                { key: 'actions', label: this.$t('ayhoot.table.header.actions'), align: 'right', thClass: 'relative', tdClass: 'font-medium' },
            ]
        }
    },
}
</script>
