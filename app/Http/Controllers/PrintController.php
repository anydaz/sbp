<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;

class PrintController extends Controller
{
    public function print(Request $request)
    {
        try {
            // $logo = EscposImage::load(public_path('assets/logo.png'), false);
            $items_data = $request->input('data');

            $fn = function($value){
                // array value = [name, qty, price, discount]
                // value ex: ["item 1", "2", "200.000", "0"]
                return  [ new itemName($value[0]) , new itemDetail($value[2], $value[1], $value[3]) ];
            };

            $items = array_map($fn, $items_data);

            
            $sales_number = $request->input('sales_number');
            $total_before_discount_data = $request->input('total_before_discount');
            $sales_discount_data = $request->input('sales_discount');
            $total_after_discount_data = $request->input('total_after_discount');
            $notes = $request->input('notes');
            $payment_type = $request->input('payment_type');
            $date = $request->input('date');

            $total_before_discount = new footerItem('Total', $total_before_discount_data);
            $sales_discount = new footerItem('Diskon N.', $sales_discount_data);
            $total_after_discount = new footerItem('G. Total', $total_after_discount_data);

            $connector = new WindowsPrintConnector("POS58"); // Adjust if needed
            $printer = new Printer($connector);


            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            $printer -> setFont(Printer::FONT_A);
            // $printer -> graphics($logo);

            /* Name of shop */
            $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer -> text("Toko Dewi.\n");
            $printer -> selectPrintMode();
            $printer -> feed();

            /* Title of receipt */
            $printer -> setEmphasis(true);
            $printer -> text("Invoice Penjualan\n");
            $printer -> text("$sales_number\n");
            $printer -> feed();
            $printer -> setEmphasis(false);

            $printer -> setJustification(Printer::JUSTIFY_LEFT);
            $printer -> setEmphasis(true);
            $printer -> text(new itemDetail('Rp', 'Pcs'));
            $printer -> setEmphasis(false);

            foreach ($items as $item) {
                $printer -> text($item[0]); // print item Name
                $printer -> text($item[1]); // print item Detail
            }

            $printer -> feed();

            $printer -> text($total_before_discount);
            if($sales_discount_data > 0){
                $printer -> text($sales_discount);
                $printer -> text($total_after_discount);
            }

            $printer -> feed(2);
            $printer -> setFont(Printer::FONT_B);
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            $printer -> text("Please visit our store again, thank you\n");
            $printer -> text("Sudilah mengunjungi lagi toko kami,\n");
            $printer -> text("terima kasih\n");
            $printer -> feed(1);
            $printer -> text("Barang-barang dapat ditukar\n");
            $printer -> text("jika ada perjanjian\n");
            $printer -> feed(1);
            $printer -> text($date);
            $printer -> feed(3);

            // $printer->text("=== Receipt ===\n");
            // $printer->text("No: $sales_number\n");
            // $printer->text("Date: $date\n");
            // $printer->text("Payment: $payment_type\n");
            // $printer->text("---------------------\n");

            // foreach ($items_data as $item) {
            //     $printer->text("$item[0] x$item[1] - Rp $item[2]\n");
            // }

            // $printer->text("---------------------\n");
            // $printer->text("Subtotal: $total_before_discount\n");
            // $printer->text("Discount: $sales_discount\n");
            // $printer->text("Total: $total_after_discount\n");
            // $printer->text("Notes: $notes\n");
            $printer->cut();
            $printer->close();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

class itemDetail
{
    private $price;
    private $amount;
    private $discount;

    public function __construct($price = '', $amount = '', $discount ='')
    {
        $this -> price = $price;
        $this -> amount = $amount;
        $this -> discount = $discount;
        // $this -> dollarSign = $dollarSign;
    }

    public function __toString()
    {
        // $rightCols = 10;
        $leftCols = 20;
        $midCols = 3;
        $rightCols = 8;
        // $max_cols = 50;
        // $items_cols = strlen($this->name);
        // $price_cols = strlen($this->price);

        // if ($this -> dollarSign) {
        //     $leftCols = $leftCols / 2 - $rightCols / 2;
        // }

        $left = str_pad('', $leftCols);
        $mid = str_pad($this -> amount, $midCols, ' ', STR_PAD_LEFT);
        $right = str_pad($this -> price, $rightCols, ' ', STR_PAD_LEFT);

        $discountLeft = str_pad("discount", $leftCols);
        $discountRight = str_pad("@ -".$this -> discount, ($midCols + $rightCols), ' ', STR_PAD_LEFT);

        // dd($this -> discount);
        $response = "$left$mid$right\n";
        if($this -> discount != '' && $this -> discount != 0){
            $response = "$response$discountLeft$discountRight\n";
        }
        return $response;
    }
}


class itemName{
    private $name;

    public function __construct($name = '', $price = '', $amount = '', $discount ='')
    {
        $this -> name = $name;
    }

    public function __toString(){
        $max_cols = 31;
        $text = str_pad($this->name, $max_cols);
        return "$text\n";
    }
}

class footerItem
{
    private $name;
    private $price;
    private $amount;
    private $discount;

    public function __construct($description = '', $price = '')
    {
        $this -> price = $price;
        $this -> description = $description;
    }

    public function __toString()
    {
        // $rightCols = 10;
        $leftCols = 20;
        $midCols = 3;
        $rightCols = 8;
        // $max_cols = 50;
        // $items_cols = strlen($this->name);
        // $price_cols = strlen($this->price);

        // if ($this -> dollarSign) {
        //     $leftCols = $leftCols / 2 - $rightCols / 2;
        // }
        $left = str_pad($this -> description, $leftCols);
        $mid = str_pad('', $midCols, ' ', STR_PAD_LEFT);
        $right = str_pad($this -> price, $rightCols, ' ', STR_PAD_LEFT);

        // dd($this -> discount);
        $response = "$left$mid$right\n";
        return $response;
    }
}
