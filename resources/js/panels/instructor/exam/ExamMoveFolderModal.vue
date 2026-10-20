<template>
    <Modal :model-value="show" size="sm" :title="$t('ayhoot.moveFolderModal.title')" @close="$emit('close')">
        <div>
                <p class="text-xs text-gray-500 mb-4">{{ $t('ayhoot.moveFolderModal.description') }}</p>
                <div class="mb-4">
                    <AyInput
                        :model-value="searchQuery"
                        :placeholder="$t('ayhoot.moveFolderModal.searchPlaceholder')"
                        @update:model-value="$emit('update:searchQuery', $event)"
                    />
                </div>
                <div v-if="isLoading" class="text-center py-6">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500">
                    </div>
                    <p class="mt-3 text-sm text-gray-500">{{ $t('ayhoot.moveFolderModal.loading') }}</p>
                </div>
                <div v-else-if="folders.length > 0" class="max-h-72 overflow-y-auto custom-scrollbar pr-2 space-y-1">
                    <ul class="divide-y divide-gray-100">
                        <li v-for="folder in folders" :key="folder.id"
                            class="flex justify-between border-b border-gray-300 items-center py-2">
                            <span class="text-sm text-gray-700 font-medium truncate" :title="folder.name">
                                {{ folder.name }}
                            </span>
                            <ResponsiveButton type="light-green" v-if="!folder.isExamAlreadyInFolder"
                                @click="$emit('add', folder)" :disabled="isAssigning === folder.id"
                                class="text-xs px-3 py-1.5 rounded-md font-semibold" :class="{
                                    'bg-blue-300 cursor-wait': isAssigning === folder.id,
                                }">
                                <span v-if="isAssigning === folder.id">{{
                                    $t('ayhoot.moveFolderModal.adding')
                                    }}</span>
                                <span v-else>{{ $t('ayhoot.moveFolderModal.add') }}</span>
                            </ResponsiveButton>
                            <ResponsiveButton type="light-red" v-else @click="$emit('remove', folder)"
                                :disabled="isAssigning === folder.id" class="text-xs px-3 py-1.5 rounded-md font-semibold"
                                :class="{ 'bg-red-300 cursor-wait': isAssigning === folder.id }">
                                <span v-if="isAssigning === folder.id">{{
                                    $t('ayhoot.moveFolderModal.removing')
                                    }}</span>
                                <span v-else>{{ $t('ayhoot.moveFolderModal.remove') }}</span>
                            </ResponsiveButton>
                        </li>
                    </ul>
                </div>
                <div v-else class="text-center py-6">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-600 font-medium">
                        {{
                            searchQuery
                                ? $t('ayhoot.moveFolderModal.empty.titleWithSearch')
                                : $t('ayhoot.moveFolderModal.empty.title')
                        }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">
                        {{ $t('ayhoot.moveFolderModal.empty.description') }}
                    </p>
                </div>
        </div>
        <template #footer>
            <AyButton type="light-gray" @click="$emit('close')">
                {{ $t('ayhoot.moveFolderModal.done') }}
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
    name: 'ExamMoveFolderModal',
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
        folders: {
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
