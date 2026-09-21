<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    staff: Object,
    services: Array,
});

const days = [
    { key: 'saturday', label: 'السبت' },
    { key: 'sunday', label: 'الأحد' },
    { key: 'monday', label: 'الإثنين' },
    { key: 'tuesday', label: 'الثلاثاء' },
    { key: 'wednesday', label: 'الأربعاء' },
    { key: 'thursday', label: 'الخميس' },
    { key: 'friday', label: 'الجمعة' },
];

const defaultHours = {};
days.forEach((day) => {
    const existing = props.staff.working_hours?.[day.key];
    defaultHours[day.key] = {
        enabled: existing ? existing.enabled : false,
        start: existing?.start || '09:00',
        end: existing?.end || '17:00',
    };
});

const form = useForm({
    name: props.staff.user.name,
    title: props.staff.title,
    is_active: !!props.staff.is_active,
    can_view_all_bookings: !!props.staff.can_view_all_bookings,
    working_hours: defaultHours,
    service_ids: props.staff.services.map((s) => s.id),
});

const submit = () => {
    form.put(route('staff.update', props.staff.id));
};
</script>

<template>
    <Head title="تعديل موظف" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">

                <Link
                    :href="route('staff.index')"
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 text-gray-400 transition hover:bg-gray-50 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>

                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        تعديل بيانات الموظف
                    </h2>

                    <p class="mt-1.5 text-sm text-gray-500">
                        تحديث بيانات "{{ staff.user.name }}"
                    </p>
                </div>
            </div>
        </template>


        <div class="min-h-screen bg-[#f8fafc]">
            <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">

                <form @submit.prevent="submit" class="space-y-6">

                    <!-- ============ Account Info ============ -->
                    <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

                        <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-6 py-6 sm:px-8">

                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-100 text-indigo-600 ring-1 ring-indigo-100">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8z" />
                                    </svg>
                                </div>

                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">بيانات الحساب</h3>
                                    <p class="mt-1 text-sm text-gray-500">موظف #{{ staff.id }}</p>
                                </div>
                            </div>

                            <!-- Active toggle -->
                            <button
                                type="button"
                                @click="form.is_active = !form.is_active"
                                class="flex shrink-0 items-center gap-2.5 rounded-full py-1.5 pl-1.5 pr-3 text-xs font-semibold transition"
                                :class="form.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                            >
                                {{ form.is_active ? 'نشط' : 'غير نشط' }}

                                <span
                                    class="relative inline-flex h-5 w-9 items-center rounded-full transition"
                                    :class="form.is_active ? 'bg-emerald-500' : 'bg-gray-300'"
                                >
                                    <span
                                        class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition"
                                        :class="form.is_active ? '-translate-x-0.5' : '-translate-x-4'"
                                    ></span>
                                </span>
                            </button>

                        </div>


                        <div class="space-y-5 px-6 py-7 sm:px-8">

                            <div>
                                <label for="name" class="mb-2 block text-sm font-semibold text-gray-700">اسم الموظف</label>
                                <input
                                    id="name" type="text" v-model="form.name" required autofocus
                                    class="block w-full rounded-xl border-gray-200 text-sm text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">البريد الإلكتروني</label>
                                <div class="flex items-center gap-2 rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-500" dir="ltr">
                                    {{ staff.user.email }}
                                </div>
                                <p class="mt-1.5 text-xs text-gray-400">لا يمكن تعديل البريد الإلكتروني حالياً</p>
                            </div>

                            <div>
                                <label for="title" class="mb-2 block text-sm font-semibold text-gray-700">
                                    المسمى الوظيفي
                                    <span class="font-normal text-gray-400">(اختياري)</span>
                                </label>
                                <input
                                    id="title" type="text" v-model="form.title"
                                    placeholder="مثال: فني صيانة، حلاق، أخصائي تجميل"
                                    class="block w-full rounded-xl border-gray-200 text-sm text-gray-800 shadow-sm placeholder:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <!-- Permission toggle -->
                            <label
                                class="flex cursor-pointer items-start gap-3 rounded-2xl border border-gray-100 bg-gray-50/60 p-4 transition hover:bg-gray-50"
                            >
                                <input
                                    type="checkbox"
                                    v-model="form.can_view_all_bookings"
                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />

                                <div>
                                    <p class="text-sm font-semibold text-gray-800">
                                        السماح برؤية كافة الحجوزات
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        تسمح للموظف بالاطلاع على الجدول العام لكل الموظفين، مو حجوزاته فقط.
                                    </p>
                                </div>
                            </label>

                        </div>

                    </div>


                    <!-- ============ Services ============ -->
                    <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

                        <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-6 sm:px-8">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 ring-1 ring-emerald-100">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">الخدمات التي يقدمها</h3>
                                <p class="mt-1 text-sm text-gray-500">اختر الخدمات التي يستطيع هذا الموظف تنفيذها</p>
                            </div>
                        </div>


                        <div class="px-6 py-7 sm:px-8">

                            <div v-if="services.length === 0" class="text-sm text-gray-400">
                                لا توجد خدمات مضافة بعد.
                            </div>

                            <div v-else class="flex flex-wrap gap-2">
                                <label
                                    v-for="service in services"
                                    :key="service.id"
                                    class="cursor-pointer select-none rounded-xl border px-4 py-2.5 text-sm font-medium transition"
                                    :class="
                                        form.service_ids.includes(service.id)
                                            ? 'border-indigo-200 bg-indigo-50 text-indigo-700'
                                            : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'
                                    "
                                >
                                    <input
                                        type="checkbox"
                                        :value="service.id"
                                        v-model="form.service_ids"
                                        class="hidden"
                                    />

                                    {{ service.name }}
                                </label>
                            </div>

                        </div>

                    </div>


                    <!-- ============ Working Hours ============ -->
                    <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

                        <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-6 sm:px-8">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 ring-1 ring-amber-100">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="9" stroke-width="1.8" />
                                    <path stroke-linecap="round" stroke-width="1.8" d="M12 7v5l3 2" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900">ساعات الدوام</h3>
                                <p class="mt-1 text-sm text-gray-500">حدد أيام وأوقات عمل الموظف</p>
                            </div>
                        </div>


                        <div class="divide-y divide-gray-100 px-6 sm:px-8">

                            <div
                                v-for="day in days"
                                :key="day.key"
                                class="flex flex-wrap items-center justify-between gap-3 py-4"
                            >

                                <button
                                    type="button"
                                    @click="form.working_hours[day.key].enabled = !form.working_hours[day.key].enabled"
                                    class="flex w-32 shrink-0 items-center gap-2.5"
                                >
                                    <span
                                        class="relative inline-flex h-5 w-9 items-center rounded-full transition"
                                        :class="form.working_hours[day.key].enabled ? 'bg-emerald-500' : 'bg-gray-300'"
                                    >
                                        <span
                                            class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition"
                                            :class="form.working_hours[day.key].enabled ? '-translate-x-0.5' : '-translate-x-4'"
                                        ></span>
                                    </span>

                                    <span
                                        class="text-sm font-medium"
                                        :class="form.working_hours[day.key].enabled ? 'text-gray-800' : 'text-gray-400'"
                                    >
                                        {{ day.label }}
                                    </span>
                                </button>

                                <div v-if="form.working_hours[day.key].enabled" class="flex items-center gap-2">
                                    <input
                                        type="time"
                                        v-model="form.working_hours[day.key].start"
                                        class="rounded-lg border-gray-200 text-xs text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />

                                    <span class="text-xs text-gray-300">إلى</span>

                                    <input
                                        type="time"
                                        v-model="form.working_hours[day.key].end"
                                        class="rounded-lg border-gray-200 text-xs text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>

                                <span v-else class="text-xs font-medium text-gray-300">
                                    غير متاح
                                </span>

                            </div>

                        </div>

                        <div class="h-2"></div>

                    </div>


                    <!-- ============ Footer ============ -->
                    <div class="flex items-center justify-end gap-3">

                        <Link
                            :href="route('staff.index')"
                            class="rounded-xl px-5 py-3 text-sm font-semibold text-gray-500 transition hover:bg-gray-100"
                        >
                            إلغاء
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 hover:shadow-md active:scale-[0.98] disabled:opacity-60"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>

                            حفظ التعديلات
                        </button>

                    </div>

                </form>

            </div>
        </div>

    </AuthenticatedLayout>
</template>