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
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { ExternalLink } from 'lucide-vue-next';

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
            return 'badge-scheduled';
        case 'In Progress':
            return 'badge-in-progress';
        case 'Completed':
            return 'badge-completed';
        case 'Cancelled':
            return 'badge-cancelled';
        case 'Draft':
            return 'badge-draft';
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

// Generate player initials for avatar
const getPlayerInitials = (player) => {
    return `${player.first_name[0]}${player.last_name[0]}`.toUpperCase();
};

// Format penalty time in appropriate format
const formatPenaltyTime = (minutes) => {
    return `${minutes} min`;
};

// Helper to extract YouTube video ID from URL
const getYoutubeEmbedUrl = (url) => {
    if (!url) return null;
    
    // Try to extract YouTube video ID
    const regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
    const match = url.match(regExp);
    const videoId = (match && match[7].length === 11) ? match[7] : null;
    
    return videoId ? `https://www.youtube.com/embed/${videoId}` : null;
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
                                <div v-if="!game.playerStats || game.playerStats.length === 0" class="py-8 text-center">
                                    <p class="text-muted-foreground">No player statistics recorded for this game yet.</p>
                                </div>
                                <div v-else>
                                    <!-- Home Team Stats -->
                                    <h3 class="mb-3 text-lg font-semibold">{{ game.home_team.name }}</h3>
                                    <Table class="mb-8">
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead class="w-[50px]">#</TableHead>
                                                <TableHead>Player</TableHead>
                                                <TableHead class="text-right">G</TableHead>
                                                <TableHead class="text-right">A</TableHead>
                                                <TableHead class="text-right">Shots</TableHead>
                                                <TableHead class="text-right">+/-</TableHead>
                                                <TableHead class="text-right">Profile</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="stat in game.playerStats.filter(s => s.player?.team_id === game.home_team_id)" 
                                                     :key="stat.id">
                                                <TableCell>{{ stat.player?.jersey_number || '-' }}</TableCell>
                                                <TableCell class="flex items-center gap-2">
                                                    <Avatar class="h-6 w-6">
                                                        <AvatarFallback>{{ getPlayerInitials(stat.player) }}</AvatarFallback>
                                                    </Avatar>
                                                    {{ stat.player?.first_name }} {{ stat.player?.last_name }}
                                                </TableCell>
                                                <TableCell class="text-right">{{ stat.goals || 0 }}</TableCell>
                                                <TableCell class="text-right">{{ stat.assists || 0 }}</TableCell>
                                                <TableCell class="text-right">{{ stat.shots || 0 }}</TableCell>
                                                <TableCell class="text-right">{{ stat.plus_minus || 0 }}</TableCell>
                                                <TableCell class="text-right">
                                                    <Button variant="ghost" size="sm" asChild>
                                                        <Link :href="route('players.show', stat.player.id)">View</Link>
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                    
                                    <!-- Away Team Stats -->
                                    <h3 class="mb-3 text-lg font-semibold">{{ game.away_team.name }}</h3>
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead class="w-[50px]">#</TableHead>
                                                <TableHead>Player</TableHead>
                                                <TableHead class="text-right">G</TableHead>
                                                <TableHead class="text-right">A</TableHead>
                                                <TableHead class="text-right">Shots</TableHead>
                                                <TableHead class="text-right">+/-</TableHead>
                                                <TableHead class="text-right">Profile</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="stat in game.playerStats.filter(s => s.player?.team_id === game.away_team_id)" 
                                                     :key="stat.id">
                                                <TableCell>{{ stat.player?.jersey_number || '-' }}</TableCell>
                                                <TableCell class="flex items-center gap-2">
                                                    <Avatar class="h-6 w-6">
                                                        <AvatarFallback>{{ getPlayerInitials(stat.player) }}</AvatarFallback>
                                                    </Avatar>
                                                    {{ stat.player?.first_name }} {{ stat.player?.last_name }}
                                                </TableCell>
                                                <TableCell class="text-right">{{ stat.goals || 0 }}</TableCell>
                                                <TableCell class="text-right">{{ stat.assists || 0 }}</TableCell>
                                                <TableCell class="text-right">{{ stat.shots || 0 }}</TableCell>
                                                <TableCell class="text-right">{{ stat.plus_minus || 0 }}</TableCell>
                                                <TableCell class="text-right">
                                                    <Button variant="ghost" size="sm" asChild>
                                                        <Link :href="route('players.show', stat.player.id)">View</Link>
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
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
                                <div v-if="!game.penalties || game.penalties.length === 0" class="py-8 text-center">
                                    <p class="text-muted-foreground">No penalties recorded for this game.</p>
                                </div>
                                <Table v-else>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Period</TableHead>
                                            <TableHead>Team</TableHead>
                                            <TableHead>Player</TableHead>
                                            <TableHead>Infraction</TableHead>
                                            <TableHead class="text-right">Minutes</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="penalty in game.penalties" :key="penalty.id">
                                            <TableCell>{{ penalty.period }}</TableCell>
                                            <TableCell>
                                                {{ penalty.player?.team_id === game.home_team_id ? game.home_team.name : game.away_team.name }}
                                            </TableCell>
                                            <TableCell class="flex items-center gap-2">
                                                <Avatar class="h-6 w-6">
                                                    <AvatarFallback>{{ getPlayerInitials(penalty.player) }}</AvatarFallback>
                                                </Avatar>
                                                {{ penalty.player?.first_name }} {{ penalty.player?.last_name }}
                                            </TableCell>
                                            <TableCell>{{ penalty.penaltyCode?.name }}</TableCell>
                                            <TableCell class="text-right">
                                                <Badge variant="outline">
                                                    {{ formatPenaltyTime(penalty.minutes) }}
                                                </Badge>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
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
                                <!-- Game Video -->
                                <div v-if="game.video_url" class="mb-8">
                                    <h3 class="text-lg font-medium mb-4">Game Video</h3>
                                    <div class="aspect-w-16 aspect-h-9 rounded-lg overflow-hidden">
                                        <iframe 
                                            v-if="getYoutubeEmbedUrl(game.video_url)"
                                            class="w-full h-full rounded-lg"
                                            :src="getYoutubeEmbedUrl(game.video_url)"
                                            title="Game Video"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen>
                                        </iframe>
                                        <div v-else class="w-full h-full flex items-center justify-center bg-muted rounded-lg">
                                            <Button variant="outline" asChild>
                                                <a :href="game.video_url" target="_blank" rel="noopener noreferrer">
                                                    <ExternalLink class="mr-2 h-4 w-4" />
                                                    View Video
                                                </a>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Coach Clips -->
                                <div>
                                    <h3 class="text-lg font-medium mb-4">Clips</h3>
                                    
                                    <div v-if="!game.clips || game.clips.length === 0" class="text-center py-6">
                                        <p class="text-muted-foreground">No clips have been created for this game yet.</p>
                                    </div>
                                    
                                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <Card v-for="clip in game.clips" :key="clip.id" class="overflow-hidden">
                                            <CardHeader class="pb-2">
                                                <CardTitle class="text-lg">{{ clip.title }}</CardTitle>
                                                <CardDescription>
                                                    {{ clip.start_time }}s to {{ clip.end_time }}s
                                                </CardDescription>
                                            </CardHeader>
                                            <CardContent class="pb-2">
                                                <p class="text-sm">{{ clip.description }}</p>
                                            </CardContent>
                                            <CardFooter class="flex justify-between pt-2">
                                                <p class="text-xs text-muted-foreground">
                                                    Shared with {{ clip.players?.length || 0 }} players
                                                </p>
                                                <Button size="sm" asChild>
                                                    <a :href="clip.video_url" target="_blank" rel="noopener noreferrer">
                                                        View Clip
                                                    </a>
                                                </Button>
                                            </CardFooter>
                                        </Card>
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