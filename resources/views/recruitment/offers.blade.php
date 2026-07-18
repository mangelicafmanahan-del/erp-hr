@extends('layouts.app')

@section('title', 'Offers')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <a href="{{ route('recruitment.dashboard') }}" class="hover:text-gray-600">Recruitment</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Offers</span>
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Offers</h1>
        <p class="text-gray-500">All job offers issued across every applicant.</p>
    </div>

    <div class="bg-white border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Applicant</th>
                    <th class="text-left px-4 py-3">Offered Position</th>
                    <th class="text-left px-4 py-3">Salary Offered</th>
                    <th class="text-left px-4 py-3">Offer Date</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($offers as $offer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $offer->applicant->full_name }}</td>
                        <td class="px-4 py-3">{{ $offer->offered_position ?? '—' }}</td>
                        <td class="px-4 py-3">&#8369;{{ number_format($offer->salary_offered, 2) }}</td>
                        <td class="px-4 py-3">{{ $offer->offer_date->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-1 rounded-full {{ $offer->status === 'accepted' ? 'bg-green-100 text-green-700' : ($offer->status === 'declined' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ ucfirst($offer->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('recruitment.applicants.show', $offer->applicant) }}" class="text-blue-600 text-xs">View Applicant</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No offers issued yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $offers->links() }}</div>
@endsection
