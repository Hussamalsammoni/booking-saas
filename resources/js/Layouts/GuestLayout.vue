<!-- المسار: resources/js/Layouts/GuestLayout.vue -->
<script setup>
import { Head } from '@inertiajs/vue3'
import { Crown, Check } from 'lucide-vue-next'

defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
})

const rows = [
    { name: 'أحمد', blocks: [
        { c: 1, n: 1, t: 'خالد', s: 'ok' },
        { c: 3, n: 2, t: 'رامي', s: 'ok' },
        { c: 6, n: 1, t: 'يوسف', s: 'wait' },
    ] },
    { name: 'سارة', blocks: [
        { c: 2, n: 2, t: 'ريم', s: 'ok' },
        { c: 5, n: 2, t: 'لينا', s: 'wait' },
    ] },
    { name: 'ماهر', blocks: [
        { c: 1, n: 2, t: 'عمر', s: 'ok' },
        { c: 4, n: 1, t: 'هيثم', s: 'wait' },
        { c: 5, n: 2, t: 'سامر', s: 'ok' },
    ] },
]

const points = [
    'رابط حجز خاص بمحلك',
    'تأكيدات وتذكيرات على واتساب',
    'فواتير وتقارير جاهزة',
]
</script>

<template>
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600&family=Lalezar&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="g-page" dir="rtl">
        <main class="g-main">
            <a href="/" class="g-brand" aria-label="وقتي">
                <span class="g-logo"><Crown :size="20" /></span>
                <span class="g-brand-ar">وقتي</span>
                <span class="g-brand-en">Wa9ti</span>
            </a>

            <div class="g-card-wrap">
                <div class="g-card">
                    <header v-if="title" class="g-head">
                        <h1 class="g-title">{{ title }}</h1>
                        <p v-if="subtitle" class="g-sub">{{ subtitle }}</p>
                    </header>

                    <slot />
                </div>

                <div v-if="$slots.footer" class="g-foot">
                    <slot name="footer" />
                </div>
            </div>
        </main>

        <aside class="g-visual" aria-hidden="true">
            <div>
                <h2 class="g-v-h">مواعيد محلك<br />تتحجز وحدها</h2>
                <p class="g-v-p">
                    رابط حجز خاص بمحلك، ولوحة واحدة تتابع منها الموظفين والفواتير والتقارير.
                </p>
            </div>

            <div class="g-mini">
                <div v-for="(r, ri) in rows" :key="r.name" class="g-mini-row">
                    <span class="g-mini-l">{{ r.name }}</span>
                    <div class="g-mini-lane">
                        <span
                            v-for="(b, bi) in r.blocks"
                            :key="b.t"
                            class="g-mini-b"
                            :class="b.s"
                            :style="{ gridColumn: `${b.c} / span ${b.n}`, animationDelay: `${(ri * 3 + bi) * 120 + 200}ms` }"
                        >{{ b.t }}</span>
                    </div>
                </div>
            </div>

            <ul class="g-v-list">
                <li v-for="p in points" :key="p">
                    <span class="g-v-ck"><Check :size="14" /></span>{{ p }}
                </li>
            </ul>
        </aside>
    </div>
</template>

<style>
/* أنماط مشتركة لكل صفحات الدخول والتسجيل (نفس ألوان وخطوط صفحة الهبوط) */
.g-page {
    --paper: #eef2f9;
    --ink: #0a1a3f;
    --ink-2: #3f5079;
    --cobalt: #2b59ff;
    --mint: #12b886;
    --amber: #f59f00;
    --line: #d3dcec;

    min-height: 100vh;
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    background: var(--paper);
    color: var(--ink);
    font-family: 'IBM Plex Sans Arabic', Tahoma, system-ui, sans-serif;
    line-height: 1.8;
    -webkit-font-smoothing: antialiased;
}
.g-page *, .g-page *::before, .g-page *::after { box-sizing: border-box; }
.g-page :focus-visible { outline: 3px solid var(--cobalt); outline-offset: 3px; }
.g-page h1, .g-page h2, .g-page p, .g-page ul { margin: 0; padding: 0; }
.g-page ul { list-style: none; }
.g-page a { text-decoration: none; }
.g-page button { font-family: inherit; cursor: pointer; }

.g-main { display: flex; flex-direction: column; padding: 1.5rem; min-width: 0; }

