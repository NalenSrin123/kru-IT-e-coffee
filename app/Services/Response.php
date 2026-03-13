<?php

namespace App\Services;

class Response
{
    public static function sendSuccess($data = null, $message = "Success", $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function sendError($message = "Error", $code = 400,)
    {
        return response()->json([
            'status' => false,
            'message' => $message
        ], $code);
    }
}