# Tests Unitaires — OTEMPS (GestionEvents)

## Vue d'ensemble

Ce projet contient **6 fichiers de tests unitaires** répartis en 3 catégories :

| Catégorie | Fichier | Nombre de tests |
|-----------|---------|-----------------|
| Utilisateur / Auth | `tests/User/UtilisateursTest.php` | 8 |
| Utilisateur / Inscription | `tests/User/RegistrationTest.php` | 6 |
| Événements | `tests/Event/EventTest.php` | 8 |
| Événements (statut/places) | `tests/Event/EventStatutTest.php` | 8 |
| Patrimoine (Catégorie) | `tests/Patrimoine/CategorieTest.php` | 7 |
| Patrimoine (Objet) | `tests/Patrimoine/ObjetTest.php` | 7 |

---

## Structure des tests

```
tests/
├── bootstrap.php              # Fichier de démarrage PHPUnit (déjà existant)
├── User/
│   ├── UtilisateursTest.php   # Tests sur l'entité Utilisateurs
│   └── RegistrationTest.php   # Tests sur la logique d'inscription
├── Event/
│   ├── EventTest.php          # Tests sur l'entité Event (données, relations)
│   └── EventStatutTest.php    # Tests sur le statut et les places disponibles
└── Patrimoine/
    ├── CategorieTest.php      # Tests sur l'entité Categorie
    └── ObjetTest.php          # Tests sur l'entité Objet
```

---

## Prérequis

- PHP >= 8.1
- Composer installé
- PHPUnit ^9.6 (déjà dans `composer.json`)

Vérifier que les dépendances sont installées :

```bash
composer install
```

---

## Comment lancer les tests

### Lancer tous les tests

```bash
php vendor/bin/phpunit
```

### Lancer une catégorie spécifique

```bash
# Tests utilisateur uniquement
php vendor/bin/phpunit tests/User/

# Tests événements uniquement
php vendor/bin/phpunit tests/Event/

# Tests patrimoine uniquement
php vendor/bin/phpunit tests/Patrimoine/
```

### Lancer un seul fichier

```bash
php vendor/bin/phpunit tests/User/UtilisateursTest.php
php vendor/bin/phpunit tests/Event/EventStatutTest.php
php vendor/bin/phpunit tests/Patrimoine/CategorieTest.php
```

### Lancer avec un affichage détaillé (verbose)

```bash
php vendor/bin/phpunit --testdox
```

Cela affiche le nom de chaque test comme une phrase lisible, par exemple :
```
App\Tests\User\UtilisateursTest
 [x] Setters and getters
 [x] Email is normalized to lowercase
 [x] Get roles returns array
 ...
```

### Filtrer par nom de test

```bash
php vendor/bin/phpunit --filter testEmailIsNormalizedToLowercase
```

---

## Description des tests

### 1. `tests/User/UtilisateursTest.php`

Tests sur l'entité `Utilisateurs` (auth, UserInterface) :

| Test | Ce qu'il vérifie |
|------|-----------------|
| `testSettersAndGetters` | Les setters et getters fonctionnent correctement |
| `testEmailIsNormalizedToLowercase` | `setEmail()` convertit automatiquement en minuscules |
| `testGetRolesReturnsArray` | `getRoles()` retourne un tableau contenant le rôle |
| `testGetPasswordReturnsMdp` | `getPassword()` retourne le mot de passe haché |
| `testFaceImageIsNullByDefault` | `faceImage` est `null` à la création |
| `testSetFaceImage` | `setFaceImage()` enregistre correctement la photo |
| `testEraseCredentialsDoesNothing` | `eraseCredentials()` ne supprime pas le mot de passe |
| `testDefaultRoleIsUserIfNotSet` | Le rôle par défaut est `ROLE_USER` |

---

### 2. `tests/User/RegistrationTest.php`

Tests sur la logique d'inscription d'un nouvel utilisateur :

