<label class="text-primary font-century block text-sm font-medium">
    {{ $slot }}

    @if ($required)
        <span class="text-[#B45171]">*</span>
    @endif
</label>
