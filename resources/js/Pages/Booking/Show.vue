<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { getProfessionIcon } from '@/professionIcons';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    services: Array,
    staff: Array,
    shop: Object,
});

const primaryColor = computed(() => props.shop?.color || '#4f46e5');

const step = ref(1);

const selectedService = ref(null);
const selectedStaff = ref(null);
const selectedDate = ref('');
const selectedTime = ref('');
const availableSlots = ref([]);
const loadingSlots = ref(false);

const form = useForm({
    customer_name: '',
    customer_phone: '',
    service_id: '',
    staff_id: '',
    start_time: '',
});

// حساب التاريخ بالتوقيت المحلي الصحيح تجنباً لمشاكل UTC والتأخير اليومي
const upcomingDays = computed(() => {
    const days = [];
    for (let i = 0; i < 14; i++) {
        const d = new Date();
        d.setDate(d.getDate() + i);

        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        const formattedDate = `${year}-${month}-${day}`;

        days.push({
            value: formattedDate,
            label: d.toLocaleDateString('ar', { weekday: 'short', day: 'numeric', month: 'short' }),
        });
    }
    return days;
});

// فلترة الأوقات المتاحة لإخفاء الأوقات التي مضت إذا كان التاريخ المختار هو اليوم الحالي
const filteredSlots = computed(() => {
    if (!availableSlots.value.length) return [];

    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const todayFormatted = `${year}-${month}-${day}`;

    // إذا لم يكن اليوم المختار هو اليوم الحالي، نعرض كل الأوقات القادمة من السيرفر
    if (selectedDate.value !== todayFormatted) {
        return availableSlots.value;
    }

    const currentMinutes = now.getHours() * 60 + now.getMinutes();

    return availableSlots.value.filter((slot) => {
        // دعم حالة أن يكون الـ slot عبارة عن نص مثل "10:00" أو كائن يحتوي على time
        const timeString = typeof slot === 'object' ? slot.time : slot;
        if (!timeString) return true;

        const parts = timeString.split(':');
        const slotMinutes = parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);

        return slotMinutes > currentMinutes;
    });
});

const filteredStaff = computed(() => {
    if (!selectedService.value) return [];
    return props.staff.filter((member) =>
        member.services.some((s) => s.id === selectedService.value.id)
    );
});

const chooseService = (service) => {
    selectedService.value = service;
    step.value = 2;
};

const chooseStaff = (member) => {
    selectedStaff.value = member;
    step.value = 3;
};

// جلب المواعيد المتاحة مع معالجة الأخطاء
const chooseDate = async (date) => {
    selectedDate.value = date;
    selectedTime.value = '';
    loadingSlots.value = true;
    availableSlots.value = [];

    try {
        const response = await axios.get(route('availability.slots'), {
            params: {
                staff_id: selectedStaff.value.id,
                service_id: selectedService.value.id,
                date: date,
            },
        });
        availableSlots.value = response.data.slots || response.data || [];
    } catch (error) {
        console.error('Error fetching slots:', error);
        availableSlots.value = [];
    } finally {
        loadingSlots.value = false;
    }
};

const chooseTime = (time) => {
    selectedTime.value = typeof time === 'object' ? time.time : time;
    step.value = 4;
};

const submit = () => {
    if (form.processing) return; // حماية إضافية قبل الإرسال

    form.service_id = selectedService.value.id;
    form.staff_id = selectedStaff.value.id;
    form.start_time = `${selectedDate.value} ${selectedTime.value}:00`;
    
    form.post(route('booking.store'), {
        preserveScroll: true,
    });
};

const goBack = () => {
    if (step.value > 1) step.value--;
};
</script>

