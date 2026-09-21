<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SimpleDatePicker from '@/Components/SimpleDatePicker.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    bookings: {
        type: Array,
        default: () => [],
    },
    services: {
        type: Array,
        default: () => [],
    },
    staffList: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user || {});
const isOwnerView = computed(() => authUser.value.role === 'owner');

const statusLabels = {
    pending: 'قيد الانتظار',
    confirmed: 'مؤكد',
    cancelled: 'ملغى',
    completed: 'مكتمل',
};

const statusColors = {
    pending: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200',
    confirmed: 'bg-violet-50 text-violet-700 ring-1 ring-inset ring-violet-200',
    cancelled: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',
    completed: 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200',
};

const activeFilter = ref('all');

const filters = [
    { key: 'all', label: 'الكل' },
    { key: 'pending', label: 'قيد الانتظار' },
    { key: 'confirmed', label: 'مؤكدة' },
    { key: 'completed', label: 'مكتملة' },
    { key: 'cancelled', label: 'ملغاة' },
];

// ===== Time filter (custom range only) =====
const dateFrom = ref('');
const dateTo = ref('');

const isValidIsoDate = (value) => /^\d{4}-\d{2}-\d{2}$/.test(value);

const isWithinTimeFilter = (booking) => {
    const validFrom = isValidIsoDate(dateFrom.value) ? dateFrom.value : '';
    const validTo = isValidIsoDate(dateTo.value) ? dateTo.value : '';

    if (!validFrom && !validTo) {
        return true;
    }

    if (!booking.start_time) {
        return false;
    }

    const bookingDate = new Date(booking.start_time);

    if (validFrom) {
        const from = new Date(validFrom);
        from.setHours(0, 0, 0, 0);

        if (bookingDate < from) {
            return false;
        }
    }

    if (validTo) {
        const to = new Date(validTo);
        to.setHours(23, 59, 59, 999);

        if (bookingDate > to) {
            return false;
        }
    }

    return true;
};

const clearDateFilter = () => {
    dateFrom.value = '';
    dateTo.value = '';
};

const filteredBookings = computed(() => {
    return props.bookings.filter((booking) => {
        const matchesStatus =
            activeFilter.value === 'all' || booking.status === activeFilter.value;

        return matchesStatus && isWithinTimeFilter(booking);
    });
});

