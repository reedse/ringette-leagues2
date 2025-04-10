<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

</script>

<template>
    <Head title="Associations" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Associations
            </h2>
        </template>

        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium">Manage Associations</h3>
                    <button class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Association
                    </button>
                </div>
                
                <p class="my-4">This page allows you to manage the associations in the system.</p>
                
                <!-- Associations list -->
                <div class="mt-6">
                    <div class="rounded-md bg-gray-50 p-8 text-center">
                        <p class="text-gray-500">No associations have been created yet.</p>
                        <p class="mt-2 text-sm text-gray-500">Click the "Add Association" button to create your first association.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 