<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  Sheet,
  SheetContent,
  SheetTrigger,
} from '@/components/ui/sheet';

const showingNavigationDropdown = ref(false);
const showingSidebar = ref(true);

const currentUser = computed(() => usePage().props.auth.user);
const userRole = computed(() => usePage().props.userRole || 'player');

const toggleSidebar = () => {
    showingSidebar.value = !showingSidebar.value;
};

// Navigation items based on user role
const getNavigationItems = computed(() => {
    const commonItems = [
        { name: 'Dashboard', route: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
        { 
            name: 'Games', 
            href: '/games', 
            active: route().current('games.index') || route().current('games.show'), 
            icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' 
        },
        { 
            name: 'Teams', 
            href: '/teams', 
            active: route().current('teams.index') || route().current('teams.show'), 
            icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' 
        },
    ];

    // Player-specific items
    if (userRole.value === 'player') {
        return [
            ...commonItems,
            { name: 'My Stats', route: 'player.stats', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
            { name: 'My Team', route: 'player.team', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
            { name: 'Game Schedule', route: 'game.schedule', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
            { name: 'My Clips', route: 'player.clips', icon: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z' },
        ];
    }
    
    // Coach-specific items
    else if (userRole.value === 'coach') {
        return [
            ...commonItems,
            { name: 'My Team', route: 'coach.team', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
            { name: 'Game Management', route: 'coach.games', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
            { name: 'Clip Management', route: 'coach.clips', icon: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z' },
        ];
    }
    
    // League Admin items
    else if (userRole.value === 'league_admin') {
        return [
            ...commonItems,
            { name: 'Associations', route: 'admin.associations', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
            { name: 'Leagues', route: 'admin.leagues', icon: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
            { name: 'Seasons', route: 'admin.seasons', icon: 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z' },
        ];
    }
    
    // Default items
    return commonItems;
});
</script>

<template>
    <div class="min-h-screen bg-background">
        <!-- Sidebar - Hidden on mobile, visible on desktop -->
        <div
            class="fixed inset-y-0 left-0 z-10 hidden w-64 transform bg-card shadow-lg transition-all duration-300 md:block"
            :class="{ '-translate-x-full': !showingSidebar, 'translate-x-0': showingSidebar }"
        >
            <!-- Sidebar Header -->
            <div class="flex h-16 items-center justify-between border-b border-primary bg-primary px-4">
                <Link :href="route('dashboard')" class="flex items-center space-x-2">
                    <ApplicationLogo class="h-9 w-9 fill-current text-primary-foreground" />
                    <span class="text-lg font-semibold text-primary-foreground">Ringette League</span>
                </Link>
                <Button @click="toggleSidebar" variant="ghost" size="icon" class="text-primary-foreground hover:bg-primary/80 md:hidden">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </Button>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="mt-4 px-2">
                <div class="space-y-1">
                    <template v-for="item in getNavigationItems" :key="item.name">
                        <!-- For items with route -->
                        <Link
                            v-if="item.route"
                            :href="route(item.route)"
                            :class="[
                                route().current(item.route)
                                    ? 'bg-primary text-primary-foreground'
                                    : 'text-foreground/70 hover:bg-muted hover:text-foreground',
                                'group flex items-center rounded-md px-2 py-2 text-sm font-medium'
                            ]"
                        >
                            <svg
                                class="mr-3 h-5 w-5 flex-shrink-0"
                                :class="[
                                    route().current(item.route)
                                        ? 'text-primary-foreground'
                                        : 'text-foreground/50 group-hover:text-foreground'
                                ]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    :d="item.icon"
                                ></path>
                            </svg>
                            {{ item.name }}
                        </Link>
                        
                        <!-- For items with direct href -->
                        <a
                            v-else
                            :href="item.href"
                            :class="[
                                item.active
                                    ? 'bg-primary text-primary-foreground'
                                    : 'text-foreground/70 hover:bg-muted hover:text-foreground',
                                'group flex items-center rounded-md px-2 py-2 text-sm font-medium'
                            ]"
                        >
                            <svg
                                class="mr-3 h-5 w-5 flex-shrink-0"
                                :class="[
                                    item.active
                                        ? 'text-primary-foreground'
                                        : 'text-foreground/50 group-hover:text-foreground'
                                ]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    :d="item.icon"
                                ></path>
                            </svg>
                            {{ item.name }}
                        </a>
                    </template>
                </div>
            </nav>

            <!-- Profile section -->
            <div class="absolute bottom-0 flex w-full flex-col border-t border-border bg-card p-4">
                <div class="flex items-center">
                    <Avatar class="h-10 w-10 bg-primary text-primary-foreground">
                        <AvatarFallback>{{ currentUser.name.charAt(0) }}</AvatarFallback>
                    </Avatar>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-foreground">{{ currentUser.name }}</p>
                        <p class="text-xs font-medium text-muted-foreground">{{ currentUser.email }}</p>
                    </div>
                </div>
                <div class="mt-3 space-y-1">
                    <Link
                        :href="route('profile.edit')"
                        class="block rounded-md px-3 py-2 text-sm font-medium text-foreground/70 hover:bg-muted hover:text-foreground"
                    >
                        Your Profile
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="block w-full rounded-md px-3 py-2 text-left text-sm font-medium text-foreground/70 hover:bg-muted hover:text-foreground"
                    >
                        Sign out
                    </Link>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div
            class="flex min-h-screen flex-col md:pl-64"
            :class="{ 'md:pl-64': showingSidebar, 'md:pl-0': !showingSidebar }"
        >
            <!-- Top Navigation (Mobile) -->
            <div class="sticky top-0 z-10 bg-primary shadow md:hidden">
                <div class="flex h-16 items-center justify-between px-4">
                    <div class="flex items-center">
                        <Sheet>
                            <SheetTrigger asChild>
                                <Button variant="ghost" size="icon" class="text-primary-foreground hover:bg-primary/80">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                    </svg>
                                </Button>
                            </SheetTrigger>
                            <SheetContent side="left" class="w-64 p-0">
                                <div class="flex h-16 items-center justify-between border-b border-primary bg-primary px-4">
                                    <Link :href="route('dashboard')" class="flex items-center space-x-2">
                                        <ApplicationLogo class="h-9 w-9 fill-current text-primary-foreground" />
                                        <span class="text-lg font-semibold text-primary-foreground">Ringette League</span>
                                    </Link>
                                </div>
                                <nav class="mt-4 px-2">
                                    <div class="space-y-1">
                                        <template v-for="item in getNavigationItems" :key="item.name">
                                            <!-- For items with route -->
                                            <Link
                                                v-if="item.route"
                                                :href="route(item.route)"
                                                :class="[
                                                    route().current(item.route)
                                                        ? 'bg-primary text-primary-foreground'
                                                        : 'text-foreground/70 hover:bg-muted hover:text-foreground',
                                                    'group flex items-center rounded-md px-2 py-2 text-sm font-medium'
                                                ]"
                                            >
                                                <svg
                                                    class="mr-3 h-5 w-5 flex-shrink-0"
                                                    :class="[
                                                        route().current(item.route)
                                                            ? 'text-primary-foreground'
                                                            : 'text-foreground/50 group-hover:text-foreground'
                                                    ]"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        :d="item.icon"
                                                    ></path>
                                                </svg>
                                                {{ item.name }}
                                            </Link>
                                            
                                            <!-- For items with direct href -->
                                            <a
                                                v-else
                                                :href="item.href"
                                                :class="[
                                                    item.active
                                                        ? 'bg-primary text-primary-foreground'
                                                        : 'text-foreground/70 hover:bg-muted hover:text-foreground',
                                                    'group flex items-center rounded-md px-2 py-2 text-sm font-medium'
                                                ]"
                                            >
                                                <svg
                                                    class="mr-3 h-5 w-5 flex-shrink-0"
                                                    :class="[
                                                        item.active
                                                            ? 'text-primary-foreground'
                                                            : 'text-foreground/50 group-hover:text-foreground'
                                                    ]"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        :d="item.icon"
                                                    ></path>
                                                </svg>
                                                {{ item.name }}
                                            </a>
                                        </template>
                                    </div>
                                </nav>
                                <div class="absolute bottom-0 flex w-full flex-col border-t border-border bg-card p-4">
                                    <div class="flex items-center">
                                        <Avatar class="h-10 w-10 bg-primary text-primary-foreground">
                                            <AvatarFallback>{{ currentUser.name.charAt(0) }}</AvatarFallback>
                                        </Avatar>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-foreground">{{ currentUser.name }}</p>
                                            <p class="text-xs font-medium text-muted-foreground">{{ currentUser.email }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-3 space-y-1">
                                        <Link
                                            :href="route('profile.edit')"
                                            class="block rounded-md px-3 py-2 text-sm font-medium text-foreground/70 hover:bg-muted hover:text-foreground"
                                        >
                                            Your Profile
                                        </Link>
                                        <Link
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            class="block w-full rounded-md px-3 py-2 text-left text-sm font-medium text-foreground/70 hover:bg-muted hover:text-foreground"
                                        >
                                            Sign out
                                        </Link>
                                    </div>
                                </div>
                            </SheetContent>
                        </Sheet>
                        <Link :href="route('dashboard')" class="ml-2">
                            <ApplicationLogo class="h-8 w-8 fill-current text-primary-foreground" />
                        </Link>
                    </div>
                    <!-- Mobile User Dropdown -->
                    <div class="relative">
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" class="flex items-center text-sm font-medium text-primary-foreground">
                                    <span>{{ currentUser.name }}</span>
                                    <svg class="ml-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent>
                                <DropdownMenuItem asChild>
                                    <Link :href="route('profile.edit')">Profile</Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem asChild>
                                    <Link :href="route('logout')" method="post" as="button">Log Out</Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </div>

            <!-- Page Header -->
            <header class="bg-card shadow" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 bg-background">
                <div class="py-6">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
