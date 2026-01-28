<label class="text-primary dark:text-accent-add-dark font-century block text-sm font-medium">
    {{ $slot }}

    @if ($required)
        <span class="text-[#B45171]">*</span>
    @endif
</label>
