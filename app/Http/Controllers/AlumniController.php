<?php

namespace App\Http\Controllers;

use App\Imports\AlumniImport;
use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(Alumni::statusOptions())],
        ]);

        $alumnis = Alumni::query()
            ->when($filters['q'] ?? null, function ($query, string $keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery
                        ->where('nama', 'like', "%{$keyword}%")
                        ->orWhere('nim', 'like', "%{$keyword}%")
                        ->orWhere('prodi', 'like', "%{$keyword}%")
                        ->orWhere('tempat_bekerja', 'like', "%{$keyword}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status_verifikasi', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('alumni.index', [
            'alumnis' => $alumnis,
            'statusOptions' => Alumni::statusOptions(),
        ]);
    }

    public function create()
    {
        return view('alumni.create', [
            'kategoriOptions' => Alumni::kategoriPekerjaanOptions(),
        ]);
    }

    public function store(Request $request)
    {
        Alumni::create($this->validatedData($request));

        return redirect()->route('alumni.index')
            ->with('success', 'Data alumni berhasil ditambahkan.');
    }

    public function show(Alumni $alumni)
    {
        $alumni->load([
            'hasilPelacakans' => fn ($query) => $query->with('user')->latest('tanggal_ditemukan')->latest('id'),
            'queryPelacakans' => fn ($query) => $query->latest('generated_at')->orderBy('prioritas'),
        ]);

        return view('alumni.show', compact('alumni'));
    }

    public function edit(Alumni $alumni)
    {
        return view('alumni.edit', [
            'alumni' => $alumni,
            'kategoriOptions' => Alumni::kategoriPekerjaanOptions(),
        ]);
    }

    public function update(Request $request, Alumni $alumni)
    {
        $alumni->update($this->validatedData($request, $alumni));

        return redirect()->route('alumni.show', $alumni)
            ->with('success', 'Data alumni berhasil diperbarui.');
    }

    public function destroy(Alumni $alumni)
    {
        $alumni->delete();

        return redirect()->route('alumni.index')
            ->with('success', 'Data alumni berhasil dihapus.');
    }

    public function importForm()
    {
        return view('alumni.import');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $path = $request->file('file_excel')->store('imports');
        Excel::queueImport(new AlumniImport, $path, 'local');

        return redirect()->route('alumni.index')
            ->with('success', 'File berhasil diterima dan sedang diproses melalui antrean. Jalankan queue worker untuk menyelesaikan import.');
    }

    private function validatedData(Request $request, ?Alumni $alumni = null): array
    {
        $currentYear = (int) now()->format('Y');

        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nim' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('alumnis', 'nim')->ignore($alumni?->id),
            ],
            'prodi' => ['nullable', 'string', 'max:150'],
            'angkatan' => ['nullable', 'digits:4', 'integer', 'min:1900', 'max:'.($currentYear + 1)],
            'tahun_lulus' => ['nullable', 'integer', 'min:1900', 'max:'.($currentYear + 1)],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
            'tempat_bekerja' => ['nullable', 'string', 'max:255'],
            'alamat_bekerja' => ['nullable', 'string', 'max:1000'],
            'posisi' => ['nullable', 'string', 'max:255'],
            'kategori_pekerjaan' => ['nullable', Rule::in(Alumni::kategoriPekerjaanOptions())],
            'linkedin' => ['nullable', 'url:http,https', 'max:2048'],
            'instagram' => ['nullable', 'url:http,https', 'max:2048'],
            'facebook' => ['nullable', 'url:http,https', 'max:2048'],
            'tiktok' => ['nullable', 'url:http,https', 'max:2048'],
            'sosmed_tempat_bekerja' => ['nullable', 'url:http,https', 'max:2048'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
