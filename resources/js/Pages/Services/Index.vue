<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    services: Array,
});

const deleteService = (id) => {
    if (confirm('هل أنت متأكد من حذف هذه الخدمة؟')) {
        router.delete(route('services.destroy', id));
    }
};
</script>

<template>
    <Head title="الخدمات" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">الخدمات</h2>
                <Link
                    :href="route('services.create')"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700"
                >
                    + إضافة خدمة جديدة
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div v-if="services.length === 0" class="text-gray-500 text-center py-8">
                        لا توجد خدمات مضافة بعد. ابدأ بإضافة أول خدمة!
                    </div>

                    <table v-else class="w-full text-right">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="pb-3 text-sm font-medium text-gray-500">الاسم</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">المدة</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">السعر</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">الحالة</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="service in services" :key="service.id" class="border-b border-gray-100">
                                <td class="py-3 font-medium text-gray-800">{{ service.name }}</td>
                                <td class="py-3 text-gray-600">{{ service.duration_minutes }} دقيقة</td>
                                <td class="py-3 text-gray-600">{{ service.price }} $</td>
                                <td class="py-3">
                                    <span
                                        :class="service.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                                        class="px-2 py-1 rounded-full text-xs"
                                    >
                                        {{ service.is_active ? 'نشطة' : 'غير نشطة' }}
                                    </span>
                                </td>
                                <td class="py-3 space-x-2 space-x-reverse">
                                    <Link
                                        :href="route('services.edit', service.id)"
                                        class="text-indigo-600 hover:underline text-sm"
                                    >
                                        تعديل
                                    </Link>
                                    <button
                                        @click="deleteService(service.id)"
                                        class="text-red-600 hover:underline text-sm"
                                    >
                                        حذف
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>