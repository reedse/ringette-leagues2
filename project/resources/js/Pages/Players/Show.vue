<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { format } from 'date-fns';

// Import shadcn-vue components
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/Components/ui/card";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import { Separator } from "@/Components/ui/separator";

const props = defineProps({
    player: Object,
    cumulativeStats: Object,
    recentGames: Array,
    currentTeam: Object
});

// Format date for display
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return format(new Date(dateString), 'MMMM d, yyyy');
};

// Format game date
const formatGameDate = (dateString) => {
    if (!dateString) return 'TBD';
    return format(new Date(dateString), 'MMM d, yyyy • h:mm a');
};

// Calculate age from date of birth
const calculateAge = (dateOfBirth) => {
    if (!dateOfBirth) return 'N/A';
    const today = new Date();
    const birthDate = new Date(dateOfBirth);
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
};

// Generate player initials for avatar
const getPlayerInitials = () => {
    return `${props.player.first_name[0]}${props.player.last_name[0]}`.toUpperCase();
};

// Get game result for a player
const getGameResult = (game, playerStatId) => {
    // Find player stat for this game
    const stat = game.playerStats.find(s => s.player_id === props.player.id);
    if (!stat) return null;
    
    // Determine if player's team was home or away
    const isPlayerOnHomeTeam = stat.team_id === game.home_team_id;
    
    // Get scores
    const teamScore = isPlayerOnHomeTeam ? game.home_score : game.away_score;
    const opponentScore = isPlayerOnHomeTeam ? game.away_score : game.home_score;
    
    // If scores aren't set yet
    if (teamScore === null || opponentScore === null) {
        return {
            text: 'Upcoming',
            class: 'bg-amber-100 text-amber-800'
        };
    }
    
    // Determine result
    if (teamScore > opponentScore) {
        return {
            text: 'Win',
            class: 'bg-green-100 text-green-800'
        };
    } else if (teamScore < opponentScore) {
        return {
            text: 'Loss',
            class: 'bg-red-100 text-red-800'
        };
    } else {
        return {
            text: 'Tie',
            class: 'bg-blue-100 text-blue-800'
        };
    }
};

// Get opponent for a game
const getOpponent = (game) => {
    // Find player stat for this game
    const stat = game.playerStats.find(s => s.player_id === props.player.id);
    if (!stat) return 'Unknown';
    
    // Determine if player's team was home or away
    const isPlayerOnHomeTeam = stat.team_id === game.home_team_id;
    
    // Return the opponent name
    return isPlayerOnHomeTeam ? game.away_team.name : game.home_team.name;
};
</script>

