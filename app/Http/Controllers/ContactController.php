<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactCustomFields;
use App\Models\CustomFields;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        // Filter by Phone Number
        $phone = $request->get('phone');
        if ($phone) {
            $query->where(function($q) use ($phone) {
                $q->where('phone_number', 'like', "%{$phone}%")
                  ->orWhere('phone_numbers', 'like', "%{$phone}%");
            });
        }

        // Filter by Gender
        $gender = $request->get('gender');
        if ($gender !== null && $gender !== '') {
            $query->where('gender', $gender);
        }

        // Filter by Custom Fields
        $customFieldFilters = $request->get('custom_fields', []);
        $allContactIds = null; // Initialize to null to handle the first filter correctly

        if (!empty($customFieldFilters) && is_array($customFieldFilters)) {
            foreach ($customFieldFilters as $fieldId => $value) {
                if (!empty($value)) {
                    $field = CustomFields::find($fieldId);
                    $isDateField = $field && $field->field_type === 'date';
                    
                    $contactIds = [];

                    if ($isDateField) {
                        $dateValue = $value;
                        // Convert from dd/mm/yyyy to Y-m-d if needed
                        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                            $parts = explode('/', $value);
                            if (count($parts) === 3) {
                                $dateValue = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                            }
                        }
                        
                        // Ensure date is in Y-m-d format
                        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue)) {
                            try {
                                $carbon = \Carbon\Carbon::parse($dateValue);
                                $dateValue = $carbon->format('Y-m-d');
                            } catch (\Exception $e) {
                                // If parsing fails, skip this filter
                                continue;
                            }
                        }
                        
                        // Try exact match first
                        $contactIds = ContactCustomFields::where('custom_field_id', $fieldId)
                            ->where('value', $dateValue)
                            ->pluck('contact_id')
                            ->toArray();
                        
                        // If no exact match, try LIKE search (for partial dates)
                        if (empty($contactIds)) {
                            $contactIds = ContactCustomFields::where('custom_field_id', $fieldId)
                                ->where('value', 'like', "%{$dateValue}%")
                                ->pluck('contact_id')
                                ->toArray();
                        }
                    } else {
                        // For non-date fields, use LIKE search
                        $contactIds = ContactCustomFields::where('custom_field_id', $fieldId)
                            ->where('value', 'like', "%{$value}%")
                            ->pluck('contact_id')
                            ->toArray();
                    }
                    
                    if (empty($contactIds)) {
                        $query->whereRaw('1 = 0'); // No matches for this filter, so no overall matches
                        break;
                    }
                    
                    // Apply filter - intersect with previous filters (AND logic)
                    if ($allContactIds === null) {
                        $allContactIds = $contactIds;
                    } else {
                        $allContactIds = array_intersect($allContactIds, $contactIds);
                        if (empty($allContactIds)) {
                            $query->whereRaw('1 = 0'); // No matches after intersection
                            break;
                        }
                    }
                }
            }
        }

        if ($allContactIds !== null) {
            $query->whereIn('id', $allContactIds);
        }

        // Legacy search parameter (for backward compatibility)
        $search = $request->get('search');
        if ($search && !$name && !$email && !$phone) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('phone_numbers', 'like', "%{$search}%");
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

        try {
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
        } catch (\Exception $e) {
            // Log the error for debugging but don't expose details to user
            \Log::error('Contact creation failed: ' . $e->getMessage());
            
            // Check if it's a duplicate entry error
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), '23000')) {
                $errorMessage = 'A contact with this email or phone number already exists.';
            } else {
                $errorMessage = 'Something went wrong. Please try again.';
            }
            
            // AJAX request → return JSON
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }

            return redirect()->route('contacts.index')->with('error', $errorMessage);
        }
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

        try {
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
        } catch (\Exception $e) {
            // Log the error for debugging but don't expose details to user
            \Log::error('Contact update failed: ' . $e->getMessage());
            
            // Check if it's a duplicate entry error
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), '23000')) {
                $errorMessage = 'A contact with this email or phone number already exists.';
            } else {
                $errorMessage = 'Something went wrong. Please try again.';
            }
            
            // AJAX request → return JSON
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }

            return redirect()->route('contacts.index')->with('error', $errorMessage);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Contact $contact)
    {
        try {
            // Delete associated custom fields first
            ContactCustomFields::where('contact_id', $contact->id)->delete();
            
            // Delete profile picture if exists
            if ($contact->profile_picture) {
                Storage::disk('public')->delete($contact->profile_picture);
            }
            
            // Delete additional files if exist
            if ($contact->additional_files) {
                $files = is_array($contact->additional_files) 
                    ? $contact->additional_files 
                    : (json_decode($contact->additional_files, true) ?? []);
                
                foreach ($files as $file) {
                    if ($file) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }
            
            // Actually delete the contact record from database
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
        } catch (\Exception $e) {
            // Log the error for debugging but don't expose details to user
            \Log::error('Contact deletion failed: ' . $e->getMessage());
            
            $errorMessage = 'Something went wrong while deleting the contact. Please try again.';
            
            // AJAX request → return JSON
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }

            return redirect()->route('contacts.index')
                ->with('error', $errorMessage);
        }
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
            $errorMessage = 'Cannot merge contacts that are already merged.';
            
            // AJAX request → return JSON
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }
            
            return redirect()->route('contacts.index')
                ->with('error', $errorMessage);
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

            // Merge profile picture - use secondary if master doesn't have one (prevent data loss)
            $mergedProfilePicture = $masterContact->profile_picture;
            if (!$mergedProfilePicture && $contact->profile_picture) {
                $mergedProfilePicture = $contact->profile_picture;
            }

            // Merge additional files
            $masterFiles = is_array($masterContact->additional_files) 
                ? $masterContact->additional_files 
                : (json_decode($masterContact->additional_files, true) ?? []);
            $secondaryFiles = is_array($contact->additional_files) 
                ? $contact->additional_files 
                : (json_decode($contact->additional_files, true) ?? []);

            $mergedFiles = array_unique(array_merge($masterFiles, $secondaryFiles));
            $mergedFiles = array_values(array_filter($mergedFiles));

            // Store merge history before updating master contact
            $mergeHistory = [
                'merged_at' => now()->toDateTimeString(),
                'secondary_contact_id' => $contact->id,
                'secondary_contact_name' => $contact->first_name . ' ' . $contact->last_name,
                'secondary_contact_email' => $contact->email,
                'merged_by' => auth()->id() ?? null,
            ];
            
            // Get existing merge history if any
            $existingHistory = $masterContact->merge_history 
                ? (is_array($masterContact->merge_history) ? $masterContact->merge_history : json_decode($masterContact->merge_history, true) ?? [])
                : [];
            
            // Add new merge to history
            $existingHistory[] = $mergeHistory;

            // Update master contact with merged data
            $masterContact->update([
                'emails' => json_encode($mergedEmails),
                'email' => $mergedEmails[0] ?? $masterContact->email,
                'phone_numbers' => json_encode($mergedPhones),
                'phone_number' => $mergedPhones[0] ?? $masterContact->phone_number,
                'profile_picture' => $mergedProfilePicture ?? $masterContact->profile_picture,
                'additional_files' => !empty($mergedFiles) ? json_encode($mergedFiles) : $masterContact->additional_files,
                'merge_history' => json_encode($existingHistory),
            ]);

            // Merge custom fields with intelligent conflict handling
            $masterCustomFields = ContactCustomFields::where('contact_id', $masterContact->id)
                ->get()
                ->keyBy('custom_field_id');
            
            $secondaryCustomFields = ContactCustomFields::where('contact_id', $contact->id)->get();
            
            // Get field types for intelligent conflict resolution
            $fieldIds = $secondaryCustomFields->pluck('custom_field_id')->unique();
            $fieldTypes = CustomFields::whereIn('id', $fieldIds)
                ->pluck('field_type', 'id')
                ->toArray();

            foreach ($secondaryCustomFields as $secondaryField) {
                $fieldId = $secondaryField->custom_field_id;
                $fieldType = $fieldTypes[$fieldId] ?? 'text';
                
                if (!isset($masterCustomFields[$fieldId])) {
                    // Master doesn't have this field - add it (no conflict, no data loss)
                    if (!empty($secondaryField->value)) {
                        ContactCustomFields::create([
                            'contact_id' => $masterContact->id,
                            'custom_field_id' => $fieldId,
                            'value' => $secondaryField->value,
                        ]);
                    }
                } else {
                    // Conflict: Both have the same field with potentially different values
                    $masterValue = $masterCustomFields[$fieldId]->value;
                    $secondaryValue = $secondaryField->value;
                    
                    // Always merge if secondary value is not empty (even if same, to ensure no data loss)
                    if (!empty($secondaryValue)) {
                        // Handle conflicts based on field type to prevent data loss
                        if (in_array($fieldType, ['text', 'textarea'])) {
                            // For text fields: Append secondary value if different (preserve both values)
                            if (empty($masterValue)) {
                                // Master is empty, use secondary value
                                $masterCustomFields[$fieldId]->update(['value' => $secondaryValue]);
                            } elseif ($masterValue !== $secondaryValue) {
                                // Values are different - check if one contains the other
                                if (strpos($masterValue, $secondaryValue) === false && strpos($secondaryValue, $masterValue) === false) {
                                    // Both values are different and neither contains the other - append both
                                    $mergedValue = trim($masterValue) . ' | ' . trim($secondaryValue);
                                    $masterCustomFields[$fieldId]->update(['value' => $mergedValue]);
                                } else {
                                    // One contains the other - keep the longer/more complete value
                                    $mergedValue = strlen($masterValue) >= strlen($secondaryValue) ? $masterValue : $secondaryValue;
                                    if ($mergedValue !== $masterValue) {
                                        $masterCustomFields[$fieldId]->update(['value' => $mergedValue]);
                                    }
                                }
                            }
                        } elseif ($fieldType === 'number') {
                            // For numbers: Merge intelligently
                            // Check if field name contains "fax" - treat as text-like for merging
                            $isFaxField = false;
                            $fieldModel = CustomFields::find($fieldId);
                            if ($fieldModel && stripos($fieldModel->field_name, 'fax') !== false) {
                                $isFaxField = true;
                            }
                            
                            if ($isFaxField) {
                                // Fax numbers: Append like text fields to preserve both values
                                if (empty($masterValue)) {
                                    $masterCustomFields[$fieldId]->update(['value' => $secondaryValue]);
                                } elseif ($masterValue !== $secondaryValue) {
                                    // Append if different
                                    if (strpos($masterValue, $secondaryValue) === false && strpos($secondaryValue, $masterValue) === false) {
                                        $mergedValue = trim($masterValue) . ' | ' . trim($secondaryValue);
                                        $masterCustomFields[$fieldId]->update(['value' => $mergedValue]);
                                    } else {
                                        // One contains the other - keep the longer value
                                        $mergedValue = strlen($masterValue) >= strlen($secondaryValue) ? $masterValue : $secondaryValue;
                                        if ($mergedValue !== $masterValue) {
                                            $masterCustomFields[$fieldId]->update(['value' => $mergedValue]);
                                        }
                                    }
                                }
                            } else {
                                // Regular numbers: Keep the larger value or use secondary if master is empty
                                if (!empty($masterValue) && is_numeric($masterValue) && is_numeric($secondaryValue)) {
                                    $mergedValue = max((float)$masterValue, (float)$secondaryValue);
                                    if ($mergedValue != $masterValue) {
                                        $masterCustomFields[$fieldId]->update(['value' => (string)$mergedValue]);
                                    }
                                } elseif (empty($masterValue)) {
                                    // Master is empty, use secondary (even if not numeric, to prevent data loss)
                                    $masterCustomFields[$fieldId]->update(['value' => $secondaryValue]);
                                } elseif (!is_numeric($masterValue) && is_numeric($secondaryValue)) {
                                    // Master is not numeric but secondary is - use secondary
                                    $masterCustomFields[$fieldId]->update(['value' => $secondaryValue]);
                                } elseif (is_numeric($masterValue) && !is_numeric($secondaryValue) && !empty($secondaryValue)) {
                                    // Master is numeric but secondary is not - append as text
                                    $mergedValue = trim($masterValue) . ' | ' . trim($secondaryValue);
                                    $masterCustomFields[$fieldId]->update(['value' => $mergedValue]);
                                }
                            }
                        } elseif ($fieldType === 'date') {
                            // For dates: Keep the more recent date (newer information)
                            try {
                                if (!empty($masterValue)) {
                                    $masterDate = \Carbon\Carbon::parse($masterValue);
                                    $secondaryDate = \Carbon\Carbon::parse($secondaryValue);
                                    if ($secondaryDate->gt($masterDate)) {
                                        // Secondary date is more recent, update master
                                        $masterCustomFields[$fieldId]->update(['value' => $secondaryValue]);
                                    }
                                } else {
                                    // Master is empty, use secondary
                                    $masterCustomFields[$fieldId]->update(['value' => $secondaryValue]);
                                }
                            } catch (\Exception $e) {
                                // If date parsing fails, use secondary if master is empty
                                if (empty($masterValue)) {
                                    $masterCustomFields[$fieldId]->update(['value' => $secondaryValue]);
                                }
                            }
                        } elseif ($fieldType === 'checkbox') {
                            // For checkboxes: If secondary is checked, ensure master is checked
                            // (OR logic - if either is true, result is true)
                            if ($secondaryValue && !$masterValue) {
                                $masterCustomFields[$fieldId]->update(['value' => $secondaryValue]);
                            }
                        } elseif ($fieldType === 'select') {
                            // For select: Keep master's value if not empty, otherwise use secondary
                            if (empty($masterValue) && !empty($secondaryValue)) {
                                $masterCustomFields[$fieldId]->update(['value' => $secondaryValue]);
                            }
                        } else {
                            // For other types: Keep master's value if not empty, otherwise use secondary
                            if (empty($masterValue) && !empty($secondaryValue)) {
                                $masterCustomFields[$fieldId]->update(['value' => $secondaryValue]);
                            }
                        }
                    }
                }
            }

            // Mark secondary contact as merged
            $contact->update([
                'is_merged' => true,
                'merged_into_contact_id' => $masterContact->id,
            ]);

            \DB::commit();

            // AJAX request → return JSON
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Contacts merged successfully. The secondary contact has been marked as merged.'
                ]);
            }

            return redirect()->route('contacts.index')
                ->with('success', 'Contacts merged successfully. The secondary contact has been marked as merged.');

        } catch (\Exception $e) {
            \DB::rollBack();
            
            // Log the error for debugging but don't expose details to user
            \Log::error('Contact merge failed: ' . $e->getMessage());
            
            $errorMessage = 'Something went wrong while merging contacts. Please try again.';
            
            // AJAX request → return JSON
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }
            
            return redirect()->route('contacts.index')
                ->with('error', $errorMessage);
        }
    }
}
