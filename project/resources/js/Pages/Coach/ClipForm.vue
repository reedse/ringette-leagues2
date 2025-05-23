<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch, reactive } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

// Import shadcn-vue components
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/Components/ui/dialog";
import { Textarea } from "@/Components/ui/textarea";
import { Checkbox } from "@/Components/ui/checkbox";
import { Alert, AlertTitle, AlertDescription } from "@/Components/ui/alert";
import { Separator } from "@/Components/ui/separator";
import { ScrollArea } from "@/Components/ui/scroll-area";
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from "@/Components/ui/form";

const props = defineProps({
    team: Object,
    game: Object,
    recentGames: Array,
    players: Array,
    clip: Object,
    selectedPlayers: Array,
    isEditing: {
        type: Boolean,
        default: false,
    },
    debugInfo: Object,
});

// Debug: Log props on mount
onMounted(() => {
    console.log('ClipForm mounted with props:', {
        teamId: props.team?.id,
        teamName: props.team?.name,
        gameId: props.game?.id,
        recentGamesCount: props.recentGames?.length,
        playersCount: props.players?.length,
        isEditing: props.isEditing,
        debugInfo: props.debugInfo
    });
    
    if (props.recentGames?.length === 0) {
        console.log('No recent games found.');
    } else {
        console.log('Recent games:', props.recentGames);
        
        // Check the first game's structure
        const firstGame = props.recentGames[0];
        console.log('First game structure:', {
            id: firstGame.id,
            homeTeamId: firstGame.home_team_id,
            awayTeamId: firstGame.away_team_id,
            homeTeam: firstGame.homeTeam,
            awayTeam: firstGame.awayTeam,
            gameDateTime: firstGame.game_date_time,
            scheduledDate: firstGame.scheduled_date,
            videoUrl: firstGame.video_url,
            dateField: getGameDateField(firstGame)
        });
    }

    // Initialize players in the form if editing
    if (props.isEditing && props.selectedPlayers && props.selectedPlayers.length > 0) {
        form.players = props.selectedPlayers;
        console.log('Initialized form players from props:', form.players);
    }
});

// Helper to get the appropriate date field from a game object
const getGameDateField = (game) => {
    if (!game) return null;
    
    // Check possible date field names
    const dateFields = ['game_date_time', 'scheduled_date', 'date', 'game_date'];
    
    for (const field of dateFields) {
        if (game[field]) {
            return game[field];
        }
    }
    
    return null;
};

// Format game date with fallbacks
const formatGameDate = (game) => {
    if (!game) return '';
    
    const dateValue = getGameDateField(game);
    if (!dateValue) return 'No date available';
    
    try {
        return new Date(dateValue).toLocaleDateString();
    } catch (e) {
        console.error('Error formatting date:', e);
        return 'Invalid date';
    }
};

// Get team name with fallbacks
const getTeamName = (team) => {
    if (!team) return 'Unknown Team';
    return team.name || team.team_name || `Team #${team.id}`;
};

const showGameSelectModal = ref(false);
const selectedGame = ref(props.game || null);
const selectedPlayerIds = ref([]);
const playerNotes = ref({});
const formError = ref('');
const isSubmitting = ref(false);

// Initialize player notes if editing
if (props.isEditing && props.selectedPlayers) {
    props.selectedPlayers.forEach(player => {
        selectedPlayerIds.value.push(player.id);
        if (player.note) {
            playerNotes.value[player.id] = player.note;
        }
    });
    console.log('Initialized selectedPlayerIds from props:', selectedPlayerIds.value);
}

const form = useForm({
    game_id: props.game ? props.game.id : '',
    title: props.clip ? props.clip.title : '',
    description: props.clip ? props.clip.description : '',
    start_time: props.clip ? props.clip.start_time : '',
    end_time: props.clip ? props.clip.end_time : '',
    players: [], // Start with empty array, we'll update it reactively
});

const formTitle = computed(() => {
    return props.isEditing ? "Edit Clip" : "Create New Clip";
});

