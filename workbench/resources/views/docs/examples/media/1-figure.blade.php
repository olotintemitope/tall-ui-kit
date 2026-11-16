{{-- Figure with a caption (an inline SVG here; use your image URL and srcset) --}}
<div class="max-w-md">
    <x-ui.figure
        src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 320 180'%3E%3Cg fill='currentColor' fill-opacity='.18'%3E%3Crect x='24' y='24' width='120' height='12' rx='2'/%3E%3Crect x='24' y='52' width='272' height='8' rx='2'/%3E%3Crect x='24' y='72' width='240' height='8' rx='2'/%3E%3Crect x='24' y='92' width='256' height='8' rx='2'/%3E%3Crect x='200' y='132' width='96' height='16' rx='2'/%3E%3C/g%3E%3C/svg%3E"
        alt="Invoice INV-2041 preview: a header, three line items and a total at the bottom right."
        caption="Invoice INV-2041, first page."
        aspect="16/9"
        :width="320"
        :height="180"
    />
</div>
