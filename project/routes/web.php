<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlayerStatsController;
use App\Http\Controllers\PlayerTeamController;
use App\Http\Controllers\PlayerScheduleController;
use App\Http\Controllers\PlayerClipsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CoachGameController;
use App\Http\Controllers\CoachTeamController;
use App\Http\Controllers\ClipController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PlayerController;
use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Common routes
    Route::get('/games', [GameController::class, 'index'])->name('games.index');
    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
    
    // Teams routes
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    
    // Players routes
    Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
    Route::get('/players/{player}', [PlayerController::class, 'show'])->name('players.show');

    // Player routes
    Route::prefix('player')->middleware(['auth', 'verified', CheckRole::class.':player'])->group(function () {
        Route::get('/stats', [PlayerStatsController::class, 'index'])->name('player.stats');
        Route::get('/team', [PlayerTeamController::class, 'index'])->name('player.team');
        Route::get('/schedule', [PlayerScheduleController::class, 'index'])->name('game.schedule');
        Route::get('/clips', [PlayerClipsController::class, 'index'])->name('player.clips');
    });

    // Subscription routes
    Route::prefix('subscription')->name('subscription.')->middleware('auth')->group(function () {
        Route::get('/', [SubscriptionController::class, 'show'])->name('show');
        Route::get('/checkout', [SubscriptionController::class, 'checkout'])->name('checkout');
        Route::post('/store', [SubscriptionController::class, 'store'])->name('store');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/resume', [SubscriptionController::class, 'resume'])->name('resume');
    });

    // Coach routes
    Route::prefix('coach')->middleware(['auth', 'verified', CheckRole::class.':coach'])->group(function () {
        // Team management routes
        Route::get('/team', [CoachTeamController::class, 'index'])->name('coach.team');
        Route::get('/team/add-player', [CoachTeamController::class, 'addPlayerForm'])->name('coach.team.add-player-form');
        Route::post('/team/search-players', [CoachTeamController::class, 'searchPlayers'])->name('coach.team.search-players');
        Route::post('/team/add-player', [CoachTeamController::class, 'addPlayer'])->name('coach.team.add-player');
        Route::post('/team/remove-player', [CoachTeamController::class, 'removePlayer'])->name('coach.team.remove-player');
        Route::post('/team/create-player', [CoachTeamController::class, 'createPlayer'])->name('coach.team.create-player');
        
        // Game management routes
        Route::get('/games', [CoachGameController::class, 'index'])->name('coach.games');
        Route::get('/games/create', [CoachGameController::class, 'create'])->name('coach.games.create');
        Route::post('/games', [CoachGameController::class, 'store'])->name('coach.games.store');
        Route::get('/games/{game}', [CoachGameController::class, 'show'])->name('coach.games.show');
        Route::get('/games/{game}/edit', [CoachGameController::class, 'edit'])->name('coach.games.edit');
        Route::put('/games/{game}', [CoachGameController::class, 'update'])->name('coach.games.update');
        Route::put('/games/{game}/player-stats', [CoachGameController::class, 'updatePlayerStats'])->name('coach.games.update-player-stats');
        Route::put('/games/{game}/penalties', [CoachGameController::class, 'updatePenalties'])->name('coach.games.update-penalties');
        
        // Clip management routes
        Route::get('/clips', [ClipController::class, 'index'])->name('coach.clips');
        Route::get('/clips/create', [ClipController::class, 'create'])->name('coach.clips.create');
        Route::post('/clips', [ClipController::class, 'store'])->name('coach.clips.store');
        Route::get('/clips/{clip}', [ClipController::class, 'show'])->name('coach.clips.show');
        Route::get('/clips/{clip}/edit', [ClipController::class, 'edit'])->name('coach.clips.edit');
        Route::put('/clips/{clip}', [ClipController::class, 'update'])->name('coach.clips.update');
        Route::delete('/clips/{clip}', [ClipController::class, 'destroy'])->name('coach.clips.destroy');
    });

    // League admin routes
    Route::prefix('admin')->middleware(['auth', 'verified', CheckRole::class.':league_admin'])->group(function () {
        Route::get('/associations', function () {
            return Inertia::render('Admin/Associations');
        })->name('admin.associations');
        
        Route::get('/leagues', function () {
            return Inertia::render('Admin/Leagues');
        })->name('admin.leagues');
        
        Route::get('/seasons', function () {
            return Inertia::render('Admin/Seasons');
        })->name('admin.seasons');
        
        // Team management routes
        Route::get('/teams', [App\Http\Controllers\AdminTeamController::class, 'index'])->name('admin.teams');
        Route::get('/teams/{team}/assign-coach', [App\Http\Controllers\AdminTeamController::class, 'assignCoachForm'])->name('admin.teams.assign-coach');
        Route::post('/teams/{team}/assign-coach', [App\Http\Controllers\AdminTeamController::class, 'assignCoach']);
        Route::post('/teams/{team}/create-coach', [App\Http\Controllers\AdminTeamController::class, 'createCoach'])->name('admin.teams.create-coach');
        Route::post('/teams/remove-coach', [App\Http\Controllers\AdminTeamController::class, 'removeCoach'])->name('admin.teams.remove-coach');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Stripe webhook route
Route::post('/webhook/stripe', [App\Http\Controllers\WebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook')
    ->middleware('throttle:60,1'); // Add rate limiting to webhook route
