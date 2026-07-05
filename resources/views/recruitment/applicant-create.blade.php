@extends('layouts.app')

@section('title', 'Add Applicant')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <a href="{{ route('recruitment.dashboard') }}" class="hover:text-gray-600">Recruitment</a>
    <span class="mx-1">/</span>
    <a href="{{ route('recruitment.applicants') }}" class="hover:text-gray-600">Applicants</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Add Applicant</span>
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Add Applicant</h1>
        <p class="text-gray-500">Record a new candidate against an open vacancy.</p>
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

    <form action="{{ route('recruitment.applicants.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-white border rounded-lg p-6 max-w-2xl space-y-4">
        @csrf
        <div>
            <label class="text-sm text-gray-600">Applying For *</label>
            <select name="job_vacancy_id" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                <option value="">Select Vacancy</option>
                @foreach ($vacancies as $vacancy)
                    <option value="{{ $vacancy->id }}">{{ $vacancy->title }}</option>
                @endforeach
            </select>
            @if ($vacancies->isEmpty())
                <p class="text-xs text-amber-600 mt-1">No open vacancies yet - <a href="{{ route('recruitment.vacancies') }}" class="underline">post one first</a>.</p>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm text-gray-600">First Name *</label>
                <input type="text" name="first_name" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
            </div>
            <div>
                <label class="text-sm text-gray-600">Last Name *</label>
                <input type="text" name="last_name" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
            </div>
            <div>
                <label class="text-sm text-gray-600">Email *</label>
                <input type="email" name="email" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
            </div>
            <div>
                <label class="text-sm text-gray-600">Phone</label>
                <input type="text" name="phone" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
            </div>
            <div>
                <label class="text-sm text-gray-600">Applied On *</label>
                <input type="date" name="applied_at" required value="{{ now()->format('Y-m-d') }}" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
            </div>
            <div>
                <label class="text-sm text-gray-600">Resume (PDF/DOC, max 5MB)</label>
                <input type="file" name="resume" class="w-full text-sm mt-1">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('recruitment.applicants') }}" class="border rounded-md px-4 py-2 text-sm text-gray-600">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-md">
                Add Applicant
            </button>
        </div>
    </form>
@endsection
