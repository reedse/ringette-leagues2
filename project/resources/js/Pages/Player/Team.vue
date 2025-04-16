<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Badge } from '@/Components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Alert, AlertDescription } from '@/Components/ui/alert';

const props = defineProps({
    team: Object,
    roster: Array,
    upcomingGames: Array,
    seasons: Array,
    selectedSeasonId: String,
    error: String
});

const selectedSeason = ref(props.selectedSeasonId || '');
const activeTab = ref('roster');

// Function to handle season change
const handleSeasonChange = (seasonId) => {
    selectedSeason.value = seasonId;
    router.get(route('player.team'), { season_id: seasonId }, { preserveState: true });
};
</script>

<template>
    <Head title="My Team" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    My Team
                </h2>
                <Select v-if="seasons?.length > 0" v-model="selectedSeason" @update:modelValue="handleSeasonChange">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Select Season" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="season in seasons" :key="season.id" :value="season.id">
                            {{ season.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Error message if no player profile or team -->
                <Alert v-if="error" variant="destructive" class="mb-6">
                    <AlertDescription>
                        {{ error }}
                    </AlertDescription>
                </Alert>

                <div v-if="!error && team">
                    <!-- Team Info Card -->
                    <Card class="mb-6">
                        <CardHeader>
                            <CardTitle>{{ team.name }}</CardTitle>
                            <CardDescription>
                                {{ team.association }} - {{ team.league }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <h3 class="text-lg font-medium">Team Information</h3>
                                    <dl class="mt-2 space-y-2">
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500">Season:</dt>
                                            <dd class="text-sm text-gray-900">{{ team.season }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500">Coach:</dt>
                                            <dd class="text-sm text-gray-900">{{ team.coach ? team.coach.name : 'Not assigned' }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500">Contact:</dt>
                                            <dd class="text-sm text-gray-900">{{ team.coach ? team.coach.email : 'N/A' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium">Season Record</h3>
                                    <div class="mt-2 grid grid-cols-4 gap-2 text-center">
                                        <div class="rounded-md bg-primary-50 p-2">
                                            <div class="text-lg font-bold text-primary-800">{{ team.record }}</div>
                                            <div class="text-xs text-primary-600">Record</div>
                                        </div>
                                        <div class="rounded-md bg-green-50 p-2">
                                            <div class="text-lg font-bold text-green-800">{{ team.stats.wins }}</div>
                                            <div class="text-xs text-green-600">Wins</div>
                                        </div>
                                        <div class="rounded-md bg-red-50 p-2">
                                            <div class="text-lg font-bold text-red-800">{{ team.stats.losses }}</div>
                                            <div class="text-xs text-red-600">Losses</div>
                                        </div>
                                        <div class="rounded-md bg-blue-50 p-2">
                                            <div class="text-lg font-bold text-blue-800">{{ team.stats.ties }}</div>
                                            <div class="text-xs text-blue-600">Ties</div>
                                        </div>
                                    </div>
                                    <div class="mt-4 grid grid-cols-2 gap-2 text-center">
                                        <div class="rounded-md bg-emerald-50 p-2">
                                            <div class="text-lg font-bold text-emerald-800">{{ team.stats.goalsFor }}</div>
                                            <div class="text-xs text-emerald-600">Goals For</div>
                                        </div>
                                        <div class="rounded-md bg-amber-50 p-2">
                                            <div class="text-lg font-bold text-amber-800">{{ team.stats.goalsAgainst }}</div>
                                            <div class="text-xs text-amber-600">Goals Against</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Team Tabs -->
                    <Tabs v-model="activeTab" class="w-full">
                        <TabsList class="grid w-full grid-cols-2">
                            <TabsTrigger value="roster">Team Roster</TabsTrigger>
                            <TabsTrigger value="schedule">Upcoming Games</TabsTrigger>
                        </TabsList>
                        <TabsContent value="roster">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Team Roster</CardTitle>
                                    <CardDescription>Players on your team for the {{ team.season }} season</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <Table>
                                        <TableCaption>Team roster for the {{ team.season }} season.</TableCaption>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Jersey#</TableHead>
                                                <TableHead>Name</TableHead>
                                                <TableHead>Position</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="player in roster" :key="player.id">
                                                <TableCell>{{ player.jerseyNumber }}</TableCell>
                                                <TableCell>{{ player.name }}</TableCell>
                                                <TableCell>{{ player.position }}</TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </CardContent>
                            </Card>
                        </TabsContent>
                        <TabsContent value="schedule">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Upcoming Games</CardTitle>
                                    <CardDescription>Next games for your team</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <Table v-if="upcomingGames && upcomingGames.length > 0">
                                        <TableCaption>Upcoming games for the {{ team.season }} season.</TableCaption>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Time</TableHead>
                                                <TableHead>Opponent</TableHead>
                                                <TableHead>Location</TableHead>
                                                <TableHead>Home/Away</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="game in upcomingGames" :key="game.id">
                                                <TableCell>{{ new Date(game.date).toLocaleDateString() }}</TableCell>
                                                <TableCell>{{ game.time ?? 'TBD' }}</TableCell>
                                                <TableCell>{{ game.opponent }}</TableCell>
                                                <TableCell>{{ game.location }}</TableCell>
                                                <TableCell>
                                                    <Badge :variant="game.isHome ? 'default' : 'outline'">
                                                        {{ game.isHome ? 'Home' : 'Away' }}
                                                    </Badge>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                    <div v-else class="text-center py-6 text-gray-500">
                                        No upcoming games scheduled.
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>
                    </Tabs>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 