<?php
// order-status.php - Tracking UI con polling en tiempo real
require_once 'conexion.php';

$order_id   = isset($_GET['id'])   ? intval($_GET['id'])      : 0;
$id_comercio = isset($_GET['uid']) ? intval($_GET['uid'])      : 1;
$tipo        = isset($_GET['type']) ? $_GET['type']            : 'delivery';

// Fetch merchant info
$merchant = null;
try {
    $stmt = $pdo->prepare("SELECT nombre, logo_url, telefono_contacto as telefono, whatsapp_pedidos FROM negocios WHERE id = ?");
    $stmt->execute([$id_comercio]);
    $merchant = $stmt->fetch();
} catch (Exception $e) {}

if (!$merchant) {
    $merchant = ['nombre' => 'Negocio YaLoPido', 'logo_url' => '', 'telefono' => '521234567890', 'whatsapp_pedidos' => '521234567890'];
}

// Fetch initial order status
$estado_inicial = 'pendiente';
if ($order_id) {
    try {
        $stmt2 = $pdo->prepare("SELECT estado, metodo_entrega FROM pedidos WHERE id = ?");
        $stmt2->execute([$order_id]);
        $row = $stmt2->fetch();
        if ($row) {
            $estado_inicial = $row['estado'];
            $tipo = $row['metodo_entrega'];
        }
    } catch (Exception $e) {}
}

