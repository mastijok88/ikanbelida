<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use App\Models\Periode;
use App\Models\Progress;
use App\Helpers\WhatsappHelper;
use Illuminate\Support\Facades\Http;

class LaporanController extends Controller
{
    public function index($periodeId = null)
    {
        // Ambil semua periode untuk dropdown
        $periodes = Periode::orderBy('id', 'desc')->get();

        // Default: ambil periode terakhir kalau tidak ada parameter
        $periode = $periodeId
            ? Periode::with(['tugas.progress'])->findOrFail($periodeId)
            : $periodes->first();

        // Hitung total & selesai
        // $totalJuz   = $periode->tugas->count();
        $totalJuz = $periode->tugas->where('is_additional', false)->count();
        $selesai    = $periode->tugas->filter(fn($t) => optional($t->progress->last())->status === 'selesai')->count();
        $rasio      = $totalJuz > 0 ? round(($selesai / $totalJuz) * 100, 2) : 0;

        // Ambil periode sebelumnya
        $periodeSebelumnya = Periode::where('id', '<', $periode->id)->latest('id')->first();

        $sapuBersih = [];
        if ($periodeSebelumnya) {
            $progressSemua = Progress::with(['tugas.kelompok', 'user'])
                ->whereHas('tugas', function ($q) use ($periodeSebelumnya) {
                    $q->where('periode_id', $periodeSebelumnya->id);
                })
                ->get()
                ->groupBy(function ($p) {
                    // grouping: kelompok - juz
                    return $p->tugas->kelompok->nama_kelompok . '|' . $p->tugas->juz;
                });

            $sapuBersih = Tugas::with(['kelompok', 'progress', 'user'])
                ->where('periode_id', $periodeSebelumnya->id)
                ->where('is_additional', true) // ✅ hanya tambahan
                ->get()
                ->groupBy('kelompok.nama_kelompok')
                ->map(function ($tugas) {
                    return $tugas->map(function ($t) {
                        // ambil semua progress untuk tugas ini
                        $progressList = $t->progress;
            
                        if ($progressList->isEmpty()) {
                            if ($t->is_additional) {
                                return "{$t->juz} : ⏳ belum mulai (oleh {$t->user->name})";
                            }
                            return "{$t->juz} : ⏳ belum mulai";
                        }
            
                        $adaProses = $progressList->contains('status', 'proses');
                        $adaSelesai = $progressList->contains('status', 'selesai');
            
                        if ($adaProses) {
                            return "{$t->juz} : 🚧 diproses (oleh {$t->user->name})";
                        }
            
                        if ($adaSelesai && !$adaProses) {
                            return "{$t->juz} : ✅ selesai";
                        }
            
                        return "{$t->juz} : ⏳ belum mulai";
                    })->filter()->values();
                });
        }

        // Susun teks laporan
        $laporan = "*Laporan Pelaksanaan Tilawah {$periode->nama_periode}*\n\n";
        $laporan .= "*A. Hasil {$periode->nama_periode}*\n";
        $laporan .= "Alhamdulillah pada {$periode->nama_periode} kita telah menunaikan tilawah {$selesai} Juz dari {$totalJuz} Juz di "
            . $periode->tugas->groupBy('kelompok_id')->count() . " Grup atau rasio terbaca sebesar {$rasio}%.\n";
        $laporan .= "Tahniah untuk kita semua, semoga berkah. Kekurangan " . ($totalJuz - $selesai) . " Juz in syaa Alloh akan ditunaikan dalam pekan ini oleh Tim Admin Sapu Bersih Sisa Tilawah.\n\n";

        if ($periodeSebelumnya) {
            $laporan .= "*B. Penyelesaian Sisa Tilawah {$periodeSebelumnya->nama_periode}*\n";
            $laporan .= "Alhamdulillah sisa kekurangan tilawah {$periodeSebelumnya->nama_periode} telah ditunaikan oleh Tim Admin Saber Sawah sbb:\n";

            foreach ($sapuBersih as $grup => $juzList) {
                $laporan .= "{$grup} :\n";
                if ($juzList->isEmpty()) {
                    $laporan .= "Tidak ada\n";
                } else {
                    foreach ($juzList as $j) {
                        $laporan .= "{$j}\n";   // ✅ cukup langsung tampilkan
                    }
                }
            }

            $laporan .= "\nTerima kasih Tim Admin Saber Sawah, semoga berkah bagi semua.\n\n";
        }
        
        // Tambahan doa
        $laporan .= "*C. Doa Khatam Tilawah Al Qur'an:*\n";
        $laporan .= "اللَّهُمَّ ارْحَمْنَا بِالقُرْءَانِ\n";
        $laporan .= "وَاجْعَلْهُ لَنَا إِمَامًا وَنُورًا وَهُدًا وَرَحْمَةً\n";
        $laporan .= "اللَّهُمَّ ذَكِّرْنَا مِنْهُ مَا نَسِينَا\n";
        $laporan .= "وَعَلِّمْنَا مِنْهُ مَا جَهِلْنَا\n";
        $laporan .= "وَارْزُقْنَا تِلَاوَتَهُ ءَانَآءَ الَّيْلِ وَأَطْرَافَ النَّهَارِ\n";
        $laporan .= "وَاجْعَلْهُ لَنَا حُجَّةً يَا رَبَّ الْعَالَمِينَ\n\n";
        
        $laporan .= "Allahummarhamna Bil Quran, Waj'alhulana imama wa nura wa huda wa rahmah, ";
        $laporan .= "Allahumma dzakkirna minhuma nasina Wa 'alimna minhuma jahilna.";
        $laporan .= "Warzuqna tilawatahu, ala allayli wa athra fannahar, ";
        $laporan .= "Waj 'alhulana hujjatay yaa rabbal 'alamiin.\n\n";
        
        $laporan .= "\"Ya Allah, rahmatilah kami dengan Al Quran. ";
        $laporan .= "Jadikan ia imam, cahaya, petunjuk, dan rahmat bagi kami. ";
        $laporan .= "Ya Allah, ingatkan kami tentang apa yang kami lupa ";
        $laporan .= "dan ajarkan kepada kami apa yang kami jahil. ";
        $laporan .= "Kurniakan kami untuk dapat membacanya sepanjang malam dan sepanjang siang. ";
        $laporan .= "Jadikan ia perisai kami, wahai Tuhan semesta alam.\"\n ";
        $laporan .= "aamiin aamiin aamiin 🤲\n";

        return view('laporan.index', compact('periode', 'periodes', 'laporan'));
    }



    public function kirim($periodeId)
    {
        $periode = Periode::findOrFail($periodeId);
        $laporan = $this->index($periodeId)->getData()['laporan'];

        WhatsappHelper::send(env('WA_GROUP'), $laporan);

        return back()->with('success', 'Laporan berhasil dikirim ke grup WA!');
    }
}
