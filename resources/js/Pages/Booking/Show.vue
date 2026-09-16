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

// الألوان الديناميكية من إعدادات صاحب المحل مع ألوان افتراضية فاخرة
const primaryColor = computed(() => props.shop?.primary_color || props.shop?.color || '#C5A079');
const secondaryColor = computed(() => props.shop?.secondary_color || '#1E1E24');
const bgColor = computed(() => props.shop?.bg_color || '#F9F8F6');
const textColor = computed(() => props.shop?.text_color || '#2D2D2D');

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

// حساب الأيام القادمة بالتوقيت المحلي
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
            dayName: d.toLocaleDateString('ar', { weekday: 'short' }),
            dayNum: d.getDate(),
            monthName: d.toLocaleDateString('ar', { month: 'short' }),
        });
    }
    return days;
});

// فلترة الأوقات المتاحة
const filteredSlots = computed(() => {
    if (!availableSlots.value.length) return [];

    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const todayFormatted = `${year}-${month}-${day}`;

    if (selectedDate.value !== todayFormatted) {
        return availableSlots.value;
    }

    const currentMinutes = now.getHours() * 60 + now.getMinutes();

    return availableSlots.value.filter((slot) => {
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
    if (form.processing) return;

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

    <!-- الواجهة مع التخصيص الكامل للألوان -->
    <div 
        class="min-h-screen py-8 px-4 transition-colors duration-300 font-sans"
        :style="{ backgroundColor: bgColor, color: textColor }"
        dir="rtl"
    >
        <div class="max-w-xl mx-auto">

            <!-- كرت المحل الرئيسي: غلاف عريض + اللوغو والاسم والوصف بجانبه -->
            <div v-if="shop" class="mb-6 rounded-3xl overflow-hidden shadow-xl bg-white border border-black/5 transition-all">
                
                <!-- غلاف عريض وواضح -->
                <div 
                    class="h-48 w-full bg-gray-200 bg-cover bg-center relative"
                    :style="shop.cover ? { backgroundImage: `url(${shop.cover})` } : { backgroundColor: secondaryColor }"
                >
                    <div class="absolute inset-0 bg-black/5"></div>
                </div>

                <!-- اللوغو مع الاسم والوصف بجانبه -->
                <div class="p-6 pt-0 relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    
                    <div class="flex items-center gap-4 -mt-12">
                        <!-- لوغو بحجم عريض وواضح -->
                        <div class="w-24 h-24 rounded-2xl bg-white p-1.5 shadow-xl ring-1 ring-black/5 shrink-0 overflow-hidden backdrop-blur-sm">
                            <img v-if="shop.logo" :src="shop.logo" class="w-full h-full object-cover rounded-xl" />
                            <div v-else class="w-full h-full flex items-center justify-center bg-gray-50 text-4xl rounded-xl">✨</div>
                        </div>

                        <!-- الاسم والوصف جنب اللوغو مباشرة -->
                        <div class="pt-12 sm:pt-10">
                            <h1 class="text-2xl font-black tracking-wide leading-snug" :style="{ color: textColor }">
                                {{ shop.name }}
                            </h1>
                            <p v-if="shop.description" class="text-xs text-gray-500 mt-1 line-clamp-2">
                                {{ shop.description }}
                            </p>
                        </div>
                    </div>

                    <!-- شارة حجز أونلاين -->
                    <span 
                        class="self-end sm:self-center text-xs font-semibold px-3.5 py-1.5 rounded-full shadow-sm backdrop-blur-md shrink-0"
                        :style="{ backgroundColor: primaryColor + '20', color: primaryColor }"
                    >
                        حجز أونلاين مباشر
                    </span>
                </div>
            </div>

            <!-- عنوان الصفحة -->
            <div class="text-center mb-6">
                <h2 class="text-2xl font-black tracking-tight" :style="{ color: textColor }">احجز موعدك بسهولة</h2>
                <p class="text-sm opacity-70 mt-1">اختر الخدمة والموعد المناسب لك بخطوات سريعة</p>
            </div>

            <!-- رسالة النجاح -->
            <div v-if="$page.props.flash?.success" class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 p-6 rounded-2xl mb-6 text-center font-bold shadow-sm">
                <div class="text-3xl mb-2">🎉</div>
                {{ $page.props.flash.success }}
            </div>

            <!-- مؤشر الخطوات (Progress Stepper) -->
            <div v-if="!$page.props.flash?.success" class="flex items-center justify-between gap-2 mb-6 px-4">
                <div 
                    v-for="n in 4" 
                    :key="n"
                    class="h-2 flex-1 rounded-full transition-all duration-500"
                    :style="n <= step ? { backgroundColor: primaryColor } : { backgroundColor: '#E5E7EB' }"
                ></div>
            </div>

            <!-- البطاقة التفاعلية الرئيسية -->
            <div v-if="!$page.props.flash?.success" class="bg-white rounded-3xl p-6 shadow-xl shadow-black/5 border border-black/5 transition-all">

                <!-- الخطوة 1: اختيار الخدمة -->
                <div v-if="step === 1" class="space-y-4">
                    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <span>1️⃣</span> اختر الخدمة المطلوبة
                    </h3>
                    <div class="space-y-3">
                        <button
                            v-for="service in services"
                            :key="service.id"
                            @click="chooseService(service)"
                            class="w-full text-right p-4 rounded-2xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:shadow-md transition-all duration-200 flex items-center justify-between group"
                        >
                            <div>
                                <div class="font-bold text-gray-900 group-hover:text-black transition-colors">{{ service.name }}</div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                    <span>⏱️ {{ service.duration_minutes }} دقيقة</span>
                                </div>
                            </div>
                            <div class="text-left shrink-0">
                                <span class="text-base font-extrabold px-3 py-1.5 rounded-xl block" :style="{ backgroundColor: primaryColor + '15', color: primaryColor }">
                                    ${{ service.price }}
                                </span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- الخطوة 2: اختيار الموظف -->
                <div v-else-if="step === 2" class="space-y-4">
                    <button @click="goBack" class="text-xs font-bold text-gray-400 hover:text-gray-600 mb-2 flex items-center gap-1">
                        ← الرجوع للخدمات
                    </button>
                    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <span>2️⃣</span> اختر الموظف
                    </h3>

                    <div v-if="filteredStaff.length === 0" class="text-gray-400 text-sm py-8 text-center">
                        لا يوجد موظفون متاحون لهذه الخدمة حالياً.
                    </div>

                    <div v-else class="space-y-3">
                        <button
                            v-for="member in filteredStaff"
                            :key="member.id"
                            @click="chooseStaff(member)"
                            class="w-full text-right p-4 rounded-2xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:shadow-md transition-all duration-200 flex items-center gap-4 group"
                        >
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 shadow-sm" :style="{ backgroundColor: primaryColor + '15' }">
                                <component :is="getProfessionIcon(member.title)" class="w-6 h-6" :style="{ color: primaryColor }" />
                            </div>
                            <div class="grow">
                                <div class="font-bold text-gray-900">{{ member.user.name }}</div>
                                <div v-if="member.title" class="text-xs text-gray-500 mt-0.5">{{ member.title }}</div>
                            </div>
                            <span class="text-gray-300 group-hover:text-gray-600 transition-colors">←</span>
                        </button>
                    </div>
                </div>

                <!-- الخطوة 3: اختيار التاريخ والوقت -->
                <div v-else-if="step === 3" class="space-y-4">
                    <button @click="goBack" class="text-xs font-bold text-gray-400 hover:text-gray-600 mb-2 flex items-center gap-1">
                        ← الرجوع لاختيار الموظف
                    </button>
                    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <span>3️⃣</span> حدد التاريخ والوقت
                    </h3>

                    <!-- شريط التواريخ -->
                    <div class="flex gap-2 overflow-x-auto pb-3 scrollbar-none">
                        <button
                            v-for="day in upcomingDays"
                            :key="day.value"
                            @click="chooseDate(day.value)"
                            :style="selectedDate === day.value ? { backgroundColor: primaryColor, color: '#fff' } : {}"
                            :class="selectedDate === day.value ? 'shadow-md scale-105' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-100'"
                            class="shrink-0 w-16 py-3 rounded-2xl text-center transition-all duration-200"
                        >
                            <div class="text-xs opacity-80 font-medium">{{ day.dayName }}</div>
                            <div class="text-lg font-black my-0.5">{{ day.dayNum }}</div>
                            <div class="text-[10px] opacity-75">{{ day.monthName }}</div>
                        </button>
                    </div>

                    <!-- الأوقات -->
                    <div v-if="selectedDate" class="pt-4 border-t border-gray-100">
                        <h4 class="font-bold text-sm text-gray-700 mb-3">الأوقات المتاحة:</h4>

                        <div v-if="loadingSlots" class="text-gray-400 text-sm py-6 text-center animate-pulse">
                            جاري جلب الأوقات المتاحة...
                        </div>

                        <div v-else-if="filteredSlots.length === 0" class="text-gray-400 text-sm py-6 text-center">
                            لا توجد أوقات متاحة في هذا اليوم، يرجى اختيار تاريخ آخر.
                        </div>

                        <div v-else class="grid grid-cols-3 gap-2.5">
                            <button
                                v-for="(slot, index) in filteredSlots"
                                :key="index"
                                @click="chooseTime(slot)"
                                class="border border-gray-100 bg-gray-50/80 hover:bg-white rounded-xl py-2.5 text-xs font-bold hover:shadow-md transition-all text-center"
                            >
                                {{ typeof slot === 'object' ? (slot.formatted || slot.time) : slot }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- الخطوة 4: تأكيد البيانات -->
                <div v-else-if="step === 4" class="space-y-4">
                    <button @click="goBack" class="text-xs font-bold text-gray-400 hover:text-gray-600 mb-2 flex items-center gap-1">
                        ← الرجوع لتغيير الوقت
                    </button>
                    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <span>4️⃣</span> تأكيد الحجز
                    </h3>

                    <!-- ملخص الحجز -->
                    <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50/50 space-y-2 text-xs text-gray-600">
                        <div class="flex justify-between"><span>الخدمة:</span> <strong class="text-gray-900">{{ selectedService.name }}</strong></div>
                        <div class="flex justify-between"><span>الموظف:</span> <strong class="text-gray-900">{{ selectedStaff.user.name }}</strong></div>
                        <div class="flex justify-between"><span>التاريخ والوقت:</span> <strong class="text-gray-900">{{ selectedDate }} — {{ selectedTime }}</strong></div>
                        <div class="flex justify-between border-t border-gray-200/60 pt-2 mt-2"><span>التكلفة:</span> <strong class="text-base" :style="{ color: primaryColor }">${{ selectedService.price }}</strong></div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">الاسم الكامل</label>
                            <input 
                                type="text" 
                                v-model="form.customer_name" 
                                placeholder="أدخل اسمك"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:outline-none transition-all"
                                required 
                            />
                            <div v-if="form.errors.customer_name" class="text-red-500 text-xs mt-1">{{ form.errors.customer_name }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">رقم الهاتف</label>
                            <input 
                                type="text" 
                                v-model="form.customer_phone" 
                                placeholder="05XXXXXXXX"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:outline-none transition-all"
                                required 
                            />
                            <div v-if="form.errors.customer_phone" class="text-red-500 text-xs mt-1">{{ form.errors.customer_phone }}</div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            :style="{ backgroundColor: primaryColor }"
                            class="w-full text-white py-4 rounded-xl font-bold shadow-lg hover:opacity-95 disabled:opacity-50 transition-all duration-200 flex items-center justify-center gap-2 text-sm mt-2"
                        >
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

                            <span>{{ form.processing ? 'جاري إرسال الحجز...' : 'تأكيد الحجز الآن' }}</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</template>