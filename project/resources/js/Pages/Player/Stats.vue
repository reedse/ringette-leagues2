<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { Badge } from '@/Components/ui/badge';
import { Progress } from '@/Components/ui/progress';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Alert, AlertDescription } from '@/Components/ui/alert';

const props = defineProps({
    playerStats: Object,
    error: String
});

const selectedSeason = ref(props.playerStats?.selectedSeasonId || '');
const activeTab = ref('summary');

// Calculate stats for progress bars
const maxGoals = computed(() => {
    if (!props.playerStats || !props.playerStats.gameStats || props.playerStats.gameStats.length === 0) return 1;
    return Math.max(...props.playerStats.gameStats.map(game => game.goals));
});

const goalPercentages = computed(() => {
    if (!props.playerStats || !props.playerStats.gameStats) return [];
    
    return props.playerStats.gameStats.map(game => ({
        ...game,
        goalPercentage: Math.round((game.goals / maxGoals.value) * 100) || 0
    }));
});

// Function to handle season change
const handleSeasonChange = (seasonId) => {
    selectedSeason.value = seasonId;
    router.get(route('player.stats'), { season_id: seasonId }, { preserveState: true });
};
</script>

<template>
    <Head title="Player Stats" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    My Stats
                </h2>
                <Select v-if="playerStats?.seasons?.length > 0" v-model="selectedSeason" @update:modelValue="handleSeasonChange">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Select Season" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="season in playerStats.seasons" :key="season.id" :value="season.id">
                            {{ season.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Error message if no player profile -->
                <Alert v-if="error" variant="destructive" class="mb-6">
                    <AlertDescription>
                        {{ error }}
                    </AlertDescription>
                </Alert>

                <div v-if="!error && playerStats">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ playerStats.summary.name }} <Badge class="ml-2">#{{ playerStats.summary.jerseyNumber }}</Badge></CardTitle>
                            <CardDescription>{{ playerStats.summary.position }}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div class="rounded-lg bg-primary-50 p-4 text-center">
                                    <div class="text-2xl font-bold text-primary-800">{{ playerStats.summary.gamesPlayed }}</div>
                                    <div class="text-sm text-primary-600">Games Played</div>
                                </div>
                                <div class="rounded-lg bg-green-50 p-4 text-center">
                                    <div class="text-2xl font-bold text-green-800">{{ playerStats.summary.goals }}</div>
                                    <div class="text-sm text-green-600">Goals</div>
                                </div>
                                <div class="rounded-lg bg-blue-50 p-4 text-center">
                                    <div class="text-2xl font-bold text-blue-800">{{ playerStats.summary.assists }}</div>
                                    <div class="text-sm text-blue-600">Assists</div>
                                </div>
                                <div class="rounded-lg bg-purple-50 p-4 text-center">
                                    <div class="text-2xl font-bold text-purple-800">{{ playerStats.summary.totalPoints }}</div>
                                    <div class="text-sm text-purple-600">Total Points</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Tabs v-model="activeTab" class="w-full mt-6">
                        <TabsList class="grid w-full grid-cols-2">
                            <TabsTrigger value="summary">Performance Summary</TabsTrigger>
                            <TabsTrigger value="games">Game by Game</TabsTrigger>
                        </TabsList>
                        <TabsContent value="summary">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Season Performance</CardTitle>
                                    <CardDescription>Your performance during the selected season</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div class="space-y-4">
                                        <div v-if="playerStats.summary.gamesPlayed > 0">
                                            <div class="mb-1 flex items-center justify-between">
                                                <div class="text-sm font-medium">Goals per Game</div>
                                                <div class="text-sm text-gray-500">{{ (playerStats.summary.goals / playerStats.summary.gamesPlayed).toFixed(2) }}</div>
                                            </div>
                                            <Progress :value="(playerStats.summary.goals / playerStats.summary.gamesPlayed) * 50" class="h-2 w-full" />
                                        </div>
                                        <div v-if="playerStats.summary.gamesPlayed > 0">
                                            <div class="mb-1 flex items-center justify-between">
                                                <div class="text-sm font-medium">Assists per Game</div>
                                                <div class="text-sm text-gray-500">{{ (playerStats.summary.assists / playerStats.summary.gamesPlayed).toFixed(2) }}</div>
                                            </div>
                                            <Progress :value="(playerStats.summary.assists / playerStats.summary.gamesPlayed) * 50" class="h-2 w-full" />
                                        </div>
                                        <div v-if="playerStats.summary.gamesPlayed > 0">
                                            <div class="mb-1 flex items-center justify-between">
                                                <div class="text-sm font-medium">Points per Game</div>
                                                <div class="text-sm text-gray-500">{{ (playerStats.summary.totalPoints / playerStats.summary.gamesPlayed).toFixed(2) }}</div>
                                            </div>
                                            <Progress :value="(playerStats.summary.totalPoints / playerStats.summary.gamesPlayed) * 20" class="h-2 w-full" />
                                        </div>
                                        <div v-if="playerStats.summary.gamesPlayed === 0" class="text-center py-6 text-gray-500">
                                            No games played yet in this season.
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>
                        <TabsContent value="games">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Game Statistics</CardTitle>
                                    <CardDescription>Your performance in individual games</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <Table v-if="goalPercentages.length > 0">
                                        <TableCaption>Game statistics for the selected season.</TableCaption>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Opponent</TableHead>
                                                <TableHead>Result</TableHead>
                                                <TableHead>Goals</TableHead>
                                                <TableHead>Assists</TableHead>
                                                <TableHead>Points</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="game in goalPercentages" :key="game.id">
                                                <TableCell>{{ new Date(game.date).toLocaleDateString() }}</TableCell>
                                                <TableCell>{{ game.opponent }}</TableCell>
                                                <TableCell>
                                                    <Badge :variant="game.result.startsWith('W') ? 'default' : 'destructive'">
                                                        {{ game.result }}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell class="w-32">
                                                    <div class="flex items-center gap-2">
                                                        <span>{{ game.goals }}</span>
                                                        <Progress :value="game.goalPercentage" class="h-2 w-24" />
                                                    </div>
                                                </TableCell>
                                                <TableCell>{{ game.assists }}</TableCell>
                                                <TableCell>{{ game.goals + game.assists }}</TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                    <div v-else class="text-center py-6 text-gray-500">
                                        No game statistics available for this season.
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>
                    </Tabs>

                    <div class="flex justify-end mt-6">
                        <Button variant="outline">Download Stats</Button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 