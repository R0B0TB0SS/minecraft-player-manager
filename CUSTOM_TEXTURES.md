# Minecraft Player Manager Custom Assets Guide

# English

## Introduction

For the plugin to properly load custom resources (textures, langs), the file structure on the web server **must exactly match** the resource path expected by the game. Any discrepancy in folder structure or file naming will prevent the plugin to find the resources.

---

## 1. General Principle

Minecraft uses a **resource path system** built from:
- A **namespace** (usually `minecraft` or your pack/mod name)
- A **resource type** (`textures`, `langs`)
- A **subcategory** (`block`, `item`)
- The **in-game ID** of the object

The web server must therefore faithfully reproduce this hierarchy so that HTTP requests sent by the client match the files actually present.

---

## 2. Folder Structure Diagram
webserver/
└── assets/
    ├── minecraft/
    │   ├── textures/
    │   │   ├── block/
    │   │   │   ├── stone.png
    │   │   │   ├── oak_log.png
    │   │   │   └── chest.png          <-- Tile Entity (block with logic)
    │   │   │
    │   │   ├── item/
    │   │   │   ├── diamond_sword.png
    │   │   │   └── apple.png
    │   │   │
    │   │   └── entity/
    │   │       └── chest/
    │   │           └── normal.png
    │   │
    │   └── langs/
    │       ├── en_us.json
    │       └── fr_fr.json
    └── ae2/
        ├── ....
        .
        .
        .

---

## 3. Important Rule: Tile Entities

**Tile Entities** (blocks with dynamic behavior such as furnaces, chests, or spawners) must **always** have their texture placed in the folder:
assets/minecraft/textures/block/

❌ **Mistake**: creating a separate `entity/` folder — this **will not work**.

✅ **Best practice**: always place them in `textures/block/`.

---

## 4. Naming Rule: ID Must Match Exactly

Each texture file must bear **the exact ID** of the object.

### Example:

| In-Game Object       | Internal ID         | Expected Filename        |
|----------------------|---------------------|--------------------------|
| Furnace              | `furnace`           | `furnace.png`            |
| Stone Block          | `stone`             | `stone.png`              |
| Diamond Sword        | `diamond_sword`     | `diamond_sword.png`      |
| Oak Log              | `oak_log`           | `oak_log.png`            |

for the 3D textures of block, I use https://co3moz.github.io/minecraft-render/

---

# Français

## Introduction

Pour que le plugin puisse charger correctement les ressources personnalisées (textures, langues), il est impératif que l'arborescence des fichiers sur le serveur web **corresponde exactement** au chemin d'accès attendu par le jeu. Le moindre écart dans la structure des dossiers ou dans le nommage des fichiers empêchera le plugin de les trouver.

---

## 1. Principe général

Minecraft utilise un système de **ressources basé sur des chemins** (resource path) construits à partir de :
- Un **namespace** (généralement `minecraft` ou le nom de votre pack/mod)
- Un **type de ressource** (`textures`, `langs`)
- Une **sous-catégorie** (`block`, `item`)
- L'**identifiant (ID) de l'objet** en jeu

Le serveur web doit donc reproduire fidèlement cette hiérarchie afin que les requêtes HTTP envoyées par le client correspondent aux fichiers réellement présents.

---

## 2. Schéma de l'arborescence
webserver/
└── assets/
    ├── minecraft/
    │   ├── textures/
    │   │   ├── block/
    │   │   │   ├── stone.png
    │   │   │   ├── oak_log.png
    │   │   │   └── chest.png          <-- Tile Entity (bloc avec logique)
    │   │   │
    │   │   ├── item/
    │   │   │   ├── diamond_sword.png
    │   │   │   └── apple.png
    │   │   │
    │   │   └── entity/
    │   │       └── chest/
    │   │           └── normal.png
    │   │
    │   └── langs/
    │       ├── en_us.json
    │       └── fr_fr.json
    └── ae2/
        ├── ....
        .
        .
        .

---

## 3. Règle importante : les Tile Entities

Les **Tile Entities** (blocs possédant un comportement dynamique comme les fours, coffres, ou spawners) doivent **impérativement** avoir leur texture placée dans le dossier :
assets/minecraft/textures/block/

❌ **Erreur** : créer un dossier séparé `entity/` — cela **ne fonctionnera pas**.

✅ **Bonne pratique** : ranger dans `textures/block/`.

---

## 4. Règle du nommage : l'ID doit correspondre exactement

Chaque fichier texture doit porter **le nom exact de l'ID** de l'objet.

### Exemple :

| Objet en jeu       | ID                    | Nom du fichier attendu       |
|----------------------|---------------------|------------------------------|
| Four                 | `furnace`           | `furnace.png`                |
| Bloc de pierre       | `stone`             | `stone.png`                  |
| Épée en diamant      | `diamond_sword`     | `diamond_sword.png`          |
| Bûche de chêne       | `oak_log`           | `oak_log.png`                |

pour les textures de block en 3D, j'utilise https://co3moz.github.io/minecraft-render/