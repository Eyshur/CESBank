<?php

require_once __DIR__ . '/Model.php';

class Reports extends Model
{
    protected string $table = '';

    public function transactionRows(string $from, string $to, string $type = ''): array
    {
        $sql = "SELECT
                    t.transaction_date,
                    a.account_number,
                    c.first_name,
                    c.last_name,
                    t.transaction_type,
                    t.amount,
                    t.remarks
                FROM transactions t
                JOIN accounts a
                    ON a.account_id = t.account_id
                JOIN customers c
                    ON c.customer_id = a.customer_id
                WHERE t.transaction_date BETWEEN :from AND :to";

        $params = [
            'from' => $from . ' 00:00:00',
            'to'   => $to . ' 23:59:59'
        ];

        if (in_array($type, ['Deposit', 'Withdraw', 'Transfer'], true)) {
            $sql .= " AND t.transaction_type = :type";
            $params['type'] = $type;
        }

        $sql .= " ORDER BY t.transaction_date ASC";

        return $this->raw($sql, $params)->fetchAll();
    }

    public function transactionTotals(string $from, string $to): array
    {
        $sql = "SELECT
                    transaction_type,
                    COUNT(*) AS total_count,
                    SUM(amount) AS total_amount
                FROM transactions
                WHERE transaction_date BETWEEN :from AND :to
                GROUP BY transaction_type";

        $rows = $this->raw($sql, [
            'from' => $from . ' 00:00:00',
            'to'   => $to . ' 23:59:59'
        ])->fetchAll();

        $totals = [
            'Deposit' => [
                'count' => 0,
                'amount' => 0
            ],
            'Withdraw' => [
                'count' => 0,
                'amount' => 0
            ],
            'Transfer' => [
                'count' => 0,
                'amount' => 0
            ]
        ];

        foreach ($rows as $row) {
            $totals[$row['transaction_type']] = [
                'count' => (int)$row['total_count'],
                'amount' => (float)$row['total_amount']
            ];
        }

        return $totals;
    }

    public function accountRows(string $type = ''): array
    {
        $sql = "SELECT
                    a.account_number,
                    a.account_type,
                    c.first_name,
                    c.last_name,
                    a.balance,
                    a.status
                FROM accounts a
                JOIN customers c
                    ON c.customer_id = a.customer_id
                WHERE 1 = 1";

        $params = [];

        if (in_array($type, ['Savings', 'Current'], true)) {
            $sql .= " AND a.account_type = :type";
            $params['type'] = $type;
        }

        $sql .= " ORDER BY a.account_type ASC, a.account_number ASC";

        return $this->raw($sql, $params)->fetchAll();
    }

    public function accountTotals(): array
    {
        $sql = "SELECT
                    account_type,
                    status,
                    COUNT(*) AS total_count,
                    SUM(balance) AS total_balance
                FROM accounts
                GROUP BY account_type, status
                ORDER BY account_type, status";

        return $this->raw($sql)->fetchAll();
    }
}