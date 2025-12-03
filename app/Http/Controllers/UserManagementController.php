<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    private function ensureIT()
    {
        $u = Auth::user();
        if (! $u) abort(401);
        if ($u->role !== 'IT') abort(403);
    }

    public function index(Request $request)
    {
        $this->ensureIT();
        $q = trim($request->get('q', ''));
        $role = $request->get('role');
        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%")
                        ->orWhere('username', 'like', "%$q%");
                });
            })
            ->when($role, function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
        return view('it.users.index', compact('users', 'q', 'role'));
    }

    public function create()
    {
        $this->ensureIT();
        return view('it.users.create');
    }

    public function store(Request $request)
    {
        $this->ensureIT();
        $data = $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,username',
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:IT,CABANG,VENDOR,ADMIN',
        ]);
        $user = User::create([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);
        return redirect()->route('it.users.index')->with('success', 'User dibuat.');
    }

    public function edit(User $user)
    {
        $this->ensureIT();
        return view('it.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureIT();
        $data = $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,username,'.$user->id,
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|in:IT,CABANG,VENDOR,ADMIN',
            'password' => 'nullable|string|min:8',
        ]);
        $user->username = $data['username'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        return redirect()->route('it.users.index')->with('success', 'User diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->ensureIT();
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        $user->delete();
        return back()->with('success', 'User dihapus.');
    }
}
