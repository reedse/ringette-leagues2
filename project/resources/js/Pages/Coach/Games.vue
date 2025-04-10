<script setup>
import { ref, reactive } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';

// Mock team data
const teamName = "Ringette Rockets";
const games = [
    {
        id: 1,
        date: '2025-04-15',
        time: '18:30',
        homeTeam: teamName,
        awayTeam: 'Ice Tigers',
        location: 'City Arena',
        status: 'upcoming'
    },
    {
        id: 2,
        date: '2025-04-08',
        time: '19:00',
        homeTeam: 'Lightning Bolts',
        awayTeam: teamName,
        location: 'East End Arena',
        status: 'completed',
        score: {
            home: 3,
            away: 5
        }
    },
    {
        id: 3,
        date: '2025-04-01',
        time: '20:15',
        homeTeam: teamName,
        awayTeam: 'Blue Blizzards',
        location: 'City Arena',
        status: 'completed',
        score: {
            home: 7,
            away: 2
        }
    }
];

// Form state for multi-step form
const showGameForm = ref(false);
const currentStep = ref(1);
const totalSteps = 4;

const formState = reactive({
    // Step 1: Basic Game Info
    gameInfo: {
        date: '',
        time: '',
        location: '',
        homeTeam: teamName,
        awayTeam: '',
        homeScore: '',
        awayScore: '',
    },
    // Step 2: Player Selection
    selectedPlayers: [],
    // Step 3: Player Stats
    playerStats: [],
    // Step 4: Video and Notes
    videoUrl: '',
    notes: ''
});

// Available players for roster selection
const availablePlayers = [
    { id: 1, name: 'Jane Smith', number: 42 },
    { id: 2, name: 'Sarah Johnson', number: 7 },
    { id: 3, name: 'Melissa Brown', number: 15 },
    { id: 4, name: 'Amanda Lee', number: 23 },
    { id: 5, name: 'Olivia Garcia', number: 10 },
    { id: 6, name: 'Emma Wilson', number: 8 },
    { id: 7, name: 'Rebecca Taylor', number: 3 },
    { id: 8, name: 'Lauren Martinez', number: 19 },
];

// Available opposing teams
const opposingTeams = [
    'Ice Tigers',
    'Lightning Bolts', 
    'Blue Blizzards',
    'Red Rockets',
    'Golden Stars'
];

