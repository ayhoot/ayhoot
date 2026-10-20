<template>
    <div class="w-full">
        <div class="mb-4 bg-blue-100/50 p-3 rounded-md text-sm text-blue-700 border border-blue-200">
            <div class="font-semibold mb-1">Instructions:</div>
            <p>Add items in the correct sequence. Students must arrange them in this exact order to get the answer right.</p>
        </div>

        <div class="mb-4">
            <draggable :list="localSequenceOptions" @end="onDragEnd" item-key="fieldId" handle=".drag-handle" ghost-class="opacity-50" class="space-y-2">
                <template #item="{ element, index }">
                    <div class="flex items-center bg-white p-2 border border-gray-300 rounded-md shadow-sm">
                        <div class="drag-handle cursor-move mr-2 p-1 rounded hover:bg-gray-100">
                             <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </div>
                        <div :class="['w-10 h-10 sm:w-12 sm:h-12 rounded flex items-center justify-center mr-2 shrink-0', getShapeColor(element.shape)]">
                            <!-- Shape SVGs -->
                            <svg v-if="getShapeIcon(element.shape) === 'triangle'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3L19.7942 16.5H4.20577L12 3Z" fill="white" /></svg>
                            <svg v-else-if="getShapeIcon(element.shape) === 'diamond'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3L21 12L12 21L3 12L12 3Z" fill="white" /></svg>
                            <svg v-else-if="getShapeIcon(element.shape) === 'circle'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" fill="white" /></svg>
                            <svg v-else-if="getShapeIcon(element.shape) === 'rectangle'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="5" y="5" width="14" height="14" fill="white" /></svg>
                        </div>
                        <div class="w-7 h-7 flex items-center justify-center bg-gray-100 rounded-full text-sm font-medium text-gray-700 mr-2 shrink-0">
                            {{ index + 1 }}
                        </div>
                        <input type="text" v-model="element.text" @input="emitUpdate" placeholder="Add item text" class="flex-1 px-3 py-2 border-0 focus:outline-none focus:ring-0 text-sm sm:text-base" />
                        <button @click="removeSequenceOption(index)" class="ml-2 text-red-500 hover:text-red-700 p-1 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </template>
            </draggable>
        </div>

        <ResponsiveButton @click="addSequenceOption" class="w-full p-3" type="light-blue">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Add Sequence Item
        </ResponsiveButton>

        <div class="mt-4">
            <div class="flex items-center">
                <input type="checkbox" id="partial-credit" :checked="modelValue.allowPartialCredit"
                       @change="updateAllowPartialCredit($event.target.checked)"
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                <label for="partial-credit" class="ml-2 block text-sm text-gray-700">
                    Allow partial credit for partially correct sequences
                </label>
            </div>
        </div>
    </div>
</template>

<script>
import draggable from 'vuedraggable';
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'; // Adjust path
import { nextTick } from 'vue';

export default {
    name: 'Sequence',
    components: { draggable, ResponsiveButton },
    props: {
        modelValue: { // { sequenceOptions: [], allowPartialCredit: true }
            type: Object,
            default: () => ({ sequenceOptions: [], allowPartialCredit: true })
        },
        shapeMap: { type: Array, required: true },
        getShapeColor: { type: Function, required: true },
        getShapeIcon: { type: Function, required: true },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            localSequenceOptions: []
        }
    },
    watch: {
        'modelValue.sequenceOptions': {
            handler(newVal) {
                this.localSequenceOptions = JSON.parse(JSON.stringify(newVal || [])).map(opt => ({
                    ...opt,
                    fieldId: opt.id || this.generateId() // Ensure unique key for draggable
                }));
                this.updateOrders();
            },
            deep: true,
            immediate: true
        }
    },
    methods: {
        generateId() {
            return Math.random().toString(36).substr(2, 9);
        },
        addSequenceOption() {
            if (this.localSequenceOptions.length >= 4) return;
            const shapeIndex = this.localSequenceOptions.length % this.shapeMap.length;
            const shape = this.shapeMap[shapeIndex];
            const newId = String.fromCharCode(65 + this.localSequenceOptions.length);

            this.localSequenceOptions.push({
                text: '',
                shape: `${shape.color}${shape.name}`,
                id: newId, // This is the 'A', 'B', 'C' ID
                order: this.localSequenceOptions.length,
                fieldId: this.generateId() // For v-for key
            });
            this.emitUpdate();
        },
        removeSequenceOption(index) {
            if (this.localSequenceOptions.length <= 2) return;
            this.localSequenceOptions.splice(index, 1);
            this.updateOrders();
            this.emitUpdate();
        },
        onDragEnd() {
            this.updateOrders();
            this.emitUpdate();
        },
        updateOrders() {
             this.localSequenceOptions.forEach((item, index) => {
                item.order = index;
            });
        },
        updateAllowPartialCredit(value) {
            this.$emit('update:modelValue', { ...this.modelValue, allowPartialCredit: value });
        },
        emitUpdate() {
            nextTick(() => {
                this.$emit('update:modelValue', {
                    sequenceOptions: JSON.parse(JSON.stringify(this.localSequenceOptions)),
                    allowPartialCredit: this.modelValue.allowPartialCredit
                });
            });
        }
    },
    created() {
      if (this.modelValue && this.modelValue.sequenceOptions) {
        this.localSequenceOptions = JSON.parse(JSON.stringify(this.modelValue.sequenceOptions)).map(opt => ({
            ...opt,
            fieldId: opt.id || this.generateId()
        }));
        this.updateOrders();
      }
    }
};
</script>