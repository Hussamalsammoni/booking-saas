<script setup>
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
    tenants: Array,
})

function toggleActive(tenantId) {
    useForm({}).post(route('admin.tenants.toggle-active', tenantId), {
        preserveScroll: true,
    })
}
</script>

<template>
    <AdminLayout>
        <h2 class="text-2xl font-bold mb-6">المحلات المسجلة</h2>

        <table class="w-full text-right border-collapse">
            <thead>
                <tr class="border-b border-gray-700 text-gray-400 text-sm">
                    <th class="py-2">اسم المحل</th>
                    <th class="py-2">الدومين</th>
                    <th class="py-2">تاريخ التسجيل</th>
                    <th class="py-2">الحالة</th>
                    <th class="py-2">إجراء</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="tenant in tenants" :key="tenant.id" class="border-b border-gray-800">
                    <td class="py-3">{{ tenant.shop_name }}</td>
                    <td class="py-3 text-gray-400">{{ tenant.domain }}</td>
                    <td class="py-3 text-gray-400">{{ tenant.created_at }}</td>
                    <td class="py-3">
                        <span :class="tenant.is_active ? 'text-green-400' : 'text-red-400'">
                            {{ tenant.is_active ? 'فعّال' : 'معطّل' }}
                        </span>
                    </td>
                    <td class="py-3">
                        <button
                            @click="toggleActive(tenant.id)"
                            class="text-sm px-3 py-1 rounded"
                            :class="tenant.is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                        >
                            {{ tenant.is_active ? 'تعطيل' : 'تفعيل' }}
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </AdminLayout>
</template>