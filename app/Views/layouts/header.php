<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo env('APP_URL'); ?>/assets/css/style.css">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Event Booking</title>
    <style>
        body { font-family: sans-serif; background-color: #111827; color: #e5e7eb; padding: 20px; }
        .container { max-width: 400px; margin: 50px auto; }
        .card { background: #1F2937; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        .card h2 { color: #e5e7eb; margin-bottom: 20px; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #9CA3AF; font-size: 14px; }
        input { width: 100%; padding: 10px; background: #111827; color: #e5e7eb; border: 1px solid #9CA3AF; border-radius: 4px; box-sizing: border-box; }
        input:focus { outline: none; border-color: #8B5CF6; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1); }
        button { width: 100%; padding: 10px; background: linear-gradient(135deg, #6366f1, #22d3ee); color: #040025; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; transition: all 0.3s; }
        button:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4); }
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 4px; }
        .alert-error { background: #7f1d1d; color: #fecaca; border: 1px solid #dc2626; }
        .alert-success { background: #064e3b; color: #86efac; border: 1px solid #16a34a; }
        .alert-warning { background: #78350f; color: #fdba74; border: 1px solid #f59e0b; }
        .alert-info { background: #0c4a6e; color: #7dd3fc; border: 1px solid #0284c7; }
        p { text-align: center; color: #9CA3AF; margin-top: 20px; }
        a { color: #22d3ee; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <?php require_once __DIR__ . '/flash.php'; ?>
