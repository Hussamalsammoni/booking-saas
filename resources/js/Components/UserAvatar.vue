<!-- المسار: resources/js/Components/UserAvatar.vue -->
<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
    user: { type: Object, default: null },
    // معاينة مؤقتة (مثلاً قبل الحفظ) تتقدّم على صورة المستخدم المحفوظة
    src: { type: String, default: null },
    size: { type: Number, default: 44 },
})

const failed = ref(false)
const url = computed(() => props.src ?? props.user?.avatar_url ?? null)
watch(url, () => { failed.value = false })

const initials = computed(() => {
    const name = props.user?.name
    if (!name) return '?'

    const words = name.trim().split(/\s+/)
    if (words.length === 1) return words[0].substring(0, 2).toUpperCase()

    return (words[0].charAt(0) + words[words.length - 1].charAt(0)).toUpperCase()
})
</script>

<template>
    <span
        class="inline-flex shrink-0 select-none items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 font-bold text-white"
        :style="{ width: size + 'px', height: size + 'px', fontSize: Math.round(size * 0.34) + 'px' }"
    >
        <img
            v-if="url && !failed"
            :src="url"
            alt=""
            class="h-full w-full object-cover"
            @error="failed = true"
        />
        <template v-else>{{ initials }}</template>
    </span>
</template>