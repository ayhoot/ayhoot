import StudentDashboard from '../panels/student/Dashboard.vue';
import ExamIndex from '../panels/student/exam/Index.vue';
import ExamPlay from '../panels/student/exam/Play.vue';
import ExamDetail from '../panels/student/exam/Detail.vue';
import ExamEdit from '../panels/student/exam/Edit.vue';
import ExamAssignments from '../panels/student/exam/Assignments.vue';
import ExamPurchased from '../panels/student/exam/Purchased.vue';

import CourseCourse from '../panels/student/course/Course.vue';
import MyCourses from '../panels/student/course/MyCourses.vue';

import TicketIndex from '../panels/student/ticket/Index.vue';
import TicketShow from '../panels/student/ticket/Show.vue';
import TicketTicketForm from '../panels/student/ticket/TicketForm.vue';

import ProfileMyProfile from '../panels/student/profile/MyProfile.vue';
import ProfileProfile from '../panels/student/profile/Profile.vue';

import WalletIndex from '../panels/student/wallet/Index.vue';
import NotificationIndex from '../panels/student/notification/Index.vue';

import PurchaseBuyExam from '../panels/student/purchase/BuyExam.vue';
import PurchaseBuyCourse from '../panels/student/purchase/BuyCourse.vue';

import ReportIndex from '../panels/student/report/Index.vue';
import ReportAssignedAyhoot from '../panels/student/report/AssignedAyhoot.vue';

import JoinGroup from '../panels/student/join/JoinGroup.vue';
import JoinInstitute from '../panels/student/join/JoinInstitute.vue';
import JoinTutor from '../panels/student/join/JoinTutor.vue';

// Discover components are now shared in components/discover/
import DiscoverIndex from '../components/discover/Index.vue';
import DiscoverCategories from '../components/discover/Categories.vue';
import DiscoverAyhoots from '../components/discover/Ayhoots.vue';
import DiscoverCourses from '../components/discover/RecommendedCourses.vue';
import DiscoverRecommended from '../components/discover/Recommended.vue';
import DiscoverCreators from '../components/discover/Creators.vue';
import DiscoverInstitutes from '../components/discover/Institutes.vue';
import DiscoverLabeled from '../components/discover/Labeled.vue';

export const studentRoutes = [
    { path: '/student/dashboard', component: StudentDashboard, meta: { requiresAuth: true } },
    { path: '/student/discover', component: DiscoverIndex, meta: { requiresAuth: true } },
    { path: '/student/discover/categories/:subject', component: DiscoverCategories, meta: { requiresAuth: true } },
    { path: '/student/discover/ayhoots', component: DiscoverAyhoots, meta: { requiresAuth: true } },
    { path: '/student/discover/courses', component: DiscoverCourses, meta: { requiresAuth: true } },
    { path: '/student/discover/recommended', component: DiscoverRecommended, meta: { requiresAuth: true } },
    { path: '/student/discover/creators', component: DiscoverCreators, meta: { requiresAuth: true } },
    { path: '/student/discover/institutes', component: DiscoverInstitutes, meta: { requiresAuth: true } },
    { path: '/student/discover/labeled/:id', component: DiscoverLabeled, meta: { requiresAuth: true } },

    { path: '/student/exam', component: ExamIndex, meta: { requiresAuth: true } },
    { path: '/student/exam/:id/play', component: ExamPlay, meta: { requiresAuth: true } },
    { path: '/student/exam/:id/game/:gameId/play', component: ExamPlay, meta: { requiresAuth: true } },
    { path: '/student/exam/:id/detail', component: ExamDetail, meta: { requiresAuth: true } },
    // "ayhoot" aliases for the exam detail/preview links used across discover/cards.
    { path: '/student/ayhoot/:id', component: ExamDetail, meta: { requiresAuth: true } },
    { path: '/student/ayhoot/:id/preview', component: ExamDetail, meta: { requiresAuth: true } },
    { path: '/student/exam/:id/edit', component: ExamEdit, meta: { requiresAuth: true } },
    { path: '/student/exam/assignments', component: ExamAssignments, meta: { requiresAuth: true } },
    { path: '/student/exam/purchased', component: ExamPurchased, meta: { requiresAuth: true } },

    { path: '/student/courses', component: MyCourses, meta: { requiresAuth: true } },
    { path: '/student/course/:id', component: CourseCourse, meta: { requiresAuth: true } },

    { path: '/student/ticket', component: TicketIndex, meta: { requiresAuth: true } },
    { path: '/student/ticket/:id', component: TicketShow, meta: { requiresAuth: true } },
    { path: '/student/ticket/create', component: TicketTicketForm, meta: { requiresAuth: true } },

    { path: '/student/my-profile', component: ProfileMyProfile, meta: { requiresAuth: true } },
    { path: '/student/profile/:id', component: ProfileProfile, meta: { requiresAuth: true } },

    { path: '/student/wallet', component: WalletIndex, meta: { requiresAuth: true } },
    { path: '/student/notification', component: NotificationIndex, meta: { requiresAuth: true } },

    { path: '/student/exam/:id/purchase', component: PurchaseBuyExam, meta: { requiresAuth: true } },
    { path: '/student/course/:id/purchase', component: PurchaseBuyCourse, meta: { requiresAuth: true } },

    { path: '/student/report', component: ReportIndex, meta: { requiresAuth: true } },
    { path: '/student/report/assigned-ayhoots/:id', component: ReportAssignedAyhoot, meta: { requiresAuth: true } },

    { path: '/student/join-group/:token', component: JoinGroup, meta: { requiresAuth: true } },
    { path: '/student/join-institute/:token', component: JoinInstitute, meta: { requiresAuth: true } },
    { path: '/student/join-tutor/:token', component: JoinTutor, meta: { requiresAuth: true } },
];
