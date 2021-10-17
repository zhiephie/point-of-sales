<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Http\Response;
use App\Utils\JsonResponse;

class CategoryController extends Controller
{
    protected $repo;

    public function __construct(Category $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        $categories = $this->repo
            ->with('image.imageable')
            ->latest();
        $keyword = request()->keyword;
        if ($keyword) {
            $categories = $categories->search($keyword);
        }
        $categories = $categories->paginate(10);

        return response()->json(new JsonResponse(
            'Data Semua Kategori',
            $categories
        ), Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        //validate incoming request
        $data = $this->validate($request, [
            'name' => 'required|max:255|unique:categories',
            'image' => 'image|mimes:jpeg,png,jpg|max:10000',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'slug' => $request->name
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
                'Pembuatan Kategori Berhasil',
                $store
            ), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            //return error message
            return response()->json(new JsonResponse(
                'Pembuatan Kategori Gagal',
                ['data' => $e->getMessage()],
                'create_error'
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id)
    {
        $category = $this->repo->with('image.imageable')->find($id);
        if (!$category) {
            //return error message
            return response()->json(new JsonResponse(
                'Data Kategori tidak ditemukan',
                [],
                'show_error'
            ), Response::HTTP_NOT_FOUND);
        }
        //return successful response
        return response()->json(new JsonResponse(
            'Show Kategori Berhasil',
            $category
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
                'Perubahan Kategori Berhasil',
                $update
            ), Response::HTTP_OK);
        } catch (\Exception $e) {
            //return error message
            return response()->json(new JsonResponse(
                'Perubahan Kategori Gagal',
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
                    'Data kategori tidak ditemukan',
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
                'Hapus Kategori Berhasil',
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
