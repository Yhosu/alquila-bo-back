<!DOCTYPE html>
<html lang="en">
<head>
    <title></title>
    <style>
        @font-face {
            font-family: 'Arial';
            font-weight: normal;
            font-style: normal;
            font-variant: normal;
            /* src: url("https://solunes.s3.us-east-1.amazonaws.com/Downloads/arial.ttf") format("truetype"); */
        }
        body {
            font-family: Arial, sans-serif !important;
        }        
        @page { margin: 0px; }
        .background-gray {
            background-color: #f4f4f4;
        }
        .background-gray .factura {
            padding: 2rem;
            text-align: center;
            /* height: 1267px; */
            background-color: #fff;
            display: block;
            margin: auto;
        }
        .background-gray .factura p {
            font-size: 13.83px !important;
            color: #3f4246;
            line-height: 1.3;
            margin-bottom: 0;
        }
        .background-gray .factura p span {
            color: #5a6068 !important;
            font-weight: 600 !important;
        }
        .background-gray .factura p.bold {
            /* font-weight: 600 !important; */
        }
        .background-gray .factura p.bold-2 {
            /* font-weight: 700 !important; */
        }
        .background-gray .factura p.main_title {
            font-size: 14.56px !important;
        }

        .background-gray .factura p.sub_title {
            font-size: 11.92px !important;
        }

        .background-gray .factura p.sub_content {
            font-size: 7.92px !important;
        }

        .background-gray .factura p.table_text {
            font-size: 10.3333px !important;
        }

        .background-gray .factura p.small {
            font-size: 6.56px !important;
        }

        .table-header {
            width: 100%;
            border: none;
            margin-bottom: 0
        }

        .table-header td {
            width: 50%
        }

        .table-header table {
            width: 100%
        }

        .table-img tr td:nth-child(1) {
            width: 85px;
        }

        .table-img tr td:nth-child(2) {
            width: 100%;
        }

        .table-gray {
            background-color: #E3E3E3;
            border-radius: 10px;
        }

        .table-gray tr td {
            padding: 10px
        }

        .background-gray .factura .grid-data-3 {
            display: table;
            width: 100%;
            margin: .5rem auto;
            background-color: #e3e3e3;
            border-radius: 10px;
            padding: 1rem;
        }

        .background-gray .factura .grid-data-3.not-background {
            background-color: #fff;
        }

        .background-gray .factura .grid-data-3 .grid-row {
            display: table-row;
            width: 100%;
        }

        .background-gray .factura .grid-data-3 .grid-row div:nth-child(1) {
            text-align: left;
        }

        .background-gray .factura .grid-data-3 .grid-row div:nth-child(2) {
            text-align: right;
        }

        .background-gray .factura .grid-data-3 .grid-row .grid-col {
            display: table-cell;
            width: 50%;
        }

        .mb_50 {
            margin-bottom: .5rem;
        }

        .background-gray .factura .grid-data-4 {
            display: table;
            width: 100%;
        }

        .background-gray .factura .grid-data-4 .grid-row {
            display: table-row;
            width: 100%;
        }

        .background-gray .factura .grid-data-4 .grid-row .grid-col {
            display: table-cell;
        }

        .background-gray .factura .grid-data-4 .grid-row .grid-col.w_85 {
            width: 76%;
            vertical-align: top;
        }

        .background-gray .factura .grid-data-4 .grid-row .grid-col.w_15 {
            width: 24%;
        }

        .background-gray .factura .grid-data-4 .grid-row .grid-col.w_15 img {
            max-width: 100%;
            display: block;
            margin: auto;
        }

        .background-gray .factura .grid-data-4.m_50 {
            margin: 0.5rem 0;
        }

        .background-gray .factura .tabla-factura {
            width: 100%;
            /* border: 2px solid #5a6068; */
            margin-bottom: 20px;
        }

        .background-gray .factura .tabla-factura tr {
            border: 1px solid #5a6068;
        }

        .background-gray .factura .tabla-factura td {
            border: 2px solid #5a6068;
            padding: 0;
            color: #5a6068;
            padding: 1px 5px;
            margin: 0;
        }

        .background-gray .factura .tabla-factura td.no-border-1 {
            border-color: transparent;
        }

        .background-gray .factura .tabla-factura td.td_title {
            background-color: #e3e3e3;
            /* font-weight: 600; */
        }             
    </style>
