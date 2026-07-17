<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log In | ERP System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800">
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm">

        <div class="flex items-center justify-center gap-2 mb-8">
            <div class="h-10 w-10 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold">HR</div>
            <div>
                <div class="text-gray-900 font-semibold leading-tight">ERP System</div>
                <div class="text-xs text-blue-600 leading-tight">HR Module</div>
            </div>
        </div>

        <div class="bg-white border rounded-lg p-8">
            <h1 class="text-xl font-bold text-gray-900 mb-1">Welcome Back</h1>
            <p class="text-sm text-gray-500 mb-6">Sign in to access your account.</p>

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.attempt') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm text-gray-600">Email Address</label>
                    <input type="email" name="email" required autofocus value="{{ old('email') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Password</label>
                    <input type="password" name="password" required
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember">
                    Remember Me
                </label>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md">
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">&copy; 2026 ERP System &mdash; HR Module.</p>
    </div>
</div>
</body>
</html>
