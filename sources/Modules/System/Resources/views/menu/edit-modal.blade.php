<form action="{{ route('system.menu.update', $menu->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="modal-body">
        <div class="form-group">
            <label>Nama Menu <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ $menu->name }}" required>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Icon Class</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="{{ $menu->icon }}"></i></span>
                        </div>
                        <input type="text" name="icon" class="form-control" value="{{ $menu->icon }}">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Route Laravel</label>
                    <input type="text" name="route" class="form-control" value="{{ $menu->route }}" placeholder="system.users.index">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Permission</label>
                    <select name="permission_name" class="form-control select2-edit" style="width: 100%;">
                        <option value="">-- Public --</option>
                        @foreach($permissions as $perm)
                            <option value="{{ $perm }}" {{ $menu->permission_name === $perm ? 'selected' : '' }}>
                                {{ $perm }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Parent Menu</label>
                    <select name="parent_id" class="form-control select2-edit" style="width: 100%;">
                        <option value="">-- Jadikan Utama --</option>
                        @foreach($parents as $id => $name)
                            <option value="{{ $id }}" {{ $menu->parent_id == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Urutan</label>
                    <input type="number" name="order" class="form-control" value="{{ $menu->order }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Status</label>
                    <div class="custom-control custom-switch pt-2">
                        <input type="checkbox" class="custom-control-input" id="edit_isactive" name="isactive" value="1" {{ $menu->isactive ? 'checked' : '' }}>
                        <label class="custom-control-label" for="edit_isactive">Aktif</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-warning">Update Perubahan</button>
    </div>
</form>

<script>
    // Re-init Select2 karena ini konten AJAX
    $('.select2-edit').select2();
</script>
