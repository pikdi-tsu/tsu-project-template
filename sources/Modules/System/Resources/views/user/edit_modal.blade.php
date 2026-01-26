<form action="{{ route('system.user.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="modal-header">
        <h4 class="modal-title">Edit User: {{ $user->name }}</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="form-group">
            <label>Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
        </div>
        <div class="form-group">
            <label>Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
        </div>
        <div class="form-group">
            <label>Password (Opsional)</label>
            <input type="password" name="password" class="form-control"
                   placeholder="Kosongkan jika tidak ingin ubah password">
            <small class="text-muted">Hanya isi jika ingin mengganti password user ini.</small>
        </div>
        <div class="form-group">
            <label>Role <span class="text-danger">*</span></label>
            <select name="roles[]" class="form-control select2-edit" multiple="multiple" style="width: 100%" required>
                @foreach($roles as $role)
                    {{-- Cek apakah user punya role ini --}}
                    <option value="{{ $role }}" {{ in_array($role, $userRole, true) ? 'selected' : '' }}>
                        {{ $role }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-warning">Update User</button>
    </div>
</form>

<script>
    // Init Select2 khusus AJAX Modal
    $('.select2-edit').select2({
        dropdownParent: $('#modal-edit'),
        placeholder: "Pilih Role"
    });
</script>
