<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/Components/ui/card';

const props = defineProps({
    userRole: {
        type: String,
        required: true
    },
    dashboardData: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- League Admin Dashboard -->
                <div v-if="userRole === 'league_admin'">
                    <h3 class="mb-4 text-lg font-medium">League Administration Dashboard</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                        <Card>
                            <CardHeader class="bg-indigo-50">
                                <CardTitle class="text-indigo-800">Associations</CardTitle>
                            </CardHeader>
                            <CardContent class="pt-4">
                                <p class="text-2xl font-bold">{{ dashboardData.associations_count }}</p>
                            </CardContent>
                        </Card>
                        
                        <Card>
                            <CardHeader class="bg-blue-50">
                                <CardTitle class="text-blue-800">Leagues</CardTitle>
                            </CardHeader>
                            <CardContent class="pt-4">
                                <p class="text-2xl font-bold">{{ dashboardData.leagues_count }}</p>
                            </CardContent>
                        </Card>
                        
                        <Card>
                            <CardHeader class="bg-green-50">
                                <CardTitle class="text-green-800">Teams</CardTitle>
                            </CardHeader>
                            <CardContent class="pt-4">
                                <p class="text-2xl font-bold">{{ dashboardData.teams_count }}</p>
                            </CardContent>
                            <CardFooter>
                                <Button variant="outline" asChild>
                                    <a :href="route('admin.teams')">Manage Teams</a>
                                </Button>
                            </CardFooter>
                        </Card>
                        
                        <Card>
                            <CardHeader class="bg-yellow-50">
                                <CardTitle class="text-yellow-800">Players</CardTitle>
                            </CardHeader>
                            <CardContent class="pt-4">
                                <p class="text-2xl font-bold">{{ dashboardData.players_count }}</p>
                            </CardContent>
                        </Card>
                        
                        <Card>
                            <CardHeader class="bg-red-50">
                                <CardTitle class="text-red-800">Completed Games</CardTitle>
                            </CardHeader>
                            <CardContent class="pt-4">
                                <p class="text-2xl font-bold">{{ dashboardData.games_count }}</p>
                            </CardContent>
                        </Card>
                        
                        <Card>
                            <CardHeader class="bg-purple-50">
                                <CardTitle class="text-purple-800">Upcoming Games</CardTitle>
                            </CardHeader>
                            <CardContent class="pt-4">
                                <p class="text-2xl font-bold">{{ dashboardData.upcoming_games_count }}</p>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Coach Dashboard -->
                <div v-else-if="userRole === 'coach'">
                    <Card>
                        <CardHeader>
                            <CardTitle>Coach Dashboard</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="dashboardData.no_team" class="space-y-4">
                                <p>You currently don't have a team assigned to manage.</p>
                                <p>Please contact the league administrator.</p>
                            </div>
                            
                            <div v-else class="space-y-6">
                                <div>
                                    <h4 class="mb-2 text-xl font-semibold">{{ dashboardData.team.name }}</h4>
                                    <p>{{ dashboardData.players_count }} registered players</p>
                                    <Button class="mt-4" variant="outline" asChild>
                                        <a :href="route('coach.team')">View Team</a>
                                    </Button>
                                </div>
                                
                                <div>
                                    <h4 class="mb-3 font-medium">Upcoming Games</h4>
                                    <div v-if="dashboardData.games.length === 0" class="text-gray-500">
                                        No upcoming games scheduled
                                    </div>
                                    <div v-else class="space-y-3">
                                        <Card v-for="game in dashboardData.games" :key="game.id" class="border border-gray-200">
                                            <CardContent class="pt-4">
                                                <div class="font-medium">
                                                    {{ new Date(game.game_date_time).toLocaleDateString() }}
                                                </div>
                                                <div>
                                                    {{ game.home_team_id === dashboardData.team.id ? 'Home' : 'Away' }} vs 
                                                    {{ game.home_team_id === dashboardData.team.id ? game.away_team_name : game.home_team_name }}
                                                </div>
                                            </CardContent>
                                        </Card>
                                    </div>
                                    <Button class="mt-4" variant="outline" asChild>
                                        <a :href="route('coach.games')">Manage Games</a>
                                    </Button>
                                </div>
                                
                                <div>
                                    <h4 class="mb-2 font-medium">Clips</h4>
                                    <p>You have created {{ dashboardData.clips_count }} clips</p>
                                    <Button class="mt-4" variant="outline" asChild>
                                        <a :href="route('coach.clips')">Manage Clips</a>
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Player Dashboard -->
                <div v-else-if="userRole === 'player'">
                    <Card>
                        <CardHeader>
                            <CardTitle>Player Dashboard</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="dashboardData.no_player_profile" class="space-y-4">
                                <p>Your player profile hasn't been set up yet.</p>
                                <p>Please contact your coach or team administrator.</p>
                            </div>
                            
                            <div v-else class="space-y-6">
                                <div>
                                    <h4 class="mb-2 text-xl font-semibold">{{ dashboardData.player.name }}</h4>
                                    <p>Teams: 
                                        <span v-for="(team, index) in dashboardData.teams" :key="team.id">
                                            {{ team.name }}{{ index < dashboardData.teams.length - 1 ? ', ' : '' }}
                                        </span>
                                    </p>
                                    <Button class="mt-4" variant="outline" asChild>
                                        <a :href="route('player.team')">View Team</a>
                                    </Button>
                                </div>
                                
                                <div>
                                    <h4 class="mb-3 font-medium">Games</h4>
                                    <div v-if="dashboardData.games.length === 0" class="text-gray-500">
                                        No upcoming games scheduled
                                    </div>
                                    <div v-else class="space-y-3">
                                        <Card v-for="game in dashboardData.games" :key="game.id" class="border border-gray-200">
                                            <CardContent class="pt-4">
                                                <div class="font-medium">
                                                    {{ new Date(game.game_date_time).toLocaleDateString() }}
                                                </div>
                                                <div>
                                                    {{ game.home_team_name }} vs {{ game.away_team_name }}
                                                </div>
                                            </CardContent>
                                        </Card>
                                    </div>
                                    <Button class="mt-4" variant="outline" asChild>
                                        <a :href="route('game.schedule')">View Schedule</a>
                                    </Button>
                                </div>
                                
                                <div>
                                    <h4 class="mb-2 font-medium">Shared Clips</h4>
                                    <p>{{ dashboardData.clips_count }} clips have been shared with you</p>
                                    <Button class="mt-4" variant="outline" asChild>
                                        <a :href="route('player.clips')">View Clips</a>
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
                
                <!-- Default Message -->
                <div v-else>
                    <Card>
                        <CardContent class="pt-6">
                            <p>You're logged in!</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
