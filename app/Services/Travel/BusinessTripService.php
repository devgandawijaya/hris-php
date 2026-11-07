<?php

namespace App\Services\Travel;

use App\Models\BusinessTripAdvance;
use App\Models\BusinessTripExpense;
use App\Models\BusinessTripOrder;
use App\Models\BusinessTripSettlement;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BusinessTripService
{
    /**
     * Generate an official Indonesian corporate SPPD sequence number.
     * Format: SPPD/{YEAR}/{MONTH_ROMAN}/{SEQUENCE}
     */
    public function generateSppdNumber(Company $company): string
    {
        $year = date('Y');
        $month = (int) date('n');
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $roman = $romanMonths[$month] ?? 'I';

        $countThisMonth = BusinessTripOrder::where('company_id', $company->id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;

        $sequence = str_pad((string) $countThisMonth, 3, '0', STR_PAD_LEFT);

        return "SPPD/{$year}/{$roman}/{$sequence}";
    }

    /**
     * Create a new SPPD order.
     */
    public function createTripOrder(Employee $employee, array $data): BusinessTripOrder
    {
        $departure = Carbon::parse($data['departure_date']);
        $return = Carbon::parse($data['return_date']);
        $days = max(1, $departure->diffInDays($return) + 1);

        $perDiem = isset($data['per_diem_daily_rate']) ? (float) $data['per_diem_daily_rate'] : 250000.00;
        $budget = isset($data['estimated_budget']) && (float) $data['estimated_budget'] > 0
            ? (float) $data['estimated_budget']
            : ($perDiem * $days) + 1500000.00; // default estimated buffer for transport/hotel

        $order = new BusinessTripOrder();
        $order->tenant_id = $employee->tenant_id;
        $order->company_id = $employee->company_id;
        $order->employee_id = $employee->id;
        $order->sppd_number = $this->generateSppdNumber($employee->company);
        $order->purpose = trim($data['purpose']);
        $order->origin_city = trim($data['origin_city'] ?? 'Jakarta');
        $order->destination_city = trim($data['destination_city']);
        $order->departure_date = $departure->toDateString();
        $order->return_date = $return->toDateString();
        $order->duration_days = $days;
        $order->transport_type = $data['transport_type'] ?? 'flight';
        $order->lodging_needed = $data['lodging_needed'] ?? true;
        $order->per_diem_daily_rate = $perDiem;
        $order->estimated_budget = $budget;
        $order->status = 'submitted';
        $order->notes = $data['notes'] ?? null;
        $order->save();

        // If requested cash advance upfront
        if (!empty($data['advance_requested']) && (float) $data['advance_requested'] > 0) {
            BusinessTripAdvance::create([
                'tenant_id' => $employee->tenant_id,
                'business_trip_order_id' => $order->id,
                'amount_requested' => (float) $data['advance_requested'],
                'amount_approved' => 0.00,
                'status' => 'pending',
            ]);
        }

        return $order;
    }

    /**
     * Approve or reject SPPD by authorized manager.
     */
    public function processApproval(BusinessTripOrder $order, User $approver, bool $approved, ?string $reason = null): BusinessTripOrder
    {
        if ($approved) {
            $order->status = 'approved';
            $order->approved_by = $approver->id;
            $order->approved_at = now();
            $order->rejection_reason = null;

            // Auto-approve advance up to estimated budget if exists
            foreach ($order->advances as $advance) {
                if ($advance->status === 'pending') {
                    $advance->amount_approved = $advance->amount_requested;
                    $advance->status = 'approved';
                    $advance->save();
                }
            }
        } else {
            $order->status = 'rejected';
            $order->approved_by = $approver->id;
            $order->approved_at = now();
            $order->rejection_reason = $reason ?? 'Tidak disetujui atasan.';
        }

        $order->save();
        return $order;
    }

    /**
     * Disburse cash advance by finance.
     */
    public function disburseAdvance(BusinessTripAdvance $advance, User $financeUser, ?string $transferRef = null): BusinessTripAdvance
    {
        $advance->status = 'disbursed';
        $advance->transfer_reference = $transferRef ?? 'TRF-' . strtoupper(bin2hex(random_bytes(4)));
        $advance->disbursed_at = now();
        $advance->disbursed_by = $financeUser->id;
        $advance->save();

        if ($advance->businessTripOrder->status === 'approved') {
            $advance->businessTripOrder->status = 'ongoing';
            $advance->businessTripOrder->save();
        }

        return $advance;
    }

    /**
     * Add verified expense line item.
     */
    public function addExpense(BusinessTripOrder $order, array $data): BusinessTripExpense
    {
        return BusinessTripExpense::create([
            'tenant_id' => $order->tenant_id,
            'business_trip_order_id' => $order->id,
            'expense_category' => $data['expense_category'],
            'amount' => (float) $data['amount'],
            'expense_date' => $data['expense_date'] ?? date('Y-m-d'),
            'description' => $data['description'] ?? null,
            'receipt_path' => $data['receipt_path'] ?? null,
            'is_verified' => $data['is_verified'] ?? true,
        ]);
    }

    /**
     * Calculate and finalize reconciliation settlement.
     */
    public function finalizeSettlement(BusinessTripOrder $order, User $auditor, ?string $notes = null): BusinessTripSettlement
    {
        $totalAdvance = (float) $order->advances()
            ->whereIn('status', ['approved', 'disbursed'])
            ->sum('amount_approved');

        $totalExpenses = (float) $order->expenses()
            ->sum('amount');

        $balance = $totalExpenses - $totalAdvance;

        $settlement = BusinessTripSettlement::updateOrCreate(
            [
                'tenant_id' => $order->tenant_id,
                'business_trip_order_id' => $order->id,
            ],
            [
                'total_advance' => $totalAdvance,
                'total_expenses' => $totalExpenses,
                'balance_amount' => $balance,
                'status' => 'finalized',
                'audited_by' => $auditor->id,
                'audited_at' => now(),
                'settled_at' => now(),
                'notes' => $notes,
            ]
        );

        $order->status = 'completed';
        $order->save();

        return $settlement;
    }
}
