<?php
/**
 * Loan.php
 * Handles loan applications and the approve/decline workflow.
 */

require_once __DIR__ . '/Model.php';

class Loan extends Model
{
    protected string $table = 'loans';
    protected string $primaryKey = 'loan_id';

    /**
     * Submit a new loan application. Always starts as Pending —
     * only setStatus() (via a staff approve/decline action) can change that.
     */
    public function apply(int $customerId, float $amount, float $interestRate, int $termMonths): int
    {
        return $this->create([
            'customer_id'      => $customerId,
            'loan_amount'      => $amount,
            'interest_rate'    => $interestRate,
            'loan_term'        => $termMonths,
            'status'           => 'Pending',
            'application_date' => date('Y-m-d'),
        ]);
    }

    /**
     * List loan applications, joined with the customer's name.
     * Customers only ever see their own; staff can see everyone's,
     * optionally filtered by status.
     */
    public function listForUser(int $userId, string $role, string $status = ''): array
    {
        $sql = "SELECT l.loan_id, l.customer_id, l.loan_amount, l.interest_rate,
                       l.loan_term, l.status, l.application_date,
                       c.first_name, c.last_name
                FROM loans l
                JOIN customers c ON c.customer_id = l.customer_id
                WHERE 1 = 1";
        $params = [];

        if ($role === 'Customer') {
            $sql .= ' AND c.user_id = :user_id';
            $params['user_id'] = $userId;
        }

        if (in_array($status, ['Pending', 'Accepted', 'Declined'], true)) {
            $sql .= ' AND l.status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY l.application_date DESC, l.loan_id DESC';

        return $this->raw($sql, $params)->fetchAll();
    }

    /**
     * Fetch a single loan with its customer's details, for the detail page.
     * Includes customers.user_id so the caller can enforce that a Customer
     * may only view their own application.
     */
    public function findWithCustomer(int $loanId): array|false
    {
        return $this->raw(
            "SELECT l.*, c.first_name, c.last_name, c.user_id
             FROM loans l
             JOIN customers c ON c.customer_id = l.customer_id
             WHERE l.loan_id = :id
             LIMIT 1",
            ['id' => $loanId]
        )->fetch();
    }

    /**
     * Approve or decline a loan. Only allowed while it's still Pending —
     * prevents a second staff member from overturning a decision that's
     * already been made (and processed) by someone else.
     */
    public function setStatus(int $loanId, string $newStatus): bool
    {
        if (!in_array($newStatus, ['Accepted', 'Declined'], true)) {
            throw new InvalidArgumentException('Invalid loan status.');
        }

        $current = $this->find($loanId);
        if (!$current || $current['status'] !== 'Pending') {
            return false;
        }

        return $this->update($loanId, ['status' => $newStatus]);
    }

    public function countByStatus(string $status): int
    {
        return (int) $this->raw(
            "SELECT COUNT(*) AS c FROM loans WHERE status = :status",
            ['status' => $status]
        )->fetch()['c'];
    }

    /**
     * All active customers, for the staff-facing "apply on behalf of" dropdown.
     */
    public function listCustomersForDropdown(): array
    {
        return $this->raw(
            "SELECT c.customer_id, c.first_name, c.last_name
             FROM customers c
             JOIN users u ON u.user_id = c.user_id
             WHERE u.status = 'Active'
             ORDER BY c.first_name, c.last_name"
        )->fetchAll();
    }
}