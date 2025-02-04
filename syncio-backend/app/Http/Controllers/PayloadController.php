<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PayloadController extends Controller
{
    public function receivePayload(Request $request)
    {
        $payload = $request->json()->all();

        if (!Cache::has('payload_1')) {
            Cache::forever('payload_1', $payload);
            return response()->json(['message' => 'Payload 1 received.'], 200);
        } elseif (!Cache::has('payload_2')) {
            Cache::forever('payload_2', $payload);
            return response()->json(['message' => 'Payload 2 received, comparison ready.'], 200);
        } else {
            return response()->json(['error' => 'Both payloads have already been received.'], 400);
        }
    }


    public function comparePayloads()
    {
        $payload1 = Cache::get('payload_1');
        $payload2 = Cache::get('payload_2');

        if (empty($payload1) || empty($payload2)) {
            return response()->json(['error' => 'Both payloads must be received first.'], 400);
        }

        $differences = $this->arrayDiffAssocRecursive($payload1, $payload2);
        return response()->json(['differences' => $differences], 200);
    }


    private function arrayDiffAssocRecursive($array1, $array2)
    {
        $difference = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value) && isset($array2[$key]) && is_array($array2[$key])) {
                $new_diff = $this->arrayDiffAssocRecursive($value, $array2[$key]);
                if (!empty($new_diff)) {
                    $difference[$key] = $new_diff;
                }
            } elseif (!isset($array2[$key]) || $array2[$key] !== $value) {
                $difference[$key] = ['old' => $value, 'new' => $array2[$key] ?? null];
            }
        }
        return $difference;
    }
}
