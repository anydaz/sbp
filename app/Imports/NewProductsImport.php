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

class NewProductsImport implements ToModel, WithBatchInserts, WithUpserts, withHeadingRow, WithValidation, SkipsOnFailure
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    protected $categories;
    protected $all_barcodes;
    public $rowCount = 0;

    public function __construct()
    {
        $this->categories = ProductCategory::pluck('id', 'code')->toArray();
        $this->all_barcodes = Product::pluck('barcode')->mapWithKeys(function ($barcode) {
            return [$barcode => true];
        })->toArray();
    }

    public function model(array $row)
    {
        ++$this->rowCount;

        $inputCategoryCode = strtolower(trim($row['kode_kategori'] ?? ''));
        $categoryId = $this->categories[$inputCategoryCode] ?? null;

        if (!$categoryId) {
            throw new \Exception("Category code '{$row['kode_kategori']}' not found for product '{$row['nama_lengkap']}'");
        }

        if(isset($this->all_barcodes[$row['barcode']])){
            throw new \Exception("Product with barcode '{$row['barcode']}' already exist'");
        }

        return new Product([
            'name'          => $row['nama_lengkap'],
            'alias'         => $row['nama_panggilan'],
            'barcode'       => $row['barcode'],
            'efficiency_code' => $row['efficien'],
            'price'         => $row['harga_jual'] ? $row['harga_jual'] : 0,
            'code'          => $row['kode'] ?? null,
            'min'           => $row['min'] ?? null,
            'plus'          => $row['plus'] ?? null,
            'quantity'      => 0,
            'cogs'           => 0,
            'product_category_id' => $categoryId
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        return 'barcode';
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required',
        ];
    }

    /**
     * @param Failure[] $failures
    */
    public function onFailure(Failure ...$failures)
    {
    }
}
