import { useToast } from 'vue-toast-notification'
import i18n from '../utils/i18n'

const toast = useToast()

let API_BASE_URL = ''
try {
    if (typeof window !== 'undefined' && window.__APP_CONFIG__ && window.__APP_CONFIG__.VITE_API_BASE_URL) {
        API_BASE_URL = window.__APP_CONFIG__.VITE_API_BASE_URL || ''
    } else if (import.meta.env.PROD) {
        // In production builds, default to origin-relative so the bundle works on any domain/IP
        API_BASE_URL = typeof window !== 'undefined' ? window.location.origin : ''
    } else {
        API_BASE_URL = import.meta.env.VITE_API_BASE_URL || ''
    }
} catch (e) {
    API_BASE_URL = ''
}
if (API_BASE_URL && API_BASE_URL.endsWith('/')) {
    API_BASE_URL = API_BASE_URL.slice(0, -1)
}

if (!API_BASE_URL && typeof window !== 'undefined') {
    API_BASE_URL = window.location.origin
}

// Ensure API_BASE_URL ends with a slash for consistent concatenation
if (API_BASE_URL && !API_BASE_URL.endsWith('/')) {
    API_BASE_URL += '/'
}
console.log('[apiService] Initialized with API_BASE_URL:', API_BASE_URL)

export async function apiCall(method, url, data = null, extraHeaders = {}) {
    const token = localStorage.getItem('token')

    const hasFile = data && (
        data instanceof FormData ||
        Object.values(data).some(value => value instanceof File)
    )

    // Get CSRF token from meta tag
    const csrfToken = typeof document !== 'undefined' 
        ? document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
        : null;

    const currentLocale = (typeof i18n?.global?.locale === 'object' && i18n?.global?.locale?.value)
        ? i18n.global.locale.value
        : (i18n?.global?.locale || 'en')

    const headers = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept-Language': currentLocale,
        ...(token && { 'Authorization': `Bearer ${token}` }),
        ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
        ...extraHeaders,
    }

    if (hasFile) {
        if (headers['Content-Type'] && headers['Content-Type'].toString().toLowerCase().includes('multipart/form-data')) {
            delete headers['Content-Type']
        }
    } else {
        if (!headers['Content-Type']) {
            headers['Content-Type'] = 'application/json'
        }
    }

    let body
    if (data) {
        if (data instanceof FormData) {
            body = data
        } else if (hasFile) {
            const formData = new FormData()
            Object.entries(data).forEach(([key, value]) => {
                if (value !== null && value !== undefined) {
                    formData.append(key, value)
                }
            })
            body = formData
        } else {
            body = JSON.stringify(data)
        }
    }

    const options = {
        method,
        headers,
        credentials: 'same-origin',
        ...(body && { body }),
    }

    console.log('[apiCall] Request:', { method, url, fullUrl: url.startsWith('http') ? url : `${API_BASE_URL}${url.startsWith('/') ? url.substring(1) : url}` })

    try {
        const cleanUrl = url.startsWith('/') ? url.substring(1) : url
        const fullUrl = url.startsWith('http') ? url : `${API_BASE_URL}${cleanUrl}`
        const response = await fetch(fullUrl, options)

        console.log('[apiCall] Response status:', response.status, 'Content-Type:', response.headers.get('content-type'))

        if (!response.ok) {
            console.error('[apiCall] Error response:', response.status, fullUrl)

            let errorData = null
            try {
                errorData = await response.json()
            } catch (e) {
                errorData = null
            }

            const cleanUrlLower = cleanUrl.toLowerCase()
            const isAuthEndpoint = cleanUrlLower.includes('login') || 
                                   cleanUrlLower.includes('signup') || 
                                   cleanUrlLower.includes('register') || 
                                   cleanUrlLower.includes('password') || 
                                   cleanUrlLower.includes('otp') || 
                                   cleanUrlLower.includes('verify')

            switch (response.status) {
                case 401:
                    if (isAuthEndpoint) {
                        // When logging in / authenticating, 401 is invalid credentials / code, NOT session expired!
                        const invalidMsg = errorData?.message || i18n.global.t('login.invalidCredentials') || i18n.global.t('errors.invalidCredentials') || 'Invalid credentials'
                        toast.error(invalidMsg)
                    } else {
                        toast.error(i18n.global.t('errors.sessionExpired') || 'Your session has expired. Please log in again.')
                        localStorage.removeItem('token')
                        localStorage.removeItem('user')
                        localStorage.removeItem('role')
                    }
                    break
                case 403: {
                    toast.error(errorData?.message || i18n.global.t('errors.unauthorized') || 'Access Denied.')
                    break
                }
                case 404:
                    // Only toast if it's not a background check
                    if (!url.includes('purchase-status')) {
                        toast.error(errorData?.message || i18n.global.t('errors.notFound') || 'Resource not found.')
                    }
                    break
                case 409: {
                    toast.error(errorData?.message || 'Conflict error.')
                    break
                }
                case 419:
                    toast.error(i18n.global.t('errors.pageExpired') || 'Page expired. Please refresh.')
                    break
                case 422:
                    // Validation errors are usually handled by the caller
                    break
                case 500:
                case 503:
                    toast.error(i18n.global.t('errors.serverError') || 'A server error occurred. Please try again later.')
                    break
                default:
                    console.warn(`[apiCall] Unhandled error status: ${response.status}`)
                    break
            }
            
            // Re-throw so the caller can handle specific logic (like showing form errors)
            const err = new Error(errorData?.message || `HTTP ${response.status}`)
            err.status = response.status
            err.response = errorData || {}
            if (err.response && typeof err.response === 'object') {
                err.response.data = errorData
            }
            throw err
        }

        const contentType = response.headers.get('content-type') || ''
        const responseText = await response.text()
        
        if (!contentType.includes('application/json')) {
            // Check if we were redirected to a login page or home page (common in Laravel 302 redirects for API)
            const responseUrl = response.url.toLowerCase();
            const isAuthEndpoint = cleanUrl.toLowerCase().includes('login') || cleanUrl.toLowerCase().includes('signup');
            if (!isAuthEndpoint && (responseUrl.includes('/login') || responseUrl.includes('/admin/login') || !responseUrl.includes('/api/'))) {
                console.error('[apiCall] Detected redirection to non-API URL:', response.url);
                toast.error(i18n.global.t('errors.sessionExpired') || 'Session expired. Please log in again.');
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                localStorage.removeItem('role');
                throw new Error('REDIRECTED_TO_LOGIN');
            }

            console.error('Server response is not JSON. URL:', fullUrl, 'Content-Type:', contentType)
            console.error('Response text (truncated):', responseText.slice(0, 1000))

            try {
                if (method === 'GET' && fullUrl.includes('/api/student/category/')) {
                    const altUrl = fullUrl.replace('/api/student/category/', '/api/home/category/')
                    console.warn('Retrying with public endpoint:', altUrl)
                    const altResp = await fetch(altUrl, options)
                    const altContentType = altResp.headers.get('content-type') || ''
                    const altText = await altResp.text()
                    if (altResp.ok && altContentType.includes('application/json')) {
                        return JSON.parse(altText)
                    }
                }
            } catch (fallbackErr) {
                console.warn('Fallback category request failed:', fallbackErr)
            }
            throw new Error(`Server returned invalid JSON response from ${fullUrl}`)
        }

        try {
            const responseData = JSON.parse(responseText)
            return responseData
        } catch (jsonError) {
            console.error('Failed to parse JSON response:', jsonError)
            throw new Error('Server returned invalid JSON response')
        }
    } catch (error) {
        console.error('API call error:', error)
        console.trace('Trace for API call error')
        if (error.response) {
            throw error
        }
        throw error
    }
}