| Test | Ce qu'il vérifie |
|------|-----------------|
| `testNewUserHasNoId` | Un utilisateur non persisté n'a pas d'ID |
| `testNewUserHasEmptyParticipations` | Aucune participation à la création |
| `testUserCanSetAllRegistrationFields` | Tous les champs d'inscription sont correctement assignables |
| `testDefaultRoleAfterRegistrationIsUser` | Le rôle à l'inscription est `ROLE_USER` (pas admin) |
| `testUserCanHaveFaceImageAfterRegistration` | La photo FaceID peut être ajoutée après inscription |
| `testSetRoleToAdmin` | Un utilisateur peut être promu administrateur |

---

### 3. `tests/Event/EventTest.php`

Tests sur l'entité `Event` (création, données, relations) :

| Test | Ce qu'il vérifie |
|------|-----------------|
| `testEventCreationWithValidData` | Un événement se crée avec tous ses champs |
| `testDefaultStatutIsActif` | Le statut par défaut est `actif` |
| `testSetStatut` | Le statut peut être changé (ex: `annulé`) |
| `testIsAnnuleReturnsFalseForActif` | `isAnnule()` retourne `false` quand statut = `actif` |
| `testNewEventHasNoParticipations` | Aucune participation à la création |
| `testNewEventHasNoReviews` | Aucun avis à la création, note moyenne = `null` |
| `testDateFinIsAfterDateDebut` | La date de fin est bien après la date de début |
| `testNewEventHasNoId` | Un événement non persisté n'a pas d'ID |

---

### 4. `tests/Event/EventStatutTest.php`

Tests sur la gestion du statut et des places d'un événement :

| Test | Ce qu'il vérifie |
|------|-----------------|
| `testPlacesRestantesEqualsNbPlacesWhenNoParticipants` | Sans participants, places restantes = nb total |
| `testPlacesRestantesIsNeverNegative` | `getPlacesRestantes()` ne retourne jamais un négatif |
| `testStatutTransitions` | Transitions de statut : actif → complet → annulé |
| `testIsAnnuleDetectsAnnulePrefix` | `isAnnule()` détecte tout statut commençant par "annulé" |
| `testNbPlacesCanBeUpdated` | Le nombre de places peut être mis à jour |
| `testGetParticipantsCountIsZeroWithoutParticipations` | Comptage des participants = 0 sans données |
| `testReviewsCountIsZeroWithoutReviews` | Comptage des avis = 0 sans données |
| `testAverageRatingIsNullWithoutReviews` | Note moyenne = `null` sans avis |

---

### 5. `tests/Patrimoine/CategorieTest.php`

Tests sur l'entité `Categorie` (patrimoine culturel) :

| Test | Ce qu'il vérifie |
|------|-----------------|
| `testSettersAndGetters` | Nom et description de catégorie fonctionnent |
| `testNewCategorieHasNoId` | Pas d'ID avant persistance |
| `testNewCategorieHasNoObjets` | Aucun objet associé à la création |
| `testDescriptionTypeIsOptional` | La description peut être `null` |
| `testAddObjet` | Ajout d'un objet à la catégorie + liaison bidirectionnelle |
| `testAddObjetDoesNotDuplicate` | Ajouter deux fois le même objet ne crée pas de doublon |
| `testRemoveObjet` | Suppression d'un objet de la catégorie |
| `testToStringReturnsNomCategorie` | `__toString()` retourne le nom de la catégorie |

---

### 6. `tests/Patrimoine/ObjetTest.php`

Tests sur l'entité `Objet` (patrimoine culturel) :

| Test | Ce qu'il vérifie |
|------|-----------------|
| `testSettersAndGetters` | Nom, époque, origine, matériaux fonctionnent |
| `testNewObjetHasNoId` | Pas d'ID avant persistance |
| `testNewObjetHasNoMedias` | Aucun média associé à la création |
| `testOptionalFieldsCanBeNull` | Les champs optionnels acceptent `null` |
| `testObjetCanBeAssignedToCategorie` | Un objet peut être associé à une catégorie |
| `testObjetCategorieCanbeSwitched` | La catégorie d'un objet peut être changée |
| `testToStringReturnsNom` | `__toString()` retourne le nom de l'objet |

