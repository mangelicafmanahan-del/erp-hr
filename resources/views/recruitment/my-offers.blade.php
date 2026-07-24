@extends('layouts.app')

@section('title', 'Job Offers')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Job Offers</span>
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Job Offers</h1>
        <p class="text-gray-500">Review job offers sent to your employee account.</p>
    </div>

    @if (! $employee)
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800">
            Your account is not currently linked to an employee record. Contact HR if you believe this is incorrect.
        </div>
    @else
        <div class="space-y-4">
            @forelse ($offers as $offer)
                <div class="bg-white border rounded-lg p-6">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ $offer->offered_position ?? $offer->applicant->jobVacancy?->title ?? 'Job Offer' }}
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $offer->applicant->jobVacancy?->department?->name ?? '—' }}
                                &middot;
                                {{ $offer->employment_type ?? '—' }}
                            </p>
                        </div>

                        <span class="self-start text-xs px-2 py-1 rounded-full
                            {{ $offer->status === 'accepted' ? 'bg-green-100 text-green-700' : ($offer->status === 'declined' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ ucfirst($offer->status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-5 text-sm">
                        <div>
                            <div class="text-xs text-gray-500">Salary Offered</div>
                            <div class="font-medium text-gray-900">&#8369;{{ number_format($offer->salary_offered, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Offer Date</div>
                            <div class="font-medium text-gray-900">{{ $offer->offer_date?->format('M d, Y') ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Start Date</div>
                            <div class="font-medium text-gray-900">{{ $offer->start_date?->format('M d, Y') ?? '—' }}</div>
                        </div>
                    </div>

                    @if ($offer->notes)
                        <div class="mt-5 border-t pt-4">
                            <div class="text-xs text-gray-500 mb-1">Notes from HR</div>
                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $offer->notes }}</p>
                        </div>
                    @endif

                    @if ($offer->status === 'pending')
                        <div class="flex gap-3 mt-5 border-t pt-4">
                            <form action="{{ route('my.job-offers.respond', $offer) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="accepted">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-md" onclick="return confirm('Accept this job offer?')">
                                    Accept Offer
                                </button>
                            </form>

                            <form action="{{ route('my.job-offers.respond', $offer) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="declined">
                                <button type="submit" class="border border-red-300 text-red-600 hover:bg-red-50 text-sm font-medium px-4 py-2 rounded-md" onclick="return confirm('Decline this job offer?')">
                                    Decline Offer
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white border rounded-lg p-10 text-center text-gray-400">
                    No job offers have been sent to your account yet.
                </div>
            @endforelse
        </div>
    @endif
@endsection
