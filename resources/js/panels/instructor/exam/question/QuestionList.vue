<template>
    <!-- Two root nodes (Vue 3 fragments): desktop sidebar stays a direct flex child;
         the mobile bar is position-fixed. This preserves the original page layout. -->
    <!-- Desktop Left Sidebar -->
    <div
        class="w-full md:w-1/6 min-h-[calc(100vh-4rem)] bg-white overflow-y-auto p-4 border-gray-200 hidden md:block mt-16"
        :class="$i18n.locale === 'fa' ? 'border-l' : 'border-r'"
    >
            <div v-if="questions.length">
                <div
                    v-for="(q_item, index) in questions"
                    :key="q_item.id"
                    @click="$emit('select', q_item.id)"
                    class="py-1 flex items-center justify-between cursor-pointer"
                >
                    <button
                        class="text-red-400 hover:text-red-700 shrink-0"
                        :class="$i18n.locale === 'fa' ? 'pl-2' : 'pr-2'"
                        @click.stop="$emit('delete', q_item.id)"
                    >
                        <DashLilRemove />
                    </button>
                    <div
                        :class="[
                            'w-full rounded-sm border-2 flex flex-col space-y-1 p-2 py-2 cursor-pointer hover:bg-gray-100 transition-all',
                            selectedId === q_item.id
                                ? 'border-dashed border-blue-300 bg-blue-100'
                                : 'border-dashed border-gray-200',
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="text-xs px-1 font-semibold text-gray-500">{{ index + 1 }} -</span>
                                <span class="text-xs font-semibold truncate max-w-[16ch] text-gray-500">
                                    {{ stripHtml(q_item.title) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-1 items-center">
                            <span class="text-[0.6rem] px-1 rounded-full border" :class="getBadgeStyle(q_item.type)">
                                {{ badgeLabel(q_item.type) }}
                            </span>
                            <span
                                class="text-[0.6rem] px-1 rounded-full border bg-gray-100 text-gray-400"
                                v-if="q_item.type !== 'slide'"
                            >
                                {{ q_item.duration }}s
                            </span>
                            <span
                                class="text-[0.6rem] px-1 rounded-full border-gray-800 border bg-gray-500 text-white"
                                v-if="q_item.type !== 'slide' && q_item.difficulty_level"
                            >
                                {{ $t(`createQuestion.difficultyLevels.${q_item.difficulty_level}`) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="questions.length === 0" class="py-1 flex items-center justify-between cursor-pointer">
                <button class="pr-2 text-white"><DashLilRemove /></button>
                <div
                    class="w-full rounded-sm border-2 flex flex-col p-2 py-4 cursor-pointer transition-all marching-ants border-dashed border-gray-200"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex justify-center w-full items-center">
                            <span class="text-xs font-semibold text-center w-full text-gray-200">{{
                                $t('createQuestion.sidebar.addFirstQuestion')
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview placeholder -->
            <div v-if="previewThumbnail" class="py-1 flex items-center justify-between cursor-pointer">
                <button class="pr-2 text-white"><DashLilRemove /></button>
                <div
                    class="w-full rounded-sm border-2 flex flex-col p-2 py-4 cursor-pointer transition-all marching-ants border-dashed border-gray-200"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex justify-center w-full items-center">
                            <span class="text-xs font-semibold text-center w-full text-gray-200">{{
                                $t('createQuestion.sidebar.saveForPreview')
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div :class="$i18n.locale === 'fa' ? 'pr-6' : 'pl-6'">
                <ResponsiveButton type="light-blue" class="w-full p-1.5 mt-2" @click="$emit('create')">
                    {{ $t('createQuestion.sidebar.addQuestion') }}
                </ResponsiveButton>
            </div>
        </div>

        <!-- Mobile bottom bar -->
        <div
            class="fixed bottom-0 left-0 w-full bg-white/80 border-t border-gray-400 backdrop-blur z-40 block md:hidden"
        >
            <div class="flex items-center overflow-x-auto px-4 py-4 gap-2 scroll-x-no-expand">
                <div
                    v-for="(q_item, index) in questions"
                    :key="q_item.id"
                    @click="$emit('select', q_item.id)"
                    class="min-w-[160px] flex-shrink-0 p-2 rounded-md border cursor-pointer text-xs bg-white hover:bg-gray-100"
                    :class="selectedId === q_item.id ? 'border-blue-400 bg-blue-100' : 'border-gray-300'"
                >
                    <div class="flex items-center justify-between">
                        <div class="font-semibold truncate max-w-[12ch]">
                            {{ index + 1 }} - {{ stripHtml(q_item.title) }}
                        </div>
                        <button class="text-red-400 hover:text-red-700 shrink-0" @click.stop="$emit('delete', q_item.id)">
                            <DashLilRemove />
                        </button>
                    </div>
                    <div class="flex flex-wrap items-center gap-1 mt-1">
                        <span class="text-[0.6rem] px-1 border rounded" :class="getBadgeStyle(q_item.type)">
                            {{ badgeLabel(q_item.type) }}
                        </span>
                        <span
                            v-if="q_item.type !== 'slide'"
                            class="text-[0.6rem] px-1 border rounded bg-gray-100 text-gray-500"
                        >
                            {{ q_item.duration }}s
                        </span>
                    </div>
                </div>
                <div class="min-w-[40px] flex-shrink-0" :class="$i18n.locale === 'fa' ? 'mr-auto' : 'ml-auto'">
                    <ResponsiveButton type="light-blue" class="px-4 py-2 text-2xl" @click="$emit('create')">+</ResponsiveButton>
                </div>
            </div>
        </div>
    <!-- /mobile bar -->
</template>

<script>
import DashLilRemove from '@/components/atoms/icons/DashLilRemove.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'
import { stripHtml, getBadgeStyle, getBadgeLabel } from '@/utils/questionModel'

export default {
    name: 'QuestionList',
    components: { DashLilRemove, ResponsiveButton },
    props: {
        questions: { type: Array, default: () => [] },
        selectedId: { type: [Number, String], default: null },
        previewThumbnail: { type: Boolean, default: false },
    },
    emits: ['select', 'delete', 'create'],
    methods: {
        stripHtml,
        getBadgeStyle,
        badgeLabel(type) {
            return getBadgeLabel(type, this.$t)
        },
    },
}
</script>

<style scoped>
.scroll-x-no-expand {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    width: 100%;
    max-width: 100%;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
}
.scroll-x-no-expand > * {
    flex: 0 0 auto;
}
</style>
