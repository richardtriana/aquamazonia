<?php

namespace App\Http\Controllers;

use App\Alimento;
use Illuminate\Http\Request;

class GetReportTotalController extends Controller
{
    public function reportTotalAlimentos(Request $request)
    {
        try {
            $total_items = Alimento::selectRaw('count(id) as numero_de_productos')
                ->selectRaw('SUM(cantidad) as cantidad_de_productos')
                ->selectRaw('SUM(cantidad * costo_kg ) as costo_stock');

            if ($request->category_id != '' && $request->category_id  != null && $request->category_id  != 0) {
                $total_items = $total_items
                    ->where('category_id', "$request->category_id");
            }

            if ($request->brand_id != '' && $request->brand_id  != null && $request->brand_id  != 0) {
                $total_items = $total_items
                    ->where('brand_id', "$request->brand_id");
            }

            if ($request->warehouse_id != '' && $request->warehouse_id  != null && $request->warehouse_id  != 0) {
                $total_items = $total_items
                    ->where('warehouse_id', "$request->warehouse_id");
            }


            $total_items = $total_items->first();

            return $total_items;
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'code' => 400,                
                'totals' => $total_items
              ], 400);
        }
    }
}
