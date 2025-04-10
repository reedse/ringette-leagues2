<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';

const props = defineProps({
    intent: Object
});

const stripe = ref(null);
const elements = ref(null);
const cardElement = ref(null);
const cardErrors = ref('');
const processing = ref(false);
const setupComplete = ref(false);

const form = useForm({
    payment_method: '',
    plan: 'price_clips_monthly' // Default plan, would come from previous page
});

onMounted(() => {
    if (window.Stripe) {
        stripe.value = window.Stripe(import.meta.env.VITE_STRIPE_KEY);
        elements.value = stripe.value.elements();
        cardElement.value = elements.value.create('card');
        cardElement.value.mount('#card-element');
        
        cardElement.value.on('change', (event) => {
            cardErrors.value = event.error ? event.error.message : '';
        });
    }
});

const handleSubmit = async () => {
    if (processing.value) return;
    
    processing.value = true;
    const { setupIntent, error } = await stripe.value.confirmCardSetup(
        props.intent.client_secret, {
            payment_method: {
                card: cardElement.value,
                billing_details: {
                    name: 'Player Name', // In a real app, get from form or user data
                }
            }
        }
    );
    
    if (error) {
        cardErrors.value = error.message;
        processing.value = false;
    } else {
        setupComplete.value = true;
        form.payment_method = setupIntent.payment_method;
        form.post(route('subscription.store'));
    }
};
</script>

<template>
    <Head title="Payment Details" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Payment Details
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Complete Your Subscription</CardTitle>
                        <CardDescription>Enter your payment details to start your 7-day free trial</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="mb-6">
                            <Alert variant="info">
                                <AlertDescription>
                                    Your card won't be charged until your free trial ends. You can cancel anytime before then.
                                </AlertDescription>
                            </Alert>
                        </div>
                        
                        <form @submit.prevent="handleSubmit">
                            <div class="space-y-4">
                                <div>
                                    <Label for="card-element">Card Details</Label>
                                    <div id="card-element" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"></div>
                                    <div v-if="cardErrors" class="mt-2 text-sm text-red-600">{{ cardErrors }}</div>
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <Button type="submit" class="w-full" :disabled="processing || setupComplete">
                                    <span v-if="processing">Processing...</span>
                                    <span v-else-if="setupComplete">Subscription Created!</span>
                                    <span v-else>Start Free Trial</span>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                    <CardFooter>
                        <p class="text-center text-sm text-gray-500">
                            Secured by Stripe. We don't store your payment information.
                        </p>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 