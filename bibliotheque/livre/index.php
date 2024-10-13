<?php
require_once '../../helper/request.php';
require_once '../../helper/bdd.php';

// query('q'); : Récupère la valeur du paramètre de requête 'q' (recherche) en appelant la fonction query
$query = query('q');
// Récupère la valeur du paramètre de requête 'field' en appelant la fonction query.
$field = query('field');

// Récupère la valeur du paramètre de requête 'sort' en appelant la fonction query
//Si le paramètre 'sort' n'est pas présent dans la requête, la valeur par défaut est "asc".
$sort = query('sort', "asc");

// Appelle la fonction connection pour établir une connexion à la base de données. 
$c = connection();
//Initialise la variable $sql avec une requête SQL de base qui sélectionne les colonnes 'id', 'titre' et 'date_parution' de la table 'livre'.
$sql = "SELECT id, titre, date_parution FROM livre";

// Ajout d'une condition WHERE basée sur le paramètre 'q' :
/*
 - Si le paramètre 'q' (recherche) n'est pas nul, cette ligne ajoute une condition WHERE à la requête SQL.
 - La condition utilise le champ 'titre' et recherche les occurrences du texte spécifié dans la variable $query.
 - La fonction mysqli_real_escape_string est utilisée pour éviter les attaques par injection SQL en échappant les
  caractères spéciaux.
*/
//Vérifie si le paramètre 'q' existe dans la chaîne de requête. Cela signifie que l'utilisateur a spécifié une valeur pour la recherche ($query)
if ($query != null) {
    //La requête SQL est alors modifiée pour inclure une clause WHERE qui filtre les résultats basés sur le titre du livre en utilisant une correspondance 
    //partielle avec la valeur de recherche. La fonction mysqli_real_escape_string est utilisée pour éviter les attaques par injection SQL  
    $sql .= " WHERE titre LIKE '%" . mysqli_real_escape_string($c, $query) . "%'";
}

// Cette condition vérifie si les variables $field et $sort ne sont pas nulles: https://chatgpt.com/c/dfbc77da-e613-4866-854d-ce8e2b5ddc7f
if ($field != null && $sort != null) { // $field : représente le nom de la colonne par laquelle les résultats doivent être triés.
    if ($sort != "asc" && $sort != "desc") { // $sort : représente l'ordre de tri, 
        $sort = "asc";
    }
    // $sql : représente la chaîne de requête SQL en cours de construction.
    $sql .= " ORDER BY " . mysqli_real_escape_string($c, $field) . " " . mysqli_real_escape_string($c, $sort); //
}

// On exécute la requête SQL ($sql) sur la connexion à la base de données ($c), le resultatat es stocké dans la BDD
$result = mysqli_query($c, $sql);
// On récupérer tous les résultats de la requête sous forme d'un tableau associatif 
$livres = mysqli_fetch_all($result, MYSQLI_ASSOC);

//Le formateur est configuré pour utiliser le format complet de date en français (fr_FR) et ne pas inclure l'heure
$fmt = datefmt_create('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);

// $current_url = $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'];

require '../header.php'; ?>

<h2>Liste des livres</h2>
<!-- action: Affiche le chemin du script PHP actuellement exécuté -->
<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <div class="input-group mb-3">
        <input
        type="search" name="q" class="form-control" aria-label="Rechercher par le titre" placeholder="Rechercher par le titre" value="<?php echo $query; ?>"/>
        <?php if ($query): ?>
            <a href="/bibliotheque/livre/" class="btn btn-outline-secondary">Réinitialiser le filtre</a>
        <?php endif ?>
        <button class="btn btn-outline-secondary">Rechercher</button>
    </div>
</form>

<table class="table">
    <thead>
        <tr>
            <th>Numero</th>
            <th>Titre Du Livre</th>
            <th>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?field=date_parution&sort=<?php echo $sort === "asc" ? "desc" : "asc" ?>">
                    Date de parution
                </a>
            </th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if ($livres): ?>
            <?php foreach ($livres as $livre): ?>
                    <tr><td>
                        <?php echo htmlentities($livre['id']); ?>
                    </td>
                    <td>
                        <?php echo htmlentities($livre['titre']); ?>
                    </td>
                    <td>
                        <?php echo $livre['date_parution'] != null ? datefmt_format($fmt, date_create($livre['date_parution'])) : '-'; ?>
                    </td>
                    <td class="text-end">
                        <a href="/bibliotheque/livre/detail.php?id=<?php echo $livre['id'] ?>">Détail</a>
                        -
                        <a href="/bibliotheque/livre/modifier.php?id=<?php echo $livre['id'] ?>">Modifier</a>
                        -
                        <a href="/bibliotheque/livre/supprimer.php?id=<?php echo $livre['id'] ?>">Supprimer</a>


                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="text-center">Aucun livre</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require '../footer.php'; ?>

