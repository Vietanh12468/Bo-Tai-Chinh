<?php

namespace App\Http\Middleware;

use App\ApiResponser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\CryptoHelper;

class EncryptDecryptMiddleware
{
    use ApiResponser;

    public function handle(Request $request, Closure $next): Response
    {
        // Step 1: Decrypt incoming payload
        // if ($request->isJson()) {
        //     $payload = json_decode($request->getContent(), true);

        //     if (isset($payload)) {
        //         try {
        //             $decrypted = CryptoHelper::customDecrypt($payload);

        //             // Replace the request body with decrypted content
        //             // This updates $request->input() and $request->json()
        //             $request->replace($decrypted);

        //             // Also update the raw content so getContent() returns the decrypted JSON
        //             $newJson = json_encode($decrypted, JSON_UNESCAPED_UNICODE);
        //             $request->server->set('CONTENT_LENGTH', strlen($newJson));
        //             $request->setJson($newJson);
        //         } catch (\Exception $e) {
        //             return response()->json([
        //                 'error' => 'Invalid or corrupt encrypted payload',
        //                 'details' => $e->getMessage(),
        //             ], 400);
        //         }
        //     }
        // }

        // if ($request->isJson()) {
        //     $payload = json_decode($request->getContent(), true);

        //     if (isset($payload)) {
        //         try {
        //             // use for test
        //             // $encrypted =[];
        //             // foreach ($payload as $key => $value) {
        //             //     if (is_string($value)) {
        //             //         // Attempt to decrypt string values
        //             //         $encrypted[$key] = CryptoHelper::encryptData($value);
        //             //     } elseif (is_array($value)) {
        //             //         // Recursively decrypt arrays
        //             //         $encrypted[$key] = CryptoHelper::encryptData(json_encode($value));
        //             //     } else {
        //             //         // Non-string, non-array values remain unchanged
        //             //         $encrypted[$key] = $value;
        //             //     }
        //             // }
        //             // dd($encrypted);

        //             $decrypted = [];
        //             foreach ($payload as $key => $value) {
        //                 $decrypted[$key] = CryptoHelper::decryptData($value);
        //                 try {
        //                     // Attempt to decode JSON strings
        //                     $jsonDecoded = json_decode($decrypted[$key], true);
        //                     if (json_last_error() === JSON_ERROR_NONE) {
        //                         $decrypted[$key] = $jsonDecoded;
        //                     }
        //                 } catch (\Exception $e) {
        //                     // If decoding fails, keep the original decrypted value
        //                 }
        //             }

        //             // Replace the request body with decrypted content
        //             // This updates $request->input() and $request->json()
        //             $request->replace($decrypted);

        //             // Also update the raw content so getContent() returns the decrypted JSON
        //             $newJson = json_encode($decrypted, JSON_UNESCAPED_UNICODE);
        //             $request->server->set('CONTENT_LENGTH', strlen($newJson));
        //         } catch (\Exception $e) {
        //             return response()->json([
        //                 'error' => 'Invalid or corrupt encrypted payload',
        //                 'details' => $e->getMessage(),
        //             ], 400);
        //         }
        //     }
        // }

        // use for test
        // $payload = $request->all();
        // $encrypted = CryptoHelper::encryptData($payload);
        // dd($encrypted);

        if (($request->isMethod('post') || $request->isMethod('delete')) && (!$request->has('payload') || empty($request->input('payload')))) {
            // Create an error response and skip calling the controller ($next).
            // We keep HTTP 200 so the outgoing-encryption step still runs; include the real status inside the body.
            $response = $this->sendError(__('notification.api.missing_payload'));
        } else {

            // If it's a POST and there's no payload, return an error
            if ($request->has('payload')) {
                $payload = $request->input('payload');

                try {
                    $decrypted = CryptoHelper::decryptData($payload);

                    // Replace the request body with decrypted content
                    // This updates $request->input() and $request->json()
                    $request->replace($decrypted);

                    // Also update the raw content so getContent() returns the decrypted JSON
                    $newJson = json_encode($decrypted, JSON_UNESCAPED_UNICODE);
                    $request->server->set('CONTENT_LENGTH', strlen($newJson));
                } catch (\Exception $e) {
                    return response()->json([
                        'error' => 'Invalid or corrupt encrypted payload',
                        'details' => $e->getMessage(),
                    ], 400);
                }
            }

            // Step 2: Continue request to controller
            $response = $next($request);
        }

        // Step 3: Encrypt outgoing response (if it’s JSON)
        if ($response->isSuccessful() && $response->headers->get('content-type') === 'application/json') {
            $originalData = json_decode($response->getContent(), true);
            $encrypted = CryptoHelper::encryptData($originalData);
            $response->setContent(json_encode(["response" => $encrypted], JSON_UNESCAPED_UNICODE));
        }

        return $response;
    }
}
