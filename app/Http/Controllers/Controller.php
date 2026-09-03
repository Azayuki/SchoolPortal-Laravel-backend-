<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function Success($data = [], $message = "Succeeded!", $others = []){
        return response([
            "success" => true,
            "data" => $data,
            "message" => $message,
            "others" => $others,
        ]);
    }

    protected function BadRequest($validator, $message = "Request didn't pass the validation!", $others = []){
        return response([
            "success" => false,
            "errors" => $validator->errors(),
            "message" => $message,
            "others" => $others
        ], 400);
    }
    protected function Unauthorized($message = "Unauthorized!", $others = []){
        return response([
            "success" => false,
            "message" => $message,
            "others" => $others
        ], 401);
    }
    protected function Forbidden($message = "Permission not granted!", $others = []){
        return response([
            "success" => false,
            "message" => $message,
            "others" => $others
        ], 403);
    }
    protected function NotFound($message = "Id Not Found!", $others = []){
        return response([
            "success" => false,
            "message" => $message,
            "others" => $others
        ], 404);
    }
    protected function Created($data = [], $message = "Created!", $others = []){
        return response([
            "success" => true,
            "data" => $data,
            "message" => $message,
            "others" => $others
        ], 201);
    }

    protected function Sanitizer($name){
        $name = trim($name);
        $name = strtolower($name);
        $name = ucwords($name);

        do{
            $name = str_replace("  ", " ", $name);
        }while(str_contains($name, "  "));

        return $name;
    }
}