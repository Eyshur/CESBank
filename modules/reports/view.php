<?php

require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/Reports.php';
require_once __DIR__ . '/../../lib/fpdf/fpdf.php';

cb_require_role(['Administrator']);

$reports = new Reports();

$type = $_GET['type'] ?? '';

if (!in_array($type, ['transactions', 'accounts'], true)) {
    die('Invalid report type.');
}

function validDate(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);

    return $d && $d->format('Y-m-d') === $date;
}

class ReportPDF extends FPDF
{
    public string $reportTitle = '';
    public string $dateRange = '';

    public function Header()
    {
        $this->SetFont('Helvetica','B',22);
        $this->Cell(0,10,'CESBANK',0,1,'C');

        $this->SetFont('Helvetica','B',15);
        $this->Cell(0,8,$this->reportTitle,0,1,'C');

        $this->SetFont('Helvetica', '', 10);

        if ($this->dateRange !== '') {
            $this->Cell(0, 6, $this->dateRange, 0, 1, 'C');
        }

        $this->Cell(
            0,
            6,
            'Generated: ' . date('F d, Y h:i A'),
            0,
            1,
            'C'
        );

        $this->Ln(5);
    }

    public function Footer()
    {
        $this->SetY(-15);

        $this->SetFont('Helvetica', '', 9);

        $this->Cell(
            0,
            10,
            'Page ' . $this->PageNo() . '/{nb}',
            0,
            0,
            'R'
        );
    }

    public function sectionTitle(string $title)
    {
        $this->SetFont('Helvetica', 'B', 12);
        $this->Cell(0, 8, $title, 0, 1);
    }

    public function summaryRow(string $label, string $value)
    {
        $this->SetFont('Helvetica', '', 10);

        $this->Cell(70, 7, $label, 1);

        $this->Cell(50, 7, $value, 1);

        $this->Ln();
    }
}

$pdf = new ReportPDF('L', 'mm', 'A4');
$pdf->SetMargins(15,15,15);
$pdf->SetAutoPageBreak(true,15);

$pdf->AliasNbPages();

if ($type === 'transactions') {

    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
    $transactionType = $_GET['transaction_type'] ?? '';

    if (
        !validDate($from) ||
        !validDate($to)
    ) {
        die('Invalid date.');
    }

    if (
        !in_array(
            $transactionType,
            ['', 'Deposit', 'Withdraw', 'Transfer'],
            true
        )
    ) {
        die('Invalid transaction type.');
    }

    $rows = $reports->transactionRows(
        $from,
        $to,
        $transactionType
    );

    $totals = $reports->transactionTotals(
        $from,
        $to
    );

    $pdf->reportTitle = 'Transactions Report';

    $pdf->dateRange =
        'Period: ' .
        date('F d, Y', strtotime($from)) .
        ' - ' .
        date('F d, Y', strtotime($to));
        
    $pdf->AddPage();

    $pdf->sectionTitle('Transaction Summary');

    foreach ($totals as $transaction => $summary) {

        $pdf->summaryRow(
            $transaction . ' Count',
            number_format($summary['count'])
        );

        $pdf->summaryRow(
            $transaction . ' Total',
            'PHP ' . number_format($summary['amount'], 2)
        );
    }

    $pdf->Ln(8);

    $pdf->sectionTitle('Transaction Details');

    $pdf->SetFont('Helvetica', 'B', 9);

    $pdf->Cell(35, 8, 'Date', 1);
    $pdf->Cell(35, 8, 'Account', 1);
    $pdf->Cell(55, 8, 'Customer', 1);
    $pdf->Cell(30, 8, 'Type', 1);
    $pdf->Cell(35, 8, 'Amount', 1);
    $pdf->Cell(80, 8, 'Remarks', 1);

    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 8);

    if (empty($rows)) {

        $pdf->Cell(
            270,
            10,
            'No records found.',
            1,
            1,
            'C'
        );

    } else {

        foreach ($rows as $row) {

            $pdf->Cell(
                35,
                8,
                date(
                    'Y-m-d',
                    strtotime($row['transaction_date'])
                ),
                1
            );

            $pdf->Cell(
                35,
                8,
                $row['account_number'],
                1
            );

            $pdf->Cell(
                55,
                8,
                $row['first_name'] . ' ' . $row['last_name'],
                1
            );

            $pdf->Cell(
                30,
                8,
                $row['transaction_type'],
                1
            );

            $pdf->Cell(
                35,
                8,
                'PHP ' . number_format($row['amount'], 2),
                1,
                0,
                'R'
            );

            $remarks = trim($row['remarks']);

            if ($remarks === '') {
                $remarks = '-';
            }

            $pdf->Cell(
                80,
                8,
                substr($remarks, 0, 45),
                1
            );

            $pdf->Ln();
        }
    }

    $pdf->Output(
        'I',
        'transactions-report.pdf'
    );

    exit;
}

if ($type === 'accounts') {

    $accountType = $_GET['account_type'] ?? '';

    if (
        !in_array(
            $accountType,
            ['', 'Savings', 'Current'],
            true
        )
    ) {
        die('Invalid account type.');
    }

    $rows = $reports->accountRows($accountType);

    $totals = $reports->accountTotals();

    $pdf->reportTitle = 'Accounts Report';

    $pdf->AddPage();

    $pdf->sectionTitle('Account Summary');

    $pdf->SetFont('Helvetica', 'B', 9);

    $pdf->Cell(55, 8, 'Account Type', 1);
    $pdf->Cell(45, 8, 'Status', 1);
    $pdf->Cell(40, 8, 'Count', 1);
    $pdf->Cell(55, 8, 'Total Balance', 1);

    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 9);
    if (empty($totals)) {

        $pdf->Cell(
            195,
            10,
            'No records found.',
            1,
            1,
            'C'
        );

    } else {

        foreach ($totals as $row) {

            $pdf->Cell(
                55,
                8,
                $row['account_type'],
                1
            );

            $pdf->Cell(
                45,
                8,
                $row['status'],
                1
            );

            $pdf->Cell(
                40,
                8,
                number_format($row['total_count']),
                1,
                0,
                'C'
            );

            $pdf->Cell(
                55,
                8,
                'PHP ' . number_format($row['total_balance'], 2),
                1,
                1,
                'R'
            );
        }
    }

    $pdf->Ln(8);

    $pdf->sectionTitle('Account Details');

    $pdf->SetFont('Helvetica', 'B', 9);

    $pdf->Cell(45, 8, 'Account Number', 1);
    $pdf->Cell(40, 8, 'Account Type', 1);
    $pdf->Cell(70, 8, 'Owner', 1);
    $pdf->Cell(45, 8, 'Balance', 1);
    $pdf->Cell(35, 8, 'Status', 1);

    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 9);

    if (empty($rows)) {

        $pdf->Cell(
            235,
            10,
            'No records found.',
            1,
            1,
            'C'
        );

    } else {

        foreach ($rows as $row) {

            $pdf->Cell(
                45,
                8,
                $row['account_number'],
                1
            );

            $pdf->Cell(
                40,
                8,
                $row['account_type'],
                1
            );

            $pdf->Cell(
                70,
                8,
                $row['first_name'] . ' ' . $row['last_name'],
                1
            );

            $pdf->Cell(
                45,
                8,
                'PHP ' . number_format($row['balance'], 2),
                1,
                0,
                'R'
            );

            $pdf->Cell(
                35,
                8,
                $row['status'],
                1,
                1,
                'C'
            );
        }
    }

    $pdf->Output(
        'I',
        'accounts-report.pdf'
    );

    exit;
}

die('Invalid report.');