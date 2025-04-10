<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Import shadcn-vue components
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { FormItem, FormLabel, FormControl, FormMessage } from "@/components/ui/form";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Separator } from "@/components/ui/separator";

const props = defineProps({
    game: Object,
    team: Object,
    isHome: Boolean,
    roster: Array,
    playerStats: Array,
    penalties: Array,
    penaltyCodes: Array,
});

const activeTab = ref('details');

// Game details form
const gameForm = useForm({
    location: props.game.location || '',
    video_url: props.game.video_url || '',
    status: props.game.status || 'Scheduled',
    home_score: props.game.home_score !== null ? props.game.home_score : '',
    away_score: props.game.away_score !== null ? props.game.away_score : '',
});

// Build player stats form data from existing stats
const playerStatsForm = useForm({
    stats: props.roster.map(player => {
        const existingStat = props.playerStats.find(stat => stat.player_id === player.id);
        return {
            player_id: player.id,
            goals: existingStat ? existingStat.goals : 0,
            assists: existingStat ? existingStat.assists : 0,
            plus_minus: existingStat ? existingStat.plus_minus : 0,
        };
    }),
});

// Build penalties form data from existing penalties
const penaltiesForm = useForm({
    penalties: [...props.penalties.map(penalty => ({
        id: penalty.id,
        player_id: penalty.player_id,
        penalty_code_id: penalty.penalty_code_id,
        period: penalty.period,
        time: penalty.time,
        delete: false,
    }))],
});

// Function to add a new penalty to the form
const addPenalty = () => {
    penaltiesForm.penalties.push({
        player_id: '',
        penalty_code_id: '',
        period: 1,
        time: '',
        delete: false,
    });
};

// Function to mark a penalty for deletion
const markPenaltyForDeletion = (index) => {
    if (penaltiesForm.penalties[index].id) {
        penaltiesForm.penalties[index].delete = true;
    } else {
        penaltiesForm.penalties.splice(index, 1);
    }
};

// Game information
const gameDate = computed(() => {
    if (!props.game.game_date_time) return '';
    const date = new Date(props.game.game_date_time);
    return date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
});

const gameTime = computed(() => {
    if (!props.game.game_date_time) return '';
    const date = new Date(props.game.game_date_time);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
});

const submitGameDetails = () => {
    gameForm.put(route('coach.games.update', props.game.id), {
        preserveScroll: true,
    });
};

const submitPlayerStats = () => {
    playerStatsForm.put(route('coach.games.update-player-stats', props.game.id), {
        preserveScroll: true,
    });
};

const submitPenalties = () => {
    // Only submit non-deleted penalties
    const filteredPenalties = penaltiesForm.penalties.filter(p => !p.delete || p.id);
    penaltiesForm.penalties = filteredPenalties;
    
    penaltiesForm.put(route('coach.games.update-penalties', props.game.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Filter out deleted penalties from the form after successful submission
            penaltiesForm.penalties = penaltiesForm.penalties.filter(p => !p.delete);
        }
    });
};

const getTeamName = (isHomeTeam) => {
    return isHomeTeam ? props.game.homeTeam.name : props.game.awayTeam.name;
};

const playerById = (id) => {
    return props.roster.find(p => p.id === id);
};

const penaltyCodeById = (id) => {
    return props.penaltyCodes.find(p => p.id === id);
};
</script>

