<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Alimento;
use App\HistorialAlimento;

class AlimentosController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {

    $alimentos = Alimento::all();
    return $alimentos;
  }

  /**
   * Display a listing of the resource for inventory.
   *
   * @return \Illuminate\Http\Response
   */
  public function list(Request $request)
  {
    $nro_results = $request->nro_results ?? 15;
    $category_id = $request->food_category_id;
    $brand_id = $request->brand_id;
    $warehouse_id = $request->warehouse_id;

    $foods = Alimento::orderBy('alimento', 'asc')
      ->where(function ($query) use ($category_id) {
        if (!is_null($category_id) && $category_id != '' && $category_id != 'all') {
          $query->where('category_id', 'LIKE',  "%$category_id%");
        }
      })->where(function ($query) use ($brand_id) {
        if (!is_null($brand_id) && $brand_id != '' && $brand_id != 'all') {
          $query->where('brand_id', 'LIKE',  "%$brand_id%");
        }
      })->where(function ($query) use ($warehouse_id) {
        if (!is_null($warehouse_id) && $warehouse_id != '' && $warehouse_id != 'all') {
          $query->where('warehouse_id', 'LIKE',  "%$warehouse_id%");
        }
      })
      ->paginate($nro_results);

    return response()->json([
      'status' => 'success',
      'code' => 200,
      'foods' => $foods
    ], 200);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $val = $request->validate([
      'alimento' => 'required',
      'costo_kg' => 'required',
      'warehouse_id' => 'nullable',
      'category_id' => 'nullable',
      'brand_id' => 'nullable',
      'provider_id' => 'nullable',
      'cantidad' => 'numeric',
      'cantidad_minima' => 'numeric',
      'cantidad_maxima' => 'numeric'
    ]);
    $alimento = Alimento::create([
      'alimento' => $request['alimento'],
      'costo_kg' => $request['costo_kg'],
      'warehouse_id' => $request['warehouse_id'],
      'category_id' => $request['category_id'],
      'brand_id' => $request['brand_id'],
      'provider_id' => $request['provider_id'],
      'cantidad' => $request['cantidad'],
      'cantidad_minima' => $request['cantidad_minima'],
      'cantidad_maxima' => $request['cantidad_maxima'],
    ]);

    HistorialAlimento::create([
      'id_alimento' => $alimento['id'],
      'costo' => $alimento['costo_kg'],
      'fecha_registro' => date('Y-m-d'),
    ]);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    abort(404);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $alimento = Alimento::findOrFail($id);
    $alimento->update($request->all());

    HistorialAlimento::create([
      'id_alimento' => $request['id'],
      'costo' => $request['costo_kg'],
      'fecha_registro' => date('Y-m-d')
    ]);

    return $alimento;
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    //
    Alimento::destroy($id);
    HistorialAlimento::where('id_alimento', $id)->delete();

    return 'eliminado';
  }
}
