CREATE TABLE xecon_accounts (
	owner_type VARCHAR(100),
	owner_name VARCHAR(100),
	acc_name VARCHAR(100),
	balance DOUBLE,
	last_finalize TIMESTAMP,
	PRIMARY KEY(owner_type, owner_name, acc_name)
);
CREATE TABLE xecon_account_modifiers (
	owner_type VARCHAR(100),
	owner_name VARCHAR(100),
	acc_name VARCHAR(100),
	modifier_name VARCHAR(100),
	addition_time TIMESTAMP,
	FOREIGN KEY (owner_type, owner_name, acc_name) REFERENCES xecon_accounts (owner_type, owner_name, acc_name)
			ON DELETE CASCADE
			ON UPDATE CASCADE,
	PRIMARY KEY (owner_type, owner_name, acc_name, modifier_name)
);
