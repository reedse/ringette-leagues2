<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    clip: Object,
});

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
        
        if (videoId) {
            const startTime = Math.round(props.clip.start_time);
            return `https://www.youtube.com/embed/${videoId}?start=${startTime}&end=${Math.round(props.clip.end_time)}&autoplay=1`;
        }
    }
    
    // Default to original URL if not YouTube or unsupported format
    return props.clip.video_url;
});
</script>

<template>
    <Head :title="`Clip: ${clip.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Clip Details
                </h2>
                <div class="flex space-x-4">
                    <Link
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
                <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">{{ clip.title }}</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            {{ clip.game.homeTeam.name }} vs {{ clip.game.awayTeam.name }} | {{ formatDate(clip.created_at) }}
                        </p>
                    </div>
                    
                    <!-- Video Player -->
                    <div class="border-t border-gray-200">
                        <div class="aspect-w-16 aspect-h-9">
                            <iframe 
                                v-if="videoUrl"
                                :src="videoUrl" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen
                                class="w-full h-full"
                            ></iframe>
                            <div v-else class="flex items-center justify-center bg-gray-100 w-full h-full text-gray-500">
                                Video URL not supported for embedding
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
                                <p class="mt-1 text-sm text-gray-900">{{ clip.start_time }}s</p>
                            </div>
                            <div class="sm:col-span-1">
                                <h3 class="text-sm font-medium text-gray-500">End Time</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ clip.end_time }}s</p>
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
                                        <span class="text-gray-500 font-medium">{{ player.jersey_number }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ player.first_name }} {{ player.last_name }}
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