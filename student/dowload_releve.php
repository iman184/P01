<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'students') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';
require_once '../vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
use Dompdf\Dompdf;

$student_id = $_SESSION['user_id'];

// ── Fetch data ────────────────────────────
$stmt = $pdo->prepare("
    SELECT student_number, first_name, last_name, email
    FROM students WHERE id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT n.grade, m.title AS module_name, m.code, m.coefficient
    FROM notes n
    JOIN modules m ON n.module_id = m.id
    WHERE n.student_id = ?
    ORDER BY m.code ASC
");
$stmt->execute([$student_id]);
$notes = $stmt->fetchAll();

// ── Weighted average ──────────────────────
$total_score = 0; $total_weight = 0;
foreach ($notes as $n) {
    $total_score  += $n['grade'] * $n['coefficient'];
    $total_weight += $n['coefficient'];
}
$average = $total_weight > 0 ? round($total_score / $total_weight, 2) : null;

// ── Mention helper ────────────────────────
function mention($g) {
    if ($g >= 16) return 'Très bien';
    if ($g >= 14) return 'Bien';
    if ($g >= 12) return 'Assez bien';
    if ($g >= 10) return 'Passable';
    return 'Insuffisant';
}

// ── Build HTML for PDF ────────────────────
$rows = '';
foreach ($notes as $n) {
    $color  = $n['grade'] >= 10 ? '#16a34a' : '#dc2626';
    $rows  .= '
    <tr>
        <td>' . htmlspecialchars($n['code']) . '</td>
        <td>' . htmlspecialchars($n['module_name']) . '</td>
        <td style="text-align:center">' . $n['coefficient'] . '</td>
        <td style="text-align:center;color:' . $color . ';font-weight:bold">
            ' . number_format($n['grade'], 2) . '
        </td>
        <td style="text-align:center">' . mention($n['grade']) . '</td>
    </tr>';
}

$status       = $average >= 10 ? 'ADMIS' : 'AJOURNÉ';
$status_color = $average >= 10 ? '#16a34a' : '#dc2626';
$date         = date('d/m/Y');

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #1e293b; }
    .header { text-align: center; margin-bottom: 30px; }
    .header h1 { font-size: 20px; margin: 0 0 4px; }
    .header p  { color: #64748b; font-size: 12px; margin: 0; }
    .info-table { width: 100%; margin-bottom: 24px; border-collapse: collapse; }
    .info-table td { padding: 6px 10px; font-size: 13px; }
    .info-table .label { color: #64748b; width: 140px; }
    .info-table .value { font-weight: bold; }
    .grades-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .grades-table th {
        background: #1e293b; color: #fff;
        padding: 9px 10px; text-align: left; font-size: 12px;
    }
    .grades-table td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
    .grades-table tr:last-child td { border-bottom: none; }
    .footer-row td {
        background: #f8fafc; font-weight: bold;
        padding: 10px; border-top: 2px solid #1e293b;
    }
    .status { font-size: 16px; font-weight: bold; color: ' . $status_color . '; }
    .watermark { text-align:center; margin-top: 40px; color: #cbd5e1; font-size: 11px; }
</style>
</head>
<body>

<div class="header">
    <h1>Relevé de Notes Officiel</h1>
    <p>Généré le ' . $date . '</p>
</div>

<table class="info-table">
    <tr>
        <td class="label">Matricule</td>
        <td class="value">' . htmlspecialchars($student['student_number']) . '</td>
        <td class="label">Nom complet</td>
        <td class="value">' . htmlspecialchars($student['first_name'].' '.$student['last_name']) . '</td>
    </tr>
    <tr>
        <td class="label">Email</td>
        <td class="value">' . htmlspecialchars($student['email']) . '</td>
      
        
    </tr>
</table>

<table class="grades-table">
    <thead>
        <tr>
            <th>Code</th>
            <th>Module</th>
            <th style="text-align:center">Coefficient</th>
            <th style="text-align:center">Note /20</th>
            <th style="text-align:center">Mention</th>
        </tr>
    </thead>
    <tbody>
        ' . $rows . '
    </tbody>
    <tfoot>
        <tr class="footer-row">
            <td colspan="3">Moyenne générale pondérée</td>
            <td style="text-align:center;color:' . $status_color . '">' . $average . ' / 20</td>
            <td style="text-align:center" class="status">' . $status . '</td>
        </tr>
    </tfoot>
</table>

<div class="watermark">Document généré automatiquement — School Management System</div>

</body>
</html>';

// ── Generate and stream PDF ───────────────
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream(
    'releve_' . $student['matricule'] . '.pdf',
    ['Attachment' => true]
);
exit;