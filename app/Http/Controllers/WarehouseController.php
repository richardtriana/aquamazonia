<?php

namespace App\Http\Controllers;

use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $warehouse = $request->warehouse;

        $warehouses = Warehouse::orderBy('warehouse', 'asc')
        ->where(function ($query) use ($warehouse) {
			if (!is_null($warehouse) && $warehouse != '' && $warehouse != 'all') {
				$query->where('warehouse', 'LIKE',  "%$warehouse%");
			}
		})
        ->paginate(10);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'warehouses' => $warehouses
        ]);        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWarehouseRequest $request)
    {
        $warehouse = Warehouse::create([
            'warehouse' => $request->warehouse,
            'description' => $request->description,
            'state' => $request->state
        ]);

        return $warehouse;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function show(Warehouse $warehouse)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function edit(Warehouse $warehouse)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        if ($warehouse) {
            $warehouse->warehouse = $request->input('warehouse');
            $warehouse->description = $request->input('description');
            $warehouse->state = $request->input('state');
            $warehouse->save();
    
            $data = [
                'status' => 'success',
                'code' =>  200,
                'message' => 'Actualización exitosa',
                'warehouse' =>  $warehouse
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' =>  400,
                'message' => 'Registro no encontrado',
            ];
        }
        return response()->json($data, $data['code']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse) {
            $warehouse->delete();
            $data = [
                'status' => 'success',
                'code' => 200,
                'warehouse' => $warehouse
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Registro no encontrado'
            ];
        }

        return response()->json($data, $data['code']);
    }
}
