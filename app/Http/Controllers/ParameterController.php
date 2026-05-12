<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\RootCause;
use App\Models\RootCauseDetail;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ParameterController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'IT') abort(403);

        $categories = Category::with('subcategories')->orderBy('name')->get();
        $rootCauses = RootCause::with('details')->orderBy('sort')->orderBy('name')->get();
        $vendors = User::where('role', 'VENDOR')->orderBy('name')->get();
        $its = User::where('role', 'IT')->orderBy('name')->get();
        $usersForAiChat = User::query()->orderBy('role')->orderBy('name')->get(['id', 'name', 'email', 'role', 'ai_chat_enabled']);
        $aiChatEnabled = AppSetting::getBool('ai_chat_enabled', true);

        return view('it.parameters', compact('categories','rootCauses','vendors','its','usersForAiChat','aiChatEnabled'));
    }

    public function saveItVisibility(Request $request)
    {
        if (auth()->user()->role !== 'IT') abort(403);

        $data = $request->validate([
            'visible' => 'nullable|array',
            'visible.*' => 'integer|exists:users,id'
        ]);

        $visible = $data['visible'] ?? [];

        // Set all IT users to not visible, then enable selected ones
        User::where('role', 'IT')->update(['visible_on_assign' => false]);
        if (!empty($visible)) {
            User::whereIn('id', $visible)->where('role', 'IT')->update(['visible_on_assign' => true]);
        }

        return back()->with('success', 'Pengaturan tampilan IT pada form pembuatan tiket disimpan.');
    }

    public function saveAiChatSetting(Request $request)
    {
        if (auth()->user()->role !== 'IT') abort(403);

        $validated = $request->validate([
            'ai_chat_users' => 'nullable|array',
            'ai_chat_users.*' => 'integer|exists:users,id',
        ]);

        $enabled = $request->boolean('ai_chat_enabled');
        $enabledUsers = $validated['ai_chat_users'] ?? [];
        AppSetting::setValue('ai_chat_enabled', $enabled ? '1' : '0');
        User::query()->update(['ai_chat_enabled' => false]);
        if (! empty($enabledUsers)) {
            User::query()->whereIn('id', $enabledUsers)->update(['ai_chat_enabled' => true]);
        }

        return back()->with('success', 'Pengaturan AI chat dan daftar user berhasil disimpan.');
    }

    public function storeCategory(Request $request)
    {
        if (auth()->user()->role !== 'IT') abort(403);
        $data = $request->validate(['name' => 'required|string|max:191']);
        Category::create(['name' => $data['name']]);
        return back()->with('success','Kategori ditambahkan.');
    }

    public function storeSubcategory(Request $request)
    {
        if (auth()->user()->role !== 'IT') abort(403);
        $data = $request->validate(['category_id' => 'required|exists:categories,id','name' => 'required|string|max:191']);
        Subcategory::create(['category_id' => $data['category_id'], 'name' => $data['name']]);
        return back()->with('success','Subkategori ditambahkan.');
    }

    public function storeRootCause(Request $request)
    {
        if (auth()->user()->role !== 'IT') abort(403);
        $data = $request->validate(['name' => 'required|string|max:191']);
        $max = RootCause::max('sort') ?? 0;
        RootCause::create(['name' => $data['name'], 'sort' => $max + 1]);
        return back()->with('success','Root cause ditambahkan.');
    }

    public function deleteCategory($id)
    {
        if (auth()->user()->role !== 'IT') abort(403);
        $cat = Category::findOrFail($id);
        $cat->delete();
        return back()->with('success','Kategori dihapus.');
    }

    public function deleteSubcategory($id)
    {
        if (auth()->user()->role !== 'IT') abort(403);
        $sub = Subcategory::findOrFail($id);
        $sub->delete();
        return back()->with('success','Subkategori dihapus.');
    }

    public function deleteRootCause($id)
    {
        if (auth()->user()->role !== 'IT') abort(403);
        $rc = RootCause::findOrFail($id);
        $rc->delete();
        return back()->with('success','Root cause dihapus.');
    }

    public function storeRootCauseDetail(Request $request)
    {
        if (auth()->user()->role !== 'IT') {
            abort(403);
        }

        $data = $request->validate([
            'root_cause_id' => 'required|exists:root_causes,id',
            'label' => 'required|string|max:191',
            'is_other' => 'nullable|boolean',
        ]);

        $isOther = $request->boolean('is_other');
        if ($isOther) {
            $exists = RootCauseDetail::where('root_cause_id', $data['root_cause_id'])->where('is_other', true)->exists();
            if ($exists) {
                return back()->with('error', 'Hanya satu opsi bertipe "Lainnya" per root cause.');
            }
        }

        $max = RootCauseDetail::where('root_cause_id', $data['root_cause_id'])->max('sort') ?? 0;
        RootCauseDetail::create([
            'root_cause_id' => $data['root_cause_id'],
            'label' => $data['label'],
            'sort' => $max + 1,
            'is_other' => $isOther,
        ]);

        return back()->with('success', 'Detail root cause ditambahkan.');
    }

    public function deleteRootCauseDetail(RootCauseDetail $detail)
    {
        if (auth()->user()->role !== 'IT') {
            abort(403);
        }
        $detail->delete();

        return back()->with('success', 'Detail root cause dihapus.');
    }
}
