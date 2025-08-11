<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\SalesOrderCreated::class => [
            \App\Listeners\CreateJournalEntry::class,
        ],
        \App\Events\SalesOrderUpdated::class => [
            \App\Listeners\HandleSalesOrderJournalUpdate::class,
        ],
        \App\Events\SalesOrderDeleted::class => [
            \App\Listeners\HandleSalesOrderJournalDeletion::class,
        ],
        \App\Events\PurchaseOrderCreated::class => [
            \App\Listeners\CreatePurchaseJournalEntry::class,
        ],
        \App\Events\PurchaseOrderUpdated::class => [
            \App\Listeners\HandlePurchaseOrderJournalUpdate::class,
        ],
        \App\Events\PurchaseOrderDeleted::class => [
            \App\Listeners\HandlePurchaseOrderJournalDeletion::class,
        ],
        \App\Events\DeliveryNoteCreated::class => [
            \App\Listeners\CreateDeliveryNoteJournalEntry::class,
        ],
        \App\Events\DeliveryNoteUpdated::class => [
            \App\Listeners\HandleDeliveryNoteJournalUpdate::class,
        ],
        \App\Events\DeliveryNoteDeleted::class => [
            \App\Listeners\HandleDeliveryNoteJournalDeletion::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
