{{-- resources/views/filament/pages/video-files.blade.php --}}
<x-filament::page>
    <h2 class="text-2xl font-bold mb-4">Video Files</h2>

    <ul>
        @foreach ($videoFiles as $file)
            <li>
                <a href="{{ Storage::url($file) }}" target="_blank">{{ basename($file) }}</a>
            </li>
        @endforeach
    </ul>
</x-filament::page>
