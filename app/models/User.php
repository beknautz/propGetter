<?php
/**
 * PropIntel CRM - User Model
 */

class User
{
    public static function findById(int $id): ?array
    {
        return Database::fetchOne('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::fetchOne('SELECT * FROM users WHERE email = ? LIMIT 1', [strtolower(trim($email))]);
    }

    public static function all(): array
    {
        return Database::fetchAll('SELECT id, name, email, role, active, created_at, last_login FROM users ORDER BY name');
    }

    public static function create(array $data): int
    {
        Database::query(
            'INSERT INTO users (name, email, password, role, active) VALUES (?, ?, ?, ?, ?)',
            [
                trim($data['name']),
                strtolower(trim($data['email'])),
                password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                $data['role'] ?? 'viewer',
                $data['active'] ?? 1,
            ]
        );
        $id = (int)Database::lastInsertId();
        Logger::activity('user_created', null, "User created: {$data['email']}", ['user_id' => $id]);
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        $fields = [];
        $params = [];

        foreach (['name', 'email', 'role', 'active'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = ?";
                $params[]  = $data[$col];
            }
        }

        if (!empty($data['password'])) {
            $fields[] = 'password = ?';
            $params[]  = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        if (!$fields) return;

        $params[] = $id;
        Database::query('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?', $params);
    }

    public static function forSelect(): array
    {
        return Database::fetchAll('SELECT id, name FROM users WHERE active = 1 ORDER BY name');
    }
}
