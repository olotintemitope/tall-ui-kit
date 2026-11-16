{{-- One row per file, in each state --}}
<ul aria-label="Campaign files" class="divide-y divide-line">
    <x-ui.upload-progress as="li" name="cover-art-final-v3-approved-by-label-2026-11-15.png" :size="2400000" :progress="45" status="uploading" />
    <x-ui.upload-progress as="li" name="distribution-contract.pdf" :size="180000" status="done" />
    <x-ui.upload-progress as="li" name="stems.zip" :size="512000000" :progress="70" status="failed" error="The connection dropped. Try again." />
    <x-ui.upload-progress as="li" name="lyrics.txt" :size="2400" status="queued" />
</ul>
