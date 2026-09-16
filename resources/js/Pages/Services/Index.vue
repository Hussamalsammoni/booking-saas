<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    services: Array,
});

const deleteService = (id) => {
    if (confirm('هل أنت متأكد من حذف هذه الخدمة؟')) {
        router.delete(route('services.destroy', id));
    }
};
</script>

<template>
    <Head title="الخدمات" />

    <AuthenticatedLayout>
        <!-- Header -->
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        الخدمات
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        إدارة الخدمات والأسعار والمدة الخاصة بمحلك
                    </p>
                </div>

                <Link
                    :href="route('services.create')"
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

                    إضافة خدمة جديدة
                </Link>
            </div>
        </template>

        <div class="min-h-screen bg-gray-50 py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- Top Summary -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

                    <!-- Total Services -->
                    <div
                        class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    إجمالي الخدمات
                                </p>

                                <p class="mt-2 text-3xl font-bold text-gray-900">
                                    {{ services.length }}
                                </p>
                            </div>

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6"
                                    />
                                </svg>
                            </div>
                        </div>

                        <p class="mt-4 text-xs text-gray-400">
                            الخدمات المضافة إلى المحل
                        </p>
                    </div>


                    <!-- Active Services -->
                    <div
                        class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    الخدمات النشطة
                                </p>

                                <p class="mt-2 text-3xl font-bold text-gray-900">
                                    {{
                                        services.filter(
                                            service => service.is_active
                                        ).length
                                    }}
                                </p>
                            </div>

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                            </div>
                        </div>

                        <p class="mt-4 text-xs text-gray-400">
                            الخدمات المتاحة للحجز
                        </p>
                    </div>


                    <!-- Inactive Services -->
                    <div
                        class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    الخدمات غير النشطة
                                </p>

                                <p class="mt-2 text-3xl font-bold text-gray-900">
                                    {{
                                        services.filter(
                                            service => !service.is_active
                                        ).length
                                    }}
                                </p>
                            </div>

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-gray-500"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </div>
                        </div>

                        <p class="mt-4 text-xs text-gray-400">
                            الخدمات غير المتاحة حالياً
                        </p>
                    </div>

                </div>


                <!-- Services Table -->
                <section
                    class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm"
                >

                    <!-- Table Header -->
                    <div
                        class="flex flex-col gap-3 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">
                                قائمة الخدمات
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                جميع الخدمات المسجلة في محلك
                            </p>
                        </div>

                        <div
                            class="inline-flex w-fit items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-xs font-medium text-gray-500"
                        >
                            <span
                                class="h-2 w-2 rounded-full bg-emerald-500"
                            ></span>

                            {{ services.length }} خدمة
                        </div>
                    </div>


                    <!-- Empty State -->
                    <div
                        v-if="services.length === 0"
                        class="flex flex-col items-center justify-center px-6 py-20 text-center"
                    >
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500"
                        >
                            <svg
                                class="h-10 w-10"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.7"
                                    d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6"
                                />
                            </svg>
                        </div>

                        <h4 class="mt-5 text-lg font-bold text-gray-900">
                            لا توجد خدمات بعد
                        </h4>

                        <p class="mt-2 max-w-md text-sm leading-6 text-gray-500">
                            أضف خدمات محلك حتى يتمكن العملاء من رؤيتها
                            واختيارها عند الحجز.
                        </p>

                        <Link
                            :href="route('services.create')"
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

                            إضافة أول خدمة
                        </Link>
                    </div>


                    <!-- Desktop Table -->
                    <div
                        v-else
                        class="hidden overflow-x-auto md:block"
                    >
                        <table class="w-full text-right">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/70">
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-gray-500"
                                    >
                                        الخدمة
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-gray-500"
                                    >
                                        المدة
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-gray-500"
                                    >
                                        السعر
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

                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="service in services"
                                    :key="service.id"
                                    class="group transition hover:bg-gray-50/70"
                                >

                                    <!-- Service -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">

                                            <div
                                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
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
                                                        stroke-width="1.8"
                                                        d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6"
                                                    />
                                                </svg>
                                            </div>

                                            <div>
                                                <p
                                                    class="font-semibold text-gray-900"
                                                >
                                                    {{ service.name }}
                                                </p>

                                                <p
                                                    class="mt-1 text-xs text-gray-400"
                                                >
                                                    خدمة رقم #{{ service.id }}
                                                </p>
                                            </div>

                                        </div>
                                    </td>


                                    <!-- Duration -->
                                    <td class="px-6 py-5">
                                        <div
                                            class="inline-flex items-center gap-2 text-sm text-gray-600"
                                        >
                                            <svg
                                                class="h-4 w-4 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="9"
                                                    stroke-width="1.8"
                                                />

                                                <path
                                                    stroke-linecap="round"
                                                    stroke-width="1.8"
                                                    d="M12 7v5l3 2"
                                                />
                                            </svg>

                                            {{ service.duration_minutes }}
                                            دقيقة
                                        </div>
                                    </td>


                                    <!-- Price -->
                                    <td class="px-6 py-5">
                                        <div
                                            class="inline-flex items-center gap-1.5"
                                        >
                                            <span
                                                class="font-semibold text-gray-900"
                                            >
                                                {{ service.price }}
                                            </span>

                                            <span class="text-xs text-gray-400">
                                                $
                                            </span>
                                        </div>
                                    </td>


                                    <!-- Status -->
                                    <td class="px-6 py-5">
                                        <span
                                            :class="
                                                service.is_active
                                                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200'
                                                    : 'bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-200'
                                            "
                                            class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold"
                                        >
                                            <span
                                                :class="
                                                    service.is_active
                                                        ? 'bg-emerald-500'
                                                        : 'bg-gray-400'
                                                "
                                                class="h-1.5 w-1.5 rounded-full"
                                            ></span>

                                            {{
                                                service.is_active
                                                    ? 'نشطة'
                                                    : 'غير نشطة'
                                            }}
                                        </span>
                                    </td>


                                    <!-- Actions -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">

                                            <Link
                                                :href="
                                                    route(
                                                        'services.edit',
                                                        service.id
                                                    )
                                                "
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-600 transition hover:border-indigo-200 hover:bg-indigo-100"
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
                                                        stroke-width="1.8"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                                                    />
                                                </svg>

                                                تعديل
                                            </Link>

                                            <button
                                                type="button"
                                                @click="
                                                    deleteService(service.id)
                                                "
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:border-red-200 hover:bg-red-100"
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
                                                        stroke-width="1.8"
                                                        d="M6 7h12M9 7V5a1 1 0 011-1h2a1 1 0 011 1v2m2 0v12a2 2 0 01-2 2H9a2 2 0 01-2-2V7m3 4v6m4-6v6"
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


                    <!-- Mobile Cards -->
                    <div
                        v-if="services.length > 0"
                        class="divide-y divide-gray-100 md:hidden"
                    >
                        <div
                            v-for="service in services"
                            :key="service.id"
                            class="p-5"
                        >

                            <div class="flex items-start justify-between gap-4">

                                <div class="flex min-w-0 items-center gap-3">

                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
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
                                                stroke-width="1.8"
                                                d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6"
                                            />
                                        </svg>
                                    </div>

                                    <div class="min-w-0">
                                        <p
                                            class="truncate font-semibold text-gray-900"
                                        >
                                            {{ service.name }}
                                        </p>

                                        <span
                                            :class="
                                                service.is_active
                                                    ? 'bg-emerald-50 text-emerald-700'
                                                    : 'bg-gray-100 text-gray-600'
                                            "
                                            class="mt-1.5 inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold"
                                        >
                                            {{
                                                service.is_active
                                                    ? 'نشطة'
                                                    : 'غير نشطة'
                                            }}
                                        </span>
                                    </div>

                                </div>

                                <div class="text-left">
                                    <p class="font-bold text-gray-900">
                                        {{ service.price }} $
                                    </p>
                                </div>

                            </div>


                            <div
                                class="mt-5 grid grid-cols-2 gap-3"
                            >

                                <div
                                    class="rounded-xl bg-gray-50 p-3"
                                >
                                    <p class="text-xs text-gray-400">
                                        المدة
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-semibold text-gray-700"
                                    >
                                        {{ service.duration_minutes }}
                                        دقيقة
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl bg-gray-50 p-3"
                                >
                                    <p class="text-xs text-gray-400">
                                        رقم الخدمة
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-semibold text-gray-700"
                                    >
                                        #{{ service.id }}
                                    </p>
                                </div>

                            </div>


                            <div class="mt-4 flex gap-2">

                                <Link
                                    :href="
                                        route(
                                            'services.edit',
                                            service.id
                                        )
                                    "
                                    class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-100"
                                >
                                    تعديل
                                </Link>

                                <button
                                    type="button"
                                    @click="deleteService(service.id)"
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