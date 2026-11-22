<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { update } from '@/actions/App/Http/Controllers/Calendar/CalendarDayController';
import { index as calendarEditIndex } from '@/routes/calendar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';
import type { CalendarDayEditItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Calendrier de l\'Avent',
                href: '/calendar',
            },
            {
                title: 'Éditer les cases',
                href: calendarEditIndex(),
            },
        ] satisfies BreadcrumbItem[],
    },
});

const { day } = defineProps<{
    day: CalendarDayEditItem;
}>();

const form = useForm({
    challenge_type: day.challenge_type,
    available_at: day.available_at,
    riddle_question: day.riddle_question,
    riddle_answers: day.riddle_answers,
    qcm_question: day.qcm_question,
    qcm_options: [...day.qcm_options],
    qcm_correct_index: day.qcm_correct_index,
    game_title: day.game_title,
    game_instructions: day.game_instructions,
    game_symbols: day.game_symbols,
    gift_title: day.gift_title,
    gift_message: day.gift_message,
    gift_link_url: day.gift_link_url,
    gift_link_label: day.gift_link_label,
    gift_image: null as File | null,
    remove_gift_image: false as boolean,
});

const challengeTypeLabels: Record<string, string> = {
    riddle: 'Devinette',
    qcm: 'QCM',
    game: 'Mini-jeu',
};