<template>
    <Head :title="shop?.name ? `احجز موعدك - ${shop.name}` : 'احجز موعدك'" />

    <div class="min-h-screen bg-gradient-to-b from-indigo-50 to-white py-10 px-4">
        <div class="max-w-xl mx-auto">

            <!-- هيدر المحل: غلاف + شعار + اسم + وصف -->
            <div v-if="shop" class="mb-8 rounded-xl overflow-hidden shadow-sm bg-white">
                <div
                    class="h-32 bg-gray-100 bg-cover bg-center"
                    :style="shop.cover ? { backgroundImage: `url(${shop.cover})` } : {}"
                ></div>
                <div class="px-5 pb-5 -mt-8 flex items-end gap-3">
                    <div class="w-16 h-16 rounded-xl bg-white shadow-md border-4 border-white overflow-hidden shrink-0">
                        <img v-if="shop.logo" :src="shop.logo" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300 text-2xl">🏬</div>
                    </div>
                    <div class="pb-1">
                        <h1 class="text-lg font-bold text-gray-800">{{ shop.name }}</h1>
                        <p v-if="shop.description" class="text-sm text-gray-500 mt-0.5">{{ shop.description }}</p>
                    </div>
                </div>
            </div>

            <h1 class="text-3xl font-bold text-gray-800 mb-2 text-center">احجز موعدك</h1>
            <p class="text-gray-500 text-center mb-8">اختر ما يناسبك في خطوات بسيطة</p>

            <div v-if="$page.props.flash?.success" class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 text-center font-medium">
                {{ $page.props.flash.success }}
            </div>

            <div v-if="!$page.props.flash?.success" class="flex justify-center gap-2 mb-8">
                <div
                    v-for="n in 4"
                    :key="n"
                    :style="n <= step ? { backgroundColor: primaryColor } : {}"
                    :class="n <= step ? '' : 'bg-gray-200'"
                    class="h-1.5 w-10 rounded-full transition-colors"
                ></div>
            </div>

            <div v-if="!$page.props.flash?.success" class="bg-white shadow-md rounded-xl p-6">

                <!-- الخطوة 1: اختيار الخدمة -->
                <div v-if="step === 1">
                    <h2 class="font-semibold text-lg text-gray-800 mb-4">اختر الخدمة</h2>
                    <div class="space-y-2">
                        <button
                            v-for="service in services"
                            :key="service.id"
                            @click="chooseService(service)"
                            class="w-full text-right border border-gray-200 rounded-lg p-4 hover:border-indigo-500 hover:bg-indigo-50 transition"
                        >
                            <div class="font-medium text-gray-800">{{ service.name }}</div>
                            <div class="text-sm text-gray-500 mt-1" dir="ltr" style="text-align: right;">
                                {{ service.duration_minutes }} دقيقة | ${{ service.price }}
                            </div>
                        </button>
                    </div>
                </div>

                <!-- الخطوة 2: اختيار الموظف -->
                <div v-else-if="step === 2">
                    <button @click="goBack" class="text-sm text-gray-500 mb-4">← رجوع</button>
                    <h2 class="font-semibold text-lg text-gray-800 mb-4">اختر الموظف</h2>

                    <div v-if="filteredStaff.length === 0" class="text-gray-500 text-sm py-4">
                        لا يوجد موظفون متاحون لهذه الخدمة حالياً.
                    </div>

                    <div v-else class="space-y-2">
                        <button
                            v-for="member in filteredStaff"
                            :key="member.id"
                            @click="chooseStaff(member)"
                            class="w-full text-right border border-gray-200 rounded-lg p-4 hover:border-indigo-500 hover:bg-indigo-50 transition flex items-center gap-3"
                        >
                            <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center shrink-0">
                                <component :is="getProfessionIcon(member.title)" class="w-5 h-5 text-indigo-600" />
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">{{ member.user.name }}</div>
                                <div v-if="member.title" class="text-sm text-gray-500 mt-1">{{ member.title }}</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- الخطوة 3: اختيار التاريخ والوقت -->
                <div v-else-if="step === 3">
                    <button @click="goBack" class="text-sm text-gray-500 mb-4">← رجوع</button>
                    <h2 class="font-semibold text-lg text-gray-800 mb-4">اختر التاريخ</h2>

                    <div class="flex gap-2 overflow-x-auto pb-2 mb-6">
                        <button
                            v-for="day in upcomingDays"
                            :key="day.value"
                            @click="chooseDate(day.value)"
                            :style="selectedDate === day.value ? { backgroundColor: primaryColor, color: '#fff' } : {}"
                            :class="selectedDate === day.value ? '' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="shrink-0 px-4 py-2 rounded-lg text-sm whitespace-nowrap transition"
                        >
                            {{ day.label }}
                        </button>
                    </div>

                    <div v-if="selectedDate">
                        <h3 class="font-medium text-gray-700 mb-3">الأوقات المتاحة</h3>

                        <div v-if="loadingSlots" class="text-gray-500 text-sm">جاري التحميل...</div>

                        <div v-else-if="filteredSlots.length === 0" class="text-gray-500 text-sm">
                            لا توجد أوقات متاحة في هذا اليوم، جرّب يوماً آخر.
                        </div>

                        <div v-else class="grid grid-cols-3 gap-2">
                            <button
                                v-for="(slot, index) in filteredSlots"
                                :key="index"
                                @click="chooseTime(slot)"
                                class="border border-gray-200 rounded-lg py-2 text-sm hover:border-indigo-500 hover:bg-indigo-50 transition text-center"
                            >
                                {{ typeof slot === 'object' ? (slot.formatted || slot.time) : slot }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- الخطوة 4: تأكيد البيانات -->
                <div v-else-if="step === 4">
                    <button @click="goBack" class="text-sm text-gray-500 mb-4">← رجوع</button>
                    <h2 class="font-semibold text-lg text-gray-800 mb-4">أدخل بياناتك لتأكيد الحجز</h2>

                    <div class="bg-gray-50 rounded-lg p-4 mb-4 text-sm text-gray-600 space-y-1">
                        <div>الخدمة: <span class="font-medium text-gray-800">{{ selectedService.name }}</span></div>
                        <div>الموظف: <span class="font-medium text-gray-800">{{ selectedStaff.user.name }}</span></div>
                        <div>الموعد: <span class="font-medium text-gray-800">{{ selectedDate }} — {{ selectedTime }}</span></div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">اسمك</label>
                            <input type="text" v-model="form.customer_name" class="w-full border-gray-300 rounded-md shadow-sm" required />
                            <div v-if="form.errors.customer_name" class="text-red-600 text-sm mt-1">{{ form.errors.customer_name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف</label>
                            <input type="text" v-model="form.customer_phone" class="w-full border-gray-300 rounded-md shadow-sm" required />
                            <div v-if="form.errors.customer_phone" class="text-red-600 text-sm mt-1">{{ form.errors.customer_phone }}</div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            :style="{ backgroundColor: primaryColor }"
                            class="w-full text-white py-3 rounded-lg font-medium hover:opacity-90 disabled:opacity-50 transition flex items-center justify-center gap-2"
                        >
                            <!-- مؤشر التحميل (Spinner) أثناء معالجة الطلب -->
                            <svg 
                                v-if="form.processing" 
                                class="animate-spin h-5 w-5 text-white" 
                                xmlns="http://www.w3.org/2000/svg" 
                                fill="none" 
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>

                            <span>{{ form.processing ? 'جاري تأكيد الحجز...' : 'تأكيد الحجز' }}</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</template>