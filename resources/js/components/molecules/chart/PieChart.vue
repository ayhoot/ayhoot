<template>
    <div
        class="w-full h-full flex flex-col items-center justify-center relative"
        @mousemove="handleMouseMove"
        @mouseleave="handleMouseLeave"
    >
        <svg :viewBox="`0 0 ${width} ${height}`" class="w-full h-full max-h-[250px]">
            <g :transform="`translate(${width / 2}, ${height / 2})`">
                <path
                    v-for="(slice, i) in slices"
                    :key="`slice-${i}`"
                    :d="slice.path"
                    :fill="slice.color"
                    @mouseover="activeSliceIndex = i"
                    class="transition-all duration-200"
                    :style="{ opacity: activeSliceIndex === null || activeSliceIndex === i ? 1 : 0.5 }"
                />

                <!-- Center Text -->
                <text text-anchor="middle" dominant-baseline="central" class="pointer-events-none">
                    <tspan v-if="activeSlice" x="0" dy="-0.5em" class="text-lg font-bold fill-current text-gray-800">
                        {{ activeSlice.name }}
                    </tspan>
                    <tspan
                        v-if="activeSlice"
                        x="0"
                        dy="1.2em"
                        class="text-2xl font-bold fill-current"
                        :style="{ fill: activeSlice.color }"
                    >
                        {{ activeSlice.percent.toFixed(1) }}%
                    </tspan>
                    <tspan
                        v-if="!activeSlice"
                        x="0"
                        dy="-0.5em"
                        class="text-md font-semibold fill-current text-gray-500"
                    >
                        Total Users
                    </tspan>
                    <tspan v-if="!activeSlice" x="0" dy="1.2em" class="text-lg font-bold fill-current text-gray-800">
                        {{ total.toLocaleString() }}
                    </tspan>
                </text>
            </g>
        </svg>

        <!-- Legend -->
        <div class="flex flex-wrap justify-center gap-x-4 gap-y-2 mt-4 px-2">
            <div
                v-for="(item, i) in data"
                :key="`legend-${i}`"
                @mouseover="activeSliceIndex = i"
                @mouseleave="activeSliceIndex = null"
                class="flex items-center gap-2 cursor-pointer"
            >
                <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: item.color }"></span>
                <span class="text-xs font-medium text-gray-600">{{ item.name }}</span>
            </div>
        </div>
    </div>
</template>

<script>
import { pie, arc } from 'd3-shape'

export default {
    name: 'PieChart',
    props: {
        data: { type: Array, required: true },
        innerRadius: { type: Number, default: 60 },
    },
    data() {
        return {
            width: 300,
            height: 250,
            activeSliceIndex: null,
        }
    },
    computed: {
        total() {
            return this.data.reduce((sum, item) => sum + item.value, 0)
        },
        activeSlice() {
            if (this.activeSliceIndex === null) return null
            const item = this.data[this.activeSliceIndex]
            return {
                ...item,
                percent: (item.value / this.total) * 100,
            }
        },
        slices() {
            if (!this.data.length || this.total === 0) return []

            const pieGenerator = pie()
                .value((d) => d.value)
                .sort(null)

            const arcGenerator = arc()
                .innerRadius(this.innerRadius)
                .outerRadius(Math.min(this.width, this.height) / 2 - 10)
                .padAngle(0.02)
                .cornerRadius(8)

            const pieData = pieGenerator(this.data)

            return pieData.map((p) => ({
                ...p,
                path: arcGenerator(p),
                color: p.data.color,
            }))
        },
    },
    methods: {
        adjustDimensions() {
            if (this.$el && this.$el.clientWidth) {
                this.width = this.$el.clientWidth
                this.height = Math.min(this.$el.clientHeight, this.width * 0.8)
            }
        },
        handleMouseMove() {
            /* Logic is handled by mouseover on slices */
        },
        handleMouseLeave() {
            this.activeSliceIndex = null
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
/* The path transform-origin is no longer needed as there's no transform */
</style>