$steps = [
    'pendiente'       => 0,
    'aceptado'        => 1,
    'en_preparacion'  => 2,
    'en_camino'       => 3,
    'entregado'       => 4,
    'cancelado'       => -1,
];
$current_step = $steps[$estado_inicial] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Seguimiento de Pedido | YaLoPido</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --blue:   #0b1c2e;
            --cyan:   #00aeef;
            --green:  #39b54a;
            --yellow: #ffd100;
            --red:    #e63946;
            --bg:     #f6f6f6;
            --white:  #ffffff;
            --text:   #0b1c2e;
            --sub:    #666;
            --border: #eeeeee;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background: var(--bg); min-height: 100vh; }

        /* Header */
        .top-bar {
            background: var(--white);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 100;
        }
        .top-bar h1 { font-size: 1.05rem; font-weight: 800; }
        .btn-back { background: none; border: none; cursor: pointer; display:flex; align-items:center; }

        /* Order ID Badge */
        .order-badge {
            background: var(--blue);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        /* Status Banner */
        .status-banner {
            margin: 20px 16px 0;
            padding: 20px;
            border-radius: 16px;
            background: var(--white);
            border: 1px solid var(--border);
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .status-icon {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: var(--cyan);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            flex-shrink: 0;
            transition: background 0.4s;
        }
        .status-icon.canceled { background: var(--red); }
        .status-icon.done     { background: var(--green); }
        .status-title { font-size: 1.15rem; font-weight: 800; }
        .status-sub   { font-size: 0.82rem; color: var(--sub); margin-top: 2px; }

        /* Timeline */
        .timeline-card {
            margin: 16px;
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border);
            padding: 24px 20px;
        }
        .timeline { position: relative; padding-left: 36px; }
        .timeline::before {
            content: '';
            position: absolute;
            left: 8px; top: 6px; bottom: 6px;
            width: 2px;
            background: var(--border);
        }
        .tl-item { position: relative; margin-bottom: 28px; }
        .tl-item:last-child { margin-bottom: 0; }

        .tl-dot {
            position: absolute; left: -44px; top: 2px;
            width: 18px; height: 18px; border-radius: 50%;
            background: #ddd;
            z-index: 2;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.4s, box-shadow 0.4s;
        }
        .tl-item.done   .tl-dot { background: var(--green); }
        .tl-item.active .tl-dot { background: var(--cyan); box-shadow: 0 0 0 5px rgba(0,174,239,0.18); }
        .tl-item.canceled .tl-dot { background: var(--red); }

        .tl-item.done::after {
            content: '';
            position: absolute;
            left: -36px; top: 20px;
            width: 2px; height: calc(100% + 8px);
            background: var(--green);
            z-index: 1;
        }

        .pulse-dot {
            width: 8px; height: 8px;
            background: white; border-radius: 50%;
            animation: pulse-anim 1.4s infinite;
        }
        @keyframes pulse-anim {
            0%   { transform: scale(0.8); opacity:1; }
            100% { transform: scale(2.2); opacity:0; }
        }

        .tl-title { font-size: 0.95rem; font-weight: 700; }
        .tl-sub   { font-size: 0.82rem; color: var(--sub); margin-top: 2px; }
        .tl-item.inactive .tl-title,
        .tl-item.inactive .tl-sub { color: #bbb; }

        /* Merchant card */
        .merchant-card {
            margin: 0 16px 16px;
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border);
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .m-logo { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; background: #eee; }
        .m-info { flex: 1; }
        .m-info h4 { font-size: 0.95rem; font-weight: 700; }
        .m-info p  { font-size: 0.8rem; color: var(--green); font-weight: 600; }
        .m-btns { display: flex; gap: 8px; }
        .circle-btn {
            width: 40px; height: 40px; border-radius: 50%;
            background: #f4f4f4;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none; color: var(--blue);
            transition: 0.2s;
        }
        .circle-btn:active { transform: scale(0.9); }

        /* Canceled state */
        .canceled-box {
            margin: 0 16px 16px;
            background: #fff1f1;
            border: 1px solid #ffc5c5;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            display: none;
        }
        .canceled-box h3 { font-size: 1rem; font-weight: 800; color: var(--red); margin-bottom: 6px; }
        .canceled-box p  { font-size: 0.85rem; color: #666; }

        /* Delivered state */
        .delivered-box {
            margin: 0 16px 16px;
            background: #f0fff4;
            border: 1px solid #b2f5c8;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            display: none;
        }
        .delivered-box h3 { font-size: 1.1rem; font-weight: 800; color: var(--green); margin-bottom: 4px; }

        /* Safety */
        .safety-card {
            margin: 0 16px 30px;
            background: #fffbe6;
            border: 1px solid #ffe58f;
            border-radius: 16px;
            padding: 18px;
        }
        .safety-card h4 { font-size: 0.85rem; font-weight: 700; color: #856404; margin-bottom: 10px; display:flex; gap:6px; align-items:center; }
        .safety-card li { font-size: 0.82rem; color: #856404; margin-bottom: 6px; display:flex; gap:6px; }

        /* Repartidor Card */
        .repartidor-card {
            margin: 0 16px 16px;
            background: var(--white);
            border-radius: 16px;
            border: 2px solid var(--cyan);
            padding: 18px;
            display: none; /* Se muestra via JS */
            align-items: center;
            gap: 14px;
            animation: slideIn 0.4s ease-out;
        }
        @keyframes slideIn { from { transform: translateY(20px); opacity:0; } to { transform: translateY(0); opacity:1; } }
        .rep-icon { 
            width: 50px; height: 50px; border-radius: 50%; 
            background: #e0f7ff; color: var(--cyan); 
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
        }
        .rep-info h4 { font-size: 0.9rem; font-weight: 800; }
        .rep-info p { font-size: 0.75rem; color: var(--sub); text-transform:uppercase; font-weight:700; }
    </style>
</head>
<body>

    <div class="top-bar">
        <button class="btn-back" onclick="window.location.href='index.html'">
            <i data-lucide="arrow-left" style="width:22px;"></i>
        </button>
        <h1>Tu Pedido</h1>
        <?php if ($order_id): ?>
        <span class="order-badge">#<?php echo $order_id; ?></span>
        <?php else: ?>
        <div style="width:40px;"></div>
        <?php endif; ?>
    </div>

    <!-- Status Banner -->
    <div class="status-banner">
        <div class="status-icon" id="status-icon">⏳</div>
        <div>
            <div class="status-title" id="status-title">Cargando...</div>
            <div class="status-sub" id="status-sub">Verificando estado de tu pedido</div>
        </div>
    </div>

    <!-- Canceled box (hidden by default) -->
    <div class="canceled-box" id="canceled-box">
        <h3>⚠️ Pedido Cancelado</h3>
        <p>Comunícate con el negocio para más información.</p>
    </div>

    <!-- Delivered box (hidden by default) -->
    <div class="delivered-box" id="delivered-box">
        <h3>🎉 ¡Pedido Entregado!</h3>
        <p style="font-size:0.85rem; color:#555;">¡Gracias por tu pedido! Que lo disfrutes.</p>
    </div>

    <!-- Repartidor Info (shown when en_camino) -->
    <div class="repartidor-card" id="repartidor-card">
        <div class="rep-icon"><i data-lucide="bike"></i></div>
        <div class="rep-info">
            <p>Tu Repartidor</p>
            <h4 id="rep-name-display">---</h4>
        </div>
        <div class="m-btns" style="margin-left:auto;">
            <a href="#" id="rep-call-btn" class="circle-btn" style="background:var(--cyan); color:white;">
                <i data-lucide="phone" style="width:18px;"></i>
            </a>
        </div>
    </div>

    <!-- Timeline -->
    <div class="timeline-card">
        <div class="timeline">
            <div class="tl-item done" id="step-0">
                <div class="tl-dot"><i data-lucide="check" style="width:10px; color:white;"></i></div>
                <div class="tl-title">Pedido Recibido</div>
                <div class="tl-sub">Tu orden llegó correctamente al negocio.</div>
            </div>
            <div class="tl-item inactive" id="step-1">
                <div class="tl-dot"></div>
                <div class="tl-title">Pedido Aceptado</div>
                <div class="tl-sub">El negocio confirmó tu pedido.</div>
            </div>
            <div class="tl-item inactive" id="step-2">
                <div class="tl-dot"></div>
                <div class="tl-title">En Preparación</div>
                <div class="tl-sub">Tu comida está siendo preparada con amor.</div>
            </div>
            <div class="tl-item inactive" id="step-3">
                <div class="tl-dot"></div>
                <div class="tl-title">
                    <?php 
                        if ($tipo === 'pickup') echo 'Listo para recoger';
                        elseif ($tipo === 'dinein') echo 'Mesa lista / Servido';
                        else echo 'En Camino';
                    ?>
                </div>
                <div class="tl-sub">
                    <?php 
                        if ($tipo === 'pickup') echo 'Ya puedes pasar por tu pedido.';
                        elseif ($tipo === 'dinein') echo 'Tu comida está lista en la mesa.';
                        else echo 'Tu repartidor está en camino.';
                    ?>
                </div>
            </div>
            <div class="tl-item inactive" id="step-4">
                <div class="tl-dot"></div>
                <div class="tl-title">Entregado</div>
                <div class="tl-sub">¡Pedido completado! Buen provecho 🎉</div>
            </div>
        </div>
    </div>

    <!-- Merchant Card -->
    <div class="merchant-card">
        <img src="<?php echo htmlspecialchars($merchant['logo_url'] ?? ''); ?>" class="m-logo"
             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($merchant['nombre']); ?>&background=0b1c2e&color=fff'">
        <div class="m-info">
            <h4><?php echo htmlspecialchars($merchant['nombre']); ?></h4>
            <p id="merchant-status-text">Preparando tu pedido...</p>
        </div>
        <div class="m-btns">
            <a href="tel:<?php echo htmlspecialchars($merchant['telefono'] ?? ''); ?>" class="circle-btn">
                <i data-lucide="phone" style="width:18px;"></i>
            </a>
            <a href="https://wa.me/<?php echo htmlspecialchars($merchant['whatsapp_pedidos'] ?? $merchant['telefono'] ?? ''); ?>" class="circle-btn">
                <i data-lucide="message-circle" style="width:18px;"></i>
            </a>
        </div>
    </div>

    <!-- Safety Tips -->
    <div class="safety-card">
        <h4><i data-lucide="shield-check" style="width:16px;"></i> Consejos de Seguridad</h4>
        <ul style="list-style:none; padding:0;">
            <li><i data-lucide="user-check" style="width:13px;"></i> Verifica que el repartidor mencione tu pedido.</li>
            <li><i data-lucide="banknote" style="width:13px;"></i> Si pagas en efectivo, trata de tener el monto exacto.</li>
            <li><i data-lucide="star" style="width:13px;"></i> Califica tu experiencia para ayudarnos a mejorar.</li>
        </ul>
    </div>

<script>
lucide.createIcons();

const ORDER_ID   = <?php echo $order_id ?: 0; ?>;
const TIPO       = '<?php echo $tipo; ?>';
const POLL_MS    = 5000; // Cada 5 segundos

const STATUS_MAP = {
    pendiente:      { step: 0, icon: '⏳', title: 'Esperando confirmación', sub: 'El negocio revisará tu pedido en breve.', merchantText: 'Revisando tu pedido...' },
    aceptado:       { step: 1, icon: '✅', title: 'Pedido Aceptado',         sub: 'El negocio confirmó y está por prepararlo.', merchantText: 'Pedido aceptado — iniciando preparación' },
    en_preparacion: { step: 2, icon: '👨‍🍳', title: 'En Preparación',        sub: 'Tu comida está siendo preparada.', merchantText: 'Preparando tu pedido ahora...' },
    en_camino:      { step: 3, icon: (TIPO === 'delivery' ? '🛵' : '🥡'), title: (TIPO === 'delivery' ? 'En Camino' : (TIPO === 'pickup' ? 'Listo para recoger' : 'Mesa lista')), sub: (TIPO === 'delivery' ? 'Tu repartidor va de camino.' : (TIPO === 'pickup' ? 'Ven por tu pedido.' : 'Tu mesa está lista.')), merchantText: (TIPO === 'delivery' ? 'En camino 🛵' : '¡Listo! Te esperamos.') },
    entregado:      { step: 4, icon: '🎉', title: '¡Pedido Entregado!',      sub: 'Buen provecho. ¡Gracias por tu pedido!', merchantText: '¡Pedido entregado con éxito!' },
    cancelado:      { step: -1, icon: '❌', title: 'Pedido Cancelado',        sub: 'Contáctanos si tienes dudas.', merchantText: 'Pedido cancelado' },
};

let currentEstado = '<?php echo $estado_inicial; ?>';
let pollInterval  = null;

function applyStatus(estado, repNombre = null, repTel = null) {
    const info = STATUS_MAP[estado] || STATUS_MAP['pendiente'];

    // Icon & banner
    const icon = document.getElementById('status-icon');
    icon.textContent = info.icon;
    icon.className = 'status-icon' +
        (estado === 'cancelado' ? ' canceled' : '') +
        (estado === 'entregado' ? ' done'     : '');

    document.getElementById('status-title').textContent = info.title;
    document.getElementById('status-sub').textContent   = info.sub;
    document.getElementById('merchant-status-text').textContent = info.merchantText;

    // Special boxes
    document.getElementById('canceled-box').style.display  = estado === 'cancelado' ? 'block' : 'none';
    document.getElementById('delivered-box').style.display = estado === 'entregado' ? 'block' : 'none';

    // Repartidor display
    const repCard = document.getElementById('repartidor-card');
    if (estado === 'en_camino' && repNombre) {
        repCard.style.display = 'flex';
        document.getElementById('rep-name-display').innerText = repNombre;
        if (repTel) {
            document.getElementById('rep-call-btn').href = 'tel:' + repTel;
            document.getElementById('rep-call-btn').style.display = 'flex';
        } else {
            document.getElementById('rep-call-btn').style.display = 'none';
        }
    } else if (estado === 'entregado' || estado === 'cancelado') {
        repCard.style.display = 'none';
    }

    // Timeline steps
    for (let i = 0; i <= 4; i++) {
        const el = document.getElementById('step-' + i);
        if (!el) continue;
        const dot = el.querySelector('.tl-dot');

        if (estado === 'cancelado') {
            el.className = 'tl-item' + (i === 0 ? ' canceled' : ' inactive');
            dot.innerHTML = i === 0 ? '<i data-lucide="x" style="width:10px; color:white;"></i>' : '';
        } else if (i < info.step) {
            el.className = 'tl-item done';
            dot.innerHTML = '<i data-lucide="check" style="width:10px; color:white;"></i>';
        } else if (i === info.step) {
            el.className = 'tl-item active';
            dot.innerHTML = '<div class="pulse-dot"></div>';
        } else {
            el.className = 'tl-item inactive';
            dot.innerHTML = '';
        }
    }
    lucide.createIcons();

    // Stop polling when terminal state
    if ((estado === 'entregado' || estado === 'cancelado') && pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
}

async function pollStatus() {
    if (!ORDER_ID) return;
    try {
        const res  = await fetch(`get_order_status.php?id=${ORDER_ID}`);
        const json = await res.json();
        if (json.status === 'success') {
            if (json.estado !== currentEstado || json.repartidor_nombre) {
                currentEstado = json.estado;
                applyStatus(json.estado, json.repartidor_nombre, json.repartidor_telefono);
            }
        }
    } catch (e) { /* silencioso */ }
}

// Init
applyStatus(currentEstado);
if (ORDER_ID && currentEstado !== 'entregado' && currentEstado !== 'cancelado') {
    pollInterval = setInterval(pollStatus, POLL_MS);
}
</script>
</body>
</html>
