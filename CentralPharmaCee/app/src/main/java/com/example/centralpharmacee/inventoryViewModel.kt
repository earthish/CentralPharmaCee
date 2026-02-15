package com.example.centralpharmacee

import androidx.compose.runtime.mutableStateOf
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import kotlinx.coroutines.launch

class InventoryViewModel : ViewModel() {
    var medicineList = mutableStateOf<List<Medicine>>(emptyList())
    var isLoading = mutableStateOf(false)

    fun fetchInventory() {
        viewModelScope.launch {
            isLoading.value = true
            try {
                val response = RetrofitClient.instance.getInventory()
                medicineList.value = response
            } catch (e: Exception) {
                // Handle error (e.g., no internet)
            } finally {
                isLoading.value = false
            }
        }
    }
}