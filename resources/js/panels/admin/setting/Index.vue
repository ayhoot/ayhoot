<template>
    <div class="bg-gray-50 min-h-screen">
        <div class="fixed top-0 inset-x-0 z-50">
            <Header @toggle-sidebar="toggleSidebar" />
        </div>
        <div class="flex">
            <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />
            <div class="flex-1 sm:ml-64 p-4 sm:p-6 lg:p-8 overflow-y-auto mt-16">
                <h1 class="text-3xl font-bold text-gray-800 mb-8">Settings</h1>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <LocaleSettingsCard />
                    <ChangePasswordCard />
                    <DiscountSettingsCard />
                    <CategoryManager />
                    <LabelManager />
                    <BannerSettingsCard />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Sidebar from '@/components/layout/AdminSidebar.vue'
import Header from '@/components/layout/AdminHeader.vue'
import LocaleSettingsCard from './LocaleSettingsCard.vue'
import ChangePasswordCard from './ChangePasswordCard.vue'
import DiscountSettingsCard from './DiscountSettingsCard.vue'
import CategoryManager from './CategoryManager.vue'
import LabelManager from './LabelManager.vue'
import BannerSettingsCard from './BannerSettingsCard.vue'

export default {
    name: 'AdminSetting',
    components: {
        Sidebar,
        Header,
        LocaleSettingsCard,
        ChangePasswordCard,
        DiscountSettingsCard,
        CategoryManager,
        LabelManager,
        BannerSettingsCard,
    },
    data() {
        return { isSidebarOpen: false }
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen
        },
    },
}
</script>
