<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import type { RequestPayload } from '@inertiajs/core';
import { update } from '@/actions/App/Http/Controllers/Calendar/CalendarDayController';
import { index as calendarEditIndex } from '@/routes/calendar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
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

const page = usePage();

const challengeType = ref<string>(day.challenge_type);
const gameVariant = ref<string>(day.game_variant);
const processing = ref(false);

const challengeTypeLabels: Record<string, string> = {
    riddle: 'Devinette',
    qcm: 'QCM',
    game: 'Mini-jeu',
};

const gameVariantLabels: Record<string, string> = {
    memory: 'Memory (paires)',
    simon: 'Simon (séquence à répéter)',
    puzzle: 'Taquin (tuiles à remettre en ordre)',
    mole: 'Frappe-Taupe',
};

function onChallengeTypeChange(event: Event): void {
    challengeType.value = (event.target as HTMLSelectElement).value;
}

function onGameVariantChange(event: Event): void {
    gameVariant.value = (event.target as HTMLSelectElement).value;
}

function errorFor(key: string): string | undefined {
    const errors = page.props.errors as Record<string, string | undefined>;

    return errors[key];
}

function save(event: Event): void {
    const el = event.target as HTMLFormElement;

    const payload: Record<string, unknown> = {};
    new FormData(el).forEach((value, key) => {
        // qcm_options[0], qcm_options[1]... arrive avec des clés à crochets :
        // on les regroupe en vrai tableau avant l'envoi.
        const indexed = /^(?<name>\w+)\[(?<index>\d+)\]$/.exec(key);
        if (indexed?.groups) {
            const { index, name } = indexed.groups;
            const list = (payload[name] ?? []) as unknown[];
            list[Number(index)] = value;
            payload[name] = list;

            return;
        }
        payload[key] = value;
    });

    const fileInput = el.querySelector<HTMLInputElement>('#gift_image');
    if (fileInput?.files?.[0]) {
        payload.gift_image = fileInput.files[0];
    }

    processing.value = true;
    router.patch(update.url({ day: day.id }), payload as RequestPayload, {
        onFinish: () => {
            processing.value = false;
        },
    });
}
</script>

