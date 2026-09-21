<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SimpleDatePicker from '@/Components/SimpleDatePicker.vue';
import { ref, computed } from 'vue';

import { Line, Bar, Doughnut } from 'vue-chartjs';

import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    BarElement,
    ArcElement,
    CategoryScale,
    LinearScale,
    PointElement,
} from 'chart.js';

import {
    Zap,
    ClipboardList,
    Users,
    Trophy,
    Crown,
    TrendingUp,
    CalendarCheck,
    Wallet,
    Medal,
    ChevronLeft,
    UserRound,
    Sparkles,
} from 'lucide-vue-next';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    BarElement,
    ArcElement,
    CategoryScale,
    LinearScale,
    PointElement
);

const props = defineProps({
    filters: Object,
    totalRevenue: Number,
    revenueByDay: Array,
    topServices: Array,
    topStaff: Array,
    bookingStatusBreakdown: Object,
});

const fromDate = ref(props.filters.from);
const toDate = ref(props.filters.to);

function applyFilter() {
    router.get(
        route('analytics.index'),
        {
            from: fromDate.value,
            to: toDate.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
}

/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

function formatCurrency(value) {
    return Number(value || 0).toLocaleString('ar-SY', {
        maximumFractionDigits: 0,
    });
}

function formatNumber(value) {
    return Number(value || 0).toLocaleString('ar-SY');
}

/*
|--------------------------------------------------------------------------
| Employee Helpers
|--------------------------------------------------------------------------
*/

function getEmployeeInitials(name) {
    if (!name) return '؟';

    const words = name.trim().split(/\s+/);

    if (words.length === 1) {
        return words[0].substring(0, 2);
    }

    return (
        words[0].charAt(0) +
        words[words.length - 1].charAt(0)
    );
}

function getEmployeeRank(index) {
    return index + 1;
}

function getRankStyle(index) {
    if (index === 0) {
        return {
            badge: 'bg-amber-50 text-amber-600 border-amber-200',
            avatar: 'bg-gradient-to-br from-amber-100 to-yellow-50 text-amber-600 border-amber-200',
        };
    }

    if (index === 1) {
        return {
            badge: 'bg-slate-100 text-slate-600 border-slate-200',
            avatar: 'bg-gradient-to-br from-slate-100 to-gray-50 text-slate-600 border-slate-200',
        };
    }

    if (index === 2) {
        return {
            badge: 'bg-orange-50 text-orange-600 border-orange-200',
            avatar: 'bg-gradient-to-br from-orange-100 to-amber-50 text-orange-600 border-orange-200',
        };
    }

    return {
        badge: 'bg-indigo-50 text-indigo-600 border-indigo-100',
        avatar: 'bg-gradient-to-br from-indigo-50 to-violet-50 text-indigo-600 border-indigo-100',
    };
}

function getEmployeeProgress(staff) {
    if (!props.topStaff?.length) return 0;

    const maxRevenue = Math.max(
        ...props.topStaff.map(
            employee => Number(employee.revenue || 0)
        )
    );

    if (!maxRevenue) return 0;

    return Math.round(
        (Number(staff.revenue || 0) / maxRevenue) * 100
    );
}

const topEmployee = computed(() => {
    if (!props.topStaff?.length) return null;

    return props.topStaff[0];
});

/*
|--------------------------------------------------------------------------
| Revenue Chart
|--------------------------------------------------------------------------
*/

const revenueChartData = computed(() => ({
    labels: props.revenueByDay.map(r => r.date),

    datasets: [
        {
            label: 'الإيرادات',
            data: props.revenueByDay.map(r => r.total),

            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.12)',

            tension: 0.35,
            fill: true,

            pointRadius: 3,
            pointHoverRadius: 6,

            borderWidth: 3,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,

    interaction: {
        mode: 'index',
        intersect: false,
    },

    animation: {
        duration: 900,
        easing: 'easeOutQuart',
    },

    plugins: {
        legend: {
            display: false,
        },

        tooltip: {
            rtl: true,
            textDirection: 'rtl',

            backgroundColor: '#111827',
            titleColor: '#fff',
            bodyColor: '#fff',

            padding: 12,
            displayColors: false,

            cornerRadius: 10,

            callbacks: {
                label(context) {
                    return `الإيرادات: ${formatCurrency(context.raw)} ل.س`;
                },
            },
        },
    },

    scales: {
        x: {
            grid: {
                display: false,
            },

            border: {
                display: false,
            },

            ticks: {
                maxRotation: 45,
                minRotation: 0,
                color: '#94a3b8',
            },
        },

        y: {
            beginAtZero: true,

            border: {
                display: false,
            },

            grid: {
                color: 'rgba(148, 163, 184, 0.12)',
            },

            ticks: {
                color: '#94a3b8',
                padding: 8,

                callback(value) {
                    return formatCurrency(value);
                },
            },
        },
    },
};

/*
|--------------------------------------------------------------------------
| Services Chart
|--------------------------------------------------------------------------
*/

const servicesChartData = computed(() => ({
    labels: props.topServices.map((s) => s.name),

    datasets: [
        {
            label: '',
            data: props.topServices.map(
                (s) => s.bookings_count
            ),

            backgroundColor: '#6366f1',

            borderRadius: 12,

            borderSkipped: false,

            barThickness: 42,

            maxBarThickness: 48,
        },
    ],
}));
const servicesChartOptions = {
    responsive: true,

    maintainAspectRatio: false,

    plugins: {
        legend: {
            display: false,
        },

        tooltip: {
            rtl: true,

            textDirection: 'rtl',

            padding: 12,

            displayColors: false,

            callbacks: {
                title: function (items) {
                    return items[0]?.label || '';
                },

                label: function (context) {
                    return `عدد الحجوزات: ${formatNumber(context.raw)}`;
                },
            },
        },
    },

    scales: {
        x: {
            grid: {
                display: false,
            },

            border: {
                display: false,
            },

            ticks: {
                color: '#64748b',

                font: {
                    size: 12,
                },
            },
        },

        y: {
            beginAtZero: true,

            grid: {
                color: 'rgba(148, 163, 184, 0.12)',
            },

            border: {
                display: false,
            },

            ticks: {
                color: '#94a3b8',

                precision: 0,

                callback: function (value) {
                    return formatNumber(value);
                },
            },
        },
    },
};

/*
|--------------------------------------------------------------------------
| Booking Status
|--------------------------------------------------------------------------
*/

const statusLabels = {
    pending: 'قيد الانتظار',
    confirmed: 'مؤكد',
    cancelled: 'ملغي',
    completed: 'مكتمل',
};

const statusColors = {
    pending: '#f59e0b',
    confirmed: '#6366f1',
    cancelled: '#ef4444',
    completed: '#22c55e',
};

const statusKeys = computed(() =>
    Object.keys(props.bookingStatusBreakdown || {})
);

const totalBookings = computed(() =>
    Object.values(props.bookingStatusBreakdown || {})
        .reduce((sum, value) => sum + Number(value), 0)
);

function getStatusPercentage(key) {
    const value = Number(
        props.bookingStatusBreakdown?.[key] || 0
    );

    if (!totalBookings.value) return 0;

    return Math.round(
        (value / totalBookings.value) * 100
    );
}

const statusChartData = computed(() => {
    const keys = statusKeys.value;

    return {
        labels: keys.map(
            key => statusLabels[key] || key
        ),

        datasets: [
            {
                data: keys.map(
                    key =>
                        props.bookingStatusBreakdown[key]
                ),

                backgroundColor: keys.map(
                    key =>
                        statusColors[key] || '#94a3b8'
                ),

                borderColor: '#ffffff',
                borderWidth: 5,

                hoverOffset: 10,

                spacing: 3,

                borderRadius: 7,
            },
        ],
    };
});

const doughnutCenterPlugin = {
    id: 'doughnutCenter',

    beforeDraw(chart) {
        if (!chart.chartArea) return;

        const {
            ctx,
            chartArea: {
                left,
                right,
                top,
                bottom,
            },
        } = chart;

        const x = (left + right) / 2;
        const y = (top + bottom) / 2;

        ctx.save();

        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        ctx.font = '700 30px Arial';
        ctx.fillStyle = '#111827';

        ctx.fillText(
            formatNumber(totalBookings.value),
            x,
            y - 9
        );

        ctx.font = '500 12px Arial';
        ctx.fillStyle = '#94a3b8';

        ctx.fillText(
            'إجمالي الحجوزات',
            x,
            y + 18
        );

        ctx.restore();
    },
};

ChartJS.register(doughnutCenterPlugin);

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,

    cutout: '70%',

    animation: {
        animateRotate: true,
        animateScale: true,

        duration: 1000,
        easing: 'easeOutQuart',
    },

    plugins: {
        legend: {
            display: false,
        },

        tooltip: {
            rtl: true,
            textDirection: 'rtl',

            backgroundColor: '#111827',

            titleColor: '#fff',
            bodyColor: '#fff',

            padding: 12,
            cornerRadius: 10,

            callbacks: {
                label(context) {
                    const value = Number(context.raw);

                    const percentage =
                        totalBookings.value
                            ? (
                                  (value /
                                      totalBookings.value) *
                                  100
                              ).toFixed(1)
                            : 0;

                    return `${value} حجز — ${percentage}%`;
                },
            },
    
        },
    },

    
};

</script>

<template>

    <Head title="التقارير والتحليلات" />

    <AuthenticatedLayout>

        <template #header>

            <div class="flex items-center gap-3">

                <div
                    class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"
                >
                    <TrendingUp class="w-5 h-5" />
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-800">
                        التقارير والتحليلات
                    </h2>

                    <p class="text-xs text-gray-400 mt-0.5">
                        نظرة ذكية على أداء المتجر
                    </p>
                </div>

            </div>

        </template>


        <div class="min-h-screen bg-[#f8fafc] py-8">

            <div
                class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
            >


                <!-- FILTER -->

                <div
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"
                >

                    <div
                        class="flex flex-wrap items-end gap-4"
                    >

                        <div>

                            <label
                                class="block text-sm font-medium text-gray-600 mb-2"
                            >
                                من
                            </label>

                            <SimpleDatePicker
                                v-model="fromDate"
                                placeholder="اختر تاريخ"
                            />

                        </div>


                        <div>

                            <label
                                class="block text-sm font-medium text-gray-600 mb-2"
                            >
                                إلى
                            </label>

                            <SimpleDatePicker
                                v-model="toDate"
                                placeholder="اختر تاريخ"
                            />

                        </div>


                        <button
                            @click="applyFilter"
                            class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-indigo-700 transition shadow-sm"
                        >
                            تطبيق
                        </button>

                    </div>

                </div>


                <!-- TOTAL REVENUE -->

                <div
                    class="relative overflow-hidden bg-white rounded-2xl border border-gray-100 shadow-sm p-6"
                >

                    <div
                        class="absolute -left-10 -top-10 w-32 h-32 bg-indigo-50 rounded-full opacity-60"
                    ></div>

                    <div class="relative">

                        <p
                            class="text-sm text-gray-500 mb-2"
                        >
                            إجمالي الإيرادات بالفترة المحددة
                        </p>

                        <div
                            class="flex items-end gap-2"
                        >

                            <p
                                class="text-4xl font-bold text-indigo-600"
                            >
                                {{ formatCurrency(totalRevenue) }}
                            </p>

                            <span
                                class="text-gray-500 mb-1"
                            >
                                ل.س
                            </span>

                        </div>

                    </div>

                </div>


                <!-- REVENUE -->

                <div
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6"
                >

                    <div
                        class="flex items-center justify-between mb-5"
                    >

                        <div>

                            <h3
                                class="font-bold text-gray-800 text-lg"
                            >
                                الإيرادات عبر الوقت
                            </h3>

                            <p
                                class="text-sm text-gray-400 mt-1"
                            >
                                متابعة الإيرادات خلال الفترة المحددة
                            </p>

                        </div>

                    </div>


                    <div class="h-[360px]">

                        <Line
                            v-if="revenueByDay.length"
                            :data="revenueChartData"
                            :options="chartOptions"
                        />

                        <div
                            v-else
                            class="h-full flex items-center justify-center text-gray-400 text-sm"
                        >
                            لا توجد بيانات بهذه الفترة
                        </div>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- SERVICES + STATUS -->
                <!-- ================================================= -->

                <div
                    class="grid grid-cols-1 lg:grid-cols-2 gap-6"
                >


                    <!-- ================= SERVICES ================= -->

                    <div
                        class="group relative overflow-hidden bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-500"
                    >

                        <!-- Decorative -->

                        <div
                            class="absolute -top-20 -right-20 w-48 h-48 rounded-full bg-indigo-50/70 blur-2xl"
                        ></div>

                        <div
                            class="absolute -bottom-20 -left-20 w-48 h-48 rounded-full bg-violet-50/60 blur-2xl"
                        ></div>


                        <div class="relative p-6">

                            <!-- Header -->

                            <div
                                class="flex items-center justify-between mb-6"
                            >

                                <div class="flex items-center gap-3">

                                    <div
                                        class="relative w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white flex items-center justify-center shadow-lg shadow-indigo-200"
                                    >
                                        <Zap
                                            class="w-6 h-6"
                                            stroke-width="2.2"
                                        />

                                        <span
                                            class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-white border-2 border-indigo-500"
                                        ></span>
                                    </div>

                                    <div>

                                        <h3
                                            class="font-bold text-gray-900 text-lg"
                                        >
                                            أفضل الخدمات
                                        </h3>

                                        <p
                                            class="text-xs text-gray-400 mt-1"
                                        >
                                            الخدمات الأكثر طلباً
                                        </p>

                                    </div>

                                </div>


                                <div
                                    v-if="topServices.length"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold"
                                >

                                    <Sparkles class="w-3.5 h-3.5" />

                                    {{ formatNumber(topServices.length) }}
                                    خدمات

                                </div>

                            </div>


                            <!-- Chart -->

                            <div class="h-[285px]">

                              <Bar
    v-if="topServices.length"
    :data="servicesChartData"
    :options="servicesChartOptions"
/>

                                <div
                                    v-else
                                    class="h-full flex items-center justify-center text-gray-400 text-sm"
                                >
                                    لا توجد بيانات بهذه الفترة
                                </div>

                            </div>


                            <!-- Services Ranking -->

                            <div
                                v-if="topServices.length"
                                class="mt-5 space-y-2"
                            >

                                <div
                                    v-for="(s, index) in topServices"
                                    :key="s.id"
                                    class="group/service relative flex items-center justify-between gap-4 p-3.5 rounded-2xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:border-indigo-100 hover:shadow-sm transition-all duration-300"
                                >

                                    <div
                                        class="flex items-center gap-3 min-w-0"
                                    >

                                        <!-- Rank -->

                                        <div
                                            class="relative w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                            :class="
                                                index === 0
                                                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200'
                                                    : 'bg-white text-gray-500 border border-gray-100'
                                            "
                                        >

                                            <Crown
                                                v-if="index === 0"
                                                class="w-4 h-4"
                                            />

                                            <span
                                                v-else
                                                class="font-bold text-sm"
                                            >
                                                {{ index + 1 }}
                                            </span>

                                        </div>


                                        <div class="min-w-0">

                                            <p
                                                class="font-bold text-gray-800 truncate"
                                            >
                                                {{ s.name }}
                                            </p>

                                            <div
                                                class="flex items-center gap-2 mt-1 text-xs text-gray-400"
                                            >

                                                <span>
                                                    {{
                                                        formatNumber(
                                                            s.bookings_count
                                                        )
                                                    }}
                                                    حجز
                                                </span>

                                                <span>
                                                    •
                                                </span>

                                                <span>
                                                    {{
                                                        formatCurrency(
                                                            s.revenue
                                                        )
                                                    }}
                                                    ل.س
                                                </span>

                                            </div>

                                        </div>

                                    </div>


                                    <div
                                        class="text-left shrink-0"
                                    >

                                        <p
                                            class="font-bold text-indigo-600"
                                            dir="ltr"
                                        >
                                            {{
                                                formatCurrency(
                                                    s.revenue
                                                )
                                            }}
                                            ل.س
                                        </p>

                                        <p
                                            class="text-[10px] text-gray-400 mt-1"
                                        >
                                            الإيرادات
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ================= STATUS ================= -->

                    <div
                        class="group relative overflow-hidden bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-500"
                    >

                        <!-- Decorative -->

                        <div
                            class="absolute -top-24 -left-24 w-56 h-56 rounded-full bg-indigo-50/70 blur-3xl"
                        ></div>

                        <div
                            class="absolute -bottom-24 -right-24 w-56 h-56 rounded-full bg-emerald-50/60 blur-3xl"
                        ></div>


                        <div class="relative p-6">

                            <!-- Header -->

                            <div
                                class="flex items-center justify-between mb-2"
                            >

                                <div class="flex items-center gap-3">

                                    <div
                                        class="w-12 h-12 rounded-2xl bg-gray-900 text-white flex items-center justify-center shadow-lg"
                                    >
                                        <ClipboardList
                                            class="w-6 h-6"
                                            stroke-width="2"
                                        />
                                    </div>

                                    <div>

                                        <h3
                                            class="font-bold text-gray-900 text-lg"
                                        >
                                            حالة الحجوزات
                                        </h3>

                                        <p
                                            class="text-xs text-gray-400 mt-1"
                                        >
                                            نظرة مباشرة على توزيع الحجوزات
                                        </p>

                                    </div>

                                </div>


                                <div
                                    class="text-left"
                                >

                                    <p
                                        class="text-[10px] uppercase tracking-widest text-gray-400"
                                    >
                                        TOTAL
                                    </p>

                                    <p
                                        class="text-xl font-black text-gray-900"
                                    >
                                        {{ formatNumber(totalBookings) }}
                                    </p>

                                </div>

                            </div>


                            <!-- Doughnut -->

                            <div
                                class="h-[300px] flex items-center justify-center"
                            >

                                <Doughnut
                                    v-if="statusKeys.length"
                                    :data="statusChartData"
                                    :options="doughnutOptions"
                                />

                                <div
                                    v-else
                                    class="text-gray-400 text-sm"
                                >
                                    لا توجد بيانات بهذه الفترة
                                </div>

                            </div>


                            <!-- Status Cards -->

                            <div
                                v-if="statusKeys.length"
                                class="grid grid-cols-2 gap-3 mt-3"
                            >

                                <div
                                    v-for="key in statusKeys"
                                    :key="key"
                                    class="relative overflow-hidden rounded-2xl border border-gray-100 bg-gray-50/60 p-3.5 hover:bg-white hover:shadow-sm transition-all"
                                >

                                    <div
                                        class="flex items-center justify-between gap-2"
                                    >

                                        <div
                                            class="flex items-center gap-2"
                                        >

                                            <span
                                                class="w-2.5 h-2.5 rounded-full shrink-0"
                                                :style="{
                                                    backgroundColor:
                                                        statusColors[key],
                                                }"
                                            ></span>

                                            <span
                                                class="text-sm font-semibold text-gray-700"
                                            >
                                                {{
                                                    statusLabels[key] ||
                                                    key
                                                }}
                                            </span>

                                        </div>

                                        <span
                                            class="font-bold text-gray-900"
                                        >
                                            {{
                                                formatNumber(
                                                    bookingStatusBreakdown[
                                                        key
                                                    ]
                                                )
                                            }}
                                        </span>

                                    </div>


                                    <div
                                        class="mt-3 h-1.5 bg-gray-200 rounded-full overflow-hidden"
                                    >

                                        <div
                                            class="h-full rounded-full transition-all duration-700"
                                            :style="{
                                                width: `${getStatusPercentage(
                                                    key
                                                )}%`,
                                                backgroundColor:
                                                    statusColors[key],
                                            }"
                                        ></div>

                                    </div>


                                    <div
                                        class="flex justify-end mt-1"
                                    >

                                        <span
                                            class="text-[10px] text-gray-400"
                                        >
                                            {{
                                                getStatusPercentage(
                                                    key
                                                )
                                            }}%
                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- PREMIUM TOP STAFF -->
                <!-- ================================================= -->

                <div
                    class="relative overflow-hidden bg-white rounded-3xl border border-gray-100 shadow-sm"
                >

                    <!-- Decorative background -->

                    <div
                        class="absolute -top-32 -right-32 w-72 h-72 rounded-full bg-indigo-50/70 blur-3xl"
                    ></div>

                    <div
                        class="absolute -bottom-32 -left-32 w-72 h-72 rounded-full bg-violet-50/50 blur-3xl"
                    ></div>


                    <div class="relative">

                        <!-- Header -->

                        <div
                            class="px-6 py-6 border-b border-gray-100"
                        >

                            <div
                                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                            >

                                <div
                                    class="flex items-center gap-3"
                                >

                                    <div
                                        class="relative w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white flex items-center justify-center shadow-lg shadow-indigo-200"
                                    >

                                        <Users
                                            class="w-6 h-6"
                                            stroke-width="2"
                                        />

                                        <span
                                            class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full bg-amber-400 border-2 border-white"
                                        ></span>

                                    </div>

                                    <div>

                                        <div
                                            class="flex items-center gap-2"
                                        >

                                            <h3
                                                class="font-bold text-gray-900 text-lg"
                                            >
                                                أفضل الموظفين
                                            </h3>

                                            <Sparkles
                                                class="w-4 h-4 text-indigo-500"
                                            />

                                        </div>

                                        <p
                                            class="text-sm text-gray-400 mt-1"
                                        >
                                            أداء فريق العمل خلال الفترة المحددة
                                        </p>

                                    </div>

                                </div>


                                <div
                                    v-if="topStaff.length"
                                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-50 text-indigo-600 text-xs font-bold"
                                >

                                    <span
                                        class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"
                                    ></span>

                                    {{ formatNumber(topStaff.length) }}
                                    موظفين

                                </div>

                            </div>

                        </div>


                        <!-- Empty -->

                        <div
                            v-if="!topStaff.length"
                            class="px-6 py-16 text-center"
                        >

                            <div
                                class="w-16 h-16 mx-auto rounded-2xl bg-gray-50 flex items-center justify-center text-gray-300 mb-4"
                            >

                                <UserRound
                                    class="w-8 h-8"
                                />

                            </div>

                            <p
                                class="text-gray-500 font-medium"
                            >
                                لا توجد بيانات للموظفين
                            </p>

                            <p
                                class="text-gray-400 text-sm mt-1"
                            >
                                جرّب اختيار فترة زمنية أخرى
                            </p>

                        </div>


                        <!-- Staff -->

                        <div
                            v-else
                            class="p-6"
                        >

                            <!-- Featured Employee -->

                            <div
                                v-if="topEmployee"
                                class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-gray-950 via-indigo-950 to-indigo-700 text-white p-6 mb-6 shadow-xl shadow-indigo-100"
                            >

                                <!-- Glow -->

                                <div
                                    class="absolute -top-20 -right-20 w-56 h-56 rounded-full bg-indigo-400/20 blur-3xl"
                                ></div>

                                <div
                                    class="absolute -bottom-24 left-1/3 w-64 h-64 rounded-full bg-violet-400/10 blur-3xl"
                                ></div>


                                <div
                                    class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6"
                                >

                                    <!-- Employee -->

                                    <div
                                        class="flex items-center gap-4"
                                    >

                                        <div
                                            class="relative shrink-0"
                                        >

                                            <div
                                                class="w-20 h-20 rounded-2xl bg-white/10 border border-white/20 backdrop-blur-md flex items-center justify-center text-2xl font-black shadow-inner"
                                            >
                                                {{
                                                    getEmployeeInitials(
                                                        topEmployee.name
                                                    )
                                                }}
                                            </div>

                                            <div
                                                class="absolute -bottom-2 -right-2 w-8 h-8 rounded-xl bg-amber-400 text-white flex items-center justify-center border-2 border-indigo-950 shadow-lg"
                                            >
                                                <Crown
                                                    class="w-4 h-4"
                                                />
                                            </div>

                                        </div>


                                        <div>

                                            <div
                                                class="flex items-center gap-2 mb-1"
                                            >

                                                <span
                                                    class="text-[11px] uppercase tracking-wider text-indigo-200 font-bold"
                                                >
                                                    الموظف المتميز
                                                </span>

                                            </div>

                                            <h4
                                                class="text-2xl font-black"
                                            >
                                                {{ topEmployee.name }}
                                            </h4>

                                            <p
                                                class="text-indigo-200 text-sm mt-1"
                                            >
                                                {{
                                                    topEmployee.title ||
                                                    'موظف'
                                                }}
                                            </p>

                                        </div>

                                    </div>


                                    <!-- Stats -->

                                    <div
                                        class="flex items-center gap-3"
                                    >

                                        <div
                                            class="min-w-[125px] rounded-2xl bg-white/10 border border-white/10 backdrop-blur-sm px-4 py-3"
                                        >

                                            <div
                                                class="flex items-center gap-2 text-indigo-200 mb-1"
                                            >

                                                <CalendarCheck
                                                    class="w-4 h-4"
                                                />

                                                <span
                                                    class="text-xs"
                                                >
                                                    الحجوزات
                                                </span>

                                            </div>

                                            <p
                                                class="text-2xl font-black"
                                            >
                                                {{
                                                    formatNumber(
                                                        topEmployee.bookings_count
                                                    )
                                                }}
                                            </p>

                                        </div>


                                        <div
                                            class="min-w-[125px] rounded-2xl bg-white/10 border border-white/10 backdrop-blur-sm px-4 py-3"
                                        >

                                            <div
                                                class="flex items-center gap-2 text-indigo-200 mb-1"
                                            >

                                                <Wallet
                                                    class="w-4 h-4"
                                                />

                                                <span
                                                    class="text-xs"
                                                >
                                                    الإيرادات
                                                </span>

                                            </div>

                                            <p
                                                class="text-2xl font-black"
                                                dir="ltr"
                                            >
                                                {{
                                                    formatCurrency(
                                                        topEmployee.revenue
                                                    )
                                                }}
                                                ل.س
                                            </p>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <!-- Staff Table -->

                            <div
                                class="rounded-2xl border border-gray-100 overflow-hidden bg-white"
                            >

                                <!-- Table Header -->

                                <div
                                    class="hidden md:grid grid-cols-[80px_1.8fr_1fr_1fr_1.3fr] gap-4 px-5 py-3.5 bg-gray-50/80 border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wide"
                                >

                                    <div>
                                        الترتيب
                                    </div>

                                    <div>
                                        الموظف
                                    </div>

                                    <div>
                                        المسمى
                                    </div>

                                    <div>
                                        الحجوزات
                                    </div>

                                    <div>
                                        الإيرادات
                                    </div>

                                </div>


                                <!-- Rows -->

                                <div
                                    v-for="(st, index) in topStaff"
                                    :key="st.id"
                                    class="group relative px-5 py-4 border-b border-gray-100 last:border-b-0 hover:bg-indigo-50/30 transition-all duration-300"
                                >

                                    <div
                                        class="grid grid-cols-1 md:grid-cols-[80px_1.8fr_1fr_1fr_1.3fr] gap-4 items-center"
                                    >

                                        <!-- Rank -->

                                        <div>

                                            <div
                                                class="w-10 h-10 rounded-xl flex items-center justify-center border font-bold"
                                                :class="
                                                    getRankStyle(
                                                        index
                                                    ).badge
                                                "
                                            >

                                                <Trophy
                                                    v-if="
                                                        index ===
                                                        0
                                                    "
                                                    class="w-4 h-4"
                                                />

                                                <Medal
                                                    v-else-if="
                                                        index ===
                                                        1 ||
                                                        index ===
                                                        2
                                                    "
                                                    class="w-4 h-4"
                                                />

                                                <span
                                                    v-else
                                                >
                                                    #{{
                                                        getEmployeeRank(
                                                            index
                                                        )
                                                    }}
                                                </span>

                                            </div>

                                        </div>


                                        <!-- Employee -->

                                        <div
                                            class="flex items-center gap-3 min-w-0"
                                        >

                                            <div
                                                class="w-11 h-11 rounded-xl flex items-center justify-center font-black text-sm border shrink-0"
                                                :class="
                                                    getRankStyle(
                                                        index
                                                    ).avatar
                                                "
                                            >
                                                {{
                                                    getEmployeeInitials(
                                                        st.name
                                                    )
                                                }}
                                            </div>


                                            <div
                                                class="min-w-0"
                                            >

                                                <p
                                                    class="font-bold text-gray-800 truncate"
                                                >
                                                    {{ st.name }}
                                                </p>


                                                <div
                                                    class="mt-1.5 flex items-center gap-2"
                                                >

                                                    <div
                                                        class="h-1.5 w-24 bg-gray-100 rounded-full overflow-hidden"
                                                    >

                                                        <div
                                                            class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500 transition-all duration-700"
                                                            :style="{
                                                                width: `${getEmployeeProgress(
                                                                    st
                                                                )}%`,
                                                            }"
                                                        ></div>

                                                    </div>

                                                    <span
                                                        class="text-[10px] font-semibold text-gray-400"
                                                    >
                                                        {{
                                                            getEmployeeProgress(
                                                                st
                                                            )
                                                        }}%
                                                    </span>

                                                </div>

                                            </div>

                                        </div>


                                        <!-- Title -->

                                        <div>

                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-100 text-xs text-gray-500"
                                            >
                                                {{
                                                    st.title ||
                                                    '—'
                                                }}
                                            </span>

                                        </div>


                                        <!-- Bookings -->

                                        <div>

                                            <p
                                                class="md:hidden text-[11px] text-gray-400 mb-1"
                                            >
                                                عدد الحجوزات
                                            </p>

                                            <div
                                                class="flex items-center gap-2"
                                            >

                                                <CalendarCheck
                                                    class="w-4 h-4 text-gray-300"
                                                />

                                                <span
                                                    class="font-bold text-gray-800"
                                                >
                                                    {{
                                                        formatNumber(
                                                            st.bookings_count
                                                        )
                                                    }}
                                                </span>

                                                <span
                                                    class="text-xs text-gray-400"
                                                >
                                                    حجز
                                                </span>

                                            </div>

                                        </div>


                                        <!-- Revenue -->

                                        <div>

                                            <p
                                                class="md:hidden text-[11px] text-gray-400 mb-1"
                                            >
                                                الإيرادات
                                            </p>

                                            <div
                                                class="flex items-center justify-between gap-3"
                                            >

                                                <div>

                                                    <p
                                                        class="font-black text-indigo-600"
                                                        dir="ltr"
                                                    >
                                                        {{
                                                            formatCurrency(
                                                                st.revenue
                                                            )
                                                        }}
                                                        ل.س
                                                    </p>

                                                    <p
                                                        class="text-[10px] text-gray-400 mt-0.5"
                                                    >
                                                        إجمالي الإيرادات
                                                    </p>

                                                </div>

                                                <ChevronLeft
                                                    class="hidden md:block w-4 h-4 text-gray-300 group-hover:text-indigo-500 group-hover:-translate-x-1 transition-all"
                                                />

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </AuthenticatedLayout>

</template>