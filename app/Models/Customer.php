<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model Customer (Member / Mitra).
 *
 * Merepresentasikan member MLM sekaligus customer e-commerce.
 * Memiliki struktur binary tree (upline/downline) dan sponsor network.
 *
 * @property int $id
 * @property int|null $sponsor_id Sponsor referral yang merekrut
 * @property int|null $upline_id Upline dalam binary tree placement
 * @property string|null $position Posisi di binary tree (left|right)
 * @property string|null $ref_code Kode referral unik
 * @property string|null $username Username unik member
 * @property string|null $nik Nomor Induk Kependudukan
 * @property string $name Nama lengkap
 * @property string $email Email untuk login dan komunikasi
 * @property string|null $phone Nomor telepon/WhatsApp
 * @property string $password Password hash
 * @property string|null $gender Jenis kelamin (male|female|L|P)
 * @property string|null $alamat Alamat lengkap (legacy)
 * @property string|null $address Alamat singkat
 * @property int|null $city_id ID kota
 * @property int|null $province_id ID provinsi
 * @property string|null $remember_token Token remember me
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $ewallet_id ID unik e-wallet
 * @property float $ewallet_saldo Saldo e-wallet
 * @property float $bonus_pending Bonus yang belum diproses
 * @property float $bonus_processed Bonus yang sudah diproses
 * @property string|null $bank_name Nama bank untuk penarikan
 * @property string|null $bank_account Nomor rekening bank
 * @property string|null $description Catatan tambahan
 * @property int|null $package_id Paket sesuai omset
 * @property int|null $foot_left ID kaki kiri level 1
 * @property int|null $foot_right ID kaki kanan level 1
 * @property int $total_left Jumlah downline kaki kiri
 * @property int $total_right Jumlah downline kaki kanan
 * @property int $sponsor_left Jumlah member disponsori kaki kiri
 * @property int $sponsor_right Jumlah member disponsori kaki kanan
 * @property int $pv_left PV pairing kaki kiri
 * @property int $pv_right PV pairing kaki kanan
 * @property float $omzet Omset personal
 * @property float $omzet_group Omset group keseluruhan
 * @property float $omzet_planb Omset plan B
 * @property float $omzet_group_left Omset group kaki kiri
 * @property float $omzet_group_right Omset group kaki kanan
 * @property float $omzet_pairing_left Omset pairing kaki kiri
 * @property float $omzet_pairing_right Omset pairing kaki kanan
 * @property string|null $level Level member (Associate|Senior Associate|Executive|Director)
 * @property bool $is_stockist Apakah member adalah stockist
 * @property int $daily_pairing Jumlah pairing harian
 * @property int $max_daily_pairing Batas maksimum pairing harian
 * @property \Illuminate\Support\Carbon|null $last_pairing_date Tanggal pairing terakhir
 * @property bool $network_generated Apakah network sudah digenerate
 * @property int $status Status (1=prospek, 2=pasif, 3=aktif)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guard = 'customer';

    /** @var list<string> */
    protected $fillable = [
        'sponsor_id',
        'upline_id',
        'position',
        'ref_code',
        'username',
        'nik',
        'name',
        'email',
        'phone',
        'password',
        'gender',
        'alamat',
        'address',
        'city_id',
        'province_id',
        'email_verified_at',
        'ewallet_id',
        'ewallet_saldo',
        'bonus_pending',
        'bonus_processed',
        'bank_name',
        'bank_account',
        'description',
        'package_id',
        'foot_left',
        'foot_right',
        'total_left',
        'total_right',
        'sponsor_left',
        'sponsor_right',
        'pv_left',
        'pv_right',
        'omzet',
        'omzet_group',
        'omzet_planb',
        'omzet_group_left',
        'omzet_group_right',
        'omzet_pairing_left',
        'omzet_pairing_right',
        'omzet_group_left_plana',
        'omzet_group_right_plana',
        'omzet_group_left_planb',
        'omzet_group_right_planb',
        'level',
        'is_stockist',
        'stockist_kabupaten_id',
        'stockist_kabupaten_name',
        'stockist_province_id',
        'stockist_province_name',
        'daily_pairing',
        'max_daily_pairing',
        'last_pairing_date',
        'network_generated',
        'status',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_pairing_date' => 'date',
            'password' => 'hashed',
            'ewallet_saldo' => 'decimal:2',
            'bonus_pending' => 'decimal:2',
            'bonus_processed' => 'decimal:2',
            'omzet' => 'decimal:2',
            'omzet_group' => 'decimal:2',
            'is_stockist' => 'boolean',
            'network_generated' => 'boolean',
        ];
    }

    // ──────────────────────────────────────────────
    //  Relasi Binary Tree & Sponsor
    // ──────────────────────────────────────────────

    /**
     * Sponsor yang merekrut customer ini.
     */
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'sponsor_id');
    }

    /**
     * Upline dalam binary tree placement.
     */
    public function upline(): BelongsTo
    {
        return $this->belongsTo(self::class, 'upline_id');
    }

    /**
     * Paket member berdasarkan total omset.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(CustomerPackage::class, 'package_id');
    }

    /**
     * Downline langsung kaki kiri (level 1).
     */
    public function footLeft(): BelongsTo
    {
        return $this->belongsTo(self::class, 'foot_left');
    }

    /**
     * Downline langsung kaki kanan (level 1).
     */
    public function footRight(): BelongsTo
    {
        return $this->belongsTo(self::class, 'foot_right');
    }

    /**
     * Semua member yang disponsori oleh customer ini.
     */
    public function downlines(): HasMany
    {
        return $this->hasMany(self::class, 'sponsor_id');
    }

    /**
     * Semua member di bawah upline ini dalam binary tree.
     */
    public function binaryChildren(): HasMany
    {
        return $this->hasMany(self::class, 'upline_id');
    }

    // ──────────────────────────────────────────────
    //  Relasi E-Commerce
    // ──────────────────────────────────────────────

    /**
     * Alamat-alamat pengiriman customer.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    /**
     * Keranjang belanja aktif.
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Semua order yang dibuat customer.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Review produk yang ditulis customer.
     */
    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Wishlist customer.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    // ──────────────────────────────────────────────
    //  Relasi Bonus & Wallet
    // ──────────────────────────────────────────────

    /**
     * Transaksi e-wallet customer.
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(CustomerWalletTransaction::class);
    }

    /**
     * Bonus gabungan (summary) customer.
     */
    public function bonuses(): HasMany
    {
        return $this->hasMany(CustomerBonus::class, 'member_id');
    }

    /**
     * Bonus sponsor dari downline yang direkrut.
     */
    public function bonusSponsors(): HasMany
    {
        return $this->hasMany(CustomerBonusSponsor::class, 'member_id');
    }

    /**
     * Bonus matching dari kedalaman jaringan.
     */
    public function bonusMatchings(): HasMany
    {
        return $this->hasMany(CustomerBonusMatching::class, 'member_id');
    }

    /**
     * Bonus pairing dari pasangan binary.
     */
    public function bonusPairings(): HasMany
    {
        return $this->hasMany(CustomerBonusPairing::class, 'member_id');
    }

    /**
     * Bonus retail dari selisih harga.
     */
    public function bonusRetails(): HasMany
    {
        return $this->hasMany(CustomerBonusRetail::class, 'member_id');
    }

    /**
     * Bonus cashback dari pembelian.
     */
    public function bonusCashbacks(): HasMany
    {
        return $this->hasMany(CustomerBonusCashback::class, 'member_id');
    }

    /**
     * Bonus reward (promotion/lifetime).
     */
    public function bonusRewards(): HasMany
    {
        return $this->hasMany(CustomerBonusReward::class, 'member_id');
    }

    /**
     * Bonus lifetime cash reward.
     */
    public function bonusLifetimeCashRewards(): HasMany
    {
        return $this->hasMany(CustomerBonusLifetimeCashReward::class, 'member_id');
    }

    // ──────────────────────────────────────────────
    //  Relasi Network & Rewards
    // ──────────────────────────────────────────────

    /**
     * Jaringan binary tree customer.
     */
    public function networks(): HasMany
    {
        return $this->hasMany(CustomerNetwork::class, 'member_id');
    }

    /**
     * Matrix jaringan sponsor.
     */
    public function networkMatrixes(): HasMany
    {
        return $this->hasMany(CustomerNetworkMatrix::class, 'member_id');
    }

    /**
     * Reward yang diraih customer.
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(CustomerReward::class, 'member_id');
    }

    /**
     * BV Reward tracking.
     */
    public function bvRewards(): HasMany
    {
        return $this->hasMany(CustomerBvReward::class, 'member_id');
    }

    /**
     * Data NPWP customer.
     */
    public function npwp(): HasOne
    {
        return $this->hasOne(CustomerNpwp::class, 'member_id');
    }

    public function addBalance(float $amount, ?string $description = null): bool
    {
        if (! $this->exists || $amount <= 0) {
            return false;
        }

        /** @var bool $saved */
        $saved = DB::transaction(function () use ($amount, $description): bool {
            /** @var self|null $lockedCustomer */
            $lockedCustomer = self::query()
                ->whereKey($this->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedCustomer) {
                return false;
            }

            $balanceBefore = (float) ($lockedCustomer->ewallet_saldo ?? 0);

            $lockedCustomer->increment('ewallet_saldo', $amount);
            $lockedCustomer->refresh();

            $balanceAfter = (float) ($lockedCustomer->ewallet_saldo ?? 0);

            $lockedCustomer->walletTransactions()->create([
                'type' => 'topup',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'completed',
                'payment_method' => 'admin_inject',
                'transaction_ref' => 'INJECT-'.strtoupper(Str::random(10)),
                'notes' => $description ?? 'Top up ewallet',
                'completed_at' => now(),
            ]);

            $this->setRawAttributes($lockedCustomer->getAttributes(), sync: true);

            return true;
        });

        return $saved;
    }
}
