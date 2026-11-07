<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\TaxForm1721A1Record;
use App\Services\Tax\Ebupot21ExportService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TaxEbupotController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected Ebupot21ExportService $ebupotService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->firstOrFail();
        $taxYear = (int) $request->input('year', date('Y'));

        $records = TaxForm1721A1Record::where('company_id', $company->id)
            ->where('tax_year', $taxYear)
            ->with(['employee.department', 'employee.position'])
            ->paginate(15);

        $totalEmployees = Employee::where('company_id', $company->id)->count();
        $generatedCount = TaxForm1721A1Record::where('company_id', $company->id)->where('tax_year', $taxYear)->count();

        $stats = [
            'total_gross' => TaxForm1721A1Record::where('company_id', $company->id)->where('tax_year', $taxYear)->sum('penghasilan_bruto'),
            'total_pph21' => TaxForm1721A1Record::where('company_id', $company->id)->where('tax_year', $taxYear)->sum('pph21_terutang_setahun'),
            'generated_count' => $generatedCount,
            'coverage_pct' => $totalEmployees > 0 ? round(($generatedCount / $totalEmployees) * 100, 1) : 0.0,
        ];

        return view('company.taxes.ebupot', compact('records', 'taxYear', 'stats', 'company'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->firstOrFail();
        $taxYear = (int) $request->input('year', date('Y'));

        $employees = Employee::where('company_id', $company->id)->get();
        $count = 0;

        foreach ($employees as $employee) {
            $this->ebupotService->generateForm1721A1($employee, $taxYear);
            $count++;
        }

        return redirect()->route('company.taxes.ebupot.index', ['year' => $taxYear])
            ->with('success', "Berhasil menyusun {$count} data Formulir 1721-A1 Tahun Pajak {$taxYear}.");
    }

    public function showForm(TaxForm1721A1Record $record): View
    {
        $record->load(['company', 'employee.department', 'employee.position']);
        return view('company.taxes.form1721a1', compact('record'));
    }

    public function exportCsv(Request $request): Response
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->firstOrFail();
        $taxYear = (int) $request->input('year', date('Y'));

        $csv = $this->ebupotService->generateEbupotCsv($company, $taxYear);
        $filename = "eBupot2126_1721A1_{$company->id}_{$taxYear}_" . date('Ymd_His') . ".csv";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
