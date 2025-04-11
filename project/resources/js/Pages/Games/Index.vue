<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, watch, ref, onMounted } from 'vue';
import { format } from 'date-fns';
import { PlayCircle } from 'lucide-vue-next';

// Import filters constants
import {
    FILTER_LEAGUE,
    FILTER_SEASON,
    FILTER_TEAM,
    FILTER_STATUS,
    STATUS_SCHEDULED,
    STATUS_IN_PROGRESS,
    STATUS_COMPLETED,
    STATUS_OPTIONS,
    applyFilters
} from '@/Constants/filters';

// Import shadcn-vue components
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import { Badge } from "@/Components/ui/badge";
import { Alert, AlertDescription, AlertTitle } from "@/Components/ui/alert";
import { Separator } from "@/Components/ui/separator";

// Fix pagination imports
import { 
    Pagination,
    PaginationList as PaginationContent,
    PaginationListItem as PaginationItem,
    PaginationNext,
    PaginationPrev as PaginationPrevious,
    PaginationEllipsis
} from "@/Components/ui/pagination";

// Toggle for debug info
const showDebug = ref(false);

// Helper to get human-readable filter names
const getFilterReadableName = (filterKey) => {
    switch (filterKey) {
        case FILTER_LEAGUE:
            return 'League';
        case FILTER_SEASON:
            return 'Season';
        case FILTER_TEAM:
            return 'Team';
        case FILTER_STATUS:
            return 'Status';
        default:
            return filterKey;
    }
};

const props = defineProps({
    games: Object,
    leagues: Array,
    seasons: Array,
    teams: Array,
    statusOptions: {
        type: Array,
        default: () => STATUS_OPTIONS
    },
    filters: Object,
});

const selectedLeague = ref(props.filters[FILTER_LEAGUE] || '');
const selectedSeason = ref(props.filters[FILTER_SEASON] || '');
const selectedTeam = ref(props.filters[FILTER_TEAM] || '');
const selectedStatus = ref(props.filters[FILTER_STATUS] || '');

// Set latest season as default if no filter applied
onMounted(() => {
    if (!props.filters[FILTER_SEASON] && uniqueSeasons.value.length > 0) {
        // Get the latest season by start_date
        const latestSeason = uniqueSeasons.value.reduce((latest, current) => {
            return new Date(current.start_date) > new Date(latest.start_date) ? current : latest;
        }, uniqueSeasons.value[0]);
        
        if (latestSeason) {
            selectedSeason.value = latestSeason.name;
        }
    }
});

