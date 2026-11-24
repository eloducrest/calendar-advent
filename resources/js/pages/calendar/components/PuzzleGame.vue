<script setup lang="ts">
import { ref } from 'vue';

// Taquin 3×3 : les 8 tuiles emoji + une case vide. Le mélange applique
// uniquement des coups légaux, la grille est donc toujours résoluble.
const TILES = ['🎁', '🎄', '❄️', '⭐', '🕯️', '🧦', '⛄', '🔔'];
const SIZE = 3;

const emit = defineEmits<{
    solved: [];
}>();

// board contient les indices 0..8 : 0..7 = tuiles, 8 = case vide.
const board = ref<number[]>([]);
const moves = ref(0);
const done = ref(false);

function shuffledBoard(): number[] {
    const current = [...Array(TILES.length + 1).keys()];

    for (let turn = 0; turn < 80; turn++) {
        const blank = current.indexOf(8);
        const row = Math.floor(blank / SIZE);
        const column = blank % SIZE;
        const neighbours: number[] = [];

        if (row > 0) {
            neighbours.push(blank - SIZE);
        }
        if (row < SIZE - 1) {
            neighbours.push(blank + SIZE);
        }
        if (column > 0) {
            neighbours.push(blank - 1);
        }
        if (column < SIZE - 1) {
            neighbours.push(blank + 1);
        }

        const pick = neighbours[Math.floor(Math.random() * neighbours.length)]!;
        current[blank] = current[pick]!;
        current[pick] = 8;
    }

    return current;
}

function reset(): void {
    board.value = shuffledBoard();
    moves.value = 0;
    done.value = false;
}

reset();

function movable(index: number): boolean {
    if (done.value) {
        return false;
    }

    const blank = board.value.indexOf(8);

    return [index - SIZE, index + SIZE, index - 1, index + 1].includes(blank)
        // Empêche un "wrap" visuel (fin de ligne ↔ début de ligne suivante).
        && Math.abs((index % SIZE) - (blank % SIZE)) <= 1;
}

function move(index: number): void {
    if (!movable(index)) {
        return;
    }

    const blank = board.value.indexOf(8);
    board.value[blank] = board.value[index]!;
    board.value[index] = 8;
    moves.value++;

    if (board.value.every((tile, position) => tile === position)) {
        done.value = true;
        setTimeout(() => emit('solved'), 600);
    }
}
</script>

<template>
    <div>
        <p class="mb-2 text-center text-xs text-muted-foreground">
            Remets les tuiles dans l'ordre, modèle ci-dessus :
            <span class="tracking-widest">{{ TILES.join('') }}&nbsp;&nbsp;·</span>
        </p>

        <div class="mx-auto grid max-w-64 grid-cols-3 gap-1.5">
            <button
                v-for="(tile, index) in board"
                :key="index"
                type="button"
                class="flex aspect-square items-center justify-center rounded-lg text-3xl transition-all duration-150 select-none"
                :class="tile === 8
                    ? 'bg-emerald-100 dark:bg-emerald-900/40'
                    : movable(index)
                        ? 'bg-red-700 shadow-md cursor-pointer hover:bg-red-600 dark:bg-red-950 dark:hover:bg-red-900'
                        : 'bg-red-700/60 dark:bg-red-950/60'"
                :aria-label="tile === 8 ? 'Case vide' : `Tuile ${tile + 1}`"
                @click="move(index)"
            >
                <span v-if="tile !== 8">{{ TILES[tile] }}</span>
            </button>
        </div>

        <p class="mt-3 text-center text-xs text-muted-foreground">
            {{ done ? 'Image reconstituée ! ✨' : `Coups : ${moves}` }}
        </p>
    </div>
</template>
