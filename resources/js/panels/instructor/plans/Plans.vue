<template>
    <!-- Main container: allows natural scrolling for better UX on smaller screens -->
    <div class="min-h-screen bg-gray-50 flex flex-col items-center relative overflow-x-hidden">
        <!-- Fixed Background Blobs -->
        <div
            class="fixed w-96 h-96 bg-indigo-100 rounded-full opacity-30 pointer-events-none"
            style="top: 10%; inset-inline-start: -10%; transform: rotate(25deg)"
        ></div>
        <div
            class="fixed w-96 h-96 bg-purple-100 rounded-lg opacity-20 pointer-events-none"
            style="bottom: 10%; inset-inline-end: -10%; transform: rotate(-15deg)"
        ></div>

        <!-- Sticky Navigation -->
        <LoginNavBar class="fixed top-0 left-0 right-0 z-50" />

        <!-- Main Content Area -->
        <main class="flex-1 w-full max-w-7xl px-6 pt-32 pb-16 z-10">
            <!-- Page Header -->
            <div class="text-center mb-12">
                <h1 class="text-3xl sm:text-5xl font-extrabold text-gray-900 tracking-tight">
                    {{ t('plans.teacherTitle') }}
                </h1>
                <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
                    {{ t('plans.teacherSubtitle') }}
                </p>
            </div>

            <!-- START: Skeleton Loader -->
            <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div
                    v-for="i in 4"
                    :key="i"
                    class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 animate-pulse"
                >
                    <div class="h-6 bg-gray-200 rounded w-1/2 mb-4"></div>
                    <div class="h-4 bg-gray-100 rounded w-full mb-6"></div>
                    <div class="h-10 bg-gray-200 rounded w-1/3 mb-8"></div>
                    <div class="space-y-3">
                        <div v-for="j in 5" :key="j" class="flex items-center gap-3">
                            <div class="w-5 h-5 bg-gray-200 rounded-full"></div>
                            <div class="h-3 bg-gray-100 rounded w-full"></div>
                        </div>
                    </div>
                    <div class="mt-10 h-12 bg-gray-200 rounded-xl"></div>
                </div>
            </div>
            <!-- END: Skeleton Loader -->

            <!-- START: Actual Plan Cards -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    :class="[
                        'relative flex flex-col p-8 rounded-2xl transition-all duration-300 transform hover:-translate-y-2',
                        plan.isPopular
                            ? 'bg-gradient-to-b from-purple-600 to-indigo-800 text-white shadow-2xl scale-105 z-20 ring-4 ring-purple-500 ring-opacity-50'
                            : 'bg-white text-gray-900 shadow-sm border border-gray-200 hover:shadow-xl',
                    ]"
                >
                    <!-- Popular Badge -->
                    <div
                        v-if="plan.isPopular"
                        class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-pink-500 to-purple-500 text-white px-4 py-1 rounded-full text-xs font-bold uppercase tracking-widest shadow-lg"
                    >
                        {{ t('plans.mostPopular') }}
                    </div>

                    <div class="flex-grow">
                        <h2 class="text-2xl font-bold">{{ formatPlanName(plan.name) }}</h2>
                        <p :class="['mt-2 text-sm', plan.isPopular ? 'text-purple-100' : 'text-gray-500']">
                            {{ formatPlanDescription(plan.description) }}
                        </p>

                        <div class="mt-8 flex items-baseline">
                            <span class="text-4xl font-extrabold tracking-tight">{{ formatPlanPrice(plan) }}</span>
                            <span
                                v-if="!plan.is_free"
                                :class="[
                                    'ms-1 text-sm font-semibold',
                                    plan.isPopular ? 'text-purple-200' : 'text-gray-500',
                                ]"
                            >
                                <template v-if="$i18n.locale === 'fa'">{{ t('plans.currency') }} / {{ t('plans.month') }}</template>
                                <template v-else>/{{ t('plans.month') }}</template>
                            </span>
                        </div>

                        <!-- Features List -->
                        <ul class="mt-8 space-y-4">
                            <li v-for="(feature, index) in plan.features" :key="index" class="flex items-start gap-3">
                                <svg
                                    class="flex-shrink-0 h-5 w-5 mt-0.5"
                                    :class="plan.isPopular ? 'text-purple-300' : 'text-indigo-600'"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2.5"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                <span class="text-sm font-medium">{{ formatFeature(feature, index, plan) }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Call to Action -->
                    <div class="mt-10">
                        <button
                            @click="selectPlan(plan)"
                            :disabled="isSubmitting"
                            :class="[
                                'w-full py-4 px-6 text-sm font-bold rounded-xl transition-all duration-300 focus:outline-none disabled:opacity-50',
                                plan.isPopular
                                    ? 'bg-white text-purple-700 hover:bg-purple-50 shadow-md'
                                    : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm',
                            ]"
                        >
                            {{ plan.is_free ? t('plans.ctaFree') : t('plans.ctaChoosePlan', { name: formatPlanName(plan.name) }) }}
                        </button>
                    </div>
                </div>
            </div>
            <!-- END: Actual Plan Cards -->
        </main>
    </div>
