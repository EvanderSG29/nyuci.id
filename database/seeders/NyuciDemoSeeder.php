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

    private const LAUNDRY_BLUEPRINTS = [
        [
            'service' => 'cuci_kering_lipat_reguler',
            'client' => 0,
            'qty' => 2.50,
            'status' => 'belum_selesai',
            'days_ago' => 13,
            'eta_days' => 3,
            'payment' => 'cash_unpaid',
        ],
        [
            'service' => 'cuci_kering_lipat_premium',
            'client' => 1,
            'qty' => 4.00,
            'status' => 'belum_selesai',
            'days_ago' => 12,
            'eta_days' => 3,
            'payment' => 'qris_pending_a',
        ],
        [
            'service' => 'cuci_setrika_reguler',
            'client' => 2,
            'qty' => 3.00,
            'status' => 'belum_selesai',
            'days_ago' => 4,
            'eta_days' => 3,
            'payment' => 'none',
        ],
        [
            'service' => 'cuci_setrika_express',
            'client' => 3,
            'qty' => 2.00,
            'status' => 'belum_selesai',
            'days_ago' => 0,
            'eta_days' => 1,
            'payment' => 'transfer_unpaid_a',
        ],
        [
            'service' => 'setrika_saja',
            'client' => 4,
            'qty' => 3.50,
            'status' => 'proses',
            'days_ago' => 11,
            'eta_days' => 2,
            'payment' => 'cash_paid_a',
        ],
        [
            'service' => 'dry_cleaning_jas',
            'client' => 5,
            'qty' => 1.00,
            'status' => 'proses',
            'days_ago' => 9,
            'eta_days' => 4,
            'payment' => 'transfer_paid_a',
        ],
        [
            'service' => 'dry_cleaning_kebaya',
            'client' => 6,
            'qty' => 1.00,
            'status' => 'proses',
            'days_ago' => 5,
            'eta_days' => 4,
            'payment' => 'qris_pending_b',
        ],
        [
            'service' => 'laundry_koin_paket_7_kg',
            'client' => 7,
            'qty' => 1.00,
            'status' => 'proses',
            'days_ago' => 1,
            'eta_days' => 1,
            'payment' => 'none',
        ],
        [
            'service' => 'cuci_kering_lipat_reguler',
            'client' => 8,
            'qty' => 5.00,
            'status' => 'selesai',
            'days_ago' => 10,
            'eta_days' => 3,
            'completed_after_days' => 4,
            'payment' => 'qris_paid_a',
        ],
        [
            'service' => 'cuci_setrika_reguler',
            'client' => 9,
            'qty' => 4.00,
            'status' => 'selesai',
            'days_ago' => 8,
            'eta_days' => 3,
            'completed_after_days' => 3,
            'payment' => 'cash_paid_b',
        ],
        [
            'service' => 'cuci_setrika_express',
            'client' => 10,
            'qty' => 2.50,
            'status' => 'selesai',
            'days_ago' => 7,
            'eta_days' => 1,
            'completed_after_days' => 1,
            'payment' => 'transfer_paid_b',
        ],
        [
            'service' => 'setrika_saja',
            'client' => 11,
            'qty' => 6.00,
            'status' => 'selesai',
            'days_ago' => 6,
            'eta_days' => 2,
            'completed_after_days' => 2,
            'payment' => 'ewallet_unpaid',
        ],
        [
            'service' => 'dry_cleaning_jas',
            'client' => 0,
            'qty' => 2.00,
            'status' => 'selesai',
            'days_ago' => 6,
            'eta_days' => 4,
            'completed_after_days' => 4,
            'payment' => 'ewallet_paid',
        ],
        [
            'service' => 'dry_cleaning_kebaya',
            'client' => 1,
            'qty' => 1.00,
            'status' => 'selesai',
            'days_ago' => 5,
            'eta_days' => 4,
            'completed_after_days' => 4,
            'payment' => 'qris_paid_b',
        ],
        [
            'service' => 'laundry_koin_paket_7_kg',
            'client' => 2,
            'qty' => 1.00,
            'status' => 'selesai',
            'days_ago' => 3,
            'eta_days' => 1,
            'completed_after_days' => 1,
            'payment' => 'cash_paid_c',
        ],
        [
            'service' => 'cuci_kering_lipat_premium',
            'client' => 3,
            'qty' => 1.50,
            'status' => 'selesai',
            'days_ago' => 2,
            'eta_days' => 3,
            'completed_after_days' => 2,
            'payment' => 'transfer_unpaid_b',
        ],
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

            $services = $this->seedServices($toko, $stats);
            $clients = $this->seedClients($toko, $stats);

            foreach (self::LAUNDRY_BLUEPRINTS as $index => $blueprint) {
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
    private function seedLaundry(Toko $toko, Klien $klien, Jasa $jasa, array $blueprint, array &$stats): Laundry
    {
        $qty = (float) $blueprint['qty'];
        $tanggalDimulai = today()->subDays($blueprint['days_ago'])->toDateString();
        $estimasiSelesai = today()->subDays($blueprint['days_ago'])->addDays($blueprint['eta_days'])->toDateString();
        $tglSelesai = $blueprint['status'] === 'selesai'
            ? today()->subDays($blueprint['days_ago'])->addDays($blueprint['completed_after_days'] ?? $blueprint['eta_days'])->toDateString()
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
