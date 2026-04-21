<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'students') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';

$student_id = $_SESSION['user_id'];

// ── Student info ──────────────────────────
$stmt = $pdo->prepare("
    SELECT student_number, first_name, last_name, email
    FROM students WHERE id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// ── Notes with module info ─────────────────
$stmt = $pdo->prepare("
    SELECT n.grade,
           m.title AS module_name, m.code, m.coefficient
    FROM notes n
    JOIN modules m ON n.module_id = m.id
    WHERE n.student_id = ?
    ORDER BY m.code ASC
");
$stmt->execute([$student_id]);
$notes = $stmt->fetchAll();

// ── Weighted average ──────────────────────
$average      = null;
$total_score  = 0;
$total_weight = 0;

foreach ($notes as $n) {
    $total_score  += $n['grade'] * $n['coefficient'];
    $total_weight += $n['coefficient'];
}
if ($total_weight > 0) {
    $average = round($total_score / $total_weight, 2);
}

require_once '../includes/student_header.php';
?>

<style>
    .releve-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }

    .releve-sheet {
        background: #fff;
        border: 1px solid #0f172a;
        padding: 18px;
        color: #0f172a;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }

    .sheet-head {
        display: grid;
        grid-template-columns: 1fr 1.1fr 1fr;
        gap: 14px;
        align-items: start;
        border-bottom: 1px solid #0f172a;
        padding-bottom: 12px;
        margin-bottom: 12px;
    }

    .sheet-head p {
        margin: 2px 0;
        font-size: 12px;
        line-height: 1.25;
    }

    .sheet-head .arabic {
        direction: rtl;
        text-align: right;
    }

    .sheet-center {
        text-align: center;
    }

    .sheet-center img {
        width: 72px;
        height: auto;
        margin-bottom: 4px;
    }

    .sheet-center h2 {
        margin: 4px 0;
        letter-spacing: 1px;
        font-size: 27px;
    }

    .sheet-center .sub {
        font-size: 12px;
        margin: 0;
    }

    .identity-table,
    .grades-table-usthb {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }

    .identity-table td,
    .grades-table-usthb th,
    .grades-table-usthb td {
        border: 1px solid #0f172a;
        padding: 6px 8px;
        font-size: 12px;
    }

    .identity-table .label {
        width: 20%;
        font-weight: 700;
        background: #f8fafc;
    }

    .grades-table-usthb th {
        font-weight: 700;
        text-align: center;
        background: #eef2ff;
    }

    .grades-table-usthb td {
        text-align: center;
    }

    .grades-table-usthb td.module {
        text-align: left;
    }

    .grade-ok {
        color: #166534;
        font-weight: 700;
    }

    .grade-bad {
        color: #991b1b;
        font-weight: 700;
    }

    .summary-row td {
        font-weight: 700;
        background: #f8fafc;
    }

    .sheet-bottom {
        margin-top: 12px;
        border-top: 1px solid #0f172a;
        padding-top: 8px;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: 12px;
    }

    @media (max-width: 900px) {
        .sheet-head {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .sheet-head .arabic {
            text-align: center;
            direction: ltr;
        }

        .sheet-bottom {
            flex-direction: column;
        }
    }
</style>

<div class="releve-actions">
    <h1>Releve de Notes</h1>
    <?php if (!empty($notes)): ?>
        <a href="./dowload_releve.php" class="btn btn-primary">
            Telecharger en PDF
        </a>
    <?php endif; ?>
</div>

<?php if (empty($notes)): ?>
    <div class="alert amber">
        ⚠️ Aucune note disponible pour le moment.
    </div>

<?php else: ?>

    <?php
        $academic_year = date('Y') . '/' . (date('Y') + 1);
        $decision = ($average !== null && $average >= 10) ? 'Admis' : 'Ajourne';
        $total_validated_credits = 0;
        foreach ($notes as $n) {
            if ($n['grade'] >= 10) {
                $total_validated_credits += (float)$n['coefficient'];
            }
        }
    ?>

    <div class="releve-sheet">
        <div class="sheet-head">
            <div>
                <p>Ministere de l'Enseignement Superieur et de la Recherche Scientifique</p>
                <p>Universite des Sciences et de la Technologie Houari Boumediene</p>
                <p>Faculte d'Informatique</p>
                <p>Departement Ingenierie des Systemes Informatiques</p>
            </div>

            <div class="sheet-center">
                <img src="../assets/images/USTHB.png" alt="Logo USTHB">
                <h2>RELEVE DE NOTES</h2>
                <p class="sub">Annee universitaire <?= htmlspecialchars($academic_year) ?></p>
            </div>

            <div class="arabic">
                <p>وزارة التعليم العالي والبحث العلمي</p>
                <p>جامعة العلوم والتكنولوجيا هواري بومدين</p>
                <p>كلية الاعلام الالي</p>
            </div>
        </div>

        <table class="identity-table">
            <tr>
                <td class="label">Nom et prenom</td>
                <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                <td class="label">Matricule</td>
                <td><?= htmlspecialchars($student['student_number']) ?></td>
            </tr>
            <tr>
                <td class="label">Filiere</td>
                <td>Informatique</td>
                <td class="label">Niveau</td>
                <td>Licence</td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td><?= htmlspecialchars($student['email']) ?></td>
                <td class="label">Session</td>
                <td>Normale</td>
            </tr>
        </table>

        <table class="grades-table-usthb">
            <thead>
                <tr>
                    <th style="width: 14%;">Code UE</th>
                    <th style="width: 45%;">Matiere</th>
                    <th style="width: 10%;">Coef</th>
                    <th style="width: 13%;">Moy /20</th>
                    <th style="width: 10%;">Credits</th>
                    <th style="width: 8%;">Sess</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $n): ?>
                    <?php
                        $grade_ok = $n['grade'] >= 10;
                        $credit = $grade_ok ? number_format((float)$n['coefficient'], 1) : '0.0';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($n['code']) ?></td>
                        <td class="module"><?= htmlspecialchars($n['module_name']) ?></td>
                        <td><?= number_format((float)$n['coefficient'], 1) ?></td>
                        <td class="<?= $grade_ok ? 'grade-ok' : 'grade-bad' ?>"><?= number_format($n['grade'], 2) ?></td>
                        <td><?= $credit ?></td>
                        <td>N</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="summary-row">
                    <td colspan="3">Moyenne generale</td>
                    <td class="<?= ($average !== null && $average >= 10) ? 'grade-ok' : 'grade-bad' ?>">
                        <?= $average !== null ? number_format($average, 2) : '0.00' ?>
                    </td>
                    <td><?= number_format($total_validated_credits, 1) ?></td>
                    <td><?= ($average !== null && $average >= 10) ? 'N' : 'R' ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="sheet-bottom">
            <div>
                <strong>Decision:</strong> <?= $decision ?>
            </div>
            <div>
                <strong>Date:</strong> <?= date('d/m/Y') ?>
            </div>
            <div>
                <strong>Total des credits valides:</strong> <?= number_format($total_validated_credits, 1) ?>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php require_once '../includes/student_footer.php'; ?>