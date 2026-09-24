<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import {
    Crown,
    MessageCircle,
    FileText,
    BarChart3,
    Users,
    Timer,
    Database,
    Scissors,
    Sparkles,
    Smartphone,
    Check,
    X,
} from 'lucide-vue-next'

defineProps({
    canLogin: { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },

    // رابط التسجيل على الـ Central Domain
    registerUrl: { type: String, default: '/register' },

    laravelVersion: String,
    phpVersion: String,
})

/* ───────── لوحة اليوم ───────── */

const hours = ['10 ص', '11 ص', '12 م', '1 م', '2 م', '3 م', '4 م', '5 م']

const rowsSeed = [
    {
        name: 'أحمد',
        role: 'حلاق',
        icon: Scissors,
        blocks: [
            { col: 1, span: 1, name: 'خالد', svc: 'حلاقة', status: 'confirmed' },
            { col: 3, span: 2, name: 'رامي', svc: 'حلاقة وذقن', status: 'confirmed' },
            { col: 6, span: 1, name: 'يوسف', svc: 'حلاقة', status: 'pending' },
        ],
    },
    {
        name: 'سارة',
        role: 'خبيرة تجميل',
        icon: Sparkles,
        blocks: [
            { col: 2, span: 2, name: 'ريم', svc: 'صبغة', status: 'confirmed' },
            { col: 5, span: 3, name: 'لينا', svc: 'تسريحة عروس', status: 'pending' },
            { col: 1, span: 1, name: 'نور', svc: 'عناية', status: 'new', isNew: true },
        ],
    },
    {
        name: 'ماهر',
        role: 'فني صيانة',
        icon: Smartphone,
        blocks: [
            { col: 1, span: 2, name: 'عمر', svc: 'تغيير شاشة', status: 'confirmed' },
            { col: 4, span: 1, name: 'هيثم', svc: 'بطارية', status: 'pending' },
            { col: 6, span: 2, name: 'سامر', svc: 'فورمات', status: 'confirmed' },
        ],
    },
]

// ترتيب ظهور الحجوزات
const all = rowsSeed.flatMap((r, ri) =>
    r.blocks.map((b) => ({ b, ri }))
)

all
    .filter((x) => !x.b.isNew)
    .sort((a, b) => a.b.col - b.b.col || a.ri - b.ri)
    .forEach((x, i) => {
        x.b.order = i
    })

const total = all.length

all.find((x) => x.b.isNew).b.order = total - 1

const rows = rowsSeed

const shown = ref(0)

const shownCount = computed(() => shown.value)

const pendingCount = computed(
    () =>
        all.filter(
            (x) =>
                x.b.order < shown.value &&
                x.b.status !== 'confirmed'
        ).length
)

const toastIn = computed(() => shown.value >= total)

let startTimer = null
let tickTimer = null

onMounted(() => {
    const reduce = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches

    if (reduce) {
        shown.value = total
        return
    }

    startTimer = setTimeout(() => {
        tickTimer = setInterval(() => {
            if (shown.value >= total) {
                clearInterval(tickTimer)
                return
            }

            shown.value += 1
        }, 450)
    }, 600)

    window.addEventListener('keydown', onKey)
})

onBeforeUnmount(() => {
    clearTimeout(startTimer)
    clearInterval(tickTimer)
    window.removeEventListener('keydown', onKey)
})

/* ───────── صفحة الحجز التجريبية ───────── */

const times = [
    { t: '10:00' },
    { t: '10:30', taken: true },
    { t: '11:00' },
    { t: '11:30', taken: true },
    { t: '12:00' },
    { t: '12:30' },
    { t: '1:00', taken: true },
    { t: '1:30' },
    { t: '2:00' },
]

const picked = ref('11:00')
const booked = ref(false)

function pick(t) {
    picked.value = t
    booked.value = false
}

/* ───────── تسجيل الدخول ───────── */

const loginOpen = ref(false)
const shop = ref('')
const shopError = ref('')
const checking = ref(false)
const shopInput = ref(null)

function openLogin() {
    loginOpen.value = true
    shopError.value = ''

    nextTick(() => shopInput.value?.focus())
}

function closeLogin() {
    loginOpen.value = false
}

function onKey(e) {
    if (e.key === 'Escape') {
        closeLogin()
    }
}

