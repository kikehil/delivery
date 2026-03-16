<?php
// admin/dashboard.php - YaLoPido Admin Panel
$require_admin = true;
require_once 'check_session.php';
require_once '../conexion.php';

// Branding Config
$brand_name = "YaLoPido";
$accent_color = "#ff6600"; // Bocao Orange 
$bg_color = "#f4f7f6";

// 1. Fetch Metrics
// Total Businesses (Pending vs Active)
$total_biz = $pdo->query("SELECT COUNT(*) FROM negocios")->fetchColumn();
$active_biz = $pdo->query("SELECT COUNT(*) FROM negocios WHERE estado = 'activo'")->fetchColumn();
$pending_biz = $pdo->query("SELECT COUNT(*) FROM negocios WHERE estado = 'pendiente'")->fetchColumn();

// Total Orders (Sum from stats)
$total_orders = $pdo->query("SELECT SUM(cantidad) FROM stats_pedidos")->fetchColumn() ?: 0;

// Unique Reach (Sum of zone interactions)
$total_reach = $pdo->query("SELECT SUM(cantidad) FROM stats_zonas")->fetchColumn() ?: 0;

// Hot Zone
$hot_zone_query = $pdo->query("SELECT z.nombre_colonia, SUM(s.cantidad) as total 
                               FROM stats_zonas s 
                               JOIN zonas z ON s.id_zona = z.id 
                               GROUP BY s.id_zona 
                               ORDER BY total DESC LIMIT 1")->fetch();
$hot_zone_name = $hot_zone_query ? $hot_zone_query['nombre_colonia'] : "N/A";

$partners = $pdo->query("SELECT n.*, z.nombre_colonia, u.username 
                         FROM negocios n 
                         LEFT JOIN zonas z ON n.id_zona_base = z.id 
                         LEFT JOIN usuarios u ON u.id_negocio = n.id AND u.rol = 'socio'
                         ORDER BY n.id DESC")->fetchAll();

$zones = $pdo->query("SELECT * FROM zonas ORDER BY nombre_colonia ASC")->fetchAll();

// Promociones
$promociones = $pdo->query("SELECT * FROM promociones ORDER BY orden ASC")->fetchAll();

// 3. Prepare Chart Data (Last 7 days)
$orders_chart = $pdo->query("SELECT fecha, SUM(cantidad) as total 
                             FROM stats_pedidos 
                             WHERE fecha >= CURDATE() - INTERVAL 7 DAY 
                             GROUP BY fecha ORDER BY fecha ASC")->fetchAll();

$zones_chart = $pdo->query("SELECT z.nombre_colonia, SUM(s.cantidad) as total 
                            FROM stats_zonas s 
                            JOIN zonas z ON s.id_zona = z.id 
                            GROUP BY s.id_zona")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | <?php echo $brand_name; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --brand: <?php echo $accent_color; ?>;
            --brand-light: #fff5ed;
            --bg: <?php echo $bg_color; ?>;
            --text: #1e293b;
            --text-muted: #64748b;
            --card-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: 260px; background: white; border-right: 1px solid #e2e8f0; padding: 2rem 1.5rem; display: flex; flex-direction: column; }
        .logo-area { display: flex; align-items: center; gap: 0.8rem; margin-bottom: 3rem; }
        .logo-icon { background: var(--brand); color: white; padding: 0.5rem; border-radius: 12px; }
        .brand-text { font-weight: 800; font-size: 1.25rem; letter-spacing: -0.5px; }

        .nav-link { display: flex; align-items: center; gap: 1rem; padding: 0.9rem 1.25rem; color: var(--text-muted); text-decoration: none; border-radius: 12px; margin-bottom: 0.6rem; font-weight: 500; transition: 0.2s; }
        .nav-link i { width: 22px; height: 22px; }
        .nav-link.active { background: var(--brand-light); color: var(--brand); font-weight: 700; }
        .nav-link:hover:not(.active) { background: #f8fafc; color: var(--text); }

        /* Main Content */
        .main { flex: 1; padding: 2.5rem; overflow-y: auto; background: var(--bg); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; }
        .title { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.5px; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 16px; box-shadow: var(--card-shadow); border: 1px solid #f1f5f9; }
        .stat-label { color: var(--text-muted); font-size: 0.85rem; font-weight: 500; margin-bottom: 0.5rem; display: block; }
        .stat-value { font-size: 1.75rem; font-weight: 800; }
        .stat-sub { font-size: 0.75rem; margin-top: 0.5rem; color: var(--brand); font-weight: 600; }

        /* Charts Section */
        .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
        .chart-card { background: white; padding: 1.5rem; border-radius: 16px; box-shadow: var(--card-shadow); }
        .chart-title { font-size: 1rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .chart-container { position: relative; height: 300px; width: 100%; }

        /* Table */
        .table-card { background: white; border-radius: 16px; box-shadow: var(--card-shadow); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1rem; background: #f8fafc; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }
        td { padding: 1rem; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
        .tr-hover:hover { background: #f8fafc; }

        .status-badge { padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .status-active { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef9c3; color: #854d0e; }

        .action-btn { background: none; border: 1px solid #e2e8f0; padding: 0.4rem; border-radius: 6px; cursor: pointer; color: var(--text-muted); transition: 0.2s; }
        .action-btn:hover { background: var(--brand); color: white; border-color: var(--brand); }

        @media (max-width: 1024px) {
            .charts-grid { grid-template-columns: 1fr; }
            .sidebar { width: 80px; padding: 2rem 0.5rem; }
            .brand-text, .nav-label { display: none; }
            .logo-area { justify-content: center; }
            .nav-link { justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-area">
            <div class="logo-icon"><i data-lucide="utensils"></i></div>
            <span class="brand-text"><?php echo $brand_name; ?></span>
        </div>
        <nav>
            <a href="dashboard.php" class="nav-link active">
                <i data-lucide="layout-dashboard"></i> <span class="nav-label">Dashboard</span>
            </a>
            <a href="#negocios" class="nav-link" onclick="alert('Módulo de gestión extendida en desarrollo')">
                <i data-lucide="store"></i> <span class="nav-label">Negocios</span>
            </a>
            <a href="#reportes" class="nav-link" onclick="alert('Módulo de reportes detallados en desarrollo')">
                <i data-lucide="bar-chart-3"></i> <span class="nav-label">Reportes</span>
            </a>
            <a href="#config" class="nav-link" onclick="alert('Configuración del sistema en desarrollo')">
                <i data-lucide="settings"></i> <span class="nav-label">Configuración</span>
            </a>
            <div style="margin-top:auto; padding-top:2rem; border-top:1px solid #f1f5f9;">
                <a href="logout.php" class="nav-link" style="color:#e11d48;">
                    <i data-lucide="log-out"></i> <span class="nav-label">Cerrar Sesión</span>
                </a>
            </div>
        </nav>
    </div>

    <div class="main">
        <div class="header">
            <h1 class="title">Panel de Administración</h1>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500;">Pánuco Hub</span>
                <div style="width: 44px; height: 44px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                    <i data-lucide="user" style="width: 20px;"></i>
                </div>
            </div>
        </div>

        <!-- Metric Counters -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total Negocios</span>
                <div class="stat-value"><?php echo $total_biz; ?></div>
                <div class="stat-sub"><?php echo $active_biz; ?> activos / <?php echo $pending_biz; ?> pendientes</div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Pedidos</span>
                <div class="stat-value"><?php echo number_format($total_orders); ?></div>
                <div class="stat-sub">Volumen proyectado</div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Alcance Clientes</span>
                <div class="stat-value"><?php echo $total_reach; ?></div>
                <div class="stat-sub">Zonas seleccionadas</div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Zona más Caliente</span>
                <div class="stat-value" style="font-size: 1.2rem;"><?php echo $hot_zone_name; ?></div>
                <div class="stat-sub">Mayor interacción</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-title">Pedidos por día <i data-lucide="trending-up" style="color:var(--brand)"></i></div>
                <div class="chart-container">
                    <canvas id="ordersChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-title">Distribución por Zonas</div>
                <div class="chart-container">
                    <canvas id="zonesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- CRUD Table -->
        <div class="table-card">
            <div style="padding: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1.1rem; font-weight: 700;">Gestión de Socios</h3>
                <button onclick="openAddPartnerModal()" style="background: var(--brand); color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                    <i data-lucide="plus" style="width:16px;"></i> Registrar Socio
                </button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Negocio</th>
                        <th>WhatsApp</th>
                        <th>Categoría</th>
                        <th>Zona</th>
                        <th>Plan</th>
                        <th>Estado</th>
                        <th>Acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($partners as $p): ?>
                    <tr class="tr-hover">
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($p['nombre']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($p['nombre_responsable'] ?? 'N/A'); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($p['whatsapp_pedidos'] ?: $p['telefono_contacto']); ?></td>
                        <td>
                            <select onchange="updateCategory(<?php echo $p['id']; ?>, this.value)" style="padding:4px; border-radius:4px; border:1px solid #e2e8f0; font-size:0.8rem; background:var(--bg);">
                                <?php 
                                    $cats = ['Antojitos', 'Hotdogs/Hamburguesas', 'Pasteles', 'Pizza', 'Pollos', 'Snack', 'Tacos/Tortas', 'Otros'];
                                    foreach($cats as $c) {
                                        $sel = ($p['categoria'] == $c) ? 'selected' : '';
                                        echo "<option value=\"$c\" $sel>$c</option>";
                                    }
                                ?>
                            </select>
                        </td>
                        <td><?php echo htmlspecialchars($p['nombre_colonia'] ?? 'Sin zona'); ?></td>
                        <td><span style="text-transform: capitalize;"><?php echo $p['plan']; ?></span></td>
                        <td>
                            <span class="status-badge <?php echo $p['estado'] == 'activo' ? 'status-active' : 'status-pending'; ?>">
                                <?php echo ucfirst($p['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($p['username']): ?>
                                <span style="font-size: 0.8rem; color: var(--brand); font-weight: 600;">@<?php echo $p['username']; ?></span>
                            <?php else: ?>
                                <span style="font-size: 0.75rem; color: #ef4444; font-weight: 600; background: #fee2e2; padding: 2px 8px; border-radius: 6px;">Sin cuenta</span>
                            <?php endif; ?>
                        </td>
                        <td id="row-<?php echo $p['id']; ?>">
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="action-btn" title="Contactar" onclick="window.open('https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $p['whatsapp_pedidos'] ?: $p['telefono_contacto']); ?>')">
                                    <i data-lucide="phone" style="width:14px;"></i>
                                </button>
                                <button class="action-btn" title="Alternar Estado" onclick="togglePartner(<?php echo $p['id']; ?>, '<?php echo $p['estado']; ?>')">
                                    <i data-lucide="toggle-right" style="width:14px;"></i>
                                </button>
                                <?php if (!$p['username']): ?>
                                <button class="action-btn" title="Asignar Usuario" onclick="prepareAssignModal(<?php echo $p['id']; ?>, '<?php echo addslashes($p['nombre']); ?>')" style="color: var(--brand); background: #fff7ed; border:1px solid #ffedd5;">
                                    <i data-lucide="key" style="width:14px;"></i>
                                </button>
                                <?php endif; ?>
                                <button class="action-btn" title="Eliminar" onclick="deletePartner(<?php echo $p['id']; ?>)">
                                    <i data-lucide="trash-2" style="width:14px;"></i>
                                </button>
                                <?php if ($p['username']): ?>
                                <button class="action-btn" title="Cambiar Contraseña" onclick="prepareChangePassModal(<?php echo $p['id']; ?>, '<?php echo addslashes($p['nombre']); ?>')">
                                    <i data-lucide="lock" style="width:14px;"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Promociones CRUD Table -->
        <div class="table-card" style="margin-top: 2rem;" id="modulo-promociones">
            <div style="padding: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1.1rem; font-weight: 700;">Gestión de Promociones (Banners)</h3>
                <button onclick="openAddPromoModal()" style="background: var(--brand); color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                    <i data-lucide="image-plus" style="width:16px;"></i> Nueva Promo
                </button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">Imagen</th>
                        <th>Etiqueta</th>
                        <th>Título / Subtítulo</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($promociones)): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 2rem;">No hay promociones configuradas.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($promociones as $promo): ?>
                    <tr class="tr-hover">
                        <td>
                            <img src="<?php echo htmlspecialchars($promo['imagen_url']); ?>" style="width: 60px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
                        </td>
                        <td><span style="background: #00aeef; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; text-transform:uppercase;"><?php echo htmlspecialchars($promo['etiqueta']); ?></span></td>
                        <td>
                            <div style="font-weight: 700;"><?php echo htmlspecialchars($promo['titulo']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($promo['subtitulo']); ?></div>
                        </td>
                        <td><?php echo $promo['orden']; ?></td>
                        <td>
                            <span class="status-badge <?php echo $promo['activa'] ? 'status-active' : 'status-pending'; ?>">
                                <?php echo $promo['activa'] ? 'Activa' : 'Inactiva'; ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="action-btn" title="Alternar Estado" onclick="togglePromo(<?php echo $promo['id']; ?>, <?php echo $promo['activa']; ?>)">
                                    <i data-lucide="<?php echo $promo['activa'] ? 'eye-off' : 'eye'; ?>" style="width:14px;"></i>
                                </button>
                                <button class="action-btn" title="Eliminar" onclick="deletePromo(<?php echo $promo['id']; ?>)">
                                    <i data-lucide="trash-2" style="width:14px; color:#e11d48;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Registrar Socio -->
    <div id="addPartnerModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
        <div class="modal-content" style="background:white; width:100%; max-width:500px; border-radius:20px; padding:2.5rem; position:relative; animation: slideIn 0.3s ease-out;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <h2 style="font-size:1.5rem; font-weight:800; letter-spacing:-0.5px;">Registrar Nuevo Socio</h2>
                <button onclick="closeAddPartnerModal()" style="background:none; border:none; color:var(--text-muted); cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            
            <form id="addPartnerForm">
                <input type="hidden" name="action" value="add_partner">
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
                    <div>
                        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">Nombre Negocio</label>
                        <input type="text" name="biz_name" required style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">Nombre Dueño</label>
                        <input type="text" name="owner_name" required style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
                    <div>
                        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">Categoría</label>
                        <select name="category" required style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                            <option value="Antojitos">Antojitos</option>
                            <option value="Hotdogs/Hamburguesas">Hotdogs/Hamburguesas</option>
                            <option value="Pasteles">Pasteles</option>
                            <option value="Pizza">Pizza</option>
                            <option value="Pollos">Pollos</option>
                            <option value="Snack">Snack</option>
                            <option value="Tacos/Tortas">Tacos/Tortas</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">WhatsApp Pedidos</label>
                        <input type="tel" name="whatsapp" required style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;" placeholder="528461234567">
                    </div>
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">Zona</label>
                    <select name="zone_id" required style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <?php foreach($zones as $z): ?>
                            <option value="<?php echo $z['id']; ?>"><?php echo $z['nombre_colonia']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="border-top:1px dashed #e2e8f0; padding-top:1.5rem; margin-bottom:1.5rem;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                        <div>
                            <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">Usuario</label>
                            <input type="text" name="username" required style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;" placeholder="Ej: taco_king">
                        </div>
                        <div>
                            <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">Contraseña</label>
                            <input type="password" name="password" required style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        </div>
                    </div>
                </div>

                <button type="submit" style="width:100%; background:#000; color:white; border:none; padding:1rem; border-radius:12px; font-weight:700; font-size:1rem; cursor:pointer;" id="submitBtn">
                    Crear Cuenta de Socio
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Asignar Credenciales a Socio Existente -->
    <div id="assignCredentialsModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
        <div class="modal-content" style="background:white; width:100%; max-width:400px; border-radius:20px; padding:2.5rem; position:relative;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <h2 style="font-size:1.3rem; font-weight:800; letter-spacing:-0.5px;">Acceso para <span id="assign-biz-name"></span></h2>
                <button onclick="closeAssignModal()" style="background:none; border:none; color:var(--text-muted); cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            
            <form id="assignCredentialsForm">
                <input type="hidden" name="action" value="assign_credentials">
                <input type="hidden" name="id_negocio" id="assign-id-negocio">
                
                <div style="margin-bottom:1.2rem;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">Usuario</label>
                    <input type="text" name="username" required style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;" placeholder="Ej: taco_king">
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">Contraseña</label>
                    <input type="password" name="password" required style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                </div>

                <button type="submit" style="width:100%; background:var(--brand); color:white; border:none; padding:1rem; border-radius:12px; font-weight:700; font-size:1rem; cursor:pointer;" id="assignBtn">
                    Activar Acceso
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Cambiar Contraseña -->
    <div id="changePassModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
        <div class="modal-content" style="background:white; width:100%; max-width:400px; border-radius:20px; padding:2.5rem; position:relative;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <h2 style="font-size:1.3rem; font-weight:800; letter-spacing:-0.5px;">Nueva Contraseña para <span id="pass-biz-name"></span></h2>
                <button onclick="closeChangePassModal()" style="background:none; border:none; color:var(--text-muted); cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            
            <form id="changePassForm">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="id_negocio" id="pass-id-negocio">
                
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">Nueva Contraseña</label>
                    <input type="password" name="password" required style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;" placeholder="Mínimo 6 caracteres">
                </div>

                <button type="submit" style="width:100%; background:#000; color:white; border:none; padding:1rem; border-radius:12px; font-weight:700; font-size:1rem; cursor:pointer;" id="passBtn">
                    Actualizar Contraseña
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Registrar Promoción -->
    <div id="addPromoModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
        <div class="modal-content" style="background:white; width:100%; max-width:500px; border-radius:20px; padding:2.5rem; position:relative; animation: slideIn 0.3s ease-out;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <h2 style="font-size:1.5rem; font-weight:800; letter-spacing:-0.5px;">Nueva Promoción</h2>
                <button onclick="closeAddPromoModal()" style="background:none; border:none; color:var(--text-muted); cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            
            <form id="addPromoForm">
                <input type="hidden" name="action" value="add_promo">
                
                <div style="margin-bottom:1.2rem;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">URL de la Imagen</label>
                    <input type="url" name="imagen_url" required style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;" placeholder="https://ejemplo.com/imagen.jpg">
                </div>
                
                <div style="display:grid; grid-template-columns: 1fr 2fr; gap:1rem; margin-bottom:1.2rem;">
                    <div>
                        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">Etiqueta</label>
                        <input type="text" name="etiqueta" style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;" placeholder="Ej: Nuevo">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">Título</label>
                        <input type="text" name="titulo" required style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;" placeholder="Ej: Envío Gratis">
                    </div>
                </div>

                <div style="margin-bottom:1.2rem;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">Subtítulo (Opcional)</label>
                    <input type="text" name="subtitulo" style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;" placeholder="En todos los comercios seleccionados">
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase;">Orden de Aparición (1, 2, 3...)</label>
                    <input type="number" name="orden" value="1" min="1" required style="width:100%; padding:0.8rem; border:1px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                </div>

                <button type="submit" style="width:100%; background:var(--brand); color:white; border:none; padding:1rem; border-radius:12px; font-weight:700; font-size:1rem; cursor:pointer;" id="submitPromoBtn">
                    Guardar Promoción
                </button>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons({
            attrs: {
                'stroke-width': 2.5,
                'class': 'lucide-icon'
            }
        });

        // Chart 1: Orders Bar
        const ctxOrders = document.getElementById('ordersChart').getContext('2d');
        new Chart(ctxOrders, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($orders_chart, 'fecha')); ?>,
                datasets: [{
                    label: 'Pedidos',
                    data: <?php echo json_encode(array_column($orders_chart, 'total')); ?>,
                    backgroundColor: 'rgba(255, 102, 0, 0.8)',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                    x: { grid: { display: false } }
                },
                maxBarThickness: 50 // Prevent bars from being too wide
            }
        });

        // Chart 2: Zones Pie
        const ctxZones = document.getElementById('zonesChart').getContext('2d');
        new Chart(ctxZones, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($zones_chart, 'nombre_colonia')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($zones_chart, 'total')); ?>,
                    backgroundColor: ['#ff6600', '#0b1c2e', '#00aeef', '#39b54a', '#ffd100', '#64748b'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } },
                cutout: '70%'
            }
        });

        // --- Admin Functions ---
        async function updateCategory(id, newCat) {
            const data = new FormData();
            data.append('action', 'update_category');
            data.append('id', id);
            data.append('category', newCat);

            try {
                const res = await fetch('admin_actions.php', { method: 'POST', body: data });
                const json = await res.json();
                if(json.status !== 'success') {
                    alert('Error al actualizar categoría');
                } else {
                    // Optional toast indicator
                }
            } catch(e) { alert('Error al procesar acción'); }
        }

        async function togglePartner(id, current) {
            const data = new FormData();
            data.append('action', 'toggle_partner_status');
            data.append('id', id);
            data.append('current_status', current);

            try {
                const res = await fetch('admin_actions.php', { method: 'POST', body: data });
                const json = await res.json();
                if(json.status === 'success') {
                    location.reload(); // Refresh to update badges
                }
            } catch(e) { alert('Error al procesar acción'); }
        }

        async function deletePartner(id) {
            if(!confirm('¿Seguro que deseas eliminar este socio? No se puede deshacer.')) return;
            const data = new FormData();
            data.append('action', 'delete_partner');
            data.append('id', id);

            try {
                const res = await fetch('admin_actions.php', { method: 'POST', body: data });
                const json = await res.json();
                if(json.status === 'success') {
                    location.reload();
                }
            } catch(e) { alert('Error al eliminar'); }
        }

        // --- Partner Addition ---
        function openAddPartnerModal() {
            document.getElementById('addPartnerModal').style.display = 'flex';
        }

        function closeAddPartnerModal() {
            document.getElementById('addPartnerModal').style.display = 'none';
        }

        document.getElementById('addPartnerForm').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerText = 'Creando...';

            try {
                const res = await fetch('admin_actions.php', {
                    method: 'POST',
                    body: new FormData(e.target)
                });
                const json = await res.json();
                
                if(json.status === 'success') {
                    location.reload();
                } else {
                    alert('Error: ' + json.message);
                    btn.disabled = false;
                    btn.innerText = 'Crear Cuenta de Socio';
                }
            } catch(e) {
                alert('No se pudo conectar con el servidor.');
                btn.disabled = false;
                btn.innerText = 'Cerrar Sesión';
            }
        };

        // --- Assignment Logic ---
        function prepareAssignModal(id, name) {
            document.getElementById('assign-id-negocio').value = id;
            document.getElementById('assign-biz-name').innerText = name;
            document.getElementById('assignCredentialsModal').style.display = 'flex';
        }

        function closeAssignModal() {
            document.getElementById('assignCredentialsModal').style.display = 'none';
        }

        document.getElementById('assignCredentialsForm').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('assignBtn');
            btn.disabled = true;
            btn.innerText = 'Asignando...';

            try {
                const res = await fetch('admin_actions.php', {
                    method: 'POST',
                    body: new FormData(e.target)
                });
                const json = await res.json();
                if(json.status === 'success') location.reload();
                else {
                    alert('Error: ' + json.message);
                    btn.disabled = false;
                    btn.innerText = 'Activar Acceso';
                }
            } catch(e) { alert('Error de conexión'); btn.disabled = false; btn.innerText = 'Activar Acceso'; }
        };
        // --- Promociones Config ---
        function openAddPromoModal() {
            document.getElementById('addPromoModal').style.display = 'flex';
        }

        function closeAddPromoModal() {
            document.getElementById('addPromoModal').style.display = 'none';
        }

        document.getElementById('addPromoForm').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submitPromoBtn');
            btn.disabled = true;
            btn.innerText = 'Guardando...';

            try {
                const res = await fetch('admin_actions.php', { method: 'POST', body: new FormData(e.target) });
                const json = await res.json();
                if(json.status === 'success') location.reload();
                else alert('Error: ' + json.message);
            } catch(e) { alert('Error de conexión'); }
            
            btn.disabled = false;
            btn.innerText = 'Guardar Promoción';
        };

        async function togglePromo(id, current) {
            const data = new FormData();
            data.append('action', 'toggle_promo');
            data.append('id', id);
            data.append('current_status', current);

            try {
                const res = await fetch('admin_actions.php', { method: 'POST', body: data });
                const json = await res.json();
                if(json.status === 'success') location.reload();
            } catch(e) { alert('Error al procesar acción'); }
        }

        async function deletePromo(id) {
            if(!confirm('¿Seguro que deseas eliminar esta promoción?')) return;
            const data = new FormData();
            data.append('action', 'delete_promo');
            data.append('id', id);

            try {
                const res = await fetch('admin_actions.php', { method: 'POST', body: data });
                const json = await res.json();
                if(json.status === 'success') location.reload();
            } catch(e) { alert('Error al eliminar'); }
        }

        // --- Change Password Logic ---
        function prepareChangePassModal(id, name) {
            document.getElementById('pass-id-negocio').value = id;
            document.getElementById('pass-biz-name').innerText = name;
            document.getElementById('changePassModal').style.display = 'flex';
        }

        function closeChangePassModal() {
            document.getElementById('changePassModal').style.display = 'none';
        }

        document.getElementById('changePassForm').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('passBtn');
            btn.disabled = true;
            btn.innerText = 'Actualizando...';

            try {
                const res = await fetch('admin_actions.php', {
                    method: 'POST',
                    body: new FormData(e.target)
                });
                const json = await res.json();
                if(json.status === 'success') {
                    alert('Contraseña actualizada con éxito');
                    closeChangePassModal();
                } else {
                    alert('Error: ' + json.message);
                }
            } catch(e) { alert('Error de conexión'); }
            
            btn.disabled = false;
            btn.innerText = 'Actualizar Contraseña';
        };
    </script>
</body>
</html>
