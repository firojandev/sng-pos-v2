<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Employee\Services\EmployeeImport;
use Modules\Employee\Services\EmployeeLogin;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Bulk employee upload from an Excel sheet, optionally with a login for
 * every employee that has an email.
 */
class EmployeeImportController extends Controller
{
    public function template(EmployeeImport $import): StreamedResponse
    {
        $spreadsheet = $import->template();

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            'employee-import-format.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    public function store(Request $request, EmployeeImport $import, EmployeeLogin $login): RedirectResponse
    {
        $createLogins = $request->boolean('create_user');

        abort_if($createLogins && ! $login->allowed(), 403, 'ইউজার তৈরির অনুমতি আপনার নেই (You are not allowed to create users)।');

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:5120', 'extensions:xlsx,xls,csv'],
            'create_user' => ['nullable', 'boolean'],
            'user_role' => ['nullable', Rule::requiredIf($createLogins && $login->roleRequired()), Rule::in(array_keys($login->roleOptions()))],
        ], [
            'file.required' => 'কর্মচারীর এক্সেল ফাইলটি নির্বাচন করুন (Choose the employee Excel file)।',
            'file.extensions' => 'শুধু .xlsx, .xls বা .csv ফাইল আপলোড করা যায় (Only .xlsx, .xls or .csv files)।',
            'user_role.required' => 'ইউজার রোল নির্বাচন করুন (User role is required)।',
        ]);

        $result = $import->import($request->file('file'), $createLogins, isset($validated['user_role']) ? (int) $validated['user_role'] : null);

        if ($result['errors'] !== []) {
            return back()->with('import_errors', $result['errors']);
        }

        $status = "{$result['created']} জন কর্মচারী যোগ হয়েছে ({$result['created']} employees imported)";

        if ($createLogins) {
            $status .= "; {$result['logins']}টি লগইন তৈরি ও পাসওয়ার্ড সেটের ইমেইল পাঠানো হয়েছে ({$result['logins']} logins created and invited)";
        }

        return back()->with('status', $status)->with('import_report', $result);
    }
}