<template>
    <Head :title="`Edit Game - ${game.homeTeam.name} vs ${game.awayTeam.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit Game
                </h2>
                <Link :href="route('coach.games')">
                    <Button variant="outline">Back to Games</Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ game.homeTeam.name }} vs {{ game.awayTeam.name }}</CardTitle>
                        <CardDescription>{{ gameDate }} at {{ gameTime }}</CardDescription>
                    </CardHeader>
                    
                    <CardContent class="p-0">
                        <Tabs :defaultValue="activeTab" @update:value="activeTab = $event" class="w-full">
                            <TabsList class="grid w-full grid-cols-3">
                                <TabsTrigger value="details">Game Details</TabsTrigger>
                                <TabsTrigger value="stats">Player Statistics</TabsTrigger>
                                <TabsTrigger value="penalties">Penalties</TabsTrigger>
                            </TabsList>
                            
                            <!-- Game Details Tab -->
                            <TabsContent value="details" class="p-4 pt-6">
                                <form @submit.prevent="submitGameDetails">
                                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                        <div class="sm:col-span-3">
                                            <FormItem>
                                                <FormLabel for="status">Game Status</FormLabel>
                                                <Select v-model="gameForm.status">
                                                    <FormControl>
                                                        <SelectTrigger id="status">
                                                            <SelectValue placeholder="Select status" />
                                                        </SelectTrigger>
                                                    </FormControl>
                                                    <SelectContent>
                                                        <SelectItem value="Scheduled">Scheduled</SelectItem>
                                                        <SelectItem value="In Progress">In Progress</SelectItem>
                                                        <SelectItem value="Completed">Completed</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                                <FormMessage>{{ gameForm.errors.status }}</FormMessage>
                                            </FormItem>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <FormItem>
                                                <FormLabel for="location">Location (Optional)</FormLabel>
                                                <Input
                                                    id="location"
                                                    type="text"
                                                    v-model="gameForm.location"
                                                    placeholder="e.g. City Arena, Rink 2"
                                                />
                                                <FormMessage>{{ gameForm.errors.location }}</FormMessage>
                                            </FormItem>
                                        </div>

                                        <div class="sm:col-span-6">
                                            <FormItem>
                                                <FormLabel for="video_url">Video URL (Optional)</FormLabel>
                                                <Input
                                                    id="video_url"
                                                    type="url"
                                                    v-model="gameForm.video_url"
                                                    placeholder="e.g. https://www.youtube.com/watch?v=..."
                                                />
                                                <FormMessage>{{ gameForm.errors.video_url }}</FormMessage>
                                            </FormItem>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <FormItem>
                                                <FormLabel :for="isHome ? 'home_score' : 'away_score'">{{ team.name }} Score</FormLabel>
                                                <Input
                                                    :id="isHome ? 'home_score' : 'away_score'"
                                                    type="number"
                                                    min="0"
                                                    v-model="isHome ? gameForm.home_score : gameForm.away_score"
                                                />
                                                <FormMessage>{{ isHome ? gameForm.errors.home_score : gameForm.errors.away_score }}</FormMessage>
                                            </FormItem>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <FormItem>
                                                <FormLabel :for="isHome ? 'away_score' : 'home_score'">{{ isHome ? game.awayTeam.name : game.homeTeam.name }} Score</FormLabel>
                                                <Input
                                                    :id="isHome ? 'away_score' : 'home_score'"
                                                    type="number"
                                                    min="0"
                                                    v-model="isHome ? gameForm.away_score : gameForm.home_score"
                                                />
                                                <FormMessage>{{ isHome ? gameForm.errors.away_score : gameForm.errors.home_score }}</FormMessage>
                                            </FormItem>
                                        </div>
                                    </div>

                                    <div class="mt-6 flex justify-end">
                                        <Button type="submit" :disabled="gameForm.processing">
                                            Save Details
                                        </Button>
                                    </div>
                                </form>
                            </TabsContent>

                            <!-- Player Stats Tab -->
                            <TabsContent value="stats" class="p-4 pt-6">
                                <form @submit.prevent="submitPlayerStats">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Player</TableHead>
                                                <TableHead>Goals</TableHead>
                                                <TableHead>Assists</TableHead>
                                                <TableHead>+/-</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="(stat, index) in playerStatsForm.stats" :key="stat.player_id">
                                                <TableCell>
                                                    {{ playerById(stat.player_id)?.first_name }} {{ playerById(stat.player_id)?.last_name }}
                                                    <span class="text-xs text-muted-foreground">(#{{ playerById(stat.player_id)?.jersey_number }})</span>
                                                </TableCell>
                                                <TableCell>
                                                    <Input
                                                        type="number"
                                                        min="0"
                                                        v-model="playerStatsForm.stats[index].goals"
                                                        class="w-16"
                                                    />
                                                </TableCell>
                                                <TableCell>
                                                    <Input
                                                        type="number"
                                                        min="0"
                                                        v-model="playerStatsForm.stats[index].assists"
                                                        class="w-16"
                                                    />
                                                </TableCell>
                                                <TableCell>
                                                    <Input
                                                        type="number"
                                                        v-model="playerStatsForm.stats[index].plus_minus"
                                                        class="w-16"
                                                    />
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>

                                    <div class="mt-6 flex justify-end">
                                        <Button type="submit" :disabled="playerStatsForm.processing">
                                            Save Player Stats
                                        </Button>
                                    </div>
                                </form>
                            </TabsContent>

                            <!-- Penalties Tab -->
                            <TabsContent value="penalties" class="p-4 pt-6">
                                <form @submit.prevent="submitPenalties">
                                    <div v-for="(penalty, index) in penaltiesForm.penalties" 
                                         :key="index" 
                                         v-show="!penalty.delete"
                                         class="mb-6 p-4 border border-border rounded-md">
                                        <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-12">
                                            <div class="sm:col-span-5">
                                                <FormItem>
                                                    <FormLabel>Player</FormLabel>
                                                    <Select v-model="penalty.player_id">
                                                        <FormControl>
                                                            <SelectTrigger>
                                                                <SelectValue placeholder="Select player" />
                                                            </SelectTrigger>
                                                        </FormControl>
                                                        <SelectContent>
                                                            <SelectItem v-for="player in roster" :key="player.id" :value="player.id">
                                                                {{ player.first_name }} {{ player.last_name }} (#{{ player.jersey_number }})
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </FormItem>
                                            </div>

                                            <div class="sm:col-span-4">
                                                <FormItem>
                                                    <FormLabel>Penalty Type</FormLabel>
                                                    <Select v-model="penalty.penalty_code_id">
                                                        <FormControl>
                                                            <SelectTrigger>
                                                                <SelectValue placeholder="Select penalty" />
                                                            </SelectTrigger>
                                                        </FormControl>
                                                        <SelectContent>
                                                            <SelectItem v-for="code in penaltyCodes" :key="code.id" :value="code.id">
                                                                {{ code.code }} - {{ code.description }}
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </FormItem>
                                            </div>

                                            <div class="sm:col-span-1">
                                                <FormItem>
                                                    <FormLabel>Period</FormLabel>
                                                    <Select v-model="penalty.period">
                                                        <FormControl>
                                                            <SelectTrigger>
                                                                <SelectValue />
                                                            </SelectTrigger>
                                                        </FormControl>
                                                        <SelectContent>
                                                            <SelectItem :value="1">1</SelectItem>
                                                            <SelectItem :value="2">2</SelectItem>
                                                            <SelectItem :value="3">3</SelectItem>
                                                            <SelectItem :value="4">OT</SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </FormItem>
                                            </div>

                                            <div class="sm:col-span-1">
                                                <FormItem>
                                                    <FormLabel>Time</FormLabel>
                                                    <Input
                                                        type="text"
                                                        v-model="penalty.time"
                                                        placeholder="MM:SS"
                                                    />
                                                </FormItem>
                                            </div>

                                            <div class="sm:col-span-1 flex items-end justify-end">
                                                <Button 
                                                    type="button" 
                                                    variant="destructive" 
                                                    size="sm"
                                                    @click="markPenaltyForDeletion(index)">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                                </Button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-between mt-6">
                                        <Button type="button" variant="outline" @click="addPenalty">
                                            Add Penalty
                                        </Button>
                                        <Button type="submit" :disabled="penaltiesForm.processing">
                                            Save Penalties
                                        </Button>
                                    </div>
                                </form>
                            </TabsContent>
                        </Tabs>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 