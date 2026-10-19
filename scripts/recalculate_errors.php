<?php
/*
 * recalculate_errors.php
 * ----------------------
 * Relit le texte d'analyse de chaque résultat et recalcule
 * les compteurs d'erreurs + le score à partir du texte.
 * ⚠️ SUPPRIMER après utilisation !
 */

include("config.php");

$dry_run = isset($_GET['dry_run']) && $_GET['dry_run'] == '1';

echo "<pre style='font-family:monospace; font-size:0.85rem;'>";
echo "=== RECALCUL DES ERREURS DEPUIS L'ANALYSE ===\n";
echo ($dry_run ? "MODE SIMULATION (dry_run=1) — aucune modification\n" : "MODE RÉEL — les données seront modifiées\n");
echo "Pour simuler d'abord : ajouter ?dry_run=1 à l'URL\n\n";

// Récupérer tous les résultats qui ont une analyse non vide
$result = $conn->query("
    SELECT id_resultat, analyse, faute_orthographique, mot_ou_ponctuation_manquant,
           accord_adjectif, nom_pluriel, accord_sujet_verbe, score
    FROM g1_dictes_resultats
    WHERE analyse IS NOT NULL AND analyse != ''
    ORDER BY id_resultat
");

if (!$result) {
    echo "Erreur SQL: " . $conn->error . "\n";
    exit;
}

$total = 0;
$updated = 0;
$skipped = 0;
$errors_found = [];

while ($row = $result->fetch_assoc()) {
    $total++;
    $id = $row['id_resultat'];
    $analyse = strtolower($row['analyse']);

    // Compter les erreurs depuis le texte d'analyse
    $ortho   = substr_count($analyse, "faute orthographique :");
    $manquant = substr_count($analyse, "mot manquant :")
              + substr_count($analyse, "ponctuation manquante :")
              + substr_count($analyse, "mot en trop :")
              + substr_count($analyse, "ponctuation en trop :");
    $adj     = substr_count($analyse, "accord adjectif :");
    $pluriel = substr_count($analyse, "pluriel nom :");
    $sv      = substr_count($analyse, "accord sujet-verbe :");

    $nb_erreurs = $ortho + $manquant + $adj + $pluriel + $sv;
    $score = max(0, 20 - $nb_erreurs);

    // Comparer avec les valeurs actuelles
    $old_ortho   = (int)$row['faute_orthographique'];
    $old_manquant = (int)$row['mot_ou_ponctuation_manquant'];
    $old_adj     = (int)$row['accord_adjectif'];
    $old_pluriel = (int)$row['nom_pluriel'];
    $old_sv      = (int)$row['accord_sujet_verbe'];
    $old_score   = (int)$row['score'];

    $changed = ($ortho != $old_ortho || $manquant != $old_manquant ||
                $adj != $old_adj || $pluriel != $old_pluriel ||
                $sv != $old_sv || $score != $old_score);

    if ($changed) {
        $updated++;
        echo "ID $id :\n";
        if ($ortho != $old_ortho)    echo "  ortho       : $old_ortho → $ortho\n";
        if ($manquant != $old_manquant) echo "  mot/ponct   : $old_manquant → $manquant\n";
        if ($adj != $old_adj)        echo "  accord adj  : $old_adj → $adj\n";
        if ($pluriel != $old_pluriel) echo "  pluriel nom : $old_pluriel → $pluriel\n";
        if ($sv != $old_sv)          echo "  accord sv   : $old_sv → $sv\n";
        if ($score != $old_score)    echo "  score       : $old_score → $score\n";

        if (!$dry_run) {
            $conn->query("
                UPDATE g1_dictes_resultats SET
                    faute_orthographique       = $ortho,
                    mot_ou_ponctuation_manquant = $manquant,
                    accord_adjectif            = $adj,
                    nom_pluriel                = $pluriel,
                    accord_sujet_verbe         = $sv,
                    score                      = $score
                WHERE id_resultat = $id
            ");
            if ($conn->error) {
                echo "  ❌ Erreur SQL: " . $conn->error . "\n";
            } else {
                echo "  ✅ Mis à jour\n";
            }
        }
        echo "\n";
    } else {
        $skipped++;
    }
}

echo "=== RÉSUMÉ ===\n";
echo "Total résultats avec analyse : $total\n";
echo "À mettre à jour              : $updated\n";
echo "Déjà corrects                : $skipped\n";

if ($dry_run && $updated > 0) {
    echo "\n→ Pour appliquer les modifications, ouvrir sans ?dry_run=1\n";
} elseif (!$dry_run && $updated > 0) {
    echo "\n✅ Mise à jour terminée !\n";
}

echo "</pre>";
?>
