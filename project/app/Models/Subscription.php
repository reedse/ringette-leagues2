<?php

namespace App\Models;

use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Override to use 'name' column instead of 'type'
     * This is needed because our database schema uses 'name' while Cashier now expects 'type'
     */
    public function getTypeAttribute()
    {
        return $this->name;
    }

    /**
     * Override to store in 'name' column instead of 'type'
     */
    public function setTypeAttribute($value)
    {
        $this->attributes['name'] = $value;
    }
} 