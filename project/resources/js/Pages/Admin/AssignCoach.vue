<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { Label } from '@/Components/ui/label';
import { Input } from '@/Components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { onMounted, ref } from 'vue';

const props = defineProps({
  team: Object,
  currentCoaches: Array,
  availableCoaches: Array,
});

// Form to assign an existing coach
const assignForm = useForm({
  coach_id: '',
});

// Form to create a new coach
const createForm = useForm({
  name: '',
  email: '',
  password: '',
});

const activeTab = ref('assign');

// Submit handler for assigning an existing coach
const submitAssignForm = () => {
  assignForm.post(route('admin.teams.assign-coach', props.team.id), {
    onSuccess: () => {
      assignForm.reset();
    },
  });
};

// Submit handler for creating a new coach
const submitCreateForm = () => {
  createForm.post(route('admin.teams.create-coach', props.team.id), {
    onSuccess: () => {
      createForm.reset();
    },
  });
};
</script>

<template>
  <Head title="Assign Coach to Team" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Assign Coach to Team
        </h2>
        <Link :href="route('admin.teams')" class="text-sm text-primary-600 hover:text-primary-900">
          Back to Teams
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
        <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Team Information</h3>
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <p class="text-sm text-gray-500">Team Name</p>
                <p class="text-gray-900">{{ team.name }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">League</p>
                <p class="text-gray-900">{{ team.league?.name || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Season</p>
                <p class="text-gray-900">{{ team.season?.name || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Association</p>
                <p class="text-gray-900">{{ team.association?.name || 'N/A' }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Current Coaches</h3>
            
            <div v-if="currentCoaches.length" class="divide-y">
              <div v-for="coach in currentCoaches" :key="coach.id" class="flex items-center justify-between py-3">
                <div>
                  <p class="font-medium text-gray-900">{{ coach.name }}</p>
                  <p class="text-sm text-gray-500">{{ coach.email }}</p>
                </div>
                <Link
                  :href="route('admin.teams.remove-coach')"
                  method="post"
                  :data="{ coach_id: coach.id }"
                  as="button"
                  class="rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-100"
                >
                  Remove Coach
                </Link>
              </div>
            </div>
            <p v-else class="text-gray-500">No coaches currently assigned to this team.</p>
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Assign Coach</CardTitle>
            <CardDescription>Add a new coach to this team by assigning an existing user or creating a new coach account.</CardDescription>
          </CardHeader>
          <CardContent>
            <Tabs v-model="activeTab" class="w-full">
              <TabsList class="grid w-full grid-cols-2">
                <TabsTrigger value="assign">Assign Existing Coach</TabsTrigger>
                <TabsTrigger value="create">Create New Coach</TabsTrigger>
              </TabsList>
              
              <TabsContent value="assign">
                <Alert v-if="availableCoaches.length === 0" class="mb-4" variant="warning">
                  <AlertTitle>No available coaches</AlertTitle>
                  <AlertDescription>
                    There are no unassigned coaches in the system. Please create a new coach account or remove a coach from another team.
                  </AlertDescription>
                </Alert>
                
                <form v-else @submit.prevent="submitAssignForm" class="space-y-4">
                  <div>
                    <Label for="coach">Select Coach</Label>
                    <Select
                      v-model="assignForm.coach_id"
                      name="coach"
                      id="coach"
                      required
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Select a coach" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="coach in availableCoaches"
                          :key="coach.id"
                          :value="coach.id"
                        >
                          {{ coach.name }} ({{ coach.email }})
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <p v-if="assignForm.errors.coach_id" class="mt-1 text-sm text-red-600">
                      {{ assignForm.errors.coach_id }}
                    </p>
                  </div>
                  
                  <Button
                    type="submit"
                    :disabled="assignForm.processing"
                    class="mt-4"
                  >
                    Assign Coach
                  </Button>
                </form>
              </TabsContent>
              
              <TabsContent value="create">
                <form @submit.prevent="submitCreateForm" class="space-y-4">
                  <div>
                    <Label for="name">Name</Label>
                    <Input
                      id="name"
                      v-model="createForm.name"
                      type="text"
                      required
                      autocomplete="name"
                    />
                    <p v-if="createForm.errors.name" class="mt-1 text-sm text-red-600">
                      {{ createForm.errors.name }}
                    </p>
                  </div>
                  
                  <div>
                    <Label for="email">Email</Label>
                    <Input
                      id="email"
                      v-model="createForm.email"
                      type="email"
                      required
                      autocomplete="email"
                    />
                    <p v-if="createForm.errors.email" class="mt-1 text-sm text-red-600">
                      {{ createForm.errors.email }}
                    </p>
                  </div>
                  
                  <div>
                    <Label for="password">Password</Label>
                    <Input
                      id="password"
                      v-model="createForm.password"
                      type="password"
                      required
                      autocomplete="new-password"
                    />
                    <p v-if="createForm.errors.password" class="mt-1 text-sm text-red-600">
                      {{ createForm.errors.password }}
                    </p>
                  </div>
                  
                  <Button
                    type="submit"
                    :disabled="createForm.processing"
                    class="mt-4"
                  >
                    Create & Assign Coach
                  </Button>
                </form>
              </TabsContent>
            </Tabs>
          </CardContent>
          <CardFooter>
            <p class="text-xs text-gray-500">
              Coaches will have access to manage this team's games, clips, and roster.
            </p>
          </CardFooter>
        </Card>
      </div>
    </div>
  </AuthenticatedLayout>
</template> 