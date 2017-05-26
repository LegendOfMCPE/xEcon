-- main
CREATE TABLE xecon_metadata (
	name VARCHAR(20) PRIMARY KEY,
	value VARCHAR(20)
);
CREATE TABLE xecon_accounts (
	ownerType VARCHAR(120),
	ownerName VARCHAR(70),
	accName VARCHAR(50), -- it says 40 in the config, but I want to reserve 10 for technical use in case it's needed
	balance DOUBLE,
	isLiability BIT(1),
	PRIMARY KEY(ownerType, ownerName, accName),
	KEY(ownerType, ownerName)
);
