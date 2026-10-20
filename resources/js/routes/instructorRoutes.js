import InstructorDashboard from '../panels/instructor/Dashboard.vue';
import HostLive from '../panels/instructor/HostLive.vue';
import PlansPlans from '../panels/instructor/plans/Plans.vue';
import ExamIndex from '../panels/instructor/exam/Index.vue';
import ExamArchive from '../panels/instructor/exam/Archive.vue';
import ExamDetail from '../panels/instructor/exam/Detail.vue';
import ExamEdit from '../panels/instructor/exam/Edit.vue';
import ExamQuestion from '../panels/instructor/exam/Question.vue';
import ExamQuestionCreate from '../panels/instructor/exam/QuestionCreate.vue';
import ExamQuestionCreateForm from '../panels/instructor/exam/QuestionCreateForm.vue';
import ExamPreview from '../panels/instructor/exam/Preview.vue';
import ExamPreviewFromDiscover from '../panels/instructor/exam/PreviewFromDiscover.vue';
import ExamCourses from '../panels/instructor/exam/Courses.vue';
import ExamPurchased from '../panels/instructor/exam/Purchased.vue';
import ExamFolder from '../panels/instructor/exam/Folder.vue';
import ExamCreate from '../panels/instructor/exam/Create.vue';
import ExamCourseCreate from '../panels/instructor/exam/CourseCreate.vue';
import ExamCourseCreateDetail from '../panels/instructor/exam/CourseCreateDetail.vue';

import CourseCourse from '../panels/instructor/course/Course.vue';
import CourseEdit from '../panels/instructor/course/Edit.vue';

import ReportIndex from '../panels/instructor/report/Index.vue';
import ReportAssignedAyhoot from '../panels/instructor/report/AssignedAyhoot.vue';
import LiveSessionReport from '../panels/instructor/report/LiveSessionReport.vue';

import StudentIndex from '../panels/instructor/student/Index.vue';
import StudentGroupPage from '../panels/instructor/student/GroupPage.vue';
import StudentAssignedGroups from '../panels/instructor/student/Assigned-groups.vue';
import StudentOwnedGroups from '../panels/instructor/student/Owned-groups.vue';

import TicketIndex from '../panels/instructor/ticket/Index.vue';
import TicketShow from '../panels/instructor/ticket/Show.vue';
import TicketTicketForm from '../panels/instructor/ticket/TicketForm.vue';

import ProfileMyProfile from '../panels/instructor/profile/MyProfile.vue';
import ProfileProfile from '../panels/instructor/profile/Profile.vue';
import ProfileStudentProfile from '../panels/instructor/profile/StudentProfile.vue';

import WalletIndex from '../panels/instructor/wallet/Index.vue';
import NotificationIndex from '../panels/instructor/notification/Index.vue';
import PlansConfirm from '../panels/instructor/plans/Confirm.vue';

import PurchaseBuyExam from '../panels/instructor/purchase/BuyExam.vue';
import PurchaseBuyCourse from '../panels/instructor/purchase/BuyCourse.vue';

// Discover components are now shared in components/discover/
import DiscoverIndex from '../components/discover/Index.vue';
import DiscoverAyhoots from '../components/discover/Ayhoots.vue';
import DiscoverRecommended from '../components/discover/Recommended.vue';
import DiscoverCreators from '../components/discover/Creators.vue';
import DiscoverInstitutes from '../components/discover/Institutes.vue';
import DiscoverCategories from '../components/discover/Categories.vue';
import DiscoverCourses from '../components/discover/RecommendedCourses.vue';

// Teacher-specific or Institute-specific pages
import TeacherIndex from '../panels/instructor/teacher/Index.vue';
import TeacherSelectInstitute from '../panels/instructor/SelectInstitute.vue';