<template>
    <Head :title="`${player.first_name} ${player.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Player Profile
                </h2>
                <Link :href="route('players.index')">
                    <Button variant="outline" size="sm">
                        Back to Players
                    </Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Player Header -->
                <Card class="mb-6">
                    <CardContent class="p-0">
                        <div class="p-6 flex flex-col sm:flex-row items-start gap-6">
                            <!-- Player Avatar -->
                            <Avatar class="h-24 w-24 rounded-full">
                                <AvatarFallback class="text-2xl">
                                    {{ getPlayerInitials() }}
                                </AvatarFallback>
                            </Avatar>
                            
                            <!-- Player Info -->
                            <div class="flex-1">
                                <div class="mb-4">
                                    <h1 class="text-2xl font-bold">
                                        {{ player.first_name }} {{ player.last_name }}
                                        <span v-if="player.jersey_number" class="text-lg font-normal text-muted-foreground ml-2">
                                            #{{ player.jersey_number }}
                                        </span>
                                    </h1>
                                    <p class="text-muted-foreground">{{ player.position || 'Position not specified' }}</p>
                                </div>
                                
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-4">
                                    <div v-if="player.date_of_birth" class="space-y-1">
                                        <p class="text-sm text-muted-foreground">Age</p>
                                        <p class="font-medium">{{ calculateAge(player.date_of_birth) }} years</p>
                                    </div>
                                    <div v-if="currentTeam" class="space-y-1">
                                        <p class="text-sm text-muted-foreground">Current Team</p>
                                        <p class="font-medium">{{ currentTeam.name }}</p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm text-muted-foreground">Career Games</p>
                                        <p class="font-medium">{{ cumulativeStats.games }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-wrap gap-2">
                                    <Badge variant="secondary" v-if="currentTeam && currentTeam.league">
                                        {{ currentTeam.league.name }}
                                    </Badge>
                                    <Badge variant="outline" v-if="currentTeam && currentTeam.season">
                                        {{ currentTeam.season.name }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Career Stats Summary Card -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Career Statistics</CardTitle>
                        <CardDescription>
                            Summary of career statistics across all seasons
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-6">
                            <div class="space-y-1 text-center p-2 bg-muted/20 rounded-md">
                                <p class="text-sm text-muted-foreground">Games</p>
                                <p class="text-2xl font-bold">{{ cumulativeStats.games }}</p>
                            </div>
                            <div class="space-y-1 text-center p-2 bg-muted/20 rounded-md">
                                <p class="text-sm text-muted-foreground">Goals</p>
                                <p class="text-2xl font-bold">{{ cumulativeStats.goals }}</p>
                            </div>
                            <div class="space-y-1 text-center p-2 bg-muted/20 rounded-md">
                                <p class="text-sm text-muted-foreground">Assists</p>
                                <p class="text-2xl font-bold">{{ cumulativeStats.assists }}</p>
                            </div>
                            <div class="space-y-1 text-center p-2 bg-muted/20 rounded-md">
                                <p class="text-sm text-muted-foreground">Points</p>
                                <p class="text-2xl font-bold">{{ cumulativeStats.points }}</p>
                            </div>
                            <div class="space-y-1 text-center p-2 bg-muted/20 rounded-md">
                                <p class="text-sm text-muted-foreground">+/-</p>
                                <p class="text-2xl font-bold">{{ cumulativeStats.plus_minus }}</p>
                            </div>
                            <div class="space-y-1 text-center p-2 bg-muted/20 rounded-md">
                                <p class="text-sm text-muted-foreground">Shot %</p>
                                <p class="text-2xl font-bold">{{ cumulativeStats.shooting_percentage }}%</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Tabs for Player Info -->
                <Tabs defaultValue="recent-games">
                    <TabsList class="grid w-full grid-cols-3">
                        <TabsTrigger value="recent-games">Recent Games</TabsTrigger>
                        <TabsTrigger value="history">Team History</TabsTrigger>
                        <TabsTrigger value="penalties">Penalties</TabsTrigger>
                    </TabsList>
                    
                    <!-- Recent Games Tab -->
                    <TabsContent value="recent-games">
                        <Card>
                            <CardHeader>
                                <CardTitle>Recent Games</CardTitle>
                                <CardDescription>
                                    Last {{ recentGames.length }} games played
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div v-if="recentGames.length === 0" class="py-8 text-center">
                                    <p class="text-muted-foreground">No recent games found for this player.</p>
                                </div>
                                <Table v-else>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Date</TableHead>
                                            <TableHead>Opponent</TableHead>
                                            <TableHead>Result</TableHead>
                                            <TableHead class="text-right">G</TableHead>
                                            <TableHead class="text-right">A</TableHead>
                                            <TableHead class="text-right">PTS</TableHead>
                                            <TableHead class="text-right">+/-</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="game in recentGames" :key="game.id">
                                            <TableCell>
                                                <Link :href="route('games.show', game.id)" class="hover:underline">
                                                    {{ formatGameDate(game.game_date_time) }}
                                                </Link>
                                            </TableCell>
                                            <TableCell>
                                                <div class="font-medium">{{ getOpponent(game) }}</div>
                                                <div class="text-xs text-muted-foreground">{{ game.league.name }}</div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge v-if="getGameResult(game)" :class="getGameResult(game).class">
                                                    {{ getGameResult(game).text }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                {{ game.playerStats[0].goals || 0 }}
                                            </TableCell>
                                            <TableCell class="text-right">
                                                {{ game.playerStats[0].assists || 0 }}
                                            </TableCell>
                                            <TableCell class="text-right">
                                                {{ (game.playerStats[0].goals || 0) + (game.playerStats[0].assists || 0) }}
                                            </TableCell>
                                            <TableCell class="text-right">
                                                {{ game.playerStats[0].plus_minus || 0 }}
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </TabsContent>
                    
                    <!-- Team History Tab -->
                    <TabsContent value="history">
                        <Card>
                            <CardHeader>
                                <CardTitle>Team History</CardTitle>
                                <CardDescription>
                                    Teams and seasons player has participated in
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div v-if="!player.rosterEntries || player.rosterEntries.length === 0" class="py-8 text-center">
                                    <p class="text-muted-foreground">No team history available for this player.</p>
                                </div>
                                <Table v-else>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Season</TableHead>
                                            <TableHead>Team</TableHead>
                                            <TableHead>League</TableHead>
                                            <TableHead class="text-right">Team Info</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="entry in player.rosterEntries" :key="entry.id">
                                            <TableCell>{{ entry.season?.name || 'Unknown Season' }}</TableCell>
                                            <TableCell>
                                                <div class="font-medium">{{ entry.team?.name || 'Unknown Team' }}</div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="secondary">
                                                    {{ entry.team?.league?.name || 'N/A' }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <Button v-if="entry.team_id" variant="outline" size="sm" asChild>
                                                    <Link :href="route('teams.show', entry.team_id)">
                                                        View Team
                                                    </Link>
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </TabsContent>
                    
                    <!-- Penalties Tab -->
                    <TabsContent value="penalties">
                        <Card>
                            <CardHeader>
                                <CardTitle>Penalties</CardTitle>
                                <CardDescription>
                                    Penalties assessed to this player
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div v-if="!player.penalties || player.penalties.length === 0" class="py-8 text-center">
                                    <p class="text-muted-foreground">No penalties recorded for this player.</p>
                                </div>
                                <div v-else>
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Game</TableHead>
                                                <TableHead>Infraction</TableHead>
                                                <TableHead class="text-right">Minutes</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="penalty in player.penalties" :key="penalty.id">
                                                <TableCell>{{ formatGameDate(penalty.game?.game_date_time) }}</TableCell>
                                                <TableCell>
                                                    <Link :href="route('games.show', penalty.game_id)" class="hover:underline">
                                                        {{ penalty.game?.home_team?.name }} vs {{ penalty.game?.away_team?.name }}
                                                    </Link>
                                                </TableCell>
                                                <TableCell>{{ penalty.penaltyCode?.name || 'Unspecified' }}</TableCell>
                                                <TableCell class="text-right">
                                                    <Badge variant="outline">
                                                        {{ penalty.minutes }} min
                                                    </Badge>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                        <TableCaption>
                                            Total: {{ cumulativeStats.penalties }} penalties, {{ cumulativeStats.penalty_minutes }} penalty minutes
                                        </TableCaption>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 