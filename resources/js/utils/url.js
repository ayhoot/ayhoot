// Get the base URL from the environment variables.
// This is read from your .env.development or .env.production file.
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

// If you are using Vue CLI instead of Vite, your variable would be:
// const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;


/**
 * Creates a full, usable URL for an asset (like an image) from the backend.
 * This function is the single source of truth for constructing asset paths.
 *
 * It correctly handles:
 * - null or empty paths (returns a fallback).
 * - Full URLs that already start with http (returns them as-is).
 * - Base64 data strings (returns them as-is).
 * - Relative paths from the API (constructs the full URL).
 *
 * @param {string | null | undefined} path - The path from the API (e.g., "exams/cover.jpg").
 * @param {string} [fallback='/imgHolders/CardHolderImage.jpg'] - A fallback image path to use if the main one is invalid.
 * @returns {string} The full, usable image URL.
 */
export function getAssetUrl(path, fallback = '/imgHolders/CardHolderImage.jpg') {
    // 1. If the path is null, undefined, or an empty string, return the fallback immediately.
    if (!path) {
        return fallback;
    }

    // 2. If the path is already a full URL or a Base64 data string, return it directly.
    if (path.startsWith('http') || path.startsWith('data:image')) {
        return path;
    }
    
    // 3. For all other relative paths, clean them and construct the full URL.
    const cleanPath = path.replace(/\\/g, '/').replace(/^\//, '');

    const baseUrl = (typeof window !== 'undefined' && window.__APP_CONFIG__?.VITE_API_BASE_URL) 
        ? window.__APP_CONFIG__.VITE_API_BASE_URL 
        : (import.meta.env.PROD ? '' : (import.meta.env.VITE_API_BASE_URL || ''));
    
    const cleanBase = baseUrl ? baseUrl.replace(/\/$/, '') : '';
    return cleanBase ? `${cleanBase}/uploads/${cleanPath}` : `/uploads/${cleanPath}`;
}