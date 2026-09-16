<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getProfessionIcon } from '@/professionIcons';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
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

                    <p class="mt-1 text-sm text-gray-500">
                        إدارة فريق العمل والموظفين في محلك
                    </p>
                </div>

                <Link
                    :href="route('staff.create')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 hover:shadow-md active:scale-[0.98]"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>

                    إضافة موظف جديد
                </Link>
            </div>
        </template>

        <div class="min-h-screen bg-gray-50 py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Main Card -->
                <section
                    class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm"
                >

                    <!-- Card Header -->
                    <div
                        class="flex flex-col gap-3 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h3 class="font-bold text-gray-900">
                                    فريق العمل
                                </h3>

                                <p class="mt-0.5 text-sm text-gray-500">
                                    الموظفون المسجلون في المحل
                                </p>
                            </div>
                        </div>

                        <div
                            class="inline-flex w-fit items-center gap-2 rounded-full bg-gray-50 px-3 py-1.5 text-sm text-gray-600"
                        >
                            <span class="h-2 w-2 rounded-full bg-green-500"></span>

                            {{ staff.length }} موظف
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="staff.length === 0"
                        class="flex flex-col items-center justify-center px-6 py-20 text-center"
                    >
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-50 text-indigo-500"
                        >
                            <svg
                                class="h-9 w-9"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"
                                />
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
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>

                            إضافة أول موظف
                        </Link>
                    </div>

                    <!-- Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full min-w-[850px] text-right">

                            <!-- Table Head -->
                            <thead>
                                <tr class="bg-gray-50/70">
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-gray-500"
                                    >
                                        الموظف
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-gray-500"
                                    >
                                        البريد الإلكتروني
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-gray-500"
                                    >
                                        المسمى الوظيفي
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-gray-500"
                                    >
                                        الحالة
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-gray-500"
                                    >
                                        الإجراءات
                                    </th>
                                </tr>
                            </thead>

                            <!-- Table Body -->
                            <tbody class="divide-y divide-gray-100">

                                <tr
                                    v-for="member in staff"
                                    :key="member.id"
                                    class="group transition hover:bg-indigo-50/30"
                                >

                                    <!-- Employee -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">

                                            <div
                                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-100"
                                            >
                                                <component
                                                    :is="getProfessionIcon(member.title)"
                                                    class="h-5 w-5"
                                                />
                                            </div>

                                            <div class="min-w-0">
                                                <p
                                                    class="truncate font-semibold text-gray-900"
                                                >
                                                    {{ member.user?.name }}
                                                </p>

                                                <p class="mt-0.5 text-xs text-gray-400">
                                                    موظف #{{ member.id }}
                                                </p>
                                            </div>

                                        </div>
                                    </td>

                                    <!-- Email -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <svg
                                                class="h-4 w-4 shrink-0 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                                />
                                            </svg>

                                            <span>
                                                {{ member.user?.email }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Job Title -->
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center rounded-lg bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-700"
                                        >
                                            {{ member.title || 'غير محدد' }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4">
                                        <span
                                            :class="
                                                member.is_active
                                                    ? 'bg-green-50 text-green-700 ring-green-100'
                                                    : 'bg-gray-50 text-gray-600 ring-gray-100'
                                            "
                                            class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold ring-1 ring-inset"
                                        >
                                            <span
                                                :class="
                                                    member.is_active
                                                        ? 'bg-green-500'
                                                        : 'bg-gray-400'
                                                "
                                                class="h-1.5 w-1.5 rounded-full"
                                            ></span>

                                            {{ member.is_active ? 'نشط' : 'غير نشط' }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">

                                            <!-- Edit -->
                                            <Link
                                                :href="route('staff.edit', member.id)"
                                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-indigo-600 transition hover:bg-indigo-50"
                                            >
                                                <svg
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                                                    />
                                                </svg>

                                                تعديل
                                            </Link>

                                            <!-- Delete -->
                                            <button
                                                type="button"
                                                @click="deleteStaff(member.id)"
                                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50"
                                            >
                                                <svg
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-9 0h12"
                                                    />
                                                </svg>

                                                حذف
                                            </button>

                                        </div>
                                    </td>

                                </tr>

                            </tbody>
                        </table>
                    </div>

                </section>

            </div>
        </div>
    </AuthenticatedLayout>
</template>