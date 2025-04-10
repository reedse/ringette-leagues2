<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                    'id' => 'price_clips_monthly',
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
                    'id' => 'price_clips_yearly',
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
        
        try {
            $user->newSubscription('clips', $planId)
                ->trialDays(7) // 7-day free trial
                ->create($paymentMethod);
                
            return redirect()->route('player.clips')->with('success', 'Subscription created successfully!');
        } catch (IncompletePayment $exception) {
            return redirect()->route('cashier.payment', [
                $exception->payment->id, 
                'redirect' => route('player.clips')
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
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