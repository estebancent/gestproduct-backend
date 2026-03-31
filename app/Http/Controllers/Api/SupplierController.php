<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        return response()->json(Supplier::withTrashed()->get());
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'cuit' => 'nullable|string|max:20|unique:suppliers,cuit',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier = Supplier::create($validated);
        return response()->json(['message' => 'Proveedor guardado', 'supplier' => $supplier], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
     public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:150',
            'cuit' => 'nullable|string|max:20|unique:suppliers,cuit,' . $id,
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $supplier->update($validated);
        return response()->json(['message' => 'Proveedor actualizado', 'supplier' => $supplier]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();
        return response()->json(['message' => 'Proveedor desactivado']);
    }

    public function restore($id)
    {
        Supplier::withTrashed()->findOrFail($id)->restore();
        return response()->json(['message' => 'Proveedor restaurado']);
    }

    public function toggleActive1($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->is_active = !$supplier->is_active;
        $supplier->save();
        return response()->json(['is_active' => $supplier->is_active]);
    }
     public function toggleActive($id)
        {
            $supplier = Supplier::withTrashed()->findOrFail($id);
            $supplier->is_active = !$supplier->is_active;
            $supplier->save();

            return response()->json([
                'message' => 'Estado actualizado',
                'supplier' => $supplier // <--- ESTO ES VITAL
            ]);}
}
