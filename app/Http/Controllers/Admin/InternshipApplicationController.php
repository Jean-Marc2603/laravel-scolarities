<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternshipApplication;
use App\Services\InternshipOfferCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternshipApplicationController extends Controller
{
    public function index(Request $request, InternshipOfferCatalog $catalog): View
    {
        $search = trim((string) $request->query('q', ''));

        $applications = InternshipApplication::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('applied_at')
            ->paginate(15)
            ->withQueryString();

        foreach ($applications as $application) {
            $application->offer = $catalog->find($application->offer_id);
        }

        return view('admin.applications.index', compact('applications', 'search'));
    }

    public function updateStatus(Request $request, InternshipApplication $application): RedirectResponse
    {
        $status = $request->validate([
            'status' => ['required', 'string', 'in:pending,accepted,rejected'],
        ])['status'];

        $application->update(['status' => $status]);

        return redirect()->route('admin.applications.index')
            ->with('status', 'Le statut de la candidature a été mis à jour.');
    }
}
