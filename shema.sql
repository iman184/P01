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
VALUES ('admin', '$2y$10$FxWs2xqrnU0sQrbOTPkUzeBpmPxQVxTXlHcbqoHbfImeOzrrA.chy', 'admin')
ON DUPLICATE KEY UPDATE
    password_hash = VALUES(password_hash),
    role = VALUES(role);

INSERT INTO teachers (first_name, last_name, email, subject, password_hash, must_change_password, is_active)
VALUES
    ('Amina', 'Gheffar', 'amina.gheffar@gmail.com', 'Mathematiques', '$2y$10$s6160WsOHj2VPVlJZzpKCOxMcCYbJcJKoNhc1iUeoQayIkbuGl91e', 0, 1),
    ('Hamza', 'Abdellahoum', 'abdellahoumhamza89@gmail.com', 'Informatique', '$2y$10$dV1n91yNjixWqZQUoLUwSeDp3hVlePx5Kc8umVXvTd4rNFeRWRwxO', 0, 1),
    ('Labde', 'Laachemi', 'labde79@gmail.com', 'Physique', '$2y$10$Rfh/a1qknfidoVKU64HDzO1AH8D0gbcOidGT9y0oeCF7dmzHsukZe', 0, 1)
ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    subject = VALUES(subject),
    password_hash = VALUES(password_hash),
    must_change_password = VALUES(must_change_password),
    is_active = VALUES(is_active);

INSERT INTO teachers (first_name, last_name, email, subject, password_hash, must_change_password, is_active)
VALUES
    ('Sofiane', 'Benkacem', 'sofiane.benkacem@edusync.local', 'Base de donnees', '$2y$10$LWo//cebtlX5ij0O111f4uF.vGDqVaIh06pFBgJUEp2/5yeeraDDO', 0, 1),
    ('Nadia', 'Mansouri', 'nadia.mansouri@edusync.local', 'Architecture dordinateur', '$2y$10$lY74MldcIH1CxkWM4.o6mO8XvsD/z0C3rjrPgKmseVveAlT9RtBPW', 0, 1),
    ('Yacine', 'Merabet', 'yacine.merabet@edusync.local', 'Theorie des graphes', '$2y$10$tT0A24evQl7JYG1pUK7gUeZnpNrtWzVvdt/W9LNex6NtaBTgg//wy', 0, 1),
    ('Samira', 'Khellaf', 'samira.khellaf@edusync.local', 'Genie logiciel', '$2y$10$BrbWFngY7zoxgPr3S0yFPuApW24bKVUrVDEDnZWPOkK5OFdSpW3QK', 0, 1),
    ('Karim', 'Bouzid', 'karim.bouzid@edusync.local', 'Programmation web', '$2y$10$3H256HfridmQPcV9UX7yk.rByMu/h/ConxPh8yO30tN2a583OoNaC', 0, 1)
ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    subject = VALUES(subject),
    password_hash = VALUES(password_hash),
    must_change_password = VALUES(must_change_password),
    is_active = VALUES(is_active);

    INSERT INTO modules (code, title, coefficient, teacher_id)
    SELECT
        src.code,
        src.title,
        src.coefficient,
        src.teacher_id
    FROM (
        SELECT 'BDD' AS code, 'Base de donnees' AS title, 3 AS coefficient,
            (SELECT id FROM teachers WHERE email = 'abdellahoumhamza89@gmail.com' LIMIT 1) AS teacher_id
        UNION ALL
        SELECT 'ARCHI', 'Architecture dordinateur', 3,
            (SELECT id FROM teachers WHERE email = 'labde79@gmail.com' LIMIT 1)
        UNION ALL
        SELECT 'THG', 'Theorie des graphes', 2,
            (SELECT id FROM teachers WHERE email = 'amina.gheffar@gmail.com' LIMIT 1)
        UNION ALL
        SELECT 'GL', 'Genie logiciel', 3,
            (SELECT id FROM teachers WHERE email = 'samira.khellaf@edusync.local' LIMIT 1)
        UNION ALL
        SELECT 'PWEB', 'Programmation web', 3,
            (SELECT id FROM teachers WHERE email = 'karim.bouzid@edusync.local' LIMIT 1)
    ) AS src
    ON DUPLICATE KEY UPDATE
        title = VALUES(title),
        coefficient = VALUES(coefficient),
        teacher_id = VALUES(teacher_id);


DELETE FROM students WHERE email LIKE 'student%@class.local';

