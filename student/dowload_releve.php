<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'students') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$student_id = $_SESSION['user_id'];

// ── Fetch data ────────────────────────────
$stmt = $pdo->prepare("
    SELECT student_number, first_name, last_name, email
    FROM students WHERE id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    http_response_code(404);
    exit('Etudiant introuvable.');
}

$student_number = (string)($student['student_number'] ?? '');
$student_full_name = trim((string)($student['first_name'] ?? '') . ' ' . (string)($student['last_name'] ?? ''));
$student_email = (string)($student['email'] ?? '');

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

$logo_data_uri = '';
$logo_path_jpg = realpath(__DIR__ . '/../assets/images/USTHB.jpg');
$logo_path_png = realpath(__DIR__ . '/../assets/images/USTHB.png');

if ($logo_path_jpg && is_file($logo_path_jpg)) {
    $logo_data_uri = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logo_path_jpg));
} elseif (extension_loaded('gd') && $logo_path_png && is_file($logo_path_png)) {
    $logo_data_uri = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path_png));
}

// ── Mention helper ────────────────────────
// ── Build HTML for PDF ────────────────────
$rows = '';
$total_validated_credits = 0;
foreach ($notes as $n) {
    $color  = $n['grade'] >= 10 ? '#16a34a' : '#dc2626';
    if ($n['grade'] >= 10) {
        $total_validated_credits += (float)$n['coefficient'];
    }
    $credit = $n['grade'] >= 10 ? number_format((float)$n['coefficient'], 1) : '0.0';
    $rows  .= '
    <tr>
        <td>' . htmlspecialchars($n['code']) . '</td>
        <td>' . htmlspecialchars($n['module_name']) . '</td>
        <td style="text-align:center">' . number_format((float)$n['coefficient'], 1) . '</td>
        <td style="text-align:center;color:' . $color . ';font-weight:bold">
            ' . number_format($n['grade'], 2) . '
        </td>
        <td style="text-align:center">' . $credit . '</td>
        <td style="text-align:center">N</td>
    </tr>';
}

$status       = ($average !== null && $average >= 10) ? 'ADMIS' : 'AJOURNE';
$status_color = ($average !== null && $average >= 10) ? '#16a34a' : '#dc2626';
$date         = date('d/m/Y');
$academic_year = date('Y') . '/' . (date('Y') + 1);

$logo_html = $logo_data_uri !== ''
    ? '<img src="' . $logo_data_uri . '" alt="USTHB" style="width:62px; height:auto; margin-bottom:4px;">'
    : '<div style="font-size:12px; font-weight:bold; margin-bottom:6px;">USTHB</div>';

// ── Arabic header as SVG image for clean rendering ────
$ar_header_svg = '';
$ar_svg_path = realpath(__DIR__ . '/../assets/images/usthb_header_ar.svg');
if ($ar_svg_path && is_file($ar_svg_path)) {
    $ar_header_svg = '<img src="' . $ar_svg_path . '" alt="Arabic Header" style="width:200px; height:auto;">';
} else {
    $ar_header_svg = '<div style="font-size:10px; text-align:right; direction:rtl;">وزارة التعليم العالي والبحث العلمي<br>جامعة العلوم والتكنولوجيا هواري بومدين<br>كلية الاعلام الالي</div>';
}

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
    .sheet { border: 1px solid #0f172a; padding: 12px; }
    .head { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .head td { vertical-align: top; width: 33.33%; font-size: 10px; line-height: 1.4; }
    .head .center { text-align: center; }
    .head .center h1 { font-size: 24px; margin: 3px 0; letter-spacing: 1px; }
    .head .center p { margin: 0; font-size: 10px; }
    .head .rtl { text-align: right; }
    .identity { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .identity td { border: 1px solid #0f172a; padding: 5px 7px; }
    .identity .label { font-weight: bold; width: 20%; background: #f8fafc; }
    .grades-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .grades-table th {
        background: #eef2ff;
        border: 1px solid #0f172a;
        padding: 6px;
        text-align: center;
        font-size: 10px;
    }
    .grades-table td {
        border: 1px solid #0f172a;
        padding: 6px;
        text-align: center;
        font-size: 10px;
    }
    .grades-table .module { text-align: left; }
    .summary-row td { font-weight: bold; background: #f8fafc; }
    .footer { margin-top: 10px; border-top: 1px solid #0f172a; padding-top: 8px; }
    .footer-line { margin-bottom: 2px; font-size: 10px; }
    .status { color: ' . $status_color . '; font-weight: bold; }
</style>
</head>
<body>

<div class="sheet">
<table class="head">
    <tr>
        <td>
            Ministere de l Enseignement Superieur et de la Recherche Scientifique<br>
            Universite des Sciences et de la Technologie Houari Boumediene<br>
            Faculte d Informatique<br>
            Departement Ingenierie des Systemes Informatiques
        </td>
        <td class="center">
            ' . $logo_html . '
            <h1>RELEVE DE NOTES</h1>
            <p>Annee universitaire ' . htmlspecialchars($academic_year) . '</p>
        </td>
        <td class="rtl">
            ' . $ar_header_svg . '
        </td>
    </tr>
</table>

<table class="identity">
    <tr>
        <td class="label">Nom et prenom</td>
        <td>' . htmlspecialchars($student_full_name) . '</td>
        <td class="label">Matricule</td>
        <td>' . htmlspecialchars($student_number) . '</td>
    </tr>
    <tr>
        <td class="label">Filiere</td>
        <td>Informatique</td>
        <td class="label">Niveau</td>
        <td>Licence</td>
    </tr>
    <tr>
        <td class="label">Email</td>
        <td>' . htmlspecialchars($student_email) . '</td>
        <td class="label">Session</td>
        <td>Normale</td>
    </tr>
</table>

<table class="grades-table">
    <thead>
        <tr>
            <th style="width:14%">Code UE</th>
            <th style="width:45%">Matiere</th>
            <th style="width:10%">Coef</th>
            <th style="width:13%">Moy /20</th>
            <th style="width:10%">Credits</th>
            <th style="width:8%">Sess</th>
        </tr>
    </thead>
    <tbody>
        ' . $rows . '
    </tbody>
    <tfoot>
        <tr class="summary-row">
            <td colspan="3">Moyenne generale</td>
            <td style="color:' . $status_color . '">' . ($average !== null ? number_format($average, 2) : '0.00') . '</td>
            <td>' . number_format($total_validated_credits, 1) . '</td>
            <td>' . (($average !== null && $average >= 10) ? 'N' : 'R') . '</td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    <div class="footer-line"><strong>Decision:</strong> <span class="status">' . $status . '</span></div>
    <div class="footer-line"><strong>Date:</strong> ' . $date . '</div>
    <div class="footer-line"><strong>Total des credits valides:</strong> ' . number_format($total_validated_credits, 1) . '</div>
</div>
</div>

</body>
</html>';

// ── Generate and stream PDF ───────────────
try {
    // Clean any previous output to avoid corrupting PDF download headers.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream(
        'releve_' . ($student_number !== '' ? $student_number : 'etudiant') . '.pdf',
        ['Attachment' => true]
    );
} catch (Throwable $e) {
    http_response_code(500);
    exit('Erreur lors de la generation du PDF.');
}
exit;