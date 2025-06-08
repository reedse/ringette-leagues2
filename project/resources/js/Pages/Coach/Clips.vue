<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    team: Object,
    clips: Array,
});

const page = usePage();
const flashMessages = computed(() => page.props.flash || {});

const showDeleteModal = ref(false);
const clipToDelete = ref(null);
const isDeleting = ref(false);

const confirmDelete = (clip) => {
    clipToDelete.value = clip;
    showDeleteModal.value = true;
};

const deleteClip = () => {
    if (!clipToDelete.value || isDeleting.value) return;
    
    isDeleting.value = true;
    
    router.delete(route('coach.clips.destroy', clipToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            clipToDelete.value = null;
            isDeleting.value = false;
        },
        onError: (errors) => {
            console.error('Error deleting clip:', errors);
            isDeleting.value = false;
            // The error will be shown via flash messages from the backend
            // Keep the modal open so user can see the error and try again
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};

const formatDuration = (start, end) => {
    const duration = Math.round(end - start);
    const minutes = Math.floor(duration / 60);
    const seconds = duration % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Helper functions for data consistency
const getTeamName = (team) => {
    if (!team) return 'Unknown Team';
    return team.name || team.team_name || `Team #${team.id}`;
};
</script>

<template>
    <Head title="Manage Clips" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Manage Clips
                </h2>
                <Link
                    v-if="team"
                    :href="route('coach.clips.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Create New Clip
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="flashMessages.success" class="mb-4 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ flashMessages.success }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Error Message -->
                <div v-if="flashMessages.error" class="mb-4 rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ flashMessages.error }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="clips && clips.length === 0" class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No clips created</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new clip from your game footage.</p>
                    <div class="mt-6">
                        <Link
                            v-if="team"
                            :href="route('coach.clips.create')"
                            class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Create New Clip
                        </Link>
                    </div>
                </div>

                <div v-else-if="clips && clips.length > 0" class="mt-8">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="clip in clips" :key="clip.id" class="overflow-hidden rounded-lg bg-white shadow">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 rounded-md bg-blue-500 p-3">
                                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <h3 class="truncate text-lg font-medium text-gray-900">{{ clip.title }}</h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ clip.game && clip.game.home_team ? getTeamName(clip.game.home_team) : 'Unknown Home Team' }} vs 
                                            {{ clip.game && clip.game.away_team ? getTeamName(clip.game.away_team) : 'Unknown Away Team' }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 border-t border-gray-200 pt-4">
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="block font-medium text-gray-500">Created</span>
                                            <span class="block mt-1 text-gray-900">{{ formatDate(clip.created_at) }}</span>
                                        </div>
                                        <div>
                                            <span class="block font-medium text-gray-500">Duration</span>
                                            <span class="block mt-1 text-gray-900">{{ formatDuration(clip.start_time, clip.end_time) }}</span>
                                        </div>
                                        <div>
                                            <span class="block font-medium text-gray-500">Players</span>
                                            <span class="block mt-1 text-gray-900" v-if="!clip.players || clip.players.length === 0">
                                                No players
                                            </span>
                                            <div class="mt-1 text-gray-900" v-else>
                                                <span v-for="(player, index) in clip.players" :key="player.id" class="inline-block">
                                                    {{ player.name || player.first_name || `Player #${player.id}` }}{{ index < clip.players.length - 1 ? ', ' : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-6 flex space-x-3">
                                    <Link
                                        :href="route('coach.clips.show', clip.id)"
                                        class="inline-flex flex-1 items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        :href="route('coach.clips.edit', clip.id)"
                                        class="inline-flex flex-1 items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        @click="confirmDelete(clip)"
                                        class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete confirmation modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete Clip
                </h2>

                <p class="mt-1 text-sm text-gray-600" v-if="clipToDelete">
                    Are you sure you want to delete the clip "{{ clipToDelete.title }}"? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showDeleteModal = false">
                        Cancel
                    </SecondaryButton>

                    <DangerButton 
                        class="ml-3" 
                        @click="deleteClip"
                        :disabled="isDeleting"
                    >
                        <span v-if="isDeleting" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Deleting...
                        </span>
                        <span v-else>Delete Clip</span>
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template> 