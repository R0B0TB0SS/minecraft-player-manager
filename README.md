# Minecraft Player Manager for Pelican Panel

## 🇬🇧 English

### ⚠️ Note from the Developer
**I am a Japanese developer.**
While I strive to provide support in English, please understand that my responses might be delayed or rely on translation tools. I appreciate your patience and understanding!

### Overview
**Minecraft Player Manager** is a plugin for [Pelican Panel](https://pelican.dev/) that allows you to manage players on your Minecraft servers directly from the panel.
View real-time status with RCON, check inventories, and perform administrative actions like Kick, Ban, and OP/Deop without entering the game.

### Features
*   **Real-time Player List**: View all known players (Online, Offline, Banned, OP).
*   **Visual Stats**:
    *   Health (Hearts) and Food (Drumsticks) visualization.
    *   Experience Level, Gamemode.
    *   Statistics from world data (Play time, Mobs killed, Distance walked, Deaths).
*   **Inventory Viewer**:
    *   Visual representation of player inventory and armor slots.
*   **Management Actions**:
    *   **kick**: Kick a player from the server.
    *   **ban**: Ban a player (with reason).
    *   **op / deop**: Grant or revoke operator status.
    *   **clear inventory**: Wipe a player's items.
*   **Multi-language Support**: Fully localized in English and Japanese.

### Requirements
*   **PHP**: 8.2 or higher
*   **Node.js**: v20 or higher
*   **Yarn**: v1.22 or higher
*   **Pelican Panel**: v1.0.0 or higher
*   **Minecraft Server**:
    *   **Egg Tag**: The server MUST have the `minecraft` tag assigned for the plugin to be visible.
    *   **Java Edition**: Version 1.13+ recommended (for Data Command support).
    *   **RCON**: Must be enabled (`enable-rcon=true` and valid port/password).
        *   **Note**: Use a dedicated port (different from the primary server port) and ensure the allocation is assigned correctly (e.g., set the Alias to the Node IP).
    *   **Query**: Must be enabled (`enable-query=true`) for real-time player listing.

### Installation
1.  Download the plugin release.
2.  Upload the plugin to your Pelican Panel's `plugins` directory.
3.  Install via the Panel Administration page.

### Usage
1.  Navigate to the **Server View** in Pelican Panel.
2.  Click on the **Player** tab in the navigation menu.
3.  You will see a list of players. Click "View" (or "詳細") to see real-time details and inventory.