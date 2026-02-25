<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\RootCause;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ParameterController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'IT') abort(403);

        $categories = Category::with('subcategories')->orderBy('name')->get();
        $rootCauses = RootCause::orderBy('sort')->orderBy('name')->get();
        $vendors = User::where('role', 'VENDOR')->orderBy('name')->get();

        return view('it.parameters', compact('categories','rootCauses','vendors'));
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
}
