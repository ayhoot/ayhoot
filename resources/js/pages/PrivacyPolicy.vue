<template>
    <PublicLayout footer>
        <div class="py-12 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto" :dir="isRtl ? 'rtl' : 'ltr'">
            <!-- Hero Header -->
            <div class="text-center py-8 sm:py-12 border-b border-gray-200 mb-10">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 mb-4">
                    {{ isRtl ? 'حفظ حریم خصوصی و امنیت داده‌ها' : 'Privacy & Data Protection' }}
                </span>
                <h1 class="text-3xl sm:text-5xl font-extrabold text-gray-900 tracking-tight">
                    {{ isRtl ? 'سیاست حفظ حریم خصوصی آی‌هوت' : 'Ayhoot Privacy Policy' }}
                </h1>
                <p class="mt-4 text-base sm:text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    {{ isRtl
                        ? 'در آی‌هوت امنیت و حریم خصوصی دانش‌آموزان، اساتید و موسسات اولویت اصلی ماست. در این سند نحوه جمع‌آوری، استفاده و حفاظت از اطلاعات شما شفاف‌سازی شده است.'
                        : 'At Ayhoot, safeguarding the privacy of students, educators, and institutions is our top priority. This policy outlines how your information is collected, used, and protected.'
                    }}
                </p>
                <div class="mt-4 text-xs font-medium text-gray-400">
                    {{ isRtl ? 'آخرین به‌روزرسانی: مهر ۱۴۰۵' : 'Last Updated: October 2026' }}
                </div>
            </div>

            <!-- Content Sections -->
            <div class="space-y-8 text-gray-700 leading-relaxed">
                <section
                    v-for="(section, idx) in sections"
                    :key="idx"
                    class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200"
                >
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 font-bold text-sm">
                            {{ idx + 1 }}
                        </div>
                        <div class="flex-1">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">
                                {{ section.title }}
                            </h2>
                            <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed whitespace-pre-line">
                                {{ section.description }}
                            </p>
                            <ul v-if="section.points && section.points.length" class="space-y-2.5">
                                <li
                                    v-for="(point, pIdx) in section.points"
                                    :key="pIdx"
                                    class="flex items-start gap-2.5 text-sm sm:text-base text-gray-600"
                                >
                                    <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>{{ point }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Contact Box -->
            <div class="mt-12 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 sm:p-8 text-white text-center shadow-lg">
                <h3 class="text-xl sm:text-2xl font-bold mb-2">
                    {{ isRtl ? 'سوالی درباره حریم خصوصی دارید؟' : 'Have Questions About Your Privacy?' }}
                </h3>
                <p class="text-blue-100 text-sm sm:text-base max-w-2xl mx-auto mb-6">
                    {{ isRtl
                        ? 'تیم پشتیبانی آی‌هوت همواره آماده پاسخگویی به ابهامات و بررسی درخواست‌های شماست.'
                        : 'Our dedicated support team is always ready to assist with any questions or privacy-related requests.'
                    }}
                </p>
                <router-link
                    to="/"
                    class="inline-flex items-center justify-center px-6 py-2.5 rounded-lg bg-white text-blue-700 font-bold text-sm hover:bg-blue-50 transition-colors shadow-sm"
                >
                    {{ isRtl ? 'بازگشت به صفحه اصلی' : 'Back to Home' }}
                </router-link>
            </div>
        </div>
    </PublicLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import PublicLayout from '@/components/layout/PublicLayout.vue'

const { locale } = useI18n()
const isRtl = computed(() => locale.value === 'fa')

const sections_en = [
    {
        title: '1. Information We Collect',
        description: 'To provide an engaging, gamified educational experience, Ayhoot collects necessary information when you interact with our platform:',
        points: [
            'Account Information: Name, email address, username, phone number, and account role (Student, Educator, Institute).',
            'Educational & Activity Data: Quizzes created, exam answers, response times, scores, assignment completions, and reports.',
            'Institute Affiliations: Connections between students, instructors, and educational centers.',
            'Transaction Information: Subscription plans and paid content purchase records (financial payment card details are processed securely by payment gateways and are never stored on our servers).',
            'Technical & Usage Data: IP address, browser type, device information, and session cookies for authentication security.',
        ],
    },
    {
        title: '2. How We Use Your Information',
        description: 'We process your data strictly to deliver and enhance our educational services:',
        points: [
            'Hosting live interactive sessions, multiplayer quiz battles, and assignments.',
            'Generating detailed performance metrics and progress charts for learners and educators.',
            'Enabling teachers and institutions to assess student proficiency and manage groups.',
            'Preventing academic dishonesty, cheating, and unauthorized account access.',
            'Sending essential service notifications, updates, and account security alerts.',
        ],
    },
    {
        title: '3. Data Sharing & Disclosure',
        description: 'Ayhoot respects your academic privacy. We do not sell or rent personal information to advertisers or third parties.',
        points: [
            'Instructors and Institutes: Teachers and school administrators have access to scores and analytics of students enrolled in their assigned exams and groups.',
            'Public & Community Features: In live quiz competitions or public leaderboards, your chosen nickname or display name and points may be displayed to participants.',
            'Legal Requirements: We may disclose data if required by applicable laws or to protect the safety and rights of our users.',
        ],
    },
    {
        title: '4. Student & Minor Protection',
        description: 'Ayhoot is designed as a safe, constructive digital learning environment for learners of all ages.',
        points: [
            'Student accounts in institutional groups are managed under educational supervision.',
            'We do not employ targeted advertising or profiling on student accounts.',
            'Personal contact details of students are kept private and never exposed publicly on the platform.',
        ],
    },
    {
        title: '5. Data Security & Storage',
        description: 'We implement industry-standard technical and organizational security measures:',
        points: [
            'Encrypted communications via HTTPS/SSL across all web and mobile interactions.',
            'Hashed password storage and tokenized authentication sessions.',
            'Regular database backups and access-restricted cloud infrastructure.',
        ],
    },
    {
        title: '6. Your Rights & Control',
        description: 'You have full autonomy over your personal information on Ayhoot:',
        points: [
            'Access and update your profile details and preferences at any time in your dashboard.',
            'Request deletion of your account and associated personal data by contacting support.',
            'Opt-out of non-essential email notifications while retaining critical system notices.',
        ],
    },
]

const sections_fa = [
    {
        title: '۱. اطلاعاتی که جمع‌آوری می‌کنیم',
        description: 'برای ارائه یک بستر یادگیری تعاملی و بازی‌وار (گیمیفیکیشن)، آی‌هوت اطلاعات زیر را هنگام استفاده از خدمات جمع‌آوری می‌نماید:',
        points: [
            'اطلاعات حساب کاربری: نام، نشانی ایمیل، نام کاربری، شماره تماس و نقش کاربری (دانش‌آموز، استاد، موسسه).',
            'اطلاعات آموزشی و عملکردی: آزمون‌های ساخته‌شده، پاسخ‌های ثبت‌شده، زمان پاسخگویی، نمرات، نتایج تکالیف و کارنامه‌ها.',
            'ارتباطات آموزشی: عضویت دانش‌آموزان در گروه‌ها و موسسات آموزشی.',
            'اطلاعات تراکنش‌ها: سابقه فعال‌سازی اشتراک‌ها و آزمون‌های خریداری‌شده (اطلاعات حساس بانکی مستقیماً در درگاه شاپرک پردازش شده و در آی‌هوت ذخیره نمی‌شوند).',
            'اطلاعات فنی: آدرس IP، نوع دستگاه، مرورگر و نشست‌های ورود امن جهت تضمین امنیت حساب.',
        ],
    },
    {
        title: '۲. نحوه استفاده از اطلاعات',
        description: 'اطلاعات جمع‌آوری‌شده صرفاً در راستای بهبود تجربه آموزشی و ارائه خدمات به کار گرفته می‌شوند:',
        points: [
            'برگزاری آزمون‌های زنده گروهی، تکالیف زمان‌دار و دوره‌های آموزشی.',
            'ایجاد گزارش‌های تحلیلی، نمودارهای پیشرفت و کارنامه‌های دقیق برای اساتید و دانش‌آموزان.',
            'امکان نظارت علمی معلمان و موسسات بر روند یادگیری فراگیران.',
            'شناسایی و جلوگیری از تخلفات، تقلب یا ورود غیرمجاز به حساب‌های کاربری.',
            'ارسال اطلاعیه‌های آموزشی مهم، وضعیت اشتراک و پیام‌های پشتیبانی.',
        ],
    },
    {
        title: '۳. اشتراک‌گذاری اطلاعات با دیگران',
        description: 'آی‌هوت حریم خصوصی شما را محترم می‌شمارد و هرگز اطلاعات کاربران را به اشخاص ثالث یا شرکت‌های تبلیغاتی واگذار نمی‌کند.',
        points: [
            'اساتید و مدیران موسسات: اساتید به نمرات و وضعیت تکالیف دانش‌آموزانِ عضو در دوره‌ها و آزمون‌های خود دسترسی دارند.',
            'تابلوی امتیازات و بازی‌های جمعی: در رقابت‌های زنده یا جدول رده‌بندی عمومی، نام نمایشی و امتیاز به شرکت‌کنندگان نمایش داده می‌شود.',
            'مراجع قانونی: در صورت لزوم و در چارچوب قوانین موضوعه کشور، همکاری‌های قانونی صورت می‌پذیرد.',
        ],
    },
    {
        title: '۴. حفاظت ویژه از حریم خصوصی دانش‌آموزان',
        description: 'آی‌هوت محیطی امن، سالم و متناسب با رشد تحصیلی دانش‌آموزان فراهم می‌آورد:',
        points: [
            'دانش‌آموزان در گروه‌های کلاسی تحت نظارت مستقیم اساتید و مدارس فعالیت می‌کنند.',
            'هیچ‌گونه تبلیغات تجاری یا ردیابی تبلیغاتی برای حساب‌های دانش‌آموزی اعمال نمی‌شود.',
            'اطلاعات تماس شخصی دانش‌آموزان در پروفایل عمومی نمایش داده نمی‌شود.',
        ],
    },
    {
        title: '۵. امنیت و نگهداری داده‌ها',
        description: 'ما از به‌روزترین پروتکل‌های امنیتی برای محافظت از اطلاعات کاربران استفاده می‌کنیم:',
        points: [
            'رمزنگاری تبادل داده‌ها با پروتکل امن HTTPS/SSL در کلیه بخش‌های سامانه.',
            'رمزنگاری قوی رمزهای عبور و احراز هویت توکنی امن.',
            'پشتیبان‌گیری منظم از پایگاه داده و کنترل دقیق سطوح دسترسی.',
        ],
    },
    {
        title: '۶. حقوق و اختیارات کاربر',
        description: 'شما کنترل کاملی بر اطلاعات خود در آی‌هوت دارید:',
        points: [
            'امکان ویرایش اطلاعات پروفایل و رمز عبور در هر زمان از طریق بخش تنظیمات کاربری.',
            'امکان ثبت درخواست حذف حساب کاربری و سوابق از طریق تیکت پشتیبانی.',
            'مدیریت دریافت پیام‌ها و اطلاعیه‌های سامانه.',
        ],
    },
]

const sections = computed(() => (isRtl.value ? sections_fa : sections_en))
</script>
