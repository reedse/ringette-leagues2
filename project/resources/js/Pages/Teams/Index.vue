<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, watch, ref, onMounted } from 'vue';

// Import filters constants
import {
    FILTER_LEAGUE,
    FILTER_SEASON,
    applyFilters
} from '@/Constants/filters';

// Import shadcn-vue components
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { Badge } from "@/Components/ui/badge";
import { Alert, AlertDescription, AlertTitle } from "@/Components/ui/alert";
import { Separator } from "@/Components/ui/separator";
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";

// Fix pagination imports
import { 
    Pagination,
    PaginationList as PaginationContent,
    PaginationListItem as PaginationItem,
    PaginationNext,
    PaginationPrev as PaginationPrevious,
    PaginationEllipsis
} from "@/Components/ui/pagination";

const props = defineProps({
    teams: Object,
    leagues: Array,
    seasons: Array,
    filters: Object,
});

const selectedLeague = ref(props.filters[FILTER_LEAGUE] || '');
const selectedSeason = ref(props.filters[FILTER_SEASON] || '');

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
watch([selectedLeague, selectedSeason], () => {
    const params = applyFilters({
        [FILTER_LEAGUE]: selectedLeague.value,
        [FILTER_SEASON]: selectedSeason.value
    });
    
    router.get(
        route('teams.index'),
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

// Count active filters
const activeFiltersCount = computed(() => {
    let count = 0;
    if (selectedLeague.value) count++;
    if (selectedSeason.value) count++;
    return count;
});
</script>

<template>
    <Head title="Teams" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Teams
            </h2>
        </template>

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
                        <CardDescription>Filter teams by league and season</CardDescription>
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
                            
                            <div class="flex items-end sm:col-span-2">
                                <Button variant="outline" @click="clearFilters" class="h-10">
                                    Clear Filters
                                </Button>
                            </div>
                        </div>
                        
                        <!-- Active Filters Display -->
                        <div v-if="selectedLeague || selectedSeason" class="mt-4">
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
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- No Results Alert -->
                <Alert v-if="teams.data.length === 0" class="mb-6">
                    <AlertTitle>No teams found</AlertTitle>
                    <AlertDescription>
                        No teams match your current filters. Try changing your filter criteria or clear all filters.
                    </AlertDescription>
                </Alert>

                <!-- Teams Table -->
                <Card v-if="teams.data.length > 0">
                    <CardHeader>
                        <CardTitle>Teams</CardTitle>
                        <CardDescription>
                            Showing {{ teams.data.length }} of {{ teams.total }} teams
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Team Name</TableHead>
                                    <TableHead>League</TableHead>
                                    <TableHead>Season</TableHead>
                                    <TableHead>Association</TableHead>
                                    <TableHead class="w-[100px]">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="team in teams.data" :key="team.id">
                                    <TableCell class="text-center">{{ team.id }}</TableCell>
                                    <TableCell class="font-medium">
                                        {{ team.name }}
                                    </TableCell>
                                    <TableCell>{{ team.league?.name || 'N/A' }}</TableCell>
                                    <TableCell>{{ team.season?.name || 'N/A' }}</TableCell>
                                    <TableCell>{{ team.association?.name || 'N/A' }}</TableCell>
                                    <TableCell>
                                        <Link :href="route('teams.show', team.id)">
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
                                    <PaginationItem v-if="teams.prev_page_url">
                                        <PaginationPrevious :href="teams.prev_page_url" />
                                    </PaginationItem>
                                    
                                    <template v-for="(link, i) in teams.links" :key="i">
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
                                    
                                    <PaginationItem v-if="teams.next_page_url">
                                        <PaginationNext :href="teams.next_page_url" />
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