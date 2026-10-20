<template>
    <PublicLayout footer>
        <div class="py-12 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto" :dir="isRtl ? 'rtl' : 'ltr'">
            <!-- Hero Header -->
            <div class="text-center py-8 sm:py-12 border-b border-gray-200 mb-10">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 mb-4">
                    {{ isRtl ? 'قوانین و مقررات استفاده از سامانه' : 'Platform Rules & Agreements' }}
                </span>
                <h1 class="text-3xl sm:text-5xl font-extrabold text-gray-900 tracking-tight">
                    {{ isRtl ? 'شرایط و قوانین خدمات آی‌هوت' : 'Ayhoot Terms of Service' }}
                </h1>
                <p class="mt-4 text-base sm:text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    {{ isRtl
                        ? 'استفاده از سامانه بازی‌وار آموزشی آی‌هوت به معنای پذیرش کلیه شرایط و ضوابط مندرج در این سند است. لطفاً پیش از ادامه استفاده، این قوانین را با دقت مطالعه فرمایید.'
                        : 'By using the Ayhoot gamified learning platform, you agree to comply with and be bound by these terms. Please review them carefully.'
                    }}
                </p>
                <div class="mt-4 text-xs font-medium text-gray-400">
                    {{ isRtl ? 'آخرین بازنگری: مهر ۱۴۰۵' : 'Last Revised: October 2026' }}
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
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 font-bold text-sm">
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
                                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ point }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Quick Links Box -->
            <div class="mt-12 bg-gradient-to-r from-gray-900 to-blue-900 rounded-2xl p-6 sm:p-8 text-white text-center shadow-lg">
                <h3 class="text-xl sm:text-2xl font-bold mb-2">
                    {{ isRtl ? 'آماده شروع تجربه یادگیری بازی‌وار هستید؟' : 'Ready to Experience Gamified Learning?' }}
                </h3>
                <p class="text-gray-300 text-sm sm:text-base max-w-2xl mx-auto mb-6">
                    {{ isRtl
                        ? 'به جمع هزاران استاد، دانش‌آموز و مدرسه در سامانه آی‌هوت بپیوندید.'
                        : 'Join thousands of educators, learners, and schools on the Ayhoot platform.'
                    }}
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <router-link
                        to="/signup"
                        class="inline-flex items-center justify-center px-6 py-2.5 rounded-lg bg-yellow-400 text-gray-900 font-bold text-sm hover:bg-yellow-300 transition-colors shadow-sm"
                    >
                        {{ isRtl ? 'ثبت نام در آی‌هوت' : 'Sign Up for Ayhoot' }}
                    </router-link>
                    <router-link
                        to="/privacy-policy"
                        class="inline-flex items-center justify-center px-6 py-2.5 rounded-lg bg-white/10 text-white font-bold text-sm hover:bg-white/20 transition-colors border border-white/20"
                    >
                        {{ isRtl ? 'سیاست حفظ حریم خصوصی' : 'Privacy Policy' }}
                    </router-link>
                </div>
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
        title: '1. Acceptance of Terms & Eligibility',
        description: 'By accessing or using Ayhoot, you affirm that you have read, understood, and agreed to these Terms of Service. If you are under the legal age of majority in your jurisdiction, you must have the consent of a parent, legal guardian, or authorized educational institution.',
        points: [
            'These terms govern all user tiers: Learners (Students/Tutees), Educators (Teachers/Tutors), and Educational Institutions.',
            'Institutions creating accounts for groups or classrooms warrant that they possess necessary authority to administer member accounts.',
        ],
    },
    {
        title: '2. Account Registration & Security',
        description: 'You are responsible for maintaining the confidentiality of your account credentials and for all activities conducted under your login:',
        points: [
            'All registration information provided must be accurate, current, and complete.',
            'You may not share passwords or permit unauthorized persons to access your account.',
            'Notify Ayhoot support immediately upon detecting any suspected breach or unauthorized use.',
        ],
    },
    {
        title: '3. Platform Code of Conduct & Academic Integrity',
        description: 'Ayhoot is dedicated to constructive, gamified education. Users agree to abide by the following standards:',
        points: [
            'Fair Play: Automated answer bots, scripts, screen scrapers, or exploits that falsify scores or game timings are strictly prohibited.',
            'Respectful Content: Quizzes, question texts, multimedia, and commentary must not contain harmful, hateful, infringing, or inappropriate material.',
            'Session Etiquette: Participants in live quiz sessions must maintain respectful conduct towards peers and session hosts.',
        ],
    },
    {
        title: '4. Intellectual Property & User Content',
        description: 'We respect the intellectual labor of educators and content creators:',
        points: [
            'Creator Ownership: Instructors and institutions retain intellectual property rights to the exams, question banks, and course materials they author.',
            'Platform License: By creating public or assigned content, you grant Ayhoot a non-exclusive license to host, display, and deliver that content to authorized participants.',
            'Third-Party Rights: Users must not upload images, audio, or test materials that violate third-party trademarks or copyrights.',
        ],
    },
    {
        title: '5. Subscriptions, Payments & Digital Purchases',
        description: 'Ayhoot offers premium features, subscription plans, and paid assessment content:',
        points: [
            'Pricing and plan features are stated clearly before checkout.',
            'Digital subscriptions and exam access passes become effective immediately upon successful payment confirmation.',
            'Refund requests for digital services are evaluated in accordance with statutory consumer protections and platform service policies.',
        ],
    },
    {
        title: '6. Live Sessions & Host Authority',
        description: 'In live multiplayer quiz sessions, host instructors retain disciplinary and administrative authority:',
        points: [
            'Hosts may remove or mute participants who violate room conduct.',
            'Game pins and join links are intended for authorized participants of the designated educational group.',
        ],
    },
    {
        title: '7. Service Availability & Modifications',
        description: 'We endeavor to provide uninterrupted, high-performance platform uptime:',
        points: [
            'Ayhoot may periodically perform maintenance, feature upgrades, or security updates.',
            'We reserve the right to modify these terms with reasonable notice posted on the platform.',
        ],
    },
    {
        title: '8. Termination & Account Suspension',
        description: 'Ayhoot reserves the right to suspend or terminate accounts that engage in severe or repeated violations of these terms, academic fraud, or security threats.',
        points: [
            'Users may terminate their account at any time via support requests.',
            'Provisions concerning intellectual property and liability limitations survive termination.',
        ],
    },
]

