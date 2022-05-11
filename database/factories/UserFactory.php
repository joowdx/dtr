<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Laravel\Jetstream\Features;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * Indicate that the user should have a personal team.
     *
     * @return $this
     */
    public function withPersonalTeam()
    {
        if (! Features::hasTeamFeatures()) {
            return $this->state([]);
        }

        return $this->has(
            Team::factory()
                ->state(function (array $attributes, User $user) {
                    return ['name' => $user->name.'\'s Team', 'user_id' => $user->id, 'personal_team' => true];
                }),
            'ownedTeams'
        );
    }

    public function pgso()
    {
        return $this->state([
            'assignee' => 'JUDE C. PINEDA',
            'position' => 'PICT OFFICER',
            'username' => 'pgso',
            'password' => '$2y$10$7Z.IrLwDkXBA5fJB.i0n8O8LN9Sowb5mKKXDM7S6ZsixTLKq2Ht.S'
        ]);
    }

    public function admin()
    {
        return $this->state([
            'assignee' => 'GENE PHILIP L. RELLANOS',
            'position' => 'ADMINISTRATIVE CLERK II',
            'username' => 'admin',
            'password' => '$2y$10$wuXwNwXKmlQ4mPi1PIsfNOjnI2.rYOStzupp49v9FGIXcDBd88z8O'
        ]);
    }

    public function accounting()
    {
        return $this->state([
            'assignee' => 'GENE PHILIP L. RELLANOS',
            'position' => 'ADMINISTRATIVE CLERK II',
            'username' => 'accounting',
            'password' => '$2y$10$wuXwNwXKmlQ4mPi1PIsfNOjnI2.rYOStzupp49v9FGIXcDBd88z8O'
        ]);
    }

    public function lobby()
    {
        return $this->state([
            'username' => 'lobby',
            'password' => '$2y$10$CoLZ83/3QdiKmd4icWC6AeKpqtNTXHBM8zjcjNMZemtD4JfczwBQS'
        ]);
    }
}
