<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | ERP System HR Module</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800">
<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-60 shrink-0 bg-slate-900 text-slate-300 flex flex-col">
        <div class="flex items-center gap-2 px-5 py-5 border-b border-slate-800">
            <div class="h-8 w-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-sm">HR</div>
            <div>
                <div class="text-white font-semibold leading-tight">ERP System</div>
                <div class="text-xs text-blue-400 leading-tight">HR Module</div>
            </div>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md {{ request()->routeIs('dashboard') ? 'bg-slate-700 text-white' : 'hover:bg-slate-800' }}">
                Dashboard
            </a>

            <div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wide text-slate-500">Employee Records</div>
            <a href="{{ route('employees.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md {{ request()->routeIs('employees.index') ? 'bg-slate-700 text-white' : 'hover:bg-slate-800' }}">
                Employee Directory
            </a>
            <a href="{{ route('employees.create') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md {{ request()->routeIs('employees.create') ? 'bg-slate-700 text-white' : 'hover:bg-slate-800' }}">
                Add Employee
            </a>

            <div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wide text-slate-500">Payroll</div>
            <a href="{{ route('payroll.dashboard') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md {{ request()->routeIs('payroll.dashboard') ? 'bg-slate-700 text-white' : 'hover:bg-slate-800' }}">
                Payroll Dashboard
            </a>
            <a href="{{ route('payroll.runs.create') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md {{ request()->routeIs('payroll.runs.create') ? 'bg-slate-700 text-white' : 'hover:bg-slate-800' }}">
                Run Payroll
            </a>

            <div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wide text-slate-500">Recruitment</div>
            <a href="{{ route('recruitment.dashboard') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md {{ request()->routeIs('recruitment.dashboard') ? 'bg-slate-700 text-white' : 'hover:bg-slate-800' }}">
                Recruitment Dashboard
            </a>
            <a href="{{ route('recruitment.vacancies') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md {{ request()->routeIs('recruitment.vacancies') ? 'bg-slate-700 text-white' : 'hover:bg-slate-800' }}">
                Job Vacancies
            </a>
            <a href="{{ route('recruitment.applicants') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md {{ request()->routeIs('recruitment.applicants') || request()->routeIs('recruitment.applicants.*') ? 'bg-slate-700 text-white' : 'hover:bg-slate-800' }}">
                Applicants
            </a>
            <a href="{{ route('recruitment.interviews') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md {{ request()->routeIs('recruitment.interviews') ? 'bg-slate-700 text-white' : 'hover:bg-slate-800' }}">
                Interviews
            </a>
            <a href="{{ route('recruitment.offers') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md {{ request()->routeIs('recruitment.offers') ? 'bg-slate-700 text-white' : 'hover:bg-slate-800' }}">
                Offers
            </a>

            {{-- Not yet built - placeholders only, matching the reference UI's structure.
                 Building these out is future work, not part of this delivery. --}}
            <div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wide text-slate-500">Attendance &amp; Leave</div>
            <a href="{{ route('attendance.log') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md {{ request()->routeIs('attendance.log') ? 'bg-slate-700 text-white' : 'hover:bg-slate-800' }}">
                Attendance Log
            </a>
            <a href="{{ route('attendance.leave') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md {{ request()->routeIs('attendance.leave') ? 'bg-slate-700 text-white' : 'hover:bg-slate-800' }}">
                Leave Requests
            </a>

            {{-- Not yet built - placeholders only, matching the reference UI's structure.
                 Building these out is future work, not part of this delivery. --}}
            <div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wide text-slate-500">Other Modules</div>
            <span class="flex items-center gap-2 px-3 py-2 rounded-md opacity-40 cursor-not-allowed">Reports</span>
        </nav>

        <div class="px-3 py-4 border-t border-slate-800 text-sm space-y-1">
            <a href="{{ route('about.show') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-md {{ request()->routeIs('about.*') ? 'bg-slate-700 text-white' : 'hover:bg-slate-800' }}">
                About the Developers
            </a>
            <span class="flex items-center gap-2 px-3 py-2 rounded-md opacity-40 cursor-not-allowed">Settings</span>
        </div>
    </aside>

    {{-- Main column --}}
    <div class="flex-1 flex flex-col min-w-0">
        {{-- Top navbar --}}
        <header class="h-16 bg-white border-b flex items-center justify-between px-6 shrink-0">
            <div class="text-gray-400 text-sm">@yield('breadcrumb', '')</div>
            <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-semibold">
                    A
                </div>
                <span class="text-sm text-gray-600">Admin User</span>
            </div>
        </header>

        <main class="flex-1 p-6">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
