<?php
// login.php - Ultra Minimalist Security for YaLoPido (Bocao Style)
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['rol'] == 'admin' ? 'dashboard.php' : '../partner/panel_socio.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | YaLoPido</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bocao-orange: #f28b20; }
        body { margin:0; height:100vh; display:flex; align-items:center; justify-content:center; background:white; font-family:'Inter', sans-serif; color:#000; }
        .login-wrap { width:100%; max-width:320px; text-align:center; padding: 20px; }
        .logo-text { font-size:2rem; font-weight:800; margin-bottom:2rem; letter-spacing:-1px; }
        .logo-text span { color: var(--bocao-orange); }
        
        .form-group { margin-bottom: 1.5rem; text-align: left; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem; color: #666; }
        
        input { 
            width:100%; padding:12px 0; border:none; border-bottom: 2px solid #eee; 
            font-size:1rem; outline:none; transition:0.3s;
        }
        input:focus { border-bottom-color: var(--bocao-orange); }
        
        .btn-access { 
            width:100%; background: var(--bocao-orange); color:white; border:none; 
            padding:14px; border-radius:12px; font-weight:700; font-size:1rem; 
            cursor:pointer; margin-top:2rem; transition:0.3s;
        }
        .btn-access:hover { opacity: 0.9; transform: translateY(-2px); }
        
        .error-box { color: #ff3b30; font-size: 0.85rem; margin-top: 1rem; display: none; }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="logo-text">YaLo<span>Pido</span></div>
        <form id="loginForm">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" id="user" required>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" id="pass" required>
            </div>
            <div id="error" class="error-box">Credenciales inválidas</div>
            <button type="submit" class="btn-access" id="btn">Acceder</button>
        </form>
    </div>

    <script>
        document.getElementById('loginForm').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn');
            const err = document.getElementById('error');
            
            btn.disabled = true;
            btn.innerText = 'Cargando...';
            err.style.display = 'none';

            const data = new FormData();
            data.append('username', document.getElementById('user').value);
            data.append('password', document.getElementById('pass').value);

            try {
                const res = await fetch('auth_process.php', { method: 'POST', body: data });
                const json = await res.json();
                if (json.status === 'success') {
                    window.location.href = json.redirect;
                } else {
                    err.innerText = json.message;
                    err.style.display = 'block';
                    btn.disabled = false;
                    btn.innerText = 'Acceder';
                }
            } catch (e) {
                err.innerText = 'Error de conexión';
                err.style.display = 'block';
                btn.disabled = false;
                btn.innerText = 'Acceder';
            }
        };
    </script>
</body>
</html>
