<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// CONTROLLER (API version): everyone reads announcements; admin/registrar post, edit and delete.
// Which announcements a user can SEE is decided by the database (row-level security).
class AnnouncementController extends Controller
{
    public function index()
    {
        return Announcement::with('poster:id,name')->latest()->get();
    }

    public function store(Request $request)
    {
        $announcement = Announcement::create($this->validated($request) + ['posted_by' => $request->user()->id]);

        return response()->json($announcement, 201);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $announcement->update($this->validated($request));

        return $announcement;
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'audience' => ['required', Rule::in(['all', 'student', 'faculty'])],
        ]);
    }
}