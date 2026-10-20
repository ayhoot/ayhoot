import { createRouter, createWebHistory } from 'vue-router';

// --- Route Module Imports ---
import { adminRoutes } from './routes/adminRoutes';
import { instructorRoutes } from './routes/instructorRoutes';
import { studentRoutes } from './routes/studentRoutes';

const teacherRoutes = instructorRoutes('teacher');
const instituteRoutes = instructorRoutes('institute');

// --- Error Page Imports (Using the new stylish ones) ---
import Error401 from './pages/errors/Error401.vue';
import Error403 from './pages/errors/Error403.vue';
import Error404 from './pages/errors/Error404.vue';
import Error500 from './pages/errors/Error500.vue';

// --- General Page Imports ---
const Home = () => import('./pages/Home.vue');
const AyhootForInstitutes = () => import('./pages/AyhootForInstitutes.vue');
const AyhootForTutors = () => import('./pages/AyhootForTutors.vue');
const AdminLogin = () => import('./panels/admin/Login.vue');
const Signup = () => import('./pages/Signup.vue');
const Login = () => import('./pages/Login.vue');
const Verify = () => import('./pages/Verify.vue');
const ResetPassword = () => import('./pages/ResetPassword.vue');
const ResetPasswordVerify = () => import('./pages/ResetPasswordVerify.vue');
const UpdateUser = () => import('./pages/UpdateUser.vue');
const Discover = () => import('./pages/discover/Discover.vue');
const NewsIndex = () => import('./pages/News.vue');
const NewsDetail = () => import('./pages/NewsDetail.vue');
const Profile = () => import('./pages/Profile.vue');
const PaymentStatus = () => import('./pages/PaymentStatus.vue');
const PaymentResult = () => import('./pages/PaymentResult.vue');
const CheckoutPage = () => import('./pages/CheckoutPage.vue');
const UniversalCheckoutPage = () => import('./pages/UniversalCheckoutPage.vue');
const PrivacyPolicy = () => import('./pages/PrivacyPolicy.vue');
const TermsOfService = () => import('./pages/TermsOfService.vue');

// --- Discover Page Imports ---
const Categories = () => import('./pages/discover/categories/Index.vue');
const AyhootsIndex = () => import('./pages/discover/Ayhoots.vue');
const CreatorsIndex = () => import('./pages/discover/Creators.vue');
const CoursesIndex = () => import('./pages/discover/RecommendedCourses.vue');
const CoursePreview = () => import('./pages/courses/Course.vue');
const AyhootPreview = () => import('./pages/ayhoots/Ayhoot.vue');
const RecommendedIndex = () => import('./pages/discover/Recommended.vue');
const InstitutesIndex = () => import('./pages/discover/Institutes.vue');


