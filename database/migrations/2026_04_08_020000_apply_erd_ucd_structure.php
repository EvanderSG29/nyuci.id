<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jasas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained()->onDelete('cascade');
            $table->string('nama_jasa');
            $table->string('satuan', 50);
            $table->unsignedBigInteger('harga')->default(0);
            $table->timestamps();

            $table->unique(['toko_id', 'nama_jasa', 'satuan']);
        });

        Schema::create('kliens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained()->onDelete('cascade');
            $table->string('nama_klien');
            $table->string('alamat_klien')->nullable();
            $table->string('no_hp_klien', 30);
            $table->timestamps();

            $table->unique(['toko_id', 'no_hp_klien']);
        });

        Schema::table('laundries', function (Blueprint $table) {
            $table->foreignId('klien_id')->nullable()->after('id')->constrained('kliens')->restrictOnDelete();
            $table->foreignId('jasa_id')->nullable()->after('klien_id')->constrained('jasas')->restrictOnDelete();
            $table->decimal('qty', 10, 2)->nullable()->after('jasa_id');
            $table->string('status')->default('belum_selesai')->after('qty');
            $table->date('tanggal_dimulai')->nullable()->after('status');
            $table->date('ets_selesai')->nullable()->after('tanggal_dimulai');
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->foreignId('klien_id')->nullable()->after('id')->constrained('kliens')->nullOnDelete();
            $table->unsignedBigInteger('total_biaya')->nullable()->after('laundry_id');
        });

        $this->backfillKlienAndJasaData();
        $this->backfillLaundryAndPembayaranRelations();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('klien_id');
            $table->dropColumn('total_biaya');
        });

        Schema::table('laundries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('klien_id');
            $table->dropConstrainedForeignId('jasa_id');
            $table->dropColumn(['qty', 'status', 'tanggal_dimulai', 'ets_selesai']);
        });

        Schema::dropIfExists('kliens');
        Schema::dropIfExists('jasas');
    }

    private function backfillKlienAndJasaData(): void
    {
        DB::table('laundries')
            ->leftJoin('pembayarans', 'pembayarans.laundry_id', '=', 'laundries.id')
            ->select([
                'laundries.id',
                'laundries.toko_id',
                'laundries.nama',
                'laundries.no_hp',
                'laundries.berat',
                'laundries.satuan',
                'laundries.jenis_jasa',
                'laundries.layanan',
                'pembayarans.total',
            ])
            ->orderBy('laundries.id')
            ->chunk(100, function ($rows): void {
                foreach ($rows as $row) {
                    $parsed = $this->parseLegacyUnit($row->satuan, (float) $row->berat);
                    $serviceName = $row->jenis_jasa ?: $row->layanan ?: 'layanan';

                    DB::table('kliens')->updateOrInsert(
                        [
                            'toko_id' => $row->toko_id,
                            'no_hp_klien' => $row->no_hp ?: 'unknown-'.$row->id,
                        ],
                        [
                            'nama_klien' => $row->nama ?: 'Klien',
                            'alamat_klien' => null,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );

                    $estimatedPrice = $parsed['qty'] > 0 && $row->total !== null
                        ? (int) round(((int) $row->total) / $parsed['qty'])
                        : 0;

                    DB::table('jasas')->updateOrInsert(
                        [
                            'toko_id' => $row->toko_id,
                            'nama_jasa' => $serviceName,
                            'satuan' => $parsed['unit'],
                        ],
                        [
                            'harga' => $estimatedPrice,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            });
    }

    private function backfillLaundryAndPembayaranRelations(): void
    {
        DB::table('laundries')
            ->select([
                'id',
                'toko_id',
                'nama',
                'no_hp',
                'berat',
                'satuan',
                'jenis_jasa',
                'layanan',
                'tanggal',
                'estimasi_selesai',
                'tgl_selesai',
                'is_taken',
            ])
            ->orderBy('id')
            ->chunk(100, function ($rows): void {
                foreach ($rows as $row) {
                    $parsed = $this->parseLegacyUnit($row->satuan, (float) $row->berat);
                    $serviceName = $row->jenis_jasa ?: $row->layanan ?: 'layanan';

                    $klienId = DB::table('kliens')
                        ->where('toko_id', $row->toko_id)
                        ->where('no_hp_klien', $row->no_hp ?: 'unknown-'.$row->id)
                        ->value('id');

                    $jasaId = DB::table('jasas')
                        ->where('toko_id', $row->toko_id)
                        ->where('nama_jasa', $serviceName)
                        ->where('satuan', $parsed['unit'])
                        ->value('id');

                    DB::table('laundries')
                        ->where('id', $row->id)
                        ->update([
                            'klien_id' => $klienId,
                            'jasa_id' => $jasaId,
                            'qty' => $parsed['qty'],
                            'status' => $row->is_taken ? 'selesai' : 'belum_selesai',
                            'tanggal_dimulai' => $row->tanggal,
                            'ets_selesai' => $row->estimasi_selesai,
                        ]);

                    DB::table('pembayarans')
                        ->where('laundry_id', $row->id)
                        ->update([
                            'klien_id' => $klienId,
                            'total_biaya' => DB::raw('total'),
                        ]);
                }
            });
    }

    /**
     * @return array{qty: float, unit: string}
     */
    private function parseLegacyUnit(?string $legacyUnit, float $fallbackWeight): array
    {
        $normalized = trim(Str::lower((string) $legacyUnit));

        if ($normalized !== '' && preg_match('/(\d+(?:[.,]\d+)?)\s*(.*)/', $normalized, $matches) === 1) {
            $quantity = (float) str_replace(',', '.', $matches[1]);
            $unit = trim($matches[2]) !== '' ? trim($matches[2]) : 'unit';

            return [
                'qty' => $quantity > 0 ? $quantity : max($fallbackWeight, 1),
                'unit' => $unit,
            ];
        }

        if ($fallbackWeight > 0) {
            return [
                'qty' => $fallbackWeight,
                'unit' => 'kg',
            ];
        }

        return [
            'qty' => 1,
            'unit' => $normalized !== '' ? $normalized : 'unit',
        ];
    }
};
