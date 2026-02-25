# OTEMPS — Journal des modifications

## Vue d'ensemble

Application de gestion d'événements culturels développée avec **Symfony 6.4** et **MySQL 8.0**.

---

## Fonctionnalités ajoutées

### 1. Gestion des places (`nb_places`)

**Objectif :** Chaque événement a un nombre limité de places. Ce nombre diminue quand un utilisateur s'inscrit et augmente quand il annule.

**Fichiers modifiés / créés :**

| Fichier | Modification |
|---|---|
| `src/Entity/Event.php` | Ajout du champ `nbPlaces` (INT, obligatoire, positif) + `getNbPlaces()`, `setNbPlaces()`, `getPlacesRestantes()` |
| `src/Controller/EventController.php` | Vérification des places disponibles avant création d'une participation |
| `src/Controller/ProfileController.php` | Vérification des places avant re-confirmation d'une participation annulée |
| `src/Controller/AdminEventController.php` | Lecture et sauvegarde de `nbPlaces` dans les formulaires admin (new/edit) |
| `templates/admin/event/new.html.twig` | Champ "Nombre de places" + validation JS |
| `templates/admin/event/edit.html.twig` | Champ "Nombre de places" pré-rempli + validation JS |
| `templates/event/show.html.twig` | Carte "Places disponibles" (vert/rouge), formulaire bloqué si complet |
| `templates/home/index.html.twig` | Badge "X place(s)" ou "Complet" sur les cartes événements |
| `migrations/Version20260225000000.php` | `ALTER TABLE events ADD nb_places INT NOT NULL DEFAULT 20` |
| `database.sql` | Colonne `nb_places` dans `CREATE TABLE events` + valeurs dans les INSERT |

**Logique :**
```php
// Event::getPlacesRestantes()
return max(0, $this->nbPlaces - $this->getParticipantsCount());
// getParticipantsCount() ne compte que les participations avec statut = 'confirmée'
```

---

### 2. Système d'avis (reviews)

**Objectif :** Les participants confirmés peuvent laisser un commentaire + une note de 0 à 5 étoiles. Chaque utilisateur ne peut laisser qu'un seul avis par événement.

**Fichiers modifiés / créés :**

| Fichier | Modification |
|---|---|
| `src/Entity/Review.php` | **Nouveau** — Entité Review : id, event (ManyToOne), user (ManyToOne), comment (TEXT ≥ 5 chars), rating (INT 0–5), createdAt (auto) |
| `src/Repository/ReviewRepository.php` | **Nouveau** — Repository de base |
| `src/Entity/Event.php` | Relation OneToMany vers Review + `getReviews()`, `getReviewsCount()`, `getAverageRating()` |
| `src/Entity/User.php` | Relation OneToMany vers Review + `getReviews()` |
| `src/Controller/ReviewController.php` | **Nouveau** — Route `POST /event/{id}/review` avec validations |
| `migrations/Version20260225000001.php` | Création de la table `reviews` avec FK vers `events` (CASCADE) et `users` |
| `templates/event/show.html.twig` | Section "Avis des participants" : résumé étoiles, formulaire, liste des avis |
| `templates/home/index.html.twig` | Note moyenne + nombre d'avis sur les cartes événements |
| `database.sql` | Table `reviews` + données de test |

