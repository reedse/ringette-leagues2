<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Alert, AlertDescription } from '@/Components/ui/alert';

const props = defineProps({
    intent: Object
});

const stripe = ref(null);
const elements = ref(null);
const cardElement = ref(null);
const cardErrors = ref('');
const processing = ref(false);
const setupComplete = ref(false);

// Get the plan ID from the URL query parameters
const planId = new URLSearchParams(window.location.search).get('plan') || 
    import.meta.env.VITE_MONTHLY_PRICE_ID || 'price_clips_monthly';

const form = useForm({
    payment_method: '',
    plan: planId
});

onMounted(() => {
    // Reset form state if coming back to this page
    processing.value = false;
    setupComplete.value = false;
    cardErrors.value = '';
    
    console.log('Stripe key available:', !!import.meta.env.VITE_STRIPE_KEY);
    console.log('Window.Stripe available:', !!window.Stripe);
    
    if (window.Stripe) {
        try {
            stripe.value = window.Stripe(import.meta.env.VITE_STRIPE_KEY);
            elements.value = stripe.value.elements();
            cardElement.value = elements.value.create('card');
            cardElement.value.mount('#card-element');
            
            cardElement.value.on('change', (event) => {
                cardErrors.value = event.error ? event.error.message : '';
            });
            console.log('Stripe element mounted successfully');
        } catch (error) {
            console.error('Error initializing Stripe:', error);
            cardErrors.value = 'Error initializing payment form. Please try again.';
        }
    } else {
        console.error('Stripe.js not loaded');
        cardErrors.value = 'Payment processing is unavailable. Please try again later.';
    }
});

const handleSubmit = async () => {
    if (processing.value) return;
    
    processing.value = true;
    cardErrors.value = '';
    console.log('Starting payment processing...');
    
    try {
        if (!props.intent?.client_secret) {
            throw new Error('Missing payment intent. Please refresh the page and try again.');
        }
        
        console.log('Confirming card setup...');
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
            console.error('Card setup error:', error);
            cardErrors.value = error.message;
            processing.value = false;
            return;
        }
        
        console.log('Card setup successful, payment method ID:', setupIntent.payment_method);
        form.payment_method = setupIntent.payment_method;
        
        // Use standard form post with Inertia
        form.post(route('subscription.store'), {
            preserveScroll: true,
            onSuccess: (page) => {
                console.log('Subscription created successfully');
                setupComplete.value = true;
                // Let the server redirect
            },
            onError: (errors) => {
                console.error('Form validation errors:', errors);
                if (errors.message && errors.message.includes('Stripe customer')) {
                    // Special handling for customer creation issues
                    cardErrors.value = 'We had trouble setting up your billing account. Please refresh and try again.';
                } else {
                    cardErrors.value = errors.message || 'There was an error processing your subscription.';
                }
                processing.value = false;
            },
            onFinish: () => {
                // If still processing after 5 seconds, something went wrong
                setTimeout(() => {
                    if (processing.value && !setupComplete.value) {
                        console.log('Request completed but UI not updated - resetting state');
                        processing.value = false;
                        cardErrors.value = 'The payment process timed out. Please check your subscription status or try again.';
                    }
                }, 5000);
            }
        });
        
    } catch (e) {
        console.error('Unhandled error during payment processing:', e);
        cardErrors.value = e.message || 'An unexpected error occurred. Please try again.';
        processing.value = false;
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