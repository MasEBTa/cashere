<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\PurchasingDetail;
use App\Models\Product;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    public function dataSupplier()
    {
        $supplier = Supplier::orderBy("name")->get();
        
        return datatables()
                ->of($supplier)
                ->addIndexColumn()
                ->addColumn('action', function ($supplier)
                {
                    return '
                    <button data-id="'.$supplier->id.'" class="addSupplier btn btn-primary btn-xs btn-flat">
                        Pilih
                    </button>
                    ';
                })->rawColumns(['action'])
                ->make(true);
    }

    public function data()
    {
        $purchasing = Purchase::orderBy('id', 'desc')->with('supplier')->get();
        $purchasing->sesi = true;

        // total item
        foreach ($purchasing as $key => $value) {
            $total_item = 0;
            $purchasingDetail = PurchasingDetail::where('purchasing_id', $value->id)->get();
            foreach ($purchasingDetail as $index => $item) {
                $total_item += $item->item_qty;
            }
            $value->total_item = $total_item;
        }

        foreach ($purchasing as $key => $value) {
            if ((Session::has('id'))) {
                $value['sesi'] = 1;
            } else {
                $value['sesi'] = 0;
            }
        }
        // return $purchasing;
        return datatables()
                ->of($purchasing)
                ->addIndexColumn()
                ->addColumn('date', function ($purchasing)
                {
                    return tanggal_indonesia($purchasing->created_at, false);
                })
                ->editColumn('supplier', function ($purchasing)
                {
                    return $purchasing->supplier->name;
                })
                ->editColumn('total_price', function ($purchasing)
                {
                    return money_format($purchasing->total_price, '.', 'Rp ', ',-');  
                })
                ->editColumn('total_payment', function ($purchasing)
                {
                    return money_format($purchasing->total_payment, '.', 'Rp ', ',-');  
                })
                ->editColumn('discount', function ($purchasing)
                {
                    return $purchasing->discount."%";
                })
                ->addColumn('action', function ($purchasing)
                {
                    if ($purchasing->active == 1) {
                        return 
                        '<div class="btn-group d-flex justify-content-center">
                            <a href="#" data-total="'.$purchasing->total_item.'" data-iddelete="'.$purchasing->id.'" class="deleteData btn btn-xs btn-danger btn-flate p-1"> Delete </a>
                            <a href="#" data-idfinish="'.$purchasing->id.'" class="finishData btn btn-xs btn-warning btn-flate p-1"> Finish </a>
                        </div>';
                    } else {
                        return '<div class="btn-group d-flex justify-content-center">
                                    <a href="#" data-idsee="'.$purchasing->id.'" class="see btn btn-xs btn-info btn-flate p-1"><i class="bi bi-eye"></i> item </a>
                                </div>';
                    }
                })->rawColumns(['action'])
                ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        // return $id;
        $purchasing = new Purchase();
        $purchasing->supplier_id   = $id;
        $purchasing->total_items   = 0;
        $purchasing->total_price   = 0;
        $purchasing->discount      = 0;
        $purchasing->active        = true;
        $purchasing->total_payment = 0;
        $purchasing->save();

        // Session::put('id', 'id_supplier');

        $purchaseNews = Purchase::latest()->get();;
        $purchaseNews_id = $purchaseNews[0]->id;

        // session(['id' => $purchaseNews_id]);
        // session(['id_supplier' => $purchasing->supplier_id]);
        // return session('id');
        // return session('id');
        return response()->json([
            'success' => true,
            'message' => 'Purchasing Updated',
            'data'    => $purchasing  
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $purchase = Purchase::findOrFail($request->id);
        $purchase->total_items = $request->total_item;
        $purchase->total_price = $request->total_payment;
        $purchase->total_payment = $request->bayar;
        $purchase->discount = $request->discount;
        $purchase->update();
        // return $purchase;
        // return $request;

        $detail = PurchasingDetail::where('purchasing_id', $purchase->id)->get();
        foreach ($detail as $key => $value) {
            $product = Product::find($value->product_id);
            $product->stock += $value->item_qty;
            $product->update();
        }

        Session::forget(['id', 'id_supplier']);
        // return $product;
        return redirect()->route('purchase.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        // $detail = PurchasingDetail::where('purchasing_id', $purchase->id)->get();
        $detail = PurchasingDetail::with('product')->where('purchasing_id', $purchase->id)
                                    ->join('purchases', 'purchasing_details.purchasing_id', '=', 'purchases.id')
                                    ->get();
        // return $purchase;
        return datatables()
        ->of($detail)
        ->addIndexColumn()
        ->addColumn('code', function ($purchasing)
        {
            return '<span class="badge badge-success">'.$purchasing->product['code'].'</span>';
        })
        ->editColumn('pricing_label', function ($purchasing)
        {
            return money_format($purchasing->pricing_label, '.', 'Rp ', ',-');
        })
        ->editColumn('subtotal', function ($purchasing)
        {
            return money_format($purchasing->subtotal, '.', 'Rp ', ',-');
        })
        ->editColumn('name', function ($purchasing)
        {
            return $purchasing->product['name'];
        })
        ->rawColumns(['code'])
        ->make(true);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function save(string $id, Request $request)
    {
        $purchased = Purchase::find($id);
        $purchased->update($request->all());

        $detail = PurchasingDetail::where('purchasing_id', $id)->get();
        foreach ($detail as $key => $value) {
            $product = Product::find($value->product_id);
            $product->stock += $value->item_qty;
            $product->update();
        }
    }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, string $purchase)
    // {
    //     $purchased = Purchase::find($purchase);
    //     // return 'hai';
    //     $purchased->update($request->all());

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Data updated',
    //         'data'    => $purchased  
    //     ], 200);
    // }

    /**
     * Update the specified resource in storage.
     */
    public function diskonUpdate(Request $request, string $id)
    {
        // return $request;
        $purchased = Purchase::find($id);
        // return 'hai';
        $purchased->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data updated',
            'data'    => $purchased  
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        $purchase->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data deleted',
            'data'    => $purchase  
        ], 200);
    }
}
