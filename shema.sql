CREATE DATABASE IF NOT EXISTS university_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE university_system;


-- -----------------------------------------------
-- TEACHERS
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS teachers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    last_name             VARCHAR(80)  NOT NULL,
    first_name            VARCHAR(80)  NOT NULL,
    email                 VARCHAR(120) NOT NULL UNIQUE,
    subject     VARCHAR(100),
    password_hash         VARCHAR(255) NOT NULL,
    must_change_password  TINYINT(1)   NOT NULL DEFAULT 1,
    is_active             TINYINT(1)   NOT NULL DEFAULT 1,
    created_at            TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------
-- MODULES
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS modules (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    code          VARCHAR(20) NOT NULL UNIQUE,
    title       VARCHAR(150) NOT NULL,
    coefficient   INT NOT NULL DEFAULT 1,
    teacher_id  INT NOT NULL,        -- UNIQUE enforces one-to-one
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
        ON DELETE CASCADE
);
-- -----------------------------------------------
-- STUDENTS
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    last_name             VARCHAR(80)  NOT NULL,
    first_name            VARCHAR(80)  NOT NULL,
    email                 VARCHAR(120) NOT NULL UNIQUE,
    profile_image         VARCHAR(255) DEFAULT NULL,
    student_number        VARCHAR(20)  NOT NULL UNIQUE,
    birth_date            DATE,
    password_hash         VARCHAR(255) NOT NULL,
    must_change_password  TINYINT(1)   NOT NULL DEFAULT 1,
    last_login            DATETIME DEFAULT NULL,
    last_activity         DATETIME DEFAULT NULL,
    is_active             TINYINT(1)   NOT NULL DEFAULT 1,
    created_at            TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- â”€â”€ Enrollments (student â†” module) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
CREATE TABLE IF NOT EXISTS enrollments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    student_id      INT NOT NULL,
    module_id       INT NOT NULL,
    academic_year   VARCHAR(10) NOT NULL DEFAULT '2025/2026',
    UNIQUE KEY uq_enrollment (student_id, module_id, academic_year),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id)   REFERENCES modules(id) ON DELETE CASCADE
);
-- -----------------------------------------------
-- NOTES  (junction table: student + module + grade)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS notes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT NOT NULL,
    module_id   INT NOT NULL,
    grade       DECIMAL(4,2) NOT NULL CHECK (grade >= 0 AND grade <= 20),
    academic_year VARCHAR(10) NOT NULL DEFAULT '2025/2026',
UNIQUE KEY uq_note (student_id, module_id, academic_year),
    FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE CASCADE,
    FOREIGN KEY (module_id)  REFERENCES modules(id)
        ON DELETE CASCADE
);

-- -----------------------------------------------
-- ADMIN
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS admin (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(80)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          VARCHAR(50)  DEFAULT 'admin'
);

-- -----------------------------------------------
-- SEED DATA (kept in repository for portability)
-- -----------------------------------------------

INSERT INTO admin (username, password_hash, role)
VALUES ('admin', 'admin123', 'admin')
ON DUPLICATE KEY UPDATE
    password_hash = VALUES(password_hash),
    role = VALUES(role);

INSERT INTO teachers (first_name, last_name, email, subject, password_hash, must_change_password, is_active)
VALUES
    ('Amina', 'Gheffar', 'amina.gheffar@gmail.com', 'Mathematiques', 'gheffaramina123', 0, 1),
    ('Hamza', 'Abdellahoum', 'abdellahoumhamza89@gmail.com', 'Informatique', 'abdellahoumhamza123', 0, 1),
    ('Labde', 'Laachemi', 'labde79@gmail.com', 'Physique', 'laachemilabde123', 0, 1)
ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    subject = VALUES(subject),
    password_hash = VALUES(password_hash),
    must_change_password = VALUES(must_change_password),
    is_active = VALUES(is_active);

DELETE FROM students WHERE email LIKE 'student%@class.local';

