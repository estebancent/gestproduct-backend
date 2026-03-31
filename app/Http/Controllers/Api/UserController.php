<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{   
    // ✅ Usando el método del modelo
    if (!auth()->user()->isAdmin()) {
        return response()->json(['message' => 'No autorizado'], 403);
    }
    return response()->json(User::with('role')->get());
}
    /**
     * Store a newly created resource in storage.
     */
 public function store(Request $request)
{
    // 🔐 Solo admin puede crear usuarios
    if (auth()->user()->role->name !== 'admin') {
        return response()->json(['message' => 'No autorizado'], 403);
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'phone' => 'nullable|string',
        'dni' => 'nullable|string|unique:users,dni',
        'address' => 'nullable|string',
        'role_id' => 'required|exists:roles,id',
        'is_active' => 'boolean',
    ]);

    //$validated['password'] = Hash::make($validated['password']);

    $user = User::create($validated);

    return response()->json([
        'message' => 'Usuario creado correctamente',
        'user' => $user
    ], 201);
}
    /**
     * Display the specified resource.
     */
   public function show($id)
{    
    if (auth()->user()->role->name !== 'admin') {
        return response()->json(['message' => 'No autorizado'], 403);
    }
    $user = User::with('role')->findOrFail($id);

    return response()->json($user);
}
    /**
     * Update the specified resource in storage.
     */
  public function update(Request $request, $id)
{
    $user = User::findOrFail($id);

    $validated = $request->validate([
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|email|unique:users,email,' . $user->id,
        'password' => 'sometimes|min:6',
        'phone' => 'nullable|string',
        'dni' => 'nullable|string',
        'address' => 'nullable|string',
        'role_id' => 'sometimes|exists:roles,id',
        'is_active' => 'sometimes|boolean',
    ]);

    // Si viene password, la hasheamos
    //if (isset($validated['password'])) {
      //  $validated['password'] = Hash::make($validated['password']);
    //}

    $user->update($validated);

    return response()->json([
        'message' => 'Usuario actualizado correctamente',
        'user' => $user
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
   public function destroy($id)
{
    $user = User::findOrFail($id);

    // 🔐 Solo admin puede eliminar
    if (auth()->user()->role->name !== 'admin') {
        return response()->json(['message' => 'No autorizado'], 403);
    }

    $user->delete();

    return response()->json([
        'message' => 'Usuario eliminado correctamente'
    ]);
}
public function restore($id)
{
    $user = User::withTrashed()->findOrFail($id);
    $user->restore();

    return response()->json([
        'message' => 'Usuario restaurado'
    ]);
}
public function toggleActive($id)
{
    $user = User::findOrFail($id);

    $user->is_active = !$user->is_active;
    $user->save();

    return response()->json([
        'message' => 'Estado actualizado',
        'is_active' => $user->is_active
    ]);
}
}
