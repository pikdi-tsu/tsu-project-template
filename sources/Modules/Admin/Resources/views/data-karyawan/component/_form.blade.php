@php
    // Deteksi apakah ini lagi Edit/Show (ada data karyawan), atau Create (kosong)
    $isEdit = isset($karyawan);

    // Deteksi apakah ini mode "Show" (Read-only semua)
    $isShow = isset($mode) && $mode === 'show';
@endphp

{{-- 1. LOOPING TABS HEADER --}}
<div class="card card-primary card-outline card-outline-tabs border-0 shadow-none mb-0 mt-3">
    <div class="card-header p-0 border-bottom-0 px-4">
        <ul class="nav nav-tabs" id="dynamic-tabs" role="tablist">
            @foreach($formConfig as $tabKey => $tab)
                <li class="nav-item">
                    <a class="nav-link font-weight-bold {{ $loop->first ? 'active' : '' }}"
                       data-toggle="pill"
                       href="#tab-{{ $tabKey }}">
                        {{ $tab['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- 2. LOOPING KONTEN FORM --}}
    <div class="card-body p-4 bg-light">
        <div class="tab-content">
            @foreach($formConfig as $tabKey => $tab)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $tabKey }}">
                    <div class="row">

                        {{-- LOOPING KOLOM (FIELDS) --}}
                        @foreach($tab['fields'] as $field)
                            @php
                                // Ambil value: Kalau edit ambil dari DB, kalau create ambil dari old() biar nggak hilang pas error validasi
                                $value = $isEdit ? $karyawan->{$field['name']} : old($field['name'], $field['default'] ?? null);

                                if ($field['type'] === 'date' && !empty($value)) {
                                    try {
                                        // Paksa wujudnya jadi YYYY-MM-DD
                                        $value = \Carbon\Carbon::parse($value)->format('Y-m-d');
                                    } catch (\Exception $e) {
                                        // Kalau error parsing, mending dikosongin daripada jadi 1970 wkwk
                                        $value = null;
                                    }
                                }

                                // Tentukan apakah field ini harus di-lock (berdasarkan config ATAU karena mode show)
                                $isReadonly = ($field['readonly'] ?? false) || $isShow;
                                $bgClass = $isReadonly ? 'bg-light text-muted' : '';
                            @endphp

                            <div class="col-md-{{ $field['col_size'] ?? 12 }} form-group mb-3">
                                <label class="font-weight-bold text-dark">
                                    {{ $field['label'] }}
                                    @if(isset($field['required']) && $field['required'])
                                        <span class="text-danger ml-1" title="Kolom ini wajib diisi">*</span>
                                    @endif
                                </label>

                                @if($field['type'] === 'textarea')
                                    <textarea name="{{ $field['name'] }}"
                                              class="form-control {{ $bgClass }}"
                                              rows="3"
                                              {{ $isReadonly ? 'readonly disabled' : '' }}>{{ $value }}</textarea>

                                @elseif($field['type'] === 'select')
                                    <select name="{{ $field['name'] }}" id="{{ $field['name'] }}"
                                            class="form-control {{ $bgClass }}"
                                        {{ isset($field['required']) ? 'required' : '' }}
                                        {{ $isReadonly ? 'readonly disabled' : '' }}>
                                        <option value="">- Pilih -</option>
                                        @if(isset($field['options']))
                                            @foreach($field['options'] as $optVal => $optLabel)
                                                <option value="{{ $optVal }}" {{ $value == $optVal ? 'selected' : '' }}>
                                                    {{ $optLabel }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    {{-- Kalau di-disable, nilainya nggak ke-submit, jadi kita akalin pakai hidden input --}}
                                    @if($isReadonly)
                                        <input type="hidden" name="{{ $field['name'] }}" value="{{ $value }}">
                                    @endif
                                @else
                                    @php
                                        // Cek apakah ada settingan prefix (kayak wa.me/)
                                        $hasPrefix = isset($field['prefix']);

                                        // Kalau ada prefix dan value-nya ada isinya, kita potong prefix-nya
                                        if($hasPrefix && $value) {
                                            $value = str_replace($field['prefix'], '', $value);
                                        }
                                    @endphp

                                    @if($hasPrefix)
                                        {{-- Render Input Group (Kotak Abu-abu di Kiri) --}}
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text font-weight-bold bg-light border-right-0">{{ $field['prefix'] }}</span>
                                            </div>
                                            <input type="{{ $field['type'] }}"
                                                   name="{{ $field['name'] }}"
                                                   class="form-control {{ $bgClass }}"
                                                   value="{{ $value }}"
                                                   placeholder="{{ $field['placeholder'] ?? '' }}"
                                                {{ $isReadonly ? 'readonly' : '' }}>
                                        </div>
                                    @else
                                        {{-- Render Inputan Biasa --}}
                                        <input type="{{ $field['type'] }}"
                                               name="{{ $field['name'] }}"
                                               class="form-control {{ $bgClass }}"
                                               value="{{ $value }}"
                                               placeholder="{{ $field['placeholder'] ?? '' }}"
                                            {{ $isReadonly ? 'readonly' : '' }}>
                                    @endif
                                @endif
                            </div>
                        @endforeach

                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
