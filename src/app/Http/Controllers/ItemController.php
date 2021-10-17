<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Http\Response;
use App\Utils\JsonResponse;

class ItemController extends Controller
{
    protected $repo;

    public function __construct(Item $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        $items = $this->repo
            ->with('image.imageable')
            ->latest();

        $keyword = request()->keyword;
        if ($keyword) {
            $items = $items->search($keyword);
        }
        $items = $items->paginate(10);

        return response()->json(new JsonResponse(
            'Data Semua Item',
            $items
        ), Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        //validate incoming request
        $data = $this->validate($request, [
            'category_id' => 'required|exists:categories,id',
            'barcode' => 'required|integer',
            'name' => 'required|max:255|unique:items',
            'description' => 'required',
            'price' => 'required|integer',
            'quantity' => 'required|integer',
            'image' => 'image|mimes:jpeg,png,jpg|max:10000',
        ]);

        try {
            $data = [
                'category_id' => $request->category_id,
                'barcode' => $request->barcode,
                'name' => $request->name,
                'slug' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'quantity' => $request->quantity,
            ];
            $store = $this->repo->create($data);

            if ($request->hasFile('image') && $request->image != '') {
                $image = $request->file('image');
                $imageName = $image->hashName();

                $store->image()->create([
                    'url' => $imageName
                ]);

                $image->move(storage_path('images'), $imageName);
            }

            //return successful response
            return response()->json(new JsonResponse(
                'Pembuatan Item Berhasil',
                $store
            ), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            //return error message
            return response()->json(new JsonResponse(
                'Pembuatan Item Gagal',
                ['data' => $e->getMessage()],
                'create_error'
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id)
    {
        $item = $this->repo->with('image.imageable')->find($id);
        if (!$item) {
            //return error message
            return response()->json(new JsonResponse(
                'Data Item tidak ditemukan',
                [],
                'show_error'
            ), Response::HTTP_NOT_FOUND);
        }
        //return successful response
        return response()->json(new JsonResponse(
            'Show Item Berhasil',
            $item
        ), Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        //validate incoming request
        $data = $this->validate($request, [
            'category_id' => 'required|exists:categories,id',
            'barcode' => 'required|integer',
            'name' => 'required|max:255',
            'description' => 'required',
            'price' => 'required|integer',
            'quantity' => 'required|integer',
        ]);

        try {
            $data = [
                'category_id' => $request->category_id,
                'barcode' => $request->barcode,
                'name' => $request->name,
                'slug' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'quantity' => $request->quantity,
            ];
            $update = $this->repo->findOrFail($id);
            $update->fill($data);
            $update->save();

            if ($request->hasFile('image') && $request->image != '') {
                $image = $request->file('image');
                $imageName = $image->hashName();

                $oldImage = $update->image->url;
                $filePath = storage_path('images') . '/' . $oldImage;
                unlink($filePath);

                $update->image()->update([
                    'url' => $imageName
                ]);

                $image->move(storage_path('images'), $imageName);
            }

            //return successful response
            return response()->json(new JsonResponse(
                'Perubahan Item Berhasil',
                $update
            ), Response::HTTP_OK);
        } catch (\Exception $e) {
            //return error message
            return response()->json(new JsonResponse(
                'Perubahan Item Gagal',
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
                    'Data Item tidak ditemukan',
                    [],
                    'delete_error'
                ), Response::HTTP_NOT_FOUND);
            }

            if ($delete->image) {
                $image = $delete->image->url;
                $filePath = storage_path('images') . '/' . $image;
                unlink($filePath);

                $delete->image()->delete();
            }
            $delete->delete();
            //return successful response
            return response()->json(new JsonResponse(
                'Hapus Item Berhasil',
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
