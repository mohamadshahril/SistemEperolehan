<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'sort_by' => ['nullable', Rule::in(['name','email','created_at'])],
            'sort_dir' => ['nullable', Rule::in(['asc','desc'])],
            'page' => ['nullable','integer','min:1'],
            'per_page' => ['nullable','integer','min:1','max:100'],
        ]);

        $query = User::query()->with(['roles:id,name','permissions:id,name']);

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name','like',"%{$search}%")
                  ->orWhere('email','like',"%{$search}%");
            });
        }

        $sortBy = $request->input('sort_by','created_at');
        $sortDir = $request->input('sort_dir','desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) $request->input('per_page', 10);
        $users = $query->paginate($perPage)->withQueryString();

        return Inertia::render('users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->input('search'),
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function edit(User $user)
    {
        // Only admins can edit direct permissions
        $canManageDirectPermissions = auth()->user()?->hasRole('Admin') ?? false;

        return Inertia::render('users/Edit', [
            'user' => $user->only(['id','name','email']) + [
                'roles' => $user->roles()->select('id')->pluck('id'),
                'permissions' => $user->permissions()->select('id')->pluck('id'),
            ],
            'allRoles' => Role::select('id','name')->orderBy('name')->get(),
            'allPermissions' => Permission::select('id','name')->orderBy('name')->get(),
            'canManageDirectPermissions' => $canManageDirectPermissions,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $canManageDirectPermissions = auth()->user()?->hasRole('Admin') ?? false;

        $rules = [
            'role_ids' => ['array'],
            'role_ids.*' => ['integer','exists:roles,id'],
        ];

        // Only allow admins to update direct permissions
        if ($canManageDirectPermissions) {
            $rules['permission_ids'] = ['array'];
            $rules['permission_ids.*'] = ['integer','exists:permissions,id'];
        }

        $data = $request->validate($rules);

        $user->roles()->sync($data['role_ids'] ?? []);

        // Only sync permissions if admin
        if ($canManageDirectPermissions) {
            $user->permissions()->sync($data['permission_ids'] ?? []);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }
}
