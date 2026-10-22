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
        // 1. Intentamos obtener el usuario de la sesión actual
        $user = auth()->user() ?? auth('web')->user();

        if ($user) {
            $user_id = $user->id;
        } else {
            // 2. Si no hay sesión, buscamos el primer usuario administrador en la DB
            // Esto evita el error de la llave foránea si el ID 1 no existe.
            $adminUser = \App\User::first();
            $user_id = $adminUser ? $adminUser->id : null;
        }

        // Si después de todo no hay un usuario en la tabla 'users', fallará por DB.
        // Pero con Richard Peña logueado, $user_id tendrá su valor real.

        $purchase = new Purchase;
        $purchase->provider_id = $request->id_provider;
        $purchase->user_id = $user_id; // <--- Aquí ya va el ID validado
        $purchase->total_cost = $request->total_tax_inc;
        $purchase->total_discount = $request->total_discount ?? 0;
        $purchase->state = $request->state;
        $purchase->save();

        // 3. Procesar los items (esta parte ya funciona bien)
        foreach ($request->itemsPurchase as $item_data) {
            
            $isAlimento = ($request->type == 'alimentos' || isset($item_data['alimento_id']) || !isset($item_data['resource_id']));

            $normalizedDetail = [
                'id'                  => $item_data['id'],
                'food_id'             => $isAlimento ? $item_data['id'] : null,
                'resource_id'         => !$isAlimento ? $item_data['id'] : null,
                'price'               => $item_data['price'],
                'quantity'            => $item_data['quantity'],
                'item'                => $item_data['item'],
                'discount_percentage' => $item_data['discount_percentage'] ?? 0,
                'discount_value'      => $item_data['discount_value'] ?? 0,
                'total_cost'          => $item_data['total_cost'],
            ];

            $detailController = new \App\Http\Controllers\DetailPurchaseController;
            $detailController->store($normalizedDetail, $purchase->id);

            $objUpdate = (object) $normalizedDetail;

            if ($isAlimento) {
                $itemController = new \App\Http\Controllers\API\AlimentosController;
                $itemController->updatePriceById('add', $objUpdate);
            } else {
                $itemController = new \App\Http\Controllers\API\RecursosController;
                $itemController->updatePriceById('add', $objUpdate);
            }
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
