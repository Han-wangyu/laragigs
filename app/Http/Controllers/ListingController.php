<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    // Show all listings
    public function index() {
//        dd(\request()->tag);
        return view('listings.index', [
            'listings' => Listing::query()->latest()
                ->filter(request(['tag', 'search']))->paginate(6)
//                ->filter(request(['tag', 'search']))->get()
        ]);
    }

    // Show single listing
    public function show(Listing $listing) {
        return view('listings.show', [
            'listing' => $listing
        ]);
    }

    // Show create form
    public function create() {
        return view('listings.create');
    }

    // Store Listing Data
    public function store(Request $request) {
//         dd($request->file('logo'));

        $formFields = $request->validate([
           'title' => 'required',
           'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => ['required', 'url'],
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);



        if ($request->hasFile('logo')) {
            // dd($request->file('logo'));
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $formFields['user_id'] = auth()->id();
        Listing::query()->create($formFields);

        return redirect('/')
            ->with('message', 'Listing created successfully');
    }

    // Show Edit Form
    public function edit(Listing $listing) {
        // Make sure login user is owner
        if ($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action');
        }

        // dd($listing);
        return view('listings.edit', ['listing' => $listing]);
    }

    // Update Listing Data
    public function update(Request $request, Listing $listing) {
        // Make sure login user is owner
        if ($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action');
        }

        // dd($request->file('logo'));

        $formFields = $request->validate([
            'title' => 'required',
            'company' => 'required',
            'location' => 'required',
            'website' => ['required', 'url'],
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $formFields['user_id'] = auth()->id();

        // Listing::factory()->create($formFields);
        $listing->update($formFields);

        return back()
            ->with('message', 'Listing updated successfully');
    }

    // Delete listing
    public function destroy(Listing $listing) {
        // Make sure login user is owner
        if ($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action');
        }

        $listing->delete();
        return redirect('/')
            ->with('message', 'Listing deleted successfully');
    }

    // Manage Listings
    public function manage() {
        return view('listings.manage', ['listings' => auth()->user()->listings()->get()]);
    }
}
