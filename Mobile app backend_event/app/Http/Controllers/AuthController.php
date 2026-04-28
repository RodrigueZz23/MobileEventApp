<?php

namespace App\Http\Controllers;

use App\Models\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function getUser()
    {
    $user = User::all();
    return $user;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


   public function login(Request $request)
{
    // Validate the request
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    // Find user by email
    $user = \App\Models\User::where('email', $validated['email'])->first();

    // Check if user exists and password is correct
    if (!$user || !\Illuminate\Support\Facades\Hash::check($validated['password'], $user->password)) {
        return response()->json([
            'message' => 'Email ou mot de passe incorrect.',
        ], 401);
    }

    // Generate token
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Connexion réussie !',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ],
        'token' => $token,
        'role' => $user->role, // Double assurance
    ], 200);
}

    /**
     * Store a newly created resource in storage.
     */
   public function register(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);


        return response()->json([
            'message' => 'User created successfully!',
            'user' => $user,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(Request $request)
{
    // Récupérer l'utilisateur connecté via le token (Sanctum)
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
    }

    // Validation des champs
    $validated = $request->validate([
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|string|email|unique:users,email,' . $user->id,
        'password' => 'sometimes|string|min:6|confirmed', // si tu veux require password_confirmation
    ]);

    // Mise à jour conditionnelle
    if (isset($validated['name'])) {
        $user->name = $validated['name'];
    }

    if (isset($validated['email'])) {
        $user->email = $validated['email'];
    }

    if (isset($validated['password'])) {
        $user->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
    }

    $user->save();

    return response()->json([
        'message' => 'Profil mis à jour avec succès.',
        'user' => $user,
    ], 200);
}


    /**
     * Update the specified resource in storage.
     */


    /**
     * Remove the specified resource from storage.
     */

public function destroy(Request $request)
{
    $user = $request->user(); //

    if (!$user) {
        return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
    }

    try {
        $user->delete();

        return response()->json(['message' => 'Compte supprimé avec succès.'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Erreur lors de la suppression du compte.'], 500);
    }
}

}
