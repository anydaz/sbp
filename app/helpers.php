<?php

function calculateCogs($product, $received_qty, $received_price){
    if ($product->quantity + $received_qty <= 0) {
        return 0;
    }

    $starting_balance = $product->quantity * $product->cogs;
    $final_balance = $starting_balance + ($received_price * $received_qty);

    return $final_balance / ($product->quantity + $received_qty);
}