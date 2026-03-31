<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        // Traemos todo, incluyendo los que están en papelera para el Dashboard
        return response()->json(
            Product::with(['category', 'brand', 'variants'])
                ->withTrashed()
                ->latest()
                ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'profit_margin' => 'nullable|numeric',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Para la foto
            // Validación de variantes (talles)
            'variants' => 'required|array|min:1',
            'variants.*.size' => 'required|string',
            'variants.*.color' => 'nullable|string',
            'variants.*.sku' => 'required|string|unique:product_variants,sku',
            'variants.*.stock' => 'required|integer|min:0',
        ]);

        try {
            return DB::transaction(function () use ($request, $validated) {
                // 1. Manejo de imagen
                if ($request->hasFile('image')) {
                    $validated['image_path'] = $request->file('image_path')->store('products', 'public');
                }

                // 2. Generar Slug
                $validated['slug'] = Str::slug($validated['name']) . '-' . time();

                // 3. Crear Producto Padre
                $product = Product::create($validated);

                // 4. Crear Variantes (Talles) automáticamente
                $product->variants()->createMany($validated['variants']);

                return response()->json([
                    'message' => 'Producto y talles creados con éxito',
                    'product' => $product->load('variants')
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
  public function show($id)
    {
        $product = Product::withTrashed()->with(['category', 'brand', 'variants'])->findOrFail($id);
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
   public function update1(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:150',
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'sometimes|exists:brands,id',
            'purchase_price' => 'sometimes|numeric',
            'sale_price' => 'sometimes|numeric',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']) . '-' . $product->id;
        }

        $product->update($validated);

        return response()->json(['message' => 'Producto actualizado', 'product' => $product]);
    }
    public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $validated = $request->validate([
        'name' => 'sometimes|string|max:150',
        'description' => 'nullable|string',
        'category_id' => 'sometimes|exists:categories,id',
        'brand_id' => 'sometimes|exists:brands,id',
        'purchase_price' => 'sometimes|numeric',
        'sale_price' => 'sometimes|numeric',
        'profit_margin' => 'nullable|numeric',
        'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'variants' => 'required|array|min:1',
        'variants.*.size' => 'required|string',
        'variants.*.color' => 'nullable|string',
        'variants.*.sku' => 'required|string',
        'variants.*.stock' => 'required|integer',
        
    ]);

    try {
        return DB::transaction(function () use ($request, $validated, $product) {
            // 1. Imagen nueva (si hay)
            if ($request->hasFile('image_path')) {
                if ($product->image_path) Storage::disk('public')->delete($product->image_path);
                $validated['image_path'] = $request->file('image_path')->store('products', 'public');
            }

            // 2. Actualizar Padre
            if (isset($validated['name'])) {
                $validated['slug'] = Str::slug($validated['name']) . '-' . $product->id;
            }
            $product->update($validated);

            // 3. Sincronizar Variantes (Lo más simple: borrar y recrear)
            // OJO: Esto es válido si no tienes IDs de variantes en el front todavía
            $product->variants()->delete(); 
            $product->variants()->createMany($validated['variants']);

            return response()->json([
                'message' => 'Producto y variantes actualizados',
                'product' => $product->load(['category', 'brand', 'variants'])
            ]);
        });
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
    }
}
    /**
     * Remove the specified resource from storage.
     */
   public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete(); // SoftDelete

        return response()->json(['message' => 'Producto enviado a papelera']);
    }

    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return response()->json(['message' => 'Producto restaurado']);
    }

    public function toggleActive($id)
    {
        $product = Product::findOrFail($id);
        $product->is_active = !$product->is_active;
        $product->save();

        return response()->json(['is_active' => $product->is_active]);
    }

    /**
     * Actualización masiva por porcentaje (Inflación Argentina)
     */
    public function massPriceUpdate(Request $request)
    {
        $request->validate([
            'percentage' => 'required|numeric',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        $factor = 1 + ($request->percentage / 100);
        
        $query = Product::query();

        // Filtros opcionales para no subirle a todo si no se quiere
        if ($request->brand_id) $query->where('brand_id', $request->brand_id);
        if ($request->category_id) $query->where('category_id', $request->category_id);

        $query->update([
            'sale_price' => DB::raw("sale_price * $factor"),
            'purchase_price' => DB::raw("purchase_price * $factor"),
        ]);

        return response()->json(['message' => "Precios actualizados por un factor de $factor"]);
    }
}
