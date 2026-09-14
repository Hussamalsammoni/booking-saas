<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    booking: Object,
});

// إعداد رابط التواصل المباشر عبر الواتساب مع نص مجهز
const whatsappUrl = `https://wa.me/${props.booking.business_phone}?text=` + 
    encodeURIComponent(`مرحباً، لدي استفسار بشأن الحجز رقم #${props.booking.id} باسم ${props.booking.customer_name}`);
</script>

<template>
    <Head title="تم تقديم الطلب بنجاح" />

    <div class="min-h-screen bg-gradient-to-b from-indigo-50 to-white flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 text-center space-y-6">
            
            <!-- أيقونة النجاح -->
            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto text-4xl font-bold">
                ✓
            </div>

            <div>
                <h2 class="text-2xl font-bold text-gray-800">تم تقديم طلب الحجز!</h2>
                <p class="text-gray-500 text-sm mt-2">طلبك الآن قيد المراجعة، وسيصلك إشعار فور تأكيده.</p>
            </div>

            <!-- تفاصيل الحجز -->
            <div class="bg-gray-50 rounded-xl p-4 text-right text-sm space-y-3 border border-gray-100">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">اسم العميل:</span>
                    <span class="font-semibold text-gray-800">{{ booking.customer_name }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">الخدمة:</span>
                    <span class="font-semibold text-gray-800">{{ booking.service_name }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">الموظف:</span>
                    <span class="font-semibold text-gray-800">{{ booking.staff_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">الموعد:</span>
                    <span class="font-semibold text-gray-800" dir="ltr">{{ booking.date }} — {{ booking.time }}</span>
                </div>
            </div>

            <!-- الأزرار والتفاعل -->
            <div class="space-y-3 pt-2">
                <a 
                    :href="whatsappUrl" 
                    target="_blank" 
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-xl flex items-center justify-center gap-2 transition shadow-sm"
                >
                    <span>💬 التواصل مع الصالون عبر الواتساب</span>
                </a>

                <Link 
                    :href="route('booking.index')" 
                    class="block text-sm text-indigo-600 hover:text-indigo-800 font-medium underline pt-2"
                >
                    إجراء حجز جديد
                </Link>
            </div>

        </div>
    </div>
</template>