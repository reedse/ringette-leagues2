<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';

const props = defineProps({
    team: Object,
    currentPlayerIds: Array,
});

const searchQuery = ref('');
const searching = ref(false);
const searchResults = ref([]);
const noResults = ref(false);
const selectedPlayer = ref(null);
const showConfirmModal = ref(false);

const addPlayerForm = useForm({
    player_id: null,
});

const searchPlayers = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        noResults.value = false;
        return;
    }

    searching.value = true;
    noResults.value = false;

    try {
        const response = await axios.post(route('coach.team.search-players'), {
            search: searchQuery.value,
            exclude: props.currentPlayerIds || [],
        });
        
        searchResults.value = response.data.players;
        noResults.value = searchResults.value.length === 0;
    } catch (error) {
        console.error('Error searching players:', error);
        searchResults.value = [];
    } finally {
        searching.value = false;
    }
};

const selectPlayer = (player) => {
    selectedPlayer.value = player;
    showConfirmModal.value = true;
};

const addPlayer = () => {
    addPlayerForm.player_id = selectedPlayer.value.id;
    addPlayerForm.post(route('coach.team.add-player'), {
        onSuccess: () => {
            showConfirmModal.value = false;
        },
    });
};

// Debounce search
let timeout = null;
watch(searchQuery, (newValue) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        searchPlayers();
    }, 300);
});
</script>

<template>
    <Head title="Add Player to Team" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Add Player to Team
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Search for Players</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Search for existing players by name or jersey number to add to {{ team.name }}.
                            </p>
                        </div>

                        <div class="mt-6">
                            <InputLabel for="search" value="Search" />
                            <TextInput
                                id="search"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="searchQuery"
                                placeholder="Enter player name or jersey number"
                                autofocus
                            />
                        </div>

                        <div v-if="searching" class="mt-4 flex justify-center">
                            <svg class="h-6 w-6 animate-spin text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        <div v-else-if="noResults && searchQuery.length >= 2" class="mt-4 rounded-md bg-gray-50 p-4 text-center">
                            <p class="text-gray-700">No players found matching your search.</p>
                        </div>

                        <div v-else-if="searchResults.length > 0" class="mt-4">
                            <div class="flow-root">
                                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                        <table class="min-w-full divide-y divide-gray-300">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Name</th>
                                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Jersey #</th>
                                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Position</th>
                                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                                        <span class="sr-only">Actions</span>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                <tr v-for="player in searchResults" :key="player.id">
                                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">{{ player.first_name }} {{ player.last_name }}</td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ player.jersey_number }}</td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ player.position || 'Not specified' }}</td>
                                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                                        <button @click="selectPlayer(player)" class="text-indigo-600 hover:text-indigo-900">
                                                            Add to Team
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <SecondaryButton :href="route('coach.team')" as="a">
                        Back to Team
                    </SecondaryButton>
                </div>
            </div>
        </div>

        <!-- Confirm add player modal -->
        <Modal :show="showConfirmModal" @close="showConfirmModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Add Player to Team
                </h2>

                <p class="mt-1 text-sm text-gray-600" v-if="selectedPlayer">
                    Are you sure you want to add {{ selectedPlayer.first_name }} {{ selectedPlayer.last_name }} to your team?
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showConfirmModal = false">
                        Cancel
                    </SecondaryButton>

                    <PrimaryButton class="ml-3" :class="{ 'opacity-25': addPlayerForm.processing }" :disabled="addPlayerForm.processing" @click="addPlayer">
                        Add Player
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template> 