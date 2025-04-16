<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Import shadcn-vue components
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
import { FormItem, FormLabel, FormControl, FormMessage } from "@/Components/ui/form";

const props = defineProps({
    team: Object,
    leagues: Array,
    seasons: Array,
    teams: Array,
    isHome: {
        type: Boolean,
        default: true
    }
});

const form = useForm({
    league_id: props.leagues && props.leagues.length > 0 ? props.leagues[0].id : '',
    season_id: props.seasons && props.seasons.length > 0 ? props.seasons[0].id : '',
    opponent_team_id: '',
    is_home: props.isHome,
    game_date_time: '',
    location: '',
    video_url: '',
});

const formTitle = computed(() => {
    return "Add New Game";
});
</script>

<template>
    <Head title="Create Game" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ formTitle }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Game Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="form.post(route('coach.games.store'))">
                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <FormItem>
                                        <FormLabel for="league_id">League</FormLabel>
                                        <Select v-model="form.league_id" required>
                                            <FormControl>
                                                <SelectTrigger id="league_id">
                                                    <SelectValue placeholder="Select league" />
                                                </SelectTrigger>
                                            </FormControl>
                                            <SelectContent>
                                                <SelectItem v-for="league in leagues" :key="league.id" :value="league.id">
                                                    {{ league.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <FormMessage>{{ form.errors.league_id }}</FormMessage>
                                    </FormItem>
                                </div>

                                <div class="sm:col-span-3">
                                    <FormItem>
                                        <FormLabel for="season_id">Season</FormLabel>
                                        <Select v-model="form.season_id" required>
                                            <FormControl>
                                                <SelectTrigger id="season_id">
                                                    <SelectValue placeholder="Select season" />
                                                </SelectTrigger>
                                            </FormControl>
                                            <SelectContent>
                                                <SelectItem v-for="season in seasons" :key="season.id" :value="season.id">
                                                    {{ season.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <FormMessage>{{ form.errors.season_id }}</FormMessage>
                                    </FormItem>
                                </div>

                                <div class="sm:col-span-3">
                                    <FormItem>
                                        <FormLabel for="opponent_team_id">Opponent Team</FormLabel>
                                        <Select v-model="form.opponent_team_id" required>
                                            <FormControl>
                                                <SelectTrigger id="opponent_team_id">
                                                    <SelectValue placeholder="Select opponent" />
                                                </SelectTrigger>
                                            </FormControl>
                                            <SelectContent>
                                                <SelectItem v-for="opponent in teams" :key="opponent.id" :value="opponent.id">
                                                    {{ opponent.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <FormMessage>{{ form.errors.opponent_team_id }}</FormMessage>
                                    </FormItem>
                                </div>

                                <div class="sm:col-span-3">
                                    <FormItem>
                                        <FormLabel>Game Location</FormLabel>
                                        <RadioGroup v-model="form.is_home" class="mt-2">
                                            <div class="flex items-center space-x-2">
                                                <RadioGroupItem id="is_home_true" :value="true" />
                                                <Label for="is_home_true">Home Game</Label>
                                            </div>
                                            <div class="flex items-center space-x-2 mt-2">
                                                <RadioGroupItem id="is_home_false" :value="false" />
                                                <Label for="is_home_false">Away Game</Label>
                                            </div>
                                        </RadioGroup>
                                        <FormMessage>{{ form.errors.is_home }}</FormMessage>
                                    </FormItem>
                                </div>

                                <div class="sm:col-span-3">
                                    <FormItem>
                                        <FormLabel for="game_date_time">Game Date and Time</FormLabel>
                                        <Input
                                            id="game_date_time"
                                            type="datetime-local"
                                            v-model="form.game_date_time"
                                            required
                                        />
                                        <FormMessage>{{ form.errors.game_date_time }}</FormMessage>
                                    </FormItem>
                                </div>

                                <div class="sm:col-span-3">
                                    <FormItem>
                                        <FormLabel for="location">Location (Optional)</FormLabel>
                                        <Input
                                            id="location"
                                            type="text"
                                            v-model="form.location"
                                            placeholder="e.g. City Arena, Rink 2"
                                        />
                                        <FormMessage>{{ form.errors.location }}</FormMessage>
                                    </FormItem>
                                </div>

                                <div class="sm:col-span-6">
                                    <FormItem>
                                        <FormLabel for="video_url">Video URL (Optional)</FormLabel>
                                        <Input
                                            id="video_url"
                                            type="url"
                                            v-model="form.video_url"
                                            placeholder="e.g. https://www.youtube.com/watch?v=..."
                                        />
                                        <p class="mt-1 text-sm text-muted-foreground">
                                            You can add this later if the video isn't available yet.
                                        </p>
                                        <FormMessage>{{ form.errors.video_url }}</FormMessage>
                                    </FormItem>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-3">
                                <Link :href="route('coach.games')">
                                    <Button variant="outline" type="button">Cancel</Button>
                                </Link>
                                <Button type="submit" :disabled="form.processing">Create Game</Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 