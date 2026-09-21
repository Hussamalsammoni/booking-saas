<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getProfessionIcon } from '@/professionIcons';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    staff: Array,
});

const deleteStaff = (id) => {
    if (confirm('هل أنت متأكد من حذف هذا الموظف؟')) {
        router.delete(route('staff.destroy', id));
    }
};
</script>

<template>
    <Head title="الموظفين" />

    <AuthenticatedLayout>
        <!-- Header -->
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        الموظفين
                    </h2>

                    <p class="mt-1.5 text-sm text-gray-500">
                        إدارة فريق العمل والموظفين في محلك
                    </p>
                </div>

                <Link
                    :href="route('staff.create')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 hover:shadow-md active:scale-[0.98]"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>

                    إضافة موظف جديد
                </Link>
            </div>
        </template>


        <div class="min-h-screen bg-[#f8fafc]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

                <!-- =========================
                     Statistics Grid
                ========================== -->
                <section class="mb-8">

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

                        <!-- Total -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-indigo-100 hover:shadow-lg hover:shadow-indigo-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        إجمالي الموظفين
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ staff.length }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            موظف
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white"
                                >
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                                    </svg>
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                عدد الموظفين المسجلين
                            </div>
                        </div>


                        <!-- Active -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-emerald-100 hover:shadow-lg hover:shadow-emerald-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        الموظفين النشطين
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ staff.filter(m => m.is_active).length }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            موظف
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white"
                                >
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                يعملون حالياً بالمحل
                            </div>
                        </div>


                        <!-- Inactive -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-gray-200 hover:shadow-lg hover:shadow-gray-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        غير نشطين
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ staff.filter(m => !m.is_active).length }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            موظف
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-500 transition group-hover:bg-gray-700 group-hover:text-white"
                                >
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                خارج الخدمة حالياً
                            </div>
                        </div>

                    </div>
                </section>


                <!-- =========================
                     Staff List
                ========================== -->
                <section
                    class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm"
                >

                    <!-- Section Header -->
                    <div
                        class="flex flex-col gap-3 border-b border-gray-100 px-6 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-8"
                    >
                        <div class="flex items-center gap-4">

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">
                                    فريق العمل
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    الموظفون المسجلون في المحل
                                </p>
                            </div>

                        </div>

                        <div
                            class="inline-flex w-fit items-center gap-2 rounded-xl bg-gray-50 px-3 py-2 text-xs font-medium text-gray-500"
                        >
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            {{ staff.length }} موظف
                        </div>
                    </div>


                    <!-- Empty -->
                    <div
                        v-if="staff.length === 0"
                        class="flex flex-col items-center justify-center px-6 py-20 text-center"
                    >
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-3xl bg-indigo-50 text-indigo-500"
                        >
                            <svg class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                            </svg>
                        </div>

                        <h4 class="mt-5 text-lg font-bold text-gray-900">
                            لا يوجد موظفين بعد
                        </h4>

                        <p class="mt-2 max-w-md text-sm leading-6 text-gray-500">
                            ابدأ بإضافة أول موظف إلى فريق العمل لإدارة الموظفين والخدمات بشكل أفضل.
                        </p>

                        <Link
                            :href="route('staff.create')"
                            class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>

                            إضافة أول موظف
                        </Link>
                    </div>


                    <!-- Desktop Table -->
                    <div v-else class="hidden overflow-x-auto md:block">
                        <table class="w-full min-w-[850px] text-right">

                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/70">
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">الموظف</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">البريد الإلكتروني</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">المسمى الوظيفي</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">الحالة</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">الإجراءات</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">

                                <tr
                                    v-for="member in staff"
                                    :key="member.id"
                                    class="group transition hover:bg-gray-50/70"
                                >

                                    <!-- Employee -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">

                                            <div
                                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-100 text-indigo-600 ring-1 ring-indigo-100"
                                            >
                                                <component
                                                    :is="getProfessionIcon(member.title)"
                                                    class="h-5 w-5"
                                                />
                                            </div>

                                            <div class="min-w-0">
                                                <p class="truncate font-semibold text-gray-900">
                                                    {{ member.user?.name }}
                                                </p>

                                                <p class="mt-0.5 text-xs text-gray-400">
                                                    موظف #{{ member.id }}
                                                </p>
                                            </div>

                                        </div>
                                    </td>

                                    <!-- Email -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>

                                            <span>{{ member.user?.email }}</span>
                                        </div>
                                    </td>

                                    <!-- Job Title -->
                                    <td class="px-6 py-5">
                                        <span class="inline-flex items-center rounded-lg bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-700">
                                            {{ member.title || 'غير محدد' }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-5">
                                        <span
                                            :class="
                                                member.is_active
                                                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200'
                                                    : 'bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-200'
                                            "
                                            class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold"
                                        >
                                            <span
                                                :class="member.is_active ? 'bg-emerald-500' : 'bg-gray-400'"
                                                class="h-1.5 w-1.5 rounded-full"
                                            ></span>

                                            {{ member.is_active ? 'نشط' : 'غير نشط' }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">

                                            <Link
                                                :href="route('staff.edit', member.id)"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-600 transition hover:border-indigo-200 hover:bg-indigo-100"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>

                                                تعديل
                                            </Link>

                                            <button
                                                type="button"
                                                @click="deleteStaff(member.id)"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:border-red-200 hover:bg-red-100"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-9 0h12" />
                                                </svg>

                                                حذف
                                            </button>

                                        </div>
                                    </td>

                                </tr>

                            </tbody>
                        </table>
                    </div>


                    <!-- Mobile Cards -->
                    <div v-if="staff.length > 0" class="divide-y divide-gray-100 md:hidden">
                        <div
                            v-for="member in staff"
                            :key="member.id"
                            class="p-5"
                        >

                            <div class="flex items-start justify-between gap-4">

                                <div class="flex min-w-0 items-center gap-3">

                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-100 text-indigo-600 ring-1 ring-indigo-100"
                                    >
                                        <component :is="getProfessionIcon(member.title)" class="h-5 w-5" />
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-gray-900">
                                            {{ member.user?.name }}
                                        </p>

                                        <p class="mt-0.5 truncate text-xs text-gray-400">
                                            {{ member.user?.email }}
                                        </p>
                                    </div>

                                </div>

                                <span
                                    :class="
                                        member.is_active
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-gray-100 text-gray-600'
                                    "
                                    class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold"
                                >
                                    {{ member.is_active ? 'نشط' : 'غير نشط' }}
                                </span>

                            </div>


                            <div class="mt-4 rounded-xl bg-gray-50 p-3">
                                <p class="text-xs text-gray-400">المسمى الوظيفي</p>
                                <p class="mt-1 text-sm font-medium text-gray-800">
                                    {{ member.title || 'غير محدد' }}
                                </p>
                            </div>


                            <div class="mt-4 flex gap-2">

                                <Link
                                    :href="route('staff.edit', member.id)"
                                    class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-100"
                                >
                                    تعديل
                                </Link>

                                <button
                                    type="button"
                                    @click="deleteStaff(member.id)"
                                    class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-100"
                                >
                                    حذف
                                </button>

                            </div>

                        </div>
                    </div>

                </section>

            </div>
        </div>
    </AuthenticatedLayout>
</template>