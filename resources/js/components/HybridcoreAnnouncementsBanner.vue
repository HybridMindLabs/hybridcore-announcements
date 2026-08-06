<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Info, CheckCircle, AlertTriangle, XCircle, X } from '@lucide/vue';

interface Announcement {
    id: number;
    title: string;
    body: string;
    type: 'info' | 'success' | 'warning' | 'danger';
    updated_at: string | null;
}

const props = defineProps<{ announcements: Announcement[] }>();

const STORAGE_KEY = 'hc_announcements_dismissed';

const config = {
    info:    { icon: Info,          cls: 'bg-blue-500/10 border-blue-500/20 text-blue-300' },
    success: { icon: CheckCircle,   cls: 'bg-emerald-500/10 border-emerald-500/20 text-emerald-300' },
    warning: { icon: AlertTriangle, cls: 'bg-amber-500/10 border-amber-500/20 text-amber-300' },
    danger:  { icon: XCircle,       cls: 'bg-red-500/10 border-red-500/20 text-red-300' },
} as const;

function dismissKey(a: Announcement): string {
    return `${a.id}:${a.updated_at ?? ''}`;
}

// SSR-rendered with everything visible; onMounted narrows to what this
// visitor hasn't dismissed yet — matches the useTheme() localStorage pattern,
// avoids touching localStorage during SSR where it doesn't exist.
const visible = ref<Announcement[]>(props.announcements);

function readDismissed(): string[] {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '[]');
    } catch {
        return [];
    }
}

onMounted(() => {
    const dismissed = new Set(readDismissed());
    visible.value = props.announcements.filter((a) => !dismissed.has(dismissKey(a)));
});

function dismiss(a: Announcement) {
    const dismissed = readDismissed();
    dismissed.push(dismissKey(a));
    localStorage.setItem(STORAGE_KEY, JSON.stringify(dismissed));
    visible.value = visible.value.filter((v) => v.id !== a.id);
}
</script>

<template>
    <div v-if="visible.length" class="flex flex-col">
        <div
            v-for="item in visible"
            :key="item.id"
            class="flex items-center gap-3 px-4 py-2 border-b text-[13px]"
            :class="config[item.type].cls"
        >
            <component :is="config[item.type].icon" :size="15" :stroke-width="2" class="shrink-0" />
            <p class="flex-1 min-w-0 leading-snug">
                <span class="font-semibold">{{ item.title }}</span>
                <span class="opacity-80"> — {{ item.body }}</span>
            </p>
            <button
                type="button"
                class="shrink-0 p-1 rounded-md opacity-60 hover:opacity-100 hover:bg-black/10 transition"
                :aria-label="`Dismiss: ${item.title}`"
                @click="dismiss(item)"
            >
                <X :size="14" :stroke-width="2" />
            </button>
        </div>
    </div>
</template>
