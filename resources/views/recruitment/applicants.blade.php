@extends('layouts.app')

@section('title', 'Applicants')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <a href="{{ route('recruitment.dashboard') }}" class="hover:text-gray-600">Recruitment</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Applicants</span>
@endsection

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Applicants</h1>
            <p class="text-gray-500">Track candidates through the hiring pipeline.</p>
        </div>
        <a href="{{ route('recruitment.applicants.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md">
            + Add Applicant
        </a>
    </div>

    <form method="GET" action="{{ route('recruitment.applicants') }}" class="bg-white border rounded-lg p-4 mb-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by name or email..."
               class="flex-1 min-w-[220px] border rounded-md px-3 py-2 text-sm">

        <select name="job_vacancy_id" onchange="this.form.submit()" class="border rounded-md px-3 py-2 text-sm">
            <option value="">All Vacancies</option>
            @foreach ($vacancies as $vacancy)
                <option value="{{ $vacancy->id }}" @selected(request('job_vacancy_id') == $vacancy->id)>{{ $vacancy->title }}</option>
            @endforeach
        </select>

        <select name="status" onchange="this.form.submit()" class="border rounded-md px-3 py-2 text-sm">
            <option value="">All Stages</option>
            <option value="applied" @selected(request('status') === 'applied')>Applied</option>
            <option value="screening" @selected(request('status') === 'screening')>Screening</option>
            <option value="interview" @selected(request('status') === 'interview')>Interview</option>
            <option value="offered" @selected(request('status') === 'offered')>Offered</option>
            <option value="hired" @selected(request('status') === 'hired')>Hired</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
        </select>

        <a href="{{ route('recruitment.applicants') }}" class="border rounded-md px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Reset</a>
    </form>
    <p class="text-xs text-gray-400 -mt-3 mb-4">Tip: press Enter in the search box to search. Vacancy/Stage filter automatically.</p>

    <div class="bg-white border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Applicant</th>
                    <th class="text-left px-4 py-3">Job Title</th>
                    <th class="text-left px-4 py-3">Applied On</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($applicants as $applicant)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('recruitment.applicants.show', $applicant) }}" class="text-blue-600 font-medium">{{ $applicant->full_name }}</a>
                            <div class="text-xs text-gray-400">{{ $applicant->email }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $applicant->jobVacancy->title }}</td>
                        <td class="px-4 py-3">{{ $applicant->applied_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            @php
                                $stageColors = [
                                    'applied' => 'bg-gray-100 text-gray-600',
                                    'screening' => 'bg-blue-100 text-blue-700',
                                    'interview' => 'bg-purple-100 text-purple-700',
                                    'offered' => 'bg-amber-100 text-amber-700',
                                    'hired' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="text-xs px-2 py-1 rounded-full {{ $stageColors[$applicant->status] ?? 'bg-gray-100' }}">
                                {{ ucfirst($applicant->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('recruitment.applicants.show', $applicant) }}" class="text-blue-600 text-xs">Review</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">No applicants found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $applicants->links() }}</div>
@endsection
