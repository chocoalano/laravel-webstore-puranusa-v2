<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerAddress\SaveCustomerAddressRequest;
use App\Models\Customer;
use App\Services\CustomerAddress\CustomerAddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerAddressController extends Controller
{
    public function __construct(
        private readonly CustomerAddressService $customerAddressService
    ) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('dashboard', ['section' => 'addresses']);
    }

    public function store(SaveCustomerAddressRequest $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->customerAddressService->create($customer, $request->payload());

        return back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(SaveCustomerAddressRequest $request, int $addressId): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->customerAddressService->update($customer, $addressId, $request->payload());

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    public function setDefault(Request $request, int $addressId): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->customerAddressService->setDefault($customer, $addressId);

        return back()->with('success', 'Alamat default berhasil diperbarui.');
    }

    public function destroy(Request $request, int $addressId): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $this->customerAddressService->delete($customer, $addressId);

        return back()->with('success', 'Alamat berhasil dihapus.');
    }
}
