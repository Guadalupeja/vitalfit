<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->with('branches:id,name')
            ->orderByDesc('active')
            ->orderBy('name')
            ->paginate(15);

        return view('usuarios.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('usuarios.create', compact('branches'));
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => trim($data['name']),
            'email' => trim($data['email']),
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'active' => (bool) $request->boolean('active', true),
        ]);

        $user->branches()->sync($data['branch_ids']);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        $branches = Branch::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $usuario->load('branches:id,name');

        return view('usuarios.edit', compact('usuario', 'branches'));
    }

    public function update(UserRequest $request, User $usuario)
    {
        $data = $request->validated();

        $updateData = [
            'name' => trim($data['name']),
            'email' => trim($data['email']),
            'role' => $data['role'],
            'active' => (bool) $request->boolean('active', false),
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if ((int) Auth::id() === (int) $usuario->id && $updateData['active'] === false) {
            return back()
                ->withErrors(['active' => 'No puedes desactivar tu propio usuario.'])
                ->withInput();
        }

        $usuario->update($updateData);
        $usuario->branches()->sync($data['branch_ids']);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        if ((int) Auth::id() === (int) $usuario->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->branches()->detach();
        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario eliminado.');
    }
}