const videoUrl = computed(() => {
    if (selectedGame.value && selectedGame.value.video_url) {
        //return selectedGame.value.video_url;
        // Hardcoded video URL for preview, dont change this, keep as is for now
        return "https://www.youtube.com/watch?v=4sEF4e3_O7s";
    }
    
});

// Extract YouTube video ID from URL
const youtubeVideoId = computed(() => {
    if (!videoUrl.value) return null;
    
    const url = videoUrl.value;
    const regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
    const match = url.match(regExp);
    
    return (match && match[7].length === 11) ? match[7] : null;
});

// YouTube embedded URL
const youtubeEmbedUrl = computed(() => {
    if (!youtubeVideoId.value) return null;
    return `https://www.youtube.com/embed/${youtubeVideoId.value}`;
});

const selectGame = (game) => {
    console.log('Selecting game:', game);
    selectedGame.value = game;
    form.game_id = game.id;
    showGameSelectModal.value = false;
};

const togglePlayer = (playerId) => {
    console.log('Toggle player called for ID:', playerId);
    console.log('Before toggle, selectedPlayerIds:', [...selectedPlayerIds.value]);
    
    // Convert to Number if it's a string (from HTML elements)
    playerId = Number(playerId);
    
    const index = selectedPlayerIds.value.indexOf(playerId);
    if (index === -1) {
        // Add player
        selectedPlayerIds.value = [...selectedPlayerIds.value, playerId];
    } else {
        // Remove player
        selectedPlayerIds.value = selectedPlayerIds.value.filter(id => id !== playerId);
    }
    
    console.log('After toggle, selectedPlayerIds:', [...selectedPlayerIds.value]);
    
    // Update the form data immediately when players are selected/deselected
    updateFormPlayers();
};

const updatePlayerNote = (playerId, note) => {
    playerNotes.value[playerId] = note;
    // Update form when player notes change
    updateFormPlayers();
};

// Helper to update the form's players data
const updateFormPlayers = () => {
    // Create a copy of the array to ensure proper reactivity
    const playerData = selectedPlayerIds.value.map(id => ({
        id,
        note: playerNotes.value[id] || '',
    }));
    
    // Set the form data with the new array
    form.players = playerData;
    
    console.log('Form players updated:', JSON.stringify(form.players));
};

// Watch for changes in selectedPlayerIds and update form
watch(selectedPlayerIds, (newVal) => {
    console.log('selectedPlayerIds changed:', newVal);
    updateFormPlayers();
}, { deep: true });

