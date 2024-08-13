<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ScoreController extends Controller
{
    public function getRTScores()
    {
        try {
            $rtScores = DB::select('
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

            $formattedData = collect($rtScores)->map(function ($item) {
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
                'message' => 'RT scores retrieved successfully',
                'data' => $formattedData,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while retrieving RT scores data',
                'data' => null,
            ], 500);
        }
    }

    public function getSTScores()
    {
        try {
            $stScores = DB::select('
            SELECT 
                nama,
                nisn,
                SUM(CASE WHEN pelajaran_id = 44 THEN skor * 41.67 ELSE 0 END) AS verbal,
                SUM(CASE WHEN pelajaran_id = 45 THEN skor * 29.67 ELSE 0 END) AS kuantitatif,
                SUM(CASE WHEN pelajaran_id = 46 THEN skor * 100 ELSE 0 END) AS penalaran,
                SUM(CASE WHEN pelajaran_id = 47 THEN skor * 23.81 ELSE 0 END) AS figural
            FROM nilai
            WHERE materi_uji_id = 4
            GROUP BY nama, nisn
            ORDER BY 
                (SUM(CASE WHEN pelajaran_id = 44 THEN skor * 41.67 ELSE 0 END) +
                SUM(CASE WHEN pelajaran_id = 45 THEN skor * 29.67 ELSE 0 END) +
                SUM(CASE WHEN pelajaran_id = 46 THEN skor * 100 ELSE 0 END) +
                SUM(CASE WHEN pelajaran_id = 47 THEN skor * 23.81 ELSE 0 END)) DESC
        ');

            $formattedData = collect($stScores)->map(function ($item) {
                return [
                    'name' => $item->nama,
                    'nisn' => $item->nisn,
                    'listNilai' => [
                        'figural' => (float)$item->figural,
                        'kuantitatif' => (float)$item->kuantitatif,
                        'penalaran' => (float)$item->penalaran,
                        'verbal' => (float)$item->verbal,
                    ],
                    'total' => (float)($item->figural + $item->kuantitatif + $item->penalaran + $item->verbal),
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'ST scores retrieved successfully',
                'data' => $formattedData,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while retrieving ST scores data',
            ], 500);
        }
    }
}
