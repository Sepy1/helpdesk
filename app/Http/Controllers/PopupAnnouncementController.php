<?php

namespace App\Http\Controllers;

use App\Models\PopupAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PopupAnnouncementController extends Controller
{
    private function ensureIT(): void
    {
        if (! auth()->check() || auth()->user()->role !== 'IT') {
            abort(403);
        }
    }

    public function index()
    {
        $this->ensureIT();

        $popups = PopupAnnouncement::query()->orderByDesc('is_active')->orderBy('sort_order')->latest()->get();

        return view('it.popups.index', compact('popups'));
    }

    public function store(Request $request)
    {
        $this->ensureIT();

        $data = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_active' => 'nullable|boolean',
            'desktop_active' => 'nullable|boolean',
            'mobile_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('popup-announcements', 'public');
        }

        PopupAnnouncement::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image_path' => $imagePath,
            'is_active' => $request->boolean('is_active'),
            'desktop_active' => $request->boolean('desktop_active'),
            'mobile_active' => $request->boolean('mobile_active'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ]);

        return back()->with('success', 'Popup berhasil ditambahkan.');
    }

    public function update(Request $request, PopupAnnouncement $popup)
    {
        $this->ensureIT();

        $data = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_active' => 'nullable|boolean',
            'desktop_active' => 'nullable|boolean',
            'mobile_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        if ($request->hasFile('image')) {
            if ($popup->image_path) {
                Storage::disk('public')->delete($popup->image_path);
            }
            $popup->image_path = $request->file('image')->store('popup-announcements', 'public');
        }

        $popup->fill([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'desktop_active' => $request->boolean('desktop_active'),
            'mobile_active' => $request->boolean('mobile_active'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ])->save();

        return back()->with('success', 'Popup diperbarui.');
    }

    public function destroy(PopupAnnouncement $popup)
    {
        $this->ensureIT();

        if ($popup->image_path) {
            Storage::disk('public')->delete($popup->image_path);
        }

        $popup->delete();

        return back()->with('success', 'Popup dihapus.');
    }
}
