<form action="{{ route('system.role.update', $role->id) }}" method="POST" id="form-role-permission">
    @csrf
    @method('PUT')

    {{-- HEADER --}}
    <div class="modal-header bg-purple text-white">
        <h5 class="modal-title">
            <i class="fas fa-user-shield mr-1"></i> Edit Role: <b>{{ $role->name }}</b>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    {{-- BODY --}}
    <div class="modal-body p-0" style="height: 65vh; display: flex; flex-direction: column;">
        @php
            // Lock Role Identity (Homebase) atau mengandung kata 'admin'
            $isLocked = $role->is_identity || \Illuminate\Support\Str::contains($role->name, 'admin') || in_array($role->name, ['dosen', 'tendik', 'mahasiswa']);
        @endphp

        <div class="bg-white p-3 border-bottom shadow-sm" style="z-index: 10;">
            <div class="form-group mb-0">
                <label class="small text-muted font-weight-bold text-uppercase">Nama Role</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text {{ $isLocked ? 'bg-light' : 'bg-white' }}">
                            <i class="{{ $isLocked ? 'fas fa-lock text-danger' : 'fas fa-pen text-primary' }}"></i>
                        </span>
                    </div>
                    <input type="text" name="name" class="form-control {{ $isLocked ? 'bg-light' : '' }}"
                           value="{{ $role->name }}"
                           placeholder="Contoh: staff-keuangan"
                        {{ $isLocked ? 'readonly' : 'required' }}>
                </div>

                @if($isLocked)
                    <small class="text-danger mt-1 d-block">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        Nama role ini dikunci oleh sistem (Global/Core Role).
                    </small>
                @endif
            </div>
        </div>

        {{-- SPLIT VIEW (Sidebar + Content) --}}
        <div class="d-flex flex-fill overflow-hidden">
            {{-- SIDEBAR KIRI (Daftar Grup) --}}
            <div class="nav flex-column nav-pills p-2 bg-light border-right overflow-auto"
                 id="v-pills-tab" role="tablist" aria-orientation="vertical" style="width: 30%; min-width: 200px;">

                <div class="px-2 pb-2 mt-2">
                    <small class="text-muted text-uppercase font-weight-bold">Daftar Modul</small>
                </div>

                @foreach($groupedPermissions as $groupName => $permissions)
                    <a class="nav-link {{ $loop->first ? 'active' : '' }} text-sm mb-1"
                       id="v-pills-{{ Str::slug($groupName) }}-tab"
                       data-toggle="pill"
                       href="#v-pills-{{ Str::slug($groupName) }}"
                       role="tab"
                       aria-controls="v-pills-{{ Str::slug($groupName) }}"
                       aria-selected="{{ $loop->first ? 'true' : 'false' }}">

                        <i class="fas fa-folder mr-1"></i> {{ $groupName }}
                        <span class="badge badge-light float-right ml-2 shadow-sm">{{ count($permissions) }}</span>
                    </a>
                @endforeach
            </div>

            {{-- KONTEN KANAN (Daftar Checkbox) --}}
            <div class="tab-content p-3 flex-fill overflow-auto bg-white" id="v-pills-tabContent" style="width: 70%;">

                @foreach($groupedPermissions as $groupName => $permissions)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                         id="v-pills-{{ Str::slug($groupName) }}"
                         role="tabpanel"
                         aria-labelledby="v-pills-{{ Str::slug($groupName) }}-tab">

                        {{-- Header Group --}}
                        <div
                            class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom sticky-top bg-white"
                            style="top: -1rem; padding-top: 1rem; margin-top: -1rem;">
                            <h5 class="m-0 text-dark"><i class="fas fa-cube text-primary mr-1"></i> Fitur:
                                <b>{{ $groupName }}</b></h5>

                            {{-- Check All Per Group --}}
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input check-all-group"
                                       id="check_all_{{ Str::slug($groupName) }}"
                                       data-group="{{ Str::slug($groupName) }}">
                                <label class="custom-control-label font-weight-bold text-primary"
                                       for="check_all_{{ Str::slug($groupName) }}">
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
                                                   id="perm_{{ $perm->id }}"
                                                   name="permissions[]"
                                                   value="{{ $perm->name }}"
                                                {{ in_array($perm->name, $rolePermissions, true) ? 'checked' : '' }}>
                                            <label class="custom-control-label d-flex flex-column"
                                                   for="perm_{{ $perm->id }}" style="cursor: pointer;">
                                                <span
                                                    class="text-dark font-weight-medium text-sm">{{ $perm->name }}</span>
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
            <i class="fas fa-info-circle"></i> Update nama role & permission sekaligus.
        </div>
        <div>
            <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-warning shadow-sm px-4 font-weight-bold">
                <i class="fas fa-save mr-1"></i> Simpan Perubahan
            </button>
        </div>
    </div>
</form>

<style>
    .nav-pills .nav-link {
        color: #555;
        border-radius: 0.25rem;
        transition: all 0.2s;
    }

    .nav-pills .nav-link:hover {
        background-color: #e9ecef;
    }

    .nav-pills .nav-link.active {
        background-color: #6f42c1; /* Purple */
        color: #fff;
    }

    .nav-pills .nav-link.active .badge {
        background-color: rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    .permission-card {
        transition: background-color 0.2s;
    }

    .permission-card:hover {
        background-color: #f8f9fa;
        border-color: #6f42c1 !important;

    }

    /* Scrollbar */
    .overflow-auto::-webkit-scrollbar {
        width: 6px;
    }

    .overflow-auto::-webkit-scrollbar-thumb {
        background-color: #ccc;
        border-radius: 4px;
    }

    .hover-effect:hover {
        background-color: #f8f9fa;
        border-color: #6f42c1 !important;
    }

    .nav-pills .nav-link.active {
        background-color: #6f42c1;
        color: #fff;
    }
</style>

<script>
    // Logic Check All per Group
    $('.check-all-group').change(function () {
        var groupSlug = $(this).data('group');
        var isChecked = $(this).is(':checked');

        $('.group-' + groupSlug).prop('checked', isChecked);
    });

    // child uncheck
    $('.perm-item').change(function () {
        var groupClass = $(this).attr('class').split(' ').pop();
        var groupSlug = groupClass.replace('group-', '');

        var allChecked = $(`.${groupClass}:checked`).length === $(`.${groupClass}`).length;

        // Update status tombol "Pilih Semua"
        $(`#check_all_${groupSlug}`).prop('checked', allChecked);
    });

</script>
