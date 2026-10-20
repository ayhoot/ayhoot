<template>
    <svg :width="width" :height="height" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Triangle -->
        <path v-if="type === 'triangle'" d="M12 3L19.7942 16.5H4.20577L12 3Z" :fill="fill"/>
        
        <!-- Diamond -->
        <path v-else-if="type === 'diamond'" d="M12 3L21 12L12 21L3 12L12 3Z" :fill="fill"/>
        
        <!-- Circle -->
        <circle v-else-if="type === 'circle'" cx="12" cy="12" r="8" :fill="fill"/>
        
        <!-- Rectangle -->
        <rect v-else-if="type === 'rectangle'" x="5" y="5" width="14" height="14" :fill="fill"/>
        
        <!-- Pentagon -->
        <path v-else-if="type === 'pentagon'" d="M12 3L20.5595 9.21885L17.2901 19.2812H6.70993L3.44049 9.21885L12 3Z" :fill="fill"/>
        
        <!-- Hexagon -->
        <path v-else-if="type === 'hexagon'" d="M12 3L19.7942 7.5V16.5L12 21L4.20577 16.5V7.5L12 3Z" :fill="fill"/>
    </svg>
</template>

<script>
export default {
    name: 'QuizShape',
    props: {
        type: {
            type: String,
            required: true,
            validator: (value) => ['triangle', 'diamond', 'circle', 'rectangle', 'pentagon', 'hexagon'].includes(value)
        },
        width: {
            type: [String, Number],
            default: 24
        },
        height: {
            type: [String, Number],
            default: 24
        },
        fill: {
            type: String,
            default: 'currentColor'
        }
    }
}
</script>
