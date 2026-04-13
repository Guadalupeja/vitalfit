<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class BranchSelectionController extends Controller
{
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $branches = $user->branches()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Si solo tiene una sucursal, se asigna automática
        if ($branches->count() === 1) {
            session(['current_branch_id' => $branches->first()->id]);
            return redirect()->route('agenda.index');
        }

        return view('branches.select', compact('branches'));
    }

    public function select(Request $request)
    {
        $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $branchId = (int) $request->branch_id;

        if (!$user->hasBranch($branchId)) {
            abort(403, 'No tienes acceso a esa sucursal.');
        }

        session(['current_branch_id' => $branchId]);

        return redirect()->route('agenda.index');
    }
}