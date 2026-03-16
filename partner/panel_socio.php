<?php
// partner/panel_socio.php - Panel de Gestión Ultra-Minimalista (Bocao Style)
session_start();

// Seguridad: Redirigir si no es socio
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'socio') {
    header('Location: ../admin/login.php');
    exit;
}

require_once '../conexion.php';
$id_negocio = $_SESSION['id_negocio'];

// Obtener datos del negocio
$stmt = $pdo->prepare("SELECT * FROM negocios WHERE id = ?");
$stmt->execute([$id_negocio]);
$biz = $stmt->fetch();

// Obtener productos
$stmt_prod = $pdo->prepare("SELECT * FROM productos WHERE id_negocio = ? ORDER BY id DESC");
$stmt_prod->execute([$id_negocio]);
$productos = $stmt_prod->fetchAll();

$activos = 0;
foreach($productos as $p) if($p['disponible']) $activos++;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Panel Socio | YaLoPido</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --bocao-orange: #f28b20;
            --success: #34c759;
            --danger: #ff3b30;
            --bg: #f8f9fa;
            --white: #ffffff;
            --gray: #f2f2f2;
            --text: #0b1c2e;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text); padding-bottom: 100px; -webkit-tap-highlight-color: transparent; }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        /* Header & Welcome */
        .header { 
            padding: 20px; 
            border-bottom: 1px solid #eee; 
            position: sticky; 
            top: 0; 
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(10px);
            z-index: 100; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .welcome-text { font-size: 1.2rem; font-weight: 800; letter-spacing: -0.5px; }
        .stats-pill { font-size: 0.85rem; color: #666; margin-top: 2px; font-weight: 500; }
        .stats-pill strong { color: var(--bocao-orange); }
        
        .header-actions { display: flex; gap: 12px; align-items: center; }
        .btn-header {
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 16px;
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s;
            border: 1px solid transparent;
        }
        
        .btn-cocina { background: var(--bocao-orange); color: white; border-color: var(--bocao-orange); }
        .btn-cocina:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(242, 139, 32, 0.3); }
        
        .config-btn { background: white; color: var(--text); border-color: #ddd; }
        .logout-btn { background: #fff0f0; color: var(--danger); border-color: #ffebeb; }

        /* Status Toggle Card */
        .status-card { 
            margin: 20px; 
            padding: 24px; 
            background: #000; 
            color: white; 
            border-radius: 24px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
        }
        .status-info h4 { font-size: 1.1rem; font-weight: 800; margin-bottom: 4px; }
        .status-info p { font-size: 0.85rem; opacity: 0.7; }

        /* Menu Grid */
        .section-header { 
            padding: 0 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin: 40px 0 20px; 
        }
        .section-header h2 { font-size: 1.4rem; font-weight: 800; }
        
        .product-grid { 
            padding: 0 20px; 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
            gap: 20px; 
        }
        
        .product-card { 
            background: white;
            padding: 16px; 
            border-radius: 20px; 
            border: 1px solid #eee; 
            display: flex; 
            gap: 16px; 
            align-items: center; 
            transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            position: relative; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.05); }
        .product-card.paused { opacity: 0.6; background: #fafafa; }
        
        .prod-img { width: 85px; height: 85px; border-radius: 16px; object-fit: cover; background: var(--gray); }
        .prod-detail { flex: 1; }
        .prod-detail h3 { font-size: 1.05rem; font-weight: 800; margin-bottom: 4px; color: var(--text); }
        .prod-detail p { font-size: 1.1rem; font-weight: 700; color: var(--bocao-orange); }

        .prod-actions { display: flex; flex-direction: column; gap: 8px; }
        .action-btn { width: 40px; height: 40px; border-radius: 12px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
        .btn-pause { background: var(--gray); color: #666; }
        .btn-pause.active { background: #e8f5e9; color: var(--success); }
        .btn-delete { background: #fff1f1; color: var(--danger); }

        /* Bottom Tab / FAB */
        .fab { 
            position: fixed; 
            bottom: 30px; 
            right: 30px; 
            background: #000; 
            color: white; 
            padding: 18px 32px; 
            border-radius: 20px; 
            font-weight: 700; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            border: none; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.3); 
            z-index: 200; 
            cursor: pointer;
            transition: 0.3s;
        }
        .fab:hover { transform: scale(1.05) translateY(-5px); }

        /* Modal Subir Platillo */
        .modal { 
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(11, 28, 46, 0.6); 
            backdrop-filter: blur(5px);
            z-index: 1000; 
            animation: fadeIn 0.3s; 
            padding: 20px;
        }
        
        .modal-body { 
            position: relative; 
            width: 100%; 
            max-width: 550px;
            margin: 50px auto;
            background: white; 
            border-radius: 30px; 
            padding: 35px; 
            animation: modalPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            max-height: 90vh;
            overflow-y: auto;
        }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes modalPop { from { opacity: 0; transform: scale(0.9) translateY(30px); } to { opacity: 1; transform: scale(1) translateY(0); } }

        /* Para móviles, mantenemos el estilo de hoja inferior si es pantalla pequeña */
        @media (max-width: 600px) {
            .modal { padding: 0; }
            .modal-body { position: absolute; bottom: 0; margin: 0; border-radius: 30px 30px 0 0; animation: slideUp 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); }
            @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
            .fab { left: 50%; right: auto; transform: translateX(-50%); }
            .fab:hover { transform: translateX(-50%) scale(1.05) translateY(-5px); }
        }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #999; margin-bottom: 10px; letter-spacing: 1px; }
        .form-group input, .form-group textarea { width: 100%; padding: 16px; border: 1.5px solid #eee; background: white; border-radius: 14px; font-size: 1rem; outline: none; transition: 0.2s; }
        .form-group input:focus { border-color: var(--bocao-orange); background: #fffaf5; }
        
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .checkbox-group { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; padding: 14px; background: var(--gray); border-radius: 14px; cursor: pointer; transition: 0.2s; }
        .checkbox-group:hover { background: #e8e8e8; }
        .checkbox-group input { width: 22px; height: 22px; cursor: pointer; accent-color: var(--bocao-orange); }
        .checkbox-group label { margin-bottom: 0; font-size: 0.95rem; font-weight: 600; flex: 1; text-transform: none; color: var(--text); cursor: pointer; }
        
        .btn-save { width: 100%; background: var(--text); color: white; border: none; padding: 20px; border-radius: 18px; font-weight: 800; font-size: 1rem; margin-top: 10px; cursor: pointer; transition: 0.2s; }
        .btn-save:hover { background: #000; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }

        .modal-body::-webkit-scrollbar { width: 6px; }
        .modal-body::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }

        /* Switch Styling */
        .switch { position: relative; display: inline-block; width: 60px; height: 32px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #444; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 24px; width: 24px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        input:checked + .slider { background-color: var(--success); }
        input:checked + .slider:before { transform: translateX(28px); }

        /* Utility Classes */
        @media (max-width: 768px) {
            .d-none-mobile { display: none !important; }
        }
        @media (min-width: 769px) {
            .d-only-mobile { display: none !important; }
            .container { padding: 0 40px; }
        }


    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div>
                <div class="welcome-text">¡Hola, <?php echo htmlspecialchars($biz['nombre']); ?>!</div>
                <div class="stats-pill">Tienes <strong><?php echo $activos; ?></strong> productos activos</div>
            </div>
            <div class="header-actions">
                <a href="pedidos.php" class="btn-header btn-cocina">
                    <i data-lucide="utensils-crossed" style="width:18px;"></i> 
                    <span class="d-none-mobile">Ir a Cocina</span>
                    <span class="d-only-mobile">Cocina</span>
                </a>
                <button class="btn-header config-btn" onclick="document.getElementById('configModal').style.display='block'" title="Configurar Negocio">
                    <i data-lucide="settings" style="width:20px;"></i>
                </button>
                <a href="../admin/logout.php" class="btn-header logout-btn" title="Cerrar Sesión">
                    <i data-lucide="log-out" style="width:20px;"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Interruptor de Estado Mortal -->
        <div class="status-card">
            <div class="status-info">
                <h4 id="status-label"><?php echo $biz['modulo_abierto'] ? 'ESTABLECIMIENTO ABIERTO' : 'ESTABLECIMIENTO CERRADO'; ?></h4>
                <p id="status-desc"><?php echo $biz['modulo_abierto'] ? 'Tus clientes pueden ver tu menú y hacer pedidos ahora mismo.' : 'Tu local está oculto. No recibirás pedidos hasta que abras.'; ?></p>
            </div>
            <label class="switch">
                <input type="checkbox" id="business-toggle" <?php echo $biz['modulo_abierto'] ? 'checked' : ''; ?>>
                <span class="slider"></span>
            </label>
        </div>

        <div class="section-header">
            <h2>Gestión de Menú</h2>
        </div>

        <!-- Lista de Productos -->
        <div class="product-grid" id="product-list">
            <?php foreach($productos as $p): ?>
            <div class="product-card <?php echo !$p['disponible'] ? 'paused' : ''; ?>" id="card-<?php echo $p['id']; ?>">
                <img src="../<?php echo $p['foto_url']; ?>" class="prod-img">
                <div class="prod-detail">
                    <h3><?php echo htmlspecialchars($p['nombre']); ?></h3>
                    <p>$<?php echo number_format($p['precio'], 2); ?></p>
                </div>
                <div class="prod-actions">
                    <button class="action-btn btn-pause <?php echo $p['disponible'] ? 'active' : ''; ?>" title="<?php echo $p['disponible'] ? 'Ocultar del menú' : 'Mostrar en menú'; ?>" 
                            onclick="toggleAvailability(<?php echo $p['id']; ?>, <?php echo $p['disponible']; ?>)">
                        <i data-lucide="<?php echo $p['disponible'] ? 'eye' : 'eye-off'; ?>" style="width:18px;"></i>
                    </button>
                    <button class="action-btn btn-delete" title="Eliminar definitivamente" onclick="deleteProduct(<?php echo $p['id']; ?>)">
                        <i data-lucide="trash-2" style="width:18px;"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button class="fab" onclick="document.getElementById('addModal').style.display='block'">
        <i data-lucide="plus"></i> Añadir Platillo
    </button>

    <!-- Modal Añadir -->
    <div id="addModal" class="modal">
        <div class="modal-body">
            <div style="display:flex; justify-content:space-between; margin-bottom:25px; align-items:center;">
                <h3 style="font-weight:800; font-size:1.4rem;">Nuevo Platillo</h3>
                <button onclick="document.getElementById('addModal').style.display='none'" style="background:#eee; border:none; width:36px; height:36px; border-radius:50%; font-size:1.2rem; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
            </div>
            <form id="addForm">
                <input type="hidden" name="action" value="add_product">
                <div class="form-group">
                    <label>Nombre del platillo</label>
                    <input type="text" name="nombre" placeholder="Ej: Hamburguesa Especial" required>
                </div>
                <div class="form-group">
                    <label>Precio ($)</label>
                    <input type="number" name="precio" step="0.01" placeholder="Ej: 145" required>
                </div>
                <div class="form-group">
                    <label>Descripción corta (INCLUYE)</label>
                    <textarea name="descripcion" placeholder="Cosas que ya vienen con el platillo..."></textarea>
                </div>
                <div class="form-group">
                    <label>Complementos adicionales (Opcionales con costo o elección)</label>
                    <div id="complements-container" style="display: flex; flex-direction: column; gap: 8px;">
                        <!-- Inputs dinámicos -->
                    </div>
                    <button type="button" onclick="addComplementInput()" style="background:#f0f0f0; border:1px dashed #ccc; padding:10px; border-radius:10px; width:100%; margin-top:8px; font-weight:600; cursor:pointer; font-size:0.8rem;">
                        + Agregar Complemento (Ej: Papas fritas, Soda)
                    </button>
                </div>
                <div class="form-group">
                    <label>Imagen del platillo</label>
                    <input type="file" name="foto" accept="image/*" required>
                </div>
                <button type="submit" class="btn-save" id="btnSave">Guardar Producto</button>
            </form>
        </div>
    </div>

    <!-- Modal Configuración -->
    <div id="configModal" class="modal">
        <div class="modal-body">
            <div style="display:flex; justify-content:space-between; margin-bottom:25px; align-items:center;">
                <h3 style="font-weight:800; font-size:1.4rem;">Datos del Negocio</h3>
                <button onclick="document.getElementById('configModal').style.display='none'" style="background:#eee; border:none; width:36px; height:36px; border-radius:50%; font-size:1.2rem; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
            </div>
            <form id="configForm">
                <input type="hidden" name="action" value="update_business_data">
                
                <div class="form-group">
                    <label>Logo del Negocio</label>
                    <?php if($biz['logo_url']): ?>
                        <img src="../<?php echo $biz['logo_url']; ?>" style="width:60px; height:60px; border-radius:10px; margin-bottom:10px; display:block;">
                    <?php endif; ?>
                    <input type="file" name="logo" accept="image/*">
                </div>

                <div class="form-group">
                    <label>Horario de Atención</label>
                    <input type="text" name="horario_atencion" value="<?php echo htmlspecialchars($biz['horario_atencion']); ?>" placeholder="Ej: Lun-Sab 9am - 10pm">
                </div>

                <div class="form-group">
                    <label>Dirección</label>
                    <textarea name="direccion" placeholder="Calle, número, colonia..."><?php echo htmlspecialchars($biz['direccion']); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Teléfono Contacto</label>
                        <input type="tel" name="telefono_contacto" value="<?php echo htmlspecialchars($biz['telefono_contacto']); ?>" placeholder="Ej: 8331234567">
                    </div>
                    <div class="form-group">
                        <label>WhatsApp Pedidos</label>
                        <input type="tel" name="whatsapp_pedidos" value="<?php echo htmlspecialchars($biz['whatsapp_pedidos']); ?>" placeholder="Ej: 5218331234567">
                    </div>
                </div>

                <div class="form-group">
                    <label>Opciones de Servicio</label>
                    <div class="checkbox-group">
                        <input type="checkbox" name="entrega_domicilio" id="chk_dom" <?php echo $biz['entrega_domicilio'] ? 'checked' : ''; ?>>
                        <label for="chk_dom">Entrega a Domicilio</label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" name="recolecta_pedidos" id="chk_rec" <?php echo $biz['recolecta_pedidos'] ? 'checked' : ''; ?>>
                        <label for="chk_rec">Recolecta en Tienda</label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" name="consumo_sucursal" id="chk_con" <?php echo $biz['consumo_sucursal'] ? 'checked' : ''; ?>>
                        <label for="chk_con">Consumo en Local</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ubicación (Coordenadas)</label>
                    <div class="form-row">
                        <input type="text" name="latitud" value="<?php echo htmlspecialchars($biz['latitud']); ?>" placeholder="Latitud">
                        <input type="text" name="longitud" value="<?php echo htmlspecialchars($biz['longitud']); ?>" placeholder="Longitud">
                    </div>
                    <small style="color:#666; font-size:0.7rem;">Puedes obtenerlas desde Google Maps</small>
                </div>

                <button type="submit" class="btn-save" id="btnSaveConfig">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // 1. Toggle Estado del Negocio
        document.getElementById('business-toggle').onchange = async (e) => {
            const isOpen = e.target.checked;
            document.getElementById('status-label').innerText = isOpen ? 'ABIERTO' : 'CERRADO';
            document.getElementById('status-desc').innerText = isOpen ? 'Los clientes pueden pedir ahora' : 'Tu local no aparece en el feed';
            
            const data = new FormData();
            data.append('action', 'toggle_business_status');
            data.append('status', isOpen ? 1 : 0);
            await fetch('api_menu.php', { method: 'POST', body: data });
        };

        // 2. toggle Disponibilidad Producto
        async function toggleAvailability(id, current) {
            const data = new FormData();
            data.append('action', 'toggle_availability');
            data.append('id', id);
            data.append('current', current);

            const res = await fetch('api_menu.php', { method: 'POST', body: data });
            const json = await res.json();
            if(json.status === 'success') {
                location.reload(); // Recarga simple para actualizar UI
            }
        }

        // 3. Eliminar Producto
        async function deleteProduct(id) {
            if(!confirm('¿Seguro que quieres eliminar este producto?')) return;
            
            const data = new FormData();
            data.append('action', 'delete_product');
            data.append('id', id);

            const res = await fetch('api_menu.php', { method: 'POST', body: data });
            if(res.ok) {
                document.getElementById('card-' + id).remove();
            }
        }

        // 4. Submit Añadir Producto
        document.getElementById('addForm').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnSave');
            btn.disabled = true;
            btn.innerText = 'Guardando...';

            const data = new FormData(e.target);
            const res = await fetch('api_menu.php', { method: 'POST', body: data });
            const json = await res.json();

            if(json.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + json.message);
                btn.disabled = false;
                btn.innerText = 'Guardar Producto';
            }
        };

        // 5. Submit Configuración Negocio
        document.getElementById('configForm').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnSaveConfig');
            btn.disabled = true;
            btn.innerText = 'Guardando...';

            const data = new FormData(e.target);
            const res = await fetch('api_menu.php', { method: 'POST', body: data });
            const json = await res.json();

            if(json.status === 'success') {
                alert('¡Datos actualizados!');
                location.reload();
            } else {
                alert('Error: ' + json.message);
                btn.disabled = false;
                btn.innerText = 'Guardar Cambios';
            }
        };

        // 6. Lógica de Complementos Dinámicos
        function addComplementInput() {
            const container = document.getElementById('complements-container');
            const div = document.createElement('div');
            div.style.display = 'flex';
            div.style.gap = '8px';
            div.style.alignItems = 'center';
            div.innerHTML = `
                <input type="text" name="complementos[]" placeholder="Ej: Salsa Extra" style="flex:2;">
                <input type="number" name="comp_precios[]" placeholder="$0.00" step="0.01" style="flex:1; padding: 15px; border-radius:12px; border:none; background:var(--gray);">
                <button type="button" onclick="this.parentElement.remove()" style="background:#fee2e2; color:#ef4444; border:none; padding:10px; border-radius:10px; cursor:pointer;">
                    <i data-lucide="x" style="width:16px;"></i>
                </button>
            `;
            container.appendChild(div);
            lucide.createIcons();
        }
    </script>
</body>
</html>
