<?php

declare(strict_types=1);

namespace Migrations;

use FinGather\Service\Encryption\EncryptionService;
use MarekSkopal\ORM\Migrations\Migration\Migration;
use PDO;

final class EncryptApiKeysMigration extends Migration
{
	public function up(): void
	{
		$encryptionKey = (string) getenv('ENCRYPTION_KEY');
		if ($encryptionKey === '') {
			throw new \RuntimeException('ENCRYPTION_KEY environment variable is required for this migration.');
		}

		$encryptionService = new EncryptionService($encryptionKey);
		$pdo = $this->databaseProvider->getDatabase()->getPdo();

		$stmt = $pdo->query('SELECT id, api_key, user_key FROM api_keys');
		assert($stmt !== false);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($rows as $row) {
			$update = $pdo->prepare('UPDATE api_keys SET api_key = ?, user_key = ? WHERE id = ?');
			$update->execute([
				$encryptionService->encrypt($row['api_key']),
				$row['user_key'] !== null ? $encryptionService->encrypt($row['user_key']) : null,
				$row['id'],
			]);
		}
	}

	public function down(): void
	{
		$encryptionKey = (string) getenv('ENCRYPTION_KEY');
		if ($encryptionKey === '') {
			throw new \RuntimeException('ENCRYPTION_KEY environment variable is required for this migration.');
		}

		$encryptionService = new EncryptionService($encryptionKey);
		$pdo = $this->databaseProvider->getDatabase()->getPdo();

		$stmt = $pdo->query('SELECT id, api_key, user_key FROM api_keys');
		assert($stmt !== false);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($rows as $row) {
			$update = $pdo->prepare('UPDATE api_keys SET api_key = ?, user_key = ? WHERE id = ?');
			$update->execute([
				$encryptionService->decrypt($row['api_key']),
				$row['user_key'] !== null ? $encryptionService->decrypt($row['user_key']) : null,
				$row['id'],
			]);
		}
	}
}
