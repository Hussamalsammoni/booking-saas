<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import { Bell } from 'lucide-vue-next'

const POLL_MS = 20000 // كل 20 ثانية

const open = ref(false)
const items = ref([])
const unread = ref(0)
const root = ref(null)
let timer = null

async function load() {
  if (document.hidden) return // ما نطلب إذا التاب مخفي
  try {
    const { data } = await axios.get('/notifications')
    unread.value = data.unread_count
    items.value = data.items
  } catch (e) {
    // تجاهل أخطاء الشبكة المؤقتة
  }
}

function markItemsRead() {
  items.value = items.value.map((i) => ({ ...i, read: true }))
}

async function toggle() {
  open.value = !open.value
  if (open.value && unread.value > 0) {
    try {
      await axios.post('/notifications/read')
      unread.value = 0 // العناصر بتضل ملوّنة "جديدة" لحتى تسكّر القائمة
    } catch (e) {}
  }
  if (!open.value) markItemsRead()
}

function go(item) {
  open.value = false
  markItemsRead()
  if (item.url) router.visit(item.url)
}

function onClickOutside(e) {
  if (open.value && root.value && !root.value.contains(e.target)) {
    open.value = false
    markItemsRead()
  }
}

onMounted(() => {
  load()
  timer = setInterval(load, POLL_MS)
  document.addEventListener('click', onClickOutside)
  document.addEventListener('visibilitychange', load)
})

onBeforeUnmount(() => {
  clearInterval(timer)
  document.removeEventListener('click', onClickOutside)
  document.removeEventListener('visibilitychange', load)
})
</script>

<template>
  <!-- على الموبايل القائمة بتتموضع نسبة للهيدر الثابت، ومن sm وفوق نسبة للجرس -->
  <div ref="root" class="sm:relative">
    <button
      type="button"
      @click="toggle"
      class="relative w-10 h-10 rounded-xl bg-slate-50 border border-slate-100
             text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition
             flex items-center justify-center focus:outline-none
             focus-visible:ring-2 focus-visible:ring-indigo-400"
      aria-label="الإشعارات"
    >
      <Bell class="w-[18px] h-[18px]" stroke-width="1.8" />

      <span
        v-if="unread > 0"
        class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1
               rounded-full bg-rose-500 text-white text-[10px] font-bold
               flex items-center justify-center ring-2 ring-white"
      >
        {{ unread > 9 ? '9+' : unread }}
      </span>
    </button>

    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 -translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="open"
        dir="rtl"
        class="absolute z-50 inset-x-3 top-full mt-3
               sm:inset-x-auto sm:left-0 sm:w-80
               overflow-hidden rounded-2xl bg-white border border-slate-100
               shadow-xl shadow-slate-900/10"
      >
        <div class="px-4 py-3 border-b border-slate-100 text-sm font-bold text-slate-800">
          الإشعارات
        </div>

        <div v-if="items.length === 0" class="px-4 py-10 text-center text-sm text-slate-500 leading-6">
          ما في إشعارات لهلأ.<br />أول ما ييجي حجز جديد رح يظهر هون.
        </div>

        <ul v-else class="max-h-96 overflow-y-auto divide-y divide-slate-50">
          <li
            v-for="item in items"
            :key="item.id"
            @click="go(item)"
            class="px-4 py-3 cursor-pointer transition hover:bg-slate-50"
            :class="{ 'bg-indigo-50/60': !item.read }"
          >
            <div class="flex items-start gap-3">
              <span
                class="mt-1.5 w-2 h-2 rounded-full shrink-0"
                :class="item.read ? 'bg-transparent' : 'bg-indigo-500'"
              />
              <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-800">{{ item.title }}</p>
                <p class="text-sm text-slate-600 leading-5">{{ item.message }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ item.time }}</p>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </Transition>
  </div>
</template>