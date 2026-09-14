<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
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

ChartJS.register(
  Title, Tooltip, Legend,
  LineElement, BarElement, ArcElement,
  CategoryScale, LinearScale, PointElement
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
  router.get(route('analytics.index'), {
    from: fromDate.value,
    to: toDate.value,
  }, { preserveState: true, preserveScroll: true });
}

function formatCurrency(value) {
  return new Intl.NumberFormat('ar', { maximumFractionDigits: 0 }).format(value || 0);
}

const revenueChartData = {
  labels: props.revenueByDay.map(r => r.date),
  datasets: [
    {
      label: 'الإيرادات',
      data: props.revenueByDay.map(r => r.total),
      borderColor: '#6366f1',
      backgroundColor: 'rgba(99, 102, 241, 0.15)',
      tension: 0.35,
      fill: true,
    },
  ],
};

const chartOptions = {
  responsive: true,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true } },
};

const servicesChartData = {
  labels: props.topServices.map(s => s.name),
  datasets: [
    {
      label: 'عدد الحجوزات',
      data: props.topServices.map(s => s.bookings_count),
      backgroundColor: '#22c55e',
      borderRadius: 6,
    },
  ],
};

const statusLabels = {
  pending: 'قيد الانتظار',
  confirmed: 'مؤكد',
  cancelled: 'ملغي',
  completed: 'مكتمل',
};

const statusChartData = {
  labels: Object.keys(props.bookingStatusBreakdown).map(k => statusLabels[k] || k),
  datasets: [
    {
      data: Object.values(props.bookingStatusBreakdown),
      backgroundColor: ['#fbbf24', '#3b82f6', '#ef4444', '#22c55e'],
    },
  ],
};
</script>

<template>
  <Head title="التقارير والتحليلات" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">التقارير والتحليلات</h2>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white rounded-xl shadow p-4 flex flex-wrap items-end gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">من</label>
            <input type="date" v-model="fromDate" class="border rounded-lg px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">إلى</label>
            <input type="date" v-model="toDate" class="border rounded-lg px-3 py-2" />
          </div>
          <button
            @click="applyFilter"
            class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition"
          >
            تطبيق
          </button>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
          <p class="text-sm text-gray-500 mb-1">إجمالي الإيرادات بالفترة المحددة</p>
          <p class="text-3xl font-bold text-indigo-600">{{ formatCurrency(totalRevenue) }}</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="font-semibold text-gray-800 mb-4">الإيرادات عبر الوقت</h3>
          <Line v-if="revenueByDay.length" :data="revenueChartData" :options="chartOptions" />
          <p v-else class="text-gray-400 text-sm">لا توجد بيانات بهذه الفترة</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="bg-white rounded-xl shadow p-6">
            <h3 class="font-semibold text-gray-800 mb-4">أفضل الخدمات</h3>
            <Bar v-if="topServices.length" :data="servicesChartData" :options="chartOptions" />
            <p v-else class="text-gray-400 text-sm">لا توجد بيانات بهذه الفترة</p>

            <div class="mt-4 divide-y">
              <div v-for="s in topServices" :key="s.id" class="flex justify-between py-2 text-sm">
                <span>{{ s.name }}</span>
                <span class="text-gray-500">{{ formatCurrency(s.revenue) }}</span>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow p-6">
            <h3 class="font-semibold text-gray-800 mb-4">حالة الحجوزات</h3>
            <Doughnut v-if="Object.keys(bookingStatusBreakdown).length" :data="statusChartData" />
            <p v-else class="text-gray-400 text-sm">لا توجد بيانات بهذه الفترة</p>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="font-semibold text-gray-800 mb-4">أفضل الموظفين</h3>
          <table class="w-full text-sm text-right">
            <thead>
              <tr class="text-gray-500 border-b">
                <th class="py-2">الموظف</th>
                <th class="py-2">المسمى</th>
                <th class="py-2">عدد الحجوزات</th>
                <th class="py-2">الإيرادات</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="st in topStaff" :key="st.id">
                <td class="py-2">{{ st.name }}</td>
                <td class="py-2 text-gray-500">{{ st.title || '-' }}</td>
                <td class="py-2">{{ st.bookings_count }}</td>
                <td class="py-2 font-medium">{{ formatCurrency(st.revenue) }}</td>
              </tr>
            </tbody>
          </table>
          <p v-if="!topStaff.length" class="text-gray-400 text-sm mt-2">لا توجد بيانات بهذه الفترة</p>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>