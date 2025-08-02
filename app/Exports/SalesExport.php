<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromArray, WithEvents
{
    protected $salesOrders;

    public function __construct($salesOrders)
    {
        $this->salesOrders = $salesOrders;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        $data = [];
        $currentRow = 1;


        foreach ($this->salesOrders->get() as $salesOrder) {

            // dd($salesOrder);
            // Add sales number and date
            $data[] = ['No Sales: ' . $salesOrder->sales_number, 'Customer: ' . ($salesOrder->customer->name ?? 'Tanpa Nama')];
            $data[] = ['Tanggal: ' . $salesOrder->date, 'Jenis Pembayaran: ' . ($salesOrder->payment_type->name ?? 'Tidak Diketahui')];
            $data[] = ['']; // Empty row with empty string

            // Add table headers
            $data[] = ['Kode Efisiensi', 'Nama Produk', 'Kuantitas', 'Harga', 'Diskon Barang', 'Subtotal'];

            // Add product details
            foreach ($salesOrder->details as $detail) {
                $data[] = [
                    $detail->product->efficiency_code ?? '',
                    $detail->product->name ?? '',
                    $detail->qty,
                    $detail->price,
                    $detail->item_discount,
                    $detail->subtotal
                ];
            }

            // Add empty row after product details
            $data[] = [''];

            $discount = $salesOrder->sales_discount;
            $total_return = $salesOrder->total_return;
            $grand_total = $salesOrder->details->sum('subtotal') - $discount - $total_return;

            // Add discount and total amount
            $data[] = ['', '', '', '', 'Diskon Nota', $discount == 0 ? "0" : $discount];
            $data[] = ['', '', '', '', 'Total Retur', $total_return == 0 ? "0" : $total_return];
            $data[] = ['', '', '', '', 'Total', $grand_total];

            // Add empty rows between sales orders if there are multiple
            $data[] = [''];
            $data[] = [''];
        }

        return $data;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $currentRow = 1;

                foreach ($this->salesOrders->get() as $salesOrder) {
                    // Style sales number and date
                    $sheet->getStyle('A' . $currentRow . ':A' . ($currentRow + 1))
                        ->getFont()->setBold(true)->setSize(12);

                    $sheet->getStyle('B' . $currentRow . ':B' . ($currentRow + 1))
                        ->getFont()->setBold(true)->setSize(12);

                    $currentRow += 3; // Skip to headers row

                    // Style table headers
                    $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)
                        ->getFont()->setBold(true);
                    $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFE6E6E6');

                    $startTableRow = $currentRow;
                    $currentRow++; // Move to first data row

                    // Count detail rows
                    $detailCount = $salesOrder->details->count();
                    $endTableRow = $currentRow + $detailCount - 1;

                    // Add borders to the table
                    if ($detailCount > 0) {
                        $sheet->getStyle('A' . $startTableRow . ':F' . $endTableRow)
                            ->getBorders()
                            ->getAllBorders()
                            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    }

                    $currentRow = $endTableRow + 2;

                    $sheet->getStyle('E' . $currentRow . ':E' . ($currentRow + 2))
                        ->getFont()->setBold(true);

                    $currentRow = $currentRow + 4;

                    $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)
                            ->getBorders()
                            ->getTop()
                            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED);

                    $currentRow = $currentRow + 1; // Move to next sales order position
                }

                // Auto-size columns
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);
                $sheet->getColumnDimension('F')->setAutoSize(true);
            }
        ];
    }
}
