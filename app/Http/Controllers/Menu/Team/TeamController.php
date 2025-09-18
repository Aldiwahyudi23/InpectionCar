<?php

namespace App\Http\Controllers\Menu\Team;

use App\Http\Controllers\Controller;
use App\Models\Team\Region;
use App\Models\Team\RegionTeam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
     * Remove the specified resource from storage.
     */

     /**
     * Mengupdate foto profil
     */
    /**
     * Menampilkan halaman team
     */
    public function index()
    {
        $user = Auth::user();
        
        // Ambil semua region
        $regions = Region::all();
        $allUser = User::all();
        
        // Ambil semua data team dengan relasi user
        $teamMembers = RegionTeam::with('user')
            ->get()
            ->map(function ($team) {
                return [
                    'id' => $team->id,
                    'user' => [
                        'id' => $team->user->id,
                        'name' => $team->user->name,
                        'email' => $team->user->email,
                        'phone' => $team->user->numberPhone,
                        'profile_photo_url' => $team->user->profile_photo_url,
                        'roles' => $team->user->getRoleNames(), // 🔥 pastikan dikirim
                    ],
                    'region_id' => $team->region_id,
                    'role' => $team->role,
                    'status' => $team->status,
                    'created_at' => $team->created_at,
                ];
            });

        return inertia('FrontEnd/Menu/Team/Index', [
            'regions' => $regions,
            'allUser' => $allUser,
            'roleName' => $user->getRoleNames(),
            'teamMembers' => $teamMembers
        ]);
    }

    /**
     * Menambahkan user baru ke team
     */
public function addUser(Request $request)
{
    // Cek apakah user memiliki role Admin atau Coordinator
    $user = Auth::user();
    if (!$user->hasRole('Admin') && !$user->hasRole('coordinator')) {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki izin untuk menambahkan user'
        ], 403);
    }

    // Validasi input dasar
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'required|string|max:20',
        'role' => 'required|in:admin_region,inspector',
        'region_id' => 'required|exists:regions,id'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $validator->errors()
        ], 422);
    }

    // 🔎 Filter email & phone sudah ada atau belum
    $exists = User::where('email', $request->email)
        ->orWhere('numberPhone', $request->phone)
        ->exists();

    if ($exists) {
        return response()->json([
            'success' => false,
            'message' => 'Email atau nomor telepon sudah terdaftar pada pengguna lain'
        ], 409); // 409 = Conflict
    }

    try {
        // Buat user baru dengan status tidak aktif
        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'numberPhone' => $request->phone,
            'password' => Hash::make('cekMobil123'), // Password default
            'is_active' => false // User tidak aktif sampai disetujui admin
        ]);

        // Tambahkan user ke team dengan status pending
        RegionTeam::create([
            'user_id' => $newUser->id,
            'region_id' => $request->region_id,
            'status' => 'paused' // Status pending menunggu persetujuan
        ]);

        // Berikan role kepada user
        $newUser->assignRole($request->role);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan dan menunggu persetujuan admin'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}


    /**
     * Mengambil anggota team berdasarkan region
     */
    public function getByRegion($regionId)
    {
        $teamMembers = RegionTeam::with('user')
            ->where('region_id', $regionId)
            ->get()
            ->map(function ($team) {
                return [
                    'id' => $team->id,
                    'user' => [
                        'id' => $team->user->id,
                        'name' => $team->user->name,
                        'email' => $team->user->email,
                        'phone' => $team->user->phone,
                        'profile_photo_url' => $team->user->profile_photo_url,
                    ],
                    'region_id' => $team->region_id,
                    'role' => $team->role,
                    'status' => $team->status,
                    'created_at' => $team->created_at,
                ];
            });

        return response()->json([
            'members' => $teamMembers,
            'count' => $teamMembers->count()
        ]);
    }

    public function setting_team($id)
    {
        $regionTeam = RegionTeam::with('user')
        ->where('id',$id)
        ->first();

         return inertia('FrontEnd/Menu/Team/TeamSettings', [
            'regionTeam' => $regionTeam
        ]);
    }

    /**
     * Mengupdate pengaturan team
     */
    public function updateettingTeam(Request $request, $id)
    {
        $user = Auth::user();
        
        // Hanya admin dan coordinator yang bisa mengakses
        if (!$user->hasRole('Admin') && !$user->hasRole('coordinator')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'status' => 'required|in:active,inactive,paused',
            'settings.inspection_price_self' => 'required|numeric|min:0',
            'settings.inspection_price_external' => 'required|numeric|min:0',
        ]);

        try {
            $team = RegionTeam::findOrFail($id);
            $team->update([
                'status' => $request->status,
                'settings' => $request->settings
            ]);

            return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

}
