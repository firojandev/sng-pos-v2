<?php

namespace Modules\Employee\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Core\Support\BanglaNumber;
use Modules\Core\Support\TenantContext;
use Modules\Employee\Models\Employee;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Throwable;

/**
 * Bulk employee import from the Excel format given by template(). The file
 * is checked as a whole first: one bad row and nothing is imported, so a
 * corrected file can be uploaded again without duplicates.
 */
class EmployeeImport
{
    public const MAX_ROWS = 500;

    /**
     * [field, heading, required, hint, example]
     *
     * @var list<array{0: string, 1: string, 2: bool, 3: string, 4: string}>
     */
    private const COLUMNS = [
        ['employee_code', 'Employee Code', false, 'Leave blank to get the next free code (EMP-0001, ...). Must be unique.', ''],
        ['name', 'Name', true, 'Full name of the employee.', 'Rahim Uddin'],
        ['phone', 'Phone', true, 'Mobile number, unique for each employee. Keep the leading 0.', '01711000000'],
        ['email', 'Email', false, 'Needed to create a login: the set-password link is sent here. Unique.', 'rahim@example.com'],
        ['designation', 'Designation', true, 'Job title. A new designation is added to the list automatically.', 'Sales Executive'],
        ['department', 'Department', false, 'A new department is added to the list automatically.', 'Sales'],
        ['salary', 'Monthly Salary', true, 'Monthly salary in Taka, numbers only.', '18000'],
        ['joining_date', 'Joining Date', false, 'YYYY-MM-DD (or DD/MM/YYYY).', '2026-01-15'],
        ['address', 'Address', false, 'Present address.', 'Mirpur, Dhaka'],
        ['status', 'Status', false, 'active or inactive. Blank means active.', 'active'],
    ];

    public function __construct(private EmployeeLogin $login, private TenantContext $tenant) {}

