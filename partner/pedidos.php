<?php
// partner/pedidos.php - Panel de Cocina / Gestión de Pedidos en Tiempo Real
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['rol'], ['socio', 'admin'])) {
    header('Location: ../admin/login.php');
    exit;
}

require_once '../conexion.php';
$id_negocio = $_SESSION['id_negocio'];

// Obtener nombre del negocio
$biz_stmt = $pdo->prepare("SELECT nombre FROM negocios WHERE id = ?");
$biz_stmt->execute([$id_negocio]);
$biz = $biz_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Cocina — <?php echo htmlspecialchars($biz['nombre'] ?? 'Panel'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --blue:  #0b1c2e;
            --green: #39b54a;
            --cyan:  #00aeef;
            --red:   #e63946;
            --amber: #f28b20;
            --bg:    #f4f4f4;
            --white: #ffffff;
            --border:#eee;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; -webkit-tap-highlight-color:transparent; }
        body { background: var(--bg); padding-bottom: 40px; }

        /* Header */
        .top-bar {
            background: var(--blue); color: white;
            padding: 18px 20px;
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 100;
        }
        .top-bar h1 { font-size: 1.1rem; font-weight: 800; }
        .top-bar a  { color: white; text-decoration: none; font-size: 0.8rem; opacity: 0.7; }
        .badge {
            background: var(--amber); color: white;
            padding: 3px 10px; border-radius: 20px;
            font-size: 0.75rem; font-weight: 800;
        }

        /* Filter Tabs */
        .filter-tabs {
            display: flex; gap: 8px;
            padding: 14px 16px;
            overflow-x: auto; scrollbar-width: none;
            background: var(--white);
            border-bottom: 1px solid var(--border);
        }
        .filter-tabs::-webkit-scrollbar { display:none; }
        .tab-btn {
            padding: 8px 16px; border-radius: 20px;
            border: 1.5px solid var(--border);
            background: var(--white); font-size: 0.8rem; font-weight: 700;
            cursor: pointer; white-space: nowrap;
            transition: 0.2s;
        }
        .tab-btn.active { background: var(--blue); color: white; border-color: var(--blue); }

        /* Orders grid */
        .orders-container { padding: 16px; display: flex; flex-direction: column; gap: 12px; }
        .empty-state {
            text-align: center; padding: 60px 20px; color: #bbb;
        }
        .empty-state h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 6px; }

        /* Order Card */
        .order-card {
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border);
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        .order-card.new { border-color: var(--amber); box-shadow: 0 0 0 2px rgba(242,139,32,0.2); }

        .card-head {
            padding: 14px 16px;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid var(--border);
        }
        .order-id    { font-size: 0.75rem; color: #999; font-weight: 600; }
        .order-zona  { font-size: 0.85rem; font-weight: 700; }
        .order-time  { font-size: 0.72rem; color: #aaa; margin-top: 2px; }
        .order-total { font-size: 1rem; font-weight: 800; color: var(--blue); }

        .status-chip {
            padding: 4px 10px; border-radius: 20px;
            font-size: 0.72rem; font-weight: 800; text-transform: uppercase;
        }
        .chip-pendiente      { background: #fff3cd; color: #856404; }
        .chip-aceptado       { background: #cce5ff; color: #004085; }
        .chip-en_preparacion { background: #d4edda; color: #155724; }
        .chip-en_camino      { background: #e2f4fb; color: #0c5460; }
        .chip-entregado      { background: #d4edda; color: #155724; }
        .chip-cancelado      { background: #f8d7da; color: #721c24; }

        /* Items list */
        .card-items { padding: 12px 16px; border-bottom: 1px solid var(--border); }
        .item-row {
            display: flex; justify-content: space-between;
            font-size: 0.87rem; padding: 4px 0;
        }
        .item-note { font-size: 0.75rem; color: #e05b00; font-style: italic; }
        .item-comp { font-size: 0.75rem; color: #666; }

        /* Action Buttons */
        .card-actions {
            padding: 12px 16px;
            display: flex; gap: 8px; flex-wrap: wrap;
        }
        .action-btn {
            flex: 1; min-width: 100px;
            padding: 10px 8px;
            border-radius: 10px; border: none;
            font-size: 0.8rem; font-weight: 700;
            cursor: pointer; transition: 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .btn-accept  { background: var(--blue);  color: white; }
        .btn-prep    { background: var(--amber); color: white; }
        .btn-road    { background: var(--cyan);  color: white; }
        .btn-done    { background: var(--green); color: white; }
        .btn-cancel  { background: #fff1f0; color: var(--red); border: 1px solid #ffd6d6; }
        .btn-print   { background: #f4f4f4; color: var(--blue); border: 1px solid #ddd; }

        /* Notification toast */
        #toast {
            position: fixed; bottom: 24px; left:50%; transform:translateX(-50%);
            background: var(--blue); color:white;
            padding: 12px 22px; border-radius: 50px;
            font-weight: 700; font-size: 0.9rem;
            z-index: 9999; opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none; white-space: nowrap;
        }
    </style>
</head>
<body>

<div class="top-bar">
    <div>
        <h1>🍳 Cocina — <?php echo htmlspecialchars($biz['nombre'] ?? ''); ?></h1>
        <div style="font-size:0.75rem; opacity:0.6; margin-top:2px;">Actualización automática cada 8s</div>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
        <button id="sound-btn" onclick="toggleSound()" style="background:rgba(255,255,255,0.1); color:white; border:none; padding:8px; border-radius:10px; display:flex; align-items:center; gap:6px; cursor:pointer;">
            <i data-lucide="volume-x" id="sound-icon" style="width:16px;"></i>
        </button>
        <span class="badge" id="pending-count">0</span>
        <a href="panel_socio.php">← Panel</a>
    </div>
</div>

<!-- Filters -->
<div class="filter-tabs">
    <button class="tab-btn active" onclick="setFilter('activos')">🔥 Activos</button>
    <button class="tab-btn" onclick="setFilter('pendiente')">⏳ Pendientes</button>
    <button class="tab-btn" onclick="setFilter('en_preparacion')">👨‍🍳 En Prep.</button>
    <button class="tab-btn" onclick="setFilter('en_camino')">🛵 En Camino</button>
    <button class="tab-btn" onclick="setFilter('entregado')">✅ Entregados</button>
    <button class="tab-btn" onclick="setFilter('todos')">📋 Todos</button>
</div>

<div class="orders-container" id="orders-list">
    <div class="empty-state"><h3>Cargando pedidos...</h3></div>
</div>

<div id="toast"></div>

<!-- Modal Repartidor -->
<div id="repartidor-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10000; align-items:center; justify-content:center;">
    <div style="background:white; padding:25px; border-radius:20px; width:90%; max-width:400px; box-shadow:0 15px 35px rgba(0,0,0,0.2);">
        <h3 style="margin-bottom:15px; font-weight:800;">Datos del Repartidor</h3>
        <div style="margin-bottom:15px;">
            <label style="display:block; font-size:0.75rem; font-weight:700; color:#666; margin-bottom:5px; text-transform:uppercase;">Nombre del Repartidor</label>
            <input type="text" id="rep-nombre" placeholder="Ej: Juan Pérez" style="width:100%; padding:12px; border-radius:10px; border:1px solid #ddd; outline:none; font-family:'Inter', sans-serif;">
        </div>
        <div style="margin-bottom:20px;">
            <label style="display:block; font-size:0.75rem; font-weight:700; color:#666; margin-bottom:5px; text-transform:uppercase;">WhatsApp/Tel (Opcional)</label>
            <input type="tel" id="rep-tel" placeholder="Ej: 521..." style="width:100%; padding:12px; border-radius:10px; border:1px solid #ddd; outline:none; font-family:'Inter', sans-serif;">
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="cerrarModalRepartidor()" style="flex:1; padding:12px; border-radius:10px; border:none; background:#eee; font-weight:700; cursor:pointer;">Cancelar</button>
            <button id="btn-confirmar-envio" style="flex:1; padding:12px; border-radius:10px; border:none; background:var(--blue); color:white; font-weight:700; cursor:pointer;">Confirmar Envío</button>
        </div>
    </div>
</div>

<script>
lucide.createIcons();

const NEGOCIO_ID = <?php echo intval($id_negocio); ?>;
let currentFilter = 'activos';
let allOrders     = [];
let knownIds      = new Set();
let soundEnabled  = false;

// Audio context or simple audio element
const notificationSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');

function toggleSound() {
    soundEnabled = !soundEnabled;
    const icon = document.getElementById('sound-icon');
    icon.setAttribute('data-lucide', soundEnabled ? 'volume-2' : 'volume-x');
    document.getElementById('sound-btn').style.background = soundEnabled ? 'var(--green)' : 'rgba(255,255,255,0.1)';
    lucide.createIcons();
    if (soundEnabled) {
        notificationSound.play().catch(e => console.log("User must interact first"));
        showToast("🔊 Sonido activado");
    } else {
        showToast("🔇 Sonido desactivado");
    }
}

const ESTADO_LABELS = {
    pendiente:      'Pendiente',
    aceptado:       'Aceptado',
    en_preparacion: 'En Preparación',
    en_camino:      'En Camino',
    entregado:      'Entregado',
    cancelado:      'Cancelado',
};

function setFilter(f) {
    currentFilter = f;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    renderOrders(allOrders);
}

function filterOrders(orders) {
    if (currentFilter === 'todos') return orders;
    if (currentFilter === 'activos') {
        return orders.filter(o => !['entregado','cancelado'].includes(o.estado));
    }
    return orders.filter(o => o.estado === currentFilter);
}

function timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr.replace(' ','T'))) / 1000);
    if (diff < 60)  return `hace ${diff}s`;
    if (diff < 3600) return `hace ${Math.floor(diff/60)}min`;
    return `hace ${Math.floor(diff/3600)}h`;
}

function actionButtons(order) {
    const id = order.id;
    const estado = order.estado;
    let btns = '';

    if (estado === 'pendiente') {
        btns += `<button class="action-btn btn-accept" onclick="cambiarEstado(${id},'aceptado')"><i data-lucide="check" style="width:14px;"></i> Aceptar</button>`;
        btns += `<button class="action-btn btn-cancel" onclick="cambiarEstado(${id},'cancelado')"><i data-lucide="x" style="width:14px;"></i> Cancelar</button>`;
    }
    if (estado === 'aceptado') {
        btns += `<button class="action-btn btn-prep" onclick="cambiarEstado(${id},'en_preparacion')"><i data-lucide="flame" style="width:14px;"></i> Preparando</button>`;
        btns += `<button class="action-btn btn-cancel" onclick="cambiarEstado(${id},'cancelado')">Cancelar</button>`;
    }
    if (estado === 'en_preparacion') {
        btns += `<button class="action-btn btn-road" onclick="cambiarEstado(${id},'en_camino')"><i data-lucide="bike" style="width:14px;"></i> Enviar</button>`;
    }
    if (estado === 'en_camino') {
        btns += `<button class="action-btn btn-done" onclick="cambiarEstado(${id},'entregado')"><i data-lucide="package-check" style="width:14px;"></i> Entregado</button>`;
    }
    return btns;
}

function renderOrders(orders) {
    const list = document.getElementById('orders-list');
    const filtered = filterOrders(orders);

    if (filtered.length === 0) {
        list.innerHTML = `<div class="empty-state"><h3>Sin pedidos aquí</h3><p>Actualizando automáticamente...</p></div>`;
        return;
    }

    list.innerHTML = filtered.map(order => {
        let items = [];
        try { items = JSON.parse(order.items_json); } catch(e) {}

        const isNew = order.estado === 'pendiente' && !knownIds.has(order.id);
        const chipClass = `chip-${order.estado}`;

        const itemsHtml = items.map(it => {
            const comps = it.complementos && it.complementos.length
                ? `<div class="item-comp">+ ${it.complementos.map(c=>c.nombre).join(', ')}</div>` : '';
            const nota = it.instrucciones
                ? `<div class="item-note">Nota: ${it.instrucciones}</div>` : '';
            return `<div class="item-row">
                <div><b>${it.qty}x</b> ${it.nombre}${comps}${nota}</div>
                <div>$${(parseFloat(it.precio)*it.qty).toFixed(2)}</div>
            </div>`;
        }).join('');

        const btns = actionButtons(order);

        return `
        <div class="order-card ${isNew ? 'new' : ''}" id="order-${order.id}">
            <div class="card-head">
                <div>
                    <div class="order-id">#${order.id} · ${timeAgo(order.created_at)}</div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div class="order-zona">${order.cliente_zona}</div>
                        <span style="font-size:0.65rem; padding:2px 6px; border-radius:4px; font-weight:800; background:#eee; color:#666; text-transform:uppercase;">
                            ${order.metodo_entrega === 'delivery' ? '🛵 Domicilio' : (order.metodo_entrega === 'pickup' ? '🥡 Para llevar' : '🍽️ Local')}
                        </span>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div class="order-total">$${parseFloat(order.total).toFixed(2)}</div>
                    <span class="status-chip ${chipClass}">${ESTADO_LABELS[order.estado]}</span>
                </div>
            </div>
            <div class="card-items">${itemsHtml}</div>
            ${btns ? `<div class="card-actions">${btns}</div>` : ''}
        </div>`;
    }).join('');

    lucide.createIcons();

    // Update pending badge
    const pendingCount = orders.filter(o => o.estado === 'pendiente').length;
    document.getElementById('pending-count').textContent = pendingCount;
}

async function loadOrders() {
    try {
        const res  = await fetch(`../get_pedidos_negocio.php?id=${NEGOCIO_ID}`);
        const json = await res.json();
        if (json.status === 'success') {
            // Detect new orders for notification
            const prevIds = new Set(allOrders.map(o => o.id));
            const newOrders = json.data.filter(o => !prevIds.has(o.id) && o.estado === 'pendiente');
            if (newOrders.length > 0 && allOrders.length > 0) {
                showToast(`🔔 ${newOrders.length} nuevo(s) pedido(s)`);
                if (soundEnabled) {
                    notificationSound.play().catch(e => console.error("Audio play failed", e));
                }
                // Update known IDs AFTER showing the highlight
                setTimeout(() => { newOrders.forEach(o => knownIds.add(o.id)); }, 4000);
            } else {
                newOrders.forEach(o => knownIds.add(o.id));
            }
            allOrders = json.data;
            renderOrders(allOrders);
        }
    } catch(e) {}
}

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.opacity = '1';
    setTimeout(() => { t.style.opacity = '0'; }, 3000);
}

let pendingOrderForRep = null;

function cerrarModalRepartidor() {
    document.getElementById('repartidor-modal').style.display = 'none';
    pendingOrderForRep = null;
}

async function cambiarEstado(orderId, nuevoEstado) {
    if (nuevoEstado === 'en_camino') {
        pendingOrderForRep = orderId;
        document.getElementById('repartidor-modal').style.display = 'flex';
        document.getElementById('rep-nombre').value = '';
        document.getElementById('rep-tel').value = '';
        
        document.getElementById('btn-confirmar-envio').onclick = async () => {
            const nombre = document.getElementById('rep-nombre').value.trim();
            const tel = document.getElementById('rep-tel').value.trim();
            
            if (!nombre) {
                alert('Por favor ingresa el nombre del repartidor');
                return;
            }
            
            await ejecutarCambioEstado(orderId, nuevoEstado, nombre, tel);
            cerrarModalRepartidor();
        };
        return;
    }
    
    await ejecutarCambioEstado(orderId, nuevoEstado);
}

async function ejecutarCambioEstado(orderId, nuevoEstado, repNombre = null, repTel = null) {
    const fd = new FormData();
    fd.append('order_id', orderId);
    fd.append('estado', nuevoEstado);
    if (repNombre) fd.append('repartidor_nombre', repNombre);
    if (repTel) fd.append('repartidor_telefono', repTel);

    try {
        const res  = await fetch('../update_order_status.php', { method:'POST', body:fd });
        const json = await res.json();
        if (json.status === 'success') {
            showToast(`Pedido #${orderId} → ${ESTADO_LABELS[nuevoEstado]}`);
            loadOrders();
        } else {
            alert('Error: ' + json.message);
        }
    } catch(e) { alert('Error de conexión'); }
}

// Initial load + auto-refresh
loadOrders();
setInterval(loadOrders, 8000);
</script>
</body>
</html>
