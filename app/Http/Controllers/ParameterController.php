<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\RequestType;
use App\Models\RootCause;
use App\Models\RootCauseDetail;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ParameterController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'IT') abort(403);

        $categories = Category::with(['subcategories.requestTypes'])->orderBy('name')->get();
        $rootCauses = RootCause::with('details')->orderBy('sort')->orderBy('name')->get();
        $its = User::where('role', 'IT')->orderBy('name')->get();
        $usersForAiChat = User::query()->orderBy('role')->orderBy('name')->get(['id', 'name', 'email', 'role', 'ai_chat_enabled']);
        $aiChatEnabled = AppSetting::getBool('ai_chat_enabled', true);
        $appTheme = AppSetting::getValue(
            'default_app_theme',
            AppSetting::getValue('app_theme', 'blue')
        );

        return view('it.parameters', compact('categories','rootCauses','its','usersForAiChat','aiChatEnabled','appTheme'));
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

    public function saveTheme(Request $request)
    {
        if (auth()->user()->role !== 'IT') abort(403);

        $data = $request->validate([
            'theme' => ['required', Rule::in(['blue', 'emerald', 'rose', 'violet', 'amber', 'midnight', 'obsidian', 'deep_navy', 'dark_forest', 'burgundy'])],
        ]);

        AppSetting::setValue('default_app_theme', $data['theme']);

        return back()->with('success', 'Tema default aplikasi berhasil diperbarui untuk seluruh user.');
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

    public function updateCategoryStatus(Request $request, Category $category)
    {
        if (auth()->user()->role !== 'IT') abort(403);

        $category->update(['is_enabled' => $request->boolean('is_enabled')]);

        return back()->with('success', 'Status kategori berhasil diperbarui.');
    }

    public function updateSubcategoryStatus(Request $request, Subcategory $subcategory)
    {
        if (auth()->user()->role !== 'IT') abort(403);

        $subcategory->update(['is_enabled' => $request->boolean('is_enabled')]);

        return back()->with('success', 'Status subkategori berhasil diperbarui.');
    }

    public function storeRequestType(Request $request)
    {
        if (auth()->user()->role !== 'IT') abort(403);
        $data = $request->validate([
            'subcategory_id' => 'required|exists:subcategories,id',
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('request_types', 'name')->where(
                    fn ($query) => $query->where('subcategory_id', $request->input('subcategory_id'))
                ),
            ],
        ]);
        RequestType::create($data);
        return back()->with('success', 'Jenis permintaan ditambahkan.');
    }

    public function updateRequestTypeStatus(Request $request, RequestType $requestType)
    {
        if (auth()->user()->role !== 'IT') abort(403);
        $requestType->update(['is_enabled' => $request->boolean('is_enabled')]);
        return back()->with('success', 'Status jenis permintaan berhasil diperbarui.');
    }

    public function deleteRequestType(RequestType $requestType)
    {
        if (auth()->user()->role !== 'IT') abort(403);
        $requestType->delete();
        return back()->with('success', 'Jenis permintaan dihapus.');
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
