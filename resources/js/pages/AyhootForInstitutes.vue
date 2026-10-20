<template>
    <PublicLayout footer>
        <!-- Hero Section -->
        <section class="bg-white py-16 sm:py-24 border-b border-gray-100">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                    <div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-6xl">
                            {{ content.heroTitle }}
                        </h1>
                        <p class="mt-6 text-lg leading-8 text-gray-600 text-justify">
                            {{ content.useCasesDescription }}
                        </p>
                        <div class="mt-10 flex items-center gap-x-6">
                            <ResponsiveButton type="light-blue" to="/signup" class="p-3 px-6">
                                {{ locale === 'fa' ? 'شروع رایگان' : 'Start for Free' }}
                            </ResponsiveButton>
                        </div>
                    </div>
                    <div class="relative">
                        <!-- We use :src here to prevent Vite from failing if the file is missing during build -->
                        <img
                            :src="heroImagePath"
                            alt="Institutes Platform"
                            class="w-full rounded-lg shadow-md ring-1 ring-gray-400/10"
                            @error="(e) => (e.target.src = 'https://placehold.co/600x400?text=Ayhoot+Institutes')"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Solutions Section -->
        <section class="py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl lg:text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        {{ content.solutionsTitle }}
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        {{
                            locale === 'fa'
                                ? 'مزایای استفاده از آیهوت در اکوسیستم آموزشی شما'
                                : 'Benefits of using Ayhoot in your educational ecosystem'
                        }}
                    </p>
                </div>

                <div
                    class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 sm:mt-20 lg:mt-24 lg:max-w-none lg:grid-cols-3"
                >
                    <div
                        v-for="solution in content.solutions"
                        :key="solution.title"
                        class="flex flex-col bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
                    >
                        <dt class="text-xl font-bold leading-7 text-indigo-600 mb-4">
                            {{ solution.title }}
                        </dt>
                        <dd class="flex flex-auto flex-col text-base leading-7 text-gray-600">
                            <p class="flex-auto text-justify">
                                {{ solution.description }}
                            </p>

                            <!-- Result Highlight -->
                            <div
                                v-if="solution.result"
                                class="mt-6 p-4 bg-indigo-50 rounded-lg border-indigo-500 result-box"
                            >
                                <p class="text-sm font-bold text-indigo-900">
                                    {{ locale === 'fa' ? 'نتیجه برای مؤسسه:' : 'Result for the Institute:' }}
                                </p>
                                <p class="mt-1 text-sm text-indigo-800 italic">
                                    {{ solution.result }}
                                </p>
                            </div>
                        </dd>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="bg-gray-900 py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        {{ locale === 'fa' ? 'طرح‌ها و قیمت‌گذاری' : 'Plans & Pricing' }}
                    </h2>
                </div>

                <div class="mx-auto mt-16 max-w-lg rounded-lg ring-1 ring-gray-200 bg-white p-8 sm:p-10 shadow-2xl">
                    <div class="flex items-center justify-between gap-x-4">
                        <h3 class="text-2xl font-bold leading-8 text-indigo-600">
                            {{ content.plan.name }}
                        </h3>
                        <span
                            class="rounded-full bg-indigo-600/10 px-2.5 py-1 text-xs font-semibold leading-5 text-indigo-600"
                        >
                            {{ content.plan.status }}
                        </span>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-gray-500">
                        {{ content.plan.deadline }}
                    </p>
                    <ul role="list" class="mt-8 space-y-3 text-sm leading-6 text-gray-600">
                        <li v-for="feature in content.plan.features" :key="feature" class="flex gap-x-3">
                            <svg class="h-6 w-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    fill-rule="evenodd"
                                    d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            {{ feature }}
                        </li>
                    </ul>
                    <ResponsiveButton to="/login" type="dark-blue" class="px-6 p-3 mt-8 w-full">
                        {{ locale === 'fa' ? 'انتخاب طرح' : 'Select Plan' }}
                    </ResponsiveButton>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import ResponsiveButton from '@/components/atoms/button/ResponsiveButton.vue'

const { locale } = useI18n()

// This fixes the Vite error.
// It treats the path as a string literal instead of a required module.
const heroImagePath = '/imgHolders/321.jpg'

const content_en = {
    heroTitle: 'Ayhoot for Institutes',
    useCasesDescription:
        'The Ayhoot platform allows educational institutions to manage their teaching, evaluation, and student assessment process online and seamlessly. From holding coordinated exams to designing exclusive question banks, everything is provided in a simple and secure environment.',
    solutionsTitle: 'Solutions',
    solutions: [
        {
            title: 'Assessment Beyond Grades',
            description:
                "Ayhoot measures learners' real skills and abilities, including analytical thinking, problem-solving, and decision-making speed, rather than just short-term memorization.",
            result: 'A clearer picture of learning quality and the true level of students.',
        },
        {
            title: 'Increased Engagement',
            description:
                'Using games and challenges, Ayhoot transforms learning into an active experience, increasing focus and reducing academic dropout.',
            result: 'More dynamic classes and higher satisfaction for students and parents.',
        },
        {
            title: 'Smart Performance Analytics',
            description:
                'Ayhoot provides detailed analytical reports at individual and group levels, identifying strengths, improvement areas, and progress trends.',
            result: 'Data-driven educational decision-making, not just guesswork.',
        },
        {
            title: 'Support for Educators',
            description:
                'By reducing manual grading time, Ayhoot helps teachers spend more time on purposeful teaching and effective interaction with students.',
            result: 'Improved teaching quality and staff productivity.',
        },
        {
            title: 'Talent Discovery',
            description:
                'Analyzing learner behavior in gamified environments allows for identifying interests and creating personalized learning paths.',
            result: 'Providing distinct educational services and real added value.',
        },
        {
            title: 'Scalability & Integration',
            description:
                'Ayhoot is usable in schools and institutions of various scales and can easily be integrated alongside existing systems.',
            result: 'Seamless growth and educational consistency.',
        },
    ],
    plan: {
        name: 'Institutes Ultimate',
        status: 'Limited Offer',
        deadline: 'Available until January 2, 2026',
        features: [
            '150 ayhoots',
            '200 Tutees per month',
            '10 Occupy Tutors',
            'Ultimate reports',
            'No limit on monetize',
            '100 Participant Limit on assignment',
        ],
    },
}