    /**
     * The upload format: the "Employees" sheet to fill in, and an
     * "Instructions" sheet explaining each column with an example.
     */
    public function template(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Employees');

        foreach (self::COLUMNS as $index => [, $heading, $required]) {
            $column = chr(65 + $index);
            $sheet->setCellValue($column.'1', $heading.($required ? ' *' : ''));
            $sheet->getColumnDimension($column)->setWidth(22);
            // Text columns: Excel must not drop a leading zero or reformat a date.
            $sheet->getStyle($column.'2:'.$column.(self::MAX_ROWS + 1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        }

        $lastColumn = chr(64 + count(self::COLUMNS));
        $sheet->getStyle('A1:'.$lastColumn.'1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:'.$lastColumn.'1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F766E');
        $sheet->freezePane('A2');

        $guide = $spreadsheet->createSheet()->setTitle('Instructions');
        $guide->fromArray([
            ['Employee import format'],
            ['Fill in the "Employees" sheet, one employee per row, and upload the file. Columns marked * are required. Do not rename or reorder the headings. At most '.self::MAX_ROWS.' rows per file.'],
            ['If any row has a mistake nothing is imported: correct the rows listed and upload the file again.'],
            [],
            ['Column', 'Required', 'What to enter', 'Example'],
        ]);

        foreach (self::COLUMNS as $index => [, $heading, $required, $hint, $example]) {
            $row = $index + 6;
            $guide->fromArray([$heading, $required ? 'Yes' : 'No', $hint], null, 'A'.$row);
            $guide->setCellValueExplicit('D'.$row, $example, DataType::TYPE_STRING);
        }

        $guide->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $guide->getStyle('A5:D5')->getFont()->setBold(true);
        $guide->getColumnDimension('A')->setWidth(20);
        $guide->getColumnDimension('B')->setWidth(10);
        $guide->getColumnDimension('C')->setWidth(75);
        $guide->getColumnDimension('D')->setWidth(24);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * @return array{created: int, logins: int, emails_failed: int, without_login: list<array{row: int, name: string, reason: string}>, errors: list<string>}
     */
    public function import(UploadedFile $file, bool $createLogins = false, ?int $roleId = null): array
    {
        $result = ['created' => 0, 'logins' => 0, 'emails_failed' => 0, 'without_login' => [], 'errors' => []];

        try {
            $rows = $this->read($file);
        } catch (Throwable) {
            $result['errors'][] = 'ফাইলটি পড়া যায়নি; ডাউনলোড করা ফরম্যাটটি ব্যবহার করুন (The file could not be read; use the downloaded format)।';

            return $result;
        }

        if (is_string($rows)) {
            $result['errors'][] = $rows;

            return $result;
        }

        $result['errors'] = $this->errors($rows);

        if ($result['errors'] !== []) {
            return $result;
        }

        $invited = [];

        DB::transaction(function () use ($rows, $createLogins, $roleId, &$result, &$invited) {
            foreach ($rows as $line => $row) {
                $employee = Employee::create([...$row, 'shop_id' => $this->tenant->shopId()]);
                $result['created']++;

                if (! $createLogins) {
                    continue;
                }

                $reason = $employee->email
                    ? $this->login->blocker($employee->phone, $employee->email)
                    : 'ইমেইল নেই, তাই পাসওয়ার্ড সেটের লিংক পাঠানো যায়নি (No email to send the set-password link to)।';

                if ($reason) {
                    $result['without_login'][] = ['row' => $line, 'name' => $employee->name, 'reason' => $reason];

                    continue;
                }

                $invited[] = $this->login->create($employee, $roleId);
                $result['logins']++;
            }
        });

        // Sent once the logins are saved; queued so a large file doesn't time out.
        foreach ($invited as $user) {
            if (! $this->login->invite($user, queued: true)) {
                $result['emails_failed']++;
            }
        }

        return $result;
    }

    /**
     * The filled rows keyed by their line in the sheet, or a message when
     * the file isn't in the format.
     *
     * @return array<int, array<string, mixed>>|string
     */
    private function read(UploadedFile $file): array|string
    {
        $sheet = IOFactory::load($file->getRealPath())->getSheet(0)->toArray(null, true, false, false);
        $fields = $this->fields(array_shift($sheet) ?? []);

        foreach (self::COLUMNS as [$field, $heading, $required]) {
            if ($required && ! in_array($field, $fields, true)) {
                return "\"{$heading}\" কলামটি ফাইলে নেই; ডাউনলোড করা ফরম্যাটটি ব্যবহার করুন (The \"{$heading}\" column is missing; use the downloaded format)।";
            }
        }

        $rows = [];

        foreach ($sheet as $index => $cells) {
            $row = [];

            foreach ($fields as $column => $field) {
                if ($field !== null) {
                    $row[$field] = $this->value($field, $cells[$column] ?? null);
                }
            }

            if (array_filter($row, fn ($value) => $value !== null) !== []) {
                $row['status'] ??= 'active';
                $rows[$index + 2] = $row;
            }
        }

        if ($rows === []) {
            return 'ফাইলে কোনো কর্মচারী পাওয়া যায়নি (No employees found in the file)।';
        }

        if (count($rows) > self::MAX_ROWS) {
            return 'এক ফাইলে সর্বোচ্চ '.self::MAX_ROWS.' জন কর্মচারী আপলোড করা যায় (At most '.self::MAX_ROWS.' employees per file)।';
        }

        return $rows;
    }

    /**
     * The field of each column, from its heading ("Monthly Salary *" => salary).
     *
     * @param  array<int, mixed>  $headings
     * @return array<int, string|null>
     */
    private function fields(array $headings): array
    {
        $known = [];

        foreach (self::COLUMNS as [$field, $heading]) {
            $known[$this->key($heading)] = $field;
            $known[$this->key($field)] = $field;
        }

        return array_map(fn ($heading) => $known[$this->key((string) $heading)] ?? null, $headings);
    }

    private function key(string $heading): string
    {
        return preg_replace('/[^a-z]/', '', strtolower($heading));
    }

    private function value(string $field, mixed $cell): mixed
    {
        $text = trim((string) $cell);

        if ($text === '') {
            return null;
        }

        return match ($field) {
            'phone' => $this->phone($text),
            'salary' => str_replace([',', '৳', ' '], '', BanglaNumber::toEn($text)),
            'joining_date' => $this->date($cell),
            'status' => strtolower($text),
            'email' => strtolower($text),
            default => $text,
        };
    }

    /**
     * Excel drops the leading zero of a number typed into a general cell.
     */
    private function phone(string $text): string
    {
        $phone = preg_replace('/[\s\-()]/', '', BanglaNumber::toEn($text));

        return preg_match('/^1[3-9]\d{8}$/', $phone) ? '0'.$phone : $phone;
    }

    /**
     * A real Excel date, YYYY-MM-DD, or DD/MM/YYYY as written in Bangladesh;
     * anything else is left for validation to reject.
     */
    private function date(mixed $cell): string
    {
        $text = trim((string) $cell);

        try {
            if (is_numeric($cell)) {
                return Date::excelToDateTimeObject((float) $cell)->format('Y-m-d');
            }

            if (preg_match('#^(\d{1,2})[/.](\d{1,2})[/.](\d{4})$#', $text, $parts)) {
                return Carbon::createFromFormat('!d/m/Y', "{$parts[1]}/{$parts[2]}/{$parts[3]}")->format('Y-m-d');
            }

            return Carbon::parse($text)->format('Y-m-d');
        } catch (Throwable) {
            return $text;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return list<string>
     */
    private function errors(array $rows): array
    {
        $companyId = $this->tenant->companyId();
        $errors = [];
        $seen = ['phone' => [], 'email' => [], 'employee_code' => []];

        foreach ($rows as $line => $row) {
            $validator = Validator::make($row, [
                'employee_code' => ['nullable', 'string', 'max:30', Rule::unique('employees', 'employee_code')->where('company_id', $companyId)],
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:20', 'unique:employees,phone'],
                'email' => ['nullable', 'email', 'max:255', 'unique:employees,email'],
                'designation' => ['required', 'string', 'max:255'],
                'department' => ['nullable', 'string', 'max:255'],
                'salary' => ['required', 'numeric', 'min:0'],
                'joining_date' => ['nullable', 'date'],
                'address' => ['nullable', 'string', 'max:255'],
                'status' => ['required', 'in:active,inactive'],
            ], [], [
                'employee_code' => 'Employee Code', 'name' => 'Name', 'phone' => 'Phone', 'email' => 'Email', 'designation' => 'Designation',
                'department' => 'Department', 'salary' => 'Monthly Salary', 'joining_date' => 'Joining Date', 'address' => 'Address', 'status' => 'Status',
            ]);

            $messages = $validator->errors()->all();

            foreach ($seen as $field => $values) {
                $value = strtolower((string) ($row[$field] ?? ''));

                if ($value === '') {
                    continue;
                }

                if (isset($values[$value])) {
                    $messages[] = "{$field}: সারি {$values[$value]}-এর সাথে একই (same as row {$values[$value]})";
                }

                $seen[$field][$value] ??= $line;
            }

            if ($messages !== []) {
                $errors[] = "সারি (Row) {$line}: ".implode(' ', $messages);
            }
        }

        return $errors;
    }
}
