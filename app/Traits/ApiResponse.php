<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * សម្រាប់ពេលទាញយក ឬធ្វើប្រតិបត្តិការទូទៅជោគជ័យ (Status 200)
     */
    protected function successResponse($data, string $message = 'ប្រតិបត្តិការជោគជ័យ', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }

    /**
     * សម្រាប់ពេលបង្កើតទិន្នន័យថ្មីជោគជ័យ (Status 201)
     */
    protected function createdResponse($data, string $message = 'បង្កើតទិន្នន័យថ្មីជោគជ័យ'): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], 201);
    }

    /**
     * សម្រាប់ពេលមានបញ្ហា ឬ Error ផ្សេងៗ (Status 4xx, 5xx)
     */
    protected function errorResponse(string $message, int $statusCode = 400, $errors = null): JsonResponse
    {
        $response = [
            'status'  => 'error',
            'message' => $message,
        ];

        // បើមានលម្អិត Error (ដូចជា Validation) នោះវាលោតចូលមកទីនេះ
        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}
