<?php

namespace App\Imports;

use App\Models\ProductCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Validation\Rule;

class ProductCategoriesImport implements ToModel, WithBatchInserts, WithUpserts, withHeadingRow, WithValidation, SkipsOnFailure
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public $rowCount = 0;

    public function model(array $row)
    {
        ++$this->rowCount;
        return new ProductCategory([
            'name'          => $row['nama'],
            'code'         => strtolower(trim($row['kode'])),
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        return 'name';
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required',
            'kode' => 'required',
        ];
    }

    /**
     * @param Failure[] $failures
    */
    public function onFailure(Failure ...$failures)
    {
    }
}
