import AdminDashboard from '../panels/admin/Dashboard.vue';
import AdminStudent from '../panels/admin/student/Index.vue';

import AdminTeacher from '../panels/admin/teacher/Index.vue';
import AdminTeacherCreate from '../panels/admin/teacher/Create.vue';
import AdminTeacherEdit from '../panels/admin/teacher/Edit.vue';
import AdminInstitute from '../panels/admin/institute/Index.vue';
import AdminInstituteCreate from '../panels/admin/institute/Create.vue';
import AdminInstituteEdit from '../panels/admin/institute/Edit.vue';
import AdminPlan from '../panels/admin/plan/Index.vue';
import AdminPlanCreate from '../panels/admin/plan/Create.vue';
import AdminPlanEdit from '../panels/admin/plan/Edit.vue';
import AdminSetting from '../panels/admin/setting/Index.vue';
import AdminExam from '../panels/admin/exam/Index.vue';
import CourseIndex from '../panels/admin/exam/course/Index.vue';
import AdminExamCreate from '../panels/admin/exam/Create.vue';
import StudentProfile from '../panels/admin/profile/StudentProfile.vue';
import TeacherProfile from '../panels/admin/profile/TeacherProfile.vue';
import InstituteProfile from '../panels/admin/profile/InstituteProfile.vue';
import AdminExamEdit from '../panels/admin/exam/Edit.vue';
import AdminExamQuestionCreate from '../panels/admin/exam/question/Create.vue';
import AdminExamQuestion from '../panels/admin/exam/question/Index.vue';
import AyhootPreview from '../panels/admin/exam/Ayhoot.vue';
import AdminTicket from '../panels/admin/ticket/Index.vue';
import AdminTicketShow from '../panels/admin/ticket/Show.vue';
import CoursePreview from '../panels/admin/courses/Course.vue';
import TransactionIndex from '../panels/admin/transaction/Index.vue';
import NotificationIndex from '../panels/admin/notification/Index.vue';
import NewsIndex from '../panels/admin/news/Index.vue';
import NewsCreate from '../panels/admin/news/Create.vue';
import NewsEdit from '../panels/admin/news/Edit.vue';
import AdminBanner from '../panels/admin/banner/Index.vue';
import AdminHeroSlide from '../panels/admin/hero/Index.vue';

import AdminActivity from '../panels/admin/Activity.vue';

export const adminRoutes = [
    { path: '/admin/dashboard', component: AdminDashboard, meta: { requiresAuth: true } },
    { path: '/admin/student', component: AdminStudent, meta: { requiresAuth: true } },
    { path: '/admin/student/profile/:id', component: StudentProfile, meta: { requiresAuth: true } },
    { path: '/admin/teacher/profile/:id', component: TeacherProfile, meta: { requiresAuth: true } },
    { path: '/admin/teacher', component: AdminTeacher, meta: { requiresAuth: true } },
    { path: '/admin/teacher/:id/edit', component: AdminTeacherEdit, meta: { requiresAuth: true } },
    { path: '/admin/teacher/create', component: AdminTeacherCreate, meta: { requiresAuth: true } },
    { path: '/admin/institute', component: AdminInstitute, meta: { requiresAuth: true } },
    { path: '/admin/institute/profile/:id', component: InstituteProfile, meta: { requiresAuth: true } },
    { path: '/admin/institute/create', component: AdminInstituteCreate, meta: { requiresAuth: true } },
    { path: '/admin/institute/:id/edit', component: AdminInstituteEdit, meta: { requiresAuth: true } },
    { path: '/admin/plan', component: AdminPlan, meta: { requiresAuth: true } },
    { path: '/admin/plan/create', component: AdminPlanCreate, meta: { requiresAuth: true } },
    { path: '/admin/plan/:id/edit', component: AdminPlanEdit, meta: { requiresAuth: true } },
    { path: '/admin/setting', component: AdminSetting, meta: { requiresAuth: true } },
    { path: '/admin/exam', component: AdminExam, meta: { requiresAuth: true } },
    { path: '/admin/exam/:id/edit', component: AdminExamEdit, meta: { requiresAuth: true } },
    { path: '/admin/exam/create', component: AdminExamCreate, meta: { requiresAuth: true } },
    { path: '/admin/exam/:id/questions', component: AdminExamQuestion, meta: { requiresAuth: true } },
    { path: '/admin/exam/:id/create', component: AdminExamQuestionCreate, meta: { requiresAuth: true } },
    { path: '/admin/exam/:id/question/:question/edit', component: AdminExamQuestionCreate, meta: { requiresAuth: true } },
    { path: '/admin/course', component: CourseIndex, meta: { requiresAuth: true } },
    { path: '/admin/exam/:id', component: AyhootPreview, meta: { requiresAuth: true } },
    { path: '/admin/course/:id', component: CoursePreview, meta: { requiresAuth: true } },
    { path: '/admin/transaction', component: TransactionIndex, meta: { requiresAuth: true } },
    { path: '/admin/notification', component: NotificationIndex, meta: { requiresAuth: true } },
    { path: '/admin/activity', component: AdminActivity, meta: { requiresAuth: true } },
    { path: '/admin/ticket', component: AdminTicket, meta: { requiresAuth: true } },
    { path: '/admin/ticket/:id', component: AdminTicketShow, meta: { requiresAuth: true } },
    { path: '/admin/news', component: NewsIndex, meta: { requiresAuth: true } },
    { path: '/admin/news/create', component: NewsCreate, meta: { requiresAuth: true } },
    { path: '/admin/news/edit/:id', component: NewsEdit, meta: { requiresAuth: true } },
    { path: '/admin/banner', component: AdminBanner, meta: { requiresAuth: true } },
    { path: '/admin/hero-slides', component: AdminHeroSlide, meta: { requiresAuth: true } }
];