const content_fa = {
    heroTitle: 'آیهوت برای مؤسسات',
    useCasesDescription:
        'پلتفرم ایهوت به مؤسسات آموزشی این امکان را می‌دهد تا فرآیند آموزش، ارزیابی و سنجش دانش‌آموزان یا دانشجویان خود را به‌صورت آنلاین و یکپارچه مدیریت کنند. از برگزاری آزمون‌های هماهنگ گرفته تا طراحی بانک سوالات اختصاصی، همه در یک محیط ساده و امن فراهم شده است.',
    solutionsTitle: 'راه‌حل‌ها',
    solutions: [
        {
            title: ' ارزیابی دقیق و فراتر از نمره',
            description:
                'آی‌هوت امکان سنجش مهارت‌ها و توانمندی‌های واقعی فراگیران را فراهم می‌کند؛ شامل تفکر تحلیلی، حل مسئله، سرعت تصمیم‌گیری و میزان درک مفاهیم، نه صرفاً حفظیات و پاسخ‌های کوتاه‌مدت.',
            result: 'تصویری شفاف‌تر از کیفیت یادگیری و سطح واقعی دانش‌آموزان.',
        },
        {
            title: ' افزایش مشارکت و انگیزه‌ی فراگیران',
            description:
                'با استفاده از بازی، چالش و تعامل، آی‌هوت فرآیند یادگیری را به تجربه‌ای فعال تبدیل می‌کند. این رویکرد باعث افزایش تمرکز، کاهش افت تحصیلی و مشارکت بیشتر دانش‌آموزان می‌شود.',
            result: 'کلاس‌های پویا‌تر و رضایت بالاتر فراگیران و والدین.',
        },
        {
            title: ' گزارش‌دهی و تحلیل هوشمند عملکرد',
            description:
                'آی‌هوت گزارش‌های تحلیلی دقیق در سطح فردی و گروهی ارائه می‌دهد؛ شامل نقاط قوت، نقاط قابل بهبود و روند پیشرفت در طول زمان.',
            result: 'تصمیم‌گیری آموزشی مبتنی بر داده، نه حدس و تجربه.',
        },
        {
            title: ' پشتیبانی از معلمان و کادر آموزشی',
            description:
                'با کاهش زمان تصحیح و ارزیابی دستی، آی‌هوت به معلمان کمک می‌کند زمان بیشتری را به آموزش هدفمند و تعامل مؤثر با دانش‌آموزان اختصاص دهند.',
            result: 'افزایش کیفیت تدریس و بهره‌وری کادر آموزشی.',
        },
        {
            title: ' استعدادیابی و هدایت آموزشی',
            description:
                'تحلیل رفتار فراگیران در محیط‌های گیمیفای‌شده، امکان شناسایی استعدادها و علایق آموزشی را فراهم می‌کند و مسیر یادگیری شخصی‌سازی‌شده‌تری ایجاد می‌نماید.',
            result: 'ارائه خدمات آموزشی متمایز و ارزش‌افزوده واقعی.',
        },
        {
            title: ' مقیاس‌پذیری و یکپارچگی آسان',
            description:
                'آی‌هوت قابل استفاده در مدارس، آموزشگاه‌ها و مؤسسات آموزشی با مقیاس‌های مختلف است و می‌تواند به‌سادگی در کنار سیستم‌های آموزشی موجود مورد استفاده قرار گیرد.',
            result: 'تطبیق‌پذیری بالا با ساختارهای فعلی مؤسسه.',
        },
    ],
    plan: {
        name: 'موسسه ویژه (Ultimate Institutes)',
        status: 'پیشنهاد ویژه',
        deadline: 'در دسترس تا ۱۲ دی ۱۴۰۴',
        features: [
            '۱۵۰ آیهوت (Ayhoot)',
            '۲۰۰ دانش‌آموز (Tutee) در ماه',
            '۱۰ ظرفیت مدرس',
            'گزارش‌های پیشرفته (Ultimate)',
            'بدون محدودیت در درآمدزایی',
            '۱۰۰ شرکت‌کننده در هر تکلیف',
        ],
    },
}

const content = computed(() => {
    return locale.value === 'fa' ? content_fa : content_en
})
</script>

<style scoped>
/* RTL and LTR handling for the result highlight boxes */
[lang='fa'] .result-box {
    border-right-width: 4px;
    border-left-width: 0;
    text-align: right;
}
[lang='en'] .result-box {
    border-left-width: 4px;
    border-right-width: 0;
    text-align: left;
}
</style>
