<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Jobs\ProcessProductImport;
use App\Jobs\ProcessProductImportExcel;
use App\Models\Category;
use App\Models\ImportError;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->resolveDateRange($request);
        $categoryId = $this->resolveCategoryId($request);

        $query = $this->filteredQuery($from, $to, $categoryId);

        return view('reports.index', [
            'totalPenjualan' => $query->sum('total'),
            'jumlahTransaksi' => $query->count(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'categoryId' => $categoryId,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        $categoryId = $this->resolveCategoryId($request);

        $query = $this->filteredQuery($from, $to, $categoryId);

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['ID', 'Tanggal', 'Kasir', 'Total']);

            foreach ($query->lazyById() as $t) {
                fputcsv($out, [
                    $t->id,
                    $t->created_at,
                    $t->user->name,
                    $t->total,
                ]);
            }

            fclose($out);
        }, 'laporan-penjualan.csv');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        $categoryId = $this->resolveCategoryId($request);

        return Excel::download(
            new SalesReportExport($this->filteredQuery($from, $to, $categoryId)),
            'laporan-penjualan.xlsx',
        );
    }

    public function exportPdf(Request $request): Response
    {
        [$from, $to] = $this->resolveDateRange($request);
        $categoryId = $this->resolveCategoryId($request);

        $query = $this->filteredQuery($from, $to, $categoryId);

        $pdf = Pdf::loadView('reports.pdf', [
            'totalPenjualan' => (clone $query)->sum('total'),
            'jumlahTransaksi' => (clone $query)->count(),
            'transactions' => $query->latest()->limit(50)->get(),
            'topProducts' => $this->topProductsQuery($from, $to, $categoryId)->get(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'categoryName' => $categoryId === null
                ? null
                : Category::find($categoryId)?->name,
        ]);

        return $pdf->download('laporan-penjualan.pdf');
    }

    public function importForm(): View
    {
        return view('reports.import', [
            'importErrors' => ImportError::latest()->paginate(20),
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
            'mode' => ['nullable', 'in:manual,excel'],
        ]);

        $file = $request->file('csv');
        $path = $file->store('imports');
        $mode = $validated['mode'] ?? 'manual';

        $job = $mode === 'excel'
            ? ProcessProductImportExcel::class
            : ProcessProductImport::class;

        $job::dispatch(
            $path,
            $request->user()?->id,
            $file->getClientOriginalName(),
        );

        return back()->with(
            'success',
            'Import sedang diproses di background.'
        );
    }

    private function topProductsQuery($from, $to, ?int $categoryId = null): Builder
    {
        $query = TransactionDetail::query();

        $query->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_details.product_id')
            ->whereBetween('transactions.created_at', [$from, $to])
            ->when($categoryId !== null, function (Builder $query) use ($categoryId) {
                $query->where('products.category_id', $categoryId);
            })
            ->selectRaw('products.name as name')
            ->selectRaw('SUM(transaction_details.qty) as total_qty')
            ->selectRaw('SUM(transaction_details.subtotal) as total_sales')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->orderByDesc('total_sales')
            ->limit(10);

        return $query;
    }

    private function resolveDateRange(Request $request): array
    {
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();

        return [$from, $to];
    }

    private function resolveCategoryId(Request $request): ?int
    {
        $categoryId = $request->input('category_id', $request->input('category'));

        if ($categoryId === null || $categoryId === '' || ! is_scalar($categoryId)) {
            return null;
        }

        return (int) $categoryId;
    }

    private function filteredQuery($from, $to, ?int $categoryId = null): Builder
    {
        return Transaction::with('user')
            ->whereBetween('created_at', [$from, $to])
            ->when($categoryId !== null, function (Builder $query) use ($categoryId) {
                $query->whereHas('details.product', function (Builder $query) use ($categoryId) {
                    $query->where('products.category_id', $categoryId);
                });
            });
    }
}
