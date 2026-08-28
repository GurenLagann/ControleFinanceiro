<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0f0f1a">
    <script>document.documentElement.classList.add('js-loaded');</script>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ config('app.name', 'Controle Financeiro') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            -webkit-tap-highlight-color: transparent;
            box-sizing: border-box;
        }
        .card, .btn, .nav-link, .form-control, .form-select,
        .sidebar, .main-content, .bottom-nav-item {
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.15s ease;
        }
        button, a, .btn, .nav-link, label, .categoria-pill {
            touch-action: manipulation;
        }
        @media (hover: none) {
            .card:hover {
                transform: none !important;
                box-shadow: none !important;
            }
        }
        body {
            font-family: 'IBM Plex Sans', system-ui, sans-serif;
            background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 50%, #0d1421 100%);
            min-height: 100vh;
            color: #e4e4e4;
        }

        /* Layout com Sidebar */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: rgba(10,10,20,0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255,255,255,0.08);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, width 0.3s ease;
        }
        .sidebar.collapsed {
            width: 70px;
        }
        .sidebar.collapsed .nav-text,
        .sidebar.collapsed .sidebar-header span,
        .sidebar.collapsed .sidebar-footer small {
            display: none;
        }
        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 12px;
        }
        .sidebar.collapsed .nav-link i {
            margin-right: 0;
            font-size: 1.2rem;
        }
        .sidebar.hidden {
            transform: translateX(-100%);
        }
        .sidebar:not(.hidden) {
            opacity: 1 !important;
        }
        .sidebar.hidden ~ .main-content {
            margin-left: 0;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-header i {
            font-size: 1.5rem;
            color: #00ff88;
        }
        .sidebar-header span {
            font-weight: 600;
            font-size: 1.1rem;
            white-space: nowrap;
        }
        .btn-hide-sidebar {
            background: transparent;
            border: none;
            color: #999;
            cursor: pointer;
            padding: 5px 8px;
            border-radius: 5px;
            transition: background-color 0.2s ease, color 0.2s ease;
            margin-left: auto;
        }
        .btn-hide-sidebar:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .sidebar.collapsed .btn-hide-sidebar {
            display: none;
        }

        .sidebar-nav {
            flex: 1;
            padding: 15px 0;
            overflow-y: auto;
        }
        .sidebar-nav .nav-section {
            padding: 10px 20px 5px;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #999;
        }
        .sidebar.collapsed .nav-section {
            display: none;
        }
        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #aaa;
            text-decoration: none;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
            border-left: 3px solid transparent;
        }
        .sidebar-nav .nav-link:hover {
            background: rgba(55,66,250,0.15);
            color: #fff;
            border-left-color: rgba(55,66,250,0.5);
        }
        .sidebar-nav .nav-link.active {
            background: rgba(0,255,136,0.1);
            color: #00ff88;
            border-left-color: #00ff88;
        }
        .sidebar-nav .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        .sidebar-nav .nav-text {
            white-space: nowrap;
        }
        .sidebar-nav .nav-badge {
            margin-left: auto;
            background: rgba(255,71,87,0.2);
            color: #ff4757;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
        }

        .sidebar-footer {
            padding: 15px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            transition: margin-left 0.3s ease;
        }
        .sidebar.collapsed ~ .main-content {
            margin-left: 70px;
        }

        /* Top Bar */
        .topbar {
            background: rgba(10,10,20,0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 12px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-toggle-sidebar {
            background: transparent;
            border: none;
            color: #aaa;
            font-size: 1.3rem;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.2s ease, color 0.2s ease;
            position: relative;
        }
        .btn-toggle-sidebar:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .btn-toggle-sidebar.highlight {
            color: #00ff88;
        }
        .btn-toggle-sidebar.highlight::after {
            content: '';
            position: absolute;
            top: 4px;
            right: 4px;
            width: 6px;
            height: 6px;
            background: #00ff88;
            border-radius: 50%;
            pointer-events: none;
        }

        /* Content Area */
        .content-area {
            padding: 25px;
            overscroll-behavior: contain;
        }

        /* Mobile */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
                width: 260px !important;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .sidebar.collapsed {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0 !important;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1055;
            }
            .sidebar-overlay.show {
                display: block;
            }
            .sidebar {
                z-index: 1060 !important;
            }
            .bottom-nav {
                display: flex;
            }
            .content-area {
                padding-bottom: calc(72px + env(safe-area-inset-bottom, 0px));
            }
        }

        /* Responsividade Mobile */
        @media (max-width: 767px) {
            .content-area {
                padding: 15px 10px;
                padding-bottom: calc(72px + env(safe-area-inset-bottom, 0px));
            }
            .topbar {
                padding: 10px 15px;
            }
            .card-body {
                padding: 12px;
            }
            .card-title {
                font-size: 1.3rem;
            }
            .card-subtitle {
                font-size: 0.75rem;
            }
            .table {
                font-size: 0.85rem;
            }
            .table td, .table th {
                padding: 8px 6px;
            }
            .btn-sm {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
            .modal-dialog {
                margin: 10px;
            }
            .modal-body {
                padding: 15px;
            }
            /* Cards de resumo em 2 colunas no mobile */
            .row > [class*="col-md-3"] {
                flex: 0 0 50%;
                max-width: 50%;
                padding: 5px;
            }
            /* Graficos em coluna unica no mobile */
            .chart-card {
                margin-bottom: 10px;
            }
            .row > .col-md-4 {
                padding: 5px;
            }
            /* Tabelas com scroll horizontal */
            .table-responsive {
                font-size: 0.8rem;
            }
            /* Ajuste modal de multiplas despesas */
            #modalMultiplasDespesas .col-5,
            #modalMultiplasDespesas .col-3,
            #modalMultiplasDespesas .col-1 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 8px;
            }
            #modalMultiplasDespesas .despesa-item {
                border-bottom: 1px solid rgba(255,255,255,0.1);
                padding-bottom: 10px;
                margin-bottom: 10px;
            }
            /* Botoes do topbar */
            .topbar-right .btn {
                padding: 6px 10px;
            }
            /* Progress bar */
            .progress {
                height: 6px !important;
            }
            /* Remove scroll aninhado em listas recorrentes */
            .card-body-scroll {
                max-height: none !important;
                overflow-y: visible !important;
            }
        }

        @media (max-width: 480px) {
            /* Cards em coluna unica em telas muito pequenas */
            .row > [class*="col-md-3"] {
                flex: 0 0 100%;
                max-width: 100%;
            }
            .card-title {
                font-size: 1.1rem;
            }
            .topbar-right .btn span {
                display: none;
            }
            /* Esconder texto dos botoes, manter icones */
            .card-body .btn-sm {
                padding: 6px 10px;
            }
            .card-body .btn-sm i + span,
            .card-body .btn-sm span:not(:only-child) {
                display: none;
            }
            /* Pills de categoria mais compactas em telas pequenas */
            .categoria-pill {
                padding: 5px 7px !important;
                font-size: 10px !important;
            }
            .categoria-pill i:first-child {
                font-size: 12px !important;
            }
        }

        /* iOS input zoom prevention */
        @media (max-width: 991px) {
            input[type="text"],
            input[type="number"],
            input[type="date"],
            input[type="email"],
            input[type="password"],
            input[type="tel"],
            select,
            textarea {
                font-size: 16px !important;
            }
        }

        /* Bottom Navigation (mobile) */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(8,8,18,0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255,255,255,0.1);
            z-index: 1050;
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }
        .bottom-nav-inner {
            display: flex;
            align-items: stretch;
            height: 56px;
        }
        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            padding: 6px 2px;
            color: #555;
            text-decoration: none;
            font-size: 0.6rem;
            letter-spacing: 0.02em;
            gap: 3px;
            transition: color 0.2s;
            cursor: pointer;
            border: none;
            background: transparent;
            min-height: 44px;
        }
        .bottom-nav-item i {
            font-size: 1.2rem;
            line-height: 1;
        }
        .bottom-nav-item span {
            line-height: 1;
            white-space: nowrap;
        }
        .bottom-nav-item.active {
            color: #00ff88;
        }
        .bottom-nav-item:active {
            opacity: 0.6;
        }

        /* Cards e outros estilos existentes */
        .card {
            background: rgba(15,15,26,0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 15px;
            overflow: hidden;
        }
        .card:hover {
            border-color: rgba(255,255,255,0.2);
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .card-receita { border-left: 4px solid #00ff88; }
        .card-despesa { border-left: 4px solid #ff4757; }
        .card-saldo { border-left: 4px solid #3742fa; }
        .card-header {
            background: rgba(20,20,35,0.9) !important;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            color: #fff !important;
        }
        .card-header.bg-success { background: linear-gradient(135deg, rgba(0,255,136,0.25), rgba(15,15,26,0.9)) !important; color: #fff !important; }
        .card-header.bg-danger { background: linear-gradient(135deg, rgba(255,71,87,0.25), rgba(15,15,26,0.9)) !important; color: #fff !important; }
        .card-header.bg-warning { background: linear-gradient(135deg, rgba(255,193,7,0.25), rgba(15,15,26,0.9)) !important; color: #fff !important; }
        .card-header.bg-info { background: linear-gradient(135deg, rgba(55,66,250,0.25), rgba(15,15,26,0.9)) !important; color: #fff !important; }
        .card-header.bg-primary { background: linear-gradient(135deg, rgba(55,66,250,0.25), rgba(15,15,26,0.9)) !important; color: #fff !important; }
        .card-header.bg-light { background: rgba(20,20,35,0.9) !important; color: #fff !important; }
        .table { color: #fff; background: transparent; --bs-table-bg: transparent; }
        .table-hover tbody tr:hover { background: rgba(60,60,80,0.5); }
        .table thead { background: rgba(40,40,60,0.8) !important; border-bottom: 1px solid rgba(255,255,255,0.15); }
        .table thead th { background: transparent !important; color: #fff; border: none; font-weight: 500; }
        .table td, .table th { background: transparent !important; border-color: rgba(255,255,255,0.08); color: #fff; }
        .table tbody tr { background: rgba(30,30,50,0.4); }
        .table tbody tr:nth-child(even) { background: rgba(35,35,55,0.5); }
        .valor-positivo { color: #00ff88 !important; font-weight: 600; text-shadow: 0 0 20px rgba(0,255,136,0.3); }
        .valor-negativo { color: #ff4757 !important; font-weight: 600; text-shadow: 0 0 20px rgba(255,71,87,0.3); }
        .valor-atencao { color: #ffc107 !important; font-weight: 600; }
        .border-atencao { border-left: 4px solid #ffc107 !important; }
        .btn-success { background: linear-gradient(135deg, #00ff88, #00cc6a); border: none; color: #0d2818 !important; font-weight: 600; }
        .btn-success:hover { background: linear-gradient(135deg, #00cc6a, #00ff88); color: #0d2818 !important; }
        .btn-danger { background: linear-gradient(135deg, #ff4757, #ff3344); border: none; }
        .btn-danger:hover { background: linear-gradient(135deg, #ff3344, #ff4757); }
        .btn-outline-success { border-color: #00ff88; color: #00ff88; }
        .btn-outline-success:hover { background: #00ff88; color: #1a1a2e; }
        .btn-outline-danger { border-color: #ff4757; color: #ff4757; }
        .btn-outline-danger:hover { background: #ff4757; color: #fff; }
        .btn-outline-warning { border-color: #ffc107; color: #ffc107; }
        .btn-outline-warning:hover { background: #ffc107; color: #1a1a2e; }
        .btn:hover {
            filter: brightness(1.1);
        }
        .btn:active {
            transform: scale(0.97) !important;
            transition: transform 0.08s ease !important;
        }
        .btn-icon {
            min-width: 32px;
            min-height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 8px !important;
        }
        .progress { background: rgba(20,20,35,0.8); border-radius: 10px; overflow: hidden; }
        .progress-bar.bg-success { background: linear-gradient(90deg, #00ff88, #00cc6a) !important; }
        .progress-bar.bg-danger { background: linear-gradient(90deg, #ff4757, #ff3344) !important; }
        .modal-content {
            background: rgba(15,15,26,0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 15px;
        }
        .modal-header { border-bottom: 1px solid rgba(255,255,255,0.08); }
        .modal-footer { border-top: 1px solid rgba(255,255,255,0.08); }
        .form-control, .form-select {
            background: rgba(20,20,35,0.8);
            border: 1px solid rgba(255,255,255,0.08);
            color: #e4e4e4;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(25,25,45,0.9);
            border-color: #00ff88;
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(0,255,136,0.25);
        }
        .form-label { color: #aaa; }
        .alert-success {
            background: rgba(0,255,136,0.1);
            border: 1px solid rgba(0,255,136,0.3);
            color: #00ff88;
        }
        .alert-danger {
            background: rgba(255,71,87,0.1);
            border: 1px solid rgba(255,71,87,0.3);
            color: #ff4757;
        }
        .badge.bg-light { background: rgba(30,30,50,0.8) !important; }
        .badge.bg-dark { background: rgba(10,10,20,0.8) !important; }
        .badge.bg-success { background: rgba(0,255,136,0.2) !important; color: #00ff88; }
        .text-muted { color: #999 !important; }
        .bg-light { background: rgba(20,20,35,0.8) !important; }
        .sticky-top.bg-white { background: rgba(15,15,26,0.95) !important; }
        .table-secondary { background: rgba(20,20,35,0.5) !important; }
        .btn-group .btn-outline-secondary {
            border-color: rgba(255,255,255,0.2);
            color: #aaa;
        }
        .btn-group .btn-check:checked + .btn-outline-secondary {
            background: rgba(30,30,50,0.9);
            color: #fff;
            border-color: rgba(255,255,255,0.2);
        }
        .alert-info {
            background: rgba(55,66,250,0.1);
            border: 1px solid rgba(55,66,250,0.3);
            color: #a5b4fc;
        }

        /* Paginacao */
        .pagination { margin-bottom: 0; }
        .pagination .page-link {
            background: rgba(20,20,35,0.8);
            border-color: rgba(255,255,255,0.1);
            color: #e4e4e4;
        }
        .pagination .page-link:hover {
            background: rgba(55,66,250,0.3);
            border-color: rgba(55,66,250,0.5);
            color: #fff;
        }
        .pagination .page-item.active .page-link {
            background: rgba(55,66,250,0.6);
            border-color: rgba(55,66,250,0.8);
        }
        .pagination .page-item.disabled .page-link {
            background: rgba(20,20,35,0.5);
            color: #999;
        }

        /* Animacoes iniciais — somente quando JS carregou */
        .js-loaded .card,
        .js-loaded .alert { opacity: 0; }
        .js-loaded .topbar { opacity: 0; transform: translateY(-20px); }
        /* Sidebar: animação de entrada só no desktop; mobile usa CSS + .show */
        @media (min-width: 992px) {
            .js-loaded .sidebar { opacity: 0; }
        }

        /* Efeito glow nos cards */
        .glow-green { box-shadow: 0 0 30px rgba(0,255,136,0.1); }
        .glow-red { box-shadow: 0 0 30px rgba(255,71,87,0.1); }
        .glow-blue { box-shadow: 0 0 30px rgba(55,66,250,0.1); }
        .glow-purple { box-shadow: 0 0 30px rgba(111,66,193,0.1); }

        /* Scrollbar personalizada */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(15,15,26,0.8); }
        ::-webkit-scrollbar-thumb { background: rgba(40,40,65,0.8); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(60,60,90,0.8); }

        /* Validacao de formularios */
        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #ff4757 !important;
            background-image: none;
            box-shadow: 0 0 0 0.2rem rgba(255,71,87,0.25);
        }
        .form-control.is-valid, .form-select.is-valid {
            border-color: #00ff88 !important;
            background-image: none;
            box-shadow: 0 0 0 0.2rem rgba(0,255,136,0.15);
        }
        .invalid-feedback {
            color: #ff4757;
            font-size: 0.8rem;
            margin-top: 4px;
            display: block;
        }
        .valid-feedback {
            color: #00ff88;
            font-size: 0.8rem;
            margin-top: 4px;
        }
        .form-control.is-invalid ~ .invalid-feedback,
        .form-select.is-invalid ~ .invalid-feedback {
            display: block;
        }

        /* ===== Componentes de design compartilhados (hero, abas, feed) ===== */
        .engage-bar{ display:flex; flex-wrap:wrap; align-items:center; gap:14px; background: rgba(0,255,136,0.06); border:1px solid rgba(0,255,136,0.25); border-radius:12px; padding:12px 18px; margin-bottom:1.25rem; box-shadow: 0 0 30px rgba(0,255,136,0.08); }
        .engage-bar .streak{ display:flex; align-items:center; gap:8px; font-size:0.9rem; white-space:nowrap; }
        .engage-sep{ width:1px; align-self:stretch; background: rgba(255,255,255,0.1); }
        .engage-insights{ display:flex; flex-direction:column; gap:6px; flex:1; min-width:200px; }
        .engage-insight{ display:flex; align-items:center; gap:8px; color:#c9d1ff; font-size:0.85rem; }
        .engage-insight i{ color:#3742fa; }
        .hero-card{ border-left:4px solid #3742fa; padding:26px 28px; display:flex; flex-direction:column; gap:16px; justify-content:center; }
        .hero-top{ display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; }
        .hero-label{ display:flex; align-items:center; gap:8px; color:#999; font-size:0.85rem; }
        .hero-label i{ color:#3742fa; }
        .hero-value{ font-size:2.5rem; font-weight:600; line-height:1; margin-top:8px; color:#fff; }
        .hero-trend{ font-size:0.85rem; margin-top:8px; color:#fff; }
        .hero-actions{ display:flex; gap:10px; flex-wrap:wrap; }
        .stat-list-card{ display:flex; flex-direction:column; justify-content:center; }
        .stat-row{ display:flex; align-items:center; gap:12px; padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.08); }
        .stat-row:last-child{ border-bottom:none; }
        .stat-row.clickable{ cursor:pointer; }
        .stat-icon{ width:36px; height:36px; border-radius:10px; flex:none; display:flex; align-items:center; justify-content:center; font-size:1rem; }
        .stat-icon.g{ background: rgba(0,255,136,0.12); color:#00ff88; }
        .stat-icon.r{ background: rgba(255,71,87,0.12); color:#ff4757; }
        .stat-icon.p{ background: rgba(111,66,193,0.16); color:#a97bf0; }
        .stat-icon.b{ background: rgba(55,66,250,0.14); color:#8f97ff; }
        .stat-icon.w{ background: rgba(255,193,7,0.14); color:#ffc107; }
        .stat-icon.n{ background: rgba(255,255,255,0.08); color:#ccc; }
        .stat-body{ flex:1; min-width:0; }
        .stat-label{ font-size:0.76rem; color:#999; }
        .stat-value{ font-size:1.15rem; font-weight:600; margin-top:2px; color:#fff; }
        .stat-delta{ font-size:0.72rem; color:#6b6b78; margin-top:1px; }
        .dashboard-tabs{ border-bottom:none; gap:4px; background: rgba(20,20,35,0.9); border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:4px; width:fit-content; max-width:100%; overflow-x:auto; flex-wrap:nowrap; }
        .dashboard-tabs .nav-link{ border:none; color:#999; border-radius:7px; padding:8px 16px; font-size:0.85rem; font-weight:500; white-space:nowrap; }
        .dashboard-tabs .nav-link.active, .dashboard-tabs .nav-link:hover{ background: rgba(0,255,136,0.12); color:#00ff88; }
        .dashboard-tab-content{ margin-top:14px; }
        .donut-wrap{ display:flex; flex-direction:column; align-items:center; gap:6px; max-width:230px; margin:0 auto; }
        .donut-legend{ width:100%; max-width:220px; }
        .donut-legend .legend-row{ display:flex; align-items:center; gap:8px; font-size:0.88rem; margin-top:8px; }
        .donut-legend .legend-dot{ width:10px; height:10px; border-radius:2px; flex:none; }
        .donut-legend .name{ color:#999; flex:1; }
        .feed-row{ display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid rgba(255,255,255,0.08); }
        .feed-row:last-child{ border-bottom:none; }
        .feed-icon{ width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex:none; }
        .feed-icon.g{ background:rgba(0,255,136,0.12); color:#00ff88; }
        .feed-icon.r{ background:rgba(255,71,87,0.12); color:#ff4757; }
        .feed-icon.b{ background:rgba(55,66,250,0.14); color:#8f97ff; }
        .feed-icon.p{ background:rgba(111,66,193,0.16); color:#a97bf0; }
        .feed-icon.w{ background: rgba(255,193,7,0.14); color:#ffc107; }
        .feed-icon.n{ background: rgba(255,255,255,0.08); color:#ccc; }
        .feed-body{ flex:1; min-width:0; color:#fff; }
        .feed-cat{ font-size:0.75rem; color:#999; }
        .feed-when{ font-size:0.75rem; color:#6b6b78; width:44px; text-align:right; flex:none; }
        .feed-amt{ font-weight:600; min-width:110px; text-align:right; color:#fff; }
        .empty-state{ text-align:center; color:#999; padding:3rem 1rem; }
        .empty-state > i{ font-size:2.5rem; display:block; margin-bottom:0.75rem; opacity:0.35; }
        .empty-state p{ color:#999; margin:0 0 4px; }
        .empty-state small{ color:#6b6b78; }
        @media (max-width: 767px){ .hero-value{ font-size:2rem; } }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <i class="bi bi-wallet2" aria-hidden="true"></i>
                <span>Financeiro</span>
                <button class="btn-hide-sidebar ms-auto" id="hideSidebar" aria-label="Ocultar menu lateral">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                </button>
            </div>

            <nav class="sidebar-nav" aria-label="Menu principal">
                <div class="nav-section">Principal</div>
                <a href="{{ route('financas.index') }}" class="nav-link {{ request()->routeIs('financas.index') ? 'active' : '' }}">
                    <i class="bi bi-house" aria-hidden="true"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="{{ route('financas.transacoes') }}" class="nav-link {{ request()->routeIs('financas.transacoes') ? 'active' : '' }}">
                    <i class="bi bi-table" aria-hidden="true"></i>
                    <span class="nav-text">Transacoes</span>
                </a>

                <a href="{{ route('dividas.index') }}" class="nav-link {{ request()->routeIs('dividas.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card" aria-hidden="true"></i>
                    <span class="nav-text">Dívidas</span>
                    @php
                        $dividasEmAtraso = \App\Models\Divida::emAtraso()->count();
                    @endphp
                    @if($dividasEmAtraso > 0)
                        <span class="nav-badge">{{ $dividasEmAtraso }}</span>
                    @endif
                </a>

                <a href="{{ route('cartoes.index') }}" class="nav-link {{ request()->routeIs('cartoes.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card-2-front" aria-hidden="true"></i>
                    <span class="nav-text">Cartões</span>
                </a>

                <a href="{{ route('relatorios.fluxoCaixa') }}" class="nav-link {{ request()->routeIs('relatorios.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up" aria-hidden="true"></i>
                    <span class="nav-text">Relatórios</span>
                </a>

                <div class="nav-section">Configuracoes</div>
                <a href="{{ route('categorias.index') }}" class="nav-link {{ request()->routeIs('categorias.index') ? 'active' : '' }}">
                    <i class="bi bi-tags" aria-hidden="true"></i>
                    <span class="nav-text">Categorias</span>
                </a>
                <a href="{{ route('metas.index') }}" class="nav-link {{ request()->routeIs('metas.index') ? 'active' : '' }}">
                    <i class="bi bi-bullseye" aria-hidden="true"></i>
                    <span class="nav-text">Metas</span>
                </a>
                <a href="{{ route('alertas.index') }}" class="nav-link {{ request()->routeIs('alertas.index') ? 'active' : '' }}">
                    <i class="bi bi-bell" aria-hidden="true"></i>
                    <span class="nav-text">Alertas</span>
                    @php
                        $alertasCount = \App\Models\Alerta::naoLidos()->count();
                    @endphp
                    @if($alertasCount > 0)
                        <span class="nav-badge">{{ $alertasCount }}</span>
                    @endif
                </a>

                <div class="nav-section">Sistema</div>
                <a href="{{ route('logs.index') }}" class="nav-link {{ request()->routeIs('logs.index') ? 'active' : '' }}">
                    <i class="bi bi-journal-text" aria-hidden="true"></i>
                    <span class="nav-text">Logs de Auditoria</span>
                </a>
                <a href="{{ route('backup.index') }}" class="nav-link {{ request()->routeIs('backup.*') ? 'active' : '' }}">
                    <i class="bi bi-cloud-arrow-down" aria-hidden="true"></i>
                    <span class="nav-text">Backup / Exportar</span>
                </a>
                <a href="{{ route('importacao.index') }}" class="nav-link {{ request()->routeIs('importacao.*') ? 'active' : '' }}">
                    <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
                    <span class="nav-text">Importar Extrato</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <small class="text-muted">&copy; {{ date('Y') }} Financeiro</small>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <header class="topbar">
                <div class="topbar-left">
                    <button class="btn-toggle-sidebar" id="toggleSidebar" aria-label="Abrir menu lateral">
                        <i class="bi bi-list" aria-hidden="true"></i>
                    </button>
                    <h5 class="mb-0 d-none d-md-block">@yield('page-title', 'Dashboard')</h5>
                </div>
                <div class="topbar-right">
                    @hasSection('page-actions')
                        @yield('page-actions')
                    @else
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalReceita" aria-label="Nova receita">
                            <i class="bi bi-plus-lg" aria-hidden="true"></i> <span class="d-none d-sm-inline">Receita</span>
                        </button>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalDespesa" aria-label="Nova despesa">
                            <i class="bi bi-plus-lg" aria-hidden="true"></i> <span class="d-none d-sm-inline">Despesa</span>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalMultiplasDespesas" aria-label="Adicionar múltiplas despesas">
                            <i class="bi bi-list-ul" aria-hidden="true"></i>
                        </button>
                    @endif
                </div>
            </header>

            <!-- Content Area -->
            <main class="content-area">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/validation.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    <script>
        // Registrar plugin ScrollTrigger
        gsap.registerPlugin(ScrollTrigger);

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Toggle Sidebar
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('toggleSidebar');
        const hideBtn = document.getElementById('hideSidebar');

        function isMobile() {
            return window.innerWidth < 992;
        }

        // Toggle: colapsar ou mostrar sidebar oculta
        toggleBtn.addEventListener('click', () => {
            if (isMobile()) {
                // Limpa qualquer transform inline deixado pelo GSAP antes de alternar
                sidebar.style.transform = '';
                sidebar.style.opacity = '';
                sidebar.classList.remove('hidden');
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            } else {
                // Se estiver oculta, mostrar
                if (sidebar.classList.contains('hidden')) {
                    sidebar.classList.remove('hidden');
                    if (!prefersReducedMotion) {
                        gsap.to(sidebar, {
                            x: 0,
                            opacity: 1,
                            duration: 0.3,
                            ease: 'power2.out'
                        });
                    } else {
                        sidebar.style.opacity = '1';
                        sidebar.style.transform = 'none';
                    }
                    localStorage.setItem('sidebarHidden', 'false');
                    updateToggleButton();
                } else {
                    // Colapsar/expandir
                    sidebar.classList.toggle('collapsed');
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                }
            }
        });

        // Ocultar completamente a sidebar
        hideBtn.addEventListener('click', () => {
            if (isMobile()) {
                // No mobile apenas fecha (sem persistir estado)
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            } else {
                if (!prefersReducedMotion) {
                    gsap.to(sidebar, {
                        x: '-100%',
                        duration: 0.3,
                        ease: 'power2.in',
                        onComplete: () => {
                            sidebar.classList.add('hidden');
                            sidebar.classList.remove('collapsed');
                        }
                    });
                } else {
                    sidebar.style.opacity = '0';
                    sidebar.style.transform = 'translateX(-100%)';
                    sidebar.classList.add('hidden');
                    sidebar.classList.remove('collapsed');
                }
                localStorage.setItem('sidebarHidden', 'true');
                localStorage.setItem('sidebarCollapsed', 'false');
                updateToggleButton();
            }
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });

        // Fechar sidebar no mobile ao clicar em um link
        document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (isMobile()) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                }
            });
        });

        // Restaurar estado da sidebar do localStorage
        const sidebarHidden = localStorage.getItem('sidebarHidden');
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed');

        function updateToggleButton() {
            if (sidebar.classList.contains('hidden')) {
                toggleBtn.classList.add('highlight');
                toggleBtn.setAttribute('aria-label', 'Mostrar menu lateral');
            } else {
                toggleBtn.classList.remove('highlight');
                toggleBtn.setAttribute('aria-label', 'Abrir menu lateral');
            }
        }

        if (!isMobile()) {
            if (sidebarHidden === 'true') {
                sidebar.classList.add('hidden');
            } else if (sidebarCollapsed === 'true') {
                sidebar.classList.add('collapsed');
            }
        }

        updateToggleButton();

        // Observer para mudanças na sidebar (atualiza botão automaticamente)
        const sidebarObserver = new MutationObserver(updateToggleButton);
        sidebarObserver.observe(sidebar, { attributes: true, attributeFilter: ['class'] });

        // Animacao da sidebar e topbar (apenas se nao estiver oculta)
        if (prefersReducedMotion) {
            // Mostrar imediatamente sem animacao
            document.querySelectorAll('.js-loaded .card, .js-loaded .alert').forEach(el => {
                el.style.opacity = '1';
                el.style.transform = 'none';
            });
            // Desktop: remover opacity inicial; mobile: CSS já está correto, não mexer
            if (!isMobile()) {
                sidebar.style.opacity = '1';
            }
            document.querySelector('.topbar').style.opacity = '1';
            document.querySelector('.topbar').style.transform = 'none';
        } else {
            // Mobile: sem animação na sidebar — CSS controla posição e visibilidade
            if (!isMobile()) {
                if (!sidebar.classList.contains('hidden')) {
                    // Desktop: slide-in + fade com fromTo explícito
                    gsap.fromTo(sidebar,
                        { x: -20, opacity: 0 },
                        { x: 0, opacity: 1, duration: 0.6, ease: 'power3.out' }
                    );
                }
                // Se hidden: sidebar está fora da tela (CSS), não precisa de GSAP
            }

            gsap.to('.topbar', {
                opacity: 1,
                y: 0,
                duration: 0.6,
                delay: 0.1,
                ease: 'power3.out'
            });
        }

        // Animacao dos alerts
        if (!prefersReducedMotion) {
            gsap.to('.alert', {
                opacity: 1,
                y: 0,
                duration: 0.5,
                delay: 0.3,
                ease: 'back.out(1.7)'
            });
        }

        // Animacao dos cards principais (primeira linha)
        if (!prefersReducedMotion) {
            gsap.to('.row:first-child .card', {
                opacity: 1,
                y: 0,
                duration: 0.6,
                stagger: 0.1,
                delay: 0.2,
                ease: 'power3.out',
                onStart: function() {
                    document.querySelectorAll('.row:first-child .card').forEach(card => {
                        card.style.transform = 'translateY(30px)';
                    });
                }
            });
        }

        // Animacao dos demais cards com ScrollTrigger
        if (!prefersReducedMotion) {
            gsap.utils.toArray('.row:not(:first-child) .card').forEach((card, i) => {
                gsap.to(card, {
                    scrollTrigger: {
                        trigger: card,
                        start: 'top 85%',
                        toggleActions: 'play none none none'
                    },
                    opacity: 1,
                    y: 0,
                    duration: 0.6,
                    delay: i * 0.05,
                    ease: 'power3.out',
                    onStart: function() {
                        card.style.transform = 'translateY(20px)';
                    }
                });
            });
        }

        // Animacao de contagem nos valores
        function animateValue(element, start, end, duration) {
            const range = end - start;
            const startTime = performance.now();

            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const easeProgress = 1 - Math.pow(1 - progress, 3);
                const current = start + (range * easeProgress);

                element.textContent = 'R$ ' + current.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }
            requestAnimationFrame(update);
        }

        // Animar valores ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            if (prefersReducedMotion) return;
            setTimeout(() => {
                document.querySelectorAll('.card-title').forEach(el => {
                    const text = el.textContent.trim();
                    if (text.startsWith('R$')) {
                        const value = parseFloat(text.replace('R$', '').replace(/\./g, '').replace(',', '.').trim());
                        if (!isNaN(value)) {
                            animateValue(el, 0, value, 1500);
                        }
                    }
                });
            }, 500);
        });

        // Animacao dos modais
        if (!prefersReducedMotion) {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('show.bs.modal', function() {
                    const dialog = this.querySelector('.modal-dialog');
                    gsap.fromTo(dialog,
                        { opacity: 0, scale: 0.8, y: -50 },
                        { opacity: 1, scale: 1, y: 0, duration: 0.4, ease: 'back.out(1.7)' }
                    );
                });
            });
        }

        // Animacao das linhas da tabela
        if (!prefersReducedMotion) {
            gsap.utils.toArray('tbody tr').forEach((row, i) => {
                gsap.fromTo(row,
                    { opacity: 0, x: -20 },
                    {
                        opacity: 1,
                        x: 0,
                        duration: 0.4,
                        delay: 0.8 + (i * 0.03),
                        ease: 'power2.out'
                    }
                );
            });
        }

        // Efeito ripple nos botoes
        if (!prefersReducedMotion) {
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    ripple.style.cssText = `
                        position: absolute;
                        background: rgba(255,255,255,0.3);
                        border-radius: 50%;
                        pointer-events: none;
                        transform: scale(0);
                        left: ${e.clientX - rect.left}px;
                        top: ${e.clientY - rect.top}px;
                        width: 100px;
                        height: 100px;
                        margin-left: -50px;
                        margin-top: -50px;
                    `;
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    gsap.to(ripple, {
                        scale: 3,
                        opacity: 0,
                        duration: 0.6,
                        ease: 'power2.out',
                        onComplete: () => ripple.remove()
                    });
                });
            });
        }

        // Animacao dos links da sidebar
        if (!prefersReducedMotion) {
            gsap.utils.toArray('.sidebar-nav .nav-link').forEach((link, i) => {
                gsap.fromTo(link,
                    { opacity: 0, x: -20 },
                    {
                        opacity: 1,
                        x: 0,
                        duration: 0.4,
                        delay: 0.3 + (i * 0.05),
                        ease: 'power2.out'
                    }
                );
            });
        }
    </script>

    @yield('scripts')

    <!-- Bottom Navigation (mobile only) -->
    @php $rn = request()->route() ? request()->route()->getName() : ''; @endphp
    <nav class="bottom-nav" aria-label="Navegação principal">
        <div class="bottom-nav-inner">
            <a href="{{ route('financas.index') }}" class="bottom-nav-item {{ $rn === 'financas.index' ? 'active' : '' }}">
                <i class="bi bi-house{{ $rn === 'financas.index' ? '-fill' : '' }}" aria-hidden="true"></i>
                <span>Início</span>
            </a>
            <a href="{{ route('financas.transacoes') }}" class="bottom-nav-item {{ $rn === 'financas.transacoes' ? 'active' : '' }}">
                <i class="bi bi-list-ul" aria-hidden="true"></i>
                <span>Transações</span>
            </a>
            <a href="{{ route('dividas.index') }}" class="bottom-nav-item {{ \Illuminate\Support\Str::startsWith($rn, 'dividas') ? 'active' : '' }}">
                <i class="bi bi-credit-card{{ \Illuminate\Support\Str::startsWith($rn, 'dividas') ? '-fill' : '' }}" aria-hidden="true"></i>
                <span>Dívidas</span>
            </a>
            <a href="{{ route('metas.index') }}" class="bottom-nav-item {{ in_array($rn, ['metas.index', 'categorias.index', 'alertas.index']) ? 'active' : '' }}">
                <i class="bi bi-bullseye" aria-hidden="true"></i>
                <span>Config</span>
            </a>
            <button type="button" class="bottom-nav-item" id="bottomNavMenu" aria-label="Abrir menu completo">
                <i class="bi bi-grid-fill" aria-hidden="true"></i>
                <span>Mais</span>
            </button>
        </div>
    </nav>
    <script>
        document.getElementById('bottomNavMenu').addEventListener('click', function() {
            document.getElementById('toggleSidebar').click();
        });
    </script>
</body>
</html>