const countByStatus = (status) => {
    return props.bookings.filter((booking) => booking.status === status).length;
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

const hasBookings = computed(() => filteredBookings.value.length > 0);

// ===== Manual (walk-in) booking modal =====
const showAddModal = ref(false);

const addForm = useForm({
    customer_name: '',
    customer_phone: '',
    service_id: '',
    staff_id: '',
    date: '',
    time: '',
    status: 'confirmed',
});

const openAddModal = () => {
    addForm.reset();
    addForm.clearErrors();
    addForm.status = 'confirmed';

    // إذا كان المستخدم موظف بدون صلاحية عرض الكل، يحدد نفسه تلقائياً
    if (!isOwnerView.value) {
        const selfStaff = props.staffList.find(
            (s) => s.user_id === authUser.value.id
        );

        if (selfStaff) {
            addForm.staff_id = selfStaff.id;
        }
    }

    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
};

const submitAddForm = () => {
    if (!addForm.date || !addForm.time) {
        return;
    }

    addForm.transform((data) => ({
        ...data,
        start_time: `${data.date} ${data.time}:00`,
    })).post(route('bookings.store'), {
        preserveScroll: true,
        onSuccess: () => closeAddModal(),
    });
};

const staffOptionsForForm = computed(() => {
    if (isOwnerView.value) {
        return props.staffList;
    }

    return props.staffList.filter((s) => s.user_id === authUser.value.id);
});
</script>

<template>
    <Head title="الحجوزات" />

    <AuthenticatedLayout>

        <!-- Header -->
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        إدارة الحجوزات
                    </h2>

                    <p class="mt-1.5 text-sm text-gray-500">
                        متابعة وإدارة جميع مواعيد العملاء
                    </p>
                </div>

                <button
                    type="button"
                    @click="openAddModal"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 hover:shadow-md active:scale-[0.98]"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>

                    إضافة حجز يدوي
                </button>
            </div>
        </template>


        <div class="min-h-screen bg-[#f8fafc]">

            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

                <!-- =========================
                     Statistics Grid
                ========================== -->
                <section class="mb-8">

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

                        <!-- Total -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-indigo-100 hover:shadow-lg hover:shadow-indigo-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        إجمالي الحجوزات
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ bookings.length }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            حجز
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white"
                                >
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.8" />
                                        <path stroke-linecap="round" stroke-width="1.8" d="M16 2v4M8 2v4M3 10h18" />
                                    </svg>
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                جميع الحجوزات المسجلة
                            </div>
                        </div>


                        <!-- Pending -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-amber-100 hover:shadow-lg hover:shadow-amber-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        بانتظار التأكيد
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ countByStatus('pending') }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            حجز
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 transition group-hover:bg-amber-500 group-hover:text-white"
                                >
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="9" stroke-width="1.8" />
                                        <path stroke-linecap="round" stroke-width="1.8" d="M12 7v5l3 2" />
                                    </svg>
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                تحتاج مراجعة وتأكيد
                            </div>
                        </div>


                        <!-- Confirmed -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-violet-100 hover:shadow-lg hover:shadow-violet-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        الحجوزات المؤكدة
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ countByStatus('confirmed') }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            حجز
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 transition group-hover:bg-violet-600 group-hover:text-white"
                                >
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
                                جاهزة لتنفيذ الموعد
                            </div>
                        </div>


                        <!-- Completed -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-emerald-100 hover:shadow-lg hover:shadow-emerald-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        الحجوزات المكتملة
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ countByStatus('completed') }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            حجز
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
                                تم إنجازها بنجاح
                            </div>
                        </div>

                    </div>
                </section>


                <!-- =========================
                     Bookings List
                ========================== -->
                <section
                    class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm"
                >

                    <!-- Section Header + Filters -->
                    <div
                        class="flex flex-col gap-5 border-b border-gray-100 px-6 py-6 sm:px-8"
                    >

                        <div class="flex items-center justify-between">

                            <div class="flex items-center gap-4">

                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.8" />
                                        <path stroke-linecap="round" stroke-width="1.8" d="M16 2v4M8 2v4M3 10h18" />
                                    </svg>
                                </div>

                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">
                                        جميع الحجوزات
                                    </h3>

                                    <p class="mt-1 text-sm text-gray-500">
                                        عرض وإدارة مواعيد العملاء وحالات الحجوزات
                                    </p>
                                </div>

                            </div>

                            <div class="flex flex-wrap items-center gap-2">

                                <!-- Date range picker -->
                                <div
                                    class="flex items-center gap-3 rounded-xl bg-gray-50 px-3 py-2 text-sm text-gray-500"
                                >
                                    <div class="flex items-center gap-1.5">
                                        <label class="text-xs font-medium text-gray-400">
                                            من
                                        </label>

                                        <SimpleDatePicker
                                            v-model="dateFrom"
                                            placeholder="أي تاريخ"
                                        />
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        <label class="text-xs font-medium text-gray-400">
                                            إلى
                                        </label>

                                        <SimpleDatePicker
                                            v-model="dateTo"
                                            placeholder="أي تاريخ"
                                        />
                                    </div>

                                    <button
                                        v-if="dateFrom || dateTo"
                                        type="button"
                                        @click="clearDateFilter"
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-200 hover:text-gray-600"
                                    >
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>


                                <!-- Count badge -->
                                <div
                                    class="hidden items-center gap-2 rounded-xl bg-gray-50 px-3 py-2 text-sm text-gray-500 sm:flex"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>

                                    <span>
                                        {{ filteredBookings.length }} حجز
                                    </span>
                                </div>

                            </div>

                        </div>


                        <!-- Filter tabs (status) -->
                        <div class="flex flex-wrap gap-2">

                            <button
                                v-for="filter in filters"
                                :key="filter.key"
                                type="button"
                                @click="activeFilter = filter.key"
                                class="rounded-xl px-4 py-2 text-xs font-semibold transition"
                                :class="
                                    activeFilter === filter.key
                                        ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20'
                                        : 'bg-gray-50 text-gray-500 hover:bg-gray-100'
                                "
                            >
                                {{ filter.label }}
                            </button>

                        </div>

                    </div>


                    <!-- Empty -->
                    <div
                        v-if="!hasBookings"
                        class="flex flex-col items-center justify-center px-6 py-20 text-center"
                    >
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-3xl bg-gray-50 text-gray-300"
                        >
                            <svg class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.6" />
                                <path stroke-linecap="round" stroke-width="1.6" d="M16 2v4M8 2v4M3 10h18" />
                            </svg>
                        </div>

                        <h4 class="mt-5 text-base font-bold text-gray-900">
                            لا توجد حجوزات
                        </h4>

                        <p class="mt-2 max-w-md text-sm leading-6 text-gray-500">
                            لا يوجد حجوزات مطابقة لهذا الفلتر حالياً.
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
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">الزبون</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">الهاتف</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">الخدمة</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">الموظف</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">الموعد</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">الحالة</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">إجراءات</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">

                                <tr
                                    v-for="booking in filteredBookings"
                                    :key="booking.id"
                                    class="group transition hover:bg-gray-50/70"
                                >

                                    <!-- Customer -->
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-100 text-sm font-bold text-indigo-600 ring-1 ring-indigo-100"
                                            >
                                                {{ (booking.customer_name || 'ع').charAt(0).toUpperCase() }}
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
                                        <div
                                            class="inline-flex items-center gap-2 rounded-xl border border-indigo-100 bg-indigo-50/70 px-3 py-2 text-xs font-semibold text-indigo-700"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="9" stroke-width="1.8" />
                                                <path stroke-linecap="round" stroke-width="1.8" d="M12 7v5l3 2" />
                                            </svg>

                                            {{ formatDateTime(booking.start_time) }}
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            :class="statusColors[booking.status]"
                                            class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold"
                                        >
                                            <span class="ml-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>
                                            {{ statusLabels[booking.status] || 'غير محدد' }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center gap-2">

                                            <button
                                                v-if="booking.status === 'pending'"
                                                type="button"
                                                @click="updateStatus(booking, 'confirmed')"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                تأكيد
                                            </button>

                                            <button
                                                v-if="booking.status === 'confirmed'"
                                                type="button"
                                                @click="updateStatus(booking, 'completed')"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                إتمام
                                            </button>

                                            <button
                                                v-if="booking.status !== 'cancelled' && booking.status !== 'completed'"
                                                type="button"
                                                @click="updateStatus(booking, 'cancelled')"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
                        v-if="hasBookings"
                        class="divide-y divide-gray-100 md:hidden"
                    >

                        <div
                            v-for="booking in filteredBookings"
                            :key="booking.id"
                            class="p-5"
                        >

                            <div class="flex items-start justify-between gap-4">

                                <div class="flex min-w-0 items-center gap-3">
                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-100 font-bold text-indigo-600 ring-1 ring-indigo-100"
                                    >
                                        {{ (booking.customer_name || 'ع').charAt(0).toUpperCase() }}
                                    </div>

                                    <div class="min-w-0">
                                        <h4 class="truncate font-semibold text-gray-900">
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


                            <div class="mt-5 grid grid-cols-2 gap-3">

                                <div class="rounded-xl bg-gray-50 p-3">
                                    <p class="text-xs text-gray-400">الخدمة</p>
                                    <p class="mt-1 text-sm font-medium text-gray-800">
                                        {{ booking.service?.name || 'غير محدد' }}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-gray-50 p-3">
                                    <p class="text-xs text-gray-400">الموظف</p>
                                    <p class="mt-1 text-sm font-medium text-gray-800">
                                        {{ booking.staff?.user?.name || 'غير محدد' }}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-gray-50 p-3">
                                    <p class="text-xs text-gray-400">الهاتف</p>
                                    <p class="mt-1 text-sm font-medium text-gray-800">
                                        {{ booking.customer_phone || '-' }}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-indigo-50 p-3">
                                    <p class="text-xs text-indigo-400">الموعد</p>
                                    <p class="mt-1 text-sm font-medium text-indigo-700">
                                        {{ formatDateTime(booking.start_time) }}
                                    </p>
                                </div>

                            </div>


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
                                    v-if="booking.status !== 'cancelled' && booking.status !== 'completed'"
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


        <!-- ===================================================== -->
        <!-- Manual Booking Modal -->
        <!-- ===================================================== -->
        <div
            v-if="showAddModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 p-4 backdrop-blur-sm"
            @click.self="closeAddModal"
        >
            <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">

                <!-- Modal header -->
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            إضافة حجز يدوي
                        </h3>

                        <p class="mt-1 text-xs text-gray-400">
                            لزبون حضر إلى المحل مباشرة
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeAddModal"
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-50 text-gray-500 transition hover:bg-gray-100"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>


                <form @submit.prevent="submitAddForm" class="max-h-[75vh] space-y-5 overflow-y-auto p-6">

                    <!-- Customer name -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            اسم الزبون
                        </label>

                        <input
                            type="text"
                            v-model="addForm.customer_name"
                            required
                            placeholder="مثال: أحمد محمد"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />

                        <p v-if="addForm.errors.customer_name" class="mt-1.5 text-xs text-red-600">
                            {{ addForm.errors.customer_name }}
                        </p>
                    </div>


                    <!-- Customer phone -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            رقم الهاتف
                        </label>

                        <input
                            type="text"
                            v-model="addForm.customer_phone"
                            required
                            dir="ltr"
                            placeholder="0912345678"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-left text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />

                        <p v-if="addForm.errors.customer_phone" class="mt-1.5 text-xs text-red-600">
                            {{ addForm.errors.customer_phone }}
                        </p>
                    </div>


                    <!-- Service -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            الخدمة
                        </label>

                        <select
                            v-model="addForm.service_id"
                            required
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="" disabled>اختر الخدمة</option>
                            <option v-for="service in services" :key="service.id" :value="service.id">
                                {{ service.name }} — {{ service.duration_minutes }} دقيقة
                            </option>
                        </select>

                        <p v-if="addForm.errors.service_id" class="mt-1.5 text-xs text-red-600">
                            {{ addForm.errors.service_id }}
                        </p>
                    </div>


                    <!-- Staff -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            الموظف
                        </label>

                        <!-- Owner (or staff with view-all): dropdown -->
                        <select
                            v-if="isOwnerView || staffOptionsForForm.length > 1"
                            v-model="addForm.staff_id"
                            required
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="" disabled>اختر الموظف</option>
                            <option v-for="s in staffOptionsForForm" :key="s.id" :value="s.id">
                                {{ s.user?.name }}
                            </option>
                        </select>

                        <!-- Staff without view-all: locked to self -->
                        <div
                            v-else
                            class="rounded-xl bg-indigo-50 px-4 py-3 text-sm font-medium text-indigo-700"
                        >
                            {{ staffOptionsForForm[0]?.user?.name || authUser.name }}
                        </div>

                        <p v-if="addForm.errors.staff_id" class="mt-1.5 text-xs text-red-600">
                            {{ addForm.errors.staff_id }}
                        </p>
                    </div>


                    <!-- Date + Time -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                التاريخ
                            </label>

                            <SimpleDatePicker
                                v-model="addForm.date"
                                placeholder="اختر تاريخ"
                            />

                            <p v-if="addForm.errors.date" class="mt-1.5 text-xs text-red-600">
                                {{ addForm.errors.date }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                الوقت
                            </label>

                            <input
                                type="time"
                                v-model="addForm.time"
                                dir="ltr"
                                required
                                class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />

                            <p v-if="addForm.errors.time" class="mt-1.5 text-xs text-red-600">
                                {{ addForm.errors.time }}
                            </p>
                        </div>
                    </div>

                    <p v-if="addForm.errors.start_time" class="text-xs text-red-600">
                        {{ addForm.errors.start_time }}
                    </p>


                    <!-- Status -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            حالة الحجز
                        </label>

                        <div class="grid grid-cols-3 gap-2">
                            <button
                                v-for="opt in [
                                    { key: 'pending', label: 'قيد الانتظار' },
                                    { key: 'confirmed', label: 'مؤكد' },
                                    { key: 'completed', label: 'مكتمل' },
                                ]"
                                :key="opt.key"
                                type="button"
                                @click="addForm.status = opt.key"
                                class="rounded-xl px-3 py-2.5 text-xs font-semibold transition"
                                :class="
                                    addForm.status === opt.key
                                        ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20'
                                        : 'bg-gray-50 text-gray-500 hover:bg-gray-100'
                                "
                            >
                                {{ opt.label }}
                            </button>
                        </div>

                        <p class="mt-2 text-xs text-gray-400">
                            اختيار "مكتمل" سينشئ فاتورة تلقائياً لهذا الحجز.
                        </p>
                    </div>


                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                        <button
                            type="button"
                            @click="closeAddModal"
                            class="rounded-xl px-4 py-2.5 text-sm font-medium text-gray-500 transition hover:bg-gray-100"
                        >
                            إلغاء
                        </button>

                        <button
                            type="submit"
                            :disabled="addForm.processing"
                            class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ addForm.processing ? 'جاري الحفظ...' : 'حفظ الحجز' }}
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </AuthenticatedLayout>
</template>