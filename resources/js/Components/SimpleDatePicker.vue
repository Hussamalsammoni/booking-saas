<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'اختر تاريخ',
    },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const triggerRef = ref(null);
const popoverRef = ref(null);
const popoverStyle = ref({});

const positionPopover = () => {
    if (!triggerRef.value) return;

    const rect = triggerRef.value.getBoundingClientRect();

    popoverStyle.value = {
        position: 'fixed',
        top: `${rect.bottom + 8}px`,
        left: `${Math.max(8, rect.right - 260)}px`,
    };
};

const monthNames = [
    'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر',
];

// الأسبوع يبدأ السبت (العرف الشائع بالمنطقة)
const weekDayLabels = ['سب', 'أح', 'اث', 'ثل', 'أر', 'خم', 'جم'];

const parseModelValue = () => {
    if (props.modelValue && /^\d{4}-\d{2}-\d{2}$/.test(props.modelValue)) {
        const [y, m, d] = props.modelValue.split('-').map(Number);
        return new Date(y, m - 1, d);
    }
    return null;
};

const today = new Date();
const selectedDate = ref(parseModelValue());
const viewYear = ref((selectedDate.value || today).getFullYear());
const viewMonth = ref((selectedDate.value || today).getMonth());

const displayLabel = computed(() => {
    if (!selectedDate.value) return '';
    const d = selectedDate.value;
    return `${d.getDate()} ${monthNames[d.getMonth()]} ${d.getFullYear()}`;
});

const toIso = (date) => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
};

const calendarCells = computed(() => {
    const firstOfMonth = new Date(viewYear.value, viewMonth.value, 1);
    // تحويل getDay() (0=أحد) إلى ترتيب يبدأ من السبت (0=سبت)
    const jsDay = firstOfMonth.getDay(); // 0=أحد..6=سبت
    const offset = (jsDay + 1) % 7; // 0=سبت

    const daysInMonth = new Date(viewYear.value, viewMonth.value + 1, 0).getDate();

    const cells = [];

    for (let i = 0; i < offset; i++) {
        cells.push(null);
    }

    for (let day = 1; day <= daysInMonth; day++) {
        cells.push(new Date(viewYear.value, viewMonth.value, day));
    }

    return cells;
});

const isSameDay = (a, b) => {
    if (!a || !b) return false;
    return (
        a.getFullYear() === b.getFullYear() &&
        a.getMonth() === b.getMonth() &&
        a.getDate() === b.getDate()
    );
};

const isToday = (date) => isSameDay(date, today);
const isSelected = (date) => isSameDay(date, selectedDate.value);

const prevMonth = () => {
    if (viewMonth.value === 0) {
        viewMonth.value = 11;
        viewYear.value -= 1;
    } else {
        viewMonth.value -= 1;
    }
};

const nextMonth = () => {
    if (viewMonth.value === 11) {
        viewMonth.value = 0;
        viewYear.value += 1;
    } else {
        viewMonth.value += 1;
    }
};

const pickDay = (date) => {
    selectedDate.value = date;
    emit('update:modelValue', toIso(date));
    isOpen.value = false;
};

const pickToday = () => {
    pickDay(new Date(today.getFullYear(), today.getMonth(), today.getDate()));
    viewYear.value = today.getFullYear();
    viewMonth.value = today.getMonth();
};

const clearValue = () => {
    selectedDate.value = null;
    emit('update:modelValue', '');
    isOpen.value = false;
};

const toggleOpen = () => {
    isOpen.value = !isOpen.value;

    if (isOpen.value) {
        const base = selectedDate.value || today;
        viewYear.value = base.getFullYear();
        viewMonth.value = base.getMonth();
        positionPopover();
    }
};

const handleScroll = () => {
    if (isOpen.value) {
        isOpen.value = false;
    }
};

const handleClickOutside = (event) => {
    if (
        triggerRef.value &&
        !triggerRef.value.contains(event.target) &&
        popoverRef.value &&
        !popoverRef.value.contains(event.target)
    ) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
    window.addEventListener('scroll', handleScroll, true);
    window.addEventListener('resize', handleScroll);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
    window.removeEventListener('scroll', handleScroll, true);
    window.removeEventListener('resize', handleScroll);
});
</script>

<template>
    <div class="relative" ref="triggerRef">

        <!-- Trigger -->
        <button
            type="button"
            @click="toggleOpen"
            class="flex items-center gap-1.5 rounded-lg border-0 bg-white px-2.5 py-1.5 text-xs text-gray-600 shadow-sm transition hover:bg-gray-50"
            :class="{ 'ring-1 ring-indigo-500': isOpen }"
        >
            <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.8" />
                <path stroke-linecap="round" stroke-width="1.8" d="M16 2v4M8 2v4M3 10h18" />
            </svg>

            <span :class="displayLabel ? 'text-gray-700' : 'text-gray-300'">
                {{ displayLabel || placeholder }}
            </span>
        </button>


        <!-- Popover -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <div
                    v-if="isOpen"
                    ref="popoverRef"
                    class="z-50 w-[260px] rounded-2xl border border-gray-100 bg-white p-4 shadow-xl"
                    :style="popoverStyle"
                >

                <!-- Month header -->
                <div class="mb-3 flex items-center justify-between">

                    <button
                        type="button"
                        @click="prevMonth"
                        class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-50 hover:text-gray-600"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <span class="text-sm font-bold text-gray-800">
                        {{ monthNames[viewMonth] }} {{ viewYear }}
                    </span>

                    <button
                        type="button"
                        @click="nextMonth"
                        class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-50 hover:text-gray-600"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                </div>


                <!-- Weekday labels -->
                <div class="mb-1 grid grid-cols-7 gap-1">
                    <span
                        v-for="label in weekDayLabels"
                        :key="label"
                        class="text-center text-[10px] font-semibold text-gray-400"
                    >
                        {{ label }}
                    </span>
                </div>


                <!-- Day cells -->
                <div class="grid grid-cols-7 gap-1">

                    <div
                        v-for="(cell, index) in calendarCells"
                        :key="index"
                        class="flex h-8 items-center justify-center"
                    >

                        <button
                            v-if="cell"
                            type="button"
                            @click="pickDay(cell)"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-xs font-medium transition"
                            :class="[
                                isSelected(cell)
                                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30'
                                    : isToday(cell)
                                        ? 'bg-indigo-50 text-indigo-600'
                                        : 'text-gray-600 hover:bg-gray-50',
                            ]"
                        >
                            {{ cell.getDate() }}
                        </button>

                    </div>

                </div>


                <!-- Footer -->
                <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3">

                    <button
                        type="button"
                        @click="pickToday"
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-700"
                    >
                        اليوم
                    </button>

                    <button
                        v-if="selectedDate"
                        type="button"
                        @click="clearValue"
                        class="text-xs font-medium text-gray-400 hover:text-gray-600"
                    >
                        مسح
                    </button>

                </div>

            </div>
        </Transition>
        </Teleport>

    </div>
</template>