// Apply filters when they change
watch([selectedLeague, selectedSeason, selectedTeam, selectedStatus], () => {
    const params = applyFilters({
        [FILTER_LEAGUE]: selectedLeague.value,
        [FILTER_SEASON]: selectedSeason.value,
        [FILTER_TEAM]: selectedTeam.value,
        [FILTER_STATUS]: selectedStatus.value
    });
    
    router.get(
        route('games.index'),
        params,
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
        case STATUS_SCHEDULED:
            return 'secondary';
        case STATUS_IN_PROGRESS:
            return 'warning';
        case STATUS_COMPLETED:
            return 'success';
        default:
            return 'outline';
    }
};

// Ensure unique leagues for dropdown, sorted alphabetically
const uniqueLeagues = computed(() => {
    const seen = new Set();
    const filtered = props.leagues.filter(league => {
        if (seen.has(league.name)) {
            return false;
        }
        seen.add(league.name);
        return true;
    });
    
    // Sort alphabetically by name
    return filtered.sort((a, b) => a.name.localeCompare(b.name));
});

// Ensure unique seasons for dropdown, sorted by start_date (most recent first)
const uniqueSeasons = computed(() => {
    const seen = new Set();
    const filtered = props.seasons.filter(season => {
        if (seen.has(season.name)) {
            return false;
        }
        seen.add(season.name);
        return true;
    });
    
    // Sort by start_date (newest first)
    return filtered.sort((a, b) => {
        const dateA = new Date(a.start_date || 0);
        const dateB = new Date(b.start_date || 0);
        return dateB - dateA;
    });
});

// Ensure unique teams for dropdown
const uniqueTeams = computed(() => {
    const seen = new Set();
    return props.teams.filter(team => {
        if (seen.has(team.id)) {
            return false;
        }
        seen.add(team.id);
        return true;
    });
});

// Count active filters
const activeFiltersCount = computed(() => {
    let count = 0;
    if (selectedLeague) count++;
    if (selectedSeason) count++;
    if (selectedTeam) count++;
    if (selectedStatus) count++;
    return count;
});

// Generate result text based on scores
const getResultText = (game) => {
    if (game.status !== STATUS_COMPLETED || game.home_score === null || game.away_score === null) {
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
        <!-- Page Header -->
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Games
            </h2>
        </template>

        <!-- Togglable Debug Information -->
        <div class="mb-6">
            <Button variant="outline" size="sm" @click="showDebug = !showDebug" class="mb-2">
                {{ showDebug ? 'Hide' : 'Show' }} Debug Info
            </Button>
            
            <div v-if="showDebug" class="bg-yellow-100 p-4 rounded-md shadow">
                <h3 class="font-bold mb-2">Debug Info:</h3>
                <p>Games loaded: {{ games && games.data ? 'Yes' : 'No' }}</p>
                <p>Number of games: {{ games && games.data ? games.data.length : 0 }}</p>
                <p>Filters: {{ JSON.stringify(filters) }}</p>
                <p>Leagues loaded: {{ leagues ? 'Yes (' + leagues.length + ')' : 'No' }}</p>
                <p>Unique Leagues: {{ uniqueLeagues.length }}</p>
                <p>Seasons loaded: {{ seasons ? 'Yes (' + seasons.length + ')' : 'No' }}</p>
                <p>Unique Seasons: {{ uniqueSeasons.length }}</p>
                <p>Teams loaded: {{ teams ? 'Yes (' + teams.length + ')' : 'No' }}</p>
                <p>Unique Teams: {{ uniqueTeams.length }}</p>
            </div>
        </div>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filters -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>
                            Filters 
                            <Badge v-if="activeFiltersCount > 0" variant="secondary" class="ml-2">
                                {{ activeFiltersCount }} active
                            </Badge>
                        </CardTitle>
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
                                        <SelectItem 
                                            v-for="league in uniqueLeagues" 
                                            :key="league.id" 
                                            :value="league.name"
                                        >
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
                                        <SelectItem 
                                            v-for="season in uniqueSeasons" 
                                            :key="season.id" 
                                            :value="season.name"
                                        >
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
                                        <SelectItem v-for="team in uniqueTeams" :key="team.id" :value="team.id">
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
                                        <SelectItem v-for="status in STATUS_OPTIONS" :key="status" :value="status">
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
                        
                        <!-- Active Filters Display -->
                        <div v-if="selectedLeague || selectedSeason || selectedTeam || selectedStatus" class="mt-4">
                            <h3 class="text-sm font-medium mb-2">Active Filters:</h3>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-if="selectedLeague" variant="secondary" class="flex items-center gap-1">
                                    <span>League: {{ selectedLeague }}</span>
                                    <button 
                                        @click="selectedLeague = ''" 
                                        class="text-xs rounded-full hover:bg-muted p-1"
                                        aria-label="Clear league filter"
                                    >
                                        ✕
                                    </button>
                                </Badge>
                                
                                <Badge v-if="selectedSeason" variant="secondary" class="flex items-center gap-1">
                                    <span>Season: {{ selectedSeason }}</span>
                                    <button 
                                        @click="selectedSeason = ''" 
                                        class="text-xs rounded-full hover:bg-muted p-1"
                                        aria-label="Clear season filter"
                                    >
                                        ✕
                                    </button>
                                </Badge>
                                
                                <Badge v-if="selectedTeam" variant="secondary" class="flex items-center gap-1">
                                    <span>Team: {{ uniqueTeams.find(t => t.id == selectedTeam)?.name }}</span>
                                    <button 
                                        @click="selectedTeam = ''" 
                                        class="text-xs rounded-full hover:bg-muted p-1"
                                        aria-label="Clear team filter"
                                    >
                                        ✕
                                    </button>
                                </Badge>
                                
                                <Badge v-if="selectedStatus" variant="secondary" class="flex items-center gap-1">
                                    <span>Status: {{ selectedStatus }}</span>
                                    <button 
                                        @click="selectedStatus = ''" 
                                        class="text-xs rounded-full hover:bg-muted p-1"
                                        aria-label="Clear status filter"
                                    >
                                        ✕
                                    </button>
                                </Badge>
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
                                    <TableHead class="w-[100px]">Video</TableHead>
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
                                    <TableCell>{{ game.season.name ? game.season.name.replace(' Season', '') : 'N/A' }}</TableCell>
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
                                    <TableCell> 
                                        <a v-if="game.video_url" :href="game.video_url" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-blue-600 hover:underline">
                                            <PlayCircle class="h-4 w-4" />
                                            Preview
                                        </a>
                                        <span v-else class="text-muted-foreground text-sm">N/A</span>
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
                                            <a 
                                                :href="link.url" 
                                                class="flex h-9 w-9 items-center justify-center rounded-md border border-input bg-background text-sm transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
                                                :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': link.active }"
                                            >
                                                {{ link.label }}
                                            </a>
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