async function goToShop() {
    shopError.value = ''

    const slug = shop.value
        .trim()
        .toLowerCase()
        .replace(/^https?:\/\//, '')
        .split('.')[0]
        .replace(/[^a-z0-9-]/g, '')

    if (!slug) {
        shopError.value =
            'اكتب معرّف محلك بالأحرف الإنكليزية، مثل: royal-look'

        return
    }

    checking.value = true

    try {
        const res = await fetch(
            `/shop-lookup?shop=${encodeURIComponent(slug)}`,
            {
                headers: {
                    Accept: 'application/json',
                },
            }
        )

        const data = await res.json().catch(() => ({}))

        if (res.status === 429) {
            shopError.value =
                'محاولات كثيرة، انتظر دقيقة وحاول مرة ثانية.'

            return
        }

        if (!res.ok || !data.domain) {
            shopError.value =
                data.message ||
                'ما لقينا محلاً بهذا المعرّف. تأكد من كتابته وحاول مرة ثانية.'

            return
        }

        const port = window.location.port
            ? `:${window.location.port}`
            : ''

        window.location.href =
            `${window.location.protocol}//${data.domain}${port}/login`

    } catch (e) {
        shopError.value =
            'تعذّر الاتصال بالخادم، حاول مرة ثانية.'
    } finally {
        checking.value = false
    }
}

/* ───────── المحتوى ───────── */

const features = [
    {
        icon: Timer,
        title: 'لا مواعيد متداخلة',
        text: 'النظام يمنع حجز الموظف في وقتين معاً، ويترك فاصل 5 دقائق بين موعد وآخر ليرتاح ويجهّز.',
    },
    {
        icon: MessageCircle,
        title: 'تأكيد وتذكير على واتساب',
        text: 'يصل الزبون رسالة عند تأكيد الحجز أو إلغائه، وتذكير قبل موعده.',
    },
    {
        icon: FileText,
        title: 'فواتير عربية جاهزة',
        text: 'تُنشأ الفاتورة برقم متسلسل عند إنهاء الموعد وتنزّل PDF. سجّل الدفع نقداً أو عبر شام كاش برقم العملية.',
    },
    {
        icon: BarChart3,
        title: 'تقارير تُفهم بنظرة',
        text: 'الإيراد عبر الزمن، أكثر الخدمات طلباً، وأداء كل موظف، مع فلتر بالتاريخ.',
    },
    {
        icon: Users,
        title: 'صلاحيات لكل موظف',
        text: 'الموظف يرى مواعيده فقط، وصاحب المحل يرى كل شيء ويتحكم بمن يشاهد حجوزات الآخرين.',
    },
    {
        icon: Database,
        title: 'بيانات محلك لك وحدك',
        text: 'لكل محل قاعدة بيانات مستقلة وعنوان خاص به، فلا تختلط بياناتك بأحد.',
    },
]

const steps = [
    {
        title: 'سجّل محلك',
        text: 'اكتب اسم المحل وأنشئ حسابك، فيتجهّز لك نظامك الخاص مع رابط حجز باسمك.',
    },
    {
        title: 'أضف خدماتك وموظفيك',
        text: 'حدّد الخدمات ومدتها وأسعارها، وساعات دوام كل موظف.',
    },
    {
        title: 'شارك رابط الحجز',
        text: 'انسخه من لوحة التحكم وضعه في واتساب وإنستغرام، وزبونك يحجز بالاسم ورقم الهاتف فقط.',
    },
]
</script>

<template>
    <Head title="وقتي | نظام حجوزات لمحلك">
        <meta
            name="description"
            content="نظام حجوزات لصالونات التجميل والحلاقة ومراكز الصيانة: رابط حجز خاص بمحلك، تذكير واتساب، فواتير وتقارير."
        />

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin="anonymous"
        />

        <link
            href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600&family=Lalezar&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="w-page" dir="rtl">

        <!-- الشريط العلوي -->
        <header class="w-nav">
            <div class="w-wrap w-nav-in">

                <a href="/" class="w-brand" aria-label="وقتي">
                    <span class="w-logo">
                        <Crown :size="20" />
                    </span>

                    <span class="w-brand-ar">وقتي</span>
                    <span class="w-brand-en">Wa9ti</span>
                </a>

                <nav class="w-links" aria-label="أقسام الصفحة">
                    <a href="#features">المزايا</a>
                    <a href="#how">كيف يبدأ محلك</a>
                </nav>

                <div class="w-nav-cta">

                    <button
                        v-if="canLogin"
                        type="button"
                        class="w-btn w-btn-ghost"
                        @click="openLogin"
                    >
                        تسجيل الدخول
                    </button>

                    <!-- التسجيل على Central Domain -->
                    <a
                        v-if="canRegister"
                        :href="registerUrl"
                        class="w-btn w-btn-solid"
                    >
                        إنشاء حساب
                    </a>

                </div>
            </div>
        </header>

        <main>

            <!-- الواجهة الرئيسية -->
            <section class="w-hero">
                <div class="w-wrap w-hero-grid">

                    <div class="w-hero-copy">

                        <h1 class="w-h1">
                            مواعيد محلك<br />
                            تتحجز وحدها
                        </h1>

                        <p class="w-lead">
                            نظام حجوزات لصالونات التجميل ومحلات الحلاقة ومراكز صيانة الموبايل.
                            زبونك يحجز من رابط خاص بمحلك، وأنت تتابع الموظفين والفواتير والتقارير من لوحة واحدة.
                        </p>

                        <div class="w-hero-cta">

                            <!-- التسجيل على Central Domain -->
                            <a
                                v-if="canRegister"
                                :href="registerUrl"
                                class="w-btn w-btn-solid w-btn-lg"
                            >
                                أنشئ حساب محلك
                            </a>

                            <button
                                v-if="canLogin"
                                type="button"
                                class="w-btn w-btn-ghost w-btn-lg"
                                @click="openLogin"
                            >
                                لدي حساب
                            </button>

                        </div>

                        <p class="w-note">
                            يتجهّز نظامك ورابط حجز محلك فور التسجيل.
                        </p>

                    </div>

                    <!-- لوحة الحجوزات -->
                    <div
                        class="w-board"
                        role="img"
                        aria-label="مثال توضيحي: لوحة حجوزات اليوم تمتلئ بالمواعيد لثلاثة موظفين"
                    >

                        <div class="w-board-top">

                            <div>
                                <div class="w-board-title">
                                    حجوزات اليوم
                                </div>

                                <div class="w-board-sub">
                                    مثال توضيحي
                                </div>
                            </div>

                            <div class="w-board-stats">

                                <div>
                                    <b>{{ shownCount }}</b>
                                    <span>حجز</span>
                                </div>

                                <div>
                                    <b>{{ pendingCount }}</b>
                                    <span>بانتظار التأكيد</span>
                                </div>

                            </div>
                        </div>

                        <div class="w-board-scroll">

                            <div class="w-board-inner">

                                <div class="w-row w-row-hours">

                                    <div class="w-lane-label"></div>

                                    <div class="w-hours">
                                        <span
                                            v-for="h in hours"
                                            :key="h"
                                        >
                                            {{ h }}
                                        </span>
                                    </div>

                                </div>

                                <div
                                    v-for="row in rows"
                                    :key="row.name"
                                    class="w-row"
                                >

                                    <div class="w-lane-label">

                                        <component
                                            :is="row.icon"
                                            :size="16"
                                        />

                                        <span>
                                            <b>{{ row.name }}</b>
                                            <small>{{ row.role }}</small>
                                        </span>

                                    </div>

                                    <div class="w-lane">

                                        <div
                                            v-for="b in row.blocks"
                                            :key="b.name"
                                            class="w-block"
                                            :class="[
                                                b.status,
                                                { in: b.order < shown }
                                            ]"
                                            :style="{
                                                gridColumn: `${b.col} / span ${b.span}`
                                            }"
                                        >
                                            <b>{{ b.name }}</b>
                                            <small>{{ b.svc }}</small>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="w-legend">
                            <span>
                                <i class="dot confirmed"></i>
                                مؤكد
                            </span>

                            <span>
                                <i class="dot pending"></i>
                                بانتظار التأكيد
                            </span>

                            <span>
                                <i class="dot new"></i>
                                جديد من رابط الحجز
                            </span>
                        </div>

                        <div
                            class="w-toast"
                            :class="{ in: toastIn }"
                            role="status"
                        >

                            <span class="w-toast-ic">
                                <MessageCircle :size="16" />
                            </span>

                            <span>
                                <b>حجز جديد من رابط الحجز</b>
                                <small>
                                    نور اختارت العناية مع سارة الساعة 10 ص
                                </small>
                            </span>

                        </div>

                    </div>
                </div>
            </section>

            <!-- المزايا -->
            <section id="features" class="w-section">

                <div class="w-wrap">

                    <h2 class="w-h2">
                        من الحجز إلى الفاتورة في مكان واحد
                    </h2>

                    <div class="w-feat-grid">

                        <ul class="w-feat-list">

                            <li
                                v-for="f in features"
                                :key="f.title"
                            >

                                <span class="w-feat-ic">
                                    <component
                                        :is="f.icon"
                                        :size="20"
                                    />
                                </span>

                                <div>
                                    <h3>{{ f.title }}</h3>
                                    <p>{{ f.text }}</p>
                                </div>

                            </li>

                        </ul>

                        <!-- صفحة الحجز -->
                        <div class="w-phone-wrap">

                            <div
                                class="w-phone"
                                aria-label="مثال تفاعلي لصفحة الحجز كما يراها الزبون"
                            >

                                <div class="w-phone-head">

                                    <span class="w-logo sm">
                                        <Crown :size="14" />
                                    </span>

                                    <b>ROYAL LOOK</b>

                                </div>

                                <div class="w-phone-body">

                                    <div class="w-phone-q">
                                        اختر الوقت المناسب
                                    </div>

                                    <div class="w-phone-sub">
                                        حلاقة مع أحمد، الأربعاء
                                    </div>

                                    <div class="w-chips">

                                        <button
                                            v-for="t in times"
                                            :key="t.t"
                                            type="button"
                                            class="w-chip"
                                            :class="{
                                                on: picked === t.t,
                                                taken: t.taken
                                            }"
                                            :disabled="t.taken"
                                            :aria-pressed="picked === t.t"
                                            @click="pick(t.t)"
                                        >
                                            {{ t.t }}
                                        </button>

                                    </div>

                                    <div class="w-fake">
                                        الاسم
                                    </div>

                                    <div class="w-fake">
                                        رقم الهاتف
                                    </div>

                                    <button
                                        type="button"
                                        class="w-phone-btn"
                                        :class="{ done: booked }"
                                        @click="booked = true"
                                    >

                                        <template v-if="booked">
                                            <Check :size="16" />
                                            تم إرسال طلب الحجز
                                        </template>

                                        <template v-else>
                                            تأكيد حجز {{ picked }}
                                        </template>

                                    </button>

                                </div>
                            </div>

                            <p class="w-phone-cap">
                                جرّب اختيار وقت. زبونك يحجز بدون إنشاء حساب.
                            </p>

                        </div>

                    </div>
                </div>
            </section>

            <!-- خطوات البدء -->
            <section id="how" class="w-section w-section-tight">

                <div class="w-wrap">

                    <h2 class="w-h2">
                        محلك جاهز لاستقبال الحجوزات في ثلاث خطوات
                    </h2>

                    <ol class="w-steps">

                        <li
                            v-for="(s, i) in steps"
                            :key="s.title"
                        >

                            <span class="w-step-n">
                                {{ i + 1 }}
                            </span>

                            <h3>{{ s.title }}</h3>

                            <p>{{ s.text }}</p>

                        </li>

                    </ol>

                </div>
            </section>

            <!-- الدعوة الأخيرة -->
            <section class="w-section w-section-tight">

                <div class="w-wrap">

                    <div class="w-cta">

                        <div>

                            <h2 class="w-cta-h">
                                ابدأ بترتيب مواعيد محلك اليوم
                            </h2>

                            <p>
                                أنشئ حساباً لمحلك، أو ادخل إلى حسابك إن كان لديك واحد.
                            </p>

                        </div>

                        <div class="w-cta-btns">

                            <!-- التسجيل على Central Domain -->
                            <a
                                v-if="canRegister"
                                :href="registerUrl"
                                class="w-btn w-btn-light w-btn-lg"
                            >
                                إنشاء حساب
                            </a>

                            <button
                                v-if="canLogin"
                                type="button"
                                class="w-btn w-btn-outline-light w-btn-lg"
                                @click="openLogin"
                            >
                                تسجيل الدخول
                            </button>

                        </div>

                    </div>
                </div>
            </section>

        </main>

        <footer class="w-footer">

            <div class="w-wrap w-footer-in">

                <span>
                    وقتي (Wa9ti) © 2026
                </span>

                <a href="/admin/login">
                    لوحة الإدارة
                </a>

            </div>
        </footer>

        <!-- نافذة تسجيل الدخول -->
        <div
            v-if="loginOpen"
            class="w-overlay"
            @click.self="closeLogin"
        >

            <div
                class="w-modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="w-login-title"
            >

                <button
                    type="button"
                    class="w-modal-x"
                    aria-label="إغلاق"
                    @click="closeLogin"
                >
                    <X :size="18" />
                </button>

                <h2 id="w-login-title">
                    تسجيل الدخول إلى محلك
                </h2>

                <p>
                    لكل محل عنوانه الخاص. اكتب معرّف محلك،
                    وهو أول جزء من رابط الحجز الخاص بك.
                </p>

                <form @submit.prevent="goToShop">

                    <label for="w-shop">
                        معرّف المحل
                    </label>

                    <input
                        id="w-shop"
                        ref="shopInput"
                        v-model="shop"
                        type="text"
                        @input="shopError = ''"
                        dir="ltr"
                        inputmode="url"
                        autocomplete="off"
                        placeholder="royal-look"
                    />

                    <div
                        v-if="shopError"
                        class="w-err"
                    >
                        {{ shopError }}
                    </div>

                    <button
                        type="submit"
                        class="w-btn w-btn-solid w-btn-lg w-full"
                        :disabled="checking"
                    >
                        {{
                            checking
                                ? 'جارٍ التحقق...'
                                : 'متابعة إلى تسجيل الدخول'
                        }}
                    </button>

                </form>

                <!-- التسجيل على Central Domain -->
                <a
                    v-if="canRegister"
                    :href="registerUrl"
                    class="w-modal-alt"
                >
                    ليس لديك محل بعد؟ أنشئ حساباً
                </a>

            </div>
        </div>

    </div>
