import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.example.centralpharmacee.InventoryViewModel
import com.example.centralpharmacee.Medicine


class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            Surface(color = MaterialTheme.colorScheme.background) {
                InventoryScreen()
            }
        }
    }
}

@Composable
fun InventoryScreen(viewModel: InventoryViewModel = viewModel()) {
    // Fetch data when the screen opens
    LaunchedEffect(Unit) {
        viewModel.fetchInventory()
    }

    Column(modifier = Modifier.padding(16.dp)) {
        Text(text = "Pharmacy Stock", style = MaterialTheme.typography.headlineMedium)
        Spacer(modifier = Modifier.height(16.dp))

        if (viewModel.isLoading.value) {
            CircularProgressIndicator()
        } else {
            LazyColumn {
                items(viewModel.medicineList.value) { medicine ->
                    MedicineCard(medicine)
                }
            }
        }
    }
}

@Composable
fun MedicineCard(medicine: Medicine) {
    Card(
        modifier = Modifier.fillMaxWidth().padding(vertical = 4.dp),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(16.dp)) {
            Text(text = medicine.name, style = MaterialTheme.typography.titleLarge)
            Text(text = "Batch: ${medicine.batch_id}")
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                Text(text = "Stock: ${medicine.stock_qty}")
                Text(text = "₹${medicine.price_per_unit}", color = MaterialTheme.colorScheme.primary)
            }
        }
    }
}