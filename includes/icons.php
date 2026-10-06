<?php
/** Inline SVG icons (stroke = currentColor). */
function icon(string $name): string
{
    static $paths = [
        'eye'         => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/><path class="slash" d="M3 3l18 18"/>',
        'dollar'      => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 6v12M15 9c0-1.2-1.3-2-3-2s-3 .8-3 2 1.3 1.8 3 2 3 .8 3 2-1.3 2-3 2-3-.8-3-2"/>',
        'tree'        => '<path d="M12 3l-5 7h3l-4 6h12l-4-6h3z"/><path d="M12 16v5"/>',
        'zero'        => '<ellipse cx="12" cy="12" rx="5" ry="8"/><path d="M5 19L19 5"/>',
        'scan'        => '<path d="M3 8V3h5M16 3h5v5M21 16v5h-5M8 21H3v-5"/>',
        'fingerprint' => '<path d="M12 4a8 8 0 0 0-8 8M12 8a4 4 0 0 0-4 4c0 3 1 5 2 7M12 12v2c0 2 .5 4 1.5 6M16 12a4 4 0 0 0-1-2.6M20 12a8 8 0 0 0-3-6.2M16 15c0 2 .5 3.5 1 4.5"/>',
        'backspace'   => '<path d="M9 5h11v14H9l-6-7z"/><path d="M13 9l4 6M17 9l-4 6"/>',
    ];
    return '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
}
