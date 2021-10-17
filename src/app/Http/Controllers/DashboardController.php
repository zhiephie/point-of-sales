<?php

namespace App\Http\Controllers;

use App\Utils\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $bestSelling = collect(DB::table('transaction_details')
            ->join('items', 'items.id', '=', 'transaction_details.item_id')
            ->select('items.name', DB::raw('SUM(transaction_details.quantity) as qty'))
            ->groupBy('items.name')
            ->orderBy('qty', 'DESC')
            ->limit(5)
            ->get());

        $lowItems = collect(DB::table('items')
            ->select('name', DB::raw('SUM(quantity) as qty'))
            ->where('quantity', '<', 5)
            ->groupBy('name')
            ->orderBy('qty', 'ASC')
            ->limit(5)
            ->get());

        return response()->json(new JsonResponse(
            'Data Dashboard',
            [
                'best_selling' => $bestSelling,
                'low_quantity' => $lowItems
            ]
        ), Response::HTTP_OK);
    }
}
