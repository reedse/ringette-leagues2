<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { Badge } from '@/Components/ui/badge';

const props = defineProps({
    error: String,
    hasSubscription: Boolean,
    clips: Array
});

// For the video player
const activeClip = ref(null);
const showPlayer = ref(false);

const playClip = (clip) => {
    activeClip.value = clip;
    showPlayer.value = true;
};

const closePlayer = () => {
    showPlayer.value = false;
    activeClip.value = null;
};
</script>

<template>
    <Head title="My Clips" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                My Clips
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Error message if no player profile -->
                <Alert v-if="error" variant="destructive" class="mb-6">
                    <AlertDescription>
                        {{ error }}
                    </AlertDescription>
                </Alert>

                <!-- Subscription prompt -->
                <div v-if="!hasSubscription" class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <h3 class="mt-4 text-xl font-bold text-gray-900">Get Access to Video Clips</h3>
                        <p class="mt-2 text-gray-600">
                            Unlock access to video clips shared by your coaches. See your performance, receive feedback, and improve your game.
                        </p>
                        <div class="mt-8">
                            <Button asChild size="lg">
                                <Link :href="route('subscription.show')" class="px-8">Subscribe Now</Link>
                            </Button>
                            <p class="mt-2 text-sm text-gray-500">Includes a 7-day free trial. Cancel anytime.</p>
                        </div>
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="text-center">
                                    <div class="flex justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <h4 class="mt-2 font-medium text-gray-900">Personalized Feedback</h4>
                                    <p class="mt-1 text-sm text-gray-500">Get detailed feedback from your coaches</p>
                                </div>
                                <div class="text-center">
                                    <div class="flex justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <h4 class="mt-2 font-medium text-gray-900">Game Highlights</h4>
                                    <p class="mt-1 text-sm text-gray-500">Watch your best moments from games</p>
                                </div>
                                <div class="text-center">
                                    <div class="flex justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <h4 class="mt-2 font-medium text-gray-900">Skill Improvement</h4>
                                    <p class="mt-1 text-sm text-gray-500">Analyze and improve your performance</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clips library (when subscribed) -->
                <div v-if="hasSubscription">
                    <div v-if="clips && clips.length > 0" class="space-y-6">
                        <h3 class="text-lg font-medium">Your Shared Clips</h3>
                        
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <Card v-for="clip in clips" :key="clip.id" class="overflow-hidden">
                                <div class="relative h-48 bg-gray-200">
                                    <!-- Video thumbnail placeholder -->
                                    <div class="absolute inset-0 flex items-center justify-center bg-gray-800 text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <Button class="absolute bottom-2 right-2" size="sm" @click="playClip(clip)">Play Clip</Button>
                                </div>
                                <CardHeader>
                                    <CardTitle class="text-base">{{ clip.title }}</CardTitle>
                                    <CardDescription>
                                        {{ new Date(clip.game.date).toLocaleDateString() }} -
                                        {{ clip.game.homeTeam }} vs {{ clip.game.awayTeam }}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div v-if="clip.notes" class="mb-2 text-sm">
                                        <div class="font-medium">Coach Notes:</div>
                                        <p class="text-gray-600">{{ clip.notes }}</p>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Shared by {{ clip.creator.name }} on {{ new Date(clip.createdAt).toLocaleDateString() }}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                    
                    <div v-else class="bg-white rounded-lg shadow p-6 text-center">
                        <div class="mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium">No clips shared yet</h3>
                        <p class="mt-1 text-gray-500">Your coaches haven't shared any clips with you yet.</p>
                    </div>
                </div>
                
                <!-- Video player modal overlay -->
                <div v-if="showPlayer && activeClip" class="fixed inset-0 z-50 bg-black bg-opacity-75 flex items-center justify-center p-4">
                    <div class="bg-white rounded-lg max-w-4xl w-full overflow-hidden">
                        <div class="p-4 flex justify-between items-center border-b">
                            <h3 class="font-medium">{{ activeClip.title }}</h3>
                            <Button variant="ghost" size="sm" @click="closePlayer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </Button>
                        </div>
                        <div class="aspect-video bg-black">
                            <!-- Embed YouTube video player - in a real app, use a proper video player component -->
                            <div class="w-full h-full flex items-center justify-center text-white">
                                <p>Video player would be embedded here with custom start/end times:</p>
                                <p class="mt-2">Start: {{ activeClip.startTime }}s, End: {{ activeClip.endTime }}s</p>
                            </div>
                        </div>
                        <div class="p-4">
                            <h4 class="font-medium">Coach Notes:</h4>
                            <p class="mt-1 text-gray-600">{{ activeClip.notes || 'No notes provided.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 