INSERT INTO students (first_name, last_name, email, student_number, birth_date, password_hash, must_change_password, is_active)
VALUES
    ('MELISSA-LYNA', 'ABAOUI', 'abaoui.melissalyna@gmail.com', '212431859912', NULL, '$2y$10$ReoepI1hDcXEvB1m/6jNb.76hzG9JyLPrP42ar79XdXhl/mP1k3xO', 0, 1),
    ('MAYA MYRIAM', 'ABBAS', 'abbas.maya@gmail.com', '232431546203', NULL, '$2y$10$bxa2FNAT1bJC.TbO1QEG3ePRnMkuG1sBc9Nxb4wsamz.LBrBtbGEW', 0, 1),
    ('AKRAM', 'ABDELHAMID', 'abdelhamid.akram@gmail.com', '242431599204', NULL, '$2y$10$PvZylS9BLlZhaCGKovZLruE3kao57utP4eD39I7JQhYjlwFy3x6tm', 0, 1),
    ('YOUCEF', 'ABDELLAOUI', 'abdellaoui.youcef@gmail.com', '222231609707', NULL, '$2y$10$vLjRY2h7p9D96HyHWVy32.F1pn2NAZpIvs72IHviLB0zpcbvexHqe', 0, 1),
    ('SARA', 'ABDELLATIF', 'abdellatif.sara@gmail.com', '242431676416', NULL, '$2y$10$15K8G7ETwjvJ5SBYbKQC7uc8VaTPQcVXGDxJvn/Xjt5zDNV0iaSn.', 0, 1),
    ('NIHAD', 'AISSA', 'aissa.nihad@gmail.com', '232331500107', NULL, '$2y$10$slZtM4t6wogC70kxuj0O/OIoMJSD1/mK/DMs0.HK56/YBp7joZ7Pq', 0, 1),
    ('IMAD EDDINE', 'AISSAOUI', 'aissaoui.imad@gmail.com', '242431370909', NULL, '$2y$10$w/0n7S5YnrjwPBH59c04U.M0p1CRDwMxq3Dd42aTLtFBGrPpbLIIW', 0, 1),
    ('YOUSRA', 'AISSAOUI', 'aissaoui.yousra@gmail.com', '232331413601', NULL, '$2y$10$ab8CUB1aA6KF0lxiA0Leo./CSzK3ux.eKCF0AJv8QOLgjogSq1NNu', 0, 1),
    ('ABDELMALEK', 'AIT KACI', 'ait.abdelmalek@gmail.com', '242431368913', NULL, '$2y$10$mLzHFqWdrrzNwfIfqJUsPOTjFqMacGDs4DtVycLkjexzLvS41KCM6', 0, 1),
    ('IMED FAROUK', 'AIT MEHDI', 'ait.imed@gmail.com', '222231413217', NULL, '$2y$10$idEzEnAiG1P/6mo0u4Yl..4pIfNvjVKGguWhp.MOfh6cFTe5zyBM2', 0, 1),
    ('AYA', 'AIT OUAMAR', 'ait.aya@gmail.com', '242431438719', NULL, '$2y$10$292sRlJ4fuHARKknSOtQgO0lUxEvInWq8YfV3ucBp8TzVeYETQOsS', 0, 1),
    ('ABDENOUR', 'AKACEM', 'akacem.abdenour@gmail.com', '242431577510', NULL, '$2y$10$6lRAU/G1OHu.WlAQjlJACOTtrU7TouPL1eOfBUuDLVLDtK6JuLZx2', 0, 1),
    ('OUAIL ABD ERRAOUF', 'AKOUIRADJEMOU', 'akouiradjemou.ouail@gmail.com', '222231581410', NULL, '$2y$10$5p5hTjMtX44CCaIruHgcXO7MK2EFtUQKgR/cQefm56cE.5.jOxhSS', 0, 1),
    ('IKRAM', 'AKTOUF', 'aktouf.ikram@gmail.com', '242431461716', NULL, '$2y$10$tTIeBlrNwjdBoewyzAYwSu1Rjz9s6f2cYHv9.oPgQKB79qOCPoYEi', 0, 1),
    ('MOHAMMED AMINE ABDERRAOUF', 'ALI', 'ali.mohammed@gmail.com', '232333374911', NULL, '$2y$10$odjfoaZzN2qg6YJO4HBlzuzg3s60UU3IxnHHJekeXi7d0iFaglR2m', 0, 1),
    ('KAMEL', 'ALIM', 'alim.kamel@gmail.com', '242431453208', NULL, '$2y$10$JfCtjpbf3kMa/GCIu3zFRe4aCDtyIjOg0wHdQukzg/e13D.d.SkTm', 0, 1),
    ('ASMAA', 'AMDIDOUCHE', 'amdidouche.asmaa@gmail.com', '222231438707', NULL, '$2y$10$SATJWNfaiiUHHMr2Z5EwsOdNOWLwWdhvUgbeMhfYRah3HWTscutAO', 0, 1),
    ('NOUR ELHOUDA', 'AMMICHE', 'ammiche.nour@gmail.com', '232332170007', NULL, '$2y$10$y88PiPctYeldJNTInjjnH.30EYmt.etbPqqcTJX6IOuZio2bQsLHi', 0, 1),
    ('ABDELMALEK', 'ASSABAT', 'assabat.abdelmalek@gmail.com', '232331499219', NULL, '$2y$10$zLTxIl6sNWwzRE2qf0GTNOJA.UiYj.m75yvWofNrpkOwI56gBXQLq', 0, 1),
    ('M''elissa', 'AZZOUG', 'azzoug.melissa@gmail.com', '232333087110', NULL, '$2y$10$d/Io0VAIxZRN3ZyBE0QU4uEABmssij5/WF5kBtCNDtX7I7t9u8bMO', 0, 1),
    ('ABDENNOUR', 'AZZOUZ', 'azzouz.abdennour@gmail.com', '232331738702', NULL, '$2y$10$uf.Uxj6jxnLlyXYTF6VVB.G6YN2MfpOFWfOizj6Qt8SP6FkhMVa6y', 0, 1),
    ('MEHDI', 'AZZOUZI', 'azzouzi.mehdi@gmail.com', '242431730502', NULL, '$2y$10$HwK/vuo3uXZHnCNSQpdCBuch1Bkh7IZ.S.Qx.DO5jPJAALUQtJnqq', 0, 1),
    ('NESRINE', 'BAHA', 'baha.nesrine@gmail.com', '222233370909', NULL, '$2y$10$sGLwr91uL4bVF/kRUrU4X.mnS./SLIuU8IC5F9HfcWz6YHW0dEPNS', 0, 1),
    ('DOUAA', 'BAOUZ', 'baouz.douaa@gmail.com', '242431620609', NULL, '$2y$10$XlIUvv90V3kaGHubecwwZuijQMrDIJyI1mq21MjCf0VkhuEbdjS/q', 0, 1),
    ('IMADEDDINE', 'BARA', 'bara.imadeddine@gmail.com', '232331388007', NULL, '$2y$10$CAPgF02g4ca..zTDgbrGP.i63cZq5rP8FQtvNjIKVdkZzf3mPllQa', 0, 1),
    ('ISSAM EDDINE', 'BEARCIA', 'bearcia.issam@gmail.com', '232331412506', NULL, '$2y$10$C.SDDU2kZjshCloYS3TXH.J3ia79hZBB4.o2NaDrB7htefvz.jcx2', 0, 1),
    ('MOUSSA YAKOUB', 'BEDDIAF', 'beddiaf.moussa@gmail.com', '232331740006', NULL, '$2y$10$8Xx2L3d3BSfX7OqroQLn0eGM.6tRTICvG/U.ugENjDPcI7RfELUWC', 0, 1),
    ('IMENE ZOHRA', 'BELABED', 'belabed.imene@gmail.com', '242431597817', NULL, '$2y$10$6BKZaOK3AeuSFLdTr52PtO3IsgDqJRxNxld98OiQaq6raNPhNlJAS', 0, 1),
    ('YASMINE FATMA ZOHRA', 'BELABRIK', 'belabrik.yasmine@gmail.com', '232331667419', NULL, '$2y$10$JO8pS0REjiAMjMrxlcMTAercDpIaA5/jRBb9ACS0zHLNtVhUaY3oS', 0, 1),
    ('REDA ABDELKARIM', 'BELARBI', 'belarbi.reda@gmail.com', '232331441703', NULL, '$2y$10$mc6WzJmCXTiwNB/a3vi5S.GNtsszpWcNiDZTO1czVgV1NGh7BiDeK', 0, 1),
    ('ABDELHAKIM', 'BELHADJ', 'belhadj.abdelhakim@gmail.com', '232331715109', NULL, '$2y$10$SXM.qOBb0GRpZM11XPtUl.J0UqaCkJ66b/07UJDKKkHsrENGSq4fS', 0, 1),
    ('MOHAMED', 'BELKHIR', 'belkhir.mohamed@gmail.com', '242431715620', NULL, '$2y$10$HZT0aL9D6822WG67gZ7m2OlO8jOytzPXWIDXZi.9jiJbR1AyHdllK', 0, 1),
    ('AYA', 'BEN AISSA CHRIF', 'ben.aya@gmail.com', '222231345706', NULL, '$2y$10$hV2jUl3P94SadZ1yVJojouQoe4WNBQMszGvGi.jt8LiQOFHdioaHu', 0, 1),
    ('MOHAMED LOKMAN', 'BEN BACHIR', 'ben.mohamed@gmail.com', '242431460816', NULL, '$2y$10$me14yVZ0pnwkQYRo3ZjWUOaFcow5hvwPrkrJYYO3N7ZFO5pm55WQG', 0, 1),
    ('OMAR', 'BENABDELLATIF', 'benabdellatif.omar@gmail.com', '242431461920', NULL, '$2y$10$AXenbeq//BAIhB8HOTgPbuyvw96MUIMkgDEqDxRfAnHifdsnZh17C', 0, 1),
    ('BOUCHRA', 'BENAISSA', 'benaissa.bouchra@gmail.com', '242431786010', NULL, '$2y$10$xm6s/ZtBAFhIpe0BPfQn9uhAK7aVfSbALLQ/lNKoPeSsM646GlUau', 0, 1),
    ('RANIA', 'BENAMARA', 'benamara.rania@gmail.com', '232331692611', NULL, '$2y$10$bGOUrPr4GyunHJbq90Oiy.wGmFNEVVuKOIR/XKriDr4zNFrbD6HE6', 0, 1),
    ('NADA', 'BENCHEIKH', 'bencheikh.nada@gmail.com', '242431596411', NULL, '$2y$10$h9ujaemS3KI2V5CQyxLr6.aerSOXO/.EwtkX.TdqUowKXAqoa1sFq', 0, 1),
    ('FARAH FARIDA', 'BENGUESMIA', 'benguesmia.farah@gmail.com', '212431656304', NULL, '$2y$10$Zv9s13j9XO4Iq1gzCdfpweOU/afUKXNJTd2dprN0Zp0oIDqiU.Is2', 0, 1),
    ('YASSER', 'BENMOKHTAR', 'benmokhtar.yasser@gmail.com', '242431680418', NULL, '$2y$10$xQpcLTxP61eZA7cD2cGHXOhzpzohFCbzl/VKeJlEH5bY3nxZmeTJq', 0, 1),
    ('EL WALID ISSAM', 'BENYAHIA', 'benyahia.el@gmail.com', '242431675005', NULL, '$2y$10$Q1s.ttOWkbSv0HiCyUbcLuULNEk04fubkbmHw7qGH.ZpgtkOnlbf6', 0, 1),
    ('MOHAMED AMINE', 'BESSAA', 'bessaa.mohamed@gmail.com', '232431652101', NULL, '$2y$10$lJpxQrJh1uGpYUUAuxmgve0BQo4cPvYtVNWyTZk7HeWm3x0lyxpme', 0, 1),
    ('SID ALI', 'BETTAYEB', 'bettayeb.sid@gmail.com', '242431622804', NULL, '$2y$10$9zpPcDVXcYefPj5.Y79aXuU6YUMFw5MuwSdg3ItIued9BrYZREDIq', 0, 1),
    ('ZINEB AZHAR', 'BOUALI', 'bouali.zineb@gmail.com', '232331424405', NULL, '$2y$10$N5ZayHPjGkTL8YIFnCU8QOTUPSuaZyGwc.dMhveIcHJPvs5C9Mbee', 0, 1),
    ('FATMA ZOHRA', 'BOUDANI', 'boudani.fatma@gmail.com', '242431440109', NULL, '$2y$10$H3lLAklcqSBPqkwtO8fWCOK.6NeWyJ8mvrFrLyfpZr11dpcoYHXg2', 0, 1),
    ('FAIROUZ', 'BOUDAOUD', 'boudaoud.fairouz@gmail.com', '232331499415', NULL, '$2y$10$GNzBxe4T3rjc25iF4rf2L.oUoVVp0ah.j6yhc1NpnXEHthPuahyDq', 0, 1),
    ('Maroua', 'BOUDERRAZ', 'bouderraz.maroua@gmail.com', '232335477206', NULL, '$2y$10$Kg30wT5mlnBpGMl0MG8U1e/l.izd6b9uOZRVsqiDsVWpPvTLT6I82', 0, 1),
    ('MALIK', 'BOUDINE', 'boudine.malik@gmail.com', '192431546202', NULL, '$2y$10$MbJ8QJh.x0DA/wbgC.BIXOblsA06MPcK66UX7qGwO7G9jkqf4TF92', 0, 1),
    ('RADJAA', 'BOUDJANA', 'boudjana.radjaa@gmail.com', '242431843605', NULL, '$2y$10$gPfk0MW9Pjc7XgHCT0Edeu3m/HPLTiIfLuq/Nx/RNRoPUaIyTJ/4C', 0, 1),
    ('Mouhyeddine ibrahim', 'BOUDRAF', 'boudraf.mouhyeddine@gmail.com', '232331698617', NULL, '$2y$10$w9YXJyuUFwwCcqs1mORA8.SFF8zhabt7Qy3zXxcJfgKpsRWzNZvdW', 0, 1),
    ('HAOUA', 'BOUHADDA', 'bouhadda.haoua@gmail.com', '232331740411', NULL, '$2y$10$0hLzjFxun0oH.IythOGnkOKUHPm8mBtCMsmaJbXvHPDLkiLp8Gqoi', 0, 1),
    ('NOURELHOUDA', 'BOUHADJA', 'bouhadja.nourelhouda@gmail.com', '242431424613', NULL, '$2y$10$OeXGT4FKA1x5Xl5Slku/eOV9zXaxtsfbyOfQqHxcVsOxDpq3YgNNS', 0, 1),
    ('HANANE', 'BOUKERDOUS', 'boukerdous.hanane@gmail.com', '232331544604', NULL, '$2y$10$cAILHIO6ISdoPUIwMdnjH.8IAZJeKKavHvOKdcG6qg7gdnU93KiMW', 0, 1),
    ('LINA HADIL', 'BOUKHALFA', 'boukhalfa.lina@gmail.com', '232331621308', NULL, '$2y$10$qecBC2Gvkxz3s4hU8nMQwev2RYDrdkuoWkKzFo7dlZiSG8OTtbJRe', 0, 1),
    ('ADLANE', 'BOUKHARI', 'boukhari.adlane@gmail.com', '232431650501', NULL, '$2y$10$MQmTBMggnfPETvCSqYGfHObX4SCmCFl/WQGIv/gy49KXzu7Ex1IPO', 0, 1),
    ('YASSER ABDELMOUMAN', 'BOUKHARI', 'boukhari.yasser@gmail.com', '242431434219', NULL, '$2y$10$zaa4unA4LIn26nbLb7M81uLUgPdPqrJ0h7NMPwkGcUGmn14CXiUK6', 0, 1),
    ('MOHAMED ADAM', 'BOUKTITE', 'bouktite.mohamed@gmail.com', '242431577705', NULL, '$2y$10$q12RY9F4BWXiJMQrFHWOAOp0ZYijWC5xfJAOsaHMP2cWcSuUMmMfy', 0, 1),
    ('LINA MARIA', 'BOUMEDIENE', 'boumediene.lina@gmail.com', '242431223007', NULL, '$2y$10$b9dyeYTihNDRa46VxT374OHOyPSBOoQ/h/lF6iIgF1ElZYcjpwlva', 0, 1),
    ('MOHAMED LYES', 'BOUMEDINE', 'boumedine.mohamed@gmail.com', '232331072415', NULL, '$2y$10$FzSHoY.3WDHulCnIXWIeu.36dWEjK4mtjIM6ckREs81KRwlxlHvI6', 0, 1),
    ('TADJ EL BAHA LYNA', 'BOUSBAA', 'bousbaa.tadj@gmail.com', '242431433019', NULL, '$2y$10$3hHL9gDeYorTrkJm78HNjedPjboxDd9bZFSWwSoFezW2uyzORPuZa', 0, 1),
    ('ABDESSAMED RIADH', 'BOUSSOUSSOU', 'boussoussou.abdessamed@gmail.com', '232331433007', NULL, '$2y$10$pRr38/C2uqWGfKFgG6bdEextlJC52HKwFGA7m9M4amAAtHgcI4jNy', 0, 1),
    ('CHEYMA', 'BOUTRAH', 'boutrah.cheyma@gmail.com', '242431472812', NULL, '$2y$10$vI9LMTpuSRj5JL8wCEmiTe5KwrJv7MxDN8ZWgfkIMFYxDyWzNTz8W', 0, 1),
    ('ABDELLAH', 'BOUZIANE', 'bouziane.abdellah@gmail.com', '232331413914', NULL, '$2y$10$WM1CGlC14mitKHAP0vsqf.6MjS0oCDoPQWARZKqCvdVFZs3P6ZoD.', 0, 1),
    ('ABDELKRIM', 'BRAHIMI', 'brahimi.abdelkrim@gmail.com', '232331553511', NULL, '$2y$10$CYrrN0nIe0lmP0y.rrmMJ.0zFv5lkH2kIJbx/hqb6FF9SlGmTgD9a', 0, 1),
    ('ALA EDDINE', 'CHABANE', 'chabane.ala@gmail.com', '232431859207', NULL, '$2y$10$8qwxNplZY1Jb9BaDNYYK.ummHGr8G0h9iwzc7QCdeYR.IM0fhpKLO', 0, 1),
    ('ABDALLAH', 'CHAOUADI', 'chaouadi.abdallah@gmail.com', '242431362004', NULL, '$2y$10$.HyTAw8vjwVckW.ve89gaeKHieDWpGmszz3lznWf0NDFNw15cv2l.', 0, 1),
    ('MANEL CHAIMA', 'CHEBBAH', 'chebbah.manel@gmail.com', '232331641011', NULL, '$2y$10$jK4Dn.TgvWG/iXdnOw3yl.3acR8G1oZsF5fq0NmlDGJ8QmnT3FE3y', 0, 1),
    ('CHAIMA', 'CHELABI', 'chelabi.chaima@gmail.com', '232331674009', NULL, '$2y$10$F1b.Z.9CUmAvilt7yt0JiOQJeYkqX3tJlsO1nT6PihOV16dBwoUc2', 0, 1),
    ('FARES', 'CHERFAOUI', 'cherfaoui.fares@gmail.com', '242431598307', NULL, '$2y$10$XX./VuBPJ3UwcNj3u6QoSuxzn.u1kq/LAWWJarkLk60lpW2emZQw.', 0, 1),
    ('SAFIA', 'CHERGUI', 'chergui.safia@gmail.com', '232331488404', NULL, '$2y$10$UEf5JlCENhysvTlkTQYiQuQBF5YjhAW8vzrv7oBMaTOyf77Wt4WNO', 0, 1),
    ('ABDERRAHMANE', 'CHERIFI', 'cherifi.abderrahmane@gmail.com', '222231619218', NULL, '$2y$10$sSXck.dejpPzdEFRtomrLOsvh/rt1wEneYrhjCcCKD5.3X4OnXuBq', 0, 1),
    ('RAHMA', 'CHERIFI', 'cherifi.rahma@gmail.com', '242431414302', NULL, '$2y$10$.kcD57eTXRLlh10infny6O4YAvpJ9y7GFlAXTyIHJwG2L29lFqTdK', 0, 1),
    ('YACINE', 'CHORFI', 'chorfi.yacine@gmail.com', '242431577704', NULL, '$2y$10$JSA9vfBPF00oz4nMFd66y.n3uJaS594ioWoW63O6R/V9e81zazQ0.', 0, 1),
    ('YACINE', 'DADDA', 'dadda.yacine@gmail.com', '232331600106', NULL, '$2y$10$qHepDUMX1h76J6hHkmfgtOcjizmxFjUEDI.gWOb.K5k/gcyd7IWPy', 0, 1),
    ('ANAIS', 'DAHMANI', 'dahmani.anais@gmail.com', '242431679715', NULL, '$2y$10$JS1i9jczGRX4jvwa.jt/oeLu6DYiSI50DuYdeIzdES.noalgZicm6', 0, 1),
    ('AYOUB', 'DAYA', 'daya.ayoub@gmail.com', '232331781916', NULL, '$2y$10$o8d2wnKiAnYbAOv0ikd/Ru9gDDPsl0fVEcZ8/3IK4nAy5Uj6ipb3m', 0, 1),
    ('ABDERRAHMANE', 'DERRADJI', 'derradji.abderrahmane@gmail.com', '242431370906', NULL, '$2y$10$fgTptW91SIJvk42WiTZyAewMTIXRc30tB7zoEJXFUqkUzibTznC0S', 0, 1),
    ('AYAT ERRAHMANE', 'DIAFI', 'diafi.ayat@gmail.com', '232335051703', NULL, '$2y$10$av1rykQg/cpo.93s0JOwH.v0CzIFVZE/9N4Wrsu4GB95iajWIsJpG', 0, 1),
    ('YOUSRA', 'DJAHEL', 'djahel.yousra@gmail.com', '232331406306', NULL, '$2y$10$Ew48ReplPbYfaKSsZ2k0HuX2zoOElmDM/kplD3lKc.jigDBbJ3ek2', 0, 1),
    ('ACHRAF ISLAM', 'DJENNADI', 'djennadi.achraf@gmail.com', '242431597707', NULL, '$2y$10$E5ddnqQRunXH5ej6rSZUmuqsap57GPb5HoIJn.RT2/iQg8duSEIsy', 0, 1),
    ('SALSABILA', 'DOUDOU', 'doudou.salsabila@gmail.com', '242431414315', NULL, '$2y$10$F0GR999F1JvaBkOWZAawuuaHqWMqlvViuhT3alK7g7tBGhXvYNwrG', 0, 1),
    ('SALAH EDDIN', 'DOUKHI', 'doukhi.salah@gmail.com', '242431475412', NULL, '$2y$10$7fdEN1PXrNQ9TE5XOny5Q.radZGjKHL0Up6qWAhigZkX8bIY7KRHC', 0, 1),
    ('WALID', 'DRIDI', 'dridi.walid@gmail.com', '242431454420', NULL, '$2y$10$8YM.oswxWq5BG.gjz6yp1OJ8SYronEd2a1fCuZZyqM.xKP4CVI0J.', 0, 1),
    ('MOHAMED HOUSSAM', 'FERKOUS', 'ferkous.mohamed@gmail.com', '242431413006', NULL, '$2y$10$fzFXJ6dYyQDoVmj0SGGrLO8sOlLV7t4nT2rrATZXIipeANAjVCkc.', 0, 1),
    ('NIHAL YASMINE', 'FERRAH', 'ferrah.nihal@gmail.com', '242431423103', NULL, '$2y$10$.fq4WMrQYYNjhORs/hdSEuBUntOZYESJNHr6Q98v5ebuCquUH0wrS', 0, 1),
    ('OUSSAMA ABDELKARIM', 'FERRANI', 'ferrani.oussama@gmail.com', '242431486406', NULL, '$2y$10$dAPYteH7KLWoxizGiXM4WuKqXRRAKw7qNAb.UlsKikGN450F1laIG', 0, 1),
    ('KHADIDIJA', 'FISSAH', 'fissah.khadidija@gmail.com', '232331440603', NULL, '$2y$10$3qH7HZ8N9gdCcxP.OYDZZewp2nK8FNxsfJqALRTvdk9yOyd41Or9C', 0, 1),
    ('AICHA', 'GHARBI', 'gharbi.aicha@gmail.com', '232331418809', NULL, '$2y$10$hVV0d0Br/rdDa3xvpii3euUocor1ZBF9kE4X.7jCd5o/T8NPPOQO2', 0, 1),
    ('ANES', 'GHERMOUL', 'ghermoul.anes@gmail.com', '242431461709', NULL, '$2y$10$3.iLnO6yquY7L9BlEjLBi.IrlIPKPiB5Tdb0CLeW6MGMdBqM2zSXq', 0, 1),
    ('CERINE', 'GUETTACHE', 'guettache.cerine@gmail.com', '242431616006', NULL, '$2y$10$LJDw4Fjyatr3ejLBYMsQmOFN6CcxS5k4DTzN8dqck/zI3fN5bFzni', 0, 1),
    ('NOUH', 'HABBOUCHE', 'habbouche.nouh@gmail.com', '242431776615', NULL, '$2y$10$En5xnd/autKQs7JC1Q/pd.D6FhRqgmBoLBXDwEX84YphSMK4lt5mS', 0, 1),
    ('ABDERRAOUF', 'HAFI', 'hafi.abderraouf@gmail.com', '242431621102', NULL, '$2y$10$2UTMRTDN.TeExgaG3UbfjOHe4VYI6hbMZOQi8XvxPOQErTPbD8qLO', 0, 1),
    ('ISRAA', 'HAIF', 'haif.israa@gmail.com', '242432464917', NULL, '$2y$10$X0nvC4YmngXOaUx.pgF4WOazPpecM2x6.oWcYE3LKsjkSgiEkfyki', 0, 1),
    ('HIBA MERIEM', 'HAMANI', 'hamani.hiba@gmail.com', '222231620901', NULL, '$2y$10$2gz4lB5MAmFSkkBFvHR27.K4N/kykiQF92UI3b.Il9RTNliQz3LM.', 0, 1),
    ('SIRINE', 'HAMITI', 'hamiti.sirine@gmail.com', '232331601509', NULL, '$2y$10$mYGOX2GfctvWT.MbDFt1fe7EMVhn3T7Mk.wXDo.ttUFT9K18rPqUW', 0, 1),
    ('INES SALSABIL', 'HAMMADOU', 'hammadou.ines@gmail.com', '242431777702', NULL, '$2y$10$up3WSfTbTuKhXPzGeQtt0u8yJEE/ZtSGqaRcJPAskcTIDtroD5T1K', 0, 1),
    ('HOCINE', 'HASNI', 'hasni.hocine@gmail.com', '242431624503', NULL, '$2y$10$7oZ10v4omRpZ91Txoab63Oe4iJoxgtmUCami6wlgOZ5JWfjGgFO3G', 0, 1),
    ('LITISSIA', 'IDJOUBAR', 'idjoubar.litissia@gmail.com', '232333149512', NULL, '$2y$10$x6LpYkQkOiZ2A6HPgQKU3uAGirZMJPKgDw10S7juNm.islDBaE4Z2', 0, 1),
    ('BOUTINE', 'IKRAM', 'ikram.boutine@gmail.com', '242431433013', NULL, '$2y$10$Bxlb.jMDGQotPkNkei7RsesXoB49p542buoQGfSy1/.6nnSxrxrce', 0, 1),
    ('YAHIA', 'KABOUCHE', 'kabouche.yahia@gmail.com', '232331338314', NULL, '$2y$10$oB48IBq47gCB72LAByUXVe6DX1sZEUThjP63k4HUqNVzc6dDzdJfG', 0, 1),
    ('OUAIS', 'KADRI', 'kadri.ouais@gmail.com', '232331430512', NULL, '$2y$10$dNS6a.IsYBg5/n7MtQb/q.7usQanBLKtUxJR9KU.wpoOpzmA2/0.6', 0, 1),
    ('MOHAMED ACYL', 'KEDDAR', 'keddar.mohamed@gmail.com', '242431476317', NULL, '$2y$10$yK1ji0l/G8wXZnTwE9kxKucDVedlW1DQRvQeF4iTUBuEXDi7J23Mu', 0, 1),
    ('SAFOUANE ABDERRAHMANE', 'KEDIDAH', 'kedidah.safouane@gmail.com', '232331572613', NULL, '$2y$10$rgnMd8rvm6G2Vv3TMvUbTeKbyj6XJwsb8WPVE7U0WPNLWHqM6HY7.', 0, 1),
    ('YAZID', 'KESSI', 'kessi.yazid@gmail.com', '232331674415', NULL, '$2y$10$Y7StJLk2iTAn9ni6PbTilegQnB1ie5J4pBtK7HxYK1PGea7spYkB.', 0, 1),
    ('HADIL', 'KHALFOUN', 'khalfoun.hadil@gmail.com', '212431546808', NULL, '$2y$10$Uuw7Z5ej56V0jIoqhpPE2OCS8d4iODzCYvsJJGN5/fZTAZobPWirW', 0, 1),
    ('MOHAMED BACHIR', 'KHELIFA', 'khelifa.mohamed@gmail.com', '232432511703', NULL, '$2y$10$2ePyeCuPq7N2DHgc5DR16OGWSDyItc6X0cPKDJXFR/lbPTGgKPnZi', 0, 1),
    ('MERIEM', 'KHELIL', 'khelil.meriem@gmail.com', '242431575703', NULL, '$2y$10$D9ytv4GgW4WQ0/XtePChQ.g7oaES8fLShWB2.ROPGlSTT4a5SulbK', 0, 1),
    ('MARIA', 'KHELLAS', 'khellas.maria@gmail.com', '242431486807', NULL, '$2y$10$aaZtShdqVGYkXb8B5KesGOAeToktk1fQtOK0EakgrUWL6aD/hKWxa', 0, 1),
    ('IMEDEDDIEN', 'KHETTAB', 'khettab.imededdien@gmail.com', '232331734515', NULL, '$2y$10$eHjjtKVqplngmQnL8lKpZ.mERhM2GduuqZsohkQEO2dMnEGbGidpm', 0, 1),
    ('ABDERRAHMANE', 'LAGRAA', 'lagraa.abderrahmane@gmail.com', '242431431503', NULL, '$2y$10$Ls81YGcyDxoOScGnBWCUOe7d1pBxfjUbYc9f.6XjCveaP9r4XSI2m', 0, 1),
    ('ABD EL DJALIL', 'LAIB', 'laib.abd@gmail.com', '242431454303', NULL, '$2y$10$/S8B90gmC1q3Md/VTbTYgORdjL9HMlOcuSWOFht2Q8H2ymv/Qc4Je', 0, 1),
    ('RACIM', 'LAIDI', 'laidi.racim@gmail.com', '232331532706', NULL, '$2y$10$dLr2z5gEqM7MJvDzk5v29OS9UXSsiiwdKA49Pxh4wLtMOBpFyq2P.', 0, 1),
    ('DEKRAH', 'LAKEHAL', 'lakehal.dekrah@gmail.com', '242431577219', NULL, '$2y$10$3OGEZ3zU99q.tQHMH0.4oO58l3bEBqXGrDF5wYA.SSER5BEfoiqdK', 0, 1),
    ('AYOUB', 'LAMARA', 'lamara.ayoub@gmail.com', '222231412710', NULL, '$2y$10$UZ.l07WOGVkwCuirCCZSfemP4EtZBlnPHuYGxG8WPrADiDvx.rAmK', 0, 1),
    ('MELYNA', 'LAMARA', 'lamara.melyna@gmail.com', '242431618608', NULL, '$2y$10$663wAqVQd6DITWinx6zHQeN3HYlw1BnCcx5a1p0mIHWY1HmsCGBr6', 0, 1),
    ('LINA', 'LARBI', 'larbi.lina@gmail.com', '232331531201', NULL, '$2y$10$c0ZUMa4nnNaXeAGe76yr0u.theWdHuKsDljrWuYjON7/2y.J94/tG', 0, 1),
    ('NOURA', 'LASLEDJ', 'lasledj.noura@gmail.com', '242431386417', NULL, '$2y$10$btosT9NmNnyiRBy/ZUFnR.jnlbuDCAn6bW/MA.xXULw6Zwl2eyeW.', 0, 1),
    ('AISSA', 'LOUCIF', 'loucif.aissa@gmail.com', '232331639705', NULL, '$2y$10$co.6L.m.TOrZODP7eA0OKegP.Rwsdu6Fa7pI1WNGPCSaWEMr16ks2', 0, 1),
    ('SALEM', 'MADIOU', 'madiou.salem@gmail.com', '242431441601', NULL, '$2y$10$nqJKASu4FkvRLtaGD0ivHORUHa/0YY.WkA73KNZuWeybOUwjufCM.', 0, 1),
    ('MALAK', 'MADJENE', 'madjene.malak@gmail.com', '23239DZA2098', NULL, '$2y$10$K.hlF4CTpkl0cBQwp6viweRRtcLn4BD4cwK/Dnlv1lzc6O5CNMRaW', 0, 1),
    ('AYMEN AYOUB SOFIANE', 'MAHALELAINE', 'mahalelaine.aymen@gmail.com', '242431579806', NULL, '$2y$10$BR31Jt2WpiiuSsU0i.JDY.cFBggLlSux9B0RfORpSkhR3yT.QzLI2', 0, 1),
    ('MELINA', 'MAHDI', 'mahdi.melina@gmail.com', '242431475712', NULL, '$2y$10$YLOAu6fM2jGemUk.v3fTDOYE4RBw4k0ZcwCd7YL0S5zOA94eGFT1a', 0, 1),
    ('MOHAMED NAZIM', 'MAHDI', 'mahdi.mohamed@gmail.com', '232331717713', NULL, '$2y$10$eJQV6GQGhesl9OjAiMa1I.bYRIp93IPnLu77mWvCOr4H6tbnYSzKK', 0, 1),
    ('MARYA', 'MAHROUG', 'mahroug.marya@gmail.com', '232431549320', NULL, '$2y$10$I6dOgnttn8ZHXVk169v3auyjN6YJv0zfa4V8SWGeI9X46TdZaindS', 0, 1),
    ('YASMINE', 'MAMMERI', 'mammeri.yasmine@gmail.com', '232331503216', NULL, '$2y$10$Z1M3QnOTnUUVhA2CaUI06.naP9E/KclM7DVxrJBk69FSDOIdRjVdS', 0, 1),
    ('MOHAMED RAFIK', 'MAOUCHE', 'maouche.mohamed@gmail.com', '242431562616', NULL, '$2y$10$PQYw3iwSsDB2ca5E7CbQq.FdWm4Xq36LwCPjAGfd30/BhfkCs.bM2', 0, 1),
    ('OUIAM', 'MECHAI', 'mechai.ouiam@gmail.com', '232331602210', NULL, '$2y$10$V6yELU8nRY2WgDaMyaZa5.G7pUdnEQ//1tRm7J7NXKukJckaMBvs2', 0, 1),
    ('ABDALLAH', 'MECHTI', 'mechti.abdallah@gmail.com', '232331223806', NULL, '$2y$10$L/vedAd4f2emKawzu2XWEuFzXoqrGOEzxuxF206PUbKe6ceiteQZq', 0, 1),
    ('IMENE', 'MEDDOUR', 'meddour.imene@gmail.com', '242431559810', NULL, '$2y$10$l2JH5CxJTGMDBGC2Sq5kBeHoO8AMNL5ykF6.McitspC/dALatXNS6', 0, 1),
    ('RYMA', 'MEDJAHED', 'medjahed.ryma@gmail.com', '232331532312', NULL, '$2y$10$j2.Bo.3B83lI9FpJ9gBDl.Am03dVGtwFQs4qWKv9wmVA1/cSZKaHe', 0, 1),
    ('NOUR EL ISLAM', 'MEDJDOUBI', 'medjdoubi.nour@gmail.com', '242431777714', NULL, '$2y$10$sK.k7RHpTJMdpwDEqkUEbe0ERpdvfFTQ/EPBvLyNP8cPnvGtx0YdW', 0, 1),
    ('ANFEL', 'MEFTAH', 'meftah.anfel@gmail.com', '212131040805', NULL, '$2y$10$XUGqeOXRrAJRSdbu8/Zu2O4TIhYUkrm4kdVWj.foffBeDpUH3YNEW', 0, 1),
    ('MOHAMED IDIR', 'MEKDAM', 'mekdam.mohamed@gmail.com', '232331431614', NULL, '$2y$10$embZhX8BVgLbau27e9ClB.Hcv7MYH1OjtpVtQkJ5vqqL7XXhVxdKm', 0, 1),
    ('FATIMA ZAHRA', 'MEKKI', 'mekki.fatima@gmail.com', '242431431613', NULL, '$2y$10$wkKNzO019IY8bZH0tGLxkOHI87D1.KkKR6zUJTHNy0Yk3OcxrLDGG', 0, 1),
    ('YOUCEF', 'MENIA', 'menia.youcef@gmail.com', '242431731319', NULL, '$2y$10$0MHHj3drLVvEHnadvcMINeiH1VhURu01pgYukw7H0LbN2v7qwS8xC', 0, 1),
    ('IKRAM', 'MERAR', 'merar.ikram@gmail.com', '242431591407', NULL, '$2y$10$lrOjvARNYkdrQ.x2/Ker/.r8GGJwrgPniTatz4FcKjPHGGn6RudPe', 0, 1),
    ('WASSIM', 'MESSAOUDI', 'messaoudi.wassim@gmail.com', '242431622106', NULL, '$2y$10$GfERWRmYCXCamJsB8.pwxuHKSRXROCCSxrDwRExGHLVJZwkUbWTEy', 0, 1),
    ('ABD RAOUF', 'MEZIANI', 'meziani.abd@gmail.com', '242431434209', NULL, '$2y$10$2qIYW5Y.ceGOAiSa7/xVyeKwS2UX8JLkBOPFMLuUpm64tPZ8cRPzm', 0, 1),
    ('SERINE MELISSA', 'MEZIANI', 'meziani.serine@gmail.com', '242431666406', NULL, '$2y$10$KYf170nsROWsKYwG2yTi/OO3oJwgSAs3HP.drWa2OI8r/2Ga9gYOO', 0, 1),
    ('ABDERRAHMAN', 'MOKHTARI', 'mokhtari.abderrahman@gmail.com', '222231498417', NULL, '$2y$10$BwbBBcBjbstmuQxvE65TLuLolvwyvOmV993EmoQlH7T2yoh1yvGsK', 0, 1),
    ('ALI', 'MOKNINE', 'moknine.ali@gmail.com', '212131087391', NULL, '$2y$10$w07K/5pHtFY.uhg1PKH9MuqNT8bI4QZr5J/jsY4bE2QgbZg1Q5/9i', 0, 1),
    ('MOHAMED HOCINE', 'MOSTEFA', 'mostefa.mohamed@gmail.com', '232331674811', NULL, '$2y$10$fdt4xRlAsXy/PR4n5FV/Guk0fN0YpDji4GEYz5TK.QnNJIWgh/VlK', 0, 1),
    ('SARAH CHYRAZ', 'MOUSSOUS', 'moussous.sarah@gmail.com', '232431861119', NULL, '$2y$10$6uBD0krB96wGMojCVechXO7MnOtJt07a.1Qo9I.NwY9BYPSSYbsQe', 0, 1),
    ('KENZA', 'MOUZAOUI', 'mouzaoui.kenza@gmail.com', '232431535911', NULL, '$2y$10$xLsfYO2vnlGjC7Nwn409.OphLa8Sj6DokB/6NGp1mP9VHpAKHPin.', 0, 1),
    ('MUSTAPHA IYAD', 'NAIMI', 'naimi.mustapha@gmail.com', '242431618418', NULL, '$2y$10$pSQKa7El2kzeJoe0L0noCOGYOLGVdGX/2Tro8lHWLnongz2v0ZFhy', 0, 1),
    ('ANES ZAKARIA', 'NASRI', 'nasri.anes@gmail.com', '242439340418', NULL, '$2y$10$0U4aG7E3WAXwYa2QF7r7r.EOKjx2qYfKT1pg77yNpxntwYFa3mM8G', 0, 1),
    ('AHMED BAHA EDDINE', 'NEDIR', 'nedir.ahmed@gmail.com', '242431367805', NULL, '$2y$10$J7xZR5p8XEUvkgLRvlqFS.50YnuwgtTAAhrGjG99hRyX5c.zGmDce', 0, 1),
    ('Souheil', 'NID', 'nid.souheil@gmail.com', '232331032114', NULL, '$2y$10$rnipjAhPDZXKzKJFmhJXqui2mL0XOdhMqtGVZFSEddOFb94VSzNuK', 0, 1),
    ('HICHEM', 'OUAREZKI', 'ouarezki.hichem@gmail.com', '242431398806', NULL, '$2y$10$Xet.jXpxWW8QCEDnsBvQB.mwAIkAQ4uPJRh4xmZcwZ6NoF44giTfm', 0, 1),
    ('FERHAT MOHAMED ANIS', 'OUGUENOUNE', 'ouguenoune.ferhat@gmail.com', '242431433205', NULL, '$2y$10$PY3EZez735DXWGrbz.M1BehkoXjYui0e2ir69dWDjxLPbaopeed9y', 0, 1),
    ('CHAIMA', 'OULDEDINE', 'ouldedine.chaima@gmail.com', '232331595914', NULL, '$2y$10$Ju.3/f07MOGaM.IDxRGBDut7Q1sGBNq5ptXWuPD2KqlZClUFxAtqO', 0, 1),
    ('CELINA', 'RABEHI', 'rabehi.celina@gmail.com', '232431531515', NULL, '$2y$10$l1qm4XSnoQ92e3VOw5Qkze1MJIguYX7f27n.qV75YbUVhXpTm7lBm', 0, 1),
    ('ANIS', 'RAHILI', 'rahili.anis@gmail.com', '242431572814', NULL, '$2y$10$OAnxqw9JllRJNnZGe9hmhOCZh48u2xqLdSZgfLjre7WEt5DSkB3yu', 0, 1),
    ('DOUAA HIBAT ELLAH', 'RAMDANI', 'ramdani.douaa@gmail.com', '232331430814', NULL, '$2y$10$b775GuBHDNSbecMwJgQd/OqTcRQWIEehmHN6oCCeKUjXPCQlpLTg.', 0, 1),
    ('MERIEM', 'RAMOUL', 'ramoul.meriem@gmail.com', '242431422801', NULL, '$2y$10$JefBG7Ig0I0oVlnN0OyQXOVv4wOlFoA.rR5HTfeKHOPUtb1n4an9u', 0, 1),
    ('AHMED ELAMINE', 'REMRAM', 'remram.ahmed@gmail.com', '242431383508', NULL, '$2y$10$edISqK7zgpotT44Xn5JQL.VWgYJp0XL1gH8AVabFgDm58sq01xJES', 0, 1),
    ('ABDALLAH', 'RETIM', 'retim.abdallah@gmail.com', '232431859120', NULL, '$2y$10$14Ve7P.LfF5n2ZHr26aDhu7.0AfqQnhl6g2ydeFGwEwZIn.EjuT0K', 0, 1),
    ('RIAD', 'REZKI', 'rezki.riad@gmail.com', '242431624912', NULL, '$2y$10$aLDg6mqPfxn5G2mS7vTIde3WJAEcc2IIsQh4Osz5GuYF3iVFX4WKi', 0, 1),
    ('AMINA', 'ROUIBAH', 'rouibah.amina@gmail.com', '232433341813', NULL, '$2y$10$n6A/ZV.izfzX7dNX6k8UCeMCZZzlp5MUrxrsMu7/ZEKrZfF1QFPle', 0, 1),
    ('ISLEM', 'SAADI', 'saadi.islem@gmail.com', '232331698506', NULL, '$2y$10$Q.AcKJLJPUBJoozfJFF1PO/FAqO.vIBSbRpki0HQTUVnVhYsFLqyi', 0, 1),
    ('MEZIANE', 'SAIB', 'saib.meziane@gmail.com', '232331105319', NULL, '$2y$10$L833SKMGkfB6pXG4oO3jXukBkEXaNduy6Kbo8v8TSNuMOKVqcL62m', 0, 1),
    ('MOHAMED DHIAEDDINE', 'SAIDANI', 'saidani.mohamed@gmail.com', '242431370913', NULL, '$2y$10$Dvwo3Glm39Dy3G6SiVBJ0.9jYs3JftcAYGTZ51hfBtslqTze1d2mS', 0, 1),
    ('AIMEN ABDELOUAHID', 'SBAI', 'sbai.aimen@gmail.com', '232431526712', NULL, '$2y$10$qO3RteAXJGZmaoY5RG1tw.KBJCjQ46OLiOgX7u5uZLJtQs9aqp60u', 0, 1),
    ('YOUNES MEHDI', 'SEDAOUI', 'sedaoui.younes@gmail.com', '242431601409', NULL, '$2y$10$AMtFixuYhmMZfK82FxBBTuCMCRqMRMqfDyx34yZ.M0zInX.nODFOy', 0, 1),
    ('WASSIM', 'SELAMA', 'selama.wassim@gmail.com', '242431696012', NULL, '$2y$10$hnBnJbYez/8ZzT1YArCpl.ESV6zkqpwui72d1aq6v1h5LS8H3Hr.u', 0, 1),
    ('MOHAMED RACIM', 'SEMMAR', 'semmar.mohamed@gmail.com', '242431621604', NULL, '$2y$10$0c8IjMxBK5VEgGdbH8kf8uhCp18tUmgSI9YuBykLuylgDzlGSSvxy', 0, 1),
    ('IMENE', 'SERAY', 'seray.imene@gmail.com', '242431423801', NULL, '$2y$10$U0PwbrMX0bBE5onFrTDaguYZlZKT8uEBCIs11n982KJGKyotrb49O', 0, 1),
    ('NEWFEL', 'SKENDER', 'skender.newfel@gmail.com', '242431680409', NULL, '$2y$10$V4/v4WIkbBfi6BDqimi9veAjGmznVZDqasozh8fHWlXfEygrEEl.q', 0, 1),
    ('ANIS', 'SLIMANI', 'slimani.anis@gmail.com', '232331659203', NULL, '$2y$10$l58szLKoe1.1SkYhYr5e3ed0a9UQI5DoA8DJCOI2u9coLapZW5i9.', 0, 1),
    ('IMAD', 'SLIMANI', 'slimani.imad@gmail.com', '242431461919', NULL, '$2y$10$iTbq.mNnjrw9HPeRx2xRh.xGAZhQG0Cbt/SkLxOjI7fbShufLiI72', 0, 1),
    ('MOUDJIB EL RAHMANE', 'SOUFI', 'soufi.moudjib@gmail.com', '232331597818', NULL, '$2y$10$oIARTfDzXGlPdMKB1ISZO.d.Z/AupYiKP38OuyTxg6oLW63mYLxT.', 0, 1),
    ('ICHRAK', 'SOUIDI', 'souidi.ichrak@gmail.com', '232331500812', NULL, '$2y$10$jvrxg085XKAPgxNo27R1Qu0N7mHzA8hl4EkzwRZSEyU7RxQDNEOkW', 0, 1),
    ('MEHDI ABDELHAKIM', 'SOUISSI', 'souissi.mehdi@gmail.com', '222231649017', NULL, '$2y$10$8gu4erCvlSGkLlyeKFwl7eqFvda3LTDbA3MgXMim2wOKwBPnv326u', 0, 1),
    ('ABDEL MOUMENE', 'TAKRATI', 'takrati.abdel@gmail.com', '232332212801', NULL, '$2y$10$TaXWjKBtBHfRj2m1UeG2guQx5KN5qsP5yYxaMV9IFJKiFVi8OvHKq', 0, 1),
    ('ISLAM', 'TAS', 'tas.islam@gmail.com', '242431572917', NULL, '$2y$10$4Qn9mROYXLu/5wi4PebdFuGwxNWQNZaPVTVyAWzrMWLpvAX4WDJtC', 0, 1),
    ('ANES', 'TATA', 'tata.anes@gmail.com', '242431624311', NULL, '$2y$10$c7dImufAUBA7EAaaaZr3lu3qnrBJQUW73r/UjUHeOw8m3u3PUxg4C', 0, 1),
    ('NACIM', 'TAYEB', 'tayeb.nacim@gmail.com', '242431596506', NULL, '$2y$10$cZ71p9C61q88c.dRCb/vneEX1uY9rf0aQayMMf1LKHKSz.fwZWRsy', 0, 1),
    ('SAMI AYOUB', 'TEHAR', 'tehar.sami@gmail.com', '232431845311', NULL, '$2y$10$IVJRQETDzM0vtxAsm7nHjudNAMz4gT2wwYbjySAnNb578uxrlxAPS', 0, 1),
    ('MOHAMED DJAOUED', 'TEMAM', 'temam.mohamed@gmail.com', '242431722303', NULL, '$2y$10$1C7LBZGcXOw1yyzhXOwMMOHdU6iUMl/Av2HiCDyHsmRfkzjs8w6Pa', 0, 1),
    ('OUSSAMA', 'TEMLALI', 'temlali.oussama@gmail.com', '232331734201', NULL, '$2y$10$4KI00q0cNno9clde/py/t.2CrG3jtzB6jpW9UnD14vdLQ8vxOph9y', 0, 1),
    ('MOHAMED ADEM AYOUB', 'TOUAT', 'touat.mohamed@gmail.com', '242431680215', NULL, '$2y$10$bPCbES0zp4cWksZ4aJkIt.31fiUFAIHvDNKtm2mmv4JJI8roZK3bW', 0, 1),
    ('NESSRINE', 'TSAMDA', 'tsamda.nessrine@gmail.com', '242431730516', NULL, '$2y$10$8jAVOvwHluthUPIAt9Dl1.6LWUukTNDD9udZnJMYvm/9PL0X/FtZK', 0, 1),
    ('Abdelhak', 'YESSAD', 'yessad.abdelhak@gmail.com', '2424354270', NULL, '$2y$10$KehieSFxoVNUxrs/nLfn.esu7Hv6fPp4lBqVPAMrJ7Crvrrmmt5rq', 0, 1),
    ('ABD ELRAHMAN', 'ZAHAF', 'zahaf.abd@gmail.com', '232331338807', NULL, '$2y$10$kAg4axtvPnycXBWlP5nVY.YLMWchf4IuVjF6QL2ppdZB6BkWPy9ay', 0, 1),
    ('RAYAN', 'ZAHED', 'zahed.rayan@gmail.com', '232331394803', NULL, '$2y$10$qHNPWhps1pmnOc9iTm2k5u8T.CfkTv4IzWVOrb.Ot6OTGNJpyW9wq', 0, 1),
    ('Madi', 'ZAKARIA', 'zakaria.madi@gmail.com', '232431844615', NULL, '$2y$10$I22sywk9M38ATc.QBl0bkehCoK9BVaeWD0P7/1bMMItAHaCFIr.tO', 0, 1),
    ('ABDENOUR', 'ZEGHDANE', 'zeghdane.abdenour@gmail.com', '232431534320', NULL, '$2y$10$IgRLdyjg3b3LvKiMW7BICOX4Bn0.DpX9vkdrJGtakI61c7Y7Cp0MS', 0, 1),
    ('MAYA', 'ZERAIA', 'zeraia.maya@gmail.com', '242431748813', NULL, '$2y$10$Xmco.6rO2AXmY1N/dI2tNuu409Ip1Ma/Lzx/ltSUHlasH8P3rtMtS', 0, 1),
    ('ILYES', 'ZERGOUN', 'zergoun.ilyes@gmail.com', '232331481012', NULL, '$2y$10$87cJW50OmwMnAhuHOHwV4Ok7vXytSXNyaQ.yF61K4CN5tTloOQ6cC', 0, 1),
    ('RITADJE', 'ZERGUI', 'zergui.ritadje@gmail.com', '222431858709', NULL, '$2y$10$8U9YEGJkolNFhfNoMoEJje2bFKZMsn2ehcZZ3unjlY.e1NGb9O8iy', 0, 1),
    ('WALID', 'ZERKOUK', 'zerkouk.walid@gmail.com', '242431680417', NULL, '$2y$10$KbrOw/wWdXNdRT6lfn7m7.DuD.hxp03/W.bHUq1rXl938tXDlbl1m', 0, 1),
    ('RABAH HICHEM', 'ZERTIT', 'zertit.rabah@gmail.com', '242431614911', NULL, '$2y$10$slJIqQ5wB8eug/nVL/NZuOxNoXhIRmaQfSIkhMwrpjcWgV4YrBOUy', 0, 1),
    ('DAMIA FARIEL', 'ZIANE', 'ziane.damia@gmail.com', '232431847516', NULL, '$2y$10$qw3x7pFJOiPfbjDrGFVev.cRtJBvCOpI3Ggcr/kHGfadzK6dVLF2O', 0, 1),
    ('YACINE', 'ZIGADI', 'zigadi.yacine@gmail.com', '202331414107', NULL, '$2y$10$.hCeJGqCwk7KSivVlGW.eupDlcW3W/ym5xvX3kiTBIQoCt9oUIAQu', 0, 1),
    ('IMEN', 'ZIGHED', 'zighed.imen@gmail.com', '232335330411', NULL, '$2y$10$ouS0tmieO5YDZdzxYKFjHuuULzj4JdZT6BU4J/4IZSOdbBQOLZA46', 0, 1),
    ('SABER', 'ZITOUNI', 'zitouni.saber@gmail.com', '232331650909', NULL, '$2y$10$z/CJLMaie03Qs1BBfArC6OGeqxc5fwr3NRwPzLSm/zi.W3KULVmhi', 0, 1),
    ('', '', '.@gmail.com', '232331346601', NULL, '$2y$10$CV8Wv1mFlYt0.d11QZisTeITWOZLWnKpFSlJSyVXREikzoX.OmJVO', 0, 1)
ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    student_number = VALUES(student_number),
    birth_date = VALUES(birth_date),
    password_hash = VALUES(password_hash),
    must_change_password = VALUES(must_change_password),
    is_active = VALUES(is_active);


