<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        $user = Auth::user();

        $firstGroupId = $user->groups()->orderBy('group_members.created_at')->value('groups.id');

        if ($firstGroupId === null) {
            return redirect()->route('onboarding');
        }

        return redirect()->route('groups.show', $firstGroupId);
    }
}
