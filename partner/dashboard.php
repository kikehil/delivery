<?php
// partner/dashboard.php - Ultra Minimalist Mobile-First Partner Panel (YaLoPido / Bocao)
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'socio') {
    header('Location: ../admin/login.php');
    exit;
}
require_once '../conexion.php';
$id_negocio = $_SESSION['id_negocio'];

// Get Business Data
$stmt = $pdo->prepare("SELECT * FROM negocios WHERE id = ?");
$stmt->execute([$id_negocio]);
$biz = $stmt->fetch();

// Get Products Data
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id_negocio = ? ORDER BY id DESC");
$stmt->execute([$id_negocio]);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Gestión | <?php echo htmlspecialchars($biz['nombre']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --accent-blue: #007aff; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }
        body { background:#fafafa; color:#000; padding-bottom: 40px; }
        
        .mobile-header { background:white; padding:20px; border-bottom:1px solid #eee; position:sticky; top:0; z-index:100; }
        .flex-between { display:flex; justify-content:space-between; align-items:center; }
        
        .status-card { background:white; margin:15px; padding:20px; border-radius:16px; border:1px solid #eee; }
        .toggle-wrap { display:flex; align-items:center; gap:12px; }
        
        /* Switch UI */
        .switch { position:relative; display:inline-block; width:52px; height:28px; }
        .switch input { opacity:0; width:0; height:0; }
        .slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:#ccc; transition:.4s; border-radius:34px; }
        .slider:before { position:absolute; content:""; height:20px; width:20px; left:4px; bottom:4px; background-color:white; transition:.4s; border-radius:50%; }
        input:checked + .slider { background-color: #34c759; }
        input:checked + .slider:before { transform: translateX(24px); }

        .section-title { padding:0 15px; font-size:1.1rem; font-weight:800; margin:20px 0 10px; }
        
        .product-list { padding:0 15px; }
        .product-card { background:white; padding:12px; border-radius:12px; border:1px solid #eee; margin-bottom:12px; display:flex; gap:12px; align-items:center; }
        .prod-img { width:60px; height:60px; border-radius:8px; object-fit:cover; background:#f0f0f0; }
        .prod-info { flex:1; }
        .prod-info h4 { font-size:0.95rem; font-weight:700; }
        .prod-info p { font-size:0.85rem; color:#666; }

        .btn-fab { position:fixed; bottom:20px; right:20px; background:#000; color:white; width:56px; height:56px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 12px rgba(0,0,0,0.2); border:none; }
        
        .tab-bar { padding:0 15px; margin-top:20px; }
    </style>
</head>
<body>
    <header class="mobile-header">
        <div class="flex-between">
            <h1 style="font-size:1.2rem; font-weight:800;"><?php echo htmlspecialchars($biz['nombre']); ?></h1>
            <a href="../admin/logout.php" style="color:#ff3b30; text-decoration:none; font-size:0.9rem; font-weight:600;">Salir</a>
        </div>
    </header>

    <div class="status-card">
        <div class="flex-between">
            <div>
                <h3 style="font-size:1rem; font-weight:700;">Estado del Local</h3>
                <p style="font-size:0.85rem; color:#666;" id="status-text"><?php echo $biz['modulo_abierto'] ? 'Recibiendo pedidos' : 'Cerrado temporalmente'; ?></p>
            </div>
            <label class="switch">
                <input type="checkbox" id="negocio-toggle" <?php echo $biz['modulo_abierto'] ? 'checked' : ''; ?>>
                <span class="slider"></span>
            </label>
        </div>
    </div>

    <h2 class="section-title">Tu Menú</h2>
    <div class="product-list">
        <?php foreach ($products as $pr): ?>
        <div class="product-card">
            <img src="<?php echo $pr['imagen_url'] ?: 'https://via.placeholder.com/60'; ?>" class="prod-img">
            <div class="prod-info">
                <h4><?php echo htmlspecialchars($pr['nombre']); ?></h4>
                <p>$<?php echo number_format($pr['precio'], 2); ?></p>
            </div>
            <div class="toggle-wrap">
                <label class="switch">
                    <input type="checkbox" class="product-toggle" data-id="<?php echo $pr['id']; ?>" <?php echo $pr['disponible'] ? 'checked' : ''; ?>>
                    <span class="slider" style="transform: scale(0.8);"></span>
                </label>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <button class="btn-fab" onclick="alert('Funcionalidad de añadir será habilitada pronto')">
        <i data-lucide="plus"></i>
    </button>

    <script>
        lucide.createIcons();

        // Toggle Negocio
        document.getElementById('negocio-toggle').onchange = async (e) => {
            const statusText = document.getElementById('status-text');
            const abierto = e.target.checked;
            statusText.innerText = abierto ? 'Recibiendo pedidos' : 'Cerrado temporalmente';
            
            const data = new FormData();
            data.append('action', 'toggle_negocio');
            data.append('abierto', abierto);
            await fetch('api_actions.php', { method: 'POST', body: data });
        };

        // Toggle Producto
        document.querySelectorAll('.product-toggle').forEach(el => {
            el.onchange = async (e) => {
                const data = new FormData();
                data.append('action', 'toggle_producto');
                data.append('id_producto', e.target.dataset.id);
                data.append('disponible', e.target.checked);
                await fetch('api_actions.php', { method: 'POST', body: data });
            };
        });
    </script>
</body>
</html>
