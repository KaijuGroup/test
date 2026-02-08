<?php

declare(strict_types=1);

function getDatabaseConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $databaseDir = __DIR__ . '/data';
    $databasePath = $databaseDir . '/museum_cashier.sqlite';

    if (!is_dir($databaseDir)) {
        mkdir($databaseDir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS daily_cash_reports (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            report_date TEXT NOT NULL,
            receptionist_name TEXT NOT NULL,
            opening_float REAL NOT NULL,
            cash_sales REAL NOT NULL,
            card_sales REAL NOT NULL,
            other_sales REAL NOT NULL,
            notes TEXT,
            created_at TEXT NOT NULL
        )'
    );

    return $pdo;
}
