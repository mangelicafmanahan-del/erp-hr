@extends('layouts.app')

@section('title', 'Applicant Evaluation & Job Offer')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <a href="{{ route('recruitment.dashboard') }}" class="hover:text-gray-600">Recruitment</a>
    <span class="mx-1">/</span>
    <a href="{{ route('recruitment.applicants') }}" class="hover:text-gray-600">Applicants</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Applicant Evaluation & Job Offer</span>
@endsection

@section('content')
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

    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                {{ $applicant->full_name }}
                <span class="text-xs px-2 py-1 rounded-full {{ $stageColors[$applicant->status] ?? 'bg-gray-100' }}">
                    {{ ucfirst($applicant->status) }}
                </span>
            </h1>
            <p class="text-gray-500">Applying for {{ $applicant->jobVacancy->title }} &middot; {{ $applicant->jobVacancy->department?->name ?? '—' }}</p>
        </div>
        <a href="{{ route('recruitment.applicants') }}" class="border rounded-md px-4 py-2 text-sm text-gray-600">Back to Applicants</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Applicant info --}}
        <div class="bg-white border rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Applicant Info</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd>{{ $applicant->email }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd>{{ $applicant->phone ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Applied On</dt><dd>{{ $applicant->applied_at->format('M d, Y') }}</dd></div>
            </dl>
            @if ($applicant->resume_path)
                <a href="{{ asset('storage/' . $applicant->resume_path) }}" target="_blank"
                   class="block text-center border rounded-md px-4 py-2 text-sm text-blue-600 mt-4">
                    View Resume ({{ $applicant->resume_file_name }})
                </a>
            @else
                <p class="text-xs text-gray-400 mt-4">No resume uploaded.</p>
            @endif

            @if ($applicant->employee_id)
                <a href="{{ route('employees.show', $applicant->employee_id) }}"
                   class="block text-center bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium px-4 py-2 rounded-md mt-4">
                    View Employee Record
                </a>
            @elseif ($applicant->status === 'hired')
                <form action="{{ route('recruitment.applicants.convert', $applicant) }}" method="POST" class="mt-4"
                      onsubmit="return confirm('Create an Employee Records entry for this applicant?');">
                    @csrf
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium px-4 py-2 rounded-md">
                        Convert to Employee Record
                    </button>
                </form>
            @endif
        </div>

        {{-- Interviews --}}
        <div class="bg-white border rounded-lg p-6 lg:col-span-2">
            <h2 class="font-semibold text-gray-900 mb-4">Interview Evaluation</h2>

            @forelse ($applicant->interviews as $interview)
                <div class="flex justify-between items-start py-2 {{ !$loop->last ? 'border-b' : '' }} text-sm">
                    <div>
                        <div class="font-medium text-gray-800">{{ $interview->stage ?? 'Interview' }} &middot; {{ $interview->interviewer ?? '—' }}</div>
                        <div class="text-gray-500">{{ $interview->feedback ?? 'No feedback recorded.' }}</div>
                    </div>
                    <div class="text-right shrink-0 pl-4">
                        <div class="text-gray-500">{{ $interview->interview_date->format('M d, Y g:i A') }}</div>
                        @if ($interview->score)
                            <div class="font-medium">{{ $interview->score }} / 5.0</div>
                        @endif
                        @if ($interview->result)
                            <div class="text-xs">{{ $interview->result }}</div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">No interviews recorded yet.</p>
            @endforelse

            @if (in_array($applicant->status, ['offered', 'hired', 'rejected']))
                <p class="text-xs text-gray-400 border-t mt-4 pt-4">
                    A decision has already been made for this applicant, so interviews can no longer be recorded.
                </p>
            @else
                <form action="{{ route('recruitment.applicants.interviews.store', $applicant) }}" method="POST"
                      class="grid grid-cols-1 sm:grid-cols-2 gap-3 border-t mt-4 pt-4">
                    @csrf
                    <div>
                        <label class="text-xs text-gray-500">Stage</label>
                        <input type="text" name="stage" placeholder="HR Interview, Technical, Final" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Interviewer</label>
                        <input type="text" name="interviewer" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Date & Time *</label>
                        <input type="datetime-local" name="interview_date" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Score (0-5)</label>
                        <input type="number" step="0.1" min="0" max="5" name="score" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Result</label>
                        <select name="result" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                            <option value="Pending">Pending</option>
                            <option value="Passed">Passed</option>
                            <option value="Failed">Failed</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs text-gray-500">Feedback</label>
                        <textarea name="feedback" rows="2" class="w-full border rounded-md px-3 py-2 text-sm mt-1"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md">
                            Record Interview
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Decision & Job Offer --}}
        <div class="bg-white border rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Decision & Job Offer</h2>

            @if ($applicant->offer)
                <div class="text-sm mb-4 pb-4 border-b space-y-1">
                    <div class="flex justify-between"><span class="text-gray-500">Offered Position</span><span>{{ $applicant->offer->offered_position ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Salary Offered</span><span>&#8369;{{ number_format($applicant->offer->salary_offered, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Start Date</span><span>{{ optional($applicant->offer->start_date)->format('M d, Y') ?? '—' }}</span></div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Offer Status</span>
                        <span class="text-xs px-2 py-1 rounded-full {{ $applicant->offer->status === 'accepted' ? 'bg-green-100 text-green-700' : ($applicant->offer->status === 'declined' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ ucfirst($applicant->offer->status) }}
                        </span>
                    </div>
                </div>

                @if ($applicant->offer->status === 'pending')
                    <div class="flex gap-2">
                        <form action="{{ route('recruitment.offers.status', $applicant->offer) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="status" value="accepted">
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-md">
                                Mark Accepted
                            </button>
                        </form>
                        <form action="{{ route('recruitment.offers.status', $applicant->offer) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="status" value="declined">
                            <button type="submit" class="w-full border border-red-300 text-red-600 hover:bg-red-50 text-sm font-medium px-4 py-2 rounded-md">
                                Mark Declined
                            </button>
                        </form>
                    </div>
                @endif
            @else
                <p class="text-sm text-gray-400 mb-4">No offer issued yet.</p>
            @endif

            @if ($applicant->offer && in_array($applicant->offer->status, ['accepted', 'declined']))
                <p class="text-xs text-gray-400 border-t mt-4 pt-4">
                    This offer has already been {{ $applicant->offer->status }} and can no longer be revised.
                </p>
            @else
                <form action="{{ route('recruitment.applicants.offer.store', $applicant) }}" method="POST" class="space-y-3 border-t mt-4 pt-4">
                    @csrf
                    <p class="text-xs text-gray-400">{{ $applicant->offer ? 'Revise the offer:' : 'Issue a new offer:' }}</p>
                    <div>
                        <label class="text-xs text-gray-500">Offered Position</label>
                        <input type="text" name="offered_position" value="{{ $applicant->jobVacancy->title }}" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500">Employment Type</label>
                            <select name="employment_type" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Contractual">Contractual</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Salary Offered *</label>
                            <input type="number" step="0.01" min="0" name="salary_offered" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500">Offer Date *</label>
                            <input type="date" name="offer_date" required value="{{ now()->format('Y-m-d') }}" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Start Date</label>
                            <input type="date" name="start_date" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md">
                        {{ $applicant->offer ? 'Update Offer' : 'Send Offer' }}
                    </button>
                </form>
            @endif
        </div>

        {{-- Onboarding Checklist (3d) --}}
        <div class="bg-white border rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Onboarding Checklist</h2>

            @if ($applicant->onboardingTasks->isEmpty())
                <p class="text-sm text-gray-400">
                    Checklist starts automatically once the job offer is marked as <strong>Accepted</strong>.
                </p>
            @else
                @php
                    $completedCount = $applicant->onboardingTasks->where('is_completed', true)->count();
                @endphp
                <p class="text-xs text-gray-400 mb-3">{{ $completedCount }} / {{ $applicant->onboardingTasks->count() }} Completed</p>
                <div class="space-y-2">
                    @foreach ($applicant->onboardingTasks as $task)
                        <form action="{{ route('recruitment.onboarding.toggle', $task) }}" method="POST" class="flex items-center justify-between text-sm border rounded-md px-3 py-2">
                            @csrf
                            <span class="{{ $task->is_completed ? 'line-through text-gray-400' : 'text-gray-700' }}">{{ $task->task_name }}</span>
                            <button type="submit" class="text-xs px-2 py-1 rounded-full {{ $task->is_completed ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $task->is_completed ? 'Completed' : 'Pending' }}
                            </button>
                        </form>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
