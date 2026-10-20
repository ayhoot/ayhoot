<template>
    <div class="p-4 border-b border-gray-100">
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
            <!-- Left: Search & Filters -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto">
                <!-- Search -->
                <AyInput type="text" :model-value="search" @update:model-value="$emit('update:search', $event)"
                    :placeholder="$t('ayhoot.searchPlaceholder')" class="min-w-80 bg-gray-50 font-bold">
                    <template #icon>
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </template>
                </AyInput>

                <!-- Sort By -->
                <AySelect :model-value="sort" @update:model-value="$emit('update:sort', $event)">
                    <option value="created_at">{{ $t('ayhoot.filters.dateCreated') }}</option>
                    <option value="title">{{ $t('ayhoot.filters.title') }}</option>
                    <option value="status">{{ $t('ayhoot.filters.status') }}</option>
                </AySelect>
            </div>

            <AyButton to="/student/discover" type="dash-blue"
                class="w-full sm:w-auto inline-flex items-center justify-center sm:justify-start">
                {{ $t('ayhoot.discover') }}
            </AyButton>
        </div>
    </div>
</template>

<script>
import AyButton from '@/components/atoms/button/AyButton.vue'
import AySelect from '@/components/atoms/form/AySelect.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'

export default {
    name: 'StudentExamFilters',
    components: {
        AyButton,
        AySelect,
        AyInput
    },
    props: {
        search: String,
        sort: String
    }
}
</script>
