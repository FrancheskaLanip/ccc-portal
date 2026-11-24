<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// CONTROLLER (web version): everyone can read announcements; admin/registrar can post, edit and delete.
// Which announcements a user can SEE is decided by the database (row-level security).
class AnnouncementController extends Controller
{
    // READ: newest first. Staff also get the add/edit form (?edit=ID fills it for editing)
    public function index(Request $request)
    {
        $announcements = Announcement::with('poster')->latest()->get();

        $editing = $request->filled('edit') && in_array($request->user()->role, ['admin', 'registrar'])
            ? Announcement::findOrFail($request->edit)
            : null;

        return view('announcements.index', compact('announcements', 'editing'));
    }

    // CREATE
    public function store(Request $request)
    {
        Announcement::create($this->validated($request) + ['posted_by' => $request->user()->id]);

        return redirect()->route('announcements.index')->with('success', 'Announcement posted.');
    }

    // UPDATE
    public function update(Request $request, Announcement $announcement)
    {
        $announcement->update($this->validated($request));

        return redirect()->route('announcements.index')->with('success', 'Announcement updated.');
    }

    // DELETE
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted.');
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