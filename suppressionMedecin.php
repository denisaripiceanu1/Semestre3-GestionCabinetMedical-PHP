<?php include 'verificationUtilisateur.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suppression du Médecin</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" type="text/css" href="menu.css">
</head>
<body>
    <?php 
        include 'menu.php'; // Menu de navigation
        include 'connexion_bd.php'; // Connexion à la BD

        if (isset($_GET['id'])) {
            // Récupérer l'identifiant du médecin depuis l'URL
            $id = $_GET['id'];

            // Sélectionner les informations du médecin correspondant dans la base de données
            $query = $linkpdo->prepare('SELECT * FROM Medecins WHERE ID_Medecin = :id');
            $query->execute(array('id' => $id));
            $medecin = $query->fetch();

            if ($medecin) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Si le formulaire a été soumis
                    if (isset($_POST['confirmation']) && ($_POST['confirmation'] === 'oui' || $_POST['confirmation'] === 'non')) {
                        // Si la confirmation est "oui", supprimer le médecin
                        if ($_POST['confirmation'] === 'oui') {
                            try {
                                // Vérifier si le médecin est médecin référent
                                $estMedecinReferent = $linkpdo->prepare('SELECT COUNT(*) FROM Usagers WHERE MédecinRéférent = :id');
                                $estMedecinReferent->execute(array('id' => $id));
                                $resultat = $estMedecinReferent->fetchColumn();

                                if ($resultat > 0) {
                                    // Le médecin est référent, mettre à jour la table Usagers
                                    $updateUsagers = $linkpdo->prepare('UPDATE Usagers SET MédecinRéférent = NULL WHERE MédecinRéférent = :id');
                                    $updateUsagers->execute(array('id' => $id));
                                }

                                // Supprimer les rendez-vous associés au médecin
                                $deleteRendezVousQuery = $linkpdo->prepare('DELETE FROM RendezVous WHERE ID_Medecin = :id');
                                $deleteRendezVousQuery->execute(array('id' => $id));

                                // Supprimer le médecin
                                $deleteQuery = $linkpdo->prepare('DELETE FROM Medecins WHERE ID_Medecin = :id');
                                $deleteQuery->execute(array('id' => $id));

                                // Rediriger vers la page rechercheMedecin.php (ou la page appropriée)
                                header('Location: rechercheMedecin.php');
                                exit();
                            } catch (PDOException $e) {
                                echo 'Erreur lors de la suppression : ' . $e->getMessage();
                            }
                        } else {
                            // Si la confirmation est "non", rediriger vers la page rechercheMedecin.php sans supprimer le médecin
                            header('Location: rechercheMedecin.php');
                            exit();
                        }
                    }
                }

                // Afficher un message de confirmation avec des boutons radio stylisés
                echo '<h1>Confirmation de suppression</h1>';
                echo '<section><p><b>Êtes-vous sûr de vouloir supprimer le médecin suivant ?</b></p>';
                echo '<p>Civilité : ' . $medecin['Civilité'] . '</p>'; 
                echo '<p>Nom : ' . $medecin['Nom'] . '</p>';
                echo '<p>Prénom : ' . $medecin['Prénom'] . '</p>';
                echo '<form method="POST" action="suppressionMedecin.php?id=' . $id . '">';
                echo '<div>';
                echo '<label for="oui">Oui</label>';
                echo '<input type="radio" name="confirmation" id="oui" value="oui" required>';
                echo '</div>';
                echo '<div>';
                echo '<label for="non">Non</label>';
                echo '<input type="radio" name="confirmation" id="non" value="non" required>';
                echo '</div>';
                echo '<input type="submit" value="Valider">';
                echo '</form></section>';
            } else {
                echo '<h1>Médecin introuvable</h1>';
                echo '<section><p>Le médecin spécifié n\'existe pas.</p></section>';
            }
        } else {
            echo '<h1>Identifiant non spécifié</h1>';
            echo '<section><p>Aucun identifiant de médecin spécifié.</p></section>';
        }
    ?>
</body>
</html>
