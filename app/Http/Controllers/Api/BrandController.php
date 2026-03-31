<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Traemos incluso las eliminadas para que el admin pueda restaurarlas si quiere
        return response()->json(Brand::withTrashed()->get());
    }
 
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:brands,name',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($request->name);
        $brand = Brand::create($validated);

        return response()->json(['message' => 'Marca creada', 'brand' => $brand], 201);
    }

    
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
   
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
      $brand = Brand::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100|unique:brands,name,' . $id,
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $brand->update($validated);
        return response()->json(['message' => 'Marca actualizada', 'brand' => $brand]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Brand::findOrFail($id)->delete();
        return response()->json(['message' => 'Marca enviada a papelera']);
    }

    public function restore($id)
    {
        $brand = Brand::withTrashed()->findOrFail($id);
        $brand->restore();
        return response()->json(['message' => 'Marca restaurada']);
    }

    public function toggleActive($id)
{
    $brand = Brand::withTrashed()->findOrFail($id);
    $brand->is_active = !$brand->is_active;
    $brand->save();

    return response()->json([
        'message' => 'Estado actualizado',
        'brand' => $brand // <--- ESTO ES VITAL
    ]);
}
    
}