-- -----------------------------------------------
-- ENROLLMENTS: Imen Zighed (232335330411) in all 5 modules
-- -----------------------------------------------
INSERT INTO enrollments (student_id, module_id, academic_year)
SELECT s.id, m.id, '2025/2026'
FROM students s, modules m
WHERE s.student_number = '232335330411'
ON DUPLICATE KEY UPDATE academic_year = VALUES(academic_year);

-- -----------------------------------------------
-- NOTES: Imen Zighed - all 5 modules
-- -----------------------------------------------
INSERT INTO notes (student_id, module_id, grade, academic_year)
SELECT s.id, m.id,
    CASE m.code
        WHEN 'THG'   THEN 16.50   -- Theorie des graphes (Amina)
        WHEN 'BDD'   THEN 14.00   -- Base de donnees
        WHEN 'ARCHI' THEN 12.75   -- Architecture
        WHEN 'GL'    THEN 15.25   -- Genie logiciel
        WHEN 'PWEB'  THEN 17.00   -- Programmation web
    END AS grade,
    '2025/2026'
FROM students s, modules m
WHERE s.student_number = '232335330411'
  AND m.code IN ('THG','BDD','ARCHI','GL','PWEB')
ON DUPLICATE KEY UPDATE grade = VALUES(grade);

