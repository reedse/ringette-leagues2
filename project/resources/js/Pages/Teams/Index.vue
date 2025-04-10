<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, watch, ref } from 'vue';

// Import shadcn-vue components
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationLink, PaginationNext, PaginationPrevious } from "@/components/ui/pagination";
import { Badge } from "@/components/ui/badge";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Separator } from "@/components/ui/separator";

const props = defineProps({
    teams: Object,
    leagues: Array,
    seasons: Array,
    filters: Object,
});

const selectedLeague = ref(props.filters.league || '');
const selectedSeason = ref(props.filters.season || '');

// Apply filters when they change
watch([selectedLeague, selectedSeason], () => {
    router.get(
        route('teams.index'),
        { 
            league: selectedLeague.value || null,
            season: selectedSeason.value || null
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
};

// Get league name by ID
const getLeagueName = (leagueId) => {
    const league = props.leagues.find(l => l.id === leagueId);
    return league ? league.name : 'Unknown League';
};

// Group teams by league for display
const teamsByLeague = computed(() => {
    const grouped = {};
    
    props.teams.data.forEach(team => {
        const leagueId = team.league_id;
        if (!grouped[leagueId]) {
            grouped[leagueId] = {
                league: team.league,
                teams: []
            };
        }
        grouped[leagueId].teams.push(team);
    });
    
    return Object.values(grouped);
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
                        <CardTitle>Filters</CardTitle>
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
                            
                            <div class="flex items-end sm:col-span-2">
                                <Button variant="outline" @click="clearFilters" class="h-10">
                                    Clear Filters
                                </Button>
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

                <!-- Team Grid -->
                <div v-for="group in teamsByLeague" :key="group.league.id" class="mb-8">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">{{ group.league.name }}</h3>
                        <Badge variant="outline">{{ group.teams.length }} teams</Badge>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <Card v-for="team in group.teams" :key="team.id" class="overflow-hidden">
                            <CardHeader class="space-y-1 bg-muted/40">
                                <CardTitle class="text-xl">{{ team.name }}</CardTitle>
                                <CardDescription>
                                    {{ team.association?.name }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="p-6">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-muted-foreground">Season:</span>
                                        <span>{{ team.season?.name }}</span>
                                    </div>
                                    <Separator />
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-muted-foreground">Association:</span>
                                        <span>{{ team.association?.name }}</span>
                                    </div>
                                </div>
                            </CardContent>
                            <CardFooter>
                                <Link :href="route('teams.show', team.id)" class="w-full">
                                    <Button variant="default" class="w-full">
                                        View Team
                                    </Button>
                                </Link>
                            </CardFooter>
                        </Card>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="teams.data.length > 0" class="mt-6 flex justify-center">
                    <Pagination>
                        <PaginationContent>
                            <PaginationItem v-if="teams.prev_page_url">
                                <PaginationPrevious :href="teams.prev_page_url" />
                            </PaginationItem>
                            
                            <template v-for="(link, i) in teams.links" :key="i">
                                <PaginationItem v-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next')">
                                    <PaginationLink :href="link.url" :isActive="link.active">
                                        {{ link.label }}
                                    </PaginationLink>
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
            </div>
        </div>
    </AuthenticatedLayout>
</template> 