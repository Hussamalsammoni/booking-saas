<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { getProfessionIcon } from '@/professionIcons';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';

const props = defineProps({
    services: Array,
    staff: Array,
    shop: Object,
});

/* ---------------- الثيم: كل الألوان من إعدادات المحل ---------------- */
const primaryColor = computed(() => props.shop?.primary_color || '#111827');
const secondaryColor = computed(() => props.shop?.secondary_color || '#1E1E24');
const bgColor = computed(() => props.shop?.bg_color || '#F7F7F8');
const textColor = computed(() => props.shop?.text_color || '#111827');

// إذا الخلفية غامقة، البطاقات بتفتح شوي بدل الأبيض الصريح
const isDark = computed(() => {
    const h = bgColor.value.replace('#', '');
    if (h.length !== 6) return false;
    const [r, g, b] = [0, 2, 4].map((i) => parseInt(h.slice(i, i + 2), 16));
    return (r * 299 + g * 587 + b * 114) / 1000 < 128;
});

const rootStyle = computed(() => ({
    '--p': primaryColor.value,
    '--s': secondaryColor.value,
    '--bg': bgColor.value,
    '--t': textColor.value,
    '--card': isDark.value ? 'color-mix(in srgb, var(--bg) 88%, white)' : '#ffffff',
    '--line': 'color-mix(in srgb, var(--t) 12%, transparent)',
    backgroundColor: 'var(--bg)',
    color: 'var(--t)',
}));

const heroStyle = computed(() =>
    props.shop?.cover
        ? { backgroundImage: `url(${props.shop.cover})` }
        : { background: `linear-gradient(135deg, ${secondaryColor.value}, ${primaryColor.value})` }
);

/* ---------------- بيانات المحل ---------------- */
const features = computed(() => props.shop?.features || []);
const gallery = computed(() => props.shop?.gallery || []);
const workingHours = computed(() => props.shop?.working_hours || []);
const socials = computed(() => props.shop?.social_links || {});
const testimonials = computed(() => props.shop?.testimonials || []);

const avgRating = computed(() => {
    if (!testimonials.value.length) return null;
    const sum = testimonials.value.reduce((acc, t) => acc + Number(t.rating || 0), 0);
    return (sum / testimonials.value.length).toFixed(1);
});

const whatsappLink = computed(() => {
    let phone = (props.shop?.phone || '').replace(/\D/g, '');
    if (!phone) return null;
    if (phone.startsWith('09')) phone = '963' + phone.slice(1);
    return `https://wa.me/${phone}`;
});

const ICONS = {
    whatsapp: 'M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.71.45 3.38 1.3 4.85L2.05 22l5.36-1.4a9.87 9.87 0 004.63 1.18h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.86 9.86 0 0012.04 2zm5.81 14.11c-.24.68-1.4 1.31-1.94 1.39-.5.08-1.12.11-1.81-.11-.42-.13-.95-.31-1.64-.6-2.88-1.24-4.76-4.14-4.9-4.33-.14-.19-1.17-1.55-1.17-2.96 0-1.4.73-2.09 1-2.38.26-.28.57-.35.76-.35.19 0 .38 0 .55.01.18.01.41-.07.64.49.24.58.81 2 .88 2.14.07.14.12.31.02.5-.09.19-.14.31-.28.48-.14.16-.29.36-.42.49-.14.14-.28.29-.12.57.16.28.71 1.17 1.52 1.9 1.05.94 1.93 1.23 2.21 1.37.28.14.44.12.6-.07.16-.19.68-.79.86-1.06.18-.28.36-.23.61-.14.24.09 1.55.73 1.82.86.26.14.44.2.5.31.07.12.07.65-.17 1.33z',
    map: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z',
    instagram: 'M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 01-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 017.8 2m-.2 2A3.6 3.6 0 004 7.6v8.8C4 18.4 5.6 20 7.6 20h8.8a3.6 3.6 0 003.6-3.6V7.6C20 5.6 18.4 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 110 2.5 1.25 1.25 0 010-2.5M12 7a5 5 0 110 10 5 5 0 010-10m0 2a3 3 0 100 6 3 3 0 000-6z',
    facebook: 'M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z',
    tiktok: 'M16.5 3c.3 1.9 1.6 3.4 3.5 3.8v2.6c-1.3 0-2.5-.4-3.5-1.1v6.4c0 3-2.4 5.3-5.4 5.3S5.7 17.7 5.7 14.7c0-3 2.4-5.3 5.4-5.3.4 0 .8 0 1.2.1v2.7c-.4-.1-.8-.2-1.2-.2-1.5 0-2.7 1.2-2.7 2.7s1.2 2.7 2.7 2.7 2.8-1.2 2.8-2.7V3h2.6z',
};