const submitForm = () => {
    // Make sure the players data is up to date
    updateFormPlayers();
    // Reset error state
    formError.value = '';
    form.errors = {};
    isSubmitting.value = true;

    console.log('Submitting form with data:', {
        game_id: form.game_id,
        title: form.title,
        description: form.description,
        start_time: form.start_time,
        end_time: form.end_time,
        players: JSON.stringify(form.players),
        videoUrl: videoUrl.value,
        playerCount: selectedPlayerIds.value.length
    });

    // Check if players are selected - show detailed info
    console.log('Selected players check:', {
        selectedPlayerIds: JSON.stringify(selectedPlayerIds.value),
        length: selectedPlayerIds.value.length,
        isEmpty: selectedPlayerIds.value.length === 0,
        formPlayers: JSON.stringify(form.players),
        formPlayersLength: form.players.length
    });
    
    // Only check the form.players array since that's what we're submitting
    if (selectedPlayerIds.value.length === 0) {
        alert('Please select at least one player');
        return;
    }

    // Ensure form.players is correctly populated
    if (form.players.length === 0 && selectedPlayerIds.value.length > 0) {
        console.log('Fixing players data before submission');
        updateFormPlayers();
    }

    const handleSuccess = (page) => {
        console.log('Success response:', page);
        // Check if there are success messages in the flash data
        if (page?.props?.flash?.success) {
            console.log('Success message:', page.props.flash.success);
        }
    };

    const handleError = (errors) => {
        console.error('Form submission errors:', errors);
        // Display first error message as the form error
        if (typeof errors === 'object' && Object.keys(errors).length > 0) {
            formError.value = Object.values(errors)[0];
        }
    };

    if (props.isEditing) {
        form.put(route('coach.clips.update', props.clip.id), {
            onSuccess: (page) => {
                handleSuccess(page);
                isSubmitting.value = false;
            },
            onError: (errors) => {
                console.error('Form update errors:', errors);
                isSubmitting.value = false;
                
                // Display first error message as the form error
                if (typeof errors === 'object' && Object.keys(errors).length > 0) {
                    formError.value = Object.values(errors)[0];
                } else if (typeof errors === 'string') {
                    formError.value = errors;
                } else {
                    formError.value = 'An unexpected error occurred during update.';
                }
            },
            forceFormData: true
        });
    } else {
        // Ensure the players data is in the correct format for the server
        console.log('Converting players array before submission:', JSON.stringify(form.players));
        
        // Manual post to ensure correct format
        const formData = new FormData();
        formData.append('game_id', form.game_id);
        formData.append('title', form.title);
        formData.append('description', form.description || '');
        formData.append('start_time', form.start_time);
        formData.append('end_time', form.end_time);
        
        // Add each player as a separate entry
        form.players.forEach((player, index) => {
            formData.append(`players[${index}][id]`, player.id);
            formData.append(`players[${index}][note]`, player.note || '');
        });
        
        // Post using axios directly
        axios.post(route('coach.clips.store'), formData)
            .then(response => {
                console.log('Clip created successfully:', response);
                isSubmitting.value = false;
                if (response.data.redirect) {
                    window.location.href = response.data.redirect;
                } else {
                    window.location.href = route('coach.clips');
                }
            })
            .catch(error => {
                console.error('Error creating clip:', error);
                isSubmitting.value = false;
                
                // Handle different types of errors
                if (error.response) {
                    // Server responded with error
                    if (error.response.data && error.response.data.errors) {
                        // Laravel validation errors
                        form.errors = error.response.data.errors;
                        handleError(error.response.data.errors);
                    } else if (error.response.data && error.response.data.message) {
                        // Server error message 
                        formError.value = error.response.data.message;
                        
                        // Check for common database errors
                        if (error.response.data.message.includes('Database error') || 
                            error.response.data.message.includes('column') || 
                            error.response.data.message.toLowerCase().includes('sql')) {
                            console.error('Database error detected:', error.response.data);
                            formError.value = 'Database error: There appears to be an issue with the database schema. Please contact support.';
                        }
                    } else {
                        // Other HTTP errors
                        formError.value = `Server error: ${error.response.status} ${error.response.statusText}`;
                    }
                } else if (error.request) {
                    // Request made but no response
                    formError.value = 'No response from server. Please check your connection and try again.';
                } else {
                    // Error setting up request
                    formError.value = `Error: ${error.message}`;
                }
            });
    }
};
</script>

