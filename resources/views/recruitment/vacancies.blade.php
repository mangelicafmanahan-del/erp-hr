@extends('layouts.app')

@section('title', $isHr ? 'Job Vacancies' : 'Job Opportunities')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">{{ $isHr ? 'Recruitment / Job Vacancies' : 'Job Opportunities' }}</span>
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $isHr ? 'Job Vacancies' : 'Job Opportunities' }}</h1>
        <p class="text-gray-500">
            {{ $isHr ? 'Create and manage open positions.' : 'Explore open positions and apply using your existing employee profile.' }}
        </p>
    </div>

    @if ($isHr)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white border rounded-lg p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Post a Vacancy</h2>
                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3"><ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <form action="{{ route('recruitment.vacancies.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div><label class="text-xs text-gray-500">Job Title *</label><input type="text" name="title" required class="w-full border rounded-md px-3 py-2 text-sm mt-1"></div>
                    <div><label class="text-xs text-gray-500">Department</label><select name="department_id" class="w-full border rounded-md px-3 py-2 text-sm mt-1"><option value="">Select Department</option>@foreach ($departments as $dept)<option value="{{ $dept->id }}">{{ $dept->name }}</option>@endforeach</select></div>
                    <div><label class="text-xs text-gray-500">Employment Type</label><select name="employment_type" class="w-full border rounded-md px-3 py-2 text-sm mt-1"><option value="Full-time">Full-time</option><option value="Part-time">Part-time</option><option value="Contractual">Contractual</option></select></div>
                    <div><label class="text-xs text-gray-500">Location</label><input type="text" name="location" class="w-full border rounded-md px-3 py-2 text-sm mt-1"></div>
                    <div><label class="text-xs text-gray-500">Posted Date *</label><input type="date" name="posted_date" required value="{{ now()->format('Y-m-d') }}" class="w-full border rounded-md px-3 py-2 text-sm mt-1"></div>
                    <div><label class="text-xs text-gray-500">Closing Date</label><input type="date" name="closing_date" class="w-full border rounded-md px-3 py-2 text-sm mt-1"></div>
                    <div><label class="text-xs text-gray-500">Description</label><textarea name="description" rows="3" class="w-full border rounded-md px-3 py-2 text-sm mt-1"></textarea></div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md">Post Vacancy</button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white border rounded-lg overflow-hidden">
    @else
        <div class="bg-white border rounded-lg overflow-hidden">
    @endif
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="text-left px-4 py-3">Position</th>
                            <th class="text-left px-4 py-3">Department</th>
                            <th class="text-left px-4 py-3">Employment Type</th>
                            <th class="text-left px-4 py-3">Closing Date</th>
                            @if ($isHr)<th class="text-left px-4 py-3">Applicants</th>@endif
                            <th class="text-right px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($vacancies as $vacancy)
                            <tr class="hover:bg-gray-50 align-top">
                                <td class="px-4 py-4"><div class="font-medium text-gray-800">{{ $vacancy->title }}</div><div class="text-xs text-gray-400 mt-1">Posted {{ $vacancy->posted_date->format('M d, Y') }}</div>@if($vacancy->description)<div class="text-xs text-gray-500 mt-2 max-w-md">{{ $vacancy->description }}</div>@endif</td>
                                <td class="px-4 py-4">{{ $vacancy->department?->name ?? '—' }}</td>
                                <td class="px-4 py-4">{{ $vacancy->employment_type ?? '—' }}</td>
                                <td class="px-4 py-4">{{ $vacancy->closing_date?->format('M d, Y') ?? 'No closing date' }}</td>
                                @if ($isHr)
                                    <td class="px-4 py-4"><a href="{{ route('recruitment.applicants', ['job_vacancy_id' => $vacancy->id]) }}" class="text-blue-600">{{ $vacancy->applicants_count }}</a></td>
                                    <td class="px-4 py-4 text-right">
                                        @if ($vacancy->status === 'open')
                                            <form action="{{ route('recruitment.vacancies.close', $vacancy) }}" method="POST" onsubmit="return confirm('Close this vacancy?');">@csrf<button type="submit" class="text-gray-500 hover:text-red-600 text-xs">Close</button></form>
                                        @else
                                            <span class="text-xs text-gray-400">Closed</span>
                                        @endif
                                    </td>
                                @else
                                    <td class="px-4 py-4 text-right">
                                        @if (in_array($vacancy->id, $appliedVacancyIds))
                                            <span class="inline-flex text-xs px-3 py-1 rounded-full bg-green-100 text-green-700">Applied</span>
                                        @elseif ($employee)
                                            <form action="{{ route('recruitment.vacancies.apply', $vacancy) }}" method="POST" onsubmit="return confirm('Apply for this position?');">@csrf<button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-3 py-2 rounded-md">Apply Now</button></form>
                                        @else
                                            <span class="text-xs text-gray-400">Profile not linked</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $isHr ? 6 : 5 }}" class="px-4 py-10 text-center text-gray-400">{{ $isHr ? 'No vacancies posted yet.' : 'No open job opportunities right now.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">{{ $vacancies->links() }}</div>
            </div>
@endsection