</template>

<style scoped>
.w-page {
    --paper: #eef2f9;
    --ink: #0a1a3f;
    --ink-2: #3f5079;
    --cobalt: #2b59ff;
    --mint: #12b886;
    --amber: #f59f00;
    --line: #d3dcec;
    --white: #ffffff;

    min-height: 100vh;
    background: var(--paper);
    color: var(--ink);
    font-family: 'IBM Plex Sans Arabic', Tahoma, system-ui, sans-serif;
    font-size: 1rem;
    line-height: 1.8;
    -webkit-font-smoothing: antialiased;
}

.w-page *,
.w-page *::before,
.w-page *::after {
    box-sizing: border-box;
}

.w-page :focus-visible {
    outline: 3px solid var(--cobalt);
    outline-offset: 3px;
}

.w-page h1,
.w-page h2,
.w-page h3,
.w-page p,
.w-page ul,
.w-page ol {
    margin: 0;
    padding: 0;
}

.w-page ul,
.w-page ol {
    list-style: none;
}

.w-page a {
    text-decoration: none;
}

.w-page a:not(.w-btn) {
    color: inherit;
}

.w-page button {
    font-family: inherit;
    cursor: pointer;
}

.w-wrap {
    width: 100%;
    max-width: 74rem;
    margin-inline: auto;
    padding-inline: 1.5rem;
}

