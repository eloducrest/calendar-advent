<script setup lang="ts">
import type { CalendarDayGift } from '@/types';
import { Button } from '@/components/ui/button';

defineProps<{
    gift: CalendarDayGift;
    day: number;
}>();
</script>

<template>
    <div class="text-center">
        <div class="text-5xl animate-bounce">🎁</div>
        <h3 class="mt-3 text-xl font-semibold">
            {{ gift.title ?? `Cadeau du jour ${day}` }}
        </h3>
        <p v-if="gift.message" class="mt-2 text-sm whitespace-pre-line text-muted-foreground">
            {{ gift.message }}
        </p>
        <img
            v-if="gift.image_url"
            :src="gift.image_url"
            alt="Illustration du cadeau"
            class="mt-4 w-full rounded-xl border object-cover"
        />
        <a
            v-if="gift.link_url"
            :href="gift.link_url"
            target="_blank"
            rel="noopener"
            class="mt-4 block"
        >
            <Button class="w-full">
                {{ gift.link_label ?? 'Ouvrir le lien' }} ↗
            </Button>
        </a>
    </div>
</template>
