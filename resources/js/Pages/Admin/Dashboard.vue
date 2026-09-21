<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    tenants: Array,
    stats: Object,
})

const page = usePage()
const busyId = ref(null)

const statCards = computed(() => [
    { label: 'إجمالي المحلات', value: props.stats?.total ?? 0, color: 'text-white' },
    { label: 'فعّالة', value: props.stats?.active ?? 0, color: 'text-green-400' },
    { label: 'تنتهي خلال 7 أيام', value: props.stats?.expiring ?? 0, color: 'text-amber-400' },
    { label: 'منتهية', value: props.stats?.expired ?? 0, color: 'text-red-400' },
    { label: 'معطّلة', value: props.stats?.disabled ?? 0, color: 'text-gray-400' },
    { label: 'إجمالي الحجوزات', value: props.stats?.total_bookings ?? 0, color: 'text-indigo-400' },
])

const statusMeta = {
    active: { label: 'فعّال', class: 'bg-green-500/15 text-green-400' },
    expiring: { label: 'ينتهي قريباً', class: 'bg-amber-500/15 text-amber-400' },
    expired: { label: 'منتهي', class: 'bg-red-500/15 text-red-400' },
    disabled: { label: 'معطّل', class: 'bg-gray-500/20 text-gray-400' },
}

const extendOptions = [
    { days: 30, label: '+30 يوم' },
    { days: 90, label: '+90 يوم' },
    { days: 365, label: '+سنة' },
]

function daysLabel(t) {
    if (t.days_left === null) return 'بدون تاريخ انتهاء'
    if (t.days_left < 0) return `منتهي منذ ${Math.abs(t.days_left)} يوم`
    if (t.days_left === 0) return 'ينتهي اليوم'
    return `باقي ${t.days_left} يوم`
}

function send(id, url, data = {}) {
    if (busyId.value) return
    busyId.value = id
    router.post(url, data, {
        preserveScroll: true,
        onFinish: () => (busyId.value = null),
    })
}

function toggleActive(id) {
    send(id, route('admin.tenants.toggle-active', id))
}

function extend(id, days) {
    send(id, route('admin.tenants.extend', id), { days })
}
</script>

<template>
    <AdminLayout>
        <h2 class="text-2xl font-bold mb-6">المحلات المسجلة</h2>

        <div
            v-if="page.props.flash?.success"
            class="mb-6 rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm font-semibold text-green-400"
        >
            {{ page.props.flash.success }}
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 gap-3 mb-8 md:grid-cols-3 xl:grid-cols-6">
            <div
                v-for="card in statCards"
                :key="card.label"
                class="rounded-xl border border-gray-700 bg-gray-800 p-4"
            >
                <p class="text-xs text-gray-400">{{ card.label }}</p>
                <p class="mt-2 text-3xl font-black" :class="card.color">{{ card.value }}</p>
            </div>
        </div>

        <!-- Tenants table -->
        <div class="overflow-x-auto rounded-xl border border-gray-700">
            <table class="w-full min-w-[860px] text-right border-collapse">
                <thead>
                    <tr class="border-b border-gray-700 bg-gray-800 text-gray-400 text-sm">
                        <th class="py-3 px-4">اسم المحل</th>
                        <th class="py-3 px-4">الدومين</th>
                        <th class="py-3 px-4">الحجوزات</th>
                        <th class="py-3 px-4">الاشتراك</th>
                        <th class="py-3 px-4">الحالة</th>
                        <th class="py-3 px-4">إجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-if="!tenants.length">
                        <td colspan="6" class="py-10 text-center text-gray-500">
                            ما في محلات مسجلة لهلأ.
                        </td>
                    </tr>

                    <tr
                        v-for="tenant in tenants"
                        :key="tenant.id"
                        class="border-b border-gray-800 last:border-0"
                    >
                        <td class="py-3 px-4">
                            <div class="font-semibold">{{ tenant.shop_name }}</div>
                            <div class="text-xs text-gray-500">سُجّل {{ tenant.created_at }}</div>
                        </td>

                        <td class="py-3 px-4 text-gray-400" dir="ltr">{{ tenant.domain }}</td>

                        <td class="py-3 px-4">{{ tenant.bookings_count }}</td>

                        <td class="py-3 px-4">
                            <div class="text-sm">{{ daysLabel(tenant) }}</div>
                            <div v-if="tenant.subscription_ends_at" class="text-xs text-gray-500" dir="ltr">
                                {{ tenant.subscription_ends_at }}
                            </div>
                        </td>

                        <td class="py-3 px-4">
                            <span
                                class="inline-block rounded-full px-3 py-1 text-xs font-bold"
                                :class="statusMeta[tenant.status].class"
                            >
                                {{ statusMeta[tenant.status].label }}
                            </span>
                        </td>

                        <td class="py-3 px-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <button
                                    v-for="opt in extendOptions"
                                    :key="opt.days"
                                    type="button"
                                    :disabled="busyId === tenant.id"
                                    @click="extend(tenant.id, opt.days)"
                                    class="rounded-lg bg-indigo-600 px-3 py-1 text-xs font-semibold hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    {{ opt.label }}
                                </button>

                                <button
                                    type="button"
                                    :disabled="busyId === tenant.id"
                                    @click="toggleActive(tenant.id)"
                                    class="rounded-lg px-3 py-1 text-xs font-semibold disabled:opacity-50"
                                    :class="tenant.is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                                >
                                    {{ tenant.is_active ? 'تعطيل' : 'تفعيل' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>