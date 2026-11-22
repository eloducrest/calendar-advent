<script setup lang="ts">
import { ref, watch } from 'vue';

type Card = {
    id: number;
    symbol: string;
    flipped: boolean;
    matched: boolean;
};

const props = defineProps<{
    symbols: string[];
}>();

const emit = defineEmits<{
    solved: [];
}>();

const cards = ref<Card[]>([]);
const flippedIds = ref<number[]>([]);
const lock = ref(false);
const moves = ref(0);

function reset(): void {
    const pairs = props.symbols.flatMap((symbol, index): Card[] => [
        { id: index * 2, symbol, flipped: false, matched: false },
        { id: index * 2 + 1, symbol, flipped: false, matched: false },
    ]);

    cards.value = pairs
        .map((card) => ({ card, sort: Math.random() }))
        .sort((a, b) => a.sort - b.sort)
        .map(({ card }) => card);
    flippedIds.value = [];
    lock.value = false;
    moves.value = 0;
}

watch(() => props.symbols, reset, { immediate: true, deep: false });

function flip(card: Card): void {
    if (lock.value || card.flipped || card.matched) {
        return;
    }

    card.flipped = true;
    flippedIds.value.push(card.id);

    if (flippedIds.value.length < 2) {
        return;
    }

    moves.value++;

    const [first, second] = flippedIds.value
        .map((id) => cards.value.find((current) => current.id === id))
        .filter((current) => current !== undefined);

    if (first !== undefined && second !== undefined && first.symbol === second.symbol) {
        first.matched = true;
        second.matched = true;
        flippedIds.value = [];

        if (cards.value.every((current) => current.matched)) {
            setTimeout(() => emit('solved'), 500);
        }

        return;
    }

    lock.value = true;
    setTimeout(() => {
        if (first) {
            first.flipped = false;
        }
        if (second) {
            second.flipped = false;
        }
        flippedIds.value = [];
        lock.value = false;
    }, 750);
}
</script>

<template>
    <div>
        <div class="grid grid-cols-4 gap-2">
            <button
                v-for="card in cards"
                :key="card.id"
                type="button"
                class="flex aspect-square items-center justify-center rounded-lg text-3xl transition-all duration-200 select-none sm:gap-3"
                :class="card.flipped || card.matched
                    ? 'bg-emerald-100 dark:bg-emerald-900/40'
                    : 'bg-red-700 hover:bg-red-600 dark:bg-red-950 dark:hover:bg-red-900'"
                :aria-label="card.flipped || card.matched ? card.symbol : 'Carte cachée'"
                @click="flip(card)"
            >
                <span v-if="card.flipped || card.matched">{{ card.symbol }}</span>
                <span v-else class="text-lg text-amber-200/90">?</span>
            </button>
        </div>

        <p class="mt-3 text-center text-xs text-muted-foreground">
            Essais : {{ moves }}
        </p>
    </div>
</template>
