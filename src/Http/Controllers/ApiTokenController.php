<?php

namespace TeamStream\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use TeamStream\TeamStream;

class ApiTokenController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('TeamStream/API/Index', [
            'tokens' => $request->user()->tokens->map->only(['id', 'name', 'abilities', 'last_used_ago', 'created_at']),
            'availablePermissions' => app(TeamStream::class)->getPermissions(),
            'defaultPermissions' => app(TeamStream::class)->getDefaultPermissions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => Rule::in(app(TeamStream::class)->getPermissions()),
        ]);

        $token = $request->user()->createToken(
            $request->name,
            $request->permissions ?? app(TeamStream::class)->getDefaultPermissions()
        );

        return back()->with('flash', [
            'token' => explode('|', $token->plainTextToken, 2)[1],
        ]);
    }

    public function update(Request $request, string $tokenId): RedirectResponse
    {
        $request->validate([
            'permissions' => ['array'],
            'permissions.*' => Rule::in(app(TeamStream::class)->getPermissions()),
        ]);

        $token = $request->user()->tokens()->where('id', $tokenId)->firstOrFail();

        $token->forceFill(['abilities' => $request->permissions ?? []])->save();

        return back()->with('status', 'token-updated');
    }

    public function destroy(Request $request, string $tokenId): RedirectResponse
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return back()->with('status', 'token-deleted');
    }
}
