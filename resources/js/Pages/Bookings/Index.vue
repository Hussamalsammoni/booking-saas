<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
    bookings: Array,
});

const statusLabels = {
    pending: 'قيد الانتظار',
    confirmed: 'مؤكد',
    cancelled: 'ملغى',
    completed: 'مكتمل',
};

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-700',
    confirmed: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
    completed: 'bg-blue-100 text-blue-700',
};

const formatDateTime = (value) => {
    const date = new Date(value);
    return date.toLocaleString('ar', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const updateStatus = (booking, status) => {
    router.patch(route('bookings.updateStatus', booking.id), { status });
};
</script>

<template>
    <Head title="الحجوزات" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">إدارة الحجوزات</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div v-if="bookings.length === 0" class="text-gray-500 text-center py-8">
                        لا توجد حجوزات حالياً.
                    </div>

                    <table v-else class="w-full text-right">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="pb-3 text-sm font-medium text-gray-500">الزبون</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">الهاتف</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">الخدمة</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">الموظف</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">الموعد</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">الحالة</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="booking in bookings" :key="booking.id" class="border-b border-gray-100">
                                <td class="py-3 font-medium text-gray-800">{{ booking.customer_name }}</td>
                                <td class="py-3 text-gray-600">{{ booking.customer_phone }}</td>
                                <td class="py-3 text-gray-600">{{ booking.service?.name }}</td>
                                <td class="py-3 text-gray-600">{{ booking.staff?.user?.name }}</td>
                                <td class="py-3 text-gray-600">{{ formatDateTime(booking.start_time) }}</td>
                                <td class="py-3">
                                    <span :class="statusColors[booking.status]" class="px-2 py-1 rounded-full text-xs">
                                        {{ statusLabels[booking.status] }}
                                    </span>
                                </td>
                                <td class="py-3 space-x-2 space-x-reverse">
                                    <button
                                        v-if="booking.status === 'pending'"
                                        @click="updateStatus(booking, 'confirmed')"
                                        class="text-green-600 hover:underline text-sm"
                                    >
                                        تأكيد
                                    </button>
                                    <button
                                        v-if="booking.status !== 'cancelled' && booking.status !== 'completed'"
                                        @click="updateStatus(booking, 'cancelled')"
                                        class="text-red-600 hover:underline text-sm"
                                    >
                                        إلغاء
                                    </button>
                                    <button
                                        v-if="booking.status === 'confirmed'"
                                        @click="updateStatus(booking, 'completed')"
                                        class="text-blue-600 hover:underline text-sm"
                                    >
                                        إتمام
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>