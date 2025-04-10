<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Import shadcn-vue components
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";
import { Separator } from "@/components/ui/separator";
import { ScrollArea } from "@/components/ui/scroll-area";
import { FormItem, FormLabel, FormControl, FormMessage } from "@/components/ui/form";

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
});

const showGameSelectModal = ref(false);
const selectedGame = ref(props.game || null);
const selectedPlayerIds = ref([]);
const playerNotes = ref({});

// Initialize player notes if editing
if (props.isEditing && props.selectedPlayers) {
    props.selectedPlayers.forEach(player => {
        selectedPlayerIds.value.push(player.id);
        if (player.note) {
            playerNotes.value[player.id] = player.note;
        }
    });
}

const form = useForm({
    game_id: props.game ? props.game.id : '',
    title: props.clip ? props.clip.title : '',
    description: props.clip ? props.clip.description : '',
    start_time: props.clip ? props.clip.start_time : '',
    end_time: props.clip ? props.clip.end_time : '',
    players: [],
});

const formTitle = computed(() => {
    return props.isEditing ? "Edit Clip" : "Create New Clip";
});

const videoUrl = computed(() => {
    if (selectedGame.value && selectedGame.value.video_url) {
        return selectedGame.value.video_url;
    }
    return null;
});

const selectGame = (game) => {
    selectedGame.value = game;
    form.game_id = game.id;
    showGameSelectModal.value = false;
};

const togglePlayer = (playerId) => {
    const index = selectedPlayerIds.value.indexOf(playerId);
    if (index === -1) {
        selectedPlayerIds.value.push(playerId);
    } else {
        selectedPlayerIds.value.splice(index, 1);
    }
};

const updatePlayerNote = (playerId, note) => {
    playerNotes.value[playerId] = note;
};

const submitForm = () => {
    // Format player data for form submission
    form.players = selectedPlayerIds.value.map(id => ({
        id,
        note: playerNotes.value[id] || '',
    }));

    if (props.isEditing) {
        form.put(route('coach.clips.update', props.clip.id), {
            preserveScroll: true,
        });
    } else {
        form.post(route('coach.clips.store'), {
            preserveScroll: true,
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
                                                <h4 class="font-medium">{{ selectedGame.homeTeam.name }} vs {{ selectedGame.awayTeam.name }}</h4>
                                                <p class="text-sm text-muted-foreground">{{ new Date(selectedGame.game_date_time).toLocaleDateString() }}</p>
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

                            <!-- Clip Details -->
                            <div class="mb-8" :class="{ 'opacity-50 pointer-events-none': !selectedGame || !videoUrl }">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Step 2: Clip Details</h3>
                                
                                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                    <div class="sm:col-span-6">
                                        <FormItem>
                                            <FormLabel for="title">Clip Title</FormLabel>
                                            <Input
                                                id="title"
                                                type="text"
                                                v-model="form.title"
                                                required
                                                :disabled="!selectedGame || !videoUrl"
                                            />
                                            <FormMessage>{{ form.errors.title }}</FormMessage>
                                        </FormItem>
                                    </div>

                                    <div class="sm:col-span-6">
                                        <FormItem>
                                            <FormLabel for="description">Description (Optional)</FormLabel>
                                            <Textarea
                                                id="description"
                                                v-model="form.description"
                                                rows="3"
                                                :disabled="!selectedGame || !videoUrl"
                                            />
                                            <FormMessage>{{ form.errors.description }}</FormMessage>
                                        </FormItem>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <FormItem>
                                            <FormLabel for="start_time">Start Time (seconds)</FormLabel>
                                            <Input
                                                id="start_time"
                                                type="number"
                                                min="0"
                                                step="0.1"
                                                v-model="form.start_time"
                                                required
                                                :disabled="!selectedGame || !videoUrl"
                                            />
                                            <FormMessage>{{ form.errors.start_time }}</FormMessage>
                                        </FormItem>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <FormItem>
                                            <FormLabel for="end_time">End Time (seconds)</FormLabel>
                                            <Input
                                                id="end_time"
                                                type="number"
                                                min="0"
                                                step="0.1"
                                                v-model="form.end_time"
                                                required
                                                :disabled="!selectedGame || !videoUrl"
                                            />
                                            <FormMessage>{{ form.errors.end_time }}</FormMessage>
                                        </FormItem>
                                    </div>
                                </div>
                            </div>

                            <!-- Player Selection -->
                            <div class="mb-8" :class="{ 'opacity-50 pointer-events-none': !selectedGame || !videoUrl }">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Step 3: Select Players</h3>
                                
                                <Card>
                                    <CardContent class="p-0">
                                        <ScrollArea className="h-[400px]">
                                            <div class="space-y-0">
                                                <div v-for="player in players" :key="player.id" 
                                                     class="relative border-b border-border py-4 px-4">
                                                    <div class="flex items-start">
                                                        <div class="flex h-5 items-center">
                                                            <Checkbox
                                                                :id="`player-${player.id}`"
                                                                :checked="selectedPlayerIds.includes(player.id)"
                                                                @update:checked="togglePlayer(player.id)"
                                                                :disabled="!selectedGame || !videoUrl"
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

                            <div class="flex justify-end space-x-3">
                                <Link :href="route('coach.clips')">
                                    <Button variant="outline" type="button">Cancel</Button>
                                </Link>
                                <Button 
                                    type="submit"
                                    :disabled="form.processing || !selectedGame || !videoUrl">
                                    {{ props.isEditing ? 'Update Clip' : 'Create Clip' }}
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
                        <Card v-for="game in recentGames" :key="game.id" 
                              class="cursor-pointer hover:bg-muted transition-colors"
                              @click="selectGame(game)">
                            <CardContent class="p-4">
                                <h3 class="font-medium">{{ game.homeTeam.name }} vs {{ game.awayTeam.name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ new Date(game.game_date_time).toLocaleDateString() }}</p>
                                <div class="flex items-center mt-2">
                                    <div class="w-2 h-2 rounded-full mr-2" 
                                         :class="game.video_url ? 'bg-green-500' : 'bg-amber-500'"></div>
                                    <span class="text-xs text-muted-foreground">
                                        {{ game.video_url ? 'Video available' : 'No video' }}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </ScrollArea>
                
                <DialogFooter>
                    <Button variant="outline" @click="showGameSelectModal = false">Cancel</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template> 