/**
 * Normalize question options coming from server or DB.
 * Accepts either a JSON string or an already-parsed array and
 * returns an array of option objects with sane defaults.
 */
export function parseOptions(input) {
    const DEFAULT_SHAPES = ['triangle', 'diamond', 'circle', 'rectangle', 'pentagon', 'hexagon']

    if (!input) return []

    let opts = input
    if (typeof input === 'string') {
        try {
            opts = JSON.parse(input)
        } catch (e) {
            return []
        }
    }

    if (!Array.isArray(opts)) return []

    // The instructor editor concatenates color+name when saving shapes
    // (e.g. "redtriangle", "greenrectangle", "bluediamond"). QuizShape only
    // matches the bare name, so normalize to the base shape here.
    const normalizeShape = (raw, idx) => {
        const fallback = DEFAULT_SHAPES[idx % DEFAULT_SHAPES.length]
        if (!raw || typeof raw !== 'string') return fallback
        const lower = raw.toLowerCase()
        const match = DEFAULT_SHAPES.find((name) => lower.includes(name))
        return match || fallback
    }

    // For two-option True/False shapes we can recover a sensible label from
    // the shape colour when text is missing — older saves sometimes blanked it.
    const isTrueFalsePair = opts.length === 2 && opts.every((o) => {
        const s = (o?.shape || '').toLowerCase()
        return s.includes('green') || s.includes('rectangle') || s.includes('red') || s.includes('triangle')
    })

    return opts.map((opt, idx) => {
        const o = opt || {}
        // The instructor editor may save the option text under different keys
        // ('text', 'label', 'value', 'content', 'option', 'answer') — accept
        // any of them so the viewer can always render `option.text`.
        let text = o.text ?? o.label ?? o.value ?? o.content ?? o.option ?? o.answer ?? ''
        if (typeof text !== 'string') text = String(text ?? '')
        // Fallback: True/False questions whose options lost their text.
        if (!text.trim() && isTrueFalsePair) {
            const s = (o?.shape || '').toLowerCase()
            if (s.includes('green') || s.includes('rectangle')) text = 'True'
            else if (s.includes('red') || s.includes('triangle')) text = 'False'
        }
        // Likewise the "correct" flag may be 'correct' or 'isCorrect'.
        const rawCorrect = (typeof o.correct !== 'undefined') ? o.correct
            : (typeof o.isCorrect !== 'undefined') ? o.isCorrect
            : undefined
        return {
            ...o,
            id: o.id || o.uid || `opt-${idx}`,
            text,
            shape: normalizeShape(o.shape, idx),
            correct: typeof rawCorrect !== 'undefined' ? Boolean(rawCorrect) : undefined,
            image: o.image ?? o.img ?? o.file ?? undefined,
        }
    })
}
