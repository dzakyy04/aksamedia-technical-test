<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ScoreController extends Controller
{
    /**
     * @OA\Get(
     *     path="/nilaiRT",
     *     summary="Get RT Scores",
     *     description="Retrieves the RT scores for different categories: Artistic, Conventional, Enterprising, Investigative, Realistic, and Social.",
     *     tags={"Scores"},
     *     @OA\Response(
     *         response=200,
     *         description="RT scores retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="RT scores retrieved successfully"),
     *             @OA\Property(
     *                 property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Name of Student"),
     *                     @OA\Property(property="nisn", type="string", example="NISN of Student"),
     *                     @OA\Property(
     *                         property="nilaiRt", type="object",
     *                         @OA\Property(property="artistic", type="integer", example=75),
     *                         @OA\Property(property="conventional", type="integer", example=80),
     *                         @OA\Property(property="enterprising", type="integer", example=70),
     *                         @OA\Property(property="investigative", type="integer", example=85),
     *                         @OA\Property(property="realistic", type="integer", example=90),
     *                         @OA\Property(property="social", type="integer", example=65)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred while retrieving RT scores data"),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/nilaiST",
     *     summary="Get ST Scores",
     *     description="Retrieves the ST scores including verbal, quantitative, reasoning, and figural scores.",
     *     tags={"Scores"},
     *     @OA\Response(
     *         response=200,
     *         description="ST scores retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="ST scores retrieved successfully"),
     *             @OA\Property(
     *                 property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Name of Student"),
     *                     @OA\Property(property="nisn", type="string", example="NISN of Student"),
     *                     @OA\Property(
     *                         property="listNilai", type="object",
     *                         @OA\Property(property="verbal", type="number", format="float", example=83.34),
     *                         @OA\Property(property="kuantitatif", type="number", format="float", example=58.67),
     *                         @OA\Property(property="penalaran", type="number", format="float", example=100.00),
     *                         @OA\Property(property="figural", type="number", format="float", example=23.81)
     *                     ),
     *                     @OA\Property(property="total", type="number", format="float", example=265.82)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred while retrieving ST scores data")
     *         )
     *     )
     * )
     */
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
