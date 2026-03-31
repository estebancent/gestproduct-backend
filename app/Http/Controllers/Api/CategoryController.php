<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  //  public function index()
   // {
     //   return response()->json(Category::withTrashed()->get());

         // Verificamos si la colección no tiene registros
    //if ($brands->isEmpty()) {
      //  return response()->json([
        //    'message' => 'No hay marcas registradas aún',
          //  'data' => []
        //], 200); // Usamos 200 porque la petición fue exitosa, aunque no haya datos
    //}

    //return response()->json($brands);
   // }
     public function index()
    {
        // Traemos incluso las eliminadas para que el admin pueda restaurarlas si quiere
        return response()->json(Category::withTrashed()->get());
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:300',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($request->name);
        $category = Category::create($validated);

        return response()->json(['message' => 'Categoría creada', 'category' => $category], 201);
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
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100|unique:categories,name,' . $id,
            'description' => 'nullable|string|max:300',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);
        return response()->json(['message' => 'Categoría actualizada', 'category' => $category]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Category::findOrFail($id)->delete();
        return response()->json(['message' => 'Categoría eliminada (SoftDelete)']);
    }

    public function restore($id)
    {
        Category::withTrashed()->findOrFail($id)->restore();
        return response()->json(['message' => 'Categoría restaurada']);
    }

    public function toggleActive1($id)
    {
        $category = Category::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();
        return response()->json(['is_active' => $category->is_active]);
    }
    
      public function toggleActive($id)
        {
            $category = Category::withTrashed()->findOrFail($id);
            $category->is_active = !$category->is_active;
            $category->save();

            return response()->json([
                'message' => 'Estado actualizado',
                'category' => $category // <--- ESTO ES VITAL
            ]);}

}
