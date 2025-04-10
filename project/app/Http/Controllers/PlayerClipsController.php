<?php

namespace App\Http\Controllers;

use App\Models\Clip;
use App\Models\Player;
use App\Models\ClipPlayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PlayerClipsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $player = Player::where('user_id', $user->id)->first();
        
        if (!$player) {
            return Inertia::render('Player/Clips', [
                'error' => 'No player profile found for your account.'
            ]);
        }
        
        // Check if player has an active subscription
        $hasSubscription = false; // In a real app, check Cashier/Stripe subscription status
        
        // If player doesn't have subscription, don't query clips
        if (!$hasSubscription) {
            return Inertia::render('Player/Clips', [
                'hasSubscription' => false,
                'clips' => []
            ]);
        }
        
        // Get clips shared with the player
        $clips = ClipPlayer::with(['clip.game', 'clip.creator'])
            ->where('player_id', $player->id)
            ->get()
            ->map(function ($clipPlayer) {
                $clip = $clipPlayer->clip;
                $game = $clip->game;
                
                return [
                    'id' => $clip->id,
                    'title' => $clip->title ?? 'Game Clip - ' . $game->scheduled_date,
                    'description' => $clip->description,
                    'videoUrl' => $game->video_url,
                    'startTime' => $clip->start_time,
                    'endTime' => $clip->end_time,
                    'createdAt' => $clip->created_at,
                    'notes' => $clipPlayer->notes,
                    'game' => [
                        'id' => $game->id,
                        'date' => $game->scheduled_date,
                        'homeTeam' => $game->homeTeam->name,
                        'awayTeam' => $game->awayTeam->name,
                        'score' => $game->home_score . '-' . $game->away_score,
                    ],
                    'creator' => [
                        'name' => $clip->creator->name,
                    ],
                ];
            });
            
        return Inertia::render('Player/Clips', [
            'hasSubscription' => true,
            'clips' => $clips
        ]);
    }
    
    public function subscribe(Request $request)
    {
        $user = Auth::user();
        
        // Redirect to Stripe checkout
        return redirect()->route('subscription.checkout');
    }
} 