@extends('layouts.app')

@section('title', 'About the Developers')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">About the Developers</span>
@endsection

@section('content')
    @if (! $profile)
        <div class="bg-white border rounded-lg p-10 text-center">
            <h1 class="text-xl font-semibold text-gray-900 mb-2">About the Developers</h1>
            <p class="text-gray-500 mb-4">No profile has been set up yet.</p>
            <a href="{{ route('about.edit') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md">
                Set Up Profile
            </a>
        </div>
    @else
        <div class="max-w-3xl mx-auto">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">About the Developers</h1>
                    <p class="text-gray-500">The team behind this ERP System.</p>
                </div>
                <a href="{{ route('about.edit') }}" class="border rounded-md px-4 py-2 text-sm text-gray-600">Edit</a>
            </div>

            <div class="bg-white border rounded-lg p-8 text-center mb-6">
                @if ($profile->photo_path)
                    <img src="{{ asset('storage/' . $profile->photo_path) }}" alt="{{ $profile->name }}"
                         class="h-32 w-32 rounded-full object-cover mx-auto mb-4 border">
                @else
                    <div class="h-32 w-32 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-4xl font-semibold mx-auto mb-4">
                        {{ strtoupper(substr($profile->name, 0, 1)) }}
                    </div>
                @endif

                <h2 class="text-xl font-bold text-gray-900">{{ $profile->name }}</h2>
                <p class="text-blue-600 text-sm mb-4">{{ $profile->module_name ?? '—' }} Module</p>

                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm max-w-lg mx-auto mt-6 pt-6 border-t text-left">
                    <div>
                        <dt class="text-gray-400 text-xs uppercase">Section</dt>
                        <dd class="text-gray-800">{{ $profile->section ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs uppercase">Professor</dt>
                        <dd class="text-gray-800">{{ $profile->professor ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs uppercase">GitHub</dt>
                        <dd>
                            @if ($profile->github_url)
                                <a href="{{ $profile->github_url }}" target="_blank" class="text-blue-600 hover:underline">Repository &rarr;</a>
                            @else
                                <span class="text-gray-800">&mdash;</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            @if ($profile->summary)
                <div class="bg-white border rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-2">About This Project</h3>
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $profile->summary }}</p>
                </div>
            @endif
        </div>
    @endif
@endsection
