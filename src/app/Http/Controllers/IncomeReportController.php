<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Utils\JsonResponse;

class IncomeReportController extends Controller
{

    protected $repo;

    public function __construct(Transaction $repo)
    {
        $this->repo = $repo;
    }

    public function __invoke(Request $request)
    {
        //validate incoming request
        $this->validate($request, [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $query = $this->repo
            ->with('details.items')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest()
            ->get();

        return response()->json(new JsonResponse(
            'Hasil Query',
            $query
        ), Response::HTTP_OK);
    }
}
