<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../config/security.php';

function brasasol_rate_limit_key(string $bucket, string $identity): string
{
    return hash('sha256', $bucket . '|' . strtolower(trim($identity)) . '|' . brasasol_client_ip());
}

function brasasol_rate_limit_allow(string $bucket, string $identity, int $limit, int $windowSeconds, int $blockSeconds): bool
{
    $pdo = brasasol_db();
    if (!$pdo) return false;
    $key = brasasol_rate_limit_key($bucket, $identity);
    $now = new DateTimeImmutable();
    try {
        if (random_int(1, 100) === 1) $pdo->exec("DELETE FROM security_rate_limits WHERE updated_at < (NOW() - INTERVAL 2 DAY)");
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT attempts,window_started_at,blocked_until FROM security_rate_limits WHERE bucket=? AND key_hash=? FOR UPDATE');
        $stmt->execute([$bucket, $key]);
        $row = $stmt->fetch();
        if ($row && !empty($row['blocked_until']) && strtotime((string) $row['blocked_until']) > $now->getTimestamp()) {
            $pdo->commit();
            return false;
        }
        $windowStarted = $row ? strtotime((string) $row['window_started_at']) : 0;
        $attempts = (!$row || $now->getTimestamp() - $windowStarted >= $windowSeconds) ? 1 : ((int) $row['attempts'] + 1);
        $startedAt = (!$row || $now->getTimestamp() - $windowStarted >= $windowSeconds) ? $now->format('Y-m-d H:i:s') : (string) $row['window_started_at'];
        $blockedUntil = $attempts > $limit ? $now->modify('+' . $blockSeconds . ' seconds')->format('Y-m-d H:i:s') : null;
        $save = $pdo->prepare('INSERT INTO security_rate_limits(bucket,key_hash,attempts,window_started_at,blocked_until) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE attempts=VALUES(attempts),window_started_at=VALUES(window_started_at),blocked_until=VALUES(blocked_until)');
        $save->execute([$bucket, $key, $attempts, $startedAt, $blockedUntil]);
        $pdo->commit();
        return $blockedUntil === null;
    } catch (Throwable) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}

function brasasol_rate_limit_clear(string $bucket, string $identity): void
{
    $pdo = brasasol_db();
    if (!$pdo) return;
    $pdo->prepare('DELETE FROM security_rate_limits WHERE bucket=? AND key_hash=?')->execute([$bucket, brasasol_rate_limit_key($bucket, $identity)]);
}
