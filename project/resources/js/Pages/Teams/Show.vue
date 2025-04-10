<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';

// Import shadcn-vue components
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Separator } from "@/components/ui/separator";

const props = defineProps({
    team: Object,
    record: Object,
    stats: Object,
    recentGames: Array,
});

const initials = computed(() => {
    const words = props.team.name.split(' ');
    if (words.length === 1) {
        return words[0].substring(0, 2).toUpperCase();
    } else {
        return (words[0][0] + words[1][0]).toUpperCase();
    }
});

const formatGameDate = (dateString) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', { 
        weekday: 'short',
        month: 'short', 
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    }).format(date);
};

const getGameResult = (game) => {
    // Determine if the team is home or away in this game
    const isHome = game.home_team_id === props.team.id;
    
    // If scores are null, the game hasn't been played yet
    if (game.home_score === null || game.away_score === null) {
        return {
            text: 'Upcoming',
            class: 'bg-amber-100 text-amber-800'
        };
    }
    
    // Determine the result based on the score
    const teamScore = isHome ? game.home_score : game.away_score;
    const opponentScore = isHome ? game.away_score : game.home_score;
    
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

const getOpponentName = (game) => {
    const isHome = game.home_team_id === props.team.id;
    return isHome ? game.away_team?.name : game.home_team?.name;
};

const getGameScore = (game) => {
    if (game.home_score === null || game.away_score === null) {
        return '-';
    }
    
    const isHome = game.home_team_id === props.team.id;
    const teamScore = isHome ? game.home_score : game.away_score;
    const opponentScore = isHome ? game.away_score : game.home_score;
    
    return `${teamScore} - ${opponentScore}`;
};

// Group players by position
const playersByPosition = computed(() => {
    const positions = {};
    
    if (!props.team.players) return positions;
    
    props.team.players.forEach(player => {
        const position = player.position || 'Unspecified';
        if (!positions[position]) {
            positions[position] = [];
        }
        positions[position].push(player);
    });
    
    // Sort by position priority
    const sortedPositions = {};
    const positionPriority = ['Forward', 'Defense', 'Goalie', 'Unspecified'];
    
    positionPriority.forEach(position => {
        if (positions[position]) {
            sortedPositions[position] = positions[position];
        }
    });
    
    // Add any remaining positions
    Object.keys(positions).forEach(position => {
        if (!positionPriority.includes(position)) {
            sortedPositions[position] = positions[position];
        }
    });
    
    return sortedPositions;
});
</script>

<template>
    <Head :title="team.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ team.name }}
                </h2>
                <Link :href="route('teams.index')">
                    <Button variant="outline">Back to Teams</Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Team Header -->
                <Card class="shadow-md mb-6">
                    <CardContent class="p-0">
                        <div class="p-6 sm:p-8 flex flex-col sm:flex-row gap-8 items-start">
                            <!-- Team Avatar -->
                            <Avatar class="w-24 h-24 rounded-md border">
                                <AvatarFallback class="rounded-md bg-primary text-3xl text-primary-foreground">
                                    {{ initials }}
                                </AvatarFallback>
                            </Avatar>
                            
                            <!-- Team Info -->
                            <div class="flex-1 space-y-4">
                                <div>
                                    <h1 class="text-2xl font-bold">{{ team.name }}</h1>
                                    <p class="text-muted-foreground">{{ team.association?.name }}</p>
                                </div>
                                
                                <div class="flex flex-wrap gap-2">
                                    <Badge variant="secondary">{{ team.league?.name }}</Badge>
                                    <Badge variant="outline">{{ team.season?.name }}</Badge>
                                </div>
                                
                                <!-- Team Record & Stats Summary -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
                                    <div class="space-y-1">
                                        <p class="text-sm text-muted-foreground">Record</p>
                                        <p class="text-lg font-medium">{{ record.wins }}-{{ record.losses }}-{{ record.ties }}</p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm text-muted-foreground">Win %</p>
                                        <p class="text-lg font-medium">{{ record.winPercentage }}%</p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm text-muted-foreground">Goals For</p>
                                        <p class="text-lg font-medium">{{ stats.goalsFor }} ({{ stats.goalsForAvg }}/G)</p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm text-muted-foreground">Goals Against</p>
                                        <p class="text-lg font-medium">{{ stats.goalsAgainst }} ({{ stats.goalsAgainstAvg }}/G)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Tabs for Team Info -->
                <Tabs defaultValue="roster" class="mb-8">
                    <TabsList class="grid w-full grid-cols-2">
                        <TabsTrigger value="roster">Team Roster</TabsTrigger>
                        <TabsTrigger value="games">Recent Games</TabsTrigger>
                    </TabsList>
                    
                    <!-- Roster Tab -->
                    <TabsContent value="roster">
                        <Card>
                            <CardHeader>
                                <CardTitle>Team Roster</CardTitle>
                                <CardDescription>
                                    {{ team.players?.length || 0 }} players on roster
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div v-if="!team.players || team.players.length === 0" class="text-center py-12">
                                    <p class="text-muted-foreground">No players on the roster yet.</p>
                                </div>
                                
                                <div v-else>
                                    <div v-for="(players, position) in playersByPosition" :key="position" class="mb-6">
                                        <h3 class="font-medium text-lg mb-2">{{ position }}</h3>
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead class="w-16">#</TableHead>
                                                    <TableHead>Name</TableHead>
                                                    <TableHead class="text-right">Action</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                <TableRow v-for="player in players" :key="player.id">
                                                    <TableCell class="font-medium">{{ player.jersey_number }}</TableCell>
                                                    <TableCell>{{ player.first_name }} {{ player.last_name }}</TableCell>
                                                    <TableCell class="text-right">
                                                        <Button variant="ghost" size="sm" asChild>
                                                            <Link href="#">
                                                                Profile
                                                            </Link>
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                    
                    <!-- Games Tab -->
                    <TabsContent value="games">
                        <Card>
                            <CardHeader>
                                <CardTitle>Recent Games</CardTitle>
                                <CardDescription>
                                    Last {{ recentGames.length }} games played by {{ team.name }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div v-if="recentGames.length === 0" class="text-center py-12">
                                    <p class="text-muted-foreground">No games scheduled yet.</p>
                                </div>
                                
                                <div v-else>
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Opponent</TableHead>
                                                <TableHead>Result</TableHead>
                                                <TableHead>Score</TableHead>
                                                <TableHead class="text-right">Action</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="game in recentGames" :key="game.id">
                                                <TableCell>{{ formatGameDate(game.game_date_time) }}</TableCell>
                                                <TableCell>{{ getOpponentName(game) }}</TableCell>
                                                <TableCell>
                                                    <Badge 
                                                        variant="outline" 
                                                        :class="getGameResult(game).class"
                                                    >
                                                        {{ getGameResult(game).text }}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>{{ getGameScore(game) }}</TableCell>
                                                <TableCell class="text-right">
                                                    <Button variant="ghost" size="sm" asChild>
                                                        <Link href="#">
                                                            Details
                                                        </Link>
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                    
                                    <div class="mt-4 flex justify-end">
                                        <Button variant="outline" asChild>
                                            <Link href="#">
                                                View All Games
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 