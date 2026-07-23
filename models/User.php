<?php
/**
 * User.php
 * Handles authentication and CRUD for the `users` table.
 * Customer profile data (name, contact info) lives in the `customers`
 * table and is joined in where needed.
 */

require_once __DIR__ . '/Model.php';

class User extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'user_id';

    /**
     * Attempt to authenticate a user by username + plaintext password.
     * Returns the user row (without password) on success, or false on failure.
     */
    public function authenticate(string $username, string $password): array|false
    {
        $stmt = $this->raw(
            "SELECT * FROM users WHERE username = :username LIMIT 1",
            ['username' => $username]
        );
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        if ($user['status'] !== 'Active') {
            return false;
        }

        unset($user['password']);
        return $user;
    }

    /**
     * Create a new user with a securely hashed password.
     */
    public function createUser(string $username, string $plainPassword, string $role, string $status = 'Active'): int
    {
        return $this->create([
            'username' => $username,
            'password' => password_hash($plainPassword, PASSWORD_DEFAULT),
            'role'     => $role,
            'status'   => $status,
        ]);
    }

    /**
     * Check whether a username is already taken.
     */
    public function usernameExists(string $username): bool
    {
        return (bool) $this->findWhere(['username' => $username]);
    }

    /**
     * Fetch a user together with their linked customer profile, if any.
     */
    public function findWithCustomerProfile(int $userId): array|false
    {
        $stmt = $this->raw(
            "SELECT u.user_id, u.username, u.role, u.status, u.created_at,
                    c.customer_id, c.first_name, c.last_name, c.birthdate,
                    c.gender, c.email, c.contact_no, c.address
             FROM users u
             LEFT JOIN customers c ON c.user_id = u.user_id
             WHERE u.user_id = :id
             LIMIT 1",
            ['id' => $userId]
        );
        return $stmt->fetch();
    }

    /**
     * Full listing for the Users index table, newest first.
     * Optionally filtered by role.
     */
    public function listAllWithProfiles(?string $role = null): array
    {
        $sql = "SELECT u.user_id, u.username, u.role, u.status, u.created_at,
                       c.customer_id, c.first_name, c.last_name, c.email, c.contact_no
                FROM users u
                LEFT JOIN customers c ON c.user_id = u.user_id";
        $params = [];
        if ($role) {
            $sql .= " WHERE u.role = :role";
            $params['role'] = $role;
        }
        $sql .= " ORDER BY u.user_id DESC";

        $stmt = $this->raw($sql, $params);
        return $stmt->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->raw("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
    }

    public function countByRole(string $role): int
    {
        return (int) $this->raw(
            "SELECT COUNT(*) AS c FROM users WHERE role = :role",
            ['role' => $role]
        )->fetch()['c'];
    }

    /**
     * Create a user, and — for Customer accounts — their linked customer
     * profile, as a single atomic operation. If the customer insert fails,
     * the user insert is rolled back too (no orphaned login with no profile).
     */
    public function createWithProfile(array $userData, ?array $customerData = null): int
    {
        $this->db->beginTransaction();
        try {
            $userId = $this->createUser(
                $userData['username'],
                $userData['password'],
                $userData['role'],
                $userData['status'] ?? 'Active'
            );

            if ($userData['role'] === 'Customer' && $customerData) {
                $customerData['user_id'] = $userId;
                $this->raw(
                    "INSERT INTO customers
                        (user_id, first_name, last_name, birthdate, gender, email, contact_no, address)
                     VALUES
                        (:user_id, :first_name, :last_name, :birthdate, :gender, :email, :contact_no, :address)",
                    $customerData
                );
            }

            $this->db->commit();
            return $userId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update a user's account fields and, if applicable, their customer
     * profile, atomically. Password is only updated when a new one is passed.
     */
    public function updateWithProfile(int $userId, array $userData, ?array $customerData = null): bool
    {
        $this->db->beginTransaction();
        try {
            $fields = [
                'username' => $userData['username'],
                'role'     => $userData['role'],
            ];
            if (!empty($userData['password'])) {
                $fields['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            }
            $this->update($userId, $fields);

            if ($userData['role'] === 'Customer' && $customerData) {
                $existing = $this->raw(
                    "SELECT customer_id FROM customers WHERE user_id = :uid",
                    ['uid' => $userId]
                )->fetch();

                $customerData['user_id'] = $userId;

                if ($existing) {
                    $set = implode(', ', array_map(
                        fn($col) => "{$col} = :{$col}",
                        array_keys($customerData)
                    ));
                    $customerData['customer_id'] = $existing['customer_id'];
                    $this->raw(
                        "UPDATE customers SET {$set} WHERE customer_id = :customer_id",
                        $customerData
                    );
                } else {
                    $this->raw(
                        "INSERT INTO customers
                            (user_id, first_name, last_name, birthdate, gender, email, contact_no, address)
                         VALUES
                            (:user_id, :first_name, :last_name, :birthdate, :gender, :email, :contact_no, :address)",
                        $customerData
                    );
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function setStatus(int $userId, string $status): bool
    {
        return $this->update($userId, ['status' => $status]);
    }

    /**
     * A customer with any account or loan record on file can't be safely
     * deleted (the accounts/loans FKs are ON DELETE RESTRICT, and losing
     * that history would break the audit trail). Deactivating them instead
     * is the correct move — this just tells the caller whether delete is safe.
     */
    public function canDeleteCustomer(int $customerId): bool
    {
        $accounts = $this->raw(
            "SELECT COUNT(*) AS c FROM accounts WHERE customer_id = :id",
            ['id' => $customerId]
        )->fetch()['c'];

        $loans = $this->raw(
            "SELECT COUNT(*) AS c FROM loans WHERE customer_id = :id",
            ['id' => $customerId]
        )->fetch()['c'];

        return ((int) $accounts === 0) && ((int) $loans === 0);
    }

    /**
     * Deletes a user, and their linked customer row if one exists and it's
     * safe to do so. Returns ['success' => bool, 'message' => string].
     */
    public function deleteUserCascade(int $userId): array
    {
        $profile = $this->findWithCustomerProfile($userId);

        if ($profile && $profile['customer_id'] && !$this->canDeleteCustomer($profile['customer_id'])) {
            return [
                'success' => false,
                'message' => 'This customer has existing accounts or loan records and can\'t be deleted. Deactivate the account instead.',
            ];
        }

        $this->db->beginTransaction();
        try {
            if ($profile && $profile['customer_id']) {
                $this->raw(
                    "DELETE FROM customers WHERE customer_id = :id",
                    ['id' => $profile['customer_id']]
                );
            }
            $this->delete($userId);
            $this->db->commit();
            return ['success' => true, 'message' => 'User deleted successfully.'];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Log an action into activity_logs for auditing purposes.
     */
    public function logActivity(int $userId, string $action): void
    {
        $stmt = $this->raw(
            "INSERT INTO activity_logs (user_id, action, log_time) VALUES (:user_id, :action, NOW())",
            ['user_id' => $userId, 'action' => $action]
        );
    }
}