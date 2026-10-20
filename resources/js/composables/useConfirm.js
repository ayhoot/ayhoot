import { reactive } from 'vue'

/**
 * App-wide confirm/alert dialogs rendered as styled modals (replacing SweetAlert /
 * native alert()/confirm()). A single <ConfirmDialog /> (mounted once in App.vue)
 * renders this shared state.
 *
 *   import { useConfirm } from '@/composables/useConfirm'
 *   const { confirm, alert } = useConfirm()
 *
 *   if (await confirm({ title: 'Delete item?', message: 'This cannot be undone.', confirmText: 'Delete', variant: 'danger' })) {
 *       // proceed
 *   }
 *
 *   await alert({ title: 'Saved', message: 'Your changes were saved.' })
 */
const state = reactive({
    open: false,
    title: '',
    message: '',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'primary', // 'primary' | 'danger'
    showCancel: true,
    _resolve: null,
})

function settle(result) {
    state.open = false
    const resolve = state._resolve
    state._resolve = null
    if (resolve) resolve(result)
}

/** Show a confirm dialog. Resolves true (confirm) / false (cancel). */
function confirm(options = {}) {
    return new Promise((resolve) => {
        Object.assign(state, {
            title: options.title || 'Are you sure?',
            message: options.message || '',
            confirmText: options.confirmText || 'Confirm',
            cancelText: options.cancelText || 'Cancel',
            variant: options.variant || 'primary',
            showCancel: true,
            _resolve: resolve,
            open: true,
        })
    })
}

/** Show an alert dialog (single OK button). Resolves when dismissed. */
function alert(options = {}) {
    return new Promise((resolve) => {
        Object.assign(state, {
            title: options.title || '',
            message: options.message || '',
            confirmText: options.confirmText || 'OK',
            cancelText: '',
            variant: options.variant || 'primary',
            showCancel: false,
            _resolve: resolve,
            open: true,
        })
    })
}

export function useConfirm() {
    return { confirm, alert }
}

// Internal accessors for the singleton <ConfirmDialog />.
export const confirmState = state
export const resolveConfirm = settle
