<script setup lang="ts">
import { onBeforeUnmount, ref } from 'vue';

const WIN_ROUNDS = 5;
const PADS = [
    { color: 'bg-red-500 hover:bg-red-400 active:bg-red-400' },
    { color: 'bg-emerald-500 hover:bg-emerald-400 active:bg-emerald-400' },
    { color: 'bg-amber-400 hover:bg-amber-300 active:bg-amber-300' },
    { color: 'bg-sky-500 hover:bg-sky-400 active:bg-sky-400' },
] as const;

const emit = defineEmits<{
    solved: [];
}>();

const phase = ref<'idle' | 'showing' | 'playing' | 'failed' | 'won'>('idle');
const round = ref(0);
const sequence = ref<number[]>([]);
const inputStep = ref(0);
const lit = ref<number | null>(null);

// Chaque partie a une "génération" : dès qu'elle change (échec relancé,
// démontage du composant), les boucles asynchrones en cours s'arrêtent.
let generation = 0;

onBeforeUnmount(() => {
    generation++;
});

function sleep(ms: number, token: number): Promise<void> {
    return new Promise((resolve) => {
        setTimeout(() => {
            if (token === generation) {
                resolve();
            }
        }, ms);
    });
}

function start(): void {
    generation++;
    const token = generation;
    sequence.value = [];
    round.value = 1;
    nextRound(token);
}

function nextRound(token: number): void {
    sequence.value = [...sequence.value, Math.floor(Math.random() * PADS.length)];
    showSequence(token);
}

async function showSequence(token: number): Promise<void> {
    phase.value = 'showing';
    lit.value = null;

    for (const pad of sequence.value) {
        await sleep(450, token);
        if (token !== generation) {
            return;
        }
        lit.value = pad;
        await sleep(320, token);
        if (token !== generation) {
            return;
        }
        lit.value = null;
    }

    inputStep.value = 0;
    phase.value = 'playing';
}

async function press(pad: number): Promise<void> {
    if (phase.value !== 'playing') {
        return;
    }

    const token = generation;
    lit.value = pad;
    await sleep(180, token);
    lit.value = null;

    if (token !== generation || phase.value !== 'playing') {
        return;
    }

    if (pad !== sequence.value[inputStep.value]) {
        // Manche manquée : on rejoue la même séquence, sans pénalité.
        phase.value = 'failed';
        await sleep(900, token);
        if (token !== generation) {
            return;
        }
        showSequence(token);

        return;
    }

    inputStep.value++;

    if (inputStep.value < sequence.value.length) {
        return;
    }

    if (round.value >= WIN_ROUNDS) {
        phase.value = 'won';
        emit('solved');

        return;
    }

    round.value++;
    nextRound(token);
}

const statusMessage = (): string => {
    switch (phase.value) {
        case 'showing':
            return 'Regarde bien la séquence...';
        case 'playing':
            return `Manche ${round.value} / ${WIN_ROUNDS} — répète la séquence !`;
        case 'failed':
            return 'Raté ! On regarde à nouveau...';
        case 'won':
            return 'Gagné ! Super mémoire ✨';
        default:
            return '5 manches à répéter et le cadeau est à toi.';
    }
};
</script>

<template>
    <div>
        <div class="mx-auto grid max-w-56 grid-cols-2 gap-3">
            <button
                v-for="(pad, index) in PADS"
                :key="index"
                type="button"
                class="aspect-square rounded-2xl transition-all duration-150"
                :class="[
                    pad.color,
                    lit === index
                        ? 'scale-105 ring-4 ring-white brightness-150'
                        : 'brightness-75',
                    phase === 'idle' || phase === 'won' ? 'pointer-events-none opacity-40' : '',
                ]"
                :aria-label="`Pad ${index + 1}`"
                @click="press(index)"
            />
        </div>

        <p class="mt-4 min-h-5 text-center text-sm font-medium">
            {{ statusMessage() }}
        </p>

        <div
            v-if="phase === 'idle' || phase === 'won'"
            class="mt-2 flex justify-center"
        >
            <button
                type="button"
                class="rounded-full bg-emerald-600 px-6 py-2 font-semibold text-white transition hover:bg-emerald-500"
                @click="start"
            >
                {{ phase === 'won' ? 'Rejouer' : 'Jouer ▶' }}
            </button>
        </div>
    </div>
</template>
