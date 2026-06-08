<?php

namespace App\Http\Controllers;

use App\Models\Icd10;
use Illuminate\Http\Request;

class Icd10Controller extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('query');
        $type = $request->get('type', 'code');
        $limit = $request->get('limit', 10);
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ]);
        }
        
        $icd10Query = Icd10::active();
        
        if ($type === 'code') {
            $icd10Query->where('code', 'LIKE', $query . '%');
        } else {
            $icd10Query->where('description', 'LIKE', '%' . $query . '%');
        }
        
        $results = $icd10Query->orderBy('code')->take($limit)->get();
        
        return response()->json([
            'success' => true,
            'data' => $results,
            'count' => $results->count()
        ]);
    }
}