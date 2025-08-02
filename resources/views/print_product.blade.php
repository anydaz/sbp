<html>
  <head></head>
  <body>
    <table>
        <tr>
            <td>
                @php
                    echo DNS1D::getBarcodeHTML($product->barcode, "C39", 1, 40); 
                @endphp
            </td>
        </tr>
        <tr style="text-align: center">
            <td>{{$product->name}}</td>
        </tr>
        <tr style="text-align: center">
            <td>Rp. {{number_format($product->price)}}</td>
        </tr>
    <table>
  </body>
</html>