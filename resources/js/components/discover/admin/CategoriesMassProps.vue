<!-- CORRECTED: src/views/discover/layout/CategoriesMassProps.vue -->
<template>
    <div class="">
        <div class="flex items-center justify-between mb-2 pr-4">
            <h3 class="font-bold text-gray-700 mb-2">All {{ category }} Ayhoots!</h3>
        </div>
        <div class="pr-4">
            <div class="grid grid-cols-2 md:grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <RouterLink
                    v-for="ayhoot in ayhoots"
                    :key="ayhoot.id"
                    :to="`/teacher/ayhoot/${ayhoot.id}`"
                    class="group bg-gradient-to-b from-[#5A89DA] to-[#4C1D92] rounded-md shadow-sm w-full transition-transform transform hover:scale-101 hover:shadow-md duration-100"
                >
                    <div class="rounded-t-md text-xs py-1 px-2 flex justify-between items-center">
                        <div class="flex gap-1 items-center text-white">
                            <svg
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M15.5993 12.8093L15.0538 11.9713L15.0398 11.9803L15.0262 11.9899L15.5993 12.8093ZM15.5993 11.4332L15.0064 12.2384L15.051 12.2712L15.099 12.299L15.5993 11.4332ZM10.0839 7.37187L10.6769 6.56662L10.6487 6.54585L10.6191 6.52709L10.0839 7.37187ZM8.92766 7.97847L9.92765 7.97557L9.92763 7.96682L9.92745 7.95807L8.92766 7.97847ZM8.95078 15.9562L7.95079 15.9591L7.95087 15.9874L7.95255 16.0156L8.95078 15.9562ZM10.1494 16.621L10.6337 17.4959L10.6796 17.4705L10.7226 17.4405L10.1494 16.621ZM5.9999 3.3999H17.9999V1.3999H5.9999V3.3999ZM20.5999 5.9999V17.9999H22.5999V5.9999H20.5999ZM17.9999 20.5999H5.9999V22.5999H17.9999V20.5999ZM3.3999 17.9999V5.9999H1.3999V17.9999H3.3999ZM5.9999 20.5999C4.56396 20.5999 3.3999 19.4358 3.3999 17.9999H1.3999C1.3999 20.5404 3.45939 22.5999 5.9999 22.5999V20.5999ZM20.5999 17.9999C20.5999 19.4358 19.4358 20.5999 17.9999 20.5999V22.5999C20.5404 22.5999 22.5999 20.5404 22.5999 17.9999H20.5999ZM17.9999 3.3999C19.4358 3.3999 20.5999 4.56396 20.5999 5.9999H22.5999C22.5999 3.45939 20.5404 1.3999 17.9999 1.3999V3.3999ZM5.9999 1.3999C3.45939 1.3999 1.3999 3.45939 1.3999 5.9999H3.3999C3.3999 4.56396 4.56396 3.3999 5.9999 3.3999V1.3999ZM16.1449 13.6474C16.5793 13.3646 17.1284 12.8602 17.1274 12.1C17.1263 11.3173 16.5491 10.827 16.0997 10.5673L15.099 12.299C15.2036 12.3595 15.2229 12.3909 15.2083 12.3723C15.1867 12.3448 15.1276 12.2501 15.1274 12.1027C15.1272 11.9591 15.1833 11.8745 15.192 11.8627C15.1963 11.8569 15.1655 11.8985 15.0538 11.9713L16.1449 13.6474ZM16.1923 10.6279L10.6769 6.56662L9.49098 8.17711L15.0064 12.2384L16.1923 10.6279ZM10.6191 6.52709C10.1484 6.22898 9.48756 6.05818 8.86255 6.35001C8.1823 6.66763 7.91466 7.35185 7.92786 7.99888L9.92745 7.95807C9.92625 7.89951 9.93844 7.91844 9.90768 7.97728C9.87303 8.04359 9.80534 8.11708 9.70869 8.1622C9.52273 8.24903 9.4477 8.1526 9.5488 8.21664L10.6191 6.52709ZM7.92766 7.98137L7.95079 15.9591L9.95078 15.9533L9.92765 7.97557L7.92766 7.98137ZM7.95255 16.0156C7.98436 16.5499 8.17011 17.2622 8.84054 17.6203C9.50063 17.9728 10.1879 17.7426 10.6337 17.4959L9.66523 15.746C9.56611 15.8009 9.52715 15.804 9.5428 15.8018C9.56646 15.7985 9.66412 15.7928 9.78273 15.8561C9.90301 15.9204 9.95507 16.0077 9.96769 16.0346C9.976 16.0523 9.95623 16.018 9.94901 15.8968L7.95255 16.0156ZM10.7226 17.4405L16.1725 13.6288L15.0262 11.9899L9.57631 15.8015L10.7226 17.4405Z"
                                    fill="white"
                                />
                            </svg>
                            <span class="text-xs font-medium text-white">{{ ayhoot.plays_count }} plays</span>
                        </div>
                        <div v-if="ayhoot.price" class="text-[0.6rem] text-white bg-white/20 rounded-md px-2 py-0.5">
                            {{ ayhoot.price }} R
                        </div>
                        <div v-else class="text-[0.6rem] text-white bg-white/20 rounded-md px-2 py-0.5">Free</div>
                    </div>
                    <div class="relative">
                        <!-- MODIFIED LINE: Use the new method to format the image src -->
                        <img :src="formatImageUrl(ayhoot.image)" :alt="ayhoot.title" class="w-full h-30 object-cover" />
                        <span
                            class="absolute opacity-0 group-hover:opacity-100 transition-opacity duration-300 bottom-2 left-2 text-xs text-white bg-white/20 p-1 rounded-md"
                            >{{ ayhoot?.question_count }} questions</span
                        >
                    </div>
                    <div class="p-3 w-full flex flex-col justify-between">
                        <h4
                            class="font-semibold text-white truncate sm:max-w-[16ch] md:max-w-[14ch] lg:max-w-[16ch] overflow-hidden text-ellipsis"
                        >
                            {{ ayhoot.title }}
                        </h4>
                        <p
                            class="text-sm text-gray-100 truncate sm:max-w-[20ch] md:max-w-[16ch] lg:max-w-[22ch] overflow-hidden text-ellipsis"
                        >
                            {{ ayhoot.description }}
                        </p>
                        <div class="mt-2 flex items-center justify-start gap-2">
                            <div class="">
                                <div class="text-xs text-white">@{{ ayhoot.teacher?.username }}</div>
                            </div>
                        </div>
                    </div>
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<script>
import { RouterLink } from 'vue-router'
import AyLinedButton from '@/components/atoms/button/AyLinedButton.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'

export default {
    name: 'CategoriesMassProps',
    components: {
        AyLinedButton,
        ResponsiveButton,
        RouterLink,
    },
    props: {
        ayhoots: {
            type: Array,
            required: true,
        },
        category: {
            type: String,
            required: true,
        },
    },
    methods: {
        // NEW METHOD: Formats the image URL
        formatImageUrl(relativePath) {
            if (!relativePath) {
                // Return a placeholder if the image path is missing
                return '/imgHolders/CardHolderImage.jpg'
            }
            // Construct the full URL, assuming Laravel's public storage link
            return `/uploads/${relativePath}`
        },
    },
}
</script>

<style scoped>
/* Smooth scrolling */
.scroll-smooth {
    scroll-behavior: smooth;
}
</style>
