<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { edit as editDay } from '@/actions/App/Http/Controllers/Calendar/CalendarDayController';
import { index as calendarIndex } from '@/routes/calendar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types';
import type { CalendarDayEditSummary } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Calendrier de l\'Avent',
                href: calendarIndex(),
            },
            {
                title: 'Éditer les cases',
                href: '/calendar/edit',
            },
        ] satisfies BreadcrumbItem[],
    },
});

const { days } = defineProps<{
    days: CalendarDayEditSummary[];
}>();

const challengeLabels: Record<string, string> = {
    riddle: 'Devinette',
    qcm: 'QCM',
    game: 'Mini-jeu',
};
</script>

<template>
    <Head title="Éditer les cases" />

    <div class="mx-auto w-full max-w-4xl px-4 py-6">
        <h1 class="text-xl font-semibold">Éditer les cases du calendrier</h1>
        <p class="mt-1 text-sm text-muted-foreground">
            Personnalise chaque défi et chaque cadeau. Les jours non écoulés peuvent être modifiés à tout moment.
        </p>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="day in days" :key="day.id" class="py-4">
                <CardContent class="flex h-full flex-col justify-between gap-3">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="font-bold">Case {{ day.day }}</h2>
                            <Badge variant="secondary">{{ challengeLabels[day.challenge_type] }}</Badge>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            S'ouvre le {{ new Date(day.available_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long' }) }}
                        </p>

                        <p v-if="day.challenge_summary" class="mt-2 line-clamp-2 text-sm">
                            {{ day.challenge_summary }}
                        </p>

                        <p class="mt-2 text-sm">
                            <template v-if="day.gift_title">🎁 {{ day.gift_title }}</template>
                            <template v-else><span class="text-muted-foreground">Aucun cadeau défini</span></template>
                        </p>
                    </div>

                    <Button variant="outline" as-child class="w-full">
                        <Link :href="editDay({ day: day.id })">Modifier</Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