function onGiftImageChange(event: Event): void {
    form.gift_image = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function onRemoveGiftImageChange(checked: boolean | 'indeterminate'): void {
    form.remove_gift_image = checked === true;
}

function formatDayDate(value: string): string {
    return new Date(value).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
}
</script>

<template>
    <Head :title="`Éditer la case ${day.day}`" />

    <div class="mx-auto w-full max-w-2xl px-4 py-6">
        <h1 class="flex items-center gap-2 text-xl font-semibold">
            <span>Case {{ day.day }}</span>
            <Badge variant="secondary">{{ challengeTypeLabels[form.challenge_type] }}</Badge>
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">
            S'ouvre le {{ formatDayDate(day.available_at) }}.
        </p>

        <form
            class="mt-6 space-y-6"
            @submit.prevent="form.patch(update.url({ day: day.id }))"
        >
            <Card>
                <CardContent class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="challenge_type">Type de défi</Label>
                        <select
                            id="challenge_type"
                            v-model="form.challenge_type"
                            class="border-input bg-background flex h-9 w-full rounded-md border px-3 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <option value="riddle">Devinette (réponse à écrire)</option>
                            <option value="qcm">QCM (choix multiples)</option>
                            <option value="game">Mini-jeu</option>
                        </select>
                        <InputError :message="form.errors.challenge_type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="available_at">Date de déblocage</Label>
                        <Input
                            id="available_at"
                            v-model="form.available_at"
                            type="date"
                        />
                        <InputError :message="form.errors.available_at" />
                    </div>

                    <template v-if="form.challenge_type === 'riddle'">
                        <div class="grid gap-2">
                            <Label for="riddle_question">Devinette</Label>
                            <textarea
                                id="riddle_question"
                                v-model="form.riddle_question"
                                rows="3"
                                class="border-input bg-background flex w-full rounded-md border px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring"
                                placeholder="Ex : Je tombe du ciel en hiver et je fond dans la main. Suis-je ?"
                            ></textarea>
                            <InputError :message="form.errors.riddle_question" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="riddle_answers">Réponses acceptées (une par ligne)</Label>
                            <textarea
                                id="riddle_answers"
                                v-model="form.riddle_answers"
                                rows="4"
                                class="border-input bg-background flex w-full rounded-md border px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring"
                                placeholder="neige&#10;flocon"
                            ></textarea>
                            <p class="text-xs text-muted-foreground">
                                Les accents, majuscules et ponctuation sont ignorés lors de la comparaison.
                            </p>
                            <InputError :message="form.errors.riddle_answers" />
                        </div>
                    </template>

                    <template v-else-if="form.challenge_type === 'qcm'">
                        <div class="grid gap-2">
                            <Label for="qcm_question">Question</Label>
                            <textarea
                                id="qcm_question"
                                v-model="form.qcm_question"
                                rows="2"
                                class="border-input bg-background flex w-full rounded-md border px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring"
                            ></textarea>
                            <InputError :message="form.errors.qcm_question" />
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div v-for="(_, index) in 3" :key="index" class="grid gap-2">
                                <Label :for="`qcm_options_${index}`">Choix {{ index + 1 }}</Label>
                                <Input
                                    :id="`qcm_options_${index}`"
                                    v-model="form.qcm_options[index]"
                                    type="text"
                                    maxlength="255"
                                />
                            </div>
                        </div>
                        <InputError :message="form.errors['qcm_options.0'] || form.errors.qcm_options" />

                        <div class="grid gap-2">
                            <Label for="qcm_correct_index">Bonne réponse</Label>
                            <select
                                id="qcm_correct_index"
                                v-model.number="form.qcm_correct_index"
                                class="border-input bg-background flex h-9 w-full rounded-md border px-3 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring"
                            >
                                <option
                                    v-for="(_, index) in 3"
                                    :key="index"
                                    :value="index"
                                >
                                    Choix {{ index + 1 }}
                                </option>
                            </select>
                            <InputError :message="form.errors.qcm_correct_index" />
                        </div>
                    </template>

                    <template v-else>
                        <div class="grid gap-2">
                            <Label for="game_title">Titre du mini-jeu</Label>
                            <Input
                                id="game_title"
                                v-model="form.game_title"
                                type="text"
                                maxlength="120"
                                placeholder="Ex : Trouve les paires"
                            />
                            <InputError :message="form.errors.game_title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="game_instructions">Instructions</Label>
                            <textarea
                                id="game_instructions"
                                v-model="form.game_instructions"
                                rows="2"
                                class="border-input bg-background flex w-full rounded-md border px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring"
                            ></textarea>
                            <InputError :message="form.errors.game_instructions" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="game_symbols">Symboles des cartes (séparés par des virgules, 6 pour 12 cartes)</Label>
                            <Input
                                id="game_symbols"
                                v-model="form.game_symbols"
                                type="text"
                                maxlength="255"
                                placeholder="🎄, ⭐, 🎁, ❄️, 🔔, 🕯️"
                            />
                            <InputError :message="form.errors.game_symbols" />
                        </div>
                    </template>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="space-y-4">
                    <h2 class="font-semibold">Le cadeau</h2>

                    <div class="grid gap-2">
                        <Label for="gift_title">Titre</Label>
                        <Input
                            id="gift_title"
                            v-model="form.gift_title"
                            type="text"
                            maxlength="120"
                        />
                        <InputError :message="form.errors.gift_title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="gift_message">Message</Label>
                        <textarea
                            id="gift_message"
                            v-model="form.gift_message"
                            rows="3"
                            class="border-input bg-background flex w-full rounded-md border px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring"
                        ></textarea>
                        <InputError :message="form.errors.gift_message" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="gift_image">Image</Label>
                        <img
                            v-if="day.gift_image_url && !form.remove_gift_image"
                            :src="day.gift_image_url"
                            alt="Image actuelle du cadeau"
                            class="w-32 rounded-lg border object-cover"
                        />
                        <Input
                            id="gift_image"
                            type="file"
                            accept="image/png,image/jpeg,image/webp"
                            @change="onGiftImageChange"
                        />
                        <InputError :message="form.errors.gift_image" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="gift_link_url">Lien (URL du cadeau en ligne)</Label>
                        <Input
                            id="gift_link_url"
                            v-model="form.gift_link_url"
                            type="url"
                            maxlength="2048"
                            placeholder="https://..."
                        />
                        <InputError :message="form.errors.gift_link_url" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="gift_link_label">Libellé du bouton du lien</Label>
                        <Input
                            id="gift_link_label"
                            v-model="form.gift_link_label"
                            type="text"
                            maxlength="120"
                            placeholder="Ex : Utiliser mon bon d'achat"
                        />
                        <InputError :message="form.errors.gift_link_label" />
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
                </Button>
                <Button variant="ghost" as-child>
                    <Link :href="calendarEditIndex()">Annuler</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
