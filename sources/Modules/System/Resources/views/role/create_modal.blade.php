<form action="{{ route('system.role.store') }}" method="POST" id="form-create-role">
    @csrf

    {{-- HEADER --}}
    <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
            <i class="fas fa-plus-circle mr-1"></i> Buat Role Baru
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    {{-- BODY --}}
    <div class="modal-body p-0" style="height: 65vh; display: flex; flex-direction: column;">

        {{-- INPUT NAMA ROLE --}}
        <div class="bg-white p-3 border-bottom shadow-sm" style="z-index: 10;">
            <div class="form-group mb-0">
                <label class="small text-muted font-weight-bold text-uppercase">Nama Role <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white"><i class="fas fa-id-badge text-success"></i></span>
                    </div>
                    <input type="text" name="name" class="form-control"
                           placeholder="Contoh: staff-gudang" required autocomplete="off">
                </div>
                <small class="text-muted mt-1 d-block">
                    <i class="fas fa-info-circle mr-1"></i>
                    Role ini akan berstatus <b>Lokal</b> (Tidak tersync ke Homebase).
                </small>
            </div>
        </div>

        {{-- SPLIT VIEW (Sidebar + Content) --}}
        <div class="d-flex flex-fill overflow-hidden">
            {{-- SIDEBAR KIRI --}}
            <div class="nav flex-column nav-pills p-2 bg-light border-right overflow-auto"
                 id="v-pills-create-tab" role="tablist" style="width: 30%; min-width: 200px;">

                <div class="px-2 pb-2 mt-2">
                    <small class="text-muted text-uppercase font-weight-bold">Daftar Modul</small>
                </div>

                @foreach($groupedPermissions as $groupName => $permissions)
                    <a class="nav-link {{ $loop->first ? 'active' : '' }} text-sm mb-1"
                       id="v-create-{{ Str::slug($groupName) }}-tab"
                       data-toggle="pill"
                       href="#v-create-{{ Str::slug($groupName) }}"
                       role="tab">
                        <i class="fas fa-folder mr-1"></i> {{ $groupName }}
                        <span class="badge badge-light float-right ml-2 shadow-sm">{{ count($permissions) }}</span>
                    </a>
                @endforeach
            </div>

            {{-- KONTEN KANAN --}}
            <div class="tab-content p-3 flex-fill overflow-auto bg-white" id="v-pills-create-tabContent" style="width: 70%;">

                @foreach($groupedPermissions as $groupName => $permissions)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                         id="v-create-{{ Str::slug($groupName) }}" role="tabpanel">

                        {{-- Header Group --}}
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom sticky-top bg-white"
                             style="top: -1rem; padding-top: 1rem; margin-top: -1rem;">
                            <h5 class="m-0 text-dark"><i class="fas fa-cube text-success mr-1"></i> Fitur: <b>{{ $groupName }}</b></h5>

                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input check-all-group"
                                       id="create_check_all_{{ Str::slug($groupName) }}"
                                       data-group="{{ Str::slug($groupName) }}">
                                <label class="custom-control-label font-weight-bold text-success" for="create_check_all_{{ Str::slug($groupName) }}">
                                    Pilih Semua
                                </label>
                            </div>
                        </div>

                        {{-- List Checkbox --}}
                        <div class="row">
                            @foreach($permissions as $perm)
                                <div class="col-md-6">
                                    <div class="permission-card mb-2 p-2 border rounded hover-effect">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox"
                                                   class="custom-control-input perm-item group-{{ Str::slug($groupName) }}"
                                                   id="create_perm_{{ $perm->id }}"
                                                   name="permissions[]"
                                                   value="{{ $perm->name }}">
                                            <label class="custom-control-label d-flex flex-column" for="create_perm_{{ $perm->id }}" style="cursor: pointer;">
                                                <span class="text-dark font-weight-medium text-sm">{{ $perm->name }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="modal-footer bg-light justify-content-between">
        <div class="text-muted small">
            <i class="fas fa-check-circle text-success"></i> Role otomatis aktif setelah disimpan.
        </div>
        <div>
            <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-success shadow-sm px-4 font-weight-bold">
                <i class="fas fa-save mr-1"></i> Simpan Role Baru
            </button>
        </div>
    </div>
</form>

<style>
    .hover-effect:hover { background-color: #f0fdf4; border-color: #28a745 !important; }
    .nav-pills .nav-link.active { background-color: #28a745; color: #fff; }
</style>
<script>
    $('.check-all-group').change(function() {
        var groupSlug = $(this).data('group');
        var isChecked = $(this).is(':checked');
        $('.group-' + groupSlug).prop('checked', isChecked);
    });
    $('.perm-item').change(function() {
        var groupClass = $(this).attr('class').split(' ').pop();
        var groupSlug = groupClass.replace('group-', '');
        var allChecked = $(`.${groupClass}:checked`).length === $(`.${groupClass}`).length;
        $(`#create_check_all_${groupSlug}`).prop('checked', allChecked);
    });
</script>
