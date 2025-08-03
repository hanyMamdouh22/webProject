<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Requests\NoteStoreRequest;
use App\Http\Requests\NoteUpdateRequest;

class NoteController extends Controller
{
    public function index(): View
    {
        $notes = auth()->user()->notes()->latest()->paginate(5);
        $i = (request()->input('page', 1) - 1) * 5;
        return view('notes.index', compact('notes', 'i'));

    }

    public function create(): View
    {
        return view('notes.create');
    }

    public function store(NoteStoreRequest $request): RedirectResponse
    {
        $request->user()->notes()->create($request->validated());
        return redirect()->route('notes.index')
            ->with('success', 'Note created successfully.');
    }

    public function show(Note $note): View
    {
        return view('notes.show', compact('note'));
    }

    public function edit(Note $note): View
    {
        return view('notes.edit', compact('note'));
    }

    public function update(NoteUpdateRequest $request, Note $note): RedirectResponse
    {
        $note->update($request->validated()); // ✅ بدل create()
        return redirect()->route('notes.index')
            ->with('success', 'Note updated successfully.');
    }

    public function destroy(Note $note): RedirectResponse
    {
        $note->delete();
        return redirect()->route('notes.index')
            ->with('success', 'Note deleted successfully');
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        $notes = Note::where('user_id', auth()->id()) 
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%$query%")
                  ->orWhere('content', 'LIKE', "%$query%");
            })
            ->get();

        return response()->json($notes);
    }
}