</head>
<body>
    <div class="background-gray">
        <div class="factura">
            <table class="table-header">
                <tr>
                    <td valign="top">
                        <table class="table-img">
                            <tr>
                                <td><img src="data:image/png;base64,{{ base64_encode(file_get_contents(asset( \Asset::get_image_path('provider-image', 'normal', $provider->image) )) ) }}" alt="Logo" width="80"></td>
                                <td>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td valign="top">
                    </td>
                </tr>
            </table>

            <div>
                <p class="main_title bold-2"><strong>Recibo Nº -</strong></p>
            </div>

            <div class="grid-data-3">
                <div class="grid-row">
                    <div class="grid-col">
                        <p class="table_text"><strong>Nombre cliente: </strong>{{ $profile->invoice_name }}</p>
                        <p class="table_text"><strong>Código Cliente: </strong>{{ $profile->id }}</p>
                        <p class="table_text"><strong>Correo Electrónico: </strong>{{ $profile->email }}</p>
                        <p class="table_text"><strong>Dirección: </strong>-</p>
                    </div>
                    <div class="grid-col">
                        <p class="table_text"><strong>Fecha: </strong> {{ $firstProviderItem->created_at }}</p>
                        <p class="table_text"><strong>NIT/CI/CEX: </strong> {{ $profile->invoice_number }}</p>
                        <p class="table_text"><strong>Estado: </strong> Pagado</p>
                        <p class="table_text"><strong>Facturar a: </strong> {{ $profile->invoice_name }}</p>
                    </div>
                </div>
            </div>
            <table class="tabla-factura">
                <tr>
                    <td class="td_title" colspan="2">
                        <p class="table_text"> <strong>CÓDIGO PRODUCTO /<br>SERVICIO</strong></p>
                    </td>
                    <td class="bordered-top td_title">
                        <p class="table_text"> <strong>CANTIDAD</strong></p>
                    </td>
                    <td class="bordered-top td_title">
                        <p class="table_text"> <strong>DESCRIPCIÓN</strong></p>
                    </td>
                    <td class="bordered-top td_title">
                        <p class="table_text"> <strong>PRECIO UNITARIO</strong></p>
                    </td>
                    <td class="bordered-top td_title">
                        <p class="table_text"> <strong>DESCUENTO</strong></p>
                    </td>
                    <td class="bordered-top td_title">
                        <p class="table_text"> <strong>SUBTOTAL</strong></p>
                    </td>
                </tr>
                @php
                    $total = 0;
                @endphp
                @foreach( $providerItems as $detail )
                @php 
                    $total+=$detail->amount;
                @endphp
                <tr>
                    <td colspan="2">
                        <p class="table_text">-</p>
                    </td>
                    <td>
                        <p class="table_text">1</p>
                    </td>
                    <td>
                        <p class="table_text">{{ $detail->name }}</p>
                    </td>
                    <td>
                        <p class="table_text" style="text-align: right;">{{ currencyFormat( $detail->amount ) }}</p>
                    </td>
                    <td>
                        <p class="table_text" style="text-align: right;">{{ currencyFormat( 0 ) }}</p>
                    </td>
                    <td>
                        <p class="table_text" style="text-align: right;">{{ currencyFormat( $detail->amount ) }}</p>
                    </td>
                </tr>
                @endforeach
                <tr>
                    <td class="no-border-1" valign="bottom" colspan="4" rowspan="2">
                        <p class="table_text bold left"><strong>Son: {{ ucfirst( numberToText($total) ) }} {{ explode('.', currencyFormat( $total ))[1] }}/100 Bs</strong></p>
                        <p class="table_text bold left"></p>
                    </td>
                    <td colspan="2">
                        <p class="table_text right">SUBTOTAL Bs</p>
                    </td>
                    <td>
                        <p class="table_text right" style="text-align: right;">{{ currencyFormat( $total ) }}</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p class="table_text right">DESCUENTO Bs</p>
                    </td>
                    <td>
                        <p class="table_text right" style="text-align: right;">{{ currencyFormat( 0 ) }}</p>
                    </td>
                </tr>
                <tr>
                    <td class="no-border-1" valign="bottom" colspan="4" rowspan="2">
                    </td>
                    <td colspan="2">
                        <p class="table_text right">TOTAL Bs</p>
                    </td>
                    <td>
                        <p class="table_text right" style="text-align: right;">{{ currencyFormat( $total ) }}</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>