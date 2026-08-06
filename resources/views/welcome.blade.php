<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <main class="max-w-6xl mx-auto px-6 py-16 lg:py-24">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <section>
                <span class="inline-flex px-3 py-1 rounded-full bg-blue-500/15 text-blue-300 text-sm font-medium mb-5">
                    Daily Project 3 · Rekayasa Kebutuhan
                </span>
                <h1 class="text-4xl sm:text-5xl font-bold leading-tight">Sistem Pelacakan Alumni</h1>
                <p class="mt-5 text-lg text-slate-300 leading-relaxed">
                    Aplikasi web untuk mengelola data master alumni, menyiapkan query pencarian publik, mencatat kandidat, menghitung confidence score, melakukan verifikasi manual, dan menyimpan jejak bukti.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 font-semibold">Buka Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 font-semibold">Login Admin</a>
                    @endauth
                </div>
            </section>

            <section class="rounded-3xl border border-white/10 bg-white/5 p-7 shadow-2xl">
                <h2 class="text-xl font-semibold mb-5">Alur Utama</h2>
                <ol class="space-y-4">
                    @foreach([
                        'Kelola profil target alumni dan data pendukung.',
                        'Buat query untuk Google, LinkedIn, GitHub, Scholar, ORCID, dan sumber lain.',
                        'Catat tautan kandidat serta sinyal kecocokan identitas.',
                        'Sistem menghitung confidence score dan kategori kecocokan.',
                        'Admin menetapkan status alumni dan menyimpan jejak bukti.',
                    ] as $index => $step)
                        <li class="flex gap-4">
                            <span class="shrink-0 size-8 rounded-full bg-blue-600 flex items-center justify-center font-bold">{{ $index + 1 }}</span>
                            <span class="text-slate-200 pt-1">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>
    </main>
</body>
</html>
