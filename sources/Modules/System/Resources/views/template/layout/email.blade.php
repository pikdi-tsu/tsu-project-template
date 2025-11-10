<!doctype html>
<html>

<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Notification from {{ session('app.longname') }}</title>
    <style>
        /* -------------------------------------
            GLOBAL RESETS
        ------------------------------------- */
        img {
            border: none;
            -ms-interpolation-mode: bicubic;
            max-width: 100%;
        }

        body {
            background-color: #f6f6f6;
            font-family: sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        table {
            border-collapse: separate;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            width: 100%;
        }

        table td {
            font-family: sans-serif;
            font-size: 14px;
            vertical-align: top;
        }

        /* -------------------------------------
            BODY & CONTAINER
        ------------------------------------- */

        .body {
            background-color: #f6f6f6;
            width: 100%;
        }

        /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
        .container {
            display: block;
            Margin: 0 auto !important;
            /* makes it centered */
            max-width: 690px;
            padding: 15px;
            width: 690px;
        }

        /* This should also be a block element, so that it will fill 100% of the .container */
        .content {
            box-sizing: border-box;
            display: block;
            Margin: 0 auto;
            max-width: 580px;
            padding: 10px;
            position: absolute;
            z-index: 1;
        }

        /* -------------------------------------
            HEADER, FOOTER, MAIN
        ------------------------------------- */
        .main {
            background: #ffffff;
            border-radius: 3px;
            width: 100%;
        }

        .wrapper {
            box-sizing: border-box;
            padding: 20px;
        }

        .content-block {
            padding-bottom: 10px;
            padding-top: 10px;
        }

        .footer {
            clear: both;
            Margin-top: 10px;
            text-align: center;
            width: 100%;
        }

        .footer td,
        .footer p,
        .footer span,
        .footer a {
            color: #999999;
            font-size: 12px;
            text-align: center;
        }

        /* -------------------------------------
            TYPOGRAPHY
        ------------------------------------- */
        h1,
        h2,
        h3,
        h4 {
            color: #000000;
            font-family: sans-serif;
            font-weight: 400;
            line-height: 1.4;
            margin: 0;
            Margin-bottom: 30px;
        }

        h1 {
            font-size: 35px;
            font-weight: 300;
            text-align: center;
            text-transform: capitalize;
        }

        p,
        ul,
        ol {
            font-family: sans-serif;
            font-size: 14px;
            font-weight: normal;
            margin: 0;
            Margin-bottom: 15px;
        }

        p li,
        ul li,
        ol li {
            list-style-position: inside;
            margin-left: 5px;
        }

        a {
            color: #3498db;
            text-decoration: underline;
        }

        /* -------------------------------------
            BUTTONS
        ------------------------------------- */
        .btn {
            box-sizing: border-box;
            width: 100%;
        }

        .btn>tbody>tr>td {
            padding-bottom: 15px;
        }

        .btn table {
            width: auto;
        }

        .btn table td {
            background-color: #ffffff;
            border-radius: 5px;
            text-align: center;
        }

        .btn a {
            background-color: #ffffff;
            border: solid 1px #3498db;
            border-radius: 5px;
            box-sizing: border-box;
            color: #3498db;
            cursor: pointer;
            display: inline-block;
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            padding: 12px 25px;
            text-decoration: none;
            text-transform: capitalize;
        }

        .btn-primary table td {
            background-color: #3498db;
        }

        .btn-primary a {
            background-color: #3498db;
            border-color: #3498db;
            color: #ffffff;
        }

        /* -------------------------------------
            OTHER STYLES THAT MIGHT BE USEFUL
        ------------------------------------- */
        .last {
            margin-bottom: 0;
        }

        .first {
            margin-top: 0;
        }

        .align-center {
            text-align: center;
        }

        .align-right {
            text-align: right;
        }

        .align-left {
            text-align: left;
        }

        .clear {
            clear: both;
        }

        .mt0 {
            margin-top: 0;
        }

        .mb0 {
            margin-bottom: 0;
        }

        .preheader {
            color: transparent;
            display: none;
            height: 0;
            max-height: 0;
            max-width: 0;
            opacity: 0;
            overflow: hidden;
            mso-hide: all;
            visibility: hidden;
            width: 0;
        }

        .powered-by a {
            text-decoration: none;
        }

        hr {
            border: 0;
            border-bottom: 1px solid #f6f6f6;
            Margin: 20px 0;
        }

        /* -------------------------------------
            RESPONSIVE AND MOBILE FRIENDLY STYLES
        ------------------------------------- */
        @media only screen and (max-width: 620px) {
            table[class=body] h1 {
                font-size: 28px !important;
                margin-bottom: 10px !important;
            }

            table[class=body] p,
            table[class=body] ul,
            table[class=body] ol,
            table[class=body] td,
            table[class=body] span,
            table[class=body] a {
                font-size: 16px !important;
            }

            table[class=body] .wrapper,
            table[class=body] .article {
                padding: 10px !important;
            }

            table[class=body] .content {
                padding: 0 !important;
            }

            table[class=body] .container {
                padding: 0 !important;
                width: 100% !important;
            }

            table[class=body] .main {
                border-left-width: 0 !important;
                border-radius: 0 !important;
                border-right-width: 0 !important;
            }

            table[class=body] .btn table {
                width: 100% !important;
            }

            table[class=body] .btn a {
                width: 100% !important;
            }

            table[class=body] .img-responsive {
                height: auto !important;
                max-width: 100% !important;
                width: auto !important;
            }
        }

        /* -------------------------------------
            PRESERVE THESE STYLES IN THE HEAD
        ------------------------------------- */
        @media all {
            .ExternalClass {
                width: 100%;
            }

            .ExternalClass,
            .ExternalClass p,
            .ExternalClass span,
            .ExternalClass font,
            .ExternalClass td,
            .ExternalClass div {
                line-height: 100%;
            }

            .apple-link a {
                color: inherit !important;
                font-family: inherit !important;
                font-size: inherit !important;
                font-weight: inherit !important;
                line-height: inherit !important;
                text-decoration: none !important;
            }

            .btn-primary table td:hover {
                background-color: #34495e !important;
            }

            .btn-primary a:hover {
                background-color: #34495e !important;
                border-color: #34495e !important;
            }
        }

        #background {
            position: absolute;
            z-index: 0;
            background: white;
            display: block;
            min-height: 50%;
            min-width: 50%;
            color: yellow;
        }

        #bg-text {
            color: lightgrey;
            font-size: 120px;
            transform: rotate(300deg);
            -webkit-transform: rotate(300deg);
        }

        .tombol {
            background: #2C97DF;
            color: white;
            border-top: 0;
            border-left: 0;
            border-right: 0;
            border-bottom: 5px solid #2A80B9;
            padding: 10px 20px;
            text-decoration: none;
            font-family: sans-serif;
            font-size: 11pt;
        }
    </style>
