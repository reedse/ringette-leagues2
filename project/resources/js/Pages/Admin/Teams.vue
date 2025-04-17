<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table';
import { Badge } from '@/Components/ui/badge';

defineProps({
  teams: Object,
});
</script>

<template>
  <Head title="Manage Teams" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Manage Teams
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="mb-6 flex items-center justify-between">
              <h3 class="text-lg font-medium">Team Management</h3>
            </div>

            <Table>
              <TableCaption>A list of all teams and their assigned coaches.</TableCaption>
              <TableHeader>
                <TableRow>
                  <TableHead>Team Name</TableHead>
                  <TableHead>League</TableHead>
                  <TableHead>Season</TableHead>
                  <TableHead>Players</TableHead>
                  <TableHead>Coaches</TableHead>
                  <TableHead class="w-[150px]">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="team in teams.data" :key="team.id">
                  <TableCell class="font-medium">{{ team.name }}</TableCell>
                  <TableCell>{{ team.league?.name || 'N/A' }}</TableCell>
                  <TableCell>{{ team.season?.name || 'N/A' }}</TableCell>
                  <TableCell>{{ team.players_count }}</TableCell>
                  <TableCell>
                    <div v-if="team.coaches && team.coaches.length" class="space-y-1">
                      <div v-for="coach in team.coaches" :key="coach.id" class="flex items-center">
                        <span class="mr-2">{{ coach.name }}</span>
                        <Link
                          :href="route('admin.teams.remove-coach')"
                          method="post"
                          :data="{ coach_id: coach.id }"
                          as="button"
                          class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-100 p-0.5 text-xs text-red-500 hover:bg-red-200"
                          title="Remove coach"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                          </svg>
                        </Link>
                      </div>
                    </div>
                    <Badge v-else variant="outline" class="text-amber-500 bg-amber-50">No coaches assigned</Badge>
                  </TableCell>
                  <TableCell>
                    <Link :href="route('admin.teams.assign-coach', team.id)" class="text-primary-600 hover:text-primary-900">
                      Assign Coach
                    </Link>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
            
            <div class="mt-6 flex items-center justify-between">
              <div v-if="teams.links && teams.links.length > 3" class="flex flex-wrap">
                <Link
                  v-for="(link, i) in teams.links"
                  :key="i"
                  :href="link.url"
                  class="mr-1 mb-1 rounded-md border px-4 py-2"
                  :class="{'bg-primary-50 text-primary-700': link.active, 'text-gray-700': !link.active}"
                  v-html="link.label"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template> 