// Methods for form navigation
const nextStep = () => {
    if (currentStep.value < totalSteps) {
        // If moving to player stats step, initialize stats for selected players
        if (currentStep.value === 2) {
            formState.playerStats = formState.selectedPlayers.map(playerId => {
                const player = availablePlayers.find(p => p.id === playerId);
                return {
                    playerId,
                    playerName: player ? player.name : '',
                    playerNumber: player ? player.number : '',
                    goals: 0,
                    assists: 0,
                    penalties: 0
                };
            });
        }
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const submitGame = () => {
    // In a real app, this would send data to the server
    alert('Game saved successfully!');
    showGameForm.value = false;
    currentStep.value = 1;
    
    // Reset form
    formState.gameInfo = {
        date: '',
        time: '',
        location: '',
        homeTeam: teamName,
        awayTeam: '',
        homeScore: '',
        awayScore: '',
    };
    formState.selectedPlayers = [];
    formState.playerStats = [];
    formState.videoUrl = '';
    formState.notes = '';
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};
</script>

<template>
    <Head title="Game Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Game Management
                </h2>
                <Button @click="showGameForm = true">Add New Game</Button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ teamName }} Games</CardTitle>
                        <CardDescription>Manage your team's game schedule and results</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Tabs defaultValue="upcoming" class="w-full">
                            <TabsList class="grid w-full grid-cols-2">
                                <TabsTrigger value="upcoming">Upcoming Games</TabsTrigger>
                                <TabsTrigger value="completed">Completed Games</TabsTrigger>
                            </TabsList>
                            <TabsContent value="upcoming">
                                <Table>
                                    <TableCaption>List of upcoming games for {{ teamName }}</TableCaption>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Date</TableHead>
                                            <TableHead>Time</TableHead>
                                            <TableHead>Home</TableHead>
                                            <TableHead>Away</TableHead>
                                            <TableHead>Location</TableHead>
                                            <TableHead>Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="game in games.filter(g => g.status === 'upcoming')" :key="game.id">
                                            <TableCell>{{ formatDate(game.date) }}</TableCell>
                                            <TableCell>{{ game.time }}</TableCell>
                                            <TableCell>
                                                <span :class="{ 'font-bold': game.homeTeam === teamName }">
                                                    {{ game.homeTeam }}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                <span :class="{ 'font-bold': game.awayTeam === teamName }">
                                                    {{ game.awayTeam }}
                                                </span>
                                            </TableCell>
                                            <TableCell>{{ game.location }}</TableCell>
                                            <TableCell>
                                                <div class="flex gap-2">
                                                    <Button variant="outline" size="sm">Edit</Button>
                                                    <Button variant="destructive" size="sm">Cancel</Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                        <TableRow v-if="games.filter(g => g.status === 'upcoming').length === 0">
                                            <TableCell colspan="6" class="text-center py-6 text-gray-500">
                                                No upcoming games scheduled
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </TabsContent>
                            <TabsContent value="completed">
                                <Table>
                                    <TableCaption>List of completed games for {{ teamName }}</TableCaption>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Date</TableHead>
                                            <TableHead>Result</TableHead>
                                            <TableHead>Score</TableHead>
                                            <TableHead>Location</TableHead>
                                            <TableHead>Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="game in games.filter(g => g.status === 'completed')" :key="game.id">
                                            <TableCell>{{ formatDate(game.date) }}</TableCell>
                                            <TableCell>
                                                <div>
                                                    <div :class="{ 'font-bold': game.homeTeam === teamName }">
                                                        {{ game.homeTeam }}
                                                    </div>
                                                    <div>vs</div>
                                                    <div :class="{ 'font-bold': game.awayTeam === teamName }">
                                                        {{ game.awayTeam }}
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge :variant="
                                                    (game.homeTeam === teamName && game.score.home > game.score.away) || 
                                                    (game.awayTeam === teamName && game.score.away > game.score.home) 
                                                        ? 'default' 
                                                        : 'destructive'
                                                ">
                                                    {{ game.score.home }} - {{ game.score.away }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>{{ game.location }}</TableCell>
                                            <TableCell>
                                                <div class="flex gap-2">
                                                    <Button variant="outline" size="sm">View Details</Button>
                                                    <Button variant="outline" size="sm">Edit</Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </TabsContent>
                        </Tabs>
                    </CardContent>
                </Card>
                
                <!-- Multi-step Game Form Dialog -->
                <Dialog v-model:open="showGameForm">
                    <DialogContent class="sm:max-w-[600px]">
                        <DialogHeader>
                            <DialogTitle>Add New Game</DialogTitle>
                            <DialogDescription>
                                Step {{ currentStep }} of {{ totalSteps }}: 
                                <span v-if="currentStep === 1">Game Details</span>
                                <span v-else-if="currentStep === 2">Select Players</span>
                                <span v-else-if="currentStep === 3">Player Statistics</span>
                                <span v-else>Additional Information</span>
                            </DialogDescription>
                        </DialogHeader>
                        
                        <!-- Step 1: Game Details -->
                        <div v-if="currentStep === 1" class="space-y-4">
                            <FormItem>
                                <FormLabel>Game Date</FormLabel>
                                <FormControl>
                                    <Input type="date" v-model="formState.gameInfo.date" />
                                </FormControl>
                            </FormItem>
                            
                            <FormItem>
                                <FormLabel>Game Time</FormLabel>
                                <FormControl>
                                    <Input type="time" v-model="formState.gameInfo.time" />
                                </FormControl>
                            </FormItem>
                            
                            <FormItem>
                                <FormLabel>Location</FormLabel>
                                <FormControl>
                                    <Input v-model="formState.gameInfo.location" placeholder="Arena/venue name" />
                                </FormControl>
                            </FormItem>
                            
                            <FormItem>
                                <FormLabel>Home Team</FormLabel>
                                <FormControl>
                                    <Input v-model="formState.gameInfo.homeTeam" disabled />
                                </FormControl>
                            </FormItem>
                            
                            <FormItem>
                                <FormLabel>Away Team</FormLabel>
                                <FormControl>
                                    <Select v-model="formState.gameInfo.awayTeam">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select opposing team" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="team in opposingTeams" :key="team" :value="team">
                                                {{ team }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </FormControl>
                            </FormItem>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <FormItem>
                                    <FormLabel>Home Score</FormLabel>
                                    <FormControl>
                                        <Input type="number" v-model="formState.gameInfo.homeScore" min="0" />
                                    </FormControl>
                                </FormItem>
                                
                                <FormItem>
                                    <FormLabel>Away Score</FormLabel>
                                    <FormControl>
                                        <Input type="number" v-model="formState.gameInfo.awayScore" min="0" />
                                    </FormControl>
                                </FormItem>
                            </div>
                        </div>
                        
                        <!-- Step 2: Player Selection -->
                        <div v-else-if="currentStep === 2" class="space-y-4">
                            <div class="mb-4">Select players who participated in this game:</div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div v-for="player in availablePlayers" :key="player.id" class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :id="`player-${player.id}`"
                                        :value="player.id"
                                        v-model="formState.selectedPlayers"
                                        class="mr-2 h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                    />
                                    <label :for="`player-${player.id}`" class="text-sm">
                                        #{{ player.number }} {{ player.name }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 3: Player Statistics -->
                        <div v-else-if="currentStep === 3" class="space-y-4">
                            <div class="mb-4">Enter statistics for each player:</div>
                            
                            <div v-for="(stat, index) in formState.playerStats" :key="stat.playerId" class="mb-6 border-b pb-6 last:border-b-0">
                                <div class="mb-2 font-medium">#{{ stat.playerNumber }} {{ stat.playerName }}</div>
                                
                                <div class="grid grid-cols-3 gap-4">
                                    <FormItem>
                                        <FormLabel>Goals</FormLabel>
                                        <FormControl>
                                            <Input type="number" v-model="formState.playerStats[index].goals" min="0" />
                                        </FormControl>
                                    </FormItem>
                                    
                                    <FormItem>
                                        <FormLabel>Assists</FormLabel>
                                        <FormControl>
                                            <Input type="number" v-model="formState.playerStats[index].assists" min="0" />
                                        </FormControl>
                                    </FormItem>
                                    
                                    <FormItem>
                                        <FormLabel>Penalties</FormLabel>
                                        <FormControl>
                                            <Input type="number" v-model="formState.playerStats[index].penalties" min="0" />
                                        </FormControl>
                                    </FormItem>
                                </div>
                            </div>
                            
                            <div v-if="formState.playerStats.length === 0" class="text-center py-4 text-gray-500">
                                No players selected. Go back to select players.
                            </div>
                        </div>
                        
                        <!-- Step 4: Video URL and Notes -->
                        <div v-else class="space-y-4">
                            <FormItem>
                                <FormLabel>YouTube Video URL</FormLabel>
                                <FormControl>
                                    <Input v-model="formState.videoUrl" placeholder="https://youtube.com/watch?v=..." />
                                </FormControl>
                                <FormDescription>
                                    Link to the game video for review and clip creation
                                </FormDescription>
                            </FormItem>
                            
                            <FormItem>
                                <FormLabel>Game Notes</FormLabel>
                                <FormControl>
                                    <Textarea v-model="formState.notes" placeholder="Additional notes about the game..." rows="4" />
                                </FormControl>
                            </FormItem>
                        </div>
                        
                        <DialogFooter>
                            <div class="flex justify-between w-full">
                                <Button 
                                    v-if="currentStep > 1" 
                                    variant="outline" 
                                    @click="prevStep"
                                >
                                    Back
                                </Button>
                                <div v-else></div>
                                
                                <div>
                                    <Button 
                                        v-if="currentStep < totalSteps" 
                                        @click="nextStep"
                                    >
                                        Continue
                                    </Button>
                                    <Button 
                                        v-else 
                                        @click="submitGame"
                                    >
                                        Save Game
                                    </Button>
                                </div>
                            </div>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 