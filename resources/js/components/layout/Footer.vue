<template>
    <footer class="bg-white border-t border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <!-- Left section -->
                <div class="flex items-center gap-4">
                    <span class="text-blue-600 font-semibold">Ayhoot</span>
                    <span class="text-gray-400">|</span>
                    <span class="text-sm text-gray-600">© {{ currentYear }} All rights reserved</span>
                </div>

                <!-- Middle section -->
                <div class="flex flex-wrap justify-center gap-6">
                    <a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Privacy Policy</a>
                    <a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Terms of Service</a>
                    <a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Contact Support</a>
                </div>

                <!-- Right section -->
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">Version 1.0.0</span>
                    <div class="flex gap-3">
                        <a
                            v-for="(social, index) in socials"
                            :key="index"
                            :href="social.link"
                            class="text-gray-400 hover:text-blue-600 transition-colors"
                        >
                            <component :is="social.icon" class="w-5 h-5" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<script>
export default {
    name: 'Footer',
    data() {
        return {
            socials: [
                {
                    name: 'Facebook',
                    link: '#',
                    icon: 'FacebookIcon',
                },
                {
                    name: 'Twitter',
                    link: '#',
                    icon: 'TwitterIcon',
                },
                {
                    name: 'Instagram',
                    link: '#',
                    icon: 'InstagramIcon',
                },
            ],
        }
    },
    computed: {
        currentYear() {
            return new Date().getFullYear()
        },
    },
}
</script>

<style scoped></style>
