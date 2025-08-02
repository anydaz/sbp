<style>
  table {
    width: 100%; 
    table-layout: fixed;
  }
  
  table.bordered {
    border-collapse: collapse;
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
</style>
<html>
  <head></head>
  <body>
    <table>
      <tr>
        <td class="bold text-centered">
          TOKO DEWI
        </td>
      </tr>
      <tr>
        <td class="bold text-centered">
          {{$title}}
        </td>
      </tr>
    <table>
    <table class="bordered">
      <thead>
        <tr>
          <td class="text-centered" style="width:15%">
            Banyaknya
          </td>
          <td>
            Nama Barang
          </td>
        </tr>
      </thead>
      <tbody>
        @foreach ($draft_sales_order->details as $detail)
          <tr>
            <td class="text-centered">
              {{number_format($detail->qty, 0, ',', '.')}}
            </td>
            <td>
              {{$detail->product->alias}}
            </td>
          </tr>
        @endforeach
      </tbody>
    <table>
  </body>
</html>