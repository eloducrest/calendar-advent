<?php

namespace App\Http\Controllers;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Manage the player accounts; only admins reach this controller.
 */
class PlayerController extends Controller
{
    use PasswordValidationRules;

    /**
     * Show the players and the creation form.
     */
    public function index(): Response
    {
        $players = User::query()
            ->where('role', User::ROLE_PLAYER)
            ->withCount('solvers')
            ->orderBy('name')
            ->get()
            ->map(fn (User $player): array => [
                'id' => $player->id,
                'name' => $player->name,
                'email' => $player->email,
                'solved_count' => $player->solvers_count,
                'created_at' => $player->created_at?->toDateString(),
            ]);

        return Inertia::render('players/Index', [
            'players' => $players,
        ]);
    }

    /**
     * Create a player account (email and password login, already verified).
     */
    public function store(): RedirectResponse
    {
        $validated = request()->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => $this->passwordRules(),
        ]);

        // user mass assignment is deliberately restricted by #[Fillable];
        // forceFill sets the admin-controlled fields here.
        (new User)->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_PLAYER,
            'email_verified_at' => now(),
        ])->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Joueur :name créé 🎁', ['name' => $validated['name']]),
        ]);

        return redirect()->route('players.index');
    }
}
