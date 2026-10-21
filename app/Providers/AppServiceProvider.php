<?php

namespace App\Providers;

use App\Employee;
use App\Events\EmployeeDeleting;
use App\Events\PaidTimeOffApproved;
use App\Events\PaidTimeOffDeleted;
use App\Events\PaidTimeOffRequested;
use App\Events\TagDeleting;
use App\Listeners\NotifyEmployeeOfPTOApproval;
use App\Listeners\NotifyEmployeeOfPTODelete;
use App\Listeners\NotifyManagerOfPTORequest;
use App\Listeners\RemoveEmployeePTO;
use App\Listeners\RemoveTeamFromTaggables;
use App\Policies\EmployeePolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        EmployeeDeleting::class => [
            RemoveEmployeePTO::class,
        ],
        TagDeleting::class => [
            RemoveTeamFromTaggables::class,
        ],
        PaidTimeOffRequested::class => [
            NotifyManagerOfPTORequest::class,
        ],
        PaidTimeOffApproved::class => [
            NotifyEmployeeOfPTOApproval::class,
        ],
        PaidTimeOffDeleted::class => [
            NotifyEmployeeOfPTODelete::class,
        ],
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }

        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::define('view-pto', [EmployeePolicy::class, 'viewpto']);
    }
}
