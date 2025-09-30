<?php

namespace App\Http\Controllers\DataCar;

use App\Http\Controllers\Controller;
use App\Models\DataCar\Brand;
use App\Models\DataCar\CarDetail;
use App\Models\DataCar\CarModel;
use App\Models\DataCar\CarType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $CarDetail = CarDetail::with(['brand', 'model', 'type'])
        ->get();

        return Inertia::render('FrontEnd/Menu/Home/Car/Detail', [
            'CarDetail' => $CarDetail,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         $CarDetail = CarDetail::with(['brand', 'model', 'type'])
        ->get();

        return Inertia::render('FrontEnd/Menu/Home/Car/Create', [
            'CarDetail' => $CarDetail,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

      // Ambil hanya gambar berdasarkan car_id
    public function images($id)
    {
        $car = CarDetail::findOrFail($id);
        return response()->json($car->images);
    }
    /**
     * Check if brand name already exists
     */

        // GET Methods
    public function getBrands()
    {
        $brands = Brand::where('is_active', true)->get();
        return response()->json($brands);
    }

    public function getModels(Request $request)
    {
        $brandId = $request->query('brand_id');
        $models = CarModel::where('is_active', true);
        
        if ($brandId) {
            $models->where('brand_id', $brandId);
        }
        
        return response()->json($models->get());
    }

    public function getTypes(Request $request)
    {
        $modelId = $request->query('car_model_id');
        $types = CarType::where('is_active', true);
        
        if ($modelId) {
            $types->where('car_model_id', $modelId);
        }
        
        return response()->json($types->get());
    }

    // Duplicate Check Methods
    public function checkBrandDuplicate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Tidak Valid'], 422);
        }

        $exists = Brand::where('name', $request->name)
            ->where('is_active', true)
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    public function checkModelDuplicate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'brand_id' => 'required|exists:brands,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Tidak Valid'], 422);
        }

        $exists = CarModel::where('name', $request->name)
            ->where('brand_id', $request->brand_id)
            ->where('is_active', true)
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    public function checkTypeDuplicate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'car_model_id' => 'required|exists:car_models,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Tidak Valid'], 422);
        }

        $exists = CarType::where('name', $request->name)
            ->where('car_model_id', $request->car_model_id)
            ->where('is_active', true)
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    // Store Methods
    public function storeBrand(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:brands,name,NULL,id,is_active,true'
        ]);

         // Custom unique validation
        $validator->after(function ($validator) use ($request) {
            $exists = Brand::where('name', $request->name)
                ->where('is_active', true)
                ->exists();
                
            if ($exists) {
                $validator->errors()->add('name', 'Merk sudah ada');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Tidak Valid',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            $brand = Brand::create([
                'name' => $request->name,
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'data' => $brand
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menyimpan Merk'
            ], 500);
        }
    }

    public function storeModel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'brand_id' => 'required|exists:brands,id'
        ]);

        // Custom unique validation
        $validator->after(function ($validator) use ($request) {
            $exists = CarModel::where('name', $request->name)
                ->where('brand_id', $request->brand_id)
                ->where('is_active', true)
                ->exists();
                
            if ($exists) {
                $validator->errors()->add('name', 'Model sudah ada berdasarkan Merk ini');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validasi Gagal',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            $model = CarModel::create([
                'name' => $request->name,
                'brand_id' => $request->brand_id,
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'data' => $model
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menyimpan Model'
            ], 500);
        }
    }

    public function storeType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'car_model_id' => 'required|exists:car_models,id'
        ]);

        // Custom unique validation
        $validator->after(function ($validator) use ($request) {
            $exists = CarType::where('name', $request->name)
                ->where('car_model_id', $request->car_model_id)
                ->where('is_active', true)
                ->exists();
                
            if ($exists) {
                $validator->errors()->add('name', 'Type sudah ada untuk Model ini');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validasi Gagal',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            $type = CarType::create([
                'name' => $request->name,
                'car_model_id' => $request->car_model_id,
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'data' => $type
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal Menyimpan Type'
            ], 500);
        }
    }

    public function checkDuplicateCarDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_id' => 'required|exists:brands,id',
            'car_model_id' => 'required|exists:car_models,id',
            'car_type_id' => 'required|exists:car_types,id',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'transmission' => 'required|in:AT,MT',
            'fuel_type' => 'required|string|max:50',
            'production_period' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Tidak Valid'], 422);
        }

        $exists = CarDetail::where('brand_id', $request->brand_id)
            ->where('car_model_id', $request->car_model_id)
            ->where('car_type_id', $request->car_type_id)
            ->where('year', $request->year)
            ->where('transmission', $request->transmission)
            ->where('fuel_type', $request->fuel_type)
            ->where('production_period', $request->production_period)
            ->exists();

        return response()->json(['exists' => $exists]);
    }

public function storeCarDetail(Request $request)
{
    $validator = Validator::make($request->all(), [
        'brand_id' => 'required|exists:brands,id',
        'car_model_id' => 'required|exists:car_models,id',
        'car_type_id' => 'required|exists:car_types,id',
        'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
        'cc' => 'nullable|integer|min:0',
        'transmission' => 'required|in:AT,MT',
        'fuel_type' => 'required|string|max:50',
        'production_period' => 'required|string|max:50',
        'description' => 'nullable|string',
    ]);

    // Cek duplikat
    $validator->after(function ($validator) use ($request) {
        $exists = CarDetail::where('brand_id', $request->brand_id)
            ->where('car_model_id', $request->car_model_id)
            ->where('car_type_id', $request->car_type_id)
            ->where('year', $request->year)
            ->where('transmission', $request->transmission)
            ->where('fuel_type', $request->fuel_type)
            ->where('production_period', $request->production_period)
            ->exists();

        if ($exists) {
            $validator->errors()->add('duplicate', 'Mobil sudah ada untuk spesifikasi ini.');
        }
    });

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput(); // biar input sebelumnya tetap ada
    }

    try {
        CarDetail::create($request->all());

        return redirect()->back()->with('success', 'Data mobil berhasil disimpan.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Gagal menyimpan data mobil.');
    }
}



}