.w-full {
    width: 100%;
}

/* ───── أزرار ───── */

.w-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    padding: .7rem 1.25rem;
    border-radius: .9rem;
    border: 1.5px solid transparent;
    font-size: .95rem;
    font-weight: 600;
    line-height: 1.4;
    white-space: nowrap;
    transition:
        background-color .15s,
        border-color .15s,
        color .15s;
}

.w-btn-lg {
    padding: .95rem 1.7rem;
    font-size: 1.05rem;
}

.w-btn-solid {
    background: var(--cobalt);
    color: var(--white);
}

.w-btn-solid:hover {
    background: #1f47d9;
}

.w-btn-ghost {
    background: transparent;
    color: var(--ink);
    border-color: var(--line);
}

.w-btn-ghost:hover {
    border-color: var(--ink);
}

.w-btn-light {
    background: var(--white);
    color: var(--ink);
}

.w-btn-light:hover {
    background: #e3eaff;
}

.w-btn-outline-light {
    background: transparent;
    color: var(--white);
    border-color: rgba(255,255,255,.4);
}

.w-btn-outline-light:hover {
    border-color: var(--white);
}

/* ───── الشريط العلوي ───── */

.w-nav {
    position: sticky;
    top: 0;
    z-index: 30;
    background: rgba(238, 242, 249, .88);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--line);
}

