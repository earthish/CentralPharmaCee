package com.example.centralpharmacee

import retrofit2.http.GET

interface ApiService {
    @GET("get_inventory_api.php") // This matches the file we made earlier
    suspend fun getInventory(): List<Medicine>
}