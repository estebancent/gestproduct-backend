<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{   
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
    /**
     * Store: Añadir un talle nuevo a un producto existente.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'sku' => 'required|string|unique:product_variants,sku',
            'barcode' => 'nullable|string|unique:product_variants,barcode',
            'size' => 'required|string',
            'color' => 'nullable|string',
            'stock' => 'integer|min:0',
            'min_stock' => 'integer|min:0',
        ]);

        $variant = ProductVariant::create($validated);

        return response()->json([
            'message' => 'Variante añadida con éxito',
            'variant' => $variant
        ], 201);
    }

    /**
     * Update: Cambiar SKU, talle o stock mínimo de una variante.
     */
    public function update(Request $request, $id)
    {
        $variant = ProductVariant::findOrFail($id);

        $validated = $request->validate([
            'sku' => 'sometimes|string|unique:product_variants,sku,' . $id,
            'barcode' => 'nullable|string|unique:product_variants,barcode,' . $id,
            'size' => 'sometimes|string',
            'color' => 'nullable|string',
            'min_stock' => 'sometimes|integer|min:0',
            // El stock NO se debería editar libremente aquí, pero lo dejamos por si acaso
            'stock' => 'sometimes|integer|min:0', 
        ]);

        $variant->update($validated);

        return response()->json([
            'message' => 'Variante actualizada',
            'variant' => $variant
        ]);
    }

    /**
     * Destroy: Eliminar un talle que ya no se fabricará más.
     */
    public function destroy($id)
    {
        $variant = ProductVariant::findOrFail($id);
        
        // Ojo: Si tiene stock, quizás convenga avisar al usuario
        $variant->delete();

        return response()->json(['message' => 'Variante eliminada']);
    }

    /**
     * Un método extra para ajustar stock manualmente (Ajuste de Inventario)
     * Diferente a una compra o venta.
     */
    public function updateStock(Request $request, $id)
    {
        $request->validate(['stock' => 'required|integer|min:0']);
        
        $variant = ProductVariant::findOrFail($id);
        $variant->stock = $request->stock;
        $variant->save();

        return response()->json(['message' => 'Stock ajustado manualmente', 'stock' => $variant->stock]);
    }
}