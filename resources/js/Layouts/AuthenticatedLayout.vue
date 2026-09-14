<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';

const showingMobileMenu = ref(false);

const navItems = [
    { name: 'dashboard', label: 'لوحة التحكم', icon: 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z' },
    { name: 'bookings.index', label: 'الحجوزات', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
    { name: 'services.index', label: 'الخدمات', icon: 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5' },
    { name: 'staff.index', label: 'الموظفين', icon: 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z' },
    { name: 'invoices.index', label: 'الفواتير', icon: 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { name: 'analytics.index', label: 'التقارير', icon: 'M3 13.5V21h18v-7.5M3 13.5l4.5-4.5 4.5 4.5 4.5-9M3 13.5h18' },
];
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">

        <!-- Sidebar (Desktop) -->
        <aside class="hidden lg:flex lg:flex-col w-56 bg-white border-l border-gray-100 shrink-0">
            <div class="flex items-center gap-2 px-4 py-5">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-800">{{ $page.props.auth.user.name }}</span>
            </div>

            <nav class="flex-1 px-3 space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.name"
                    :href="route(item.name)"
                    :class="route().current(item.name.split('.')[0] + '.*') || route().current(item.name)
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-600 hover:bg-gray-50'"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition"
                >
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </Link>
            </nav>

            <div class="p-3 border-t border-gray-100">
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button class="flex items-center gap-2 w-full px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                            <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-600">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                            <span class="truncate">{{ $page.props.auth.user.email }}</span>
                        </button>
                    </template>
                    <template #content>
                        <DropdownLink :href="route('profile.edit')">الملف الشخصي</DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">تسجيل الخروج</DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </aside>

        <!-- Mobile top bar -->
        <div class="lg:hidden fixed top-0 inset-x-0 z-20 bg-white border-b border-gray-100 flex items-center justify-between px-4 h-14">
            <span class="text-sm font-medium text-gray-800">{{ $page.props.auth.user.name }}</span>
            <button @click="showingMobileMenu = !showingMobileMenu" class="p-2 text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <div v-if="showingMobileMenu" class="lg:hidden fixed top-14 inset-x-0 z-20 bg-white border-b border-gray-100 p-3 space-y-1">
            <Link
                v-for="item in navItems"
                :key="item.name"
                :href="route(item.name)"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
            >
                {{ item.label }}
            </Link>
            <Link :href="route('profile.edit')" class="block px-3 py-2.5 text-sm text-gray-600">الملف الشخصي</Link>
            <Link :href="route('logout')" method="post" as="button" class="block px-3 py-2.5 text-sm text-gray-600 w-full text-right">تسجيل الخروج</Link>
        </div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-white border-b border-gray-100 lg:mt-0 mt-14" v-if="$slots.header">
                <div class="px-4 sm:px-6 lg:px-8 py-6">
                    <slot name="header" />
                </div>
            </header>

            <main class="flex-1 lg:mt-0 mt-14">
                <slot />
            </main>
        </div>

    </div>
</template>