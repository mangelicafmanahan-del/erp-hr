@extends('layouts.app')

@section('title', 'Recruitment Dashboard')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Recruitment</span>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Overview</span>
@endsection

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Recruitment Dashboard</h1>
            <p class="text-gray-500">Manage job openings, applicants, and hiring pipeline.</p>
        </div>
        <a href="{{ route('recruitment.vacancies') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md">
            + Post Vacancy
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg border p-4">
            <div class="text-xs text-gray-500">Open Vacancies</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ $totals['open_vacancies'] }}</div>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <div class="text-xs text-gray-500">Total Applicants</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ $totals['total_applicants'] }}</div>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <div class="text-xs text-gray-500">In Interviews</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ $totals['in_interviews'] }}</div>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <div class="text-xs text-gray-500">Offers Extended</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ $totals['offers_extended'] }}</div>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <div class="text-xs text-gray-500">Hired</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ $totals['hired'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-900">Open Job Vacancies</h2>
                <a href="{{ route('recruitment.vacancies') }}" class="text-blue-600 text-sm">View all vacancies &rarr;</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse ($openVacancies as $vacancy)
                    <div class="bg-white border rounded-lg p-4">
                        <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">Open</span>
                        <div class="font-semibold text-blue-600 mt-2">{{ $vacancy->title }}</div>
                        <div class="text-sm text-gray-500">{{ $vacancy->department?->name ?? '—' }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $vacancy->location ?? '—' }} &middot; {{ $vacancy->employment_type ?? '—' }}</div>
                        <div class="text-xs text-gray-400">Posted on {{ $vacancy->posted_date->format('M d, Y') }}</div>
                        <a href="{{ route('recruitment.applicants', ['job_vacancy_id' => $vacancy->id]) }}" class="text-blue-600 text-xs mt-2 inline-block">
                            {{ $vacancy->applicants_count }} Applicants
                        </a>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 sm:col-span-2">No open vacancies yet. <a href="{{ route('recruitment.vacancies') }}" class="text-blue-600">Post one</a>.</p>
                @endforelse
            </div>
        </div>

        <div>
            <h2 class="font-semibold text-gray-900 mb-3">Upcoming Interviews</h2>
            <div class="bg-white border rounded-lg divide-y">
                @forelse ($upcomingInterviews as $interview)
                    <div class="p-4">
                        <div class="font-medium text-gray-800 text-sm">{{ $interview->applicant->full_name }}</div>
                        <div class="text-xs text-gray-500">{{ $interview->applicant->jobVacancy->title }}</div>
                        <div class="text-xs text-gray-400">{{ $interview->interview_date->format('M d, Y \a\t g:i A') }}</div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 p-4">No interviews scheduled.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white border rounded-lg overflow-hidden">
        <div class="px-5 py-4 border-b flex items-center justify-between">
            <h2 class="font-semibold text-gray-900">Recent Applicants</h2>
            <a href="{{ route('recruitment.applicants') }}" class="text-blue-600 text-sm">View all &rarr;</a>
        </div>
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
                @forelse ($recentApplicants as $applicant)
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
                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">No applicants yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
