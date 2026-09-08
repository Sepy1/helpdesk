<?php

namespace App\Http\Controllers;

use App\Models\ChangelogEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChangelogController extends Controller
{
    private const TYPES = ['added', 'changed', 'fixed', 'security'];

    public function index()
    {
        $entries = ChangelogEntry::with('author:id,name')
            ->latest('created_at')
            ->latest('id')
            ->paginate(12);

        return view('it.changelog.index', compact('entries'));
    }

    public function manage(Request $request)
    {
        $entries = ChangelogEntry::with('author:id,name')
            ->latest('created_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
        $editing = $request->filled('edit')
            ? ChangelogEntry::findOrFail($request->integer('edit'))
            : null;

        return view('it.changelog.manage', compact('entries', 'editing'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published'] ? now() : null;
        ChangelogEntry::create($data);

        return back()->with('success', 'Changelog berhasil ditambahkan.');
    }

    public function update(Request $request, ChangelogEntry $changelog)
    {
        $data = $this->validated($request);
        $wasPublished = $changelog->is_published;
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published']
            ? ($wasPublished ? $changelog->published_at : now())
            : null;
        $changelog->update($data);

        return redirect()->route('it.changelog.manage')->with('success', 'Changelog berhasil diperbarui.');
    }

    public function destroy(ChangelogEntry $changelog)
    {
        $changelog->delete();

        return back()->with('success', 'Changelog berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'version' => ['nullable', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(self::TYPES)],
            'summary' => ['nullable', 'string', 'max:1000'],
            'details' => ['required', 'string', 'max:20000'],
            'release_date' => ['required', 'date'],
        ]);
    }
}
