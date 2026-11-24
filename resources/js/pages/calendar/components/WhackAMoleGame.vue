<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

// Frappe 10 taupes, sans limite de temps : aucun moyen de perdre.
const TARGET_HITS = 10;
const HOLES = 9;
const SPAWN_INTERVAL_MS = 550;
const MOLE_LIFETIME_MS = 950;

const emit = defineEmits<{
    solved: [];
}>();

const hits = ref(0);
const done = ref(false);
// index de trou -> identifiant d'intervalle qui fait disparaître la taupe.
const timers = ref<Record<number, number>>({});

let spawner: ReturnType<typeof setInterval> | undefined;

function pop(): void {
    if (done.value) {
        return;
    }

    const freeHoles = [...Array(HOLES).keys()].filter((hole) => timers.value[hole] === undefined);
    const hole = freeHoles[Math.floor(Math.random() * freeHoles.length)];

    if (hole === undefined || freeHoles.length <= HOLES - 3) {
        return;
    }

    timers.value[hole] = window.setTimeout(() => despawn(hole), MOLE_LIFETIME_MS);
}

function despawn(hole: number): void {
    delete timers.value[hole];
}

function whack(hole: number): void {
    if (done.value || timers.value[hole] === undefined) {
        return;
    }

    clearTimeout(timers.value[hole]);
    despawn(hole);
    hits.value++;

    if (hits.value >= TARGET_HITS) {
        finish();
    }
}

function finish(): void {
    done.value = true;
    Object.values(timers.value).forEach((timer) => clearTimeout(timer));
    timers.value = {};
    if (spawner !== undefined) {
        clearInterval(spawner);
    }
    setTimeout(() => emit('solved'), 500);
}

onMounted(() => {
    spawner = setInterval(pop, SPAWN_INTERVAL_MS);
});

onBeforeUnmount(() => {
    if (spawner !== undefined) {
        clearInterval(spawner);
    }
    Object.values(timers.value).forEach((timer) => clearTimeout(timer));
});
</script>

<template>
    <div>
        <div class="mx-auto grid max-w-64 grid-cols-3 gap-2">
            <button
                v-for="hole in HOLES"
                :key="hole"
                type="button"
                class="flex aspect-square items-center justify-center rounded-full text-3xl transition-transform duration-100 select-none active:scale-95"
                :class="timers[hole - 1] !== undefined
                    ? 'bg-amber-800 shadow-inner ring-4 ring-amber-950/40'
                    : 'bg-emerald-900 shadow-inner'"
                :aria-label="timers[hole - 1] !== undefined ? 'Taupe !' : 'Trou vide'"
                @click="whack(hole - 1)"
            >
                <span v-if="timers[hole - 1] !== undefined" class="animate-bounce">🐹</span>
            </button>
        </div>

        <p class="mt-4 min-h-5 text-center text-sm font-medium">
            {{ done ? 'Toutes frappées ! 🐹💥' : `Taupes frappées : ${hits} / ${TARGET_HITS}` }}
        </p>
    </div>
</template>
