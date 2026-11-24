<script setup lang="ts">
import { ref } from 'vue';

// Taquin 3×3 : les 8 tuiles emoji + une case vide. Le mélange applique
// uniquement des coups légaux, la grille est donc toujours résoluble.
const TILES = ['🎁', '🎄', '❄️', '⭐', '🕯️', '🧦', '⛄', '🔔'];
const SIZE = 3;
const SHUFFLE_MOVES = 20;

const emit = defineEmits<{
    solved: [];
}>();

// board contient les indices 0..8 : 0..7 = tuiles, 8 = case vide.
const board = ref<number[]>([]);
const moves = ref(0);
const done = ref(false);

function legalNeighbours(position: number): number[] {
    const row = Math.floor(position / SIZE);
    const column = position % SIZE;
    const neighbours: number[] = [];

    if (row > 0) {
        neighbours.push(position - SIZE);
    }
    if (row < SIZE - 1) {
        neighbours.push(position + SIZE);
    }
    if (column > 0) {
        neighbours.push(position - 1);
    }
    if (column < SIZE - 1) {
        neighbours.push(position + 1);
    }

    return neighbours;
}

function shuffledBoard(): number[] {
    const current = [...Array(TILES.length + 1).keys()];

    for (let turn = 0; turn < SHUFFLE_MOVES; turn++) {
        const blank = current.indexOf(8);
        const neighbours = legalNeighbours(blank);
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

// Une tuile est déplaçable si elle partage une ligne ou une colonne avec
// la case vide : tout le segment jusqu'au vide glisse d'un coup.
function isMovable(index: number): boolean {
    if (done.value) {
        return false;
    }

    const blank = board.value.indexOf(8);

    return index !== blank
        && (Math.floor(index / SIZE) === Math.floor(blank / SIZE) || index % SIZE === blank % SIZE);
}

function move(index: number): void {
    if (!isMovable(index)) {
        return;
    }

    const blank = board.value.indexOf(8);
    const sameRow = Math.floor(index / SIZE) === Math.floor(blank / SIZE);
    const step = sameRow ? 1 : SIZE;

    for (let position = blank; position !== index; ) {
        const next = position + Math.sign(index - position) * step;
        board.value[position] = board.value[next]!;
        position = next;
    }

    board.value[index] = 8;
    moves.value++;

    if (board.value.every((tile, position) => tile === position)) {
        done.value = true;
        setTimeout(() => emit('solved'), 900);
    }
}
</script>

<template>
    <div class="flex flex-col items-center">
        <div class="mb-3 flex items-center gap-3">
            <div
                class="grid grid-cols-3 gap-0.5 rounded-lg bg-emerald-800/15 p-1.5 dark:bg-emerald-700/20"
                aria-label="Modèle à reconstituer"
            >
                <span
                    v-for="tile in [...Array(TILES.length + 1).keys()]"
                    :key="tile"
                    class="flex size-6 items-center justify-center rounded text-sm"
                >
                    {{ tile === 8 ? '·' : TILES[tile] }}
                </span>
            </div>
            <p class="max-w-24 text-xs leading-snug text-muted-foreground">
                Remets les tuiles dans l'ordre du modèle
            </p>
        </div>

        <div class="grid w-full max-w-72 grid-cols-3 gap-2">
            <button
                v-for="(tile, index) in board"
                :key="index"
                type="button"
                class="relative flex aspect-square items-center justify-center rounded-xl text-3xl transition-all duration-150 select-none active:scale-95"
                :class="tile === 8
                    ? 'bg-transparent'
                    : [
                        'bg-red-700 shadow-md dark:bg-red-950',
                        isMovable(index)
                            ? 'ring-2 ring-amber-300 cursor-pointer hover:bg-red-600 hover:dark:bg-red-900 hover:scale-[1.03]'
                            : 'cursor-default opacity-80',
                    ]"
                :aria-label="tile === 8
                    ? 'Case vide'
                    : isMovable(index) ? `Tuile ${tile + 1}, déplaçable` : `Tuile ${tile + 1}`"
                @click="move(index)"
            >
                <span v-if="tile !== 8">{{ TILES[tile] }}</span>
                <span
                    v-if="tile !== 8"
                    class="absolute top-1 left-1.5 text-[10px] leading-none font-semibold text-white/50"
                >
                    {{ tile + 1 }}
                </span>
            </button>
        </div>

        <template v-if="done">
            <p class="mt-4 animate-pulse text-center text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                Image reconstituée ! ✨
            </p>
        </template>
        <template v-else>
            <div class="mt-4 flex items-center gap-3">
                <p class="text-xs text-muted-foreground">Coups : {{ moves }}</p>
                <button
                    type="button"
                    class="rounded-full border px-3 py-1 text-xs font-medium text-muted-foreground transition hover:bg-muted"
                    @click="reset"
                >
                    Mélanger ↻
                </button>
            </div>
        </template>
    </div>
</template>
