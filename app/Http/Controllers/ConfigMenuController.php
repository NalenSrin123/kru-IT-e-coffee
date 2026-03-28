<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Services\Response;
use Illuminate\Support\Facades\Validator;

class ConfigmenuController extends Controller
{
    
    // ✅ GET ONLY visible menus
    public function index()
    {
        $menus = Menu::where('visible', true)->get();

        return Response::sendSuccess($menus, 'Menu retrieved successfully');
    }

    // ✅ UPDATE menu visibility
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menus'           => 'required|array',
            'menus.*.id'      => 'required|exists:menus,id',
            'menus.*.visible' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return Response::sendError('Validation Error', $validator->errors(), 422);
        }

        foreach ($request->menus as $item) {
            $menu = Menu::find($item['id']);

            if ($menu) {
                $menu->visible = (bool)$item['visible'];
                $menu->save();
            }
        }

        return Response::sendSuccess(null, 'Menu updated successfully');
    }
}