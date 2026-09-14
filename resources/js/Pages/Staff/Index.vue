<script setup>

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getProfessionIcon } from '@/professionIcons';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    staff: Array,
});

const deleteStaff = (id) => {
    if (confirm('هل أنت متأكد من حذف هذا الموظف؟')) {
        router.delete(route('staff.destroy', id));
    }
};
</script>

<template>
    <Head title="الموظفين" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">الموظفين</h2>
                <Link
                    :href="route('staff.create')"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700"
                >
                    + إضافة موظف جديد
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div v-if="staff.length === 0" class="text-gray-500 text-center py-8">
                        لا يوجد موظفين مضافين بعد. ابدأ بإضافة أول موظف!
                    </div>

                    <table v-else class="w-full text-right">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="pb-3 text-sm font-medium text-gray-500">الاسم</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">البريد الإلكتروني</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">المسمى الوظيفي</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">الحالة</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="member in staff" :key="member.id" class="border-b border-gray-100">
<td class="py-3">
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center shrink-0">
            <component :is="getProfessionIcon(member.title)" class="w-4 h-4 text-indigo-600" />
        </div>
        <span class="font-medium text-gray-800">{{ member.user?.name }}</span>
    </div>
</td>                            <td class="py-3 text-gray-600">{{ member.user?.email }}</td>
                                <td class="py-3 text-gray-600">{{ member.title || '—' }}</td>
                                <td class="py-3">
                                    <span
                                        :class="member.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                                        class="px-2 py-1 rounded-full text-xs"
                                    >
                                        {{ member.is_active ? 'نشط' : 'غير نشط' }}
                                    </span>
                                </td>
                                <td class="py-3 space-x-2 space-x-reverse">
                                    <Link
                                        :href="route('staff.edit', member.id)"
                                        class="text-indigo-600 hover:underline text-sm"
                                    >
                                        تعديل
                                    </Link>
                                    <button
                                        @click="deleteStaff(member.id)"
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