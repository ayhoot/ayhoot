<template>
    <PublicLayout footer>
        <!-- Hero Section -->
        <section class="bg-white py-16 sm:py-24 border-b border-gray-100">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                    <div :dir="locale === 'fa' ? 'rtl' : 'ltr'">
                        <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-6xl">
                            {{ content.heroTitle }}
                        </h1>
                        <p class="mt-6 text-lg leading-8 text-gray-600 text-justify">
                            {{ content.heroDescription }}
                        </p>
                        <div class="mt-10 flex items-center gap-x-6">
                            <ResponsiveButton type="light-blue" to="/signup" class="p-3 px-6">
                                {{ locale === 'fa' ? 'شروع به عنوان مدرس' : 'Start as a Tutor' }}
                            </ResponsiveButton>
                        </div>
                    </div>
                    <div class="relative">
                        <img
                            :src="heroImagePath"
                            alt="Tutor Platform"
                            class="w-full rounded-lg shadow-md ring-1 ring-gray-400/10"
                            @error="(e) => (e.target.src = 'https://placehold.co/600x400?text=Ayhoot+for+Tutors')"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 1: Teaching & Evaluation Tools -->
        <section class="py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl lg:text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        {{ content.toolsTitle }}
                    </h2>
                </div>

                <div
                    class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 sm:mt-20 lg:mt-24 lg:max-w-none lg:grid-cols-3"
                >
                    <div
                        v-for="item in content.tools"
                        :key="item.title"
                        class="flex flex-col bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
                    >
                        <dt class="text-xl font-bold leading-7 text-indigo-600 mb-4">
                            {{ item.title }}
                        </dt>
                        <dd class="flex flex-auto flex-col text-base leading-7 text-gray-600">
                            <p class="flex-auto text-justify">
                                {{ item.description }}
                            </p>
                            <div
                                v-if="item.highlight"
                                class="mt-6 p-4 bg-indigo-50 rounded-lg border-indigo-500 result-box"
                            >
                                <p class="text-sm font-bold text-indigo-900">
                                    {{ item.highlightLabel }}
                                </p>
                                <p class="mt-1 text-sm text-indigo-800 italic">
                                    {{ item.highlight }}
                                </p>
                            </div>
                        </dd>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 2: Monetization & Freelancing -->
        <section class="bg-indigo-600 py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="lg:text-center mb-16">
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        {{ content.monetizeTitle }}
                    </h2>
                </div>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="frelance in content.freelancing"
                        :key="frelance.title"
                        class="bg-white/10 backdrop-blur-md p-6 rounded-xl border border-white/20 hover:bg-white/20 transition-colors"
                    >
                        <h3 class="text-lg font-bold text-white mb-2">{{ frelance.title }}</h3>
                        <p class="text-indigo-100 text-sm leading-relaxed">{{ frelance.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="bg-gray-900 py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        {{ locale === 'fa' ? 'طرح مدرسین' : 'Tutor Plans' }}
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

// Use a variable to prevent Vite build errors if file is missing
const heroImagePath = '/imgHolders/654.jpg'

const content_en = {
    heroTitle: 'Ayhoot for Tutors',
    heroDescription:
        'Today’s teachers are not just content deliverers; they are designers of learning experiences, progress evaluators, and guides for student growth. Ayhoot provides the tools to make teaching more effective, precise, and even profitable.',
    toolsTitle: 'Teaching & Evaluation Tools',
    tools: [
        {
            title: 'Smart Tests & Challenges',
            description:
                'Design gamified tests and exercises without needing technical skills to see the real understanding of your students.',
            highlightLabel: 'Use Case:',
            highlight: 'In-person classes, online classes, periodic exams, and home practice.',
        },
        {
            title: 'Fast Evaluation',
            description: 'Ayhoot automates the assessment process and displays results in an easy-to-analyze format.',
            highlightLabel: 'Benefit:',
            highlight: 'Time-saving, reduced human error, and more focus on teaching.',
        },
        {
            title: 'Performance Analytics',
            description:
                'Identify strengths/weaknesses, track progress over time, and adjust teaching to the class level.',
            highlightLabel: 'Result:',
            highlight: 'Data-driven insights for every single student.',
        },
        {
            title: 'Increased Engagement',
            description: 'Use games, scoring, and healthy competition to boost student participation and motivation.',
            highlightLabel: 'Result:',
            highlight: 'More active classrooms and effective teacher-student connection.',
        },
        {
            title: 'Personalized Education',
            description:
                'Ayhoot helps teachers design customized learning paths for students with different abilities.',
            highlightLabel: 'Opportunity:',
            highlight: 'Tailored growth for every learner.',
        },
    ],
    monetizeTitle: 'Monetization & Freelance Opportunities',
    freelancing: [
        {
            title: 'Sell Content',
            description:
                'Design and sell your exams, challenges, and educational packages to other teachers or students.',
        },
        {
            title: 'Independent Teaching',
            description: 'Conduct online assessments outside of schools for private classes or multiple institutions.',
        },
        {
            title: 'Personal Branding',
            description: 'Differentiate your teaching style and be recognized as a creative, data-driven educator.',
        },
        {
            title: 'Institutional Collaboration',
            description:
                'High-quality content creators can collaborate with institutions on a project or contract basis.',
        },
    ],
    plan: {
        name: 'Tutor Pro',
        status: 'Most Popular',
        deadline: 'Available until January 2, 2026',
        features: [
            '50 active ayhoots',
            '100 Tutees per month',
            'Advanced analytical reports',
            'Content store access',
            'Personal branding tools',
            'Assignment participant limit: 50',
        ],
    },
}

const content_fa = {
    heroTitle: 'آیهوت برای معلم‌ها',
    heroDescription:
        'معلم‌های امروز فقط انتقال‌دهنده‌ی محتوا نیستند؛ آن‌ها طراح تجربه‌ی یادگیری، ارزیاب پیشرفت و راهنمای مسیر رشد دانش‌آموزان هستند. آی‌هوت ابزارهایی در اختیار معلم‌ها قرار می‌دهد تا آموزش مؤثرتر، دقیق‌تر و حتی درآمدزا داشته باشند.',
    toolsTitle: 'ابزارهای طراحی و ارزیابی',
    tools: [
        {
            title: 'طراحی آزمون و چالش‌های هوشمند',
            description:
                'معلم‌ها می‌توانند بدون نیاز به مهارت فنی، آزمون‌ها، تمرین‌ها و چالش‌های گیمیفای‌شده طراحی کنند که میزان درک واقعی دانش‌آموزان را نشان می‌دهد.',
            highlightLabel: 'کاربرد:',
            highlight: 'کلاس حضوری، آنلاین، آزمون‌های دوره‌ای و تمرین خانگی.',
        },
        {
            title: ' ارزیابی سریع و خودکار',
            description:
                'آی‌هوت فرآیند ارزیابی را به‌صورت خودکار انجام می‌دهد و نتایج را به شکل قابل تحلیل نمایش می‌دهد.',
            highlightLabel: 'مزیت برای معلم:',
            highlight: 'صرفه‌جویی در زمان، کاهش خطای انسانی و تمرکز بر آموزش.',
        },
        {
            title: ' تحلیل عملکرد و شناخت دقیق',
            description: 'شناسایی نقاط ضعف و قوت هر دانش‌آموز، بررسی روند پیشرفت و تنظیم سطح آموزش متناسب با کلاس.',
            highlightLabel: 'نتیجه:',
            highlight: 'شناخت دقیق از پتانسیل واقعی هر فراگیر.',
        },
        {
            title: ' افزایش تعامل و انگیزه',
            description:
                'استفاده از بازی، امتیازدهی و رقابت سالم باعث افزایش مشارکت دانش‌آموزان و کاهش بی‌انگیزگی می‌شود.',
            highlightLabel: 'نتیجه:',
            highlight: 'کلاس‌های فعال‌تر و ارتباط مؤثرتر بین معلم و دانش‌آموز.',
        },
        {
            title: ' شخصی‌سازی آموزش',
            description:
                'آی‌هوت به معلم کمک می‌کند برای دانش‌آموزان با سطح‌ها و توانمندی‌های متفاوت، مسیر یادگیری متناسب طراحی کند.',
            highlightLabel: 'فرصت:',
            highlight: 'عدالت آموزشی و رشد فردی هر دانش‌آموز.',
        },
    ],
    monetizeTitle: 'فرصت‌های درآمدزایی و فعالیت فریلنسری',
    freelancing: [
        {
            title: ' فروش آزمون‌ها و محتوا',
            description: 'طراحی و فروش آزمون‌ها، چالش‌ها و بسته‌های آموزشی به سایر معلم‌ها، مؤسسات یا دانش‌آموزان.',
        },
        {
            title: ' تدریس و ارزیابی مستقل',
            description: 'برگزاری آزمون و ارزیابی آنلاین برای کلاس‌های خصوصی و همکاری هم‌زمان با چند مؤسسه.',
        },
        {
            title: ' برندسازی شخصی',
            description: 'متمایز کردن سبک آموزشی و شناخته شدن به‌عنوان معلم خلاق و داده‌محور.',
        },
        {
            title: ' همکاری با مؤسسات',
            description: 'تولید محتوای باکیفیت و همکاری پروژه‌محور یا قراردادی با مؤسسات آموزشی بزرگ.',
        },
    ],
    plan: {
        name: 'معلم ویژه (Ultimate Tutors)',
        status: 'پیشنهاد ویژه',
        deadline: 'در دسترس تا ۱۲ دی ۱۴۰۴',
        features: [
            '۵۰ آیهوت فعال',
            '۱۰۰ دانش‌آموز در ماه',
            'گزارش‌های تحلیلی پیشرفته',
            'دسترسی به فروشگاه محتوا',
            'ابزارهای برندسازی شخصی',
            'ظرفیت ۵۰ شرکت‌کننده در تکالیف',
        ],
    },
}

const content = computed(() => {
    return locale.value === 'fa' ? content_fa : content_en
})
</script>

<style scoped>
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
