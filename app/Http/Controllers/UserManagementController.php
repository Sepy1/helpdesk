<?php

namespace App\Http\Controllers;

use App\Models\KodeKantor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UserManagementController extends Controller
{
    private const ROLES = ['IT', 'CABANG', 'VENDOR', 'ADMIN'];

    private function ensureIT()
    {
        $u = Auth::user();
        if (! $u) abort(401);
        if ($u->role !== 'IT') abort(403);
    }

    private function filteredUsers(Request $request)
    {
        $q = trim($request->get('q', ''));
        $role = $request->get('role');
        $kodeKantor = trim((string) $request->get('kode_kantor', ''));

        return User::query()
            ->with('kodeKantor')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%")
                        ->orWhere('username', 'like', "%$q%");
                });
            })
            ->when($role, fn ($query) => $query->where('role', $role))
            ->when($kodeKantor !== '', fn ($query) => $query->where('kode_kantor', $kodeKantor));
    }

    public function index(Request $request)
    {
        $this->ensureIT();
        $q = trim($request->get('q', ''));
        $role = $request->get('role');
        $kodeKantor = trim((string) $request->get('kode_kantor', ''));
        $perPage = (int) $request->get('per_page', 25);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $users = $this->filteredUsers($request)
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
        $kodeKantors = KodeKantor::orderBy('kode')->get();
        $roleCounts = User::query()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');
        $totalUsers = User::count();

        return view('it.users.index', compact('users', 'q', 'role', 'kodeKantor', 'kodeKantors', 'roleCounts', 'totalUsers', 'perPage'));
    }

    public function create()
    {
        $this->ensureIT();
        $kodeKantors = KodeKantor::orderBy('kode')->get();

        return view('it.users.create', compact('kodeKantors'));
    }

    public function store(Request $request)
    {
        $this->ensureIT();
        $request->merge([
            'kode_kantor' => $request->filled('kode_kantor') ? $request->input('kode_kantor') : null,
        ]);
        $data = $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,username',
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:IT,CABANG,VENDOR,ADMIN',
            'kode_kantor' => 'nullable|string|size:3|exists:kode_kantor,kode',
        ]);
        $user = User::create([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'kode_kantor' => $data['kode_kantor'] ?? null,
            'email_notifications_enabled' => true,
            'android_notifications_enabled' => true,
        ]);
        return redirect()->route('it.users.index')->with('success', 'User dibuat.');
    }

    public function edit(User $user)
    {
        $this->ensureIT();
        $kodeKantors = KodeKantor::orderBy('kode')->get();

        return view('it.users.edit', compact('user', 'kodeKantors'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureIT();
        $request->merge([
            'kode_kantor' => $request->filled('kode_kantor') ? $request->input('kode_kantor') : null,
        ]);
        $data = $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,username,'.$user->id,
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|in:IT,CABANG,VENDOR,ADMIN',
            'password' => 'nullable|string|min:8',
            'kode_kantor' => 'nullable|string|size:3|exists:kode_kantor,kode',
            'email_notifications_enabled' => ['nullable', 'boolean'],
            'android_notifications_enabled' => ['nullable', 'boolean'],
        ]);
        $user->username = $data['username'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->kode_kantor = $data['kode_kantor'] ?? null;
        $user->email_notifications_enabled = (bool) ($data['email_notifications_enabled'] ?? false);
        $user->android_notifications_enabled = (bool) ($data['android_notifications_enabled'] ?? false);
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        return redirect()->route('it.users.index')->with('success', 'User diperbarui.');
    }

    public function updateAiChat(Request $request, User $user)
    {
        $this->ensureIT();

        $data = $request->validate([
            'ai_chat_enabled' => ['nullable', 'boolean'],
        ]);

        $user->ai_chat_enabled = (bool) ($data['ai_chat_enabled'] ?? false);
        $user->save();

        return redirect()->route('it.users.index', $request->query())->with('success', 'Status AI user diperbarui.');
    }

    public function updateEmailNotifications(Request $request, User $user)
    {
        $this->ensureIT();

        $data = $request->validate([
            'email_notifications_enabled' => ['nullable', 'boolean'],
        ]);

        $user->email_notifications_enabled = (bool) ($data['email_notifications_enabled'] ?? false);
        $user->save();

        return redirect()->route('it.users.index', $request->query())->with('success', 'Status notifikasi email user diperbarui.');
    }

    public function updateAndroidNotifications(Request $request, User $user)
    {
        $this->ensureIT();

        $data = $request->validate([
            'android_notifications_enabled' => ['nullable', 'boolean'],
        ]);

        $user->android_notifications_enabled = (bool) ($data['android_notifications_enabled'] ?? false);
        $user->save();

        if (! $user->android_notifications_enabled) {
            $user->devices()->delete();
        }

        return redirect()->route('it.users.index', $request->query())->with('success', 'Status notifikasi Android user diperbarui.');
    }

    public function export(Request $request)
    {
        $this->ensureIT();

        $rows = $this->filteredUsers($request)
            ->orderBy('name')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Users');

        $headers = [
            'username',
            'name',
            'email',
            'role',
            'kode_kantor',
            'password',
            'visible_on_assign',
            'ai_chat_enabled',
            'email_notifications_enabled',
            'android_notifications_enabled',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1) . '1', $header);
        }

        $rowNum = 2;
        foreach ($rows as $user) {
            $sheet->setCellValue('A' . $rowNum, $user->username);
            $sheet->setCellValue('B' . $rowNum, $user->name);
            $sheet->setCellValue('C' . $rowNum, $user->email);
            $sheet->setCellValue('D' . $rowNum, $user->role);
            $sheet->setCellValue('E' . $rowNum, $user->kode_kantor);
            $sheet->setCellValue('F' . $rowNum, '');
            $sheet->setCellValue('G' . $rowNum, $user->visible_on_assign ? '1' : '0');
            $sheet->setCellValue('H' . $rowNum, $user->ai_chat_enabled ? '1' : '0');
            $sheet->setCellValue('I' . $rowNum, $user->email_notifications_enabled ? '1' : '0');
            $sheet->setCellValue('J' . $rowNum, $user->android_notifications_enabled ? '1' : '0');
            $rowNum++;
        }

        foreach (range(1, count($headers)) as $column) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setAutoSize(true);
        }
        $sheet->freezePane('A2');

        $writer = new Xlsx($spreadsheet);
        $filename = 'users_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request)
    {
        $this->ensureIT();

        $data = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $sheet = IOFactory::load($data['file']->getRealPath())->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        $headerRow = array_shift($rows) ?: [];
        $headerMap = [];

        foreach ($headerRow as $column => $label) {
            $key = strtolower(trim((string) $label));
            if ($key !== '') {
                $headerMap[$key] = $column;
            }
        }

        $requiredHeaders = ['username', 'name', 'email', 'role', 'kode_kantor', 'password'];
        $missingHeaders = array_diff($requiredHeaders, array_keys($headerMap));
        if (! empty($missingHeaders)) {
            return back()->with('error', 'Header import belum lengkap: ' . implode(', ', $missingHeaders) . '. Gunakan file hasil Export sebagai template.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $rowNumber => $row) {
            $line = $rowNumber + 2;
            $value = function (string $key) use ($row, $headerMap): string {
                if (! array_key_exists($key, $headerMap)) {
                    return '';
                }
                return trim((string) ($row[$headerMap[$key]] ?? ''));
            };

            $payload = [
                'username' => $value('username'),
                'name' => $value('name'),
                'email' => $value('email'),
                'role' => strtoupper($value('role')),
                'kode_kantor' => $value('kode_kantor') !== '' ? $value('kode_kantor') : null,
                'password' => $value('password'),
                'visible_on_assign' => $value('visible_on_assign') !== '' ? $value('visible_on_assign') : '0',
                'ai_chat_enabled' => $value('ai_chat_enabled') !== '' ? $value('ai_chat_enabled') : '0',
                'email_notifications_enabled' => $value('email_notifications_enabled') !== '' ? $value('email_notifications_enabled') : '1',
                'android_notifications_enabled' => $value('android_notifications_enabled') !== '' ? $value('android_notifications_enabled') : '1',
            ];

            if (collect($payload)->except(['visible_on_assign', 'ai_chat_enabled'])->filter(fn ($item) => $item !== null && $item !== '')->isEmpty()) {
                continue;
            }

            $existing = User::where('username', $payload['username'])
                ->orWhere('email', $payload['email'])
                ->first();

            $rules = [
                'username' => ['required', 'string', 'min:3', 'max:50', Rule::unique('users', 'username')->ignore($existing?->id)],
                'name' => ['required', 'string', 'min:3'],
                'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($existing?->id)],
                'role' => ['required', Rule::in(self::ROLES)],
                'kode_kantor' => ['nullable', 'string', 'size:3', 'exists:kode_kantor,kode'],
                'password' => [$existing ? 'nullable' : 'required', 'string', 'min:8'],
                'visible_on_assign' => ['nullable', 'boolean'],
                'ai_chat_enabled' => ['nullable', 'boolean'],
                'email_notifications_enabled' => ['nullable', 'boolean'],
                'android_notifications_enabled' => ['nullable', 'boolean'],
            ];

            $validator = validator($payload, $rules);
            if ($validator->fails()) {
                $skipped++;
                $errors[] = 'Baris ' . $line . ': ' . $validator->errors()->first();
                continue;
            }

            $user = $existing ?: new User();
            $user->username = $payload['username'];
            $user->name = $payload['name'];
            $user->email = $payload['email'];
            $user->role = $payload['role'];
            $user->kode_kantor = $payload['kode_kantor'];
            $user->visible_on_assign = (bool) $payload['visible_on_assign'];
            $user->ai_chat_enabled = (bool) $payload['ai_chat_enabled'];
            $user->email_notifications_enabled = (bool) $payload['email_notifications_enabled'];
            $user->android_notifications_enabled = (bool) $payload['android_notifications_enabled'];
            if ($payload['password'] !== '') {
                $user->password = Hash::make($payload['password']);
            }
            $user->save();

            $existing ? $updated++ : $created++;
        }

        $message = "Import selesai. {$created} dibuat, {$updated} diperbarui, {$skipped} dilewati.";
        if (! empty($errors)) {
            $message .= ' ' . implode(' ', array_slice($errors, 0, 5));
        }

        return back()->with($skipped > 0 ? 'error' : 'success', $message);
    }

    public function destroy(User $user)
    {
        $this->ensureIT();
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        $user->delete();
        return back()->with('success', 'User dihapus.');
    }
}
