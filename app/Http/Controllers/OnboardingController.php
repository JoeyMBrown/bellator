<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function show(Request $request): RedirectResponse|Response
    {
        if ($request->user()->groupMemberships()->exists()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Onboarding/GroupGate');
    }
}
