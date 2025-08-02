<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class BulkUpdatePriceImport implements ToModel, WithBatchInserts, WithUpserts, withHeadingRow, WithValidation, SkipsOnFailure
{
    private $rowCount = 0;
    private $updatedCount = 0;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->rowCount++;

        // Find product by efficiency_code
        $product = Product::where('efficiency_code', $row['kode'])->active()->first();

        if (!$product) {
            throw new \Exception("Product dengan kode '{$row['kode']}' tidak ditemukan di baris {$this->rowCount}");
            return null;
        }

        // Update the product price
        $product->update([
            'price' => (float) $row['harga_jual'],
            'last_edited' => Carbon::now()
        ]);

        $this->updatedCount++;

        return $product;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'kode' => 'required',
            'harga_jual' => 'required',
        ];
    }

    /**
     * Get the number of rows processed
     *
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * Get the number of products updated
     *
     * @return int
     */
    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    /**
     * Get the batch size for inserts
     *
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }

    public function uniqueBy()
    {
        return 'barcode';
    }

    /**
     * @param Failure[] $failures
    */
    public function onFailure(Failure ...$failures)
    {
    }
}