<template>
    <Head :title="formTitle" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ formTitle }}
                </h2>
                <Link :href="route('coach.clips')">
                    <Button variant="outline">Back to Clips</Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-6">
                        <form @submit.prevent="submitForm">
                            <!-- Error alert -->
                            <Alert v-if="formError" variant="destructive" class="mb-6">
                                <AlertTitle>Error</AlertTitle>
                                <AlertDescription>
                                    <div>{{ formError }}</div>
                                    
                                    <!-- If we have multiple validation errors, show them in a list -->
                                    <ul v-if="Object.keys(form.errors).length > 1" class="mt-2 list-disc list-inside text-sm">
                                        <li v-for="(error, field) in form.errors" :key="field">
                                            <strong>{{ field.charAt(0).toUpperCase() + field.slice(1).replace('_', ' ') }}:</strong> {{ error }}
                                        </li>
                                    </ul>
                                </AlertDescription>
                            </Alert>
                            
                            <!-- Game Selection -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Step 1: Select Game</h3>
                                
                                <div v-if="!selectedGame" class="rounded-md bg-muted p-6 text-center">
                                    <p class="text-muted-foreground mb-4">Select a game to create a clip from.</p>
                                    <Button type="button" @click="showGameSelectModal = true">
                                        Select Game
                                    </Button>
                                </div>

                                <Card v-else>
                                    <CardContent class="p-4">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <h4 class="font-medium">
                                                    {{ selectedGame.homeTeam ? getTeamName(selectedGame.homeTeam) : 'Home Team' }} 
                                                    vs 
                                                    {{ selectedGame.awayTeam ? getTeamName(selectedGame.awayTeam) : 'Away Team' }}
                                                </h4>
                                                <p class="text-sm text-muted-foreground">{{ formatGameDate(selectedGame) }}</p>
                                            </div>
                                            
                                            <Button variant="ghost" type="button" @click="showGameSelectModal = true" class="text-primary">
                                                Change
                                            </Button>
                                        </div>
                                        
                                        <Alert v-if="!videoUrl" variant="warning" class="mt-4">
                                            <AlertDescription>
                                                This game doesn't have a video URL. Please add one in the game details before creating clips.
                                            </AlertDescription>
                                        </Alert>
                                    </CardContent>
                                </Card>
                            </div>

                            <!-- Video Preview -->
                            <div v-if="selectedGame && youtubeEmbedUrl" class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Game Video</h3>
                                <Card>
                                    <CardContent class="p-0 overflow-hidden aspect-video">
                                        <iframe 
                                            :src="youtubeEmbedUrl" 
                                            class="w-full h-full" 
                                            frameborder="0" 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                            allowfullscreen>
                                        </iframe>
                                    </CardContent>
                                </Card>
                            </div>

                            <!-- Clip Details -->
                            <div class="mb-8" :class="{ 'opacity-50 pointer-events-none': !selectedGame || !videoUrl }">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Step 2: Clip Details</h3>
                                
                                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                    <div class="sm:col-span-6">
                                        <div>
                                            <Label for="title">Clip Title</Label>
                                            <Input
                                                id="title"
                                                type="text"
                                                v-model="form.title"
                                                required
                                                :disabled="!selectedGame || !videoUrl"
                                                class="mt-1"
                                            />
                                            <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-6">
                                        <div>
                                            <Label for="description">Description (Optional)</Label>
                                            <Textarea
                                                id="description"
                                                v-model="form.description"
                                                rows="3"
                                                :disabled="!selectedGame || !videoUrl"
                                                class="mt-1"
                                            />
                                            <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <div>
                                            <Label for="start_time">Start Time (seconds)</Label>
                                            <Input
                                                id="start_time"
                                                type="number"
                                                min="0"
                                                step="0.1"
                                                v-model="form.start_time"
                                                required
                                                :disabled="!selectedGame || !videoUrl"
                                                class="mt-1"
                                            />
                                            <div v-if="form.errors.start_time" class="text-red-500 text-sm mt-1">{{ form.errors.start_time }}</div>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <div>
                                            <Label for="end_time">End Time (seconds)</Label>
                                            <Input
                                                id="end_time"
                                                type="number"
                                                min="0"
                                                step="0.1"
                                                v-model="form.end_time"
                                                required
                                                :disabled="!selectedGame || !videoUrl"
                                                class="mt-1"
                                            />
                                            <div v-if="form.errors.end_time" class="text-red-500 text-sm mt-1">{{ form.errors.end_time }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Player Selection -->
                            <div class="mb-8" :class="{ 'opacity-50 pointer-events-none': !selectedGame || !videoUrl }">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Step 3: Select Players</h3>
                                
                                <div>
                                    <div v-if="form.errors.players" class="mb-2 text-red-500 text-sm">{{ form.errors.players }}</div>
                                    <Card>
                                        <CardContent class="p-0">
                                            <ScrollArea className="h-[400px]">
                                                <div class="space-y-0">
                                                    <div v-for="player in players" :key="player.id" 
                                                        class="relative border-b border-border py-4 px-4">
                                                        <div class="flex items-start">
                                                            <div class="flex h-5 items-center">
                                                                <input 
                                                                    type="checkbox"
                                                                    :id="`player-${player.id}`"
                                                                    :checked="selectedPlayerIds.includes(player.id)"
                                                                    @change="togglePlayer(player.id)"
                                                                    :disabled="!selectedGame || !videoUrl"
                                                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                                                />
                                                            </div>
                                                            <div class="ml-3 text-sm w-full">
                                                                <Label :for="`player-${player.id}`" class="font-medium">
                                                                    {{ player.first_name }} {{ player.last_name }} (#{{ player.jersey_number }})
                                                                </Label>
                                                                
                                                                <div v-if="selectedPlayerIds.includes(player.id)" class="mt-2">
                                                                    <Label :for="`note-${player.id}`" class="text-xs">Note for this player:</Label>
                                                                    <Textarea
                                                                        :id="`note-${player.id}`"
                                                                        :value="playerNotes[player.id] || ''"
                                                                        @input="updatePlayerNote(player.id, $event.target.value)"
                                                                        rows="2"
                                                                        class="mt-1"
                                                                        :disabled="!selectedGame || !videoUrl"
                                                                        placeholder="Add specific notes for this player..."
                                                                    />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </ScrollArea>
                                        </CardContent>
                                    </Card>
                                </div>
                                
                                <!-- Debug information for selected players -->
                                <div class="mt-3 p-3 bg-gray-100 text-xs rounded">
                                    <div>Selected player IDs: {{ selectedPlayerIds }}</div>
                                    <div>Number of selected players: {{ selectedPlayerIds.length }}</div>
                                    <div>Players in form data: {{ form.players.length }}</div>
                                    
                                    <!-- Manual player selection -->
                                    <div class="mt-3 pt-3 border-t border-gray-300">
                                        <div class="font-bold mb-2">Manual Player Selection:</div>
                                        <div class="flex flex-wrap gap-2">
                                            <div v-for="player in players" :key="`manual-${player.id}`" class="flex items-center">
                                                <button 
                                                    type="button"
                                                    @click="togglePlayer(player.id)"
                                                    class="px-2 py-1 text-xs rounded"
                                                    :class="selectedPlayerIds.includes(player.id) 
                                                        ? 'bg-primary text-white' 
                                                        : 'bg-gray-200 text-gray-800'"
                                                >
                                                    {{ player.first_name }} {{ player.last_name }} (#{{ player.jersey_number }})
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Force update button -->
                                    <div class="mt-3">
                                        <button 
                                            type="button" 
                                            @click="updateFormPlayers()" 
                                            class="px-2 py-1 bg-blue-500 text-white rounded text-xs"
                                        >
                                            Force Update Players
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="selectedPlayerIds = []" 
                                            class="px-2 py-1 bg-red-500 text-white rounded text-xs ml-2"
                                        >
                                            Clear All
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="selectedPlayerIds = players.map(p => p.id)" 
                                            class="px-2 py-1 bg-green-500 text-white rounded text-xs ml-2"
                                        >
                                            Select All
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <Link :href="route('coach.clips')">
                                    <Button variant="outline" type="button">Cancel</Button>
                                </Link>
                                <Button 
                                    type="submit"
                                    :disabled="form.processing || !selectedGame || !videoUrl || isSubmitting">
                                    <span v-if="isSubmitting">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ props.isEditing ? 'Updating...' : 'Creating...' }}
                                    </span>
                                    <span v-else>
                                        {{ props.isEditing ? 'Update Clip' : 'Create Clip' }}
                                    </span>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Game Selection Dialog -->
        <Dialog :open="showGameSelectModal" @update:open="showGameSelectModal = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Select Game</DialogTitle>
                </DialogHeader>
                
                <ScrollArea className="h-[400px] mt-2">
                    <div class="space-y-2">
                        <Card v-for="(game, index) in recentGames" :key="game.id || index" 
                              class="cursor-pointer hover:bg-muted transition-colors"
                              @click="selectGame(game)">
                            <CardContent class="p-4">
                                <div v-if="game && (game.homeTeam || game.home_team_id)">
                                    <h3 class="font-medium">
                                        {{ game.homeTeam ? getTeamName(game.homeTeam) : 'Home Team' }} 
                                        vs 
                                        {{ game.awayTeam ? getTeamName(game.awayTeam) : 'Away Team' }}
                                    </h3>
                                    <p class="text-sm text-muted-foreground">{{ formatGameDate(game) }}</p>
                                    <div class="flex items-center mt-2">
                                        <div class="w-2 h-2 rounded-full mr-2 bg-green-500"></div>
                                        <span class="text-xs text-muted-foreground">
                                            Video available
                                        </span>
                                    </div>
                                </div>
                                <div v-else class="p-2 text-sm text-red-500">
                                    <p>Game data issue (see console)</p>
                                    <pre class="text-xs mt-1 overflow-auto max-h-20">{{ JSON.stringify(game, null, 2) }}</pre>
                                </div>
                            </CardContent>
                        </Card>
                        <div v-if="recentGames.length === 0" class="p-4 text-center">
                            <p class="text-muted-foreground">No games with video found.</p>
                            <p class="text-sm text-muted-foreground mt-2">Add video URLs to your games to create clips.</p>
                            
                            <!-- Debug Information -->
                            <Alert variant="destructive" class="mt-4">
                                <AlertTitle>Debug Information</AlertTitle>
                                <AlertDescription>
                                    <div class="text-left">
                                        <p>Team ID: {{ team?.id }}</p>
                                        <p>Team Name: {{ team?.name }}</p>
                                        <p>Games Array: {{ Array.isArray(recentGames) ? 'Yes' : 'No' }}</p>
                                        <p>Games Count: {{ recentGames?.length }}</p>
                                        <div v-if="debugInfo" class="mt-3 pt-3 border-t border-destructive">
                                            <p>Total Games in DB: {{ debugInfo.totalGames }}</p>
                                            <p>Any Team Games: {{ debugInfo.hasTeamGames ? 'Yes' : 'No' }}</p>
                                            <p>Any Games With Video: {{ debugInfo.hasGamesWithVideo ? 'Yes' : 'No' }}</p>
                                            <p>Debug Mode: {{ debugInfo.debugMode ? 'Enabled' : 'Disabled' }}</p>
                                            <div v-if="!debugInfo.debugMode" class="mt-2">
                                                <p class="text-xs">Add ?debug_mode=1 to URL to test with fake data</p>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <p class="font-semibold">Possible Issues:</p>
                                            <ul class="list-disc ml-5 mt-1">
                                                <li v-if="!debugInfo?.totalGames">No games in database</li>
                                                <li v-else-if="!debugInfo?.hasTeamGames">No games associated with this team</li>
                                                <li v-else-if="!debugInfo?.hasGamesWithVideo">No games have video URLs</li>
                                                <li v-else>No games for this team have video URLs</li>
                                            </ul>
                                        </div>
                                    </div>
                                </AlertDescription>
                            </Alert>
                        </div>
                        <div v-else-if="recentGames.length > 0" class="p-4 text-center">
                            <Alert variant="info" class="mt-4">
                                <AlertTitle>Debug - Games Found</AlertTitle>
                                <AlertDescription>
                                    <p>{{ recentGames.length }} games found</p>
                                    <Button size="sm" variant="outline" class="mt-2" 
                                            @click="console.log('Recent games:', recentGames)">
                                        Log games to console
                                    </Button>
                                </AlertDescription>
                            </Alert>
                        </div>
                    </div>
                </ScrollArea>
                
                <DialogFooter>
                    <Button variant="outline" @click="showGameSelectModal = false">Cancel</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template> 