<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ScoreController extends Controller
{
    public function getRTScore()
    {
        try {
            $rtScore = DB::select('
            SELECT 
                nama,
                nisn,
                MAX(CASE WHEN nama_pelajaran = "ARTISTIC" THEN skor ELSE 0 END) AS artistic,
                MAX(CASE WHEN nama_pelajaran = "CONVENTIONAL" THEN skor ELSE 0 END) AS conventional,
                MAX(CASE WHEN nama_pelajaran = "ENTERPRISING" THEN skor ELSE 0 END) AS enterprising,
                MAX(CASE WHEN nama_pelajaran = "INVESTIGATIVE" THEN skor ELSE 0 END) AS investigative,
                MAX(CASE WHEN nama_pelajaran = "REALISTIC" THEN skor ELSE 0 END) AS realistic,
                MAX(CASE WHEN nama_pelajaran = "SOCIAL" THEN skor ELSE 0 END) AS social
            FROM nilai
            WHERE materi_uji_id = 7
            GROUP BY nama, nisn
            ORDER BY nama
        ');

            $formattedData = collect($rtScore)->map(function ($item) {
                return [
                    'name' => $item->nama,
                    'nisn' => $item->nisn,
                    'nilaiRt' => [
                        'artistic' => (int)$item->artistic,
                        'conventional' => (int)$item->conventional,
                        'enterprising' => (int)$item->enterprising,
                        'investigative' => (int)$item->investigative,
                        'realistic' => (int)$item->realistic,
                        'social' => (int)$item->social,
                    ],
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'RT score retrieved successfully',
                'data' => $formattedData,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while retrieving RT score data',
                'data' => null,
            ], 500);
        }
    }
}
