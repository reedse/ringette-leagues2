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
  seasons: Object,
});

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString();
};
</script>

<template>
  <Head title="Manage Seasons" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Manage Seasons
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="mb-6 flex items-center justify-between">
              <h3 class="text-lg font-medium">Season Management</h3>
              <Link :href="route('admin.seasons.create')">
                <Button>Create New Season</Button>
              </Link>
            </div>

            <Table>
              <TableCaption>A list of all seasons and their details.</TableCaption>
              <TableHeader>
                <TableRow>
                  <TableHead>Season Name</TableHead>
                  <TableHead>League</TableHead>
                  <TableHead>Association</TableHead>
                  <TableHead>Start Date</TableHead>
                  <TableHead>End Date</TableHead>
                  <TableHead>Teams</TableHead>
                  <TableHead>Games</TableHead>
                  <TableHead class="w-[150px]">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="season in seasons.data" :key="season.id">
                  <TableCell class="font-medium">{{ season.name }}</TableCell>
                  <TableCell>{{ season.league?.name || 'N/A' }}</TableCell>
                  <TableCell>{{ season.league?.association?.name || 'N/A' }}</TableCell>
                  <TableCell>{{ formatDate(season.start_date) }}</TableCell>
                  <TableCell>{{ formatDate(season.end_date) }}</TableCell>
                  <TableCell>
                    <Badge variant="secondary">{{ season.teams_count }} teams</Badge>
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline">{{ season.games_count }} games</Badge>
                  </TableCell>
                  <TableCell>
                    <div class="flex space-x-2">
                      <Link :href="route('admin.seasons.edit', season.id)" class="text-primary-600 hover:text-primary-900">
                        Edit
                      </Link>
                      <Link
                        :href="route('admin.seasons.destroy', season.id)"
                        method="delete"
                        as="button"
                        class="text-red-600 hover:text-red-900"
                        @click="confirm('Are you sure you want to delete this season?') || $event.preventDefault()"
                      >
                        Delete
                      </Link>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
            
            <div class="mt-6 flex items-center justify-between">
              <div v-if="seasons.links && seasons.links.length > 3" class="flex flex-wrap">
                <Link
                  v-for="(link, i) in seasons.links"
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