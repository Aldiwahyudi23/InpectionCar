<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DataInspection\Inspection;
use App\Models\Finance\Transaction;
use App\Models\Finance\TransactionDistribution;
use App\Models\Team\Region;
use App\Models\Team\RegionTeam;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TransactionController extends Controller
{

    /**
     * Menampilkan halaman report transaction distribution
     */

    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();

        // Inisialisasi query utama
        $query = TransactionDistribution::with(['transaction', 'user', 'region', 'released','transaction.inspection'])
            ->select(
                'transaction_distributions.*',
                DB::raw('DATE(transaction_distributions.created_at) as distribution_date')
            );

        // Filter berdasarkan role
        if ($role === 'inspector') {
            // Inspector hanya melihat data sendiri
            $query->where('transaction_distributions.user_id', $user->id);
        } elseif ($role === 'coordinator') {
            // Coordinator hanya melihat data di regionnya
            $userRegionId = $user->regionTeams()->first()->region_id ?? null;
            if ($userRegionId) {
                $query->where('transaction_distributions.region_id', $userRegionId);
            } else {
                // Jika coordinator tidak punya region, kasih kosong
                $query->where('transaction_distributions.region_id', -1);
            }
        }
        
        // Admin melihat semua data

        // Filter tambahan berdasarkan request

        if ($request->filled('region_id')) {
            $query->where('transaction_distributions.region_id', $request->region_id);
        }

        if ($request->filled('user_id')) {
            $query->where('transaction_distributions.user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_distributions.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_distributions.created_at', '<=', $request->date_to);
        }

        // Default: bulan berjalan (jika tidak ada filter date)
        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            $query->whereBetween('transaction_distributions.created_at', [
                now()->startOfMonth(),
                now()->endOfDay(),
            ]);
        }

        if ($request->has('is_released') && $request->is_released !== null) {
            $query->where('transaction_distributions.is_released', $request->is_released);
        }

        // Filter pencarian global
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('role_type', 'like', "%{$search}%")
                ->orWhere('amount', 'like', "%{$search}%")
                ->orWhereHas('transaction', function ($q2) use ($search) {
                    $q2->where('invoice_number', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('region', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('transaction.inspection', function ($q2) use ($search) {
                    $q2->where('plate_number', 'like', "%{$search}%")
                    ->orWhere('car_name', 'like', "%{$search}%");
                });
            });
        }


        // Sorting
        $query->orderBy('transaction_distributions.created_at', 'desc');

        $distributions = $query->paginate(10)->through(function ($item) {
            $item->encrypted_id = Crypt::encrypt($item->id); // Tambahin field baru
            return $item;
        })->appends($request->all());


        // Data untuk filter
        $regions = Region::all();

        $usersQuery = User::role(['inspector', 'coordinator']);
        if ($role === 'coordinator') {
            $userRegionId = $user->regionTeams()->first()->region_id ?? null;
            if ($userRegionId) {
                $usersQuery->whereHas('regionTeams', function ($q) use ($userRegionId) {
                    $q->where('region_id', $userRegionId);
                });
            }
        }
        $users = $usersQuery->get();

        // Hitung total amounts (rekapan semua transaksi)
        $totalQuery = clone $query;
        $totals = DB::table(DB::raw("({$totalQuery->toSql()}) as sub"))
            ->mergeBindings($totalQuery->getQuery()) // penting biar binding ikut masuk
            ->selectRaw('
                SUM(amount) as total_amount,
                COUNT(*) as total_records,
                SUM(CASE WHEN is_released = 1 THEN amount ELSE 0 END) as total_released,
                SUM(CASE WHEN is_released = 0 THEN amount ELSE 0 END) as total_pending
            ')
            ->first();


            $paidBy = $request->user_id ?? Auth::id();
         $transactions = Transaction::where('paid_by', $paidBy)
            ->whereHas('distributions', function ($q) {
                $q->where('is_released', 'pending');
            })
            ->with(['distributions' => function ($q) {
                $q->where('is_released', 'pending')->with('user');
            }])
            ->get();

    // Hitung total amount dari semua distribusi pending
    $totalPendingAmount = $transactions->flatMap->distributions->sum('amount');

    // dd($totalPendingAmount);
        return Inertia::render('FrontEnd/Menu/Home/Finance/Index', [
            'distributions' => $distributions,
            'regions' => $regions,
            'users' => $users,
            'totals' => $totals,
            'role' => $role,
            'tagihan' => $totalPendingAmount,
            'filters' => $request->all(),
        ]);
    }

     public function updateRelease(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $distribution = TransactionDistribution::findOrFail($id);
            $distribution->is_released = true; // set true
            $distribution->released_at = Carbon::now(); // set true
            $distribution->released_by = Auth::user()->id; // set true
        $distribution->save();

        return redirect()->back()->with('success', 'Distribusi berhasil di-serahkan.');
    }
    /**
     * Export data ke CSV
     */
public function export(Request $request)
{
    $user = Auth::user();
    $role = $user->getRoleNames()->first();
    
    $query = TransactionDistribution::with(['transaction', 'user', 'region']);

    // Filter berdasarkan role
    if ($role === 'inspector') {
        $query->where('user_id', $user->id);
    } elseif ($role === 'coordinator') {
        $userRegionId = $user->regionTeams()->first()->region_id ?? null;
        if ($userRegionId) {
            $query->where('region_id', $userRegionId);
        }
    }

    // Filter request
    if ($request->filled('region_id')) {
        $query->where('region_id', $request->region_id);
    }
    if ($request->filled('user_id')) {
        $query->where('user_id', $request->user_id);
    }
    if ($request->filled('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }

    $distributions = $query->orderBy('created_at', 'desc')->get();

    $filename = "transaction_distributions_" . date('Y-m-d') . ".csv";
    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];

    $callback = function() use ($distributions) {
        $file = fopen('php://output', 'w');

        // Tambah BOM biar Excel Windows baca UTF-8
        fputs($file, "\xEF\xBB\xBF");

        // Header CSV
        fputcsv($file, [
            'Tanggal',
            'Transaction ID',
            'User',
            'Region',
            'Role Type',
            'Amount',
            'Percentage',
            'Status',
            'Released At'
        ]);

        foreach ($distributions as $distribution) {
            fputcsv($file, [
                $distribution->created_at->format('Y-m-d H:i'),
                $distribution->transaction ? $distribution->transaction->id : 'N/A',
                $distribution->user ? $distribution->user->name : 'N/A',
                $distribution->region ? $distribution->region->name : 'N/A',
                $distribution->role_type,
                $distribution->amount, // biar Excel bisa hitung
                $distribution->percentage . '%',
                $distribution->is_released ? 'Released' : 'Pending',
                $distribution->released_at ? $distribution->released_at->format('Y-m-d H:i') : ''
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}



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
        $id = Crypt::decrypt($id);
         $distribution = TransactionDistribution::with(['transaction', 'user', 'region','released'])
            ->findOrFail($id);

        $transaction = Transaction::with(['inspection','payer'])->findOrFail($distribution->transaction_id);

        $distributionAll = TransactionDistribution::with('transaction', 'user', 'region', 'released')
            ->where('transaction_id', $transaction->id)
            ->get()
            ->map(function ($item) {
                $item->encrypted_id = Crypt::encrypt($item->id);
                return $item;
            });


        $inspection = Inspection::with([
            'customer',
            'car',
            'car.brand',
            'car.model',
            'car.type',
            'user.roles', // 👈 tambahkan ini
            'submitted.roles',
            ])
            ->findOrFail($transaction->inspection_id);
        
        $inspectionId = Crypt::encrypt($inspection->id);

        return Inertia::render('FrontEnd/Menu/Home/Finance/Show', [
            'distribution' => $distribution,
            'transaction' => $transaction,
            'distributionAll' => $distributionAll,
            'inspection' => $inspection,
            'inspectionId' => $inspectionId,
            'role' => Auth::user()->getRoleNames(),
        ]);
    }

     public function tagihan()
    {
        $userId = Auth::id();

       $transactions = Transaction::where('paid_by', $userId)
            ->whereHas('distributions', function ($q) {
                $q->where('is_released', 'pending');
            })
            ->with(['distributions' => function ($q) {
                $q->where('is_released', 'pending')->with('user');
            }])
            ->get();

        // Hitung total pending amount
        $totalPendingAmount = $transactions->flatMap->distributions->sum('amount');

         // Kelompokkan berdasarkan kolom role_type di distributions
        $groupedByRoleType = $transactions->flatMap->distributions
            ->groupBy('role_type')
            ->map(function ($group) {
                return $group->sum('amount');
            });
            
        return inertia('FrontEnd/Menu/Home/Finance/Tagihan', [
            'transactions' => $transactions,
            'totalPendingAmount' => $totalPendingAmount,
            'groupedByRoleType'  => $groupedByRoleType,
            'role' => Auth::user()->getRoleNames(),
        ]);
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

    public function updateUnified(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $inspection = Inspection::findOrFail($id);

        $validated = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:15',
            'customer_email'   => 'nullable|email',
            'customer_address' => 'nullable|string',
            'amount'           => 'required|numeric|min:0',
            'payment_method'   => 'required|in:cash,transfer,debit_card,credit_card,qris',
            'status'           => 'required|in:pending,paid,failed,refunded',
        ]);

        try {
            DB::beginTransaction(); // 🔒 mulai transaksi

            // =========================
            // 1. Proses Customer
            // =========================
            if (!$inspection->customer_id) {
                $customer = Customer::firstOrCreate(
                    ['phone' => $validated['customer_phone']],
                    [
                        'name'    => $validated['customer_name'],
                        'email'   => $validated['customer_email'],
                        'address' => $validated['customer_address'],
                    ]
                );

                $inspection->customer_id = $customer->id;
                $inspection->save();
                $inspection->addLog('create_customer', 'Membuat data customer baru');
            } else {
                $inspection->customer->update([
                    'name'    => $validated['customer_name'],
                    'email'   => $validated['customer_email'],
                    'address' => $validated['customer_address'],
                ]);
            }

            // =========================
            // 2. Proses Transaction
            // =========================

            $cekStatus = $validated['status'] == 'paid';

            $transaction = Transaction::updateOrCreate(
                ['inspection_id' => $inspection->id],
                [
                    'amount'         => $validated['amount'],
                    'payment_method' => $validated['payment_method'],
                    'status'         => $validated['status'],
                    'paid_by'         => $cekStatus? Auth::user()->id : Null,
                    'invoice_number' => Transaction::generateInvoiceNumber()
                ]
            );

            if (!$transaction) {
                throw new \Exception("Gagal membuat transaksi");
            }

            // =========================
            // 3. Distribusi
        if ($cekStatus) {
            // =========================
            // =========================
            $inspectorUser = $inspection->user; // user yang buat inspection

            $team = RegionTeam::with('region','user')
                ->where('user_id', $inspection->user_id)
                ->first();

            if (!$team) {
                throw new \Exception("Team tidak ditemukan untuk inspector ini.");
            }

            $teamSettings = $team->settings ?? [];
            $inspectionData = Inspection::find($transaction->inspection_id);

            $isSelf   = $inspectionData->submitted_by == $inspectionData->user_id;
            $priceKey = $isSelf ? 'inspection_price_self' : 'inspection_price_external';

            if (!array_key_exists($priceKey, $teamSettings)) {
                throw new \Exception("Pengaturan {$priceKey} belum tersedia di tim {$team->region->name}");
            }

            $inspectionFee = $teamSettings[$priceKey];
            $region         = Region::find($team->region_id);
            $regionSettings = $region->settings ?? [];

            $incomeOwner       = $regionSettings['income_owner'] ?? 100;
            $incomeCoordinator = $regionSettings['income_coordinator'] ?? 0;

            $inspectorShare   = $validated['amount'] - $inspectionFee;
            $coordinatorShare = $inspectionFee * $incomeCoordinator / 100;
            $ownerShare       = $inspectionFee * $incomeOwner / 100;

            // Pastikan distribusi lama dihapus dulu biar tidak dobel
            TransactionDistribution::where('transaction_id', $transaction->id)->delete();

                    // =======================
            // LOGIKA KHUSUS
            // =======================

            // Jika user Admin → hanya Fee Inspeksi
            if ($inspectorUser->hasRole('Admin')) {
                TransactionDistribution::create([
                    'transaction_id'   => $transaction->id,
                    'user_id'          => $inspectorUser->id,
                    'region_id'        => $region->id,
                    'role_type'        => 'Fee Inspeksi',
                    'amount'           => $validated['amount'], // langsung full amount
                    'percentage'       => 0,
                    'calculation_note' => "Admin langsung menerima seluruh fee inspeksi.",
                    'is_released'      => true,
                    'released_at'      => now(),
                    'released_by'      => Auth::id(),
                ]);

            }
            // Jika user Coordinator → Fee Inspeksi + Pendapatan (Owner)
            elseif ($inspectorUser->hasRole('coordinator')) {
                // Fee Inspeksi
                TransactionDistribution::create([
                    'transaction_id'   => $transaction->id,
                    'user_id'          => $inspectorUser->id,
                    'region_id'        => $region->id,
                    'role_type'        => 'Fee Inspeksi',
                    'amount'           => $inspectorShare,
                    'percentage'       => 0,
                    'calculation_note' => "Total amount {$validated['amount']} - inspection fee {$inspectionFee} = {$inspectorShare}",
                    'is_released'      => true,
                ]);

                // Pendapatan (Owner, langsung tanpa komisi coordinator)
                TransactionDistribution::create([
                    'transaction_id'   => $transaction->id,
                    'user_id'          => null, // Owner global
                    'region_id'        => $region->id,
                    'role_type'        => 'Pendapatan',
                    'amount'           => $inspectionFee,
                    'percentage'       => '100',
                    'calculation_note' => "Inspection fee {$inspectionFee} x 100% = {$inspectionFee}",
                    'is_released'      => false,
                ]);
            }
            // Jika user Inspector biasa → normal (3 distribusi)
            else {
            // Inspector
            $inspectorReleased = $transaction->paid_by == $inspection->user_id;
            TransactionDistribution::create([
                'transaction_id'   => $transaction->id,
                'user_id'          => $inspection->user_id,
                'region_id'        => $region->id,
                'role_type'        => 'Fee Inspeksi',
                'amount'           => $inspectorShare,
                'percentage'       => 0,
                'calculation_note' => "Total amount {$validated['amount']} - inspection fee {$inspectionFee} = {$inspectorShare}",
                'is_released'      => $inspectorReleased,
                'released_at'      => $inspectorReleased ? now() : null,
                'released_by'      => $inspectorReleased ? Auth::id() : null,
            ]);

            // Coordinator
            $coordinator = RegionTeam::with('user')
                ->where('region_id', $team->region_id)
                ->whereHas('user.roles', function ($q) {
                    $q->where('name', 'coordinator');
                })
                ->first();

                
                if ($coordinator) {
                $coordinatorReleased =  $transaction->paid_by == $coordinator->user_id;
                TransactionDistribution::create([
                    'transaction_id'   => $transaction->id,
                    'user_id'          => $coordinator->user_id,
                    'region_id'        => $region->id,
                    'role_type'        => 'Fee Koordinasi/Komisi',
                    'amount'           => $coordinatorShare,
                    'percentage'       => $incomeCoordinator,
                    'calculation_note' => "Inspection fee {$inspectionFee} x {$incomeCoordinator}% = {$coordinatorShare}",

                        'is_released'      => $coordinatorReleased,
                    'released_at'      => $coordinatorReleased ? now() : null,
                    'released_by'      => $coordinatorReleased ? Auth::id() : null,
                ]);
            }

            // Owner
            $ownerUser = User::role('Admin')->first();
            $ownerReleased = $transaction->paid_by == optional($ownerUser)->id;
            TransactionDistribution::create([
                'transaction_id'   => $transaction->id,
                'user_id'          => Null,
                'region_id'        => $region->id,
                'role_type'        => 'Pendapatan',
                'amount'           => $ownerShare,
                'percentage'       => $incomeOwner,
                'calculation_note' => "Inspection fee {$inspectionFee} x {$incomeOwner}% = {$ownerShare}",

                    'is_released'      => $ownerReleased,
                'released_at'      => $ownerReleased ? now() : null,
                'released_by'      => $ownerReleased ? Auth::id() : null,
            ]);
        }

        }
            $inspection->addLog('payment', 'Menginput Pembayaran');

            DB::commit(); // ✅ simpan semua perubahan

            return redirect()->back()->with('success', 'Pembayaran berhasil di lakukan.');
        } catch (\Throwable $e) {
            DB::rollBack(); // ❌ batalkan semua jika ada error
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }


    // ==========================Belum jrlas kalau yang di bawah====================
    public function storeTransaction(Request $request, $id)
    {
        $inspection = Inspection::findOrFail($id);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,debit_card,credit_card,qris',
            'status' => 'required|in:pending,paid,failed,refunded'
        ]);
        
        // Buat transaksi baru
        $transaction = Transaction::create([
            'inspection_id' => $inspection->id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'status' => $validated['status'],
            'invoice_number' => Transaction::generateInvoiceNumber()
        ]);
        
        return redirect()->back()->with('success', 'Data pembayaran berhasil disimpan.');
    }

    public function updateTransaction(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,debit_card,credit_card,qris',
            'status' => 'required|in:pending,paid,failed,refunded'
        ]);
        
        $transaction->update($validated);
        
        return redirect()->back()->with('success', 'Data pembayaran berhasil diupdate.');
    }
}
