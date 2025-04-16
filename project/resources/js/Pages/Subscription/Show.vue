<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Alert, AlertDescription } from '@/Components/ui/alert';

const props = defineProps({
    user: Object,
    intent: Object,
    plans: Array,
    hasSubscription: Boolean,
    onTrial: Boolean
});

const selectedPlan = ref(props.plans[0]?.id || '');

const form = useForm({
    plan: selectedPlan.value
});

const selectPlan = (planId) => {
    selectedPlan.value = planId;
    form.plan = planId;
};

const subscribe = () => {
    form.post(route('subscription.checkout'));
};

const cancelSubscription = () => {
    if (confirm('Are you sure you want to cancel your subscription?')) {
        form.post(route('subscription.cancel'));
    }
};

const resumeSubscription = () => {
    form.post(route('subscription.resume'));
};
</script>

<template>
    <Head title="Subscription Plans" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Subscription Plans
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div v-if="hasSubscription" class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Manage Your Subscription
                        </h3>
                        <div class="mt-2 max-w-xl text-sm text-gray-500">
                            <p>
                                You currently have an active subscription to access video clips.
                                <span v-if="onTrial" class="font-medium text-primary-500"> (Trial period)</span>
                            </p>
                        </div>
                        <div v-if="user.subscription('clips').cancelled()" class="mt-5">
                            <Alert variant="warning" class="mb-6">
                                <AlertDescription>
                                    Your subscription will end on {{ new Date(user.subscription('clips').ends_at).toLocaleDateString() }}.
                                </AlertDescription>
                            </Alert>
                            <Button @click="resumeSubscription">Resume Subscription</Button>
                        </div>
                        <div v-else class="mt-5">
                            <Button variant="destructive" @click="cancelSubscription">Cancel Subscription</Button>
                        </div>
                    </div>
                </div>

                <div v-if="!hasSubscription">
                    <div class="mb-6 text-center">
                        <h3 class="text-2xl font-bold">Choose Your Subscription Plan</h3>
                        <p class="mt-2 text-gray-500">Gain access to video clips shared by your coaches</p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <Card 
                            v-for="plan in plans" 
                            :key="plan.id" 
                            :class="[
                                'cursor-pointer transition-all',
                                selectedPlan === plan.id ? 'ring-2 ring-primary-500' : 'hover:shadow-lg'
                            ]"
                            @click="selectPlan(plan.id)"
                        >
                            <CardHeader>
                                <CardTitle>{{ plan.name }}</CardTitle>
                                <CardDescription>
                                    {{ plan.interval === 'yearly' ? 'Annual billing' : 'Monthly billing' }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="mb-4">
                                    <span class="text-3xl font-bold">{{ plan.price }}</span>
                                    <span class="text-gray-500">/{{ plan.interval === 'yearly' ? 'year' : 'month' }}</span>
                                </div>
                                <ul class="space-y-2">
                                    <li v-for="(feature, index) in plan.features" :key="index" class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ feature }}</span>
                                    </li>
                                </ul>
                                <div v-if="plan.interval === 'yearly'" class="mt-4">
                                    <Badge variant="success">Save over 15%</Badge>
                                </div>
                            </CardContent>
                            <CardFooter>
                                <div class="w-full">
                                    <Button
                                        class="w-full"
                                        :variant="selectedPlan === plan.id ? 'default' : 'outline'"
                                        @click.stop="selectPlan(plan.id)"
                                    >
                                        {{ selectedPlan === plan.id ? 'Selected' : 'Select Plan' }}
                                    </Button>
                                </div>
                            </CardFooter>
                        </Card>
                    </div>

                    <div class="mt-8 text-center">
                        <p class="mb-4 text-gray-500">All plans include a 7-day free trial. You won't be charged until the trial period ends.</p>
                        <Button size="lg" :disabled="!selectedPlan" @click="subscribe">
                            Continue to Payment
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 