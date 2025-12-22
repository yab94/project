# Migration vers UrlGenerator - État d'avancement

## ✅ Contrôleurs mis à jour

### QuoteController (100% complété)
- [x] Toutes les routes ont des noms
- [x] Tous les redirects utilisent `$this->urlGenerator()->route()`
- Routes nommées :
  - `quotes.index` - Liste des devis
  - `quotes.create` - Formulaire de création
  - `quotes.store` - Enregistrement
  - `quotes.view` - Détails d'un devis
  - `quotes.addLine` - Ajout d'une ligne
  - `quotes.send` - Envoi du devis
  - `quotes.accept` - Acceptation
  - `quotes.reject` - Rejet

### PersonController (100% complété)
- [x] Toutes les routes ont des noms  
- [x] Tous les redirects utilisent `$this->urlGenerator()->route()`
- Routes nommées :
  - `persons.index` - Liste des personnes
  - `persons.create` - Formulaire de création
  - `persons.store` - Enregistrement
  - `persons.view` - Détails d'une personne
  - `persons.delete` - Suppression

### HomeController
- [x] Route nommée mais pas de redirects (juste du render)

## ⏳ Contrôleurs à mettre à jour

### InvoiceController (À faire)
Routes à nommer :
- [ ] `invoices.index`
- [ ] `invoices.create`
- [ ] `invoices.store`
- [ ] `invoices.view`
- [ ] `invoices.issue`
- [ ] `invoices.pay`
- [ ] `invoices.cancel`

Redirects à convertir : ~10 occurrences

## ⏳ Vues à mettre à jour

### Vues Person (Partiellement fait)
- [x] `person/view.php` - Lien "Create Quote" utilise `$url->route()`
- [ ] `person/view.php` - Lien "Back to List" à convertir
- [ ] `person/index.php` - Lien "Create New Person" à convertir
- [ ] `person/index.php` - Lien "View" dans la liste à convertir
- [ ] `person/create.php` - Lien "Cancel" à convertir

### Vues Quote (À faire)
- [ ] `quote/index.php` - Tous les liens
- [ ] `quote/view.php` - Tous les liens
- [ ] `quote/create.php` - Lien "Cancel"

### Vues Invoice (À faire)
- [ ] `invoice/index.php` - Tous les liens
- [ ] `invoice/view.php` - Tous les liens
- [ ] `invoice/create.php` - Tous les liens

### Vues Shared (À faire)
- [ ] `home/index.php` - 4 liens vers les modules
- [ ] `layout/header.php` - 4 liens de navigation

## �� Script de migration pour les vues

Remplacer tous les liens hardcodés par :
```php
<!-- Avant -->
<a href="/persons">Liste</a>
<a href="/persons/<?= $person->id() ?>">Voir</a>
<a href="/quotes/create?person_id=<?= $id ?>">Créer</a>

<!-- Après -->
<a href="<?= $url->route('persons.index') ?>">Liste</a>
<a href="<?= $url->route('persons.view', ['id' => $person->id()]) ?>">Voir</a>
<a href="<?= $url->route('quotes.create', [], ['person_id' => $id]) ?>">Créer</a>
```

## 🎯 Prochaines étapes

1. Terminer InvoiceController (ajouter noms de routes + convertir redirects)
2. Mettre à jour toutes les vues Person
3. Mettre à jour toutes les vues Quote
4. Mettre à jour toutes les vues Invoice
5. Mettre à jour les vues Shared (home + header)
6. Vérifier qu'aucun lien hardcodé ne subsiste avec grep
7. Exécuter les tests

## 🔍 Commandes utiles

```bash
# Trouver tous les liens hardcodés restants
grep -r "href=['\"]/" src/Modules --include="*.php" | grep -v "route("

# Trouver tous les redirects hardcodés restants
grep -r "redirect(['\"]/" src/Modules --include="*Controller.php"

# Tester
sudo make test
```
