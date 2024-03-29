<?php include 'verificationUtilisateur.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" type="text/css" href="menu.css">
</head>
<body>
    <?php
        include 'menu.php'; // Menu de navigation
        include 'connexion_bd.php'; // Connexion à la BD
    ?>

    <h1> Statistiques </h1>
    <section>
        <?php
            // Répartition des usagers par sexe et âge
            $querySexeAge = $linkpdo->query('
                SELECT 
                    CASE
                        WHEN YEAR(CURDATE()) - YEAR(DateNaissance) < 25 THEN "Moins de 25 ans"
                        WHEN YEAR(CURDATE()) - YEAR(DateNaissance) BETWEEN 25 AND 50 THEN "Entre 25 et 50 ans"
                        ELSE "Plus de 50 ans"
                    END AS TrancheAge,
                    COUNT(*) AS NbUsagers,
                    Civilité
                FROM usagers
                GROUP BY TrancheAge, Civilité
            ');

            $statistiquesSexeAge = [];

            // Collecter les statistiques sur la répartition des usagers par sexe et âge
            while ($row = $querySexeAge->fetch()) {
                $trancheAge = $row['TrancheAge'];
                $sexe = $row['Civilité'];
                $nbUsagers = $row['NbUsagers'];

                if (!isset($statistiquesSexeAge[$trancheAge])) {
                    $statistiquesSexeAge[$trancheAge] = [0, 0];
                }
                $statistiquesSexeAge[$trancheAge][$sexe == 'F' ? 1 : 0] = $nbUsagers;
            }
        ?>
        <!-- Affichage du tableau de répartition par sexe et âge -->
        <h3>Répartition des usagers par sexe et âge</h3>
        <table>
            <tr>
                <th>Tranche d'âge</th>
                <th>Nombre d'hommes</th>
                <th>Nombre de femmes</th>
            </tr>
            <?php foreach ($statistiquesSexeAge as $trancheAge => $stat) : ?>
                <tr>
                    <td><?= $trancheAge ?></td>
                    <td><?= $stat[0] ?></td>
                    <td><?= $stat[1] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <br/><br/>

        <?php
            // Durée totale des consultations par médecin
            $queryDureeConsultation = $linkpdo->query('
                SELECT 
                    medecins.Nom AS Nom, medecins.Prénom AS Prénom, 
                    SUM(rendezvous.DuréeConsultation) AS DureeTotale
                FROM rendezvous
                JOIN medecins ON rendezvous.ID_Medecin = medecins.ID_Medecin
                GROUP BY medecins.Nom, medecins.Prénom 
            ');

            $statistiquesDureeConsultation = [];

            // Collecter les statistiques sur la durée totale des consultations par médecin
            while ($row = $queryDureeConsultation->fetch()) {
                $medecinNom = $row['Nom'];
                $medecinPrenom = $row['Prénom'];
                $dureeTotale = $row['DureeTotale'] / 60;
                $statistiquesDureeConsultation[] = [$medecinNom, $medecinPrenom, $dureeTotale];
            }
        ?>
        <!-- Affichage du tableau de la durée totale des consultations par médecin -->
        <h3>Durée totale des consultations par médecin</h3>
        <table>
            <tr>
                <th>Nom du Médecin</th>
                <th>Prénom du Médecin</th>
                <th>Durée totale (heures)</th>
            </tr>
            <?php foreach ($statistiquesDureeConsultation as $stat) : ?>
                <tr>
                    <td><?= $stat[0] ?></td>
                    <td><?= $stat[1] ?></td>
                    <td><?= round($stat[2], 1) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>
    ?>

</body>
</html>