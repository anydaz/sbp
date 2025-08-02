<?php

namespace App\Imports;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Validation\Rule;

class PurchaseOrdersImport implements ToModel, WithBatchInserts, WithUpserts, withHeadingRow, WithValidation, SkipsOnFailure
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public $rowCount = 0;
    protected $products;


    public function __construct()
    {
        $this->products = Product::active()->get()->keyBy('efficiency_code');
    }

    public function model(array $row)
    {
        ++$this->rowCount;

        $user_id = auth()->user()->id;
        $items = [];

        for ($i = 0; $i < 10; $i++) {
            $efficiency_code = $row['kode_' . ($i + 1)];
            $qty = $row['qty_' . ($i + 1)];


            if (!$efficiency_code || !$qty) continue;
            $product = $this->products[$efficiency_code] ?? null;

            if (!$product) {
                throw new \Exception("efficiency code '{$efficiency_code}' invalid on row '{$this->rowCount}'");
            }

            $product_id = $product->id;
            $price = $product->price;
            array_push($items, [$product_id, $price, $qty]);
        }

        if (count($items) == 0) {
            throw new \Exception("No valid items found on row '{$this->rowCount}'");
        }

        return \DB::transaction(function () use ($row, $items, $user_id) {
            $po = PurchaseOrder::create([
            'purchase_number' => $row['no_pembelian'],
            'user_id' => $user_id,
            'purchase_discount' => 0
            ]);

            $details = [];

            foreach ($items as $item) {
                array_push($details, [
                    'product_id'        => $item[0],
                    'price'             => (int) $item[1],
                    'qty'               => (int) $item[2],
                    'item_discount'     => 0,
                    'subtotal'          => $item[1] * $item[2]
                ]);
            }

            // dd($details);

            $po->details()->createMany($details);

            return $po;
        });
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        return "id";
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function rules(): array
    {
        return [
            'no_pembelian' => 'required',
        ];
    }

    /**
     * @param Failure[] $failures
    */
    public function onFailure(Failure ...$failures)
    {
    }
}
