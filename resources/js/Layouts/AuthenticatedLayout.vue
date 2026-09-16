<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

import {
    LayoutDashboard,
    CalendarDays,
    Scissors,
    Users,
    Receipt,
    BarChart3,
    Settings,
    Sparkles,
    Menu,
    X,
    ChevronDown,
    LogOut,
    UserRound,
    Bell,
    ExternalLink,
} from 'lucide-vue-next';

const page = usePage();

const showingMobileMenu = ref(false);

const user = computed(() => page.props.auth?.user || {});

const navItems = [
    {
        name: 'dashboard',
        label: 'لوحة التحكم',
        icon: LayoutDashboard,
    },
    {
        name: 'bookings.index',
        label: 'الحجوزات',
        icon: CalendarDays,
    },
    {
        name: 'services.index',
        label: 'الخدمات',
        icon: Scissors,
    },
    {
        name: 'staff.index',
        label: 'الموظفين',
        icon: Users,
    },
    {
        name: 'invoices.index',
        label: 'الفواتير',
        icon: Receipt,
    },
    {
        name: 'analytics.index',
        label: 'التقارير والتحليلات',
        icon: BarChart3,
    },
    {
        name: 'shop-settings.edit',
        label: 'إعدادات المحل',
        icon: Settings,
    },
];

function isActive(item) {
    if (item.name === 'dashboard') {
        return route().current('dashboard');
    }

    const baseName = item.name.split('.')[0];

    return (
        route().current(item.name) ||
        route().current(`${baseName}.*`)
    );
}

function closeMobileMenu() {
    showingMobileMenu.value = false;
}

function getInitials(name) {
    if (!name) return '?';

    const words = name.trim().split(/\s+/);

    if (words.length === 1) {
        return words[0].substring(0, 2).toUpperCase();
    }

    return (
        words[0].charAt(0) +
        words[words.length - 1].charAt(0)
    ).toUpperCase();
}
</script>

