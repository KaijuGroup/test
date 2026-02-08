<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportDate = trim($_POST['report_date'] ?? '');
    $receptionistName = trim($_POST['receptionist_name'] ?? '');
    $openingFloat = (float)($_POST['opening_float'] ?? 0);
    $cashSales = (float)($_POST['cash_sales'] ?? 0);
    $cardSales = (float)($_POST['card_sales'] ?? 0);
    $otherSales = (float)($_POST['other_sales'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if ($reportDate === '') {
        $errors[] = 'La fecha es obligatoria.';
    }

    if ($receptionistName === '') {
        $errors[] = 'El nombre del recepcionista es obligatorio.';
    }

    foreach ([
        'Fondo de caja' => $openingFloat,
        'Ventas en efectivo' => $cashSales,
        'Ventas con tarjeta' => $cardSales,
        'Otros cobros' => $otherSales,
    ] as $label => $amount) {
        if ($amount < 0) {
            $errors[] = "$label no puede ser negativo.";
        }
    }

    if ($errors === []) {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO daily_cash_reports (
                report_date,
                receptionist_name,
                opening_float,
                cash_sales,
                card_sales,
                other_sales,
                notes,
                created_at
            ) VALUES (
                :report_date,
                :receptionist_name,
                :opening_float,
                :cash_sales,
                :card_sales,
                :other_sales,
                :notes,
                :created_at
            )'
        );

        $stmt->execute([
            ':report_date' => $reportDate,
            ':receptionist_name' => $receptionistName,
            ':opening_float' => $openingFloat,
            ':cash_sales' => $cashSales,
            ':card_sales' => $cardSales,
            ':other_sales' => $otherSales,
            ':notes' => $notes,
            ':created_at' => date('c'),
        ]);

        $successMessage = 'Registro guardado correctamente.';
        $_POST = [];
    }
}

$pdo = getDatabaseConnection();
$reports = $pdo->query(
    'SELECT * FROM daily_cash_reports ORDER BY report_date DESC, id DESC LIMIT 20'
)->fetchAll();

function old(string $key, string $default = ''): string
{
    return htmlspecialchars($_POST[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de caja - Museo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; background: #f5f7fb; }
        .container { max-width: 980px; margin: 0 auto; }
        .card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,.06); margin-bottom: 20px; }
        h1, h2 { margin-top: 0; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        label { display: block; font-size: 14px; margin-bottom: 6px; color: #333; }
        input, textarea, button { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #ccd3dd; border-radius: 8px; }
        textarea { min-height: 90px; resize: vertical; }
        button { background: #1e66f5; color: #fff; font-weight: bold; border: none; cursor: pointer; }
        button:hover { background: #184fbf; }
        .full { grid-column: 1 / -1; }
        .error { background: #ffe5e5; color: #8b0000; padding: 10px; border-radius: 8px; margin-bottom: 12px; }
        .success { background: #e6ffef; color: #0a6e33; padding: 10px; border-radius: 8px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Panel diario de caja (Museo)</h1>
        <p>Rellena el cierre diario de recepción con los importes del día.</p>

        <?php if ($errors !== []): ?>
            <div class="error">
                <strong>Hay errores en el formulario:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($successMessage !== ''): ?>
            <div class="success"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="grid">
                <div>
                    <label for="report_date">Fecha</label>
                    <input type="date" id="report_date" name="report_date" value="<?= old('report_date', date('Y-m-d')) ?>" required>
                </div>
                <div>
                    <label for="receptionist_name">Recepcionista</label>
                    <input type="text" id="receptionist_name" name="receptionist_name" value="<?= old('receptionist_name') ?>" required>
                </div>
                <div>
                    <label for="opening_float">Fondo de caja (€)</label>
                    <input type="number" step="0.01" min="0" id="opening_float" name="opening_float" value="<?= old('opening_float', '0') ?>" required>
                </div>
                <div>
                    <label for="cash_sales">Cobrado en efectivo (€)</label>
                    <input type="number" step="0.01" min="0" id="cash_sales" name="cash_sales" value="<?= old('cash_sales', '0') ?>" required>
                </div>
                <div>
                    <label for="card_sales">Cobrado por tarjeta (€)</label>
                    <input type="number" step="0.01" min="0" id="card_sales" name="card_sales" value="<?= old('card_sales', '0') ?>" required>
                </div>
                <div>
                    <label for="other_sales">Otros cobros (€)</label>
                    <input type="number" step="0.01" min="0" id="other_sales" name="other_sales" value="<?= old('other_sales', '0') ?>" required>
                </div>
                <div class="full">
                    <label for="notes">Observaciones</label>
                    <textarea id="notes" name="notes" placeholder="Incidencias, anulaciones, etc."><?= old('notes') ?></textarea>
                </div>
                <div class="full">
                    <button type="submit">Guardar registro diario</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Últimos registros</h2>
        <table>
            <thead>
            <tr>
                <th>Fecha</th>
                <th>Recepcionista</th>
                <th>Fondo</th>
                <th>Efectivo</th>
                <th>Tarjeta</th>
                <th>Otros</th>
                <th>Total ventas</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($reports === []): ?>
                <tr><td colspan="7">Aún no hay registros.</td></tr>
            <?php else: ?>
                <?php foreach ($reports as $report): ?>
                    <?php $totalSales = $report['cash_sales'] + $report['card_sales'] + $report['other_sales']; ?>
                    <tr>
                        <td><?= htmlspecialchars($report['report_date'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($report['receptionist_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= number_format((float)$report['opening_float'], 2, ',', '.') ?> €</td>
                        <td><?= number_format((float)$report['cash_sales'], 2, ',', '.') ?> €</td>
                        <td><?= number_format((float)$report['card_sales'], 2, ',', '.') ?> €</td>
                        <td><?= number_format((float)$report['other_sales'], 2, ',', '.') ?> €</td>
                        <td><strong><?= number_format((float)$totalSales, 2, ',', '.') ?> €</strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
