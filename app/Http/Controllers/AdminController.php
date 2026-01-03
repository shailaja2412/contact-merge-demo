<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function index()
    {
        $customFieldsTypes = ['text', 'number', 'date', 'select', 'checkbox'];

        // debug example: dd($customFieldsTypes);
        return view('admin.index', compact('customFieldsTypes'));
    }
}
