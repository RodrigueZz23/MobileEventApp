<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;

class EventController extends Controller
{
    // 🔹 Lister tous les événements
    public function index()
    {
        try {
            $events = Event::all();

            $formatted = $events->map(function ($event) {
                $picturePath = $event->picture ? str_replace('"', '', $event->picture) : null;
                $pictureUrl = $picturePath
                    ? url("/storage/{$picturePath}")
                    : $this->getDefaultImageByCategory($event->categorie ?? 'Autre');

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->date,
                    'localisation' => $event->localisation,
                    'price' => $event->price,
                    'categorie' => $event->categorie,
                    'business' => $event->business,
                    'description' => $event->description,
                    'rating' => $event->rating,
                    'type' => $event->type,
                    'picture_url' => $pictureUrl,
                ];
            });

            return response()->json($formatted);

        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération des événements : {$e->getMessage()}");
            return response()->json(['error' => 'Erreur serveur'], 500);
        }
    }

    // 🔹 Afficher un seul événement
    public function show($id)
    {
        try {
            $event = Event::findOrFail($id);

            $event->picture_url = $event->picture
                ? url("/storage/{$event->picture}")
                : $this->getDefaultImageByCategory($event->categorie ?? 'Autre');

            return response()->json($event);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Événement introuvable'], 404);
        }
    }

    // 🔹 Créer un nouvel événement
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'date' => 'required|date',
                'localisation' => 'required|string|max:255',
                'price' => 'nullable|numeric',
                'categorie' => 'nullable|string|max:255',
                'business' => 'nullable|string|max:255',
                'rating' => 'nullable|numeric',
                'description' => 'nullable|string',
                'type' => 'nullable|string|max:255',
                'picture_data' => 'nullable|string', // base64
            ]);

            $picturePath = null;

            // ✅ Si une image base64 est envoyée
            if (!empty($validated['picture_data'])) {
                $imageData = base64_decode($validated['picture_data']);
                $fileName = 'events/' . uniqid() . '.jpg';
                Storage::disk('public')->put($fileName, $imageData);
                $picturePath = $fileName;
            }

            $event = Event::create([
                'title' => $validated['title'],
                'date' => $validated['date'],
                'localisation' => $validated['localisation'],
                'price' => $validated['price'] ?? 0,
                'categorie' => $validated['categorie'] ?? 'Autre',
                'business' => $validated['business'] ?? 'Inconnu',
                'rating' => $validated['rating'] ?? 0,
                'type' => $validated['type'] ?? 'Autre',
                'description' => $validated['description'] ?? '',
                'picture' => $picturePath,
            ]);

            return response()->json([
                'message' => 'Événement créé avec succès',
                'event' => $event,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur création événement : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur serveur'], 500);
        }
    }

    // 🔹 Mettre à jour un événement
    public function update(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);

            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'date' => 'nullable|date',
                'localisation' => 'nullable|string|max:255',
                'price' => 'nullable|numeric',
                'categorie' => 'nullable|string|max:255',
                'business' => 'nullable|string|max:255',
                'rating' => 'nullable|numeric',
                'description' => 'nullable|string',
                'type' => 'nullable|string|max:255',
                'picture_data' => 'nullable|string',
            ]);

            // ✅ Gérer image base64 si nouvelle image
            if (!empty($validated['picture_data'])) {
                $imageData = base64_decode($validated['picture_data']);
                $fileName = 'events/' . uniqid() . '.jpg';
                Storage::disk('public')->put($fileName, $imageData);
                $validated['picture'] = $fileName;
            }

            $event->update($validated);

            return response()->json([
                'message' => 'Événement mis à jour avec succès',
                'event' => $event,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour événement : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la mise à jour'], 500);
        }
    }

    // 🔹 Supprimer un événement
    public function destroy($id)
    {
        try {
            $event = Event::findOrFail($id);

            if ($event->picture && Storage::disk('public')->exists($event->picture)) {
                Storage::disk('public')->delete($event->picture);
            }

            $event->delete();

            return response()->json(['message' => 'Événement supprimé avec succès']);
        } catch (\Exception $e) {
            Log::error('Erreur suppression événement : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la suppression'], 500);
        }
    }

    // 🔹 Image par défaut selon catégorie
    private function getDefaultImageByCategory($category)
    {
        $defaultImages = [
            'Musique' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=200&h=200&fit=crop&q=80',
            'Art' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=200&h=200&fit=crop&q=80',
            'Sports' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=200&h=200&fit=crop&q=80',
            'Gastronomie' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=200&h=200&fit=crop&q=80',
            'Business' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=200&h=200&fit=crop&q=80',
            'Cinéma' => 'https://images.unsplash.com/photo-1489599809505-f2d4c65055e9?w=200&h=200&fit=crop&q=80',
            'Théâtre' => 'https://images.unsplash.com/photo-1507676184212-d03ab07a01bf?w=200&h=200&fit=crop&q=80',
            'Technologie' => 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=200&h=200&fit=crop&q=80',
        ];

        return $defaultImages[$category] ?? 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=200&h=200&fit=crop&q=80';
    }





    public function reserve(Request $request, $eventId)
{
    $event = Event::findOrFail($eventId);

    // Empêcher une double réservation (optionnel)
    $existing = Reservation::where('event_id', $eventId)
        ->where('user_id', auth()->id())
        ->first();

    if ($existing) {
        return response()->json([
            'message' => 'Vous avez déjà réservé cet événement.'
        ], 400);
    }

    // Création de la réservation
    $reservation = Reservation::create([
        'event_id' => $event->id,
        'user_id' => auth()->id(),
        'quantity' => $request->quantity ?? 1,
        'status' => 'confirmed'
    ]);

    return response()->json([
        'message' => 'Réservation effectuée avec succès.',
        'reservation' => $reservation
    ], 201);
}
}