---

## Comment créer un nouveau test

1. Créer un fichier dans le bon dossier sous `tests/` (ex: `tests/Event/MonNouveauTest.php`)

2. Utiliser le namespace correspondant :
   ```php
   namespace App\Tests\Event;
   ```

3. Étendre `PHPUnit\Framework\TestCase` :
   ```php
   use PHPUnit\Framework\TestCase;

   class MonNouveauTest extends TestCase
   {
       public function testMonCas(): void
       {
           // Arrange
           $objet = new MonEntite();

           // Act
           $objet->setValeur('test');

           // Assert
           $this->assertSame('test', $objet->getValeur());
       }
   }
   ```

4. Chaque méthode de test **doit** :
   - Commencer par `test` (ex: `testEmailIsValid`)
   - Être publique (`public function`)
   - Ne retourner rien (`: void`)

---

## Assertions PHPUnit les plus utilisées

| Assertion | Usage |
|-----------|-------|
| `$this->assertSame($expected, $actual)` | Vérifie égalité stricte (type + valeur) |
| `$this->assertNull($value)` | Vérifie que la valeur est `null` |
| `$this->assertNotNull($value)` | Vérifie que la valeur n'est pas `null` |
| `$this->assertCount($n, $collection)` | Vérifie qu'une collection a N éléments |
| `$this->assertTrue($condition)` | Vérifie qu'une condition est vraie |
| `$this->assertFalse($condition)` | Vérifie qu'une condition est fausse |
| `$this->assertIsArray($value)` | Vérifie que la valeur est un tableau |
| `$this->assertContains($needle, $haystack)` | Vérifie qu'un tableau contient une valeur |
| `$this->assertGreaterThan($expected, $actual)` | Vérifie que actual > expected |
| `$this->assertStringStartsWith($prefix, $string)` | Vérifie le début d'une chaîne |

---

## Configuration PHPUnit (`phpunit.xml.dist`)

Si le fichier `phpunit.xml.dist` n'existe pas encore, en créer un à la racine du projet :

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true">
    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </coverage>
</phpunit>
```

---

## Résultat attendu

Après `php vendor/bin/phpunit --testdox`, vous devriez voir :

```
App\Tests\User\UtilisateursTest
 [x] Setters and getters
 [x] Email is normalized to lowercase
 [x] Get roles returns array
 [x] Get password returns mdp
 [x] Face image is null by default
 [x] Set face image
 [x] Erase credentials does nothing
 [x] Default role is user if not set

App\Tests\User\RegistrationTest
 [x] New user has no id
 [x] New user has empty participations
 [x] User can set all registration fields
 [x] Default role after registration is user
 [x] User can have face image after registration
 [x] Set role to admin

App\Tests\Event\EventTest
 [x] Event creation with valid data
 [x] Default statut is actif
 [x] Set statut
 [x] Is annule returns false for actif
 [x] New event has no participations
 [x] New event has no reviews
 [x] Date fin is after date debut
 [x] New event has no id

App\Tests\Event\EventStatutTest
 [x] Places restantes equals nb places when no participants
 [x] Places restantes is never negative
 [x] Statut transitions
 [x] Is annule detects annule prefix
 [x] Nb places can be updated
 [x] Get participants count is zero without participations
 [x] Reviews count is zero without reviews
 [x] Average rating is null without reviews

App\Tests\Patrimoine\CategorieTest
 [x] Setters and getters
 [x] New categorie has no id
 [x] New categorie has no objets
 [x] Description type is optional
 [x] Add objet
 [x] Add objet does not duplicate
 [x] Remove objet
 [x] To string returns nom categorie

App\Tests\Patrimoine\ObjetTest
 [x] Setters and getters
 [x] New objet has no id
 [x] New objet has no medias
 [x] Optional fields can be null
 [x] Objet can be assigned to categorie
 [x] Objet categorie canbe switched
 [x] To string returns nom

OK (44 tests, X assertions)
```
