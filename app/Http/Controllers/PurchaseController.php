<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\AlimentosController;
use App\Http\Controllers\API\RecursosController;
use App\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'purchases' => (object)[]
        ], 200);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_id =  auth()->user();

        $purchase = new Purchase;
        $purchase->provider_id = $request->id_provider;
        $purchase->user_id = $user_id;
        $purchase->total_cost = $request->total_tax_inc;
        $purchase->total_discount = $request->total_discount;
        $purchase->state = $request->state;
        $purchase->save();

        foreach ($request->itemsPurchase as $details_purchase) {
            $new_detail = new DetailPurchaseController;
            $new_detail = $new_detail->store($details_purchase, $purchase->id);

            if (isset($details_purchase['food_id'])) {
                $itemController = new AlimentosController;
            } else {
                $itemController = new RecursosController;
            }

            $itemController->updatePriceById('add',(object) $details_purchase);
        }

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'purchase' => $purchase
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function show(Purchase $purchase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Purchase $purchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(Purchase $purchase)
    {
        //
    }
}
