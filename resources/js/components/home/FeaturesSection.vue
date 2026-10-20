<template>
    <div class="bg-white py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:text-center">
                <h2 class="text-xl font-bold tracking-tight text-gray-900 sm:text-3xl">
                    {{ t('home.features.title') }}
                </h2>
            </div>
            <div class="mx-auto mt-10 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                <div class="grid grid-cols-1 cursor-pointer gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="card in featureCards"
                        :key="card.title"
                        class="flex flex-col p-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 ease-in-out hover:-translate-y-2"
                        :style="{ backgroundColor: card.bgColor }"
                    >
                        <div>
                            <h3 class="text-xl font-semibold" :style="{ color: card.titleColor }">
                                {{ card.title }}
                            </h3>
                            <p class="mt-2 text-base text-gray-800">
                                {{ card.description }}
                            </p>
                            <h3 class="sr-only">{{ card.seo }}</h3>
                        </div>

                        <router-link
                            :to="card.link"
                            class="mt-auto pt-4 inline-block font-semibold text-indigo-600 hover:text-indigo-800"
                        >
                            {{ card.cta }} &rarr;
                        </router-link>
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
const features_en = [
    {
        title: 'Online Exams',
        description: 'Show off the appeal of your quizzes and exams to everyone!',
        seo: 'Online Exam',
        cta: 'Create Ayhoot',
        link: '/login',
        bgColor: '#FFCCCC',
        titleColor: '#C0392B',
    },
    {
        title: 'Training Courses',
        description: 'You can create a course with diverse content and assignments for your audience!',
        seo: 'Training Course',
        cta: 'Create Course',
        link: '/login',
        bgColor: '#D2E3FF',
        titleColor: '#2980B9',
    },
    {
        title: 'Game-based Learning',
        description: 'You can make it even more engaging with leaderboards, scoring, and timers!',
        seo: 'Educational Gamification, Game-based Learning',
        cta: 'Start Ayhoot',
        link: '/login',
        bgColor: '#B9FFBC',
        titleColor: '#27AE60',
    },
    {
        title: 'Analytical Reports',
        description: 'You can identify your progress, strengths, and weaknesses.',
        seo: 'Analytical Report',
        cta: 'View Reports',
        link: '/login',
        bgColor: '#FFE8A3',
        titleColor: '#F39C12',
    },
]

// Persian data
const features_fa = [
    {
        title: 'آزمون‌های آنلاین',
        description: 'جذابیت کوییز و آزمون هاتو به رخ همه بکش!',
        seo: 'آزمون آنلاین',
        cta: 'ایجاد آیهوت',
        link: '/login',
        bgColor: '#FFCCCC',
        titleColor: '#C0392B',
    },
    {
        title: 'دوره‌های آموزشی',
        description: 'میتونی یه دوره با محتوا و تکالیف متنوع برای مخاطبان خودت بسازی!',
        seo: 'دوره آموزشی',
        cta: 'ساخت دوره',
        link: '/login',
        bgColor: '#D2E3FF',
        titleColor: '#2980B9',
    },
    {
        title: 'آموزش با بازی',
        description: 'با لیدربورد، امتیازبندی و زمان بندی، حتی میتونی جذاب ترش کنی!',
        seo: 'گیمیفیکیشن آموزشی،آموزش با بازی',
        cta: ' شروع آیهوت',
        link: '/login',
        bgColor: '#B9FFBC',
        titleColor: '#27AE60',
    },
    {
        title: 'گزارش تحلیلی',
        description: 'شما میتونی آمار پیشرفت، نقاط قوت و ضعف تو شناسایی کنی.',
        seo: 'گزارش تحلیلی',
        cta: 'مشاهده گزارش ها',
        link: '/login',
        bgColor: '#FFE8A3',
        titleColor: '#F39C12',
    },
]

// Computed property to select the correct language data
const featureCards = computed(() => {
    return locale.value === 'fa' ? features_fa : features_en
})
</script>
