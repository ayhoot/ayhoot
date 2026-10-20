<template>
    <div class="bg-gray-50 min-h-screen" :dir="$i18n.locale === 'fa' ? 'rtl' : 'ltr'">
        <!-- Fixed top nav (public NavBar by default; LoginNavBar for auth pages). -->
        <div class="fixed top-0 inset-x-0 z-50">
            <component :is="navComponent" />
        </div>

        <!-- Page content. mt-16 clears the fixed nav; pages add their own inner
             padding unless `padded` is set. -->
        <main :class="['mt-12 min-h-[calc(100vh-4rem)]', padded ? 'p-6' : '']">
            <slot />
        </main>

        <FooterSection v-if="footer" />
    </div>
</template>

<script>
/**
 * Unified shell for PUBLIC pages (home, discover, ayhoot/course detail, news,
 * profile, auth, …). Replaces the per-page NavBar + content + FooterSection
 * boilerplate so every public page shares one consistent layout.
 *
 *   <PublicLayout footer>            ...content...   </PublicLayout>   // main nav + footer
 *   <PublicLayout nav="login">       ...form...      </PublicLayout>   // login nav, no footer
 */
import NavBar from '@/components/organisms/nav/NavBar.vue'
import LoginNavBar from '@/components/organisms/nav/LoginNavBar.vue'
import FooterSection from '@/components/home/FooterSection.vue'

export default {
    name: 'PublicLayout',
    components: { NavBar, LoginNavBar, FooterSection },
    props: {
        nav: { type: String, default: 'main' }, // 'main' | 'login'
        footer: { type: Boolean, default: false },
        padded: { type: Boolean, default: false },
    },
    computed: {
        navComponent() {
            return this.nav === 'login' ? 'LoginNavBar' : 'NavBar'
        },
    },
}
</script>
