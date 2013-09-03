<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Transaction Class ******
* This class allows users to gift or trade (exchange) virtual goods and services. It does this by storing the functions
* of each virtual good (which should be provided by the app) in a list that, upon all conditions being met, will all
* simultaneously be exchanged between all users that agreed to partake in the transaction.
* 
****** Example of Use ******


****** Methods Available ******
* Transaction::setColumns($columns)		// Set which columns get called
*
****** Database ******

CREATE TABLE IF NOT EXISTS `transactions`
(
	`id`					int(11)			unsigned	NOT NULL	AUTO_INCREMENT,
	`title`					varchar(32)					NOT NULL	DEFAULT '',
	
	PRIMARY KEY (`id`),
	INDEX (`user_id`, `time_posted`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `transactions_users`
(
	`user_id`				int(11)			unsigned	NOT NULL	DEFAULT '0',
	`transaction_id`		int(11)			unsigned	NOT NULL	DEFAULT '0',
	`has_agreed`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
	
	UNIQUE (`user_id`, `transaction_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `transactions_list`
(
	`id`					int(11)			unsigned	NOT NULL	AUTO_INCREMENT,
	`transaction_id`		int(11)			unsigned	NOT NULL	DEFAULT '0',
	
	`class`					varchar(16)					NOT NULL	DEFAULT '',
	
	`display_method`		varchar(22)					NOT NULL	DEFAULT '',
	`display_parameters`	text						NOT NULL	DEFAULT '',
	
	`process_method`		varchar(22)					NOT NULL	DEFAULT '',
	`process_parameters`	text						NOT NULL	DEFAULT '',
	
	PRIMARY KEY (`id`),
	INDEX (`transaction_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

****** Transaction App Classes ******

// All of the APP classes that display a transaction must return an array with the following data:

// Display the Transaction (allows viewers to see what the transaction will consist of)
return array(
		"image"			=> "/images/coins.png",			// Image to show in the trade view
		"title"			=> "250 Coins",					// The name of the digital good being traded
		"sender_id"		=> 100,							// The ID of the user trading the goods
		"recipient_id"	=> 140,							// The ID of the user receiving the goods.
		"description"	=> "An exchange of 250 coins."	// The description of the transaction.
	);

*/

class Transaction {
	
	
/****** Delete Transaction ******/
	public static function delete
	(
		$id		/* <int> The Job ID to delete. */
	)			/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Job::delete(153)
	{
		return Database::query("DELETE FROM jobs WHERE id=?", array($id));
	}
}