.w-nav-in {
    display: flex;
    align-items: center;
    gap: 2rem;
    min-height: 4.25rem;
}

.w-brand {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
}

.w-logo {
    display: grid;
    place-items: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: .7rem;
    background: var(--cobalt);
    color: var(--white);
}

.w-logo.sm {
    width: 1.75rem;
    height: 1.75rem;
    border-radius: .5rem;
}

.w-brand-ar {
    font-family: 'Lalezar', 'IBM Plex Sans Arabic', sans-serif;
    font-size: 1.7rem;
    line-height: 1;
}

.w-brand-en {
    font-size: .8rem;
    color: var(--ink-2);
    direction: ltr;
}

.w-links {
    display: flex;
    gap: 1.5rem;
    margin-inline-end: auto;
}

.w-links a {
    color: var(--ink-2);
    font-weight: 500;
}

.w-links a:hover {
    color: var(--ink);
}

.w-nav-cta {
    display: flex;
    gap: .6rem;
}

/* ───── الواجهة الرئيسية ───── */

.w-hero {
    padding-block: 3.5rem 5.5rem;
}

.w-hero-grid {
    display: grid;
    grid-template-columns: minmax(0, 5fr) minmax(0, 7fr);
    gap: 3.5rem;
    align-items: center;
}

.w-h1 {
    font-family: 'Lalezar', 'IBM Plex Sans Arabic', sans-serif;
    font-weight: 400;
    font-size: clamp(3rem, 6.4vw, 5.2rem);
    line-height: 1.2;
    letter-spacing: 0;
}

