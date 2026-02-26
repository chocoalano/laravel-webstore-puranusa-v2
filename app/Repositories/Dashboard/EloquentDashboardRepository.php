<?php

namespace App\Repositories\Dashboard;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerBonus;
use App\Models\CustomerBonusCashback;
use App\Models\CustomerBonusLifetimeCashReward;
use App\Models\CustomerBonusMatching;
use App\Models\CustomerBonusPairing;
use App\Models\CustomerBonusRetail;
use App\Models\CustomerBonusReward;
use App\Models\CustomerBonusSponsor;
use App\Models\CustomerNetwork;
use App\Models\CustomerNpwp;
use App\Models\CustomerWalletTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\Reward;
use App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class EloquentDashboardRepository implements DashboardRepositoryInterface
{
    public function findCustomerById(int $customerId): ?Customer
    {
        return Customer::query()->find($customerId);
    }

    public function countCustomersByPhone(string $phone, ?int $exceptCustomerId = null): int
    {
        return Customer::query()
            ->when($exceptCustomerId !== null, fn (Builder $query) => $query->where('id', '!=', $exceptCustomerId))
            ->where('phone', $phone)
            ->count();
    }

    public function updateCustomerAccount(Customer $customer, array $attributes): void
    {
        $customer->update($attributes);
    }

    public function upsertCustomerNpwp(int $customerId, array $attributes): void
    {
        $now = now();
        $record = CustomerNpwp::query()
            ->where('member_id', $customerId)
            ->first();

        $payload = [
            'nama' => $attributes['nama'] ?? '',
            'npwp' => $attributes['npwp'] ?? '',
            'jk' => $attributes['jk'] ?? 1,
            'npwp_date' => $attributes['npwp_date'] ?? $now->toDateString(),
            'alamat' => $attributes['alamat'] ?? '',
            'menikah' => $attributes['menikah'] ?? 'N',
            'anak' => $attributes['anak'] ?? '0',
            'kerja' => $attributes['kerja'] ?? 'N',
            'office' => $attributes['office'] ?? '',
            'updated' => $now,
            'updatedby' => 'dashboard',
        ];

        if ($record) {
            $record->update($payload);

            return;
        }

        CustomerNpwp::query()->create(array_merge($payload, [
            'member_id' => $customerId,
            'created' => $now,
            'createdby' => 'dashboard',
        ]));
    }

    public function getDefaultAddress(int $customerId): ?CustomerAddress
    {
        return CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    public function countOrders(int $customerId): int
    {
        return Order::query()
            ->where('customer_id', $customerId)
            ->count();
    }

    public function countPendingOrders(int $customerId, array $pendingStatuses): int
    {
        return Order::query()
            ->where('customer_id', $customerId)
            ->whereIn('status', $pendingStatuses)
            ->count();
    }

    public function getPaginatedOrders(int $customerId, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return Order::query()
            ->withCount('items')
            ->with($this->orderRelations())
            ->where('customer_id', $customerId)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'orders_page', $page);
    }

    public function findOrderForCustomer(int $customerId, int $orderId): ?Order
    {
        return Order::query()
            ->withCount('items')
            ->with($this->orderRelations())
            ->where('customer_id', $customerId)
            ->whereKey($orderId)
            ->first();
    }

    public function updatePaymentFromGateway(Payment $payment, string $status, array $gatewayPayload): void
    {
        $transactionId = trim((string) ($gatewayPayload['transaction_id'] ?? ''));
        $signatureKey = trim((string) ($gatewayPayload['signature_key'] ?? ''));

        $attributes = [
            'status' => $status,
            'metadata_json' => $gatewayPayload,
        ];

        if ($transactionId !== '') {
            $attributes['provider_txn_id'] = $transactionId;
            $attributes['transaction_id'] = $transactionId;
        }

        if ($signatureKey !== '') {
            $attributes['signature_key'] = $signatureKey;
        }

        $payment->update($attributes);
    }

    public function createPaymentTransaction(Payment $payment, string $status, float $amount, array $rawPayload): void
    {
        $payment->transactions()->create([
            'status' => $status,
            'amount' => $amount,
            'raw_json' => $rawPayload,
            'created_at' => now(),
        ]);
    }

    public function updatePaymentMetadata(Payment $payment, array $metadata): void
    {
        $payment->update([
            'metadata_json' => $metadata,
        ]);
    }

    public function markOrderAsPaid(Order $order): void
    {
        $normalizedStatus = strtolower((string) $order->status);
        $attributes = [];

        if (in_array($normalizedStatus, ['pending', 'unpaid', 'waiting_payment', 'awaiting_payment'], true)) {
            $attributes['status'] = 'processing';
        }

        if ($order->paid_at === null) {
            $attributes['paid_at'] = now();
        }

        if ($attributes !== []) {
            $order->update($attributes);
        }
    }

    public function getLatestOrderTimestamp(int $customerId): ?CarbonInterface
    {
        $lastPlacedAt = Order::query()
            ->where('customer_id', $customerId)
            ->max('placed_at');

        if (! $lastPlacedAt) {
            return null;
        }

        return Carbon::parse($lastPlacedAt);
    }

    public function countActiveNetworkMembers(int $customerId): int
    {
        return CustomerNetwork::query()
            ->where('upline_id', $customerId)
            ->where('status', 1)
            ->count();
    }

    public function getMaxNetworkLevel(int $customerId): int
    {
        return (int) (CustomerNetwork::query()
            ->where('upline_id', $customerId)
            ->max('level') ?? 0);
    }

    public function sumAvailableBonuses(int $customerId): float
    {
        return (float) CustomerBonus::query()
            ->where('member_id', $customerId)
            ->where('status', 0)
            ->sum('tax_netto');
    }

    public function sumMonthlyBonuses(int $customerId, int $year, int $month): float
    {
        return (float) CustomerBonus::query()
            ->where('member_id', $customerId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('tax_netto');
    }

    public function sumLifetimeBonuses(int $customerId): float
    {
        return (float) CustomerBonusLifetimeCashReward::query()
            ->where('member_id', $customerId)
            ->where('status', 1)
            ->sum('amount');
    }

    public function getBonusStats(int $customerId): array
    {
        $referralIncentive = $this->aggregateBonus(
            CustomerBonusSponsor::query()->where('member_id', $customerId)
        );
        $teamAffiliateCommission = $this->aggregateBonus(
            CustomerBonusMatching::query()->where('member_id', $customerId)
        );
        $partnerTeamCommission = $this->aggregateBonus(
            CustomerBonusPairing::query()->where('member_id', $customerId)
        );
        $cashbackCommission = $this->aggregateBonus(
            CustomerBonusCashback::query()->where('member_id', $customerId)
        );
        $promotionsRewards = $this->aggregateBonus(
            $this->promotionRewardsQuery($customerId)
        );
        $retailCommission = $this->aggregateBonus(
            CustomerBonusRetail::query()->where('member_id', $customerId)
        );
        $lifetimeCashRewards = $this->aggregateBonus(
            CustomerBonusLifetimeCashReward::query()->where('member_id', $customerId)
        );

        $totalAmount = (float) (
            $referralIncentive['amount'] +
            $teamAffiliateCommission['amount'] +
            $partnerTeamCommission['amount'] +
            $cashbackCommission['amount'] +
            $promotionsRewards['amount'] +
            $retailCommission['amount'] +
            $lifetimeCashRewards['amount']
        );
        $totalCount = (int) (
            $referralIncentive['count'] +
            $teamAffiliateCommission['count'] +
            $partnerTeamCommission['count'] +
            $cashbackCommission['count'] +
            $promotionsRewards['count'] +
            $retailCommission['count'] +
            $lifetimeCashRewards['count']
        );

        return [
            'referral_incentive' => $referralIncentive,
            'team_affiliate_commission' => $teamAffiliateCommission,
            'partner_team_commission' => $partnerTeamCommission,
            'cashback_commission' => $cashbackCommission,
            'promotions_rewards' => $promotionsRewards,
            'retail_commission' => $retailCommission,
            'lifetime_cash_rewards' => $lifetimeCashRewards,
            'total_bonus' => [
                'amount' => $totalAmount,
                'count' => $totalCount,
            ],
        ];
    }

    public function getBonusSponsors(int $customerId, int $limit = 50): Collection
    {
        $safeLimit = max(1, $limit);

        return CustomerBonusSponsor::query()
            ->with(['fromMember:id,name,email'])
            ->where('member_id', $customerId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($safeLimit)
            ->get([
                'id',
                'member_id',
                'from_member_id',
                'amount',
                'index_value',
                'status',
                'description',
                'created_at',
            ]);
    }

    public function getBonusMatchings(int $customerId, int $limit = 50): Collection
    {
        $safeLimit = max(1, $limit);

        return CustomerBonusMatching::query()
            ->with(['fromMember:id,name,email'])
            ->where('member_id', $customerId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($safeLimit)
            ->get([
                'id',
                'member_id',
                'from_member_id',
                'level',
                'amount',
                'index_value',
                'status',
                'description',
                'created_at',
            ]);
    }

    public function getBonusPairings(int $customerId, int $limit = 50): Collection
    {
        $safeLimit = max(1, $limit);

        return CustomerBonusPairing::query()
            ->with(['sourceMember:id,name,email'])
            ->where('member_id', $customerId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($safeLimit)
            ->get([
                'id',
                'member_id',
                'source_member_id',
                'pairing_count',
                'amount',
                'index_value',
                'status',
                'description',
                'created_at',
            ]);
    }

    public function getBonusCashbacks(int $customerId, int $limit = 50): Collection
    {
        $safeLimit = max(1, $limit);

        return CustomerBonusCashback::query()
            ->where('member_id', $customerId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($safeLimit)
            ->get([
                'id',
                'member_id',
                'order_id',
                'amount',
                'index_value',
                'status',
                'description',
                'created_at',
            ]);
    }

    public function getPromotionRewards(int $customerId, int $limit = 50): Collection
    {
        $safeLimit = max(1, $limit);

        return $this->promotionRewardsQuery($customerId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($safeLimit)
            ->get([
                'id',
                'member_id',
                'reward_type',
                'reward',
                'bv',
                'amount',
                'index_value',
                'status',
                'description',
                'created_at',
            ]);
    }

    public function getBonusRetails(int $customerId, int $limit = 50): Collection
    {
        $safeLimit = max(1, $limit);

        return CustomerBonusRetail::query()
            ->with(['fromMember:id,name,email'])
            ->where('member_id', $customerId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($safeLimit)
            ->get([
                'id',
                'member_id',
                'from_member_id',
                'amount',
                'index_value',
                'status',
                'description',
                'created_at',
            ]);
    }

    public function getBonusLifetimeCashRewards(int $customerId, int $limit = 50): Collection
    {
        $safeLimit = max(1, $limit);

        return CustomerBonusLifetimeCashReward::query()
            ->where('member_id', $customerId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($safeLimit)
            ->get([
                'id',
                'member_id',
                'reward_name',
                'reward',
                'amount',
                'bv',
                'status',
                'description',
                'created_at',
            ]);
    }

    public function getActiveLifetimeRewards(int $limit = 100): Collection
    {
        $safeLimit = max(1, $limit);

        return Reward::query()
            ->where('type', 1)
            ->where('status', 1)
            ->orderBy('bv')
            ->limit($safeLimit)
            ->get([
                'id',
                'name',
                'reward',
                'bv',
                'value',
                'type',
                'status',
                'created_at',
            ]);
    }

    public function getClaimedLifetimeRewardNames(int $customerId): Collection
    {
        return CustomerBonusReward::query()
            ->where('member_id', $customerId)
            ->whereRaw('LOWER(reward_type) = ?', ['lifetime'])
            ->pluck('reward')
            ->map(fn (mixed $item): string => trim((string) $item))
            ->filter(fn (string $item): bool => $item !== '')
            ->values();
    }

    public function getClaimedLifetimeRewards(int $customerId, int $limit = 50): Collection
    {
        $safeLimit = max(1, $limit);

        return CustomerBonusReward::query()
            ->where('member_id', $customerId)
            ->whereRaw('LOWER(reward_type) = ?', ['lifetime'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($safeLimit)
            ->get([
                'id',
                'member_id',
                'reward_type',
                'reward',
                'bv',
                'amount',
                'status',
                'description',
                'created_at',
            ]);
    }

    public function countActivePromotions(CarbonInterface $asOf): int
    {
        return Promotion::query()
            ->where('is_active', true)
            ->where('start_at', '<=', $asOf)
            ->where('end_at', '>=', $asOf)
            ->count();
    }

    public function getDashboardPromotions(CarbonInterface $asOf, int $limit = 50): Collection
    {
        return Promotion::query()
            ->where(function (Builder $query): void {
                $query->whereNull('show_on')
                    ->orWhere('show_on', '')
                    ->orWhereIn('show_on', ['all', 'dashboard', 'member', 'cart', 'checkout', 'homepage']);
            })
            ->where(function (Builder $query) use ($asOf): void {
                $query->where(function (Builder $inner) use ($asOf): void {
                    $inner->where('is_active', true)
                        ->whereNotNull('start_at')
                        ->whereNotNull('end_at')
                        ->where('end_at', '>=', $asOf->copy()->subDays(30));
                })->orWhere(function (Builder $inner) use ($asOf): void {
                    $inner->where('is_active', true)
                        ->whereNotNull('start_at')
                        ->where('start_at', '>', $asOf);
                });
            })
            ->orderByDesc('priority')
            ->orderBy('end_at')
            ->limit($limit)
            ->get([
                'id',
                'code',
                'name',
                'description',
                'type',
                'landing_slug',
                'start_at',
                'end_at',
                'priority',
                'is_active',
                'max_redemption',
                'per_user_limit',
                'conditions_json',
            ]);
    }

    public function getZennerCategories(int $limit = 100): Collection
    {
        $safeLimit = max(1, $limit);

        return ContentCategory::query()
            ->withCount('contents')
            ->orderBy('name')
            ->limit($safeLimit)
            ->get([
                'id',
                'parent_id',
                'name',
                'slug',
            ]);
    }

    public function getZennerContents(int $limit = 200): Collection
    {
        $safeLimit = max(1, $limit);

        return Content::query()
            ->with([
                'category:id,name,slug,parent_id',
            ])
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->limit($safeLimit)
            ->get([
                'id',
                'category_id',
                'title',
                'slug',
                'content',
                'file',
                'vlink',
                'status',
                'created_at',
                'updated_at',
            ]);
    }

    public function hasNpwp(int $customerId): bool
    {
        return CustomerNpwp::query()
            ->where('member_id', $customerId)
            ->exists();
    }

    public function getWalletTransactions(int $customerId, int $limit = 25): Collection
    {
        $safeLimit = max(1, $limit);

        return CustomerWalletTransaction::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($safeLimit)
            ->get([
                'id',
                'customer_id',
                'type',
                'amount',
                'balance_before',
                'balance_after',
                'status',
                'payment_method',
                'transaction_ref',
                'midtrans_transaction_id',
                'notes',
                'completed_at',
                'is_system',
                'created_at',
            ]);
    }

    public function getPaginatedWalletTransactions(
        int $customerId,
        int $perPage = 15,
        int $page = 1,
        array $filters = [],
    ): LengthAwarePaginator {
        $safePerPage = max(1, $perPage);
        $safePage = max(1, $page);
        $search = trim((string) ($filters['search'] ?? ''));
        $type = trim((string) ($filters['type'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return CustomerWalletTransaction::query()
            ->where('customer_id', $customerId)
            ->when($type !== '' && strtolower($type) !== 'all', function (Builder $query) use ($type): void {
                $query->where('type', strtolower($type));
            })
            ->when($status !== '' && strtolower($status) !== 'all', function (Builder $query) use ($status): void {
                $normalizedStatus = strtolower($status);

                $query->where(function (Builder $inner) use ($normalizedStatus): void {
                    $inner->where('status', $normalizedStatus)
                        ->orWhere('status', strtoupper($normalizedStatus));
                });
            })
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('transaction_ref', 'like', '%' . $search . '%')
                        ->orWhere('payment_method', 'like', '%' . $search . '%')
                        ->orWhere('midtrans_transaction_id', 'like', '%' . $search . '%')
                        ->orWhere('notes', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($safePerPage, ['*'], 'wallet_page', $safePage);
    }

    public function hasPendingWithdrawal(int $customerId): bool
    {
        return CustomerWalletTransaction::query()
            ->where('customer_id', $customerId)
            ->where('type', 'withdrawal')
            ->whereIn('status', ['pending', 'PENDING'])
            ->exists();
    }

    public function findWalletTransactionForCustomer(
        int $customerId,
        int $walletTransactionId,
        bool $lockForUpdate = false,
    ): ?CustomerWalletTransaction {
        $query = CustomerWalletTransaction::query()
            ->where('customer_id', $customerId)
            ->whereKey($walletTransactionId);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function createWalletTransaction(array $attributes): CustomerWalletTransaction
    {
        /** @var CustomerWalletTransaction $transaction */
        $transaction = CustomerWalletTransaction::query()->create($attributes);

        return $transaction;
    }

    public function updateWalletTransaction(CustomerWalletTransaction $transaction, array $attributes): void
    {
        $transaction->update($attributes);
    }

    public function adjustCustomerWalletBalance(Customer $customer, float $delta): void
    {
        if ($delta === 0.0) {
            return;
        }

        if ($delta > 0) {
            $customer->increment('ewallet_saldo', $delta);

            return;
        }

        $customer->decrement('ewallet_saldo', abs($delta));
    }

    public function getSponsoredMembers(int $customerId, int $limit = 200): Collection
    {
        return Customer::query()
            ->with(['package:id,name'])
            ->withCount('orders')
            ->where('sponsor_id', $customerId)
            ->whereIn('status', [1, 2, 3])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get([
                'id',
                'username',
                'name',
                'email',
                'phone',
                'package_id',
                'total_left',
                'total_right',
                'position',
                'level',
                'omzet',
                'status',
                'upline_id',
                'created_at',
            ]);
    }

    public function getBinaryTreeMembers(int $rootCustomerId, int $maxDepth = 6): Collection
    {
        $safeDepth = max(1, $maxDepth);
        $collectedIds = [$rootCustomerId => $rootCustomerId];
        $currentLevelIds = [$rootCustomerId];

        for ($depth = 1; $depth < $safeDepth && $currentLevelIds !== []; $depth++) {
            $members = Customer::query()
                ->whereIn('id', $currentLevelIds)
                ->get([
                    'id',
                    'foot_left',
                    'foot_right',
                ]);

            $nextLevelIds = [];

            foreach ($members as $member) {
                $leftId = $member->foot_left !== null ? (int) $member->foot_left : null;
                $rightId = $member->foot_right !== null ? (int) $member->foot_right : null;

                if ($leftId !== null && ! isset($collectedIds[$leftId])) {
                    $collectedIds[$leftId] = $leftId;
                    $nextLevelIds[] = $leftId;
                }

                if ($rightId !== null && ! isset($collectedIds[$rightId])) {
                    $collectedIds[$rightId] = $rightId;
                    $nextLevelIds[] = $rightId;
                }
            }

            $currentLevelIds = array_values(array_unique($nextLevelIds));
        }

        if ($collectedIds === []) {
            return collect();
        }

        return Customer::query()
            ->with(['package:id,name'])
            ->whereIn('id', array_values($collectedIds))
            ->get([
                'id',
                'username',
                'name',
                'email',
                'phone',
                'package_id',
                'foot_left',
                'foot_right',
                'upline_id',
                'position',
                'status',
                'level',
                'total_left',
                'total_right',
                'created_at',
            ]);
    }

    public function hasBinaryChildAtPosition(int $customerId, string $position): bool
    {
        return Customer::query()
            ->where('upline_id', $customerId)
            ->where('position', $position)
            ->exists();
    }

    public function isMemberInCustomerNetwork(int $uplineCustomerId, int $memberId): bool
    {
        return CustomerNetwork::query()
            ->where('upline_id', $uplineCustomerId)
            ->where('member_id', $memberId)
            ->exists();
    }

    public function findCustomerByIdForUpdate(int $customerId): ?Customer
    {
        return Customer::query()
            ->whereKey($customerId)
            ->lockForUpdate()
            ->first();
    }

    public function updateMemberPlacement(Customer $member, int $uplineId, string $position): void
    {
        $member->update([
            'upline_id' => $uplineId,
            'position' => $position,
            'status' => 3,
        ]);
    }

    public function updateUplineFoot(Customer $upline, string $position, int $memberId): void
    {
        $positionField = $position === 'left' ? 'foot_left' : 'foot_right';

        $upline->update([
            $positionField => $memberId,
        ]);
    }

    public function callRegistrationProcedure(int $memberId): ?stdClass
    {
        $result = DB::select('CALL sp_registration(?)', [$memberId]);
        $row = $result[0] ?? null;

        return $row instanceof stdClass ? $row : null;
    }

    /**
     * @return array{amount:float,count:int}
     */
    private function aggregateBonus(Builder $query): array
    {
        $aggregate = (clone $query)
            ->selectRaw('COALESCE(SUM(amount), 0) as total_amount, COUNT(*) as total_count')
            ->first();

        return [
            'amount' => (float) ($aggregate?->total_amount ?? 0),
            'count' => (int) ($aggregate?->total_count ?? 0),
        ];
    }

    private function promotionRewardsQuery(int $customerId): Builder
    {
        return CustomerBonusReward::query()
            ->where('member_id', $customerId)
            ->where(function (Builder $query): void {
                $query->whereNull('reward_type')
                    ->orWhere('reward_type', '')
                    ->orWhereRaw('LOWER(reward_type) = ?', ['promotion']);
            });
    }

    /**
     * @return list<string>
     */
    private function orderRelations(): array
    {
        return [
            'customer:id,name,email',
            'shippingAddress:id,recipient_name,recipient_phone,address_line1,address_line2,city_label,province_label,district,postal_code,country',
            'items:id,order_id,product_id,name,sku,qty,unit_price,row_total,meta_json',
            'items.product:id,name',
            'items.product.primaryMedia:id,product_id,url,is_primary,sort_order',
            'items.product.media:id,product_id,url,sort_order',
            'payments:id,order_id,method_id,status,amount,currency,provider_txn_id,transaction_id,signature_key,metadata_json,created_at',
            'payments.method:id,name,code',
            'shipments:id,order_id,courier_id,tracking_no,status,created_at',
        ];
    }
}
