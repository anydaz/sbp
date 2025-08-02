<?php
require __DIR__ . '/../vendor/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\CapabilityProfile;

$items_data = json_decode($_POST['data']);
$total_before_discount_data = $_POST['total_before_discount'];
$sales_discount_data = $_POST['sales_discount'];
$total_after_discount_data = $_POST['total_after_discount'];
$notes = $_POST['notes'];
$payment_type = $_POST['payment_type'];
$date = $_POST['date'];
// echo $items_data)[0][1];
// return;

/* Fill in your own connector here */
$connector = new WindowsPrintConnector("smb://DESKTOP-V9418H3/POS58");

/* Information for the receipt */
// $items_data = [["Test Item 1", "20.000"], ["Test Item 2", "30.000"]];
// $items = array(
//     new item("Example item #1", "4.00"),
//     new item("Another thing", "3.50"),
//     // new item("Something else", "1.00"),
//     // new item("A final item", "4.45"),
// );
$fn = function($value){
    // array value = [name, qty, price, discount]
    // value ex: ["item 1", "2", "200.000", "0"]
    return  [ new itemName($value[0]) , new itemDetail($value[2], $value[1], $value[3]) ];
};

$items = array_map($fn, $items_data);

// $item2 = $_POST['name'];
// $item3 = $_POST['name2'];

// $subtotal = new item('Subtotal', '12.95');
// $tax = new item('A local tax', '1.30');
$total_before_discount = new footerItem('Total', $total_before_discount_data);
$sales_discount = new footerItem('Diskon N.', $sales_discount_data);
$total_after_discount = new footerItem('G. Total', $total_after_discount_data);
/* Date is kept the same for testing */
// $date = date('l jS \of F Y h:i:s A');
$date = "Monday 6th of April 2015 02:56:25 PM";

/* Start the printer */
$logo = EscposImage::load("assets/logo.png", false);
$printer = new Printer($connector);

/* Print top logo */
$printer -> setJustification(Printer::JUSTIFY_CENTER);
$printer -> setFont(Printer::FONT_A);
$printer -> graphics($logo);

/* Name of shop */
$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
$printer -> text("Toko Dewi.\n");
$printer -> selectPrintMode();
$printer -> feed();

/* Title of receipt */
$printer -> setEmphasis(true);
$printer -> text("Invoice Penjualan\n");
$printer -> setEmphasis(false);

/* Items */
$printer -> setJustification(Printer::JUSTIFY_LEFT);
$printer -> setEmphasis(true);
$printer -> text(new itemDetail('Rp', 'Pcs'));
$printer -> setEmphasis(false);
foreach ($items as $item) {
    $printer -> text($item[0]); // print item Name
    $printer -> text($item[1]); // print item Detail
}

// $printer -> text($item2);
// $printer -> text($item3);

// $printer -> setEmphasis(true);
// $printer -> text($subtotal);
// $printer -> setEmphasis(false);
$printer -> feed();

/* Tax and total */
// $printer -> text($tax);
// $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
$printer -> text($total_before_discount);
if($sales_discount_data > 0){
    $printer -> text($sales_discount);
    $printer -> text($total_after_discount);
}
// $printer -> selectPrintMode();

/* Footer */
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
$printer -> feed(1);



// hide this for now, will be used later
// if(in_array($payment_type, ["Debit", "Kredit", "Transfer Megapolitan"])){
//     $printer -> text("PT. MEGAPOLITAN GLOBAL PRIMA\n");
//     $printer -> text("NPWP/PKP: 66.569.913.5-044.000\n");
//     $printer -> text("Jl. Cinangka Raya, Serua, Bojongsari,\n");
//     $printer -> text("Kota Depok, Jawa Barat, 16517,\n");
//     $printer -> feed(1);
// }

$printer -> setJustification(Printer::JUSTIFY_LEFT);
$printer -> text($notes);

/* Cut the receipt and open the cash drawer */
$printer -> feed(3);
$printer -> cut();
$printer -> pulse();

$printer -> close();

/* A wrapper to do organise item names & prices into columns */
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