.w-lead {
    margin-top: 1.4rem;
    max-width: 34rem;
    color: var(--ink-2);
    font-size: 1.1rem;
}

.w-hero-cta {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    margin-top: 2rem;
}

.w-note {
    margin-top: 1rem;
    font-size: .9rem;
    color: var(--ink-2);
}

/* ───── لوحة اليوم ───── */

.w-board {
    position: relative;
    background: var(--ink);
    color: var(--white);
    border-radius: 1.75rem;
    padding: 1.4rem 1.4rem 1.2rem;
    box-shadow: 0 30px 60px -28px rgba(10, 26, 63, .6);
}

.w-board-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.1rem;
}

.w-board-title {
    font-weight: 600;
    font-size: 1.05rem;
}

.w-board-sub {
    font-size: .8rem;
    color: #9fb0d6;
}

.w-board-stats {
    display: flex;
    gap: 1.5rem;
}

.w-board-stats div {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1.3;
}

.w-board-stats b {
    font-family: 'Lalezar', sans-serif;
    font-weight: 400;
    font-size: 1.9rem;
    line-height: 1.1;
    font-variant-numeric: tabular-nums;
}

.w-board-stats span {
    font-size: .78rem;
    color: #9fb0d6;
}

.w-board-scroll {
    overflow-x: auto;
    padding-bottom: .4rem;
}

.w-board-inner {
    min-width: 42rem;
}

.w-row {
    display: flex;
    align-items: stretch;
    gap: .5rem;
    margin-top: .45rem;
}

.w-row-hours {
    margin-top: 0;
}

.w-lane-label {
    flex: 0 0 6.2rem;
    display: flex;
    align-items: center;
    gap: .5rem;
    color: #c8d3ee;
}

.w-lane-label span {
    display: flex;
    flex-direction: column;
    line-height: 1.25;
}

.w-lane-label b {
    font-weight: 600;
    color: var(--white);
    font-size: .9rem;
}

.w-lane-label small {
    font-size: .72rem;
    color: #9fb0d6;
}

.w-hours {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(8, 1fr);
}

.w-hours span {
    font-size: .74rem;
    color: #9fb0d6;
    padding-inline-start: .35rem;
}

.w-lane {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    min-height: 3.7rem;
    border-radius: .8rem;
    background-color: rgba(255,255,255,.04);
    background-image:
        repeating-linear-gradient(
            to left,
            transparent 0,
            transparent calc(12.5% - 1px),
            rgba(255,255,255,.09) calc(12.5% - 1px),
            rgba(255,255,255,.09) 12.5%
        );
}

.w-block {
    display: flex;
    flex-direction: column;
    justify-content: center;
    margin: 4px 2px;
    padding: .2rem .55rem;
    border-radius: .6rem;
    overflow: hidden;
    line-height: 1.3;
    color: var(--ink);
    opacity: 0;
    transform: scale(.85);
    transform-origin: right center;
    transition:
        opacity .3s ease,
        transform .3s cubic-bezier(.2, .9, .3, 1.3);
}

.w-block.in {
    opacity: 1;
    transform: none;
}

