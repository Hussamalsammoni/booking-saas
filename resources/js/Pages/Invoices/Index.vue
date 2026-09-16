<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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

const paymentMethodLabel = (method) => {
    if (method === 'sham_cash') return 'شام كاش';
    if (method === 'card') return 'بطاقة';
    return 'نقداً';
};
</script>

<template>
    <Head title="الفواتير" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        الفواتير
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        إدارة ومتابعة جميع فواتير المحل
                    </p>
                </div>

                <div
                    class="hidden sm:flex items-center gap-2 bg-indigo-50 text-indigo-600 px-4 py-2.5 rounded-xl text-sm font-medium"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M9 14h6m-6 4h6M9 6h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"
                        />
                    </svg>

                    {{ invoices.length }} فاتورة
                </div>
            </div>
        </template>

        <div class="py-8 sm:py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Page intro -->
                <div class="mb-6">
                    <div
                        class="bg-gradient-to-l from-indigo-600 to-violet-600 rounded-2xl p-6 sm:p-7 text-white shadow-sm"
                    >
                        <div class="flex items-center justify-between gap-6">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <div
                                        class="w-11 h-11 rounded-xl bg-white/15 flex items-center justify-center"
                                    >
                                        <svg
                                            class="w-6 h-6"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.8"
                                                d="M9 14h6m-6 4h6M9 6h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"
                                            />
                                        </svg>
                                    </div>

                                    <h3 class="text-lg font-bold">
                                        إدارة الفواتير
                                    </h3>
                                </div>

                                <p class="text-indigo-100 text-sm max-w-xl">
                                    تابع حالة الدفع، تفاصيل الخدمات، وقم بإدارة
                                    الفواتير الخاصة بالحجوزات بسهولة.
                                </p>
                            </div>

                            <div class="hidden md:flex w-16 h-16 rounded-2xl bg-white/10 items-center justify-center">
                                <svg
                                    class="w-9 h-9 text-white/90"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M7 3h10a2 2 0 012 2v14l-3-2-4 2-4-2-3 2V5a2 2 0 012-2z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-if="invoices.length === 0"
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center"
                >
                    <div
                        class="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 flex items-center justify-center mb-5"
                    >
                        <svg
                            class="w-8 h-8 text-indigo-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M9 14h6m-6 4h6M9 6h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"
                            />
                        </svg>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800">
                        لا توجد فواتير بعد
                    </h3>

                    <p class="text-sm text-gray-500 mt-2">
                        الفواتير تُنشأ تلقائياً عند إتمام الحجوزات.
                    </p>
                </div>

                <!-- Invoices table -->
                <div
                    v-else
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                >
                    <!-- Table header -->
                    <div
                        class="px-6 py-5 border-b border-gray-100 flex items-center justify-between"
                    >
                        <div>
                            <h3 class="font-bold text-gray-900">
                                جميع الفواتير
                            </h3>
                            <p class="text-xs text-gray-400 mt-1">
                                سجل الفواتير والحالة المالية للحجوزات
                            </p>
                        </div>

                        <div
                            class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50 px-3 py-2 rounded-lg"
                        >
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            مدفوعة
                        </div>
                    </div>

                    <!-- Responsive table -->
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1050px] text-right">
                            <thead>
                                <tr class="bg-gray-50/70">
                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        رقم الفاتورة
                                    </th>

                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        الزبون
                                    </th>

                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        الخدمة
                                    </th>

                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        المبلغ
                                    </th>

                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        التاريخ
                                    </th>

                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        الحالة
                                    </th>

                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        طريقة الدفع
                                    </th>

                                    <th class="px-6 py-4 text-xs font-semibold text-gray-500">
                                        إجراءات
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="invoice in invoices"
                                    :key="invoice.id"
                                    class="group hover:bg-indigo-50/30 transition-colors duration-150"
                                >
                                    <!-- Invoice number -->
                                    <td class="px-6 py-4">
                                        <div
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-50 border border-gray-100"
                                            dir="ltr"
                                        >
                                            <span class="text-sm font-semibold text-gray-700">
                                                {{ invoice.invoice_number }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Customer -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0"
                                            >
                                                {{
                                                    invoice.booking.customer_name?.charAt(0)
                                                }}
                                            </div>

                                            <span class="font-medium text-gray-800">
                                                {{ invoice.booking.customer_name }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Service -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-8 h-8 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center"
                                            >
                                                <svg
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M12 6v12M6 12h12"
                                                    />
                                                </svg>
                                            </div>

                                            <span class="text-gray-600 text-sm">
                                                {{ invoice.booking.service?.name }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Amount -->
                                    <td class="px-6 py-4">
                                        <span
                                            class="font-bold text-gray-800"
                                            dir="ltr"
                                        >
                                            ${{ invoice.amount }}
                                        </span>
                                    </td>

                                    <!-- Date -->
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-500">
                                            {{ formatDate(invoice.created_at) }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4">
                                        <span
                                            v-if="invoice.status === 'paid'"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-green-50 text-green-700 text-xs font-medium"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            مدفوعة
                                        </span>

                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 text-xs font-medium"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            غير مدفوعة
                                        </span>
                                    </td>

                                    <!-- Payment -->
                                    <td class="px-6 py-4">
                                        <template v-if="invoice.status === 'paid'">
                                            <div class="text-sm text-gray-600">
                                                {{ paymentMethodLabel(invoice.payment_method) }}

                                                <div
                                                    v-if="invoice.payment_reference"
                                                    dir="ltr"
                                                    class="text-xs text-gray-400 mt-1"
                                                >
                                                    {{ invoice.payment_reference }}
                                                </div>
                                            </div>
                                        </template>

                                        <span
                                            v-else
                                            class="text-gray-300 text-sm"
                                        >
                                            —
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">

                                            <a
                                                :href="route('invoices.download', invoice.id)"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 text-xs font-medium transition"
                                            >
                                                <svg
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M12 3v12m0 0l4-4m-4 4l-4-4M5 21h14"
                                                    />
                                                </svg>

                                                PDF
                                            </a>

                                            <button
                                                @click="openEdit(invoice)"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-xs font-medium transition"
                                            >
                                                <svg
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M15.232 5.232l3.536 3.536M4 20h4l10.5-10.5a2.5 2.5 0 00-3.536-3.536L4.464 16.464A2.5 2.5 0 004 18.232V20z"
                                                    />
                                                </svg>

                                                تعديل
                                            </button>

                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom info -->
                    <div
                        class="px-6 py-4 border-t border-gray-100 bg-gray-50/50"
                    >
                        <p class="text-xs text-gray-400">
                            إجمالي الفواتير: {{ invoices.length }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div
            v-if="editingInvoice"
            class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            @click.self="closeEdit"
        >
            <div
                class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
            >
                <!-- Modal header -->
                <div
                    class="px-6 py-5 border-b border-gray-100 flex items-center justify-between"
                >
                    <div>
                        <h3 class="font-bold text-lg text-gray-900">
                            تعديل الفاتورة
                        </h3>

                        <p
                            class="text-xs text-gray-400 mt-1"
                            dir="ltr"
                        >
                            {{ editingInvoice.invoice_number }}
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeEdit"
                        class="w-9 h-9 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-500 flex items-center justify-center transition"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M6 6l12 12M18 6L6 18"
                            />
                        </svg>
                    </button>
                </div>

                <form
                    @submit.prevent="saveInvoice"
                    class="p-6 space-y-5"
                >
                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            المبلغ ($)
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            v-model="form.amount"
                            class="w-full border-gray-200 bg-gray-50 rounded-xl px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 transition"
                            required
                        />
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            حالة الدفع
                        </label>

                        <select
                            v-model="form.status"
                            class="w-full border-gray-200 bg-gray-50 rounded-xl px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 transition"
                        >
                            <option value="unpaid">غير مدفوعة</option>
                            <option value="paid">مدفوعة</option>
                        </select>
                    </div>

                    <!-- Payment method -->
                    <div v-if="form.status === 'paid'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            طريقة الدفع
                        </label>

                        <select
                            v-model="form.payment_method"
                            class="w-full border-gray-200 bg-gray-50 rounded-xl px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 transition"
                        >
                            <option value="cash">نقداً</option>
                            <option value="card">بطاقة</option>
                            <option value="sham_cash">شام كاش</option>
                        </select>
                    </div>

                    <!-- Sham Cash reference -->
                    <div
                        v-if="form.status === 'paid' && form.payment_method === 'sham_cash'"
                    >
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            رقم عملية شام كاش
                            <span class="text-gray-400 font-normal">
                                (اختياري)
                            </span>
                        </label>

                        <input
                            type="text"
                            v-model="form.payment_reference"
                            placeholder="مثال: 123456789"
                            class="w-full border-gray-200 bg-gray-50 rounded-xl px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500 transition"
                            dir="ltr"
                        />

                        <div
                            class="mt-2 flex items-start gap-2 text-xs text-gray-400"
                        >
                            <svg
                                class="w-4 h-4 shrink-0 mt-0.5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"
                                />
                            </svg>

                            <span>
                                تأكد من رقم العملية يدوياً قبل تأشير الفاتورة كمدفوعة.
                            </span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="pt-5 border-t border-gray-100 flex items-center justify-between gap-3"
                    >
                        <button
                            type="button"
                            @click="deleteInvoice"
                            class="text-red-500 hover:text-red-600 text-sm font-medium transition"
                        >
                            إلغاء الفاتورة
                        </button>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="closeEdit"
                                class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 text-sm font-medium transition"
                            >
                                إغلاق
                            </button>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50 text-sm font-medium shadow-sm transition"
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