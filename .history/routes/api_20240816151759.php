
use App\Http\Controllers\TransactionController;

Route::post('/transactions', [TransactionController::class, 'store']);
Route::get('/statistics', [TransactionController::class, 'statistics']);
Route::delete('/transactions', [TransactionController::class, 'destroy']);
