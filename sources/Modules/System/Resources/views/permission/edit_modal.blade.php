<form action="{{ route('system.role.update', $role->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="modal-header bg-warning">
        <h4 class="modal-title text-white"><i class="fas fa-cog"></i> Atur Akses: <b>{{ $role->name }}</b></h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body bg-light">
        {{-- Input Nama Role --}}
        <div class="form-group mb-4">
            <label>Nama Role</label>
            <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
        </div>

        <hr>
        <h5 class="mb-3 text-muted">Daftar Hak Akses (Permissions)</h5>

        {{-- LOOPING GROUP (MODUL) --}}
        <div class="row">
            @foreach($groupedPermissions as $groupName => $permissions)
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-navy p-2">
                            <div class="custom-control custom-checkbox">
                                {{-- Checkbox "Pilih Semua di Grup Ini" --}}
                                <input type="checkbox" class="custom-control-input check-all-group"
                                       id="check_group_{{ $groupName }}" data-group="{{ $groupName }}">
                                <label class="custom-control-label font-weight-bold" for="check_group_{{ $groupName }}">
                                    Modul {{ $groupName }}
                                </label>
                            </div>
                        </div>
                        <div class="card-body p-2" style="max-height: 200px; overflow-y: auto;">
                            {{-- LOOPING ITEM PERMISSION --}}
                            @foreach($permissions as $perm)
                                <div class="custom-control custom-checkbox mb-1">
                                    <input type="checkbox"
                                           class="custom-control-input perm-item group-{{ $groupName }}"
                                           id="perm_{{ $perm->id }}"
                                           name="permissions[]"
                                           value="{{ $perm->name }}"
                                        {{ in_array($perm->name, $rolePermissions, true) ? 'checked' : '' }}>
                                    <label class="custom-control-label small" for="perm_{{ $perm->id }}">
                                        {{ $perm->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
    </div>
</form>

<script>
    // Logic "Pilih Semua" per Group
    $('.check-all-group').change(function () {
        var groupName = $(this).data('group');
        var isChecked = $(this).is(':checked');

        // Cari semua checkbox anak buahnya, lalu samakan statusnya
        $('.group-' + groupName).prop('checked', isChecked);
    });

    // Opsional: Kalau anak buahnya di uncheck satu, induknya ikut uncheck
    $('.perm-item').change(function () {
        // (Logic ini bisa ditambahkan biar UX makin sempurna, tapi opsional)
    });
</script>
