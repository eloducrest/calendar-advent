<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { store } from '@/actions/App/Http/Controllers/PlayerController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as calendarIndex } from '@/routes/calendar';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Calendrier de l\'Avent',
                href: calendarIndex(),
            },
            {
                title: 'Joueurs',
                href: '/players',
            },
        ] satisfies BreadcrumbItem[],
    },
});

const { players } = defineProps<{
    players: {
        id: number;
        name: string;
        email: string;
        solved_count: number;
        created_at: string | null;
    }[];
}>();
</script>

<template>
    <Head title="Joueurs" />

    <div class="mx-auto w-full max-w-2xl px-4 py-6">
        <h1 class="text-xl font-semibold">Joueurs</h1>
        <p class="mt-1 text-sm text-muted-foreground">
            Les comptes qui peuvent jouer au calendrier. L'inscription publique est fermée : créez les comptes ici.
        </p>

        <Form
            v-bind="store.form()"
            v-slot="{ errors, processing }"
            class="mt-6 space-y-4"
        >
            <Card>
                <CardContent class="space-y-4">
                    <h2 class="font-semibold">Ajouter un joueur</h2>

                    <div class="grid gap-2">
                        <Label for="name">Prénom / pseudo</Label>
                        <Input
                            id="name"
                            type="text"
                            name="name"
                            required
                            maxlength="255"
                            placeholder="Ex : Camille"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">E-mail</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            maxlength="255"
                            placeholder="joueur@exemple.com"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="password">Mot de passe</Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                autocomplete="new-password"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation">Confirmation</Label>
                            <PasswordInput
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                            />
                            <InputError :message="errors.password_confirmation" />
                        </div>
                    </div>

                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Création...' : 'Créer le joueur' }}
                    </Button>
                </CardContent>
            </Card>
        </Form>

        <Card class="mt-6">
            <CardContent class="space-y-3">
                <h2 class="font-semibold">
                    Comptes joueurs
                    <Badge variant="secondary" class="ml-1">{{ players.length }}</Badge>
                </h2>

                <p v-if="players.length === 0" class="text-sm text-muted-foreground">
                    Aucun joueur pour le moment.
                </p>

                <div
                    v-for="player in players"
                    :key="player.id"
                    class="flex items-center justify-between rounded-lg bg-muted/50 px-3 py-2"
                >
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium">{{ player.name }}</p>
                        <p class="truncate text-xs text-muted-foreground">{{ player.email }}</p>
                    </div>
                    <Badge variant="secondary" class="shrink-0">
                        {{ player.solved_count }} 🎁
                    </Badge>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
