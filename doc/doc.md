# m2-productrules : documentation

## BO

### Formulaire
![](images/bo_form.png)

### Config
![](images/bo_config.png)
### les conditions
Il est possible d'empiler les conditions

#### Attributs produit
Conditions sur les attributs de Magento qui sont selectionnés dans la config du module en BO

#### Dans un website
![](images/cond_website.png)

#### Nouveau
![](images/cond_new.png)

#### Créer depuis X heures/jours
![](images/cond_create.png)
- champs 1 : un nombre pour le champs 2
- champs 2
  - heures
  - jours

#### A un prix promo
![](images/cond_promo_price.png)
- champs 1 : un nombre pour le champs 2
- champs 2
  - percent
  - fixe
  
#### A une regle catalogue en cours
![](images/cond_promo_rule.png)
- champs 1 : un nombre pour le champs 2
- champs 2
  - percent
  - fixe
  
#### Meilleures ventes
![](images/cond_bestseller.png)
- champs 1 : un nombre pour le champs 2 (heures / jours)
- champs 2
  - de tous les temps
  - heures
  - jours
- champs 3 : pour le champs 4
  - est
  - n'est pas
- champs 4 : la liste des states
- champs 5 : pour le champs 6
    - est
    - n'est pas
- champs 6 : la liste des status

### les actions
Il est possible d'empiler les actions à effectuer sur la liste des produits trouvés par les conditions

#### ajouter à des catégories
![](images/action_add_category.png)
- champs 1
  - vide la catégorie
  - ne vide pas la catégorie

#### retirer des catégories
![](images/action_remove_category.png)

#### Affecter le numéro
![](images/action_affect_number.png)
Cette action est à coupler avec la condition "Best seller" et permet de stocker dans un attribut du produit le nombre trouvé (ici le nombre de vente)
- champs 1
  - ne pas effacer les valeurs de cet attribut pour les autres produits
  - effacer les valeurs de cet attribut pour les autres produits

#### Affecter la position
![](images/action_affect_position.png)
Cette action est à coupler avec la condition "Best seller" et permet de stocker dans un attribut du produit la position trouvée
- champs 1
    - ne pas effacer les valeurs de cet attribut pour les autres produits
    - effacer les valeurs de cet attribut pour les autres produits

#### modifier un attribut simple
affecter une valeur a un attribut du produit

### modifier un attribut de type multiple
![](images/action_multi_attr.png)
- champs 1
  - ajouter une ou plusieurs valeurs
  - retirer une ou plusieurs valeurs 