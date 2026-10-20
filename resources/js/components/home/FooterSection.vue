<template>
    <footer class="bg-[#C8C8C8]">
        <div class="mx-auto max-w-7xl overflow-hidden px-6 py-16 sm:py-24 lg:px-8">
            <!-- Updated to grid-cols-4 for the new column -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center md:text-left footer-grid">
                <!-- Standard Columns -->
                <div v-for="column in footerColumns" :key="column.title">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900">{{ column.title }}</h3>
                    <ul role="list" class="mt-6 space-y-4">
                        <li v-for="item in column.links" :key="item.text">
                            <router-link :to="item.path" class="text-base leading-6 text-gray-700 hover:text-gray-900">
                                {{ item.text }}
                            </router-link>
                        </li>
                    </ul>
                </div>

                <!-- New Column: Certificates / eNAMAD -->
                <div class="flex flex-col items-center md:items-start">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900">
                        {{ locale === 'fa' ? 'مجوزها' : 'Certificates' }}
                    </h3>
                    <div class="mt-6 bg-white p-2 rounded-lg inline-block">
                        <a referrerpolicy="origin" target="_blank"
                            href="https://trustseal.enamad.ir/?id=5418221&Code=XzfsotR7l89r0aSP3z6YmMeYbHGksKPH">
                            <img referrerpolicy="origin"
                                src="https://trustseal.enamad.ir/logo.aspx?id=5418221&Code=XzfsotR7l89r0aSP3z6YmMeYbHGksKPH"
                                alt="eNAMAD Trust Seal" style="cursor: pointer" class="h-24 w-24 object-contain"
                                code="XzfsotR7l89r0aSP3z6YmMeYbHGksKPH" />
                        </a>
                    </div>
                </div>
            </div>

            <p class="mt-10 text-center text-xs leading-5 text-gray-600">
                &copy; {{ new Date().getFullYear() }} Ayhoot. All rights reserved.
            </p>
        </div>
    </footer>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()

// English data
const footerColumns_en = [
    {
        title: 'Users',
        links: [
            { text: 'Institutes', path: '/institute/dashboard' },
            { text: 'Tutors', path: '/teacher/dashboard' },
            { text: 'Tutees', path: '/student/dashboard' },
        ],
    },
    {
        title: 'Ayhoot',
        links: [
            { text: 'About Us', path: '/' },
            { text: 'Careers', path: '/' },
            { text: 'News', path: '/news' },
        ],
    },
    {
        title: 'Legal',
        links: [
            { text: 'Privacy Policy', path: '/privacy-policy' },
            { text: 'Terms of Service', path: '/terms-of-service' },
        ],
    },
]

// Persian data
const footerColumns_fa = [
    {
        title: 'کاربران',
        links: [
            { text: 'موسسات', path: '/institute/dashboard' },
            { text: 'اساتید', path: '/teacher/dashboard' },
            { text: 'دانش آموزان', path: '/student/dashboard' },
        ],
    },
    {
        title: 'شرکت',
        links: [
            { text: 'درباره ما', path: '/' },
            { text: 'فرصت‌های شغلی', path: '/' },
            { text: 'اخبار', path: '/news' },
        ],
    },
    {
        title: 'حقوقی',
        links: [
            { text: 'سیاست حفظ حریم خصوصی', path: '/privacy-policy' },
            { text: 'شرایط خدمات', path: '/terms-of-service' },
        ],
    },
]

const footerColumns = computed(() => {
    return locale.value === 'fa' ? footerColumns_fa : footerColumns_en
})
</script>

<style scoped>
/* Support for RTL layout in Persian */
[lang='fa'] .footer-grid {
    text-align: right;
    direction: rtl;
}

/* Ensure the logo looks clean on the gray background */
img {
    mix-blend-mode: multiply;
}
</style>
