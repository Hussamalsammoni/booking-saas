<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    service: Object,
});

const form = useForm({
    name: props.service.name,
    description: props.service.description,
    duration_minutes: props.service.duration_minutes,
    price: props.service.price,
    is_active: props.service.is_active,
});

const submit = () => {
    form.put(route('services.update', props.service.id));
};
</script>

<template>
    <Head title="تعديل خدمة" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">

                <Link
                    :href="route('services.index')"
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 text-gray-400 transition hover:bg-gray-50 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>

                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        تعديل الخدمة
                    </h2>

                    <p class="mt-1.5 text-sm text-gray-500">
                        تحديث بيانات "{{ service.name }}"
                    </p>
                </div>
            </div>
        </template>


        <div class="min-h-screen bg-[#f8fafc]">
            <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">

                <form @submit.prevent="submit">

                    <div class="overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm">

                        <!-- Card header -->
                        <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-6 py-6 sm:px-8">

                            <div class="flex items-center gap-4">

                                <div
                                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-100 text-indigo-600 ring-1 ring-indigo-100"
                                >
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6" />
                                    </svg>
                                </div>

                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">
                                        بيانات الخدمة
                                    </h3>

                                    <p class="mt-1 text-sm text-gray-500">
                                        خدمة رقم #{{ service.id }}
                                    </p>
                                </div>

                            </div>


                            <!-- Active toggle -->
                            <button
                                type="button"
                                @click="form.is_active = !form.is_active"
                                class="flex items-center gap-2.5 rounded-full py-1.5 pl-1.5 pr-3 text-xs font-semibold transition"
                                :class="form.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                            >
                                {{ form.is_active ? 'نشطة' : 'غير نشطة' }}

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


                        <!-- Card body -->
                        <div class="space-y-6 px-6 py-7 sm:px-8">

                            <!-- Name -->
                            <div>
                                <label for="name" class="mb-2 block text-sm font-semibold text-gray-700">
                                    اسم الخدمة
                                </label>

                                <input
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    required
                                    autofocus
                                    class="block w-full rounded-xl border-gray-200 text-sm text-gray-800 shadow-sm placeholder:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                />

                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>


                            <!-- Description -->
                            <div>
                                <label for="description" class="mb-2 block text-sm font-semibold text-gray-700">
                                    الوصف
                                    <span class="font-normal text-gray-400">(اختياري)</span>
                                </label>

                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="block w-full rounded-xl border-gray-200 text-sm text-gray-800 shadow-sm placeholder:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>

                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>


                            <!-- Duration + Price -->
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                                <!-- Duration -->
                                <div>
                                    <label for="duration_minutes" class="mb-2 block text-sm font-semibold text-gray-700">
                                        المدة
                                    </label>

                                    <div class="relative">
                                        <input
                                            id="duration_minutes"
                                            type="number"
                                            v-model="form.duration_minutes"
                                            required
                                            min="1"
                                            class="block w-full rounded-xl border-gray-200 pl-16 text-sm text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />

                                        <span
                                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center rounded-l-xl bg-indigo-50 px-3 text-xs font-semibold text-indigo-600"
                                        >
                                            دقيقة
                                        </span>
                                    </div>

                                    <InputError class="mt-2" :message="form.errors.duration_minutes" />
                                </div>


                                <!-- Price -->
                                <div>
                                    <label for="price" class="mb-2 block text-sm font-semibold text-gray-700">
                                        السعر
                                    </label>

                                    <div class="relative">
                                        <input
                                            id="price"
                                            type="number"
                                            step="0.01"
                                            v-model="form.price"
                                            required
                                            min="0"
                                            class="block w-full rounded-xl border-gray-200 pl-12 text-sm text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />

                                        <span
                                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center rounded-l-xl bg-emerald-50 px-3 text-xs font-semibold text-emerald-600"
                                        >
                                            ل.س
                                        </span>
                                    </div>

                                    <InputError class="mt-2" :message="form.errors.price" />
                                </div>

                            </div>

                        </div>


                        <!-- Card footer -->
                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/50 px-6 py-5 sm:px-8">

                            <Link
                                :href="route('services.index')"
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

                    </div>

                </form>

            </div>
        </div>

    </AuthenticatedLayout>
</template>