<template>
    <Head :title="`Éditer la case ${day.day}`" />

    <div class="mx-auto w-full max-w-2xl px-4 py-6">
        <h1 class="flex items-center gap-2 text-xl font-semibold">
            <span>Case {{ day.day }}</span>
            <Badge variant="secondary">{{ challengeTypeLabels[challengeType] }}</Badge>
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">
            S'ouvre le {{ new Date(day.available_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}.
        </p>

        <form class="mt-6 space-y-6" @submit.prevent="save">
            <Card>
                <CardContent class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="challenge_type">Type de défi</Label>
                        <select
                            id="challenge_type"
                            name="challenge_type"
                            class="border-input bg-background flex h-9 w-full rounded-md border px-3 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring"
                            @change="onChallengeTypeChange"
                        >
                            <option value="riddle" :selected="day.challenge_type === 'riddle'">Devinette (réponse à écrire)</option>
                            <option value="qcm" :selected="day.challenge_type === 'qcm'">QCM (choix multiples)</option>
                            <option value="game" :selected="day.challenge_type === 'game'">Mini-jeu</option>
                        </select>
                        <InputError :message="errorFor('challenge_type')" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="available_at">Date de déblocage</Label>
                        <Input
                            id="available_at"
                            type="date"
                            name="available_at"
                            :default-value="day.available_at"
                        />
                        <InputError :message="errorFor('available_at')" />
                    </div>

                    <template v-if="challengeType === 'riddle'">
                        <div class="grid gap-2">
                            <Label for="riddle_question">Devinette</Label>
                            <textarea
                                id="riddle_question"
                                name="riddle_question"
                                rows="3"
                                class="border-input bg-background flex w-full rounded-md border px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring"
                                placeholder="Ex : Je tombe du ciel en hiver et je fond dans la main. Suis-je ?"
                            >{{ day.riddle_question }}</textarea>
                            <InputError :message="errorFor('riddle_question')" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="riddle_answers">Réponses acceptées (une par ligne)</Label>
                            <textarea
                                id="riddle_answers"
                                name="riddle_answers"
                                rows="4"
                                class="border-input bg-background flex w-full rounded-md border px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring"
                                placeholder="neige&#10;flocon"
                            >{{ day.riddle_answers }}</textarea>
                            <p class="text-xs text-muted-foreground">
                                Les accents, majuscules et ponctuation sont ignorés lors de la comparaison.
                            </p>
                            <InputError :message="errorFor('riddle_answers')" />
                        </div>
                    </template>

                    <template v-else-if="challengeType === 'qcm'">
                        <div class="grid gap-2">
                            <Label for="qcm_question">Question</Label>
                            <textarea
                                id="qcm_question"
                                name="qcm_question"
                                rows="2"
                                class="border-input bg-background flex w-full rounded-md border px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring" 
                            >{{ day.qcm_question }}</textarea>
                            <InputError :message="errorFor('qcm_question')" />
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div v-for="(option, index) in 3" :key="index" class="grid gap-2">
                                <Label :for="`qcm_options_${index}`">Choix {{ index + 1 }}</Label>
                                <Input
                                    :id="`qcm_options_${index}`"
                                    type="text"
                                    :name="`qcm_options[${index}]`"
                                    :default-value="day.qcm_options[index] ?? ''"
                                    maxlength="255"
                                />
                            </div>
                        </div>
                        <InputError :message="errorFor('qcm_options.0')" />

                        <div class="grid gap-2">
                            <Label for="qcm_correct_index">Bonne réponse</Label>
                            <select
                                id="qcm_correct_index"
                                name="qcm_correct_index"
                                class="border-input bg-background flex h-9 w-full rounded-md border px-3 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring"
                            >
                                <option
                                    v-for="(option, index) in 3"
                                    :key="index"
                                    :value="String(index)"
                                    :selected="day.qcm_correct_index === index"
                                >
                                    Choix {{ index + 1 }}
                                </option>
                            </select>
                            <InputError :message="errorFor('qcm_correct_index')" />
                        </div>
                    </template>

                    <template v-else>
                        <div class="grid gap-2">
                            <Label for="game_variant">Variante du mini-jeu</Label>
                            <select
                                id="game_variant"
                                name="game_variant"
                                class="border-input bg-background flex h-9 w-full rounded-md border px-3 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring"
                                @change="onGameVariantChange"
                            >
                                <option
                                    v-for="(label, variant) in gameVariantLabels"
                                    :key="variant"
                                    :value="variant"
                                    :selected="gameVariant === variant"
                                >
                                    {{ label }}
                                </option>
                            </select>
                            <InputError :message="errorFor('game_variant')" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="game_title">Titre du mini-jeu</Label>
                            <Input
                                id="game_title"
                                type="text"
                                name="game_title"
                                :default-value="day.game_title"
                                maxlength="120"
                                placeholder="Ex : Trouve les paires"
                            />
                            <InputError :message="errorFor('game_title')" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="game_instructions">Instructions</Label>
                            <textarea
                                id="game_instructions"
                                name="game_instructions"
                                rows="2"
                                class="border-input bg-background flex w-full rounded-md border px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring"
                            >{{ day.game_instructions }}</textarea>
                            <InputError :message="errorFor('game_instructions')" />
                        </div>

                        <div v-if="gameVariant === 'memory'" class="grid gap-2">
                            <Label for="game_symbols">Symboles des cartes (séparés par des virgules, 6 pour 12 cartes)</Label>
                            <Input
                                id="game_symbols"
                                type="text"
                                name="game_symbols"
                                :default-value="day.game_symbols"
                                maxlength="255"
                                placeholder="🎄, ⭐, 🎁, ❄️, 🔔, 🕯️"
                            />
                            <InputError :message="errorFor('game_symbols')" />
                        </div>

                        <p v-else class="rounded-lg bg-muted p-3 text-xs text-muted-foreground">
                            {{ gameVariant === 'simon'
                                ? 'Simon : 5 manches de séquence de couleurs à répéter. Aucun réglage nécessaire.'
                                : gameVariant === 'puzzle'
                                    ? 'Taquin : remettre 8 tuiles emoji en ordre. Aucun réglage nécessaire.'
                                    : 'Frappe-Taupe : frapper 10 taupes. Aucun réglage nécessaire.' }}
                        </p>
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
                            type="text"
                            name="gift_title"
                            :default-value="day.gift_title"
                            maxlength="120"
                        />
                        <InputError :message="errorFor('gift_title')" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="gift_message">Message</Label>
                        <textarea
                            id="gift_message"
                            name="gift_message"
                            rows="3"
                            class="border-input bg-background flex w-full rounded-md border px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring" 
                        >{{ day.gift_message }}</textarea>
                        <InputError :message="errorFor('gift_message')" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="gift_image">Image</Label>
                        <img
                            v-if="day.gift_image_url"
                            :src="day.gift_image_url"
                            alt="Image actuelle du cadeau"
                            class="w-32 rounded-lg border object-cover"
                        />
                        <Input
                            id="gift_image"
                            type="file"
                            name="gift_image"
                            accept="image/png,image/jpeg,image/webp"
                        />
                        <InputError :message="errorFor('gift_image')" />
                    </div>

                    <div v-if="day.gift_image_url" class="flex items-center gap-2">
                        <input
                            id="remove_gift_image"
                            type="checkbox"
                            name="remove_gift_image"
                            value="1"
                            class="size-4"
                        />
                        <Label for="remove_gift_image">Supprimer l'image actuelle</Label>
                    </div>

                    <div class="grid gap-2">
                        <Label for="gift_link_url">Lien (URL du cadeau en ligne)</Label>
                        <Input
                            id="gift_link_url"
                            type="url"
                            name="gift_link_url"
                            :default-value="day.gift_link_url"
                            maxlength="2048"
                            placeholder="https://..."
                        />
                        <InputError :message="errorFor('gift_link_url')" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="gift_link_label">Libellé du bouton du lien</Label>
                        <Input
                            id="gift_link_label"
                            type="text"
                            name="gift_link_label"
                            :default-value="day.gift_link_label"
                            maxlength="120"
                            placeholder="Ex : Utiliser mon bon d'achat"
                        />
                        <InputError :message="errorFor('gift_link_label')" />
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="processing">
                    {{ processing ? 'Enregistrement...' : 'Enregistrer' }}
                </Button>
                <Button variant="ghost" as-child>
                    <Link :href="calendarEditIndex()">Annuler</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
