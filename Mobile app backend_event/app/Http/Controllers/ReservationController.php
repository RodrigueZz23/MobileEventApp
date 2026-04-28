<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    // 🟦 1. Lister les réservations de l'utilisateur
    public function index()
    {
        $userId = auth()->id();

        $reservations = Reservation::with('event')   // charger l'événement lié
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Formater l'image
        $reservations->transform(function ($r) {
            if ($r->event) {
                $r->event->picture_url =
                    $r->event->picture
                        ? url('/storage/' . $r->event->picture)
                        : null;
            }
            return $r;
        });

        return response()->json($reservations);
    }

    // 🟥 2. Annuler une réservation
    public function destroy($id)
    {
        $reservation = Reservation::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json([
            'message' => 'Réservation annulée avec succès'
        ]);
    }
}
