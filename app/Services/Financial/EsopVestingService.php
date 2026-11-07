<?php

namespace App\Services\Financial;

use App\Models\EsopGrant;
use App\Models\Employee;
use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Support\Str;

class EsopVestingService
{
    /**
     * Create a new ESOP / RSU Grant for an employee.
     */
    public function issueGrant(
        Employee $employee,
        int $totalShares,
        float $strikePrice,
        float $fairMarketValue,
        Carbon|string $grantDate,
        int $vestingYears = 4,
        int $cliffMonths = 12
    ): EsopGrant {
        if ($totalShares <= 0) {
            throw new InvalidArgumentException('Total shares granted must be greater than zero.');
        }

        $grantNumber = 'ESOP-' . date('Y') . '-' . strtoupper(Str::random(6));

        return EsopGrant::create([
            'tenant_id' => $employee->tenant_id,
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'grant_number' => $grantNumber,
            'grant_date' => Carbon::parse($grantDate)->toDateString(),
            'total_shares_granted' => $totalShares,
            'vesting_schedule_years' => $vestingYears,
            'cliff_months' => $cliffMonths,
            'strike_price_per_share' => $strikePrice,
            'vested_shares_count' => 0,
            'exercised_shares_count' => 0,
            'fair_market_value' => $fairMarketValue,
            'status' => 'active',
        ]);
    }

    /**
     * Recompute vested shares based on elapsed months since grant date.
     * Standard Indonesian startup/corporate policy: 4 years (48 months) with 12 months cliff.
     */
    public function updateVestingProgress(EsopGrant $grant, Carbon|string|null $asOfDate = null): EsopGrant
    {
        $asOf = $asOfDate ? Carbon::parse($asOfDate)->startOfDay() : Carbon::today();
        $grantDate = Carbon::parse($grant->grant_date)->startOfDay();

        if ($asOf->lessThan($grantDate)) {
            $grant->vested_shares_count = 0;
            $grant->save();
            return $grant;
        }

        $elapsedMonths = $grantDate->diffInMonths($asOf);
        $totalMonths = $grant->vesting_schedule_years * 12;

        if ($elapsedMonths < $grant->cliff_months) {
            // Before cliff: 0 vested
            $vestedShares = 0;
        } elseif ($elapsedMonths >= $totalMonths) {
            // Reached or exceeded full vesting schedule
            $vestedShares = $grant->total_shares_granted;
        } else {
            // Linear monthly vesting after cliff
            $ratio = $elapsedMonths / $totalMonths;
            $vestedShares = (int) floor($grant->total_shares_granted * $ratio);
        }

        $grant->vested_shares_count = $vestedShares;

        if ($grant->vested_shares_count >= $grant->total_shares_granted && $grant->status !== 'exercised') {
            $grant->status = 'fully_vested';
        }

        $grant->save();

        return $grant;
    }

    /**
     * Exercise vested options.
     */
    public function exerciseOptions(EsopGrant $grant, int $sharesToExercise): array
    {
        $availableToExercise = $grant->vested_shares_count - $grant->exercised_shares_count;

        if ($sharesToExercise <= 0) {
            throw new InvalidArgumentException('Shares to exercise must be greater than zero.');
        }

        if ($sharesToExercise > $availableToExercise) {
            throw new InvalidArgumentException(
                sprintf('Cannot exercise %d shares. Only %d vested shares are currently available.',
                    $sharesToExercise,
                    $availableToExercise
                )
            );
        }

        $grant->exercised_shares_count += $sharesToExercise;

        if ($grant->exercised_shares_count >= $grant->total_shares_granted) {
            $grant->status = 'exercised';
        }

        $grant->save();

        $totalExerciseCost = round($sharesToExercise * (float) $grant->strike_price_per_share, 2);
        $totalFairValue = round($sharesToExercise * (float) $grant->fair_market_value, 2);
        $intrinsicGain = round($totalFairValue - $totalExerciseCost, 2);

        return [
            'grant_id' => $grant->id,
            'shares_exercised' => $sharesToExercise,
            'strike_price_per_share' => (float) $grant->strike_price_per_share,
            'total_exercise_cost' => $totalExerciseCost,
            'fair_market_value_per_share' => (float) $grant->fair_market_value,
            'total_fair_value' => $totalFairValue,
            'intrinsic_gain' => $intrinsicGain,
            'remaining_exercisable_shares' => $grant->vested_shares_count - $grant->exercised_shares_count,
            'grant_status' => $grant->status,
        ];
    }
}
