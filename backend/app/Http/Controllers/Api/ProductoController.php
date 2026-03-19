namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function index()
    {
        // Esta lógica reemplaza a tu antiguo 'get_productos.php'
        $productos = DB::table('productos')->where('status', 1)->get();
        
        return response()->json($productos);
    }
}