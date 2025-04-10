<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Import shadcn-vue components
import { Label } from '@/Components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';

// Define props
defineProps({
    associations: Array, // Accept associations prop instead of teams
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'player', // Default role
    team_id: null,
    jersey_number: '',
});

// Computed property to check if the player role is selected
const isPlayerRole = computed(() => form.role === 'player');

const submit = () => {
    // Clear player-specific fields if role is not player
    if (!isPlayerRole.value) {
        form.team_id = null;
        form.jersey_number = '';
    }
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div>
                <Label for="name">Name</Label>
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <Label for="email">Email</Label>
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Role Selection -->
            <div class="mt-4">
                <Label>Registering as a</Label>
                <RadioGroup v-model="form.role" class="mt-2 grid grid-cols-2 gap-4">
                    <div>
                        <RadioGroupItem value="player" id="role-player" class="peer sr-only" />
                        <Label
                            for="role-player"
                            class="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-popover p-4 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary"
                        >
                            Player
                        </Label>
                    </div>
                    <div>
                        <RadioGroupItem value="coach" id="role-coach" class="peer sr-only" />
                        <Label
                            for="role-coach"
                            class="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-popover p-4 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary"
                        >
                            Coach
                        </Label>
                    </div>
                </RadioGroup>
                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <!-- Conditional Player Fields -->
            <template v-if="isPlayerRole">
                <div class="mt-4">
                    <Label for="team_id">Team</Label>
                    <Select v-model="form.team_id" required>
                        <SelectTrigger id="team_id" class="w-full mt-1">
                            <SelectValue placeholder="Select your team" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup v-for="association in associations" :key="association.id">
                                <SelectLabel>{{ association.name }}</SelectLabel>
                                <SelectItem v-for="team in association.teams" :key="team.id" :value="team.id">
                                    {{ team.name }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError class="mt-2" :message="form.errors.team_id" />
                </div>

                <div class="mt-4">
                    <Label for="jersey_number">Jersey Number</Label>
                    <TextInput
                        id="jersey_number"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.jersey_number"
                        required
                        autocomplete="off"
                    />
                    <InputError class="mt-2" :message="form.errors.jersey_number" />
                </div>
            </template>

            <div class="mt-4">
                <Label for="password">Password</Label>
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <Label for="password_confirmation">Confirm Password</Label>
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-8 border-t pt-8">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="bg-white px-2 text-gray-500">Or continue with</span>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-3">
                <Link
                    :href="route('socialite.redirect', { provider: 'google' })"
                    class="inline-flex w-full justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-500 shadow-sm hover:bg-gray-50"
                >
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12.545 10.239v3.821h5.445c-0.712 2.315-2.647 3.972-5.445 3.972-3.332 0-6.033-2.701-6.033-6.032s2.701-6.032 6.033-6.032c1.498 0 2.866 0.549 3.921 1.453l2.814-2.814c-1.787-1.676-4.166-2.707-6.735-2.707-5.518 0-9.99 4.472-9.99 9.99s4.473 9.99 9.99 9.99c8.083 0 9.915-7.599 9.099-11.641h-9.099z" />
                    </svg>
                    <span class="ml-2">Google</span>
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
