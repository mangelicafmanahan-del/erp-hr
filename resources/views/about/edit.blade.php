@extends('layouts.app')

@section('title', 'Edit Profile')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <a href="{{ route('about.show') }}" class="hover:text-gray-600">About the Developers</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Edit</span>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Profile</h1>
            <p class="text-gray-500">This information appears on the About the Developers page.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('about.update') }}" method="POST" enctype="multipart/form-data"
              class="bg-white border rounded-lg p-6 space-y-4">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-4">
                @if ($profile && $profile->photo_path)
                    <img src="{{ asset('storage/' . $profile->photo_path) }}" alt="Current photo"
                         class="h-16 w-16 rounded-full object-cover border">
                @else
                    <div class="h-16 w-16 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold">
                        {{ $profile ? strtoupper(substr($profile->name, 0, 1)) : '?' }}
                    </div>
                @endif
                <div class="flex-1">
                    <label class="text-xs text-gray-500 block mb-1">Photo (PNG/JPG, max 2MB)</label>
                    <input type="file" name="photo" accept="image/*" class="w-full text-sm">
                </div>
            </div>

            <div>
                <label class="text-sm text-gray-600">Full Name *</label>
                <input type="text" name="name" required value="{{ old('name', $profile->name ?? '') }}"
                       class="w-full border rounded-md px-3 py-2 text-sm mt-1">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-gray-600">Section</label>
                    <input type="text" name="section" value="{{ old('section', $profile->section ?? '') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Module Name</label>
                    <input type="text" name="module_name" value="{{ old('module_name', $profile->module_name ?? '') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Professor</label>
                    <input type="text" name="professor" value="{{ old('professor', $profile->professor ?? '') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">GitHub Repository URL</label>
                    <input type="url" name="github_url" placeholder="https://github.com/username/repo"
                           value="{{ old('github_url', $profile->github_url ?? '') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
            </div>

            <div>
                <label class="text-sm text-gray-600">Project Summary</label>
                <textarea name="summary" rows="5" placeholder="What is this system and what is it for?"
                          class="w-full border rounded-md px-3 py-2 text-sm mt-1">{{ old('summary', $profile->summary ?? '') }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('about.show') }}" class="border rounded-md px-4 py-2 text-sm text-gray-600">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-md">
                    Save Profile
                </button>
            </div>
        </form>
    </div>
@endsection
