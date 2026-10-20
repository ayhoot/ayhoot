<template>
    <div class="w-full h-full relative" @mousemove="handleMouseMove" @mouseleave="handleMouseLeave">
        <svg :viewBox="`0 0 ${width} ${height}`" class="w-full h-full">
            <defs>
                <linearGradient :id="gradientId" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" :stop-color="strokeColor" stop-opacity="0.4" />
                    <stop offset="100%" :stop-color="strokeColor" stop-opacity="0" />
                </linearGradient>
            </defs>

            <!-- Grid Lines -->
            <g v-if="grid">
                <line
                    v-for="tick in yTicks"
                    :key="`y-grid-${tick}`"
                    :x1="padding.left"
                    :y1="yScale(tick)"
                    :x2="width - padding.right"
                    :y2="yScale(tick)"
                    stroke="#e5e7eb"
                    stroke-width="1"
                />
            </g>

            <!-- Gradient Area Path -->
            <path v-if="gradient" :d="areaPath" :fill="`url(#${gradientId})`" />

            <!-- Main Line Path -->
            <path :d="linePath" fill="none" :stroke="strokeColor" stroke-width="2.5" class="line-path" />

            <!-- Interactive elements (Tooltip) -->
            <g v-if="activePoint" class="pointer-events-none">
                <!-- Vertical guide line -->
                <line
                    :x1="xScale(activePoint.index)"
                    :y1="height - padding.bottom"
                    :x2="xScale(activePoint.index)"
                    y2="0"
                    stroke="#a5b4fc"
                    stroke-width="1"
                    stroke-dasharray="4 4"
                />
                <!-- Highlighted point -->
                <circle :cx="xScale(activePoint.index)" :cy="yScale(activePoint.value)" r="6" :fill="strokeColor" />
            </g>

            <!-- Axes -->
            <g class="axis-labels">
                <!-- Y-axis labels -->
                <text
                    v-for="tick in yTicks"
                    :key="`y-label-${tick}`"
                    :x="padding.left - 8"
                    :y="yScale(tick)"
                    text-anchor="end"
                    dominant-baseline="middle"
                    font-size="12"
                    fill="#6b7280"
                >
                    {{ formatTick(tick) }}
                </text>

                <!-- X-axis labels -->
                <text
                    v-for="(point, i) in data"
                    :key="`x-label-${i}`"
                    :x="xScale(i)"
                    :y="height - padding.bottom + 16"
                    text-anchor="middle"
                    font-size="12"
                    fill="#6b7280"
                >
                    {{ point.name }}
                </text>
            </g>
        </svg>

        <!-- HTML-based Tooltip for better styling -->
        <div
            v-if="activePoint"
            class="absolute pointer-events-none transition-transform duration-100"
            :style="{ transform: `translate(${tooltipX}px, ${tooltipY}px)` }"
        >
            <div class="bg-white p-2 rounded-md shadow-lg border border-gray-200 text-center">
                <p class="text-xs text-gray-500 font-semibold">{{ activePoint.name }}</p>
                <p class="text-base font-bold text-gray-900">{{ activePoint.value.toLocaleString() }}</p>
            </div>
        </div>
    </div>
</template>

<script>
import { line, area, curveMonotoneX } from 'd3-shape'

export default {
    name: 'LineChart',
    props: {
        data: { type: Array, required: true },
        grid: { type: Boolean, default: false },
        gradient: { type: Boolean, default: false },
        strokeColor: { type: String, default: '#4f46e5' },
    },
    data() {
        return {
            width: 600,
            height: 300,
            padding: { top: 20, right: 20, bottom: 30, left: 40 },
            activePoint: null,
            tooltipX: 0,
            tooltipY: 0,
        }
    },
    computed: {
        gradientId() {
            return `gradient-${Math.random().toString(36).substr(2, 9)}`
        },
        yMax() {
            return Math.max(...this.data.map((d) => d.value)) * 1.1 || 10
        },
        yTicks() {
            const tickCount = 5
            const step = Math.ceil(this.yMax / tickCount / 10) * 10 // Round step to nearest 10
            return Array.from({ length: tickCount + 1 }, (_, i) => i * step)
        },
        xScale() {
            return (i) =>
                this.padding.left +
                i * ((this.width - this.padding.left - this.padding.right) / (this.data.length - 1 || 1))
        },
        yScale() {
            return (value) =>
                this.height -
                this.padding.bottom -
                (value / this.yMax) * (this.height - this.padding.top - this.padding.bottom)
        },
        linePath() {
            if (!this.data || this.data.length === 0) return ''
            const lineGenerator = line()
                .x((_, i) => this.xScale(i))
                .y((d) => this.yScale(d.value))
                .curve(curveMonotoneX)
            return lineGenerator(this.data)
        },
        areaPath() {
            if (!this.data || this.data.length === 0) return ''
            const areaGenerator = area()
                .x((_, i) => this.xScale(i))
                .y0(this.height - this.padding.bottom)
                .y1((d) => this.yScale(d.value))
                .curve(curveMonotoneX)
            return areaGenerator(this.data)
        },
    },
    methods: {
        adjustDimensions() {
            if (this.$el && this.$el.clientWidth) {
                this.width = this.$el.clientWidth
                this.height = this.$el.clientHeight
            }
        },
        handleMouseMove(event) {
            if (!this.data.length) return
            const x = event.offsetX
            const closestIndex = Math.round(
                (x - this.padding.left) /
                    ((this.width - this.padding.left - this.padding.right) / (this.data.length - 1))
            )

            if (closestIndex >= 0 && closestIndex < this.data.length) {
                this.activePoint = { ...this.data[closestIndex], index: closestIndex }
                this.tooltipX = this.xScale(closestIndex) + 10
                this.tooltipY = this.yScale(this.activePoint.value) - 20
            }
        },
        handleMouseLeave() {
            this.activePoint = null
        },
        formatTick(value) {
            if (value >= 1000) return `${(value / 1000).toFixed(0)}k`
            return value
        },
    },
    mounted() {
        this.adjustDimensions()
        window.addEventListener('resize', this.adjustDimensions)
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.adjustDimensions)
    },
}
</script>

<style scoped>
.line-path {
    stroke-dasharray: 1000;
    stroke-dashoffset: 1000;
    animation: draw-line 2s ease-out forwards;
}
@keyframes draw-line {
    to {
        stroke-dashoffset: 0;
    }
}
</style>