const routes = [
    // --- General Public Routes ---
    { path: '/', name: 'Home', component: Home },
    { path: '/ayhoot-for-institutes', name: 'AyhootForInstitutes', component: AyhootForInstitutes },
    { path: '/ayhoot-for-tutors', name: 'AyhootForTutors', component: AyhootForTutors },
    { path: '/admin/login', name: 'AdminLogin', component: AdminLogin },
    { path: '/signup', name: 'Signup', component: Signup },
    { path: '/verify', name: 'Verify', component: Verify },
    { path: '/reset-password', name: 'ResetPassword', component: ResetPassword },
    { path: '/reset-password/verify', name: 'ResetPasswordVerify', component: ResetPasswordVerify },
    { path: '/update-user', name: 'UpdateUser', component: UpdateUser },
    { path: '/login', name: 'Login', component: Login },
    { path: '/discover', name: 'Discover', component: Discover },
    { path: '/discover/categories/:subject', name: 'DiscoverCategory', component: Categories },
    { path: '/discover/ayhoots', name: 'DiscoverAyhoots', component: AyhootsIndex },
    { path: '/discover/courses', name: 'DiscoverCourses', component: CoursesIndex },
    { path: '/discover/course/:id/preview', name: 'CoursePreview', component: CoursePreview },
    { path: '/discover/ayhoot/:id/preview', name: 'AyhootPreview', component: AyhootPreview },
    { path: '/discover/creators', name: 'DiscoverCreators', component: CreatorsIndex },
    { path: '/discover/recommended', name: 'RecommendedIndex', component: RecommendedIndex },
    { path: '/discover/institutes', name: 'DiscoverInstitutes', component: InstitutesIndex },
    { path: '/news', name: 'News', component: NewsIndex },
    { path: '/news/:id', name: 'NewsDetail', component: NewsDetail },
    { path: '/teacher/profile/:id', name: 'TeacherProfile', component: Profile },
    { path: '/institute/profile/:id', name: 'InstituteProfile', component: Profile },
    { path: '/payment-status', name: 'PaymentStatus', component: PaymentStatus },
    { path: '/payment-result', name: 'PaymentResult', component: PaymentResult },
    { path: '/checkout/:type/:id', name: 'Checkout', component: CheckoutPage, meta: { requiresAuth: true } },
    { path: '/checkout', name: 'UniversalCheckout', component: UniversalCheckoutPage, meta: { requiresAuth: true } },
    { path: '/join-institute/:token', name: 'JoinInstitute', component: () => import('./panels/student/join/JoinInstitute.vue'), meta: { requiresAuth: true } },
    { path: '/privacy-policy', name: 'PrivacyPolicy', component: PrivacyPolicy },
    { path: '/privacy', redirect: '/privacy-policy' },
    { path: '/terms-of-service', name: 'TermsOfService', component: TermsOfService },
    { path: '/terms', redirect: '/terms-of-service' },

    // --- Specific Error Page Routes ---
    {
        path: '/unauthorized',
        name: 'Unauthorized',
        component: Error401
    },
    {
        path: '/forbidden',
        name: 'Forbidden',
        component: Error403
    },
    {
        path: '/server-error',
        name: 'ServerError',
        component: Error500
    },

    // --- Role-Specific Application Routes ---
    ...adminRoutes,
    ...teacherRoutes,
    ...studentRoutes,
    ...instituteRoutes,

    // --- Live Session Routes ---
    {
        path: '/hostlive/:pin',
        name: 'StudentJoinLive',
        component: () => import('./pages/StudentJoinLive.vue'),
        meta: { requiresAuth: true }
    },

    // --- Catch-All Route (MUST BE LAST) ---
    // This will handle any URL that doesn't match the routes above and show the 404 page.
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound', // Renamed for clarity
        component: Error404
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    // Persistent navigation loop detection (across reloads)
    const now = Date.now();
    let history = [];
    try {
        history = JSON.parse(sessionStorage.getItem('nav_history') || '[]');
    } catch (e) { history = []; }
    
    // Filter history to last 10 seconds
    history = history.filter(time => now - time < 10000);
    history.push(now);
    sessionStorage.setItem('nav_history', JSON.stringify(history));

    if (history.length > 10) {
        console.error('[Router] Rapid navigation loop detected (Across Reloads)! Halting.', { to: to.path, from: from.path });
        return next(false); // Halt navigation
    }

    const token = localStorage.getItem('token');
    const userRole = localStorage.getItem('role');

    // --- Auth & Role Checks ---
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    const isAdminRoute = to.path.startsWith('/admin') && to.name !== 'AdminLogin';
    // Public profile routes (/teacher/profile/:id, /institute/profile/:id) are viewable
    // by any role, so they are excluded from the panel role-gate below.
    const isTeacherRoute = to.path.startsWith('/teacher') && !to.path.startsWith('/teacher/profile/');
    const isStudentRoute = to.path.startsWith('/student') && !to.path.startsWith('/student/join-institute/');
    const isInstituteRoute = to.path.startsWith('/institute') && !to.path.startsWith('/institute/profile/');

    // 1. If route requires auth and no token, redirect to login
    if (requiresAuth && !token) {
        console.warn('[Router] Auth required but no token found. Redirecting to Login.');
        const loginPath = isAdminRoute ? '/admin/login' : '/login';
        return next({ path: loginPath, query: { redirect: to.fullPath } });
    }

    // 2a. Token present but role missing (stale/corrupt session): force re-auth
    //     instead of silently skipping all role gating below.
    if (requiresAuth && token && !userRole) {
        console.warn('[Router] Token present but no role in storage. Forcing re-login.');
        const loginPath = isAdminRoute ? '/admin/login' : '/login';
        return next({ path: loginPath, query: { redirect: to.fullPath } });
    }

    // 2. Role-based access control
    if (token && userRole) {
        // If logged in user tries to access a login page, redirect to their dashboard
        if (to.path === '/login' || to.path === '/admin/login') {
            console.log('[Router] User already logged in. Redirecting to dashboard.');
            return next(`/${userRole}/dashboard`);
        }

        // Prevent cross-role access (e.g., student trying to access /admin)
        if (isAdminRoute && userRole !== 'admin') {
            return next('/forbidden');
        }
        if (isTeacherRoute && userRole !== 'teacher' && userRole !== 'admin') {
            // Some teacher routes might be accessible by admin, but let's be strict for now
            return next('/forbidden');
        }
        if (isStudentRoute && userRole !== 'student') {
            return next('/forbidden');
        }
        if (isInstituteRoute && userRole !== 'institute' && userRole !== 'admin') {
            return next('/forbidden');
        }
    }

    return next();
});



let errorCount = 0;
router.onError((error) => {
    console.error('[Router] Error occurred during navigation:', error);
    errorCount++;
    
    // If we hit too many errors in a row, stop trying to redirect to avoid infinite loops
    if (errorCount > 3) {
        console.error('[Router] Too many errors, stopping redirection.');
        return;
    }

    const currentName = router.currentRoute.value.name;
    console.log('[Router] Current route name:', currentName, 'Error count:', errorCount);

    // Only redirect if we are NOT already on the error page to avoid loops
    if (currentName !== 'ServerError' && currentName !== 'NotFound') {
        router.push({ name: 'ServerError' }).catch(err => {
            console.error('[Router] Failed to push ServerError:', err);
        });
    }
});

router.afterEach((to) => {
    errorCount = 0; // Reset error count on successful navigation
    console.log('[Router] Navigation completed to:', to.path);
    if (to.meta.title) {
        document.title = `${to.meta.title} | Ayhoot`;
    }
});


export default router;