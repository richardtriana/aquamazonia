<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Recursos;
use App\HistorialRecurso;
use App\Http\Controllers\GetReportTotalController;

class RecursosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $recursos = Recursos::orderBy('recurso', 'asc')->get();
        return $recursos;
    }

    
  /**
   * Display a listing of the resource for inventory.
   *
   * @return \Illuminate\Http\Response
   */
  public function list(Request $request)
  {
    $nro_results = $request->nro_results ?? 15;
    $category_id = $request->resource_category_id;
    $brand_id = $request->brand_id;
    $warehouse_id = $request->warehouse_id;

    $resources = Recursos::orderBy('recurso', 'asc')
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

      $total_products = new GetReportTotalController();
      $total_products = $total_products->reportTotalRecursos($request);

    return response()->json([
      'status' => 'success',
      'code' => 200,
      'resources' => $resources,
      'totals' => $total_products
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
        $request->validate([
            'recurso' => 'required',
            'unidad' => 'required',
            'costo' => 'required',
            'warehouse_id' => 'nullable',
            'expiration_date' => 'nullable',
            'cantidad' => 'numeric',
            'cantidad_minima' => 'numeric',
            'cantidad_maxima' => 'numeric'
        ]);

        $recursos = Recursos::create([
            'recurso' => $request['recurso'],
            'unidad' => $request['unidad'],
            'costo' => $request['costo'],
            'warehouse_id' => $request['warehouse_id'],
            'category_id' => $request['category_id'],
            'brand_id' => $request['brand_id'],
            'provider_id' => $request['provider_id'],
            'expiration_date' => $request['expiration_date'],
            'cantidad' => $request['cantidad'],
            'cantidad_minima' => $request['cantidad_minima'],
            'cantidad_maxima' => $request['cantidad_maxima'],

        ]);

        HistorialRecurso::create([
            'id_recurso' => $recursos['id'],
            'costo' => $recursos['costo'],
            'fecha_registro' => date('Y-m-d')
        ]);
    }

    /**
     * Display the specified unidad.
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
        $recurso = Recursos::findOrFail($id);
        $recurso->update($request->all());

        HistorialRecurso::create([
            'id_recurso' => $request['id'],
            'costo' => $request['costo'],
            'fecha_registro' => date('Y-m-d')
        ]);

        return $recurso;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Recursos::destroy($id);
        HistorialRecurso::where('id_recurso', $id)->delete();

        return 'eliminado';
    }
}