</template>

<script>
import LoginNavBar from '@/components/organisms/nav/LoginNavBar.vue'
import { getRole } from '@/utils/role'
import { apiCall } from "@/services/apiService"
import { useToast } from 'vue-toast-notification'
import { useI18n } from 'vue-i18n'

export default {
    name: 'TeacherPlansPage',
    components: { LoginNavBar },
    setup() {
        const { t, te, locale } = useI18n()
        const toast = useToast()
        return { t, te, locale, toast }
    },
    data() {
        return {
            plans: [],
            isLoading: true,
            isSubmitting: false,
        }
    },
    async mounted() {
        await this.fetchPlans()
    },
    methods: {
        async fetchPlans() {
            this.isLoading = true
            try {
                // Fetching teacher-specific plans
                const response = await apiCall('GET', `/api/instructor/plans`)
                this.plans = response.plans || []
            } catch (error) {
                this.toast.error(this.t('plans.alerts.loadFailed'))
            } finally {
                this.isLoading = false
            }
        },
        async selectPlan(plan) {
            if (!localStorage.getItem('token')) {
                this.toast.info(this.t('plans.alerts.loginRequired'))
                this.$router.push('/login')
                return
            }
            if (plan.is_free) {
                this.isSubmitting = true
                try {
                    const payload = { duration: 12, use_wallet: false, voucher_code: null }
                    const response = await apiCall('POST', `/api/instructor/plan/${plan.id}/select`, payload)
                    this.toast.success(response.message)
                    this.$router.push(response.redirect_url)
                } catch (error) {
                    this.toast.error(this.t('plans.alerts.subscribeFailed'))
                } finally {
                    this.isSubmitting = false
                }
            } else {
                this.$router.push(`/${getRole()}/plan/confirm/${plan.id}`)
            }
        },
        formatPlanName(name) {
            if (!name) return ''
            const key = `plans.names.${name}`
            return this.te(key) ? this.t(key) : name
        },
        formatPlanDescription(desc) {
            if (!desc) return ''
            const key = `plans.descriptions.${desc}`
            return this.te(key) ? this.t(key) : desc
        },
        formatPlanPrice(plan) {
            if (plan.is_free) {
                return this.t('plans.freePrice')
            }
            const amount = typeof plan.raw_price === 'number' ? plan.raw_price : parseFloat(String(plan.price || 0).replace(/,/g, ''))
            const numLocale = this.locale === 'fa' ? 'fa-IR' : 'en-US'
            return Math.round(amount).toLocaleString(numLocale)
        },
        formatFeature(feature, index, plan) {
            if (!feature) return ''
            const raw = plan?.raw_features?.[index]
            const role = raw?.role || null
            const val = raw?.value !== undefined ? raw.value : null
            const title = typeof feature === 'string' ? feature : feature.title || ''

            if (this.locale !== 'fa') {
                return title
            }

            const numLocale = 'fa-IR'

            // Unlimited patterns (9999)
            if (val >= 9999 || title.includes('9999')) {
                if (role === 'tutees_per_month' || /tutees/i.test(title)) return this.t('plans.features.unlimitedTutees')
                if (role === 'participant_limit' || /participant/i.test(title)) return this.t('plans.features.unlimitedParticipants')
                if (role === 'ayhoots' || /ayhoots/i.test(title)) return this.t('plans.features.unlimitedAyhoots')
                if (role === 'courses' || /courses/i.test(title)) return this.t('plans.features.unlimitedCourses')
                if (role === 'groups' || /groups/i.test(title)) return this.t('plans.features.unlimitedGroups')
                if (role === 'occupy_tutors' || /occupy tutors/i.test(title)) return this.t('plans.features.unlimitedTutors')
            }

            // Tutees per month
            const tuteesMatch = title.match(/^(\d+)\s+Tutees per month$/i)
            if (tuteesMatch || role === 'tutees_per_month') {
                const count = tuteesMatch ? parseInt(tuteesMatch[1]) : val
                return this.t('plans.features.tuteesCount', { count: Number(count).toLocaleString(numLocale) })
            }

            // Participant Limit
            const partMatch = title.match(/^(\d+)\s+Participant Limit$/i)
            if (partMatch || role === 'participant_limit') {
                const count = partMatch ? parseInt(partMatch[1]) : val
                return this.t('plans.features.participantLimit', { count: Number(count).toLocaleString(numLocale) })
            }

            // Ayhoots (Exams)
            const ayhootMatch = title.match(/^(\d+)\s+Ayhoots\s*\(Exams\)$/i)
            if (ayhootMatch || role === 'ayhoots') {
                const count = ayhootMatch ? parseInt(ayhootMatch[1]) : val
                return this.t('plans.features.ayhootsCount', { count: Number(count).toLocaleString(numLocale) })
            }

            // Courses
            const courseMatch = title.match(/^(\d+)\s+Courses$/i)
            if (courseMatch || role === 'courses') {
                const count = courseMatch ? parseInt(courseMatch[1]) : val
                return this.t('plans.features.coursesCount', { count: Number(count).toLocaleString(numLocale) })
            }

            // Groups
            const groupMatch = title.match(/^(\d+)\s+Groups$/i)
            if (groupMatch || role === 'groups') {
                const count = groupMatch ? parseInt(groupMatch[1]) : val
                return this.t('plans.features.groupsCount', { count: Number(count).toLocaleString(numLocale) })
            }

            // Apply to Institute
            const applyMatch = title.match(/^(\d+)\s+Apply to Institute$/i)
            if (applyMatch || role === 'apply_to_institute') {
                const count = applyMatch ? parseInt(applyMatch[1]) : val
                return this.t('plans.features.applyInstituteCount', { count: Number(count).toLocaleString(numLocale) })
            }

            // Occupy Tutors
            const tutorMatch = title.match(/^(\d+)\s+Occupy Tutors$/i)
            if (tutorMatch || role === 'occupy_tutors') {
                const count = tutorMatch ? parseInt(tutorMatch[1]) : val
                return this.t('plans.features.tutorsCount', { count: Number(count).toLocaleString(numLocale) })
            }

            // Reports
            if (/reports:\s*ultimate\s*pro/i.test(title)) return this.t('plans.features.reportsUltimatePro')
            if (/reports:\s*ultimate/i.test(title)) return this.t('plans.features.reportsUltimate')
            if (/reports:\s*advanced/i.test(title)) return this.t('plans.features.reportsAdvanced')
            if (/reports:\s*basic/i.test(title)) return this.t('plans.features.reportsBasic')
            if (/reports:\s*enterprise/i.test(title)) return this.t('plans.features.reportsEnterprise')

            // Monetize
            if (/monetize:\s*yes/i.test(title)) return this.t('plans.features.monetizeYes')
            if (/monetize:\s*no/i.test(title)) return this.t('plans.features.monetizeNo')

            // Direct key match
            if (this.te(`plans.features.${title}`)) {
                return this.t(`plans.features.${title}`)
            }

            return title
        },
    },
}
</script>

<style scoped>
/* RTL / LTR Support logic */
[lang='fa'] .tracking-tight {
    letter-spacing: 0;
}

.pointer-events-none {
    pointer-events: none;
}

/* Premium card hover effect */
.hover\:-translate-y-2:hover {
    transform: translateY(-0.5rem);
}
</style>
