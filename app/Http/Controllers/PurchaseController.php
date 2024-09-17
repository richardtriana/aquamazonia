<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\AlimentosController;
use App\Http\Controllers\API\RecursosController;
use App\Purchase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $user_id =  Auth::user()->id;

        $type = $request->type ?? 'alimentos';

        $purchases = Purchase::whereHas('provider', function (Builder $query) use ($request) {
            if ($request->provider != '') {
                $query->where('name', 'like', "%$request->provider%");
            }
        }) ->whereHas('detailPurchase', function (Builder $query) use ($type) {
                if ($type == 'alimentos') {
                    $query->whereNotNull('alimento_id');
                } else {
                    $query->whereNotNull('recurso_id');
                }
            });

        $today = date('Y-m-d');
        $from = $request->from;
        $to = $request->to;

        if ($from != '' && $from != 'undefined') {
            $purchases = $purchases
                ->whereDate('created_at', '>=', $from);
        }

        if ($to != '' && $to != 'undefined') {
            $purchases = $purchases
                ->whereDate('created_at', '<=', $to);
        }

        // if ($from == '' && $to == '') {
        //     $purchases = $purchases
        //         ->where('created_at', '>=', $today);
        // }
        if ($request->id != '') {
			$purchases = $purchases->where('id', $request->id);
		}

        $purchases = $purchases
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'purchases' => $purchases,
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

            $itemController->updatePriceById('add', (object) $details_purchase);
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
        $details  = $purchase;
		return ['purchase_information' => $details, 'purchase_details' => $details->detailPurchase()->get()];
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
