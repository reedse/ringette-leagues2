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

                <!-- Subscription required message -->
                <div v-if="!hasSubscription" class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Video Clips Access</h3>
                        <div class="mb-6 rounded-md bg-primary-50 p-4">
                            <h4 class="font-medium text-primary-800">Upgrade Required</h4>
                            <p class="mt-1 text-primary-600">
                                To access video clips shared by your coaches, you need to upgrade your account.
                            </p>
                            <Link :href="route('subscription.show')">
                                <Button class="mt-3">Upgrade Account</Button>
                            </Link>
                        </div>
                        
                        <h4 class="font-medium mt-6 mb-2">Benefits of upgrading:</h4>
                        <ul class="list-disc pl-5 space-y-2 text-gray-600">
                            <li>Access to all video clips shared by your coaches</li>
                            <li>Review game highlights and key moments</li>
                            <li>See coaching tips and feedback</li>
                            <li>Analyze your performance to improve your skills</li>
                            <li>Reference clips from previous games</li>
                        </ul>
                        
                        <div class="mt-6 border-t pt-6">
                            <h4 class="font-medium mb-2">Subscription Plans:</h4>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="border rounded-md p-4">
                                    <div class="font-medium">Monthly Plan</div>
                                    <div class="text-2xl font-bold mt-1">$4.99<span class="text-sm font-normal text-gray-500">/month</span></div>
                                    <ul class="mt-3 text-sm space-y-1">
                                        <li>Full access to all clips</li>
                                        <li>7-day free trial</li>
                                        <li>Cancel anytime</li>
                                    </ul>
                                </div>
                                <div class="border rounded-md p-4 bg-primary-50">
                                    <div class="font-medium">Annual Plan <Badge class="ml-1">Best Value</Badge></div>
                                    <div class="text-2xl font-bold mt-1">$49.99<span class="text-sm font-normal text-gray-500">/year</span></div>
                                    <ul class="mt-3 text-sm space-y-1">
                                        <li>Full access to all clips</li>
                                        <li>7-day free trial</li>
                                        <li>Save over 15% compared to monthly</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mt-4 text-center">
                                <Link :href="route('subscription.show')">
                                    <Button size="lg">View Subscription Options</Button>
                                </Link>
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