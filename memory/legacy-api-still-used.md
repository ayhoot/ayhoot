---
name: legacy-api-still-used
description: Why the legacy /teacher and /institute API route groups can't be deleted yet
metadata:
  type: project
---

The Ayhoot backend was migrated to a unified `/instructor` API group (`role:teacher,institute`, controllers under `Api\Instructor\*`), but the legacy `/teacher` and `/institute` route groups in `routes/api.php` (~200 routes) **cannot be removed** because they are still called from outside the instructor panel:

- Student exam flows: `panels/student/exam/{Assignments,Edit,ExamForm,Play}.vue` call `/api/teacher/exam/*`.
- Public pages: `pages/ayhoots/Ayhoot.vue`, `pages/Profile.vue`, `components/discover/Index.vue` call `/api/teacher/*` and `/api/institute/*`.

These callers are students/anonymous users, so they can't use the role-gated `/instructor` group — finishing legacy removal requires migrating them to shared/public endpoints first (a separate effort).

**Already safely removed** (2026-06-16): the 4 unused controller classes `Api\{Teacher,Institute}\{Exam,Question}Controller` (routes used `Shared\ExamOwnerController`/`QuestionOwnerController` instead) plus their imports, and 2 broken routes calling non-existent `teacherProfile`/`instituteProfile`. See [[instructor-unification-state]].
