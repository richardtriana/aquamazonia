<?php

namespace App\Http\Controllers;

use App\Alimento;
use App\Kardex;
use App\Http\Requests\StoreKardexRequest;
use App\Http\Requests\UpdateKardexRequest;
use App\Recursos;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KardexController extends Controller
{
    const NRO_RESULTS = 15;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type = $request->type ?: 'alimento';
        $classType = $type == 'alimento' ? Alimento::class : Recursos::class;
        $from = $request->from;
        $to = $request->to;
        $product = $request->product;
        $category = $request->category;
        $nro_results = $request->nro_results ?: self::NRO_RESULTS;

        $productData = null;
        if ($request->filled('product') && $product != 'undefined') {
            $productData =  $classType::where('id', 'LIKE', "$product")
                ->orWhere($type, 'LIKE', "%$product%")
                ->firstOrFail();
        }

        $detail_kardex = Kardex::orderBy('fecha', 'desc')
            ->where(function ($query) use ($type) {
                if ($type == 'alimento') {
                    $query->whereNotNull('alimento_id');
                } else {
                    $query->whereNotNull('recurso_id');
                }
            })
            ->where(function ($query) use ($from, $to) {
                if ($from != '' && $from != 'undefined' && $from != null) {
                    $from = Carbon::parse($from)->toDateTimeString();
                    $query->where('created_at', '>=', $from);
                }
                if ($to != '' && $to != 'undefined' && $to != null) {
                    $to = Carbon::parse($to)->addSeconds(59)->toDateTimeString();
                    $query->where('created_at', '<=', $to);
                }
            })
            ->whereHas($type, function ($query) use ($category, $product, $request, $type) {
                if ($request->filled('category') && $category != 'undefined') {
                    $query->where('category_id', $category);
                }

                if ($request->filled('product') && $product != 'undefined') {
                    $query->where('id', 'LIKE', "$product")
                        ->orWhere($type, 'LIKE', "%$product%");
                }
            })
            ->paginate($nro_results);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'kardexes' => $detail_kardex,
            'product_data' => $productData
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreKardexRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreKardexRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Kardex  $kardex
     * @return \Illuminate\Http\Response
     */
    public function show(Kardex $kardex)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Kardex  $kardex
     * @return \Illuminate\Http\Response
     */
    public function edit(Kardex $kardex)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateKardexRequest  $request
     * @param  \App\Kardex  $kardex
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateKardexRequest $request, Kardex $kardex)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Kardex  $kardex
     * @return \Illuminate\Http\Response
     */
    public function destroy(Kardex $kardex)
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     ** @param array {
     * $product_id, $type, $quantity, $description
     * }
     * @return \Illuminate\Http\Response     
     */
    public function storeKardex($request)
    {

        $kardex = Kardex::create([
            'recurso_id' => $request->recurso_id ?? null,
            'alimento_id' => $request->alimento_id ?? null,
            'cantidad' => $request->cantidad,
            'tipo' => $request->tipo,
            'descripcion' => $request->descripcion,
            // 'user_id' => Auth::user()->id,
            'fecha' => Carbon::now()
        ]);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Registro exitoso',
            'kardex' => $kardex
        ], 200);
    }

    public function  updateKardexAndItem($request)
    {
        $kardex = Kardex::create([
            'recurso_id' => $request->recurso_id ?? null,
            'alimento_id' => $request->alimento_id ?? null,
            'cantidad' => $request->cantidad,
            'tipo' => $request->tipo,
            'descripcion' => $request->descripcion,
            // 'user_id' => Auth::user()->id,
            'fecha' => Carbon::now()
        ]);

        $cantidad = $request->cantidad;
        if($request->process = 'sub'){
            $cantidad = $request->cantidad * -1;
        }
        if ($request->alimento_id) {
            $alimento = Alimento::find( $request->alimento_id);
            $alimento->cantidad = $alimento->cantidad + $cantidad;
            $alimento->save();
        }
        if ($request->recurso_id) {
            $recurso = Recursos::find( $request->recurso_id);            
            $recurso->cantidad = $recurso->cantidad + $cantidad;
            $recurso->save();
        }

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Registro exitoso',
            'kardex' => $kardex
        ], 200);
    }
}
