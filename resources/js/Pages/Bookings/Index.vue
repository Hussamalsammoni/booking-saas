<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
    bookings: {
        type: Array,
        default: () => [],
    },
});

const statusLabels = {
    pending: 'قيد الانتظار',
    confirmed: 'مؤكد',
    cancelled: 'ملغى',
    completed: 'مكتمل',
};

const statusColors = {
    pending: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200',
    confirmed: 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200',
    cancelled: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',
    completed: 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200',
};

const formatDateTime = (value) => {
    if (!value) return '-';

    const date = new Date(value);

    return date.toLocaleString('ar-SY', {
        day: 'numeric',
        month: 'long',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const updateStatus = (booking, status) => {
    router.patch(
        route('bookings.updateStatus', booking.id),
        { status }
    );
};
</script>

<template>
    <Head title="الحجوزات" />

    <AuthenticatedLayout>

        <!-- Header -->
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        إدارة الحجوزات
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        متابعة وإدارة جميع مواعيد العملاء
                    </p>
                </div>

                <div
                    class="hidden sm:flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <rect
                            x="3"
                            y="4"
                            width="18"
                            height="18"
                            rx="2"
                            stroke-width="2"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-width="2"
                            d="M16 2v4M8 2v4M3 10h18"
                        />
                    </svg>
                </div>
            </div>
        </template>


        <div class="min-h-screen bg-gray-50 py-8">

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Top section -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">

                    <!-- Total -->
                    <div
                        class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    إجمالي الحجوزات
                                </p>

                                <p class="mt-2 text-3xl font-bold text-gray-900">
                                    {{ bookings.length }}
                                </p>
                            </div>

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <rect
                                        x="3"
                                        y="4"
                                        width="18"
                                        height="18"
                                        rx="2"
                                        stroke-width="2"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-width="2"
                                        d="M16 2v4M8 2v4M3 10h18"
                                    />
                                </svg>
                            </div>

                        </div>
                    </div>


                    <!-- Pending -->
                    <div
                        class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    بانتظار التأكيد
                                </p>

                                <p class="mt-2 text-3xl font-bold text-gray-900">
                                    {{
                                        bookings.filter(
                                            booking => booking.status === 'pending'
                                        ).length
                                    }}
                                </p>
                            </div>

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="9"
                                        stroke-width="2"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-width="2"
                                        d="M12 7v5l3 2"
                                    />
                                </svg>
                            </div>

                        </div>
                    </div>


                    <!-- Confirmed -->
                    <div
                        class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    الحجوزات المؤكدة
                                </p>

                                <p class="mt-2 text-3xl font-bold text-gray-900">
                                    {{
                                        bookings.filter(
                                            booking => booking.status === 'confirmed'
                                        ).length
                                    }}
                                </p>
                            </div>

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
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
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                            </div>

                        </div>
                    </div>

                </div>


                <!-- Main card -->
                <section
                    class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm"
                >

                    <!-- Card header -->
                    <div
                        class="flex flex-col gap-3 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">
                                جميع الحجوزات
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                عرض وإدارة مواعيد العملاء وحالات الحجوزات
                            </p>
                        </div>

                        <div
                            class="flex items-center gap-2 rounded-xl bg-gray-50 px-3 py-2 text-sm text-gray-500"
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
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>

                            <span>
                                {{ bookings.length }} حجز
                            </span>
                        </div>
                    </div>


                    <!-- Empty -->
                    <div
                        v-if="bookings.length === 0"
                        class="flex flex-col items-center justify-center px-6 py-20 text-center"
                    >
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50 text-indigo-500"
                        >
                            <svg
                                class="h-8 w-8"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <rect
                                    x="3"
                                    y="4"
                                    width="18"
                                    height="18"
                                    rx="2"
                                    stroke-width="2"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-width="2"
                                    d="M16 2v4M8 2v4M3 10h18"
                                />
                            </svg>
                        </div>

                        <h4 class="mt-5 text-base font-semibold text-gray-900">
                            لا توجد حجوزات حالياً
                        </h4>

                        <p class="mt-1 max-w-sm text-sm text-gray-500">
                            عندما يقوم أحد العملاء بحجز موعد، سيظهر الحجز هنا.
                        </p>
                    </div>


                    <!-- Desktop table -->
                    <div
                        v-else
                        class="hidden overflow-x-auto md:block"
                    >
                        <table class="w-full text-right">

                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/70">

                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        الزبون
                                    </th>

                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        الهاتف
                                    </th>

                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        الخدمة
                                    </th>

                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        الموظف
                                    </th>

                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        الموعد
                                    </th>

                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        الحالة
                                    </th>

                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        إجراءات
                                    </th>

                                </tr>
                            </thead>


                            <tbody class="divide-y divide-gray-100">

                                <tr
                                    v-for="booking in bookings"
                                    :key="booking.id"
                                    class="group transition hover:bg-gray-50/70"
                                >

                                    <!-- Customer -->
                                    <td class="whitespace-nowrap px-6 py-4">

                                        <div class="flex items-center gap-3">

                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-sm font-bold text-indigo-600"
                                            >
                                                {{
                                                    (booking.customer_name || 'ع')
                                                        .charAt(0)
                                                        .toUpperCase()
                                                }}
                                            </div>

                                            <div>
                                                <p class="font-semibold text-gray-900">
                                                    {{ booking.customer_name || 'عميل' }}
                                                </p>

                                                <p class="mt-0.5 text-xs text-gray-400">
                                                    #{{ booking.id }}
                                                </p>
                                            </div>

                                        </div>

                                    </td>


                                    <!-- Phone -->
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="text-sm text-gray-600">
                                            {{ booking.customer_phone || '-' }}
                                        </span>
                                    </td>


                                    <!-- Service -->
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="text-sm font-medium text-gray-800">
                                            {{ booking.service?.name || 'غير محدد' }}
                                        </span>
                                    </td>


                                    <!-- Staff -->
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="text-sm text-gray-600">
                                            {{ booking.staff?.user?.name || 'غير محدد' }}
                                        </span>
                                    </td>


                                    <!-- Date -->
                                    <td class="whitespace-nowrap px-6 py-4">

                                        <div class="flex items-center gap-2">

                                            <div
                                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                                            >
                                                <svg
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <circle
                                                        cx="12"
                                                        cy="12"
                                                        r="9"
                                                        stroke-width="2"
                                                    />

                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-width="2"
                                                        d="M12 7v5l3 2"
                                                    />
                                                </svg>
                                            </div>

                                            <span class="text-sm text-gray-600">
                                                {{ formatDateTime(booking.start_time) }}
                                            </span>

                                        </div>

                                    </td>


                                    <!-- Status -->
                                    <td class="whitespace-nowrap px-6 py-4">

                                        <span
                                            :class="statusColors[booking.status]"
                                            class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold"
                                        >
                                            <span
                                                class="ml-1.5 h-1.5 w-1.5 rounded-full bg-current"
                                            ></span>

                                            {{ statusLabels[booking.status] || 'غير محدد' }}
                                        </span>

                                    </td>


                                    <!-- Actions -->
                                    <td class="whitespace-nowrap px-6 py-4">

                                        <div class="flex items-center gap-2">

                                            <!-- Confirm -->
                                            <button
                                                v-if="booking.status === 'pending'"
                                                type="button"
                                                @click="updateStatus(booking, 'confirmed')"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
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
                                                        d="M5 13l4 4L19 7"
                                                    />
                                                </svg>

                                                تأكيد
                                            </button>


                                            <!-- Complete -->
                                            <button
                                                v-if="booking.status === 'confirmed'"
                                                type="button"
                                                @click="updateStatus(booking, 'completed')"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100"
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
                                                        d="M5 13l4 4L19 7"
                                                    />
                                                </svg>

                                                إتمام
                                            </button>


                                            <!-- Cancel -->
                                            <button
                                                v-if="
                                                    booking.status !== 'cancelled' &&
                                                    booking.status !== 'completed'
                                                "
                                                type="button"
                                                @click="updateStatus(booking, 'cancelled')"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100"
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
                                                        d="M6 18L18 6M6 6l12 12"
                                                    />
                                                </svg>

                                                إلغاء
                                            </button>

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>
                    </div>


                    <!-- Mobile cards -->
                    <div
                        v-if="bookings.length > 0"
                        class="divide-y divide-gray-100 md:hidden"
                    >

                        <div
                            v-for="booking in bookings"
                            :key="booking.id"
                            class="p-5"
                        >

                            <!-- Customer -->
                            <div class="flex items-start justify-between gap-4">

                                <div class="flex min-w-0 items-center gap-3">

                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-50 font-bold text-indigo-600"
                                    >
                                        {{
                                            (booking.customer_name || 'ع')
                                                .charAt(0)
                                                .toUpperCase()
                                        }}
                                    </div>

                                    <div class="min-w-0">
                                        <h4
                                            class="truncate font-semibold text-gray-900"
                                        >
                                            {{ booking.customer_name || 'عميل' }}
                                        </h4>

                                        <p class="mt-0.5 text-xs text-gray-400">
                                            #{{ booking.id }}
                                        </p>
                                    </div>

                                </div>

                                <span
                                    :class="statusColors[booking.status]"
                                    class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold"
                                >
                                    {{ statusLabels[booking.status] || 'غير محدد' }}
                                </span>

                            </div>


                            <!-- Details -->
                            <div class="mt-5 grid grid-cols-2 gap-3">

                                <div class="rounded-xl bg-gray-50 p-3">
                                    <p class="text-xs text-gray-400">
                                        الخدمة
                                    </p>

                                    <p class="mt-1 text-sm font-medium text-gray-800">
                                        {{ booking.service?.name || 'غير محدد' }}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-gray-50 p-3">
                                    <p class="text-xs text-gray-400">
                                        الموظف
                                    </p>

                                    <p class="mt-1 text-sm font-medium text-gray-800">
                                        {{ booking.staff?.user?.name || 'غير محدد' }}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-gray-50 p-3">
                                    <p class="text-xs text-gray-400">
                                        الهاتف
                                    </p>

                                    <p class="mt-1 text-sm font-medium text-gray-800">
                                        {{ booking.customer_phone || '-' }}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-indigo-50 p-3">
                                    <p class="text-xs text-indigo-400">
                                        الموعد
                                    </p>

                                    <p class="mt-1 text-sm font-medium text-indigo-700">
                                        {{ formatDateTime(booking.start_time) }}
                                    </p>
                                </div>

                            </div>


                            <!-- Actions -->
                            <div class="mt-4 flex flex-wrap gap-2">

                                <button
                                    v-if="booking.status === 'pending'"
                                    type="button"
                                    @click="updateStatus(booking, 'confirmed')"
                                    class="flex-1 rounded-xl bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                >
                                    تأكيد الحجز
                                </button>

                                <button
                                    v-if="booking.status === 'confirmed'"
                                    type="button"
                                    @click="updateStatus(booking, 'completed')"
                                    class="flex-1 rounded-xl bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-100"
                                >
                                    إتمام الحجز
                                </button>

                                <button
                                    v-if="
                                        booking.status !== 'cancelled' &&
                                        booking.status !== 'completed'
                                    "
                                    type="button"
                                    @click="updateStatus(booking, 'cancelled')"
                                    class="rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                                >
                                    إلغاء
                                </button>

                            </div>

                        </div>

                    </div>

                </section>

            </div>
        </div>

    </AuthenticatedLayout>
</template>