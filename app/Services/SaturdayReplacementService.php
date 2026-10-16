<?php

namespace App\Services;

use App\Models\KodeKantor;
use App\Models\PergantianUser;
use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class SaturdayReplacementService
{
    public const REQUEST_TYPE = 'Pergantian Hari Sabtu';

    public function createExcelTemplate(string $targetPath): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pergantian');
        $headers = [
            "Username Eksisting\nContoh: user.lama",
            "Username Pengganti\nContoh: user.pengganti",
            "Tanggal Mulai Pergantian\nContoh: 17/10/2026",
            "Tanggal Selesai Pergantian\nContoh: 17/10/2026",
        ];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
        ]);
        $sheet->getStyle('A2:D101')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D1D5DB');
        $sheet->getStyle('C2:D101')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle('A2:B101')->getNumberFormat()->setFormatCode('@');
        $sheet->getRowDimension(1)->setRowHeight(42);
        $sheet->getStyle('A1:D1')->getAlignment()->setWrapText(true);
        foreach (['A' => 25, 'B' => 25, 'C' => 27, 'D' => 29] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:D101');
        $sheet->getStyle('A1:D101')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A2:D101')->getAlignment()->setWrapText(true);
        (new Xlsx($spreadsheet))->save($targetPath);
        $spreadsheet->disconnectWorksheets();
    }

    public function prepare(UploadedFile $file, int $userId, string $unitCode): array
    {
        $records = $this->readAndValidateWorkbook($file, $unitCode);
        $token = (string) Str::uuid();
        $directory = "saturday-replacements/{$userId}/{$token}";
        Storage::disk('local')->makeDirectory($directory);

        $excelName = 'data-pergantian-hari-sabtu.xlsx';
        $wordName = 'form-pergantian-user-hari-sabtu.docx';
        $excelPath = "{$directory}/{$excelName}";
        $wordPath = "{$directory}/{$wordName}";
        Storage::disk('local')->putFileAs($directory, $file, $excelName);

        $this->generateWordForm($records, $unitCode, Storage::disk('local')->path($wordPath));
        Storage::disk('local')->put("{$directory}/metadata.json", json_encode([
            'user_id' => $userId,
            'unit_code' => $unitCode,
            'excel_path' => $excelPath,
            'word_path' => $wordPath,
            'row_count' => count($records),
            'created_at' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT));

        return ['token' => $token, 'row_count' => count($records), 'word_name' => $wordName];
    }

    /** Tentukan unit dari irisan unit seluruh username pada Excel (untuk akun IT tanpa kode kantor). */
    public function inferUnitCode(UploadedFile $file): ?string
    {
        try {
            $sheet = IOFactory::load($file->getRealPath())->getActiveSheet();
        } catch (\Throwable $e) {
            return null;
        }

        $usernames = [];
        for ($row = 2; $row <= $sheet->getHighestDataRow(); $row++) {
            foreach ([1, 2] as $column) {
                $username = trim((string) $sheet->getCell([$column, $row])->getFormattedValue());
                if ($username !== '') $usernames[] = Str::lower($username);
            }
        }
        $usernames = array_values(array_unique($usernames));
        if ($usernames === []) return null;

        $unitsByUsername = PergantianUser::query()
            ->whereIn(DB::raw('LOWER(user_name)'), $usernames)
            ->get(['user_name', 'unit_kerja'])
            ->groupBy(fn ($user) => Str::lower($user->user_name));

        $candidateUnits = null;
        foreach ($usernames as $username) {
            $units = $unitsByUsername->get($username, collect())->pluck('unit_kerja')->unique()->values()->all();
            if ($units === []) return null;
            $candidateUnits = $candidateUnits === null ? $units : array_values(array_intersect($candidateUnits, $units));
        }

        return count($candidateUnits ?? []) === 1 ? $candidateUnits[0] : null;
    }

    public function metadata(string $token, int $userId): array
    {
        if (! Str::isUuid($token)) {
            throw ValidationException::withMessages(['saturday_replacement_token' => 'Data pergantian belum diproses.']);
        }
        $path = "saturday-replacements/{$userId}/{$token}/metadata.json";
        if (! Storage::disk('local')->exists($path)) {
            throw ValidationException::withMessages(['saturday_replacement_token' => 'Data pergantian tidak ditemukan atau sudah kedaluwarsa. Silakan unggah ulang.']);
        }
        $metadata = json_decode(Storage::disk('local')->get($path), true);
        if (! is_array($metadata) || (int) ($metadata['user_id'] ?? 0) !== $userId) {
            abort(403);
        }
        return $metadata;
    }

    public function movePreparedAttachments(string $token, int $userId): array
    {
        $metadata = $this->metadata($token, $userId);
        $result = [];
        foreach ([
            ['key' => 'excel_path', 'name' => 'data-pergantian-hari-sabtu.xlsx', 'kind' => 'saturday_replacement_excel'],
            ['key' => 'word_path', 'name' => 'form-pergantian-user-hari-sabtu.docx', 'kind' => 'saturday_replacement_form'],
        ] as $item) {
            $source = $metadata[$item['key']];
            if (! Storage::disk('local')->exists($source)) {
                throw ValidationException::withMessages(['saturday_replacement_token' => 'Berkas hasil proses tidak lengkap. Silakan unggah ulang.']);
            }
            $destination = 'lampiran/' . Str::uuid() . '-' . $item['name'];
            Storage::disk('public')->put($destination, Storage::disk('local')->get($source));
            $result[] = [
                'path' => $destination,
                'original_name' => $item['name'],
                'mime_type' => $item['kind'] === 'saturday_replacement_excel'
                    ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'size' => Storage::disk('public')->size($destination),
                'kind' => $item['kind'],
            ];
        }
        Storage::disk('local')->deleteDirectory("saturday-replacements/{$userId}/{$token}");
        return $result;
    }

    private function readAndValidateWorkbook(UploadedFile $file, string $unitCode): array
    {
        try {
            $sheet = IOFactory::load($file->getRealPath())->getActiveSheet();
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['saturday_replacement_file' => 'File Excel tidak dapat dibaca. Gunakan template yang disediakan.']);
        }

        $expected = ['username eksisting', 'username pengganti', 'tanggal mulai pergantian', 'tanggal selesai pergantian'];
        $actual = [];
        foreach (range(1, 4) as $column) {
            $header = trim((string) $sheet->getCell([$column, 1])->getValue());
            $actual[] = Str::lower(trim(preg_split('/\R/', $header, 2)[0]));
        }
        if ($actual !== $expected) {
            throw ValidationException::withMessages(['saturday_replacement_file' => 'Header Excel tidak sesuai template. Silakan unduh dan gunakan template terbaru.']);
        }

        $rawRows = [];
        for ($row = 2; $row <= $sheet->getHighestDataRow(); $row++) {
            $usernameExisting = trim((string) $sheet->getCell([1, $row])->getFormattedValue());
            $usernameReplacement = trim((string) $sheet->getCell([2, $row])->getFormattedValue());
            $start = trim((string) $sheet->getCell([3, $row])->getFormattedValue());
            $end = trim((string) $sheet->getCell([4, $row])->getFormattedValue());
            if ($usernameExisting === '' && $usernameReplacement === '' && $start === '' && $end === '') {
                continue;
            }
            if (in_array('', [$usernameExisting, $usernameReplacement, $start, $end], true)) {
                throw ValidationException::withMessages(['saturday_replacement_file' => "Baris {$row} belum lengkap."]);
            }
            $rawRows[] = compact('row', 'usernameExisting', 'usernameReplacement', 'start', 'end');
        }
        if ($rawRows === []) {
            throw ValidationException::withMessages(['saturday_replacement_file' => 'Excel belum berisi data pergantian.']);
        }
        if (count($rawRows) > 50) {
            throw ValidationException::withMessages(['saturday_replacement_file' => 'Maksimum 50 baris data dalam satu tiket.']);
        }

        $usernames = collect($rawRows)->flatMap(fn ($row) => [$row['usernameExisting'], $row['usernameReplacement']])->unique()->values();
        $users = PergantianUser::query()
            ->where('unit_kerja', $unitCode)
            ->whereIn('user_name', $usernames)
            ->get(['user_name', 'nama_lengkap'])
            ->keyBy(fn ($user) => Str::lower($user->user_name));

        return collect($rawRows)->map(function ($row) use ($users) {
            $existing = $users->get(Str::lower($row['usernameExisting']));
            $replacement = $users->get(Str::lower($row['usernameReplacement']));
            if (! $existing || ! $replacement) {
                throw ValidationException::withMessages(['saturday_replacement_file' => "Username pada baris {$row['row']} tidak ditemukan pada unit kerja Anda."]);
            }
            if (strcasecmp($existing->user_name, $replacement->user_name) === 0) {
                throw ValidationException::withMessages(['saturday_replacement_file' => "Username eksisting dan pengganti pada baris {$row['row']} harus berbeda."]);
            }
            $start = $this->parseDate($row['start'], $row['row']);
            $end = $this->parseDate($row['end'], $row['row']);
            if ($end->lt($start)) {
                throw ValidationException::withMessages(['saturday_replacement_file' => "Tanggal selesai pada baris {$row['row']} tidak boleh sebelum tanggal mulai."]);
            }
            return [
                'existing_username' => $existing->user_name,
                'existing_name' => $existing->nama_lengkap,
                'replacement_username' => $replacement->user_name,
                'replacement_name' => $replacement->nama_lengkap,
                'start_date' => $start->format('d/m/Y'),
                'end_date' => $end->format('d/m/Y'),
            ];
        })->all();
    }

    private function parseDate(string $value, int $row): Carbon
    {
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat('!' . $format, $value);
                if ($date !== false) return $date;
            } catch (\Throwable $e) {
            }
        }
        throw ValidationException::withMessages(['saturday_replacement_file' => "Format tanggal pada baris {$row} tidak valid."]);
    }

    private function generateWordForm(array $records, string $unitCode, string $targetPath): void
    {
        $template = resource_path('templates/template-form-user.docx');
        if (! is_file($template) || ! copy($template, $targetPath)) {
            throw new \RuntimeException('Template Word pergantian user tidak tersedia.');
        }

        $zip = new ZipArchive();
        if ($zip->open($targetPath) !== true) {
            throw new \RuntimeException('Form Word gagal dibuat.');
        }
        $xml = $zip->getFromName('word/document.xml');
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $office = KodeKantor::find($unitCode);
        $this->replaceParagraphText($xpath, '$user nama kantor', (string) ($office?->nama_kantor ?? $unitCode));
        $this->replaceParagraphText($xpath, '$userr kode kantor', $unitCode);
        $this->replaceParagraphText($xpath, '$tanggal sekarang', now()->format('d/m/Y'));

        $templateRow = null;
        foreach ($xpath->query('//w:tr') as $row) {
            $directText = '';
            foreach ($xpath->query('./w:tc/w:p//w:t', $row) as $textNode) {
                $directText .= $textNode->textContent;
            }
            if (str_contains($directText, '$pergantian_users.user_name')) {
                $templateRow = $row;
                break;
            }
        }
        if (! $templateRow instanceof DOMElement) {
            $zip->close();
            throw new \RuntimeException('Slot data pada template Word tidak ditemukan.');
        }
        $parent = $templateRow->parentNode;
        foreach ($records as $record) {
            $clone = $templateRow->cloneNode(true);
            $values = [
                $record['existing_username'], $record['existing_name'],
                $record['replacement_username'], $record['replacement_name'],
                $record['start_date'], $record['end_date'], '',
            ];
            foreach ($xpath->query('./w:tc', $clone) as $index => $cell) {
                $this->setNodeText($xpath, $cell, $values[$index] ?? '');
            }
            $parent->insertBefore($clone, $templateRow);
        }
        $parent->removeChild($templateRow);
        $zip->addFromString('word/document.xml', $dom->saveXML());
        $zip->close();
    }

    private function replaceParagraphText(DOMXPath $xpath, string $search, string $replacement): void
    {
        foreach ($xpath->query('//w:p') as $paragraph) {
            $text = $this->paragraphText($xpath, $paragraph);
            if (str_contains($text, $search)) {
                $this->setParagraphText($xpath, $paragraph, str_replace($search, $replacement, $text));
            }
        }
    }

    private function paragraphText(DOMXPath $xpath, DOMNode $paragraph): string
    {
        $text = '';
        foreach ($xpath->query('./w:r//w:t | ./w:hyperlink/w:r//w:t', $paragraph) as $textNode) {
            $text .= $textNode->textContent;
        }
        return $text;
    }

    private function setParagraphText(DOMXPath $xpath, DOMNode $paragraph, string $value): void
    {
        $nodes = $xpath->query('./w:r//w:t | ./w:hyperlink/w:r//w:t', $paragraph);
        if ($nodes->length === 0) return;
        $nodes->item(0)->nodeValue = $value;
        for ($index = 1; $index < $nodes->length; $index++) $nodes->item($index)->nodeValue = '';
    }

    private function nodeText(DOMXPath $xpath, DOMNode $node): string
    {
        $text = '';
        foreach ($xpath->query('.//w:t', $node) as $textNode) $text .= $textNode->textContent;
        return $text;
    }

    private function setNodeText(DOMXPath $xpath, DOMNode $node, string $value): void
    {
        $nodes = $xpath->query('.//w:t', $node);
        if ($nodes->length === 0) return;
        $nodes->item(0)->nodeValue = $value;
        for ($index = 1; $index < $nodes->length; $index++) $nodes->item($index)->nodeValue = '';
    }
}
