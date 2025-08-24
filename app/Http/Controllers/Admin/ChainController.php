<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chain;
use Illuminate\Http\Request;

class ChainController extends Controller
{
    /**
     * Display a listing of the chains.
     */
    public function index()
    {
        $chains = Chain::all();
        return view('admin.pages.chain.index', compact('chains'));
    }

    /**
     * Store a newly created chain in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        Chain::create([
            'type' => $request->type,
            'address' => $request->address,
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Chain created successfully!');
    }

    /**
     * Update the specified chain in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        $chain = Chain::findOrFail($id);

        $chain->update([
            'type' => $request->type,
            'address' => $request->address,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Chain updated successfully!');
    }

    /**
     * Remove the specified chain from storage.
     */
    public function destroy($id)
    {
        $chain = Chain::findOrFail($id);
        $chain->delete();

        return redirect()->back()->with('success', 'Chain deleted successfully!');
    }
}
