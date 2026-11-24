<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Demo\StaticDemoDataset;
use Illuminate\View\View;

/**
 * Renders the read-only Pro demonstration views using purely static
 * in-memory data from StaticDemoDataset (zero database or API calls).
 */
class DemoPreviewController extends Controller
{
    public function index(): View
    {
        return view('demo.dashboard-pro', [
            'user' => StaticDemoDataset::user(),
            'credits' => StaticDemoDataset::credits(),
            'metrics' => StaticDemoDataset::metrics(),
            'analysisTypeCounts' => StaticDemoDataset::analysisTypeCounts(),
            'aiModels' => StaticDemoDataset::aiModels(),
            'documents' => StaticDemoDataset::documents(),
        ]);
    }

    public function show(string $slug): View
    {
        $document = StaticDemoDataset::findDocument($slug);
        if ($document === null) {
            abort(404);
        }

        return view('demo.analysis-detail', [
            'document' => $document,
        ]);
    }

    public function showClauses(string $slug): View
    {
        $document = StaticDemoDataset::findDocument($slug);
        if ($document === null) {
            abort(404);
        }

        return view('demo.analysis-clauses', [
            'document' => $document,
        ]);
    }
}