<template>
    <div
        dir="rtl"
        class="min-h-screen bg-slate-50 text-slate-800"
    >

        <!-- ===================================================== -->
        <!-- DESKTOP SIDEBAR -->
        <!-- ===================================================== -->

        <aside
            class="hidden lg:flex fixed inset-y-0 right-0 z-40 w-[270px] flex-col
                   bg-white border-l border-slate-200/80 shadow-[0_0_40px_rgba(15,23,42,0.04)]"
        >

            <!-- Brand -->
            <div class="px-5 pt-6 pb-5">

                <div
                    class="relative overflow-hidden rounded-2xl
                           bg-gradient-to-br from-indigo-600 via-violet-600 to-purple-700
                           p-4 shadow-lg shadow-indigo-500/20"
                >

                    <!-- Decorative -->
                    <div
                        class="absolute -top-8 -left-8 w-24 h-24 rounded-full bg-white/10"
                    ></div>

                    <div
                        class="absolute -bottom-10 right-10 w-28 h-28 rounded-full bg-white/5"
                    ></div>

                    <div
                        class="relative flex items-center gap-3"
                    >

                        <div
                            class="w-11 h-11 rounded-xl bg-white/15 border border-white/20
                                   flex items-center justify-center backdrop-blur-sm shrink-0"
                        >
                            <Sparkles
                                class="w-6 h-6 text-white"
                                stroke-width="1.8"
                            />
                        </div>

                        <div class="min-w-0">

                            <h1 class="text-white font-bold text-base">
                                Beauty Salon
                            </h1>

                            <p class="text-indigo-100 text-xs mt-0.5 truncate">
                                نظام إدارة الصالون
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Navigation -->
            <div class="px-4 mb-2">

                <p
                    class="px-3 mb-3 text-[10px] font-bold tracking-widest
                           text-slate-400 uppercase"
                >
                    القائمة الرئيسية
                </p>

            </div>


            <nav
                class="flex-1 px-4 space-y-1 overflow-y-auto
                       scrollbar-thin scrollbar-thumb-slate-200"
            >

                <Link
                    v-for="item in navItems"
                    :key="item.name"
                    :href="route(item.name)"
                    @click="closeMobileMenu"
                    class="group relative flex items-center gap-3 px-3.5 py-3
                           rounded-xl text-sm font-medium transition-all duration-200"
                    :class="
                        isActive(item)
                            ? 'bg-indigo-50 text-indigo-700'
                            : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'
                    "
                >

                    <!-- Active indicator -->
                    <span
                        v-if="isActive(item)"
                        class="absolute right-0 top-1/2 -translate-y-1/2
                               w-1 h-7 rounded-l-full bg-indigo-600"
                    ></span>

                    <span
                        class="flex items-center justify-center w-9 h-9 rounded-xl
                               transition-all duration-200"
                        :class="
                            isActive(item)
                                ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20'
                                : 'bg-slate-50 text-slate-400 group-hover:bg-white group-hover:text-indigo-600'
                        "
                    >

                        <component
                            :is="item.icon"
                            class="w-[18px] h-[18px]"
                            stroke-width="1.9"
                        />

                    </span>

                    <span class="flex-1">
                        {{ item.label }}
                    </span>

                    <span
                        v-if="isActive(item)"
                        class="w-1.5 h-1.5 rounded-full bg-indigo-500"
                    ></span>

                </Link>

            </nav>


            <!-- Bottom -->
            <div class="p-4">

                <!-- Quick booking -->
                <div
                    class="relative overflow-hidden rounded-2xl
                           bg-slate-900 p-4 mb-4"
                >

                    <div
                        class="absolute -left-8 -top-8 w-24 h-24
                               rounded-full bg-indigo-500/20"
                    ></div>

                    <div class="relative">

                        <div
                            class="w-9 h-9 rounded-xl bg-white/10
                                   flex items-center justify-center mb-3"
                        >
                            <CalendarDays
                                class="w-5 h-5 text-indigo-300"
                                stroke-width="1.8"
                            />
                        </div>

                        <p class="text-white text-sm font-semibold">
                            رابط الحجز
                        </p>

                        <p class="text-slate-400 text-xs mt-1 leading-5">
                            شارك رابط الحجز مع زبائنك
                        </p>

                        <Link
                            href="/book"
                            class="mt-3 flex items-center justify-center gap-2
                                   w-full rounded-xl bg-white/10 hover:bg-white/15
                                   text-white text-xs font-medium py-2.5 transition"
                        >
                            فتح صفحة الحجز
                            <ExternalLink
                                class="w-3.5 h-3.5"
                                stroke-width="1.8"
                            />
                        </Link>

                    </div>

                </div>


                <!-- User -->
                <Dropdown
                    align="right"
                    width="56"
                >

                    <template #trigger>

                        <button
                            type="button"
                            class="w-full flex items-center gap-3 p-2.5
                                   rounded-2xl border border-slate-200
                                   bg-white hover:bg-slate-50
                                   transition-all duration-200"
                        >

                            <div
                                class="w-10 h-10 rounded-xl
                                       bg-gradient-to-br from-indigo-500 to-violet-600
                                       text-white flex items-center justify-center
                                       text-sm font-bold shadow-sm shrink-0"
                            >
                                {{ getInitials(user.name) }}
                            </div>

                            <div class="flex-1 min-w-0 text-right">

                                <p
                                    class="text-sm font-semibold text-slate-800 truncate"
                                >
                                    {{ user.name || 'المستخدم' }}
                                </p>

                                <p
                                    class="text-[11px] text-slate-400 truncate mt-0.5"
                                >
                                    {{ user.email || '' }}
                                </p>

                            </div>

                            <ChevronDown
                                class="w-4 h-4 text-slate-400 shrink-0"
                                stroke-width="1.8"
                            />

                        </button>

                    </template>


                    <template #content>

                        <div
                            class="px-4 py-3 border-b border-slate-100"
                        >

                            <p class="text-xs text-slate-400">
                                الحساب
                            </p>

                            <p
                                class="text-sm font-semibold text-slate-800 mt-1 truncate"
                            >
                                {{ user.name || 'المستخدم' }}
                            </p>

                        </div>

                        <DropdownLink
                            :href="route('profile.edit')"
                        >
                            <div class="flex items-center gap-2">
                                <UserRound class="w-4 h-4" />
                                الملف الشخصي
                            </div>
                        </DropdownLink>

                        <DropdownLink
                            :href="route('logout')"
                            method="post"
                            as="button"
                        >
                            <div
                                class="flex items-center gap-2 text-red-600"
                            >
                                <LogOut class="w-4 h-4" />
                                تسجيل الخروج
                            </div>
                        </DropdownLink>

                    </template>

                </Dropdown>

            </div>

        </aside>


        <!-- ===================================================== -->
        <!-- MOBILE HEADER -->
        <!-- ===================================================== -->

        <header
            class="lg:hidden fixed top-0 inset-x-0 z-50 h-[68px]
                   bg-white/95 backdrop-blur-xl border-b border-slate-200/80
                   shadow-sm"
        >

            <div
                class="h-full px-4 flex items-center justify-between"
            >

                <!-- Brand -->
                <div class="flex items-center gap-3">

                    <div
                        class="w-10 h-10 rounded-xl
                               bg-gradient-to-br from-indigo-600 to-violet-600
                               flex items-center justify-center shadow-md shadow-indigo-500/20"
                    >
                        <Sparkles
                            class="w-5 h-5 text-white"
                            stroke-width="1.8"
                        />
                    </div>

                    <div>

                        <p class="text-sm font-bold text-slate-800">
                            Beauty Salon
                        </p>

                        <p class="text-[10px] text-slate-400">
                            نظام إدارة الصالون
                        </p>

                    </div>

                </div>


                <!-- Mobile buttons -->
                <div class="flex items-center gap-2">

                    <button
                        type="button"
                        class="w-10 h-10 rounded-xl bg-slate-50
                               text-slate-500 flex items-center justify-center
                               hover:bg-slate-100 transition"
                    >
                        <Bell
                            class="w-[18px] h-[18px]"
                            stroke-width="1.8"
                        />
                    </button>

                    <button
                        type="button"
                        @click="showingMobileMenu = !showingMobileMenu"
                        class="w-10 h-10 rounded-xl bg-slate-900
                               text-white flex items-center justify-center
                               hover:bg-slate-800 transition"
                    >

                        <X
                            v-if="showingMobileMenu"
                            class="w-5 h-5"
                        />

                        <Menu
                            v-else
                            class="w-5 h-5"
                        />

                    </button>

                </div>

            </div>

        </header>


        <!-- ===================================================== -->
        <!-- MOBILE MENU -->
        <!-- ===================================================== -->

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >

            <div
                v-if="showingMobileMenu"
                class="lg:hidden fixed top-[68px] inset-x-0 z-40
                       bg-white border-b border-slate-200
                       shadow-xl shadow-slate-900/10"
            >

                <div class="p-4">

                    <p
                        class="text-[10px] font-bold tracking-widest
                               text-slate-400 mb-3 px-2"
                    >
                        القائمة الرئيسية
                    </p>

                    <nav class="space-y-1">

                        <Link
                            v-for="item in navItems"
                            :key="item.name"
                            :href="route(item.name)"
                            @click="closeMobileMenu"
                            class="flex items-center gap-3 px-3 py-3 rounded-xl
                                   text-sm font-medium transition"
                            :class="
                                isActive(item)
                                    ? 'bg-indigo-50 text-indigo-700'
                                    : 'text-slate-600 hover:bg-slate-50'
                            "
                        >

                            <span
                                class="w-9 h-9 rounded-xl flex items-center justify-center"
                                :class="
                                    isActive(item)
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-slate-50 text-slate-400'
                                "
                            >

                                <component
                                    :is="item.icon"
                                    class="w-[18px] h-[18px]"
                                />

                            </span>

                            {{ item.label }}

                        </Link>

                    </nav>


                    <div
                        class="mt-4 pt-4 border-t border-slate-100"
                    >

                        <Link
                            :href="route('profile.edit')"
                            @click="closeMobileMenu"
                            class="flex items-center gap-3 px-3 py-3
                                   rounded-xl text-sm text-slate-600
                                   hover:bg-slate-50"
                        >

                            <UserRound
                                class="w-5 h-5 text-slate-400"
                            />

                            الملف الشخصي

                        </Link>

                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            @click="closeMobileMenu"
                            class="w-full flex items-center gap-3 px-3 py-3
                                   rounded-xl text-sm text-red-600
                                   hover:bg-red-50"
                        >

                            <LogOut
                                class="w-5 h-5"
                            />

                            تسجيل الخروج

                        </Link>

                    </div>

                </div>

            </div>

        </Transition>


        <!-- ===================================================== -->
        <!-- MAIN AREA -->
        <!-- ===================================================== -->

        <div
            class="lg:mr-[270px] min-h-screen flex flex-col"
        >

            <!-- ================================================= -->
            <!-- TOP NAVBAR -->
            <!-- ================================================= -->

            <header
                v-if="$slots.header"
                class="hidden lg:block sticky top-0 z-30
                       bg-white/90 backdrop-blur-xl
                       border-b border-slate-200/70"
            >

                <div
                    class="h-[76px] px-6 xl:px-8
                           flex items-center justify-between"
                >

                    <!-- Page title -->
                    <div class="min-w-0">

                        <slot name="header" />

                    </div>


                    <!-- Right actions -->
                    <div
                        class="flex items-center gap-3"
                    >

                        <!-- Notification -->
                        <button
                            type="button"
                            class="relative w-10 h-10 rounded-xl
                                   bg-slate-50 border border-slate-100
                                   text-slate-500
                                   hover:bg-indigo-50 hover:text-indigo-600
                                   transition"
                        >

                            <Bell
                                class="w-[18px] h-[18px] mx-auto"
                                stroke-width="1.8"
                            />

                            <span
                                class="absolute top-2 right-2
                                       w-1.5 h-1.5 rounded-full
                                       bg-indigo-500 ring-2 ring-white"
                            ></span>

                        </button>


                        <!-- User mini -->
                        <Dropdown
                            align="right"
                            width="48"
                        >

                            <template #trigger>

                                <button
                                    type="button"
                                    class="flex items-center gap-2.5
                                           px-2 py-1.5 rounded-xl
                                           hover:bg-slate-50 transition"
                                >

                                    <div
                                        class="w-9 h-9 rounded-xl
                                               bg-gradient-to-br from-indigo-500 to-violet-600
                                               text-white flex items-center justify-center
                                               text-xs font-bold"
                                    >
                                        {{ getInitials(user.name) }}
                                    </div>

                                    <div class="hidden xl:block text-right">

                                        <p
                                            class="text-xs font-semibold text-slate-700"
                                        >
                                            {{ user.name || 'المستخدم' }}
                                        </p>

                                        <p
                                            class="text-[10px] text-slate-400 mt-0.5"
                                        >
                                            حساب المدير
                                        </p>

                                    </div>

                                    <ChevronDown
                                        class="w-4 h-4 text-slate-400"
                                    />

                                </button>

                            </template>


                            <template #content>

                                <DropdownLink
                                    :href="route('profile.edit')"
                                >
                                    <div class="flex items-center gap-2">
                                        <UserRound class="w-4 h-4" />
                                        الملف الشخصي
                                    </div>
                                </DropdownLink>

                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    <div
                                        class="flex items-center gap-2 text-red-600"
                                    >
                                        <LogOut class="w-4 h-4" />
                                        تسجيل الخروج
                                    </div>
                                </DropdownLink>

                            </template>

                        </Dropdown>

                    </div>

                </div>

            </header>


            <!-- ================================================= -->
            <!-- PAGE CONTENT -->
            <!-- ================================================= -->

            <main
                class="flex-1"
                :class="[
                    'lg:mt-0',
                    'mt-[68px]'
                ]"
            >

                <slot />

            </main>

        </div>

    </div>
</template>