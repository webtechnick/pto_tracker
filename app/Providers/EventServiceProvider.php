<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\EmployeeDeleting' => [
            'App\Listeners\RemoveEmployeePTO',
        ],
        'App\Events\TagDeleting' => [
            'App\Listeners\RemoveTeamFromTaggables',
        ],
        'App\Events\PaidTimeOffRequested' => [
            'App\Listeners\NotifyManagerOfPTORequest',
        ],
        'App\Events\PaidTimeOffApproved' => [
            'App\Listeners\NotifyEmployeeOfPTOApproval',
        ],
        'App\Events\PaidTimeOffDeleted' => [
            'App\Listeners\NotifyEmployeeOfPTODelete',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
