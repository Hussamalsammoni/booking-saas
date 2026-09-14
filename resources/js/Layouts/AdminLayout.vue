<script setup>
import { router } from '@inertiajs/vue3'

function logout() {
    router.post(route('admin.logout'))
}
</script>

<template>
    <div class="min-h-screen bg-gray-900 text-white">
        <nav class="bg-gray-800 border-b border-gray-700 px-8 py-4 flex items-center justify-between">
            <h1 class="text-lg font-bold">لوحة الإدارة</h1>
            <div class="flex items-center gap-6">
                <a :href="route('admin.dashboard')" class="text-sm text-gray-300 hover:text-white">
                    المحلات
                </a>
                <button
                    @click="logout"
                    class="text-sm bg-red-600 hover:bg-red-700 px-4 py-1.5 rounded"
                >
                    تسجيل خروج
                </button>
            </div>
        </nav>

        <main class="p-8">
            <slot />
        </main>
    </div>
</template>