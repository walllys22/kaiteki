<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{Voyager::setting('admin.title') }}</title>
    <!-- Favicon -->
    <?php $admin_favicon = Voyager::setting('admin.icon_image', ''); ?>
    @if($admin_favicon == '')
        <link rel="shortcut icon" href="{{ voyager_asset('images/logo-icon-light.png')) }}" type="image/png">
    @else
        <link rel="shortcut icon" href="{{ Voyager::image($admin_favicon) }}" type="image/png">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>

        /* table, th, td {
            border-collapse: collapse !important;
        }
          
        table.print-friendly tr td, table.print-friendly tr th {
            page-break-inside: avoid !important;
        } */


        body{
            margin: 0px auto;
            font-family: Arial, sans-serif;
            font-weight: 100;
        }
        .background {
            width: 100%;
            background-color: #566573;
        }
        .sheet {
            /* width: 850px; */
            background-color: white;
            margin: auto;
            padding: 30px 50px;
        }
        .btn-print{
            padding: 5px 10px;
        }
        #watermark {
            width: 100%;
            position: fixed;
            top: 300px;
            opacity: 0.1;
            z-index:  -1;
            text-align: center
        }
        #watermark img{
            position: relative;
            width: 350px;
        }
        @media print{
            .hide-print{
                display: none
            }
            .content{
                padding: 0px 0px
            }
            .sheet {
                width: 100%;
                margin: 0px;
                padding: 0px;
            }
        }
    </style>
    @yield('css')
</head>
<body>
    <div class="background">
        <div class="hide-print" style="position: fixed; right: 0px; bottom: 0px; width:100%; text-align: right; padding: 20px">
            <button class="btn-print" onclick="window.close()">Cancelar <i class="fa fa-close"></i></button>
            <button class="btn-print" onclick="window.print()"> Imprimir <i class="fa fa-print"></i></button>
        </div>
        <div class="sheet">
            <div id="watermark">
                <?php 
                    $admin_favicon = Voyager::setting('admin.icon_image', '');
                ?>
                @if($admin_favicon == '')
                    <img src="{{ voyager_asset('images/logo-icon-light.png') }}" /> 
                @else
                    <img src="{{ Voyager::image($admin_logo_img) }}" /> 
                @endif
            </div>
            <div class="content">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        document.body.addEventListener('keypress', function(e) {
            switch (e.key) {
                case 'Enter':
                    window.print();
                    break;
                case 'Escape':
                    window.close();
                default:
                    break;
            }
        });
    </script>

    @yield('script')
</body>
</html>