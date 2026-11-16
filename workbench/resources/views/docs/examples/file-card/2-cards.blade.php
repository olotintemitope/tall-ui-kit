{{-- Cards in a grid, with a thumbnail when there is one --}}
<x-ui.attachment-list label="Artwork" variant="card" :columns="3" :actions="['download', 'remove']" :items="[
    ['key' => 'a', 'name' => 'Album cover.png', 'size' => 840000, 'thumb' => 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%271200%27 height=%27800%27%3E%3Crect width=%271200%27 height=%27800%27 fill=%27%23d6e2e6%27/%3E%3Ccircle cx=%27600%27 cy=%27400%27 r=%27220%27 fill=%27%237fa9b5%27/%3E%3C/svg%3E', 'alt' => 'Album cover', 'href' => '#'],
    ['key' => 'b', 'name' => 'Jingle demo.wav', 'size' => 88244, 'href' => '#'],
    ['key' => 'c', 'name' => 'Tour poster, final, approved by the label and the venue.pdf', 'size' => 3100000, 'status' => 'uploading', 'progress' => 70],
]" />
