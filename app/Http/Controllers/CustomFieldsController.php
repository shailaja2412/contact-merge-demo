<?php

namespace App\Http\Controllers;

use App\Models\ContactCustomFields;
use App\Models\CustomFields;
use Illuminate\Http\Request;

class CustomFieldsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        if ($search) {
            $customFields = CustomFields::where('field_name', 'like', "%{$search}%")
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $customFields = CustomFields::orderBy('id', 'desc')->get();
        }

        $customFieldsTypes = ['text', 'number', 'date', 'checkbox', 'textarea', 'select'];

        // AJAX request → return only rows
        if ($request->ajax()) {
            return view('admin.custom_fields.partials.rows', compact('customFields'));
        }

        return view('admin.custom_fields.index', compact('customFields', 'customFieldsTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'field_name' => 'required|string|max:255',
            'field_type' => 'required|string|in:text,number,date,select,checkbox,textarea',
        ]);

        $field = CustomFields::create($data);

        return redirect()->route('admin.index')->with('success', 'Custom field created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, CustomFields $customFields) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomFields $customFields)
    {
        return view('admin.custom_fields.edit', compact('customFields'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomFields $customFields, $id)
    {

        $data = $request->validate([
            'field_name' => 'required|string|max:255',
            'field_type' => 'required|string|in:text,number,date,select,checkbox,textarea',
        ]);

        $exists = ContactCustomFields::where('custom_field_id', $id)->exists();

        if ($exists) {
            return redirect()->route('admin.index')
                ->with('error', 'Cannot Update custom field as it is associated with contacts.');
        }

        $customFields->where('id', $id)->update($data);

        return redirect()->route('admin.index')->with('success', 'Custom field updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $exists = ContactCustomFields::where('custom_field_id', $id)->exists();

        if ($exists) {
            return redirect()->route('admin.index')
                ->with('error', 'Cannot delete custom field as it is associated with contacts.');
        }

        CustomFields::destroy($id);

        return redirect()->route('admin.index')
            ->with('success', 'Custom field deleted.');
    }
}
