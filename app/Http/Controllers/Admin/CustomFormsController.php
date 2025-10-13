<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomFormsController extends Controller
{
    //
	public function createView($formId)
{
    // ✅ Find the form by ID
    $form = FormName::findOrFail($formId);

    // ✅ Get all fields related to this form from pivot table
    $fieldsPivot = FormHasField::where('form_name_id', $form->id)
                    ->with('field') // relation to FormField
                    ->orderBy('step', 'asc')
                    ->get();

    // ✅ Group fields by step
    $fields = $fieldsPivot->groupBy('step');

    // ✅ Pass form and fields to blade
    return view('admin.forms.create', compact('form', 'fields'));
}
}
