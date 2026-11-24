<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
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

const page = usePage();

const isAdmin = computed(() => page.props.auth.user.role === 'admin');

const openDayId = ref<number | null>(null);

const openDay = computed(() => days.find((day) => day.id === openDayId.value) ?? null);

const solvedCount = computed(() => days.filter((day) => day.status === 'solved').length);

type TileSize = 'normal' | 'wide' | 'tall' | 'big';

const SIZE_CLASSES: Record<TileSize, string> = {
    normal: '',
    wide: 'col-span-2',
    tall: 'row-span-2',
    big: 'col-span-2 row-span-2',
};

// PRNG déterministe : la disposition dépend de l'utilisateur et de l'année,
// donc le même calendrier garde son aspect d'une visite à l'autre.
function mulberry32(seed: number): () => number {
    let state = seed >>> 0;

    return () => {
        state = (state + 0x6d2b79f5) >>> 0;
        let mixed = Math.imul(state ^ (state >>> 15), 1 | state);
        mixed = (mixed + Math.imul(mixed ^ (mixed >>> 7), 61 | mixed)) ^ mixed;

        return ((mixed ^ (mixed >>> 14)) >>> 0) / 4294967296;
    };
}

type TiledDay = {
    day: CalendarDayItem;
    size: TileSize;
    rotate: string;
    emoji: string;
};

const deck = computed<TiledDay[]>(() => {
    const seedBase = (page.props.auth.user.id * 7907 + new Date().getFullYear()) >>> 0;
    const rand = mulberry32(seedBase);

    // Mélange l'ordre d'affichage sans toucher aux données (case 24 reste
    // la plus grande, façon veillée de Noël).
    const shuffled = [...days];
    for (let index = shuffled.length - 1; index > 0; index--) {
        const swap = Math.floor(rand() * (index + 1));
        [shuffled[index], shuffled[swap]] = [shuffled[swap]!, shuffled[index]!];
    }

    const sizes: TileSize[] = ['normal', 'wide', 'tall', 'normal', 'normal', 'big'];
    const rotations = ['', 'rotate-1', '-rotate-1', 'rotate-2', '-rotate-2', ''];

    return shuffled.map((day): TiledDay => ({
        day,
        size: day.day === 24 ? 'big' : sizes[Math.floor(rand() * sizes.length)]!,
        rotate: day.status === 'locked' ? rotations[Math.floor(rand() * rotations.length)]! : '',
        emoji: ['🎅', '⭐', '🔔', '❄️', '🕯️', '🦌', '🧦', '🍪'][Math.floor(rand() * 8)] ?? '⭐',
    }));
});

const TILE_CLASS: Record<TileSize, string> = {
    normal: 'rounded-2xl text-xl',
    wide: 'rounded-2xl text-2xl',
    tall: 'rounded-2xl text-2xl',
    big: 'rounded-3xl text-4xl sm:text-5xl',
};

