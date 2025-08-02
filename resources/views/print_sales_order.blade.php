<style>
  .page { margin-bottom: 100px; margin-top: 185px; }

  table {
    width: 100%;
    table-layout: fixed;
  }

  table.bordered {
    border-collapse: collapse;
    font-size: 12px;
    margin-bottom: 16px;
  }

  table.bordered td, table.bordered th {
    border: 1px solid black;
  }

  .bold{
    font-weight: bold;
  }

  .text-centered{
    text-align: center;
  }
  #img {
    width: 20%
  }
  #footer {
    position: fixed;
    left: 0px;
    bottom: 100px;
    right: 0px;
  }
  #header {
    position: fixed;
    left: 0px;
    top: 0px;
    right: 0px;
  }

  #notes {
    position: fixed;
    left: 0px;
    bottom: -20px;
    font-size: 10px;
    margin: 0px;
  }
  .page-break{
    page-break-after: always;
  }

  .title {
    font-size: 16px;
  }
</style>
<html>
  <head></head>
  <body class="page">
    <!-- Footer Section -->
    <div id="footer">
      <!-- hide for now, will be used later -->
      <!-- @if(in_array($sales_order->payment_type->name, ["Debit", "Kredit", "Transfer Megapolitan"]) && $title != "Checklist")
        <p style="text-align: center">PT. MEGAPOLITAN GLOBAL PRIMA, NPWP: 66.569.913.5-044.000, Jl. Cinangka Raya, Serua, Bojongsari, Kota Depok, Jawa Barat, 16517</p>
      @else -->
      <!-- @endif -->
      <!-- <div style="">
        <p style="">Tanda Terima</p>
        <p style="">Hormat Kami</p>
      </div> -->
      <table style="margin-bottom: 30px; font-size: 12px; width:100%">
        <tr>
          <td style="text-align: left">
            Tanda Terima
          </td>
          <td style="text-align: right">
            Hormat kami
          </td>
        </tr>
      </table>
      <div>
        <p style="text-align: center; margin: 0px">PLEASE VISIT OUR STORE AGAIN, THANK YOU</p>
        <p style="text-align: center; margin: 0px; margin-bottom: 8px">SUDILAH MENGUNJUNGI LAGI TOKO KAMI, TERIMA KASIH</p>
        <p style="text-align: center; margin: 0px">Barang-barang dapat ditukar, jika ada perjanjian</p>
      </div>
    </div>
    @if($title == "Invoice")
      <p id="notes">{{$sales_order->notes}}</p>
    @endif
    <!-- End of Footer Section -->

    <div id="header">
      <table style="margin-bottom: 16px; font-size: 12px; width:100%">
        <tr>
          <td colspan="4" class="bold text-centered title">
            {{ $title }}
          </td>
        </tr>
        <tr>
          <td colspan="4" class="bold">
            <img id="img" src='assets/logo.png'></img>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            Toko Dewi
          </td>
          <td style="width: 8%; vertical-align: bottom">
            Tanggal
          </td>
          <td style="width: 42%; vertical-align: bottom">
            : {{$sales_order->created_at->format('d M Y')}}
          </td>
        </tr>
        <tr>
        <td style="width: 8%">
            Telp
          </td>
          <td style="width: 42%">
            : (+62 21) 724-5476, 720-1361, 725-2024
          </td>
          <td colspan="2">
            @if($sales_order->customer->id !== 0) Kepada Yth @endif
          </td>
        </tr>
        <tr>
          <td style="width: 8%">
            Fax
          </td>
          <td>
            : (+62 21) 724-6923
          </td>
          <td colspan="2">
            @if($sales_order->customer->id !== 0) {{$sales_order->customer->name}} @endif
          </td>
        </tr>
        <tr>
          <td colspan="2"></td>
          <td colspan="2">
            @if($sales_order->customer->id !== 0) {{$sales_order->customer->address}} @endif
          </td>
        </tr>
        <tr>
          <td colspan="2"></td>
          <td colspan="2">
            @if($sales_order->customer->id !== 0) {{$sales_order->customer->phone}} @endif
          </td>
        </tr>
      </table>
    </div>
    <table class="bordered">
      <thead>
        <tr>
          <td class="text-centered" style="width:15%">
            Banyaknya
          </td>
          <td>
            Nama Barang
          </td>
          @if ($title == "Invoice" || $title == "Proforma Invoice")
          <td style="width:15%">
            Harga
          </td>
          <td style="width:20%">
            Jumlah
          </td>
          @endif
        </tr>
      </thead>
      <tbody>
        @foreach ($sales_order->details as $detail)
          <tr>
            <td class="text-centered">
              {{number_format($detail->qty, 0, ',', '.')}}
            </td>
            <td>
              {{$detail->product->alias}}
            </td>
            @if ($title == "Invoice" || $title == "Proforma Invoice")
            <td>
              Rp {{number_format($detail->price, 0, ',', '.')}}
            </td>
            <td>
              Rp {{number_format($detail->subtotal, 0, ',', '.')}}
            </td>
            @endif
          </tr>
        @endforeach
        @if($sales_order->sales_discount)
        <tr>
          <td class="text-centered" colspan="3">
            Diskon Nota
          </td>
          <td>
            Rp {{number_format($sales_order->sales_discount, 0, ',', '.') }}
          </td>
        </tr>
        @endif
        @if($sales_order->total_return)
        <tr>
          <td class="text-centered" colspan="3">
            Total Retur
          </td>
          <td>
            Rp {{number_format($sales_order->sales_discount, 0, ',', '.') }}
          </td>
        </tr>
        @endif
        @if ($title == "Invoice" || $title == "Proforma Invoice")
        <tr>
          <td class="text-centered" colspan="3">
            Total
          </td>
          <td>
            Rp {{number_format($sales_order->details->sum('subtotal') - $sales_order->total_return - $sales_order->sales_discount, 0, ',', '.') }}
          </td>
        </tr>
        @endif
      </tbody>
    </table>
    @if($title == "Surat Jalan")
      <table style="margin-bottom: 16px; font-size: 12px; width:100%">
        <tr>
          <td>
            Tanda Terima
          </td>
          <td>
            Hormat Kami
          </td>
        </tr>
      </table>
    @endif
  </body>
</html>