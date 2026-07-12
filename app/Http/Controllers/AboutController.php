<?php

namespace App\Http\Controllers;

use App\Models\DeveloperProfile;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    /**
     * Public-facing About the Developers page.
     */
    public function show()
    {
        $profile = DeveloperProfile::first();

        return view('about.show', compact('profile'));
    }

    /**
     * Edit form for the profile - only one record ever exists.
     */
    public function edit()
    {
        $profile = DeveloperProfile::first();

        return view('about.edit', compact('profile'));
    }

    public function update(Request $request)
{
    $rules = [
        'name' => 'required|string|max:255',
        'section' => 'nullable|string|max:255',
        'module_name' => 'nullable|string|max:255',
        'professor' => 'nullable|string|max:255',
        'github_url' => 'nullable|url|max:255',
        'summary' => 'nullable|string',
    ];

    // Only validate the photo as a file if one was actually submitted -
    // avoids a Laravel quirk where an empty file input can still fail
    // "nullable|image" validation even when nothing was selected.
    if ($request->hasFile('photo')) {
        $rules['photo'] = 'image|max:2048'; // 2MB max
    }

    $validated = $request->validate($rules);

    $profile = DeveloperProfile::first() ?? new DeveloperProfile();

    if ($request->hasFile('photo')) {
        $validated['photo_path'] = $request->file('photo')->store('developer-photos', 'public');
    }

    $profile->fill($validated);
    $profile->save();

    return redirect()->route('about.show')->with('success', 'Profile updated.');
}
}
