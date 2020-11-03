<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeamRequest;
use App\Tag;
use App\Traits\Flashes;
use Illuminate\Http\Request;

class AdminTeamsController extends Controller
{
    use Flashes;

    /**
     * Show list of tags (teams)
     *
     * @return view
     */
    public function index()
    {
        $teams = Tag::orderBy('name', 'ASC')->get();

        return view('teams.index', compact('teams'));
    }

    /**
     * Show form to create an tag
     *
     * @return view
     */
    public function create()
    {
        return view('teams.create');
    }

    /**
     * Show a form to edit an tag
     *
     * @param  tag $tag [description]
     * @return view
     */
    public function edit(Tag $tag)
    {
        $team = $tag;
        return view('teams.edit', compact('team'));
    }

    /**
     * Delete an tag and all their PTO
     *
     * @param  tag $tag [description]
     * @return redirect
     */
    public function delete(Tag $tag)
    {
        $tag->delete();
        $this->goodFlash('Team removed');

        return redirect()->route('admin.teams');
    }

    /**
     * Store a new tag
     *
     * @param  TagRequest $request [description]
     * @return redirect
     */
    public function store(TeamRequest $request)
    {
        $tag = Tag::create($request->all());
        $this->goodFlash($tag->name . ' Created.');

        return redirect()->route('admin.teams');
    }

    /**
     * Update an tag
     *
     * @param  Request  $request  [description]
     * @param  tag $tag [description]
     * @return redirect
     */
    public function update(TeamRequest $request, Tag $tag)
    {
        $tag->update($request->all());

        $this->goodFlash($tag->name . ' Updated.');

        return redirect()->route('admin.teams');
    }
}
