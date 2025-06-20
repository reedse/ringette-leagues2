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
  associations: Object,
});
</script>

<template>
  <Head title="Manage Associations" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Manage Associations
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="mb-6 flex items-center justify-between">
              <h3 class="text-lg font-medium">Association Management</h3>
              <Link :href="route('admin.associations.create')">
                <Button>Create New Association</Button>
              </Link>
            </div>

            <Table>
              <TableCaption>A list of all associations and their details.</TableCaption>
              <TableHeader>
                <TableRow>
                  <TableHead>Association Name</TableHead>
                  <TableHead>Leagues</TableHead>
                  <TableHead>Teams</TableHead>
                  <TableHead class="w-[150px]">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="association in associations.data" :key="association.id">
                  <TableCell class="font-medium">{{ association.name }}</TableCell>
                  <TableCell>
                    <Badge variant="secondary">{{ association.leagues_count }} leagues</Badge>
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline">{{ association.teams_count }} teams</Badge>
                  </TableCell>
                  <TableCell>
                    <div class="flex space-x-2">
                      <Link :href="route('admin.associations.edit', association.id)" class="text-primary-600 hover:text-primary-900">
                        Edit
                      </Link>
                      <Link
                        :href="route('admin.associations.destroy', association.id)"
                        method="delete"
                        as="button"
                        class="text-red-600 hover:text-red-900"
                        @click="confirm('Are you sure you want to delete this association?') || $event.preventDefault()"
                      >
                        Delete
                      </Link>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
            
            <div class="mt-6 flex items-center justify-between">
              <div v-if="associations.links && associations.links.length > 3" class="flex flex-wrap">
                <Link
                  v-for="(link, i) in associations.links"
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