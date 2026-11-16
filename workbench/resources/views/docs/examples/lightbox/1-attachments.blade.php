{{-- Attachment thumbnails that open the full image --}}
@php($receipt = 'data:image/svg+xml,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 800"><rect width="600" height="800" fill="whitesmoke"/><rect x="60" y="80" width="300" height="28" fill="dimgray"/><rect x="60" y="160" width="480" height="12" fill="silver"/><rect x="60" y="196" width="420" height="12" fill="silver"/><rect x="60" y="232" width="460" height="12" fill="silver"/><rect x="340" y="660" width="200" height="32" fill="dimgray"/></svg>'))
<div class="flex flex-wrap gap-3">
    <x-ui.lightbox name="receipt-1042" :src="$receipt" alt="Bank transfer receipt for PAY-9F3C21AB" description="Uploaded by Dayo Bello, 14 Nov 2026." :width="600" :height="800" />
    <x-ui.lightbox name="receipt-1043" :src="$receipt" alt="Card refund receipt for TXN-41AC77D0" :width="600" :height="800" :size="64" :full-url="null" />
</div>
