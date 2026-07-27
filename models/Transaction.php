<?php

/**
 * Immutable banking ledger operations.
 * Every balance update and its transaction record are committed together.
 */
require_once __DIR__ . '/Model.php';

class Transaction extends Model
{
    protected string $table = 'transactions';
    protected string $primaryKey = 'transaction_id';

    public function accountsForUser(int $userId, string $role): array
    {
        $sql = "SELECT a.account_id, a.account_number, a.account_type, a.balance,
                       c.first_name, c.last_name
                FROM accounts a
                JOIN customers c ON c.customer_id = a.customer_id
                WHERE a.status = 'Active'";
        $params = [];
        if ($role === 'Customer') {
            $sql .= ' AND c.user_id = :user_id';
            $params['user_id'] = $userId;
        }
        $sql .= ' ORDER BY a.account_number';
        return $this->raw($sql, $params)->fetchAll();
    }

    public function listTransactions(int $userId, string $role, string $type = '', string $search = ''): array
    {
        $sql = "SELECT t.transaction_id, t.transaction_type, t.amount, t.receiver_id,
                       t.transaction_date, t.remarks, a.account_number,
                       c.first_name, c.last_name,
                       receiver.account_number AS receiver_account_number
                FROM transactions t
                JOIN accounts a ON a.account_id = t.account_id
                JOIN customers c ON c.customer_id = a.customer_id
                LEFT JOIN accounts receiver ON receiver.account_id = t.receiver_id
                WHERE 1 = 1";
        $params = [];
        if ($role === 'Customer') {
            $sql .= ' AND c.user_id = :user_id';
            $params['user_id'] = $userId;
        }
        if (in_array($type, ['Deposit', 'Withdraw', 'Transfer'], true)) {
            $sql .= ' AND t.transaction_type = :type';
            $params['type'] = $type;
        }
        if ($search !== '') {
            $sql .= ' AND (a.account_number LIKE :search OR c.first_name LIKE :search OR c.last_name LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        $sql .= ' ORDER BY t.transaction_date DESC, t.transaction_id DESC';
        return $this->raw($sql, $params)->fetchAll();
    }

    public function findWithDetails(int $transactionId, int $userId, string $role): array|false
    {
        $rows = $this->listTransactions($userId, $role);
        foreach ($rows as $row) {
            if ((int) $row['transaction_id'] === $transactionId) return $row;
        }
        return false;
    }

    public function process(int $actorId, string $actorRole, int $accountId, string $type, float $amount, int $receiverId, string $remarks): int
    {
        if (!in_array($type, ['Deposit', 'Withdraw', 'Transfer'], true)) throw new InvalidArgumentException('Select a valid transaction type.');
        if ($amount <= 0) throw new InvalidArgumentException('Amount must be greater than zero.');
        if ($type !== 'Transfer') $receiverId = 0;
        if ($type === 'Transfer' && $receiverId === $accountId) throw new InvalidArgumentException('Choose a different destination account.');

        $this->db->beginTransaction();
        try {
            $source = $this->lockedActiveAccount($accountId);
            if (!$source) throw new RuntimeException('The selected source account is unavailable.');
            if ($actorRole === 'Customer' && (int) $source['user_id'] !== $actorId) throw new RuntimeException('You can only use your own accounts.');

            $destination = null;
            if ($type === 'Transfer') {
                $destination = $this->lockedActiveAccount($receiverId);
                if (!$destination) throw new RuntimeException('The destination account is unavailable.');
                if ($actorRole === 'Customer' && (int) $destination['user_id'] !== $actorId) throw new RuntimeException('You can only transfer between your own accounts.');
            }
            if (in_array($type, ['Withdraw', 'Transfer'], true) && (float) $source['balance'] < $amount) throw new RuntimeException('Insufficient funds for this transaction.');

            if ($type === 'Deposit') {
                $this->changeBalance($accountId, $amount);
            } elseif ($type === 'Withdraw') {
                $this->changeBalance($accountId, -$amount);
            } else {
                $this->changeBalance($accountId, -$amount);
                $this->changeBalance($receiverId, $amount);
            }

            $transactionId = $this->create([
                'account_id' => $accountId,
                'transaction_type' => $type,
                'amount' => $amount,
                'receiver_id' => $receiverId,
                'transaction_date' => date('Y-m-d H:i:s'),
                'remarks' => $remarks,
            ]);
            if ($type === 'Transfer') {
                $this->create([
                    'account_id' => $receiverId,
                    'transaction_type' => 'Transfer',
                    'amount' => $amount,
                    'receiver_id' => $accountId,
                    'transaction_date' => date('Y-m-d H:i:s'),
                    'remarks' => 'Transfer received' . ($remarks !== '' ? ': ' . $remarks : ''),
                ]);
            }
            $this->db->commit();
            return $transactionId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    private function lockedActiveAccount(int $accountId): array|false
    {
        return $this->raw("SELECT a.account_id, a.balance, c.user_id FROM accounts a JOIN customers c ON c.customer_id = a.customer_id WHERE a.account_id = :id AND a.status = 'Active' FOR UPDATE", ['id' => $accountId])->fetch();
    }

    private function changeBalance(int $accountId, float $change): void
    {
        $this->raw('UPDATE accounts SET balance = balance + :change WHERE account_id = :id', ['change' => $change, 'id' => $accountId]);
    }

    public function createAccount(int $customerId, string $accountType, float $openingBalance): array
    {
        if (!in_array($accountType, ['Savings', 'Current'], true)) {
            throw new InvalidArgumentException('Invalid account type.');
        }
        if ($openingBalance < 0) {
            throw new InvalidArgumentException('Opening balance cannot be negative.');
        }

        $accountNumber = $this->generateAccountNumber();

        $this->raw(
            "INSERT INTO accounts (customer_id, account_number, account_type, balance, status, created_at)
         VALUES (:customer_id, :account_number, :account_type, :balance, 'Active', NOW())",
            [
                'customer_id'    => $customerId,
                'account_number' => $accountNumber,
                'account_type'   => $accountType,
                'balance'        => $openingBalance,
            ]
        );

        return [
            'account_id'     => (int) $this->db->lastInsertId(),
            'account_number' => $accountNumber,
        ];
    }

    private function generateAccountNumber(): string
    {
        do {
            // 10-digit account number, e.g. 4520193847
            $candidate = (string) random_int(1000000000, 9999999999);
            $exists = $this->raw(
                "SELECT COUNT(*) AS c FROM accounts WHERE account_number = :n",
                ['n' => $candidate]
            )->fetch()['c'];
        } while ((int) $exists > 0);

        return $candidate;
    }
}
