@extends('system::template/layout/masterlogin')
@section('title', $title)
@section('link_href')
@endsection

@section('content')
<div class="login-box">
    <div class="login-logo">
        {{$nama}}
    </div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">
            <form id="new_pass">
                {{ csrf_field() }}
                <div class="form-group">
                    <label>New Password</label>
                    <input id="password" name="password" onkeyup="checkPassword()" type="password" class="form-control " placeholder="Enter password..." required>
                    <small><code id="warning"></code></small>
                </div>
                <div class="form-group">
                    <label>Retype Password</label>
                    <input id="password_retype" name="password_retype" onkeyup="checkPassword()" type="password" class="form-control" placeholder="Retype password..." required>
                </div>
                <button id="submit" type="submit" class="btn btn-success float-right">Change</button>
            </form>

            <form id="check_birthday" style="display:none;">
                {{ csrf_field() }}
                <input value="{{$nik}}" id="nik" name="nik" type="hidden">
                <input value="{{$role}}" id="role" name="role" type="hidden">

                <label for="">Birthday</label><code id="birthday_label"></code>
                <div class="form-group">
                    <input id="birthday" name="birthday" type="date" class="form-control" placeholder="Birthday .." required>
                </div>
                <button type="button" class="btn btn-default" onclick="showNewPass()">Back</button>
                <button type="submit" class="btn btn-success float-right">Next</button>
            </form>

            <form action="{{route('NewPasswordAction')}}" method="POST" id="input_qa" style="display:none;">
                <div class="card-footer p-1 mb-2 text-center">
                    <code>PENTING !! <br>Mohon diingat Security Question anda</code>
                </div>

                {{ csrf_field() }}
                <input id="pass" name="password" type="hidden">
                <input value="{{$nik}}" id="nik" name="nik" type="hidden">
                <input value="{{$nama}}" id="nama" name="nama" type="hidden">

                <div class="form-group mb-3">
                    <select name="q_1" class="form-control select2" required>
                        <option value="" selected>-- Security Question 1 --</option>
                        @foreach ($question_1 as $q1)
                            <option value="{{$q1}}">{{$q1}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-3">
                    <input id="a_1" name="a_1" type="text" class="form-control" placeholder="Input answer" required>
                </div>
                <hr>
                <div class="form-group mb-3">
                    <select name="q_2" class="form-control select2" style="width: 100%;" required>
                        <option value="" selected>-- Security Question 2 --</option>
                        @foreach ($question_2 as $q2)
                            <option value="{{$q2}}">{{$q2}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-3">
                    <input id="a_2" name="a_2" type="text" class="form-control" placeholder="Input answer" required>
                </div>

                <button type="button" class="btn btn-default" onclick="showBirth()">Back</button>
                <button type="submit" class="btn btn-info float-right">Save and Login</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(function() {
        //Initialize Select2 Elements
        $('.select2').select2()
    });
    function showNewPass() {
        $('#new_pass').removeAttr('style');
        $('#check_birthday').attr('style', 'display:none');
        $('#input_qa').attr('style', 'display:none');
    }

    function showBirth() {
        $('#check_birthday').removeAttr('style');
        $('#new_pass').attr('style', 'display:none');
        $('#input_qa').attr('style', 'display:none');
    }

    function showQA() {
        $('#input_qa').removeAttr('style');
        $('#new_pass').attr('style', 'display:none');
        $('#check_birthday').attr('style', 'display:none');
    }

    $('#new_pass').submit(function(e){
        e.preventDefault();
        var new_pass = $('#password').val();

        if (new_pass == 'Password123') {
            sweetAlert('warning', 'Gagal', 'Silahkan input password yang baru');
        } else {
            showBirth();
        }
    });

    $('#check_birthday').submit(function(e){
        e.preventDefault();

        $.ajax({
            url: '{{url('check-birthday')}}',
            type: 'get',
            data: $('#check_birthday').serialize(),
            beforeSend:function(data) {
                $('#birthday_label').html('<i class="fas fa-sync fa-spin"></i>');
            },
            success: function(data){
                if (data == 0) {
                    sweetAlert('error', 'Wrong date of birth');
                } else {
                    $('#pass').val($('#password').val());

                    showQA();
                    sweetAlert('success', 'Success, insert your Security Question');
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                toast('error', textStatus+' : '+errorThrown);
            }
        });
    });

    function checkPassword() {
        var password    = $('#password').val();
        var password_re = $('#password_retype').val();

        if (password != '' && password_re != '') {
            if (password != password_re) {
                $('#password_retype').addClass('is-invalid');
                $('#password_retype').removeClass('is-valid');

                $('#submit').attr('disabled', 'disabled');
            }else{
                $('#password_retype').removeClass('is-invalid');
                $('#password_retype').addClass('is-valid');
                $('#submit').removeAttr('disabled');
            }
        }

        if (password.length < 8) {
            $('#password').addClass('is-invalid');
            $('#password').removeClass('is-valid');

            $('#warning').html('*Mininum length : 8');
            $('#submit').attr('disabled', 'disabled');
        }else{
            pass_numb = password.replace(/[^0-9]/g, '').length;
            pass_char = password.replace(/[0-9]/g, '').length;

            if (pass_numb == 0) {
                $('#password').addClass('is-invalid');
                $('#password').removeClass('is-valid');

                $('#warning').html('*Must contain Number');
                $('#submit').attr('disabled', 'disabled');
            }else if(pass_char == 0){
                $('#password').addClass('is-invalid');
                $('#password').removeClass('is-valid');

                $('#warning').html('*Must contain Letter');
                $('#submit').attr('disabled', 'disabled');
            }else{
                $('#password').removeClass('is-invalid');
                $('#password').addClass('is-valid');
                $('#warning').html('');
            }
        }
    }


    $("#password,#a_1,#a_2").keypress(function(event){
        var ew = event.which;

        if(48 <= ew && ew <= 57)
            return true;
        if(65 <= ew && ew <= 90)
            return true;
        if(97 <= ew && ew <= 122)
            return true;
        return false;
    });
</script>
@endsection
