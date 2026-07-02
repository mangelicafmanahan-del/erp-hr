@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Home')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Dashboard</h1>
    <p class="text-gray-500 mb-6">Welcome back! Here's a quick summary.</p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg border p-5">
            <div class="text-sm text-gray-500">Total Employees</div>
            <div class="text-3xl font-bold text-gray-900 mt-1">{{ \App\Models\Employee::count() }}</div>
        </div>
        <div class="bg-white rounded-lg border p-5">
            <div class="text-sm text-gray-500">Active Employees</div>
            <div class="text-3xl font-bold text-gray-900 mt-1">{{ \App\Models\Employee::where('employment_status', 'active')->count() }}</div>
        </div>
        <div class="bg-white rounded-lg border p-5">
            <div class="text-sm text-gray-500">Departments</div>
            <div class="text-3xl font-bold text-gray-900 mt-1">{{ \App\Models\Department::count() }}</div>
        </div>
    </div>

    <div class="mt-8 rounded-lg border bg-white p-5">
        <a href="{{ route('employees.index') }}" class="text-blue-600 font-medium text-sm">Go to Employee Directory &rarr;</a>
    </div>
@endsection