.w-block b {
    font-size: .84rem;
    font-weight: 600;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

.w-block small {
    font-size: .72rem;
    opacity: .8;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

.w-block.confirmed {
    background: var(--mint);
}

.w-block.pending {
    background: var(--amber);
}

.w-block.new {
    background: var(--white);
    color: var(--cobalt);
    box-shadow: 0 0 0 3px rgba(43, 89, 255, .55);
}

.w-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 1.1rem;
    margin-top: .9rem;
    font-size: .78rem;
    color: #c8d3ee;
}

.w-legend span {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
}

.dot {
    width: .65rem;
    height: .65rem;
    border-radius: 50%;
    display: inline-block;
}

.dot.confirmed {
    background: var(--mint);
}

.dot.pending {
    background: var(--amber);
}

.dot.new {
    background: var(--white);
    box-shadow: 0 0 0 2px rgba(43, 89, 255, .7);
}

.w-toast {
    position: absolute;
    inset-inline-start: 1rem;
    bottom: -1.6rem;
    display: flex;
    align-items: center;
    gap: .7rem;
    padding: .7rem 1rem;
    border-radius: 1rem;
    background: var(--white);
    color: var(--ink);
    box-shadow: 0 16px 34px -14px rgba(10, 26, 63, .5);
    opacity: 0;
    transform: translateY(10px);
    transition:
        opacity .35s ease,
        transform .35s ease;
    pointer-events: none;
}

.w-toast.in {
    opacity: 1;
    transform: none;
}

.w-toast-ic {
    display: grid;
    place-items: center;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background: var(--cobalt);
    color: var(--white);
    flex: none;
}

.w-toast span:last-child {
    display: flex;
    flex-direction: column;
    line-height: 1.35;
}

.w-toast b {
    font-size: .88rem;
}

.w-toast small {
    font-size: .76rem;
    color: var(--ink-2);
}

/* ───── أقسام ───── */

.w-section {
    padding-block: 5.5rem;
}

.w-section-tight {
    padding-block: 3rem 4rem;
}

.w-h2 {
    max-width: 40rem;
    margin-bottom: 3rem;
    font-family: 'Lalezar', 'IBM Plex Sans Arabic', sans-serif;
    font-weight: 400;
    font-size: clamp(2rem, 4vw, 3rem);
    line-height: 1.3;
}

.w-feat-grid {
    display: grid;
    grid-template-columns: minmax(0, 7fr) minmax(0, 5fr);
    gap: 4rem;
    align-items: start;
}

.w-feat-list li {
    display: flex;
    gap: 1.1rem;
    padding-block: 1.35rem;
    border-top: 1px solid var(--line);
}

.w-feat-list li:last-child {
    border-bottom: 1px solid var(--line);
}

.w-feat-ic {
    flex: none;
    display: grid;
    place-items: center;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: .85rem;
    background: #dfe7ff;
    color: var(--cobalt);
}

.w-feat-list h3 {
    font-size: 1.1rem;
    font-weight: 600;
    line-height: 1.5;
}

.w-feat-list p {
    margin-top: .15rem;
    color: var(--ink-2);
    max-width: 36rem;
}

/* الهاتف */

.w-phone-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    position: sticky;
    top: 6rem;
}

.w-phone {
    width: 100%;
    max-width: 20rem;
    background: var(--white);
    border: 8px solid var(--ink);
    border-radius: 2.4rem;
    overflow: hidden;
    box-shadow: 0 26px 50px -26px rgba(10, 26, 63, .55);
}

.w-phone-head {
    display: flex;
    align-items: center;
    gap: .55rem;
    padding: 1rem 1.1rem;
    background: var(--ink);
    color: var(--white);
    direction: ltr;
    justify-content: center;
    font-size: .9rem;
}

.w-phone-body {
    padding: 1.1rem;
    display: flex;
    flex-direction: column;
    gap: .6rem;
}

.w-phone-q {
    font-weight: 600;
    font-size: 1.05rem;
}

.w-phone-sub {
    font-size: .82rem;
    color: var(--ink-2);
    margin-top: -.4rem;
}

.w-chips {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .45rem;
    margin-block: .3rem;
}

.w-chip {
    padding: .5rem 0;
    border-radius: .7rem;
    border: 1.5px solid var(--line);
    background: var(--white);
    color: var(--ink);
    font-size: .85rem;
    font-weight: 500;
    direction: ltr;
    transition:
        border-color .15s,
        background-color .15s,
        color .15s;
}

.w-chip:hover:not(:disabled) {
    border-color: var(--cobalt);
}

.w-chip.on {
    background: var(--cobalt);
    border-color: var(--cobalt);
    color: var(--white);
}

.w-chip.taken {
    background: #f1f4f9;
    color: #97a3bd;
    text-decoration: line-through;
    cursor: not-allowed;
}

.w-fake {
    padding: .6rem .8rem;
    border-radius: .7rem;
    background: #f1f4f9;
    color: #7d8aa8;
    font-size: .85rem;
}

.w-phone-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .4rem;
    margin-top: .3rem;
    padding: .8rem;
    border: 0;
    border-radius: .8rem;
    background: var(--ink);
    color: var(--white);
    font-weight: 600;
    font-size: .92rem;
    transition: background-color .2s;
}

.w-phone-btn.done {
    background: var(--mint);
    color: var(--ink);
}

.w-phone-cap {
    font-size: .85rem;
    color: var(--ink-2);
    text-align: center;
}

/* الخطوات */

.w-steps {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2.5rem;
}

.w-steps li {
    padding-top: 1.4rem;
    border-top: 3px solid var(--ink);
}

