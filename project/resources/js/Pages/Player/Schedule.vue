<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Alert, AlertDescription } from '@/components/ui/alert';

const props = defineProps({
    team: Object,
    upcomingGames: Array,
    pastGames: Array,
    seasons: Array,
    selectedSeasonId: String,
    currentWeek: String,
    error: String
});

const selectedSeason = ref(props.selectedSeasonId || '');
const activeTab = ref('upcoming');

// Function to handle season change
const handleSeasonChange = (seasonId) => {
    selectedSeason.value = seasonId;
    router.get(route('game.schedule'), { season_id: seasonId }, { preserveState: true });
};

// Group upcoming games by month for better readability
const upcomingGamesByMonth = computed(() => {
    if (!props.upcomingGames) return {};
    
    return props.upcomingGames.reduce((acc, game) => {
        const date = new Date(game.date);
        const monthYear = date.toLocaleString('default', { month: 'long', year: 'numeric' });
        
        if (!acc[monthYear]) {
            acc[monthYear] = [];
        }
        
        acc[monthYear].push(game);
        return acc;
    }, {});
});

// Group past games by month
const pastGamesByMonth = computed(() => {
    if (!props.pastGames) return {};
    
    return props.pastGames.reduce((acc, game) => {
        const date = new Date(game.date);
        const monthYear = date.toLocaleString('default', { month: 'long', year: 'numeric' });
        
        if (!acc[monthYear]) {
            acc[monthYear] = [];
        }
        
        acc[monthYear].push(game);
        return acc;
    }, {});
});
</script>

<template>
    <Head title="Game Schedule" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Game Schedule
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
                    <Card class="mb-6">
                        <CardHeader>
                            <CardTitle>{{ team.name }} Schedule</CardTitle>
                            <CardDescription>Game schedule for your team</CardDescription>
                        </CardHeader>
                    </Card>

                    <Tabs v-model="activeTab" class="w-full">
                        <TabsList class="grid w-full grid-cols-2">
                            <TabsTrigger value="upcoming">Upcoming Games</TabsTrigger>
                            <TabsTrigger value="past">Past Games</TabsTrigger>
                        </TabsList>
                        
                        <!-- Upcoming Games Tab -->
                        <TabsContent value="upcoming">
                            <div v-if="Object.keys(upcomingGamesByMonth).length === 0" class="bg-white rounded-md shadow p-6 text-center">
                                <p class="text-gray-500">No upcoming games scheduled.</p>
                            </div>
                            
                            <template v-else>
                                <div v-for="(games, month) in upcomingGamesByMonth" :key="month" class="mb-6">
                                    <h3 class="text-lg font-medium mb-4">{{ month }}</h3>
                                    <Card>
                                        <CardContent class="pt-6">
                                            <Table>
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
                                                    <TableRow v-for="game in games" :key="game.id">
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
                                        </CardContent>
                                    </Card>
                                </div>
                            </template>
                        </TabsContent>
                        
                        <!-- Past Games Tab -->
                        <TabsContent value="past">
                            <div v-if="Object.keys(pastGamesByMonth).length === 0" class="bg-white rounded-md shadow p-6 text-center">
                                <p class="text-gray-500">No past games found.</p>
                            </div>
                            
                            <template v-else>
                                <div v-for="(games, month) in pastGamesByMonth" :key="month" class="mb-6">
                                    <h3 class="text-lg font-medium mb-4">{{ month }}</h3>
                                    <Card>
                                        <CardContent class="pt-6">
                                            <Table>
                                                <TableHeader>
                                                    <TableRow>
                                                        <TableHead>Date</TableHead>
                                                        <TableHead>Opponent</TableHead>
                                                        <TableHead>Result</TableHead>
                                                        <TableHead>Location</TableHead>
                                                        <TableHead>Home/Away</TableHead>
                                                    </TableRow>
                                                </TableHeader>
                                                <TableBody>
                                                    <TableRow v-for="game in games" :key="game.id">
                                                        <TableCell>{{ new Date(game.date).toLocaleDateString() }}</TableCell>
                                                        <TableCell>{{ game.opponent }}</TableCell>
                                                        <TableCell>
                                                            <Badge 
                                                                v-if="game.result" 
                                                                :variant="game.result.startsWith('W') ? 'default' : 'destructive'"
                                                            >
                                                                {{ game.result }}
                                                            </Badge>
                                                            <span v-else>Pending</span>
                                                        </TableCell>
                                                        <TableCell>{{ game.location }}</TableCell>
                                                        <TableCell>
                                                            <Badge :variant="game.isHome ? 'default' : 'outline'">
                                                                {{ game.isHome ? 'Home' : 'Away' }}
                                                            </Badge>
                                                        </TableCell>
                                                    </TableRow>
                                                </TableBody>
                                            </Table>
                                        </CardContent>
                                    </Card>
                                </div>
                            </template>
                        </TabsContent>
                    </Tabs>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 