const sections_fa = [
    {
        title: '۱. پذیرش شرایط و واجد شرایط بودن',
        description: 'استفاده از سامانه و اپلیکیشن آی‌هوت به منزله مطالعه دقیق و پذیرش بدون قید و شرط این شرایط و قوانین است. در صورتی که به سن قانونی نرسیده‌اید، استفاده شما باید با نظارت و موافقت والدین یا مدرسه صورت پذیرد.',
        points: [
            'این قوانین بر تمامی کاربران اعم از دانش‌آموزان، اساتید، معلمان و موسسات آموزشی حاکم است.',
            'مدیران موسسات و مدارسی که اقدام به ثبت‌نام گروهی فراگیران می‌نمایند، مسئولیت صحت اطلاعات و رضایت آموزشی را بر عهده دارند.',
        ],
    },
    {
        title: '۲. حساب کاربری و مسئولیت‌های امنیتی',
        description: 'حفظ امنیت و محرمانگی مشخصات ورود و حساب کاربری بر عهده شخص کاربر است:',
        points: [
            'اطلاعات وارد شده در فرآیند ثبت‌نام باید صحیح، کامل و معتبر باشد.',
            'واگذاری حساب کاربری به اشخاص ثالث یا اشتراک‌گذاری کلمات عبور مجاز نمی‌باشد.',
            'در صورت مشاهده هرگونه سوءاستفاده یا دسترسی غیرمجاز به حساب، کاربر موظف است مراتب را به پشتیبانی آی‌هوت گزارش نماید.',
        ],
    },
    {
        title: '۳. قوانین رفتاری و صداقت تحصیلی',
        description: 'آی‌هوت بستری برای رشد آموزشی و رقابت سالم است. رعایت اصول زیر الزامی است:',
        points: [
            'صداقت در رقابت: استفاده از ربات‌های خودکار، اسکریپت‌های پاسخگویی یا ابزارهای تقلب در آزمون‌ها اکیداً ممنوع بوده و منجر به مسدودسازی حساب خواهد شد.',
            'محتوای مناسب: طراحی سوالات، بارگذاری تصاویر و فایل‌های صوتی باید منطبق بر موازین علمی، اخلاقی و قوانین جاری کشور باشد.',
            'احترام در کلاس‌های زنده: شرکت‌کنندگان در رویدادها و اتاق‌های زنده آزمون موظف به رعایت احترام متقابل هستند.',
        ],
    },
    {
        title: '۴. مالکیت معنوی و محتوای تولیدی کاربران',
        description: 'آی‌هوت به حقوق پدیدآورندگان و اساتید احترام می‌گذارد:',
        points: [
            'مالکیت اساتید: معلمان و موسسات مالکیت مادی و معنوی محتوای آزمون‌ها و دوره‌های تولیدی خود را حفظ می‌کنند.',
            'مجوز بهره‌برداری: با انتشار محتوا در سامانه، به آی‌هوت اجازه نمایش و ارائه فنی آن به فراگیران مجاز داده می‌شود.',
            'رعایت حقوق مولفان: کاربران مجاز به بارگذاری کتب، سوالات یا آثار محافظت‌شده بدون اجازه صاحب اثر نمی‌باشیند.',
        ],
    },
    {
        title: '۵. اشتراک‌ها، پرداخت‌ها و محتوای پولی',
        description: 'آی‌هوت خدمات ویژه‌ای را در قالب پلن‌های اشتراک و آزمون‌های تخصصی ارائه می‌دهد:',
        points: [
            'هزینه پلن‌ها و محتوای آموزشی پیش از پرداخت به صورت شفاف و ریالی نمایش داده می‌شود.',
            'فعال‌سازی دسترسی‌ها و پلن‌ها بلافاصله پس از تایید تراکنش در درگاه بانکی انجام می‌پذیرد.',
            'شرایط مرجوعی یا بازگشت وجه تابع قوانین تجارت الکترونیک و سیاست‌های پشتیبانی محتوای دیجیتال آی‌هوت است.',
        ],
    },
    {
        title: '۶. جلسات زنده و اختیارات برگزارکننده',
        description: 'در رویدادها و کوئیزهای زنده، استاد یا موسسه میزبان اختیارات نظارتی دارد:',
        points: [
            'میزبان می‌تواند شرکت‌کنندگان متخلف را از جلسه زنده خارج نماید.',
            'پین‌کد (PIN) اختصاصی و لینک‌های ورود صرفاً برای فراگیران مدنظر رویداد است.',
        ],
    },
    {
        title: '۷. پایداری خدمات و تغییرات',
        description: 'تیم فنی آی‌هوت همواره برای ارائه خدماتی پایدار، پرسرعت و ایمن تلاش می‌نماید:',
        points: [
            'انجام به‌روزرسانی‌های سامانه‌ای ممکن است مستلزم اطلاع‌رسانی قبلی و نگهداری‌های کوتاه‌مدت باشد.',
            'آی‌هوت حق به‌روزرسانی و اصلاح این شرایط را در صورت نیاز با اعلام قبلی در سامانه محفوظ می‌دارد.',
        ],
    },
    {
        title: '۸. تعلیق یا مسدودسازی حساب',
        description: 'آی‌هوت در صورت نقض آشکار مقررات، رفتارهای مخل امنیت یا تقلب گسترده، مجاز به تعلیق موقت یا دائم حساب خاطی خواهد بود.',
        points: [
            'کاربران می‌توانند در هر زمان از طریق ارتباط با پشتیبانی، درخواست پایان فعالیت یا حذف حساب کاربری خود را ارائه نمایند.',
        ],
    },
]

const sections = computed(() => (isRtl.value ? sections_fa : sections_en))
</script>
