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
    return new Date(value).toLocaleDateString('ar', { day: 'numeric', month: 'short', year: 'numeric' });
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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">الفواتير</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div v-if="invoices.length === 0" class="text-gray-500 text-center py-8">
                        لا توجد فواتير بعد. الفواتير تُنشأ تلقائياً عند إتمام الحجوزات.
                    </div>

                    <table v-else class="w-full text-right">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="pb-3 text-sm font-medium text-gray-500">رقم الفاتورة</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">الزبون</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">الخدمة</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">المبلغ</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">التاريخ</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">الحالة</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">طريقة الدفع</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="invoice in invoices" :key="invoice.id" class="border-b border-gray-100">
                                <td class="py-3 font-medium text-gray-800" dir="ltr" style="text-align: right;">{{ invoice.invoice_number }}</td>
                                <td class="py-3 text-gray-600">{{ invoice.booking.customer_name }}</td>
                                <td class="py-3 text-gray-600">{{ invoice.booking.service?.name }}</td>
                                <td class="py-3 text-gray-600" dir="ltr" style="text-align: right;">${{ invoice.amount }}</td>
                                <td class="py-3 text-gray-600">{{ formatDate(invoice.created_at) }}</td>
                                <td class="py-3">
                                    <span
                                        :class="invoice.status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                                        class="px-2 py-1 rounded-full text-xs"
                                    >
                                        {{ invoice.status === 'paid' ? 'مدفوعة' : 'غير مدفوعة' }}
                                    </span>
                                </td>
                                <td class="py-3 text-gray-500 text-xs">
                                    <span v-if="invoice.status === 'paid'">
                                        {{ paymentMethodLabel(invoice.payment_method) }}
                                        <span v-if="invoice.payment_reference" dir="ltr" class="text-gray-400">· {{ invoice.payment_reference }}</span>
                                    </span>
                                    <span v-else>-</span>
                                </td>
                                <td class="py-3 space-x-2 space-x-reverse">
    <a :href="route('invoices.download', invoice.id)" class="text-green-600 hover:underline text-sm">
        تحميل PDF
    </a>
    <button @click="openEdit(invoice)" class="text-indigo-600 hover:underline text-sm">
        تعديل
    </button>
</td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="editingInvoice" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50" @click.self="closeEdit">
            <div class="bg-white rounded-lg p-6 w-96">
                <h3 class="font-semibold text-gray-800 mb-1">تعديل الفاتورة</h3>
                <p class="text-sm text-gray-500 mb-4" dir="ltr" style="text-align: right;">{{ editingInvoice.invoice_number }}</p>

                <form @submit.prevent="saveInvoice" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ ($)</label>
                        <input type="number" step="0.01" v-model="form.amount" class="w-full border-gray-300 rounded-md shadow-sm" required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">حالة الدفع</label>
                        <select v-model="form.status" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="unpaid">غير مدفوعة</option>
                            <option value="paid">مدفوعة</option>
                        </select>
                    </div>

                    <div v-if="form.status === 'paid'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">طريقة الدفع</label>
                        <select v-model="form.payment_method" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="cash">نقداً</option>
                            <option value="card">بطاقة</option>
                            <option value="sham_cash">شام كاش</option>
                        </select>
                    </div>

                    <div v-if="form.status === 'paid' && form.payment_method === 'sham_cash'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">رقم عملية شام كاش (اختياري)</label>
                        <input
                            type="text"
                            v-model="form.payment_reference"
                            placeholder="مثال: 123456789"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            dir="ltr"
                        />
                        <p class="text-xs text-gray-400 mt-1">تأكد من رقم العملية يدوياً قبل تأشير الفاتورة كمدفوعة</p>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <button type="button" @click="deleteInvoice" class="text-red-600 text-sm hover:underline">
                            إلغاء الفاتورة
                        </button>
                        <div class="flex gap-3">
                            <button type="button" @click="closeEdit" class="text-gray-600 text-sm">إغلاق</button>
                            <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">
                                حفظ
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>