const links = computed(() =>
    [
        whatsappLink.value && { k: 'whatsapp', href: whatsappLink.value },
        props.shop?.map_url && { k: 'map', href: props.shop.map_url },
        socials.value.instagram && { k: 'instagram', href: socials.value.instagram },
        socials.value.facebook && { k: 'facebook', href: socials.value.facebook },
        socials.value.tiktok && { k: 'tiktok', href: socials.value.tiktok },
    ].filter(Boolean)
);

const navItems = computed(() =>
    [
        (workingHours.value.length || props.shop?.map_address) && { id: 'info', label: 'معلومات المحل' },
        gallery.value.length && { id: 'gallery', label: 'معرض الصور' },
        testimonials.value.length && { id: 'testimonials', label: 'آراء الزبائن' },
    ].filter(Boolean)
);

const fmt = (v) => '$' + Number(v || 0).toLocaleString('en-US');
// إذا في إحداثيات دقيقة (من رابط المحل) بنثبّت الدبوس عليها، وإلا بنبحث بالعنوان
const mapSrc = computed(() => {
    const { map_lat: lat, map_lng: lng, map_address: addr } = props.shop || {};
    const q = lat && lng ? `${lat},${lng}` : addr;
    return q ? `https://maps.google.com/maps?q=${encodeURIComponent(q)}&z=17&output=embed` : null;
});
const isOpenDay = (d) => d.enabled && d.enabled !== '0';

/* ---------------- حالة "مفتوح الآن" ---------------- */
const dayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
const todayName = computed(() => dayNames[new Date().getDay()]);

const nowStatus = ref({ open: false, label: '' });
const computeOpenStatus = () => {
    if (!workingHours.value.length) return;
    const now = new Date();
    const today = workingHours.value.find((d) => d.day === todayName.value);

    if (!today || !isOpenDay(today)) {
        nowStatus.value = { open: false, label: 'مغلق اليوم' };
        return;
    }
    const [sh, sm] = today.start.split(':').map(Number);
    const [eh, em] = today.end.split(':').map(Number);
    const mins = now.getHours() * 60 + now.getMinutes();
    nowStatus.value =
        mins >= sh * 60 + sm && mins <= eh * 60 + em
            ? { open: true, label: `مفتوح حتى ${today.end}` }
            : { open: false, label: 'مغلق الآن' };
};

const scrolled = ref(false);
const onScroll = () => (scrolled.value = window.scrollY > 40);
onMounted(() => {
    computeOpenStatus();
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));

const scrollTo = (id) => document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });

/* ---------------- lightbox ---------------- */
const lightboxImage = ref(null);
const openLightbox = (url) => (lightboxImage.value = url);
const closeLightbox = () => (lightboxImage.value = null);

/* ------------------------------------------------------------------ */
/* منطق الحجز (بدون أي تغيير) */

const step = ref(1); // 1: خدمة، 2: موظف، 3: وقت، 4: تأكيد
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

const upcomingDays = computed(() => {
    const days = [];
    for (let i = 0; i < 14; i++) {
        const d = new Date();
        d.setDate(d.getDate() + i);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        days.push({
            value: `${year}-${month}-${day}`,
            dayName: d.toLocaleDateString('ar', { weekday: 'short' }),
            dayNum: d.getDate(),
            monthName: d.toLocaleDateString('ar', { month: 'short' }),
        });
    }
    return days;
});

