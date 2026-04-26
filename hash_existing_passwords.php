<?php
require_once __DIR__ . '/config/db.php';

header('Content-Type: text/plain; charset=utf-8');

$tables = ['admin', 'teachers', 'students'];
$totalUpdated = 0;

$globalScanned = 0;
$globalAlreadyHashed = 0;
$globalEmpty = 0;
$globalConvertible = 0;

$isBcrypt = static function (string $value): bool {
    return preg_match('/^\$2[aby]\$\d{2}\$.{53}$/', $value) === 1;
};

try {
    $pdo->beginTransaction();

    foreach ($tables as $table) {
        $select = $pdo->query("SELECT id, password_hash FROM {$table}");
        $rows = $select->fetchAll(PDO::FETCH_ASSOC);

        $tableScanned = count($rows);
        $tableAlreadyHashed = 0;
        $tableEmpty = 0;
        $tableConvertible = 0;

        $updateStmt = $pdo->prepare("UPDATE {$table} SET password_hash = ? WHERE id = ?");
        $updated = 0;

        foreach ($rows as $row) {
            $current = (string) ($row['password_hash'] ?? '');
            if ($current === '') {
                $tableEmpty++;
                continue;
            }

            if ($isBcrypt($current)) {
                $tableAlreadyHashed++;
                continue;
            }

            $tableConvertible++;

            $newHash = password_hash($current, PASSWORD_BCRYPT);
            $updateStmt->execute([$newHash, (int) $row['id']]);
            $updated++;
        }

        $globalScanned += $tableScanned;
        $globalAlreadyHashed += $tableAlreadyHashed;
        $globalEmpty += $tableEmpty;
        $globalConvertible += $tableConvertible;
        $totalUpdated += $updated;

        echo strtoupper($table) . ": scanned={$tableScanned}, already_hashed={$tableAlreadyHashed}, empty={$tableEmpty}, to_hash={$tableConvertible}, updated={$updated}\n";
    }

    $pdo->commit();
    echo "Done. Total scanned: {$globalScanned}, already_hashed: {$globalAlreadyHashed}, empty: {$globalEmpty}, to_hash: {$globalConvertible}, updated: {$totalUpdated}\n";

    if ($totalUpdated === 0) {
        echo "Info: 0 updates usually means all existing passwords are already hashed, or those tables currently have no rows.\n";
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo 'Error: ' . $e->getMessage() . "\n";
}
