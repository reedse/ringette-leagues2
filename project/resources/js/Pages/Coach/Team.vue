<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

// Import shadcn-vue components
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Separator } from "@/components/ui/separator";

const props = defineProps({
    team: Object,
    players: Array,
    seasons: Array,
    currentSeason: Object,
    error: String,
});

const showRemoveModal = ref(false);
const playerToRemove = ref(null);
const showCreatePlayerModal = ref(false);

const removePlayerForm = useForm({
    player_id: null,
});

const createPlayerForm = useForm({
    first_name: '',
    last_name: '',
    jersey_number: '',
    position: '',
    date_of_birth: '',
});

const confirmRemovePlayer = (player) => {
    playerToRemove.value = player;
    showRemoveModal.value = true;
};

const removePlayer = () => {
    removePlayerForm.player_id = playerToRemove.value.id;
    removePlayerForm.post(route('coach.team.remove-player'), {
        preserveScroll: true,
        onSuccess: () => {
            showRemoveModal.value = false;
            playerToRemove.value = null;
        },
    });
};

const submitNewPlayer = () => {
    createPlayerForm.post(route('coach.team.create-player'), {
        onSuccess: () => {
            showCreatePlayerModal.value = false;
            createPlayerForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Team Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Team Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Alert v-if="error" variant="destructive" class="mb-4">
                    <AlertDescription>{{ error }}</AlertDescription>
                </Alert>

                <Card v-if="team">
                    <CardHeader>
                        <CardTitle>Team Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-3">
                            <div>
                                <Label class="font-medium">Team Name</Label>
                                <p class="mt-1 text-sm">{{ team.name }}</p>
                            </div>
                            <div>
                                <Label class="font-medium">Association</Label>
                                <p class="mt-1 text-sm">{{ team.association?.name }}</p>
                            </div>
                            <div>
                                <Label class="font-medium">League</Label>
                                <p class="mt-1 text-sm">{{ team.league?.name }}</p>
                            </div>
                            <div>
                                <Label class="font-medium">Season</Label>
                                <p class="mt-1 text-sm">{{ team.season?.name }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="team" class="mt-6">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle>Team Roster</CardTitle>
                        <div class="flex space-x-2">
                            <Link :href="route('coach.team.add-player-form')">
                                <Button variant="default">Add Existing Player</Button>
                            </Link>
                            <Button variant="default" @click="showCreatePlayerModal = true">
                                Create New Player
                            </Button>
                        </div>
                    </CardHeader>
                    
                    <CardContent>
                        <div v-if="players.length === 0" class="text-center py-12 bg-muted rounded-md">
                            <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium">No players</h3>
                            <p class="mt-1 text-sm text-muted-foreground">Get started by adding a player to your team.</p>
                        </div>

                        <div v-else>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Jersey #</TableHead>
                                        <TableHead>Position</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="player in players" :key="player.id">
                                        <TableCell class="font-medium">{{ player.first_name }} {{ player.last_name }}</TableCell>
                                        <TableCell>{{ player.jersey_number }}</TableCell>
                                        <TableCell>{{ player.position || 'Not specified' }}</TableCell>
                                        <TableCell class="text-right">
                                            <Button variant="ghost" @click="confirmRemovePlayer(player)" class="text-destructive">
                                                Remove
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Delete player confirmation dialog -->
        <Dialog :open="showRemoveModal" @update:open="showRemoveModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Remove Player from Roster</DialogTitle>
                    <DialogDescription v-if="playerToRemove">
                        Are you sure you want to remove {{ playerToRemove?.first_name }} {{ playerToRemove?.last_name }} from your team roster?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showRemoveModal = false">Cancel</Button>
                    <Button variant="destructive" @click="removePlayer" :disabled="removePlayerForm.processing">
                        Remove Player
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Create player dialog -->
        <Dialog :open="showCreatePlayerModal" @update:open="showCreatePlayerModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Create New Player</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitNewPlayer">
                    <div class="grid gap-4 py-4">
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="first_name" class="text-right">First Name</Label>
                            <Input id="first_name" v-model="createPlayerForm.first_name" class="col-span-3" required />
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="last_name" class="text-right">Last Name</Label>
                            <Input id="last_name" v-model="createPlayerForm.last_name" class="col-span-3" required />
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="jersey_number" class="text-right">Jersey Number</Label>
                            <Input id="jersey_number" v-model="createPlayerForm.jersey_number" class="col-span-3" required />
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="position" class="text-right">Position</Label>
                            <Input id="position" v-model="createPlayerForm.position" class="col-span-3" placeholder="Optional" />
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="date_of_birth" class="text-right">Date of Birth</Label>
                            <Input id="date_of_birth" type="date" v-model="createPlayerForm.date_of_birth" class="col-span-3" placeholder="Optional" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="showCreatePlayerModal = false" type="button">Cancel</Button>
                        <Button type="submit" :disabled="createPlayerForm.processing">Create Player</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template> 