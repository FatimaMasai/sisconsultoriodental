<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    public function test_product_belongs_to_a_product_category(): void
    {
        $product = new Product();

        $this->assertInstanceOf(BelongsTo::class, $product->productCategory());
    }

    public function test_product_has_many_purchase_details(): void
    {
        $product = new Product();

        $this->assertInstanceOf(HasMany::class, $product->purchaseDetails());
    }
}
