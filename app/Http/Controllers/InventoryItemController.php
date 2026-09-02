<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryItemRequest;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryItemController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $q = trim((string) $request->query('q', ''));
        $segment = trim((string) $request->query('segment', ''));
        $status = trim((string) $request->query('status', ''));

        $itemsQuery = InventoryItem::query()
            ->where('branch_id', current_branch_id())
            ->with(['creator:id,name', 'updater:id,name'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('product', 'like', "%{$q}%")
                        ->orWhere('presentation', 'like', "%{$q}%")
                        ->orWhere('segment', 'like', "%{$q}%")
                        ->orWhere('notes', 'like', "%{$q}%");
                });
            })
            ->when($segment !== '', function ($query) use ($segment) {
                $query->where('segment', $segment);
            })
            ->when($status === 'active', fn ($query) => $query->where('active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('active', false))
            ->when($status === 'low_stock', function ($query) {
                $query->whereNotNull('minimum_stock')
                    ->whereColumn('quantity', '<=', 'minimum_stock');
            })
            ->when($status === 'expired', function ($query) {
                $query->whereNotNull('expiration_date')
                    ->whereDate('expiration_date', '<', now()->toDateString());
            })
            ->when($status === 'expires_soon', function ($query) {
                $query->whereNotNull('expiration_date')
                    ->whereDate('expiration_date', '>=', now()->toDateString())
                    ->whereDate('expiration_date', '<=', now()->addDays(30)->toDateString());
            })
            ->orderBy('product');

        $items = (clone $itemsQuery)
            ->paginate(20)
            ->withQueryString();

        $segments = InventoryItem::query()
            ->where('branch_id', current_branch_id())
            ->whereNotNull('segment')
            ->where('segment', '!=', '')
            ->distinct()
            ->orderBy('segment')
            ->pluck('segment');

        $totalItems = (clone $itemsQuery)->count();

        $lowStockCount = InventoryItem::query()
            ->where('branch_id', current_branch_id())
            ->whereNotNull('minimum_stock')
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->count();

        $expiredCount = InventoryItem::query()
            ->where('branch_id', current_branch_id())
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<', now()->toDateString())
            ->count();

        $expiresSoonCount = InventoryItem::query()
            ->where('branch_id', current_branch_id())
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '>=', now()->toDateString())
            ->whereDate('expiration_date', '<=', now()->addDays(30)->toDateString())
            ->count();

        return view('inventario.index', compact(
            'items',
            'segments',
            'q',
            'segment',
            'status',
            'totalItems',
            'lowStockCount',
            'expiredCount',
            'expiresSoonCount'
        ));
    }

    public function create()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $item = new InventoryItem([
            'entry_date' => now()->toDateString(),
            'quantity' => 0,
            'unit' => 'piezas',
            'active' => true,
        ]);

        return view('inventario.create', compact('item'));
    }

    public function store(InventoryItemRequest $request)
    {
        $data = $request->validated();

        $data['branch_id'] = current_branch_id();
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        $data['active'] = (bool) $request->boolean('active', true);

        InventoryItem::create($data);

        return redirect()
            ->route('inventario.index')
            ->with('success', 'Producto de inventario registrado correctamente.');
    }

    public function edit(InventoryItem $inventario)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        abort_unless((int) $inventario->branch_id === current_branch_id(), 404);

        $item = $inventario;

        return view('inventario.edit', compact('item'));
    }

    public function update(InventoryItemRequest $request, InventoryItem $inventario)
    {
        abort_unless((int) $inventario->branch_id === current_branch_id(), 404);

        $data = $request->validated();

        $data['updated_by'] = Auth::id();
        $data['active'] = (bool) $request->boolean('active', false);

        $inventario->update($data);

        return redirect()
            ->route('inventario.index')
            ->with('success', 'Producto de inventario actualizado correctamente.');
    }

    public function destroy(InventoryItem $inventario)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        abort_unless((int) $inventario->branch_id === current_branch_id(), 404);

        $inventario->delete();

        return redirect()
            ->route('inventario.index')
            ->with('success', 'Producto de inventario eliminado correctamente.');
    }
}