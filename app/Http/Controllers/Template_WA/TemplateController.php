<?php

namespace App\Http\Controllers\Template_WA;

use App\Http\Controllers\Controller;
use App\Models\WaTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TemplateController extends Controller
{
    public function index()
    {
        $template = WaTemplate::latest()->get();
        return Inertia::render('Jadwal/Template/index', [
            'templates' => $template
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_template' => 'required|string|max:255',
            'slug' => 'required|string|unique:wa_templates,slug',
            'pesan' => 'required|string',
        ]);

        WaTemplate::create($validated);

        return redirect()->back()->with('success', 'Template berhasil disimpan.');
    }

    public function destroy($id)
    {
        $template = WaTemplate::findOrFail($id);
        $template->delete();

        return redirect()->back()->with('success', 'Template berhasil dihapus.');
    }
}
