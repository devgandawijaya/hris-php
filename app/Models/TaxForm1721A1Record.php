<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxForm1721A1Record extends Model
{
    use BelongsToTenant;

    protected $table = 'tax_form1721_a1_records';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'tax_year',
        'nomor_bupot',
        'pemotong_npwp',
        'pemotong_nama',
        'penerima_npwp',
        'penerima_nik',
        'penerima_nama',
        'penerima_alamat',
        'status_ptkp',
        'jumlah_tanggungan',
        'masa_perolehan_awal',
        'masa_perolehan_akhir',
        'gaji_pensiun_tht',
        'tunjangan_pph',
        'tunjangan_lain_lembur',
        'honorarium_imbalan',
        'premi_asuransi_jkk_jkm',
        'natura_kenikmatan',
        'tantiem_bonus_thr',
        'penghasilan_bruto',
        'biaya_jabatan',
        'iuran_pensiun_jht',
        'total_pengurang',
        'penghasilan_neto',
        'penghasilan_neto_masa_sebelumnya',
        'jumlah_penghasilan_neto_disetahunkan',
        'ptkp',
        'pkp',
        'pph21_terutang_setahun',
        'pph21_telah_dipotong',
        'pph21_kurang_lebih_bayar',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tax_year' => 'integer',
            'jumlah_tanggungan' => 'integer',
            'masa_perolehan_awal' => 'integer',
            'masa_perolehan_akhir' => 'integer',
            'gaji_pensiun_tht' => 'decimal:2',
            'tunjangan_pph' => 'decimal:2',
            'tunjangan_lain_lembur' => 'decimal:2',
            'honorarium_imbalan' => 'decimal:2',
            'premi_asuransi_jkk_jkm' => 'decimal:2',
            'natura_kenikmatan' => 'decimal:2',
            'tantiem_bonus_thr' => 'decimal:2',
            'penghasilan_bruto' => 'decimal:2',
            'biaya_jabatan' => 'decimal:2',
            'iuran_pensiun_jht' => 'decimal:2',
            'total_pengurang' => 'decimal:2',
            'penghasilan_neto' => 'decimal:2',
            'penghasilan_neto_masa_sebelumnya' => 'decimal:2',
            'jumlah_penghasilan_neto_disetahunkan' => 'decimal:2',
            'ptkp' => 'decimal:2',
            'pkp' => 'decimal:2',
            'pph21_terutang_setahun' => 'decimal:2',
            'pph21_telah_dipotong' => 'decimal:2',
            'pph21_kurang_lebih_bayar' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
