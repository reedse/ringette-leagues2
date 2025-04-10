<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, watch, ref } from 'vue';
import { format } from 'date-fns';

// Import shadcn-vue components
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationLink, PaginationNext, PaginationPrevious } from "@/components/ui/pagination";
import { Badge } from "@/components/ui/badge";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Separator } from "@/components/ui/separator";

const props = defineProps({
    games: Object,
    leagues: Array,
    seasons: Array,
    teams: Array,
    statusOptions: Array,
    filters: Object,
});

const selectedLeague = ref(props.filters.league || '');
const selectedSeason = ref(props.filters.season || '');
const selectedTeam = ref(props.filters.team || '');
const selectedStatus = ref(props.filters.status || '');

// Apply filters when they change
watch([selectedLeague, selectedSeason, selectedTeam, selectedStatus], () => {
    router.get(
        route('games.index'),
        { 
            league: selectedLeague.value || null,
            season: selectedSeason.value || null,
            team: selectedTeam.value || null,
            status: selectedStatus.value || null
        },
        { 
            preserveState: true,
            replace: true 
        }
    );
});

// Clear all filters
const clearFilters = () => {
    selectedLeague.value = '';
    selectedSeason.value = '';
    selectedTeam.value = '';
    selectedStatus.value = '';
};

// Format date for display
const formatDate = (dateTime) => {
    if (!dateTime) return 'TBD';
    return format(new Date(dateTime), 'MMM d, yyyy • h:mm a');
};

// Get badge variant based on game status
const getStatusVariant = (status) => {
    switch (status) {
        case 'Scheduled':
            return 'secondary';
        case 'In Progress':
            return 'warning';
        case 'Completed':
            return 'success';
        default:
            return 'outline';
    }
};

// Generate result text based on scores
const getResultText = (game) => {
    if (game.status !== 'Completed' || game.home_score === null || game.away_score === null) {
        return null;
    }
    
    if (game.home_score > game.away_score) {
        return `${game.home_team.name} won`;
    } else if (game.away_score > game.home_score) {
        return `${game.away_team.name} won`;
    } else {
        return 'Tie game';
    }
};
</script>

<template>
    <Head title="Games" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Games
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filters -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Filters</CardTitle>
                        <CardDescription>Filter games by league, season, team, and status</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="league-filter">League</label>
                                <Select v-model="selectedLeague">
                                    <SelectTrigger id="league-filter">
                                        <SelectValue placeholder="All Leagues" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All Leagues</SelectItem>
                                        <SelectItem v-for="league in leagues" :key="league.id" :value="league.id">
                                            {{ league.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="season-filter">Season</label>
                                <Select v-model="selectedSeason">
                                    <SelectTrigger id="season-filter">
                                        <SelectValue placeholder="All Seasons" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All Seasons</SelectItem>
                                        <SelectItem v-for="season in seasons" :key="season.id" :value="season.id">
                                            {{ season.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="team-filter">Team</label>
                                <Select v-model="selectedTeam">
                                    <SelectTrigger id="team-filter">
                                        <SelectValue placeholder="All Teams" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All Teams</SelectItem>
                                        <SelectItem v-for="team in teams" :key="team.id" :value="team.id">
                                            {{ team.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="status-filter">Status</label>
                                <Select v-model="selectedStatus">
                                    <SelectTrigger id="status-filter">
                                        <SelectValue placeholder="All Statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All Statuses</SelectItem>
                                        <SelectItem v-for="status in statusOptions" :key="status" :value="status">
                                            {{ status }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            
                            <div class="flex items-end sm:col-span-4">
                                <Button variant="outline" @click="clearFilters" class="h-10">
                                    Clear Filters
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- No Results Alert -->
                <Alert v-if="games.data.length === 0" class="mb-6">
                    <AlertTitle>No games found</AlertTitle>
                    <AlertDescription>
                        No games match your current filters. Try changing your filter criteria or clear all filters.
                    </AlertDescription>
                </Alert>

                <!-- Games Table -->
                <Card v-if="games.data.length > 0">
                    <CardHeader>
                        <CardTitle>Games</CardTitle>
                        <CardDescription>
                            Showing {{ games.data.length }} of {{ games.total }} games
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Date & Time</TableHead>
                                    <TableHead>Teams</TableHead>
                                    <TableHead>League</TableHead>
                                    <TableHead>Season</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Score</TableHead>
                                    <TableHead class="w-[100px]">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="game in games.data" :key="game.id">
                                    <TableCell class="font-medium">
                                        {{ formatDate(game.game_date_time) }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span>{{ game.home_team.name }} (Home)</span>
                                            <span>vs.</span>
                                            <span>{{ game.away_team.name }} (Away)</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>{{ game.league.name }}</TableCell>
                                    <TableCell>{{ game.season.name }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(game.status)">
                                            {{ game.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div v-if="game.home_score !== null && game.away_score !== null" class="flex flex-col">
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium">{{ game.home_team.name }}:</span>
                                                <span>{{ game.home_score }}</span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium">{{ game.away_team.name }}:</span>
                                                <span>{{ game.away_score }}</span>
                                            </div>
                                            <Badge v-if="getResultText(game)" variant="outline" class="mt-1">
                                                {{ getResultText(game) }}
                                            </Badge>
                                        </div>
                                        <span v-else class="text-muted-foreground">Not available</span>
                                    </TableCell>
                                    <TableCell>
                                        <Link :href="route('games.show', game.id)">
                                            <Button variant="default" size="sm">View</Button>
                                        </Link>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                    <CardFooter>
                        <!-- Pagination -->
                        <div class="mt-4 flex w-full justify-center">
                            <Pagination>
                                <PaginationContent>
                                    <PaginationItem v-if="games.prev_page_url">
                                        <PaginationPrevious :href="games.prev_page_url" />
                                    </PaginationItem>
                                    
                                    <template v-for="(link, i) in games.links" :key="i">
                                        <PaginationItem v-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next')">
                                            <PaginationLink :href="link.url" :isActive="link.active">
                                                {{ link.label }}
                                            </PaginationLink>
                                        </PaginationItem>
                                        <PaginationItem v-else-if="link.label === '...'" class="cursor-default">
                                            <PaginationEllipsis />
                                        </PaginationItem>
                                    </template>
                                    
                                    <PaginationItem v-if="games.next_page_url">
                                        <PaginationNext :href="games.next_page_url" />
                                    </PaginationItem>
                                </PaginationContent>
                            </Pagination>
                        </div>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 