const filteredSlots = computed(() => {
    if (!availableSlots.value.length) return [];
    const now = new Date();
    const todayFormatted = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    if (selectedDate.value !== todayFormatted) return availableSlots.value;

    const currentMinutes = now.getHours() * 60 + now.getMinutes();
    return availableSlots.value.filter((slot) => {
        const timeString = typeof slot === 'object' ? slot.time : slot;
        if (!timeString) return true;
        const [h, m] = timeString.split(':').map(Number);
        return h * 60 + m > currentMinutes;
    });
});

const filteredStaff = computed(() => {
    if (!selectedService.value) return [];
    return props.staff.filter((m) => m.services.some((s) => s.id === selectedService.value.id));
});

const chooseService = (service) => {
    selectedService.value = service;
    selectedStaff.value = null;
    selectedDate.value = '';
    selectedTime.value = '';
    step.value = 2;
    scrollTo('booking-box');
};

const changeService = () => {
    step.value = 1;
    scrollTo('booking-box');
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
            params: { staff_id: selectedStaff.value.id, service_id: selectedService.value.id, date },
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
    form.post(route('booking.store'), { preserveScroll: true });
};

const goBackFrom = (fromStep) => {
    if (fromStep === 2) step.value = 1;
    if (fromStep === 3) step.value = 2;
    if (fromStep === 4) step.value = 3;
};
</script>

