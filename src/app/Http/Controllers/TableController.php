<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Table;
use Illuminate\Http\Response;
use App\Utils\JsonResponse;

class TableController extends Controller
{
    protected $repo;

    public function __construct(Table $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        $tables = $this->repo
            ->latest();
        $keyword = request()->keyword;
        if ($keyword) {
            $tables = $tables->search($keyword);
        }
        $tables = $tables->paginate(10);

        return response()->json(new JsonResponse(
            'Data Semua Meja',
            $tables
        ), Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        //validate incoming request
        $data = $this->validate($request, [
            'name' => 'required|max:255|unique:tables'
        ]);

        try {
            $data = [
                'name' => $request->name,
                'slug' => $request->name
            ];
            $store = $this->repo->create($data);

            //return successful response
            return response()->json(new JsonResponse(
                'Pembuatan Meja Berhasil',
                $store
            ), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            //return error message
            return response()->json(new JsonResponse(
                'Pembuatan Meja Gagal',
                ['data' => $e->getMessage()],
                'create_error'
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id)
    {
        $table = $this->repo->find($id);
        if (!$table) {
            //return error message
            return response()->json(new JsonResponse(
                'Data Meja tidak ditemukan',
                [],
                'show_error'
            ), Response::HTTP_NOT_FOUND);
        }
        //return successful response
        return response()->json(new JsonResponse(
            'Show Meja Berhasil',
            $table
        ), Response::HTTP_OK);
    }

    public function update(Request $request, int $id)
    {
        //validate incoming request
        $data = $this->validate($request, [
            'name' => 'required|max:255'
        ]);

        try {
            $data = [
                'name' => $request->name,
                'slug' => $request->name
            ];
            $update = $this->repo->findOrFail($id);
            $update->fill($data);
            $update->save();

            //return successful response
            return response()->json(new JsonResponse(
                'Perubahan Meja Berhasil',
                $update
            ), Response::HTTP_OK);
        } catch (\Exception $e) {
            //return error message
            return response()->json(new JsonResponse(
                'Perubahan Meja Gagal',
                ['data' => $e->getMessage()],
                'update_error'
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id)
    {
        try {
            $delete = $this->repo->find($id);
            if (!$delete) {
                //return error message
                return response()->json(new JsonResponse(
                    'Data Meja tidak ditemukan',
                    [],
                    'delete_error'
                ), Response::HTTP_NOT_FOUND);
            }
            $delete->delete();

            //return successful response
            return response()->json(new JsonResponse(
                'Hapus Meja Berhasil',
                $delete
            ), Response::HTTP_OK);
        } catch (\Exception $e) {
            //return error message
            return response()->json(new JsonResponse(
                'Terjadi masalah',
                ['data' => $e->getMessage()],
                'delete_error'
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
