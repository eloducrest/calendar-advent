<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { index as calendarEdit } from '@/routes/calendar';
import type { BreadcrumbItem } from '@/types';
import type { CalendarDayItem } from '@/types';
import DoorDialog from './components/DoorDialog.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Calendrier de l\'Avent',
                href: calendarEdit(),
            },
        ] satisfies BreadcrumbItem[],
    },
});

const { days } = defineProps<{
    days: CalendarDayItem[];
}>();

const openDayId = ref<number | null>(null);

const openDay = computed(() => days.find((day) => day.id === openDayId.value) ?? null);

const solvedCount = computed(() => days.filter((day) => day.status === 'solved').length);

function formatDoorDate(day: CalendarDayItem): string {
    return new Date(day.available_at).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
    });
}

function open(day: CalendarDayItem): void {
    openDayId.value = day.id;
}
</script>

<template>
    <Head title="Calendrier de l'Avent" />

    <div class="min-h-dvh bg-gradient-to-b from-emerald-950 via-emerald-900 to-red-950">
        <div class="mx-auto w-full max-w-3xl px-4 pt-6 pb-10">
            <header class="mb-6 text-center text-white">
                <p class="text-3xl">🎄❄️🎄</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight">
                    Calendrier de l'Avent
                </h1>
                <p class="mt-1 text-sm text-white/70">
                    Ouvre une case par jour, résous le défi et découvre ton cadeau !
                </p>

                <div class="mx-auto mt-4 max-w-xs">
                    <div class="flex items-center justify-between text-xs text-white/80">
                        <span>Cadeaux débloqués</span>
                        <span class="font-semibold">{{ solvedCount }} / {{ days.length }}</span>
                    </div>
                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-white/20">
                        <div
                            class="h-full rounded-full bg-amber-400 transition-all duration-500"
                            :style="{ width: `${days.length ? (solvedCount / days.length) * 100 : 0}%` }"
                        />
                    </div>
                </div>

                <Link
                    :href="calendarEdit()"
                    class="mt-4 inline-flex items-center gap-1 rounded-full border border-white/30 px-3 py-1 text-xs text-white/80 transition-colors hover:bg-white/10"
                >
                    ✏️ Personnaliser les cases
                </Link>
            </header>

            <div class="grid grid-cols-3 gap-2.5 sm:grid-cols-4 sm:gap-3 md:grid-cols-6">
                <button
                    v-for="day in days"
                    :key="day.id"
                    type="button"
                    :disabled="day.status === 'locked'"
                    class="group relative aspect-square rounded-xl border shadow-md transition-all duration-200 sm:rounded-2xl"
                    :class="{
                        'cursor-not-allowed border-white/10 bg-white/10 text-white/40': day.status === 'locked',
                        'border-amber-300/40 bg-gradient-to-br from-red-700 to-red-900 text-white hover:-rotate-1 hover:scale-105 active:scale-95': day.status === 'available' && day.day % 2 === 1,
                        'border-lime-300/40 bg-gradient-to-br from-emerald-700 to-emerald-950 text-white hover:rotate-1 hover:scale-105 active:scale-95': day.status === 'available' && day.day % 2 === 0,
                        'border-amber-200/60 bg-gradient-to-br from-amber-100 to-amber-200 text-amber-900 hover:scale-105 dark:from-amber-800 dark:to-amber-950 dark:text-amber-100': day.status === 'solved',
                        'ring-2 ring-amber-400 ring-offset-2 ring-offset-emerald-950': day.is_today,
                    }"
                    :aria-label="day.status === 'locked'
                        ? `Case ${day.day}, s'ouvre le ${formatDoorDate(day)}`
                        : day.status === 'solved'
                            ? `Case ${day.day}, cadeau débloqué`
                            : `Ouvrir la case ${day.day}`"
                    @click="open(day)"
                >
                    <span class="absolute top-1.5 left-2 text-[10px] font-medium opacity-70 sm:top-2 sm:left-2.5 sm:text-xs">
                        {{ formatDoorDate(day) }}
                    </span>

                    <span v-if="day.status === 'solved'" class="text-3xl drop-shadow sm:text-4xl">🎁</span>
                    <span v-else-if="day.status === 'available'" class="text-2xl font-extrabold tracking-tight sm:text-3xl">
                        {{ day.day }}
                    </span>
                    <span v-else class="flex flex-col items-center gap-0.5">
                        <span class="text-xl">🔒</span>
                        <span class="text-lg font-bold sm:text-xl">{{ day.day }}</span>
                    </span>

                    <span
                        v-if="day.status !== 'locked'"
                        class="absolute bottom-1.5 left-0 w-full text-center text-[9px] uppercase tracking-wide opacity-0 transition-opacity duration-200 group-hover:opacity-80 sm:bottom-2 sm:text-[10px]"
                    >
                        {{ day.status === 'solved' ? 'Revoir' : 'Ouvrir' }}
                    </span>
                </button>
            </div>
        </div>

        <DoorDialog v-if="openDay" :key="openDay.id" :day="openDay" @close="openDayId = null" />
    </div>
</template>
