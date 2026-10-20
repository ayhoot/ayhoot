---
name: instructor-unification-state
description: State of the teacher+institute -> instructor unification (frontend + backend)
metadata:
  type: project
---

Ayhoot is mid-migration unifying two roles, `teacher` and `institute`, into a shared "instructor" layer. The whole instructor layer is uncommitted/untracked work in the tree.

- Frontend: single `resources/js/panels/instructor/**` tree for both roles; `resources/js/routes/instructorRoutes.js` factory builds routes per role (`instructorRoutes('teacher')` / `('institute')`). Role differences resolved at runtime via `localStorage.getItem('role')` (helper `utils/role.js`). Old `panels/teacher` & `panels/institute` are gone.
- Backend: unified `/instructor` group in `routes/api.php` (`role:teacher,institute`) using `Api\Instructor\*`. Role detection centralized in `Api\Instructor\Concerns\InteractsWithInstructor` (`getInstructor()`, `getInstructorType()`, `isTeacher()`/`isInstitute()`, `abortUnlessTeacher()`/`abortUnlessInstitute()`).
- Shared UI component catalog (use these instead of hand-rolling; all translation-ready — pass already-translated strings):
  - `components/shared/Modal.vue` (v-model + `#footer` slot; use for ALL dialog/alert types), `ConfirmDialog.vue` (via `useConfirm()` composable), `DataTable.vue` (all tables), `Pagination.vue`, `StatusBadge.vue`, `AyCard.vue` (generic card; `#header`/`#actions`/`#footer` slots).
  - `layouts/atoms/button/AyButton.vue` (prop `type`: dark-blue|light-blue|dash-blue|yellow|light-green|dark-gray|dark-green|light-gray; no red variant).
  - `layouts/atoms/form/AyInput.vue`, `AySelect.vue`, `AyMultiSelect.vue` (chips + optional search, v-model array), `AySearchableSelect.vue` (single + search). All v-model `modelValue`.
  - `layouts/molecules/dropdown/AyDropdown.vue` (generic menu; `items` + @select, or `#trigger`/default slots).
  (AyInput/AySelect/AyMultiSelect/AySearchableSelect/AyDropdown/AyCard created 2026-06-16.)
- Role paths in instructor panel must use dynamic role: `this.$role` (global getter) in Options API script, `$role` in templates, or `getRole()` from `@/utils/role.js` in `<script setup>`. NEVER hardcode `/teacher/...` or `/institute/...` for in-app navigation (it bounces the other role to /forbidden). Fixed ~35 such paths on 2026-06-16.

Legacy `/teacher` `/institute` groups still exist and are still needed — see [[legacy-api-still-used]].
