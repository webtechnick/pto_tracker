<?php

namespace App\Listeners;

use App\Events\TagDeleting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class RemoveTeamFromTaggables
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  EmployeeDeleting  $event
     * @return void
     */
    public function handle(TagDeleting $event)
    {
        if ($event->tag) {
            DB::table('taggables')->where('tag_id', $event->tag->id)->delete();
        }
    }
}