export const instructorRoutes = (role) => [
    { path: `/${role}/dashboard`, component: InstructorDashboard, meta: { requiresAuth: true } },
    { path: `/${role}/host-live/:sessionId`, component: HostLive, meta: { requiresAuth: true } },
    { path: `/${role}/discover`, component: DiscoverIndex, meta: { requiresAuth: true } },
    { path: `/${role}/discover/ayhoots`, component: DiscoverAyhoots, meta: { requiresAuth: true } },
    { path: `/${role}/discover/recommended`, component: DiscoverRecommended, meta: { requiresAuth: true } },
    { path: `/${role}/discover/courses`, component: DiscoverCourses, meta: { requiresAuth: true } },
    { path: `/${role}/discover/creators`, component: DiscoverCreators, meta: { requiresAuth: true } },
    { path: `/${role}/discover/institutes`, component: DiscoverInstitutes, meta: { requiresAuth: true } },
    { path: `/${role}/discover/categories/:subject`, component: DiscoverCategories, meta: { requiresAuth: true } },
    
    { path: `/${role}/exam`, component: ExamIndex, meta: { requiresAuth: true, showScrollbar: true } },
    { path: `/${role}/exam/:id/edit`, component: ExamEdit, meta: { requiresAuth: true } },
    { path: `/${role}/exam/:id/detail`, component: ExamDetail, meta: { requiresAuth: true } },
    { path: `/${role}/exam/:id/questions`, component: ExamQuestion, meta: { requiresAuth: true } },
    { path: `/${role}/exam/:id/question/create`, component: ExamQuestionCreate, meta: { requiresAuth: true } },
    { path: `/${role}/exam/:id/create`, component: ExamQuestionCreateForm, meta: { requiresAuth: true } },
    { path: `/${role}/exam/:id/preview`, component: ExamPreview, meta: { requiresAuth: true } },
    { path: `/${role}/exam/:id/preview-discover`, component: ExamPreviewFromDiscover, meta: { requiresAuth: true } },
    // "ayhoot" is the product name for an exam; these aliases back the many
    // Clicking an exam card opens its DETAILS page; the explicit /preview route
    // (used by Preview/Play buttons) keeps the in-app preview/play experience.
    { path: `/${role}/ayhoot/:id`, component: ExamDetail, meta: { requiresAuth: true } },
    { path: `/${role}/ayhoot/:id/preview`, component: ExamPreviewFromDiscover, meta: { requiresAuth: true } },
    { path: `/${role}/exam/create`, component: ExamCreate, meta: { requiresAuth: true } },
    { path: `/${role}/exam/archive`, component: ExamArchive, meta: { requiresAuth: true } },
    { path: `/${role}/exam/courses`, component: ExamCourses, meta: { requiresAuth: true } },
    { path: `/${role}/exam/purchased`, component: ExamPurchased, meta: { requiresAuth: true } },
    { path: `/${role}/exam/folder/:id`, component: ExamFolder, meta: { requiresAuth: true } },
    
    { path: `/${role}/course/create`, component: ExamCourseCreate, meta: { requiresAuth: true } },
    { path: `/${role}/course/:id`, component: CourseCourse, meta: { requiresAuth: true } },
    { path: `/${role}/course/:id/edit`, component: CourseEdit, meta: { requiresAuth: true } },
    { path: `/${role}/course/:id/create`, component: ExamCourseCreateDetail, meta: { requiresAuth: true } },
    
    { path: `/${role}/my-profile`, component: ProfileMyProfile, meta: { requiresAuth: true } },
    { path: `/${role}/profile/:id`, component: ProfileProfile, meta: { requiresAuth: true } },
    { path: `/${role}/students/profile/:id`, component: ProfileStudentProfile, meta: { requiresAuth: true } },
    
    { path: `/${role}/report`, component: ReportIndex, meta: { requiresAuth: true } },
    { path: `/${role}/report/assigned-ayhoots/:id`, component: ReportAssignedAyhoot, meta: { requiresAuth: true } },
    { path: `/${role}/live-report/:sessionId`, component: LiveSessionReport, meta: { requiresAuth: true } },
    
    { path: `/${role}/students`, component: StudentIndex, meta: { requiresAuth: true } },
    { path: `/${role}/students/group/:id`, component: StudentGroupPage, meta: { requiresAuth: true } },
    { path: `/${role}/students/assigned-groups`, component: StudentAssignedGroups, meta: { requiresAuth: true } },
    { path: `/${role}/students/owned-groups`, component: StudentOwnedGroups, meta: { requiresAuth: true } },
    
    { path: `/${role}/ticket`, component: TicketIndex, meta: { requiresAuth: true } },
    { path: `/${role}/ticket/:id`, component: TicketShow, meta: { requiresAuth: true } },
    { path: `/${role}/ticket/create`, component: TicketTicketForm, meta: { requiresAuth: true } },
    
    { path: `/${role}/plans`, component: PlansPlans, meta: { requiresAuth: true } },
    { path: `/${role}/plan/confirm/:id`, component: PlansConfirm, meta: { requiresAuth: true } },
    
    { path: `/${role}/wallet`, component: WalletIndex, meta: { requiresAuth: true } },
    { path: `/${role}/notification`, component: NotificationIndex, meta: { requiresAuth: true } },
    
    { path: `/${role}/exam/:id/purchase`, component: PurchaseBuyExam, meta: { requiresAuth: true } },
    { path: `/${role}/course/:id/purchase`, component: PurchaseBuyCourse, meta: { requiresAuth: true } },

    // Role-specific additions
    ...(role === 'teacher' ? [
        { path: '/teacher/select-institute', component: TeacherSelectInstitute, meta: { requiresAuth: true } },
    ] : []),
    ...(role === 'institute' ? [
        { path: '/institute/teachers', component: TeacherIndex, meta: { requiresAuth: true } },
    ] : []),
];