-- -----------------------------------------------
-- ENROLLMENTS: all students in THG module (Amina Gheffar)
-- (enroll a representative sample so Amina can enter grades)
-- -----------------------------------------------
INSERT INTO enrollments (student_id, module_id, academic_year)
SELECT s.id, m.id, '2025/2026'
FROM students s, modules m
WHERE m.code = 'THG'
  AND s.student_number IN (
    '232335330411','212431859912','232431546203','242431599204',
    '222231609707','242431676416','232331500107','242431370909',
    '232331413601','242431368913','222231413217','242431438719',
    '242431577510','222231581410','242431461716','232333374911',
    '242431453208','222231438707','232332170007','232331499219',
    '232333087110','232331738702','242431730502','222233370909',
    '242431620609','232331388007','232331412506','232331740006',
    '242431597817','232331667419','232331441703','232331715109',
    '242431715620','222231345706','242431460816','242431461920',
    '242431786010','232331692611','242431596411','212431656304',
    '242431680418','242431675005','232431652101','242431622804',
    '232331424405','242431440109','232331499415','232335477206'
  )
ON DUPLICATE KEY UPDATE academic_year = VALUES(academic_year);

-- -----------------------------------------------
-- NOTES: Amina Gheffar's module THG - realistic grades for enrolled students
-- -----------------------------------------------
INSERT INTO notes (student_id, module_id, grade, academic_year)
SELECT s.id, m.id,
    CASE s.student_number
        WHEN '232335330411' THEN 16.50
        WHEN '212431859912' THEN 13.00
        WHEN '232431546203' THEN 11.50
        WHEN '242431599204' THEN 14.75
        WHEN '222231609707' THEN 09.00
        WHEN '242431676416' THEN 15.50
        WHEN '232331500107' THEN 12.00
        WHEN '242431370909' THEN 10.25
        WHEN '232331413601' THEN 17.00
        WHEN '242431368913' THEN 08.50
        WHEN '222231413217' THEN 13.75
        WHEN '242431438719' THEN 16.00
        WHEN '242431577510' THEN 11.00
        WHEN '222231581410' THEN 14.25
        WHEN '242431461716' THEN 07.50
        WHEN '232333374911' THEN 18.00
        WHEN '242431453208' THEN 12.50
        WHEN '222231438707' THEN 10.00
        WHEN '232332170007' THEN 15.00
        WHEN '232331499219' THEN 09.50
        WHEN '232333087110' THEN 13.25
        WHEN '232331738702' THEN 16.75
        WHEN '242431730502' THEN 11.75
        WHEN '222233370909' THEN 14.50
        WHEN '242431620609' THEN 08.00
        WHEN '232331388007' THEN 17.50
        WHEN '232331412506' THEN 12.25
        WHEN '232331740006' THEN 10.75
        WHEN '242431597817' THEN 15.75
        WHEN '232331667419' THEN 13.50
        WHEN '232331441703' THEN 09.25
        WHEN '232331715109' THEN 16.25
        WHEN '242431715620' THEN 11.25
        WHEN '222231345706' THEN 14.00
        WHEN '242431460816' THEN 12.75
        WHEN '242431461920' THEN 07.75
        WHEN '242431786010' THEN 15.25
        WHEN '232331692611' THEN 10.50
        WHEN '242431596411' THEN 13.00
        WHEN '212431656304' THEN 17.25
        WHEN '242431680418' THEN 09.75
        WHEN '242431675005' THEN 14.75
        WHEN '232431652101' THEN 11.50
        WHEN '242431622804' THEN 16.50
        WHEN '232331424405' THEN 08.25
        WHEN '242431440109' THEN 15.00
        WHEN '232331499415' THEN 13.75
        WHEN '232335477206' THEN 12.00
    END AS grade,
    '2025/2026'
FROM students s, modules m
WHERE m.code = 'THG'
  AND s.student_number IN (
    '232335330411','212431859912','232431546203','242431599204',
    '222231609707','242431676416','232331500107','242431370909',
    '232331413601','242431368913','222231413217','242431438719',
    '242431577510','222231581410','242431461716','232333374911',
    '242431453208','222231438707','232332170007','232331499219',
    '232333087110','232331738702','242431730502','222233370909',
    '242431620609','232331388007','232331412506','232331740006',
    '242431597817','232331667419','232331441703','232331715109',
    '242431715620','222231345706','242431460816','242431461920',
    '242431786010','232331692611','242431596411','212431656304',
    '242431680418','242431675005','232431652101','242431622804',
    '232331424405','242431440109','232331499415','232335477206'
  )
ON DUPLICATE KEY UPDATE grade = VALUES(grade);
