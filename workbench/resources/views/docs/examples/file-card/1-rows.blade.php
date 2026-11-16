{{-- Rows: every status as a one-line pill; names wrap --}}
<x-ui.attachment-list label="Ticket attachments" :actions="['download', 'retry', 'remove', 'cancel']" :items="[
    ['key' => 'a', 'name' => 'Quarterly report.pdf', 'size' => 2400000, 'href' => '#'],
    ['key' => 'b', 'name' => 'statement-october-2026-first-bank-business-account-ending-4821.csv', 'size' => 128000, 'status' => 'uploading', 'progress' => 45],
    ['key' => 'c', 'name' => 'Stems for the remix.zip', 'size' => 48000000, 'status' => 'failed', 'error' => 'The connection dropped at 62%.'],
    ['key' => 'd', 'name' => 'invoice-crack.exe', 'size' => 512000, 'status' => 'infected', 'error' => 'Malware was found. The file was quarantined.'],
    ['key' => 'e', 'name' => 'Contract draft.docx', 'size' => 86000, 'status' => 'blocked', 'error' => 'Files with macros are blocked.'],
]" />