const NUMBER_CLASS: Record<TileSize, string> = {
    normal: 'text-2xl',
    wide: 'text-3xl',
    tall: 'text-3xl',
    big: 'text-6xl sm:text-7xl',
};

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

    <div class="min-h-dvh bg-[radial-gradient(circle_at_20%_10%,#134e4a_0%,#022c22_45%,#450a0a_100%)]">
        <div class="mx-auto w-full max-w-2xl px-4 pt-6 pb-10">
            <header class="mb-8 text-center text-white">
                <p class="text-4xl">
                    <span class="inline-block -rotate-6">🎅</span>
                    <span class="inline-block rotate-3">🎄</span>
                    <span class="inline-block -rotate-3">❄️</span>
                    <span class="inline-block rotate-6">🎁</span>
                </p>
                <h1 class="mt-3 font-serif text-3xl font-bold tracking-tight text-amber-100 drop-shadow-lg sm:text-4xl">
                    Calendrier de l'Avent
                </h1>
                <p class="mt-2 text-sm text-amber-100/70">
                    Ouvre une case par jour, résous le défi et découvre ton cadeau !
                </p>

                <div class="mx-auto mt-5 max-w-xs">
                    <div class="flex items-center justify-between text-xs text-white/80">
                        <span>🎁 Cadeaux débloqués</span>
                        <span class="font-semibold text-amber-300">{{ solvedCount }} / {{ days.length }}</span>
                    </div>
                    <div class="mt-1 h-2.5 overflow-hidden rounded-full bg-white/15 ring-1 ring-white/20 ring-inset">
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-amber-400 to-red-400 transition-all duration-700"
                            :style="{ width: `${days.length ? (solvedCount / days.length) * 100 : 0}%` }"
                        />
                    </div>
                </div>

                <Link
                    v-if="isAdmin"
                    :href="calendarEdit()"
                    class="mt-5 inline-flex items-center gap-1 rounded-full border border-amber-200/40 bg-white/5 px-4 py-1.5 text-xs text-amber-100 transition-colors hover:bg-white/15"
                >
                    ✏️ Personnaliser les cases
                </Link>
            </header>

            <div class="grid auto-rows-[4.4rem] grid-cols-4 gap-2.5 [grid-auto-flow:dense] sm:auto-rows-[5.4rem] sm:gap-3">
                <button
                    v-for="tile in deck"
                    :key="tile.day.id"
                    type="button"
                    :disabled="tile.day.status === 'locked'"
                    class="group relative border shadow-lg transition-all duration-200"
                    :class="[
                        SIZE_CLASSES[tile.size],
                        TILE_CLASS[tile.size],
                        tile.rotate,
                        {
                            'cursor-not-allowed border-white/10 bg-white/5 text-white/35 backdrop-blur-sm': tile.day.status === 'locked',
                            'border-amber-200/50 bg-gradient-to-br from-red-600 to-red-900 text-white shadow-red-900/40 hover:scale-[1.04] hover:-rotate-1 active:scale-95': tile.day.status === 'available' && tile.day.day % 2 === 1,
                            'border-lime-200/50 bg-gradient-to-br from-emerald-600 to-emerald-900 text-white shadow-emerald-900/40 hover:scale-[1.04] hover:rotate-1 active:scale-95': tile.day.status === 'available' && tile.day.day % 2 === 0,
                            'border-amber-300/70 bg-gradient-to-br from-amber-200 to-amber-400 text-amber-950 hover:scale-[1.04] dark:from-amber-700 dark:to-amber-900 dark:text-amber-100': tile.day.status === 'solved',
                            'ring-2 ring-amber-300 ring-offset-2 ring-offset-emerald-950': tile.day.is_today,
                        },
                    ]"
                    :aria-label="tile.day.status === 'locked'
                        ? `Case ${tile.day.day}, s'ouvre le ${formatDoorDate(tile.day)}`
                        : tile.day.status === 'solved'
                            ? `Case ${tile.day.day}, cadeau débloqué`
                            : `Ouvrir la case ${tile.day.day}`"
                    @click="open(tile.day)"
                >
                    <span
                        v-if="tile.size !== 'normal'"
                        class="absolute top-1.5 left-2 text-[10px] font-medium opacity-60 sm:top-2 sm:text-xs"
                    >
                        {{ formatDoorDate(tile.day) }}
                    </span>

                    <span v-if="tile.day.status === 'solved'" class="drop-shadow">
                        <span :class="tile.size === 'big' ? 'text-6xl' : 'text-3xl'">🎁</span>
                        <span class="sr-only">Cadeau débloqué</span>
                    </span>

                    <span
                        v-else-if="tile.day.status === 'available'"
                        class="flex flex-col items-center justify-center gap-0.5"
                        :class="tile.size === 'big' ? 'gap-1' : ''"
                    >
                        <span v-if="tile.size !== 'normal'" class="opacity-70">{{ tile.emoji }}</span>
                        <span class="font-extrabold tracking-tight drop-shadow" :class="NUMBER_CLASS[tile.size]">
                            {{ tile.day.day }}
                        </span>
                        <span
                            v-if="tile.size === 'big'"
                            class="text-xs font-medium text-amber-200"
                        >
                            Jour J !
                        </span>
                    </span>

                    <span v-else class="flex flex-col items-center justify-center leading-tight">
                        <span :class="tile.size === 'normal' ? 'text-[10px]' : 'text-sm'">🔒</span>
                        <span class="font-bold" :class="NUMBER_CLASS[tile.size] === 'text-2xl' ? 'text-lg' : 'text-2xl'">
                            {{ tile.day.day }}
                        </span>
                    </span>

                    <span
                        v-if="tile.day.status !== 'locked'"
                        class="absolute bottom-1.5 left-0 w-full text-center text-[9px] uppercase tracking-widest opacity-0 transition-opacity duration-200 group-hover:opacity-80"
                    >
                        {{ tile.day.status === 'solved' ? 'Revoir' : 'Ouvrir' }}
                    </span>
                </button>
            </div>
        </div>

        <DoorDialog v-if="openDay" :key="openDay.id" :day="openDay" @close="openDayId = null" />
    </div>
</template>
