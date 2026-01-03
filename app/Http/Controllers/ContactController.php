<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactCustomFields;
use App\Models\CustomFields;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Contact::query()->where('is_merged', false);

        // Filter by Name (first_name or last_name)
        $name = $request->get('name');
        if ($name) {
            $query->where(function($q) use ($name) {
                $q->where('first_name', 'like', "%{$name}%")
                  ->orWhere('last_name', 'like', "%{$name}%");
            });
        }

        // Filter by Email
        $email = $request->get('email');
        if ($email) {
            $query->where(function($q) use ($email) {
                $q->where('email', 'like', "%{$email}%")
                  ->orWhere('emails', 'like', "%{$email}%");
            });
        }

        // Filter by Gender
        $gender = $request->get('gender');
        if ($gender !== null && $gender !== '') {
            $query->where('gender', $gender);
        }

        // Filter by Custom Fields
        $customFieldFilters = $request->get('custom_fields', []);
        if (!empty($customFieldFilters) && is_array($customFieldFilters)) {
            foreach ($customFieldFilters as $fieldId => $value) {
                if (!empty($value)) {
                    $contactIds = ContactCustomFields::where('custom_field_id', $fieldId)
                        ->where('value', 'like', "%{$value}%")
                        ->pluck('contact_id')
                        ->toArray();
                    
                    if (!empty($contactIds)) {
                        $query->whereIn('id', $contactIds);
                    } else {
                        // If no matches found, return empty result
                        $query->whereRaw('1 = 0');
                        break;
                    }
                }
            }
        }

        // Legacy search parameter (for backward compatibility)
        $search = $request->get('search');
        if ($search && !$name && !$email) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $contacts = $query->orderBy('id', 'desc')->get();
        $customFields = CustomFields::orderBy('id', 'desc')->get();

        // AJAX request → return only rows
        if ($request->ajax()) {
            return view('contact.partials.rows', compact('contacts'));
        }

        return view('contact.index', compact('contacts', 'customFields'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {        // Validate input
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'emails' => 'required|array',
            'emails.*' => 'required|email|max:255',
            'phone_numbers' => 'nullable|array',
            'phone_numbers.*' => 'nullable|string|max:10',
            'gender' => 'nullable|integer',
            'profile_picture' => 'nullable|image|max:2048',
            'additional_files' => 'nullable|array',
            'additional_files.*' => 'nullable|file|max:5120',
        ]);

        // Handle validation errors for AJAX requests
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }
        }

        $validator->validate();
        $data = $validator->validated();

        // Process emails array - set first email as primary
        $emails = array_filter($data['emails'], fn ($e) => ! empty($e));
        $data['emails'] = ! empty($emails) ? json_encode(array_values($emails)) : json_encode([]);
        $data['email'] = array_values($emails)[0] ?? null; // Primary email

        // Process phone numbers array - set first phone as primary
        $phoneNumbers = array_filter($data['phone_numbers'] ?? [], fn ($p) => ! empty($p));
        $data['phone_numbers'] = ! empty($phoneNumbers) ? json_encode(array_values($phoneNumbers)) : json_encode([]);
        $data['phone_number'] = array_values($phoneNumbers)[0] ?? null; // Primary phone

        // Handle profile picture
        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        // Handle additional files
        $files = [];
        if ($request->hasFile('additional_files')) {
            foreach ($request->file('additional_files') as $file) {
                if ($file && $file->isValid()) {
                    $files[] = $file->store('additional_files', 'public');
                }
            }
        }
        if (! empty($files)) {
            $data['additional_files'] = json_encode($files);
        }

        // Create contact
        $contact = Contact::create($data);

        // Handle custom fields
        if ($request->has('custom_fields') && is_array($request->custom_fields)) {
            foreach ($request->custom_fields as $fieldId => $value) {
                if (! empty($value)) {
                    // Get field type to check if it's a date field
                    $field = CustomFields::find($fieldId);
                    $processedValue = $value;
                    
                    // Convert dd/mm/yyyy to Y-m-d for date fields
                    if ($field && $field->field_type === 'date') {
                        $processedValue = $this->convertDateFormat($value);
                    }
                    
                    if ($processedValue) {
                        ContactCustomFields::create([
                            'contact_id' => $contact->id,
                            'custom_field_id' => $fieldId,
                            'value' => $processedValue,
                        ]);
                    }
                }
            }
        }

        // AJAX request → return JSON
        if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $contact->load('customFieldValues');
            return response()->json([
                'success' => true,
                'message' => 'Contact created successfully.',
                'contact' => $contact
            ]);
        }

        return redirect()->route('contacts.index')->with('success', 'Contact created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        return view('contact.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
        return view('contact.edit', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'emails' => 'required|array',
            'emails.*' => 'required|email|max:255',
            'phone_numbers' => 'nullable|array',
            'phone_numbers.*' => 'nullable|string|max:20',
            'gender' => 'nullable|integer',
            'profile_picture' => 'nullable|image|max:2048',
            'additional_files' => 'nullable|array',
            'additional_files.*' => 'nullable|file|max:5120',
        ]);

        // Handle validation errors for AJAX requests
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }
        }

        $validator->validate();
        $data = $validator->validated();

        // Process emails array - set first email as primary
        $emails = array_filter($data['emails'], fn ($e) => ! empty($e));
        $data['emails'] = ! empty($emails) ? json_encode(array_values($emails)) : json_encode([]);
        $data['email'] = array_values($emails)[0] ?? null; // Primary email

        // Process phone numbers array - set first phone as primary
        $phoneNumbers = array_filter($data['phone_numbers'] ?? [], fn ($p) => ! empty($p));
        $data['phone_numbers'] = ! empty($phoneNumbers) ? json_encode(array_values($phoneNumbers)) : json_encode([]);
        $data['phone_number'] = array_values($phoneNumbers)[0] ?? null; // Primary phone

        // Handle profile picture
        if ($request->hasFile('profile_picture')) {
            if ($contact->profile_picture) {
                Storage::disk('public')->delete($contact->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        // Handle additional files - append to existing
        $existingFiles = is_array($contact->additional_files) ? $contact->additional_files : (json_decode($contact->additional_files, true) ?? []);
        if ($request->hasFile('additional_files')) {
            foreach ($request->file('additional_files') as $file) {
                if ($file && $file->isValid()) {
                    $existingFiles[] = $file->store('additional_files', 'public');
                }
            }
        }
        if (! empty($existingFiles)) {
            $data['additional_files'] = json_encode($existingFiles);
        }

        // Update contact
        $contact->update($data);

        // Update custom fields
        if ($request->has('custom_fields') && is_array($request->custom_fields)) {
            // Delete existing custom field values
            ContactCustomFields::where('contact_id', $contact->id)->delete();

            // Create new ones
            foreach ($request->custom_fields as $fieldId => $value) {
                if (! empty($value)) {
                    // Get field type to check if it's a date field
                    $field = CustomFields::find($fieldId);
                    $processedValue = $value;
                    
                    // Convert dd/mm/yyyy to Y-m-d for date fields
                    if ($field && $field->field_type === 'date') {
                        $processedValue = $this->convertDateFormat($value);
                    }
                    
                    if ($processedValue) {
                        ContactCustomFields::create([
                            'contact_id' => $contact->id,
                            'custom_field_id' => $fieldId,
                            'value' => $processedValue,
                        ]);
                    }
                }
            }
        }

        // AJAX request → return JSON
        if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $contact->load('customFieldValues');
            return response()->json([
                'success' => true,
                'message' => 'Contact updated successfully.',
                'contact' => $contact
            ]);
        }

        return redirect()->route('contacts.index')->with('success', 'Contact updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Contact $contact)
    {
        $contact->delete();

        // AJAX request → return JSON
        if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Contact deleted successfully.'
            ]);
        }

        return redirect()->route('contacts.index')
            ->with('success', 'Contact deleted.');
    }

    /**
     * Get contact with custom field values for edit modal (API)
     */
    public function getCustomFields(Contact $contact)
    {
        $customFields = ContactCustomFields::where('contact_id', $contact->id)->get();
        $result = [];
        
        foreach ($customFields as $customField) {
            $field = CustomFields::find($customField->custom_field_id);
            $value = $customField->value;
            
            // Convert Y-m-d to dd/mm/yyyy for date fields
            if ($field && $field->field_type === 'date' && !empty($value)) {
                try {
                    $carbon = Carbon::createFromFormat('Y-m-d', $value);
                    $value = $carbon->format('d/m/Y');
                } catch (\Exception $e) {
                    // If parsing fails, try other formats
                    try {
                        $carbon = Carbon::parse($value);
                        $value = $carbon->format('d/m/Y');
                    } catch (\Exception $e2) {
                        // Keep original value if all parsing fails
                    }
                }
            }
            
            // Return both value and field info for view modal
            // Always return object format with field info when available
            $result[$customField->custom_field_id] = [
                'value' => $value,
                'field_name' => $field ? $field->field_name : 'Custom Field ' . $customField->custom_field_id,
                'field_type' => $field ? $field->field_type : ''
            ];
        }

        return response()->json($result);
    }

    /**
     * Convert date from dd/mm/yyyy to Y-m-d format
     *
     * @param string $date
     * @return string
     */
    private function convertDateFormat($date)
    {
        if (empty($date)) {
            return $date;
        }

        // Check if already in Y-m-d format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // Try to parse dd/mm/yyyy format
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];
            
            // Validate date
            if (checkdate((int)$month, (int)$day, (int)$year)) {
                return sprintf('%s-%s-%s', $year, $month, $day);
            }
        }

        // If format is not recognized, try Carbon parsing
        try {
            $carbon = Carbon::createFromFormat('d/m/Y', $date);
            return $carbon->format('Y-m-d');
        } catch (\Exception $e) {
            // If parsing fails, return original value
            return $date;
        }
    }

    /**
     * Show merge modal - return contact data for merge initiation
     */
    public function showMergeModal(Contact $contact)
    {
        // Prevent merging already merged contacts
        if ($contact->is_merged) {
            return response()->json([
                'error' => 'This contact has already been merged and cannot be merged again.'
            ], 400);
        }

        return response()->json([
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'emails' => is_array($contact->emails) ? $contact->emails : json_decode($contact->emails, true) ?? [],
            'phone_number' => $contact->phone_number,
            'phone_numbers' => is_array($contact->phone_numbers) ? $contact->phone_numbers : json_decode($contact->phone_numbers, true) ?? [],
        ]);
    }

    /**
     * Get list of contacts for merge selection (excluding current contact and merged contacts)
     */
    public function getContactsForMerge(Request $request)
    {
        $excludeId = $request->get('exclude_id');
        
        $contacts = Contact::where('is_merged', false)
            ->when($excludeId, function($query) use ($excludeId) {
                $query->where('id', '!=', $excludeId);
            })
            ->select('id', 'first_name', 'last_name', 'email', 'phone_number')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return response()->json($contacts);
    }

    /**
     * Merge two contacts
     */
    public function merge(Request $request, Contact $contact)
    {
        $validator = Validator::make($request->all(), [
            'master_contact_id' => 'required|exists:contacts,id|different:' . $contact->id,
        ]);

        $validator->validate();

        $masterContact = Contact::findOrFail($request->master_contact_id);

        // Prevent merging already merged contacts
        if ($contact->is_merged || $masterContact->is_merged) {
            return redirect()->route('contacts.index')
                ->with('error', 'Cannot merge contacts that are already merged.');
        }

        // Start transaction
        \DB::beginTransaction();
        try {
            // Merge emails - combine unique emails
            $masterEmails = is_array($masterContact->emails) 
                ? $masterContact->emails 
                : (json_decode($masterContact->emails, true) ?? []);
            $secondaryEmails = is_array($contact->emails) 
                ? $contact->emails 
                : (json_decode($contact->emails, true) ?? []);

            // Add primary emails if not in arrays
            if ($masterContact->email && !in_array($masterContact->email, $masterEmails)) {
                $masterEmails[] = $masterContact->email;
            }
            if ($contact->email && !in_array($contact->email, $secondaryEmails)) {
                $secondaryEmails[] = $contact->email;
            }

            // Merge unique emails
            $mergedEmails = array_unique(array_merge($masterEmails, $secondaryEmails));
            $mergedEmails = array_values(array_filter($mergedEmails));

            // Merge phone numbers - combine unique phone numbers
            $masterPhones = is_array($masterContact->phone_numbers) 
                ? $masterContact->phone_numbers 
                : (json_decode($masterContact->phone_numbers, true) ?? []);
            $secondaryPhones = is_array($contact->phone_numbers) 
                ? $contact->phone_numbers 
                : (json_decode($contact->phone_numbers, true) ?? []);

            // Add primary phones if not in arrays
            if ($masterContact->phone_number && !in_array($masterContact->phone_number, $masterPhones)) {
                $masterPhones[] = $masterContact->phone_number;
            }
            if ($contact->phone_number && !in_array($contact->phone_number, $secondaryPhones)) {
                $secondaryPhones[] = $contact->phone_number;
            }

            // Merge unique phone numbers
            $mergedPhones = array_unique(array_merge($masterPhones, $secondaryPhones));
            $mergedPhones = array_values(array_filter($mergedPhones));

            // Merge additional files
            $masterFiles = is_array($masterContact->additional_files) 
                ? $masterContact->additional_files 
                : (json_decode($masterContact->additional_files, true) ?? []);
            $secondaryFiles = is_array($contact->additional_files) 
                ? $contact->additional_files 
                : (json_decode($contact->additional_files, true) ?? []);

            $mergedFiles = array_unique(array_merge($masterFiles, $secondaryFiles));
            $mergedFiles = array_values(array_filter($mergedFiles));

            // Update master contact with merged data
            $masterContact->update([
                'emails' => json_encode($mergedEmails),
                'email' => $mergedEmails[0] ?? $masterContact->email,
                'phone_numbers' => json_encode($mergedPhones),
                'phone_number' => $mergedPhones[0] ?? $masterContact->phone_number,
                'additional_files' => !empty($mergedFiles) ? json_encode($mergedFiles) : $masterContact->additional_files,
            ]);

            // Merge custom fields
            $masterCustomFields = ContactCustomFields::where('contact_id', $masterContact->id)
                ->pluck('value', 'custom_field_id')
                ->toArray();

            $secondaryCustomFields = ContactCustomFields::where('contact_id', $contact->id)->get();

            foreach ($secondaryCustomFields as $secondaryField) {
                // If master doesn't have this custom field, add it
                if (!isset($masterCustomFields[$secondaryField->custom_field_id])) {
                    ContactCustomFields::create([
                        'contact_id' => $masterContact->id,
                        'custom_field_id' => $secondaryField->custom_field_id,
                        'value' => $secondaryField->value,
                    ]);
                }
                // If both have the same field but different values, keep master's value
                // (as per requirement: "keep the master's value or append both values")
                // We're keeping master's value here. If you want to append, you could modify this logic.
            }

            // Mark secondary contact as merged
            $contact->update([
                'is_merged' => true,
                'merged_into_contact_id' => $masterContact->id,
            ]);

            \DB::commit();

            return redirect()->route('contacts.index')
                ->with('success', 'Contacts merged successfully. The secondary contact has been marked as merged.');

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->route('contacts.index')
                ->with('error', 'An error occurred while merging contacts: ' . $e->getMessage());
        }
    }
}
