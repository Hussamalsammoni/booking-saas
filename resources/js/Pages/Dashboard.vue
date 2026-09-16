<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            todayBookings: 0,
            totalServices: 0,
            totalStaff: 0,
            completedBookings: 0,
            cancelledBookings: 0,
            confirmedBookings: 0,
            pendingBookings: 0,
            revenue: 0,
        }),
    },

    upcomingBookings: {
        type: Array,
        default: () => [],
    },
});

const copied = ref(false);

const bookingUrl = computed(() => {
    return `${window.location.origin}/book`;
});

const copyLink = async () => {
    try {
        if (navigator.clipboard) {
            await navigator.clipboard.writeText(bookingUrl.value);
        } else {
            const textArea = document.createElement('textarea');
            textArea.value = bookingUrl.value;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        }

        copied.value = true;

        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (error) {
        console.error('Failed to copy:', error);
    }
};

const formatDateTime = (value) => {
    if (!value) return '';

    const date = new Date(value);

    return date.toLocaleString('ar-SY', {
        day: 'numeric',
        month: 'long',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getCustomerName = (booking) => {
    return booking.customer?.name || 'عميل';
};

const getServiceName = (booking) => {
    return booking.service?.name || 'خدمة';
};

const getStaffName = (booking) => {
    return booking.staff?.user?.name || 'غير محدد';
};

const getStatusLabel = (status) => {
    const statuses = {
        pending: 'بانتظار التأكيد',
        confirmed: 'مؤكد',
        completed: 'مكتمل',
        cancelled: 'ملغي',
    };

    return statuses[status] || 'غير محدد';
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200',
        confirmed: 'bg-violet-50 text-violet-700 ring-1 ring-inset ring-violet-200',
        completed: 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200',
        cancelled: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',
    };

    return classes[status] || 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-200';
};

const formatPrice = (price) => {
    return Number(price || 0).toLocaleString('ar-SY');
};

const hasBookings = computed(() => {
    return props.upcomingBookings.length > 0;
});
</script>

<template>
    <Head title="لوحة التحكم" />

    <AuthenticatedLayout>

        <!-- Header -->
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        لوحة التحكم
                    </h2>

                    <p class="mt-1.5 text-sm text-gray-500">
                        نظرة سريعة على نشاط محلك وحجوزاتك
                    </p>
                </div>
            </div>
        </template>

        <div class="min-h-screen bg-[#f8fafc]">

            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

                <!-- =========================
                     Booking Link Banner
                ========================== -->
                <section
                    class="relative mb-8 overflow-hidden rounded-3xl bg-gradient-to-l from-indigo-700 via-indigo-600 to-violet-600 shadow-xl shadow-indigo-100"
                >
                    <!-- Decorative circles -->
                    <div
                        class="absolute -left-16 -top-16 h-48 w-48 rounded-full border border-white/10 bg-white/5"
                    ></div>

                    <div
                        class="absolute -bottom-24 right-20 h-64 w-64 rounded-full border border-white/10 bg-white/5"
                    ></div>

                    <div
                        class="absolute right-1/3 top-0 h-32 w-32 rounded-full bg-white/5 blur-2xl"
                    ></div>

                    <div
                        class="relative flex flex-col gap-7 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between"
                    >

                        <!-- Banner Content -->
                        <div class="min-w-0 text-white">

                            <div class="flex items-start gap-4">

                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-white/20 bg-white/10 backdrop-blur-sm"
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
                                            stroke-width="1.8"
                                            d="M13.828 10.172a4 4 0 010 5.656l-2 2a4 4 0 01-5.656-5.656l1-1m4.828-4.828a4 4 0 015.656 5.656l-2 2a4 4 0 01-5.656 0l-1-1"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <h3 class="text-xl font-bold">
                                        رابط الحجز الخاص بمحلك
                                    </h3>

                                    <p class="mt-1.5 max-w-2xl text-sm leading-6 text-indigo-100">
                                        شارك رابط الحجز مع زبائنك ليتمكنوا من اختيار الخدمة والموعد المناسب لهم بسهولة.
                                    </p>
                                </div>

                            </div>

                            <!-- URL -->
                            <div
                                class="mt-6 flex max-w-2xl items-center gap-3 rounded-2xl border border-white/10 bg-black/10 px-4 py-3 backdrop-blur-sm"
                            >
                                <svg
                                    class="h-4 w-4 shrink-0 text-indigo-200"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M13.828 10.172a4 4 0 010 5.656l-2 2a4 4 0 01-5.656-5.656l1-1m4.828-4.828a4 4 0 015.656 5.656l-2 2a4 4 0 010-5.656l1-1"
                                    />
                                </svg>

                                <span
                                    class="min-w-0 break-all text-sm text-white/90"
                                >
                                    {{ bookingUrl }}
                                </span>
                            </div>

                        </div>

                        <!-- Copy Button -->
                        <button
                            type="button"
                            @click="copyLink"
                            class="inline-flex shrink-0 items-center justify-center gap-2.5 rounded-2xl bg-white px-6 py-3.5 text-sm font-bold text-indigo-700 shadow-lg shadow-indigo-900/10 transition duration-200 hover:-translate-y-0.5 hover:bg-indigo-50 active:translate-y-0"
                        >
                            <svg
                                v-if="!copied"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-4 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                />
                            </svg>

                            <svg
                                v-else
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

                            {{ copied ? 'تم نسخ الرابط' : 'نسخ رابط الحجز' }}
                        </button>

                    </div>
                </section>


                <!-- =========================
                     Statistics Header
                ========================== -->
                <section class="mb-8">

                    <div class="mb-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-8 w-1 rounded-full bg-indigo-600"
                            ></div>

                            <div>
                                <h3 class="text-xl font-bold text-gray-900">
                                    ملخص النشاط
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    نظرة سريعة على أداء محلك
                                </p>
                            </div>
                        </div>
                    </div>


                    <!-- Statistics Grid -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

                        <!-- Today -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-indigo-100 hover:shadow-lg hover:shadow-indigo-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        حجوزات اليوم
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ stats.todayBookings }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            حجز
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white"
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
                                            stroke-width="1.8"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-width="1.8"
                                            d="M16 2v4M8 2v4M3 10h18"
                                        />
                                    </svg>
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                إجمالي حجوزات اليوم
                            </div>
                        </div>


                        <!-- Services -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-emerald-100 hover:shadow-lg hover:shadow-emerald-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        الخدمات المتوفرة
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ stats.totalServices }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            خدمة
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white"
                                >
                                    <svg
                                        class="h-6 w-6"
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
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                الخدمات المضافة إلى المحل
                            </div>
                        </div>


                        <!-- Staff -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-orange-100 hover:shadow-lg hover:shadow-orange-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        فريق العمل
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ stats.totalStaff }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            موظف
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-50 text-orange-600 transition group-hover:bg-orange-500 group-hover:text-white"
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
                                            stroke-width="1.8"
                                            d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"
                                        />
                                    </svg>
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                                عدد الموظفين المسجلين
                            </div>
                        </div>


                        <!-- Revenue -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-emerald-100 hover:shadow-lg hover:shadow-emerald-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        الإيرادات
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ Number(stats.revenue || 0).toLocaleString('ar-SY') }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            ل.س
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white"
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
                                            stroke-width="1.8"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10v1m0 10v1m0-12a9 9 0 100 18 9 9 0 000-18z"
                                        />
                                    </svg>
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                إجمالي الحجوزات المكتملة
                            </div>
                        </div>


                        <!-- Completed -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-blue-100 hover:shadow-lg hover:shadow-blue-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        الحجوزات المكتملة
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ stats.completedBookings }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            حجز
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition group-hover:bg-blue-600 group-hover:text-white"
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
                                            stroke-width="1.8"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                الحجوزات التي تم إنجازها
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
                                            {{ stats.confirmedBookings }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            حجز
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 transition group-hover:bg-violet-600 group-hover:text-white"
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
                                            stroke-width="1.8"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
                                المواعيد التي تم تأكيدها
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
                                            {{ stats.pendingBookings }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            حجز
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 transition group-hover:bg-amber-500 group-hover:text-white"
                                >
                                    <svg
                                        class="h-6 w-6"
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
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                الحجوزات التي تحتاج إلى تأكيد
                            </div>
                        </div>


                        <!-- Cancelled -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-red-100 hover:shadow-lg hover:shadow-red-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        الحجوزات الملغاة
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ stats.cancelledBookings }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            حجز
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600 transition group-hover:bg-red-600 group-hover:text-white"
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
                                            stroke-width="1.8"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                إجمالي الحجوزات الملغاة
                            </div>
                        </div>

                    </div>
                </section>


                <!-- =========================
                     Upcoming Bookings
                ========================== -->
                <section
                    class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm"
                >

                    <!-- Section Header -->
                    <div
                        class="flex items-center justify-between border-b border-gray-100 px-6 py-6 sm:px-8"
                    >
                        <div class="flex items-center gap-4">

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600"
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
                                        stroke-width="1.8"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-width="1.8"
                                        d="M12 7v5l3 2"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">
                                    أقرب الحجوزات
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    المواعيد القادمة حسب أقرب وقت
                                </p>
                            </div>

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
                            <svg
                                class="h-9 w-9"
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
                                    stroke-width="1.6"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-width="1.6"
                                    d="M16 2v4M8 2v4M3 10h18"
                                />
                            </svg>
                        </div>

                        <h4 class="mt-5 text-base font-bold text-gray-900">
                            لا توجد حجوزات قادمة
                        </h4>

                        <p class="mt-2 max-w-md text-sm leading-6 text-gray-500">
                            عندما يقوم أحد العملاء بحجز موعد جديد ستظهر تفاصيل الحجز هنا.
                        </p>
                    </div>


                    <!-- Bookings -->
                    <div v-else>

                        <div
                            v-for="(booking, index) in upcomingBookings"
                            :key="booking.id"
                            class="group flex flex-col gap-5 px-6 py-5 transition duration-200 hover:bg-gray-50/80 sm:flex-row sm:items-center sm:justify-between sm:px-8"
                            :class="{
                                'border-b border-gray-100':
                                    index !== upcomingBookings.length - 1,
                            }"
                        >

                            <!-- Customer -->
                            <div class="flex min-w-0 items-center gap-4">

                                <!-- Avatar -->
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-100 font-bold text-indigo-600 ring-1 ring-indigo-100"
                                >
                                    {{
                                        getCustomerName(booking)
                                            .charAt(0)
                                            .toUpperCase()
                                    }}
                                </div>

                                <div class="min-w-0">

                                    <h4
                                        class="truncate text-sm font-bold text-gray-900 sm:text-base"
                                    >
                                        {{ getCustomerName(booking) }}
                                    </h4>

                                    <div
                                        class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500 sm:text-sm"
                                    >
                                        <span>
                                            {{ getServiceName(booking) }}
                                        </span>

                                        <span class="text-gray-300">
                                            •
                                        </span>

                                        <span>
                                            مع {{ getStaffName(booking) }}
                                        </span>

                                        <span class="text-gray-300">
                                            •
                                        </span>

                                        <span class="font-medium text-gray-700">
                                            {{ formatPrice(booking.service?.price) }}
                                            ل.س
                                        </span>
                                    </div>

                                </div>
                            </div>


                            <!-- Status + Date -->
                            <div
                                class="flex flex-wrap items-center gap-2.5 self-start sm:self-auto"
                            >

                                <!-- Status -->
                                <span
                                    class="rounded-full px-3.5 py-1.5 text-xs font-semibold"
                                    :class="getStatusClass(booking.status)"
                                >
                                    {{ getStatusLabel(booking.status) }}
                                </span>


                                <!-- Date -->
                                <div
                                    class="flex shrink-0 items-center gap-2 rounded-xl border border-indigo-100 bg-indigo-50/70 px-4 py-2.5 text-xs font-semibold text-indigo-700 sm:text-sm"
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
                                            stroke-width="1.8"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            stroke-width="1.8"
                                            d="M12 7v5l3 2"
                                        />
                                    </svg>

                                    {{ formatDateTime(booking.start_time) }}
                                </div>

                            </div>

                        </div>

                    </div>

                </section>

            </div>
        </div>

    </AuthenticatedLayout>
</template>