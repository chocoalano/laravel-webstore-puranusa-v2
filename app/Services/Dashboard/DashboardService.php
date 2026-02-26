<?php

namespace App\Services\Dashboard;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerBonusCashback;
use App\Models\CustomerBonusLifetimeCashReward;
use App\Models\CustomerBonusMatching;
use App\Models\CustomerBonusPairing;
use App\Models\CustomerBonusRetail;
use App\Models\CustomerBonusReward;
use App\Models\CustomerBonusSponsor;
use App\Models\CustomerNpwp;
use App\Models\CustomerWalletTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promotion;
use App\Models\Reward;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Repositories\Shipping\Contracts\ShippingTargetRepositoryInterface;
use App\Services\Payment\MidtransService;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DashboardService
{
    public function __construct(
        protected DashboardRepositoryInterface $dashboardRepository,
        protected CustomerAddressRepositoryInterface $customerAddressRepository,
        protected ShippingTargetRepositoryInterface $shippingTargetRepository,
        protected MidtransService $midtransService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getPageData(
        Customer $authenticatedCustomer,
        int $ordersPage = 1,
        int $walletPage = 1,
        array $walletFilters = [],
    ): array
    {
        $customer = $this->dashboardRepository->findCustomerById($authenticatedCustomer->id);

        if (! $customer) {
            return $this->emptyPageData();
        }

        $now = now();
        $normalizedWalletFilters = $this->normalizeWalletFilters($walletFilters);
        $coreMetrics = $this->loadCoreMetrics($customer, $now);
        $addressData = $this->loadAddressData($customer);
        $mitraMembers = $this->loadMitraMembers($customer);
        $networkData = $this->loadNetworkData($customer);
        $orders = $this->loadOrdersData($customer, max(1, $ordersPage));
        $walletData = $this->loadWalletData($customer, max(1, $walletPage), $normalizedWalletFilters);
        $bonusData = $this->loadBonusData($customer);
        $contentData = $this->loadContentData($now);

        return $this->buildPageDataPayload(
            $customer,
            $addressData,
            $mitraMembers,
            $networkData,
            $orders,
            $walletData,
            $bonusData,
            $contentData,
            $coreMetrics
        );
    }

    /**
     * @param array{default_address:?CustomerAddress,addresses:Collection<int, CustomerAddress>,provinces:array<int, array<string, mixed>>,cities:array<int, array<string, mixed>>,districts:array<int, array<string, mixed>>} $addressData
     * @param array{active:array<int, array<string, mixed>>,passive:array<int, array<string, mixed>>,prospect:array<int, array<string, mixed>>} $mitraMembers
     * @param array{has_left:bool,has_right:bool,tree:?array<string, mixed>,stats:array<string, int>} $networkData
     * @param array{data:array<int, array<string, mixed>>,current_page:int,next_page:int|null,has_more:bool,per_page:int,total:int} $orders
     * @param array{transactions:array<string, mixed>,has_pending_withdrawal:bool} $walletData
     * @param array{bonus_stats:array<string, mixed>,bonus_tables:array<string, array<int, array<string, mixed>>>,lifetime_rewards:array<string, mixed>} $bonusData
     * @param array{promos:array<int, array<string, mixed>>,zenner_categories:array<int, array<string, mixed>>,zenner_contents:array<int, array<string, mixed>>} $contentData
     * @param array{
     *   orders_total:int,
     *   orders_pending:int,
     *   active_network_members:int,
     *   network_level:int,
     *   bonus_available:float,
     *   bonus_month:float,
     *   bonus_lifetime:float,
     *   promo_active:int,
     *   last_order_at:?CarbonInterface,
     *   has_npwp:bool,
     *   left_count:int,
     *   right_count:int,
     *   total_downline:int
     * } $coreMetrics
     * @return array<string, mixed>
     */
    private function buildPageDataPayload(
        Customer $customer,
        array $addressData,
        array $mitraMembers,
        array $networkData,
        array $orders,
        array $walletData,
        array $bonusData,
        array $contentData,
        array $coreMetrics,
    ): array
    {
        return [
            'customer' => $this->formatCustomer($customer),
            'currentCustomerId' => $customer->id,
            'defaultAddress' => $this->formatAddress($addressData['default_address']),
            'addresses' => $this->formatAddresses($addressData['addresses']),
            'provinces' => $addressData['provinces'],
            'cities' => $addressData['cities'],
            'districts' => $addressData['districts'],
            'activeMembers' => Arr::get($mitraMembers, 'active', []),
            'passiveMembers' => Arr::get($mitraMembers, 'passive', []),
            'prospectMembers' => Arr::get($mitraMembers, 'prospect', []),
            'hasLeft' => $networkData['has_left'],
            'hasRight' => $networkData['has_right'],
            'binaryTree' => $networkData['tree'],
            'networkTreeStats' => $networkData['stats'],
            'orders' => $orders,
            'walletTransactions' => $walletData['transactions'],
            'hasPendingWithdrawal' => $walletData['has_pending_withdrawal'],
            'bonusStats' => $bonusData['bonus_stats'],
            'bonusTables' => $bonusData['bonus_tables'],
            'lifetimeRewards' => $bonusData['lifetime_rewards'],
            'promos' => $contentData['promos'],
            'zennerCategories' => $contentData['zenner_categories'],
            'zennerContents' => $contentData['zenner_contents'],
            'midtrans' => [
                'env' => config('services.midtrans.env', 'sandbox'),
                'client_key' => config('services.midtrans.client_key', ''),
            ],
            'stats' => [
                'orders_total'     => $coreMetrics['orders_total'],
                'orders_pending'   => $coreMetrics['orders_pending'],
                'network_total'    => $coreMetrics['total_downline'],
                'network_active'   => $coreMetrics['active_network_members'],
                'network_level'    => $coreMetrics['network_level'],
                'bonus_month'      => $coreMetrics['bonus_month'],
                'bonus_lifetime'   => $coreMetrics['bonus_lifetime'],
                'bonus_available'  => $coreMetrics['bonus_available'],
                'wallet_balance'   => (float) ($customer->ewallet_saldo ?? 0),
                'promo_active'     => $coreMetrics['promo_active'],
            ],
            'networkProfile' => [
                'username'      => $customer->username ?? '—',
                'level'         => $customer->level ?? $this->memberStatusLabel((int) ($customer->status ?? 1)),
                'referral_code' => $customer->ref_code ?? '—',
                'balance'       => (float) ($customer->ewallet_saldo ?? 0),
            ],
            'networkStats' => [
                'left_count'        => $coreMetrics['left_count'],
                'right_count'       => $coreMetrics['right_count'],
                'total_downline'    => $coreMetrics['total_downline'],
                'omset_nb_left'     => (float) ($customer->omzet_group_left ?? 0),
                'omset_nb_right'    => (float) ($customer->omzet_group_right ?? 0),
                'omset_retail_left' => (float) ($customer->omzet_pairing_left ?? 0),
                'omset_retail_right' => (float) ($customer->omzet_pairing_right ?? 0),
                'omset_group'       => (float) ($customer->omzet_group ?? 0),
            ],
            'securitySummary' => [
                'account_status_label' => $this->memberStatusLabel((int) ($customer->status ?? 1)),
                'email_verified'       => $customer->email_verified_at !== null,
                'has_bank_account'     => filled($customer->bank_name) && filled($customer->bank_account),
                'has_npwp'             => $coreMetrics['has_npwp'],
                'last_order_at'        => $coreMetrics['last_order_at']?->toIso8601String(),
            ],
        ];
    }

    /**
     * @return array{
     *   orders_total:int,
     *   orders_pending:int,
     *   active_network_members:int,
     *   network_level:int,
     *   bonus_available:float,
     *   bonus_month:float,
     *   bonus_lifetime:float,
     *   promo_active:int,
     *   last_order_at:?CarbonInterface,
     *   has_npwp:bool,
     *   left_count:int,
     *   right_count:int,
     *   total_downline:int
     * }
     */
    private function loadCoreMetrics(Customer $customer, CarbonInterface $now): array
    {
        $leftCount = (int) ($customer->total_left ?? 0);
        $rightCount = (int) ($customer->total_right ?? 0);
        $totalDownline = $leftCount + $rightCount;

        return [
            'orders_total' => $this->dashboardRepository->countOrders($customer->id),
            'orders_pending' => $this->dashboardRepository->countPendingOrders($customer->id, $this->pendingOrderStatuses()),
            'active_network_members' => $this->dashboardRepository->countActiveNetworkMembers($customer->id),
            'network_level' => $this->dashboardRepository->getMaxNetworkLevel($customer->id),
            'bonus_available' => (float) $this->dashboardRepository->sumAvailableBonuses($customer->id),
            'bonus_month' => (float) $this->dashboardRepository->sumMonthlyBonuses($customer->id, (int) $now->year, (int) $now->month),
            'bonus_lifetime' => (float) $this->dashboardRepository->sumLifetimeBonuses($customer->id),
            'promo_active' => $this->dashboardRepository->countActivePromotions($now),
            'last_order_at' => $this->dashboardRepository->getLatestOrderTimestamp($customer->id),
            'has_npwp' => $this->dashboardRepository->hasNpwp($customer->id),
            'left_count' => $leftCount,
            'right_count' => $rightCount,
            'total_downline' => $totalDownline,
        ];
    }

    /**
     * @return array{
     *   default_address:?CustomerAddress,
     *   addresses:Collection<int, CustomerAddress>,
     *   provinces:array<int, array<string, mixed>>,
     *   cities:array<int, array<string, mixed>>,
     *   districts:array<int, array<string, mixed>>
     * }
     */
    private function loadAddressData(Customer $customer): array
    {
        return [
            'default_address' => $this->dashboardRepository->getDefaultAddress($customer->id),
            'addresses' => $this->customerAddressRepository->getByCustomerId($customer->id),
            'provinces' => $this->shippingTargetRepository->provinceOptions(),
            'cities' => $this->shippingTargetRepository->cityOptions(),
            'districts' => $this->shippingTargetRepository->districtOptions(),
        ];
    }

    /**
     * @return array{active:array<int, array<string, mixed>>,passive:array<int, array<string, mixed>>,prospect:array<int, array<string, mixed>>}
     */
    private function loadMitraMembers(Customer $customer): array
    {
        return $this->formatMitraMembers($this->dashboardRepository->getSponsoredMembers($customer->id));
    }

    /**
     * @return array{has_left:bool,has_right:bool,tree:?array<string, mixed>,stats:array<string, int>}
     */
    private function loadNetworkData(Customer $customer): array
    {
        $treeData = $this->formatBinaryTreeData($customer);

        return [
            'has_left' => $this->dashboardRepository->hasBinaryChildAtPosition($customer->id, 'left'),
            'has_right' => $this->dashboardRepository->hasBinaryChildAtPosition($customer->id, 'right'),
            'tree' => $treeData['tree'],
            'stats' => $treeData['stats'],
        ];
    }

    /**
     * @return array{
     *   data:array<int, array<string, mixed>>,
     *   current_page:int,
     *   next_page:int|null,
     *   has_more:bool,
     *   per_page:int,
     *   total:int
     * }
     */
    private function loadOrdersData(Customer $customer, int $page): array
    {
        $ordersPagination = $this->dashboardRepository->getPaginatedOrders($customer->id, 10, $page);

        return $this->formatOrders($ordersPagination);
    }

    /**
     * @param array{search:string,type:string,status:string,date_from:string,date_to:string} $normalizedWalletFilters
     * @return array{
     *   transactions:array<string, mixed>,
     *   has_pending_withdrawal:bool
     * }
     */
    private function loadWalletData(Customer $customer, int $page, array $normalizedWalletFilters): array
    {
        $walletPagination = $this->dashboardRepository->getPaginatedWalletTransactions(
            $customer->id,
            15,
            $page,
            $normalizedWalletFilters
        );

        return [
            'transactions' => $this->formatWalletTransactionsPagination($walletPagination, $normalizedWalletFilters),
            'has_pending_withdrawal' => $this->dashboardRepository->hasPendingWithdrawal($customer->id),
        ];
    }

    /**
     * @return array{
     *   bonus_stats:array<string, mixed>,
     *   bonus_tables:array<string, array<int, array<string, mixed>>>,
     *   lifetime_rewards:array<string, mixed>
     * }
     */
    private function loadBonusData(Customer $customer): array
    {
        return [
            'bonus_stats' => $this->formatBonusStats($this->dashboardRepository->getBonusStats($customer->id)),
            'bonus_tables' => [
                'referral_incentive' => $this->formatBonusSponsors(
                    $this->dashboardRepository->getBonusSponsors($customer->id)
                ),
                'team_affiliate_commission' => $this->formatBonusMatchings(
                    $this->dashboardRepository->getBonusMatchings($customer->id)
                ),
                'partner_team_commission' => $this->formatBonusPairings(
                    $this->dashboardRepository->getBonusPairings($customer->id)
                ),
                'cashback_commission' => $this->formatBonusCashbacks(
                    $this->dashboardRepository->getBonusCashbacks($customer->id)
                ),
                'promotions_rewards' => $this->formatPromotionRewards(
                    $this->dashboardRepository->getPromotionRewards($customer->id)
                ),
                'retail_commission' => $this->formatBonusRetails(
                    $this->dashboardRepository->getBonusRetails($customer->id)
                ),
                'lifetime_cash_rewards' => $this->formatBonusLifetimeCashRewards(
                    $this->dashboardRepository->getBonusLifetimeCashRewards($customer->id)
                ),
            ],
            'lifetime_rewards' => $this->formatLifetimeRewardsData(
                $customer,
                $this->dashboardRepository->getActiveLifetimeRewards(),
                $this->dashboardRepository->getClaimedLifetimeRewardNames($customer->id),
                $this->dashboardRepository->getClaimedLifetimeRewards($customer->id)
            ),
        ];
    }

    /**
     * @return array{
     *   promos:array<int, array<string, mixed>>,
     *   zenner_categories:array<int, array<string, mixed>>,
     *   zenner_contents:array<int, array<string, mixed>>
     * }
     */
    private function loadContentData(CarbonInterface $asOf): array
    {
        return [
            'promos' => $this->formatPromotions($this->dashboardRepository->getDashboardPromotions($asOf), $asOf),
            'zenner_categories' => $this->formatZennerCategories($this->dashboardRepository->getZennerCategories()),
            'zenner_contents' => $this->formatZennerContents($this->dashboardRepository->getZennerContents()),
        ];
    }

    /**
     * @return list<string>
     */
    private function pendingOrderStatuses(): array
    {
        return ['pending', 'PENDING', 'unpaid', 'waiting_payment', 'awaiting_payment'];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPageData(): array
    {
        return [
            'customer' => null,
            'currentCustomerId' => null,
            'defaultAddress' => null,
            'addresses' => [],
            'provinces' => [],
            'cities' => [],
            'districts' => [],
            'activeMembers' => [],
            'passiveMembers' => [],
            'prospectMembers' => [],
            'hasLeft' => false,
            'hasRight' => false,
            'binaryTree' => null,
            'networkTreeStats' => [
                'left_count' => 0,
                'right_count' => 0,
                'total_downlines' => 0,
            ],
            'orders' => [
                'data' => [],
                'current_page' => 1,
                'next_page' => null,
                'has_more' => false,
                'per_page' => 10,
                'total' => 0,
            ],
            'walletTransactions' => [
                'data' => [],
                'current_page' => 1,
                'next_page' => null,
                'has_more' => false,
                'per_page' => 15,
                'total' => 0,
                'filters' => $this->normalizeWalletFilters([]),
            ],
            'hasPendingWithdrawal' => false,
            'bonusStats' => $this->formatBonusStats([]),
            'bonusTables' => [
                'referral_incentive' => [],
                'team_affiliate_commission' => [],
                'partner_team_commission' => [],
                'cashback_commission' => [],
                'promotions_rewards' => [],
                'retail_commission' => [],
                'lifetime_cash_rewards' => [],
            ],
            'lifetimeRewards' => [
                'summary' => [
                    'accumulated_left' => 0,
                    'accumulated_right' => 0,
                    'eligible_count' => 0,
                    'claimed_count' => 0,
                    'remaining_count' => 0,
                ],
                'rewards' => [],
                'claimed' => [],
            ],
            'promos' => [],
            'zennerCategories' => [],
            'zennerContents' => [],
            'midtrans' => [
                'env' => config('services.midtrans.env', 'sandbox'),
                'client_key' => config('services.midtrans.client_key', ''),
            ],
            'stats' => [
                'orders_total'     => 0,
                'orders_pending'   => 0,
                'network_total'    => 0,
                'network_active'   => 0,
                'network_level'    => 0,
                'bonus_month'      => 0,
                'bonus_lifetime'   => 0,
                'bonus_available'  => 0,
                'wallet_balance'   => 0,
                'promo_active'     => 0,
            ],
            'networkProfile' => [
                'username'      => '—',
                'level'         => '—',
                'referral_code' => '—',
                'balance'       => 0,
            ],
            'networkStats' => [
                'left_count'         => 0,
                'right_count'        => 0,
                'total_downline'     => 0,
                'omset_nb_left'      => 0,
                'omset_nb_right'     => 0,
                'omset_retail_left'  => 0,
                'omset_retail_right' => 0,
                'omset_group'        => 0,
            ],
            'securitySummary' => [
                'account_status_label' => 'Prospek',
                'email_verified'       => false,
                'has_bank_account'     => false,
                'has_npwp'             => false,
                'last_order_at'        => null,
            ],
        ];
    }

    /**
     * @param array{
     *   username:string,
     *   name:string,
     *   nik:string,
     *   gender:string,
     *   email:string,
     *   phone:string,
     *   bank_name:string,
     *   bank_account:string,
     *   npwp:?array{
     *     nama:?string,
     *     npwp:?string,
     *     jk:?int,
     *     npwp_date:?string,
     *     alamat:?string,
     *     menikah:?string,
     *     anak:?string,
     *     kerja:?string,
     *     office:?string
     *   }
     * } $payload
     */
    public function updateAccountProfile(Customer $authenticatedCustomer, array $payload): void
    {
        $customer = $this->dashboardRepository->findCustomerById($authenticatedCustomer->id);

        if (! $customer) {
            throw ValidationException::withMessages([
                'customer' => 'Customer tidak ditemukan.',
            ]);
        }

        DB::transaction(function () use ($customer, $payload): void {
            $lockedCustomer = $this->dashboardRepository->findCustomerByIdForUpdate($customer->id);

            if (! $lockedCustomer) {
                throw ValidationException::withMessages([
                    'customer' => 'Customer tidak ditemukan.',
                ]);
            }

            $incomingPhone = trim((string) ($payload['phone'] ?? ''));
            $currentPhone = trim((string) ($lockedCustomer->phone ?? ''));

            if ($incomingPhone !== '' && $incomingPhone !== $currentPhone) {
                $phoneUsageCount = $this->dashboardRepository->countCustomersByPhone($incomingPhone, $lockedCustomer->id);

                if ($phoneUsageCount >= 7) {
                    throw ValidationException::withMessages([
                        'phone' => 'Nomor telepon/WhatsApp ini sudah digunakan oleh 7 akun.',
                    ]);
                }
            }

            $this->dashboardRepository->updateCustomerAccount($lockedCustomer, [
                'username' => $payload['username'],
                'name' => $payload['name'],
                'nik' => $payload['nik'],
                'gender' => $payload['gender'],
                'email' => $payload['email'],
                'phone' => $incomingPhone,
                'bank_name' => $payload['bank_name'],
                'bank_account' => $payload['bank_account'],
            ]);

            $npwpPayload = $payload['npwp'] ?? null;

            if (is_array($npwpPayload)) {
                $this->dashboardRepository->upsertCustomerNpwp($lockedCustomer->id, $npwpPayload);
            }
        });
    }

    /**
     * @param array{amount:float,notes?:string|null} $payload
     * @return array{
     *   snapToken:string,
     *   walletTransactionId:int,
     *   successUrl:string,
     *   pendingUrl:string,
     *   message:string
     * }
     */
    public function createWalletTopupToken(Customer $authenticatedCustomer, array $payload): array
    {
        $customer = $this->dashboardRepository->findCustomerById($authenticatedCustomer->id);

        if (! $customer) {
            throw ValidationException::withMessages([
                'customer' => 'Customer tidak ditemukan.',
            ]);
        }

        $amount = (float) ($payload['amount'] ?? 0);
        $notes = $this->normalizeWalletTransactionNotes($payload['notes'] ?? null);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal topup tidak valid.',
            ]);
        }

        $transactionRef = $this->generateWalletTransactionRef('TOPUP', $customer->id);
        $transaction = DB::transaction(function () use ($customer, $amount, $notes, $transactionRef): CustomerWalletTransaction {
            $lockedCustomer = $this->dashboardRepository->findCustomerByIdForUpdate($customer->id);

            if (! $lockedCustomer) {
                throw ValidationException::withMessages([
                    'customer' => 'Customer tidak ditemukan.',
                ]);
            }

            $balance = (float) ($lockedCustomer->ewallet_saldo ?? 0);

            return $this->dashboardRepository->createWalletTransaction([
                'customer_id' => $lockedCustomer->id,
                'type' => 'topup',
                'amount' => $amount,
                'balance_before' => $balance,
                'balance_after' => $balance,
                'status' => 'pending',
                'payment_method' => 'midtrans',
                'transaction_ref' => $transactionRef,
                'midtrans_transaction_id' => null,
                'notes' => $notes,
                'completed_at' => null,
                'is_system' => false,
                'midtrans_signature_key' => null,
            ]);
        });

        try {
            $snapToken = $this->midtransService->createSnapTokenForWalletTopup(
                $transactionRef,
                $amount,
                $customer
            );
        } catch (\RuntimeException $exception) {
            $this->dashboardRepository->updateWalletTransaction($transaction, [
                'status' => 'failed',
                'notes' => $this->appendWalletNote($transaction->notes, 'Gagal membuat token Midtrans: ' . $exception->getMessage()),
            ]);

            throw ValidationException::withMessages([
                'payment' => $exception->getMessage(),
            ]);
        }

        return [
            'snapToken' => $snapToken,
            'walletTransactionId' => (int) $transaction->id,
            'successUrl' => route('dashboard', ['section' => 'wallet']),
            'pendingUrl' => route('dashboard', ['section' => 'wallet']),
            'message' => 'Token topup Midtrans berhasil dibuat.',
        ];
    }

    /**
     * @return array{
     *   transaction:array<string, mixed>,
     *   balance:float,
     *   message:string
     * }
     */
    public function syncWalletTopupStatus(Customer $authenticatedCustomer, int $walletTransactionId): array
    {
        $transaction = $this->dashboardRepository->findWalletTransactionForCustomer(
            $authenticatedCustomer->id,
            $walletTransactionId
        );

        if (! $transaction) {
            throw ValidationException::withMessages([
                'transaction' => 'Transaksi wallet tidak ditemukan.',
            ]);
        }

        if ($this->normalizeWalletTransactionType((string) $transaction->type) !== 'topup') {
            throw ValidationException::withMessages([
                'transaction' => 'Transaksi ini bukan transaksi topup.',
            ]);
        }

        $transactionRef = trim((string) ($transaction->transaction_ref ?? ''));

        if ($transactionRef === '') {
            throw ValidationException::withMessages([
                'transaction' => 'Referensi transaksi Midtrans tidak tersedia.',
            ]);
        }

        try {
            $gatewayPayload = $this->midtransService->getTransactionStatus($transactionRef);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'payment' => $exception->getMessage(),
            ]);
        }

        $transactionStatus = strtolower(trim((string) ($gatewayPayload['transaction_status'] ?? '')));
        $fraudStatus = strtolower(trim((string) ($gatewayPayload['fraud_status'] ?? '')));
        $mappedStatus = $this->mapMidtransWalletStatus($transactionStatus, $fraudStatus);

        DB::transaction(function () use ($authenticatedCustomer, $walletTransactionId, $mappedStatus, $gatewayPayload): void {
            $lockedTransaction = $this->dashboardRepository->findWalletTransactionForCustomer(
                $authenticatedCustomer->id,
                $walletTransactionId,
                true
            );

            if (! $lockedTransaction) {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi wallet tidak ditemukan.',
                ]);
            }

            $lockedCustomer = $this->dashboardRepository->findCustomerByIdForUpdate($authenticatedCustomer->id);

            if (! $lockedCustomer) {
                throw ValidationException::withMessages([
                    'customer' => 'Customer tidak ditemukan.',
                ]);
            }

            $currentStatus = $this->normalizeWalletTransactionStatus((string) ($lockedTransaction->status ?? ''));

            if ($currentStatus === 'completed') {
                return;
            }

            $amount = (float) ($lockedTransaction->amount ?? 0);

            if ($mappedStatus === 'completed') {
                $balanceBefore = (float) ($lockedCustomer->ewallet_saldo ?? 0);
                $balanceAfter = $balanceBefore + $amount;

                $this->dashboardRepository->adjustCustomerWalletBalance($lockedCustomer, $amount);

                $this->dashboardRepository->updateWalletTransaction($lockedTransaction, [
                    'status' => 'completed',
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'midtrans_transaction_id' => trim((string) ($gatewayPayload['transaction_id'] ?? '')) ?: $lockedTransaction->midtrans_transaction_id,
                    'midtrans_signature_key' => trim((string) ($gatewayPayload['signature_key'] ?? '')) ?: $lockedTransaction->midtrans_signature_key,
                    'completed_at' => now(),
                    'notes' => $this->appendWalletNote(
                        $lockedTransaction->notes,
                        'Topup Midtrans terkonfirmasi.'
                    ),
                ]);

                return;
            }

            $terminalStatus = $mappedStatus === 'cancelled' ? 'cancelled' : ($mappedStatus === 'failed' ? 'failed' : 'pending');

            $this->dashboardRepository->updateWalletTransaction($lockedTransaction, [
                'status' => $terminalStatus,
                'midtrans_transaction_id' => trim((string) ($gatewayPayload['transaction_id'] ?? '')) ?: $lockedTransaction->midtrans_transaction_id,
                'midtrans_signature_key' => trim((string) ($gatewayPayload['signature_key'] ?? '')) ?: $lockedTransaction->midtrans_signature_key,
                'completed_at' => in_array($terminalStatus, ['failed', 'cancelled'], true) ? now() : $lockedTransaction->completed_at,
            ]);
        });

        $freshTransaction = $this->dashboardRepository->findWalletTransactionForCustomer(
            $authenticatedCustomer->id,
            $walletTransactionId
        );
        $freshCustomer = $this->dashboardRepository->findCustomerById($authenticatedCustomer->id);

        if (! $freshTransaction || ! $freshCustomer) {
            throw ValidationException::withMessages([
                'transaction' => 'Gagal memuat status transaksi wallet terbaru.',
            ]);
        }

        return [
            'transaction' => $this->formatWalletTransaction($freshTransaction),
            'balance' => (float) ($freshCustomer->ewallet_saldo ?? 0),
            'message' => 'Status topup wallet berhasil disinkronkan.',
        ];
    }

    /**
     * @param array{amount:float,password:string,notes?:string|null} $payload
     * @return array{
     *   transaction:array<string, mixed>,
     *   balance:float,
     *   message:string
     * }
     */
    public function submitWalletWithdrawal(Customer $authenticatedCustomer, array $payload): array
    {
        $customer = $this->dashboardRepository->findCustomerById($authenticatedCustomer->id);

        if (! $customer) {
            throw ValidationException::withMessages([
                'customer' => 'Customer tidak ditemukan.',
            ]);
        }

        $amount = (float) ($payload['amount'] ?? 0);
        $password = (string) ($payload['password'] ?? '');
        $notes = $this->normalizeWalletTransactionNotes($payload['notes'] ?? null);

        if (! Hash::check($password, (string) $customer->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password tidak sesuai.',
            ]);
        }

        if (! filled($customer->bank_name) || ! filled($customer->bank_account)) {
            throw ValidationException::withMessages([
                'withdrawal' => 'Data rekening bank belum lengkap. Lengkapi profil bank terlebih dahulu.',
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal withdrawal tidak valid.',
            ]);
        }

        if ($this->dashboardRepository->hasPendingWithdrawal($customer->id)) {
            throw ValidationException::withMessages([
                'withdrawal' => 'Masih ada withdrawal yang sedang diproses.',
            ]);
        }

        $transactionRef = $this->generateWalletTransactionRef('WD', $customer->id);

        $createdTransaction = DB::transaction(function () use ($customer, $amount, $notes, $transactionRef): CustomerWalletTransaction {
            $lockedCustomer = $this->dashboardRepository->findCustomerByIdForUpdate($customer->id);

            if (! $lockedCustomer) {
                throw ValidationException::withMessages([
                    'customer' => 'Customer tidak ditemukan.',
                ]);
            }

            if ($this->dashboardRepository->hasPendingWithdrawal($lockedCustomer->id)) {
                throw ValidationException::withMessages([
                    'withdrawal' => 'Masih ada withdrawal yang sedang diproses.',
                ]);
            }

            $balanceBefore = (float) ($lockedCustomer->ewallet_saldo ?? 0);

            if ($balanceBefore < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo wallet tidak mencukupi untuk withdrawal.',
                ]);
            }

            $balanceAfter = $balanceBefore - $amount;

            $this->dashboardRepository->adjustCustomerWalletBalance($lockedCustomer, -$amount);

            return $this->dashboardRepository->createWalletTransaction([
                'customer_id' => $lockedCustomer->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'pending',
                'payment_method' => 'bank_transfer',
                'transaction_ref' => $transactionRef,
                'midtrans_transaction_id' => null,
                'notes' => $this->buildWithdrawalNotes($lockedCustomer, $notes),
                'completed_at' => null,
                'is_system' => false,
                'midtrans_signature_key' => null,
            ]);
        });

        $freshCustomer = $this->dashboardRepository->findCustomerById($customer->id);

        if (! $freshCustomer) {
            throw ValidationException::withMessages([
                'customer' => 'Gagal memuat saldo wallet terbaru.',
            ]);
        }

        return [
            'transaction' => $this->formatWalletTransaction($createdTransaction),
            'balance' => (float) ($freshCustomer->ewallet_saldo ?? 0),
            'message' => 'Permintaan withdrawal berhasil dikirim.',
        ];
    }

    /**
     * @param array{member_id:int,upline_id:int,position:'left'|'right'} $payload
     * @return array{name:string,position:'left'|'right'}
     */
    public function placeMember(Customer $authenticatedCustomer, array $payload): array
    {
        $memberId = (int) ($payload['member_id'] ?? 0);
        $uplineId = (int) ($payload['upline_id'] ?? 0);
        $position = (string) ($payload['position'] ?? '');

        if (! in_array($position, ['left', 'right'], true)) {
            throw ValidationException::withMessages([
                'position' => 'Posisi tidak valid.',
            ]);
        }

        $isSelfUpline = $authenticatedCustomer->id === $uplineId;
        $isMemberInNetwork = $this->dashboardRepository->isMemberInCustomerNetwork(
            $authenticatedCustomer->id,
            $uplineId
        );

        if (! $isSelfUpline && ! $isMemberInNetwork) {
            throw ValidationException::withMessages([
                'upline_id' => 'Upline tidak berada dalam jaringan Anda.',
            ]);
        }

        try {
            /** @var array{name:string,position:'left'|'right'} $result */
            $result = DB::transaction(function () use ($memberId, $uplineId, $position, $authenticatedCustomer): array {
                $upline = $this->dashboardRepository->findCustomerByIdForUpdate($uplineId);

                if (! $upline) {
                    throw ValidationException::withMessages([
                        'upline_id' => 'Upline tidak ditemukan.',
                    ]);
                }

                $member = $this->dashboardRepository->findCustomerByIdForUpdate($memberId);

                if (! $member) {
                    throw ValidationException::withMessages([
                        'member_id' => 'Member tidak ditemukan.',
                    ]);
                }

                if ($member->upline_id !== null || filled($member->position)) {
                    throw ValidationException::withMessages([
                        'error' => 'Member sudah ditempatkan di binary tree.',
                    ]);
                }

                if ((int) $member->status !== 2) {
                    throw ValidationException::withMessages([
                        'error' => 'Hanya member dengan status Pasif yang dapat diposisikan.',
                    ]);
                }

                if ((int) ($member->sponsor_id ?? 0) !== $authenticatedCustomer->id) {
                    throw ValidationException::withMessages([
                        'error' => 'Member bukan bagian dari jaringan sponsor Anda.',
                    ]);
                }

                $positionField = $position === 'left' ? 'foot_left' : 'foot_right';

                if ($upline->{$positionField} !== null) {
                    throw ValidationException::withMessages([
                        'position' => "Posisi {$position} Anda sudah terisi. Silakan pilih posisi lain.",
                    ]);
                }

                $this->dashboardRepository->updateMemberPlacement($member, $upline->id, $position);
                $this->dashboardRepository->updateUplineFoot($upline, $position, $member->id);

                $spResult = $this->dashboardRepository->callRegistrationProcedure($member->id);

                if (! $spResult) {
                    throw ValidationException::withMessages([
                        'error' => 'Stored procedure tidak mengembalikan output.',
                    ]);
                }

                $isSuccess = (int) ($spResult->success ?? 0) === 1;

                if (! $isSuccess) {
                    $code = (string) ($spResult->code ?? 'UNKNOWN');
                    $message = (string) ($spResult->message ?? 'Terjadi kesalahan pada proses placement.');

                    throw ValidationException::withMessages([
                        'error' => "{$code} - {$message}",
                    ]);
                }

                return [
                    'name' => (string) $member->name,
                    'position' => $position,
                ];
            });

            return $result;
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'error' => 'Gagal memproses placement member. Silakan coba lagi.',
            ]);
        }
    }

    /**
     * Cek status pembayaran order via Midtrans dan sinkronkan ke database.
     *
     * @return array{order:array<string, mixed>,message:string}
     */
    public function checkOrderPaymentStatus(Customer $authenticatedCustomer, int $orderId): array
    {
        $order = $this->dashboardRepository->findOrderForCustomer($authenticatedCustomer->id, $orderId);

        if (! $order) {
            throw ValidationException::withMessages([
                'order' => 'Order tidak ditemukan.',
            ]);
        }

        $latestPayment = $order->payments
            ->sortByDesc(fn ($payment): int => $payment->created_at?->getTimestamp() ?? 0)
            ->first();

        if (! $latestPayment) {
            throw ValidationException::withMessages([
                'payment' => 'Data pembayaran tidak tersedia pada order ini.',
            ]);
        }

        $methodCode = strtolower((string) ($latestPayment->method?->code ?? ''));

        if (! in_array($methodCode, ['p-001', 'midtrans'], true)) {
            return [
                'order' => $this->formatOrder($order),
                'message' => 'Order ini tidak menggunakan metode Midtrans.',
            ];
        }

        try {
            $gatewayPayload = $this->midtransService->getTransactionStatus((string) $order->order_no);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'payment' => $exception->getMessage(),
            ]);
        }

        $transactionStatus = strtolower(trim((string) ($gatewayPayload['transaction_status'] ?? '')));
        $fraudStatus = strtolower(trim((string) ($gatewayPayload['fraud_status'] ?? '')));

        if ($transactionStatus === '') {
            $statusCode = trim((string) ($gatewayPayload['status_code'] ?? ''));
            $statusMessage = trim((string) ($gatewayPayload['status_message'] ?? ''));
            $statusMessageLower = strtolower($statusMessage);
            $isTransactionNotFound = $statusCode === '404'
                || str_contains($statusMessageLower, 'not found')
                || str_contains($statusMessageLower, "doesn't exist")
                || str_contains($statusMessageLower, 'does not exist')
                || str_contains($statusMessageLower, 'not exist');

            if ($isTransactionNotFound) {
                $existingStatus = (string) ($latestPayment->status ?? 'pending');
                $this->dashboardRepository->updatePaymentFromGateway($latestPayment, $existingStatus, $gatewayPayload);

                $refreshedOrder = $this->dashboardRepository->findOrderForCustomer($authenticatedCustomer->id, $orderId);

                return [
                    'order' => $this->formatOrder($refreshedOrder ?? $order),
                    'message' => 'Transaksi Midtrans belum tersedia untuk order ini. Selesaikan pembayaran lalu cek kembali.',
                ];
            }

            $resolvedMessage = $statusMessage !== ''
                ? "Midtrans: {$statusMessage}"
                : 'Status transaksi Midtrans tidak tersedia.';

            throw ValidationException::withMessages([
                'payment' => $resolvedMessage,
            ]);
        }

        $mappedStatus = $this->mapMidtransPaymentStatus($transactionStatus, $fraudStatus);

        $this->dashboardRepository->updatePaymentFromGateway($latestPayment, $mappedStatus, $gatewayPayload);
        $this->dashboardRepository->createPaymentTransaction(
            $latestPayment,
            $mappedStatus,
            (float) ($latestPayment->amount ?? 0),
            $gatewayPayload,
        );

        if ($mappedStatus === 'paid') {
            $this->dashboardRepository->markOrderAsPaid($order);
        }

        $refreshedOrder = $this->dashboardRepository->findOrderForCustomer($authenticatedCustomer->id, $orderId);

        if (! $refreshedOrder) {
            throw ValidationException::withMessages([
                'order' => 'Order tidak ditemukan setelah sinkronisasi status pembayaran.',
            ]);
        }

        return [
            'order' => $this->formatOrder($refreshedOrder),
            'message' => 'Status pembayaran berhasil diperbarui dari Midtrans.',
        ];
    }

    /**
     * Buat token Midtrans untuk order existing yang belum dibayar.
     *
     * @return array<string, string|null>
     */
    public function createMidtransPayNowToken(Customer $authenticatedCustomer, int $orderId): array
    {
        $order = $this->dashboardRepository->findOrderForCustomer($authenticatedCustomer->id, $orderId);

        if (! $order) {
            throw ValidationException::withMessages([
                'order' => 'Order tidak ditemukan.',
            ]);
        }

        $latestPayment = $order->payments
            ->sortByDesc(fn ($payment): int => $payment->created_at?->getTimestamp() ?? 0)
            ->first();

        if (! $latestPayment) {
            throw ValidationException::withMessages([
                'payment' => 'Data pembayaran tidak tersedia pada order ini.',
            ]);
        }

        $methodCode = strtolower((string) ($latestPayment->method?->code ?? ''));

        if (! in_array($methodCode, ['p-001', 'midtrans'], true)) {
            throw ValidationException::withMessages([
                'payment' => 'Order ini tidak menggunakan metode pembayaran Midtrans.',
            ]);
        }

        $normalizedPaymentStatus = $this->normalizePaymentStatus((string) ($latestPayment->status ?? ''));

        if (in_array($normalizedPaymentStatus, ['paid', 'refunded'], true)) {
            throw ValidationException::withMessages([
                'payment' => 'Order ini sudah tidak dapat dibayar ulang.',
            ]);
        }

        $metadata = is_array($latestPayment->metadata_json) ? $latestPayment->metadata_json : [];
        $snapToken = trim((string) ($metadata['snap_token'] ?? ''));

        if ($snapToken !== '') {
            return [
                'snapToken' => $snapToken,
                'redirectUrl' => null,
                'successUrl' => route('dashboard'),
                'pendingUrl' => route('dashboard'),
                'message' => 'Token pembayaran Midtrans siap digunakan.',
            ];
        }

        try {
            $newSnapToken = $this->midtransService->createSnapTokenForOrder($order, $authenticatedCustomer);
        } catch (\RuntimeException $exception) {
            $errorMessage = strtolower($exception->getMessage());

            if (str_contains($errorMessage, 'already been taken')) {
                try {
                    $gatewayPayload = $this->midtransService->getTransactionStatus((string) $order->order_no);
                } catch (\RuntimeException $statusException) {
                    throw ValidationException::withMessages([
                        'payment' => $statusException->getMessage(),
                    ]);
                }
                $transactionStatus = strtolower(trim((string) ($gatewayPayload['transaction_status'] ?? '')));
                $fraudStatus = strtolower(trim((string) ($gatewayPayload['fraud_status'] ?? '')));

                if ($transactionStatus !== '') {
                    $mappedStatus = $this->mapMidtransPaymentStatus($transactionStatus, $fraudStatus);

                    $this->dashboardRepository->updatePaymentFromGateway($latestPayment, $mappedStatus, $gatewayPayload);
                    $this->dashboardRepository->createPaymentTransaction(
                        $latestPayment,
                        $mappedStatus,
                        (float) ($latestPayment->amount ?? 0),
                        $gatewayPayload,
                    );

                    if ($mappedStatus === 'paid') {
                        $this->dashboardRepository->markOrderAsPaid($order);

                        throw ValidationException::withMessages([
                            'payment' => 'Order ini sudah dibayar.',
                        ]);
                    }
                }

                $redirectUrl = $this->extractMidtransRedirectUrl($gatewayPayload);

                if ($redirectUrl !== null) {
                    return [
                        'snapToken' => null,
                        'redirectUrl' => $redirectUrl,
                        'successUrl' => route('dashboard'),
                        'pendingUrl' => route('dashboard'),
                        'message' => 'Transaksi Midtrans sudah tersedia. Lanjutkan pembayaran dari halaman Midtrans.',
                    ];
                }

                throw ValidationException::withMessages([
                    'payment' => 'Transaksi Midtrans sudah dibuat sebelumnya. Silakan cek status pembayaran terlebih dahulu.',
                ]);
            }

            throw ValidationException::withMessages([
                'payment' => $exception->getMessage(),
            ]);
        }

        $updatedMetadata = array_merge($metadata, [
            'snap_token' => $newSnapToken,
            'snap_created_at' => now()->toIso8601String(),
        ]);

        $this->dashboardRepository->updatePaymentMetadata($latestPayment, $updatedMetadata);

        return [
            'snapToken' => $newSnapToken,
            'redirectUrl' => null,
            'successUrl' => route('dashboard'),
            'pendingUrl' => route('dashboard'),
            'message' => 'Token pembayaran Midtrans berhasil dibuat.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatCustomer(Customer $customer): array
    {
        $customer->loadMissing('npwp');

        return [
            'id'           => $customer->id,
            'username'     => $customer->username,
            'nik'          => $customer->nik,
            'name'         => $customer->name,
            'gender'       => $customer->gender,
            'email'        => $customer->email,
            'phone'        => $customer->phone,
            'alamat'       => $customer->alamat,
            'bank_name'    => $customer->bank_name,
            'bank_account' => $customer->bank_account,
            'npwp'         => $this->formatCustomerNpwp($customer->npwp),
            'avatar_url'   => null,
            'tier'         => $customer->level ?? $this->memberStatusLabel((int) ($customer->status ?? 1)),
            'member_since' => $customer->created_at?->toIso8601String(),
            'wallet_balance' => (float) ($customer->ewallet_saldo ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function formatCustomerNpwp(?CustomerNpwp $npwp): ?array
    {
        if (! $npwp) {
            return null;
        }

        return [
            'nama' => $npwp->nama,
            'npwp' => $npwp->npwp,
            'jk' => $npwp->jk !== null ? (int) $npwp->jk : null,
            'npwp_date' => $npwp->npwp_date?->toDateString(),
            'alamat' => $npwp->alamat,
            'menikah' => $npwp->menikah,
            'anak' => $npwp->anak,
            'kerja' => $npwp->kerja,
            'office' => $npwp->office,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function formatAddress(?CustomerAddress $address): ?array
    {
        if (! $address) {
            return null;
        }

        return [
            'id'             => $address->id,
            'label'          => $address->label ?? 'Alamat',
            'recipient_name' => $address->recipient_name,
            'phone'          => $address->recipient_phone,
            'address_line'   => $address->address_line1,
            'city'           => $address->city_label,
            'province'       => $address->province_label,
            'postal_code'    => $address->postal_code ?? '-',
            'is_default'     => (bool) $address->is_default,
        ];
    }

    /**
     * @param Collection<int, CustomerAddress> $addresses
     * @return list<array<string, mixed>>
     */
    private function formatAddresses(Collection $addresses): array
    {
        return $addresses->map(fn (CustomerAddress $address): array => [
            'id'             => $address->id,
            'label'          => $address->label,
            'is_default'     => (bool) $address->is_default,
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->recipient_phone,
            'address_line1'  => $address->address_line1,
            'address_line2'  => $address->address_line2,
            'province_label' => $address->province_label,
            'province_id'    => (int) $address->province_id,
            'city_label'     => $address->city_label,
            'city_id'        => (int) $address->city_id,
            'district'       => $address->district,
            'district_lion'  => $address->district_lion,
            'postal_code'    => $address->postal_code,
            'country'        => $address->country,
            'description'    => $address->description,
        ])->toArray();
    }

    /**
     * @param array{
     *   referral_incentive?:array{amount?:float|int,count?:int},
     *   team_affiliate_commission?:array{amount?:float|int,count?:int},
     *   partner_team_commission?:array{amount?:float|int,count?:int},
     *   cashback_commission?:array{amount?:float|int,count?:int},
     *   promotions_rewards?:array{amount?:float|int,count?:int},
     *   retail_commission?:array{amount?:float|int,count?:int},
     *   lifetime_cash_rewards?:array{amount?:float|int,count?:int},
     *   total_bonus?:array{amount?:float|int,count?:int}
     * } $stats
     * @return list<array{
     *   key:string,
     *   title:string,
     *   icon:string,
     *   amount:float,
     *   count:int
     * }>
     */
    private function formatBonusStats(array $stats): array
    {
        $definitions = [
            'referral_incentive' => [
                'title' => 'Referral Incentive',
                'icon' => 'i-lucide-users',
            ],
            'team_affiliate_commission' => [
                'title' => 'Team Affiliate Commission',
                'icon' => 'i-lucide-handshake',
            ],
            'partner_team_commission' => [
                'title' => 'Partner Team Commission',
                'icon' => 'i-lucide-network',
            ],
            'cashback_commission' => [
                'title' => 'Cashback Commission',
                'icon' => 'i-lucide-percent',
            ],
            'promotions_rewards' => [
                'title' => 'Promotions Rewards',
                'icon' => 'i-lucide-gift',
            ],
            'retail_commission' => [
                'title' => 'Retail Commission',
                'icon' => 'i-lucide-store',
            ],
            'lifetime_cash_rewards' => [
                'title' => 'Lifetime Cash Rewards',
                'icon' => 'i-lucide-trophy',
            ],
            'total_bonus' => [
                'title' => 'Total Bonus',
                'icon' => 'i-lucide-wallet-cards',
            ],
        ];

        return collect($definitions)->map(
            function (array $definition, string $key) use ($stats): array {
                $entry = $stats[$key] ?? [];

                return [
                    'key' => $key,
                    'title' => (string) ($definition['title'] ?? $key),
                    'icon' => (string) ($definition['icon'] ?? 'i-lucide-coins'),
                    'amount' => (float) ($entry['amount'] ?? 0),
                    'count' => (int) ($entry['count'] ?? 0),
                ];
            }
        )->values()->all();
    }

    /**
     * @param Collection<int, CustomerBonusSponsor> $bonuses
     * @return list<array<string, mixed>>
     */
    private function formatBonusSponsors(Collection $bonuses): array
    {
        return $bonuses->map(function (CustomerBonusSponsor $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'type' => 'referral_incentive',
                'type_label' => 'Referral Incentive',
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
                'from_member' => $this->formatBonusMember($bonus->fromMember),
                'meta' => [
                    'index_value' => (float) ($bonus->index_value ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @param Collection<int, CustomerBonusMatching> $bonuses
     * @return list<array<string, mixed>>
     */
    private function formatBonusMatchings(Collection $bonuses): array
    {
        return $bonuses->map(function (CustomerBonusMatching $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'type' => 'team_affiliate_commission',
                'type_label' => 'Team Affiliate Commission',
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
                'from_member' => $this->formatBonusMember($bonus->fromMember),
                'meta' => [
                    'level' => $bonus->level !== null ? (int) $bonus->level : null,
                    'index_value' => (float) ($bonus->index_value ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @param Collection<int, CustomerBonusPairing> $bonuses
     * @return list<array<string, mixed>>
     */
    private function formatBonusPairings(Collection $bonuses): array
    {
        return $bonuses->map(function (CustomerBonusPairing $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'type' => 'partner_team_commission',
                'type_label' => 'Partner Team Commission',
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
                'from_member' => $this->formatBonusMember($bonus->sourceMember),
                'meta' => [
                    'pairing_count' => (int) ($bonus->pairing_count ?? 0),
                    'index_value' => (float) ($bonus->index_value ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @param Collection<int, CustomerBonusCashback> $bonuses
     * @return list<array<string, mixed>>
     */
    private function formatBonusCashbacks(Collection $bonuses): array
    {
        return $bonuses->map(function (CustomerBonusCashback $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'type' => 'cashback_commission',
                'type_label' => 'Cashback Commission',
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
                'from_member' => null,
                'meta' => [
                    'order_id' => $bonus->order_id !== null ? (int) $bonus->order_id : null,
                    'index_value' => (float) ($bonus->index_value ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @param Collection<int, CustomerBonusReward> $bonuses
     * @return list<array<string, mixed>>
     */
    private function formatPromotionRewards(Collection $bonuses): array
    {
        return $bonuses->map(function (CustomerBonusReward $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'type' => 'promotions_rewards',
                'type_label' => 'Promotions Rewards',
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
                'from_member' => null,
                'meta' => [
                    'reward_type' => $bonus->reward_type,
                    'reward_name' => $bonus->reward,
                    'bv' => (float) ($bonus->bv ?? 0),
                    'index_value' => (float) ($bonus->index_value ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @param Collection<int, CustomerBonusRetail> $bonuses
     * @return list<array<string, mixed>>
     */
    private function formatBonusRetails(Collection $bonuses): array
    {
        return $bonuses->map(function (CustomerBonusRetail $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'type' => 'retail_commission',
                'type_label' => 'Retail Commission',
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
                'from_member' => $this->formatBonusMember($bonus->fromMember),
                'meta' => [
                    'index_value' => (float) ($bonus->index_value ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @param Collection<int, CustomerBonusLifetimeCashReward> $bonuses
     * @return list<array<string, mixed>>
     */
    private function formatBonusLifetimeCashRewards(Collection $bonuses): array
    {
        return $bonuses->map(function (CustomerBonusLifetimeCashReward $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'type' => 'lifetime_cash_rewards',
                'type_label' => 'Lifetime Cash Rewards',
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
                'from_member' => null,
                'meta' => [
                    'reward_name' => $bonus->reward_name,
                    'reward' => (float) ($bonus->reward ?? 0),
                    'bv' => (float) ($bonus->bv ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @return array{name:string,email:string|null}|null
     */
    private function formatBonusMember(?Customer $customer): ?array
    {
        if (! $customer) {
            return null;
        }

        return [
            'name' => $customer->name,
            'email' => $customer->email,
        ];
    }

    private function normalizeBonusStatus(int|string|null $status): string
    {
        if (is_string($status)) {
            $normalized = strtolower(trim($status));

            if (in_array($normalized, ['1', 'released', 'success', 'completed', 'settlement'], true)) {
                return 'released';
            }

            return 'pending';
        }

        return ((int) $status) === 1 ? 'released' : 'pending';
    }

    private function bonusStatusLabel(string $status): string
    {
        return $status === 'released' ? 'Released' : 'Pending';
    }

    /**
     * @param Collection<int, Reward> $activeRewards
     * @param Collection<int, string> $claimedRewardNames
     * @param Collection<int, CustomerBonusReward> $claimedLifetimeRewards
     * @return array{
     *   summary:array{
     *     accumulated_left:float,
     *     accumulated_right:float,
     *     eligible_count:int,
     *     claimed_count:int,
     *     remaining_count:int
     *   },
     *   rewards:list<array<string, mixed>>,
     *   claimed:list<array<string, mixed>>
     * }
     */
    private function formatLifetimeRewardsData(
        Customer $customer,
        Collection $activeRewards,
        Collection $claimedRewardNames,
        Collection $claimedLifetimeRewards,
    ): array {
        $accumulatedLeft = (float) ($customer->omzet_group_left_planb ?? 0);
        $accumulatedRight = (float) ($customer->omzet_group_right_planb ?? 0);
        $claimedLookup = $claimedRewardNames
            ->map(fn (string $reward): string => strtolower(trim($reward)))
            ->filter(fn (string $reward): bool => $reward !== '')
            ->values()
            ->all();

        $rewards = $activeRewards->map(function (Reward $reward) use (
            $accumulatedLeft,
            $accumulatedRight,
            $claimedLookup
        ): array {
            $requiredBv = (float) ($reward->bv ?? 0);
            $isClaimed = in_array(strtolower(trim((string) $reward->name)), $claimedLookup, true);
            $canClaim = ! $isClaimed
                && $accumulatedLeft >= $requiredBv
                && $accumulatedRight >= $requiredBv;
            $progressLeft = $requiredBv > 0
                ? min(100.0, ($accumulatedLeft / $requiredBv) * 100.0)
                : 100.0;
            $progressRight = $requiredBv > 0
                ? min(100.0, ($accumulatedRight / $requiredBv) * 100.0)
                : 100.0;

            return [
                'id' => (int) $reward->id,
                'name' => $reward->name,
                'reward' => $reward->reward,
                'bv' => $requiredBv,
                'value' => (float) ($reward->value ?? 0),
                'can_claim' => $canClaim,
                'is_claimed' => $isClaimed,
                'accumulated_left' => $accumulatedLeft,
                'accumulated_right' => $accumulatedRight,
                'progress_left' => round($progressLeft, 2),
                'progress_right' => round($progressRight, 2),
                'progress_percent' => round(min($progressLeft, $progressRight), 2),
            ];
        })->values();

        $claimed = $claimedLifetimeRewards->map(function (CustomerBonusReward $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'reward' => $bonus->reward,
                'bv' => (float) ($bonus->bv ?? 0),
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
            ];
        })->values();

        $eligibleCount = $rewards->where('can_claim', true)->count();
        $claimedCount = $rewards->where('is_claimed', true)->count();
        $totalRewardCount = $rewards->count();

        return [
            'summary' => [
                'accumulated_left' => $accumulatedLeft,
                'accumulated_right' => $accumulatedRight,
                'eligible_count' => $eligibleCount,
                'claimed_count' => $claimedCount,
                'remaining_count' => max(0, $totalRewardCount - $claimedCount),
            ],
            'rewards' => $rewards->all(),
            'claimed' => $claimed->all(),
        ];
    }

    /**
     * @param Collection<int, CustomerWalletTransaction> $transactions
     * @return list<array<string, mixed>>
     */
    private function formatWalletTransactions(Collection $transactions): array
    {
        return $transactions
            ->map(fn (CustomerWalletTransaction $transaction): array => $this->formatWalletTransaction($transaction))
            ->values()
            ->all();
    }

    /**
     * @param array{search?:string|null,type?:string|null,status?:string|null} $filters
     * @return array{
     *   data:list<array<string, mixed>>,
     *   current_page:int,
     *   next_page:int|null,
     *   has_more:bool,
     *   per_page:int,
     *   total:int,
     *   filters:array{search:string|null,type:string|null,status:string|null}
     * }
     */
    private function formatWalletTransactionsPagination(LengthAwarePaginator $paginator, array $filters): array
    {
        $transactions = collect($paginator->items())
            ->filter(fn (mixed $item): bool => $item instanceof CustomerWalletTransaction)
            ->map(fn (CustomerWalletTransaction $transaction): array => $this->formatWalletTransaction($transaction))
            ->values()
            ->all();

        return [
            'data' => $transactions,
            'current_page' => $paginator->currentPage(),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
            'has_more' => $paginator->hasMorePages(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'filters' => $this->normalizeWalletFilters($filters),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatWalletTransaction(CustomerWalletTransaction $transaction): array
    {
        $type = $this->normalizeWalletTransactionType((string) ($transaction->type ?? ''));
        $status = $this->normalizeWalletTransactionStatus((string) ($transaction->status ?? ''));

        return [
            'id' => $transaction->id,
            'type' => $type,
            'type_label' => $this->walletTransactionTypeLabel($type),
            'direction' => $this->walletTransactionDirection($type),
            'status' => $status,
            'status_label' => $this->walletTransactionStatusLabel($status),
            'amount' => (float) ($transaction->amount ?? 0),
            'balance_before' => (float) ($transaction->balance_before ?? 0),
            'balance_after' => (float) ($transaction->balance_after ?? 0),
            'payment_method' => $transaction->payment_method,
            'transaction_ref' => $transaction->transaction_ref,
            'midtrans_transaction_id' => $transaction->midtrans_transaction_id,
            'notes' => $this->normalizeWalletTransactionNotes($transaction->notes),
            'is_system' => (bool) ($transaction->is_system ?? false),
            'created_at' => $transaction->created_at?->toIso8601String(),
            'completed_at' => $transaction->completed_at?->toIso8601String(),
            'description' => $this->walletTransactionDescription($transaction, $type),
            'to' => null,
        ];
    }

    private function walletTransactionDescription(CustomerWalletTransaction $transaction, string $normalizedType): string
    {
        $parts = [$this->walletTransactionTypeLabel($normalizedType)];

        if (filled($transaction->transaction_ref)) {
            $parts[] = 'Ref: ' . $transaction->transaction_ref;
        }

        if (filled($transaction->payment_method)) {
            $parts[] = strtoupper((string) $transaction->payment_method);
        }

        return implode(' • ', $parts);
    }

    private function normalizeWalletTransactionType(string $type): string
    {
        $normalized = strtolower(trim($type));

        return match ($normalized) {
            'topup', 'withdrawal', 'bonus', 'purchase', 'refund', 'tax' => $normalized,
            default => 'other',
        };
    }

    private function walletTransactionDirection(string $normalizedType): string
    {
        return in_array($normalizedType, ['topup', 'bonus', 'refund'], true) ? 'credit' : 'debit';
    }

    private function walletTransactionTypeLabel(string $normalizedType): string
    {
        return match ($normalizedType) {
            'topup' => 'Top Up Saldo',
            'withdrawal' => 'Penarikan Saldo',
            'bonus' => 'Bonus Member',
            'purchase' => 'Pembayaran Belanja',
            'refund' => 'Refund',
            'tax' => 'Potongan Pajak',
            default => 'Transaksi Wallet',
        };
    }

    private function normalizeWalletTransactionStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'completed', 'success', 'succeeded', 'settlement' => 'completed',
            'failed', 'failure', 'deny', 'expired', 'expire' => 'failed',
            'cancelled', 'canceled' => 'cancelled',
            default => 'pending',
        };
    }

    private function walletTransactionStatusLabel(string $normalizedStatus): string
    {
        return match ($normalizedStatus) {
            'completed' => 'Selesai',
            'failed' => 'Gagal',
            'cancelled' => 'Dibatalkan',
            default => 'Menunggu',
        };
    }

    private function normalizeWalletTransactionNotes(?string $notes): ?string
    {
        if (! is_string($notes)) {
            return null;
        }

        $trimmed = trim($notes);

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * @param array{search?:string|null,type?:string|null,status?:string|null} $filters
     * @return array{search:string|null,type:string|null,status:string|null}
     */
    private function normalizeWalletFilters(array $filters): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $type = strtolower(trim((string) ($filters['type'] ?? '')));
        $status = strtolower(trim((string) ($filters['status'] ?? '')));

        if ($type === '' || $type === 'all' || ! in_array($type, $this->walletSupportedTypes(), true)) {
            $type = null;
        }

        if ($status === '' || $status === 'all' || ! in_array($status, $this->walletSupportedStatuses(), true)) {
            $status = null;
        }

        return [
            'search' => $search !== '' ? $search : null,
            'type' => $type,
            'status' => $status,
        ];
    }

    /**
     * @return list<string>
     */
    private function walletSupportedTypes(): array
    {
        return ['topup', 'withdrawal', 'bonus', 'purchase', 'refund', 'tax'];
    }

    /**
     * @return list<string>
     */
    private function walletSupportedStatuses(): array
    {
        return ['pending', 'completed', 'failed', 'cancelled'];
    }

    private function mapMidtransWalletStatus(string $transactionStatus, string $fraudStatus = ''): string
    {
        $normalizedStatus = strtolower(trim($transactionStatus));
        $normalizedFraud = strtolower(trim($fraudStatus));

        return match ($normalizedStatus) {
            'settlement' => 'completed',
            'capture' => $normalizedFraud === 'challenge' ? 'pending' : 'completed',
            'deny', 'failure' => 'failed',
            'expire', 'expired', 'cancel', 'cancelled', 'canceled' => 'cancelled',
            default => 'pending',
        };
    }

    private function generateWalletTransactionRef(string $prefix, int $customerId): string
    {
        $timePart = now()->format('YmdHis');
        $randomPart = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $cleanPrefix = strtoupper(trim($prefix));

        return "{$cleanPrefix}-{$customerId}-{$timePart}-{$randomPart}";
    }

    private function appendWalletNote(?string $existingNotes, string $additionalNote): ?string
    {
        $base = $this->normalizeWalletTransactionNotes($existingNotes);
        $addition = trim($additionalNote);

        if ($addition === '') {
            return $base;
        }

        if ($base === null) {
            return $addition;
        }

        return $base . PHP_EOL . $addition;
    }

    private function buildWithdrawalNotes(Customer $customer, ?string $notes): string
    {
        $bankName = trim((string) ($customer->bank_name ?? '-'));
        $bankAccount = trim((string) ($customer->bank_account ?? '-'));
        $base = "Bank: {$bankName} ({$bankAccount})";
        $extra = $this->normalizeWalletTransactionNotes($notes);

        return $extra !== null ? $base . PHP_EOL . $extra : $base;
    }

    private function memberStatusLabel(int $status): string
    {
        return match ($status) {
            3 => 'Aktif',
            2 => 'Pasif',
            default => 'Prospek',
        };
    }

    /**
     * @param Collection<int, ContentCategory> $categories
     * @return list<array{
     *   id:int,
     *   parent_id:int|null,
     *   name:string,
     *   slug:string,
     *   contents_count:int
     * }>
     */
    private function formatZennerCategories(Collection $categories): array
    {
        return $categories
            ->map(fn (ContentCategory $category): array => [
                'id' => (int) $category->id,
                'parent_id' => $category->parent_id !== null ? (int) $category->parent_id : null,
                'name' => (string) $category->name,
                'slug' => (string) $category->slug,
                'contents_count' => (int) ($category->contents_count ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * @param Collection<int, Content> $contents
     * @return list<array{
     *   id:int,
     *   category_id:int|null,
     *   category_name:string|null,
     *   category_slug:string|null,
     *   title:string,
     *   slug:string,
     *   excerpt:string,
     *   content:string|null,
     *   file:string|null,
     *   vlink:string|null,
     *   status:string|null,
     *   status_label:string,
     *   created_at:string|null,
     *   updated_at:string|null
     * }>
     */
    private function formatZennerContents(Collection $contents): array
    {
        return $contents
            ->map(function (Content $content): array {
                $status = $content->status !== null ? strtolower(trim((string) $content->status)) : null;

                return [
                    'id' => (int) $content->id,
                    'category_id' => $content->category_id !== null ? (int) $content->category_id : null,
                    'category_name' => $content->category?->name,
                    'category_slug' => $content->category?->slug,
                    'title' => (string) ($content->title ?? ''),
                    'slug' => (string) ($content->slug ?? ''),
                    'excerpt' => $this->buildZennerExcerpt($content->content),
                    'content' => $content->content,
                    'file' => $content->file,
                    'vlink' => $content->vlink,
                    'status' => $status,
                    'status_label' => $this->formatZennerStatusLabel($status),
                    'created_at' => $content->created_at?->toIso8601String(),
                    'updated_at' => $content->updated_at?->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }

    private function buildZennerExcerpt(?string $content): string
    {
        if (! is_string($content) || trim($content) === '') {
            return 'Konten belum memiliki deskripsi.';
        }

        $plain = strip_tags($content);
        $plain = preg_replace('/\s+/', ' ', $plain) ?? '';
        $plain = trim($plain);

        if ($plain === '') {
            return 'Konten belum memiliki deskripsi.';
        }

        return mb_strlen($plain) > 180
            ? mb_substr($plain, 0, 177) . '...'
            : $plain;
    }

    private function formatZennerStatusLabel(?string $status): string
    {
        return match ($status) {
            'published', 'publish', 'active' => 'Published',
            'draft' => 'Draft',
            'archived' => 'Archived',
            default => 'Unknown',
        };
    }

    /**
     * @param Collection<int, Promotion> $promotions
     * @return list<array<string, mixed>>
     */
    private function formatPromotions(Collection $promotions, CarbonInterface $asOf): array
    {
        return $promotions
            ->map(function (Promotion $promotion) use ($asOf): array {
                $conditions = is_array($promotion->conditions_json) ? $promotion->conditions_json : [];

                $minSpend = $this->extractNumericCondition($conditions, ['min_spend', 'minimum_spend', 'min_purchase', 'minimum_order']);
                $maxDiscount = $this->extractNumericCondition($conditions, ['max_discount', 'maximum_discount', 'discount_cap']);
                $quotaLeft = $this->extractIntegerCondition($conditions, ['quota_left', 'remaining_quota', 'quota']);
                $terms = $this->extractPromotionTerms($conditions);

                if ($quotaLeft === null && $promotion->max_redemption !== null) {
                    $quotaLeft = (int) $promotion->max_redemption;
                }

                $discountLabel = $this->buildPromotionDiscountLabel($promotion, $conditions);
                $isRunningNow = $promotion->start_at !== null
                    && $promotion->end_at !== null
                    && $promotion->start_at <= $asOf
                    && $promotion->end_at >= $asOf;

                return [
                    'id' => $promotion->id,
                    'title' => (string) ($promotion->name ?? ''),
                    'description' => $promotion->description,
                    'code' => $promotion->code ? strtoupper((string) $promotion->code) : null,
                    'type' => $this->mapPromotionType((string) $promotion->type),
                    'discount_label' => $discountLabel,
                    'min_spend' => $minSpend,
                    'max_discount' => $maxDiscount,
                    'quota_left' => $quotaLeft,
                    'expires_at' => $promotion->end_at?->toIso8601String(),
                    'terms' => $terms,
                    'claimed' => false,
                    'to' => filled($promotion->code) ? route('shop.index', ['promo' => (string) $promotion->code]) : route('shop.index'),
                    'highlight' => $isRunningNow && (int) ($promotion->priority ?? 0) > 0,
                ];
            })
            ->values()
            ->all();
    }

    private function mapPromotionType(string $type): string
    {
        $normalized = strtolower(trim($type));

        return match ($normalized) {
            'flash_sale', 'flash' => 'flash',
            'bundle' => 'bundle',
            'shipping', 'free_shipping' => 'shipping',
            'member' => 'member',
            'voucher' => 'voucher',
            default => 'discount',
        };
    }

    /**
     * @param array<string, mixed> $conditions
     * @param list<string> $keys
     */
    private function extractNumericCondition(array $conditions, array $keys): ?float
    {
        foreach ($keys as $key) {
            $value = data_get($conditions, $key);

            if ($value === null || $value === '') {
                continue;
            }

            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $conditions
     * @param list<string> $keys
     */
    private function extractIntegerCondition(array $conditions, array $keys): ?int
    {
        foreach ($keys as $key) {
            $value = data_get($conditions, $key);

            if ($value === null || $value === '') {
                continue;
            }

            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $conditions
     * @return list<string>|null
     */
    private function extractPromotionTerms(array $conditions): ?array
    {
        $rawTerms = data_get($conditions, 'terms');

        if (is_array($rawTerms)) {
            $terms = collect($rawTerms)
                ->map(fn (mixed $item): string => trim((string) $item))
                ->filter(fn (string $item): bool => $item !== '')
                ->values()
                ->all();

            return $terms !== [] ? $terms : null;
        }

        if (is_string($rawTerms) && trim($rawTerms) !== '') {
            $terms = preg_split('/\r\n|\r|\n|;/', $rawTerms) ?: [];
            $normalized = collect($terms)
                ->map(fn (string $item): string => trim($item))
                ->filter(fn (string $item): bool => $item !== '')
                ->values()
                ->all();

            return $normalized !== [] ? $normalized : null;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $conditions
     */
    private function buildPromotionDiscountLabel(Promotion $promotion, array $conditions): ?string
    {
        $discountPercent = $this->extractNumericCondition($conditions, ['discount_percent', 'percent', 'discount_percentage']);
        $discountValue = $this->extractNumericCondition($conditions, ['discount_value', 'discount_amount', 'nominal_discount', 'value']);
        $bundlePrice = $this->extractNumericCondition($conditions, ['bundle_price']);

        if ($discountPercent !== null && $discountPercent > 0) {
            $formatted = rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.');

            return "Diskon {$formatted}%";
        }

        if ($discountValue !== null && $discountValue > 0) {
            return 'Potongan Rp ' . number_format((int) round($discountValue), 0, ',', '.');
        }

        if ($bundlePrice !== null && $bundlePrice > 0) {
            return 'Harga Bundle Rp ' . number_format((int) round($bundlePrice), 0, ',', '.');
        }

        return match ($this->mapPromotionType((string) $promotion->type)) {
            'bundle' => 'Promo Bundle',
            'flash' => 'Flash Deal',
            'shipping' => 'Gratis Ongkir',
            'voucher' => 'Voucher Promo',
            'member' => 'Khusus Member',
            default => null,
        };
    }

    /**
     * @return array{
     *   tree:array<string,mixed>|null,
     *   stats:array{
     *     left_count:int,
     *     right_count:int,
     *     total_downlines:int
     *   }
     * }
     */
    private function formatBinaryTreeData(Customer $customer, int $maxDepth = 6): array
    {
        $leftCount = (int) ($customer->total_left ?? 0);
        $rightCount = (int) ($customer->total_right ?? 0);
        $totalDownlines = $leftCount + $rightCount;
        $members = $this->dashboardRepository->getBinaryTreeMembers($customer->id, $maxDepth);

        if ($members->isEmpty()) {
            return [
                'tree' => null,
                'stats' => [
                    'left_count' => $leftCount,
                    'right_count' => $rightCount,
                    'total_downlines' => $totalDownlines,
                ],
            ];
        }

        /** @var Collection<int, Customer> $membersById */
        $membersById = $members
            ->filter(fn (mixed $member): bool => $member instanceof Customer)
            ->keyBy(fn (Customer $member): int => (int) $member->id);

        if (! $membersById->has((int) $customer->id)) {
            $membersById->put((int) $customer->id, $customer);
        }

        $visited = [];
        $tree = $this->buildBinaryTreeNode(
            (int) $customer->id,
            1,
            max(1, $maxDepth),
            $membersById,
            $visited,
            true,
        );

        return [
            'tree' => $tree,
            'stats' => [
                'left_count' => $leftCount,
                'right_count' => $rightCount,
                'total_downlines' => $totalDownlines,
            ],
        ];
    }

    /**
     * @param Collection<int, Customer> $membersById
     * @param array<int, bool> $visited
     * @return array<string,mixed>|null
     */
    private function buildBinaryTreeNode(
        int $memberId,
        int $level,
        int $maxDepth,
        Collection $membersById,
        array &$visited,
        bool $isRoot = false,
    ): ?array {
        if ($level > $maxDepth || isset($visited[$memberId])) {
            return null;
        }

        $member = $membersById->get($memberId);

        if (! $member instanceof Customer) {
            return null;
        }

        $visited[$memberId] = true;

        $leftChildId = $member->foot_left !== null ? (int) $member->foot_left : null;
        $rightChildId = $member->foot_right !== null ? (int) $member->foot_right : null;

        $leftNode = $leftChildId !== null
            ? $this->buildBinaryTreeNode(
                $leftChildId,
                $level + 1,
                $maxDepth,
                $membersById,
                $visited,
            )
            : null;

        $rightNode = $rightChildId !== null
            ? $this->buildBinaryTreeNode(
                $rightChildId,
                $level + 1,
                $maxDepth,
                $membersById,
                $visited,
            )
            : null;

        $position = $isRoot ? null : $this->normalizeBinaryTreePosition($member->position);

        return [
            'id' => (int) $member->id,
            'member_id' => (int) $member->id,
            'name' => (string) ($member->name ?? ''),
            'username' => (string) ($member->username ?? ''),
            'email' => $member->email,
            'phone' => $member->phone,
            'package_name' => $member->package?->name,
            'total_left' => (int) ($member->total_left ?? 0),
            'total_right' => (int) ($member->total_right ?? 0),
            'position' => $position,
            'level' => $level,
            'status' => (int) ($member->status ?? 0) === 3,
            'joined_at' => $member->created_at?->toIso8601String(),
            'has_children' => $leftChildId !== null || $rightChildId !== null,
            'left' => $leftNode,
            'right' => $rightNode,
        ];
    }

    private function normalizeBinaryTreePosition(?string $position): ?string
    {
        $normalizedPosition = strtolower(trim((string) $position));

        if (! in_array($normalizedPosition, ['left', 'right'], true)) {
            return null;
        }

        return $normalizedPosition;
    }

    /**
     * @param Collection<int, Customer> $members
     * @return array{active:list<array<string,mixed>>,passive:list<array<string,mixed>>,prospect:list<array<string,mixed>>}
     */
    private function formatMitraMembers(Collection $members): array
    {
        $activeMembers = [];
        $passiveMembers = [];
        $prospectMembers = [];

        foreach ($members as $member) {
            $memberStatus = (int) ($member->status ?? 1);
            $hasPurchase = ((int) ($member->orders_count ?? 0)) > 0;

            $data = [
                'id'            => $member->id,
                'username'      => (string) ($member->username ?? ''),
                'name'          => (string) ($member->name ?? ''),
                'email'         => (string) ($member->email ?? ''),
                'phone'         => $member->phone,
                'package_name'  => $member->package?->name,
                'total_left'    => (int) ($member->total_left ?? 0),
                'total_right'   => (int) ($member->total_right ?? 0),
                'position'      => $member->position,
                'level'         => $member->level,
                'has_placement' => filled($member->position) || $member->upline_id !== null,
                'has_purchase'  => $hasPurchase,
                'omzet'         => (float) ($member->omzet ?? 0),
                'joined_at'     => $member->created_at?->toIso8601String(),
                'status'        => $memberStatus,
                'status_label'  => $this->memberStatusLabel($memberStatus),
            ];

            if ($memberStatus === 3) {
                $activeMembers[] = $data;

                continue;
            }

            if ($memberStatus === 2) {
                $passiveMembers[] = $data;

                continue;
            }

            if ($memberStatus === 1) {
                $prospectMembers[] = $data;
            }
        }

        return [
            'active' => $activeMembers,
            'passive' => $passiveMembers,
            'prospect' => $prospectMembers,
        ];
    }

    /**
     * @return array{
     *   data:list<array<string,mixed>>,
     *   current_page:int,
     *   next_page:int|null,
     *   has_more:bool,
     *   per_page:int,
     *   total:int
     * }
     */
    private function formatOrders(LengthAwarePaginator $paginator): array
    {
        $orders = collect($paginator->items())
            ->filter(fn (mixed $item): bool => $item instanceof Order)
            ->map(fn (Order $order): array => $this->formatOrder($order))
            ->values()
            ->all();

        return [
            'data' => $orders,
            'current_page' => $paginator->currentPage(),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
            'has_more' => $paginator->hasMorePages(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatOrder(Order $order): array
    {
        $latestPayment = $order->payments
            ->sortByDesc(fn ($payment): int => $payment->created_at?->getTimestamp() ?? 0)
            ->first();

        $latestShipment = $order->shipments
            ->sortByDesc(fn ($shipment): int => $shipment->created_at?->getTimestamp() ?? 0)
            ->first();

        $items = $order->items
            ->map(function (OrderItem $item): array {
                $productImage = $item->product?->primaryMedia->sortBy('sort_order')->first()?->url
                    ?? $item->product?->media->sortBy('sort_order')->first()?->url;

                return [
                    'id' => $item->id,
                    'name' => (string) ($item->name ?? ''),
                    'sku' => $item->sku,
                    'variant' => data_get($item->meta_json, 'variant'),
                    'qty' => (int) ($item->qty ?? 0),
                    'price' => (float) ($item->unit_price ?? 0),
                    'row_total' => (float) ($item->row_total ?? 0),
                    'image' => $this->resolveProductImageUrl($productImage),
                ];
            })
            ->values()
            ->all();

        return [
            'id' => $order->id,
            'code' => (string) ($order->order_no ?? $order->id),
            'created_at' => $order->placed_at?->toIso8601String() ?? $order->created_at?->toIso8601String() ?? '-',
            'status' => $this->normalizeOrderStatus((string) ($order->status ?? 'pending')),
            'payment_status' => $this->normalizePaymentStatus((string) ($latestPayment?->status ?? '')),
            'payment_method' => $latestPayment?->method?->name ?? $latestPayment?->method?->code,
            'payment_method_code' => $latestPayment?->method?->code,
            'shipping_method' => filled($latestShipment?->courier_id) ? strtoupper((string) $latestShipment?->courier_id) : null,
            'subtotal' => (float) ($order->subtotal_amount ?? 0),
            'discount_amount' => (float) ($order->discount_amount ?? 0),
            'shipping_cost' => (float) ($order->shipping_amount ?? 0),
            'tax_amount' => (float) ($order->tax_amount ?? 0),
            'total' => (float) ($order->grand_total ?? 0),
            'items_count' => (int) ($order->items_count ?? $order->items->count()),
            'items' => $items,
            'items_preview' => collect($items)->take(3)->values()->all(),
            'tracking_number' => $latestShipment?->tracking_no,
            'notes' => $order->notes,
            'paid_at' => $order->paid_at?->toIso8601String(),
            'shipping_address' => $order->shippingAddress ? [
                'recipient_name' => $order->shippingAddress->recipient_name,
                'recipient_phone' => $order->shippingAddress->recipient_phone,
                'address_line1' => $order->shippingAddress->address_line1,
                'address_line2' => $order->shippingAddress->address_line2,
                'district' => $order->shippingAddress->district,
                'city' => $order->shippingAddress->city_label,
                'province' => $order->shippingAddress->province_label,
                'postal_code' => $order->shippingAddress->postal_code,
                'country' => $order->shippingAddress->country,
            ] : null,
            'customer' => [
                'name' => (string) ($order->customer?->name ?? '-'),
                'email' => $order->customer?->email,
            ],
            'to' => null,
        ];
    }

    private function resolveProductImageUrl(?string $url): ?string
    {
        if (! filled($url)) {
            return null;
        }

        $path = trim((string) $url);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return asset(ltrim($path, '/'));
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return Storage::url($path);
    }

    private function mapMidtransPaymentStatus(string $transactionStatus, string $fraudStatus = ''): string
    {
        $normalizedStatus = strtolower(trim($transactionStatus));
        $normalizedFraud = strtolower(trim($fraudStatus));

        return match ($normalizedStatus) {
            'settlement' => 'paid',
            'capture' => $normalizedFraud === 'challenge' ? 'pending' : 'paid',
            'refund', 'partial_refund' => 'refunded',
            'deny', 'expire', 'cancel', 'cancelled', 'canceled', 'failure' => 'failed',
            default => 'pending',
        };
    }

    private function extractMidtransRedirectUrl(array $gatewayPayload): ?string
    {
        $redirectUrl = trim((string) ($gatewayPayload['redirect_url'] ?? ''));

        if ($redirectUrl !== '') {
            return $redirectUrl;
        }

        $actions = $gatewayPayload['actions'] ?? null;

        if (! is_array($actions)) {
            return null;
        }

        foreach ($actions as $action) {
            $url = trim((string) data_get($action, 'url'));

            if ($url !== '') {
                return $url;
            }
        }

        return null;
    }

    private function normalizeOrderStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'paid' => 'paid',
            'processing', 'processed' => 'processing',
            'shipped', 'ready_to_ship' => 'shipped',
            'delivered' => 'delivered',
            'cancelled', 'canceled' => 'cancelled',
            'refunded' => 'refunded',
            default => 'pending',
        };
    }

    private function normalizePaymentStatus(string $status): ?string
    {
        $normalized = strtolower(trim($status));

        if ($normalized === '') {
            return null;
        }

        return match ($normalized) {
            'paid', 'settlement', 'capture' => 'paid',
            'refunded', 'refund' => 'refunded',
            'failed', 'deny', 'expire', 'cancel', 'cancelled', 'canceled' => 'failed',
            default => 'unpaid',
        };
    }
}
