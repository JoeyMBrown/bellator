<?php

namespace Tests\Feature\Groups;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_group_sees_group_gate(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Onboarding/GroupGate'));
    }

    public function test_user_with_group_is_redirected_from_onboarding_to_dashboard(): void
    {
        $user = User::factory()->create();
        Group::factory()->withOwner($user)->create();

        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_dashboard_redirects_user_without_group_to_onboarding(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('onboarding'));
    }

    public function test_dashboard_redirects_user_with_group_to_group_show(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->withOwner($user)->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('groups.show', $group));
    }

    public function test_user_without_group_cannot_reach_workout_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('workout.create'))
            ->assertRedirect(route('onboarding'));

        $this->actingAs($user)
            ->get(route('workout.index'))
            ->assertRedirect(route('onboarding'));
    }
}
