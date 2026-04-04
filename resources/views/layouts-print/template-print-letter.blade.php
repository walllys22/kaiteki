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
            <link rel="shortcut icon" href="{{ voyager_asset('images/logo-icon-light.png') }}" type="image/png">
        @else
            <link rel="shortcut icon" href="{{ Voyager::image($admin_favicon) }}" type="image/png">
        @endif
        <style>
            body{
                margin: 0px auto;
                font-family: Arial, sans-serif;
            }
            .container {
                display: flex;
                justify-content: center;
                width: 100%;
                background: rgb(115,117,117);
                background: linear-gradient(90deg, rgba(115,117,117,1) 0%, rgba(173,173,173,1) 50%, rgba(115,117,117,1) 100%);
            }
            .sheet {
                padding: 30px;
                width: 780px;
                background-color: white
            }
            .content {
                text-align: justify;
                padding: 0px 34px;
                font-size: 11px;
            }
            #logo{
                margin: 0px;
                width: 90px;
            }
            .page-head {
                text-align: center;
                margin-bottom: 30px
            }
            .page-head h3 {
                margin-top: 0px !important
            }
            #watermark {
                position: fixed;
                top: 350px;
                opacity: 0.1;
                z-index:  100;
                width: 100%
            }
            #watermark img{
                position: relative;
                width: 300px;
                left: 210px;
            }

            .btn {
                padding: 8px 15px
            }
            .text-center{
                text-align: center;
            }
            ol p{
                margin: 10px
            }
            .table-signature {
                width: 100%;
                text-align: center;
                margin-top: 50px;
                margin-bottom: 80px;
            }

            @page {
                size: letter;
                /* margin: 20mm 10mm 30mm 10mm; */
            }
            @media print {
                body{
                    margin: 0px auto;
                }
                .options {
                    display: none
                }
                .sheet {
                    padding: 0px;
                    max-width: 100%;
                    background-color: white
                }
                .container {
                    background-color: transparent
                }
                .table-signature {
                    margin-bottom: 0px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="sheet">
                <table width="100%">
                    <tr>
                        <td>
                            <?php 
                                $admin_favicon = Voyager::setting('admin.icon_image', '');
                            ?>
                            @if($admin_favicon == '')
                                <img  id="logo" src="{{ voyager_asset('images/logo-icon-light.png') }}" /> 
                            @else
                                <img  id="logo" src="{{ Voyager::image($admin_logo_img) }}" /> 
                            @endif
                        </td>
                        <td style="text-align: right">@yield('qr_code')</td>
                    </tr>
                </table>
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
		        <div class="options" style="position: fixed; bottom: 10px; right: 20px">
                    <button type="button" class="btn btn-print" onclick="window.print()">Imprimir</button>
		        </div>
                @yield('content')
            </div>
        </div>

        @yield('css')

        <script>
            window.onafterprint = function(event) {
                console.log('before print');
            };
        </script>
        <script>
            $(document).ready(function () {

            });
        </script>
        @yield('script')
    </body>
</html>