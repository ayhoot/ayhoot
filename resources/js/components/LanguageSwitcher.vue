<template>
    <div class="relative" ref="dropdownWrapper">
        <button
            @click="isOpen = !isOpen"
            class="relative gap-1 p-2 flex items-center justify-center rounded-md cursor-pointer font-semibold text-sm text-[#0F1D42] bg-[#C0C0C0] hover:bg-[#979797] shadow-[0_2px_0_rgba(136,136,136,1)] hover:shadow-none hover:translate-y-[2px]"
        >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M20.4375 13C20.9898 13 21.4375 12.5523 21.4375 12C21.4375 11.4477 20.9898 11 20.4375 11V13ZM20 12C20 16.4183 16.4183 20 12 20V22C17.5228 22 22 17.5228 22 12H20ZM12 20C7.58172 20 4 16.4183 4 12H2C2 17.5228 6.47715 22 12 22V20ZM4 12C4 7.58172 7.58172 4 12 4V2C6.47715 2 2 6.47715 2 12H4ZM12 4C16.4183 4 20 7.58172 20 12H22C22 6.47715 17.5228 2 12 2V4ZM12 20C11.7872 20 11.5057 19.9056 11.1624 19.5738C10.8143 19.2373 10.4552 18.7021 10.1319 17.9631C9.48665 16.4882 9.0625 14.3807 9.0625 12H7.0625C7.0625 14.5898 7.51979 16.9823 8.29961 18.7648C8.68887 19.6545 9.1782 20.4373 9.77228 21.0117C10.3712 21.5907 11.1255 22 12 22V20ZM9.0625 12C9.0625 9.61928 9.48665 7.51177 10.1319 6.03686C10.4552 5.29792 10.8143 4.76272 11.1624 4.42621C11.5057 4.09436 11.7872 4 12 4V2C11.1255 2 10.3712 2.40931 9.77228 2.98832C9.1782 3.56266 8.68887 4.34548 8.29961 5.23522C7.51979 7.01767 7.0625 9.41015 7.0625 12H9.0625ZM12 22C12.8745 22 13.6288 21.5907 14.2277 21.0117C14.8218 20.4373 15.3111 19.6545 15.7004 18.7648C16.4802 16.9823 16.9375 14.5898 16.9375 12H14.9375C14.9375 14.3807 14.5133 16.4882 13.8681 17.9631C13.5448 18.7021 13.1857 19.2373 12.8376 19.5738C12.4943 19.9056 12.2128 20 12 20V22ZM16.9375 12C16.9375 9.41015 16.4802 7.01767 15.7004 5.23522C15.3111 4.34548 14.8218 3.56266 14.2277 2.98832C13.6288 2.40931 12.8745 2 12 2V4C12.2128 4 12.4943 4.09436 12.8376 4.42621C13.1857 4.76272 13.5448 5.29792 13.8681 6.03686C14.5133 7.51177 14.9375 9.61928 14.9375 12H16.9375ZM3 13L20.4375 13V11L3 11L3 13Z"
                    fill="black"
                />
            </svg>

            <!-- This span will be hidden on mobile and visible on sm screens and up -->
            <span class="hidden sm:inline">{{ currentLocaleName }}</span>
        </button>

        <transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <div
                v-if="isOpen"
                class="absolute end-0 w-40 mt-5 bg-white rounded-lg shadow-xl border border-gray-200 z-20"
            >
                <div class="py-1">
                    <a
                        @click="switchLanguage('en')"
                        href="#"
                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        :class="{ 'font-bold bg-gray-100': locale === 'en' }"
                    >
                        English
                    </a>
                    <a
                        @click="switchLanguage('fa')"
                        href="#"
                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 font-payda"
                        :class="{ 'font-bold bg-gray-100': locale === 'fa' }"
                    >
                        فارسی
                    </a>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useI18n } from 'vue-i18n'
import { apiCall } from "@/services/apiService"

export default {
    setup() {
        const { locale } = useI18n()
        const isOpen = ref(false)
        const dropdownWrapper = ref(null)

        const languageMap = {
            en: 'English',
            fa: 'فارسی',
        }

        const currentLocaleName = computed(() => languageMap[locale.value])

        const switchLanguage = async (newLocale) => {
            if (locale.value === newLocale) return
            
            // Set cookie first
            const d = new Date()
            d.setTime(d.getTime() + 365 * 24 * 60 * 60 * 1000)
            let expires = 'expires=' + d.toUTCString()
            document.cookie = `lang=${newLocale};${expires};path=/`
            
            // Update i18n locale
            locale.value = newLocale
            isOpen.value = false
            
            // Update i18n locale
            locale.value = newLocale
            isOpen.value = false
        }


        const handleClickOutside = (event) => {
            if (isOpen.value && dropdownWrapper.value && !dropdownWrapper.value.contains(event.target)) {
                isOpen.value = false
            }
        }

        onMounted(() => {
            document.addEventListener('click', handleClickOutside)
        })

        onBeforeUnmount(() => {
            document.removeEventListener('click', handleClickOutside)
        })

        return {
            locale,
            isOpen,
            switchLanguage,
            currentLocaleName,
            dropdownWrapper,
        }
    },
}
</script>
