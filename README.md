xEcon [![Chat@Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/LegendOfMCPE/xEcon?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge) [![Poggit-CI](https://poggit.pmmp.io/ci.shield/LegendOfMCPE/xEcon/xEcon)](https://poggit.pmmp.io/ci/LegendOfMCPE/xEcon/~)
=====

The eXtraordinary eXtensible economy plugin with ultimate fleXibility.

## AccountOwner types
xEcon is the economy plugin for _all the things_. This means that everything can own accounts. Apart from players, other
plugins may declare new types of AccountOwners. For example, a factions plugin may allow factions to own faction
accounts. Another example is that multiple players can form a "joint account owner" and own accounts jointly.

## Multiple accounts per AccountOwner
Each AccountOwner can have multiple accounts. Different accounts may behave differently depending on the applied
AccountModifiers.

## Different AccountModifiers applied on an account
An AccountModifier affects the value of an account in many ways. For the basic use, an AccountModifier can change the
currency unit of an account so that you can have different currencies on the same plugin. They can do things like
capping the maximum account balance and allowing overdraft. Other plugins can also use AccountModifier to identify
account types and implement custom behaviour, such as creating ATMs to access bank accounts, creating wallets to contain
money in the inventory that are deleted/collected upon death, etc.

## Exchange rates
Exchange rate paths can be set up in config.yml to let players exchange different currencies in one-way or two-way.
