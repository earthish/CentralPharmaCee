package com.example.centralpharmacee

data class Medicine(
    val batch_id: String,
    val name: String,
    val stock_qty: Int,
    val price_per_unit: Double
)