</head>

<body class="">
    <table border="0" cellpadding="0" cellspacing="0" class="body">
        <tr>
            <td>&nbsp;</td>
            <td class="container">
                <div class="content">

                    <!-- START CENTERED WHITE CONTAINER -->
                    <span class="preheader">Email Notification {{ session('appname') }} <br></span>
                    <table class="main">

                        <!-- START MAIN CONTENT AREA -->
                        <tr>
                            <td class="wrapper">
                                <table border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <center><b>Tiga Serangkai University</b><br>Welcome to
                                                {{ strtoupper(session('appname')) == null ? 'SIAKAD' : strtoupper(session('appname')) }}
                                            </center>
                                        </td><br>
                                    </tr>
                                    <tr>
                                        <td> </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p>Halo, <b><i> {{ $nama }}</i></b></p>

                                            <table border="0" cellpadding="0" cellspacing="0">
                                                <tbody>
                                                    <tr>
                                                        <td align="left">
                                                            <table border="0" cellpadding="0" cellspacing="0">
                                                                <tbody>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="container">
                                                                                @if ($jenis == 'Reset Password')
                                                                                    @php
                                                                                        $datalist = explode('##', $data);
                                                                                    @endphp
                                                                                    <p style="font-size: 11pt">Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.</p>
                                                                                    <table border="0"
                                                                                        style="width:100%;line-height: 1.6;font-size: 11pt">
                                                                                        <tr>
                                                                                            <td style="width:20%">{{$datalist[0]=='MAHASISWA' ? 'NIM' : 'NIP'}}</td>
                                                                                            <td> : </td>
                                                                                            <td style="width:78%"><b
                                                                                                    style="font-size: 13pt">{{ $datalist[1] }}</b>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>Nama</td>
                                                                                            <td> : </td>
                                                                                            <td>{{ $nama }}</td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>Link Reset</td>
                                                                                            <td> : </td>
                                                                                            <td>
                                                                                                <a href="{{ $datalist[2] }}" class="button">Reset Password</a>
                                                                                            </td>
                                                                                        </tr>
                                                                                    </table>
                                                                                    <br>
                                                                                    <p style="font-size: 11pt">Jika Anda tidak merasa melakukan permintaan ini, abaikan saja email ini dan tidak ada perubahan yang akan dilakukan.
                                                                                        <a href="{{ $datalist[3] }}">Klik Disini Untuk Login Siakad</a>.
                                                                                @endif

                                                                                <br><br><br>
                                                                                <p style="font-size: 10pt">Thanks<br>
                                                                                    Administrator System
                                                                                    Tiga Serangkai University
                                                                                </p>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- END MAIN CONTENT AREA -->
                    </table>

                    <!-- START FOOTER -->
                    <div class="footer">
                        <table border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="content-block">
                                    <span class="apple-link"><i>Don't reply this e-mail, e-mail auto sent from
                                            system</i></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="content-block powered-by">
                                    Powered by <a href="">IT Team Tiga Serangkai University</a>.
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- END FOOTER -->

                    <!-- END CENTERED WHITE CONTAINER -->
                </div>
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
</body>

</html>
