Documentation
===
# General stuff
[xEcon Core](#xecon-core) is the main plugin of the package. It handles the database and logs of xEcon entities and transactions; executes the basic commands; and holds the external API of xEcon.

## Concept of "Economic Entity" and Accounts
Not to be confused with Minecraft entities, "economic entity" (to avoid confusion, we will call an economic entity an `entity` and a Minecraft entity `MCEntity` in this file) refers to anything that has accounts that hold money and can do transactions.

Obviously, a player is one as players are the main target of xEcon transactions. However, xEcon tries to keep every transaction in record. Therefore, when a new player joins and gets money to start with, they actually get money from an account of an entity called `Server/Services` instead of from nowhere.

Let's look at another example. [PocketFactions](https://github.com/LegendOfMCPE/PocketFactions) is a plugin that uses xEcon. It manages "factions" (like organizations of players), where when these factions want to do things like claiming chunks, they have to pay money from the faction's account (instead of from its players'). In this case, factions are entities because they hold accounts to transact with.

To avoid collisions of plugins of different entity types, each entity has a "type" and a "name". For players, it is `Player/<player name in lowercase>`. If you use a JSON database, players will be saved in `entities/Player$<player lowercase name>.json`.

As you can see from a database, each entity has "accounts" and "loans". For players, 

# xEcon Core

# xEcon Tax
## Tax config.yml setup


# xEcon Jobs
