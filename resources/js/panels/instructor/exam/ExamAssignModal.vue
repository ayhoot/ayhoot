<template>
    <Modal :model-value="show" size="xl" :title="$t('ayhoot.assignModal.formTitle')" body-class="" @close="$emit('close')">
        <form @submit.prevent="handleSubmit" class="flex flex-col">
            <div class="p-6 bg-gray-50 space-y-4">
                <!-- Assignment Title -->
                <div>
                    <AyInput
                        type="text"
                        v-model="form.title"
                        :label="$t('ayhoot.assignModal.assignmentTitle')"
                        :placeholder="$t('ayhoot.assignModal.titlePlaceholder')"
                    />
                </div>

                <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
                    <div class="w-full sm:w-1/2">
                        <AyInput type="date" v-model="form.date" :label="$t('ayhoot.assignModal.dueDate')" required />
                    </div>
                    <div class="w-full sm:w-1/2">
                        <AyInput type="time" v-model="form.time" :label="$t('ayhoot.assignModal.time')" required />
                    </div>
                </div>

                <!-- Assign to Group -->
                <div>
                    <label class="block font-semibold text-gray-700 mb-1 text-sm">{{
                        $t('ayhoot.assignModal.assignToGroupLabel')
                    }}</label>
                    <AyMultiSelect
                        v-model="form.selectedGroups"
                        :options="groupOptions"
                        :placeholder="$t('ayhoot.assignModal.selectGroupPlaceholder')"
                        :empty-text="isLoadingGroups ? $t('ayhoot.assignModal.loadingGroups') : $t('ayhoot.assignModal.noGroupsFound')"
                    />
                </div>
            </div>

            <div class="p-6 space-y-3">
                <div class="text-gray-500 text-sm font-medium">
                    {{ $t('ayhoot.assignModal.optionsTitle') }}
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="font-semibold text-gray-700 text-sm">{{ $t('ayhoot.assignModal.questionTimer') }}</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" :checked="form.questionTime === 'on'"
                            @change="form.questionTime = $event.target.checked ? 'on' : 'off'" />
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition-all peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500"></div>
                        <div class="absolute bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5"
                            :class="$i18n.locale === 'fa' ? 'right-1 top-1' : 'left-1 top-1'"></div>
                    </label>
                </div>
                <hr class="border-gray-200" />
                <div class="flex items-center justify-between py-2">
                    <span class="font-semibold text-gray-700 text-sm">{{ $t('ayhoot.assignModal.optionRandomize') }}</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" :checked="form.optionRandomize === 'on'"
                            @change="form.optionRandomize = $event.target.checked ? 'on' : 'off'" />
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition-all peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500"></div>
                        <div class="absolute bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5"
                            :class="$i18n.locale === 'fa' ? 'right-1 top-1' : 'left-1 top-1'"></div>
                    </label>
                </div>
                <hr class="border-gray-200" />
                <div class="flex items-center justify-between py-2">
                    <span class="font-semibold text-gray-700 text-sm">{{ $t('ayhoot.assignModal.participantLimit') }}</span>
                    <RouterLink to="#" class="underline text-blue-500 text-sm hover:text-blue-600">{{
                        $t('ayhoot.assignModal.upgrade') }}</RouterLink>
                </div>
            </div>
        </form>

        <template #footer>
            <AyButton type="light-gray" @click="$emit('close')" :disabled="isSubmitting">
                {{ $t('ayhoot.assignModal.cancel') }}
            </AyButton>
            <AyButton type="light-green" @click="handleSubmit" :disabled="isSubmitting">
                {{ isSubmitting ? ($t('ayhoot.assignModal.creating') || 'Creating...') : $t('ayhoot.assignModal.create') }}
            </AyButton>
        </template>
    </Modal>
</template>

<script>
import { RouterLink } from 'vue-router'
import Modal from '@/components/shared/Modal.vue'
import AyButton from '@/components/atoms/button/AyButton.vue'
import AyInput from '@/components/atoms/form/AyInput.vue'
import AyMultiSelect from '@/components/atoms/form/AyMultiSelect.vue'

export default {
    name: 'ExamAssignModal',
    components: { RouterLink, Modal, AyButton, AyInput, AyMultiSelect },
    props: {
        show: { type: Boolean, default: false },
        groups: { type: Array, default: () => [] },
        isLoadingGroups: { type: Boolean, default: false },
    },
    emits: ['submit', 'close'],
    data() {
        return {
            isSubmitting: false,
            form: {
                title: '',
                date: '',
                time: '',
                questionTime: 'off',
                optionRandomize: 'off',
                selectedGroups: [],
            },
        }
    },
    watch: {
        show(newVal) {
            if (newVal) {
                this.isSubmitting = false
                this.form = {
                    title: '',
                    date: '',
                    time: '',
                    questionTime: 'off',
                    optionRandomize: 'off',
                    selectedGroups: [],
                }
            }
        },
    },
    computed: {
        groupOptions() {
            return this.groups.map((g) => ({ value: g.id, label: g.title }))
        },
    },
    methods: {
        handleSubmit() {
            if (this.isSubmitting) return
            this.isSubmitting = true
            this.$emit('submit', { ...this.form })
            setTimeout(() => {
                this.isSubmitting = false
            }, 2000)
        },
    },
}
</script>
