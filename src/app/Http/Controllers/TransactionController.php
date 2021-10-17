<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Item;
use Illuminate\Http\Response;
use App\Utils\JsonResponse;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    protected $repo;

    public function __construct(Transaction $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        $transactions = $this->repo
            ->with('details.items')
            ->latest();
        $keyword = request()->keyword;
        if ($keyword) {
            $transactions = $transactions->search($keyword);
        }
        $transactions = $transactions->paginate(10);
        return response()->json(new JsonResponse(
            'Data Semua Transaksi',
            $transactions
        ), Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        //validate incoming request
        $data = $this->validate($request, [
            'table_id' => 'required|exists:tables,id',
            'total' => 'required|integer',
            'pay' => 'required|integer',
            'change' => 'required|integer',
            'status' => 'in:success,pending',
            'item_id' => 'required|array',
            'quantity' => 'required|array',
            'price' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $data = [
                'user_id' => auth()->user()->id,
                'table_id' => $request->table_id,
                'total' => $request->total,
                'pay' => $request->pay,
                'change' => $request->change,
                'status' => $request->status,
            ];
            $store = $this->repo->create($data);

            $items = $request->item_id;
            $quantities = $request->quantity;
            $prices = $request->price;
            $details = [];
            for ($item = 0; $item < count($items); $item++) {
                $subtotal = ($prices[$item] * $quantities[$item]);
                $details[] = [
                    'transaction_id' => $store->id,
                    'item_id' => $items[$item],
                    'quantity' => $quantities[$item],
                    'price' => $prices[$item],
                    'subtotal' => $subtotal,
                ];
            }
            $store->details()->createMany($details);
            if ($request->status === 'success') {
                foreach ($details as $item) {
                    $dataItem = Item::findOrFail($item['item_id']);
                    $dataItem->decrement('quantity', $item['quantity']);
                    $dataItem->save();
                }
            }
            // all good
            DB::commit();
            //return successful response
            return response()->json(new JsonResponse(
                'Pembuatan Item Berhasil',
                $store
            ), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // something went wrong
            DB::rollBack();
            //return error message
            return response()->json(new JsonResponse(
                'Pembuatan Item Gagal',
                ['data' => $e->getMessage()],
                'create_error'
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(string $invoice)
    {
        $transaction = $this->repo
            ->with('details.items')
            ->firstWhere('invoice', $invoice);
        if (!$transaction) {
            //return error message
            return response()->json(new JsonResponse(
                'Data Transaksi tidak ditemukan',
                [],
                'show_error'
            ), Response::HTTP_BAD_REQUEST);
        }
        //return successful response
        return response()->json(new JsonResponse(
            'Show Transaksi Berhasil',
            $transaction
        ), Response::HTTP_OK);
    }

    public function update(Request $request, string $invoice)
    {
        $data = $this->validate($request, [
            'table_id' => 'required|exists:tables,id',
            'total' => 'required|integer',
            'pay' => 'required|integer',
            'change' => 'required|integer',
            'status' => 'in:success,pending',
            'item_id' => 'required|array',
            'quantity' => 'required|array',
            'price' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $data = [
                'user_id' => auth()->user()->id,
                'table_id' => $request->table_id,
                'total' => $request->total,
                'pay' => $request->pay,
                'change' => $request->change,
                'status' => $request->status,
            ];
            $update = $this->repo->where('invoice', $invoice)->first();
            if ($update->status === 'success') {
                //return error message
                return response()->json(new JsonResponse(
                    'Data dengan status Transaksi success tidak bisa dirubah',
                    [],
                    'delete_error'
                ), Response::HTTP_BAD_REQUEST);
            }
            $update->fill($data);
            $update->save();

            // Remove all items and add new
            $update->details()->delete();

            $items = $request->item_id;
            $quantities = $request->quantity;
            $prices = $request->price;
            $details = [];
            for ($item = 0; $item < count($items); $item++) {
                $subtotal = ($prices[$item] * $quantities[$item]);
                $details[] = [
                    'transaction_id' => $update->id,
                    'item_id' => $items[$item],
                    'quantity' => $quantities[$item],
                    'price' => $prices[$item],
                    'subtotal' => $subtotal,
                ];
            }
            $update->details()->createMany($details);
            if ($request->status === 'success') {
                foreach ($details as $item) {
                    $dataItem = Item::findOrFail($item['item_id']);
                    $dataItem->decrement('quantity', $item['quantity']);
                    $dataItem->save();
                }
            }
            // all good
            DB::commit();
            //return successful response
            return response()->json(new JsonResponse(
                'Perubahan Transaksi Berhasil',
                $update
            ), Response::HTTP_OK);
        } catch (\Exception $e) {
            // something went wrong
            DB::rollBack();
            //return error message
            return response()->json(new JsonResponse(
                'Perubahan Transaksi Gagal',
                ['data' => $e->getMessage()],
                'update_error'
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $this->validate($request, [
            'status' => 'required|in:success'
        ]);

        DB::beginTransaction();

        try {
            $status = $request->status;
            $data = [
                'status' => $status
            ];
            $update = $this->repo->findOrFail($id);
            $update->fill($data);
            $update->save();

            foreach ($update->details()->get() as $item) {
                $dataItem = Item::findOrFail($item->item_id);
                $dataItem->decrement('quantity', $item->quantity);
                $dataItem->save();
            }
            // all good
            DB::commit();
            //return successful response
            return response()->json(new JsonResponse(
                'Perubahan Status Transaksi Berhasil',
                $update
            ), Response::HTTP_OK);
        } catch (\Exception $e) {
            // something went wrong
            DB::rollBack();
            //return error message
            return response()->json(new JsonResponse(
                'Perubahan Status Transaksi Gagal',
                ['data' => $e->getMessage()],
                'update_error'
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id)
    {
        DB::beginTransaction();

        try {
            $delete = $this->repo->find($id);
            if (!$delete) {
                //return error message
                return response()->json(new JsonResponse(
                    'Data Transaksi tidak ditemukan',
                    [],
                    'delete_error'
                ), Response::HTTP_BAD_REQUEST);
            }

            if ($delete->status === 'success') {
                //return error message
                return response()->json(new JsonResponse(
                    'Data dengan status Transaksi success tidak bisa dihapus',
                    [],
                    'delete_error'
                ), Response::HTTP_BAD_REQUEST);
            }

            foreach ($delete->details()->get() as $item) {
                $dataItem = Item::findOrFail($item->item_id);
                $dataItem->increment('quantity', $item->quantity);
                $dataItem->save();
            }

            $delete->details()->delete();
            $delete->delete();
            // all good
            DB::commit();
            //return successful response
            return response()->json(new JsonResponse(
                'Hapus Transaksi Berhasil',
                $delete
            ), Response::HTTP_OK);
        } catch (\Exception $e) {
            // something went wrong
            DB::rollBack();
            //return error message
            return response()->json(new JsonResponse(
                'Terjadi masalah',
                ['data' => $e->getMessage()],
                'delete_error'
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