.w-step-n {
    display: block;
    font-family: 'Lalezar', sans-serif;
    font-size: 2.6rem;
    line-height: 1.1;
    color: var(--cobalt);
}

.w-steps h3 {
    margin-top: .5rem;
    font-size: 1.2rem;
    font-weight: 600;
}

.w-steps p {
    margin-top: .3rem;
    color: var(--ink-2);
    max-width: 22rem;
}

/* الدعوة الأخيرة */

.w-cta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
    flex-wrap: wrap;
    padding: 3rem 3rem;
    border-radius: 2rem;
    background: var(--ink);
    color: var(--white);
}

.w-cta-h {
    font-family: 'Lalezar', 'IBM Plex Sans Arabic', sans-serif;
    font-weight: 400;
    font-size: clamp(1.8rem, 3.4vw, 2.6rem);
    line-height: 1.35;
}

.w-cta p {
    margin-top: .3rem;
    color: #b6c4e6;
}

.w-cta-btns {
    display: flex;
    gap: .75rem;
    flex-wrap: wrap;
}

.w-footer {
    border-top: 1px solid var(--line);
    padding-block: 1.6rem;
    font-size: .88rem;
    color: var(--ink-2);
}

.w-footer-in {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.w-footer a:hover {
    color: var(--ink);
}

/* ───── نافذة الدخول ───── */

.w-overlay {
    position: fixed;
    inset: 0;
    z-index: 50;
    display: grid;
    place-items: center;
    padding: 1.25rem;
    background: rgba(10, 26, 63, .55);
}

.w-modal {
    position: relative;
    width: 100%;
    max-width: 26rem;
    padding: 2rem 1.75rem 1.6rem;
    border-radius: 1.5rem;
    background: var(--white);
    box-shadow: 0 30px 60px -20px rgba(10, 26, 63, .6);
}

.w-modal h2 {
    font-family: 'Lalezar', sans-serif;
    font-weight: 400;
    font-size: 1.8rem;
    line-height: 1.3;
}

.w-modal p {
    margin-top: .3rem;
    color: var(--ink-2);
    font-size: .95rem;
}

.w-modal form {
    display: flex;
    flex-direction: column;
    gap: .5rem;
    margin-top: 1.2rem;
}

.w-modal label {
    font-weight: 600;
    font-size: .9rem;
}

.w-modal input {
    padding: .8rem 1rem;
    border: 1.5px solid var(--line);
    border-radius: .8rem;
    font-size: 1rem;
    text-align: left;
    background: #f7f9fd;
    color: var(--ink);
}

.w-modal input:focus {
    border-color: var(--cobalt);
    outline: none;
    background: var(--white);
    box-shadow: 0 0 0 3px rgba(43, 89, 255, .2);
}

.w-err {
    color: #c92a2a;
    font-size: .88rem;
    font-weight: 500;
}

.w-btn:disabled {
    opacity: .65;
    cursor: wait;
}

.w-modal form .w-btn {
    margin-top: .6rem;
}

.w-modal-x {
    position: absolute;
    inset-block-start: 1rem;
    inset-inline-start: 1rem;
    display: grid;
    place-items: center;
    width: 2rem;
    height: 2rem;
    border: 0;
    border-radius: 50%;
    background: #f1f4f9;
    color: var(--ink);
}

.w-modal-alt {
    display: block;
    margin-top: 1.1rem;
    text-align: center;
    font-size: .9rem;
    color: var(--cobalt);
    font-weight: 600;
}

.w-modal-alt:hover {
    text-decoration: underline;
}

/* ───── شاشات أصغر ───── */

@media (max-width: 62rem) {

    .w-hero-grid,
    .w-feat-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 3.5rem;
    }

    .w-phone-wrap {
        position: static;
    }

    .w-steps {
        grid-template-columns: minmax(0, 1fr);
        gap: 1.8rem;
    }

    .w-links {
        display: none;
    }

    .w-nav-in {
        justify-content: space-between;
    }
}

@media (max-width: 34rem) {

    .w-wrap {
        padding-inline: 1rem;
    }

    .w-hero {
        padding-block: 2rem 4.5rem;
    }

    .w-nav-cta .w-btn-ghost {
        display: none;
    }

    .w-cta {
        padding: 2rem 1.5rem;
    }

    .w-board {
        padding: 1rem .9rem;
    }

    .w-board-stats {
        gap: 1rem;
    }
}

@media (prefers-reduced-motion: reduce) {

    .w-block,
    .w-toast,
    .w-btn,
    .w-chip,
    .w-phone-btn {
        transition: none;
    }
}
</style>