<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        
        // Check if user already has a subscription
        $hasSubscription = $user->subscribed('clips');
        $onTrial = $user->onTrial('clips');
        
        return Inertia::render('Subscription/Show', [
            'user' => $user,
            'intent' => $user->createSetupIntent(),
            'hasSubscription' => $hasSubscription,
            'onTrial' => $onTrial,
            'plans' => [
                [
                    'id' => env('MONTHLY_PRICE_ID', 'price_clips_monthly'),
                    'name' => 'Monthly Subscription',
                    'price' => '$4.99',
                    'interval' => 'monthly',
                    'features' => [
                        'Access to all coach shared clips',
                        'Video breakdown of your performance',
                        'Coach feedback and notes',
                    ],
                ],
                [
                    'id' => env('YEARLY_PRICE_ID', 'price_clips_yearly'),
                    'name' => 'Annual Subscription',
                    'price' => '$49.99',
                    'interval' => 'yearly',
                    'features' => [
                        'Access to all coach shared clips',
                        'Video breakdown of your performance',
                        'Coach feedback and notes',
                        '2 months free compared to monthly plan',
                        'Historical access to all past clips'
                    ],
                ]
            ]
        ]);
    }
    
    public function checkout()
    {
        return Inertia::render('Subscription/Checkout', [
            'intent' => Auth::user()->createSetupIntent()
        ]);
    }
    
    public function store(Request $request)
    {
        $user = Auth::user();
        $paymentMethod = $request->input('payment_method');
        $planId = $request->input('plan');
        
        // Log incoming request data (excluding sensitive information)
        Log::info('Subscription creation attempt', [
            'user_id' => $user->id,
            'plan' => $planId,
            'payment_method_provided' => !empty($paymentMethod)
        ]);
        
        // Validate required inputs
        if (empty($paymentMethod)) {
            Log::error('Missing payment method');
            return redirect()->back()->withErrors(['message' => 'Payment method is required']);
        }
        
        if (empty($planId)) {
            Log::error('Missing plan ID');
            return redirect()->back()->withErrors(['message' => 'Subscription plan is required']);
        }
        
        try {
            // Ensure user has a Stripe customer
            if (!$user->stripe_id) {
                try {
                    Log::info('Creating Stripe customer for user', ['user_id' => $user->id]);
                    $user->createAsStripeCustomer();
                } catch (\Exception $e) {
                    Log::error('Failed to create Stripe customer', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id
                    ]);
                    return redirect()->back()->withErrors(['message' => 'Error creating customer account: ' . $e->getMessage()]);
                }
            }
            
            // Check if payment method exists first
            try {
                // Make sure the user is linked to their payment method
                $user->updateDefaultPaymentMethod($paymentMethod);
            } catch (\Exception $e) {
                Log::error('Payment method update failed', [
                    'error' => $e->getMessage(),
                    'payment_method' => substr($paymentMethod, 0, 5) . '...'
                ]);
                return redirect()->back()->withErrors(['message' => 'Invalid payment method: ' . $e->getMessage()]);
            }
            
            // Create the subscription with a trial period
            $subscription = $user->newSubscription('clips', $planId)
                ->trialDays(7) // 7-day free trial
                ->create($paymentMethod);
            
            Log::info('Subscription created successfully', [
                'user_id' => $user->id,
                'plan' => $planId,
                'subscription_id' => $subscription->stripe_id
            ]);
                
            return redirect()->route('player.clips')->with('success', 'Subscription created successfully!');
        } catch (IncompletePayment $exception) {
            Log::warning('Incomplete payment exception', [
                'exception' => $exception->getMessage(),
                'payment_id' => $exception->payment->id
            ]);
            
            return redirect()->route('cashier.payment', [
                $exception->payment->id, 
                'redirect' => route('player.clips')
            ]);
        } catch (\Laravel\Cashier\Exceptions\InvalidStripeCustomer $e) {
            Log::error('Invalid Stripe customer', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->withErrors(['message' => 'Stripe account setup failed. Please try again.']);
        } catch (\Exception $e) {
            Log::error('Subscription creation failed', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->withErrors(['message' => 'Subscription error: ' . $e->getMessage()]);
        }
    }
    
    public function cancel(Request $request)
    {
        $user = Auth::user();
        
        if ($user->subscribed('clips')) {
            $user->subscription('clips')->cancel();
        }
        
        return redirect()->route('subscription.show')->with('success', 'Your subscription has been canceled. You will still have access until the end of your billing period.');
    }
    
    public function resume(Request $request)
    {
        $user = Auth::user();
        
        if ($user->subscription('clips')->cancelled()) {
            $user->subscription('clips')->resume();
        }
        
        return redirect()->route('subscription.show')->with('success', 'Your subscription has been resumed.');
    }
} 