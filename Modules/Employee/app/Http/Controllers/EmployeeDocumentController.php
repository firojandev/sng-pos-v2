<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\EmployeeDocument;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Files on an employee's record, stored privately per company.
 */
class EmployeeDocumentController extends Controller
{
    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(EmployeeDocument::TYPES))],
            'expires_on' => ['nullable', 'date'],
            'document' => ['required', 'file', 'max:5120', 'extensions:pdf,jpg,jpeg,png,webp,doc,docx'],
        ]);

        $file = $request->file('document');
        $path = $file->store("employee-documents/{$employee->company_id}/{$employee->id}", EmployeeDocument::DISK);

        $employee->documents()->create([
            'company_id' => $employee->company_id,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'expires_on' => $validated['expires_on'] ?? null,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return back()->with('status', 'ডকুমেন্ট যোগ করা হয়েছে');
    }

    public function download(Employee $employee, EmployeeDocument $document): StreamedResponse
    {
        abort_unless((int) $document->employee_id === (int) $employee->id, 404);

        return Storage::disk(EmployeeDocument::DISK)->download($document->file_path, $document->original_name);
    }

    public function destroy(Employee $employee, EmployeeDocument $document): RedirectResponse
    {
        abort_unless((int) $document->employee_id === (int) $employee->id, 404);
        $document->delete();

        return back()->with('status', 'ডকুমেন্ট মুছে ফেলা হয়েছে');
    }
}
