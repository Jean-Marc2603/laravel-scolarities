<?php

namespace App\Http\Controllers;

use App\Contracts\CvAnalyzer;
use App\Models\CvDocument;
use App\Services\CvTextExtractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class CvController extends Controller
{
    public function index(Request $request): View
    {
        return view('cv.index', [
            'cv' => $request->user()->cvDocument,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'cv' => ['required', 'file', 'mimes:pdf,docx', 'max:10240'],
        ], [
            'cv.required' => 'Choisissez un fichier PDF ou DOCX.',
            'cv.mimes' => 'Le CV doit être au format PDF ou DOCX.',
            'cv.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
        ]);

        $user = $request->user();
        $previous = $user->cvDocument;
        $file = $request->file('cv');
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid().'.'.$extension;
        $path = $file->storeAs('cvs/'.$user->id, $filename, 'local');

        if (! $path) {
            return back()->withErrors(['cv' => 'Le fichier n’a pas pu être enregistré. Réessayez.']);
        }

        $cv = CvDocument::updateOrCreate(
            ['user_id' => $user->id],
            [
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size_bytes' => $file->getSize(),
                'analysis_results' => null,
                'last_analyzed_at' => null,
            ]
        );

        if ($previous && $previous->file_path !== $path) {
            Storage::disk('local')->delete($previous->file_path);
        }

        return redirect()->route('cv.index')->with('status', 'Votre CV a été enregistré. Vous pouvez maintenant lancer son analyse.');
    }

    public function download(Request $request)
    {
        $cv = $request->user()->cvDocument;
        abort_unless($cv && Storage::disk('local')->exists($cv->file_path), 404);

        return Storage::disk('local')->download($cv->file_path, $cv->original_name);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $cv = $request->user()->cvDocument;
        if ($cv) {
            Storage::disk('local')->delete($cv->file_path);
            $cv->delete();
        }

        return redirect()->route('cv.index')->with('status', 'Votre CV a été supprimé.');
    }

    public function analyze(Request $request, CvTextExtractor $extractor, CvAnalyzer $analyzer): RedirectResponse
    {
        $cv = $request->user()->cvDocument;
        if (! $cv || ! Storage::disk('local')->exists($cv->file_path)) {
            return redirect()->route('cv.index')->withErrors(['analysis' => 'Déposez un CV avant de lancer son analyse.']);
        }

        try {
            $text = $extractor->extract(Storage::disk('local')->path($cv->file_path), pathinfo($cv->file_path, PATHINFO_EXTENSION));
            $results = $analyzer->analyze($text);
        } catch (RuntimeException $exception) {
            return redirect()->route('cv.index')->withErrors(['analysis' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('cv.index')->withErrors(['analysis' => 'L’analyse n’a pas pu lire ce fichier. Essayez de déposer un PDF texte ou un DOCX non protégé.']);
        }

        $cv->forceFill([
            'analysis_results' => $results,
            'last_analyzed_at' => now(),
        ])->save();

        return redirect()->route('cv.index')->with('status', 'Analyse de votre CV terminée.');
    }
}
