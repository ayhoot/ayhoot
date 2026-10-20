<template>
    <div class="bg-white py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    {{ t('home.play.title') }}
                </h2>
            </div>

            <!-- First Part -->
            <div class="mt-16 grid grid-cols-1 gap-y-12 lg:grid-cols-2 lg:gap-x-16 lg:items-center">
                <div>
                    <!-- I'll add the same styling here for consistency -->
                    <img class="w-full rounded-xl" :src="institutesKidsImgPath" alt="Kids in an institute" />
                </div>
                <div>
                    <p class="text-lg leading-8 text-gray-700 text-justify">
                        {{ t('home.play.paragraph1') }}
                    </p>
                    <p class="sr-only">{{ t('home.play.seo1') }}</p>
                </div>
            </div>

            <!-- Blue Cards -->
            <div class="mt-12 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="card in blueCards"
                    :key="card.title"
                    class="p-6 rounded-xl bg-[#82BBFF] shadow-md hover:shadow-lg transition-all duration-300 ease-in-out hover:-translate-y-2"
                >
                    <h3 class="text-lg font-semibold text-blue-900">{{ card.title }}</h3>
                    <p class="mt-1 text-gray-800">{{ card.description }}</p>
                    <p class="sr-only">{{ card.seo }}</p>
                </div>
            </div>

            <!-- Second Part -->
            <div class="mt-24 grid grid-cols-1 gap-y-12 lg:grid-cols-2 lg:gap-x-16 lg:items-center">
                <div class="lg:order-last">
                    <img class="w-full max-w-sm mx-auto" :src="teacherKidImgPath" alt="Teacher with a kid" />
                </div>
                <div>
                    <p class="text-lg leading-8 text-gray-700 text-justify">
                        {{ t('home.play.paragraph2') }}
                    </p>
                    <p class="sr-only">{{ t('home.play.seo2') }}</p>
                </div>
            </div>

            <!-- Orange Cards -->
            <div class="mt-12 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="card in orangeCards"
                    :key="card.title"
                    class="p-6 rounded-xl bg-[#FB9C04] shadow-md hover:shadow-lg transition-all duration-300 ease-in-out hover:-translate-y-2"
                >
                    <h3 class="text-lg font-semibold text-orange-900">{{ card.title }}</h3>
                    <p class="mt-1 text-gray-800">{{ card.description }}</p>
                    <p class="sr-only">{{ card.seo }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// The <script> block remains exactly the same
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

const institutesKidsImgPath = '/imgHolders/institutes-kids.jpg'
const teacherKidImgPath = '/imgHolders/teacher-kid.png'

const blueCards_en = [
    {
        title: 'Live Sessions',
        seo: 'Live class, online teaching, online exam, Ayhoot',
        description: 'Play your games instantly in class, either in groups or individually!',
    },
    {
        title: 'Team Mode',
        seo: 'Group learning, team challenge, collaborative learning, online exam, Ayhoot',
        description: 'Foster team spirit with group challenges.',
    },
    {
        title: 'Practice at Home',
        seo: 'Online practice, self-paced learning, virtual assignment, online exam, Ayhoot',
        description: 'Assign self-paced games for practice at home.',
    },
    {
        title: 'Reports',
        seo: 'Learning report, learning analytics, student progress, online exam, Ayhoot',
        description: 'Analyze student performance to identify their strengths and weaknesses.',
    },
]
const blueCards_fa = [
    {
        title: 'جلسات زنده',
        seo: 'کلاس زنده، تدریس آنلاین، آزمون آنلاین، آیهوت ',
        description: 'بازی ها تون رو همون لحظه توی کلاس بازی کنید، چه گروهی، چه انفرادی!',
    },
    {
        title: 'حالت تیمی',
        seo: 'یادگیری گروهی، چالش تیمی، همکاری آموزشی، آزمون آنلاین، آیهوت',
        description: 'با چالش‌های گروهی، روحیه‌ی همکاری را تقویت کنید.',
    },
    {
        title: 'تمرین در خانه',
        seo: 'تمرین آنلاین، یادگیری خودمحور، تکلیف مجازی، آزمون آنلاین، آیهوت',
        description: 'بازی ها خودمحور را برای تمرین در خانه اختصاص دهید.',
    },
    {
        title: 'گزارش‌ها',
        seo: 'گزارش آموزشی، تحلیل یادگیری، پیشرفت دانشجو، آزمون آنلاین، آیهوت',
        description: 'عملکرد دانش‌آموزان را تحلیل کنید تا نقاط ضعف و قوت آنان را بشناسید.',
    },
]
const orangeCards_en = [
    {
        title: 'Self-paced Learning',
        seo: 'Self-paced learning, online exam, Ayhoot',
        description: 'Students learn at a pace that is right for them.',
    },
    {
        title: 'Endless Creativity',
        seo: 'Course content, educational content, online class, virtual school',
        description: 'Design unique content that reflects your teaching style.',
    },
    {
        title: 'Student Progress',
        seo: 'Individual progress, personal development, academic progress, online class',
        description: 'Easily track milestones and achievements.',
    },
    {
        title: 'Direct Feedback',
        seo: 'Performance report, academic report, report card, virtual school',
        description: 'Communicate effectively with students and provide guidance.',
    },
]
const orangeCards_fa = [
    {
        title: 'آموزش براساس سرعت یادگیری',
        seo: 'سرعت یادگیری، آزمون آنلاین، آیهوت',
        description: 'دانش‌آموزان با سرعتی که برایشان مناسب است یاد می‌گیرند.',
    },
    {
        title: ' خلاقیت بی نهایت',
        seo: 'محتوای درسی، محتوای آموزشی، کلاس آنلاین، مدرسه مجازی',
        description: 'محتوای منحصربه‌فرد که سبک تدریس شما را منعکس می‌کند، طراحی کنید.',
    },
    {
        title: 'پیشرفت دانش‌آموز',
        seo: 'پیشرفت فردی، توسعه فردی، پیشرفت تحصیلی، کلاس آنلاین',
        description: 'نقاط عطف و دستاوردها را به راحتی پیگیری کنید.',
    },
    {
        title: 'بازخورد مستقیم',
        seo: 'گزارش عملکرد، گزارش تحصیلی، کارنامه، مدرسه مجازی',
        description: 'با دانش‌آموزان به طور موثر ارتباط برقرار کرده و آنها را راهنمایی کنید.',
    },
]
const blueCards = computed(() => (locale.value === 'fa' ? blueCards_fa : blueCards_en))
const orangeCards = computed(() => (locale.value === 'fa' ? orangeCards_fa : orangeCards_en))
</script>
