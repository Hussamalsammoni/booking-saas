<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    invoices: Array,
});

const editingInvoice = ref(null);

const form = useForm({
    amount: 0,
    status: 'unpaid',
    payment_method: 'cash',
    payment_reference: '',
});

const openEdit = (invoice) => {
    editingInvoice.value = invoice;
    form.amount = invoice.amount;
    form.status = invoice.status;
    form.payment_method = invoice.payment_method;
    form.payment_reference = invoice.payment_reference || '';
};

const closeEdit = () => {
    editingInvoice.value = null;
};

const saveInvoice = () => {
    form.patch(route('invoices.update', editingInvoice.value.id), {
        onSuccess: () => closeEdit(),
    });
};

const deleteInvoice = () => {
    if (confirm('هل أنت متأكد من إلغاء هذه الفاتورة؟ لا يمكن التراجع عن هذا الإجراء.')) {
        router.delete(route('invoices.destroy', editingInvoice.value.id), {
            onSuccess: () => closeEdit(),
        });
    }
};

const formatDate = (value) => {
    return new Date(value).toLocaleDateString('ar', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const formatAmount = (amount) => {
    return Number(amount || 0).toLocaleString('ar-SY');
};

const paymentMethodLabel = (method) => {
    if (method === 'sham_cash') return 'شام كاش';
    if (method === 'card') return 'بطاقة';
    return 'نقداً';
};

const paidCount = computed(() => props.invoices.filter(i => i.status === 'paid').length);
const unpaidCount = computed(() => props.invoices.filter(i => i.status === 'unpaid').length);

const totalPaidAmount = computed(() =>
    props.invoices
        .filter(i => i.status === 'paid')
        .reduce((sum, i) => sum + Number(i.amount || 0), 0)
);

const totalUnpaidAmount = computed(() =>
    props.invoices
        .filter(i => i.status === 'unpaid')
        .reduce((sum, i) => sum + Number(i.amount || 0), 0)
);
</script>

<template>
    <Head title="الفواتير" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                    الفواتير
                </h2>

                <p class="mt-1.5 text-sm text-gray-500">
                    إدارة ومتابعة جميع فواتير المحل
                </p>
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
                                        إجمالي الفواتير
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ invoices.length }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            فاتورة
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white"
                                >
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14h6m-6 4h6M9 6h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z" />
                                    </svg>
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                سجل الفواتير الكامل
                            </div>
                        </div>


                        <!-- Paid -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-emerald-100 hover:shadow-lg hover:shadow-emerald-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        فواتير مدفوعة
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ paidCount }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            فاتورة
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
                                {{ formatAmount(totalPaidAmount) }} ل.س محصّلة
                            </div>
                        </div>


                        <!-- Unpaid -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-amber-100 hover:shadow-lg hover:shadow-amber-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        فواتير غير مدفوعة
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                                            {{ unpaidCount }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            فاتورة
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
                                {{ formatAmount(totalUnpaidAmount) }} ل.س معلّقة
                            </div>
                        </div>


                        <!-- Total revenue -->
                        <div
                            class="group rounded-2xl border border-gray-200/80 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-violet-100 hover:shadow-lg hover:shadow-violet-100/40"
                        >
                            <div class="flex items-start justify-between">

                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        إجمالي القيمة
                                    </p>

                                    <div class="mt-3 flex items-baseline gap-2">
                                        <span class="text-2xl font-bold tracking-tight text-gray-900">
                                            {{ formatAmount(totalPaidAmount + totalUnpaidAmount) }}
                                        </span>

                                        <span class="text-sm text-gray-400">
                                            ل.س
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 transition group-hover:bg-violet-600 group-hover:text-white"
                                >
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10v1m0 10v1m0-12a9 9 0 100 18 9 9 0 000-18z" />
                                    </svg>
                                </div>

                            </div>

                            <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
                                مدفوعة وغير مدفوعة معاً
                            </div>
                        </div>

                    </div>
                </section>


                <!-- =========================
                     Invoices List
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14h6m-6 4h6M9 6h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">
                                    جميع الفواتير
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    سجل الفواتير والحالة المالية للحجوزات
                                </p>
                            </div>

                        </div>

                        <div
                            class="inline-flex w-fit items-center gap-2 rounded-xl bg-gray-50 px-3 py-2 text-xs font-medium text-gray-500"
                        >
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            {{ invoices.length }} فاتورة
                        </div>
                    </div>


                    <!-- Empty -->
                    <div
                        v-if="invoices.length === 0"
                        class="flex flex-col items-center justify-center px-6 py-20 text-center"
                    >
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-3xl bg-indigo-50 text-indigo-500"
                        >
                            <svg class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 14h6m-6 4h6M9 6h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z" />
                            </svg>
                        </div>

                        <h4 class="mt-5 text-lg font-bold text-gray-900">
                            لا توجد فواتير بعد
                        </h4>

                        <p class="mt-2 max-w-md text-sm leading-6 text-gray-500">
                            الفواتير تُنشأ تلقائياً عند إتمام الحجوزات.
                        </p>
                    </div>


                    <!-- Desktop Table -->
                    <div v-else class="hidden overflow-x-auto md:block">
                        <table class="w-full min-w-[1050px] text-right">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/70">
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">رقم الفاتورة</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">الزبون</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">الخدمة</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">المبلغ</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">التاريخ</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">الحالة</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">طريقة الدفع</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">إجراءات</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="invoice in invoices"
                                    :key="invoice.id"
                                    class="group transition hover:bg-gray-50/70"
                                >

                                    <!-- Invoice number -->
                                    <td class="px-6 py-5">
                                        <div
                                            class="inline-flex items-center rounded-lg border border-gray-100 bg-gray-50 px-3 py-1.5"
                                            dir="ltr"
                                        >
                                            <span class="text-sm font-semibold text-gray-700">
                                                {{ invoice.invoice_number }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Customer -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-50 to-violet-100 text-sm font-bold text-indigo-600 ring-1 ring-indigo-100"
                                            >
                                                {{ invoice.booking.customer_name?.charAt(0) }}
                                            </div>

                                            <span class="font-medium text-gray-800">
                                                {{ invoice.booking.customer_name }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Service -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50 text-violet-600"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12M6 12h12" />
                                                </svg>
                                            </div>

                                            <span class="text-sm text-gray-600">
                                                {{ invoice.booking.service?.name }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Amount -->
                                    <td class="px-6 py-5">
                                        <div class="inline-flex items-baseline gap-1.5">
                                            <span class="font-bold text-gray-900">
                                                {{ formatAmount(invoice.amount) }}
                                            </span>

                                            <span class="text-xs text-gray-400">
                                                ل.س
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Date -->
                                    <td class="px-6 py-5">
                                        <span class="text-sm text-gray-500">
                                            {{ formatDate(invoice.created_at) }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-5">
                                        <span
                                            v-if="invoice.status === 'paid'"
                                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200"
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            مدفوعة
                                        </span>

                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200"
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            غير مدفوعة
                                        </span>
                                    </td>

                                    <!-- Payment -->
                                    <td class="px-6 py-5">
                                        <template v-if="invoice.status === 'paid'">
                                            <div class="text-sm text-gray-600">
                                                {{ paymentMethodLabel(invoice.payment_method) }}

                                                <div
                                                    v-if="invoice.payment_reference"
                                                    dir="ltr"
                                                    class="mt-1 text-xs text-gray-400"
                                                >
                                                    {{ invoice.payment_reference }}
                                                </div>
                                            </div>
                                        </template>

                                        <span v-else class="text-sm text-gray-300">
                                            —
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">

                                            <a
                                                :href="route('invoices.download', invoice.id)"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:border-emerald-200 hover:bg-emerald-100"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0l4-4m-4 4l-4-4M5 21h14" />
                                                </svg>
                                                PDF
                                            </a>

                                            <button
                                                type="button"
                                                @click="openEdit(invoice)"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-600 transition hover:border-indigo-200 hover:bg-indigo-100"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>
                                                تعديل
                                            </button>

                                        </div>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>


                    <!-- Mobile Cards -->
                    <div v-if="invoices.length > 0" class="divide-y divide-gray-100 md:hidden">
                        <div
                            v-for="invoice in invoices"
                            :key="invoice.id"
                            class="p-5"
                        >

                            <div class="flex items-start justify-between gap-4">

                                <div class="flex min-w-0 items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-50 to-violet-100 text-sm font-bold text-indigo-600 ring-1 ring-indigo-100"
                                    >
                                        {{ invoice.booking.customer_name?.charAt(0) }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-gray-900">
                                            {{ invoice.booking.customer_name }}
                                        </p>

                                        <p class="mt-0.5 text-xs text-gray-400" dir="ltr">
                                            {{ invoice.invoice_number }}
                                        </p>
                                    </div>
                                </div>

                                <span
                                    v-if="invoice.status === 'paid'"
                                    class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"
                                >
                                    مدفوعة
                                </span>

                                <span
                                    v-else
                                    class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700"
                                >
                                    غير مدفوعة
                                </span>
                            </div>


                            <div class="mt-5 grid grid-cols-2 gap-3">

                                <div class="rounded-xl bg-gray-50 p-3">
                                    <p class="text-xs text-gray-400">الخدمة</p>
                                    <p class="mt-1 text-sm font-medium text-gray-800">
                                        {{ invoice.booking.service?.name || '—' }}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-indigo-50 p-3">
                                    <p class="text-xs text-indigo-400">المبلغ</p>
                                    <p class="mt-1 text-sm font-medium text-indigo-700">
                                        {{ formatAmount(invoice.amount) }} ل.س
                                    </p>
                                </div>

                                <div class="rounded-xl bg-gray-50 p-3">
                                    <p class="text-xs text-gray-400">التاريخ</p>
                                    <p class="mt-1 text-sm font-medium text-gray-800">
                                        {{ formatDate(invoice.created_at) }}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-gray-50 p-3">
                                    <p class="text-xs text-gray-400">طريقة الدفع</p>
                                    <p class="mt-1 text-sm font-medium text-gray-800">
                                        {{ invoice.status === 'paid' ? paymentMethodLabel(invoice.payment_method) : '—' }}
                                    </p>
                                </div>

                            </div>


                            <div class="mt-4 flex gap-2">

                                <a
                                    :href="route('invoices.download', invoice.id)"
                                    class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                >
                                    تحميل PDF
                                </a>

                                <button
                                    type="button"
                                    @click="openEdit(invoice)"
                                    class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-100"
                                >
                                    تعديل
                                </button>

                            </div>

                        </div>
                    </div>


                    <!-- Bottom info -->
                    <div class="border-t border-gray-100 bg-gray-50/50 px-6 py-4 sm:px-8">
                        <p class="text-xs text-gray-400">
                            إجمالي الفواتير: {{ invoices.length }}
                        </p>
                    </div>

                </section>

            </div>
        </div>


        <!-- ===================================================== -->
        <!-- Edit Modal -->
        <!-- ===================================================== -->
        <div
            v-if="editingInvoice"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 p-4 backdrop-blur-sm"
            @click.self="closeEdit"
        >
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">

                <!-- Modal header -->
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            تعديل الفاتورة
                        </h3>

                        <p class="mt-1 text-xs text-gray-400" dir="ltr">
                            {{ editingInvoice.invoice_number }}
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeEdit"
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-50 text-gray-500 transition hover:bg-gray-100"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveInvoice" class="space-y-5 p-6">

                    <!-- Amount -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            المبلغ (ل.س)
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            v-model="form.amount"
                            required
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            حالة الدفع
                        </label>

                        <div class="grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                @click="form.status = 'unpaid'"
                                class="rounded-xl px-4 py-3 text-sm font-semibold transition"
                                :class="
                                    form.status === 'unpaid'
                                        ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20'
                                        : 'bg-gray-50 text-gray-500 hover:bg-gray-100'
                                "
                            >
                                غير مدفوعة
                            </button>

                            <button
                                type="button"
                                @click="form.status = 'paid'"
                                class="rounded-xl px-4 py-3 text-sm font-semibold transition"
                                :class="
                                    form.status === 'paid'
                                        ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20'
                                        : 'bg-gray-50 text-gray-500 hover:bg-gray-100'
                                "
                            >
                                مدفوعة
                            </button>
                        </div>
                    </div>

                    <!-- Payment method -->
                    <div v-if="form.status === 'paid'">
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            طريقة الدفع
                        </label>

                        <select
                            v-model="form.payment_method"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="cash">نقداً</option>
                            <option value="card">بطاقة</option>
                            <option value="sham_cash">شام كاش</option>
                        </select>
                    </div>

                    <!-- Sham Cash reference -->
                    <div v-if="form.status === 'paid' && form.payment_method === 'sham_cash'">
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            رقم عملية شام كاش
                            <span class="font-normal text-gray-400">(اختياري)</span>
                        </label>

                        <input
                            type="text"
                            v-model="form.payment_reference"
                            placeholder="مثال: 123456789"
                            dir="ltr"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-left text-sm transition focus:border-indigo-500 focus:ring-indigo-500"
                        />

                        <div class="mt-2 flex items-start gap-2 text-xs text-gray-400">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z" />
                            </svg>

                            <span>
                                تأكد من رقم العملية يدوياً قبل تأشير الفاتورة كمدفوعة.
                            </span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between gap-3 border-t border-gray-100 pt-5">
                        <button
                            type="button"
                            @click="deleteInvoice"
                            class="text-sm font-medium text-red-500 transition hover:text-red-600"
                        >
                            إلغاء الفاتورة
                        </button>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="closeEdit"
                                class="rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-200"
                            >
                                إغلاق
                            </button>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'جاري الحفظ...' : 'حفظ التعديلات' }}
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>

    </AuthenticatedLayout>
</template>