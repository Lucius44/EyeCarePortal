<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class AdminServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('created_at', 'desc')->get();
        return view('admin.services', compact('services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string|max:500',
        ]);

        Service::create($request->only('name', 'description'));

        return back()->with('success', 'Service added successfully.');
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:services,name,' . $service->id,
            'description' => 'nullable|string|max:500',
        ]);

        $service->update($request->only('name', 'description'));

        return back()->with('success', 'Service updated successfully.');
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return back()->with('success', 'Service removed successfully.');
    }
}