/* العلامة */
.g-brand { display: inline-flex; align-items: center; gap: .6rem; align-self: flex-start; color: var(--ink); }
.g-logo { display: grid; place-items: center; width: 2.25rem; height: 2.25rem; border-radius: .7rem; background: var(--cobalt); color: #fff; }
.g-brand-ar { font-family: 'Lalezar', 'IBM Plex Sans Arabic', sans-serif; font-size: 1.7rem; line-height: 1; }
.g-brand-en { font-size: .8rem; color: var(--ink-2); direction: ltr; }

/* الكارد */
.g-card-wrap { width: 100%; max-width: 28rem; margin: auto; padding-block: 2rem; }
.g-card {
    background: #fff; border: 1px solid var(--line); border-radius: 1.75rem;
    padding: 2rem 1.75rem; box-shadow: 0 24px 48px -30px rgba(10, 26, 63, .35);
}
.g-head { margin-bottom: 1.5rem; }
.g-title { font-family: 'Lalezar', 'IBM Plex Sans Arabic', sans-serif; font-weight: 400; font-size: 2rem; line-height: 1.35; }
.g-sub { margin-top: .2rem; color: var(--ink-2); font-size: .95rem; }
.g-foot { margin-top: 1.2rem; text-align: center; font-size: .92rem; color: var(--ink-2); }
.g-foot a { color: var(--cobalt); font-weight: 600; }
.g-foot a:hover { text-decoration: underline; }

/* الحقول */
.g-field { margin-top: 1rem; }
.g-field:first-child { margin-top: 0; }
.g-label { display: block; margin-bottom: .35rem; font-weight: 600; font-size: .9rem; }
.g-input {
    width: 100%; padding: .8rem 1rem; border: 1.5px solid var(--line); border-radius: .8rem;
    background: #f7f9fd; color: var(--ink); font: inherit; font-size: 1rem; line-height: 1.5;
    transition: border-color .15s, box-shadow .15s, background-color .15s;
}
.g-input::placeholder { color: #93a0bd; }
.g-input:focus { outline: none; border-color: var(--cobalt); background: #fff; box-shadow: 0 0 0 3px rgba(43, 89, 255, .18); }
.g-input.ltr { direction: ltr; text-align: left; }
.g-input.invalid { border-color: #e03131; }
.g-pw { position: relative; }
.g-pw .g-input { padding-right: 2.9rem; }
.g-eye {
    position: absolute; top: 50%; right: .55rem; transform: translateY(-50%);
    display: grid; place-items: center; width: 2rem; height: 2rem; border: 0; border-radius: .6rem;
    background: transparent; color: #7d8aa8;
}
.g-eye:hover { color: var(--ink); background: #eaeff8; }
.g-err { margin-top: .35rem; color: #c92a2a; font-size: .86rem; font-weight: 500; }
.g-status { margin-bottom: 1rem; padding: .7rem .9rem; border-radius: .8rem; background: #e6f8f1; color: #0b7a58; font-size: .9rem; font-weight: 500; }
.g-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 1rem; flex-wrap: wrap; }
.g-check { display: inline-flex; align-items: center; gap: .5rem; font-size: .9rem; color: var(--ink-2); cursor: pointer; }
.g-check input { width: 1.05rem; height: 1.05rem; accent-color: var(--cobalt); }
.g-link { color: var(--cobalt); font-size: .9rem; font-weight: 600; }
.g-link:hover { text-decoration: underline; }
.g-two { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0; }
.g-captcha { margin-top: 1rem; padding: .9rem 1rem; border-radius: 1rem; background: #eef3ff; border: 1px solid #d6e0ff; }
.g-captcha .g-label { color: var(--cobalt); margin-bottom: .45rem; }

/* الأزرار */
.g-btn {
    display: inline-flex; align-items: center; justify-content: center; width: 100%; margin-top: 1.4rem;
    padding: .95rem 1.5rem; border: 0; border-radius: .9rem; background: var(--cobalt); color: #fff;
    font-size: 1.05rem; font-weight: 600; transition: background-color .15s, opacity .15s;
}
.g-btn:hover { background: #1f47d9; }
.g-btn:disabled { opacity: .65; cursor: wait; }

/* اللوحة البصرية (على الشاشات الكبيرة فقط) */
.g-visual { display: none; }
@media (min-width: 64rem) {
    .g-page { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
    .g-main { padding: 2rem 3rem; }
    .g-visual {
        display: flex; flex-direction: column; justify-content: space-between; gap: 2.5rem;
        margin: 1rem; padding: 3rem 2.75rem; border-radius: 2rem; background: var(--ink); color: #fff;
    }
}
.g-v-h { font-family: 'Lalezar', 'IBM Plex Sans Arabic', sans-serif; font-weight: 400; font-size: clamp(2.6rem, 4.4vw, 3.8rem); line-height: 1.25; }
.g-v-p { margin-top: 1rem; max-width: 26rem; color: #b6c4e6; font-size: 1.05rem; }
.g-mini { display: flex; flex-direction: column; gap: .5rem; }
.g-mini-row { display: flex; align-items: stretch; gap: .6rem; }
.g-mini-l { flex: 0 0 3.2rem; display: flex; align-items: center; font-size: .82rem; color: #c8d3ee; font-weight: 600; }
.g-mini-lane {
    flex: 1; display: grid; grid-template-columns: repeat(6, 1fr); min-height: 2.9rem; border-radius: .8rem;
    background-color: rgba(255, 255, 255, .05);
    background-image: repeating-linear-gradient(to left, transparent 0, transparent calc(16.666% - 1px), rgba(255, 255, 255, .09) calc(16.666% - 1px), rgba(255, 255, 255, .09) 16.666%);
}
.g-mini-b {
    display: flex; align-items: center; margin: 4px 2px; padding: 0 .6rem; border-radius: .55rem;
    font-size: .8rem; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden;
    opacity: 0; animation: g-pop .4s ease forwards;
}
.g-mini-b.ok { background: var(--mint); }
.g-mini-b.wait { background: var(--amber); }
.g-v-list { display: flex; flex-direction: column; gap: .7rem; }
.g-v-list li { display: flex; align-items: center; gap: .7rem; color: #dfe7fb; }
.g-v-ck { display: grid; place-items: center; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: rgba(18, 184, 134, .2); color: var(--mint); flex: none; }

@keyframes g-pop { from { opacity: 0; transform: scale(.85); } to { opacity: 1; transform: none; } }
@media (prefers-reduced-motion: reduce) {
    .g-mini-b { animation: none; opacity: 1; }
    .g-btn, .g-input { transition: none; }
}
@media (min-width: 40rem) {
    .g-card { padding: 2.25rem 2.25rem; }
    .g-two { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 .9rem; }
}
</style>