<template>
    <Head :title="shop?.name ? `احجز موعدك - ${shop.name}` : 'احجز موعدك'">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;700&family=Reem+Kufi:wght@500;700&display=swap" rel="stylesheet" />
    </Head>

    <div dir="rtl" class="pg min-h-screen antialiased" :style="rootStyle">

        <!-- ============ شريط التنقل ============ -->
        <header class="fixed inset-x-0 top-0 z-40 transition-colors duration-300" :class="scrolled ? 'nav-solid' : 'text-white'">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full ring-1 ring-white/30">
                        <img v-if="shop?.logo" :src="shop.logo" class="h-full w-full object-cover" alt="" />
                        <div v-else class="flex h-full w-full items-center justify-center bg-white/20 font-display text-lg">{{ (shop?.name || '؟').charAt(0) }}</div>
                    </div>
                    <span class="font-display text-lg font-bold">{{ shop?.name }}</span>
                </div>

                <nav class="hidden items-center gap-7 text-sm font-medium sm:flex">
                    <button v-for="n in navItems" :key="n.id" @click="scrollTo(n.id)" class="opacity-80 transition hover:opacity-100">{{ n.label }}</button>
                </nav>

                <button @click="scrollTo('booking-box')" class="btn-p rounded-full px-5 py-2 text-sm font-bold shadow-lg transition hover:brightness-110">احجز الآن</button>
            </div>
        </header>

        <!-- ============ الهيرو ============ -->
        <section class="relative isolate flex min-h-[86vh] items-end overflow-hidden text-white">
            <div class="absolute inset-0 -z-20 bg-cover bg-center" :style="heroStyle"></div>
            <div class="absolute inset-0 -z-10" style="background: linear-gradient(180deg, rgb(0 0 0 / .5) 0%, rgb(0 0 0 / .15) 38%, rgb(0 0 0 / .88) 100%)"></div>

            <div class="up mx-auto w-full max-w-6xl px-4 pb-14 pt-32 sm:px-6 sm:pb-20">
                <div class="mb-6 h-24 w-24 overflow-hidden rounded-3xl ring-2 ring-white/40 sm:h-28 sm:w-28">
                    <img v-if="shop?.logo" :src="shop.logo" class="h-full w-full object-cover" alt="" />
                    <div v-else class="flex h-full w-full items-center justify-center bg-white/15 font-display text-4xl">{{ (shop?.name || '؟').charAt(0) }}</div>
                </div>

                <h1 class="font-display text-5xl font-bold leading-[1.1] sm:text-7xl">{{ shop?.name }}</h1>

                <div class="mt-5 flex flex-wrap items-center gap-2.5 text-sm">
                    <span v-if="avgRating" class="glass inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 font-bold">
                        <span class="text-yellow-300">★</span>{{ avgRating }}
                        <span class="font-normal text-white/70">({{ testimonials.length }})</span>
                    </span>
                    <span v-if="nowStatus.label" class="glass inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 font-bold">
                        <span class="h-2 w-2 rounded-full" :class="nowStatus.open ? 'bg-emerald-400' : 'bg-white/50'"></span>{{ nowStatus.label }}
                    </span>
                    <span v-if="shop?.map_address" class="glass rounded-full px-3.5 py-1.5">{{ shop.map_address }}</span>
                </div>

                <p v-if="shop?.description" class="mt-6 max-w-xl text-lg leading-8 text-white/80">{{ shop.description }}</p>

                <div v-if="features.length" class="mt-5 flex flex-wrap gap-2">
                    <span v-for="(f, i) in features" :key="i" class="glass rounded-full px-3.5 py-1.5 text-sm">{{ f.icon || '⭐' }} {{ f.title }}</span>
                </div>

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <button @click="scrollTo('booking-box')" class="btn-p rounded-full px-8 py-3.5 text-base font-bold shadow-xl transition hover:brightness-110">احجز موعدك</button>
                    <a v-for="l in links" :key="l.k" :href="l.href" target="_blank" rel="noopener" class="glass flex h-12 w-12 items-center justify-center rounded-full transition hover:bg-white/25">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path :d="ICONS[l.k]" /></svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- ============ المحتوى ============ -->
        <main class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <div class="grid gap-14 lg:grid-cols-[1fr_400px] lg:items-start">

                <div>
                    <!-- الخدمات: قائمة أسعار -->
                    <div class="mb-2 flex items-end justify-between">
                        <h2 class="font-display text-3xl font-bold">الخدمات</h2>
                        <span class="muted text-sm">اختر خدمة لتبدأ الحجز</span>
                    </div>

                    <ul class="mt-4 border-t line">
                        <li v-for="service in services" :key="service.id" class="border-b line">
                            <button
                                @click="chooseService(service)"
                                class="svc group -mx-3 flex w-[calc(100%+1.5rem)] items-center gap-4 rounded-2xl px-3 py-5 text-right transition"
                                :class="selectedService?.id === service.id && 'soft-bg'"
                            >
                                <span class="min-w-0">
                                    <span class="block font-display text-xl font-bold">{{ service.name }}</span>
                                    <span class="muted mt-0.5 block text-sm">{{ service.duration_minutes }} دقيقة</span>
                                </span>
                                <span class="lead mb-2 h-px grow self-end"></span>
                                <span dir="ltr" class="text-xl font-bold tabular-nums">{{ fmt(service.price) }}</span>
                                <span
                                    class="pick shrink-0 rounded-full px-5 py-2 text-sm font-bold transition"
                                    :class="selectedService?.id === service.id ? 'btn-p' : 'pick-idle'"
                                >{{ selectedService?.id === service.id ? 'مختارة ✓' : 'احجز' }}</span>
                            </button>
                        </li>
                    </ul>

                    <!-- معلومات المحل -->
                    <div v-if="workingHours.length || shop?.map_address" id="info" class="mt-20 scroll-mt-24">
                        <h2 class="mb-6 font-display text-3xl font-bold">معلومات المحل</h2>
                        <div class="grid gap-10 sm:grid-cols-2">
                            <div v-if="workingHours.length">
                                <h3 class="mb-3 font-bold">ساعات العمل</h3>
                                <div class="border-t line">
                                    <div
                                        v-for="d in workingHours"
                                        :key="d.day"
                                        class="flex items-center justify-between border-b line py-2.5 text-sm"
                                        :class="d.day === todayName ? 'font-bold' : 'muted'"
                                        :style="d.day === todayName ? { color: 'var(--p)' } : {}"
                                    >
                                        <span>{{ d.day }}<span v-if="d.day === todayName" class="me-2 text-xs">(اليوم)</span></span>
                                        <span v-if="isOpenDay(d)" dir="ltr" class="tabular-nums">{{ d.start }} – {{ d.end }}</span>
                                        <span v-else>مغلق</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="mapSrc" class="relative min-h-[22rem] overflow-hidden rounded-3xl border line">
                                <iframe :src="mapSrc" title="موقع المحل" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="absolute inset-0 h-full w-full border-0" :class="isDark && 'map-dark'"></iframe>
                                <div class="panel absolute inset-x-3 bottom-3 flex items-center gap-3 p-3.5" style="border-radius: 1.25rem">
                                    <span class="soft flex h-11 w-11 shrink-0 items-center justify-center rounded-full">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path :d="ICONS.map" /></svg>
                                    </span>
                                    <div class="min-w-0 grow">
                                        <div class="muted text-xs">موقعنا</div>
                                        <div class="truncate text-sm font-bold">{{ shop.map_address || 'افتح الموقع' }}</div>
                                    </div>
                                    <a v-if="shop?.map_url" :href="shop.map_url" target="_blank" rel="noopener" class="btn-p shrink-0 rounded-full px-4 py-2.5 text-xs font-bold transition hover:brightness-110">الاتجاهات</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- المعرض -->
                    <div v-if="gallery.length" id="gallery" class="mt-20 scroll-mt-24">
                        <h2 class="mb-6 font-display text-3xl font-bold">معرض الصور</h2>
                        <div class="grid auto-rows-[9rem] grid-cols-2 gap-3 sm:auto-rows-[11rem] sm:grid-cols-4">
                            <button
                                v-for="(url, i) in gallery"
                                :key="i"
                                @click="openLightbox(url)"
                                class="group relative overflow-hidden rounded-2xl"
                                :class="i === 0 && gallery.length > 2 ? 'col-span-2 row-span-2' : ''"
                            >
                                <img :src="url" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy" alt="" />
                            </button>
                        </div>
                    </div>

                    <!-- الآراء -->
                    <div v-if="testimonials.length" id="testimonials" class="mt-20 scroll-mt-24">
                        <div class="mb-6 flex items-end justify-between">
                            <h2 class="font-display text-3xl font-bold">آراء زبائننا</h2>
                            <span class="flex items-baseline gap-2">
                                <span class="font-display text-4xl font-bold">{{ avgRating }}</span>
                                <span class="text-yellow-400">★</span>
                                <span class="muted text-sm">{{ testimonials.length }} تقييم</span>
                            </span>
                        </div>
                        <div class="grid gap-x-12 gap-y-8 border-t line pt-8 sm:grid-cols-2">
                            <figure v-for="(t, i) in testimonials" :key="i">
                                <div class="mb-3 text-sm tracking-widest">
                                    <span v-for="n in 5" :key="n" :class="n <= t.rating ? 'text-yellow-400' : 'opacity-20'">★</span>
                                </div>
                                <blockquote class="text-lg leading-8">{{ t.text }}</blockquote>
                                <figcaption class="mt-3 text-sm font-bold" style="color: var(--p)">{{ t.name }}</figcaption>
                            </figure>
                        </div>
                    </div>
                </div>

                <!-- ============ لوحة الحجز ============ -->
                <aside id="booking-box" class="scroll-mt-24 lg:sticky lg:top-24">
                    <div class="panel overflow-hidden">
                        <div class="btn-p px-6 pb-5 pt-6">
                            <div class="flex items-center justify-between">
                                <h3 class="font-display text-2xl font-bold">احجز موعدك</h3>
                                <span class="text-xs opacity-75">الخطوة {{ step }} من 4</span>
                            </div>
                            <div class="mt-4 flex gap-1.5">
                                <span v-for="n in 4" :key="n" class="h-1 flex-1 rounded-full transition-all duration-500" :class="n <= step ? 'bg-white' : 'bg-white/30'"></span>
                            </div>
                        </div>

                        <div class="p-6">
                            <div v-if="$page.props.flash?.success" class="rounded-2xl bg-emerald-500/10 p-6 text-center">
                                <div class="mb-2 text-3xl">🎉</div>
                                <p class="text-sm font-bold text-emerald-600">{{ $page.props.flash.success }}</p>
                            </div>

                            <template v-else>
                                <!-- الخدمة المختارة -->
                                <div v-if="selectedService" class="tint mb-5 rounded-2xl p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-bold">{{ selectedService.name }}</div>
                                            <div class="muted mt-0.5 text-xs">{{ selectedService.duration_minutes }} دقيقة</div>
                                        </div>
                                        <span dir="ltr" class="font-bold tabular-nums" style="color: var(--p)">{{ fmt(selectedService.price) }}</span>
                                    </div>
                                    <button @click="changeService" class="muted mt-3 text-xs font-bold hover:opacity-70">تغيير الخدمة</button>
                                </div>

                                <!-- 1: بلا خدمة -->
                                <div v-if="step === 1" class="py-8 text-center">
                                    <div class="soft mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full text-2xl">📅</div>
                                    <p class="font-bold">اختر خدمة من القائمة</p>
                                    <p class="muted mt-1 text-sm">ثم حدد الموظف والوقت المناسب لك</p>
                                </div>

                                <!-- 2: الموظف -->
                                <div v-else-if="step === 2" class="space-y-2.5">
                                    <h4 class="mb-1 text-sm font-bold">اختر الموظف</h4>
                                    <div v-if="filteredStaff.length === 0" class="muted py-6 text-center text-sm">لا يوجد موظفون متاحون لهذه الخدمة.</div>
                                    <button v-for="member in filteredStaff" :key="member.id" @click="chooseStaff(member)" class="row flex w-full items-center gap-3 p-3 text-right transition">
                                        <div class="soft flex h-11 w-11 shrink-0 items-center justify-center rounded-full">
                                            <component :is="getProfessionIcon(member.title)" class="h-5 w-5" />
                                        </div>
                                        <div class="grow">
                                            <div class="font-bold">{{ member.user.name }}</div>
                                            <div v-if="member.title" class="muted text-xs">{{ member.title }}</div>
                                        </div>
                                    </button>
                                </div>

                                <!-- 3: الموعد -->
                                <div v-else-if="step === 3" class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-bold">حدد الموعد</h4>
                                        <button @click="goBackFrom(3)" class="muted text-xs font-bold hover:opacity-70">تغيير الموظف</button>
                                    </div>

                                    <div class="noscroll flex gap-2 overflow-x-auto pb-1">
                                        <button
                                            v-for="day in upcomingDays"
                                            :key="day.value"
                                            @click="chooseDate(day.value)"
                                            class="w-16 shrink-0 rounded-2xl py-3 text-center transition"
                                            :class="selectedDate === day.value ? 'btn-p shadow-lg' : 'row'"
                                        >
                                            <div class="text-[11px] opacity-75">{{ day.dayName }}</div>
                                            <div class="text-lg font-bold leading-tight">{{ day.dayNum }}</div>
                                            <div class="text-[10px] opacity-60">{{ day.monthName }}</div>
                                        </button>
                                    </div>

                                    <div v-if="selectedDate">
                                        <div v-if="loadingSlots" class="muted py-6 text-center text-sm">جاري التحميل...</div>
                                        <div v-else-if="filteredSlots.length === 0" class="muted py-6 text-center text-sm">لا توجد أوقات متاحة، جرّب تاريخاً آخر.</div>
                                        <div v-else class="grid grid-cols-3 gap-2">
                                            <button v-for="(slot, index) in filteredSlots" :key="index" @click="chooseTime(slot)" dir="ltr" class="row py-2.5 text-center text-sm font-bold tabular-nums transition">
                                                {{ typeof slot === 'object' ? (slot.formatted || slot.time) : slot }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4: التأكيد -->
                                <div v-else-if="step === 4" class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-bold">تأكيد الحجز</h4>
                                        <button @click="goBackFrom(4)" class="muted text-xs font-bold hover:opacity-70">تغيير الوقت</button>
                                    </div>

                                    <div class="tint space-y-2 rounded-2xl p-4 text-sm">
                                        <div class="flex justify-between"><span class="muted">الموظف</span><strong>{{ selectedStaff.user.name }}</strong></div>
                                        <div class="flex justify-between"><span class="muted">الموعد</span><strong dir="ltr">{{ selectedDate }} — {{ selectedTime }}</strong></div>
                                    </div>

                                    <form @submit.prevent="submit" class="space-y-3">
                                        <div>
                                            <input type="text" v-model="form.customer_name" placeholder="الاسم الكامل" class="inp w-full rounded-2xl px-4 py-3.5 text-sm" required />
                                            <div v-if="form.errors.customer_name" class="mt-1 text-xs text-red-500">{{ form.errors.customer_name }}</div>
                                        </div>
                                        <div>
                                            <input type="tel" v-model="form.customer_phone" placeholder="رقم الهاتف" class="inp w-full rounded-2xl px-4 py-3.5 text-sm" required />
                                            <div v-if="form.errors.customer_phone" class="mt-1 text-xs text-red-500">{{ form.errors.customer_phone }}</div>
                                        </div>
                                        <div v-if="form.errors.start_time" class="text-xs text-red-500">{{ form.errors.start_time }}</div>
                                        <button type="submit" :disabled="form.processing" class="btn-p flex w-full items-center justify-center gap-2 rounded-2xl py-4 text-sm font-bold shadow-lg transition hover:brightness-110 disabled:opacity-50">
                                            <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            {{ form.processing ? 'جاري الإرسال...' : 'تأكيد الحجز' }}
                                        </button>
                                    </form>
                                </div>
                            </template>
                        </div>
                    </div>
                </aside>
            </div>
        </main>

        <footer class="line border-t py-8 text-center text-xs muted">
            {{ shop?.name }} · مدعوم بـ وقتي
        </footer>

        <!-- عارض الصور -->
        <div v-if="lightboxImage" @click="closeLightbox" class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4">
            <button class="absolute left-5 top-5 flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-2xl text-white" aria-label="إغلاق">×</button>
            <img :src="lightboxImage" class="max-h-[88vh] max-w-full rounded-2xl object-contain shadow-2xl" alt="" />
        </div>

        <!-- واتساب عائم -->
        <a v-if="whatsappLink" :href="whatsappLink" target="_blank" rel="noopener" aria-label="واتساب" class="fixed bottom-6 left-6 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500 text-white shadow-2xl transition hover:scale-105">
            <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24"><path :d="ICONS.whatsapp" /></svg>
        </a>
    </div>
</template>

<style scoped>
.pg { font-family: 'IBM Plex Sans Arabic', ui-sans-serif, system-ui, sans-serif; }
.font-display { font-family: 'Reem Kufi', 'IBM Plex Sans Arabic', ui-sans-serif, sans-serif; }

.muted { color: color-mix(in srgb, var(--t) 58%, transparent); }
.line { border-color: var(--line); }
.tint { background: color-mix(in srgb, var(--t) 5%, transparent); }
.soft { background: color-mix(in srgb, var(--p) 12%, transparent); color: var(--p); }
.soft-bg { background: color-mix(in srgb, var(--p) 8%, transparent); }
.btn-p { background: var(--p); color: #fff; }

.nav-solid {
    background: color-mix(in srgb, var(--card) 90%, transparent);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--line);
    color: var(--t);
}
.glass {
    background: rgb(255 255 255 / 0.12);
    border: 1px solid rgb(255 255 255 / 0.2);
    backdrop-filter: blur(12px);
}

.lead { background: repeating-linear-gradient(to left, color-mix(in srgb, var(--t) 28%, transparent) 0 2px, transparent 2px 7px); }
.svc:hover { background: color-mix(in srgb, var(--p) 7%, transparent); }
.pick-idle { background: color-mix(in srgb, var(--t) 8%, transparent); }
.svc:hover .pick-idle { background: var(--p); color: #fff; }

.panel {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 2rem;
    box-shadow: 0 30px 60px -30px rgb(0 0 0 / 0.35);
}
.row { border: 1px solid var(--line); border-radius: 1rem; }
.row:hover { border-color: var(--p); }

.inp { background: transparent; border: 1px solid var(--line); color: var(--t); }
.inp:focus { outline: none; border-color: var(--p); box-shadow: 0 0 0 3px color-mix(in srgb, var(--p) 22%, transparent); }

.map-dark { filter: invert(0.92) hue-rotate(180deg) contrast(0.9) saturate(0.75); }

.noscroll { scrollbar-width: none; }
.noscroll::-webkit-scrollbar { display: none; }

button:focus-visible, a:focus-visible { outline: 2px solid var(--p); outline-offset: 2px; }

@keyframes up { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
.up { animation: up 0.8s cubic-bezier(0.2, 0.7, 0.2, 1) both; }
@media (prefers-reduced-motion: reduce) { .up { animation: none; } }
</style>