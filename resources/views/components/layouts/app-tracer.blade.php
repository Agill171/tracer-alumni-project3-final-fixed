<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Tracer Alumni' }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">
    <nav class="bg-slate-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <a href="{{ route('dashboard') }}" class="block">
                <h1 class="text-xl font-bold">Tracer Alumni</h1>
                <p class="text-sm text-slate-300">Sistem Pelacakan Alumni Berbasis Sumber Publik</p>
            </a>

            <div class="flex flex-wrap items-center gap-2 text-sm">
                <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg hover:bg-slate-800">Dashboard</a>
                <a href="{{ route('alumni.index') }}" class="px-3 py-2 rounded-lg hover:bg-slate-800">Data Alumni</a>
                <a href="{{ route('alumni.import.form') }}" class="px-3 py-2 rounded-lg hover:bg-slate-800">Import</a>
                <a href="{{ route('settings.profile') }}" class="px-3 py-2 rounded-lg hover:bg-slate-800">Profil Admin</a>

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        {{ $slot }}
    </main>

    <footer class="max-w-7xl mx-auto px-4 sm:px-6 pb-8 text-center text-sm text-slate-500">
        Data hanya digunakan untuk kepentingan pelacakan alumni dan harus diverifikasi sebelum ditetapkan sebagai identitas yang sesuai.
    </footer>
</body>
</html>
