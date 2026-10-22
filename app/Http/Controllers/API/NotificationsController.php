<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\FeedingPrelog;
use App\ParametrosAguaCeladorPrelog;
use App\ParametrosAguaAvanzadosPrelog;

class NotificationsController extends Controller
{
    public function counters()
    {
        return response()->json([
            // Preregistros de alimentación
            "feeding_prelogs" => FeedingPrelog::whereNull('synced_at')->count(),

            // Preregistros de agua (celador)
            "water_prelogs" => ParametrosAguaCeladorPrelog::whereNull('synced_at')->count(),

            // Preregistros de agua avanzados
            "water_advanced_prelogs" => ParametrosAguaAvanzadosPrelog::whereNull('synced_at')->count(),
        ]);
    }
}
