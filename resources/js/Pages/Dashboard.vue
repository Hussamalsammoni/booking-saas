<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    stats: Object,
    upcomingBookings: Array,
});

const copied = ref(false);
const bookingUrl = window.location.origin + '/book';

const copyLink = () => {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(bookingUrl);
    } else {
        const textArea = document.createElement('textarea');
        textArea.value = bookingUrl;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
    }

    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
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
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">لوحة التحكم</h2>
        </template>

       <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Booking Link -->
        <div class="bg-indigo-50 border border-indigo-200 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <div class="text-sm font-medium text-indigo-900">رابط صفحة الحجز الخاصة بمحلك</div>
                    <div class="text-sm text-indigo-700 mt-1 break-all">{{ bookingUrl }}</div>
                </div>
                <button
                    @click="copyLink"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 whitespace-nowrap"
                >
                    {{ copied ? 'تم النسخ! ✓' : 'نسخ الرابط' }}
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm text-gray-500">حجوزات اليوم</div>
                        <div class="text-3xl font-bold text-indigo-600 mt-2">{{ stats.todayBookings }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm text-gray-500">عدد الخدمات</div>
                        <div class="text-3xl font-bold text-green-600 mt-2">{{ stats.totalServices }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm text-gray-500">عدد الموظفين</div>
                        <div class="text-3xl font-bold text-orange-600 mt-2">{{ stats.totalStaff }}</div>
                    </div>
                </div>

                <!-- Upcoming Bookings -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">أقرب الحجوزات</h3>

                    <div v-if="upcomingBookings.length === 0" class="text-gray-500 text-sm">
                        لا توجد حجوزات قادمة حالياً.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="booking in upcomingBookings"
                            :key="booking.id"
                            class="flex items-center justify-between border-b border-gray-100 pb-3 last:border-0"
                        >
                            <div>
                                <div class="font-medium text-gray-800">{{ booking.customer?.name }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ booking.service?.name }} — مع {{ booking.staff?.user?.name }}
                                </div>
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ formatDateTime(booking.start_time) }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>