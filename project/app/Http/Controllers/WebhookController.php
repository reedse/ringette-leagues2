<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class WebhookController extends CashierController
{
    /**
     * Handle customer subscription updated.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        Log::info('Subscription updated', $payload);
        
        return parent::handleCustomerSubscriptionUpdated($payload);
    }
    
    /**
     * Handle customer subscription deleted.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCustomerSubscriptionDeleted(array $payload)
    {
        Log::info('Subscription deleted', $payload);
        
        return parent::handleCustomerSubscriptionDeleted($payload);
    }
    
    /**
     * Handle customer subscription trial ending.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCustomerSubscriptionTrialWillEnd(array $payload)
    {
        Log::info('Subscription trial ending', $payload);
        
        // This webhook can be used to send notifications to users
        // about their upcoming trial end
        
        return $this->successMethod();
    }
    
    /**
     * Handle payment action required.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleInvoicePaymentActionRequired(array $payload)
    {
        Log::info('Payment action required', $payload);
        
        // This webhook can be used to send notifications to users
        // about required payment actions
        
        return parent::handleInvoicePaymentActionRequired($payload);
    }
} 