**Règles métier :**
- Seuls les participants avec statut `confirmée` peuvent laisser un avis
- Un seul avis par utilisateur par événement
- Le formulaire ne montre que les utilisateurs éligibles (confirmés et n'ayant pas encore noté)

```php
// ReviewController — vérification
foreach ($event->getParticipations() as $participation) {
    if ($participation->getUser()->getId() === $user->getId()
        && $participation->getStatut() === 'confirmée') {
        $hasConfirmedParticipation = true;
    }
}
```

---

### 3. Carte Google Maps

**Objectif :** Afficher une carte interactive de la localisation pour chaque événement.

**Fichier modifié :** `templates/event/show.html.twig`

```twig
<iframe
    src="https://www.google.com/maps/embed/v1/place?key=AIzaSy...&q={{ event.lieu|url_encode }}"
    width="600" height="450" style="border:0; width:100%;" loading="lazy" allowfullscreen>
</iframe>
```

Le filtre `|url_encode` encode le champ `lieu` (ex : "Musée OTEMPS, Paris") pour l'URL.

---

### 4. Intégration formulaire d'inscription ↔ formulaire d'avis

**Objectif :** Quand un utilisateur sélectionne son profil dans le formulaire d'inscription :
- S'il est déjà participant confirmé → le bouton devient **"Annuler mon inscription"** (rouge) et la page défile vers la section avis
- S'il n'est pas inscrit → le bouton reste **"Confirmer mon inscription"** (orange)
- S'il est éligible à laisser un avis → il est pré-sélectionné dans le formulaire d'avis

**Fichier modifié :** `templates/event/show.html.twig`

**Données transmises à JavaScript via Twig :**
```twig
<script>
    window.otempsEligibleIds = {{ eligible_ids|json_encode|raw }};
    window.otempsConfirmedParticipants = {{ confirmed_participants_data|json_encode|raw }};
</script>
```

**`confirmed_participants_data`** : tableau `[{ userId, cancelUrl }]` où `cancelUrl` pointe vers `POST /profile/participation/{id}/cancel`.

**Logique JS (DOMContentLoaded) :**
```js
registrationSelect.addEventListener('change', function () {
    const confirmedData = confirmedMap[selectedId];
    if (confirmedData) {
        registrationForm.action = confirmedData.cancelUrl;       // route d'annulation
        submitBtn.textContent = 'Annuler mon inscription';
        submitBtn.style.background = 'linear-gradient(to right, #dc2626, #b91c1c)';
        reviewSection.scrollIntoView({ behavior: 'smooth' });    // défilement
        if (eligibleIds.includes(selectedId)) {
            reviewUserSelect.value = String(selectedId);          // pré-sélection
        }
    }
});
```

---

### 5. Intégration météo — Open-Meteo (100% gratuit, sans clé API)

**Objectif :** Afficher la météo sur la page de chaque événement. Annuler automatiquement les événements en cas de conditions défavorables (température < 10°C ou > 40°C).

#### 5.1 Service météo

**Fichier créé :** `src/Service/WeatherService.php`

Utilise l'API **Open-Meteo** (aucune clé API requise) :
- Géocodage : `https://geocoding-api.open-meteo.com/v1/search?name={ville}`
- Météo : `https://api.open-meteo.com/v1/forecast?latitude=...&longitude=...`

**Extraction de la ville :**
```php
// "Musée OTEMPS, Paris" → "Paris"
private function extractCity(string $lieu): string
{
    $parts = explode(',', $lieu);
    return trim(end($parts));
}
```

**Données retournées :**
```php
[
    'temperature' => 18,          // °C
    'feels_like'  => 16,          // °C (apparent_temperature)
    'description' => 'Pluie',     // description en français (codes WMO)
    'emoji'       => '🌧️',        // emoji météo
    'city'        => 'Paris',
    'humidity'    => 72,          // %
    'wind_speed'  => 25,          // km/h
    'is_forecast' => true,        // true = prévision, false = météo actuelle
]
```

**Seuils de mauvaise météo :**
```php
const BAD_TEMP_MIN = 10;   // en dessous → annulation
const BAD_TEMP_MAX = 40;   // au dessus  → annulation
```

#### 5.2 Commande d'annulation automatique

**Fichier créé :** `src/Command/CheckEventWeatherCommand.php`

```bash
php bin/console app:check-event-weather
```

- Récupère les événements actifs dans les **7 prochains jours** (`statut = 'actif'`)
- Vérifie la météo prévue pour chaque événement via Open-Meteo
- Si température < 10°C ou > 40°C → passe le statut à `annulé_météo`

```
→ Visite guidée du patrimoine artisanal | Musée OTEMPS, Paris | 15/03/2026 à 14:00
  Météo prévue : 7°C (ressenti 4°C), Pluie — humidité 85%, vent 30 km/h
  ✗ ANNULÉ — Température trop basse (7°C, minimum requis : 10°C)
```

#### 5.3 Champ `statut` sur les événements

**Fichier modifié :** `src/Entity/Event.php`

```php
#[ORM\Column(length: 50)]
private string $statut = 'actif';   // valeurs : 'actif', 'annulé_météo'

public function isAnnule(): bool
{
    return str_starts_with($this->statut, 'annulé');
}
```

**Migration :** `migrations/Version20260225000002.php`
```sql
ALTER TABLE events ADD statut VARCHAR(50) NOT NULL DEFAULT 'actif'
```

#### 5.4 Affichage dans les templates

**`templates/event/show.html.twig`** — carte météo dans la grille d'infos :
- Fond bleu (météo normale) ou rouge (conditions défavorables)
- Emoji + température + description + humidité + vent
- Bandeau d'alerte rouge si événement annulé
- Formulaire d'inscription remplacé par "Inscriptions fermées" si annulé

**`templates/home/index.html.twig`** — sur les cartes événements :
- Opacité réduite si événement annulé
- Badge "⛈ Annulé" à côté du titre
- Badge "Météo défavorable" à la place du compteur de places

#### 5.5 Configuration

Aucune clé API nécessaire. Le service est autowired automatiquement.

**`config/services.yaml`** :
```yaml
# WeatherService uses Open-Meteo (100% free, no API key needed) — autowired automatically
```

**`.env`** :
```
###> app/weather ###
# Weather powered by Open-Meteo (https://open-meteo.com) — 100% free, no API key required
###< app/weather ###
```

---

## Structure des fichiers créés / modifiés

```
projet/
├── src/
│   ├── Command/
│   │   └── CheckEventWeatherCommand.php      ← NOUVEAU
│   ├── Controller/
│   │   ├── AdminEventController.php          ← modifié (nbPlaces)
│   │   ├── EventController.php               ← modifié (météo)
│   │   ├── ProfileController.php             ← modifié (vérif. places)
│   │   └── ReviewController.php              ← NOUVEAU
│   ├── Entity/
│   │   ├── Event.php                         ← modifié (nbPlaces, statut, reviews)
│   │   ├── Review.php                        ← NOUVEAU
│   │   └── User.php                          ← modifié (reviews)
│   ├── Repository/
│   │   ├── EventRepository.php               ← modifié (findUpcomingActiveEvents)
│   │   └── ReviewRepository.php              ← NOUVEAU
│   └── Service/
│       └── WeatherService.php                ← NOUVEAU
├── migrations/
│   ├── Version20260225000000.php             ← NOUVEAU (nb_places)
│   ├── Version20260225000001.php             ← NOUVEAU (reviews)
│   └── Version20260225000002.php             ← NOUVEAU (statut)
├── templates/
│   ├── admin/event/
│   │   ├── edit.html.twig                    ← modifié (nbPlaces)
│   │   └── new.html.twig                     ← modifié (nbPlaces)
│   ├── event/
│   │   └── show.html.twig                    ← modifié (places, avis, maps, météo, annulation)
│   └── home/
│       └── index.html.twig                   ← modifié (places, avis, statut annulé)
├── config/
│   └── services.yaml                         ← modifié (commentaire WeatherService)
├── .env                                      ← modifié (commentaire Open-Meteo)
└── database.sql                              ← modifié (toutes les tables à jour)
```

---

## Base de données (`database.sql`)

Le fichier est prêt à être importé directement dans phpMyAdmin.

**Tables :**
- `users` — 1 admin + 4 utilisateurs
- `events` — 4 événements avec `nb_places` et `statut`
- `participations` — 10 participations (mix confirmée/annulée)
- `reviews` — 7 avis (participants confirmés uniquement)
- `doctrine_migration_versions` — 4 migrations pré-enregistrées (évite les conflits)

**Migrations enregistrées :**
```
Version20260211000000  — structure initiale
Version20260225000000  — ajout nb_places
Version20260225000001  — ajout table reviews
Version20260225000002  — ajout statut (météo)
```

---

## Commandes utiles

```bash
# Appliquer les migrations (si base vide)
php bin/console doctrine:migrations:migrate

# Ou importer directement le fichier SQL dans phpMyAdmin
# (les migrations sont déjà enregistrées dans doctrine_migration_versions)

# Vérifier la météo et annuler les événements défavorables
php bin/console app:check-event-weather

# Vider le cache
php bin/console cache:clear

# Lancer le serveur de développement
symfony server:start
```

---

## API utilisées

| API | Usage | Coût | Clé requise |
|---|---|---|---|
| [Open-Meteo](https://open-meteo.com) | Météo + prévisions 7 jours | Gratuit | ❌ Non |
| [Open-Meteo Geocoding](https://open-meteo.com/en/docs/geocoding-api) | Conversion ville → lat/lon | Gratuit | ❌ Non |
| [Google Maps Embed](https://developers.google.com/maps/documentation/embed) | Carte interactive sur la page événement | Gratuit (clé incluse dans le code) | ✅ Oui (déjà configurée) |
