<script setup lang="ts">
import { ref } from 'vue';
import { Form } from '@inertiajs/vue3';
import { solve } from '@/actions/App/Http/Controllers/Calendar/AdventCalendarController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { CalendarDayItem } from '@/types';
import GiftReveal from './GiftReveal.vue';
import MemoryGame from './MemoryGame.vue';

const props = defineProps<{
    day: CalendarDayItem;
}>();

const emit = defineEmits<{
    close: [];
}>();

const selectedOption = ref<number | null>(null);
</script>

<template>
    <Dialog :open="true" @update:open="(open: boolean) => { if (!open) emit('close'); }">
        <DialogContent class="max-h-[85dvh] overflow-y-auto sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <span class="text-2xl">🗓️</span>
                    <span>Case n°{{ day.day }}</span>
                </DialogTitle>
                <DialogDescription v-if="day.status !== 'solved'">
                    <template v-if="day.challenge_type === 'game'">
                        {{ day.game_instructions || 'Résous le mini-jeu pour ouvrir ton cadeau !' }}
                    </template>
                    <template v-else>
                        Résous le défi pour ouvrir ton cadeau !
                    </template>
                </DialogDescription>
            </DialogHeader>

            <GiftReveal v-if="day.status === 'solved' && day.gift" :gift="day.gift" :day="day.day" />

            <p v-else-if="day.status === 'solved'" class="rounded-lg bg-muted p-4 text-center text-sm text-muted-foreground">
                🎁 Ce cadeau n'a pas encore été préparé.
            </p>

            <Form
                v-else
                v-bind="solve.form({ day: day.id })"
                class="space-y-4"
                v-slot="{ errors, processing, submit }"
            >
                <!-- Devinette -->
                <template v-if="day.challenge_type === 'riddle'">
                    <div class="grid gap-2">
                        <Label>{{ day.challenge_question }}</Label>
                        <Input
                            name="answer"
                            type="text"
                            required
                            maxlength="255"
                            placeholder="Ta réponse..."
                            autocomplete="off"
                        />
                        <InputError :message="errors.answer || errors.challenge" />
                    </div>
                </template>

                <!-- QCM -->
                <template v-else-if="day.challenge_type === 'qcm'">
                    <div class="grid gap-2">
                        <Label>{{ day.challenge_question }}</Label>
                        <input type="hidden" name="option" :value="selectedOption ?? ''" />
                        <div class="grid gap-2">
                            <Button
                                v-for="(option, index) in day.qcm_options ?? []"
                                :key="index"
                                type="button"
                                :variant="selectedOption === index ? 'default' : 'outline'"
                                class="justify-start"
                                @click="selectedOption = index"
                            >
                                {{ option }}
                            </Button>
                        </div>
                        <InputError :message="errors.option || errors.challenge" />
                    </div>
                </template>

                <!-- Mini-jeu -->
                <template v-else>
                    <p class="text-center text-sm font-medium">{{ day.game_title }}</p>
                    <input type="hidden" name="completed" value="1" />
                    <MemoryGame :symbols="day.game_symbols ?? []" @solved="submit()" />
                    <InputError :message="errors.completed || errors.challenge" />
                </template>

                <Button
                    v-if="day.challenge_type !== 'game'"
                    type="submit"
                    class="w-full"
                    :disabled="processing || (day.challenge_type === 'qcm' && selectedOption === null)"
                >
                    {{ processing ? 'Vérification...' : 'Ouvrir le cadeau 🎁' }}
                </Button>
            </Form>
        </DialogContent>
    </Dialog>
</template>
