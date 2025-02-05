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

        // get the unique keys from both arrays
        $allKeys = array_unique(array_merge(array_keys($array1), array_keys($array2)));


        // go through each key
        foreach ($allKeys as $key) {
            $keyExistsInArray1 = array_key_exists($key, $array1);
            $keyExistsInArray2 = array_key_exists($key, $array2);

            // checks if that key exists in both arrays
            if ($keyExistsInArray1 && $keyExistsInArray2) {
                $value1 = $array1[$key];
                $value2 = $array2[$key];
                // check if it is a nested object
                if (is_array($value1) && is_array($value2)) {
                    // compares the inner objects
                    // used recursion cus inner objects might have nested objects too and they need to be compared in the same way
                    $new_diff = $this->arrayDiffAssocRecursive($value1, $value2);
                    // if there's a diff, the comparison is stored under the key
                    if (!empty($new_diff)) {
                        $difference[$key] = $new_diff;
                    }
                // if it is not an object
                } elseif ($value1 !== $value2) {
                    $difference[$key] = ['old' => $value1, 'new' => $value2];
                }
            } elseif ($keyExistsInArray1 && !$keyExistsInArray2) {
                // key exists in payload1 but missing in payload2
                $difference[$key] = ['old' => $array1[$key], 'new' => null];
            } elseif (!$keyExistsInArray1 && $keyExistsInArray2) {
                // key exists in payload2 but missing in payload1
                $difference[$key] = ['old' => null, 'new' => $array2[$key]];
            }
        }

        return $difference;
    }


    public function resetCache()
    {
        Cache::forget('payload_1');
        Cache::forget('payload_2');

        return response()->json(['message' => 'Cache cleared, you can send new payloads.'], 200);
    }
}
