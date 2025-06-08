<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    clip: Object,
});

const formatDuration = (start, end) => {
    if (!start || !end) return 'N/A';
    const duration = Math.round(end - start);
    const minutes = Math.floor(duration / 60);
    const seconds = duration % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        console.error('Error formatting date:', error);
        return 'Invalid Date';
    }
};

// Helper function to safely get team names
const getTeamName = (team) => {
    if (!team) return 'Unknown Team';
    return team.name || team.team_name || `Team #${team.id}`;
};

// Helper function to safely get game info
const getGameInfo = computed(() => {
    if (!props.clip || !props.clip.game) {
        return 'Game information not available';
    }
    
    const game = props.clip.game;
    // Use snake_case property names as that's how Laravel serializes relationships
    const homeTeam = getTeamName(game.home_team);
    const awayTeam = getTeamName(game.away_team);
    
    return `${homeTeam} vs ${awayTeam}`;
});

const videoUrl = computed(() => {
    if (!props.clip || !props.clip.video_url) return null;
    
    // Handle YouTube links
    if (props.clip.video_url.includes('youtube.com') || props.clip.video_url.includes('youtu.be')) {
        let videoId = '';
        
        if (props.clip.video_url.includes('v=')) {
            videoId = props.clip.video_url.split('v=')[1];
            const ampersandPosition = videoId.indexOf('&');
            if (ampersandPosition !== -1) {
                videoId = videoId.substring(0, ampersandPosition);
            }
        } else if (props.clip.video_url.includes('youtu.be/')) {
            videoId = props.clip.video_url.split('youtu.be/')[1];
        }
        
        if (videoId && props.clip.start_time !== null && props.clip.end_time !== null) {
            const startTime = Math.round(props.clip.start_time);
            return `https://www.youtube.com/embed/${videoId}?start=${startTime}&end=${Math.round(props.clip.end_time)}&autoplay=1`;
        }
    }
    
    // Default to original URL if not YouTube or unsupported format
    return props.clip.video_url;
});
</script>

<template>
    <Head :title="`Clip: ${clip?.title || 'Clip Details'}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Clip Details
                </h2>
                <div class="flex space-x-4">
                    <Link
                        v-if="clip?.id"
                        :href="route('coach.clips.edit', clip.id)"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Edit Clip
                    </Link>
                    <Link
                        :href="route('coach.clips')"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Back to Clips
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Error handling for missing data -->
                <div v-if="!clip" class="mb-4 p-4 border border-red-300 bg-red-50 rounded-md">
                    <h3 class="text-red-800 font-medium">Error: No clip data received</h3>
                    <p class="text-red-600 text-sm">The clip could not be loaded. Please try again or contact support.</p>
                </div>
                
                <div v-else-if="!clip.id" class="mb-4 p-4 border border-yellow-300 bg-yellow-50 rounded-md">
                    <h3 class="text-yellow-800 font-medium">Error: Invalid clip data</h3>
                    <p class="text-yellow-600 text-sm">The clip data appears to be corrupted. Please try again.</p>
                </div>

                <div v-if="clip" class="overflow-hidden bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">{{ clip.title || 'Untitled Clip' }}</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            {{ getGameInfo }} | {{ formatDate(clip.created_at) }}
                        </p>
                    </div>
                    
                    <!-- Video Player -->
                    <div class="border-t border-gray-200">
                        <div class="aspect-w-16 aspect-h-9" style="position: relative; width: 100%; height: 0; padding-bottom: 56.25%;">
                            <iframe 
                                v-if="videoUrl"
                                :src="videoUrl" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen
                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
                            ></iframe>
                            <div v-else-if="clip.video_url" class="flex items-center justify-center bg-gray-100 text-gray-500" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                                <div class="text-center">
                                    <p>Video URL not supported for embedding</p>
                                    <a :href="clip.video_url" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                                        Open in new tab
                                    </a>
                                </div>
                            </div>
                            <div v-else class="flex items-center justify-center bg-gray-100 text-gray-500" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                                No video URL available
                            </div>
                        </div>
                    </div>
                    
                    <!-- Clip Details -->
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <h3 class="text-sm font-medium text-gray-500">Description</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ clip.description || 'No description provided' }}</p>
                            </div>
                            <div class="sm:col-span-1">
                                <h3 class="text-sm font-medium text-gray-500">Duration</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDuration(clip.start_time, clip.end_time) }}</p>
                            </div>
                            <div class="sm:col-span-1">
                                <h3 class="text-sm font-medium text-gray-500">Start Time</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ clip.start_time ?? 'N/A' }}s</p>
                            </div>
                            <div class="sm:col-span-1">
                                <h3 class="text-sm font-medium text-gray-500">End Time</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ clip.end_time ?? 'N/A' }}s</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Players -->
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900">Shared with Players</h3>
                        
                        <div v-if="!clip.players || clip.players.length === 0" class="mt-4 text-sm text-gray-500">
                            This clip hasn't been shared with any players.
                        </div>
                        
                        <ul v-else class="mt-4 divide-y divide-gray-200">
                            <li v-for="player in clip.players" :key="player.id" class="py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-500 font-medium">{{ player.jersey_number || '?' }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ player.first_name || 'Unknown' }} {{ player.last_name || 'Player' }}
                                        </p>
                                        <p v-if="player.pivot && player.pivot.sent_at" class="text-sm text-gray-500">
                                            Shared on {{ formatDate(player.pivot.sent_at) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div v-if="player.pivot && player.pivot.coach_note" class="mt-2 ml-14">
                                    <h4 class="text-xs font-medium text-gray-500">Coach's Note:</h4>
                                    <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ player.pivot.coach_note }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 