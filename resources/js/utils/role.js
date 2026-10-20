/**
 * Current instructor/user role from localStorage.
 *
 * Use this in setup()/script code instead of the global $role property: $role is
 * only reachable via `this` in Options API methods/computed (and bare $role in
 * templates); inside setup() `this` is undefined, which throws
 * "can't access property $role, this is undefined".
 *
 * Templates can still use the global $role (registered in app.js).
 */
export function getRole() {
    return localStorage.getItem('role') || 'teacher'
}
