<template>
    <div class="bg-[#D1F8CE] py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    {{ t('home.testimonials.title') }}
                </h2>
            </div>
            <div class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                <div
                    v-for="testimonial in testimonials"
                    :key="testimonial.name"
                    class="flex flex-col bg-white p-8 rounded-2xl shadow-md hover:shadow-lg transition-all duration-300 ease-in-out hover:-translate-y-2"
                >
                    <div class="flex-grow">
                        <p class="text-gray-700 italic">"{{ testimonial.text }}"</p>
                    </div>
                    <div class="mt-6 flex items-center gap-x-4">
                        <img
                            class="h-12 w-12 rounded-full bg-gray-50"
                            :src="testimonial.image"
                            :alt="testimonial.name"
                        />
                        <div>
                            <div class="font-semibold text-gray-900">{{ testimonial.name }}</div>
                            <div class="text-gray-600">{{ testimonial.role }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

// English data
const testimonials_en = [
    {
        name: 'Ali Ghaffari',
        role: 'Trainer',
        image: '/imgHolders/ali.jpg',
        text: 'Ayhoot has completely changed my classroom dynamics. My students are more engaged than ever, and their test scores have improved significantly!',
    },
    {
        name: 'Aygun Academy',
        role: 'Language Institute',
        image: '/imgHolders/aygun.jpg',
        text: 'We use Ayhoot for our employee onboarding. The gamification makes mandatory training fun and much more memorable. Highly recommended.',
    },
    {
        name: 'Nematollahi',
        role: 'Student',
        image: '/imgHolders/nematollahi.jpg',
        text: 'Studying used to be a chore, but playing Ayhoots with my friends to review for exams is something I actually look forward to. It works!',
    },
]

// Persian data
const testimonials_fa = [
    {
        name: 'علی غفاری',
        role: 'مربی زبان انگلیسی',
        image: '/imgHolders/ali.jpg',
        text: 'آی‌هوت پویایی کلاس من را کاملاً تغییر داده است. دانش‌آموزانم بیش از هر زمان دیگری درگیر هستند و نمرات آزمون آنها به طور قابل توجهی بهبود یافته است!',
    },
    {
        name: ' آکادمی آی گون',
        role: 'موسسه آموزشی',
        image: '/imgHolders/aygun.jpg',
        text: 'ما از آی‌هوت برای آموزش کارکنان جدید خود استفاده می‌کنیم. گیمیفیکیشن آموزش‌های اجباری را سرگرم‌کننده و بسیار به یاد ماندنی‌تر می‌کند. به شدت توصیه می‌شود.',
    },
    {
        name: 'نعمت الهی',
        role: 'دانشجو',
        image: '/imgHolders/nematollahi.jpg',
        text: 'درس خواندن قبلاً یک کار طاقت‌فرسا بود، اما بازی کردن آی‌هوت با دوستانم برای مرور امتحانات چیزی است که واقعاً منتظرش هستم. این روش کار می‌کند!',
    },
]

// Computed property to select the correct language data
const testimonials = computed(() => {
    return locale.value === 'fa' ? testimonials_fa : testimonials_en
})
</script>