INSERT INTO students (first_name, last_name, email, student_number, birth_date, password_hash, must_change_password, is_active)
VALUES
    ('MELISSA-LYNA', 'ABAOUI', 'abaoui.melissalyna@gmail.com', '212431859912', NULL, '212431859912', 0, 1),
    ('MAYA MYRIAM', 'ABBAS', 'abbas.maya@gmail.com', '232431546203', NULL, '232431546203', 0, 1),
    ('AKRAM', 'ABDELHAMID', 'abdelhamid.akram@gmail.com', '242431599204', NULL, '242431599204', 0, 1),
    ('YOUCEF', 'ABDELLAOUI', 'abdellaoui.youcef@gmail.com', '222231609707', NULL, '222231609707', 0, 1),
    ('SARA', 'ABDELLATIF', 'abdellatif.sara@gmail.com', '242431676416', NULL, '242431676416', 0, 1),
    ('NIHAD', 'AISSA', 'aissa.nihad@gmail.com', '232331500107', NULL, '232331500107', 0, 1),
    ('IMAD EDDINE', 'AISSAOUI', 'aissaoui.imad@gmail.com', '242431370909', NULL, '242431370909', 0, 1),
    ('YOUSRA', 'AISSAOUI', 'aissaoui.yousra@gmail.com', '232331413601', NULL, '232331413601', 0, 1),
    ('ABDELMALEK', 'AIT KACI', 'ait.abdelmalek@gmail.com', '242431368913', NULL, '242431368913', 0, 1),
    ('IMED FAROUK', 'AIT MEHDI', 'ait.imed@gmail.com', '222231413217', NULL, '222231413217', 0, 1),
    ('AYA', 'AIT OUAMAR', 'ait.aya@gmail.com', '242431438719', NULL, '242431438719', 0, 1),
    ('ABDENOUR', 'AKACEM', 'akacem.abdenour@gmail.com', '242431577510', NULL, '242431577510', 0, 1),
    ('OUAIL ABD ERRAOUF', 'AKOUIRADJEMOU', 'akouiradjemou.ouail@gmail.com', '222231581410', NULL, '222231581410', 0, 1),
    ('IKRAM', 'AKTOUF', 'aktouf.ikram@gmail.com', '242431461716', NULL, '242431461716', 0, 1),
    ('MOHAMMED AMINE ABDERRAOUF', 'ALI', 'ali.mohammed@gmail.com', '232333374911', NULL, '232333374911', 0, 1),
    ('KAMEL', 'ALIM', 'alim.kamel@gmail.com', '242431453208', NULL, '242431453208', 0, 1),
    ('ASMAA', 'AMDIDOUCHE', 'amdidouche.asmaa@gmail.com', '222231438707', NULL, '222231438707', 0, 1),
    ('NOUR ELHOUDA', 'AMMICHE', 'ammiche.nour@gmail.com', '232332170007', NULL, '232332170007', 0, 1),
    ('ABDELMALEK', 'ASSABAT', 'assabat.abdelmalek@gmail.com', '232331499219', NULL, '232331499219', 0, 1),
    ('Mélissa', 'AZZOUG', 'azzoug.melissa@gmail.com', '232333087110', NULL, '232333087110', 0, 1),
    ('ABDENNOUR', 'AZZOUZ', 'azzouz.abdennour@gmail.com', '232331738702', NULL, '232331738702', 0, 1),
    ('MEHDI', 'AZZOUZI', 'azzouzi.mehdi@gmail.com', '242431730502', NULL, '242431730502', 0, 1),
    ('NESRINE', 'BAHA', 'baha.nesrine@gmail.com', '222233370909', NULL, '222233370909', 0, 1),
    ('DOUAA', 'BAOUZ', 'baouz.douaa@gmail.com', '242431620609', NULL, '242431620609', 0, 1),
    ('IMADEDDINE', 'BARA', 'bara.imadeddine@gmail.com', '232331388007', NULL, '232331388007', 0, 1),
    ('ISSAM EDDINE', 'BEARCIA', 'bearcia.issam@gmail.com', '232331412506', NULL, '232331412506', 0, 1),
    ('MOUSSA YAKOUB', 'BEDDIAF', 'beddiaf.moussa@gmail.com', '232331740006', NULL, '232331740006', 0, 1),
    ('IMENE ZOHRA', 'BELABED', 'belabed.imene@gmail.com', '242431597817', NULL, '242431597817', 0, 1),
    ('YASMINE FATMA ZOHRA', 'BELABRIK', 'belabrik.yasmine@gmail.com', '232331667419', NULL, '232331667419', 0, 1),
    ('REDA ABDELKARIM', 'BELARBI', 'belarbi.reda@gmail.com', '232331441703', NULL, '232331441703', 0, 1),
    ('ABDELHAKIM', 'BELHADJ', 'belhadj.abdelhakim@gmail.com', '232331715109', NULL, '232331715109', 0, 1),
    ('MOHAMED', 'BELKHIR', 'belkhir.mohamed@gmail.com', '242431715620', NULL, '242431715620', 0, 1),
    ('AYA', 'BEN AISSA CHRIF', 'ben.aya@gmail.com', '222231345706', NULL, '222231345706', 0, 1),
    ('MOHAMED LOKMAN', 'BEN BACHIR', 'ben.mohamed@gmail.com', '242431460816', NULL, '242431460816', 0, 1),
    ('OMAR', 'BENABDELLATIF', 'benabdellatif.omar@gmail.com', '242431461920', NULL, '242431461920', 0, 1),
    ('BOUCHRA', 'BENAISSA', 'benaissa.bouchra@gmail.com', '242431786010', NULL, '242431786010', 0, 1),
    ('RANIA', 'BENAMARA', 'benamara.rania@gmail.com', '232331692611', NULL, '232331692611', 0, 1),
    ('NADA', 'BENCHEIKH', 'bencheikh.nada@gmail.com', '242431596411', NULL, '242431596411', 0, 1),
    ('FARAH FARIDA', 'BENGUESMIA', 'benguesmia.farah@gmail.com', '212431656304', NULL, '212431656304', 0, 1),
    ('YASSER', 'BENMOKHTAR', 'benmokhtar.yasser@gmail.com', '242431680418', NULL, '242431680418', 0, 1),
    ('EL WALID ISSAM', 'BENYAHIA', 'benyahia.el@gmail.com', '242431675005', NULL, '242431675005', 0, 1),
    ('MOHAMED AMINE', 'BESSAA', 'bessaa.mohamed@gmail.com', '232431652101', NULL, '232431652101', 0, 1),
    ('SID ALI', 'BETTAYEB', 'bettayeb.sid@gmail.com', '242431622804', NULL, '242431622804', 0, 1),
    ('ZINEB AZHAR', 'BOUALI', 'bouali.zineb@gmail.com', '232331424405', NULL, '232331424405', 0, 1),
    ('FATMA ZOHRA', 'BOUDANI', 'boudani.fatma@gmail.com', '242431440109', NULL, '242431440109', 0, 1),
    ('FAIROUZ', 'BOUDAOUD', 'boudaoud.fairouz@gmail.com', '232331499415', NULL, '232331499415', 0, 1),
    ('Maroua', 'BOUDERRAZ', 'bouderraz.maroua@gmail.com', '232335477206', NULL, '232335477206', 0, 1),
    ('MALIK', 'BOUDINE', 'boudine.malik@gmail.com', '192431546202', NULL, '192431546202', 0, 1),
    ('RADJAA', 'BOUDJANA', 'boudjana.radjaa@gmail.com', '242431843605', NULL, '242431843605', 0, 1),
    ('Mouhyeddine ibrahim', 'BOUDRAF', 'boudraf.mouhyeddine@gmail.com', '232331698617', NULL, '232331698617', 0, 1),
    ('HAOUA', 'BOUHADDA', 'bouhadda.haoua@gmail.com', '232331740411', NULL, '232331740411', 0, 1),
    ('NOURELHOUDA', 'BOUHADJA', 'bouhadja.nourelhouda@gmail.com', '242431424613', NULL, '242431424613', 0, 1),
    ('HANANE', 'BOUKERDOUS', 'boukerdous.hanane@gmail.com', '232331544604', NULL, '232331544604', 0, 1),
    ('LINA HADIL', 'BOUKHALFA', 'boukhalfa.lina@gmail.com', '232331621308', NULL, '232331621308', 0, 1),
    ('ADLANE', 'BOUKHARI', 'boukhari.adlane@gmail.com', '232431650501', NULL, '232431650501', 0, 1),
    ('YASSER ABDELMOUMAN', 'BOUKHARI', 'boukhari.yasser@gmail.com', '242431434219', NULL, '242431434219', 0, 1),
    ('MOHAMED ADAM', 'BOUKTITE', 'bouktite.mohamed@gmail.com', '242431577705', NULL, '242431577705', 0, 1),
    ('LINA MARIA', 'BOUMEDIENE', 'boumediene.lina@gmail.com', '242431223007', NULL, '242431223007', 0, 1),
    ('MOHAMED LYES', 'BOUMEDINE', 'boumedine.mohamed@gmail.com', '232331072415', NULL, '232331072415', 0, 1),
    ('TADJ EL BAHA LYNA', 'BOUSBAA', 'bousbaa.tadj@gmail.com', '242431433019', NULL, '242431433019', 0, 1),
    ('ABDESSAMED RIADH', 'BOUSSOUSSOU', 'boussoussou.abdessamed@gmail.com', '232331433007', NULL, '232331433007', 0, 1),
    ('CHEYMA', 'BOUTRAH', 'boutrah.cheyma@gmail.com', '242431472812', NULL, '242431472812', 0, 1),
    ('ABDELLAH', 'BOUZIANE', 'bouziane.abdellah@gmail.com', '232331413914', NULL, '232331413914', 0, 1),
    ('ABDELKRIM', 'BRAHIMI', 'brahimi.abdelkrim@gmail.com', '232331553511', NULL, '232331553511', 0, 1),
    ('ALA EDDINE', 'CHABANE', 'chabane.ala@gmail.com', '232431859207', NULL, '232431859207', 0, 1),
    ('ABDALLAH', 'CHAOUADI', 'chaouadi.abdallah@gmail.com', '242431362004', NULL, '242431362004', 0, 1),
    ('MANEL CHAIMA', 'CHEBBAH', 'chebbah.manel@gmail.com', '232331641011', NULL, '232331641011', 0, 1),
    ('CHAIMA', 'CHELABI', 'chelabi.chaima@gmail.com', '232331674009', NULL, '232331674009', 0, 1),
    ('FARES', 'CHERFAOUI', 'cherfaoui.fares@gmail.com', '242431598307', NULL, '242431598307', 0, 1),
    ('SAFIA', 'CHERGUI', 'chergui.safia@gmail.com', '232331488404', NULL, '232331488404', 0, 1),
    ('ABDERRAHMANE', 'CHERIFI', 'cherifi.abderrahmane@gmail.com', '222231619218', NULL, '222231619218', 0, 1),
    ('RAHMA', 'CHERIFI', 'cherifi.rahma@gmail.com', '242431414302', NULL, '242431414302', 0, 1),
    ('YACINE', 'CHORFI', 'chorfi.yacine@gmail.com', '242431577704', NULL, '242431577704', 0, 1),
    ('YACINE', 'DADDA', 'dadda.yacine@gmail.com', '232331600106', NULL, '232331600106', 0, 1),
    ('ANAIS', 'DAHMANI', 'dahmani.anais@gmail.com', '242431679715', NULL, '242431679715', 0, 1),
    ('AYOUB', 'DAYA', 'daya.ayoub@gmail.com', '232331781916', NULL, '232331781916', 0, 1),
    ('ABDERRAHMANE', 'DERRADJI', 'derradji.abderrahmane@gmail.com', '242431370906', NULL, '242431370906', 0, 1),
    ('AYAT ERRAHMANE', 'DIAFI', 'diafi.ayat@gmail.com', '232335051703', NULL, '232335051703', 0, 1),
    ('YOUSRA', 'DJAHEL', 'djahel.yousra@gmail.com', '232331406306', NULL, '232331406306', 0, 1),
    ('ACHRAF ISLAM', 'DJENNADI', 'djennadi.achraf@gmail.com', '242431597707', NULL, '242431597707', 0, 1),
    ('SALSABILA', 'DOUDOU', 'doudou.salsabila@gmail.com', '242431414315', NULL, '242431414315', 0, 1),
    ('SALAH EDDIN', 'DOUKHI', 'doukhi.salah@gmail.com', '242431475412', NULL, '242431475412', 0, 1),
    ('WALID', 'DRIDI', 'dridi.walid@gmail.com', '242431454420', NULL, '242431454420', 0, 1),
    ('MOHAMED HOUSSAM', 'FERKOUS', 'ferkous.mohamed@gmail.com', '242431413006', NULL, '242431413006', 0, 1),
    ('NIHAL YASMINE', 'FERRAH', 'ferrah.nihal@gmail.com', '242431423103', NULL, '242431423103', 0, 1),
    ('OUSSAMA ABDELKARIM', 'FERRANI', 'ferrani.oussama@gmail.com', '242431486406', NULL, '242431486406', 0, 1),
    ('KHADIDIJA', 'FISSAH', 'fissah.khadidija@gmail.com', '232331440603', NULL, '232331440603', 0, 1),
    ('AICHA', 'GHARBI', 'gharbi.aicha@gmail.com', '232331418809', NULL, '232331418809', 0, 1),
    ('ANES', 'GHERMOUL', 'ghermoul.anes@gmail.com', '242431461709', NULL, '242431461709', 0, 1),
    ('CERINE', 'GUETTACHE', 'guettache.cerine@gmail.com', '242431616006', NULL, '242431616006', 0, 1),
    ('NOUH', 'HABBOUCHE', 'habbouche.nouh@gmail.com', '242431776615', NULL, '242431776615', 0, 1),
    ('ABDERRAOUF', 'HAFI', 'hafi.abderraouf@gmail.com', '242431621102', NULL, '242431621102', 0, 1),
    ('ISRAA', 'HAIF', 'haif.israa@gmail.com', '242432464917', NULL, '242432464917', 0, 1),
    ('HIBA MERIEM', 'HAMANI', 'hamani.hiba@gmail.com', '222231620901', NULL, '222231620901', 0, 1),
    ('SIRINE', 'HAMITI', 'hamiti.sirine@gmail.com', '232331601509', NULL, '232331601509', 0, 1),
    ('INES SALSABIL', 'HAMMADOU', 'hammadou.ines@gmail.com', '242431777702', NULL, '242431777702', 0, 1),
    ('HOCINE', 'HASNI', 'hasni.hocine@gmail.com', '242431624503', NULL, '242431624503', 0, 1),
    ('LITISSIA', 'IDJOUBAR', 'idjoubar.litissia@gmail.com', '232333149512', NULL, '232333149512', 0, 1),
    ('BOUTINE', 'IKRAM', 'ikram.boutine@gmail.com', '242431433013', NULL, '242431433013', 0, 1),
    ('YAHIA', 'KABOUCHE', 'kabouche.yahia@gmail.com', '232331338314', NULL, '232331338314', 0, 1),
    ('OUAIS', 'KADRI', 'kadri.ouais@gmail.com', '232331430512', NULL, '232331430512', 0, 1),
    ('MOHAMED ACYL', 'KEDDAR', 'keddar.mohamed@gmail.com', '242431476317', NULL, '242431476317', 0, 1),
    ('SAFOUANE ABDERRAHMANE', 'KEDIDAH', 'kedidah.safouane@gmail.com', '232331572613', NULL, '232331572613', 0, 1),
    ('YAZID', 'KESSI', 'kessi.yazid@gmail.com', '232331674415', NULL, '232331674415', 0, 1),
    ('HADIL', 'KHALFOUN', 'khalfoun.hadil@gmail.com', '212431546808', NULL, '212431546808', 0, 1),
    ('MOHAMED BACHIR', 'KHELIFA', 'khelifa.mohamed@gmail.com', '232432511703', NULL, '232432511703', 0, 1),
    ('MERIEM', 'KHELIL', 'khelil.meriem@gmail.com', '242431575703', NULL, '242431575703', 0, 1),
    ('MARIA', 'KHELLAS', 'khellas.maria@gmail.com', '242431486807', NULL, '242431486807', 0, 1),
    ('IMEDEDDIEN', 'KHETTAB', 'khettab.imededdien@gmail.com', '232331734515', NULL, '232331734515', 0, 1),
    ('ABDERRAHMANE', 'LAGRAA', 'lagraa.abderrahmane@gmail.com', '242431431503', NULL, '242431431503', 0, 1),
    ('ABD EL DJALIL', 'LAIB', 'laib.abd@gmail.com', '242431454303', NULL, '242431454303', 0, 1),
    ('RACIM', 'LAIDI', 'laidi.racim@gmail.com', '232331532706', NULL, '232331532706', 0, 1),
    ('DEKRAH', 'LAKEHAL', 'lakehal.dekrah@gmail.com', '242431577219', NULL, '242431577219', 0, 1),
    ('AYOUB', 'LAMARA', 'lamara.ayoub@gmail.com', '222231412710', NULL, '222231412710', 0, 1),
    ('MELYNA', 'LAMARA', 'lamara.melyna@gmail.com', '242431618608', NULL, '242431618608', 0, 1),
    ('LINA', 'LARBI', 'larbi.lina@gmail.com', '232331531201', NULL, '232331531201', 0, 1),
    ('NOURA', 'LASLEDJ', 'lasledj.noura@gmail.com', '242431386417', NULL, '242431386417', 0, 1),
    ('AISSA', 'LOUCIF', 'loucif.aissa@gmail.com', '232331639705', NULL, '232331639705', 0, 1),
    ('SALEM', 'MADIOU', 'madiou.salem@gmail.com', '242431441601', NULL, '242431441601', 0, 1),
    ('MALAK', 'MADJENE', 'madjene.malak@gmail.com', '23239DZA2098', NULL, '23239DZA2098', 0, 1),
    ('AYMEN AYOUB SOFIANE', 'MAHALELAINE', 'mahalelaine.aymen@gmail.com', '242431579806', NULL, '242431579806', 0, 1),
    ('MELINA', 'MAHDI', 'mahdi.melina@gmail.com', '242431475712', NULL, '242431475712', 0, 1),
    ('MOHAMED NAZIM', 'MAHDI', 'mahdi.mohamed@gmail.com', '232331717713', NULL, '232331717713', 0, 1),
    ('MARYA', 'MAHROUG', 'mahroug.marya@gmail.com', '232431549320', NULL, '232431549320', 0, 1),
    ('YASMINE', 'MAMMERI', 'mammeri.yasmine@gmail.com', '232331503216', NULL, '232331503216', 0, 1),
    ('MOHAMED RAFIK', 'MAOUCHE', 'maouche.mohamed@gmail.com', '242431562616', NULL, '242431562616', 0, 1),
    ('OUIAM', 'MECHAI', 'mechai.ouiam@gmail.com', '232331602210', NULL, '232331602210', 0, 1),
    ('ABDALLAH', 'MECHTI', 'mechti.abdallah@gmail.com', '232331223806', NULL, '232331223806', 0, 1),
    ('IMENE', 'MEDDOUR', 'meddour.imene@gmail.com', '242431559810', NULL, '242431559810', 0, 1),
    ('RYMA', 'MEDJAHED', 'medjahed.ryma@gmail.com', '232331532312', NULL, '232331532312', 0, 1),
    ('NOUR EL ISLAM', 'MEDJDOUBI', 'medjdoubi.nour@gmail.com', '242431777714', NULL, '242431777714', 0, 1),
    ('ANFEL', 'MEFTAH', 'meftah.anfel@gmail.com', '212131040805', NULL, '212131040805', 0, 1),
    ('MOHAMED IDIR', 'MEKDAM', 'mekdam.mohamed@gmail.com', '232331431614', NULL, '232331431614', 0, 1),
    ('FATIMA ZAHRA', 'MEKKI', 'mekki.fatima@gmail.com', '242431431613', NULL, '242431431613', 0, 1),
    ('YOUCEF', 'MENIA', 'menia.youcef@gmail.com', '242431731319', NULL, '242431731319', 0, 1),
    ('IKRAM', 'MERAR', 'merar.ikram@gmail.com', '242431591407', NULL, '242431591407', 0, 1),
    ('WASSIM', 'MESSAOUDI', 'messaoudi.wassim@gmail.com', '242431622106', NULL, '242431622106', 0, 1),
    ('ABD RAOUF', 'MEZIANI', 'meziani.abd@gmail.com', '242431434209', NULL, '242431434209', 0, 1),
    ('SERINE MELISSA', 'MEZIANI', 'meziani.serine@gmail.com', '242431666406', NULL, '242431666406', 0, 1),
    ('ABDERRAHMAN', 'MOKHTARI', 'mokhtari.abderrahman@gmail.com', '222231498417', NULL, '222231498417', 0, 1),
    ('ALI', 'MOKNINE', 'moknine.ali@gmail.com', '212131087391', NULL, '212131087391', 0, 1),
    ('MOHAMED HOCINE', 'MOSTEFA', 'mostefa.mohamed@gmail.com', '232331674811', NULL, '232331674811', 0, 1),
    ('SARAH CHYRAZ', 'MOUSSOUS', 'moussous.sarah@gmail.com', '232431861119', NULL, '232431861119', 0, 1),
    ('KENZA', 'MOUZAOUI', 'mouzaoui.kenza@gmail.com', '232431535911', NULL, '232431535911', 0, 1),
    ('MUSTAPHA IYAD', 'NAIMI', 'naimi.mustapha@gmail.com', '242431618418', NULL, '242431618418', 0, 1),
    ('ANES ZAKARIA', 'NASRI', 'nasri.anes@gmail.com', '242439340418', NULL, '242439340418', 0, 1),
    ('AHMED BAHA EDDINE', 'NEDIR', 'nedir.ahmed@gmail.com', '242431367805', NULL, '242431367805', 0, 1),
    ('Souheil', 'NID', 'nid.souheil@gmail.com', '232331032114', NULL, '232331032114', 0, 1),
    ('HICHEM', 'OUAREZKI', 'ouarezki.hichem@gmail.com', '242431398806', NULL, '242431398806', 0, 1),
    ('FERHAT MOHAMED ANIS', 'OUGUENOUNE', 'ouguenoune.ferhat@gmail.com', '242431433205', NULL, '242431433205', 0, 1),
    ('CHAIMA', 'OULDEDINE', 'ouldedine.chaima@gmail.com', '232331595914', NULL, '232331595914', 0, 1),
    ('CELINA', 'RABEHI', 'rabehi.celina@gmail.com', '232431531515', NULL, '232431531515', 0, 1),
    ('ANIS', 'RAHILI', 'rahili.anis@gmail.com', '242431572814', NULL, '242431572814', 0, 1),
    ('DOUAA HIBAT ELLAH', 'RAMDANI', 'ramdani.douaa@gmail.com', '232331430814', NULL, '232331430814', 0, 1),
    ('MERIEM', 'RAMOUL', 'ramoul.meriem@gmail.com', '242431422801', NULL, '242431422801', 0, 1),
    ('AHMED ELAMINE', 'REMRAM', 'remram.ahmed@gmail.com', '242431383508', NULL, '242431383508', 0, 1),
    ('ABDALLAH', 'RETIM', 'retim.abdallah@gmail.com', '232431859120', NULL, '232431859120', 0, 1),
    ('RIAD', 'REZKI', 'rezki.riad@gmail.com', '242431624912', NULL, '242431624912', 0, 1),
    ('AMINA', 'ROUIBAH', 'rouibah.amina@gmail.com', '232433341813', NULL, '232433341813', 0, 1),
    ('ISLEM', 'SAADI', 'saadi.islem@gmail.com', '232331698506', NULL, '232331698506', 0, 1),
    ('MEZIANE', 'SAIB', 'saib.meziane@gmail.com', '232331105319', NULL, '232331105319', 0, 1),
    ('MOHAMED DHIAEDDINE', 'SAIDANI', 'saidani.mohamed@gmail.com', '242431370913', NULL, '242431370913', 0, 1),
    ('AIMEN ABDELOUAHID', 'SBAI', 'sbai.aimen@gmail.com', '232431526712', NULL, '232431526712', 0, 1),
    ('YOUNES MEHDI', 'SEDAOUI', 'sedaoui.younes@gmail.com', '242431601409', NULL, '242431601409', 0, 1),
    ('WASSIM', 'SELAMA', 'selama.wassim@gmail.com', '242431696012', NULL, '242431696012', 0, 1),
    ('MOHAMED RACIM', 'SEMMAR', 'semmar.mohamed@gmail.com', '242431621604', NULL, '242431621604', 0, 1),
    ('IMENE', 'SERAY', 'seray.imene@gmail.com', '242431423801', NULL, '242431423801', 0, 1),
    ('NEWFEL', 'SKENDER', 'skender.newfel@gmail.com', '242431680409', NULL, '242431680409', 0, 1),
    ('ANIS', 'SLIMANI', 'slimani.anis@gmail.com', '232331659203', NULL, '232331659203', 0, 1),
    ('IMAD', 'SLIMANI', 'slimani.imad@gmail.com', '242431461919', NULL, '242431461919', 0, 1),
    ('MOUDJIB EL RAHMANE', 'SOUFI', 'soufi.moudjib@gmail.com', '232331597818', NULL, '232331597818', 0, 1),
    ('ICHRAK', 'SOUIDI', 'souidi.ichrak@gmail.com', '232331500812', NULL, '232331500812', 0, 1),
    ('MEHDI ABDELHAKIM', 'SOUISSI', 'souissi.mehdi@gmail.com', '222231649017', NULL, '222231649017', 0, 1),
    ('ABDEL MOUMENE', 'TAKRATI', 'takrati.abdel@gmail.com', '232332212801', NULL, '232332212801', 0, 1),
    ('ISLAM', 'TAS', 'tas.islam@gmail.com', '242431572917', NULL, '242431572917', 0, 1),
    ('ANES', 'TATA', 'tata.anes@gmail.com', '242431624311', NULL, '242431624311', 0, 1),
    ('NACIM', 'TAYEB', 'tayeb.nacim@gmail.com', '242431596506', NULL, '242431596506', 0, 1),
    ('SAMI AYOUB', 'TEHAR', 'tehar.sami@gmail.com', '232431845311', NULL, '232431845311', 0, 1),
    ('MOHAMED DJAOUED', 'TEMAM', 'temam.mohamed@gmail.com', '242431722303', NULL, '242431722303', 0, 1),
    ('OUSSAMA', 'TEMLALI', 'temlali.oussama@gmail.com', '232331734201', NULL, '232331734201', 0, 1),
    ('MOHAMED ADEM AYOUB', 'TOUAT', 'touat.mohamed@gmail.com', '242431680215', NULL, '242431680215', 0, 1),
    ('NESSRINE', 'TSAMDA', 'tsamda.nessrine@gmail.com', '242431730516', NULL, '242431730516', 0, 1),
    ('Abdelhak', 'YESSAD', 'yessad.abdelhak@gmail.com', '2424354270', NULL, '2424354270', 0, 1),
    ('ABD ELRAHMAN', 'ZAHAF', 'zahaf.abd@gmail.com', '232331338807', NULL, '232331338807', 0, 1),
    ('RAYAN', 'ZAHED', 'zahed.rayan@gmail.com', '232331394803', NULL, '232331394803', 0, 1),
    ('Madi', 'ZAKARIA', 'zakaria.madi@gmail.com', '232431844615', NULL, '232431844615', 0, 1),
    ('ABDENOUR', 'ZEGHDANE', 'zeghdane.abdenour@gmail.com', '232431534320', NULL, '232431534320', 0, 1),
    ('MAYA', 'ZERAIA', 'zeraia.maya@gmail.com', '242431748813', NULL, '242431748813', 0, 1),
    ('ILYES', 'ZERGOUN', 'zergoun.ilyes@gmail.com', '232331481012', NULL, '232331481012', 0, 1),
    ('RITADJE', 'ZERGUI', 'zergui.ritadje@gmail.com', '222431858709', NULL, '222431858709', 0, 1),
    ('WALID', 'ZERKOUK', 'zerkouk.walid@gmail.com', '242431680417', NULL, '242431680417', 0, 1),
    ('RABAH HICHEM', 'ZERTIT', 'zertit.rabah@gmail.com', '242431614911', NULL, '242431614911', 0, 1),
    ('DAMIA FARIEL', 'ZIANE', 'ziane.damia@gmail.com', '232431847516', NULL, '232431847516', 0, 1),
    ('YACINE', 'ZIGADI', 'zigadi.yacine@gmail.com', '202331414107', NULL, '202331414107', 0, 1),
    ('IMEN', 'ZIGHED', 'zighed.imen@gmail.com', '232335330411', NULL, '232335330411', 0, 1),
    ('SABER', 'ZITOUNI', 'zitouni.saber@gmail.com', '232331650909', NULL, '232331650909', 0, 1),
    ('أحلام', 'عزوزي', '.@gmail.com', '232331346601', NULL, '232331346601', 0, 1)
ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    student_number = VALUES(student_number),
    birth_date = VALUES(birth_date),
    password_hash = VALUES(password_hash),
    must_change_password = VALUES(must_change_password),
    is_active = VALUES(is_active);
