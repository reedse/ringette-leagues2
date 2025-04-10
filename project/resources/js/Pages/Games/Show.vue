<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { format } from 'date-fns';

// Import shadcn-vue components
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Separator } from "@/components/ui/separator";

const props = defineProps({
    game: Object,
});

// Format date for display
const formatDate = (dateTime) => {
    if (!dateTime) return 'TBD';
    return format(new Date(dateTime), 'MMMM d, yyyy • h:mm a');
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
    <Head :title="`Game Details`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Game Details
                </h2>
                <Link :href="route('games.index')">
                    <Button variant="outline" size="sm">
                        Back to Games
                    </Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Game Header Card -->
                <Card class="mb-6">
                    <CardHeader>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <CardTitle class="text-2xl">
                                    {{ game.home_team.name }} vs. {{ game.away_team.name }}
                                </CardTitle>
                                <CardDescription>
                                    {{ formatDate(game.game_date_time) }}
                                </CardDescription>
                            </div>
                            <Badge :variant="getStatusVariant(game.status)" class="mt-2 sm:mt-0">
                                {{ game.status }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <h3 class="font-semibold">League</h3>
                                <p>{{ game.league.name }}</p>
                            </div>
                            <div class="space-y-2">
                                <h3 class="font-semibold">Season</h3>
                                <p>{{ game.season.name }}</p>
                            </div>
                            <div v-if="game.location" class="space-y-2">
                                <h3 class="font-semibold">Location</h3>
                                <p>{{ game.location }}</p>
                            </div>
                        </div>

                        <Separator class="my-6" />

                        <div v-if="game.home_score !== null && game.away_score !== null" class="grid grid-cols-1 gap-8 sm:grid-cols-3">
                            <div class="text-center">
                                <h3 class="text-xl font-bold">{{ game.home_team.name }}</h3>
                                <div class="mt-2 text-5xl font-extrabold">{{ game.home_score }}</div>
                                <p class="mt-2 text-sm text-muted-foreground">Home Team</p>
                            </div>
                            
                            <div class="flex items-center justify-center">
                                <Badge class="text-lg" variant="outline">
                                    {{ getResultText(game) }}
                                </Badge>
                            </div>
                            
                            <div class="text-center">
                                <h3 class="text-xl font-bold">{{ game.away_team.name }}</h3>
                                <div class="mt-2 text-5xl font-extrabold">{{ game.away_score }}</div>
                                <p class="mt-2 text-sm text-muted-foreground">Away Team</p>
                            </div>
                        </div>
                        
                        <div v-else class="my-6 text-center">
                            <p class="text-lg text-muted-foreground">Scores will be available after the game is completed</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Game Details Tabs -->
                <Tabs defaultValue="player-stats" class="w-full">
                    <TabsList class="grid w-full grid-cols-3">
                        <TabsTrigger value="player-stats">Player Stats</TabsTrigger>
                        <TabsTrigger value="penalties">Penalties</TabsTrigger>
                        <TabsTrigger value="video">Video & Clips</TabsTrigger>
                    </TabsList>
                    
                    <TabsContent value="player-stats">
                        <Card>
                            <CardHeader>
                                <CardTitle>Player Statistics</CardTitle>
                                <CardDescription>Individual player statistics for this game</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="text-center">
                                    <p class="text-muted-foreground">
                                        This tab will display individual player statistics for this game.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                    
                    <TabsContent value="penalties">
                        <Card>
                            <CardHeader>
                                <CardTitle>Penalties</CardTitle>
                                <CardDescription>Penalties assessed during the game</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="text-center">
                                    <p class="text-muted-foreground">
                                        This tab will display penalties assessed during the game.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                    
                    <TabsContent value="video">
                        <Card>
                            <CardHeader>
                                <CardTitle>Video & Clips</CardTitle>
                                <CardDescription>Game video and coach clips</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="text-center">
                                    <p class="text-muted-foreground">
                                        This tab will display the game video (if available) and any coach-created clips.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 