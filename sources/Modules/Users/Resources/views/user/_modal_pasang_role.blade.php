<form action="{{ route('users.user.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="modal-header bg-primary">
        <h5 class="modal-title"><i class="fas fa-user-tag mr-2"></i>Pasang Role: {{ $user->name }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="form-group">
            <label>Pilih Role Akses <span class="text-danger">*</span></label>
            <select class="form-control select2" name="roles[]" multiple="multiple" data-placeholder="Pilih Role..." style="width: 100%;">
                @foreach($allRoles as $roleName)
                    {{-- Logika otomatis 'selected' jika user sudah punya role tersebut --}}
                    <option value="{{ $roleName }}" {{ in_array($roleName, $userRoles) ? 'selected' : '' }}>
                        {{ $roleName }}
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted">User dapat memiliki lebih dari satu role.</small>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
    </div>
</form>

{{-- SANGAT PENTING: Inisialisasi ulang Select2 karena form ini diload lewat AJAX --}}
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4' // Sesuaikan jika kamu pakai tema bootstrap4
        });
    });
</script>
