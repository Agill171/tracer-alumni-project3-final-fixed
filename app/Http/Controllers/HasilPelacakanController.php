<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\HasilPelacakan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HasilPelacakanController extends Controller
{
    public function create(Alumni $alumni)
    {
        return view('pelacakan.create', [
            'alumni' => $alumni,
            'statusOptions' => Alumni::statusOptions(),
        ]);
    }

    public function store(Request $request, Alumni $alumni)
    {
        $payload = $this->validatedPayload($request);
        $payload['alumni_id'] = $alumni->id;
        $payload['user_id'] = $request->user()->id;

        HasilPelacakan::create($payload);
        $this->updateStatusVerifikasiAlumni($alumni);

        return redirect()->route('alumni.show', $alumni)
            ->with('success', 'Hasil pelacakan berhasil ditambahkan.');
    }

    public function edit(HasilPelacakan $pelacakan)
    {
        return view('pelacakan.edit', [
            'pelacakan' => $pelacakan,
            'statusOptions' => Alumni::statusOptions(),
        ]);
    }

    public function update(Request $request, HasilPelacakan $pelacakan)
    {
        $pelacakan->update($this->validatedPayload($request));
        $this->updateStatusVerifikasiAlumni($pelacakan->alumni);

        return redirect()->route('alumni.show', $pelacakan->alumni)
            ->with('success', 'Hasil pelacakan berhasil diperbarui.');
    }

    public function destroy(HasilPelacakan $pelacakan)
    {
        $alumni = $pelacakan->alumni;
        $pelacakan->delete();
        $this->updateStatusVerifikasiAlumni($alumni);

        return redirect()->route('alumni.show', $alumni)
            ->with('success', 'Hasil pelacakan berhasil dihapus.');
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'status_pelacakan' => ['required', Rule::in(Alumni::statusOptions())],
            'judul_temuan' => ['nullable', 'string', 'max:255'],
            'sumber_temuan' => ['nullable', 'string', 'max:255'],
            'link_bukti' => ['nullable', 'url:http,https', 'max:2048'],
            'query_pencarian' => ['nullable', 'string', 'max:1000'],
            'ringkasan_hasil' => ['nullable', 'string', 'max:5000'],
            'tanggal_ditemukan' => ['nullable', 'date', 'before_or_equal:today'],
            'cocok_nama' => ['nullable', 'boolean'],
            'cocok_afiliasi' => ['nullable', 'boolean'],
            'cocok_timeline' => ['nullable', 'boolean'],
            'cocok_bidang' => ['nullable', 'boolean'],
        ]);

        $signals = [
            'nama' => $request->boolean('cocok_nama'),
            'afiliasi' => $request->boolean('cocok_afiliasi'),
            'timeline' => $request->boolean('cocok_timeline'),
            'bidang' => $request->boolean('cocok_bidang'),
        ];

        $score = ($signals['nama'] ? 40 : 0)
            + ($signals['afiliasi'] ? 25 : 0)
            + ($signals['timeline'] ? 20 : 0)
            + ($signals['bidang'] ? 15 : 0);

        unset(
            $validated['cocok_nama'],
            $validated['cocok_afiliasi'],
            $validated['cocok_timeline'],
            $validated['cocok_bidang']
        );

        $validated['sinyal_identitas'] = $signals;
        $validated['confidence_score'] = $score;
        $validated['kategori_kecocokan'] = HasilPelacakan::classify($score);

        return $validated;
    }

    private function updateStatusVerifikasiAlumni(Alumni $alumni): void
    {
        $pelacakanTerakhir = $alumni->hasilPelacakans()
            ->orderByDesc('tanggal_ditemukan')
            ->orderByDesc('id')
            ->first();

        $alumni->update([
            'status_verifikasi' => $pelacakanTerakhir?->status_pelacakan
                ?? Alumni::STATUS_BELUM_DILACAK,
        ]);
    }
}
