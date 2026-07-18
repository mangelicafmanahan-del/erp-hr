@extends('layouts.app')

@section('title', 'Interviews')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <a href="{{ route('recruitment.dashboard') }}" class="hover:text-gray-600">Recruitment</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Interviews</span>
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Interviews</h1>
        <p class="text-gray-500">All interviews recorded across every applicant.</p>
    </div>

    <div class="bg-white border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Applicant</th>
                    <th class="text-left px-4 py-3">Job Title</th>
                    <th class="text-left px-4 py-3">Stage</th>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-left px-4 py-3">Score</th>
                    <th class="text-left px-4 py-3">Result</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($interviews as $interview)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $interview->applicant->full_name }}</td>
                        <td class="px-4 py-3">{{ $interview->applicant->jobVacancy->title }}</td>
                        <td class="px-4 py-3">{{ $interview->stage ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $interview->interview_date->format('M d, Y g:i A') }}</td>
                        <td class="px-4 py-3">{{ $interview->score ? $interview->score . ' / 5.0' : '—' }}</td>
                        <td class="px-4 py-3">{{ $interview->result ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('recruitment.applicants.show', $interview->applicant) }}" class="text-blue-600 text-xs">View Applicant</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No interviews recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $interviews->links() }}</div>
@endsection
