<?php

namespace Database\Seeders;

use App\Models\Jasa;
use App\Models\Klien;
use App\Models\Laundry;
use App\Models\Pembayaran;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class NyuciDemoSeeder extends Seeder
{
    public const DEMO_EMAIL = 'demo@nyuci.test';

    public const DEMO_PASSWORD = 'password';

    public const DEMO_STORE_NAME = 'Nyuci Demo Store';

    private const DEMO_LAUNDRY_COUNT = 30;

    private const DEMO_MONTH_COVERAGE_COUNT = 12;

    private const DEMO_RECENT_ACTIVITY_COUNT = 10;

    private const LAUNDRY_STATUSES = ['belum_selesai', 'proses', 'selesai'];

    private const SERVICES = [
        [
            'slug' => 'cuci_kering_lipat_reguler',
            'nama_jasa' => 'Cuci Kering Lipat Reguler',
            'satuan' => 'kg',
            'harga' => 5000,
        ],
        [
            'slug' => 'cuci_kering_lipat_premium',
            'nama_jasa' => 'Cuci Kering Lipat Premium',
            'satuan' => 'kg',
            'harga' => 8500,
        ],
        [
            'slug' => 'cuci_setrika_reguler',
            'nama_jasa' => 'Cuci Setrika Reguler',
            'satuan' => 'kg',
            'harga' => 7000,
        ],
        [
            'slug' => 'cuci_setrika_express',
            'nama_jasa' => 'Cuci Setrika Express',
            'satuan' => 'kg',
            'harga' => 12000,
        ],
        [
            'slug' => 'setrika_saja',
            'nama_jasa' => 'Setrika Saja',
            'satuan' => 'kg',
            'harga' => 4000,
        ],
        [
            'slug' => 'dry_cleaning_jas',
            'nama_jasa' => 'Dry Cleaning Jas',
            'satuan' => 'pcs',
            'harga' => 35000,
        ],
        [
            'slug' => 'dry_cleaning_kebaya',
            'nama_jasa' => 'Dry Cleaning Kebaya',
            'satuan' => 'pcs',
            'harga' => 45000,
        ],
        [
            'slug' => 'laundry_koin_paket_7_kg',
            'nama_jasa' => 'Laundry Koin Paket 7 Kg',
            'satuan' => 'paket',
            'harga' => 25000,
        ],
    ];

    private const CLIENTS = [
        ['name' => 'Andi Saputra', 'city' => 'Jakarta', 'email' => true],
        ['name' => 'Siti Aisyah', 'city' => 'Bandung', 'email' => false],
        ['name' => 'Budi Santoso', 'city' => 'Bogor', 'email' => false],
        ['name' => 'Rina Kartika', 'city' => 'Depok', 'email' => true],
        ['name' => 'Dimas Pratama', 'city' => 'Tangerang', 'email' => false],
        ['name' => 'Maya Sari', 'city' => 'Bekasi', 'email' => false],
        ['name' => 'Fajar Hidayat', 'city' => 'Surabaya', 'email' => true],
        ['name' => 'Nisa Maharani', 'city' => 'Yogyakarta', 'email' => false],
        ['name' => 'Rafi Maulana', 'city' => 'Semarang', 'email' => false],
        ['name' => 'Ayu Lestari', 'city' => 'Denpasar', 'email' => true],
        ['name' => 'Kevin Simanjuntak', 'city' => 'Medan', 'email' => false],
        ['name' => 'Putri Ramadhani', 'city' => 'Makassar', 'email' => false],
    ];

    private const PAYMENT_BY_STATUS = [
        'belum_selesai' => ['cash_unpaid', 'qris_pending_a', 'transfer_unpaid_a'],
        'proses' => ['cash_paid_a', 'transfer_paid_a', 'qris_pending_b'],
        'selesai' => ['qris_paid_a', 'cash_paid_b', 'transfer_paid_b', 'ewallet_paid', 'qris_paid_b', 'cash_paid_c'],
    ];

    public function run(): void
    {
        $this->seed();
    }

    /**
     * @return array{
     *     demo_credentials: array{email: string, password: string},
     *     stores: list<array{
     *         store_name: string,
     *         owner_email: string|null,
     *         stats: array{
     *             jasa: array{created:int,updated:int,skipped:int},
     *             klien: array{created:int,updated:int,skipped:int},
     *             laundry: array{created:int,updated:int,skipped:int},
     *             pembayaran: array{created:int,updated:int,skipped:int}
     *         }
     *     }>
     * }
     */
    public function seed(): array
    {
        $this->ensureDemoStore();

        $storeSummaries = Toko::query()
            ->with('user')
            ->orderBy('id')
            ->get()
            ->map(fn (Toko $toko): array => $this->seedStore($toko))
            ->all();

        return [
            'demo_credentials' => [
                'email' => self::DEMO_EMAIL,
                'password' => self::DEMO_PASSWORD,
            ],
            'stores' => $storeSummaries,
        ];
    }

    /**
     * @return array{
     *     store_name: string,
     *     owner_email: string|null,
     *     stats: array{
     *         jasa: array{created:int,updated:int,skipped:int},
     *         klien: array{created:int,updated:int,skipped:int},
     *         laundry: array{created:int,updated:int,skipped:int},
     *         pembayaran: array{created:int,updated:int,skipped:int}
     *     }
     * }
     */
    private function seedStore(Toko $toko): array
    {
        return DB::transaction(function () use ($toko): array {
            $stats = [
                'jasa' => $this->freshStats(),
                'klien' => $this->freshStats(),
                'laundry' => $this->freshStats(),
                'pembayaran' => $this->freshStats(),
            ];

            $this->clearStoreTransactions($toko);
            $services = $this->seedServices($toko, $stats);
            $clients = $this->seedClients($toko, $stats);
            $blueprints = $this->buildLaundryBlueprints($toko);

            foreach ($blueprints as $index => $blueprint) {
                $laundry = $this->seedLaundry(
                    $toko,
                    $clients[$blueprint['client']],
                    $services[$blueprint['service']],
                    $blueprint,
                    $stats
                );

                $this->seedPayment(
                    $toko,
                    $laundry,
                    $clients[$blueprint['client']],
                    $services[$blueprint['service']],
                    $blueprint,
                    $index,
                    $stats
                );
            }

            return [
                'store_name' => $toko->nama_toko,
                'owner_email' => $toko->user?->email,
                'stats' => $stats,
            ];
        });
    }

    private function clearStoreTransactions(Toko $toko): void
    {
        $laundryIds = Laundry::query()
            ->where('toko_id', $toko->id)
            ->pluck('id');

        if ($laundryIds->isEmpty()) {
            return;
        }

        Pembayaran::query()
            ->whereIn('laundry_id', $laundryIds->all())
            ->delete();

        Laundry::query()
            ->whereIn('id', $laundryIds->all())
            ->delete();
    }

    /**
     * @return list<array{
     *     service: string,
     *     client: int,
     *     qty: float,
     *     status: string,
     *     started_at: string,
     *     eta_days: int,
     *     completed_after_days?: int,
     *     payment: string
     * }>
     */
    private function buildLaundryBlueprints(Toko $toko): array
    {
        $dates = $this->buildLaundryDatePlan();
        $serviceAssignments = $this->buildAssignments(array_column(self::SERVICES, 'slug'), self::DEMO_LAUNDRY_COUNT);
        $clientAssignments = $this->buildAssignments(range(0, count(self::CLIENTS) - 1), self::DEMO_LAUNDRY_COUNT);
        $blueprints = [];

        foreach ($dates as $index => $startedAt) {
            [$status, $payment] = $this->blueprintProfileForIndex($index);
            $etaDays = random_int(1, 5);

            $blueprints[] = [
                'service' => $serviceAssignments[$index],
                'client' => $clientAssignments[$index],
                'qty' => round(random_int(10, 60) / 10, 2),
                'status' => $status,
                'started_at' => $startedAt,
                'eta_days' => $etaDays,
                'completed_after_days' => $status === 'selesai'
                    ? random_int(1, $etaDays)
                    : null,
                'payment' => $payment,
            ];
        }

        return $blueprints;
    }

    /**
     * @return list<string>
     */
    private function buildLaundryDatePlan(): array
    {
        $today = today()->startOfDay();
        $usedDates = [];
        $dates = [];

        for ($monthsBack = self::DEMO_MONTH_COVERAGE_COUNT - 1; $monthsBack >= 0; $monthsBack--) {
            $month = $today->copy()->startOfMonth()->subMonthsNoOverflow($monthsBack);
            $dates[] = $this->pickUniqueDateInMonth($month, $usedDates, $today);
        }

        foreach ($this->buildRecentDateCandidates($today) as $candidate) {
            if (count($dates) >= self::DEMO_MONTH_COVERAGE_COUNT + self::DEMO_RECENT_ACTIVITY_COUNT) {
                break;
            }

            if (! isset($usedDates[$candidate])) {
                $usedDates[$candidate] = true;
                $dates[] = $candidate;
            }
        }

        $historyStart = $today->copy()->startOfMonth()->subMonthsNoOverflow(self::DEMO_MONTH_COVERAGE_COUNT - 1)->startOfDay();

        while (count($dates) < self::DEMO_LAUNDRY_COUNT) {
            $dates[] = $this->pickUniqueDateInRange($historyStart, $today, $usedDates);
        }

        sort($dates);

        return array_values($dates);
    }

    /**
     * @return list<string>
     */
    private function buildRecentDateCandidates(Carbon $today): array
    {
        $candidates = [];

        for ($offset = 0; $offset < 30; $offset++) {
            $candidates[] = $today->copy()->subDays($offset)->toDateString();
        }

        shuffle($candidates);

        return $candidates;
    }

    /**
     * @param  array<string, bool>  $usedDates
     */
    private function pickUniqueDateInMonth(Carbon $month, array &$usedDates, Carbon $today): string
    {
        $maxDay = $month->isSameMonth($today)
            ? min($today->day, $month->daysInMonth)
            : $month->daysInMonth;

        $days = range(1, max($maxDay, 1));
        shuffle($days);

        foreach ($days as $day) {
            $date = $month->copy()->day($day)->toDateString();

            if (! isset($usedDates[$date])) {
                $usedDates[$date] = true;

                return $date;
            }
        }

        throw new \RuntimeException('Unable to generate a unique monthly seed date.');
    }

    /**
     * @param  array<string, bool>  $usedDates
     */
    private function pickUniqueDateInRange(Carbon $start, Carbon $end, array &$usedDates): string
    {
        $offsets = range(0, max($start->diffInDays($end), 0));
        shuffle($offsets);

        foreach ($offsets as $offset) {
            $date = $start->copy()->addDays($offset)->toDateString();

            if (! isset($usedDates[$date])) {
                $usedDates[$date] = true;

                return $date;
            }
        }

        throw new \RuntimeException('Unable to generate a unique historical seed date.');
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function blueprintProfileForIndex(int $index): array
    {
        return match ($index) {
            0 => ['belum_selesai', 'qris_pending_a'],
            1 => ['proses', 'qris_pending_b'],
            2 => ['selesai', 'qris_paid_a'],
            3 => ['selesai', 'qris_paid_b'],
            4 => ['belum_selesai', 'cash_unpaid'],
            5 => ['proses', 'transfer_paid_a'],
            6 => ['selesai', 'ewallet_paid'],
            default => $this->randomBlueprintProfile(),
        };
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function randomBlueprintProfile(): array
    {
        $status = self::LAUNDRY_STATUSES[random_int(0, count(self::LAUNDRY_STATUSES) - 1)];
        $paymentOptions = self::PAYMENT_BY_STATUS[$status];

        return [
            $status,
            $paymentOptions[random_int(0, count($paymentOptions) - 1)],
        ];
    }

    /**
     * @param  list<int|string>  $requiredValues
     * @return list<int|string>
     */
    private function buildAssignments(array $requiredValues, int $count): array
    {
        $assignments = $requiredValues;

        while (count($assignments) < $count) {
            $assignments[] = $requiredValues[random_int(0, count($requiredValues) - 1)];
        }

        shuffle($assignments);

        return array_values($assignments);
    }

    private function ensureDemoStore(): void
    {
        $user = User::query()->firstOrNew(['email' => self::DEMO_EMAIL]);
        $userWasExisting = $user->exists;

        $user->name = 'Nyuci Demo';
        $user->email_verified_at = $user->email_verified_at ?? now();

        if (! $userWasExisting || ! is_string($user->password) || ! Hash::check(self::DEMO_PASSWORD, $user->password)) {
            $user->password = self::DEMO_PASSWORD;
        }

        if (! $userWasExisting || $user->isDirty()) {
            $user->save();
        }

        [$toko] = $this->syncModel(new Toko, [
            'user_id' => $user->id,
        ], [
            'nama_toko' => self::DEMO_STORE_NAME,
            'alamat' => 'Jl. Demo Laundry No. 26, Jakarta Selatan',
            'no_hp' => '081299900026',
        ]);

        $toko->unsetRelation('user');
    }

    /**
     * @param  array{
     *     jasa: array{created:int,updated:int,skipped:int},
     *     klien: array{created:int,updated:int,skipped:int},
     *     laundry: array{created:int,updated:int,skipped:int},
     *     pembayaran: array{created:int,updated:int,skipped:int}
     * }  $stats
     * @return Collection<string, Jasa>
     */
    private function seedServices(Toko $toko, array &$stats): Collection
    {
        return collect(self::SERVICES)->mapWithKeys(function (array $service) use ($toko, &$stats): array {
            [$jasa, $action] = $this->syncModel(new Jasa, [
                'toko_id' => $toko->id,
                'nama_jasa' => $service['nama_jasa'],
                'satuan' => $service['satuan'],
            ], [
                'harga' => $service['harga'],
            ]);

            $this->trackStat($stats['jasa'], $action);

            return [$service['slug'] => $jasa];
        });
    }

    /**
     * @param  array{
     *     jasa: array{created:int,updated:int,skipped:int},
     *     klien: array{created:int,updated:int,skipped:int},
     *     laundry: array{created:int,updated:int,skipped:int},
     *     pembayaran: array{created:int,updated:int,skipped:int}
     * }  $stats
     * @return Collection<int, Klien>
     */
    private function seedClients(Toko $toko, array &$stats): Collection
    {
        return collect(self::CLIENTS)->map(function (array $client, int $index) use ($toko, &$stats): Klien {
            [$klien, $action] = $this->syncModel(new Klien, [
                'toko_id' => $toko->id,
                'no_hp_klien' => $this->phoneForStore($toko->id, $index),
            ], [
                'nama_klien' => $client['name'],
                'email_klien' => $client['email']
                    ? $this->emailForStore($toko->id, $client['name'])
                    : null,
                'alamat_klien' => 'Jl. '.$client['city'].' Raya No. '.($index + 10).', '.$client['city'].', Indonesia',
            ]);

            $this->trackStat($stats['klien'], $action);

            return $klien;
        });
    }

    /**
     * @param  array{
     *     service: string,
     *     client: int,
     *     qty: float,
     *     status: string,
     *     started_at?: string,
     *     days_ago?: int,
     *     eta_days: int,
     *     payment: string,
     *     completed_after_days?: int
     * }  $blueprint
     * @param  array{
     *     jasa: array{created:int,updated:int,skipped:int},
     *     klien: array{created:int,updated:int,skipped:int},
     *     laundry: array{created:int,updated:int,skipped:int},
     *     pembayaran: array{created:int,updated:int,skipped:int}
     * }  $stats
     */
    private function seedLaundry(Toko $toko, Klien $klien, Jasa $jasa, array $blueprint, array &$stats): Laundry
    {
        $qty = (float) $blueprint['qty'];
        $startAt = isset($blueprint['started_at'])
            ? Carbon::parse($blueprint['started_at'])->startOfDay()
            : today()->subDays($blueprint['days_ago']);
        $tanggalDimulai = $startAt->toDateString();
        $estimasiSelesai = $startAt->copy()->addDays($blueprint['eta_days'])->toDateString();
        $tglSelesai = $blueprint['status'] === 'selesai'
            ? $startAt->copy()->addDays($blueprint['completed_after_days'] ?? $blueprint['eta_days'])->toDateString()
            : null;

        [$laundry, $action] = $this->syncLaundryModel([
            'toko_id' => $toko->id,
            'klien_id' => $klien->id,
            'jasa_id' => $jasa->id,
            'tanggal_dimulai' => $tanggalDimulai,
            'qty' => $qty,
        ], [
            'status' => $blueprint['status'],
            'ets_selesai' => $estimasiSelesai,
            'nama' => $klien->nama_klien,
            'no_hp' => $klien->no_hp_klien,
            'berat' => str_contains(strtolower($jasa->satuan), 'kg') ? $qty : 0,
            'tanggal' => $tanggalDimulai,
            'layanan' => $jasa->nama_jasa,
            'jenis_jasa' => $jasa->nama_jasa,
            'satuan' => trim($this->formatQty($qty).' '.$jasa->satuan),
            'estimasi_selesai' => $estimasiSelesai,
            'tgl_selesai' => $tglSelesai,
            'is_taken' => $blueprint['status'] === 'selesai',
        ]);

        $this->trackStat($stats['laundry'], $action);

        return $laundry;
    }

    /**
     * @param  array{toko_id:int,klien_id:int,jasa_id:int,tanggal_dimulai:string,qty:float}  $identity
     * @param  array<string, mixed>  $values
     * @return array{0: Laundry, 1: 'created'|'updated'|'skipped'}
     */
    private function syncLaundryModel(array $identity, array $values): array
    {
        $record = Laundry::query()
            ->where('toko_id', $identity['toko_id'])
            ->where('klien_id', $identity['klien_id'])
            ->where('jasa_id', $identity['jasa_id'])
            ->whereDate('tanggal_dimulai', $identity['tanggal_dimulai'])
            ->where('qty', $identity['qty'])
            ->first() ?? new Laundry();

        $exists = $record->exists;
        $record->fill(array_merge($identity, $values));

        if (! $exists) {
            $record->save();

            return [$record, 'created'];
        }

        if ($record->isDirty()) {
            $record->save();

            return [$record, 'updated'];
        }

        return [$record, 'skipped'];
    }

    /**
     * @param  array{
     *     service: string,
     *     client: int,
     *     qty: float,
     *     status: string,
     *     days_ago: int,
     *     eta_days: int,
     *     payment: string,
     *     completed_after_days?: int
     * }  $blueprint
     * @param  array{
     *     jasa: array{created:int,updated:int,skipped:int},
     *     klien: array{created:int,updated:int,skipped:int},
     *     laundry: array{created:int,updated:int,skipped:int},
     *     pembayaran: array{created:int,updated:int,skipped:int}
     * }  $stats
     */
    private function seedPayment(
        Toko $toko,
        Laundry $laundry,
        Klien $klien,
        Jasa $jasa,
        array $blueprint,
        int $index,
        array &$stats,
    ): void {
        if ($blueprint['payment'] === 'none') {
            return;
        }

        $payload = $this->buildPaymentPayload($toko, $laundry, $klien, $jasa, $blueprint['payment'], $index);
        [$payment, $action] = $this->syncModel(new Pembayaran, [
            'laundry_id' => $laundry->id,
        ], $payload);

        $payment->unsetRelation('laundry');
        $this->trackStat($stats['pembayaran'], $action);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPaymentPayload(Toko $toko, Laundry $laundry, Klien $klien, Jasa $jasa, string $paymentType, int $index): array
    {
        $totalBiaya = (int) round(($laundry->qty ?? 0) * $jasa->harga);
        $base = [
            'klien_id' => $klien->id,
            'total' => $totalBiaya,
            'total_biaya' => $totalBiaya,
            'metode_pembayaran' => 'cash',
            'tgl_pembayaran' => $laundry->tanggal_dimulai?->toDateString() ?? today()->toDateString(),
            'catatan' => 'Menunggu pembayaran pelanggan.',
            'status' => 'belum_bayar',
            'gateway_provider' => null,
            'gateway_reference' => null,
            'gateway_invoice_id' => null,
            'gateway_token' => null,
            'gateway_payment_url' => null,
            'gateway_qr_image' => null,
            'gateway_request_date' => null,
            'gateway_expires_at' => null,
            'gateway_status' => null,
            'gateway_customer_name' => null,
            'gateway_method_by' => null,
            'gateway_paid_at' => null,
            'gateway_payload' => null,
        ];

        $paidAt = $this->resolvePaidAt($laundry);
        $token = Str::lower(sprintf('nyuci-demo-%d-%02d', $toko->id, $index + 1));
        $reference = sprintf('NYUCI-REF-%d-%02d', $toko->id, $index + 1);
        $invoiceId = sprintf('NYUCI-INV-%d-%02d', $toko->id, $index + 1);
        $pendingExpiry = Carbon::parse(today()->addDay()->toDateString().' 09:00:00');

        return match ($paymentType) {
            'cash_unpaid' => [
                ...$base,
                'metode_pembayaran' => 'cash',
                'catatan' => 'Menunggu pembayaran tunai di kasir.',
            ],
            'transfer_unpaid_a', 'transfer_unpaid_b' => [
                ...$base,
                'metode_pembayaran' => 'transfer',
                'catatan' => 'Menunggu transfer bank pelanggan.',
            ],
            'ewallet_unpaid' => [
                ...$base,
                'metode_pembayaran' => 'ewallet',
                'catatan' => 'Menunggu pembayaran via e-wallet.',
            ],
            'cash_paid_a', 'cash_paid_b', 'cash_paid_c' => [
                ...$base,
                'metode_pembayaran' => 'cash',
                'tgl_pembayaran' => $paidAt->toDateString(),
                'catatan' => 'Lunas tunai di toko.',
                'status' => 'sudah_bayar',
            ],
            'transfer_paid_a', 'transfer_paid_b' => [
                ...$base,
                'metode_pembayaran' => 'transfer',
                'tgl_pembayaran' => $paidAt->toDateString(),
                'catatan' => 'Lunas via transfer bank.',
                'status' => 'sudah_bayar',
            ],
            'ewallet_paid' => [
                ...$base,
                'metode_pembayaran' => 'ewallet',
                'tgl_pembayaran' => $paidAt->toDateString(),
                'catatan' => 'Lunas via e-wallet.',
                'status' => 'sudah_bayar',
            ],
            'qris_pending_a', 'qris_pending_b' => [
                ...$base,
                'metode_pembayaran' => 'qris',
                'catatan' => 'Checkout QRIS masih aktif dan menunggu pelanggan.',
                'gateway_provider' => 'qris_static',
                'gateway_reference' => $reference,
                'gateway_invoice_id' => $invoiceId,
                'gateway_token' => $token,
                'gateway_payment_url' => 'https://nyuci.test/demo/checkout/'.$token,
                'gateway_qr_image' => 'data:image/png;base64,'.base64_encode('QRIS-'.$token),
                'gateway_request_date' => today()->toDateString(),
                'gateway_expires_at' => $pendingExpiry,
                'gateway_status' => 'pending',
                'gateway_customer_name' => $klien->nama_klien,
                'gateway_method_by' => 'qris',
                'gateway_payload' => [
                    'source' => 'demo_seeder',
                    'seed_token' => $token,
                    'store_id' => $toko->id,
                ],
            ],
            'qris_paid_a', 'qris_paid_b' => [
                ...$base,
                'metode_pembayaran' => 'qris',
                'tgl_pembayaran' => $paidAt->toDateString(),
                'catatan' => 'Lunas via checkout QRIS.',
                'status' => 'sudah_bayar',
                'gateway_provider' => 'qris_static',
                'gateway_reference' => $reference,
                'gateway_invoice_id' => $invoiceId,
                'gateway_token' => $token,
                'gateway_payment_url' => 'https://nyuci.test/demo/checkout/'.$token,
                'gateway_qr_image' => 'data:image/png;base64,'.base64_encode('QRIS-'.$token),
                'gateway_request_date' => $laundry->tanggal_dimulai?->toDateString() ?? today()->subDay()->toDateString(),
                'gateway_expires_at' => $paidAt->copy()->addHour(),
                'gateway_status' => 'paid',
                'gateway_customer_name' => $klien->nama_klien,
                'gateway_method_by' => 'qris',
                'gateway_paid_at' => $paidAt,
                'gateway_payload' => [
                    'source' => 'demo_seeder',
                    'seed_token' => $token,
                    'store_id' => $toko->id,
                    'paid' => true,
                ],
            ],
            default => $base,
        };
    }

    private function resolvePaidAt(Laundry $laundry): Carbon
    {
        $paymentDate = $laundry->tgl_selesai
            ?? $laundry->ets_selesai
            ?? $laundry->tanggal_dimulai
            ?? today();

        return Carbon::parse($paymentDate->toDateString().' 10:00:00');
    }

    /**
     * @param  Model  $model
     * @param  array<string, mixed>  $identity
     * @param  array<string, mixed>  $values
     * @return array{0: Model, 1: 'created'|'updated'|'skipped'}
     */
    private function syncModel(Model $model, array $identity, array $values): array
    {
        $record = $model->newQuery()->firstOrNew($identity);
        $exists = $record->exists;

        $record->fill(array_merge($identity, $values));

        if (! $exists) {
            $record->save();

            return [$record, 'created'];
        }

        if ($record->isDirty()) {
            $record->save();

            return [$record, 'updated'];
        }

        return [$record, 'skipped'];
    }

    /**
     * @return array{created:int,updated:int,skipped:int}
     */
    private function freshStats(): array
    {
        return [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];
    }

    /**
     * @param  array{created:int,updated:int,skipped:int}  $stats
     */
    private function trackStat(array &$stats, string $action): void
    {
        $stats[$action]++;
    }

    private function phoneForStore(int $storeId, int $index): string
    {
        return sprintf(
            '08%s%s%s',
            $this->tailDigits($storeId, 4),
            str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
            str_pad((string) (7000 + $index), 4, '0', STR_PAD_LEFT),
        );
    }

    private function emailForStore(int $storeId, string $name): string
    {
        return Str::slug($name, '.').'.'.$this->tailDigits($storeId, 4).'@nyuci.test';
    }

    private function tailDigits(int $value, int $length): string
    {
        return substr(str_pad((string) $value, $length, '0', STR_PAD_LEFT), -$length);
    }

    private function formatQty(float $qty): string
    {
        return rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
    }
}
