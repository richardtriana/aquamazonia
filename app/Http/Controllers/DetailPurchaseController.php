<?php

namespace App\Http\Controllers;

use App\DetailPurchase;
use App\Http\Requests\StoreDetailPurchaseRequest;
use App\Http\Requests\UpdateDetailPurchaseRequest;

class DetailPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreDetailPurchaseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store($detailPurchase, $purchase_id)
    {
        $detail = new DetailPurchase();
        $detail->purchase_id = $purchase_id;
        $detail->alimento_id = $detailPurchase['food_id'] ?? null;
        $detail->recurso_id = $detailPurchase['resource_id'] ?? null;
        $detail->discount_percentage = $detailPurchase['discount_percentage'];
        $detail->discount_value = $detailPurchase['discount_value'];
        $detail->price = $detailPurchase['price'];
        $detail->total_cost = $detailPurchase['total_cost'];
        $detail->quantity = $detailPurchase['quantity'];
        $detail->item = $detailPurchase['item'];
        $detail->save();

        $kardexController = new KardexController();
        $detail->tipo = "COMPRA";
        $detail->descripcion = "Factura de compra $purchase_id";
        $detail->cantidad =  $detail->quantity;
       
        $kardexController->storeKardex($detail);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DetailPurchase  $detailPurchase
     * @return \Illuminate\Http\Response
     */
    public function show(DetailPurchase $detailPurchase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DetailPurchase  $detailPurchase
     * @return \Illuminate\Http\Response
     */
    public function edit(DetailPurchase $detailPurchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDetailPurchaseRequest  $request
     * @param  \App\DetailPurchase  $detailPurchase
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDetailPurchaseRequest $request, DetailPurchase $detailPurchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DetailPurchase  $detailPurchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(DetailPurchase $detailPurchase)
    {
        //
    }
}
