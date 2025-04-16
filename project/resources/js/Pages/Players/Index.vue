<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue';

// Import shadcn-vue components
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { 
    Pagination, 
    PaginationList, 
    PaginationEllipsis, 
    PaginationListItem, 
    PaginationPrev, 
    PaginationNext
} from "@/components/ui/pagination";
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/components/ui/button';

const props = defineProps({
    players: Object,
    teams: Array,
    seasons: Array,
    positionOptions: Array,
    filters: Object,
});

// Set up filter form with defaults from props
const filterForm = ref({
    team: props.filters.team || '',
    season: props.filters.season || '',
    position: props.filters.position || '',
});

// Handle filter change
const handleFilterChange = () => {
    router.get(route('players.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Reset all filters
const resetFilters = () => {
    filterForm.value = {
        team: '',
        season: '',
        position: '',
    };
    handleFilterChange();
};

// Generate player initials for avatar
const getPlayerInitials = (player) => {
    return `${player.first_name[0]}${player.last_name[0]}`.toUpperCase();
};

// Watch for filter changes and update the URL
watch(() => filterForm.value, handleFilterChange, { deep: true });
</script>

<template>
    <Head title="Players" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Players</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filters -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Filters</CardTitle>
                        <CardDescription>Filter players by team, season, and position</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                            <div class="space-y-2">
                                <Label for="team">Team</Label>
                                <Select v-model="filterForm.team">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Teams" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All Teams</SelectItem>
                                        <SelectItem v-for="team in teams" :key="team.id" :value="team.id.toString()">
                                            {{ team.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="season">Season</Label>
                                <Select v-model="filterForm.season">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Seasons" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All Seasons</SelectItem>
                                        <SelectItem v-for="season in seasons" :key="season.id" :value="season.id.toString()">
                                            {{ season.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="position">Position</Label>
                                <Select v-model="filterForm.position">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Positions" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All Positions</SelectItem>
                                        <SelectItem v-for="position in positionOptions" :key="position" :value="position">
                                            {{ position }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            
                            <div class="flex items-end">
                                <Button variant="outline" @click="resetFilters" class="w-full">Reset Filters</Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Players Table -->
                <Card>
                    <CardHeader>
                        <CardTitle>Players Directory</CardTitle>
                        <CardDescription>
                            {{ players.total }} players found
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table v-if="players.data.length > 0">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Player</TableHead>
                                    <TableHead>Position</TableHead>
                                    <TableHead>Teams</TableHead>
                                    <TableHead class="text-right">Profile</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="player in players.data" :key="player.id">
                                    <TableCell>
                                        <div class="flex items-center gap-3">
                                            <Avatar>
                                                <AvatarFallback>{{ getPlayerInitials(player) }}</AvatarFallback>
                                            </Avatar>
                                            <div>
                                                <div class="font-medium">{{ player.first_name }} {{ player.last_name }}</div>
                                                <div v-if="player.jersey_number" class="text-sm text-muted-foreground">
                                                    #{{ player.jersey_number }}
                                                </div>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline">
                                            {{ player.position || 'Unspecified' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-wrap gap-1">
                                            <Badge 
                                                v-for="team in player.teams.slice(0, 2)" 
                                                :key="team.id" 
                                                variant="secondary" 
                                                class="mr-1"
                                            >
                                                {{ team.name }}
                                            </Badge>
                                            <Badge 
                                                v-if="player.teams.length > 2" 
                                                variant="outline"
                                            >
                                                +{{ player.teams.length - 2 }} more
                                            </Badge>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link :href="route('players.show', player.id)">
                                                View Profile
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        
                        <div v-else class="py-8 text-center">
                            <p class="text-muted-foreground">No players found. Try adjusting your filters.</p>
                        </div>
                        
                        <!-- Pagination -->
                        <div v-if="players.links && players.links.length > 3" class="mt-6 flex justify-center">
                            <Pagination
                                :total="players.total"
                                :per-page="players.per_page"
                                :current-page="players.current_page"
                                v-slot="{ pages, isFirstPage, isLastPage }" 
                                show-edges
                            >
                                <PaginationList>
                                    <PaginationListItem>
                                        <PaginationPrev 
                                            :href="players.prev_page_url"
                                            :class="['pe-2.5', { 'opacity-50 pointer-events-none': isFirstPage }]" 
                                            :as-child="!isFirstPage"
                                        >
                                            <Link v-if="!isFirstPage" :href="players.prev_page_url" preserve-scroll>Prev</Link>
                                            <span v-else>Prev</span>
                                        </PaginationPrev>
                                    </PaginationListItem>

                                    <template v-for="(page, index) in pages" :key="index">
                                        <PaginationListItem v-if="page.type === 'page'" :value="page.value">
                                            <Link 
                                                :href="page.url"
                                                :class="cn(
                                                    buttonVariants({ variant: page.value === players.current_page ? 'outline' : 'ghost' }), 
                                                    'h-9 w-9 p-0'
                                                )"
                                                preserve-scroll
                                            >
                                                {{ page.value }}
                                            </Link>
                                        </PaginationListItem>
                                        <PaginationListItem v-else :key="`ellipsis-${index}`">
                                            <PaginationEllipsis />
                                        </PaginationListItem>
                                    </template>

                                    <PaginationListItem>
                                        <PaginationNext 
                                            :href="players.next_page_url"
                                            :class="[ 'ps-2.5', { 'opacity-50 pointer-events-none': isLastPage }]"
                                            :as-child="!isLastPage"
                                        >
                                            <Link v-if="!isLastPage" :href="players.next_page_url" preserve-scroll>Next</Link>
                                            <span v-else>Next</span>
                                        </PaginationNext>
                                    </PaginationListItem>
                                </PaginationList>
                            </Pagination>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 